<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstrumentoOrdenamentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'numero_documento' => $this->numero_documento,
            'versao' => $this->versao,
            'status' => $this->status,
            'data_criacao' => $this->data_criacao?->toDateString(),
            'data_aprovacao' => $this->data_aprovacao?->toDateString(),
            'data_publicacao' => $this->data_publicacao?->toDateString(),
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
