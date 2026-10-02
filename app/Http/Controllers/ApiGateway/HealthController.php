<?php

namespace App\Http\Controllers\ApiGateway;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $tenant = app('current.tenant');
        
        return response()->json([
            'success' => true,
            'data' => [
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
                'tenant' => $tenant ? [
                    'id' => $tenant->id,
                    'code' => $tenant->code,
                    'name' => $tenant->name,
                    'type' => $tenant->type,
                    'is_active' => $tenant->is_active,
                    'is_online' => $tenant->is_online,
                ] : null,
                'version' => config('app.version', '1.0.0'),
                'environment' => config('app.env'),
            ],
        ]);
    }
}
