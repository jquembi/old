<?php

namespace App\Http\Requests\Api\V1\Operacao;

use Illuminate\Foundation\Http\FormRequest;
use App\Shared\Enums\StatusEnum;
use Illuminate\Validation\Rule;

class UpdateOperacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('operacao_edit');
    }

    public function rules(): array
    {
        return [
            'nome' => ['sometimes', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'municipio_id' => ['sometimes', 'integer', 'exists:municipios,id'],
            'status' => ['sometimes', 'string', Rule::in(StatusEnum::all())],
            'tipo_operacao' => ['nullable', 'string', 'max:100'],
            'area_m2' => ['nullable', 'numeric', 'min:0'],
            'beneficiarios_estimados' => ['nullable', 'integer', 'min:0'],
            'orcamento_estimado' => ['nullable', 'numeric', 'min:0'],
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
