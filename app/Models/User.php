<?php

namespace App\Models;

use App\Concerns\TemPermissoes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, TemPermissoes;

    protected $fillable = [
        'name',
        'foto_perfil',
        'email',
        'password',
        'provincia_id',
        'municipio_id',
        'activo',
        'criado_por',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    // ─── Relacionamentos ─────────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function provincia(): BelongsTo
    {
        return $this->belongsTo(Provincia::class);
    }

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function criadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function usuariosCriados(): HasMany
    {
        return $this->hasMany(User::class, 'criado_por');
    }

    public function logsAcesso(): HasMany
    {
        return $this->hasMany(LogAcesso::class);
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(Auditoria::class);
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(Notificacao::class);
    }

    // ─── Helpers ─────────────────────────────────────────

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function nomeCompleto(): string
    {
        return $this->name;
    }

    public function roleName(): string
    {
        $nomes = $this->roles->pluck('descricao');

        return $nomes->isNotEmpty() ? $nomes->implode(', ') : 'Sem função';
    }
}
