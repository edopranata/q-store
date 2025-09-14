<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\InventoryRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Repositories\SupplierRepository;
use App\Repositories\Contracts\WarehouseRepositoryInterface;
use App\Repositories\WarehouseRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind Repository Interfaces to their implementations
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
        
        $this->app->bind(
            InventoryRepositoryInterface::class,
            InventoryRepository::class
        );
        
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );
        
        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );
        
        $this->app->bind(
            CustomerRepositoryInterface::class,
            CustomerRepository::class
        );
        
        $this->app->bind(
            SupplierRepositoryInterface::class,
            SupplierRepository::class
        );
        
        $this->app->bind(
            WarehouseRepositoryInterface::class,
            WarehouseRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}