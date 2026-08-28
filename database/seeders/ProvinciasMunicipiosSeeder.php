<?php

namespace Database\Seeders;

use App\Models\Provincia;
use App\Models\Municipio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fonte: Lei n.º 14/24, de 5 de Setembro (Lei da Divisão Político-Administrativa)
 *        Decreto Presidencial n.º 270/24, de 29 de Novembro
 *        Em vigor desde 1 de Janeiro de 2025.
 *
 * A Huíla passou de 14 para 24 municípios.
 */
class ProvinciasMunicipiosSeeder extends Seeder
{
    public function run(): void
    {
        $huila = Provincia::firstOrCreate(
            ['codigo' => 'HUI'],
            ['nome'   => 'Huíla']
        );

        $municipios = [
            // ── 14 Municípios históricos ──────────────────────────────────────
            ['nome' => 'Lubango',            'slug' => 'lubango',             'codigo' => 'HUI-LUB'],
            ['nome' => 'Caconda',            'slug' => 'caconda',             'codigo' => 'HUI-CCD'],
            ['nome' => 'Cacula',             'slug' => 'cacula',              'codigo' => 'HUI-CAC'],
            ['nome' => 'Caluquembe',         'slug' => 'caluquembe',          'codigo' => 'HUI-CLQ'],
            ['nome' => 'Chibia',             'slug' => 'chibia',              'codigo' => 'HUI-CHB'],
            ['nome' => 'Chicomba',           'slug' => 'chicomba',            'codigo' => 'HUI-CHC'],
            ['nome' => 'Chipindo',           'slug' => 'chipindo',            'codigo' => 'HUI-CHP'],
            ['nome' => 'Cuvango',            'slug' => 'cuvango',             'codigo' => 'HUI-CUV'],
            ['nome' => 'Gambos',             'slug' => 'gambos',              'codigo' => 'HUI-GAM'],
            ['nome' => 'Humpata',            'slug' => 'humpata',             'codigo' => 'HUI-HUM'],
            ['nome' => 'Jamba Mineira',      'slug' => 'jamba-mineira',       'codigo' => 'HUI-JAM'], // ⚠ Corrigido: "Jamba" → "Jamba Mineira"
            ['nome' => 'Matala',             'slug' => 'matala',              'codigo' => 'HUI-MAT'],
            ['nome' => 'Quilengues',         'slug' => 'quilengues',          'codigo' => 'HUI-QUI'],
            ['nome' => 'Quipungo',           'slug' => 'quipungo',            'codigo' => 'HUI-QPG'],

            // ── 10 Municípios criados/promovidos pela nova DPA (Lei 14/24) ────
            ['nome' => 'Capelongo',          'slug' => 'capelongo',           'codigo' => 'HUI-CPL'],
            ['nome' => 'Capunda Cavilongo',  'slug' => 'capunda-cavilongo',   'codigo' => 'HUI-CPC'],
            ['nome' => 'Chicungo',           'slug' => 'chicungo',            'codigo' => 'HUI-CHK'],
            ['nome' => 'Chituto',            'slug' => 'chituto',             'codigo' => 'HUI-CTT'],
            ['nome' => 'Dongo',              'slug' => 'dongo',               'codigo' => 'HUI-DON'],
            ['nome' => 'Galangue',           'slug' => 'galangue',            'codigo' => 'HUI-GAL'],
            ['nome' => 'Hoque',              'slug' => 'hoque',               'codigo' => 'HUI-HOQ'],
            ['nome' => 'Palanca',            'slug' => 'palanca',             'codigo' => 'HUI-PAL'],
            ['nome' => 'Viti Vivali',               'slug' => 'viti-vivali',                'codigo' => 'HUI-VIT'], // ⚠ Corrigido: "Viti Vivali" são 2 municípios
        ];

        foreach ($municipios as $municipio) {
            $huila->municipios()->firstOrCreate(
                ['codigo' => $municipio['codigo']],
                $municipio
            );
        }
    }
}
