<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('terrenos_ociosos')) {
            Schema::create('terrenos_ociosos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('municipio_id')->constrained('municipios')->cascadeOnDelete();
                $table->string('proprietario');
                $table->string('bi_proprietario')->nullable();
                $table->string('slug')->unique();
                $table->string('endereco', 500)->nullable();
                $table->string('lote', 100)->nullable();
                $table->decimal('area_m2', 12, 2)->nullable();
                $table->decimal('area_construida_m2', 12, 2)->nullable();
                $table->date('data_levantamento')->nullable();
                $table->string('motivo_ociosidade')->nullable();
                $table->unsignedInteger('tempo_ociosidade_anos')->nullable();
                $table->json('geometry')->nullable();
                $table->string('pdf_path')->nullable();
                $table->string('sistema_referencia')->nullable();
                $table->string('potencial_uso')->nullable();
                $table->string('status')->default('rascunho');
                $table->string('prioridade_intervencao')->nullable();
                $table->string('contacto_proprietario')->nullable();
                $table->text('observacoes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['municipio_id', 'status']);
                $table->index('motivo_ociosidade');
                $table->index('prioridade_intervencao');
            });
        }

        if (! Schema::hasTable('terreno_ocioso_coordenadas')) {
            Schema::create('terreno_ocioso_coordenadas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('terreno_ocioso_id')->constrained('terrenos_ociosos')->cascadeOnDelete();
                $table->unsignedInteger('ordem')->default(0);
                $table->decimal('utm_easting', 12, 4)->nullable();
                $table->decimal('utm_northing', 12, 4)->nullable();
                $table->string('utm_zone', 20)->nullable();
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->string('descricao')->nullable();
                $table->timestamps();

                $table->index(['terreno_ocioso_id', 'ordem']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('terreno_ocioso_coordenadas');
        Schema::dropIfExists('terrenos_ociosos');
    }
};
