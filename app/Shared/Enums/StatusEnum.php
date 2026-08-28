<?php

namespace App\Shared\Enums;

enum StatusEnum: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    /**
     * Retorna o rótulo legível do status.
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Rascunho',
            self::SUBMITTED => 'Submetido',
            self::APPROVED => 'Aprovado',
            self::REJECTED => 'Rejeitado',
            self::PUBLISHED => 'Publicado',
            self::ARCHIVED => 'Arquivado',
        };
    }

    /**
     * Retorna a cor para badge (Bootstrap/Tailwind).
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'secondary',
            self::SUBMITTED => 'info',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::PUBLISHED => 'primary',
            self::ARCHIVED => 'dark',
        };
    }

    /**
     * Retorna todos os status como array.
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
