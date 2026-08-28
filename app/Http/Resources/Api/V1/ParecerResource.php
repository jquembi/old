<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParecerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'numero_parecer' => $this->numero_parecer,
            'data_parecer' => $this->data_parecer?->toIso8601String(),
            'data_expedicao' => $this->data_expedicao?->toDateString(),
            'departamento' => $this->departamento,
            'parecer_favoravel' => $this->parecer_favoravel,
            'parecer_desfavoravel' => $this->parecer_desfavoravel,
            'parecer_condicional' => $this->parecer_condicional,
            'condicoes' => $this->condicoes,
            'recomendacoes' => $this->recomendacoes,
            'referencia_documento' => $this->referencia_documento,
            'status' => $this->status,
            'observacoes' => $this->observacoes,
            'file_url' => $this->when((bool) $this->file_path, fn() => asset('storage/' . $this->file_path)),
            'municipio' => new MunicipioResource($this->whenLoaded('municipio')),
            'municipio_id' => $this->municipio_id,
            'emitido_por' => $this->whenLoaded('emitter', fn() => $this->emitter ? [
                'id' => $this->emitter->id,
                'name' => $this->emitter->name,
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
