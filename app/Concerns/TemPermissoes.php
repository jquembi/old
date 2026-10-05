<?php

namespace App\Concerns;

use App\DTO\PermissionContext;
use App\Models\Role;
use App\Services\AuthorizationService;
use Illuminate\Support\Collection;

/**
 * Trait TemPermissoes V2
 * Versão melhorada com suporte a contexto, cache e auditoria
 */
trait TemPermissoes
{
    /**
     * Instância de cache do serviço de autorização
     */
    protected ?AuthorizationService $authService = null;

    // ─── Interface Principal ─────────────────────────────

    /**
     * Verificar se tem permissão para uma ação (compatível com v1)
     */
    public function temPermissao(string $permissao): bool
    {
        if (! $this->activo || ($this->isAdminSomenteInformacaoTecnica() && $this->bloqueadaParaAdminLimitado($permissao))) {
            return false;
        }

        if ($this->isSuperAdminNacional()) {
            return true;
        }

        if ($this->ruleManagerTemPermissao($permissao)) {
            return true;
        }

        // Alterações de papel/permissão podem ocorrer no mesmo pedido. Só em
        // caso de negação recarregamos as relações para evitar falso 403.
        $this->unsetRelation('roles');
        $this->unsetRelation('permissoesDiretas');

        return $this->ruleManagerTemPermissao($permissao);
    }

    public function hasPermission(string $permission): bool
    {
        return collect($this->legacyPermissionCandidates($permission))
            ->contains(fn (string $candidate): bool => $this->temPermissao($candidate));
    }

    /**
     * Nova interface: verificar com contexto
     */
    public function podeAcesso(string $permissao, ?PermissionContext $contexto = null): bool
    {
        return $this->activo
            && (! $contexto || $contexto->podeAcessarLocalizacao($this))
            && $this->temPermissao($permissao);
    }

    /**
     * Verificar se tem alguma das permissões
     */
    public function temAlgumaPermissao(array $permissoes): bool
    {
        return collect($permissoes)->contains(fn (string $permissao): bool => $this->temPermissao($permissao));
    }

    /**
     * Verificar se tem todas as permissões
     */
    public function temTodasAsPermissoes(array $permissoes): bool
    {
        return collect($permissoes)->every(fn (string $permissao): bool => $this->temPermissao($permissao));
    }

    // ─── Permissões Dinâmicas ───────────────────────────

    /**
     * Verificar permissão com variações automáticas
     * Ex: podeFazer('editar', 'rascunho') verifica:
     * - editar_rascunho
     * - editar_municipio
     * - editar_proprio
     * - editar
     */
    public function podeFazer(string $acao, ?string $contexto = null, ?PermissionContext $context = null): bool
    {
        $candidatas = collect([
            $contexto ? "{$acao}_{$contexto}" : null,
            $context?->ehCriador ? "{$acao}_proprio" : null,
            $context?->municipioId ? "{$acao}_municipio" : null,
            $acao,
        ])->filter()->unique();

        return $candidatas->contains(fn (string $permissao): bool => $this->podeAcesso($permissao, $context));
    }

    // ─── Permissões Específicas (v1 compatível) ──────────

    /**
     * Retorna o nível hierárquico mais alto (menor número)
     */
    public function nivelMinimo(): ?int
    {
        if ($this->roles->isEmpty()) {
            return null;
        }

        return $this->roles->min('nivel');
    }

    public function isInotu(): bool
    {
        return $this->isSuperAdminNacional()
            || $this->isDiretorInotu()
            || ($this->roles->isNotEmpty() && $this->nivelMinimo() === Role::NIVEL_INOTU);
    }

