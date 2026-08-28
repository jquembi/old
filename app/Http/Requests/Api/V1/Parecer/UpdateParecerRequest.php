<?php

namespace App\Http\Requests\Api\V1\Parecer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParecerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('parecer_edit');
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'string', 'max:255'],
            'tipo' => ['sometimes', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'numero_parecer' => ['nullable', 'string', 'max:100'],
            'data_parecer' => ['nullable', 'date'],
            'data_expedicao' => ['nullable', 'date'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'parecer_favoravel' => ['nullable', 'boolean'],
            'parecer_desfavoravel' => ['nullable', 'boolean'],
            'parecer_condicional' => ['nullable', 'boolean'],
            'condicoes' => ['nullable', 'string', 'max:5000'],
            'recomendacoes' => ['nullable', 'string', 'max:5000'],
            'referencia_documento' => ['nullable', 'string', 'max:255'],
            'ficheiro' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
