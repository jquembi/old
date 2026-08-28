<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerrenoOciosoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proprietario' => $this->proprietario,
            'slug' => $this->slug,
            'endereco' => $this->endereco,
            'lote' => $this->lote,
            'area_m2' => $this->area_m2,
            'area_construida_m2' => $this->area_construida_m2,
            'data_levantamento' => $this->data_levantamento?->toDateString(),
            'motivo_ociosidade' => $this->motivo_ociosidade,
            'tempo_ociosidade_anos' => $this->tempo_ociosidade_anos,
            'potencial_uso' => $this->potencial_uso,
            'status' => $this->status,
            'prioridade_intervencao' => $this->prioridade_intervencao,
            'contacto_proprietario' => $this->contacto_proprietario,
            'observacoes' => $this->observacoes,
            'pdf_url' => $this->when((bool) $this->pdf_path, fn() => asset('storage/' . $this->pdf_path)),
            'geometry' => $this->when((bool) $request->query('include_geometry'), $this->geometry),
            'municipio' => new MunicipioResource($this->whenLoaded('municipio')),
            'municipio_id' => $this->municipio_id,
            'created_by' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
