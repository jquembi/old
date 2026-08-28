<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\TerrenoOcioso\StoreTerrenoOciosoRequest;
use App\Http\Requests\Api\V1\TerrenoOcioso\UpdateTerrenoOciosoRequest;
use App\Http\Resources\Api\V1\TerrenoOciosoResource;
use App\Models\TerrenoOcioso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TerrenoOciosoController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TerrenoOcioso::class);

        $query = TerrenoOcioso::with(['municipio', 'creator']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('proprietario', 'like', "%{$search}%")
                  ->orWhere('endereco', 'like', "%{$search}%")
                  ->orWhere('lote', 'like', "%{$search}%");
            });
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->withoutGlobalScope('municipio')->where('municipio_id', $municipioId);
        }

        if ($motivo = $request->query('motivo_ociosidade')) {
            $query->where('motivo_ociosidade', $motivo);
        }

        if ($prioridade = $request->query('prioridade_intervencao')) {
            $query->where('prioridade_intervencao', $prioridade);
        }

        if ($request->query('tempo_min_anos')) {
            $query->where('tempo_ociosidade_anos', '>=', (int) $request->query('tempo_min_anos'));
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $allowed = ['proprietario', 'area_m2', 'tempo_ociosidade_anos', 'prioridade_intervencao', 'created_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);

        return $this->paginated(
            $query->paginate($perPage)->through(fn($t) => new TerrenoOciosoResource($t)),
            'Terrenos ociosos listados com sucesso'
        );
    }

    public function store(StoreTerrenoOciosoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            $data['pdf_path'] = $request->file('ficheiro')->store(
                'terrenos-ociosos/pdfs/' . date('Y/m'),
                's3'
            );
        }

        unset($data['ficheiro']);
        $terreno = TerrenoOcioso::create($data);
        $terreno->load('municipio', 'creator');

        return $this->created(
            new TerrenoOciosoResource($terreno),
            'Terreno ocioso registrado com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $terreno = TerrenoOcioso::with(['municipio', 'creator'])->findOrFail($id);
        $this->authorize('view', $terreno);

        return $this->success(
            new TerrenoOciosoResource($terreno),
            'Terreno ocioso encontrado'
        );
    }

    public function update(UpdateTerrenoOciosoRequest $request, int $id): JsonResponse
    {
        $terreno = TerrenoOcioso::findOrFail($id);
        $this->authorize('update', $terreno);

        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            if ($terreno->pdf_path) {
                Storage::disk('s3')->delete($terreno->pdf_path);
            }
            $data['pdf_path'] = $request->file('ficheiro')->store(
                'terrenos-ociosos/pdfs/' . date('Y/m'),
                's3'
            );
        }

        unset($data['ficheiro']);
        $terreno->update($data);
        $terreno->load('municipio', 'creator');

        return $this->success(
            new TerrenoOciosoResource($terreno),
            'Terreno ocioso atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $terreno = TerrenoOcioso::findOrFail($id);
        $this->authorize('delete', $terreno);

        if ($terreno->pdf_path) {
            Storage::disk('s3')->delete($terreno->pdf_path);
        }

        $terreno->delete();

        return $this->success(null, 'Terreno ocioso eliminado com sucesso');
    }
}
