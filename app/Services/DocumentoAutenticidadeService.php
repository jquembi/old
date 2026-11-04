<?php

namespace App\Services;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class DocumentoAutenticidadeService
{
    public static function gerarToken(): string
    {
        return Str::random(40);
    }

    public static function gerarUrlQrSimples(string $token, ?string $documento = null): string
    {
        $url = URL::to("/documentos/verificar/{$token}");

        return $documento ? "{$url}?documento={$documento}" : $url;
    }

    public static function gerarDadosQr(
        string $token,
        string $tipoDocumento,
        int|string|null $documentoId = null,
        ?string $nomeDocumento = null,
    ): array {
        return [
            'token' => $token,
            'tipo_documento' => $tipoDocumento,
            'documento_id' => $documentoId,
            'nome_documento' => $nomeDocumento,
            'url' => self::gerarUrlQrSimples($token, $tipoDocumento),
        ];
    }

    /**
     * Localiza o registo que referencia o ficheiro e devolve o seu token de
     * autenticidade, gerando-o quando o registo ainda não tem um.
     */
    public static function buscarTokenPorCaminho(?string $caminho): ?string
    {
        if (! $caminho) {
            return null;
        }

        $colunas = [
            \App\Models\InstrumentoFicheiro::class => ['caminho'],
            \App\Models\LoteamentoFicheiro::class => ['caminho'],
            \App\Models\ProjectoSonaFicheiro::class => ['caminho'],
            \App\Models\Parecer::class => ['ficheiro_caminho'],
            \App\Models\Croqui::class => ['ficheiro_pdf_caminho', 'memoria_descritiva_caminho'],
            \App\Models\ArquivoBase::class => ['ficheiro_caminho'],
        ];

        foreach ($colunas as $modelo => $campos) {
            try {
                $registo = $modelo::query()
                    ->where(function ($q) use ($campos, $caminho) {
                        foreach ($campos as $campo) {
                            $q->orWhere($campo, $caminho);
                        }
                    })
                    ->first();
            } catch (\Throwable) {
                continue;
            }

            if ($registo) {
                return $registo->qr_token ?: $registo->gerarQrToken();
            }
        }

        return null;
    }
}
