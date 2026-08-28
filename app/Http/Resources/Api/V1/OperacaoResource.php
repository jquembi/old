<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'tipo_operacao' => $this->tipo_operacao,
            'area_m2' => $this->area_m2,
            'beneficiarios_estimados' => $this->beneficiarios_estimados,
            'orcamento_estimado' => $this->orcamento_estimado,
            'data_inicio' => $this->data_inicio?->toDateString(),
            'data_fim' => $this->data_fim?->toDateString(),
            'data_aprovacao' => $this->data_aprovacao?->toIso8601String(),
            'observacoes' => $this->observacoes,
            'municipio' => new MunicipioResource($this->whenLoaded('municipio')),
            'municipio_id' => $this->municipio_id,
            'created_by' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'approved_by' => $this->whenLoaded('approver', fn() => $this->approver ? [
                'id' => $this->approver->id,
                'name' => $this->approver->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
