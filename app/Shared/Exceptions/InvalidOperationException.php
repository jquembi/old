<?php

namespace App\Shared\Exceptions;

/**
 * Lançada quando há validação de operação inválida.
 */
class InvalidOperationException extends ApplicationException
{
    protected int $statusCode = 422;

    public function __construct(string $message = 'Operação inválida.')
    {
        parent::__construct($message);
        $this->userMessage = $message;
    }
}
