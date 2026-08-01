<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    /**
     * @param  array<string, mixed>  $extra
     */
    protected function success(
        mixed $data = null,
        string $message = 'Success.',
        int $status = 200,
        array $extra = []
    ): JsonResponse {
        if ($data instanceof JsonResource) {
            $payload = $data->response()->getData(true);
            $data = $payload['data'] ?? null;
            $extra = array_merge($extra, array_intersect_key($payload, array_flip(['links', 'meta'])));
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra), $status);
    }
}
