<?php

namespace App\Http\Controllers\Api\V1;

use App\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/auth/login
     *
     * Gerar token de autenticação via Sanctum.
     * Aceita: email, password, device_name (opcional).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorrectas.'],
            ]);
        }

        if (! $user->activo) {
            return $this->error('A sua conta está desactivada. Contacte o administrador.', 403);
        }

        // Revogar tokens anteriores deste dispositivo
        $deviceName = $request->input('device_name', 'api');
        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken($deviceName);

        $user->load(['roles', 'provincia', 'municipio']);

        return $this->success([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user'       => $this->formatUser($user),
        ], 'Autenticação realizada com sucesso.');
    }

    /**
     * POST /api/v1/auth/logout
     *
     * Revogar o token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Sessão encerrada com sucesso.');
    }

    /**
     * GET /api/v1/auth/me
     *
     * Dados do utilizador autenticado.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['roles.permissoes', 'provincia', 'municipio']);

        return $this->success([
            'user'       => $this->formatUser($user),
            'permissoes' => $user->todasPermissoes(),
        ]);
    }

    /**
     * Formato padronizado do utilizador para respostas da API.
     */
    private function formatUser(\App\Models\User $user): array
    {
        return [
            'id'        => $user->id,
            'nome'      => $user->name,
            'email'     => $user->email,
            'activo'    => $user->activo,
            'roles'     => $user->roles->map(fn ($role) => [
                'id'        => $role->id,
                'nome'      => $role->nome,
                'descricao' => $role->descricao,
                'nivel'     => $role->nivel,
                'grupo'     => $role->grupo,
            ])->values(),
            'provincia' => $user->provincia ? [
                'id'   => $user->provincia->id,
                'nome' => $user->provincia->nome,
            ] : null,
            'municipio' => $user->municipio ? [
                'id'   => $user->municipio->id,
                'nome' => $user->municipio->nome,
                'slug' => $user->municipio->slug,
            ] : null,
        ];
    }
}
