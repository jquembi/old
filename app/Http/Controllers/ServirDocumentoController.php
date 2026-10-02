<?php

namespace App\Http\Controllers;

use App\Models\Croqui;
use App\Models\LogAcesso;
use App\Models\Parecer;
use App\Services\DocumentoAutenticidadeService;
use App\Services\PdfQrStampService;
use App\Services\WordToPdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

/**
 * Controller responsável por servir ficheiros PDF e outros documentos
 * de forma segura, com controlo de permissões, auditoria e headers HTTP correctos.
 *
 * Substitui a rota closure `ficheiros/{path}` com lógica centralizada.
 */
class ServirDocumentoController extends Controller
{
    /**
     * Visualiza um ficheiro (inline) — requer permissão conforme o tipo do documento.
     * Regista auditoria de visualização.
     */
    public function visualizar(Request $request, string $path): Response
    {
        $user = $request->user();

        // Rate limiting: 60 visualizações por minuto por utilizador
        $chave = 'visualizar-doc:' . $user->id;
        if (RateLimiter::tooManyAttempts($chave, 60)) {
            abort(429, 'Demasiadas tentativas. Aguarde um momento.');
        }
        RateLimiter::hit($chave, 60);

        [$caminho, $conteudo, $mime, $nome] = $this->resolverFicheiro($path);

        // Verificar permissão específica do documento
        $this->verificarPermissaoDocumento($user, $caminho, 'ver');

        [$conteudo, $mime, $nome] = $this->converterWordParaPdfSeNecessario($conteudo, $mime, $nome, $caminho);

        // Stampar QR code para PDFs e imagens
        $conteudo = $this->aplicarQrStamp($conteudo, $mime, $caminho);

        // Registar auditoria de visualização
        $this->registarAcesso($user, $caminho, $nome, 'visualizar_pdf', $request->ip(), $request->userAgent());

        return response($conteudo, 200, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'inline; filename="' . $nome . '"',
            'Content-Length'            => strlen($conteudo),
            'Cache-Control'             => 'private, no-store, no-cache',
            'X-Content-Type-Options'    => 'nosniff',
            'X-Frame-Options'           => 'SAMEORIGIN',
            'Referrer-Policy'           => 'no-referrer',
        ]);
    }

    /**
     * Faz download de um ficheiro (attachment) — requer permissão de download.
     * Regista auditoria de download.
     */
    public function download(Request $request, string $path): Response
    {
        $user = $request->user();

        // Rate limiting: 20 downloads por minuto por utilizador
        $chave = 'download-doc:' . $user->id;
        if (RateLimiter::tooManyAttempts($chave, 20)) {
            abort(429, 'Demasiadas tentativas de download. Aguarde um momento.');
        }
        RateLimiter::hit($chave, 60);

        [$caminho, $conteudo, $mime, $nome] = $this->resolverFicheiro($path);

        // Verificar permissão de download
        $this->verificarPermissaoDocumento($user, $caminho, 'download');

        [$conteudo, $mime, $nome] = $this->converterWordParaPdfSeNecessario($conteudo, $mime, $nome, $caminho);

        // Stampar QR code
        $conteudo = $this->aplicarQrStamp($conteudo, $mime, $caminho);

        // Registar auditoria de download
        $this->registarAcesso($user, $caminho, $nome, 'download_pdf', $request->ip(), $request->userAgent());

        return response($conteudo, 200, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => 'attachment; filename="' . $nome . '"',
            'Content-Length'            => strlen($conteudo),
            'Cache-Control'             => 'private, no-store, no-cache',
            'X-Content-Type-Options'    => 'nosniff',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Métodos privados
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolve o ficheiro no disco, valida existência e retorna conteúdo.
     * @return array{string, string, string, string} [caminho, conteúdo, mime, nome]
     */
    private function resolverFicheiro(string $path): array
    {
        // Sanitizar path: impedir path traversal
        $path = ltrim($path, '/');
        if ($path === '' || str_ends_with($path, '/') || str_contains($path, '..')) {
            abort(400, 'Caminho inválido.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404, 'Documento não encontrado.');
        }

        $fullPath = $disk->path($path);
        if (! is_file($fullPath) || ! is_readable($fullPath)) {
            abort(404, 'Documento nÃ£o encontrado.');
        }

        $mime     = mime_content_type($fullPath) ?: 'application/octet-stream';
        $nome     = basename($fullPath);
        $conteudo = file_get_contents($fullPath);

        if ($conteudo === false) {
            abort(404, 'Documento nÃ£o encontrado.');
        }

        return [$path, $conteudo, $mime, $nome];
    }

    /**
     * Verifica se o utilizador tem permissão para aceder ao documento.
     * Considera o tipo do documento (croqui, parecer, instrumento, etc.) e
     * regras específicas (homologação de croquis).
     */
    private function verificarPermissaoDocumento($user, string $caminho, string $tipo): void
    {
        // 1. Verificar permissão genérica: documentos.ver ou documentos.download
        $permissao = $tipo === 'download' ? 'documentos.download' : 'documentos.ver';

        if (! $user->temPermissao($permissao)) {
            abort(403, 'Não tem permissão para ' . ($tipo === 'download' ? 'baixar' : 'visualizar') . ' este documento.');
        }

        // 2. Regras específicas por tipo de documento

        // Croqui: verificar homologação antes do download
        $croqui = Croqui::where('ficheiro_pdf_caminho', $caminho)
            ->orWhere('memoria_descritiva_caminho', $caminho)
            ->first();
        if ($croqui) {
            if (! $croqui->podeBaixar($user)) {
                abort(403, 'Croqui pendente de homologação. Acesso indisponível até aprovação pelo administrador.');
            }
            // Verificar âmbito municipal/provincial
            $municipioId = $croqui->operacao?->municipio_id;
            if ($municipioId) {
                if ($user->isMunicipal() && $user->municipio_id !== $municipioId) {
                    abort(403, 'Acesso restrito ao município responsável.');
                }
                if ($user->isProvincial()) {
                    $municipio = \App\Models\Municipio::find($municipioId);
                    if ($municipio && $user->provincia_id !== $municipio->provincia_id) {
                        abort(403, 'Acesso restrito à sua província.');
                    }
                }
            }
        }
    }

    /**
     * Aplica stamp QR code em PDFs e imagens que possuam token de autenticidade.
     */
    private function aplicarQrStamp(string $conteudo, string $mime, string $caminho): string
    {
        $suportaQr = str_contains($mime, 'pdf')
                  || str_contains($mime, 'image/jpeg')
                  || str_contains($mime, 'image/jpg')
                  || str_contains($mime, 'image/png')
                  || str_contains($mime, 'image/webp');

        if ($suportaQr) {
            $token = DocumentoAutenticidadeService::buscarTokenPorCaminho($caminho);
            if ($token) {
                $croquiMemoria = Croqui::where('memoria_descritiva_caminho', $caminho)->first();
                // O QR continua a permitir validar o documento, mas o código
                // territorial (por exemplo, CT-...) não é exibido no ficheiro.
                $textoAbaixoQr = null;
                $qrUrl = DocumentoAutenticidadeService::gerarUrlQrSimples(
                    $token,
                    $croquiMemoria ? 'memoria_descritiva' : null
                );
                $layout = match (true) {
                    (bool) $croquiMemoria => 'memoria_descritiva',
                    Croqui::where('ficheiro_pdf_caminho', $caminho)->exists() => 'croqui',
                    Parecer::where('ficheiro_caminho', $caminho)->exists() => 'parecer',
                    default => 'default',
                };
                $conteudo = PdfQrStampService::processarDownload($conteudo, $mime, $qrUrl, $layout, $textoAbaixoQr);
            }
        }

        return $conteudo;
    }

    /**
     * Regista a acção no log de acessos.
     */
    /**
     * Documentos Word ficam guardados no formato original, mas saem como PDF.
     */
    private function converterWordParaPdfSeNecessario(string $conteudo, string $mime, string $nome, string $caminho): array
    {
        $converter = app(WordToPdfService::class);

        if (! $converter->pareceWord($mime, $nome)) {
            return [$conteudo, $mime, $nome];
        }

        $pdfPath = $converter->converterConteudo($conteudo, $nome, permitirFallbackTexto: false);

        if (! $pdfPath || ! is_file($pdfPath)) {
            if (DocumentoAutenticidadeService::buscarTokenPorCaminho($caminho)) {
                abort(500, 'Não foi possível gerar a versão PDF com o layout original para aplicar o QR de validação.');
            }

            return [$conteudo, $mime, $nome];
        }

        try {
            $pdfConteudo = file_get_contents($pdfPath);

            return [
                $pdfConteudo === false ? $conteudo : $pdfConteudo,
                'application/pdf',
                $converter->nomePdf($nome),
            ];
        } finally {
            @unlink($pdfPath);
        }
    }

    private function registarAcesso($user, string $caminho, string $nome, string $accao, string $ip, ?string $userAgent): void
    {
        LogAcesso::create([
            'user_id'    => $user->id,
            'accao'      => $accao,
            'recurso'    => 'documento',
            'recurso_id' => null,
            'detalhes'   => json_encode(['ficheiro' => $nome, 'caminho' => $caminho]),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
        ]);
    }
}
