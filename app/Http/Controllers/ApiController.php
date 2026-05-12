<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function success($data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400, $errors = null): JsonResponse
    {
        $payload = ['success' => false, 'message' => $message];

        if ($errors) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
