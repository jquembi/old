<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\SonaPlano\StoreSonaPlanoRequest;
use App\Http\Requests\Api\V1\SonaPlano\UpdateSonaPlanoRequest;
use App\Http\Resources\Api\V1\SonaPlanoResource;
use App\Models\SonaPlano;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SonaPlanoController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SonaPlano::class);

        $query = SonaPlano::with(['municipio', 'creator']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->where('municipio_id', $municipioId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $allowed = ['titulo', 'status', 'percentual_conclusao', 'orcamento_total', 'data_inicio', 'created_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);

        return $this->paginated(
            $query->paginate($perPage)->through(fn($s) => new SonaPlanoResource($s)),
            'Planos SONA listados com sucesso'
        );
    }

    public function store(StoreSonaPlanoRequest $request): JsonResponse
    {
        $plano = SonaPlano::create($request->validated());
        $plano->load('municipio', 'creator');

        return $this->created(
            new SonaPlanoResource($plano),
            'Plano SONA criado com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $plano = SonaPlano::with(['municipio', 'creator', 'approver'])->findOrFail($id);
        $this->authorize('view', $plano);

        return $this->success(
            new SonaPlanoResource($plano),
            'Plano SONA encontrado'
        );
    }

    public function update(UpdateSonaPlanoRequest $request, int $id): JsonResponse
    {
        $plano = SonaPlano::findOrFail($id);
        $this->authorize('update', $plano);

        $plano->update($request->validated());
        $plano->load('municipio', 'creator');

        return $this->success(
            new SonaPlanoResource($plano),
            'Plano SONA atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $plano = SonaPlano::findOrFail($id);
        $this->authorize('delete', $plano);

        $plano->delete();

        return $this->success(null, 'Plano SONA eliminado com sucesso');
    }
}
