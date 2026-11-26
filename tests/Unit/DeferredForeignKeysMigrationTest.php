<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

test('deferred foreign keys can be added after their tables and rolled back', function () {
    foreach (['users', 'municipios', 'tenants'] as $table) {
        Schema::create($table, fn (Blueprint $blueprint) => $blueprint->id());
    }

    $files = [
        '2026_08_28_000016_create_funcionarios_table.php',
        '2026_08_28_000017_create_departamentos_table.php',
        '2026_08_28_000018_create_cargos_table.php',
        '2026_08_28_000022_create_patrimonios_table.php',
        '2026_08_28_000023_create_categorias_patrimonio_table.php',
        '2026_08_28_000024_create_localizacoes_table.php',
        '2026_08_28_000025_add_deferred_foreign_keys.php',
    ];

    $migrations = array_map(fn (string $file) => require database_path('migrations/'.$file), $files);

    foreach ($migrations as $migration) {
        $migration->up();

        foreach (['funcionarios', 'departamentos', 'cargos', 'patrimonios', 'categorias_patrimonio', 'localizacoes'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                expect(Schema::hasTable($foreignKey['foreign_table']))->toBeTrue();
            }
        }
    }

    $migrations[array_key_last($migrations)]->up();

    foreach ([
        ['funcionarios', 'departamento_id', 'departamentos'],
        ['funcionarios', 'cargo_id', 'cargos'],
        ['patrimonios', 'categoria_patrimonio_id', 'categorias_patrimonio'],
        ['patrimonios', 'localizacao_id', 'localizacoes'],
    ] as [$table, $column, $referencedTable]) {
        $matches = collect(Schema::getForeignKeys($table))->filter(
            fn (array $foreignKey) => $foreignKey['columns'] === [$column]
                && $foreignKey['foreign_table'] === $referencedTable
                && strtolower($foreignKey['on_delete']) === 'set null'
        );

        expect($matches)->toHaveCount(1);
    }

    foreach (array_reverse($migrations) as $migration) {
        $migration->down();
    }

    expect(Schema::hasTable('funcionarios'))->toBeFalse()
        ->and(Schema::hasTable('patrimonios'))->toBeFalse();
});
