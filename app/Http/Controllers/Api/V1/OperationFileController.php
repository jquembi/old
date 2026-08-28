<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOperationFileRequest;
use App\Http\Resources\OperationFileResource;
use App\Models\OperacaoUrbanistica;
use App\Models\OperationFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OperationFileController extends Controller
{
    use ApiResponse;
    /**
     * GET /api/v1/operacoes/{operacao}/ficheiros
     * Listar ficheiros da operação (filtráveis por tipo).
     */
    public function index(Request $request, OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('viewAny', OperationFile::class);

        $query = $operacao->ficheiros()->with('enviadoPor');

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($croquiId = $request->query('croqui_id')) {
            $query->where('croqui_id', $croquiId);
        }

        if ($parecerId = $request->query('parecer_id')) {
            $query->where('parecer_id', $parecerId);
        }

        $ficheiros = $query->latest()->paginate(
            $request->integer('per_page', 25)
        );

        return $this->paginated(OperationFileResource::collection($ficheiros));
    }

    /**
     * POST /api/v1/operacoes/{operacao}/ficheiros
     * Upload de ficheiro técnico.
     */
    public function store(StoreOperationFileRequest $request, OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('create', OperationFile::class);

        $file      = $request->file('ficheiro');
        $extension = strtolower($file->getClientOriginalExtension());
        $tipo      = OperationFile::detectarTipo($extension);
        $path      = $file->store("operacoes/{$operacao->id}/ficheiros", 'local');

        $record = OperationFile::create([
            'operacao_id'  => $operacao->id,
            'croqui_id'    => $request->validated('croqui_id'),
            'parecer_id'   => $request->validated('parecer_id'),
            'nome'         => pathinfo($path, PATHINFO_BASENAME),
            'nome_original' => $file->getClientOriginalName(),
            'tipo'         => $tipo,
            'extensao'     => $extension,
            'caminho'      => $path,
            'tamanho'      => $file->getSize(),
            'mime_type'    => $file->getMimeType(),
            'enviado_por'  => $request->user()->id,
        ]);

        $record->load('enviadoPor');

        return $this->created(new OperationFileResource($record));
    }

    /**
     * GET /api/v1/ficheiros/{operationFile}
     */
    public function show(OperationFile $operationFile): JsonResponse
    {
        Gate::authorize('view', $operationFile);

        $operationFile->load('enviadoPor');

        return $this->success(new OperationFileResource($operationFile));
    }

    /**
     * GET /api/v1/ficheiros/{operationFile}/download
     * Download seguro do ficheiro.
     */
    public function download(OperationFile $operationFile): StreamedResponse
    {
        Gate::authorize('view', $operationFile);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($operationFile->caminho), 404, 'Ficheiro não encontrado.');

        return $disk->download(
            $operationFile->caminho,
            $operationFile->nome_original,
        );
    }

    /**
     * DELETE /api/v1/ficheiros/{operationFile}
     */
    public function destroy(OperationFile $operationFile): JsonResponse
    {
        Gate::authorize('delete', $operationFile);

        Storage::disk('local')->delete($operationFile->caminho);
        $operationFile->delete();

        return $this->success(null, 'Ficheiro eliminado.');
    }
}
