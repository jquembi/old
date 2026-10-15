<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros_nacionais', function (Blueprint $table) {
            $table->id();
            $table->string('grupo', 50)->index();
            $table->string('chave', 100)->unique();
            $table->string('rotulo');
            $table->text('descricao')->nullable();
            $table->string('tipo', 20)->default('texto'); // texto, numero, booleano, lista
            $table->text('valor')->nullable();
            $table->text('valor_padrao')->nullable();
            $table->boolean('editavel')->default(true);
            $table->foreignId('atualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_nacionais');
    }
};
