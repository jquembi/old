<?php

namespace App\Http\Requests\Api\V1\TerrenoOcioso;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrenoOciosoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('terreno_create');
    }

    public function rules(): array
    {
        return [
            'proprietario' => ['required', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:500'],
            'lote' => ['nullable', 'string', 'max:100'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'area_construida_m2' => ['nullable', 'numeric', 'min:0'],
            'data_levantamento' => ['nullable', 'date'],
            'motivo_ociosidade' => ['required', 'string', 'in:heranca_indefinida,conflito_propriedade,razoes_legais,especulacao,abandono,motivos_financeiros,outros'],
            'tempo_ociosidade_anos' => ['nullable', 'integer', 'min:0'],
            'potencial_uso' => ['nullable', 'string', 'max:255'],
            'prioridade_intervencao' => ['nullable', 'string', 'in:baixa,media,alta,critica'],
            'contacto_proprietario' => ['nullable', 'string', 'max:255'],
            'municipio_id' => ['required', 'integer', 'exists:municipios,id'],
            'geometry' => ['nullable', 'json'],
            'ficheiro' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
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
