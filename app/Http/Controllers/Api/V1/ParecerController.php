<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreParecerRequest;
use App\Http\Resources\ParecerResource;
use App\Models\Parecer;
use App\Services\AuditoriaService;
use App\Services\LixeiraService;
use App\Services\PdfCompressionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ParecerController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/pareceres
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Parecer::class);

        $user = $request->user();

        $query = Parecer::with(['municipio', 'criadoPor', 'recurso']);

        if ($user->isMunicipal()) {
            $query->where('municipio_id', $user->municipio_id);
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->where('municipio_id', $municipioId);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        if ($servico = $request->query('servico')) {
            $query->where('servico', $servico);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('projecto_nome', 'like', "%{$search}%")
                  ->orWhere('servico', 'like', "%{$search}%")
                  ->orWhere('classificacao_area', 'like', "%{$search}%")
                  ->orWhere('uso_solo', 'like', "%{$search}%")
                  ->orWhere('entidade_requerente', 'like', "%{$search}%");
            });
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $pareceres = $query->latest()
            ->paginate($request->integer('per_page', 25));

        return $this->paginated(ParecerResource::collection($pareceres));
    }

    /**
     * POST /api/v1/pareceres
     */
    public function store(StoreParecerRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Gerar slug a partir do titulo
        if (! empty($data['titulo'])) {
            $data['slug'] = Str::slug($data['titulo']);
        }

        if (! $request->user()->podeEditarMunicipio($data['municipio_id'])) {
            return $this->forbidden('Não tem permissão para este município.');
        }

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $ficheiroGuardado = app(PdfCompressionService::class)->storeCompressed($file, "pareceres/{$data['municipio_id']}", 'local');
            $data['ficheiro_caminho'] = $ficheiroGuardado['path'];
            $data['ficheiro_nome']    = $file->getClientOriginalName();
        }

        $data['criado_por'] = $request->user()->id;

        $parecer = Parecer::create($data);

        AuditoriaService::criar($parecer);

        $parecer->load(['municipio', 'criadoPor', 'recurso']);

        return $this->created(new ParecerResource($parecer));
    }

    /**
     * GET /api/v1/pareceres/{parecer}
     */
    public function show(Parecer $parecer): JsonResponse
    {
        Gate::authorize('view', $parecer);

        $parecer->load(['municipio', 'criadoPor', 'ficheiros', 'recurso']);

        return $this->success(new ParecerResource($parecer));
    }

    /**
     * PUT /api/v1/pareceres/{parecer}
     */
    public function update(StoreParecerRequest $request, Parecer $parecer): JsonResponse
    {
        Gate::authorize('update', $parecer);

        $valoresAntigos = $parecer->toArray();
        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            if ($parecer->ficheiro_caminho) {
                Storage::disk('local')->delete($parecer->ficheiro_caminho);
            }
            $file = $request->file('ficheiro');
            $ficheiroGuardado = app(PdfCompressionService::class)->storeCompressed($file, "pareceres/{$parecer->municipio_id}", 'local');
            $data['ficheiro_caminho'] = $ficheiroGuardado['path'];
            $data['ficheiro_nome']    = $file->getClientOriginalName();
        }

        $parecer->update($data);

        AuditoriaService::editar($parecer, $valoresAntigos);

        $parecer->load(['municipio', 'criadoPor', 'ficheiros', 'recurso']);

        return $this->success(new ParecerResource($parecer), 'Parecer actualizado.');
    }

    /**
     * DELETE /api/v1/pareceres/{parecer}
     */
    public function destroy(Parecer $parecer): JsonResponse
    {
        Gate::authorize('delete', $parecer);

        LixeiraService::enviar($parecer);

        return $this->success(null, 'Parecer enviado para a lixeira.');
    }
}
