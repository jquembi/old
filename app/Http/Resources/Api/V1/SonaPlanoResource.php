<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SonaPlanoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'objetivo' => $this->objetivo,
            'area_intervencao_m2' => $this->area_intervencao_m2,
            'populacao_alvo' => $this->populacao_alvo,
            'data_inicio' => $this->data_inicio?->toDateString(),
            'data_fim_prevista' => $this->data_fim_prevista?->toDateString(),
            'data_fim_real' => $this->data_fim_real?->toDateString(),
            'orcamento_total' => $this->orcamento_total,
            'orcamento_gasto' => $this->orcamento_gasto,
            'percentual_conclusao' => $this->percentual_conclusao,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            'municipio' => new MunicipioResource($this->whenLoaded('municipio')),
            'municipio_id' => $this->municipio_id,
            'fases' => SonaFaseResource::collection($this->whenLoaded('fases')),
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
