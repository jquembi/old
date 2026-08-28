<?php

namespace App\Application\Services;

use Illuminate\Support\Facades\Log;

class ErrorNotificationService
{
    public function notify(\Throwable $e): void
    {
        Log::error('Application error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
