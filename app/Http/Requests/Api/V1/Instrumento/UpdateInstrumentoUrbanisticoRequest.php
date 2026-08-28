<?php

namespace App\Http\Requests\Api\V1\Instrumento;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstrumentoUrbanisticoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('instrumento_edit');
    }

    public function rules(): array
    {
        return [
            'titulo' => ['sometimes', 'string', 'max:255'],
            'tipo' => ['sometimes', 'string', 'max:100'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'numero_documento' => ['nullable', 'string', 'max:100'],
            'prioridade' => ['nullable', 'string', 'in:baixa,media,alta,critica'],
            'status' => ['nullable', 'string', 'max:50'],
            'area_abrangencia_km2' => ['nullable', 'numeric', 'min:0'],
            'beneficiarios_estimados' => ['nullable', 'integer', 'min:0'],
            'investimento_estimado' => ['nullable', 'numeric', 'min:0'],
            'data_criacao' => ['nullable', 'date'],
            'data_aprovacao' => ['nullable', 'date'],
            'data_publicacao' => ['nullable', 'date'],
            'ficheiro' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'observacoes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
