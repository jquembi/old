<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCroquiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->activo && $this->user()->temPermissao('operacoes.criar');
    }

    public function rules(): array
    {
        return [
            'tipo'        => ['required', 'in:elaborado,terreno_ocioso'],
            'titulo'      => ['required', 'string', 'max:255'],
            'condicao'    => ['nullable', 'in:legal,ilegal,em_conflito'],

            // Coordenadas geográficas
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],

            // Coordenadas UTM (se fornecidas, serão convertidas)
            'utm_zone'       => ['nullable', 'string', 'max:5'],
            'utm_hemisphere' => ['nullable', 'in:north,south'],
            'utm_easting'    => ['nullable', 'numeric', 'between:100000,999999'],
            'utm_northing'   => ['nullable', 'numeric', 'between:0,10000000'],

            'coordenadas_texto' => ['nullable', 'string', 'max:500'],
            'observacoes'       => ['nullable', 'string', 'max:5000'],
            'ficheiro_pdf'      => ['nullable', 'file', 'mimes:pdf,docx', 'max:20480'],
            'memoria_descritiva' => ['nullable', 'file', 'mimes:pdf,docx', 'max:20480'],

            // Proprietários
            'proprietarios'             => ['nullable', 'array', 'max:10'],
            'proprietarios.*.nome'      => ['required_with:proprietarios', 'string', 'max:255'],
            'proprietarios.*.bi'        => ['nullable', 'string', 'max:50'],
            'proprietarios.*.contacto'  => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'tipo.required'       => 'O tipo de croqui é obrigatório.',
            'titulo.required'     => 'O título é obrigatório.',
            'ficheiro_pdf.max'    => 'O ficheiro nao pode exceder 20 MB.',
            'ficheiro_pdf.mimes' => 'O ficheiro deve ser PDF ou Word (.docx).',
            'memoria_descritiva.mimes' => 'A memoria descritiva deve ser PDF ou Word (.docx).',
            'memoria_descritiva.max' => 'A memoria descritiva nao pode exceder 20 MB.',
            'utm_easting.between' => 'O easting UTM deve estar entre 100.000 e 999.999.',
        ];
    }
}
