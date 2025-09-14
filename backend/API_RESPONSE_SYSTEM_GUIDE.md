# API Response System Guide - Q-POS

## Table of Contents
1. [Overview](#overview)
2. [BaseResponseService](#baseresponseservice)
3. [Response Structure](#response-structure)
4. [Implementation Guide](#implementation-guide)
5. [Usage Examples](#usage-examples)
6. [Best Practices](#best-practices)
7. [Integration with Existing System](#integration-with-existing-system)
8. [Troubleshooting](#troubleshooting)

## Overview

Sistem API Response Q-POS dirancang untuk memberikan konsistensi dalam format respons API di seluruh aplikasi. Dengan menggunakan **BaseResponseService** sebagai komponen utama, sistem ini memastikan semua endpoint API menghasilkan respons yang terstruktur, konsisten, dan mudah dipahami oleh client.

### Key Features
- **Konsistensi Format**: Semua respons mengikuti struktur yang sama
- **Metadata Otomatis**: Timestamp, version, dan informasi tambahan disertakan secara otomatis
- **Pagination Support**: Dukungan lengkap untuk respons dengan pagination
- **Error Handling**: Penanganan error yang terstandarisasi
- **Direct Data Handling**: Menangani data langsung tanpa layer Resource tambahan

## BaseResponseService

### Overview

BaseResponseService adalah service utama yang bertanggung jawab untuk membuat respons API yang konsisten. Service ini menyediakan berbagai method untuk berbagai jenis respons dan secara otomatis menangani format, metadata, dan struktur respons.

### Available Methods

#### Success Responses
```php
// Basic success response
success($data, $message = 'Success', $statusCode = 200)

// Paginated response (with ResourceCollection support)
paginated($paginatedData, $message = 'Data retrieved successfully')

// Created response (201)
created($data, $message = 'Resource created successfully')

// No content response (204)
noContent($message = 'No content')

// Collection response
collection($data, $message = 'Collection retrieved successfully')
```

#### Error Responses
```php
// Generic error
error($message, $statusCode = 500, $errors = null)

// Validation error (422)
validationError($errors, $message = 'Validation failed')

// Not found (404)
notFound($message = 'Resource not found')

// Unauthorized (401)
unauthorized($message = 'Unauthorized')

// Forbidden (403)
forbidden($message = 'Forbidden')

// Bad request (400)
badRequest($message = 'Bad request', $errors = null)

// Conflict (409)
conflict($message = 'Conflict')

// Server error (500)
serverError($message = 'Internal server error')
```

### Implementation Details

```php
<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BaseResponseService
{
    /**
     * Create a success response
     */
    public function success($data, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        $response['meta'] = [
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0')
        ];

        return response()->json($response, $statusCode);
    }

    /**
     * Create a paginated response
     */
    public function paginated($paginatedData, string $message = 'Data retrieved successfully'): JsonResponse
    {
        if ($paginatedData instanceof LengthAwarePaginator) {
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $paginatedData->items(),
                'meta' => [
                    'timestamp' => now()->toISOString(),
                    'version' => config('app.version', '1.0'),
                    'total_items' => $paginatedData->total(),
                    'pagination' => [
                        'current_page' => $paginatedData->currentPage(),
                        'last_page' => $paginatedData->lastPage(),
                        'per_page' => $paginatedData->perPage(),
                        'total' => $paginatedData->total(),
                        'from' => $paginatedData->firstItem(),
                        'to' => $paginatedData->lastItem(),
                        'has_more_pages' => $paginatedData->hasMorePages()
                    ]
                ],
                'links' => [
                    'first' => $paginatedData->url(1),
                    'last' => $paginatedData->url($paginatedData->lastPage()),
                    'prev' => $paginatedData->previousPageUrl(),
                    'next' => $paginatedData->nextPageUrl()
                ]
            ];
            
            return response()->json($response);
        }

        return $this->success($paginatedData, $message);
    }

    /**
     * Create an error response
     */
    public function error(string $message, int $statusCode = 500, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        $response['meta'] = [
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0')
        ];

        return response()->json($response, $statusCode);
    }
}
```

## Response Structure

### Standard Success Response
```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "Sample Data",
    "created_at": "2024-01-01T00:00:00.000000Z",
    "updated_at": "2024-01-01T00:00:00.000000Z"
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00.000000Z",
    "version": "1.0"
  }
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Categories retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Electronics",
      "description": "Electronic products",
      "status": "active",
      "products_count": 25
    }
  ],
  "meta": {
    "timestamp": "2024-01-01T00:00:00.000000Z",
    "version": "1.0",
    "total_items": 100,
    "pagination": {
      "current_page": 1,
      "last_page": 7,
      "per_page": 15,
      "total": 100,
      "from": 1,
      "to": 15,
      "has_more_pages": true
    }
  },
  "links": {
    "first": "http://localhost:8000/api/v1/categories?page=1",
    "last": "http://localhost:8000/api/v1/categories?page=7",
    "prev": null,
    "next": "http://localhost:8000/api/v1/categories?page=2"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "email": [
      "The email field must be a valid email address."
    ]
  },
  "meta": {
    "timestamp": "2024-01-01T00:00:00.000000Z",
    "version": "1.0"
  }
}
```

## Implementation Guide

### Step 1: Setup BaseResponseService

1. **Dependency Injection in Controller**
```php
class CategoryController extends Controller
{
    use ApiResponseTrait;
    
    protected CategoryService $categoryService;
    protected BaseResponseService $responseService;
    
    public function __construct(
        CategoryService $categoryService,
        BaseResponseService $responseService
    ) {
        $this->categoryService = $categoryService;
        $this->responseService = $responseService;
    }
}
```

### Step 2: Implement Controller Methods

**Implementation with BaseResponseService:**
```php
public function index(Request $request): JsonResponse
{
    return $this->handleRequest(function () use ($request) {
        $filters = $request->only(['search', 'status']);
        $perPage = min($request->get('per_page', 15), 50);
        
        $categories = $this->categoryService->getPaginated(
            $filters, 
            ['products'], 
            ['products'], 
            $perPage
        );
        
        return $this->responseService->paginated(
            $categories,
            'Categories retrieved successfully'
        );
    });
}
```

### Step 3: Handle Different Response Types

```php
// Single resource
public function show(int $id): JsonResponse
{
    return $this->handleRequest(function () use ($id) {
        $category = $this->categoryService->find($id, ['products']);
        
        if (!$category) {
            return $this->responseService->notFound('Category not found');
        }
        
        return $this->responseService->success(
            $category,
            'Category retrieved successfully'
        );
    });
}

// Create resource
public function store(Request $request): JsonResponse
{
    return $this->handleRequest(function () use ($request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        $category = $this->categoryService->createCategory($validated);
        
        return $this->responseService->created(
            $category,
            'Category created successfully'
        );
    });
}

// Update resource
public function update(Request $request, int $id): JsonResponse
{
    return $this->handleRequest(function () use ($request, $id) {
        $category = $this->categoryService->find($id);
        
        if (!$category) {
            return $this->responseService->notFound('Category not found');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        $updatedCategory = $this->categoryService->updateCategory($category, $validated);
        
        return $this->responseService->success(
            $updatedCategory,
            'Category updated successfully'
        );
    });
}

// Delete resource
public function destroy(int $id): JsonResponse
{
    return $this->handleRequest(function () use ($id) {
        $category = $this->categoryService->find($id);
        
        if (!$category) {
            return $this->responseService->notFound('Category not found');
        }
        
        $this->categoryService->deleteCategory($category);
        
        return $this->responseService->success(
            null,
            'Category deleted successfully'
        );
    });
}
```

## Usage Examples

### Basic CRUD Operations

```php
class ProductController extends Controller
{
    use ApiResponseTrait;
    
    public function __construct(
        ProductService $productService,
        BaseResponseService $responseService
    ) {
        $this->productService = $productService;
        $this->responseService = $responseService;
    }
    
    public function index(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['search', 'category_id', 'status']);
            $perPage = min($request->get('per_page', 15), 50);
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $products = $this->productService->getPaginated(
                $filters,
                ['category', 'units'],
                ['units'],
                $perPage,
                $sortBy,
                $sortOrder
            );
            
            return $this->responseService->paginated(
                $products,
                'Products retrieved successfully'
            );
        });
    }
}
```

### Custom Response Scenarios

```php
// Options/Dropdown endpoint
public function options(Request $request): JsonResponse
{
    return $this->handleRequest(function () use ($request) {
        $categories = $this->categoryService->getActiveCategories();
        
        return $this->responseService->success(
            $categories,
            'Category options retrieved successfully'
        );
    });
}

// Statistics endpoint
public function statistics(): JsonResponse
{
    return $this->handleRequest(function () {
        $stats = $this->categoryService->getStatistics();
        
        return $this->responseService->success(
            $stats,
            'Category statistics retrieved successfully'
        );
    });
}

// Bulk operations
public function bulkDelete(Request $request): JsonResponse
{
    return $this->handleRequest(function () use ($request) {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:categories,id'
        ]);
        
        $deletedCount = $this->categoryService->bulkDelete($validated['ids']);
        
        return $this->responseService->success(
            ['deleted_count' => $deletedCount],
            "{$deletedCount} categories deleted successfully"
        );
    });
}
```

### Error Handling Examples

```php
public function store(Request $request): JsonResponse
{
    return $this->handleRequest(function () use ($request) {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string'
            ]);
            
            $category = $this->categoryService->createCategory($validated);
            
            return $this->responseService->created(
                $category,
                'Category created successfully'
            );
            
        } catch (ValidationException $e) {
            return $this->responseService->validationError(
                $e->errors(),
                'Validation failed'
            );
        } catch (\Exception $e) {
            return $this->responseService->serverError(
                'Failed to create category'
            );
        }
    });
}
```

## Best Practices

### 1. Consistent Response Structure
- **Selalu gunakan BaseResponseService** untuk semua endpoint API
- **Jangan mix** antara BaseResponseService dan direct response dalam satu controller
- **Pastikan** semua respons memiliki struktur yang sama

### 2. Proper HTTP Status Codes
```php
// Use appropriate methods for different scenarios
$this->responseService->success($data);           // 200 OK
$this->responseService->created($data);           // 201 Created
$this->responseService->noContent();              // 204 No Content
$this->responseService->badRequest($message);     // 400 Bad Request
$this->responseService->unauthorized();           // 401 Unauthorized
$this->responseService->forbidden();              // 403 Forbidden
$this->responseService->notFound();               // 404 Not Found
$this->responseService->conflict($message);       // 409 Conflict
$this->responseService->validationError($errors); // 422 Unprocessable Entity
$this->responseService->serverError();            // 500 Internal Server Error
```

### 3. Error Handling
- **Gunakan handleRequest() wrapper** untuk automatic error handling
- **Validate input** sebelum processing
- **Return appropriate error responses** dengan pesan yang jelas

### 4. Pagination
- **Gunakan paginated() method** untuk data yang banyak
- **Set reasonable default** untuk per_page (15)
- **Limit maximum per_page** untuk performance (50)

### 5. Message Consistency
```php
// Use consistent message patterns
'Data retrieved successfully'
'Resource created successfully'
'Resource updated successfully'
'Resource deleted successfully'
'Resource not found'
'Validation failed'
```

### 6. Service Integration
- **Inject BaseResponseService** di constructor
- **Use dependency injection** untuk better testability
- **Keep controller thin** - business logic di service layer

## Integration with Existing System

### Migration Strategy

#### Phase 1: Setup BaseResponseService
1. Create BaseResponseService class
2. Register in service container
3. Update existing controllers one by one

#### Phase 2: Update Controllers
1. **Inject BaseResponseService** in constructor
2. **Replace direct response** dengan BaseResponseService methods
3. **Update return types** ke JsonResponse
4. **Test each endpoint** untuk memastikan compatibility

#### Phase 3: Cleanup
1. **Update API documentation**
2. **Update frontend integration** jika diperlukan
3. **Test all endpoints** untuk memastikan konsistensi

### Backward Compatibility

BaseResponseService dirancang untuk **konsistensi dan simplicity**:

- **Response structure** yang terstandarisasi untuk semua endpoint
- **Pagination format** yang konsisten dengan Laravel pagination
- **Error format** yang mengikuti Laravel validation error format
- **HTTP status codes** yang sesuai dengan REST API standards

### Example Migration

**Before (Direct Model Usage):**
```php
class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with('products')->paginate(15);
        return response()->json($categories);
    }
    
    public function show(Category $category)
    {
        return response()->json($category->load('products'));
    }
}
```

**After:**
```php
class CategoryController extends Controller
{
    use ApiResponseTrait;
    
    public function __construct(
        CategoryService $categoryService,
        BaseResponseService $responseService
    ) {
        $this->categoryService = $categoryService;
        $this->responseService = $responseService;
    }
    
    public function index(Request $request): JsonResponse
    {
        return $this->handleRequest(function () use ($request) {
            $filters = $request->only(['search', 'status']);
            $categories = $this->categoryService->getPaginated($filters, ['products']);
            
            return $this->responseService->paginated(
                $categories,
                'Categories retrieved successfully'
            );
        });
    }
    
    public function show(int $id): JsonResponse
    {
        return $this->handleRequest(function () use ($id) {
            $category = $this->categoryService->find($id, ['products']);
            
            if (!$category) {
                return $this->responseService->notFound('Category not found');
            }
            
            return $this->responseService->success(
                $category,
                'Category retrieved successfully'
            );
        });
    }
}
```

## Troubleshooting

### Common Issues

#### 1. Response Format Inconsistency
**Problem:** Beberapa endpoint menggunakan format yang berbeda

**Solution:**
- Pastikan semua controller menggunakan BaseResponseService
- Check apakah ada endpoint yang masih menggunakan direct response
- Verify dependency injection setup

#### 2. Pagination Not Working
**Problem:** Pagination metadata tidak muncul

**Solution:**
```php
// Pastikan menggunakan paginated() method
return $this->responseService->paginated($paginatedData);

// Bukan success() method
return $this->responseService->success($paginatedData); // ❌ Wrong
```

#### 3. Error Handling Not Consistent
**Problem:** Error response format berbeda-beda

**Solution:**
```php
// Use handleRequest() wrapper
return $this->handleRequest(function () {
    // Your logic here
});

// Or use specific error methods
return $this->responseService->validationError($errors);
return $this->responseService->notFound('Resource not found');
```

#### 4. Missing Meta Information
**Problem:** Response tidak memiliki timestamp atau version

**Solution:**
- Check BaseResponseService implementation
- Verify config('app.version') is set
- Ensure proper method usage

### Debug Tips

#### 1. Log Response Data
```php
public function index(Request $request): JsonResponse
{
    return $this->handleRequest(function () use ($request) {
        $response = $this->responseService->paginated($data);
        
        // Log for debugging
        Log::info('API Response:', $response->getData(true));
        
        return $response;
    });
}
```

#### 2. Validate Response Structure
```php
// In tests
public function test_categories_index_response_structure()
{
    $response = $this->getJson('/api/v1/categories');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => ['id', 'name', 'description']
            ],
            'meta' => [
                'timestamp',
                'version',
                'pagination'
            ],
            'links'
        ]);
}
```

#### 3. Check Service Dependencies
```php
// Verify service is properly injected
dd($this->responseService); // Should not be null

// Check service methods
dd(get_class_methods($this->responseService));
```

## HTTP Status Codes Reference

| Code | Method | Usage |
|------|--------|-------|
| 200 | `success()` | Request berhasil |
| 201 | `created()` | Resource berhasil dibuat |
| 204 | `noContent()` | Request berhasil tanpa response body |
| 400 | `badRequest()` | Request tidak valid |
| 401 | `unauthorized()` | Authentication required |
| 403 | `forbidden()` | Access denied |
| 404 | `notFound()` | Resource tidak ditemukan |
| 409 | `conflict()` | Conflict dengan state saat ini |
| 422 | `validationError()` | Validation error |
| 500 | `serverError()` | Server error |

## Conclusion

BaseResponseService menyediakan solusi komprehensif untuk standardisasi respons API di Q-POS. Dengan mengikuti panduan ini, developer dapat:

- **Membuat API yang konsisten** dengan format respons yang terstandarisasi
- **Mengurangi boilerplate code** dengan menggunakan service methods
- **Meningkatkan maintainability** dengan centralized response handling
- **Mempermudah testing** dengan predictable response structure
- **Meningkatkan developer experience** dengan clear error messages dan proper HTTP status codes

Untuk pertanyaan lebih lanjut atau kontribusi, silakan hubungi tim development atau buat issue di repository project.