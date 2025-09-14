<?php

namespace App\Http\Traits;

use App\Services\BaseResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

trait ApiResponseTrait
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
    protected function successResponse(
        $data = null,
        string $message = 'Operation successful',
        int $statusCode = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        return BaseResponseService::success($data, $message, $statusCode, $meta);
    }

    /**
     * Return a paginated success response.
     *
     * @param LengthAwarePaginator $paginator
     * @param string $message
     * @param array $additionalMeta
     * @return JsonResponse
     */
    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $message = 'Data retrieved successfully',
        array $additionalMeta = []
    ): JsonResponse {
        return BaseResponseService::paginated($paginator, $message, $additionalMeta);
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
    protected function errorResponse(
        string $message = 'Operation failed',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        $errors = null,
        array $meta = []
    ): JsonResponse {
        return BaseResponseService::error($message, $statusCode, $errors, $meta);
    }

    /**
     * Return a validation error response.
     *
     * @param ValidationException $exception
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        ValidationException $exception,
        string $message = 'Validation failed'
    ): JsonResponse {
        return BaseResponseService::validationError($exception, $message);
    }

    /**
     * Return a not found error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(
        string $message = 'Resource not found'
    ): JsonResponse {
        return BaseResponseService::notFound($message);
    }

    /**
     * Return an unauthorized error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized access'
    ): JsonResponse {
        return BaseResponseService::unauthorized($message);
    }

    /**
     * Return a forbidden error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(
        string $message = 'Access forbidden'
    ): JsonResponse {
        return BaseResponseService::forbidden($message);
    }

    /**
     * Return a created response.
     *
     * @param mixed $data
     * @param string $message
     * @param array $meta
     * @return JsonResponse
     */
    protected function createdResponse(
        $data = null,
        string $message = 'Resource created successfully',
        array $meta = []
    ): JsonResponse {
        return BaseResponseService::created($data, $message, $meta);
    }

    /**
     * Return a no content response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function noContentResponse(
        string $message = 'Operation completed successfully'
    ): JsonResponse {
        return BaseResponseService::noContent($message);
    }

    /**
     * Return a collection response.
     *
     * @param Collection $collection
     * @param string $message
     * @param array $meta
     * @return JsonResponse
     */
    protected function collectionResponse(
        Collection $collection,
        string $message = 'Data retrieved successfully',
        array $meta = []
    ): JsonResponse {
        return BaseResponseService::collection($collection, $message, $meta);
    }

    /**
     * Return a bad request error response.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function badRequestResponse(
        string $message = 'Bad request',
        $errors = null
    ): JsonResponse {
        return BaseResponseService::badRequest($message, $errors);
    }

    /**
     * Return a conflict error response.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function conflictResponse(
        string $message = 'Resource conflict',
        $errors = null
    ): JsonResponse {
        return BaseResponseService::conflict($message, $errors);
    }

    /**
     * Return a server error response.
     *
     * @param string $message
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function serverErrorResponse(
        string $message = 'Internal server error',
        $errors = null
    ): JsonResponse {
        return BaseResponseService::serverError($message, $errors);
    }

    /**
     * Handle common try-catch pattern for controller methods.
     *
     * @param callable $callback
     * @param string $errorMessage
     * @return JsonResponse
     */
    protected function handleRequest(
        callable $callback,
        string $errorMessage = 'Operation failed'
    ): JsonResponse {
        try {
            return $callback();
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error($errorMessage . ': ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->serverErrorResponse(
                $errorMessage,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    /**
     * Transform data using a resource class if provided.
     *
     * @param mixed $data
     * @param string|null $resourceClass
     * @return mixed
     */
    protected function transformData($data, ?string $resourceClass = null)
    {
        if ($resourceClass && class_exists($resourceClass)) {
            if ($data instanceof LengthAwarePaginator) {
                return $resourceClass::collection($data);
            } elseif ($data instanceof Collection) {
                return $resourceClass::collection($data);
            } else {
                return new $resourceClass($data);
            }
        }

        return $data;
    }
}