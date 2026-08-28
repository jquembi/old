<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            if (! Schema::hasColumn('croquis', 'area')) {
                $table->decimal('area', 14, 4)->nullable()->after('coordenadas_texto')
                    ->comment('Área em m²');
            }
            if (! Schema::hasColumn('croquis', 'perimetro')) {
                $table->decimal('perimetro', 14, 4)->nullable()->after('area')
                    ->comment('Perímetro em m²');
            }
            if (! Schema::hasColumn('croquis', 'dimensao')) {
                $table->string('dimensao', 100)->nullable()->after('perimetro')
                    ->comment('Dimensão do terreno, ex: 10x20');
            }
            if (! Schema::hasColumn('croquis', 'escala')) {
                $table->string('escala', 20)->nullable()->after('dimensao')
                    ->comment('Escala do croqui, ex: 1/25000');
            }
            if (! Schema::hasColumn('croquis', 'tecnico_responsavel')) {
                $table->string('tecnico_responsavel', 255)->nullable()->after('escala')
                    ->comment('Nome do técnico responsável');
            }
            if (! Schema::hasColumn('croquis', 'folha_numero')) {
                $table->string('folha_numero', 20)->nullable()->after('tecnico_responsavel')
                    ->comment('Número da folha, ex: 01');
            }
            if (! Schema::hasColumn('croquis', 'data_folha')) {
                $table->string('data_folha', 10)->nullable()->after('folha_numero')
                    ->comment('Mês/Ano da folha, ex: 04/2026');
            }
            if (! Schema::hasColumn('croquis', 'finalidade')) {
                $table->string('finalidade', 100)->nullable()->after('data_folha')
                    ->comment('Finalidade: legalizar, regularizar, planear');
            }
            if (! Schema::hasColumn('croquis', 'micro_localizacao_caminho')) {
                $table->string('micro_localizacao_caminho')->nullable()->after('finalidade')
                    ->comment('Imagem de micro-localização (exclusivo croqui)');
            }
            if (! Schema::hasColumn('croquis', 'micro_localizacao_nome')) {
                $table->string('micro_localizacao_nome')->nullable()->after('micro_localizacao_caminho');
            }
        });
    }

    public function down(): void
    {
        Schema::table('croquis', function (Blueprint $table) {
            $cols = [
                'area', 'perimetro', 'dimensao', 'escala',
                'tecnico_responsavel', 'folha_numero', 'data_folha',
                'finalidade', 'micro_localizacao_caminho', 'micro_localizacao_nome',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('croquis', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
