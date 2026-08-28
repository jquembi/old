<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'icone',
        'link',
        'lida',
    ];

    protected function casts(): array
    {
        return [
            'lida' => 'boolean',
        ];
    }

    // ─── Relacionamentos ─────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────

    public function scopeNaoLidas($query)
    {
        return $query->where('lida', false);
    }

    public function scopeRecentes($query, int $dias = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($dias));
    }

    // ─── Helpers ─────────────────────────────────────────

    public static function criar(int $userId, string $tipo, string $titulo, ?string $mensagem = null, ?string $link = null, ?string $icone = null): static
    {
        return static::create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link ?: static::inferirLink($titulo),
            'icone' => $icone,
        ]);
    }

    public static function paraUsuario(int $userId, string $tipo, string $titulo, ?string $mensagem = null, ?string $link = null): static
    {
        return static::criar($userId, $tipo, $titulo, $mensagem, $link);
    }

    public static function paraTodos(string $tipo, string $titulo, ?string $mensagem = null, ?string $link = null): void
    {
        $userIds = User::where('activo', true)->pluck('id');
        foreach ($userIds as $userId) {
            static::criar($userId, $tipo, $titulo, $mensagem, $link);
        }
    }

    public function destino(): ?string
    {
        return $this->link ?: static::inferirLink($this->titulo);
    }

    public static function inferirLink(string $titulo): ?string
    {
        $titulo = mb_strtolower($titulo);

        return match (true) {
            str_contains($titulo, 'croqui') => route('municipios.index', absolute: false),
            str_contains($titulo, 'terreno') => route('municipios.index', absolute: false),
            str_contains($titulo, 'loteamento') => route('municipios.index', absolute: false),
            str_contains($titulo, 'instrumento') => route('municipios.index', absolute: false),
            str_contains($titulo, 'carta') => route('municipios.index', absolute: false),
            str_contains($titulo, 'parecer') => route('municipios.index', absolute: false),
            str_contains($titulo, 'pagamento') => route('pagamentos.gerir', absolute: false),
            str_contains($titulo, 'erro') => route('erros-sistema.index', absolute: false),
            default => null,
        };
    }
}
