<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Shared\Traits\HasMunicipioScope;
use App\Shared\Traits\HasSlug;
use App\Shared\Traits\HasAuditFields;
use App\Shared\Helpers\FileHelper;

class CartaMunicipal extends Model
{
    use HasFactory, SoftDeletes, HasMunicipioScope, HasSlug, HasAuditFields;

    protected $table = 'cartas_municipais';

    protected $fillable = [
        'municipio_id',
        'titulo',
        'slug',
        'tipo',
        'descricao',
        'escala',
        'data_emissao',
        'data_atualizacao',
        'versao',
        'projecao_cartografica',
        'file_path',
        'file_size',
        'formato',
        'autor',
        'status',
        'aprovado_por',
        'observacoes',
    ];

    protected $slugSource = 'titulo';

    protected function casts(): array
    {
        return [
            'data_emissao' => 'date',
            'data_atualizacao' => 'date',
            'file_size' => 'integer',
        ];
    }

    // ===== RELACIONAMENTOS =====

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    // ===== SCOPES =====

    public function scopeDoTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeVersao($query, string $versao)
    {
        return $query->where('versao', $versao);
    }

    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('data_atualizacao', '>=', now()->subDays($dias));
    }

    public function scopeAprovadas($query)
    {
        return $query->where('status', 'aprovada');
    }

    // ===== MÉTODOS HELPER =====

    public function getTipoLabel(): string
    {
        return match ($this->tipo) {
            'base' => 'Carta Base',
            'temática' => 'Carta Temática',
            'topografica' => 'Carta Topográfica',
            'cadastral' => 'Carta Cadastral',
            'solos' => 'Carta de Solos',
            default => ucfirst($this->tipo),
        };
    }

    public function getFormatoLabel(): string
    {
        return match ($this->formato) {
            'pdf' => 'PDF',
            'geotiff' => 'GeoTIFF',
            'shapefile' => 'Shapefile',
            'kml' => 'KML',
            default => strtoupper($this->formato),
        };
    }

    public function isApproved(): bool
    {
        return $this->status === 'aprovada';
    }

    public function approve(User $user): self
    {
        $this->update([
            'status' => 'aprovada',
            'aprovado_por' => $user->id,
        ]);
        return $this;
    }

    public function getFileSize(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }
        return FileHelper::formatBytes($this->file_size);
    }
}
