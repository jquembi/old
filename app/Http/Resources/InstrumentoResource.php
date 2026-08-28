<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstrumentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'titulo'      => $this->titulo,
            'descricao'   => $this->descricao,
            'estado'      => $this->estado,
            'ano'         => $this->ano,
            'tipo'        => $this->whenLoaded('tipoInstrumento', fn () => [
                'id'   => $this->tipoInstrumento->id,
                'nome' => $this->tipoInstrumento->nome,
                'categoria' => $this->tipoInstrumento->categoriaInstrumento?->nome ?? null,
            ]),
            'municipio' => $this->whenLoaded('municipio', fn () => [
                'id'   => $this->municipio->id,
                'nome' => $this->municipio->nome,
            ]),
            'ficheiros'  => $this->whenLoaded('ficheiros', fn () =>
                $this->ficheiros->map(fn ($f) => [
                    'id'            => $f->id,
                    'nome_original' => $f->nome_original,
                    'tipo'          => $f->tipo,
                    'tamanho'       => $f->tamanho,
                    'url'           => $f->caminho ? route('storage.serve', $f->caminho) : null,
                ])
            ),
            'criado_por' => $this->whenLoaded('criadoPor', fn () => [
                'id'   => $this->criadoPor->id,
                'nome' => $this->criadoPor->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
