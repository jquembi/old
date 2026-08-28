<?php

namespace App\Http\Requests\Api\V1\Croqui;

use Illuminate\Foundation\Http\FormRequest;

class StoreCroquiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('croqui_create');
    }

    public function rules(): array
    {
        return [
            'proprietario' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'lote' => ['nullable', 'string', 'max:100'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'condicao' => ['required', 'string', 'in:legal,ilegal,em_conflito,em_regularizacao'],
            'data_levantamento' => ['nullable', 'date'],
            'escala' => ['nullable', 'string', 'max:50'],
            'sistema_referencia' => ['nullable', 'string', 'max:100'],
            'municipio_id' => ['required', 'integer', 'exists:municipios,id'],
            'geometry' => ['nullable', 'json'],
            'ficheiro' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'coordenadas' => ['nullable', 'array'],
            'coordenadas.*.latitude' => ['required_with:coordenadas', 'numeric', 'between:-90,90'],
            'coordenadas.*.longitude' => ['required_with:coordenadas', 'numeric', 'between:-180,180'],
            'coordenadas.*.descricao' => ['nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()->isTecnicoMunicipal()) {
            $this->merge(['municipio_id' => $this->user()->municipio_id]);
        }
    }

    public function messages(): array
    {
        return [
            'ficheiro.max' => 'O ficheiro PDF não pode exceder 20MB.',
            'ficheiro.mimes' => 'O ficheiro deve ser um PDF.',
            'condicao.in' => 'Condição deve ser: legal, ilegal, em_conflito ou em_regularizacao.',
        ];
    }
}
