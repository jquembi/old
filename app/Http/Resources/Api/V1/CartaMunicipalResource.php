<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartaMunicipalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'escala' => $this->escala,
            'versao' => $this->versao,
            'formato' => $this->formato,
            'autor' => $this->autor,
            'projecao_cartografica' => $this->projecao_cartografica,
            'file_size' => $this->file_size,
            'status' => $this->status,
            'data_emissao' => $this->data_emissao?->toDateString(),
            'data_atualizacao' => $this->data_atualizacao?->toDateString(),
            'observacoes' => $this->observacoes,
            'file_url' => $this->when((bool) $this->file_path, fn() => asset('storage/' . $this->file_path)),
            'municipio' => new MunicipioResource($this->whenLoaded('municipio')),
            'municipio_id' => $this->municipio_id,
            'approved_by' => $this->whenLoaded('approver', fn() => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null),
            'created_by' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
