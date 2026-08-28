<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TerrenoOciosoCoordenada extends Model
{
    protected $table = 'terreno_ocioso_coordenadas';

    protected $fillable = [
        'terreno_ocioso_id',
        'ordem',
        'utm_easting',
        'utm_northing',
        'utm_zone',
        'latitude',
        'longitude',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'utm_easting' => 'decimal:4',
            'utm_northing' => 'decimal:4',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'ordem' => 'integer',
        ];
    }

    public function terrenoOcioso()
    {
        return $this->belongsTo(TerrenoOcioso::class);
    }
}
