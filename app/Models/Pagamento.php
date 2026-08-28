<?php

namespace App\Models;

use App\Enums\EstadoPagamento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    protected $table = 'pagamentos';

    protected $fillable = [
        'croqui_id',
        'municipio_id',
        'valor',
        'rupe_referencia',
        'rupe_entidade',
        'rupe_data_limite',
        'comprovativo_path',
        'comprovativo_nome',
        'comprovativo_mime',
        'estado',
        'observacoes',
        'motivo_rejeicao',
        'criado_por',
        'rupe_gerado_por',
        'rupe_gerado_em',
        'comprovativo_por',
        'comprovativo_em',
        'confirmado_por',
        'confirmado_em',
    ];

    protected function casts(): array
    {
        return [
            'estado'           => EstadoPagamento::class,
            'rupe_data_limite' => 'date',
            'rupe_gerado_em'   => 'datetime',
            'comprovativo_em'  => 'datetime',
            'confirmado_em'    => 'datetime',
            'valor'            => 'decimal:2',
        ];
    }

    // ─── Relações ──────────────────────────────────────────

    public function croqui(): BelongsTo
    {
        return $this->belongsTo(Croqui::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function rupeGeradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rupe_gerado_por');
    }

    public function comprovativoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'comprovativo_por');
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }

    // ─── Verificações de estado ─────────────────────────────

    public function aguardandoPagamento(): bool
    {
        return $this->estado === EstadoPagamento::AguardandoPagamento;
    }

    public function rupeGerado(): bool
    {
        return $this->estado === EstadoPagamento::RupeGerado;
    }

    public function comproativoSubmetido(): bool
    {
        return $this->estado === EstadoPagamento::ComproativoSubmetido;
    }

    public function isPago(): bool
    {
        return in_array($this->estado, [EstadoPagamento::Pago, EstadoPagamento::Concluido], true);
    }

    public function isRejeitado(): bool
    {
        return $this->estado === EstadoPagamento::PagamentoRejeitado;
    }

    // ─── Verificações de permissão de fluxo ───────────────

    public function podeGerarRupe(): bool
    {
        return $this->estado === EstadoPagamento::AguardandoPagamento;
    }

    public function podeSubmeterComprovativo(): bool
    {
        return in_array($this->estado, [
            EstadoPagamento::RupeGerado,
            EstadoPagamento::PagamentoRejeitado,
        ], true);
    }

    public function podeConfirmarRejeitar(): bool
    {
        return $this->estado === EstadoPagamento::ComproativoSubmetido;
    }
}
