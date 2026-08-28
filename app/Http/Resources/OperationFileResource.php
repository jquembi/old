<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperationFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'nome'           => $this->nome,
            'nome_original'  => $this->nome_original,
            'tipo'           => $this->tipo,
            'extensao'       => $this->extensao,
            'tamanho'        => $this->tamanho,
            'tamanho_fmt'    => $this->tamanhoFormatado(),
            'mime_type'      => $this->mime_type,
            'previewable'    => $this->previewable(),
            'icone'          => $this->icone(),
            'download_url'   => $this->signedDownloadUrl(),

            'operacao_id'    => $this->operacao_id,
            'croqui_id'      => $this->croqui_id,
            'parecer_id'     => $this->parecer_id,

            'enviado_por'    => $this->whenLoaded('enviadoPor', fn () => [
                'id'   => $this->enviadoPor->id,
                'nome' => $this->enviadoPor->name,
            ]),

            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
