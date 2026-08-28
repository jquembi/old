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
        if ($this->isDiretorInotu()) {
            return true;
        }

        return $this->getAuthService()->podeAcesso($permissao);
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
        return $this->getAuthService()->podeAcesso($permissao, $contexto);
    }

    /**
     * Verificar se tem alguma das permissões
     */
    public function temAlgumaPermissao(array $permissoes): bool
    {
        return $this->getAuthService()->temAlgumaPermissao($permissoes);
    }

    /**
     * Verificar se tem todas as permissões
     */
    public function temTodasAsPermissoes(array $permissoes): bool
    {
        return $this->getAuthService()->temTodasAsPermissoes($permissoes);
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
        return $this->getAuthService()->podeAcessoDinamico($acao, $contexto, $context);
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
        return $this->isDiretorInotu()
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
        return $this->getAuthService()->permissoesEfetivas();
    }

    /**
     * Obter nomes das permissões efetivas
     */
    public function nomesPermissoes(): Collection
    {
        return $this->getAuthService()->nomesPermissoes();
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
        $this->getAuthService()->invalidarCache();
        $this->authService = null; // Forçar recriação na próxima chamada
    }

    /**
     * Obter estatísticas de acesso
     */
    public function estatisticasAcesso(?\Carbon\Carbon $desde = null): array
    {
        return $this->getAuthService()->estatisticasAcesso($desde);
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
}
