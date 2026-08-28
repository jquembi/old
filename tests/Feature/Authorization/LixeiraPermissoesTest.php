<?php

use App\Models\CategoriaInstrumento;
use App\Models\Instrumento;
use App\Models\Municipio;
use App\Models\Permissao;
use App\Models\Provincia;
use App\Models\Role;
use App\Models\TipoInstrumento;
use App\Models\User;
use App\Services\LixeiraService;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Cache::flush();
});

function inotuUserWithRole(string $roleNome): User
{
    $nivel = in_array($roleNome, [
        Role::ADMIN_MUNICIPAL,
        Role::ADMIN_MUNICIPAL_ADJUNTO,
        Role::DIRECTOR_INFRAESTRUTURA,
    ], true)
        ? Role::NIVEL_MUNICIPAL
        : Role::NIVEL_INOTU;

    $role = Role::firstOrCreate(
        ['nome' => $roleNome],
        [
            'descricao' => $roleNome,
            'grupo' => $nivel === Role::NIVEL_INOTU ? Role::GRUPO_INOTU : Role::GRUPO_ADMINISTRACAO,
            'nivel' => $nivel,
        ],
    );

    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);

    return $user->fresh('roles');
}

function createPermissionForAuthorizationTest(string $nome): Permissao
{
    return Permissao::firstOrCreate(
        ['nome' => $nome],
        [
            'descricao' => $nome,
            'grupo' => str($nome)->before('.')->toString(),
        ],
    );
}

function createInstrumentForTrashTest(User $criador): Instrumento
{
    $provincia = Provincia::create([
        'nome' => 'Huila',
        'codigo' => 'HUI',
        'activo' => true,
    ]);

    $municipio = Municipio::create([
        'nome' => 'Lubango',
        'codigo' => 'HUI-LUB',
        'provincia_id' => $provincia->id,
        'activo' => true,
    ]);

    $categoria = CategoriaInstrumento::create([
        'nome' => 'Ordenamento',
        'slug' => 'ordenamento',
    ]);

    $tipo = TipoInstrumento::create([
        'categoria_id' => $categoria->id,
        'nome' => 'Plano Director Municipal',
        'slug' => 'pdm',
    ]);

    return Instrumento::create([
        'municipio_id' => $municipio->id,
        'tipo_instrumento_id' => $tipo->id,
        'titulo' => 'Instrumento teste',
        'descricao' => 'Teste',
        'estado' => 'rascunho',
        'criado_por' => $criador->id,
    ]);
}

it('permite enviar para a lixeira apenas aos papeis autorizados', function () {
    foreach ([Role::SECRETARIA_GABINETE, Role::CHEFE_SECCAO_TECNICA, Role::DIRECTOR_INOTU] as $role) {
        expect(inotuUserWithRole($role)->podeEnviarParaLixeira())->toBeTrue();
    }

    foreach ([Role::CHEFE_SECCAO_ADMINISTRATIVA, Role::ADMIN_MUNICIPAL, Role::ADMIN_MUNICIPAL_ADJUNTO] as $role) {
        expect(inotuUserWithRole($role)->podeEnviarParaLixeira())->toBeFalse();
    }
});

it('mantem o admin limitado a informacao tecnica mesmo com permissoes antigas anexadas', function () {
    $role = Role::firstOrCreate(
        ['nome' => Role::CHEFE_SECCAO_ADMINISTRATIVA],
        [
            'descricao' => 'Admin tecnico',
            'grupo' => Role::GRUPO_INOTU,
            'nivel' => Role::NIVEL_INOTU,
        ],
    );

    $permissoes = collect([
        'instrumentos.ver',
        'instrumentos.criar',
        'instrumentos.editar_municipio',
        'instrumentos.eliminar_municipio',
        'usuarios.ver',
        'documentos.download',
        'dashboard.municipal',
    ])->map(fn (string $nome) => createPermissionForAuthorizationTest($nome));

    $role->permissoes()->sync($permissoes->pluck('id'));

    $user = User::factory()->create(['activo' => true]);
    $user->roles()->attach($role);
    $user = $user->fresh('roles');

    expect($user->temPermissao('instrumentos.ver'))->toBeTrue()
        ->and($user->temPermissao('documentos.download'))->toBeTrue()
        ->and($user->temPermissao('dashboard.municipal'))->toBeTrue()
        ->and($user->temPermissao('instrumentos.criar'))->toBeFalse()
        ->and($user->temPermissao('instrumentos.editar_municipio'))->toBeFalse()
        ->and($user->temPermissao('instrumentos.eliminar_municipio'))->toBeFalse()
        ->and($user->temPermissao('usuarios.ver'))->toBeFalse()
        ->and($user->nomesPermissoes()->all())->not->toContain('instrumentos.criar')
        ->and($user->nomesPermissoes()->all())->not->toContain('usuarios.ver');
});

