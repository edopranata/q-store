const routes = [
  // Public Routes
  {
    path: '/',
    component: () => import('layouts/PublicLayout.vue'),
    children: [
      { 
        path: '', 
        name: 'home',
        component: () => import('pages/public/HomePage.vue'),
        meta: { requiresAuth: false }
      }
    ]
  },

  // Authentication Routes
  {
    path: '/auth',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('pages/auth/LoginPage.vue'),
        meta: { requiresGuest: true }
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: () => import('pages/auth/ForgotPasswordPage.vue'),
        meta: { requiresGuest: true }
      },
      {
        path: 'reset-password',
        name: 'reset-password',
        component: () => import('pages/auth/ResetPasswordPage.vue'),
        meta: { requiresGuest: true }
      }
    ]
  },

  // Application Routes
  {
    path: '/app',
    component: () => import('layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/app/dashboard'
      },
      {
        path: 'dashboard',
        name: 'dashboard',
        component: () => import('pages/app/DashboardPage.vue'),
        meta: { title: 'Dashboard', requiresAuth: true }
      },
      {
        path: 'pos',
        name: 'pos',
        component: () => import('pages/app/pos/POSPage.vue'),
        meta: { title: 'Point of Sale', requiresAuth: true }
      },
      // Master Data Routes
      {
        path: 'categories',
        name: 'categories',
        component: () => import('pages/app/categories/CategoriesPage.vue'),
        meta: { title: 'Categories', requiresAuth: true }
      },
      {
        path: 'units',
        name: 'units',
        component: () => import('pages/app/units/UnitsPage.vue'),
        meta: { title: 'Units', requiresAuth: true }
      },
      {
        path: 'suppliers',
        name: 'suppliers',
        component: () => import('pages/app/suppliers/SuppliersPage.vue'),
        meta: { title: 'Suppliers', requiresAuth: true }
      },
      {
        path: 'customers',
        name: 'customers',
        component: () => import('pages/app/customers/CustomersPage.vue'),
        meta: { title: 'Customers', requiresAuth: true }
      },
      {
        path: 'warehouses',
        name: 'warehouses',
        component: () => import('pages/app/warehouses/WarehousesPage.vue'),
        meta: { title: 'Warehouses', requiresAuth: true }
      },
      {
        path: 'payment-methods',
        name: 'payment-methods',
        component: () => import('pages/app/payment-methods/PaymentMethodsPage.vue'),
        meta: { title: 'Payment Methods', requiresAuth: true }
      },
      // Product Routes
      {
        path: 'products',
        name: 'products',
        component: () => import('pages/app/products/ProductsPage.vue'),
        meta: { title: 'Products', requiresAuth: true }
      },
      {
        path: 'products/create',
        name: 'product-create',
        component: () => import('pages/app/products/ProductCreatePage.vue'),
        meta: { title: 'Create Product', requiresAuth: true }
      },
      {
        path: 'products/:id/edit',
        name: 'product-edit',
        component: () => import('pages/app/products/ProductEditPage.vue'),
        meta: { title: 'Edit Product', requiresAuth: true }
      },
      // Transaction Routes
      {
        path: 'sales',
        name: 'sales',
        component: () => import('pages/app/transactions/SalesPage.vue'),
        meta: { title: 'Sales Transactions', requiresAuth: true }
      },
      {
        path: 'purchases',
        name: 'purchases',
        component: () => import('pages/app/transactions/PurchasesPage.vue'),
        meta: { title: 'Purchase Transactions', requiresAuth: true }
      },
      // Reports Routes
      {
        path: 'reports',
        name: 'reports',
        component: () => import('pages/app/reports/ReportsPage.vue'),
        meta: { title: 'Reports', requiresAuth: true }
      },
      
      // User & Role Management Routes
      {
        path: 'users',
        name: 'users',
        component: () => import('pages/app/users/UserManagementPage.vue'),
        meta: { title: 'User Management', requiresAuth: true }
      },
      {
        path: 'roles',
        name: 'roles',
        component: () => import('pages/app/roles/RoleManagementPage.vue'),
        meta: { title: 'Role Management', requiresAuth: true }
      },
      {
        path: 'roles/:id/edit',
        name: 'role-edit',
        component: () => import('pages/app/roles/RoleEditPage.vue'),
        meta: { title: 'Edit Role', requiresAuth: true }
      },
      {
        path: 'roles/:id/view',
        name: 'role-view',
        component: () => import('pages/app/roles/RoleViewPage.vue'),
        meta: { title: 'View Role', requiresAuth: true }
      },
      
      // User Routes
      {
        path: 'profile',
        name: 'profile',
        component: () => import('pages/app/user/ProfilePage.vue'),
        meta: { title: 'Profile', requiresAuth: true }
      },
      {
        path: 'settings',
        name: 'settings',
        component: () => import('pages/app/settings/SettingsPage.vue'),
        meta: { title: 'Settings', requiresAuth: true }
      }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
