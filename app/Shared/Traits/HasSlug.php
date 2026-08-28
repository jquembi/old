<?php

namespace App\Shared\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Trait para adicionar slug automático a um modelo.
 *
 * Gera um slug único baseado no campo 'name' ou em outro campo customizável.
 * O slug é gerado automaticamente no boot do modelo.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function (Model $model) {
            if (!$model->slug) {
                $slugSource = property_exists($model, 'slugSource') ? $model->slugSource : 'name';
                if (isset($model->{$slugSource})) {
                    $model->slug = static::generateUniqueSlug($model->{$slugSource});
                }
            }
        });

        static::updating(function (Model $model) {
            $slugSource = property_exists($model, 'slugSource') ? $model->slugSource : 'name';
            if ($model->isDirty($slugSource) && isset($model->{$slugSource})) {
                $model->slug = static::generateUniqueSlug($model->{$slugSource}, $model->id);
            }
        });
    }

    /**
     * Gera um slug único.
     *
     * @param string $text Texto para gerar o slug
     * @param int|null $excludeId ID do registro para exclusão na verificação de unicidade
     * @return string
     */
    protected static function generateUniqueSlug(string $text, ?int $excludeId = null): string
    {
        $slug = Str::slug($text);
        $original = $slug;
        $count = 1;

        $model = static::class;
        while ($model::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Busca por slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
