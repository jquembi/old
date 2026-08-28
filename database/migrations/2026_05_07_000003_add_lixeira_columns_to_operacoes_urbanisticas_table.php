<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('operacoes_urbanisticas')) {
            return;
        }

        Schema::table('operacoes_urbanisticas', function (Blueprint $table) {
            if (! Schema::hasColumn('operacoes_urbanisticas', 'deleted_by')) {
                $table->foreignId('deleted_by')
                    ->nullable()
                    ->after('updated_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('operacoes_urbanisticas', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // Intencionalmente vazio para evitar perda acidental de historico da lixeira.
    }
};