it('mantem o director do inotu como utilizador principal com acesso total', function () {
    $director = inotuUserWithRole(Role::DIRECTOR_INOTU);

    collect([
        'instrumentos.criar',
        'instrumentos.editar_municipio',
        'instrumentos.eliminar_municipio',
        'usuarios.criar',
        'permissoes.gerir',
        'lixeira.restaurar',
        'lixeira.eliminar_definitivo',
    ])->each(fn (string $nome) => createPermissionForAuthorizationTest($nome));

    expect($director->temPermissao('instrumentos.criar'))->toBeTrue()
        ->and($director->temPermissao('instrumentos.editar_municipio'))->toBeTrue()
        ->and($director->temPermissao('instrumentos.eliminar_municipio'))->toBeTrue()
        ->and($director->temPermissao('usuarios.criar'))->toBeTrue()
        ->and($director->temPermissao('permissoes.gerir'))->toBeTrue()
        ->and($director->temPermissao('lixeira.restaurar'))->toBeTrue()
        ->and($director->temPermissao('lixeira.eliminar_definitivo'))->toBeTrue()
        ->and($director->podeEditarDados())->toBeTrue()
        ->and($director->podeEditarMunicipio(999))->toBeTrue()
        ->and($director->podeGerirUsuarios())->toBeTrue()
        ->and($director->podeGerirUsuariosDe(Role::NIVEL_INOTU))->toBeTrue()
        ->and($director->podeEnviarParaLixeira())->toBeTrue()
        ->and($director->podeAcederLixeira())->toBeTrue();
});

it('envia, restaura e elimina definitivamente pela lixeira com as regras corretas', function () {
    $secretaria = inotuUserWithRole(Role::SECRETARIA_GABINETE);
    $director = inotuUserWithRole(Role::DIRECTOR_INOTU);
    $admin = inotuUserWithRole(Role::CHEFE_SECCAO_ADMINISTRATIVA);
    $instrumento = createInstrumentForTrashTest($secretaria);

    $this->actingAs($admin);
    expect(fn () => LixeiraService::enviar($instrumento))->toThrow(HttpException::class);
    expect($instrumento->fresh())->not->toBeNull();

    $this->actingAs($secretaria);
    LixeiraService::enviar($instrumento->fresh());
    $itemNaLixeira = Instrumento::onlyTrashed()->findOrFail($instrumento->id);

    expect($itemNaLixeira->deleted_by)->toBe($secretaria->id);

    expect(fn () => LixeiraService::restaurar($itemNaLixeira))->toThrow(HttpException::class);

    $this->actingAs($director);
    LixeiraService::restaurar(Instrumento::onlyTrashed()->findOrFail($instrumento->id));

    $restaurado = Instrumento::findOrFail($instrumento->id);
    expect($restaurado->deleted_at)->toBeNull()
        ->and($restaurado->deleted_by)->toBeNull();

    $this->actingAs($secretaria);
    LixeiraService::enviar($restaurado);

    $this->actingAs($director);
    LixeiraService::eliminarDefinitivo(Instrumento::onlyTrashed()->findOrFail($instrumento->id));

    expect(Instrumento::withTrashed()->find($instrumento->id))->toBeNull();
});
