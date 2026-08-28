<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionAudit extends Model
{
    protected $table = 'permission_audits';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'permissao',
        'permitido',
        'contexto',
        'motivo',
        'ip',
        'user_agent',
        'criado_em',
    ];

    protected $casts = [
        'permitido' => 'boolean',
        'contexto' => 'array',
        'criado_em' => 'datetime',
    ];

    // ─── Relacionamentos ─────────────────────────────────

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopePermitido($query)
    {
        return $query->where('permitido', true);
    }

    public function scopeNegado($query)
    {
        return $query->where('permitido', false);
    }

    public function scopeComPermissao($query, string $permissao)
    {
        return $query->where('permissao', $permissao);
    }

    public function scopeDeUtilizador($query, int $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeDesde($query, \Carbon\Carbon $data)
    {
        return $query->where('criado_em', '>=', $data);
    }

    public function scopeAte($query, \Carbon\Carbon $data)
    {
        return $query->where('criado_em', '<=', $data);
    }

    // ─── Métodos Auxiliares ─────────────────────────────

    /**
     * Obter motivo legível
     */
    public function motivo_legivel(): string
    {
        return $this->motivo ?? ($this->permitido ? 'Acesso permitido' : 'Acesso negado');
    }

    /**
     * Obter contexto decodificado
     */
    public function contexto_decodificado(): array
    {
        return is_array($this->contexto) ? $this->contexto : json_decode($this->contexto ?? '{}', true);
    }

    /**
     * Gerar relatório de tentativas falhadas por permissão
     */
    public static function relatoriNegacoes(?\Carbon\Carbon $desde = null, ?int $limite = 10)
    {
        $query = self::negado();

        if ($desde) {
            $query->desde($desde);
        }

        return $query
            ->selectRaw('permissao, COUNT(*) as total')
            ->groupBy('permissao')
            ->orderByRaw('total DESC')
            ->limit($limite)
            ->get();
    }

    /**
     * Gerar relatório de utilizadores com mais tentativas negadas
     */
    public static function relatorioUtilizadoresComNegacoes(?\Carbon\Carbon $desde = null, ?int $limite = 10)
    {
        $query = self::negado();

        if ($desde) {
            $query->desde($desde);
        }

        return $query
            ->selectRaw('usuario_id, COUNT(*) as total')
            ->groupBy('usuario_id')
            ->orderByRaw('total DESC')
            ->limit($limite)
            ->with('usuario:id,name,email')
            ->get();
    }
}
