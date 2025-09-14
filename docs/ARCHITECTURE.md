# 🏗️ Architecture Documentation

> Dokumentasi arsitektur sistem Q-POS dengan diagram komponen dan alur data

## 📋 Daftar Isi

- [Overview](#overview)
- [System Architecture](#system-architecture)
- [Backend Architecture](#backend-architecture)
- [Frontend Architecture](#frontend-architecture)
- [Database Architecture](#database-architecture)
- [API Architecture](#api-architecture)
- [Security Architecture](#security-architecture)
- [Deployment Architecture](#deployment-architecture)
- [Data Flow](#data-flow)
- [Component Interaction](#component-interaction)
- [Performance Considerations](#performance-considerations)
- [Scalability Design](#scalability-design)

## Overview

Q-POS menggunakan arsitektur **microservices** dengan pemisahan yang jelas antara frontend dan backend. Sistem ini dirancang untuk mendukung skalabilitas horizontal, maintainability, dan extensibility untuk kebutuhan bisnis retail yang berkembang.

### 🎯 Design Principles

- **Separation of Concerns**: Frontend dan backend terpisah dengan komunikasi via REST API
- **Single Responsibility**: Setiap komponen memiliki tanggung jawab yang spesifik
- **Scalability**: Dapat di-scale secara horizontal dan vertikal
- **Security First**: Implementasi security di setiap layer
- **Performance Optimized**: Caching, indexing, dan query optimization
- **Maintainable**: Code structure yang clean dan well-documented

## System Architecture

### 🏛️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                            │
├─────────────────────────────────────────────────────────────────┤
│  Web Browser  │  Mobile App  │  Desktop App  │  Third Party    │
│   (Quasar)    │   (Planned)  │   (Planned)   │   Integration   │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PRESENTATION LAYER                        │
├─────────────────────────────────────────────────────────────────┤
│                    Frontend (Quasar Vue.js)                    │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │    Pages    │ │ Components  │ │   Stores    │ │   Services  ││
│  │             │ │             │ │   (Pinia)   │ │   (Axios)   ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼ HTTP/HTTPS
┌─────────────────────────────────────────────────────────────────┐
│                       API GATEWAY LAYER                        │
├─────────────────────────────────────────────────────────────────┤
│                     Laravel API Routes                         │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │ Auth Routes │ │ API Routes  │ │ Middleware  │ │ Rate Limit  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      BUSINESS LOGIC LAYER                      │
├─────────────────────────────────────────────────────────────────┤
│                    Backend (Laravel 11.x)                      │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │ Controllers │ │  Services   │ │   Models    │ │ Repositories││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   Events    │ │   Jobs      │ │ Observers   │ │ Policies    ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATA ACCESS LAYER                       │
├─────────────────────────────────────────────────────────────────┤
│                      Eloquent ORM                              │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │ Migrations  │ │   Seeders   │ │  Factories  │ │   Models    ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATABASE LAYER                          │
├─────────────────────────────────────────────────────────────────┤
│                         MySQL 8.0+                             │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   Tables    │ │    Views    │ │   Indexes   │ │ Procedures  ││
│  │ (23 tables) │ │ (FIFO View) │ │             │ │             ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

## Frontend Architecture

### 🎨 Vue.js + Quasar Framework

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND STACK                          │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │    Vue.js   │ │   Quasar    │ │    Pinia    │ │   Axios     ││
│  │   (v3.4+)   │ │ Framework   │ │   (Store)   │ │ (HTTP)      ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │Vue Router   │ │ Composition │ │   SCSS      │ │ TypeScript  ││
│  │ (Routing)   │ │     API     │ │ (Styling)   │ │ (Optional)  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │Theme System │ │ Responsive  │ │ Component   │ │ State       ││
│  │(Dark/Light) │ │   Design    │ │ Refactoring │ │ Management  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

### 📁 Frontend Structure

```
src/
├── components/          # Reusable Vue components
│   ├── common/          # Common UI components
│   │   ├── ThemeToggle.vue # Theme switching component
│   │   └── ...         # Other common components
│   ├── forms/           # Form components
│   ├── tables/          # Table components
│   └── charts/          # Chart components
├── pages/              # Page components
│   ├── auth/            # Authentication pages
│   ├── dashboard/       # Dashboard pages
│   ├── products/        # Product management
│   ├── sales/           # Sales management
│   ├── settings/        # Settings pages
│   │   ├── UnitsPage.vue      # Units management (refactored)
│   │   ├── UserManagementPage.vue # User management (refactored)
│   │   ├── RoleManagementPage.vue # Role management (refactored)
│   │   └── ...         # Other settings pages
│   └── reports/         # Report pages
├── stores/             # Pinia stores
│   ├── auth.js         # Authentication store
│   ├── products.js     # Products store
│   ├── sales.js        # Sales store
│   ├── theme.js        # Theme management store
│   └── ui.js           # UI state store
├── services/           # API services
│   ├── api.js          # Base API configuration
│   ├── auth.js         # Auth API calls
│   ├── products.js     # Products API calls
│   └── sales.js        # Sales API calls
├── router/             # Vue Router configuration
│   ├── index.js        # Main router file
│   └── routes.js       # Route definitions
├── layouts/            # Layout components
│   ├── MainLayout.vue  # Main application layout
│   └── AuthLayout.vue  # Authentication layout
├── boot/               # Quasar boot files
│   ├── axios.js        # Axios configuration
│   └── auth.js         # Auth initialization
└── css/                # Global styles
    ├── app.scss        # Main stylesheet
    ├── themes/         # Theme-specific styles
    │   ├── dark.scss   # Dark theme variables
    │   └── light.scss  # Light theme variables
    └── quasar.variables.scss # Quasar variables
```

### 🎨 Theme System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        THEME SYSTEM                            │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │ ThemeToggle │───▶│ Theme Store │───▶│ CSS Classes │         │
│  │ Component   │    │   (Pinia)   │    │ & Variables │         │
│  └─────────────┘    └─────────────┘    └─────────────┘         │
│         │                   │                   │              │
│         ▼                   ▼                   ▼              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │ User Click  │    │ State       │    │ DOM Update  │         │
│  │ Event       │    │ Management  │    │ & Persist   │         │
│  └─────────────┘    └─────────────┘    └─────────────┘         │
└─────────────────────────────────────────────────────────────────┘

Theme Flow:
1. User clicks ThemeToggle component
2. Component dispatches action to theme store
3. Store updates current theme state
4. CSS classes applied to body element
5. Theme preference saved to localStorage
6. All components re-render with new theme
```

### 🔄 Frontend Component Flow

```
User Interaction
       │
       ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Vue       │───▶│   Pinia     │───▶│   Service   │
│ Component   │    │   Store     │    │   Layer     │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Props &   │    │   State     │    │ HTTP Client │
│   Events    │    │ Management  │    │   (Axios)   │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Component   │    │ Reactive    │    │   Backend   │
│ Re-render   │◀───│ Updates     │◀───│   (Laravel) │
└─────────────┘    └─────────────┘    └─────────────┘
```

### 📱 Responsive Design Implementation

```
┌─────────────────────────────────────────────────────────────────┐
│                    RESPONSIVE BREAKPOINTS                      │
├─────────────────────────────────────────────────────────────────┤
│ Mobile    │ Tablet    │ Desktop   │ Large     │ Extra Large     │
│ < 600px   │ 600-1023px│ 1024-1439px│ 1440-1919px│ >= 1920px      │
├─────────────────────────────────────────────────────────────────┤
│ • Single  │ • Sidebar │ • Full    │ • Wide    │ • Ultra-wide    │
│   column  │   toggle  │   sidebar │   layout  │   layout        │
│ • Stack   │ • Grid    │ • Grid    │ • Enhanced│ • Multi-panel   │
│   layout  │   layout  │   layout  │   grid    │   layout        │
│ • Touch   │ • Mixed   │ • Mouse   │ • Mouse   │ • Mouse         │
│   optimized│   input   │   optimized│   optimized│   optimized    │
└─────────────────────────────────────────────────────────────────┘
```

## Backend Architecture

### 🔧 Laravel Backend Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── ProductController.php
│   │   │   │   │   ├── CategoryController.php
│   │   │   │   │   ├── CustomerController.php
│   │   │   │   │   ├── SupplierController.php
│   │   │   │   │   ├── SalesController.php
│   │   │   │   │   ├── InventoryController.php
│   │   │   │   │   └── ReportController.php
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── CheckPermission.php
│   │   │   └── ApiRateLimit.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   ├── Product/
│   │   │   └── Sales/
│   │   └── Resources/
│   │       ├── ProductResource.php
│   │       ├── CustomerResource.php
│   │       └── SalesResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Customer.php
│   │   ├── Supplier.php
│   │   ├── SalesTransaction.php
│   │   ├── InventoryStock.php
│   │   └── StockBatch.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── ProductService.php
│   │   ├── InventoryService.php
│   │   ├── SalesService.php
│   │   └── ReportService.php
│   ├── Repositories/
│   │   ├── ProductRepository.php
│   │   ├── InventoryRepository.php
│   │   └── SalesRepository.php
│   ├── Events/
│   │   ├── ProductCreated.php
│   │   ├── StockUpdated.php
│   │   └── SaleCompleted.php
│   ├── Jobs/
│   │   ├── UpdateInventory.php
│   │   ├── SendNotification.php
│   │   └── GenerateReport.php
│   └── Policies/
│       ├── ProductPolicy.php
│       ├── SalesPolicy.php
│       └── UserPolicy.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
└── config/
    ├── database.php
    ├── auth.php
    └── sanctum.php
```

### 🔄 Backend Component Flow

```
HTTP Request
     │
     ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Middleware  │───▶│ Controller  │───▶│  Request    │
│ (Auth, etc) │    │             │    │ Validation  │
└─────────────┘    └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │   Service   │───▶│ Repository  │
                   │   Layer     │    │   Layer     │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │   Events    │    │   Models    │
                   │   & Jobs    │    │ (Eloquent)  │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ Background  │    │  Database   │
                   │ Processing  │    │   (MySQL)   │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │  Response   │◀───│   Result    │
                   │ (JSON API)  │    │   Data      │
                   └─────────────┘    └─────────────┘
```

## Frontend Architecture

### 🎨 Quasar Vue.js Frontend Structure

```
frontend/web/
├── src/
│   ├── components/
│   │   ├── common/
│   │   │   ├── AppHeader.vue
│   │   │   ├── AppSidebar.vue
│   │   │   ├── AppFooter.vue
│   │   │   └── LoadingSpinner.vue
│   │   ├── forms/
│   │   │   ├── ProductForm.vue
│   │   │   ├── CustomerForm.vue
│   │   │   └── SalesForm.vue
│   │   ├── tables/
│   │   │   ├── ProductTable.vue
│   │   │   ├── SalesTable.vue
│   │   │   └── InventoryTable.vue
│   │   └── charts/
│   │       ├── SalesChart.vue
│   │       └── InventoryChart.vue
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── LoginPage.vue
│   │   │   └── RegisterPage.vue
│   │   ├── dashboard/
│   │   │   └── DashboardPage.vue
│   │   ├── products/
│   │   │   ├── ProductListPage.vue
│   │   │   ├── ProductCreatePage.vue
│   │   │   └── ProductEditPage.vue
│   │   ├── sales/
│   │   │   ├── POSPage.vue
│   │   │   ├── SalesListPage.vue
│   │   │   └── SalesDetailPage.vue
│   │   ├── inventory/
│   │   │   ├── InventoryPage.vue
│   │   │   └── StockMovementPage.vue
│   │   └── reports/
│   │       ├── SalesReportPage.vue
│   │       └── InventoryReportPage.vue
│   ├── stores/
│   │   ├── auth.js
│   │   ├── products.js
│   │   ├── customers.js
│   │   ├── sales.js
│   │   ├── inventory.js
│   │   └── reports.js
│   ├── services/
│   │   ├── api.js
│   │   ├── auth.js
│   │   ├── products.js
│   │   ├── sales.js
│   │   └── inventory.js
│   ├── router/
│   │   ├── index.js
│   │   └── routes.js
│   ├── layouts/
│   │   ├── MainLayout.vue
│   │   └── AuthLayout.vue
│   ├── boot/
│   │   ├── axios.js
│   │   ├── pinia.js
│   │   └── auth.js
│   └── css/
│       ├── app.scss
│       └── quasar.variables.scss
├── public/
└── quasar.config.js
```

### 🔄 Frontend Component Flow

```
User Interaction
     │
     ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Page     │───▶│ Component   │───▶│   Store     │
│ (Vue Route) │    │ (Vue SFC)   │    │  (Pinia)    │
└─────────────┘    └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │   Service   │───▶│ HTTP Client │
                   │   Layer     │    │   (Axios)   │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ API Request │───▶│   Backend   │
                   │ (REST API)  │    │   (Laravel) │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ API Response│◀───│  Database   │
                   │   (JSON)    │    │   Result    │
                   └─────────────┘    └─────────────┘
                           │
                           ▼
                   ┌─────────────┐
                   │   Store     │
                   │  Update     │
                   └─────────────┘
                           │
                           ▼
                   ┌─────────────┐
                   │ Component   │
                   │ Re-render   │
                   └─────────────┘
```

## Database Architecture

### 🗄️ Database Schema Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        MASTER DATA                             │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │ categories  │ │    units    │ │  products   │ │ warehouses  ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │  customers  │ │  suppliers  │ │payment_     │ │    users    ││
│  │             │ │             │ │methods      │ │             ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      PRODUCT MANAGEMENT                        │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │product_     │ │product_     │ │product_     │ │conversion_  ││
│  │units        │ │prices       │ │barcodes     │ │units        ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     INVENTORY MANAGEMENT                       │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │inventory_   │ │stock_       │ │stock_       │ │reserved_    ││
│  │stocks       │ │batches      │ │movements    │ │stocks       ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SALES MANAGEMENT                          │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │sales_       │ │sales_       │ │sales_       │ │customer_    ││
│  │transactions │ │transaction_ │ │payments     │ │loyalty_     ││
│  │             │ │items        │ │             │ │points       ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    PURCHASE MANAGEMENT                         │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │purchase_    │ │purchase_    │ │supplier_    │ │purchase_    ││
│  │orders       │ │order_items  │ │payments     │ │receipts     ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

### 🔍 FIFO Implementation

```
FIFO Stock Tracking Flow:

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Purchase  │───▶│Stock Batch  │───▶│ FIFO View   │
│   Receipt   │    │  Creation   │    │  Query      │
└─────────────┘    └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ Batch with  │    │ Available   │
                   │ received_   │    │ Stock by    │
                   │ date        │    │ Date Order  │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ Sales       │───▶│ Stock       │
                   │ Transaction │    │ Reduction   │
                   └─────────────┘    └─────────────┘
                           │                   │
                           ▼                   ▼
                   ┌─────────────┐    ┌─────────────┐
                   │ Oldest      │    │ Inventory   │
                   │ Batch First │    │ Update      │
                   └─────────────┘    └─────────────┘
```

## API Architecture

### 🔌 REST API Design

```
API Versioning: /api/v1/

┌─────────────────────────────────────────────────────────────────┐
│                        API ENDPOINTS                           │
├─────────────────────────────────────────────────────────────────┤
│ Authentication                                                  │
│ POST   /api/v1/auth/login                                       │
│ POST   /api/v1/auth/register                                    │
│ POST   /api/v1/auth/logout                                      │
│ GET    /api/v1/auth/user                                        │
│ POST   /api/v1/auth/refresh                                     │
├─────────────────────────────────────────────────────────────────┤
│ Products                                                        │
│ GET    /api/v1/products                                         │
│ POST   /api/v1/products                                         │
│ GET    /api/v1/products/{id}                                    │
│ PUT    /api/v1/products/{id}                                    │
│ DELETE /api/v1/products/{id}                                    │
│ GET    /api/v1/products/{id}/units                              │
│ GET    /api/v1/products/{id}/prices                             │
├─────────────────────────────────────────────────────────────────┤
│ Categories                                                      │
│ GET    /api/v1/categories                                       │
│ POST   /api/v1/categories                                       │
│ GET    /api/v1/categories/{id}                                  │
│ PUT    /api/v1/categories/{id}                                  │
│ DELETE /api/v1/categories/{id}                                  │
├─────────────────────────────────────────────────────────────────┤
│ Customers                                                       │
│ GET    /api/v1/customers                                        │
│ POST   /api/v1/customers                                        │
│ GET    /api/v1/customers/{id}                                   │
│ PUT    /api/v1/customers/{id}                                   │
│ DELETE /api/v1/customers/{id}                                   │
├─────────────────────────────────────────────────────────────────┤
│ Sales                                                           │
│ GET    /api/v1/sales                                            │
│ POST   /api/v1/sales                                            │
│ GET    /api/v1/sales/{id}                                       │
│ POST   /api/v1/sales/{id}/payments                              │
│ GET    /api/v1/sales/{id}/receipt                               │
├─────────────────────────────────────────────────────────────────┤
│ Inventory                                                       │
│ GET    /api/v1/inventory/stocks                                 │
│ POST   /api/v1/inventory/adjustment                             │
│ GET    /api/v1/inventory/movements                              │
│ GET    /api/v1/inventory/fifo/{product_id}                      │
├─────────────────────────────────────────────────────────────────┤
│ Reports                                                         │
│ GET    /api/v1/reports/sales                                    │
│ GET    /api/v1/reports/inventory                                │
│ GET    /api/v1/reports/customers                                │
│ GET    /api/v1/reports/products                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 📊 API Response Format

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  },
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 100,
      "last_page": 7
    },
    "timestamp": "2025-09-01T10:30:00Z",
    "version": "v1"
  },
  "errors": null
}
```

## Security Architecture

### 🔒 Security Layers

```
┌─────────────────────────────────────────────────────────────────┐
│                      FRONTEND SECURITY                         │
├─────────────────────────────────────────────────────────────────┤
│ • Input Validation & Sanitization                              │
│ • XSS Protection                                               │
│ • CSRF Protection                                              │
│ • Secure Token Storage                                         │
│ • Route Guards & Permission Checks                            │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼ HTTPS
┌─────────────────────────────────────────────────────────────────┐
│                       API SECURITY                             │
├─────────────────────────────────────────────────────────────────┤
│ • Laravel Sanctum Authentication                               │
│ • Rate Limiting                                                │
│ • API Versioning                                               │
│ • Request Validation                                           │
│ • CORS Configuration                                           │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     BACKEND SECURITY                           │
├─────────────────────────────────────────────────────────────────┤
│ • Role-Based Access Control (RBAC)                             │
│ • Permission-Based Authorization                               │
│ • SQL Injection Protection (Eloquent ORM)                      │
│ • Password Hashing (bcrypt)                                    │
│ • Audit Logging                                                │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    DATABASE SECURITY                           │
├─────────────────────────────────────────────────────────────────┤
│ • Encrypted Connections                                         │
│ • Database User Permissions                                     │
│ • Backup Encryption                                             │
│ • Data Masking for Sensitive Fields                            │
│ • Foreign Key Constraints                                       │
└─────────────────────────────────────────────────────────────────┘
```

## Deployment Architecture

### 🚀 Production Deployment

```
┌─────────────────────────────────────────────────────────────────┐
│                        LOAD BALANCER                           │
│                      (Nginx/Apache)                            │
└─────────────────────────────────────────────────────────────────┘
                                │
                ┌───────────────┼───────────────┐
                ▼               ▼               ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│   Frontend      │ │   Frontend      │ │   Frontend      │
│   Server 1      │ │   Server 2      │ │   Server N      │
│   (Nginx)       │ │   (Nginx)       │ │   (Nginx)       │
└─────────────────┘ └─────────────────┘ └─────────────────┘
                                │
                                ▼ API Calls
┌─────────────────────────────────────────────────────────────────┐
│                      API LOAD BALANCER                         │
└─────────────────────────────────────────────────────────────────┘
                                │
                ┌───────────────┼───────────────┐
                ▼               ▼               ▼
┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐
│   Backend       │ │   Backend       │ │   Backend       │
│   Server 1      │ │   Server 2      │ │   Server N      │
│   (Laravel)     │ │   (Laravel)     │ │   (Laravel)     │
└─────────────────┘ └─────────────────┘ └─────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      DATABASE CLUSTER                          │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐│
│  │   Master    │ │   Slave 1   │ │   Slave 2   │ │   Backup    ││
│  │   (Write)   │ │   (Read)    │ │   (Read)    │ │   Server    ││
│  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

## Data Flow

### 🔄 Complete Transaction Flow

```
1. User Login
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Frontend  │───▶│   Backend   │───▶│  Database   │
│   (Login)   │    │   (Auth)    │    │   (Users)   │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Store Token │◀───│ Generate    │◀───│ Validate    │
│ in Pinia    │    │ Sanctum     │    │ Credentials │
│             │    │ Token       │    │             │
└─────────────┘    └─────────────┘    └─────────────┘

2. Product Selection (POS)
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Frontend  │───▶│   Backend   │───▶│  Database   │
│ (Search     │    │ (Product    │    │ (Products,  │
│  Product)   │    │  API)       │    │  Prices)    │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Display     │◀───│ Return      │◀───│ Join Tables │
│ Product     │    │ Product     │    │ with Units  │
│ with Prices │    │ Data        │    │ & Prices    │
└─────────────┘    └─────────────┘    └─────────────┘

3. Sales Transaction
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Frontend  │───▶│   Backend   │───▶│  Database   │
│ (Submit     │    │ (Sales      │    │ (Begin      │
│  Sale)      │    │  Service)   │    │  Transaction)│
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Show        │    │ Process     │    │ Update      │
│ Receipt     │    │ Payment     │    │ Inventory   │
│             │    │             │    │ (FIFO)      │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Update      │◀───│ Trigger     │◀───│ Create      │
│ Frontend    │    │ Events      │    │ Sales       │
│ State       │    │ & Jobs      │    │ Record      │
└─────────────┘    └─────────────┘    └─────────────┘
```

## Component Interaction

### 🔗 Inter-Component Communication

```
Frontend Components:

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│    Pages    │───▶│ Components  │───▶│   Stores    │
│             │    │             │    │  (Pinia)    │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Router    │    │   Props &   │    │   Actions   │
│ Navigation  │    │   Events    │    │ & Getters   │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Layout    │    │ Composables │    │  Services   │
│ Components  │    │ & Mixins    │    │ (API Calls) │
└─────────────┘    └─────────────┘    └─────────────┘

Backend Components:

┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Controllers │───▶│  Services   │───▶│Repositories │
│             │    │             │    │             │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│ Middleware  │    │   Events    │    │   Models    │
│ & Policies  │    │   & Jobs    │    │ (Eloquent)  │
└─────────────┘    └─────────────┘    └─────────────┘
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│  Resources  │    │ Observers   │    │  Database   │
│ & Requests  │    │ & Listeners │    │   (MySQL)   │
└─────────────┘    └─────────────┘    └─────────────┘
```

## Performance Considerations

### ⚡ Optimization Strategies

#### Frontend Performance
- **Code Splitting**: Lazy loading untuk routes dan components
- **Caching**: Browser caching untuk static assets
- **Compression**: Gzip compression untuk responses
- **CDN**: Content Delivery Network untuk assets
- **Image Optimization**: WebP format dan responsive images
- **Bundle Optimization**: Tree shaking dan minification

#### Backend Performance
- **Database Indexing**: Proper indexing untuk query optimization
- **Query Optimization**: Eager loading dan query caching
- **API Caching**: Redis caching untuk frequently accessed data
- **Background Jobs**: Queue processing untuk heavy operations
- **Database Connection Pooling**: Efficient connection management
- **Response Compression**: Gzip compression untuk API responses

#### Database Performance
- **FIFO View**: Pre-computed view untuk FIFO queries
- **Proper Indexing**: Composite indexes untuk complex queries
- **Partitioning**: Table partitioning untuk large datasets
- **Read Replicas**: Separate read/write operations
- **Query Caching**: MySQL query cache optimization
- **Connection Optimization**: Proper connection pool sizing

## Scalability Design

### 📈 Horizontal Scaling

#### Frontend Scaling
- **Multiple Frontend Servers**: Load balanced Nginx servers
- **CDN Distribution**: Global content distribution
- **Static Asset Optimization**: Separate static file servers
- **Progressive Web App**: Offline capabilities

#### Backend Scaling
- **Multiple API Servers**: Load balanced Laravel instances
- **Microservices Architecture**: Service separation by domain
- **Queue Workers**: Scalable background job processing
- **API Gateway**: Centralized API management

#### Database Scaling
- **Read Replicas**: Multiple read-only database instances
- **Database Sharding**: Horizontal data partitioning
- **Caching Layer**: Redis/Memcached for frequently accessed data
- **Connection Pooling**: Efficient database connection management

### 🔄 Auto-scaling Considerations

```
Load Monitoring:
┌─────────────┐    ┌─────────────┐    ┌─────────────┐
│   Metrics   │───▶│ Monitoring  │───▶│ Auto-scale  │
│ Collection  │    │   System    │    │  Triggers   │
└─────────────┘    └─────────────┘    └─────────────┘

Scaling Triggers:
• CPU Usage > 70%
• Memory Usage > 80%
• Response Time > 2s
• Queue Length > 100
• Database Connections > 80%

Scaling Actions:
• Add Frontend Servers
• Add Backend Workers
• Scale Database Read Replicas
• Increase Queue Workers
• Add Cache Instances
```

---

## 📚 References

- [Laravel Architecture Concepts](https://laravel.com/docs/11.x/architecture)
- [Vue.js Architecture Guide](https://vuejs.org/guide/scaling-up/)
- [Quasar Framework Architecture](https://quasar.dev/quasar-cli-vite/)
- [MySQL Performance Optimization](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)
- [REST API Design Best Practices](https://restfulapi.net/)

---

**Last Updated**: September 2025  
**Version**: 1.0  
**Maintainer**: Q-POS Development Team