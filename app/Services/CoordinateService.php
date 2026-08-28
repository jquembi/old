<?php

namespace App\Services;

class CoordinateService
{
    public function fromLatLng(float $latitude, float $longitude): array
    {
        return $this->latLngToUtm($latitude, $longitude);
    }

    public function latLngToUtm(float $latitude, float $longitude): array
    {
        $zone = (int) floor(($longitude + 180) / 6) + 1;
        $hemisphere = $latitude < 0 ? 'south' : 'north';
        $latRad = deg2rad($latitude);
        $lonRad = deg2rad($longitude);
        $lonOrigin = deg2rad(($zone - 1) * 6 - 180 + 3);

        $a = 6378137.0;
        $eccSquared = 0.00669438;
        $k0 = 0.9996;
        $eccPrimeSquared = $eccSquared / (1 - $eccSquared);

        $n = $a / sqrt(1 - $eccSquared * sin($latRad) ** 2);
        $t = tan($latRad) ** 2;
        $c = $eccPrimeSquared * cos($latRad) ** 2;
        $aa = cos($latRad) * ($lonRad - $lonOrigin);

        $m = $a * ((1 - $eccSquared / 4 - 3 * $eccSquared ** 2 / 64 - 5 * $eccSquared ** 3 / 256) * $latRad
            - (3 * $eccSquared / 8 + 3 * $eccSquared ** 2 / 32 + 45 * $eccSquared ** 3 / 1024) * sin(2 * $latRad)
            + (15 * $eccSquared ** 2 / 256 + 45 * $eccSquared ** 3 / 1024) * sin(4 * $latRad)
            - (35 * $eccSquared ** 3 / 3072) * sin(6 * $latRad));

        $easting = $k0 * $n * ($aa + (1 - $t + $c) * $aa ** 3 / 6
            + (5 - 18 * $t + $t ** 2 + 72 * $c - 58 * $eccPrimeSquared) * $aa ** 5 / 120) + 500000;

        $northing = $k0 * ($m + $n * tan($latRad) * ($aa ** 2 / 2
            + (5 - $t + 9 * $c + 4 * $c ** 2) * $aa ** 4 / 24
            + (61 - 58 * $t + $t ** 2 + 600 * $c - 330 * $eccPrimeSquared) * $aa ** 6 / 720));

        if ($latitude < 0) {
            $northing += 10000000;
        }

        return [
            'utm_zone' => (string) $zone,
            'utm_hemisphere' => $hemisphere,
            'utm_easting' => round($easting, 3),
            'utm_northing' => round($northing, 3),
        ];
    }

    public function fromUtm(string $zone, string $hemisphere, float $easting, float $northing): array
    {
        $a = 6378137.0;
        $eccSquared = 0.00669438;
        $k0 = 0.9996;
        $eccPrimeSquared = $eccSquared / (1 - $eccSquared);
        $e1 = (1 - sqrt(1 - $eccSquared)) / (1 + sqrt(1 - $eccSquared));

        $x = $easting - 500000.0;
        $y = strtolower($hemisphere) === 'south' ? $northing - 10000000.0 : $northing;
        $lonOrigin = ((int) $zone - 1) * 6 - 180 + 3;

        $m = $y / $k0;
        $mu = $m / ($a * (1 - $eccSquared / 4 - 3 * $eccSquared ** 2 / 64 - 5 * $eccSquared ** 3 / 256));

        $phi1Rad = $mu
            + (3 * $e1 / 2 - 27 * $e1 ** 3 / 32) * sin(2 * $mu)
            + (21 * $e1 ** 2 / 16 - 55 * $e1 ** 4 / 32) * sin(4 * $mu)
            + (151 * $e1 ** 3 / 96) * sin(6 * $mu);

        $n1 = $a / sqrt(1 - $eccSquared * sin($phi1Rad) ** 2);
        $t1 = tan($phi1Rad) ** 2;
        $c1 = $eccPrimeSquared * cos($phi1Rad) ** 2;
        $r1 = $a * (1 - $eccSquared) / ((1 - $eccSquared * sin($phi1Rad) ** 2) ** 1.5);
        $d = $x / ($n1 * $k0);

        $lat = $phi1Rad - ($n1 * tan($phi1Rad) / $r1) * ($d ** 2 / 2
            - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 ** 2 - 9 * $eccPrimeSquared) * $d ** 4 / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 ** 2 - 252 * $eccPrimeSquared - 3 * $c1 ** 2) * $d ** 6 / 720);

        $lon = deg2rad($lonOrigin) + ($d - (1 + 2 * $t1 + $c1) * $d ** 3 / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 ** 2 + 8 * $eccPrimeSquared + 24 * $t1 ** 2) * $d ** 5 / 120) / cos($phi1Rad);

        return [
            'latitude' => round(rad2deg($lat), 7),
            'longitude' => round(rad2deg($lon), 7),
        ];
    }

    public function formatUtm(string $zone, string $hemisphere, float $easting, float $northing): string
    {
        return sprintf('%s%s %.3f %.3f', $zone, strtolower($hemisphere) === 'south' ? 'S' : 'N', $easting, $northing);
    }

    public static function defaultHuilaZone(): array
    {
        return [
            'utm_zone' => '33',
            'utm_hemisphere' => 'south',
        ];
    }
}
