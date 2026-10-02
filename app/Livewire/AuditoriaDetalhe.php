<?php

namespace App\Livewire;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AuditoriaDetalhe extends Component
{
    public Auditoria $auditoria;

    public function mount(Auditoria $auditoria): void
    {
        $user = Auth::user();
        if (!$user || (!$user->temPermissao('auditoria.ver') && !$user->isNacional() && !$user->isInotu())) {
            abort(403, 'Não tem permissão para aceder aos registos de auditoria.');
        }

        $this->auditoria = $auditoria->load(['user.roles']);
    }

    public function render()
    {
        $antigos = (array) ($this->auditoria->valores_antigos ?? []);
        $novos = (array) ($this->auditoria->valores_novos ?? []);
        $contexto = (array) ($this->auditoria->contexto ?? []);

        $chaves = array_unique(array_merge(array_keys($antigos), array_keys($novos)));
        $camposAlterados = [];
        foreach ($chaves as $chave) {
            $vAntigo = $antigos[$chave] ?? null;
            $vNovo = $novos[$chave] ?? null;
            if ($vAntigo !== $vNovo) {
                $camposAlterados[] = [
                    'campo' => $chave,
                    'antigo' => $vAntigo,
                    'novo' => $vNovo,
                ];
            }
        }

        $resumo = [
            'campos_alterados' => count($camposAlterados),
            'campos_antes' => count($antigos),
            'campos_depois' => count($novos),
        ];

        return view('livewire.auditoria-detalhe', [
            'auditoria' => $this->auditoria,
            'resumo' => $resumo,
            'contexto' => $contexto,
            'camposAlterados' => $camposAlterados,
        ]);
    }
}
