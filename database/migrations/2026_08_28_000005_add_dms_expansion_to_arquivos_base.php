<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arquivos_base', function (Blueprint $table) {
            // Versionamento
            $table->integer('versao')->default(1)->after('slug');
            $table->foreignId('versao_anterior_id')->nullable()->after('versao')->constrained('arquivos_base')->onDelete('set null');
            
            // Workflow de aprovação
            $table->enum('estado_aprovacao', ['rascunho', 'pendente_aprovacao', 'aprovado', 'rejeitado'])->default('rascunho')->after('versao_anterior_id');
            $table->foreignId('aprovado_por')->nullable()->after('estado_aprovacao')->constrained('users')->onDelete('set null');
            $table->timestamp('aprovado_em')->nullable()->after('aprovado_por');
            $table->text('motivo_rejeicao')->nullable()->after('aprovado_em');
            
            // Metadados adicionais
            $table->string('categoria')->nullable()->after('motivo_rejeicao');
            $table->json('tags')->nullable()->after('categoria');
            $table->text('descricao')->nullable()->after('tags');
            
            // Assinatura digital
            $table->string('assinatura_hash')->nullable()->after('descricao');
            $table->foreignId('assinado_por')->nullable()->after('assinatura_hash')->constrained('users')->onDelete('set null');
            $table->timestamp('assinado_em')->nullable()->after('assinado_por');
            
            // Sincronização
            $table->uuid('sync_id')->unique()->after('assinado_em');
            $table->enum('sync_status', ['pending', 'synced', 'conflict'])->default('synced')->after('sync_id');
            
            // Índices
            $table->index('versao');
            $table->index('estado_aprovacao');
            $table->index('categoria');
            $table->index('sync_id');
            $table->index('sync_status');
        });

        // Gerar sync_id para registos existentes
        DB::table('arquivos_base')->whereNull('sync_id')->get()->each(function ($arquivo) {
            DB::table('arquivos_base')
                ->where('id', $arquivo->id)
                ->update(['sync_id' => (string) \Illuminate\Support\Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('arquivos_base', function (Blueprint $table) {
            $table->dropColumn([
                'versao',
                'versao_anterior_id',
                'estado_aprovacao',
                'aprovado_por',
                'aprovado_em',
                'motivo_rejeicao',
                'categoria',
                'tags',
                'descricao',
                'assinatura_hash',
                'assinado_por',
                'assinado_em',
                'sync_id',
                'sync_status',
            ]);
        });
    }
};
