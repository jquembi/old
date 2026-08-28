<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreParecerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('pareceres.criar');
    }

    public function rules(): array
    {
        return [
            'municipio_id'   => ['required', 'exists:municipios,id'],
            'titulo'         => ['required', 'string', 'max:255'],
            'descricao'      => ['nullable', 'string', 'max:5000'],
            'projecto_nome'  => ['nullable', 'string', 'max:255'],
            'recurso_type'   => ['nullable', 'string', 'max:255'],
            'recurso_id'     => ['nullable', 'integer'],
            'servico'        => ['nullable', 'string', 'max:80'],
            'entidade_requerente' => ['nullable', 'string', 'max:255'],
            'tipo_concessao' => ['nullable', 'string', 'max:255'],
            'classificacao_area' => ['nullable', 'string', 'max:255'],
            'uso_solo'       => ['nullable', 'string', 'max:255'],
            'zona_ordenamento' => ['nullable', 'string', 'max:255'],
            'area_reservada' => ['nullable', 'boolean'],
            'fundamentacao'  => ['nullable', 'string', 'max:10000'],
            'conclusao'      => ['nullable', 'string', 'max:10000'],
            'estado'         => ['nullable', 'in:pendente,aprovado,rejeitado,em_analise'],
            'ficheiro'       => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'municipio_id.required' => 'O município é obrigatório.',
            'titulo.required'       => 'O título é obrigatório.',
            'ficheiro.max'          => 'O ficheiro não pode exceder 50 MB.',
        ];
    }
}
