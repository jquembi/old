<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('numero_funcionario', 20)->unique();
            $table->string('nome_completo');
            $table->string('nome_curto');
            $table->string('bi', 14)->nullable();
            $table->date('data_nascimento');
            $table->enum('genero', ['masculino', 'feminino']);
            $table->string('telefone');
            $table->string('email')->nullable();
            $table->text('endereco')->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios')->onDelete('set null');
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->onDelete('set null');
            $table->foreignId('cargo_id')->nullable()->constrained('cargos')->onDelete('set null');
            $table->date('data_admissao');
            $table->date('data_demissao')->nullable();
            $table->decimal('salario_base', 15, 2)->nullable();
            $table->enum('tipo_contrato', ['efetivo', 'contrato', 'estagio', 'temporario'])->default('efetivo');
            $table->enum('status', ['ativo', 'ferias', 'licenca', 'suspenso', 'demitido'])->default('ativo');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('numero_funcionario');
            $table->index('bi');
            $table->index('status');
            $table->index('tenant_id');
            $table->index('departamento_id');
            $table->index('cargo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
