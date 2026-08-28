<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapFeature extends Model
{
    protected $fillable = [
        'layer_id',
        'croqui_id',
        'geometry_type',
        'geometry',
        'properties',
        'area_m2',
        'area_ha',
        'criado_por',
    ];

    protected $casts = [
        'geometry'   => 'array',
        'properties' => 'array',
        'area_m2'    => 'float',
        'area_ha'    => 'float',
    ];

    // ─── Relacionamentos ──────────────────────────────────

    public function layer(): BelongsTo
    {
        return $this->belongsTo(MapLayer::class, 'layer_id');
    }

    public function croqui(): BelongsTo
    {
        return $this->belongsTo(Croqui::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    // ─── Helpers ──────────────────────────────────────────

    /** Constrói um GeoJSON Feature completo a partir do model. */
    public function toGeoJsonFeature(): array
    {
        return [
            'type'       => 'Feature',
            'id'         => $this->id,
            'geometry'   => $this->geometry,
            'properties' => array_merge($this->properties ?? [], [
                'id'         => $this->id,
                'layer_id'   => $this->layer_id,
                'croqui_id'  => $this->croqui_id,
                'area_m2'    => $this->area_m2,
                'area_ha'    => $this->area_ha,
                'criado_por' => $this->criado_por,
                'criado_em'  => $this->created_at?->toIso8601String(),
                'updated_at' => $this->updated_at?->toIso8601String(),
            ]),
        ];
    }
}
