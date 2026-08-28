<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pareceres', function (Blueprint $table) {
            $table->string('recurso_type')->nullable()->after('municipio_id');
            $table->unsignedBigInteger('recurso_id')->nullable()->after('recurso_type');
            $table->string('servico')->nullable()->after('projecto_nome');
            $table->string('entidade_requerente')->nullable()->after('servico');
            $table->string('tipo_concessao')->nullable()->after('entidade_requerente');
            $table->string('classificacao_area')->nullable()->after('tipo_concessao');
            $table->string('uso_solo')->nullable()->after('classificacao_area');
            $table->string('zona_ordenamento')->nullable()->after('uso_solo');
            $table->boolean('area_reservada')->nullable()->after('zona_ordenamento');
            $table->text('fundamentacao')->nullable()->after('area_reservada');
            $table->text('conclusao')->nullable()->after('fundamentacao');

            $table->index(['recurso_type', 'recurso_id']);
            $table->index('servico');
        });
    }

    public function down(): void
    {
        Schema::table('pareceres', function (Blueprint $table) {
            $table->dropIndex(['recurso_type', 'recurso_id']);
            $table->dropIndex(['servico']);
            $table->dropColumn([
                'recurso_type',
                'recurso_id',
                'servico',
                'entidade_requerente',
                'tipo_concessao',
                'classificacao_area',
                'uso_solo',
                'zona_ordenamento',
                'area_reservada',
                'fundamentacao',
                'conclusao',
            ]);
        });
    }
};
