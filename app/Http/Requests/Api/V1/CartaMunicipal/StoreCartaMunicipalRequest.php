<?php

namespace App\Http\Requests\Api\V1\CartaMunicipal;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartaMunicipalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('carta_create');
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'tipo' => ['required', 'string', 'in:base,temática,topografica,cadastral,solos'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'escala' => ['nullable', 'string', 'max:50'],
            'versao' => ['nullable', 'string', 'max:50'],
            'formato' => ['nullable', 'string', 'max:50'],
            'autor' => ['nullable', 'string', 'max:255'],
            'projecao_cartografica' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'data_emissao' => ['nullable', 'date'],
            'data_atualizacao' => ['nullable', 'date'],
            'municipio_id' => ['required', 'integer', 'exists:municipios,id'],
            'ficheiro' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,tiff', 'max:20480'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->user()->isTecnicoMunicipal()) {
            $this->merge(['municipio_id' => $this->user()->municipio_id]);
        }
    }
}
