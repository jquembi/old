<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\OperationFile;

class StoreOperationFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('operacoes.criar');
    }

    public function rules(): array
    {
        $allowedExtensions = implode(',', array_keys(OperationFile::EXTENSION_MAP));

        return [
            'ficheiro'   => ['required', 'file', 'max:51200', 'mimes:' . $allowedExtensions . ',dwg,qgz,qgs'],
            'croqui_id'  => ['nullable', 'exists:croquis,id'],
            'parecer_id' => ['nullable', 'exists:pareceres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'ficheiro.required' => 'O ficheiro é obrigatório.',
            'ficheiro.max'      => 'O ficheiro não pode exceder 50 MB.',
        ];
    }
}
