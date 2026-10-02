<?php

namespace App\Livewire;

use App\Models\Auditoria;
use App\Models\LogAcesso;
use Livewire\Component;
use Livewire\WithPagination;

class GestaoAuditoria extends Component
{
    use WithPagination;

    public string $search = '';
    public string $tab = 'logs';
    public ?string $dataInicio = null;
    public ?string $dataFim = null;
    public ?string $filtroAccao = null;

    public function mount($tab = 'logs')
    {
        $this->tab = $tab;
    }

    public function render()
    {
        $logs = LogAcesso::query()->with('user')->latest();
        $auditorias = Auditoria::query()->where('id', 0)->paginate(25);

        if ($this->tab === 'logs') {
            if ($this->search) {
                $logs->where(function ($query) {
                    $query->where('accao', 'like', '%' . $this->search . '%')
                        ->orWhere('recurso', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            }

            if ($this->dataInicio) {
                $logs->where('created_at', '>=', $this->dataInicio);
            }

            if ($this->dataFim) {
                $logs->where('created_at', '<=', $this->dataFim);
            }

            $logs = $logs->paginate(25);
        } else {
            $logs = LogAcesso::query()->where('id', 0)->paginate(25);
        }

        if ($this->tab === 'auditoria') {
            $query = Auditoria::with('user')->latest();

            if ($this->search) {
                $query->where('acao', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            }

            if ($this->filtroAccao) {
                $query->where('acao', $this->filtroAccao);
            }

            if ($this->dataInicio) {
                $query->where('created_at', '>=', $this->dataInicio);
            }

            if ($this->dataFim) {
                $query->where('created_at', '<=', $this->dataFim);
            }

            $auditorias = $query->paginate(25);
        }

        return view('livewire.gestao-auditoria', [
            'logs' => $logs,
            'auditorias' => $auditorias,
            'tab' => $this->tab,
        ]);
    }
}
