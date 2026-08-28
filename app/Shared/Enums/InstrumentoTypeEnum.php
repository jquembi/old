<?php

namespace App\Shared\Enums;

enum InstrumentoTypeEnum: string
{
    case PDMU = 'pdmu'; // Plano Direto de Múltiplos Usos
    case PROT = 'prot'; // Plano Regional de Ordenamento Territorial
    case PEOT = 'peot'; // Programa Estadual de Ordenamento Territorial
    case OPERACAO_URBANA = 'operacao_urbana';

    /**
     * Retorna o rótulo legível do tipo de instrumento.
     */
    public function label(): string
    {
        return match($this) {
            self::PDMU => 'Plano Direto de Múltiplos Usos',
            self::PROT => 'Plano Regional de Ordenamento Territorial',
            self::PEOT => 'Programa Estadual de Ordenamento Territorial',
            self::OPERACAO_URBANA => 'Operação Urbana',
        };
    }

    /**
     * Retorna todos os tipos como array.
     */
    public static function all(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
