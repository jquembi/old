<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissoes = [
            [
                'nome'      => 'pagamentos.ver',
                'descricao' => 'Visualizar registos de pagamento RUPE',
                'grupo'     => 'pagamentos',
            ],
            [
                'nome'      => 'pagamentos.gerir_rupe',
                'descricao' => 'Gerar e gerir referências RUPE (INOTU)',
                'grupo'     => 'pagamentos',
            ],
            [
                'nome'      => 'pagamentos.confirmar',
                'descricao' => 'Confirmar ou rejeitar pagamentos RUPE (INOTU)',
                'grupo'     => 'pagamentos',
            ],
            [
                'nome'      => 'pagamentos.submeter_comprovativo',
                'descricao' => 'Submeter comprovativo de pagamento (Município)',
                'grupo'     => 'pagamentos',
            ],
        ];

        foreach ($permissoes as $perm) {
            DB::table('permissoes')->insertOrIgnore(array_merge($perm, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        DB::table('permissoes')->whereIn('nome', [
            'pagamentos.ver',
            'pagamentos.gerir_rupe',
            'pagamentos.confirmar',
            'pagamentos.submeter_comprovativo',
        ])->delete();
    }
};
