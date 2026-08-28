<?php

namespace App\Infrastructure\Logging;

use Illuminate\Support\Facades\{Log, Auth};

/**
 * Logger estruturado para operações críticas do sistema.
 * 
 * Fornece interface consistente para logging de eventos importantes
 * com estrutura de dados padronizada.
 */
class StructuredLogger
{
    /**
     * Log de operação bem-sucedida.
     */
    public static function success(string $message, array $context = []): void
    {
        Log::info($message, array_merge([
            'status' => 'success',
            'timestamp' => now(),
            'user_id' => Auth::id(),
        ], $context));
    }

    /**
     * Log de erro.
     */
    public static function error(string $message, array $context = []): void
    {
        Log::error($message, array_merge([
            'status' => 'error',
            'timestamp' => now(),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
        ], $context));
    }

    /**
     * Log de tentativa de acesso não autorizado.
     */
    public static function unauthorized(string $message, array $context = []): void
    {
        Log::warning("unauthorized: {$message}", array_merge([
            'status' => 'unauthorized',
            'timestamp' => now(),
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $context));
    }

    /**
     * Log de operação crítica no sistema.
     */
    public static function critical(string $message, array $context = []): void
    {
        Log::critical($message, array_merge([
            'status' => 'critical',
            'timestamp' => now(),
            'user_id' => Auth::id(),
        ], $context));
    }
}
