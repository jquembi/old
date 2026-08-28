<?php

namespace App\DTO;

use Illuminate\Support\Collection;

/**
 * DTO para contexto de permissões
 * Encapsula o contexto em que uma ação de permissão é verificada
 */
class PermissionContext
{
    public function __construct(
        public ?int $municipioId = null,
        public ?int $provinciaId = null,
        public ?int $usuarioId = null,                    // ID do utilizador atual
        public ?int $recursoId = null,                   // ID do recurso sendo acessado
        public ?string $estado = null,                   // Estado do recurso (rascunho, aprovado, etc)
        public ?int $criadorId = null,                   // ID do criador do recurso
        public bool $ehCriador = false,                  // Se o utilizador atual é o criador
        public ?string $tipoRecurso = null,              // Tipo do recurso (instrumento, loteamento, etc)
        public ?array $tiposPermitidos = null,           // Tipos específicos permitidos
        public ?Collection $rolesUtilizador = null,      // Roles do utilizador
        public array $metadados = [],                    // Metadados adicionais
    ) {}

    /**
     * Criar contexto a partir de um utilizador e recurso
     */
    public static function fromUserAndResource(
        \App\Models\User $user,
        ?object $resource = null,
    ): self {
        return new self(
            municipioId: $user->municipio_id,
            provinciaId: $user->provincia_id,
            usuarioId: $user->id,
            recursoId: $resource?->id,
            criadorId: $resource?->criado_por ?? null,
            ehCriador: $resource?->criado_por === $user->id,
            tipoRecurso: $resource ? class_basename($resource) : null,
            rolesUtilizador: $user->roles,
            estado: $resource?->estado ?? null,
        );
    }

    /**
     * Validar se o contexto é válido para uma ação específica
     */
    public function validar(?string $tipo = null): bool
    {
        if ($tipo === 'municipio' && $this->municipioId === null) {
            return false;
        }

        if ($tipo === 'provincia' && $this->provinciaId === null) {
            return false;
        }

        if ($tipo === 'recurso' && $this->recursoId === null) {
            return false;
        }

        return true;
    }

    /**
     * Converter para array para auditoria/logging
     */
    public function toArray(): array
    {
        return [
            'municipio_id' => $this->municipioId,
            'provincia_id' => $this->provinciaId,
            'usuario_id' => $this->usuarioId,
            'recurso_id' => $this->recursoId,
            'estado' => $this->estado,
            'criador_id' => $this->criadorId,
            'eh_criador' => $this->ehCriador,
            'tipo_recurso' => $this->tipoRecurso,
            'tipos_permitidos' => $this->tiposPermitidos,
            'metadados' => $this->metadados,
        ];
    }

    /**
     * Checar se o utilizador pode acessar esta localidade
     */
    public function podeAcessarLocalizacao(\App\Models\User $user): bool
    {
        // INOTU acessa tudo
        if ($user->isInotu()) {
            return true;
        }

        // Utilizador provincial acessa apenas sua província
        if ($user->isProvincial() && $this->provinciaId !== null) {
            return $user->provincia_id === $this->provinciaId;
        }

        // Utilizador municipal acessa apenas seu município
        if ($user->isMunicipal() && $this->municipioId !== null) {
            return $user->municipio_id === $this->municipioId;
        }

        return false;
    }
}
