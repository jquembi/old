<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('responsavel_id')->nullable()->constrained('funcionarios')->onDelete('set null');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('codigo');
            $table->index('tenant_id');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
