<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar campos UTM à tabela de croquis
        Schema::table('croquis', function (Blueprint $table) {
            $table->string('utm_zone', 5)->nullable()->after('longitude');
            $table->string('utm_hemisphere', 5)->nullable()->after('utm_zone');
            $table->decimal('utm_easting', 12, 3)->nullable()->after('utm_hemisphere');
            $table->decimal('utm_northing', 13, 3)->nullable()->after('utm_easting');
        });

        // Tabela de ficheiros de operações (Área de Trabalho)
        Schema::create('operation_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operacao_id')->constrained('operacoes_urbanisticas')->cascadeOnDelete();
            $table->foreignId('croqui_id')->nullable()->constrained('croquis')->nullOnDelete();
            $table->foreignId('parecer_id')->nullable()->constrained('pareceres')->nullOnDelete();
            $table->string('nome');
            $table->string('nome_original');
            $table->string('tipo'); // qgis, autocad, pdf, imagem, outro
            $table->string('extensao', 20);
            $table->string('caminho');
            $table->unsignedBigInteger('tamanho'); // bytes
            $table->string('mime_type')->nullable();
            $table->foreignId('enviado_por')->constrained('users');
            $table->timestamps();

            $table->index(['operacao_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_files');

        // Só remover colunas se existirem (para SQLite compatibility)
        Schema::table('croquis', function (Blueprint $table) {
            $columns = ['utm_zone', 'utm_hemisphere', 'utm_easting', 'utm_northing'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('croquis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
