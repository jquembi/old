<?php

namespace Database\Seeders;

use App\Models\Workflow;
use Illuminate\Database\Seeder;

class WorkflowsSeeder extends Seeder
{
    /**
     * Workflow mínimo para a tramitação de processos fundiários.
     * A configuração é idempotente e pode ser reposta em recuperação.
     */
    public function run(): void
    {
        Workflow::query()->firstOrCreate(
            ['tipo_processo' => 'processo_fundiario'],
            [
                'name' => 'Processo Fundiário Padrão',
                'description' => 'Workflow padrão para processos de concessão de terras',
                'configuracao' => [
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
                ],
                'activo' => true,
            ]
        );
    }
}
