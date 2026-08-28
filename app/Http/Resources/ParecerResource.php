<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParecerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'titulo'         => $this->titulo,
            'slug'           => $this->slug,
            'descricao'      => $this->descricao,
            'projecto_nome'  => $this->projecto_nome,
            'servico'        => $this->servico,
            'servico_label'  => $this->servicoLabel(),
            'entidade_requerente' => $this->entidade_requerente,
            'tipo_concessao' => $this->tipo_concessao,
            'classificacao_area' => $this->classificacao_area,
            'uso_solo' => $this->uso_solo,
            'zona_ordenamento' => $this->zona_ordenamento,
            'area_reservada' => $this->area_reservada,
            'area_reservada_label' => $this->areaReservadaLabel(),
            'fundamentacao' => $this->fundamentacao,
            'conclusao' => $this->conclusao,
            'estado'         => $this->estado,
            'ficheiro'       => $this->ficheiro_caminho
                ? route('storage.serve', $this->ficheiro_caminho)
                : null,
            'recurso' => $this->whenLoaded('recurso', fn () => [
                'tipo' => $this->recurso_type ? class_basename($this->recurso_type) : null,
                'id' => $this->recurso_id,
                'titulo' => $this->recurso?->titulo ?? $this->recurso?->nome ?? null,
            ]),
            'municipio' => $this->whenLoaded('municipio', fn () => [
                'id'   => $this->municipio->id,
                'nome' => $this->municipio->nome,
            ]),
            'ficheiros' => OperationFileResource::collection($this->whenLoaded('ficheiros')),
            'criado_por' => $this->whenLoaded('criadoPor', fn () => [
                'id'   => $this->criadoPor->id,
                'nome' => $this->criadoPor->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
