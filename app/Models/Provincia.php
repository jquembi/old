<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provincia extends Model
{
    protected $fillable = ['nome', 'codigo', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
