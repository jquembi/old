<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConvertCoordinateRequest;
use App\Services\CoordinateService;
use Illuminate\Http\JsonResponse;

class CoordinateController extends Controller
{
    use ApiResponse;
    public function __construct(
        private readonly CoordinateService $coordinates,
    ) {}

    /**
     * POST /api/v1/coordinates/convert
     *
     * Converter coordenadas entre UTM e Lat/Lng.
     * Enviar { format: "latlng", latitude, longitude } para obter UTM.
     * Enviar { format: "utm", utm_zone, utm_hemisphere, utm_easting, utm_northing } para obter lat/lng.
     */
    public function convert(ConvertCoordinateRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['format'] === 'latlng') {
            $result = $this->coordinates->fromLatLng(
                $data['latitude'],
                $data['longitude'],
            );
        } else {
            $result = $this->coordinates->fromUtm(
                $data['utm_zone'],
                $data['utm_hemisphere'],
                $data['utm_easting'],
                $data['utm_northing'],
            );
        }

        return $this->success([
            'coordinates'    => $result,
            'formatted_utm'  => $this->coordinates->formatUtm(
                $result['utm_zone'] ?? $data['utm_zone'],
                $result['utm_hemisphere'] ?? $data['utm_hemisphere'],
                $result['utm_easting'] ?? $data['utm_easting'],
                $result['utm_northing'] ?? $data['utm_northing'],
            ),
        ]);
    }

    /**
     * GET /api/v1/coordinates/default-zone
     * Zona UTM padrão para a Huíla.
     */
    public function defaultZone(): JsonResponse
    {
        return $this->success(CoordinateService::defaultHuilaZone());
    }
}
