<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOperacaoRequest;
use App\Http\Resources\GeoJsonFeatureCollection;
use App\Http\Resources\OperacaoResource;
use App\Models\OperacaoUrbanistica;
use App\Services\AuditoriaService;
use App\Services\LixeiraService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class OperacaoController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/operacoes
     * Listar operações (filtráveis por município).
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', OperacaoUrbanistica::class);

        $user = $request->user();

        $query = OperacaoUrbanistica::with(['municipio', 'criadoPor'])
            ->withCount(['croquis', 'ficheiros']);

        // Escopo por município
        if ($user->isMunicipal()) {
            $query->where('municipio_id', $user->municipio_id);
        }

        // Filtros
        if ($municipioId = $request->query('municipio_id')) {
            $query->where('municipio_id', $municipioId);
        }

        if ($search = $request->query('search')) {
            $query->where('titulo', 'like', "%{$search}%");
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $operacoes = $query->latest()
            ->paginate($request->integer('per_page', 25));

        return $this->paginated(OperacaoResource::collection($operacoes));
    }

    /**
     * POST /api/v1/operacoes
     */
    public function store(StoreOperacaoRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Verificar se pode operar neste município
        if (! $request->user()->podeEditarMunicipio($data['municipio_id'])) {
            return $this->forbidden('Não tem permissão para este município.');
        }

        if ($request->hasFile('ficheiro_base')) {
            $file = $request->file('ficheiro_base');
            $path = $file->store("operacoes/bases", 'local');
            $data['ficheiro_base_caminho'] = $path;
            $data['ficheiro_base_nome']    = $file->getClientOriginalName();
        }

        $data['criado_por'] = $request->user()->id;

        $operacao = OperacaoUrbanistica::create($data);

        AuditoriaService::criar($operacao);

        $operacao->load(['municipio', 'criadoPor']);
        $operacao->loadCount(['croquis', 'ficheiros']);

        return $this->created(new OperacaoResource($operacao));
    }

    /**
     * GET /api/v1/operacoes/{operacao}
     */
    public function show(OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('view', $operacao);

        $operacao->load(['municipio', 'criadoPor']);
        $operacao->loadCount(['croquis', 'ficheiros']);

        return $this->success(new OperacaoResource($operacao));
    }

    public function coordenadas(OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('view', $operacao);

        return $this->success(
            GeoJsonFeatureCollection::fromCroquis($operacao->croquis()->get()),
        );
    }

    /**
     * PUT /api/v1/operacoes/{operacao}
     */
    public function update(StoreOperacaoRequest $request, OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('update', $operacao);

        $valoresAntigos = $operacao->toArray();
        $data = $request->validated();

        if ($request->hasFile('ficheiro_base')) {
            if ($operacao->ficheiro_base_caminho) {
                Storage::disk('local')->delete($operacao->ficheiro_base_caminho);
            }
            $file = $request->file('ficheiro_base');
            $path = $file->store("operacoes/bases", 'local');
            $data['ficheiro_base_caminho'] = $path;
            $data['ficheiro_base_nome']    = $file->getClientOriginalName();
        }

        $operacao->update($data);

        AuditoriaService::editar($operacao, $valoresAntigos);

        $operacao->load(['municipio', 'criadoPor']);
        $operacao->loadCount(['croquis', 'ficheiros']);

        return $this->success(new OperacaoResource($operacao), 'Operação actualizada.');
    }

    /**
     * DELETE /api/v1/operacoes/{operacao}
     */
    public function destroy(OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('delete', $operacao);

        LixeiraService::enviar($operacao);

        return $this->success(null, 'Operação enviada para a lixeira.');
    }
}
