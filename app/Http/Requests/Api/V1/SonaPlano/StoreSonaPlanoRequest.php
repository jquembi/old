<?php

namespace App\Http\Requests\Api\V1\SonaPlano;

use Illuminate\Foundation\Http\FormRequest;

class StoreSonaPlanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('sona_create');
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'objetivo' => ['nullable', 'string', 'max:5000'],
            'area_intervencao_m2' => ['nullable', 'numeric', 'min:0'],
            'populacao_alvo' => ['nullable', 'integer', 'min:0'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim_prevista' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'orcamento_total' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:50'],
            'municipio_id' => ['required', 'integer', 'exists:municipios,id'],
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
