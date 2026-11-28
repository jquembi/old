<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // A ligação tenant é determinada pelo .env e pelo serviço de instalação.
        // Este ficheiro permanece por compatibilidade com bases já migradas.
    }

    public function down(): void
    {
        // A ligação anterior depende do ambiente e não deve ser inferida ou apagada.
    }
};
