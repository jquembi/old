<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fonte: Lei n.º 14/24, de 5 de Setembro (Lei da Divisão Político-Administrativa)
 *        Em vigor desde Janeiro de 2025: 21 províncias, 326 municípios, 378 comunas.
 *
 * Cada município pode ser:
 *   - 'Nome'                  → código gerado automaticamente (PROV-XXX)
 *   - ['Nome', 'PROV-XXX']    → código explícito
 *
 * Para completar: preencher o array 'municipios' das províncias vazias.
 */
class ProvinciasMunicipiosSeeder extends Seeder
{
    /**
     * Código e nome das 21 províncias, para o assistente de instalação
     * escolher o âmbito antes de a base de dados existir.
     *
     * @return array<string, string>
     */
    public static function catalogo(): array
    {
        $lista = [];

        foreach ((new self)->dados() as $codigo => $dados) {
            $lista[$codigo] = $dados['nome'];
        }

        asort($lista);

        return $lista;
    }

    public function run(): void
    {
        $catalogo = $this->dados();
        if (config('tenancy.deployment_mode') === 'node') {
            $codigoNode = strtoupper((string) config('tenancy.node.code'));
            if (! isset($catalogo[$codigoNode])) {
                throw new \RuntimeException('O código da província configurada não existe no catálogo territorial.');
            }
            $catalogo = [$codigoNode => $catalogo[$codigoNode]];
        }

        foreach ($catalogo as $codigo => $dados) {
            $provincia = Provincia::firstOrCreate(
                ['codigo' => $codigo],
                ['nome'   => $dados['nome']]
            );

            $usados = [];

            foreach ($dados['municipios'] as $item) {
                [$nome, $codigoMunicipio] = is_array($item)
                    ? $item
                    : [$item, null];

                $codigoMunicipio ??= $this->gerarCodigo($codigo, $nome, $usados);
                $usados[] = $codigoMunicipio;

                $provincia->municipios()->firstOrCreate(
                    ['codigo' => $codigoMunicipio],
                    [
                        'nome' => $nome,
                        'slug' => Str::slug($nome),
                    ]
                );
            }
        }
    }

    /**
     * Gera HUI-XXX a partir do nome, resolvendo colisões dentro da província.
     */
    private function gerarCodigo(string $prov, string $nome, array $usados): string
    {
        $letras = strtoupper(preg_replace('/[^A-Za-z]/', '', Str::ascii($nome)));

        $candidatos = [substr($letras, 0, 3)];
        for ($i = 2; $i < strlen($letras); $i++) {
            $candidatos[] = substr($letras, 0, 2) . $letras[$i];
        }

        foreach ($candidatos as $sufixo) {
            if (!in_array("$prov-$sufixo", $usados, true)) {
                return "$prov-$sufixo";
            }
        }

        $n = 2;
        while (in_array("$prov-" . substr($letras, 0, 3) . $n, $usados, true)) {
            $n++;
        }

        return "$prov-" . substr($letras, 0, 3) . $n;
    }

