<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OperationFile extends Model
{
    protected $fillable = [
        'operacao_id', 'croqui_id', 'parecer_id',
        'nome', 'nome_original', 'tipo', 'extensao',
        'caminho', 'tamanho', 'mime_type', 'enviado_por',
    ];

    // ─── Tipos de ficheiro reconhecidos ──────────────────

    public const TIPO_QGIS    = 'qgis';
    public const TIPO_AUTOCAD = 'autocad';
    public const TIPO_PDF     = 'pdf';
    public const TIPO_IMAGEM  = 'imagem';
    public const TIPO_OUTRO   = 'outro';

    public const EXTENSION_MAP = [
        'qgz'  => self::TIPO_QGIS,
        'qgs'  => self::TIPO_QGIS,
        'dwg'  => self::TIPO_AUTOCAD,
        'dxf'  => self::TIPO_AUTOCAD,
        'pdf'  => self::TIPO_PDF,
        'jpg'  => self::TIPO_IMAGEM,
        'jpeg' => self::TIPO_IMAGEM,
        'png'  => self::TIPO_IMAGEM,
        'tif'  => self::TIPO_IMAGEM,
        'tiff' => self::TIPO_IMAGEM,
    ];

    // ─── Relacionamentos ─────────────────────────────────

    public function operacao(): BelongsTo
    {
        return $this->belongsTo(OperacaoUrbanistica::class, 'operacao_id');
    }

    public function croqui(): BelongsTo
    {
        return $this->belongsTo(Croqui::class);
    }

    public function parecer(): BelongsTo
    {
        return $this->belongsTo(Parecer::class);
    }

    public function enviadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enviado_por');
    }

    // ─── Helpers ─────────────────────────────────────────

    /**
     * Determinar tipo com base na extensão do ficheiro.
     */
    public static function detectarTipo(string $extensao): string
    {
        return self::EXTENSION_MAP[strtolower($extensao)] ?? self::TIPO_OUTRO;
    }

    /**
     * Tamanho formatado (KB, MB).
     */
    public function tamanhoFormatado(): string
    {
        if ($this->tamanho >= 1048576) {
            return round($this->tamanho / 1048576, 1) . ' MB';
        }

        return round($this->tamanho / 1024, 1) . ' KB';
    }

    /**
     * Gerar URL assinada (signed URL) para download seguro.
     * Preparado para S3/MinIO — funciona também com disco local.
     */
    public function signedDownloadUrl(int $minutes = 30): string
    {
        $disk = Storage::disk('local');

        if (method_exists($disk, 'temporaryUrl')) {
            return $disk->temporaryUrl($this->caminho, now()->addMinutes($minutes));
        }

        // Fallback para disco local — usar rota de download
        return route('api.operation-files.download', $this);
    }

    /**
     * Verificar se é um tipo que pode ser pré-visualizado no browser.
     */
    public function previewable(): bool
    {
        return in_array($this->tipo, [self::TIPO_PDF, self::TIPO_IMAGEM]);
    }

    /**
     * Ícone sugerido para o tipo de ficheiro.
     */
    public function icone(): string
    {
        return match ($this->tipo) {
            self::TIPO_QGIS    => 'globe-alt',
            self::TIPO_AUTOCAD => 'cube',
            self::TIPO_PDF     => 'document-text',
            self::TIPO_IMAGEM  => 'photo',
            default            => 'paper-clip',
        };
    }
}
