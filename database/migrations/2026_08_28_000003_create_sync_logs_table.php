<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('target_tenant_id')->nullable()->constrained('tenants')->onDelete('set null');
            $table->string('sync_type', 50); // push_to_central, pull_from_central, peer_to_peer
            $table->enum('status', ['pending', 'success', 'failed', 'partial'])->default('pending');
            $table->integer('records_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['source_tenant_id', 'status']);
            $table->index('sync_type');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
