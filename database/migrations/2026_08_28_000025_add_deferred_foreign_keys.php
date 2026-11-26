<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->foreignKeys() as [$table, $column, $referencedTable]) {
            $exists = collect(Schema::getForeignKeys($table))->contains(
                fn (array $foreignKey) => $foreignKey['columns'] === [$column]
                    && $foreignKey['foreign_table'] === $referencedTable
            );

            if (! $exists) {
                Schema::table($table, function (Blueprint $blueprint) use ($column, $referencedTable) {
                    $blueprint->foreign($column)->references('id')->on($referencedTable)->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->foreignKeys() as [$table, $column]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
            });
        }
    }

    private function foreignKeys(): array
    {
        return [
            ['funcionarios', 'departamento_id', 'departamentos'],
            ['funcionarios', 'cargo_id', 'cargos'],
            ['patrimonios', 'categoria_patrimonio_id', 'categorias_patrimonio'],
            ['patrimonios', 'localizacao_id', 'localizacoes'],
        ];
    }
};
