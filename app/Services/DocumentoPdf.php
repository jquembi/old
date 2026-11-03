<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class DocumentoPdf extends Fpdi
{
    public function _outRaw(string $operadores): void
    {
        $this->_out($operadores);
    }

    public function fatorEscala(): float
    {
        return (float) $this->k;
    }
}
