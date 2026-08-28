<?php

namespace App\Http\Requests\Api\V1\SonaPlano;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSonaPlanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('sona_edit');
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'objetivo' => ['nullable', 'string', 'max:5000'],
            'area_intervencao_m2' => ['nullable', 'numeric', 'min:0'],
            'populacao_alvo' => ['nullable', 'integer', 'min:0'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim_prevista' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'data_fim_real' => ['nullable', 'date'],
            'orcamento_total' => ['nullable', 'numeric', 'min:0'],
            'orcamento_gasto' => ['nullable', 'numeric', 'min:0'],
            'percentual_conclusao' => ['nullable', 'integer', 'between:0,100'],
            'status' => ['nullable', 'string', 'max:50'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
