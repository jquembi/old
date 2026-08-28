<?php

namespace App\Domain\Municipios\Exceptions;

use App\Shared\Exceptions\ResourceNotFoundException;

/**
 * Lançada quando um município não é encontrado.
 */
class MunicipioNotFoundException extends ResourceNotFoundException
{
    public function __construct(int|string $municipioId = '?')
    {
        parent::__construct('Município', $municipioId);
    }
}
