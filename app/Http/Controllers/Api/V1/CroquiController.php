<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCroquiRequest;
use App\Http\Resources\CroquiResource;
use App\Http\Resources\GeoJsonFeatureCollection;
use App\Models\Croqui;
use App\Models\OperacaoUrbanistica;
use App\Services\AuditoriaService;
use App\Services\CoordinateService;
use App\Services\LixeiraService;
use App\Services\PdfCompressionService;
use App\Services\TerrenoIdentidadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class CroquiController extends Controller
{
    use ApiResponse;
    public function __construct(
        private readonly CoordinateService $coordinates,
    ) {}

    /**
     * GET /api/v1/operacoes/{operacao}/croquis
     * Listar croquis (filtráveis por tipo: elaborado|terreno_ocioso).
     */
    public function index(Request $request, OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('viewAny', Croqui::class);

        $query = $operacao->croquis()
            ->with(['proprietarios', 'criadoPor', 'ficheiros']);

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($request->query('condicao')) {
            $query->where('condicao', $request->query('condicao'));
        }

        // Delta sync: registos actualizados desde timestamp
        if ($updatedSince = $request->query('updated_since')) {
            $query->where('updated_at', '>=', $updatedSince);
        }

        $croquis = $query->latest()->paginate(
            $request->integer('per_page', 25)
        );

        return $this->paginated(CroquiResource::collection($croquis));
    }

    /**
     * GET /api/v1/operacoes/{operacao}/croquis/geojson
     * Exportar como GeoJSON FeatureCollection para mapas.
     */
    public function geojson(Request $request, OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('viewAny', Croqui::class);

        $query = $operacao->croquis();

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);
        }

        return $this->success(
            GeoJsonFeatureCollection::fromCroquis($query->get())
        );
    }

    /**
     * POST /api/v1/operacoes/{operacao}/croquis
     */
    public function store(StoreCroquiRequest $request, OperacaoUrbanistica $operacao): JsonResponse
    {
        Gate::authorize('create', Croqui::class);

        $data = $request->validated();

        // Gerar slug a partir do titulo
        if (! empty($data['titulo'])) {
            $data['slug'] = Str::slug($data['titulo']);
        }

        // Auto-conversão de coordenadas
        $data = $this->resolveCoordinates($data);
        $identidade = app(TerrenoIdentidadeService::class);
        $conflito = $identidade->validarOuFalhar(
            $data['utm_zone'] ?? '33',
            $data['utm_hemisphere'] ?? 'S',
            $data['utm_easting'] ?? null,
            $data['utm_northing'] ?? null,
        );

        if ($conflito) {
            return $this->error($conflito['mensagem'], 422, ['codigo_territorial' => $conflito['codigo']]);
        }

        $data = array_merge($data, $identidade->dadosParaCoordenada(
            $data['utm_zone'] ?? null,
            $data['utm_hemisphere'] ?? null,
            $data['utm_easting'] ?? null,
            $data['utm_northing'] ?? null,
        ));

        // Upload do PDF se enviado
        if ($request->hasFile('ficheiro_pdf')) {
            $file = $request->file('ficheiro_pdf');
            $ficheiroGuardado = app(PdfCompressionService::class)->storeCompressed($file, "operacoes/{$operacao->id}/croquis", 'local');
            $data['ficheiro_pdf_caminho'] = $ficheiroGuardado['path'];
            $data['ficheiro_pdf_nome']    = $ficheiroGuardado['name'] ?? $file->getClientOriginalName();
        }

        if ($request->hasFile('memoria_descritiva')) {
            $file = $request->file('memoria_descritiva');
            $memoriaGuardada = app(PdfCompressionService::class)->storeCompressed($file, "operacoes/{$operacao->id}/croquis/memorias", 'local');
            $data['memoria_descritiva_caminho'] = $memoriaGuardada['path'];
            $data['memoria_descritiva_nome']    = $memoriaGuardada['name'] ?? $file->getClientOriginalName();
        }

        $data['operacao_id'] = $operacao->id;
        $data['criado_por']  = $request->user()->id;
        $data = $this->aplicarDispensaInotu($data, $request->user()->isInotu(), $request->user()->id);

        $croqui = Croqui::create($data);

        // Criar proprietários
        if (! empty($data['proprietarios'])) {
            foreach ($data['proprietarios'] as $prop) {
                $croqui->proprietarios()->create($prop);
            }
        }

        AuditoriaService::criar($croqui);

        $croqui->load(['proprietarios', 'criadoPor', 'ficheiros']);

        return $this->created(new CroquiResource($croqui));
    }

    /**
     * GET /api/v1/croquis/{croqui}
     */
    public function show(Croqui $croqui): JsonResponse
    {
        Gate::authorize('view', $croqui);

        $croqui->load(['proprietarios', 'criadoPor', 'ficheiros']);

        return $this->success(new CroquiResource($croqui));
    }

    /**
     * PUT /api/v1/croquis/{croqui}
     */
    public function update(StoreCroquiRequest $request, Croqui $croqui): JsonResponse
    {
        Gate::authorize('update', $croqui);

        $data = $request->validated();
        $data = $this->resolveCoordinates($data);
        $identidade = app(TerrenoIdentidadeService::class);
        $conflito = $identidade->validarOuFalhar(
            $data['utm_zone'] ?? $croqui->utm_zone ?? '33',
            $data['utm_hemisphere'] ?? $croqui->utm_hemisphere ?? 'S',
            $data['utm_easting'] ?? $croqui->utm_easting,
            $data['utm_northing'] ?? $croqui->utm_northing,
            $croqui->id,
        );

        if ($conflito) {
            return $this->error($conflito['mensagem'], 422, ['codigo_territorial' => $conflito['codigo']]);
        }

        $data = array_merge($data, $identidade->dadosParaCoordenada(
            $data['utm_zone'] ?? $croqui->utm_zone,
            $data['utm_hemisphere'] ?? $croqui->utm_hemisphere,
            $data['utm_easting'] ?? $croqui->utm_easting,
            $data['utm_northing'] ?? $croqui->utm_northing,
        ));

        if ($request->hasFile('ficheiro_pdf')) {
            // Apagar antigo
            if ($croqui->ficheiro_pdf_caminho) {
                Storage::disk('local')->delete($croqui->ficheiro_pdf_caminho);
            }
            $file = $request->file('ficheiro_pdf');
            $ficheiroGuardado = app(PdfCompressionService::class)->storeCompressed($file, "operacoes/{$croqui->operacao_id}/croquis", 'local');
            $data['ficheiro_pdf_caminho'] = $ficheiroGuardado['path'];
            $data['ficheiro_pdf_nome']    = $ficheiroGuardado['name'] ?? $file->getClientOriginalName();
            $data = $this->aplicarDispensaInotu(
                $data,
                $request->user()->isInotu() || $croqui->foiCriadoPorInotu(),
                $request->user()->id
            );
        }

        if ($request->hasFile('memoria_descritiva')) {
            if ($croqui->memoria_descritiva_caminho) {
                Storage::disk('local')->delete($croqui->memoria_descritiva_caminho);
            }
            $file = $request->file('memoria_descritiva');
            $memoriaGuardada = app(PdfCompressionService::class)->storeCompressed($file, "operacoes/{$croqui->operacao_id}/croquis/memorias", 'local');
            $data['memoria_descritiva_caminho'] = $memoriaGuardada['path'];
            $data['memoria_descritiva_nome']    = $memoriaGuardada['name'] ?? $file->getClientOriginalName();
        }

        $croqui->update($data);

        // Sync proprietários (remover e recriar)
        if (array_key_exists('proprietarios', $data)) {
            $croqui->proprietarios()->delete();
            foreach ($data['proprietarios'] ?? [] as $prop) {
                $croqui->proprietarios()->create($prop);
            }
        }

        $croqui->load(['proprietarios', 'criadoPor', 'ficheiros']);

        return $this->success(new CroquiResource($croqui), 'Croqui actualizado.');
    }

    /**
     * DELETE /api/v1/croquis/{croqui}
     */
    public function destroy(Croqui $croqui): JsonResponse
    {
        Gate::authorize('delete', $croqui);

        LixeiraService::enviar($croqui);

        return $this->success(null, 'Croqui enviado para a lixeira.');
    }

    /**
     * Conversão automática: se apenas lat/lng → calcula UTM; se apenas UTM → calcula lat/lng.
     */
    private function resolveCoordinates(array $data): array
    {
        $hasLatLng = ! empty($data['latitude']) && ! empty($data['longitude']);
        $hasUtm    = ! empty($data['utm_easting']) && ! empty($data['utm_northing']) && ! empty($data['utm_zone']);

        if ($hasLatLng && ! $hasUtm) {
            $utm = $this->coordinates->latLngToUtm($data['latitude'], $data['longitude']);
            $data = array_merge($data, $utm);
        } elseif ($hasUtm && ! $hasLatLng) {
            $latLng = $this->coordinates->utmToLatLng(
                $data['utm_zone'],
                $data['utm_hemisphere'] ?? 'south',
                $data['utm_easting'],
                $data['utm_northing'],
            );
            $data = array_merge($data, $latLng);
        }

        return $data;
    }

    private function aplicarDispensaInotu(array $data, bool $dispensa, int $userId): array
    {
        if (! $dispensa) {
            return $data;
        }

        return array_merge($data, [
            'rup_pago' => true,
            'rup_referencia' => 'DISPENSADO-INOTU',
            'rup_pago_em' => now(),
            'download_liberado' => true,
            'liberado_por' => $userId,
            'liberado_em' => now(),
        ]);
    }
}
