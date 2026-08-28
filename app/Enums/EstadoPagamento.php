<?php

namespace App\Enums;

enum EstadoPagamento: string
{
    case AguardandoPagamento  = 'aguardando_pagamento';
    case RupeGerado           = 'rupe_gerado';
    case ComproativoSubmetido = 'comprovativo_submetido';
    case Pago                 = 'pago';
    case PagamentoRejeitado   = 'pagamento_rejeitado';
    case Concluido            = 'concluido';

    public function label(): string
    {
        return match ($this) {
            self::AguardandoPagamento  => 'Aguardando RUPE',
            self::RupeGerado           => 'RUPE gerado',
            self::ComproativoSubmetido => 'Comprovativo submetido',
            self::Pago                 => 'Pago',
            self::PagamentoRejeitado   => 'Pagamento rejeitado',
            self::Concluido            => 'Concluído',
        };
    }

    public function cor(): string
    {
        return match ($this) {
            self::AguardandoPagamento  => 'amber',
            self::RupeGerado           => 'blue',
            self::ComproativoSubmetido => 'violet',
            self::Pago                 => 'green',
            self::PagamentoRejeitado   => 'red',
            self::Concluido            => 'sky',
        };
    }

    public function icone(): string
    {
        return match ($this) {
            self::AguardandoPagamento  => 'clock',
            self::RupeGerado           => 'document-text',
            self::ComproativoSubmetido => 'paper-clip',
            self::Pago, self::Concluido => 'check-circle',
            self::PagamentoRejeitado   => 'x-circle',
        };
    }

    public function passo(): int
    {
        return match ($this) {
            self::AguardandoPagamento  => 1,
            self::RupeGerado           => 2,
            self::ComproativoSubmetido => 3,
            self::Pago, self::Concluido => 4,
            self::PagamentoRejeitado   => 3,
        };
    }

    public static function todos(): array
    {
        return [
            self::AguardandoPagamento,
            self::RupeGerado,
            self::ComproativoSubmetido,
            self::Pago,
            self::PagamentoRejeitado,
            self::Concluido,
        ];
    }
}
