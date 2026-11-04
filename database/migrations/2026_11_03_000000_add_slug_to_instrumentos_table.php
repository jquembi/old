<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('instrumentos', 'slug')) {
            Schema::table('instrumentos', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('titulo');
            });
        }

        $usados = [];
        DB::table('instrumentos')->whereNull('slug')->orderBy('id')->each(function ($row) use (&$usados) {
            $base = Str::slug((string) $row->titulo) ?: 'instrumento';
            $slug = $base;
            $n = 1;
            while (isset($usados[$slug]) || DB::table('instrumentos')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$n++;
            }
            $usados[$slug] = true;
            DB::table('instrumentos')->where('id', $row->id)->update(['slug' => $slug]);
        });

        Schema::table('instrumentos', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('instrumentos', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
