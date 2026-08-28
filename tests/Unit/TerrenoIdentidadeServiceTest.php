<?php

use App\Services\TerrenoIdentidadeService;

test('gera codigo territorial a partir do centro utm', function () {
    $servico = new TerrenoIdentidadeService();

    expect($servico->codigo('33', 'S', 431234.49, 9123456.51))
        ->toBe('CT-33S-E0431234-N9123457');
});

test('calcula distancia utm em metros', function () {
    $servico = new TerrenoIdentidadeService();

    expect(round($servico->distanciaMetros(100000, 200000, 100009, 200012), 3))
        ->toBe(15.0);
});
