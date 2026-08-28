<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CroquiCoordenada extends Model
{
    protected $table = 'croqui_coordenadas';

    protected $fillable = [
        'croqui_id',
        'ordem',
        'latitude',
        'longitude',
        'utm_easting',
        'utm_northing',
        'utm_zone',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'utm_easting' => 'decimal:4',
            'utm_northing' => 'decimal:4',
            'ordem' => 'integer',
        ];
    }

    public function croqui()
    {
        return $this->belongsTo(Croqui::class);
    }
}
