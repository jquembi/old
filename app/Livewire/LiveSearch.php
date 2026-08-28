<?php

namespace App\Livewire;

use App\Models\Instrumento;
use App\Models\Loteamento;
use App\Models\Municipio;
use App\Models\OperacaoUrbanistica;
use App\Models\Parecer;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class LiveSearch extends Component
{
    public string $query = '';

    public function render()
    {
        return view('livewire.live-search', [
            'resultados' => $this->resultados(),
        ]);
    }

    private function resultados(): array
    {
        $termo = trim($this->query);

        if (mb_strlen($termo) < 2) {
            return [];
        }

        return collect()
            ->merge($this->municipios($termo))
            ->merge($this->operacoes($termo))
            ->merge($this->instrumentos($termo))
            ->merge($this->loteamentos($termo))
            ->merge($this->pareceres($termo))
            ->merge($this->usuarios($termo))
            ->take(8)
            ->values()
            ->all();
    }

    private function municipios(string $termo)
    {
        return Municipio::query()
            ->where('nome', 'like', "%{$termo}%")
            ->orderBy('nome')
            ->limit(3)
            ->get()
            ->map(fn (Municipio $municipio): array => [
                'titulo' => $municipio->nome,
                'subtitulo' => 'Municipio',
                'url' => Route::has('municipios.show')
                    ? route('municipios.show', $municipio, absolute: false)
                    : route('municipios.index', absolute: false),
            ]);
    }

    private function operacoes(string $termo)
    {
        return OperacaoUrbanistica::query()
            ->where('titulo', 'like', "%{$termo}%")
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (OperacaoUrbanistica $operacao): array => [
                'titulo' => $operacao->titulo,
                'subtitulo' => 'Operacao urbanistica',
                'url' => Route::has('operacoes.show')
                    ? route('operacoes.show', $operacao, absolute: false)
                    : route('dashboard', absolute: false),
            ]);
    }

    private function instrumentos(string $termo)
    {
        return Instrumento::query()
            ->where('titulo', 'like', "%{$termo}%")
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Instrumento $instrumento): array => [
                'titulo' => $instrumento->titulo,
                'subtitulo' => 'Instrumento',
                'url' => Route::has('instrumentos.show')
                    ? route('instrumentos.show', $instrumento, absolute: false)
                    : route('dashboard', absolute: false),
            ]);
    }

    private function loteamentos(string $termo)
    {
        return Loteamento::query()
            ->where('nome', 'like', "%{$termo}%")
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Loteamento $loteamento): array => [
                'titulo' => $loteamento->nome,
                'subtitulo' => 'Loteamento',
                'url' => Route::has('loteamentos.show')
                    ? route('loteamentos.show', $loteamento, absolute: false)
                    : route('dashboard', absolute: false),
            ]);
    }

    private function pareceres(string $termo)
    {
        return Parecer::query()
            ->where(function ($query) use ($termo): void {
                $query->where('titulo', 'like', "%{$termo}%")
                    ->orWhere('projecto_nome', 'like', "%{$termo}%");
            })
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (Parecer $parecer): array => [
                'titulo' => $parecer->titulo ?: $parecer->projecto_nome,
                'subtitulo' => 'Parecer',
                'url' => Route::has('pareceres.show')
                    ? route('pareceres.show', $parecer, absolute: false)
                    : route('dashboard', absolute: false),
            ]);
    }

    private function usuarios(string $termo)
    {
        return User::query()
            ->where(function ($query) use ($termo): void {
                $query->where('name', 'like', "%{$termo}%")
                    ->orWhere('email', 'like', "%{$termo}%");
            })
            ->orderBy('name')
            ->limit(3)
            ->get()
            ->map(fn (User $user): array => [
                'titulo' => $user->name,
                'subtitulo' => $user->email,
                'url' => Route::has('usuarios.index')
                    ? route('usuarios.index', absolute: false)
                    : route('dashboard', absolute: false),
            ]);
    }
}
