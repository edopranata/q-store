<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions from API routes
        $this->createPermissionsFromRoutes();

        // Create default roles
        $this->createDefaultRoles();

        // Create admin user
        $this->createAdminUser();
    }

    private function createPermissionsFromRoutes()
    {
        // Get permissions from routes using the same logic as /permissions endpoint
        $permissions = collect(Route::getRoutes())
            ->whereNotNull('action.as')
            ->map(function ($route) {
                $action = collect($route->action)->toArray();
                $as = str($action['as'])->lower();
                if ($as->startsWith('api') && in_array('permission', $action['middleware'])) {
                    return $action['as'];
                } else {
                    return null;
                }
            })
            ->filter(function ($value) {
                return !is_null($value);
            })
            ->unique()
            ->values();

        // Create permissions for each filtered route with descriptions
        foreach ($permissions as $permissionName) {
            $description = $this->generatePermissionDescription($permissionName);
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['description' => $description]
            );
        }

        $this->command->info('Created ' . count($permissions) . ' permissions from API routes with permission middleware.');
    }

    /**
     * Generate human-readable description for permission based on route name
     */
    private function generatePermissionDescription(string $permissionName): string
    {
        // Remove 'api.' prefix for cleaner processing
        $routeName = str_replace('api.', '', $permissionName);
        
        // Split route name into parts
        $parts = explode('.', $routeName);
        $version = $parts[0] ?? ''; // v1
        $prefix = $parts[1] ?? '';   // auth, stats, options, master, management, product, inventory
        $resource = $parts[2] ?? ''; // users, roles, categories, products, etc
        $action = $parts[3] ?? '';   // index, store, show, update, destroy
        $subAction = $parts[4] ?? ''; // untuk nested routes seperti sync-roles
        
        // Resource name mappings to Indonesian
        $resourceMappings = [
            'v1' => '',
            'auth' => 'Autentikasi',
            'stats' => 'Statistik',
            'option' => 'Opsi',
            'master' => 'Master Data',
            'management' => 'Manajemen',
            'product' => 'Produk',
            'inventory' => 'Inventori',
            'transactions' => 'Transaksi',
            'report' => 'Laporan',
            'dashboard' => 'Dashboard',
            'users' => 'Pengguna',
            'roles' => 'Role',
            'permissions' => 'Permission',
            'categories' => 'Kategori',
            'units' => 'Satuan',
            'products' => 'Produk',
            'products-units' => 'Satuan Produk',
            'products-prices' => 'Harga Produk',
            'customers' => 'Pelanggan',
            'suppliers' => 'Supplier',
            'warehouses' => 'Gudang',
            'sales' => 'Penjualan',
            'purchases' => 'Pembelian',
            'purchase-orders' => 'Purchase Order',
            'payments' => 'Pembayaran',
            'stocks' => 'Stok'
        ];
        
        // Action mappings to Indonesian
        $actionMappings = [
            'index' => 'Melihat daftar',
            'show' => 'Melihat detail',
            'store' => 'Menambah',
            'update' => 'Mengubah',
            'destroy' => 'Menghapus',
            'active' => 'Mengaktifkan/menonaktifkan',
            'statistics' => 'Melihat statistik',
            'options' => 'Melihat opsi',
            'login' => 'Login',
            'logout' => 'Logout',
            'me' => 'Melihat profil',
            'assign-role' => 'Menugaskan role',
            'remove-role' => 'Menghapus role',
            'with-user-count' => 'Melihat dengan jumlah pengguna',
            'permissions' => 'Melihat permission',
            'assign-permissions' => 'Menugaskan permission',
            'revoke-permissions' => 'Mencabut permission',
            'bulk-assign-permissions' => 'Menugaskan permission secara massal',
            'sync' => 'Sinkronisasi',
            'cleanup' => 'Membersihkan',
            'routes' => 'Melihat route',
            'prices' => 'Melihat harga',
            'units' => 'Melihat satuan produk',
            'stock' => 'Melihat stok',
            'movements' => 'Melihat pergerakan',
            'receive' => 'Menerima',
            'items' => 'Melihat item',
            'transactions' => 'Melihat transaksi',
            'daily' => 'Harian',
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan'
        ];
        
        // Handle special product routes with dash separator
        if (str_contains($permissionName, 'products-units')) {
            $resourceName = 'satuan produk';
            $actionName = $actionMappings[$action] ?? 'Mengelola';
        } elseif (str_contains($permissionName, 'products-prices')) {
            $resourceName = 'harga produk';
            $actionName = $actionMappings[$action] ?? 'Mengelola';
        } elseif (str_contains($permissionName, 'products-search')) {
            return 'Mencari produk';
        } elseif (str_contains($permissionName, 'products-by-category')) {
            return 'Melihat produk berdasarkan kategori';
        } elseif (str_contains($permissionName, 'products-low-stock')) {
            return 'Melihat produk stok rendah';
        } elseif (str_contains($permissionName, 'products-statistics')) {
            return 'Melihat statistik produk';
        } elseif (str_contains($permissionName, 'products-toggle-status')) {
            return 'Mengubah status produk';
        } elseif (str_contains($permissionName, 'products-duplicate')) {
            return 'Menduplikasi produk';
        } elseif (str_contains($permissionName, 'current-price')) {
            return 'Melihat harga saat ini produk';
        } elseif (count($parts) >= 4 && $resource === 'products') {
            // Handle nested routes (products.units, products.prices)
            $nestedResource = $parts[3] ?? '';
            $nestedAction = $parts[4] ?? '';
            
            if ($nestedResource === 'units') {
                $resourceName = 'satuan produk';
                $actionName = $actionMappings[$nestedAction] ?? 'Mengelola';
            } elseif ($nestedResource === 'prices') {
                $resourceName = 'harga produk';
                $actionName = $actionMappings[$nestedAction] ?? 'Mengelola';
            } else {
                $resourceName = $resourceMappings[$resource] ?? ucfirst($resource);
                $actionName = $actionMappings[$action] ?? ucfirst($action);
            }
        } else {
            // Get mapped names for regular routes
            $resourceName = $resourceMappings[$resource] ?? ucfirst($resource);
            $actionName = $actionMappings[$action] ?? ucfirst($action);
        }
        
        $prefixName = $resourceMappings[$prefix] ?? ucfirst($prefix);
        
        // Special cases for complex route names
        if (str_contains($permissionName, 'stocks')) {
            return $actionName . ' stok inventori';
        }
        if (str_contains($permissionName, 'permissions.sync')) {
            return 'Sinkronisasi permission';
        }
        if (str_contains($permissionName, 'permissions.routes')) {
            return 'Melihat route permission';
        }
        if (str_contains($permissionName, 'permissions.cleanup')) {
            return 'Membersihkan permission';
        }
        
        // Handle auth routes
        if ($prefix === 'auth') {
            if ($action === 'login') return 'Login ke sistem';
            if ($action === 'logout') return 'Logout dari sistem';
            if ($action === 'me') return 'Melihat profil sendiri';
            return 'Akses ' . strtolower($actionName);
        }
        
        // Handle stats routes
        if ($prefix === 'stats') {
            return 'Melihat statistik ' . strtolower($resourceName);
        }
        
        // Handle option routes
        if ($prefix === 'option') {
            return 'Melihat opsi ' . strtolower($resourceName);
        }
        
        // Handle special actions
        if ($action === 'sync-roles') {
            return 'Sinkronisasi role pengguna';
        }
        if (str_contains($action, 'permissions')) {
            return $actionName . ' pada role';
        }
        if ($action === 'sync' || $action === 'cleanup') {
            return $actionName . ' ' . strtolower($resourceName);
        }
        if ($action === 'dropdown') {
            return 'Melihat opsi dropdown ' . strtolower($resourceName);
        }
        
        // Default pattern: Action + Resource + Prefix context
        if ($actionName && $resourceName) {
            $context = $prefixName ? ' (' . $prefixName . ')' : '';
            return $actionName . ' ' . strtolower($resourceName) . $context;
        }
        
        // Fallback
        return 'Akses ke ' . str_replace(['.', '-', '_'], ' ', $permissionName);
    }

    private function createDefaultRoles()
    {
        // Create Super Admin role with all permissions
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['description' => 'Administrator dengan akses penuh ke seluruh sistem, termasuk manajemen pengguna, peran, dan semua fitur aplikasi.']
        );
        $superAdminRole->givePermissionTo(Permission::all());

        // Create Admin role with most permissions (excluding sensitive user/role management)
        $adminRole = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['description' => 'Administrator dengan akses luas ke sistem, dapat mengelola master data, produk, inventori, dan laporan, namun terbatas pada manajemen pengguna dan peran.']
        );
        $adminPermissions = Permission::where('name', 'not like', '%management.roles%')
            ->where('name', 'not like', '%management.permissions%')
            ->where('name', 'not like', '%management.users.sync-roles%')
            ->get();
        $adminRole->givePermissionTo($adminPermissions);

        // Create Manager role with limited permissions
        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager', 'guard_name' => 'web'],
            ['description' => 'Manajer toko dengan akses ke master data produk, kategori, satuan, inventori, dan laporan statistik untuk operasional harian.']
        );
        $managerPermissions = Permission::where('name', 'like', '%master.categories%')
            ->orWhere('name', 'like', '%master.units%')
            ->orWhere('name', 'like', '%product.products%')
            ->orWhere('name', 'like', '%inventory%')
            ->orWhere('name', 'like', '%stats.dashboard%')
            ->orWhere('name', 'like', '%stats.recent-sales%')
            ->orWhere('name', 'like', '%options%')
            ->get();
        $managerRole->givePermissionTo($managerPermissions);

        // Create Cashier role with minimal permissions
        $cashierRole = Role::firstOrCreate(
            ['name' => 'Cashier', 'guard_name' => 'web'],
            ['description' => 'Kasir dengan akses terbatas untuk melihat produk, mengelola inventori, dan mengakses opsi dasar yang diperlukan untuk transaksi penjualan.']
        );
        $cashierPermissions = Permission::where('name', 'like', '%inventory%')
            ->orWhere('name', 'like', '%product.products.index%')
            ->orWhere('name', 'like', '%product.products.show%')
            ->orWhere('name', 'like', '%options%')
            ->orWhere('name', 'like', '%auth.me%')
            ->orWhere('name', 'like', '%auth.logout%')
            ->orWhere('name', 'like', '%auth.refresh%')
            ->orWhere('name', 'like', '%auth.updateProfile%')
            ->orWhere('name', 'like', '%auth.changePassword%')
            ->get();
        $cashierRole->givePermissionTo($cashierPermissions);

        $this->command->info('Created default roles: Super Admin, Admin, Manager, Cashier.');
    }

    private function createAdminUser()
    {
        // Create admin user sesuai spesifikasi DATABASE_SCHEMA.md
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@qpos.com'],
            [
                'name' => 'Administrator',
                'username' => 'qpos_admin',
                'full_name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole && !$adminUser->hasRole('Super Admin')) {
            $adminUser->assignRole($superAdminRole);
        }

        // Create manager user sesuai spesifikasi DATABASE_SCHEMA.md
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@qpos.com'],
            [
                'name' => 'Manager',
                'username' => 'qpos_manager',
                'full_name' => 'Store Manager',
                'password' => Hash::make('manager123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Manager role
        $managerRole = Role::where('name', 'Manager')->first();
        if ($managerRole && !$managerUser->hasRole('Manager')) {
            $managerUser->assignRole($managerRole);
        }

        // Create cashier user sesuai spesifikasi DATABASE_SCHEMA.md
        $cashierUser = User::firstOrCreate(
            ['email' => 'cashier@qpos.com'],
            [
                'name' => 'Cashier',
                'username' => 'qpos_cashier',
                'full_name' => 'Store Cashier',
                'password' => Hash::make('cashier123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign Cashier role
        $cashierRole = Role::where('name', 'Cashier')->first();
        if ($cashierRole && !$cashierUser->hasRole('Cashier')) {
            $cashierUser->assignRole($cashierRole);
        }

        $this->command->info('Created default users: admin@qpos.com, manager@qpos.com, cashier@qpos.com (password: password)');
    }
}
