<?php

namespace Database\Seeders;

use App\Models\Municipio;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuariosIniciaisSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Obter roles ───────────────────────────────────────────────────
        $chefeSeccao    = Role::where('nome', Role::CHEFE_SECCAO_TECNICA)->first();
        $chefeAdmin     = Role::where('nome', Role::CHEFE_SECCAO_ADMINISTRATIVA)->first();
        $secretaria     = Role::where('nome', Role::SECRETARIA_GABINETE)->first();
        $directorInotu  = Role::where('nome', Role::DIRECTOR_INOTU)->first();
        $tecnicoInotu   = Role::where('nome', Role::TECNICO_INOTU)->first();
        $governador     = Role::where('nome', Role::GOVERNADOR)->first();
        $adminMunicipal = Role::where('nome', Role::ADMIN_MUNICIPAL)->first();

        // ─── Província Huíla ───────────────────────────────────────────────
        $huila = Provincia::firstOrCreate(
            ['codigo' => 'HUI'],
            ['nome' => 'Huíla', 'activo' => true]
        );

        // ─────────────────────────────────────────────────────────────────
        // 1. Administrador INOTU — Chefe de Secção para Área Técnica
        //    (sem município; nível máximo do sistema)
        // ─────────────────────────────────────────────────────────────────
        $inotu = User::firstOrCreate(
            ['email' => 'admin@inotu.ao'],
            [
                'name'               => 'Administrador INOTU',
                'password'           => Hash::make('password1'),
                'email_verified_at'  => now(),
                'activo'             => true,
            ]
        );
        $this->atribuirRole($inotu, $chefeSeccao);

        if ($chefeAdmin) {
            $admin = User::firstOrCreate(
                ['email' => 'chefe.admin@inotu.ao'],
                [
                    'name'               => 'Chefe de Secção Administrativa INOTU',
                    'password'           => Hash::make('password1'),
                    'email_verified_at'  => now(),
                    'activo'             => true,
                    'criado_por'         => $inotu->id,
                ]
            );
            $this->atribuirRole($admin, $chefeAdmin);
        }

        if ($directorInotu) {
            $director = User::firstOrCreate(
                ['email' => 'director@inotu.ao'],
                [
                    'name'               => 'Director do INOTU',
                    'password'           => Hash::make('password1'),
                    'email_verified_at'  => now(),
                    'activo'             => true,
                    'criado_por'         => $inotu->id,
                ]
            );
            $this->atribuirRole($director, $directorInotu);
        }

        // ─────────────────────────────────────────────────────────────────
        // 2. Secretaria INOTU — Secretária do Gabinete do Director
        // ─────────────────────────────────────────────────────────────────
        if ($secretaria) {
            $sec = User::firstOrCreate(
                ['email' => 'secretaria@inotu.ao'],
                [
                    'name'               => 'Secretaria INOTU',
                    'password'           => Hash::make('password1'),
                    'email_verified_at'  => now(),
                    'activo'             => true,
                    'provincia_id'       => $huila->id,
                    'criado_por'         => $inotu->id,
                ]
            );
            $this->atribuirRole($sec, $secretaria);
        }

        // ─────────────────────────────────────────────────────────────────
        // 3–5. Secretárias do Gabinete do Director (Huíla)
        // ─────────────────────────────────────────────────────────────────
        if ($secretaria) {
            $secretarias = [
                [
                    'email' => 'abiliokatchio008@gmail.com',
                    'name'  => 'Abilio Cachio',
                ],
                [
                    'email' => 'osvaldoquifufutila@gmail.com',
                    'name'  => 'Osvaldo Lucas',
                ],
                [
                    'email' => 'juvecontreiras@gmail.com',
                    'name'  => 'Juvenália Contreiras',
                ],
            ];

            foreach ($secretarias as $dados) {
                $user = User::firstOrCreate(
                    ['email' => $dados['email']],
                    [
                        'name'               => $dados['name'],
                        'password'           => Hash::make('password1'),
                        'email_verified_at'  => now(),
                        'activo'             => true,
                        'provincia_id'       => $huila->id,
                        'criado_por'         => $inotu->id,
                    ]
                );
                $this->atribuirRole($user, $secretaria);
            }
        }

        // ─────────────────────────────────────────────────────────────────
        // 6–8. Técnicos do INOTU – Acesso Total (Huíla)
        // ─────────────────────────────────────────────────────────────────
        if ($tecnicoInotu) {
            $tecnicos = [
                [
                    'email' => 'icet210080@gmail.com',
                    'name'  => 'Isaias Tchitemo',
                ],
                [
                    'email' => 'agnaldoarturbenguela@gmail.com',
                    'name'  => 'Agnaldo Artur Benguela',
                ],
                [
                    'email' => 'alfredohossi9@gmail.com',
                    'name'  => 'Alfredo Manuel Hossi',
                ],
            ];

            foreach ($tecnicos as $dados) {
                $user = User::firstOrCreate(
                    ['email' => $dados['email']],
                    [
                        'name'               => $dados['name'],
                        'password'           => Hash::make('password1'),
                        'email_verified_at'  => now(),
                        'activo'             => true,
                        'provincia_id'       => $huila->id,
                        'criado_por'         => $inotu->id,
                    ]
                );
                $this->atribuirRole($user, $tecnicoInotu);
            }
        }

        // ─────────────────────────────────────────────────────────────────
        // Utilizadores de exemplo para outros níveis
        // ─────────────────────────────────────────────────────────────────

        // Governador Provincial Huíla
        if ($governador) {
            $provincial = User::firstOrCreate(
                ['email' => 'governador@gphuila.gov.ao'],
                [
                    'name'               => 'Governador Provincial Huíla',
                    'password'           => Hash::make('password1'),
                    'email_verified_at'  => now(),
                    'activo'             => true,
                    'provincia_id'       => $huila->id,
                    'criado_por'         => $inotu->id,
                ]
            );
            $this->atribuirRole($provincial, $governador);
        }

        // Administrador Municipal Lubango
        $lubango = Municipio::where('codigo', 'HUI-LUB')->first();
        if ($lubango && $adminMunicipal) {
            $municipal = User::firstOrCreate(
                ['email' => 'admin@lubango.gov.ao'],
                [
                    'name'               => 'Administrador Municipal Lubango',
                    'password'           => Hash::make('password1'),
                    'email_verified_at'  => now(),
                    'activo'             => true,
                    'provincia_id'       => $lubango->provincia_id,
                    'municipio_id'       => $lubango->id,
                    'criado_por'         => $inotu->id,
                ]
            );
            $this->atribuirRole($municipal, $adminMunicipal);
        }
    }

    /**
     * Atribui um role ao utilizador apenas se ainda não o tiver.
     */
    private function atribuirRole(User $user, ?Role $role): void
    {
        if ($role && ! $user->roles->contains('id', $role->id)) {
            $user->roles()->attach($role->id);
        }
    }
}
