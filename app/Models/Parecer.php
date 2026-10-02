<?php

namespace App\Models;

use App\Models\Concerns\ComAutenticidadeQr;
use App\Models\Concerns\EnviaParaLixeira;
use App\Models\Concerns\HasSyncMetadata;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Parecer extends Model
{
    use ComAutenticidadeQr, EnviaParaLixeira, HasSyncMetadata;

    protected $table = 'pareceres';

    protected $fillable = [
        'municipio_id', 'recurso_type', 'recurso_id',
        'titulo', 'slug', 'descricao', 'projecto_nome',
        'servico', 'entidade_requerente', 'tipo_concessao',
        'classificacao_area', 'uso_solo', 'zona_ordenamento',
        'area_reservada', 'fundamentacao', 'conclusao',
        'estado', 'ficheiro_caminho', 'ficheiro_nome', 'criado_por', 'visibilidade',
        'qr_token', 'verificado',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'area_reservada' => 'boolean',
            'verificado' => 'boolean',
        ];
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function recurso(): MorphTo
    {
        return $this->morphTo();
    }

    public function ficheiros(): HasMany
    {
        return $this->hasMany(OperationFile::class, 'parecer_id');
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function servicoLabel(): string
    {
        return match ($this->servico) {
            'croqui' => 'Croqui de Localização',
            'terreno_ocioso' => 'Terreno Ocioso',
            'loteamento' => 'Loteamento',
            'instrumento' => 'Instrumento de Ordenamento',
            'projecto_sona' => 'Projecto SONA',
            'outro_projecto' => 'Outro Projecto',
            'operacao_urbanistica' => 'Operação Urbanística',
            'outros_pareceres' => 'Outros Pareceres',
            default => $this->servico ? str($this->servico)->replace('_', ' ')->title()->toString() : 'Serviço territorial',
        };
    }

    public function areaReservadaLabel(): string
    {
        return match ($this->area_reservada) {
            true => 'Sim, incide em área reservada',
            false => 'Não incide em área reservada',
            default => 'Não informado',
        };
    }

    public function getCaminhoAttribute(): ?string
    {
        return $this->ficheiro_caminho;
    }

    public function getNomeOriginalAttribute(): ?string
    {
        return $this->ficheiro_nome ?? $this->titulo;
    }

    protected function obterNomeDocumento(): ?string
    {
        return $this->ficheiro_nome ?? $this->titulo;
    }
}
