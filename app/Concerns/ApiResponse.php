<?php

namespace App\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Trait de resposta padronizada para a API.
 *
 * Formato:
 * {
 *   "success": true,
 *   "data": {},
 *   "message": "...",
 *   "errors": null,
 *   "meta": {}
 * }
 */
trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200, array $meta = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'errors'  => null,
        ];

        if ($meta) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    protected function created(mixed $data = null, string $message = 'Recurso criado com sucesso.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function error(string $message = 'Erro interno.', int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data'    => null,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    protected function notFound(string $message = 'Recurso não encontrado.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    protected function forbidden(string $message = 'Acesso negado.'): JsonResponse
    {
        return $this->error($message, 403);
    }

    protected function paginated(ResourceCollection $collection, string $message = 'OK'): JsonResponse
    {
        $paginator = $collection->resource;

        $meta = [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $collection->resolve(),
            'message' => $message,
            'errors'  => null,
            'meta'    => $meta,
        ]);
    }
}
