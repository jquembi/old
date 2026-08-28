<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SonaImagem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sona_imagens';

    protected $fillable = [
        'sona_avanco_id',
        'image_path',
        'descricao',
        'ordem',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
        ];
    }

    // ===== RELACIONAMENTOS =====

    public function avanco()
    {
        return $this->belongsTo(SonaAvanco::class, 'sona_avanco_id');
    }

    public function fase()
    {
        return $this->hasManyThrough(SonaFase::class, SonaAvanco::class, 'id', 'id', 'sona_avanco_id', 'sona_fase_id');
    }

    // ===== SCOPES =====

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('ordem');
    }

    // ===== MÉTODOS HELPER =====

    public function getImgUrl(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function getThumbnailUrl(): string
    {
        return asset('storage/thumbnails/' . basename($this->image_path));
    }

    public function getImageName(): string
    {
        return basename($this->image_path);
    }
}
