<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CroquiCoordenadaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ordem' => $this->ordem,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'descricao' => $this->descricao,
        ];
    }
}
