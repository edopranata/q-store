# Laravel Custom Commands Guide

> Panduan lengkap penggunaan custom Artisan commands untuk generate Service, Repository, dan Repository Interface

## 📋 Daftar Commands

### 1. `make:service`
Membuat Service class lengkap dengan Repository Interface dan Repository implementation.

**Syntax:**
```bash
php artisan make:service {ModelName}
```

**Contoh:**
```bash
# Menggunakan nama model (tanpa suffix 'Service')
php artisan make:service Product

# Atau dengan suffix 'Service' (akan otomatis diproses)
php artisan make:service ProductService
```

**Output:**
- `app/Services/ProductService.php` (format: [ModelName]Service.php)
- `app/Repositories/Contracts/ProductRepositoryInterface.php`
- `app/Repositories/ProductRepository.php`

**Fitur Penamaan Otomatis:**
- ✅ Input `Product` → menghasilkan `ProductService.php`
- ✅ Input `ProductService` → menghasilkan `ProductService.php` (tidak duplikasi)
- ✅ Input `SalesTransaction` → menghasilkan `SalesTransactionService.php`
- ✅ Validasi model otomatis (model harus ada di `app/Models/`)
- ✅ Format nama file konsisten: `[ModelName]Service.php`

### 2. `make:repository-interface`
Membuat Repository Interface saja.

**Syntax:**
```bash
php artisan make:repository-interface {InterfaceName}
```

**Contoh:**
```bash
php artisan make:repository-interface CategoryRepositoryInterface
```

**Output:**
- `app/Repositories/Contracts/CategoryRepositoryInterface.php`

### 3. `make:repository`
Membuat Repository implementation saja.

**Syntax:**
```bash
php artisan make:repository {RepositoryName}
```

**Contoh:**
```bash
php artisan make:repository CategoryRepository
```

**Output:**
- `app/Repositories/CategoryRepository.php`

## 🏗️ Struktur yang Dihasilkan

### Service Layer

**Lokasi:** `app/Services/`

**Fitur yang disediakan:**
- ✅ CRUD operations (Create, Read, Update, Delete)
- ✅ Pagination dengan filtering
- ✅ Pencarian dan filtering data
- ✅ Validasi business logic
- ✅ Error handling
- ✅ Bulk operations
- ✅ Status management
- ✅ Statistics dan reporting

**Contoh method yang tersedia:**
```php
// Basic CRUD
public function getAllProducts(array $with = []): Collection
public function getPaginatedProducts(array $filters = [], array $with = [], int $perPage = 15): LengthAwarePaginator
public function getProductById(int $id, array $with = []): ?Product
public function createProduct(array $data): Product
public function updateProduct(int $id, array $data): Product
public function deleteProduct(int $id): bool

// Advanced features
public function getActiveProductsForOptions(): Collection
public function bulkUpdateStatus(array $productIds, string $status): int
public function getProductStatistics(): array
```

### Repository Interface

**Lokasi:** `app/Repositories/Contracts/`

**Fitur yang disediakan:**
- ✅ Contract definition untuk data access
- ✅ Method signatures untuk CRUD operations
- ✅ Pagination dan filtering contracts
- ✅ Search dan query contracts
- ✅ Bulk operations contracts
- ✅ Statistics contracts

### Repository Implementation

**Lokasi:** `app/Repositories/`

**Fitur yang disediakan:**
- ✅ Eloquent query implementation
- ✅ Advanced filtering dengan query builder
- ✅ Pagination dengan sorting
- ✅ Search functionality
- ✅ Relationship loading (eager loading)
- ✅ Bulk operations
- ✅ Database transactions
- ✅ Query optimization

**Contoh filtering yang tersedia:**
```php
// Dalam getPaginated method
$filters = [
    'search' => 'keyword',           // Search dalam name dan description
    'status' => 'active',            // Filter by status
    'category_id' => 1,              // Filter by category
    'price_min' => 10000,            // Filter harga minimum
    'price_max' => 50000,            // Filter harga maksimum
    'created_from' => '2024-01-01',  // Filter tanggal mulai
    'created_to' => '2024-12-31',    // Filter tanggal akhir
    'sort_by' => 'name',             // Sorting field
    'sort_order' => 'asc'            // Sorting direction
];
```

## 🔧 Konfigurasi

### Service Provider Registration

Commands telah terdaftar di `app/Providers/CommandServiceProvider.php`:

```php
class CommandServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeServiceCommand::class,
                MakeRepositoryCommand::class,
                MakeRepositoryInterfaceCommand::class,
            ]);
        }
    }
}
```

