<?php

namespace App\Shared\Enums;

enum RoleEnum: string
{
    case INOTU = 'inotu';
    case GOVERNO_PROVINCIAL = 'governo_provincial';
    case TECNICO_MUNICIPAL = 'tecnico_municipal';

    /**
     * Retorna o rótulo legível do role.
     */
    public function label(): string
    {
        return match($this) {
            self::INOTU => 'INOTU (Administrador)',
            self::GOVERNO_PROVINCIAL => 'Governo Provincial',
            self::TECNICO_MUNICIPAL => 'Técnico Municipal',
        };
    }

    /**
     * Retorna a descrição do role.
     */
    public function description(): string
    {
        return match($this) {
            self::INOTU => 'Acesso total ao sistema',
            self::GOVERNO_PROVINCIAL => 'Acesso a múltiplos municípios',
            self::TECNICO_MUNICIPAL => 'Acesso restrito ao seu município',
        };
    }

    /**
     * Retorna todos os roles como array.
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
