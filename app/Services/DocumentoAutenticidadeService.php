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

    public static function buscarTokenPorCaminho(?string $caminho): ?string
    {
        if (! $caminho) {
            return null;
        }

        return null;
    }
}
