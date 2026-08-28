<?php

namespace App\Livewire;

use App\Models\Municipio;
use App\Models\Croqui;
use Livewire\Component;

class IdsTerreno extends Component
{
    public Municipio $municipio;
    public string $pesquisa = '';
    public bool $pesquisaExecutada = false;
    public string $idTerrenoZona = '33';
    public string $idTerrenoHemisferio = 'S';
    public ?float $idTerrenoEasting = null;
    public ?float $idTerrenoNorthing = null;
    public ?array $idTerrenoResultado = null;

    public function mount(Municipio $municipio)
    {
        $this->municipio = $municipio;
    }

    public function pesquisar(): void
    {
        $this->validate([
            'pesquisa' => ['nullable', 'string', 'max:255'],
        ]);

        $this->pesquisaExecutada = trim($this->pesquisa) !== '';
    }

    public function limparPesquisa(): void
    {
        $this->pesquisa = '';
        $this->pesquisaExecutada = false;
    }

    public function consultarIdTerreno(): void
    {
        $this->validate([
            'idTerrenoZona' => ['required', 'in:32,33,34'],
            'idTerrenoHemisferio' => ['required', 'in:N,S'],
            'idTerrenoEasting' => ['required', 'numeric'],
            'idTerrenoNorthing' => ['required', 'numeric'],
        ]);

        $codigo = sprintf(
            'UTM%s%s-%.3F-%.3F',
            $this->idTerrenoZona,
            $this->idTerrenoHemisferio,
            $this->idTerrenoEasting,
            $this->idTerrenoNorthing
        );

        $sobreposto = $this->baseQuery()
            ->where('utm_zone', $this->idTerrenoZona)
            ->where('utm_hemisphere', $this->idTerrenoHemisferio)
            ->whereNotNull('centro_utm_easting')
            ->whereNotNull('centro_utm_northing')
            ->get()
            ->first(function (Croqui $croqui): bool {
                $dx = (float) $croqui->centro_utm_easting - (float) $this->idTerrenoEasting;
                $dy = (float) $croqui->centro_utm_northing - (float) $this->idTerrenoNorthing;
                $distancia = sqrt(($dx * $dx) + ($dy * $dy));

                return $distancia <= max((float) ($croqui->raio_protecao_m ?? 15), 15.0);
            });

        $this->idTerrenoResultado = [
            'codigo' => $codigo,
            'disponivel' => $sobreposto === null,
            'mensagem' => $sobreposto
                ? "Sobreposicao detectada com {$sobreposto->codigo_territorial}."
                : 'Codigo territorial disponivel para registo.',
        ];
    }

    public function render()
    {
        $query = $this->baseQuery()
            ->whereNotNull('codigo_territorial')
            ->with('proprietarios');

        if ($this->pesquisaExecutada) {
            $termo = trim($this->pesquisa);

            $query->where(function ($q) use ($termo) {
                $q->where('codigo_territorial', 'like', "%{$termo}%")
                    ->orWhere('titulo', 'like', "%{$termo}%")
                    ->orWhere('utm_easting', 'like', "%{$termo}%")
                    ->orWhere('utm_northing', 'like', "%{$termo}%")
                    ->orWhere('centro_utm_easting', 'like', "%{$termo}%")
                    ->orWhere('centro_utm_northing', 'like', "%{$termo}%")
                    ->orWhereHas('proprietarios', function ($proprietarios) use ($termo) {
                        $proprietarios->where('nome', 'like', "%{$termo}%")
                            ->orWhere('bi', 'like', "%{$termo}%")
                            ->orWhere('contacto', 'like', "%{$termo}%");
                    });
            });
        }

        $resultados = $query->latest()->limit($this->pesquisaExecutada ? 100 : 50)->get();

        return view('livewire.ids-terreno', [
            'municipio' => $this->municipio,
            'resultados' => $resultados,
            'pesquisaExecutada' => $this->pesquisaExecutada,
            'idTerrenoResultado' => $this->idTerrenoResultado,
        ]);
    }

    private function baseQuery()
    {
        return Croqui::query()
            ->whereHas('operacao', fn ($query) => $query->where('municipio_id', $this->municipio->id));
    }
}
