<?php

namespace App\Models;

use App\Models\Concerns\EnviaParaLixeira;
use App\Models\Concerns\ComAutenticidadeQr;
use App\Models\Concerns\HasSyncMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Croqui extends Model
{
    use ComAutenticidadeQr, EnviaParaLixeira, HasSyncMetadata;

    protected $fillable = [
        'operacao_id', 'tipo', 'titulo', 'slug', 'ano',
        'ficheiro_pdf_caminho', 'ficheiro_pdf_nome',
        'memoria_descritiva_caminho', 'memoria_descritiva_nome',
        'condicao', 'latitude', 'longitude',
        'utm_zone', 'utm_hemisphere', 'utm_easting', 'utm_northing',
        'codigo_territorial', 'centro_utm_easting', 'centro_utm_northing', 'raio_protecao_m',
        'coordenadas_texto', 'observacoes', 'criado_por',
        'rup_pago', 'rup_referencia', 'rup_pago_em',
        'download_liberado', 'liberado_por', 'liberado_em', 'liberacao_observacao',
        'publicado', 'publicado_por', 'publicado_em',
        'qr_token', 'verificado', 'visibilidade',
        // Campos do documento impresso
        'area', 'perimetro', 'dimensao', 'escala',
        'tecnico_responsavel', 'folha_numero', 'data_folha', 'finalidade',
        'micro_localizacao_caminho', 'micro_localizacao_nome',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'latitude'     => 'decimal:7',
            'longitude'    => 'decimal:7',
            'utm_easting'  => 'decimal:3',
            'utm_northing' => 'decimal:3',
            'centro_utm_easting' => 'decimal:3',
            'centro_utm_northing' => 'decimal:3',
            'raio_protecao_m' => 'decimal:3',
            'rup_pago' => 'boolean',
            'download_liberado' => 'boolean',
            'publicado' => 'boolean',
            'rup_pago_em' => 'datetime',
            'liberado_em' => 'datetime',
            'publicado_em' => 'datetime',
        ];
    }

    public function operacao(): BelongsTo
    {
        return $this->belongsTo(OperacaoUrbanistica::class, 'operacao_id');
    }

    public function proprietarios(): HasMany
    {
        return $this->hasMany(CroquiProprietario::class, 'croqui_id');
    }

    public function ficheiros(): HasMany
    {
        return $this->hasMany(OperationFile::class, 'croqui_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function liberadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liberado_por');
    }

    public function publicadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'publicado_por');
    }

    public function pagamentos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Pagamento::class, 'croqui_id');
    }

    public function pareceres(): MorphMany
    {
        return $this->morphMany(Parecer::class, 'recurso');
    }

    public function pagamentoActivo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Pagamento::class, 'croqui_id')->latestOfMany();
    }

    public function foiCriadoPorInotu(): bool
    {
        $criador = $this->relationLoaded('criadoPor')
            ? $this->criadoPor
            : $this->criadoPor()->with('roles')->first();

        return (bool) $criador?->isInotu();
    }

    /**
     * O croqui pode ter o RUP registado apenas após publicação.
     */
    public function podeRegistarRup(): bool
    {
        if ($this->foiCriadoPorInotu()) {
            return false;
        }

        return (bool) $this->publicado && (bool) $this->ficheiro_pdf_caminho;
    }

    /**
     * O croqui pode ser homologado (liberar download) apenas se RUP pago.
     */
    public function podeSerLiberado(): bool
    {
        if ($this->foiCriadoPorInotu()) {
            return true;
        }

        return (bool) $this->publicado && (bool) $this->rup_pago;
    }

    public function podeBaixar(?User $user = null): bool
    {
        if (! $this->ficheiro_pdf_caminho) {
            return false;
        }

        if ($this->foiCriadoPorInotu()) {
            return true;
        }

        // Administrador/homologador pode aceder para validação.
        if ($user && ($user->isInotu() || $user->temPermissao('croquis.aprovar'))) {
            return true;
        }

        return (bool) $this->rup_pago && (bool) $this->download_liberado;
    }

    /**
     * Acessor: compatibilidade com interface genérica de ficheiros.
     */
    public function getCaminhoAttribute(): ?string
    {
        return $this->ficheiro_pdf_caminho;
    }

    public function getNomeOriginalAttribute(): ?string
    {
        return $this->ficheiro_pdf_nome ?? $this->titulo;
    }

    /**
     * Nome do documento para o trait ComAutenticidadeQr.
     */
    protected function obterNomeDocumento(): ?string
    {
        return $this->ficheiro_pdf_nome ?? $this->titulo;
    }

    /**
     * Obtém localização formatada para exibição.
     */
    public function obterLocalizacao(): ?string
    {
        if ($this->utm_easting && $this->utm_northing) {
            $zona = $this->utm_zone ?: '33';
            $hem = $this->utm_hemisphere ?: 'S';
            return "UTM {$zona}{$hem} E {$this->utm_easting} N {$this->utm_northing}";
        }

        if ($this->latitude && $this->longitude) {
            return "Lat: {$this->latitude}, Long: {$this->longitude}";
        }

        return $this->coordenadas_texto;
    }

    /**
     * Obtém os nomes dos proprietários como texto.
     */
    public function obterNomesProprietarios(): ?string
    {
        $nomes = $this->proprietarios->pluck('nome')->filter()->values();
        return $nomes->isNotEmpty() ? $nomes->join(', ') : null;
    }

    public function temFicheiroPdfValido(): bool
    {
        return $this->ficheiroExisteNoDisco($this->ficheiro_pdf_caminho);
    }

    public function temMemoriaDescritivaValida(): bool
    {
        return $this->ficheiroExisteNoDisco($this->memoria_descritiva_caminho);
    }

    private function ficheiroExisteNoDisco(?string $caminho): bool
    {
        if (! $caminho || str_ends_with($caminho, '/') || str_contains($caminho, '..')) {
            return false;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($caminho)) {
            return false;
        }

        return is_file($disk->path($caminho));
    }

    /**
     * Converter para GeoJSON Feature (para Leaflet/API).
     */
    public function toGeoJsonFeature(): array
    {
        if (! $this->latitude || ! $this->longitude) {
            return [];
        }

        return [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Point',
                'coordinates' => [(float) $this->longitude, (float) $this->latitude],
            ],
            'properties' => [
                'id'        => $this->id,
                'tipo'      => $this->tipo,
                'condicao'  => $this->condicao,
                'utm_zone'  => $this->utm_zone,
                'utm_easting'  => $this->utm_easting,
                'utm_northing' => $this->utm_northing,
                'codigo_territorial' => $this->codigo_territorial,
                'raio_protecao_m' => $this->raio_protecao_m,
            ],
        ];
    }
}
