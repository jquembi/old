<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipioResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'nome'      => $this->nome,
            'slug'      => $this->slug,
            'codigo'    => $this->codigo,
            'activo'    => $this->activo,
            'latitude'  => $this->latitude,
            'longitude' => $this->longitude,
            'zoom_padrao' => $this->zoom_padrao,
            'bbox'      => $this->bbox,
            'provincia' => $this->whenLoaded('provincia', fn () => [
                'id'   => $this->provincia->id,
                'nome' => $this->provincia->nome,
            ]),
        ];
    }
}
