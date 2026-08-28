<?php

namespace App\Http\Controllers;

use App\Models\MapFeature;
use App\Models\MapLayer;
use App\Models\Municipio;
use App\Services\QgisParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MapController extends Controller
{
    // ─── Camadas ──────────────────────────────────────────

    /**
     * GET /api/maps/{municipio}/layers
     * Lista todas as camadas do município.
     */
    public function layers(Municipio $municipio): JsonResponse
    {
        $layers = MapLayer::where('municipio_id', $municipio->id)
            ->orderBy('ordem')
            ->withCount('features')
            ->get()
            ->map(fn ($l) => [
                'id'             => $l->id,
                'nome'           => $l->nome,
                'tipo'           => $l->tipo,
                'cor'            => $l->cor,
                'visivel'        => $l->visivel,
                'ordem'          => $l->ordem,
                'features_count' => $l->features_count,
            ]);

        return response()->json($layers);
    }

    /**
     * POST /api/maps/{municipio}/layers
     * Cria ou devolve camada padrão de um tipo.
     */
    public function ensureLayer(Request $request, Municipio $municipio): JsonResponse
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:60',
            'nome' => 'nullable|string|max:120',
            'cor'  => 'nullable|string|max:9',
        ]);

        $layer = MapLayer::obterOuCriar($municipio->id, $data['tipo'], Auth::id());

        if (isset($data['nome'])) {
            $layer->update(['nome' => $data['nome']]);
        }
        if (isset($data['cor'])) {
            $layer->update(['cor' => $data['cor']]);
        }

        return response()->json($layer, 201);
    }

    /**
     * PATCH /api/maps/layers/{layer}
     * Actualiza visibilidade/cor/nome de uma camada.
     */
    public function updateLayer(Request $request, MapLayer $layer): JsonResponse
    {
        $data = $request->validate([
            'nome'    => 'sometimes|string|max:120',
            'cor'     => 'sometimes|string|max:9',
            'visivel' => 'sometimes|boolean',
            'ordem'   => 'sometimes|integer',
        ]);

        $layer->update($data);

        return response()->json($layer);
    }

    // ─── Features ────────────────────────────────────────

    /**
     * GET /api/maps/{municipio}/features
     * Devolve FeatureCollection GeoJSON de todas as features (ou por tipo/camada).
     */
    public function features(Request $request, Municipio $municipio): JsonResponse
    {
        $query = MapFeature::query()
            ->whereHas('layer', fn ($q) => $q->where('municipio_id', $municipio->id))
            ->with('layer');

        if ($request->filled('tipo')) {
            $query->whereHas('layer', fn ($q) => $q->where('tipo', $request->tipo));
        }

        if ($request->filled('layer_id')) {
            $query->where('layer_id', $request->integer('layer_id'));
        }

        $features = $query->get()->map->toGeoJsonFeature();

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    /**
     * GET /api/maps/{municipio}/sync?since=ISO8601
     * Devolve features criadas/actualizadas desde o timestamp indicado,
     * bem como IDs de features eliminadas (soft track via updated_at > since).
     * Usado para polling de colaboração em tempo real.
     */
    public function sync(Request $request, Municipio $municipio): JsonResponse
    {
        $since = $request->input('since');

        $query = MapFeature::query()
            ->whereHas('layer', fn ($q) => $q->where('municipio_id', $municipio->id))
            ->with(['criadoPor:id,name']);

        if ($request->filled('tipo')) {
            $query->whereHas('layer', fn ($q) => $q->where('tipo', $request->tipo));
        }

        if ($since) {
            $query->where('updated_at', '>', $since);
        }

        $features = $query->get();

        $fc = [
            'type'       => 'FeatureCollection',
            'timestamp'  => now()->toIso8601String(),
            'features'   => $features->map(function ($f) {
                $gj = $f->toGeoJsonFeature();
                $gj['properties']['criado_por_nome'] = $f->criadoPor?->name ?? '—';
                $gj['properties']['updated_at'] = $f->updated_at?->toIso8601String();
                return $gj;
            }),
        ];

        return response()->json($fc);
    }

    /**
     * POST /api/maps/{municipio}/features
     * Cria uma nova feature a partir de um GeoJSON Feature.
     */
    public function storeFeature(Request $request, Municipio $municipio): JsonResponse
    {
        $data = $request->validate([
            'layer_id'      => 'required|integer|exists:map_layers,id',
            'geometry'      => 'required|array',
            'geometry.type' => 'required|string|in:Point,LineString,Polygon,MultiPoint,MultiLineString,MultiPolygon',
            'geometry.coordinates' => 'required|array',
            'properties'    => 'nullable|array',
            'area_m2'       => 'nullable|numeric|min:0',
            'croqui_id'     => 'nullable|integer|exists:croquis,id',
        ]);

        // Garante que a camada pertence ao município
        $layer = MapLayer::where('id', $data['layer_id'])
            ->where('municipio_id', $municipio->id)
            ->firstOrFail();

        $areaM2 = $data['area_m2'] ?? null;
        $areaHa = $areaM2 !== null ? round($areaM2 / 10000, 6) : null;

        $feature = MapFeature::create([
            'layer_id'      => $layer->id,
            'croqui_id'     => $data['croqui_id'] ?? null,
            'geometry_type' => $data['geometry']['type'],
            'geometry'      => $data['geometry'],
            'properties'    => $data['properties'] ?? null,
            'area_m2'       => $areaM2,
            'area_ha'       => $areaHa,
            'criado_por'    => Auth::id(),
        ]);

        return response()->json($feature->toGeoJsonFeature(), 201);
    }

    /**
     * PUT /api/maps/features/{feature}
     * Actualiza a geometria e/ou propriedades de uma feature.
     */
    public function updateFeature(Request $request, MapFeature $feature): JsonResponse
    {
        $data = $request->validate([
            'geometry'             => 'sometimes|array',
            'geometry.type'        => 'required_with:geometry|string',
            'geometry.coordinates' => 'required_with:geometry|array',
            'properties'           => 'nullable|array',
            'area_m2'              => 'nullable|numeric|min:0',
            'croqui_id'            => 'nullable|integer|exists:croquis,id',
            'layer_id'             => 'sometimes|integer|exists:map_layers,id',
        ]);

        if (isset($data['geometry'])) {
            $data['geometry_type'] = $data['geometry']['type'];
        }
        if (isset($data['area_m2'])) {
            $data['area_ha'] = round($data['area_m2'] / 10000, 6);
        }

        $feature->update($data);

        return response()->json($feature->fresh()->toGeoJsonFeature());
    }

    /**
     * DELETE /api/maps/features/{feature}
     */
    public function destroyFeature(MapFeature $feature): JsonResponse
    {
        $feature->delete();

        return response()->json(null, 204);
    }

    /**
     * GET /api/maps/{municipio}/export
     * Exporta toda a cartografia do município como GeoJSON.
     */
    public function exportGeoJson(Municipio $municipio): JsonResponse
    {
        $features = MapFeature::query()
            ->whereHas('layer', fn ($q) => $q->where('municipio_id', $municipio->id))
            ->get()
            ->map->toGeoJsonFeature();

        return response()->json([
            'type'     => 'FeatureCollection',
            'crs'      => ['type' => 'name', 'properties' => ['name' => 'EPSG:4326']],
            'features' => $features,
        ]);
    }

    // ─── Informações do Município ──────────────────────────

    /**
     * GET /api/maps/{municipio}/info
     * Devolve centro, bbox e metadados do município para o editor.
     */
    public function municipioInfo(Municipio $municipio): JsonResponse
    {
        return response()->json([
            'id'          => $municipio->id,
            'nome'        => $municipio->nome,
            'codigo'      => $municipio->codigo,
            'provincia'   => $municipio->provincia->nome ?? 'Angola',
            'pais'        => 'Angola',
            'latitude'    => $municipio->latitude,
            'longitude'   => $municipio->longitude,
            'zoom_padrao' => $municipio->zoom_padrao ?? 12,
            'bbox'        => $municipio->bbox,
        ]);
    }

    // ─── Tipos de Terreno ─────────────────────────────────

    /**
     * GET /api/maps/tipos
     */
    public function tipos(): JsonResponse
    {
        return response()->json(MapLayer::tiposDisponiveis());
    }

    // ─── Exportação Multi-Formato ─────────────────────────

    /**
     * GET /api/maps/{municipio}/export/kml
     */
    public function exportKml(Municipio $municipio): Response
    {
        $features = MapFeature::query()
            ->whereHas('layer', fn ($q) => $q->where('municipio_id', $municipio->id))
            ->with('layer')
            ->get();

        $kml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $kml .= '<kml xmlns="http://www.opengis.net/kml/2.2">' . "\n";
        $kml .= '<Document>' . "\n";
        $kml .= '<name>' . e($municipio->nome) . ' — Cartografia INOTU</name>' . "\n";

        $grouped = $features->groupBy('layer_id');
        foreach ($grouped as $layerFeatures) {
            $layer = $layerFeatures->first()->layer;
            $kmlColor = $this->hexToKmlColor($layer->cor ?? '#ff0000', '80');

            $kml .= '<Folder><name>' . e($layer->nome) . '</name>' . "\n";

            foreach ($layerFeatures as $f) {
                $kml .= $this->featureToKml($f, $kmlColor);
            }

            $kml .= '</Folder>' . "\n";
        }

        $kml .= '</Document></kml>';

        return response($kml, 200, [
            'Content-Type'        => 'application/vnd.google-earth.kml+xml',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($municipio->nome) . '_cartografia.kml"',
        ]);
    }

    /**
     * GET /api/maps/{municipio}/export/dxf
     */
    public function exportDxf(Municipio $municipio): Response
    {
        $features = MapFeature::query()
            ->whereHas('layer', fn ($q) => $q->where('municipio_id', $municipio->id))
            ->with('layer')
            ->get();

        $dxf = "0\nSECTION\n2\nHEADER\n0\nENDSEC\n";
        $dxf .= "0\nSECTION\n2\nENTITIES\n";

        foreach ($features as $f) {
            $layerName = Str::slug($f->layer->nome ?? 'default', '_');
            $geom = $f->geometry;
            $type = $f->geometry_type;

            if ($type === 'Point' && isset($geom['coordinates'])) {
                $dxf .= $this->pointToDxf($geom['coordinates'], $layerName);
            } elseif ($type === 'LineString' && isset($geom['coordinates'])) {
                $dxf .= $this->polylineToDxf($geom['coordinates'], $layerName, false);
            } elseif ($type === 'Polygon' && isset($geom['coordinates'][0])) {
                $dxf .= $this->polylineToDxf($geom['coordinates'][0], $layerName, true);
            } elseif ($type === 'MultiPolygon' && isset($geom['coordinates'])) {
                foreach ($geom['coordinates'] as $polygon) {
                    if (isset($polygon[0])) {
                        $dxf .= $this->polylineToDxf($polygon[0], $layerName, true);
                    }
                }
            }
        }

        $dxf .= "0\nENDSEC\n0\nEOF\n";

        return response($dxf, 200, [
            'Content-Type'        => 'application/dxf',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($municipio->nome) . '_cartografia.dxf"',
        ]);
    }

    /**
     * GET /api/maps/{municipio}/export/csv
     */
    public function exportCsv(Municipio $municipio): Response
    {
        $features = MapFeature::query()
            ->whereHas('layer', fn ($q) => $q->where('municipio_id', $municipio->id))
            ->with('layer')
            ->get();

        $csv = "ID,Camada,Tipo_Geometria,Longitude,Latitude,Area_m2,Area_ha,Proprietario,Observacoes\n";

        foreach ($features as $f) {
            $props = $f->properties ?? [];
            $centroid = $this->geometryCentroid($f->geometry);

            $csv .= implode(',', [
                $f->id,
                '"' . str_replace('"', '""', $f->layer->nome ?? '') . '"',
                $f->geometry_type,
                $centroid['lon'] ?? '',
                $centroid['lat'] ?? '',
                $f->area_m2 ?? '',
                $f->area_ha ?? '',
                '"' . str_replace('"', '""', $props['proprietario'] ?? '') . '"',
                '"' . str_replace('"', '""', $props['observacoes'] ?? '') . '"',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . Str::slug($municipio->nome) . '_cartografia.csv"',
        ]);
    }

    // ─── Helpers de exportação ────────────────────────────

    private function featureToKml(MapFeature $f, string $kmlColor): string
    {
        $props = $f->properties ?? [];
        $name = $props['proprietario'] ?? ($f->geometry_type . ' #' . $f->id);
        $geom = $f->geometry;
        $type = $f->geometry_type;

        $kml  = '<Placemark>' . "\n";
        $kml .= '<name>' . e($name) . '</name>' . "\n";
        $kml .= '<Style><LineStyle><color>' . $kmlColor . '</color><width>2</width></LineStyle>';
        $kml .= '<PolyStyle><color>' . $kmlColor . '</color></PolyStyle></Style>' . "\n";

        if ($type === 'Point' && isset($geom['coordinates'])) {
            $c = $geom['coordinates'];
            $kml .= '<Point><coordinates>' . $c[0] . ',' . $c[1] . ',0</coordinates></Point>' . "\n";
        } elseif ($type === 'LineString' && isset($geom['coordinates'])) {
            $kml .= '<LineString><coordinates>' . $this->coordsToKml($geom['coordinates']) . '</coordinates></LineString>' . "\n";
        } elseif ($type === 'Polygon' && isset($geom['coordinates'][0])) {
            $kml .= '<Polygon><outerBoundaryIs><LinearRing><coordinates>' . $this->coordsToKml($geom['coordinates'][0]) . '</coordinates></LinearRing></outerBoundaryIs></Polygon>' . "\n";
        } elseif ($type === 'MultiPolygon' && isset($geom['coordinates'])) {
            $kml .= '<MultiGeometry>' . "\n";
            foreach ($geom['coordinates'] as $polygon) {
                if (isset($polygon[0])) {
                    $kml .= '<Polygon><outerBoundaryIs><LinearRing><coordinates>' . $this->coordsToKml($polygon[0]) . '</coordinates></LinearRing></outerBoundaryIs></Polygon>' . "\n";
                }
            }
            $kml .= '</MultiGeometry>' . "\n";
        }

        $kml .= '</Placemark>' . "\n";
        return $kml;
    }

    private function coordsToKml(array $coords): string
    {
        return implode(' ', array_map(fn ($c) => $c[0] . ',' . $c[1] . ',0', $coords));
    }

    private function hexToKmlColor(string $hex, string $alpha = 'ff'): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = substr($hex, 0, 2);
        $g = substr($hex, 2, 2);
        $b = substr($hex, 4, 2);
        return $alpha . $b . $g . $r; // KML: aabbggrr
    }

    private function pointToDxf(array $coords, string $layer): string
    {
        return "0\nPOINT\n8\n{$layer}\n10\n{$coords[0]}\n20\n{$coords[1]}\n30\n0.0\n";
    }

    private function polylineToDxf(array $coords, string $layer, bool $closed): string
    {
        $n = count($coords);
        $flag = $closed ? 1 : 0;
        $dxf = "0\nLWPOLYLINE\n8\n{$layer}\n90\n{$n}\n70\n{$flag}\n";
        foreach ($coords as $c) {
            $dxf .= "10\n{$c[0]}\n20\n{$c[1]}\n";
        }
        return $dxf;
    }

    private function geometryCentroid(array $geometry): array
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];

        if ($type === 'Point') {
            return ['lon' => $coords[0] ?? null, 'lat' => $coords[1] ?? null];
        }

        // Flatten all coordinates and average
        $flat = $this->flattenCoords($coords);
        if (empty($flat)) return ['lon' => null, 'lat' => null];

        $sumLon = $sumLat = 0;
        foreach ($flat as $c) {
            $sumLon += $c[0];
            $sumLat += $c[1];
        }
        $n = count($flat);
        return ['lon' => round($sumLon / $n, 7), 'lat' => round($sumLat / $n, 7)];
    }

    private function flattenCoords($arr): array
    {
        if (empty($arr)) return [];
        if (is_numeric($arr[0] ?? null)) return [$arr];
        $result = [];
        foreach ($arr as $item) {
            $result = array_merge($result, $this->flattenCoords($item));
        }
        return $result;
    }

    // ─── QGIS ─────────────────────────────────────────────

    /**
     * POST /api/maps/parse-qgis
     * Recebe upload de .qgs/.qgz e devolve lista de camadas e extent.
     */
    public function parseQgis(Request $request): JsonResponse
    {
        $request->validate([
            'arquivo' => 'required|file|mimes:qgs,qgz,xml,zip|max:51200',
        ]);

        $path   = $request->file('arquivo')->store('temp/qgis', 'local');
        $parser = app(QgisParserService::class);

        try {
            $result = $parser->parse(storage_path("app/private/{$path}"));
        } finally {
            Storage::disk('local')->delete($path);
        }

        return response()->json($result);
    }
}
