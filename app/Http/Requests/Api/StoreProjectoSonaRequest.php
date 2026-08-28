<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectoSonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('sona.criar');
    }

    public function rules(): array
    {
        return [
            'municipio_id' => ['required', 'exists:municipios,id'],
            'titulo'       => ['required', 'string', 'max:255'],
            'descricao'    => ['nullable', 'string', 'max:5000'],
            'tipo'         => ['nullable', 'string', 'max:100'],
            'fase'         => ['nullable', 'string', 'max:100'],
            'estado'       => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'municipio_id.required' => 'O município é obrigatório.',
            'titulo.required'       => 'O título é obrigatório.',
        ];
    }
}
