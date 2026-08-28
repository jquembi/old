<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapLayer extends Model
{
    protected $fillable = [
        'municipio_id',
        'nome',
        'tipo',
        'cor',
        'visivel',
        'ordem',
        'criado_por',
    ];

    protected $casts = [
        'visivel' => 'boolean',
        'ordem'   => 'integer',
    ];

    // ─── Relacionamentos ──────────────────────────────────

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(MapFeature::class, 'layer_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    // ─── Helpers ──────────────────────────────────────────

    /** Devolve (ou cria) a camada padrão de um tipo para um município. */
    public static function obterOuCriar(int $municipioId, string $tipo, int $userId): self
    {
        return self::firstOrCreate(
            ['municipio_id' => $municipioId, 'tipo' => $tipo],
            [
                'nome'       => self::nomeParaTipo($tipo),
                'cor'        => self::corParaTipo($tipo),
                'visivel'    => true,
                'ordem'      => 0,
                'criado_por' => $userId,
            ]
        );
    }

    /** Tipos de terreno disponíveis com metadados. */
    public static function tiposDisponiveis(): array
    {
        return [
            'terrenos_ociosos'    => ['nome' => 'Terrenos Ociosos',          'cor' => '#ef4444', 'icon' => 'triangle'],
            'loteamentos'         => ['nome' => 'Loteamentos',              'cor' => '#3b82f6', 'icon' => 'grid'],
            'croquis'             => ['nome' => 'Croquis',                  'cor' => '#10b981', 'icon' => 'map'],
            'areas_verdes'        => ['nome' => 'Áreas Verdes',             'cor' => '#22c55e', 'icon' => 'tree'],
            'areas_protegidas'    => ['nome' => 'Áreas Protegidas',         'cor' => '#16a34a', 'icon' => 'shield'],
            'zona_industrial'     => ['nome' => 'Zona Industrial',          'cor' => '#f59e0b', 'icon' => 'factory'],
            'zona_residencial'    => ['nome' => 'Zona Residencial',         'cor' => '#8b5cf6', 'icon' => 'home'],
            'zona_comercial'      => ['nome' => 'Zona Comercial',           'cor' => '#ec4899', 'icon' => 'shop'],
            'infraestrutura'      => ['nome' => 'Infraestrutura',           'cor' => '#06b6d4', 'icon' => 'road'],
            'zona_agricola'       => ['nome' => 'Zona Agrícola',            'cor' => '#84cc16', 'icon' => 'crop'],
            'zona_expansao'       => ['nome' => 'Zona de Expansão',         'cor' => '#f97316', 'icon' => 'expand'],
            'patrimonio_cultural' => ['nome' => 'Património Cultural',      'cor' => '#a855f7', 'icon' => 'landmark'],
            'hidrografia'         => ['nome' => 'Hidrografia',              'cor' => '#0ea5e9', 'icon' => 'water'],
            'vias'                => ['nome' => 'Vias e Acessos',           'cor' => '#737373', 'icon' => 'route'],
            'limites'             => ['nome' => 'Limites Administrativos',  'cor' => '#dc2626', 'icon' => 'border'],
        ];
    }

    public static function corParaTipo(string $tipo): string
    {
        return self::tiposDisponiveis()[$tipo]['cor'] ?? '#8b5cf6';
    }

    public static function nomeParaTipo(string $tipo): string
    {
        return self::tiposDisponiveis()[$tipo]['nome'] ?? ucfirst(str_replace('_', ' ', $tipo));
    }
}
