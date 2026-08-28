<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\Instrumento\StoreInstrumentoUrbanisticoRequest;
use App\Http\Requests\Api\V1\Instrumento\UpdateInstrumentoUrbanisticoRequest;
use App\Http\Resources\Api\V1\InstrumentoUrbanisticoResource;
use App\Models\InstrumentoUrbanistico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstrumentoUrbanisticoController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InstrumentoUrbanistico::class);

        $query = InstrumentoUrbanistico::with(['municipio', 'creator', 'approver']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('numero_documento', 'like', "%{$search}%");
            });
        }

        if ($municipioId = $request->query('municipio_id')) {
            $query->withoutGlobalScope('municipio')->where('municipio_id', $municipioId);
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($prioridade = $request->query('prioridade')) {
            $query->where('prioridade', $prioridade);
        }

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $allowed = ['titulo', 'tipo', 'status', 'prioridade', 'investimento_estimado', 'created_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);

        return $this->paginated(
            $query->paginate($perPage)->through(fn($i) => new InstrumentoUrbanisticoResource($i)),
            'Instrumentos urbanísticos listados com sucesso'
        );
    }

    public function store(StoreInstrumentoUrbanisticoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            $data['file_path'] = $request->file('ficheiro')->store(
                'instrumentos-urbanisticos/pdfs/' . date('Y/m'),
                's3'
            );
        }

        unset($data['ficheiro']);
        $instrumento = InstrumentoUrbanistico::create($data);
        $instrumento->load('municipio', 'creator');

        return $this->created(
            new InstrumentoUrbanisticoResource($instrumento),
            'Instrumento urbanístico criado com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $instrumento = InstrumentoUrbanistico::with(['municipio', 'creator', 'approver'])->findOrFail($id);
        $this->authorize('view', $instrumento);

        return $this->success(
            new InstrumentoUrbanisticoResource($instrumento),
            'Instrumento urbanístico encontrado'
        );
    }

    public function update(UpdateInstrumentoUrbanisticoRequest $request, int $id): JsonResponse
    {
        $instrumento = InstrumentoUrbanistico::findOrFail($id);
        $this->authorize('update', $instrumento);

        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            if ($instrumento->file_path) {
                Storage::disk('s3')->delete($instrumento->file_path);
            }
            $data['file_path'] = $request->file('ficheiro')->store(
                'instrumentos-urbanisticos/pdfs/' . date('Y/m'),
                's3'
            );
        }

        unset($data['ficheiro']);
        $instrumento->update($data);
        $instrumento->load('municipio', 'creator', 'approver');

        return $this->success(
            new InstrumentoUrbanisticoResource($instrumento),
            'Instrumento urbanístico atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $instrumento = InstrumentoUrbanistico::findOrFail($id);
        $this->authorize('delete', $instrumento);

        if ($instrumento->file_path) {
            Storage::disk('s3')->delete($instrumento->file_path);
        }

        $instrumento->delete();

        return $this->success(null, 'Instrumento urbanístico eliminado com sucesso');
    }
}
