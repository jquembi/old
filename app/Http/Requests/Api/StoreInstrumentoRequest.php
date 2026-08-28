<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreInstrumentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('instrumentos.criar');
    }

    public function rules(): array
    {
        return [
            'municipio_id'        => ['required', 'exists:municipios,id'],
            'tipo_instrumento_id' => ['required', 'exists:tipo_instrumentos,id'],
            'titulo'              => ['required', 'string', 'max:255'],
            'descricao'           => ['nullable', 'string', 'max:5000'],
            'estado'              => ['nullable', 'string', 'max:50'],
            'ano'                 => ['nullable', 'integer', 'min:1975', 'max:' . (date('Y') + 5)],
        ];
    }

    public function messages(): array
    {
        return [
            'municipio_id.required'        => 'O município é obrigatório.',
            'tipo_instrumento_id.required'  => 'O tipo de instrumento é obrigatório.',
            'titulo.required'              => 'O título é obrigatório.',
        ];
    }
}
