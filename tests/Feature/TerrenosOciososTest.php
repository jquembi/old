<?php

use App\Livewire\TerrenosOciosos;
use App\Models\Municipio;
use App\Models\Provincia;
use Livewire\Livewire;

it('renderiza a lista vazia de terrenos ociosos sem variaveis em falta', function () {
    $provincia = Provincia::create([
        'nome' => 'Huíla',
        'codigo' => 'HUI',
        'activo' => true,
    ]);
    $municipio = Municipio::create([
        'nome' => 'Caconda',
        'codigo' => 'HUI-CCD',
        'provincia_id' => $provincia->id,
        'activo' => true,
    ]);

    Livewire::test(TerrenosOciosos::class, ['municipio' => $municipio])
        ->assertSee('Terrenos Ociosos')
        ->assertSee('Nenhum terreno ocioso encontrado');
});
