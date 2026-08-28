<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeoJsonFeatureCollection extends JsonResource
{
    /**
     * Cria um GeoJSON FeatureCollection a partir de uma colecção de croquis.
     */
    public static function fromCroquis($croquis): array
    {
        $features = $croquis
            ->filter(fn ($c) => $c->latitude && $c->longitude)
            ->map(fn ($c) => $c->toGeoJsonFeature())
            ->filter()
            ->values()
            ->all();

        return [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public function toArray(Request $request): array
    {
        return [
            'type'     => 'FeatureCollection',
            'features' => $this->resource,
        ];
    }
}
