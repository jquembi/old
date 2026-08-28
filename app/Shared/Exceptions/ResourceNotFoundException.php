<?php

namespace App\Shared\Exceptions;

/**
 * Lançada quando um recurso não é encontrado.
 */
class ResourceNotFoundException extends ApplicationException
{
    protected int $statusCode = 404;

    public function __construct(
        string $resourceName = 'Recurso',
        int|string $resourceId = '?'
    ) {
        $message = "{$resourceName} com ID '{$resourceId}' não encontrado.";
        parent::__construct($message);
        $this->userMessage = "O {$resourceName} solicitado não foi encontrado.";
    }
}
