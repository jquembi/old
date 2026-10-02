<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('national_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('indicator_type', 100); // processos, terras, receitas, etc.
            $table->json('value'); // Dados do indicador
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'indicator_type']);
            $table->index('period_start');
            $table->index('period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('national_indicators');
    }
};
