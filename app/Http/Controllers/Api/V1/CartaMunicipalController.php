<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\CartaMunicipal\StoreCartaMunicipalRequest;
use App\Http\Requests\Api\V1\CartaMunicipal\UpdateCartaMunicipalRequest;
use App\Http\Resources\Api\V1\CartaMunicipalResource;
use App\Models\CartaMunicipal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CartaMunicipalController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CartaMunicipal::class);

        $query = CartaMunicipal::with(['municipio', 'creator', 'approver']);

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('autor', 'like', "%{$search}%");
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
        $allowed = ['titulo', 'tipo', 'escala', 'data_emissao', 'created_at'];
        if (in_array($sortBy, $allowed)) {
            $query->orderBy($sortBy, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);

        return $this->paginated(
            $query->paginate($perPage)->through(fn($c) => new CartaMunicipalResource($c)),
            'Cartas municipais listadas com sucesso'
        );
    }

    public function store(StoreCartaMunicipalRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            $file = $request->file('ficheiro');
            $data['file_path'] = $file->store(
                'cartas-municipais/files/' . date('Y/m'),
                's3'
            );
            $data['file_size'] = $file->getSize();
        }

        unset($data['ficheiro']);
        $carta = CartaMunicipal::create($data);
        $carta->load('municipio', 'creator');

        return $this->created(
            new CartaMunicipalResource($carta),
            'Carta municipal criada com sucesso'
        );
    }

    public function show(int $id): JsonResponse
    {
        $carta = CartaMunicipal::with(['municipio', 'creator', 'approver'])->findOrFail($id);
        $this->authorize('view', $carta);

        return $this->success(
            new CartaMunicipalResource($carta),
            'Carta municipal encontrada'
        );
    }

    public function update(UpdateCartaMunicipalRequest $request, int $id): JsonResponse
    {
        $carta = CartaMunicipal::findOrFail($id);
        $this->authorize('update', $carta);

        $data = $request->validated();

        if ($request->hasFile('ficheiro')) {
            if ($carta->file_path) {
                Storage::disk('s3')->delete($carta->file_path);
            }
            $file = $request->file('ficheiro');
            $data['file_path'] = $file->store(
                'cartas-municipais/files/' . date('Y/m'),
                's3'
            );
            $data['file_size'] = $file->getSize();
        }

        unset($data['ficheiro']);
        $carta->update($data);
        $carta->load('municipio', 'creator', 'approver');

        return $this->success(
            new CartaMunicipalResource($carta),
            'Carta municipal atualizada com sucesso'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $carta = CartaMunicipal::findOrFail($id);
        $this->authorize('delete', $carta);

        if ($carta->file_path) {
            Storage::disk('s3')->delete($carta->file_path);
        }

        $carta->delete();

        return $this->success(null, 'Carta municipal eliminada com sucesso');
    }
}
