<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProjectoSonaRequest;
use App\Http\Resources\ProjectoSonaResource;
use App\Models\ProjectoSona;
use App\Services\AuditoriaService;
use App\Services\LixeiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SonaController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/sona
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', ProjectoSona::class);

        $user = $request->user();

        $query = ProjectoSona::with(['municipio', 'criadoPor']);

        if ($user->isMunicipal()) {
            $query->where('municipio_id', $user->municipio_id);
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->where('municipio_id', $municipioId);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($search = $request->query('search')) {
            $query->where('titulo', 'like', "%{$search}%");
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $projectos = $query->latest()
            ->paginate($request->integer('per_page', 25));

        return $this->paginated(ProjectoSonaResource::collection($projectos));
    }

    /**
     * POST /api/v1/sona
     */
    public function store(StoreProjectoSonaRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->user()->podeEditarMunicipio($data['municipio_id'])) {
            return $this->forbidden('Não tem permissão para este município.');
        }

        $data['criado_por'] = $request->user()->id;

        $projecto = ProjectoSona::create($data);

        AuditoriaService::criar($projecto);

        $projecto->load(['municipio', 'criadoPor']);

        return $this->created(new ProjectoSonaResource($projecto));
    }

    /**
     * GET /api/v1/sona/{projectoSona}
     */
    public function show(ProjectoSona $projectoSona): JsonResponse
    {
        Gate::authorize('view', $projectoSona);

        $projectoSona->load(['municipio', 'criadoPor', 'ficheiros']);

        return $this->success(new ProjectoSonaResource($projectoSona));
    }

    /**
     * PUT /api/v1/sona/{projectoSona}
     */
    public function update(StoreProjectoSonaRequest $request, ProjectoSona $projectoSona): JsonResponse
    {
        Gate::authorize('update', $projectoSona);

        $valoresAntigos = $projectoSona->toArray();
        $projectoSona->update($request->validated());

        AuditoriaService::editar($projectoSona, $valoresAntigos);

        $projectoSona->load(['municipio', 'criadoPor']);

        return $this->success(new ProjectoSonaResource($projectoSona), 'Projecto SONA actualizado.');
    }

    /**
     * DELETE /api/v1/sona/{projectoSona}
     */
    public function destroy(ProjectoSona $projectoSona): JsonResponse
    {
        Gate::authorize('delete', $projectoSona);

        LixeiraService::enviar($projectoSona);

        return $this->success(null, 'Projecto SONA enviado para a lixeira.');
    }
}
