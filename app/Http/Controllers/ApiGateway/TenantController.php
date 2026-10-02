<?php

namespace App\Http\Controllers\ApiGateway;

use App\Http\Controllers\Controller;
use App\Models\NationalIndicator;
use App\Models\Tenant;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        private SyncService $syncService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        // Apenas o núcleo central pode listar todos os tenants
        if (!$tenant->isCentral()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $tenants = Tenant::all();

        return response()->json([
            'success' => true,
            'data' => $tenants,
        ]);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        $currentTenant = app('current.tenant');
        
        // Apenas o núcleo central ou o próprio tenant pode ver os detalhes
        if (!$currentTenant->isCentral() && $currentTenant->id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $tenant->load(['indicators', 'syncLogs']),
        ]);
    }

    public function markOnline(Tenant $tenant): JsonResponse
    {
        $currentTenant = app('current.tenant');
        
        // Apenas o próprio tenant pode marcar-se como online
        if ($currentTenant->id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $this->syncService->markTenantOnline($tenant);

        return response()->json([
            'success' => true,
            'message' => 'Tenant marcado como online',
        ]);
    }

    public function markOffline(Tenant $tenant): JsonResponse
    {
        $currentTenant = app('current.tenant');
        
        // Apenas o próprio tenant pode marcar-se como offline
        if ($currentTenant->id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $this->syncService->markTenantOffline($tenant);

        return response()->json([
            'success' => true,
            'message' => 'Tenant marcado como offline',
        ]);
    }

    public function indicators(Tenant $tenant): JsonResponse
    {
        $currentTenant = app('current.tenant');
        
        // Apenas o núcleo central ou o próprio tenant pode ver os indicadores
        if (!$currentTenant->isCentral() && $currentTenant->id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $indicators = $tenant->indicators()->latest()->limit(50)->get();

        return response()->json([
            'success' => true,
            'data' => $indicators,
        ]);
    }

    public function nationalIndicators(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        if (!$tenant->isCentral()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $query = NationalIndicator::query();

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('tenant_id')) {
            $query->byTenant($request->tenant_id);
        }

        if ($request->has('period_start') && $request->has('period_end')) {
            $query->inPeriod($request->period_start, $request->period_end);
        }

        $indicators = $query->latest()->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $indicators,
        ]);
    }

    public function storeIndicator(Request $request): JsonResponse
    {
        $tenant = app('current.tenant');
        
        if (!$tenant->isCentral()) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'indicator_type' => 'required|string',
            'value' => 'required|array',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $indicator = NationalIndicator::create([
            'tenant_id' => $request->tenant_id,
            'indicator_type' => $request->indicator_type,
            'value' => $request->value,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'synced_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $indicator,
            'message' => 'Indicador criado com sucesso',
        ], 201);
    }

    public function showIndicator(NationalIndicator $indicator): JsonResponse
    {
        $tenant = app('current.tenant');
        
        if (!$tenant->isCentral() && $tenant->id !== $indicator->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $indicator,
        ]);
    }
}
