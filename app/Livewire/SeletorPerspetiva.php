<?php

namespace App\Livewire;

use App\Models\Provincia;
use App\Services\PerspetivaService;
use Livewire\Attributes\On;
use Livewire\Component;

class SeletorPerspetiva extends Component
{
    public string $modo = PerspetivaService::MODO_GLOBAL;
    public ?int $provinciaId = null;

    public function mount(PerspetivaService $service): void
    {
        $this->atualizarEstado($service);
    }

    #[On('perspetiva-alterada')]
    public function atualizarEstado(?PerspetivaService $service = null): void
    {
        $service = $service ?? app(PerspetivaService::class);
        $this->modo = $service->getModo();
        $this->provinciaId = $service->getProvinciaId();
    }

    public function selecionarModo(string $novoModo, ?int $provId = null): void
    {
        $service = app(PerspetivaService::class);

        if (! $service->isSuperAdmin()) {
            return;
        }

        $service->definirModo($novoModo, $provId);
        $this->atualizarEstado($service);

        $msg = match ($novoModo) {
            PerspetivaService::MODO_NACIONAL => 'Perspetiva alterada para Visão Nacional (Núcleo Central).',
            PerspetivaService::MODO_PROVINCIAL => 'Perspetiva alterada para ' . ($service->getProvincia()?->nome ? "Província de {$service->getProvincia()->nome}" : 'Provincial') . '.',
            default => 'Perspetiva alterada para Visão Global (Todas as Províncias).',
        };

        $this->dispatch('toast', type: 'info', message: $msg);
        $this->dispatch('perspetiva-alterada', modo: $novoModo, provinciaId: $this->provinciaId);

        $this->redirect(route($service->rotaInicial()), navigate: true);
    }

    public function selecionarProvincia(int $id): void
    {
        $this->selecionarModo(PerspetivaService::MODO_PROVINCIAL, $id);
    }

    public function render(PerspetivaService $service)
    {
        $user = auth()->user();
        $isSuperAdmin = $user && $service->isSuperAdmin($user);

        return view('livewire.seletor-perspetiva', [
            'isSuperAdmin' => $isSuperAdmin,
            'labelAtual' => $service->label(),
            'iconeAtual' => $service->icone(),
            'provinciaAtual' => $service->getProvincia(),
            'provincias' => $service->obterProvincias(),
        ]);
    }
}
