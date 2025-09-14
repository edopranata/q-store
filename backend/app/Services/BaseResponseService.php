<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class BaseResponseService
{
    /**
     * Return a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @param array $meta
     * @return JsonResponse
     */
    public static function success(
        $data = null,
        string $message = 'Operation successful',
        int $statusCode = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a paginated success response.
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @param array $additionalMeta
     * @return JsonResponse
     */
    public static function paginated(
        LengthAwarePaginator $paginator,
        string $message = 'Data retrieved successfully',
        array $additionalMeta = []
    ): JsonResponse {
        $meta = array_merge([
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
        ], $additionalMeta);

        return self::success(
            $paginator->items(),
            $message,
            Response::HTTP_OK,
            $meta
        );
    }

    /**
     * Return an error response.
     *
     * @param string $message
     * @param int $statusCode
     * @param mixed $errors
     * @param array $meta
     * @return JsonResponse
     */
    public static function error(
        string $message = 'Operation failed',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        $errors = null,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response.
     *
     * @param ValidationException $exception
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(
        ValidationException $exception,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $exception->errors()
        );
    }

    /**
     * Return a not found error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(
        string $message = 'Resource not found'
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Return an unauthorized error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Return a forbidden error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Return a created response.
     *
     * @param mixed $data
     * @param string $message
     * @param array $meta
     * @return JsonResponse
     */
    public static function created(
        $data = null,
        string $message = 'Resource created successfully',
        array $meta = []
    ): JsonResponse {
        return self::success(
            $data,
            $message,
            Response::HTTP_CREATED,
            $meta
        );
    }

    /**
     * Return a no content response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function noContent(
        string $message = 'Operation completed successfully'
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], Response::HTTP_NO_CONTENT);
    }

    /**
     * Return a collection response.
     *
     * @param Collection $collection
     * @param string $message
     * @param array $meta
     * @return JsonResponse
     */
    public static function collection(
        Collection $collection,
        string $message = 'Data retrieved successfully',
        array $meta = []
    ): JsonResponse {
        $additionalMeta = array_merge([
            'total_items' => $collection->count(),
        ], $meta);

        return self::success(
            $collection->toArray(),
            $message,
            Response::HTTP_OK,
            $additionalMeta
        );
    }

    /**
     * Return a bad request error response.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function badRequest(
        string $message = 'Bad request',
        $errors = null
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_BAD_REQUEST,
            $errors
        );
    }

    /**
     * Return a conflict error response.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function conflict(
        string $message = 'Resource conflict',
        $errors = null
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_CONFLICT,
            $errors
        );
    }

    /**
     * Return a server error response.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    public static function serverError(
        string $message = 'Internal server error',
        $errors = null
    ): JsonResponse {
        return self::error(
            $message,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $errors
        );
    }
}