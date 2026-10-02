<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tipo_processo', 100); // processo_fundiario, licenciamento, etc.
            $table->text('description')->nullable();
            $table->json('configuracao'); // Definição de estados e transições
            $table->boolean('activo')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('tipo_processo');
            $table->index('activo');
        });

        // Workflow padrão para processos fundiários
        DB::table('workflows')->insert([
            'name' => 'Processo Fundiário Padrão',
            'tipo_processo' => 'processo_fundiario',
            'description' => 'Workflow padrão para processos de concessão de terras',
            'configuracao' => json_encode($this->getDefaultWorkflowConfig()),
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function getDefaultWorkflowConfig(): array
    {
        return [
            'estados' => [
                'rececao' => [
                    'nome' => 'Receção',
                    'descricao' => 'Processo recebido',
                    'transicoes' => ['validacao'],
                    'final' => false,
                    'responsavel' => 'secretaria',
                ],
                'validacao' => [
                    'nome' => 'Validação',
                    'descricao' => 'Validação documental',
                    'transicoes' => ['analise', 'devolucao'],
                    'final' => false,
                    'responsavel' => 'tecnico',
                ],
                'analise' => [
                    'nome' => 'Análise',
                    'descricao' => 'Análise técnica',
                    'transicoes' => ['vistoria', 'parecer'],
                    'final' => false,
                    'responsavel' => 'tecnico',
                ],
                'vistoria' => [
                    'nome' => 'Vistoria',
                    'descricao' => 'Vistoria in loco',
                    'transicoes' => ['parecer'],
                    'final' => false,
                    'responsavel' => 'fiscal',
                ],
                'parecer' => [
                    'nome' => 'Parecer',
                    'descricao' => 'Emissão de parecer',
                    'transicoes' => ['aprovacao', 'rejeicao'],
                    'final' => false,
                    'responsavel' => 'tecnico',
                ],
                'aprovacao' => [
                    'nome' => 'Aprovação',
                    'descricao' => 'Aprovação final',
                    'transicoes' => ['pagamento'],
                    'final' => false,
                    'responsavel' => 'director',
                ],
                'pagamento' => [
                    'nome' => 'Pagamento',
                    'descricao' => 'Pagamento de taxas',
                    'transicoes' => ['emissao'],
                    'final' => false,
                    'responsavel' => 'financeiro',
                ],
                'emissao' => [
                    'nome' => 'Emissão',
                    'descricao' => 'Emissão de documento',
                    'transicoes' => ['arquivo'],
                    'final' => false,
                    'responsavel' => 'secretaria',
                ],
                'arquivo' => [
                    'nome' => 'Arquivo',
                    'descricao' => 'Processo arquivado',
                    'transicoes' => [],
                    'final' => true,
                    'responsavel' => 'arquivo',
                ],
            ],
        ];
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
