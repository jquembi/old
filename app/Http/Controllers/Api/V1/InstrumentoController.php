<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInstrumentoRequest;
use App\Http\Resources\InstrumentoResource;
use App\Models\Instrumento;
use App\Services\AuditoriaService;
use App\Services\LixeiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InstrumentoController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/instrumentos
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Instrumento::class);

        $user = $request->user();

        $query = Instrumento::with(['municipio', 'tipoInstrumento', 'criadoPor']);

        if ($user->isMunicipal()) {
            $query->where('municipio_id', $user->municipio_id);
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->where('municipio_id', $municipioId);
        }

        if ($tipoId = $request->query('tipo_instrumento_id')) {
            $query->where('tipo_instrumento_id', $tipoId);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        if ($search = $request->query('search')) {
            $query->where('titulo', 'like', "%{$search}%");
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $instrumentos = $query->latest()
            ->paginate($request->integer('per_page', 25));

        return $this->paginated(InstrumentoResource::collection($instrumentos));
    }

    /**
     * POST /api/v1/instrumentos
     */
    public function store(StoreInstrumentoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $request->user()->podeEditarMunicipio($data['municipio_id'])) {
            return $this->forbidden('Não tem permissão para este município.');
        }

        $data['criado_por'] = $request->user()->id;

        $instrumento = Instrumento::create($data);

        AuditoriaService::criar($instrumento);

        $instrumento->load(['municipio', 'tipoInstrumento', 'criadoPor']);

        return $this->created(new InstrumentoResource($instrumento));
    }

    /**
     * GET /api/v1/instrumentos/{instrumento}
     */
    public function show(Instrumento $instrumento): JsonResponse
    {
        Gate::authorize('view', $instrumento);

        $instrumento->load(['municipio', 'tipoInstrumento', 'criadoPor', 'ficheiros']);

        return $this->success(new InstrumentoResource($instrumento));
    }

    /**
     * PUT /api/v1/instrumentos/{instrumento}
     */
    public function update(StoreInstrumentoRequest $request, Instrumento $instrumento): JsonResponse
    {
        Gate::authorize('update', $instrumento);

        $valoresAntigos = $instrumento->toArray();
        $data = $request->validated();

        $data['actualizado_por'] = $request->user()->id;
        $instrumento->update($data);

        AuditoriaService::editar($instrumento, $valoresAntigos);

        $instrumento->load(['municipio', 'tipoInstrumento', 'criadoPor']);

        return $this->success(new InstrumentoResource($instrumento), 'Instrumento actualizado.');
    }

    /**
     * DELETE /api/v1/instrumentos/{instrumento}
     */
    public function destroy(Instrumento $instrumento): JsonResponse
    {
        Gate::authorize('delete', $instrumento);

        LixeiraService::enviar($instrumento);

        return $this->success(null, 'Instrumento enviado para a lixeira.');
    }
}
