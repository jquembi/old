<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vincula funcionários existentes ao utilizador com o mesmo email.
        DB::table('funcionarios')
            ->whereNull('user_id')
            ->whereNotNull('email')
            ->orderBy('id')
            ->each(function ($f) {
                $userId = DB::table('users')->where('email', $f->email)->value('id');
                $jaVinculado = $userId && DB::table('funcionarios')->where('user_id', $userId)->exists();

                if ($userId && ! $jaVinculado) {
                    DB::table('funcionarios')->where('id', $f->id)->update(['user_id' => $userId]);
                }
            });

        Schema::table('funcionarios', function ($table) {
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('funcionarios', function ($table) {
            $table->dropUnique(['user_id']);
        });
    }
};
