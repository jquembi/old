<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ConvertCoordinateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->activo ?? false;
    }

    public function rules(): array
    {
        return [
            'format' => ['required', 'in:utm,latlng'],

            // Se format=latlng → converter de lat/lng para UTM
            'latitude'  => ['required_if:format,latlng', 'numeric', 'between:-90,90'],
            'longitude' => ['required_if:format,latlng', 'numeric', 'between:-180,180'],

            // Se format=utm → converter de UTM para lat/lng
            'utm_zone'       => ['required_if:format,utm', 'string', 'max:5'],
            'utm_hemisphere' => ['required_if:format,utm', 'in:north,south'],
            'utm_easting'    => ['required_if:format,utm', 'numeric', 'between:100000,999999'],
            'utm_northing'   => ['required_if:format,utm', 'numeric', 'between:0,10000000'],
        ];
    }
}
