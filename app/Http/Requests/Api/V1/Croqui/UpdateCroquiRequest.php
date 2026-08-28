<?php

namespace App\Http\Requests\Api\V1\Croqui;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCroquiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('croqui_edit');
    }

    public function rules(): array
    {
        return [
            'proprietario' => ['sometimes', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'lote' => ['nullable', 'string', 'max:100'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'condicao' => ['sometimes', 'string', 'in:legal,ilegal,em_conflito,em_regularizacao'],
            'data_levantamento' => ['nullable', 'date'],
            'escala' => ['nullable', 'string', 'max:50'],
            'sistema_referencia' => ['nullable', 'string', 'max:100'],
            'geometry' => ['nullable', 'json'],
            'ficheiro' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'coordenadas' => ['nullable', 'array'],
            'coordenadas.*.latitude' => ['required_with:coordenadas', 'numeric', 'between:-90,90'],
            'coordenadas.*.longitude' => ['required_with:coordenadas', 'numeric', 'between:-180,180'],
            'coordenadas.*.descricao' => ['nullable', 'string', 'max:255'],
        ];
    }
}
