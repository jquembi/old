<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RegistarAcesso;
use App\Http\Middleware\VerificarAcessoMunicipio;
use App\Http\Middleware\VerificarNivel;
use App\Http\Middleware\VerificarPermissao;
use App\Application\Services\ErrorNotificationService;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'permissao'     => VerificarPermissao::class,
            'nivel'         => VerificarNivel::class,
            'log.acesso'    => RegistarAcesso::class,
            'check.role'    => CheckRole::class,
            'municipio.acesso' => VerificarAcessoMunicipio::class,
        ]);

        // Rate limiting para API: 60 req/min por utilizador
        $middleware->api(prepend: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            app(ErrorNotificationService::class)->notify($e);
        });

        // Respostas JSON padronizadas para erros na API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request): ?Response {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Não autenticado.',
                    'errors'  => null,
                ], 401);
            }
            return null;
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request): ?Response {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => $e->getMessage() ?: 'Acesso negado.',
                    'errors'  => null,
                ], 403);
            }
            return null;
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request): ?Response {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Recurso não encontrado.',
                    'errors'  => null,
                ], 404);
            }
            return null;
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request): ?Response {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Dados inválidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            return null;
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request): ?Response {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Demasiadas requisições. Tente novamente mais tarde.',
                    'errors'  => null,
                ], 429);
            }
            return null;
        });
    })->create();
