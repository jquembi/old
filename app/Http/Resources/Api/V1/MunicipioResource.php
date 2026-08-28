<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'sede' => $this->sede,
            'descricao' => $this->descricao,
            'populacao_est' => $this->populacao_est,
            'area_km2' => $this->area_km2,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'website' => $this->website,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'endereco_sede' => $this->endereco_sede,
            'status' => $this->status,
            'data_criacao' => $this->data_criacao?->toDateString(),
            'boundary' => $this->when((bool) $request->query('include_boundary'), $this->boundary),
            'geometry' => $this->when((bool) $request->query('include_geometry'), $this->geometry),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
