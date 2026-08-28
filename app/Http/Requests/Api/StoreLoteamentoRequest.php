<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoteamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('loteamentos.criar');
    }

    public function rules(): array
    {
        return [
            'municipio_id' => ['required', 'exists:municipios,id'],
            'nome'         => ['required', 'string', 'max:255'],
            'descricao'    => ['nullable', 'string', 'max:5000'],
            'estado'       => ['nullable', 'string', 'max:50'],
            'localizacao'  => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'municipio_id.required' => 'O município é obrigatório.',
            'nome.required'         => 'O nome é obrigatório.',
        ];
    }
}
