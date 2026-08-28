<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SonaFaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'ordem' => $this->ordem,
            'percentual_conclusao' => $this->percentual_conclusao,
            'responsavel_id' => $this->responsavel_id,
            'data_inicio' => $this->data_inicio?->toDateString(),
            'data_fim_prevista' => $this->data_fim_prevista?->toDateString(),
            'status' => $this->status,
            'avancos' => SonaAvancoResource::collection($this->whenLoaded('avancos')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
