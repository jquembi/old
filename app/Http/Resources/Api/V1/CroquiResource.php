<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CroquiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'proprietario' => $this->proprietario,
            'slug' => $this->slug,
            'descricao' => $this->descricao,
            'endereco' => $this->endereco,
            'lote' => $this->lote,
            'area_m2' => $this->area_m2,
            'condicao' => $this->condicao,
            'condicao_label' => $this->getCondicaoLabel(),
            'data_levantamento' => $this->data_levantamento?->toDateString(),
            'escala' => $this->escala,
            'sistema_referencia' => $this->sistema_referencia,
            'status' => $this->status,
            'numero_registro' => $this->numero_registro,
            'tipo_geometria' => $this->tipo_geometria,
            'centroid_lat' => $this->centroid_lat,
            'centroid_lng' => $this->centroid_lng,
            'perimetro_m' => $this->perimetro_m,
            'num_vertices' => $this->num_vertices,
            'has_sobreposicao' => $this->has_sobreposicao,
            'pdf_url' => $this->when((bool) $this->pdf_path, fn() => asset('storage/' . $this->pdf_path)),
            'geometry' => $this->when((bool) $request->query('include_geometry'), $this->geometry),
            'municipio' => new MunicipioResource($this->whenLoaded('municipio')),
            'municipio_id' => $this->municipio_id,
            'coordenadas' => CroquiCoordenadaResource::collection($this->whenLoaded('coordenadas')),
            'created_by' => $this->whenLoaded('creator', fn() => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
