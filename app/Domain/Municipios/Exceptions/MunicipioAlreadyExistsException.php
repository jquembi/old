<?php

namespace App\Domain\Municipios\Exceptions;

use App\Shared\Exceptions\ApplicationException;

/**
 * Lançada quando tenta-se criar um município que já existe.
 */
class MunicipioAlreadyExistsException extends ApplicationException
{
    protected int $statusCode = 409;

    public function __construct(string $municipioName = '')
    {
        $message = "Município '{$municipioName}' já existe no sistema.";
        parent::__construct($message);
        $this->userMessage = "Este município já foi registrado.";
    }
}