        private function dados(): array
    {
        return [
            'BGO' => ['nome' => 'Bengo', 'municipios' => [
                'Ambriz', 'Bula Atumba', 'Dande', 'Dembos',
                'Nambuangongo', 'Pango Aluquém',
            ]],

            'BGU' => ['nome' => 'Benguela', 'municipios' => [
                'Balombo', 'Baía Farta', 'Benguela', 'Bocoio', 'Caimbambo',
                'Catumbela', 'Chongorói', 'Cubal', 'Ganda', 'Lobito',
            ]],

            'BIE' => ['nome' => 'Bié', 'municipios' => [
                'Andulo', 'Camacupa', 'Catabola', 'Chinguar', 'Chitembo',
                'Cuemba', 'Cunhinga', 'Cuíto', 'Nharea',
            ]],

            'CAB' => ['nome' => 'Cabinda', 'municipios' => [
                'Belize', 'Buco-Zau', 'Cabinda', 'Cacongo',
            ]],

            // Antigo Cuando Cubango, agora dividido em Cubango + Cuando
            'CBG' => ['nome' => 'Cubango', 'municipios' => [
                'Menongue', 'Caiundo', 'Calai', 'Chinguanja', 'Cuangar',
                'Cuchi', 'Cutato', 'Longa', 'Mavengue', 'Nancova', 'Savate',
            ]],

            'CDO' => ['nome' => 'Cuando', 'municipios' => [
                'Mavinga', 'Cuito Cuanavale', 'Dima', 'Dirico', 'Luengue',
                'Luiana', 'Mucusso', 'Rivungo', 'Xipundo',
            ]],

            'CNO' => ['nome' => 'Cuanza Norte', 'municipios' => [
                'Ambaca', 'Banga', 'Bolongongo', 'Cambambe', 'Cazengo',
                'Golungo Alto', 'Gonguembo', 'Lucala', 'Quiculungo', 'Samba Cajú',
            ]],

            'CSU' => ['nome' => 'Cuanza Sul', 'municipios' => [
                'Sumbe', 'Amboim', 'Cassongue', 'Conda', 'Ebo', 'Libolo',
                'Mussende', 'Porto Amboim', 'Quibala', 'Quilenda', 'Seles',
                'Waku Kungo',
            ]],

            'CUN' => ['nome' => 'Cunene', 'municipios' => [
                'Cahama', 'Cuanhama', 'Curoca', 'Cuvelai', 'Namacunde', 'Ombadja',
            ]],

            'HBO' => ['nome' => 'Huambo', 'municipios' => [
                'Bailundo', 'Cachiungo', 'Caála', 'Chicala-Choloanga', 'Chinjenje',
                'Ecunha', 'Huambo', 'Londuimbali', 'Longonjo', 'Mungo', 'Ucuma',
            ]],

            'HUI' => ['nome' => 'Huíla', 'municipios' => [
                // Históricos
                ['Lubango',           'HUI-LUB'],
                ['Caconda',           'HUI-CCD'],
                ['Cacula',            'HUI-CAC'],
                ['Caluquembe',        'HUI-CLQ'],
                ['Chibia',            'HUI-CHB'],
                ['Chicomba',          'HUI-CHC'],
                ['Chipindo',          'HUI-CHP'],
                ['Cuvango',           'HUI-CUV'],
                ['Gambos',            'HUI-GAM'],
                ['Humpata',           'HUI-HUM'],
                ['Jamba Mineira',     'HUI-JAM'],
                ['Matala',            'HUI-MAT'],
                ['Quilengues',        'HUI-QUI'],
                ['Quipungo',          'HUI-QPG'],
                // Novos (Lei 14/24)
                ['Capelongo',         'HUI-CPL'],
                ['Capunda Cavilongo', 'HUI-CPC'],
                ['Chicungo',          'HUI-CHK'],
                ['Chituto',           'HUI-CTT'],
                ['Dongo',             'HUI-DON'],
                ['Galangue',          'HUI-GAL'],
                ['Hoque',             'HUI-HOQ'],
                ['Palanca',           'HUI-PAL'],
                ['Viti Vivali',       'HUI-VIT'],
            ]],

            'IEB' => ['nome' => 'Icolo e Bengo', 'municipios' => [
                'Icolo e Bengo', 'Quiçama',
            ]],

            'LUA' => ['nome' => 'Luanda', 'municipios' => [
                'Luanda', 'Belas', 'Cacuaco', 'Cazenga',
                'Quilamba Quiaxi', 'Talatona', 'Viana',
            ]],

            'LNO' => ['nome' => 'Lunda Norte', 'municipios' => [
                'Cambulo', 'Capenda-Camulemba', 'Caungula', 'Chitato', 'Cuango',
                'Cuílo', 'Lóvua', 'Lubalo', 'Lucapa', 'Xá-Muteba',
            ]],

            'LSU' => ['nome' => 'Lunda Sul', 'municipios' => [
                'Saurimo', 'Cacolo', 'Dala', 'Muconda',
            ]],

            'MAL' => ['nome' => 'Malanje', 'municipios' => [
                'Malanje', 'Cacuso', 'Calandula', 'Cambundi-Catembo', 'Cangandala',
                'Caombo', 'Cunda-Dia-Baze', 'Kiwaba Nzoji', 'Luquembo', 'Marimba',
                'Massango', 'Mucari', 'Quela', 'Quirima',
            ]],

            // Moxico e Cassai Zambeze: divisão dos municípios por confirmar
            'MOX' => ['nome' => 'Moxico', 'municipios' => []],
            'CZB' => ['nome' => 'Cassai Zambeze', 'municipios' => []],

            'NAM' => ['nome' => 'Namibe', 'municipios' => [
                'Moçâmedes', 'Bibala', 'Camucuio', 'Tômbua', 'Virei',
            ]],

            'UIG' => ['nome' => 'Uíge', 'municipios' => [
                'Uíge', 'Ambuíla', 'Bembe', 'Buengas', 'Bungo', 'Cangola',
                'Damba', 'Maquela do Zombo', 'Mucaba', 'Negage', 'Puri',
                'Quimbele', 'Quitexe', 'Santa Cruz', 'Sanza Pombo', 'Songo',
            ]],

            'ZAI' => ['nome' => 'Zaire', 'municipios' => [
                'M\'Banza Kongo', 'Cuimba', 'Noqui', 'N\'Zeto', 'Soyo', 'Tomboco',
            ]],
        ];
    }
}