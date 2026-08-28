<?php

namespace Database\Seeders;

use App\Models\ArquivoBase;
use App\Models\Municipio;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ArquivosBaseTesteSeeder extends Seeder
{
    /**
     * Seeder de teste para criar uploads de arquivos base para todos os municípios.
     * Usa pdf-base.pdf como base para os uploads.
     */
    public function run(): void
    {
        // Obter usuário que vai fazer upload (admin INOTU)
        $adminInotu = User::where('email', 'admin@inotu.ao')->first()
            ?? User::first();

        if (!$adminInotu) {
            $this->command->error('Nenhum usuário encontrado no sistema. Execute UsuariosIniciaisSeeder primeiro.');
            return;
        }

        // Caminho do arquivo base
        $sourcePath = storage_path('app/pdf-base.pdf');

        // Se não encontrar o arquivo fornecido, criar um arquivo de teste
        if (!file_exists($sourcePath)) {
            $this->command->warn('Arquivo pdf-base.pdf não encontrado em storage/app/');
            $this->command->info('Criando arquivo PDF de teste...');
            $this->createTestPdf($sourcePath);
        }

        // Verificar se o arquivo foi criado/existe
        if (!file_exists($sourcePath)) {
            $this->command->error('Falha ao criar/encontrar arquivo PDF de teste.');
            return;
        }

        // Obter todos os municípios
        $municipios = Municipio::where('activo', true)->get();

        if ($municipios->isEmpty()) {
            $this->command->warn('Nenhum município encontrado no sistema.');
            return;
        }

        $this->command->info("Criando uploads de teste para {$municipios->count()} municípios...");

        // Tipos de arquivos base para teste
        $tipos = ['instrumentos', 'loteamentos', 'operacoes', 'pareceres', 'croquis'];

        $contador = 0;
        foreach ($municipios as $municipio) {
            // Para cada município, criar múltiplos tipos de arquivo
            foreach ($tipos as $tipo) {
                $nomeArquivo = "pdf-base-{$municipio->codigo}-{$tipo}.pdf";
                $caminhoDestino = "arquivos_base/{$municipio->codigo}/{$tipo}/{$nomeArquivo}";

                // Garantir que o diretório existe
                $pastaDestino = dirname(storage_path("app/{$caminhoDestino}"));
                if (!is_dir($pastaDestino)) {
                    mkdir($pastaDestino, 0755, true);
                }

                // Copiar arquivo
                try {
                    copy($sourcePath, storage_path("app/{$caminhoDestino}"));

                    // Criar ou atualizar registro
                    ArquivoBase::updateOrCreate(
                        ['municipio_id' => $municipio->id, 'tipo' => $tipo],
                        [
                            'titulo' => "Arquivos Base - {$tipo} ({$municipio->nome})",
                            'slug' => "{$municipio->slug}-{$tipo}",
                            'ficheiro_caminho' => $caminhoDestino,
                            'ficheiro_nome' => $nomeArquivo,
                            'enviado_por' => $adminInotu->id,
                            'verificado' => true,
                        ]
                    );

                    $contador++;
                    $this->command->line("✓ {$municipio->nome} - {$tipo}");
                } catch (\Exception $e) {
                    $this->command->error("✗ Erro ao processar {$municipio->nome} - {$tipo}: {$e->getMessage()}");
                }
            }
        }

        $this->command->info("✓ {$contador} arquivos de teste criados com sucesso!");
    }

    /**
     * Criar um arquivo PDF de teste simples
     */
    private function createTestPdf(string $filePath): void
    {
        // Criar diretório se não existir
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // PDF mínimo válido
        $pdfContent = "%PDF-1.4\n"
            . "1 0 obj\n"
            . "<< /Type /Catalog /Pages 2 0 R >>\n"
            . "endobj\n"
            . "2 0 obj\n"
            . "<< /Type /Pages /Kids [3 0 R] /Count 1 >>\n"
            . "endobj\n"
            . "3 0 obj\n"
            . "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\n"
            . "endobj\n"
            . "4 0 obj\n"
            . "<< /Length 44 >>\n"
            . "stream\n"
            . "BT\n"
            . "/F1 12 Tf\n"
            . "100 700 Td\n"
            . "(Documento de Teste - Bacias de Angola) Tj\n"
            . "ET\n"
            . "endstream\n"
            . "endobj\n"
            . "5 0 obj\n"
            . "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\n"
            . "endobj\n"
            . "xref\n"
            . "0 6\n"
            . "0000000000 65535 f \n"
            . "0000000009 00000 n \n"
            . "0000000058 00000 n \n"
            . "0000000115 00000 n \n"
            . "0000000244 00000 n \n"
            . "0000000338 00000 n \n"
            . "trailer\n"
            . "<< /Size 6 /Root 1 0 R >>\n"
            . "startxref\n"
            . "417\n"
            . "%%EOF\n";

        file_put_contents($filePath, $pdfContent);
        $this->command->info("Arquivo PDF de teste criado em: {$filePath}");
    }
}
