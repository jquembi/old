<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\Instrumento\StoreInstrumentoOrdenamentoRequest;
use App\Http\Requests\Api\V1\Instrumento\UpdateInstrumentoOrdenamentoRequest;
use App\Http\Resources\Api\V1\InstrumentoOrdenamentoResource;
use App\Models\InstrumentoOrdenamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstrumentoOrdenamentoController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InstrumentoOrdenamento::class);

        $query = InstrumentoOrdenamento::with(['municipio', 'creator', 'approver']);

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

        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $allowed = ['titulo', 'tipo', 'status', 'data_aprovacao', 'created_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);

        return $this->paginated(
            $query->paginate($perPage)->through(fn($i) => new InstrumentoOrdenamentoResource($i)),
            'Instrumentos de ordenamento listados com sucesso'
        );
    }

    public function store(StoreInstrumentoOrdenamentoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            $data['file_path'] = $request->file('ficheiro')->store(
                'instrumentos-ordenamento/pdfs/' . date('Y/m'),
                's3'
            );
        }

        unset($data['ficheiro']);
        $instrumento = InstrumentoOrdenamento::create($data);
        $instrumento->load('municipio', 'creator');

        return $this->created(
            new InstrumentoOrdenamentoResource($instrumento),
            'Instrumento de ordenamento criado com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $instrumento = InstrumentoOrdenamento::with(['municipio', 'creator', 'approver'])->findOrFail($id);
        $this->authorize('view', $instrumento);

        return $this->success(
            new InstrumentoOrdenamentoResource($instrumento),
            'Instrumento de ordenamento encontrado'
        );
    }

    public function update(UpdateInstrumentoOrdenamentoRequest $request, int $id): JsonResponse
    {
        $instrumento = InstrumentoOrdenamento::findOrFail($id);
        $this->authorize('update', $instrumento);

        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            if ($instrumento->file_path) {
                Storage::disk('s3')->delete($instrumento->file_path);
            }
            $data['file_path'] = $request->file('ficheiro')->store(
                'instrumentos-ordenamento/pdfs/' . date('Y/m'),
                's3'
            );
        }

        unset($data['ficheiro']);
        $instrumento->update($data);
        $instrumento->load('municipio', 'creator', 'approver');

        return $this->success(
            new InstrumentoOrdenamentoResource($instrumento),
            'Instrumento de ordenamento atualizado com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $instrumento = InstrumentoOrdenamento::findOrFail($id);
        $this->authorize('delete', $instrumento);

        if ($instrumento->file_path) {
            Storage::disk('s3')->delete($instrumento->file_path);
        }

        $instrumento->delete();

        return $this->success(null, 'Instrumento de ordenamento eliminado com sucesso');
    }
}
