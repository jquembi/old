<?php

namespace App\Shared\Exceptions;

use Exception;

/**
 * Exceção base para toda a aplicação.
 *
 * Todas as exceções customizadas da aplicação devem herdar desta classe.
 */
class ApplicationException extends Exception
{
    /**
     * Código HTTP associado à exceção.
     */
    protected int $statusCode = 500;

    /**
     * Mensagem amigável ao usuário.
     */
    protected string $userMessage;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $message;
    }

    /**
     * Retorna o código HTTP.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Retorna a mensagem amigável ao usuário.
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Render a exception into an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'message' => $this->userMessage,
            'error' => class_basename($this),
        ], $this->statusCode);
    }
}
