<?php

use App\Models\Provincia;
use App\Models\Municipio;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $huila = Provincia::where('codigo', 'HUI')->first();

        if (! $huila) {
            return;
        }

        $novos = [
            ['nome' => 'Capelongo', 'slug' => 'capelongo', 'codigo' => 'HUI-CPL'],
            ['nome' => 'Dinde', 'slug' => 'dinde', 'codigo' => 'HUI-DIN'],
            ['nome' => 'Tchicala-Tcholoanga', 'slug' => 'tchicala-tcholoanga', 'codigo' => 'HUI-TCH'],
            ['nome' => 'Kuvango', 'slug' => 'kuvango', 'codigo' => 'HUI-KUV'],
            ['nome' => 'Quipungo', 'slug' => 'quipungo', 'codigo' => 'HUI-QPG'],
            ['nome' => 'Jau', 'slug' => 'jau', 'codigo' => 'HUI-JAU'],
            ['nome' => 'Bambi', 'slug' => 'bambi', 'codigo' => 'HUI-BAM'],
            ['nome' => 'Tchivinguiro', 'slug' => 'tchivinguiro', 'codigo' => 'HUI-TCV'],
            ['nome' => 'Catata', 'slug' => 'catata', 'codigo' => 'HUI-CAT'],
        ];

        foreach ($novos as $mun) {
            Municipio::firstOrCreate(
                ['slug' => $mun['slug']],
                array_merge($mun, ['provincia_id' => $huila->id, 'activo' => true])
            );
        }
    }

    public function down(): void
    {
        Municipio::whereIn('slug', [
            'capelongo', 'dinde', 'tchicala-tcholoanga', 'kuvango',
            'quipungo', 'jau', 'bambi', 'tchivinguiro', 'catata',
        ])->delete();
    }
};
