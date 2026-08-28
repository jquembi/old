<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\MunicipioResource;
use App\Models\Municipio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MunicipioController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/municipios
     * Listar municípios (filtragem por scope do utilizador).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Municipio::with('provincia')->where('activo', true);

        // Escopo por município — técnicos/municipais só vêem o seu
        if ($user->isMunicipal()) {
            $query->where('id', $user->municipio_id);
        }

        // Filtros
        if ($search = $request->query('search')) {
            $query->where('nome', 'like', "%{$search}%");
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $municipios = $query->orderBy('nome')
            ->paginate($request->integer('per_page', 25));

        return $this->paginated(
            MunicipioResource::collection($municipios),
        );
    }

    /**
     * GET /api/v1/municipios/{municipio}
     */
    public function show(Municipio $municipio): JsonResponse
    {
        Gate::authorize('view', $municipio);

        $municipio->load('provincia');

        return $this->success(new MunicipioResource($municipio));
    }

    /**
     * GET /api/v1/municipios/{municipio}/stats
     * Estatísticas consolidadas do município.
     */
    public function stats(Municipio $municipio): JsonResponse
    {
        Gate::authorize('view', $municipio);

        return $this->success([
            'municipio_id'   => $municipio->id,
            'municipio_nome' => $municipio->nome,
            'totais'         => [
                'operacoes'     => $municipio->operacoesUrbanisticas()->count(),
                'croquis'       => $municipio->operacoesUrbanisticas()
                    ->withCount('croquis')->get()->sum('croquis_count'),
                'pareceres'     => $municipio->pareceres()->count(),
                'instrumentos'  => $municipio->instrumentos()->count(),
                'loteamentos'   => $municipio->loteamentos()->count(),
                'projectos_sona' => $municipio->projectosSona()->count(),
                'utilizadores'  => $municipio->users()->where('activo', true)->count(),
            ],
            'pareceres_por_estado' => $municipio->pareceres()
                ->selectRaw("estado, count(*) as total")
                ->groupBy('estado')
                ->pluck('total', 'estado'),
        ]);
    }
}
