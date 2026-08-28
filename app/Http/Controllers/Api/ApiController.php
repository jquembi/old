<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    use AuthorizesRequests;
    protected function success(mixed $data = null, string $message = 'Operação realizada com sucesso', int $code = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => null,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    protected function created(mixed $data = null, string $message = 'Recurso criado com sucesso'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(string $message = 'Erro interno', int $code = 500, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    protected function notFound(string $message = 'Recurso não encontrado'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function forbidden(string $message = 'Sem permissão para esta ação'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function paginated($paginator, string $message = 'Listagem realizada com sucesso'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'message' => $message,
            'errors' => null,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
