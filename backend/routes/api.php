<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionSyncController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductUnitController;
use App\Http\Controllers\Api\ProductPriceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\WarehouseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API Version 1
Route::prefix('v1')->group(function () {
    
    // =============================================================================
    // AUTH ROUTES - api/v1/auth/*
    // =============================================================================
    Route::prefix('auth')->group(function () {
        // Public authentication routes
        Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
        Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('api.v1.auth.forgot-password');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('api.v1.auth.reset-password');
        
        // Protected authentication routes
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.v1.auth.refresh');
            Route::put('/profile', [AuthController::class, 'updateProfile'])->name('api.v1.auth.profile.update');
            Route::put('/change-password', [AuthController::class, 'changePassword'])->name('api.v1.auth.change-password');
        });
    });
    
    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        
        // =============================================================================
        // STATS ROUTES - api/v1/stats/*
        // =============================================================================
        Route::prefix('stats')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'stats'])->name('api.v1.stats.dashboard');
            Route::get('/recent-sales', [DashboardController::class, 'recentSales'])->name('api.v1.stats.recent-sales');
            Route::get('/recent-transactions', [DashboardController::class, 'recentTransactions'])->name('api.v1.stats.recent-transactions');
            Route::get('/top-products', [DashboardController::class, 'topProducts'])->name('api.v1.stats.top-products');
            Route::get('/low-stock', [DashboardController::class, 'lowStock'])->name('api.v1.stats.low-stock');
            Route::get('/sales-chart', [DashboardController::class, 'salesChart'])->name('api.v1.stats.sales-chart');
        });
        
        // =============================================================================
        // OPTION ROUTES - api/v1/option/*
        // =============================================================================
        Route::prefix('options')->group(function () {
            Route::get('/categories', [CategoryController::class, 'options'])->name('api.v1.option.categories');
            Route::get('/units', [UnitController::class, 'options'])->name('api.v1.option.units');
            Route::get('/roles', [RoleController::class, 'options'])->name('api.v1.roles.options');
            Route::get('/suppliers', [SupplierController::class, 'options'])->name('api.v1.option.suppliers');
            Route::get('/customers', [CustomerController::class, 'options'])->name('api.v1.option.customers');
            Route::get('/warehouses', [WarehouseController::class, 'options'])->name('api.v1.option.warehouses');
        });
        
        // =============================================================================
        // MIDDLEWARE PERMISSION ROUTES - api/v1/*
        // =============================================================================

        Route::middleware(['permission'])->group(function () {
            // =============================================================================
            // MASTER DATA ROUTES - api/v1/master/*
            // =============================================================================
            Route::prefix('master')->group(function () {

                // Categories
                Route::apiResource('categories', CategoryController::class)->names([
                    'index' => 'api.v1.master.categories.index',
                    'store' => 'api.v1.master.categories.store',
                    'show' => 'api.v1.master.categories.show',
                    'update' => 'api.v1.master.categories.update',
                    'destroy' => 'api.v1.master.categories.destroy',
                ]);
            
                // Units
                Route::apiResource('units', UnitController::class)->names([
                    'index' => 'api.v1.master.units.index',
                    'store' => 'api.v1.master.units.store',
                    'show' => 'api.v1.master.units.show',
                    'update' => 'api.v1.master.units.update',
                    'destroy' => 'api.v1.master.units.destroy',
                ]);

                // Suppliers
                Route::get('/suppliers/search', [SupplierController::class, 'search'])->name('api.v1.master.suppliers.search');
                Route::get('/suppliers/statistics', [SupplierController::class, 'statistics'])->name('api.v1.master.suppliers.statistics');
                Route::patch('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('api.v1.master.suppliers.toggle-status');
                Route::apiResource('suppliers', SupplierController::class)->names([
                    'index' => 'api.v1.master.suppliers.index',
                    'store' => 'api.v1.master.suppliers.store',
                    'show' => 'api.v1.master.suppliers.show',
                    'update' => 'api.v1.master.suppliers.update',
                    'destroy' => 'api.v1.master.suppliers.destroy',
                ]);

                // Customers
                Route::get('/customers/search', [CustomerController::class, 'search'])->name('api.v1.master.customers.search');
                Route::get('/customers/statistics', [CustomerController::class, 'statistics'])->name('api.v1.master.customers.statistics');
                Route::patch('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('api.v1.master.customers.toggle-status');
                Route::apiResource('customers', CustomerController::class)->names([
                    'index' => 'api.v1.master.customers.index',
                    'store' => 'api.v1.master.customers.store',
                    'show' => 'api.v1.master.customers.show',
                    'update' => 'api.v1.master.customers.update',
                    'destroy' => 'api.v1.master.customers.destroy',
                ]);

                // Warehouses
                Route::get('/warehouses/search', [WarehouseController::class, 'search'])->name('api.v1.master.warehouses.search');
                Route::get('/warehouses/statistics', [WarehouseController::class, 'statistics'])->name('api.v1.master.warehouses.statistics');
                Route::get('/warehouses/generate-code', [WarehouseController::class, 'generateCode'])->name('api.v1.master.warehouses.generate-code');
                Route::patch('/warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('api.v1.master.warehouses.toggle-status');
                Route::apiResource('warehouses', WarehouseController::class)->names([
                    'index' => 'api.v1.master.warehouses.index',
                    'store' => 'api.v1.master.warehouses.store',
                    'show' => 'api.v1.master.warehouses.show',
                    'update' => 'api.v1.master.warehouses.update',
                    'destroy' => 'api.v1.master.warehouses.destroy',
                ]);
            });
            // =============================================================================
            // MANAGEMENT ROUTES - api/v1/management/*
            // =============================================================================
            Route::prefix('management')->group(function () {

                // User management
                Route::apiResource('users', UserController::class)->names([
                    'index' => 'api.v1.management.users.index',
                    'store' => 'api.v1.management.users.store',
                    'show' => 'api.v1.management.users.show',
                    'update' => 'api.v1.management.users.update',
                    'destroy' => 'api.v1.management.users.destroy',
                ]);
                Route::post('/users/{user}/sync-roles', [UserController::class, 'syncRoles'])->name('api.v1.management.users.sync-roles');
                Route::post('/users/{user}/sync-permissions', [UserController::class, 'syncPermissions'])->name('api.v1.management.users.sync-permissions');
        
            
                // Role management
                Route::apiResource('roles', RoleController::class)->names([
                    'index' => 'api.v1.management.roles.index',
                    'store' => 'api.v1.management.roles.store',
                    'show' => 'api.v1.management.roles.show',
                    'update' => 'api.v1.management.roles.update',
                    'destroy' => 'api.v1.management.roles.destroy',
                ]);
                Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('api.v1.management.roles.permissions');
                Route::post('/roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('api.v1.management.roles.sync-permissions');
            
            
                // Permission management
                Route::apiResource('permissions', PermissionSyncController::class)->names([
                    'index' => 'api.v1.management.permissions.index',
                    'store' => 'api.v1.management.permissions.store',
                    'show' => 'api.v1.management.permissions.show',
                    'update' => 'api.v1.management.permissions.update',
                    'destroy' => 'api.v1.management.permissions.destroy',
                ]);
                
                // Permission Sync
                Route::post('permissions/sync', [PermissionSyncController::class, 'syncPermissions'])->name('api.v1.management.permissions.sync');
                Route::get('permissions/routes', [PermissionSyncController::class, 'getApiRoutes'])->name('api.v1.management.permissions.routes');
                Route::delete('permissions/cleanup', [PermissionSyncController::class, 'cleanupOrphanedPermissions'])->name('api.v1.management.permissions.cleanup');
            });
        
            // =============================================================================
            // PRODUCT ROUTES - api/v1/product/*
            // =============================================================================
            Route::prefix('product')->group(function () {
                Route::apiResource('products', ProductController::class)->names([
                    'index' => 'api.v1.product.products.index',
                    'store' => 'api.v1.product.products.store',
                    'show' => 'api.v1.product.products.show',
                    'update' => 'api.v1.product.products.update',
                    'destroy' => 'api.v1.product.products.destroy'
                ]);
                
                Route::get('/products/search', [ProductController::class, 'search'])->name('api.v1.product.products-search');
                Route::get('/products/category/{categoryId}', [ProductController::class, 'byCategory'])->name('api.v1.product.products-by-category');
                Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('api.v1.product.products-low-stock');
                Route::get('/products/statistics', [ProductController::class, 'statistics'])->name('api.v1.product.products-statistics');
                Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('api.v1.product.products-toggle-status');
                Route::post('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('api.v1.product.products-duplicate');
                
                // Product Unit Management (Nested Resource)
                Route::apiResource('products.units', ProductUnitController::class)->except(['create', 'edit'])->names([
                    'index' => 'api.v1.product.products-units.index',
                    'store' => 'api.v1.product.products-units.store',
                    'show' => 'api.v1.product.products-units.show',
                    'update' => 'api.v1.product.products-units.update',
                    'destroy' => 'api.v1.product.products-units.destroy'
                ]);
                
                // Product Price Management (Nested Resource)
                Route::apiResource('products.prices', ProductPriceController::class)->except(['create', 'edit'])->names([
                    'index' => 'api.v1.product.products-prices.index',
                    'store' => 'api.v1.product.products-prices.store',
                    'show' => 'api.v1.product.products-prices.show',
                    'update' => 'api.v1.product.products-prices.update',
                    'destroy' => 'api.v1.product.products-prices.destroy'
                ]);
                
                Route::get('/products/{product}/current-price', [ProductPriceController::class, 'getCurrentPrice'])->name('api.v1.product.products.current-price');
            });
        
            // =============================================================================
            // INVENTORY ROUTES - api/v1/inventory/*
            // =============================================================================
            Route::prefix('inventory')->group(function () {
                Route::get('/stocks', [InventoryController::class, 'index'])->name('api.v1.inventory.stocks.index');
                Route::post('/stocks/adjust', [InventoryController::class, 'adjustStock'])->name('api.v1.inventory.stocks.adjust');
                Route::post('/stocks/bulk-adjust', [InventoryController::class, 'bulkAdjustStock'])->name('api.v1.inventory.stocks.bulk-adjust');
                Route::post('/stocks/check-availability', [InventoryController::class, 'checkAvailability'])->name('api.v1.inventory.stocks.check-availability');
            });
        });
    });
    
    // Additional specific routes can be added here
    // For example:
    // Route::get('products/{product}/inventory', [ProductController::class, 'getInventory']);
    // Route::post('sales-transactions/{transaction}/payments', [PaymentController::class, 'addPayment']);
});