    public function isProvincial(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nivel === Role::NIVEL_PROVINCIAL);
    }

    public function isAdminMunicipal(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nivel === Role::NIVEL_MUNICIPAL);
    }

    public function isTecnicoMunicipal(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nivel === Role::NIVEL_TECNICO);
    }

    public function isSecretaria(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::SECRETARIA_GABINETE);
    }

    public function isDiretorInotu(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::DIRECTOR_INOTU);
    }

    public function temRole(string $role): bool
    {
        return $this->roles->contains(fn (Role $r) => $r->nome === $role);
    }

    public function hasRole(string $role): bool
    {
        return match ($role) {
            'inotu' => $this->isInotu(),
            'governo_provincial' => $this->isProvincial(),
            'tecnico_municipal' => $this->isTecnicoMunicipal(),
            default => $this->temRole($role),
        };
    }

    public function temAlgumaRole(array $roles): bool
    {
        return $this->roles->contains(fn (Role $role) => in_array($role->nome, $roles, true));
    }

    public function hasAnyRole(array $roles): bool
    {
        return collect($roles)->contains(fn (string $role): bool => $this->hasRole($role));
    }

    public function isAdminSomenteInformacaoTecnica(): bool
    {
        if ($this->isDiretorInotu()) {
            return false;
        }

        return $this->temAlgumaRole([
            Role::CHEFE_SECCAO_ADMINISTRATIVA,
            Role::ADMIN_MUNICIPAL,
            Role::ADMIN_MUNICIPAL_ADJUNTO,
        ]) && ! $this->temAlgumaRole([
            Role::DIRECTOR_INOTU,
            Role::CHEFE_SECCAO_TECNICA,
            Role::SECRETARIA_GABINETE,
        ]);
    }

    public function podeEnviarParaLixeira(): bool
    {
        return $this->temAlgumaRole([
            Role::SECRETARIA_GABINETE,
            Role::DIRECTOR_INOTU,
            Role::CHEFE_SECCAO_TECNICA,
        ]);
    }

    public function podeAcederLixeira(): bool
    {
        return $this->isDiretorInotu();
    }

    public function isMunicipal(): bool
    {
        return $this->isAdminMunicipal() || $this->isTecnicoMunicipal();
    }

    // ─── Métodos Nacionais (Multi-tenancy) ─────────────────

    public function isSuperAdminNacional(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::SUPER_ADMIN_NACIONAL);
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdminNacional();
    }

    /**
     * Retorna o modo de perspetiva atual do utilizador.
     */
    public function perspetivaModo(): string
    {
        if ($this->isSuperAdminNacional()) {
            return app(\App\Services\PerspetivaService::class)->getModo();
        }

        if ($this->isNacional()) {
            return 'nacional';
        }

        if ($this->isProvincial()) {
            return 'provincial';
        }

        return 'municipal';
    }

    /**
     * Retorna a província ativa conforme a perspetiva.
     */
    public function perspetivaProvincia(): ?\App\Models\Provincia
    {
        if ($this->isSuperAdminNacional()) {
            return app(\App\Services\PerspetivaService::class)->getProvincia();
        }

        return $this->provincia;
    }

    /**
     * Retorna o ID da província ativa conforme a perspetiva.
     */
    public function perspetivaProvinciaId(): ?int
    {
        if ($this->isSuperAdminNacional()) {
            return app(\App\Services\PerspetivaService::class)->getProvinciaId();
        }

        return $this->provincia_id;
    }

    public function isAdminNacional(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::ADMIN_NACIONAL);
    }

    public function isGestorNacional(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::GESTOR_NACIONAL);
    }

    public function isAuditorNacional(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::AUDITOR_NACIONAL);
    }

    public function isNacional(): bool
    {
        return $this->isSuperAdminNacional()
            || $this->isAdminNacional()
            || $this->isGestorNacional()
            || $this->isAuditorNacional()
            || ($this->roles->isNotEmpty() && $this->nivelMinimo() <= Role::NIVEL_ADMIN_NACIONAL);
    }

    // ─── Escopo de Permissões ─────────────────────────────

    public function temPermissaoComEscopo(string $permissao, ?string $escopo = null): bool
    {
        // Super Admin Nacional tem tudo
        if ($this->isSuperAdminNacional()) {
            return true;
        }

        // Verificar permissão no role
        $hasPermission = $this->temPermissao($permissao);

        if (!$hasPermission) {
            return false;
        }

        // Verificar escopo se fornecido
        if ($escopo) {
            return $this->verificarEscopo($escopo);
        }

        return true;
    }

    private function verificarEscopo(string $escopo): bool
    {
        return match ($escopo) {
            'nacional' => $this->isNacional(),
            'provincial' => $this->isProvincial() || $this->isNacional(),
            'municipal' => $this->isMunicipal() || $this->isProvincial() || $this->isNacional(),
            'inotu' => $this->isInotu() || $this->isNacional(),
            default => true,
        };
    }

    // ─── Permissões ERP ───────────────────────────────────

    public function isFinanceiro(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::FINANCEIRO);
    }

    public function isRh(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::RH);
    }

    public function isCompras(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::COMPRAS);
    }

    public function isPatrimonio(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::PATRIMONIO);
    }

    public function isConsulta(): bool
    {
        return $this->roles->contains(fn (Role $role) => $role->nome === Role::CONSULTA);
    }

    public function isErp(): bool
    {
        return $this->isFinanceiro()
            || $this->isRh()
            || $this->isCompras()
            || $this->isPatrimonio();
    }

    public function podeEditarDados(): bool
    {
        if ($this->isDiretorInotu()) {
            return true;
        }

        if ($this->isAdminSomenteInformacaoTecnica()) {
            return false;
        }

        return $this->isInotu() || $this->isMunicipal();
    }

    public function podeVerMunicipio(int $municipioId): bool
    {
        if ($this->isInotu() || $this->isProvincial()) {
            return true;
        }

        return $this->municipio_id === $municipioId;
    }

    public function podeEditarMunicipio(int $municipioId): bool
    {
        if ($this->isDiretorInotu()) {
            return true;
        }

        if ($this->isAdminSomenteInformacaoTecnica()) {
            return false;
        }

        if ($this->isInotu()) {
            return true;
        }

        if ($this->isMunicipal()) {
            return $this->municipio_id === $municipioId;
        }

        return false;
    }

    public function podeGerirUsuarios(): bool
    {
        if ($this->isDiretorInotu()) {
            return true;
        }

        if ($this->isAdminSomenteInformacaoTecnica()) {
            return false;
        }

        return $this->isInotu() || $this->isProvincial() || $this->isAdminMunicipal();
    }

    public function podeGerirUsuariosDe(int $nivel): bool
    {
        if ($this->isDiretorInotu()) {
            return true;
        }

        $meuNivel = $this->nivelMinimo();

        if ($meuNivel === null) {
            return false;
        }

        // Só pode gerir níveis inferiores
        return $meuNivel < $nivel;
    }

    // ─── Novos Métodos ──────────────────────────────────

    /**
     * Obter todas as permissões efetivas (com cache)
     */
    public function permissoesEfetivas(): Collection
    {
        return $this->todasAsPermissoes()
            ->filter(fn ($permissao): bool => ! $this->isAdminSomenteInformacaoTecnica()
                || ! $this->bloqueadaParaAdminLimitado($permissao->slug))
            ->values();
    }

    /**
     * Obter nomes das permissões efetivas
     */
    public function nomesPermissoes(): Collection
    {
        return $this->permissoesEfetivas()->pluck('slug')->sort()->values();
    }

    /**
     * Retorna todas as permissões agregadas de todas as roles do utilizador (v1 compatível)
     */
    public function todasPermissoes(): Collection
    {
        return $this->nomesPermissoes();
    }

    /**
     * Invalidar cache de permissões (após mudar roles/permissões)
     */
    public function invalidarCachePermissoes(): void
    {
        app('rule-manager.registrar')->limparCache();
        $this->authService = null; // Forçar recriação na próxima chamada
    }

    /**
     * Obter estatísticas de acesso
     */
    public function estatisticasAcesso(?\Carbon\Carbon $desde = null): array
    {
        return [
            'desde' => $desde,
            'permissoes_efetivas' => $this->nomesPermissoes()->count(),
        ];
    }

    /**
     * Verificar se pode visualizar um recurso (genérico)
     */
    public function podeVerRecurso(object $recurso): bool
    {
        return $this->podeAcesso(class_basename($recurso) . '.ver',
            PermissionContext::fromUserAndResource($this, $recurso)
        );
    }

    /**
     * Verificar se pode editar um recurso (genérico)
     */
    public function podeEditarRecurso(object $recurso): bool
    {
        return $this->podeAcesso(class_basename($recurso) . '.editar',
            PermissionContext::fromUserAndResource($this, $recurso)
        );
    }

    /**
     * Verificar se pode eliminar um recurso (genérico)
     */
    public function podeEliminarRecurso(object $recurso): bool
    {
        return $this->podeAcesso(class_basename($recurso) . '.eliminar',
            PermissionContext::fromUserAndResource($this, $recurso)
        );
    }

    // ─── Gestão do Serviço ───────────────────────────────

    /**
     * Obter ou criar instância do serviço de autorização
     */
    protected function getAuthService(): AuthorizationService
    {
        if ($this->authService === null) {
            $this->authService = AuthorizationService::for($this);
        }

        return $this->authService;
    }

    /**
     * Definir serviço de autorização (útil para testes)
     */
    public function setAuthService(AuthorizationService $service): void
    {
        $this->authService = $service;
    }

    private function legacyPermissionCandidates(string $permission): array
    {
        $legacyResources = [
            'carta' => 'cartas_municipais',
            'croqui' => 'croquis',
            'instrumento' => 'instrumentos',
            'operacao' => 'operacoes',
            'parecer' => 'pareceres',
            'sona' => 'sona',
            'terreno' => 'terrenos_ociosos',
        ];

        $legacyActions = [
            'approve' => ['aprovar'],
            'create' => ['criar'],
            'delete' => ['eliminar', 'eliminar_proprio', 'eliminar_municipio'],
            'edit' => ['editar', 'editar_proprio', 'editar_municipio', 'editar_rascunho'],
            'view' => ['ver'],
        ];

        $candidates = [$permission];

        foreach ($legacyResources as $legacyResource => $currentResource) {
            foreach ($legacyActions as $legacyAction => $currentActions) {
                if ($permission !== "{$legacyResource}_{$legacyAction}") {
                    continue;
                }

                foreach ($currentActions as $currentAction) {
                    $candidates[] = "{$currentResource}.{$currentAction}";
                }
            }
        }

        return array_values(array_unique($candidates));
    }

    private function bloqueadaParaAdminLimitado(string $permissao): bool
    {
        $prefixos = ['usuarios.', 'permissoes.', 'lixeira.'];
        $acoes = ['.criar', '.editar', '.editar_municipio', '.eliminar', '.eliminar_municipio', '.aprovar', '.gerir', '.restaurar', '.eliminar_definitivo'];

        return collect($prefixos)->contains(fn (string $prefixo): bool => str_starts_with($permissao, $prefixo))
            || collect($acoes)->contains(fn (string $acao): bool => str_ends_with($permissao, $acao));
    }
}
