<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperacaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('operacoes.criar');
    }

    public function rules(): array
    {
        return [
            'municipio_id'  => ['required', 'exists:municipios,id'],
            'titulo'        => ['required', 'string', 'max:255'],
            'descricao'     => ['nullable', 'string', 'max:5000'],
            'ficheiro_base' => ['nullable', 'file', 'mimes:pdf,qgz,qgs,dwg', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'municipio_id.required' => 'O município é obrigatório.',
            'titulo.required'       => 'O título é obrigatório.',
            'ficheiro_base.max'     => 'O ficheiro não pode exceder 50 MB.',
        ];
    }
}
