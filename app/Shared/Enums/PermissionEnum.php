<?php

namespace App\Shared\Enums;

enum PermissionEnum: string
{
    // Municipios
    case VIEW_MUNICIPIOS = 'view_municipios';
    case CREATE_MUNICIPIOS = 'create_municipios';
    case UPDATE_MUNICIPIOS = 'update_municipios';
    case DELETE_MUNICIPIOS = 'delete_municipios';

    // Instrumentos
    case VIEW_INSTRUMENTOS = 'view_instrumentos';
    case CREATE_INSTRUMENTOS = 'create_instrumentos';
    case UPDATE_INSTRUMENTOS = 'update_instrumentos';
    case DELETE_INSTRUMENTOS = 'delete_instrumentos';
    case APPROVE_INSTRUMENTOS = 'approve_instrumentos';

    // Operacoes
    case VIEW_OPERACOES = 'view_operacoes';
    case CREATE_OPERACOES = 'create_operacoes';
    case UPDATE_OPERACOES = 'update_operacoes';
    case DELETE_OPERACOES = 'delete_operacoes';

    // Usuarios
    case VIEW_USUARIOS = 'view_usuarios';
    case CREATE_USUARIOS = 'create_usuarios';
    case UPDATE_USUARIOS = 'update_usuarios';
    case DELETE_USUARIOS = 'delete_usuarios';
    case MANAGE_ROLES = 'manage_roles';

    // Sistema
    case VIEW_AUDIT_LOGS = 'view_audit_logs';
    case VIEW_REPORTS = 'view_reports';

    /**
     * Retorna o rótulo legível da permissão.
     */
    public function label(): string
    {
        return str_replace('_', ' ', ucwords($this->value));
    }

    /**
     * Retorna todas as permissões como array.
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * Retorna permissões por recurso.
     */
    public static function byResource(string $resource): array
    {
        return match($resource) {
            'municipios' => [
                self::VIEW_MUNICIPIOS->value,
                self::CREATE_MUNICIPIOS->value,
                self::UPDATE_MUNICIPIOS->value,
                self::DELETE_MUNICIPIOS->value,
            ],
            'instrumentos' => [
                self::VIEW_INSTRUMENTOS->value,
                self::CREATE_INSTRUMENTOS->value,
                self::UPDATE_INSTRUMENTOS->value,
                self::DELETE_INSTRUMENTOS->value,
                self::APPROVE_INSTRUMENTOS->value,
            ],
            'operacoes' => [
                self::VIEW_OPERACOES->value,
                self::CREATE_OPERACOES->value,
                self::UPDATE_OPERACOES->value,
                self::DELETE_OPERACOES->value,
            ],
            'usuarios' => [
                self::VIEW_USUARIOS->value,
                self::CREATE_USUARIOS->value,
                self::UPDATE_USUARIOS->value,
                self::DELETE_USUARIOS->value,
                self::MANAGE_ROLES->value,
            ],
            default => [],
        };
    }
}
