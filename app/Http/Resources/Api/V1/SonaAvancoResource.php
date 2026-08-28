<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SonaAvancoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo_avanco' => $this->tipo_avanco,
            'percentual_execucao' => $this->percentual_execucao,
            'data_registro' => $this->data_registro?->toDateString(),
            'observacoes' => $this->observacoes,
            'imagens' => $this->whenLoaded('imagens', fn() => $this->imagens->map(fn($img) => [
                'id' => $img->id,
                'descricao' => $img->descricao,
                'ordem' => $img->ordem,
                'url' => asset('storage/' . $img->image_path),
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
