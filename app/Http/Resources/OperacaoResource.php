<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'titulo'      => $this->titulo,
            'descricao'   => $this->descricao,
            'municipio'   => $this->whenLoaded('municipio', fn () => [
                'id'   => $this->municipio->id,
                'nome' => $this->municipio->nome,
                'slug' => $this->municipio->slug,
            ]),
            'ficheiro_base' => $this->ficheiro_base_caminho
                ? route('storage.serve', $this->ficheiro_base_caminho)
                : null,
            'total_croquis'  => $this->whenCounted('croquis'),
            'total_ficheiros' => $this->whenCounted('ficheiros'),
            'criado_por' => $this->whenLoaded('criadoPor', fn () => [
                'id'   => $this->criadoPor->id,
                'nome' => $this->criadoPor->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