Provider terdaftar di `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,
    App\Providers\CommandServiceProvider::class, // ← Custom commands
];
```

### Stub Templates

Template files tersimpan di:
- `app/Console/Commands/stubs/service.stub`
- `app/Console/Commands/stubs/repository-interface.stub`
- `app/Console/Commands/stubs/repository.stub`

## 📝 Contoh Penggunaan Lengkap

### 1. Generate Service Lengkap

```bash
# Generate ProductService dengan repository interface dan implementation
# Menggunakan nama model (recommended)
php artisan make:service Product

# Atau menggunakan nama dengan suffix Service (juga valid)
php artisan make:service ProductService

# Keduanya menghasilkan output yang sama
```

**Hasil:**
```
app/
├── Services/
│   └── ProductService.php
└── Repositories/
    ├── Contracts/
    │   └── ProductRepositoryInterface.php
    └── ProductRepository.php
```

### 2. Penggunaan dalam Controller

```php
class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'status', 'category_id', 
            'price_min', 'price_max', 'sort_by', 'sort_order'
        ]);
        
        $products = $this->productService->getPaginatedProducts(
            $filters, 
            ['category'], 
            $request->get('per_page', 15)
        );
        
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return response()->json($product, 201);
    }
}
```

### 3. Dependency Injection Setup

Daftarkan binding di `app/Providers/RepositoryServiceProvider.php`:

```php
public function register(): void
{
    $this->app->bind(
        ProductRepositoryInterface::class,
        ProductRepository::class
    );
}
```

## 🎯 Best Practices

### 1. Naming Conventions
- **Service:** `{Model}Service` (e.g., `ProductService`)
  - File: `[ModelName]Service.php` (otomatis ditambahkan suffix 'Service')
  - Class: `{Model}Service`
  - Input command: bisa `Product` atau `ProductService` (keduanya menghasilkan output yang sama)
- **Repository Interface:** `{Model}RepositoryInterface` (e.g., `ProductRepositoryInterface`)
- **Repository:** `{Model}Repository` (e.g., `ProductRepository`)

### 2. Service Layer Guidelines
- Gunakan untuk business logic dan validasi
- Jangan langsung akses database dari service
- Selalu gunakan repository untuk data access
- Implement error handling yang proper
- Gunakan transactions untuk operasi kompleks

### 3. Repository Guidelines
- Focus pada data access logic saja
- Implement interface contracts dengan konsisten
- Optimize queries dengan eager loading
- Gunakan query builder untuk filtering kompleks
- Implement proper error handling

### 4. Interface Guidelines
- Definisikan contract yang jelas dan konsisten
- Gunakan type hints yang tepat
- Dokumentasikan method dengan PHPDoc
- Pertahankan backward compatibility

## 🚀 Advanced Features

### 1. Custom Filtering

Repository mendukung filtering advanced:

```php
$filters = [
    'search' => 'laptop',
    'status' => 'active',
    'category_id' => [1, 2, 3],        // Multiple categories
    'price_range' => [10000, 50000],   // Price range
    'has_discount' => true,            // Boolean filter
    'created_after' => '2024-01-01',   // Date filter
];
```

### 2. Bulk Operations

```php
// Bulk status update
$updated = $productService->bulkUpdateStatus([1, 2, 3], 'inactive');

// Bulk delete
$deleted = $productService->bulkDelete([1, 2, 3]);
```

### 3. Statistics & Reporting

```php
$stats = $productService->getProductStatistics();
// Returns: ['total' => 100, 'active' => 80, 'inactive' => 20]
```

## 🔍 Troubleshooting

### Command tidak ditemukan
```bash
# Clear cache dan reload
php artisan config:clear
php artisan cache:clear
```

### File sudah ada
```bash
# Gunakan flag --force untuk overwrite
php artisan make:service Product --force
# atau
php artisan make:service ProductService --force
```

### Model tidak ditemukan
```bash
# Pastikan model sudah ada di app/Models/
# Command akan menampilkan daftar model yang tersedia jika model tidak ditemukan
php artisan make:service NonExistentModel
# Output: Model 'NonExistentModel' not found. Available models: Product, Category, User...
```

### Namespace issues
Pastikan autoload sudah di-update:
```bash
composer dump-autoload
```

## 📚 Referensi

- [Laravel Artisan Console](https://laravel.com/docs/artisan)
- [Repository Pattern](https://laravel.com/docs/repositories)
- [Service Layer Pattern](https://laravel.com/docs/container)
- [Dependency Injection](https://laravel.com/docs/container)

---

**Dibuat dengan ❤️ untuk Q-POS Backend Development**