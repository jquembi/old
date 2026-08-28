<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pareceres', function (Blueprint $table) {
            // Torna o campo projecto_nome nullable com valor padrão vazio
            $table->string('projecto_nome')->nullable()->default('')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pareceres', function (Blueprint $table) {
            // Reverte para string obrigatório
            $table->string('projecto_nome')->nullable(false)->change();
        });
    }
};
