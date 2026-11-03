<?php

namespace App\Services;

use App\Models\DocumentoLayout;
use Throwable;

class PdfQrStampService
{
    /**
     * Aplica o QR code estilizado nas páginas de um PDF ou imagem, de acordo com o layout configurado.
     */
    public static function processarDownload(
        string $conteudo,
        string $mime,
        string $qrUrl,
        string $tipoDocumento = 'default',
        ?string $textoAbaixoQr = null
    ): string {
        if (! str_contains($mime, 'pdf')) {
            return $conteudo;
        }

        try {
            return static::aplicarStampPdf($conteudo, $qrUrl, $tipoDocumento, $textoAbaixoQr);
        } catch (Throwable $e) {
            // Em caso de PDF protegido ou erro de parsing, preserva o PDF original
            report($e);

            return $conteudo;
        }
    }

    /**
     * Aplica o selo QR no PDF utilizando FPDI e as configurações da base de dados.
     */
    protected static function aplicarStampPdf(
        string $conteudo,
        string $qrUrl,
        string $tipoDocumento,
        ?string $textoPersonalizado = null
    ): string {
        $layout = DocumentoLayout::efetivo($tipoDocumento);
        if (! $layout['ativo']) {
            return $conteudo;
        }

        $blocos = $layout['blocos'];
        if ($textoPersonalizado) {
            foreach ($blocos as &$bloco) {
                if (($bloco['tipo'] ?? '') === 'qr') {
                    $bloco['props']['rotulo'] = $textoPersonalizado;
                }
            }
            unset($bloco);
        }

        return static::aplicarBlocos($conteudo, $blocos, [
            'qr_url' => $qrUrl,
            'tipo_nome' => DocumentoLayout::tipos()[$tipoDocumento]['nome'] ?? '',
        ]);
    }

    /**
     * Sobrepõe os blocos de um layout a cada página do PDF.
     */
    public static function aplicarBlocos(string $conteudo, array $blocos, array $ctx = []): string
    {
        $tempPdf = tempnam(sys_get_temp_dir(), 'pdf_qr_src_').'.pdf';
        file_put_contents($tempPdf, $conteudo);

        try {
            $fpdi = new DocumentoPdf;
            $fpdi->SetAutoPageBreak(false);
            $fpdi->SetMargins(0, 0, 0);
            $total = $fpdi->setSourceFile($tempPdf);
            $ctx['institucional'] ??= DocumentoLayout::institucional();

            for ($n = 1; $n <= $total; $n++) {
                $tpl = $fpdi->importPage($n);
                $size = $fpdi->getTemplateSize($tpl);
                $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                $fpdi->useTemplate($tpl);
                DocumentoLayoutRenderer::renderizar($fpdi, $blocos, $n, $total, $size['width'], $size['height'], $ctx);
            }

            return $fpdi->Output('S');
        } finally {
            @unlink($tempPdf);
        }
    }

    /**
     * PDF de exemplo (página em branco com conteúdo fictício) para pré-visualizar um layout.
     */
    public static function gerarExemplo(array $layout, string $tipoNome, string $qrUrl = 'https://inotu.ao/verificar/EXEMPLO'): string
    {
        $paisagem = ($layout['orientacao'] ?? 'retrato') === 'paisagem';
        $base = new DocumentoPdf;
        $base->SetAutoPageBreak(false);
        $base->SetMargins(0, 0, 0);
        for ($i = 1; $i <= 2; $i++) {
            $base->AddPage($paisagem ? 'L' : 'P', 'A4');
            $base->SetDrawColor(228, 228, 231);
            $base->SetFillColor(244, 244, 245);
            $larg = $base->GetPageWidth();
            $alt = $base->GetPageHeight();
            for ($l = 0; $l < 22; $l++) {
                $base->Rect($larg * 0.08, $alt * 0.2 + $l * ($alt * 0.03), $larg * (0.84 - (($l * 7) % 5) * 0.08), 2.2, 'F');
            }
        }

        return static::aplicarBlocos($base->Output('S'), $layout['blocos'] ?? [], [
            'qr_url' => $qrUrl,
            'tipo_nome' => $tipoNome,
            'titulo' => 'Documento de exemplo',
        ]);
    }
}
