<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Municipio extends Model
{
    protected $fillable = ['nome', 'slug', 'codigo', 'provincia_id', 'activo', 'latitude', 'longitude', 'zoom_padrao', 'bbox'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Municipio $municipio): void {
            if (! $municipio->slug && $municipio->nome) {
                $municipio->slug = Str::slug($municipio->nome);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'activo'    => 'boolean',
            'latitude'  => 'float',
            'longitude' => 'float',
            'bbox'      => 'array',
        ];
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function instrumentos(): HasMany
    {
        return $this->hasMany(Instrumento::class);
    }

    public function loteamentos(): HasMany
    {
        return $this->hasMany(Loteamento::class);
    }

    public function operacoesUrbanisticas(): HasMany
    {
        return $this->hasMany(OperacaoUrbanistica::class);
    }

    public function pareceres(): HasMany
    {
        return $this->hasMany(Parecer::class);
    }

    public function projectosSona(): HasMany
    {
        return $this->hasMany(ProjectoSona::class);
    }

    public function outrosProjectos(): HasMany
    {
        return $this->hasMany(OutroProjecto::class);
    }
}
