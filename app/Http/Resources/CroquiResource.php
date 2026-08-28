<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CroquiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id'         => $this->id,
            'titulo'     => $this->titulo,
            'slug'       => $this->slug,
            'tipo'       => $this->tipo,
            'condicao'   => $this->condicao,

            'coordenadas' => [
                'latitude'       => $this->latitude,
                'longitude'      => $this->longitude,
                'utm_zone'       => $this->utm_zone,
                'utm_hemisphere' => $this->utm_hemisphere,
                'utm_easting'    => $this->utm_easting,
                'utm_northing'   => $this->utm_northing,
                'codigo_territorial' => $this->codigo_territorial,
                'centro_utm_easting' => $this->centro_utm_easting,
                'centro_utm_northing' => $this->centro_utm_northing,
                'raio_protecao_m' => $this->raio_protecao_m,
                'texto'          => $this->coordenadas_texto,
            ],

            'observacoes'   => $this->observacoes,
            'ficheiro_pdf'  => ($this->temFicheiroPdfValido() && $this->podeBaixar($user))
                ? route('storage.serve', $this->ficheiro_pdf_caminho)
                : null,
            'memoria_descritiva' => ($this->temMemoriaDescritivaValida() && $this->podeBaixar($user))
                ? route('storage.serve', $this->memoria_descritiva_caminho)
                : null,
            'rup' => [
                'pago' => (bool) $this->rup_pago,
                'referencia' => $this->rup_referencia,
                'pago_em' => $this->rup_pago_em?->toIso8601String(),
            ],
            'homologacao' => [
                'download_liberado' => (bool) $this->download_liberado,
                'liberado_em' => $this->liberado_em?->toIso8601String(),
            ],

            'proprietarios' => $this->whenLoaded('proprietarios', fn () =>
                $this->proprietarios->map(fn ($p) => [
                    'id'       => $p->id,
                    'nome'     => $p->nome,
                    'bi'       => $p->bi,
                    'contacto' => $p->contacto,
                ])
            ),

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
