<?php

namespace App\Services;

use App\DTO\PermissionContext;
use App\Models\Permissao;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AuthorizationService
{
    private const CACHE_TTL = 3600;

    private const ADMIN_LIMITED_DENIED_PREFIXES = [
        'usuarios.',
        'permissoes.',
        'lixeira.',
    ];

    private const ADMIN_LIMITED_DENIED_ACTIONS = [
        '.criar',
        '.editar',
        '.editar_municipio',
        '.eliminar',
        '.eliminar_municipio',
        '.aprovar',
        '.gerir',
        '.restaurar',
        '.eliminar_definitivo',
    ];

    private function __construct(private User $user)
    {
        $this->user->loadMissing('roles.permissoes');
    }

    public static function for(User $user): self
    {
        return new self($user);
    }

    public function podeAcesso(string $permissao, ?PermissionContext $contexto = null): bool
    {
        if (! $this->user->activo) {
            return false;
        }

        if ($contexto && ! $contexto->podeAcessarLocalizacao($this->user)) {
            return false;
        }

        return $this->nomesPermissoes()->contains($permissao);
    }

    public function podeAcessoDinamico(
        string $acao,
        ?string $sufixo = null,
        ?PermissionContext $contexto = null,
    ): bool {
        $candidatas = collect([
            $sufixo ? "{$acao}_{$sufixo}" : null,
            $contexto?->ehCriador ? "{$acao}_proprio" : null,
            $contexto?->municipioId ? "{$acao}_municipio" : null,
            $acao,
        ])->filter()->unique();

        return $candidatas->contains(fn (string $permissao): bool => $this->podeAcesso($permissao, $contexto));
    }

    public function permissoesEfetivas(): Collection
    {
        $ids = $this->nomesPermissoes();

        return Permissao::query()
            ->whereIn('nome', $ids)
            ->orderBy('nome')
            ->get();
    }

    public function nomesPermissoes(): Collection
    {
        return Cache::remember($this->cacheKey(), self::CACHE_TTL, function (): Collection {
            $permissoes = $this->user->isDiretorInotu()
                ? Permissao::query()->pluck('nome')
                : $this->user->roles
                    ->flatMap(fn ($role) => $role->permissoes->pluck('nome'))
                    ->filter()
                    ->unique()
                    ->values();

            if ($this->user->isAdminSomenteInformacaoTecnica()) {
                $permissoes = $permissoes
                    ->reject(fn (string $permissao): bool => $this->bloqueadaParaAdminLimitado($permissao))
                    ->values();
            }

            return $permissoes->sort()->values();
        });
    }

    public function temAlgumaPermissao(array $permissoes): bool
    {
        return $this->nomesPermissoes()->intersect($permissoes)->isNotEmpty();
    }

    public function temTodasAsPermissoes(array $permissoes): bool
    {
        return collect($permissoes)->every(fn (string $permissao): bool => $this->podeAcesso($permissao));
    }

    public function invalidarCache(): void
    {
        Cache::forget($this->cacheKey());
    }

    public function estatisticasAcesso(?Carbon $desde = null): array
    {
        return [
            'desde' => $desde,
            'permissoes_efetivas' => $this->nomesPermissoes()->count(),
        ];
    }

    private function bloqueadaParaAdminLimitado(string $permissao): bool
    {
        foreach (self::ADMIN_LIMITED_DENIED_PREFIXES as $prefixo) {
            if (str_starts_with($permissao, $prefixo)) {
                return true;
            }
        }

        foreach (self::ADMIN_LIMITED_DENIED_ACTIONS as $acao) {
            if (str_ends_with($permissao, $acao)) {
                return true;
            }
        }

        return false;
    }

    private function cacheKey(): string
    {
        return "authz:user:{$this->user->id}:permissions";
    }
}
