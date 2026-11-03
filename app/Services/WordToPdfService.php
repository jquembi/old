<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Converte documentos Word (.doc/.docx) em PDF.
 *
 * Usa o LibreOffice (preserva o layout original). Quando não está instalado,
 * pode recorrer a um PDF simples só com o texto, se o chamador o permitir.
 */
class WordToPdfService
{
    private const MIMES_WORD = [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function pareceWord(string $mime, string $nome): bool
    {
        if (in_array(strtolower($mime), self::MIMES_WORD, true)) {
            return true;
        }

        return in_array(strtolower(pathinfo($nome, PATHINFO_EXTENSION)), ['doc', 'docx'], true);
    }

    public function nomePdf(string $nome): string
    {
        return pathinfo($nome, PATHINFO_FILENAME).'.pdf';
    }

    public function binarioDisponivel(): ?string
    {
        $configurado = config('services.libreoffice.binary');

        if ($configurado && is_file($configurado)) {
            return $configurado;
        }

        $finder = new ExecutableFinder;

        foreach (['soffice', 'libreoffice'] as $nome) {
            if ($caminho = $finder->find($nome)) {
                return $caminho;
            }
        }

        foreach ([
            'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
            'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
            '/usr/bin/soffice',
            '/usr/local/bin/soffice',
            '/opt/libreoffice/program/soffice',
        ] as $caminho) {
            if (is_file($caminho)) {
                return $caminho;
            }
        }

        return null;
    }

    /**
     * Devolve o caminho de um PDF temporário (quem chama deve apagá-lo) ou null.
     */
    public function converterConteudo(string $conteudo, string $nome, bool $permitirFallbackTexto = true): ?string
    {
        $pdf = $this->converterComLibreOffice($conteudo, $nome);

        if ($pdf === null && $permitirFallbackTexto) {
            $pdf = $this->converterApenasTexto($conteudo);
        }

        return $pdf;
    }

    private function converterComLibreOffice(string $conteudo, string $nome): ?string
    {
        $binario = $this->binarioDisponivel();

        if (! $binario) {
            return null;
        }

        $extensao = strtolower(pathinfo($nome, PATHINFO_EXTENSION)) === 'doc' ? 'doc' : 'docx';
        $pasta = sys_get_temp_dir().DIRECTORY_SEPARATOR.'inotu_word_'.bin2hex(random_bytes(8));
        $perfil = $pasta.DIRECTORY_SEPARATOR.'perfil';

        if (! mkdir($perfil, 0700, true) && ! is_dir($perfil)) {
            return null;
        }

        $origem = $pasta.DIRECTORY_SEPARATOR.'documento.'.$extensao;
        file_put_contents($origem, $conteudo);

        try {
            $processo = new Process([
                $binario,
                '--headless',
                '--norestore',
                '-env:UserInstallation='.$this->urlPerfil($perfil),
                '--convert-to', 'pdf',
                '--outdir', $pasta,
                $origem,
            ]);
            $processo->setTimeout(90);
            $processo->run();

            $gerado = $pasta.DIRECTORY_SEPARATOR.'documento.pdf';

            if (! is_file($gerado)) {
                Log::warning('Conversão Word→PDF falhou.', ['erro' => $processo->getErrorOutput()]);

                return null;
            }

            $destino = tempnam(sys_get_temp_dir(), 'inotu_pdf_');
            rename($gerado, $destino);

            return $destino;
        } catch (\Throwable $e) {
            Log::warning('Conversão Word→PDF falhou.', ['erro' => $e->getMessage()]);

            return null;
        } finally {
            $this->apagarPasta($pasta);
        }
    }

    private function converterApenasTexto(string $conteudo): ?string
    {
        if (! class_exists(\ZipArchive::class) || ! class_exists(\FPDF::class)) {
            return null;
        }

        $temp = tempnam(sys_get_temp_dir(), 'inotu_docx_');
        file_put_contents($temp, $conteudo);

        $zip = new \ZipArchive;
        $xml = $zip->open($temp) === true ? $zip->getFromName('word/document.xml') : false;
        $zip->close();
        @unlink($temp);

        if ($xml === false) {
            return null;
        }

        $xml = preg_replace('/<\/w:p>/', "\n", $xml);
        $texto = html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $texto = mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');

        $pdf = new \FPDF;
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 11);
        $pdf->MultiCell(0, 6, $texto);

        $destino = tempnam(sys_get_temp_dir(), 'inotu_pdf_');
        $pdf->Output('F', $destino);

        return $destino;
    }

    private function urlPerfil(string $caminho): string
    {
        $caminho = str_replace('\\', '/', $caminho);

        return 'file:///'.ltrim($caminho, '/');
    }

    private function apagarPasta(string $pasta): void
    {
        if (! is_dir($pasta)) {
            return;
        }

        $itens = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($pasta, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($itens as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($pasta);
    }
}
