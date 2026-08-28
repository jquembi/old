<?php

namespace App\Shared\Exceptions;

/**
 * Lançada quando um usuário tenta realizar uma ação sem autorização.
 */
class UnauthorizedException extends ApplicationException
{
    protected int $statusCode = 403;

    public function __construct(
        string $message = 'Você não tem permissão para realizar esta ação.',
        ?string $userMessage = null
    ) {
        parent::__construct($message);
        $this->userMessage = $userMessage ?? $message;
    }
}
