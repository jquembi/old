<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipioStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'slug' => $this->slug,
            'stats' => [
                'total_operacoes' => $this->operacoes_count ?? 0,
                'total_croquis' => $this->croquis_count ?? 0,
                'total_terrenos_ociosos' => $this->terrenos_ociosos_count ?? 0,
                'total_pareceres' => $this->pareceres_count ?? 0,
                'total_instrumentos_ordenamento' => $this->instrumentos_ordenamento_count ?? 0,
                'total_instrumentos_urbanisticos' => $this->instrumentos_urbanisticos_count ?? 0,
                'total_cartas_municipais' => $this->cartas_municipais_count ?? 0,
                'total_users' => $this->users_count ?? 0,
                'total_sona_planos' => $this->sona_planos_count ?? 0,
            ],
        ];
    }
}
