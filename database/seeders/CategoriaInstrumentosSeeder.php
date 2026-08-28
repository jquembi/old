<?php

namespace Database\Seeders;

use App\Models\CategoriaInstrumento;
use App\Models\TipoInstrumento;
use Illuminate\Database\Seeder;

class CategoriaInstrumentosSeeder extends Seeder
{
    public function run(): void
    {
        // 1.1 Instrumentos de Ordenamento do Território
        $ordenamento = CategoriaInstrumento::create([
            'nome' => 'Instrumentos de Ordenamento do Território',
            'slug' => 'ordenamento-territorio',
            'descricao' => 'Instrumentos de planeamento e ordenamento do território municipal',
            'ordem' => 1,
        ]);

        $tiposOrdenamento = [
            ['nome' => 'Plano Municipal de Ordenamento do Território', 'slug' => 'pmot', 'ordem' => 1],
            ['nome' => 'Plano Director Municipal', 'slug' => 'pdm', 'ordem' => 2],
            ['nome' => 'Plano Municipal de Desenvolvimento Rural', 'slug' => 'pmdr', 'ordem' => 3],
            ['nome' => 'Foral Municipal', 'slug' => 'foral-municipal', 'ordem' => 4],
        ];

        foreach ($tiposOrdenamento as $tipo) {
            $ordenamento->tipoInstrumentos()->create($tipo);
        }

        // 1.2 Instrumentos Urbanísticos
        $urbanisticos = CategoriaInstrumento::create([
            'nome' => 'Instrumentos Urbanísticos',
            'slug' => 'instrumentos-urbanisticos',
            'descricao' => 'Instrumentos de planeamento e gestão urbanística',
            'ordem' => 2,
        ]);

        $tiposUrbanisticos = [
            ['nome' => 'Plano Urbano Municipal', 'slug' => 'pum', 'ordem' => 1],
            ['nome' => 'Plano de Pormenor', 'slug' => 'plano-pormenor', 'ordem' => 2],
            ['nome' => 'Projecto de Requalificação Urbana', 'slug' => 'requalificacao-urbana', 'ordem' => 3],
            ['nome' => 'Regulamentos Urbanísticos', 'slug' => 'regulamentos-urbanisticos', 'ordem' => 4],
        ];

        foreach ($tiposUrbanisticos as $tipo) {
            $urbanisticos->tipoInstrumentos()->create($tipo);
        }

        // 1.3 Cartas Municipais
        $cartas = CategoriaInstrumento::create([
            'nome' => 'Cartas Municipais',
            'slug' => 'cartas-municipais',
            'descricao' => 'Cartas e mapas municipais diversos',
            'ordem' => 3,
        ]);

        $tiposCartas = [
            ['nome' => 'Carta Municipal', 'slug' => 'carta-municipal', 'ordem' => 1],
        ];

        foreach ($tiposCartas as $tipo) {
            $cartas->tipoInstrumentos()->create($tipo);
        }
    }
}
