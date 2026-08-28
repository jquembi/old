<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreLoteamentoRequest;
use App\Http\Resources\LoteamentoResource;
use App\Models\Loteamento;
use App\Services\AuditoriaService;
use App\Services\LixeiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LoteamentoController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/loteamentos
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Loteamento::class);

        $user = $request->user();

        $query = Loteamento::with(['municipio', 'criadoPor']);

        if ($user->isMunicipal()) {
            $query->where('municipio_id', $user->municipio_id);
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->where('municipio_id', $municipioId);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        if ($search = $request->query('search')) {
            $query->where('nome', 'like', "%{$search}%");
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $loteamentos = $query->latest()
            ->paginate($request->integer('per_page', 25));

        return $this->paginated(LoteamentoResource::collection($loteamentos));
    }

    /**
     * POST /api/v1/loteamentos
     */
    public function store(StoreLoteamentoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->user()->podeEditarMunicipio($data['municipio_id'])) {
            return $this->forbidden('Não tem permissão para este município.');
        }

        $data['criado_por'] = $request->user()->id;

        $loteamento = Loteamento::create($data);

        AuditoriaService::criar($loteamento);

        $loteamento->load(['municipio', 'criadoPor']);

        return $this->created(new LoteamentoResource($loteamento));
    }

    /**
     * GET /api/v1/loteamentos/{loteamento}
     */
    public function show(Loteamento $loteamento): JsonResponse
    {
        Gate::authorize('view', $loteamento);

        $loteamento->load(['municipio', 'criadoPor', 'ficheiros']);

        return $this->success(new LoteamentoResource($loteamento));
    }

    /**
     * PUT /api/v1/loteamentos/{loteamento}
     */
    public function update(StoreLoteamentoRequest $request, Loteamento $loteamento): JsonResponse
    {
        Gate::authorize('update', $loteamento);

        $valoresAntigos = $loteamento->toArray();
        $loteamento->update($request->validated());

        AuditoriaService::editar($loteamento, $valoresAntigos);

        $loteamento->load(['municipio', 'criadoPor']);

        return $this->success(new LoteamentoResource($loteamento), 'Loteamento actualizado.');
    }

    /**
     * DELETE /api/v1/loteamentos/{loteamento}
     */
    public function destroy(Loteamento $loteamento): JsonResponse
    {
        Gate::authorize('delete', $loteamento);

        LixeiraService::enviar($loteamento);

        return $this->success(null, 'Loteamento enviado para a lixeira.');
    }
}
