<?php

namespace App\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Success response with data and pagination.
     *
     * @param mixed $data_model
     * @param mixed $data_resource
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function successResponseWithDataIndex($data_model = null, $data_resource = null, $message = null, $code = 200): JsonResponse
    {
        $response = [
            'code' => $code,
            'status' => true,
            'message' => $message,
            'data' => $data_resource,
        ];

        if ($data_model instanceof LengthAwarePaginator || $data_model instanceof Paginator) {
            $response['pagination'] = [
                'current_page' => $data_model->currentPage(),
                'per_page' => $data_model->perPage(),
                'total' => $data_model->total(),
                'last_page' => $data_model->lastPage()
            ];
        } elseif (is_array($data_model) && isset($data_model['current_page'])) {
            $response['pagination'] = [
                'current_page' => $data_model['current_page'],
                'per_page' => $data_model['per_page'],
                'total' => $data_model['total'],
                'last_page' => $data_model['last_page']
            ];
        } else if ($data_model['pagination']['current_page']) {
            $response['pagination'] = [
                'current_page' => $data_model['pagination']['current_page'],
                'per_page' => $data_model['pagination']['per_page'],
                'total' => $data_model['pagination']['total'],
                'last_page' => $data_model['pagination']['last_page']
            ];
        } else {
            $response['pagination'] = null;
        }

        return response()->json($response, $code);
    }

    /**
     * Success response with data (without pagination).
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function successResponseWithData($data = null, $message = null, $code = 200): JsonResponse
    {
        $response = [
            'code' => $code,
            'status' => true,
            'message' => $message,
            'data' => $data,
        ];
        return response()->json($response, $code);
    }

    /**
     * Error response with data.
     *
     * @param mixed $errors
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorResponseWithData($errors = null, $message = null, $code = 400): JsonResponse
    {
        $response = [
            'code' => $code,
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ];
        return response()->json($response, $code);
    }

    /**
     * Success response.
     *
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function successResponse($message = null, $code = 200): JsonResponse
    {
        $response = [
            'code' => $code,
            'status' => true,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    /**
     * Error response.
     *
     * @param string|null $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorResponse($message = null, $code = 400): JsonResponse
    {
        $response = [
            'code' => $code,
            'status' => false,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }
}
