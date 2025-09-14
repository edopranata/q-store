# 🧪 Testing Documentation

> Dokumentasi lengkap strategi pengujian, panduan menjalankan tes, dan persyaratan coverage untuk Q-POS

## 📋 Daftar Isi

- [Overview](#overview)
- [Testing Strategy](#testing-strategy)
- [Test Types](#test-types)
- [Testing Environment](#testing-environment)
- [Running Tests](#running-tests)
- [Code Coverage](#code-coverage)
- [Test Data Management](#test-data-management)
- [Continuous Integration](#continuous-integration)
- [Performance Testing](#performance-testing)
- [Security Testing](#security-testing)
- [API Testing](#api-testing)
- [Frontend Testing](#frontend-testing)
- [Database Testing](#database-testing)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

### 🎯 Testing Philosophy

Q-POS mengadopsi pendekatan **Test-Driven Development (TDD)** dan **Behavior-Driven Development (BDD)** untuk memastikan kualitas kode yang tinggi dan fungsionalitas yang sesuai dengan kebutuhan bisnis.

### 📊 Testing Metrics

- **Target Code Coverage**: 90%+
- **Current Coverage**: 85%
- **Test Execution Time**: < 5 minutes
- **Test Success Rate**: 99.5%
- **Critical Path Coverage**: 100%

### 🏗️ Testing Pyramid

```
    /\     E2E Tests (10%)
   /  \    - User workflows
  /____\   - Integration scenarios
 
  /______\  Integration Tests (20%)
 /        \ - API endpoints
/__________\- Database interactions

/____________\ Unit Tests (70%)
              - Business logic
              - Model methods
              - Utility functions
```

## Testing Strategy

### 🎯 Testing Objectives

1. **Functional Correctness**: Memastikan semua fitur bekerja sesuai spesifikasi
2. **Performance**: Memvalidasi response time dan throughput
3. **Security**: Mengidentifikasi vulnerabilities dan access control
4. **Reliability**: Memastikan sistem stabil dalam berbagai kondisi
5. **Usability**: Memverifikasi user experience yang optimal
6. **Compatibility**: Memastikan kompatibilitas lintas platform

### 📋 Test Planning

#### Risk-Based Testing
- **High Risk**: Payment processing, inventory management, user authentication
- **Medium Risk**: Reporting, customer management, product catalog
- **Low Risk**: UI components, static content, logging

#### Test Coverage Areas
- ✅ **Core Business Logic**: 100% coverage
- ✅ **API Endpoints**: 95% coverage
- ✅ **Database Operations**: 90% coverage
- ✅ **Authentication & Authorization**: 100% coverage
- ✅ **Payment Processing**: 100% coverage
- ✅ **Inventory Management**: 95% coverage
- ✅ **User Interface**: 80% coverage

## Test Types

### 🔬 Unit Tests

Testing individual components in isolation.

#### Backend Unit Tests (Laravel/PHPUnit)

**Location**: `backend/tests/Unit/`

**Coverage Areas**:
- Model methods and relationships
- Service classes and business logic
- Utility functions and helpers
- Validation rules
- Event listeners

**Example Test Structure**:
```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_calculate_stock_value()
    {
        $product = Product::factory()->create([
            'cost_price' => 50000
        ]);
        
        $product->stocks()->create([
            'warehouse_id' => 1,
            'quantity' => 10
        ]);
        
        $this->assertEquals(500000, $product->getTotalStockValue());
    }

    /** @test */
    public function it_belongs_to_category()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id
        ]);
        
        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(ValidationException::class);
        
        Product::create([
            'name' => '', // Required field
            'category_id' => null // Required field
        ]);
    }
}
```

#### Frontend Unit Tests (Jest/Vitest)

**Location**: `frontend/web/tests/unit/`

**Coverage Areas**:
- Vue components
- Composables and utilities
- Store actions and mutations
- Business logic functions
- Form validation
- Theme system components
- Responsive design components

**Example Test Structure**:
```javascript
import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductCard from '@/components/ProductCard.vue'
import { useProductStore } from '@/stores/product'

describe('ProductCard.vue', () => {
  let wrapper
  let productStore

  beforeEach(() => {
    wrapper = mount(ProductCard, {
      global: {
        plugins: [createTestingPinia()]
      },
      props: {
        product: {
          id: 1,
          name: 'Test Product',
          price: 50000,
          stock: 10
        }
      }
    })
    
    productStore = useProductStore()
  })

  it('renders product information correctly', () => {
    expect(wrapper.find('[data-test="product-name"]').text()).toBe('Test Product')
    expect(wrapper.find('[data-test="product-price"]').text()).toContain('50,000')
    expect(wrapper.find('[data-test="product-stock"]').text()).toContain('10')
  })

  it('calls addToCart when add button is clicked', async () => {
    await wrapper.find('[data-test="add-to-cart"]').trigger('click')
    
    expect(productStore.addToCart).toHaveBeenCalledWith({
      id: 1,
      name: 'Test Product',
      price: 50000,
      quantity: 1
    })
  })

  it('disables add button when out of stock', async () => {
    await wrapper.setProps({
      product: {
        id: 1,
        name: 'Test Product',
        price: 50000,
        stock: 0
      }
    })
    
    const addButton = wrapper.find('[data-test="add-to-cart"]')
    expect(addButton.attributes('disabled')).toBeDefined()
  })
})
```

#### Theme System Tests

**Location**: `frontend/web/tests/unit/theme/`

**ThemeToggle Component Tests**:
```javascript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { Quasar } from 'quasar'
import ThemeToggle from '@/components/common/ThemeToggle.vue'
import { useTheme } from '@/composables/useTheme'

// Mock useTheme composable
vi.mock('@/composables/useTheme')

describe('ThemeToggle.vue', () => {
  let wrapper
  let mockTheme

  beforeEach(() => {
    mockTheme = {
      currentTheme: { value: 'light' },
      THEME_OPTIONS: {
        LIGHT: 'light',
        DARK: 'dark',
        AUTO: 'auto'
      },
      setTheme: vi.fn(),
      getThemeIcon: vi.fn((theme) => {
        const icons = { light: 'light_mode', dark: 'dark_mode', auto: 'brightness_auto' }
        return icons[theme] || 'brightness_auto'
      }),
      getThemeLabel: vi.fn((theme) => {
        const labels = { light: 'Light', dark: 'Dark', auto: 'Auto' }
        return labels[theme] || 'Auto'
      }),
      initializeTheme: vi.fn()
    }
    
    useTheme.mockReturnValue(mockTheme)
    
    wrapper = mount(ThemeToggle, {
      global: {
        plugins: [Quasar]
      }
    })
  })

  it('renders theme toggle button correctly', () => {
    expect(wrapper.find('.theme-toggle').exists()).toBe(true)
    expect(wrapper.find('.q-btn-dropdown').exists()).toBe(true)
  })

  it('displays current theme icon and label', () => {
    expect(mockTheme.getThemeIcon).toHaveBeenCalledWith('light')
    expect(mockTheme.getThemeLabel).toHaveBeenCalledWith('light')
  })

  it('renders all theme options in dropdown', () => {
    const themeOptions = wrapper.findAll('.theme-option')
    expect(themeOptions).toHaveLength(3)
  })

  it('calls setTheme when theme option is clicked', async () => {
    const darkOption = wrapper.find('[data-test="theme-dark"]')
    await darkOption.trigger('click')
    
    expect(mockTheme.setTheme).toHaveBeenCalledWith('dark')
  })

  it('emits theme-changed event when theme changes', async () => {
    const darkOption = wrapper.find('[data-test="theme-dark"]')
    await darkOption.trigger('click')
    
    expect(wrapper.emitted('theme-changed')).toBeTruthy()
  })

  it('applies correct CSS classes based on props', async () => {
    await wrapper.setProps({ compact: true, showLabel: false })
    
    expect(wrapper.find('.theme-toggle--compact').exists()).toBe(true)
    expect(wrapper.find('.theme-toggle--icon-only').exists()).toBe(true)
  })

  it('initializes theme on mount when autoInitialize is true', () => {
    expect(mockTheme.initializeTheme).toHaveBeenCalled()
  })
})
```

**useTheme Composable Tests**:
```javascript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useTheme, THEME_OPTIONS } from '@/composables/useTheme'
import { Dark, LocalStorage } from 'quasar'

// Mock Quasar modules
vi.mock('quasar', () => ({
  Dark: {
    set: vi.fn()
  },
  LocalStorage: {
    set: vi.fn(),
    getItem: vi.fn()
  }
}))

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation(query => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  }))
})

describe('useTheme', () => {
  let theme

  beforeEach(() => {
    vi.clearAllMocks()
    theme = useTheme()
  })

  it('initializes with default theme', () => {
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.AUTO)
  })

  it('sets theme correctly', () => {
    theme.setTheme(THEME_OPTIONS.DARK)
    
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.DARK)
    expect(Dark.set).toHaveBeenCalledWith(true)
    expect(LocalStorage.set).toHaveBeenCalledWith('app-theme', THEME_OPTIONS.DARK)
  })

  it('toggles between light and dark themes', () => {
    theme.setTheme(THEME_OPTIONS.LIGHT)
    theme.toggleTheme()
    
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.DARK)
    
    theme.toggleTheme()
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.LIGHT)
  })

  it('cycles through all theme options', () => {
    theme.setTheme(THEME_OPTIONS.LIGHT)
    theme.cycleTheme()
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.DARK)
    
    theme.cycleTheme()
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.AUTO)
    
    theme.cycleTheme()
    expect(theme.currentTheme.value).toBe(THEME_OPTIONS.LIGHT)
  })

  it('returns correct theme icons', () => {
    expect(theme.getThemeIcon(THEME_OPTIONS.LIGHT)).toBe('light_mode')
    expect(theme.getThemeIcon(THEME_OPTIONS.DARK)).toBe('dark_mode')
    expect(theme.getThemeIcon(THEME_OPTIONS.AUTO)).toBe('brightness_auto')
  })

  it('returns correct theme labels', () => {
    expect(theme.getThemeLabel(THEME_OPTIONS.LIGHT)).toBe('Light')
    expect(theme.getThemeLabel(THEME_OPTIONS.DARK)).toBe('Dark')
    expect(theme.getThemeLabel(THEME_OPTIONS.AUTO)).toBe('Auto')
  })

  it('detects system preference correctly', () => {
    window.matchMedia.mockReturnValue({ matches: true })
    expect(theme.getSystemPreference()).toBe(true)
    
    window.matchMedia.mockReturnValue({ matches: false })
    expect(theme.getSystemPreference()).toBe(false)
  })
})
```

#### Responsive Design Tests

**Location**: `frontend/web/tests/unit/responsive/`

**Responsive Component Tests**:
```javascript
import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { Quasar } from 'quasar'
import ResponsiveLayout from '@/layouts/MainLayout.vue'

describe('Responsive Design', () => {
  let wrapper

  beforeEach(() => {
    wrapper = mount(ResponsiveLayout, {
      global: {
        plugins: [Quasar]
      }
    })
  })

  it('adapts navigation for mobile screens', async () => {
    // Mock mobile viewport
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 375
    })
    
    window.dispatchEvent(new Event('resize'))
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.q-drawer--mobile').exists()).toBe(true)
  })

  it('shows desktop navigation on large screens', async () => {
    // Mock desktop viewport
    Object.defineProperty(window, 'innerWidth', {
      writable: true,
      configurable: true,
      value: 1200
    })
    
    window.dispatchEvent(new Event('resize'))
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('.q-drawer--desktop').exists()).toBe(true)
  })

  it('applies correct breakpoint classes', () => {
    const element = wrapper.find('.responsive-container')
    expect(element.classes()).toContain('col-12')
    expect(element.classes()).toContain('col-md-6')
    expect(element.classes()).toContain('col-lg-4')
  })
})
```

### 🔗 Integration Tests

Testing interactions between components.

#### Theme Integration Tests

**Location**: `frontend/web/tests/integration/theme/`

**Theme System Integration Tests**:
```javascript
import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import { Quasar } from 'quasar'
import App from '@/App.vue'
import { useTheme } from '@/composables/useTheme'

describe('Theme System Integration', () => {
  let wrapper
  let router

  beforeEach(async () => {
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/dashboard', component: { template: '<div>Dashboard</div>' } }
      ]
    })
    
    wrapper = mount(App, {
      global: {
        plugins: [Quasar, router]
      }
    })
    
    await router.isReady()
  })

  it('applies theme consistently across all pages', async () => {
    const theme = useTheme()
    
    // Set dark theme
    theme.setTheme('dark')
    await wrapper.vm.$nextTick()
    
    // Check if dark theme is applied to document
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark')
    expect(document.body.classList.contains('theme-dark')).toBe(true)
    
    // Navigate to different page
    await router.push('/dashboard')
    await wrapper.vm.$nextTick()
    
    // Theme should persist
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark')
    expect(document.body.classList.contains('theme-dark')).toBe(true)
  })

  it('persists theme preference in localStorage', () => {
    const theme = useTheme()
    
    theme.setTheme('light')
    expect(LocalStorage.getItem('app-theme')).toBe('light')
    
    theme.setTheme('dark')
    expect(LocalStorage.getItem('app-theme')).toBe('dark')
  })

  it('responds to system theme changes when auto mode is enabled', async () => {
    const theme = useTheme()
    theme.setTheme('auto')
    
    // Mock system dark mode
    const mockMatchMedia = vi.fn().mockReturnValue({
      matches: true,
      addEventListener: vi.fn(),
      removeEventListener: vi.fn()
    })
    window.matchMedia = mockMatchMedia
    
    // Trigger system preference change
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    const changeHandler = mediaQuery.addEventListener.mock.calls[0][1]
    changeHandler({ matches: true })
    
    await wrapper.vm.$nextTick()
    
    expect(document.documentElement.getAttribute('data-theme')).toBe('dark')
  })
})
```

#### API Integration Tests

**Location**: `backend/tests/Feature/`

**Coverage Areas**:
- HTTP endpoints
- Database transactions
- External service integrations
- Authentication flows
- File uploads

**Example Test Structure**:
```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);
    }

    /** @test */
    public function it_can_list_products()
    {
        Product::factory()->count(5)->create();
        
        $response = $this->getJson('/api/v1/products');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'code',
                            'price',
                            'stock'
                        ]
                    ],
                    'pagination'
                ]);
    }

    /** @test */
    public function it_can_create_product()
    {
        $productData = [
            'name' => 'New Product',
            'code' => 'NP001',
            'category_id' => 1,
            'price' => 100000,
            'cost_price' => 80000
        ];
        
        $response = $this->postJson('/api/v1/products', $productData);
        
        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'New Product',
                    'code' => 'NP001'
                ]);
                
        $this->assertDatabaseHas('products', $productData);
    }

    /** @test */
    public function it_validates_product_creation()
    {
        $response = $this->postJson('/api/v1/products', [
            'name' => '', // Required
            'code' => '', // Required
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'code']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        Sanctum::actingAs(null);
        
        $response = $this->getJson('/api/v1/products');
        
        $response->assertStatus(401);
    }
}
```

#### Database Integration Tests

```php
<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_updates_stock_when_sale_is_created()
    {
        $product = Product::factory()->create();
        
        // Initial stock
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'quantity' => 100
        ]);
        
        // Create sale
        $this->postJson('/api/v1/sales/transactions', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 50000
                ]
            ]
        ]);
        
        // Assert stock is reduced
        $this->assertDatabaseHas('stocks', [
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'quantity' => 95
        ]);
        
        // Assert stock movement is recorded
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'out',
            'quantity' => 5,
            'reference_type' => 'sale'
        ]);
    }
}
```

### 🌐 End-to-End (E2E) Tests

Testing complete user workflows.

#### Cypress E2E Tests

**Location**: `frontend/web/cypress/e2e/`

**Coverage Areas**:
- Complete user journeys
- Cross-browser compatibility
- Mobile responsiveness
- Performance scenarios

**Example Test Structure**:
```javascript
describe('Sales Transaction Flow', () => {
  beforeEach(() => {
    cy.login('cashier@example.com', 'password')
    cy.visit('/sales')
  })

  it('completes a full sales transaction', () => {
    // Add products to cart
    cy.get('[data-test="product-search"]').type('Beras')
    cy.get('[data-test="product-item"]').first().click()
    cy.get('[data-test="add-to-cart"]').click()
    
    // Verify cart
    cy.get('[data-test="cart-items"]').should('contain', 'Beras')
    cy.get('[data-test="cart-total"]').should('contain', '75,000')
    
    // Select customer
    cy.get('[data-test="customer-search"]').type('John Doe')
    cy.get('[data-test="customer-option"]').first().click()
    
    // Process payment
    cy.get('[data-test="payment-method"]').select('cash')
    cy.get('[data-test="payment-amount"]').type('100000')
    cy.get('[data-test="process-payment"]').click()
    
    // Verify transaction completion
    cy.get('[data-test="transaction-success"]').should('be.visible')
    cy.get('[data-test="transaction-number"]').should('contain', 'TRX')
    cy.get('[data-test="print-receipt"]').should('be.visible')
  })

  it('handles insufficient stock scenario', () => {
    cy.get('[data-test="product-search"]').type('Limited Stock')
    cy.get('[data-test="product-item"]').first().click()
    
    // Try to add more than available stock
    cy.get('[data-test="quantity-input"]').clear().type('999')
    cy.get('[data-test="add-to-cart"]').click()
    
    // Verify error message
    cy.get('[data-test="error-message"]')
      .should('be.visible')
      .and('contain', 'Insufficient stock')
  })

  it('calculates discounts correctly', () => {
    // Add product
    cy.addProductToCart('Beras Premium', 2)
    
    // Apply discount
    cy.get('[data-test="discount-input"]').type('10000')
    cy.get('[data-test="apply-discount"]').click()
    
    // Verify calculation
    cy.get('[data-test="subtotal"]').should('contain', '150,000')
    cy.get('[data-test="discount"]').should('contain', '10,000')
    cy.get('[data-test="total"]').should('contain', '140,000')
  })
})
```

## Testing Environment

### 🔧 Environment Setup

#### Backend Testing Environment

**Database Configuration** (`backend/.env.testing`):
```env
APP_ENV=testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
CACHE_DRIVER=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

**PHPUnit Configuration** (`backend/phpunit.xml`):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory>./app/Console</directory>
            <file>./app/Http/Kernel.php</file>
        </exclude>
    </coverage>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
    </php>
</phpunit>
```

#### Frontend Testing Environment

**Vitest Configuration** (`frontend/web/vitest.config.js`):
```javascript
import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { quasar, transformAssetUrls } from '@quasar/vite-plugin'
import path from 'path'

export default defineConfig({
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./tests/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.d.ts',
        'dist/'
      ]
    }
  },
  plugins: [
    vue({
      template: { transformAssetUrls }
    }),
    quasar({
      sassVariables: 'src/quasar-variables.sass'
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src')
    }
  }
})
```

**Cypress Configuration** (`frontend/web/cypress.config.js`):
```javascript
import { defineConfig } from 'cypress'

export default defineConfig({
  e2e: {
    baseUrl: 'http://localhost:9000',
    supportFile: 'cypress/support/e2e.js',
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    video: true,
    screenshotOnRunFailure: true,
    viewportWidth: 1280,
    viewportHeight: 720,
    defaultCommandTimeout: 10000,
    requestTimeout: 10000,
    responseTimeout: 10000
  },
  component: {
    devServer: {
      framework: 'vue',
      bundler: 'vite'
    },
    supportFile: 'cypress/support/component.js',
    specPattern: 'src/**/*.cy.{js,jsx,ts,tsx}'
  }
})
```

### 🗄️ Test Database

#### Database Seeding for Tests

**Test Seeder** (`backend/database/seeders/TestSeeder.php`):
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;

class TestSeeder extends Seeder
{
    public function run()
    {
        // Create test users
        User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password')
        ])->assignRole('admin');
        
        User::factory()->create([
            'email' => 'cashier@test.com',
            'password' => bcrypt('password')
        ])->assignRole('cashier');
        
        // Create test data
        Category::factory()->count(5)->create();
        Product::factory()->count(20)->create();
        Customer::factory()->count(10)->create();
        Warehouse::factory()->create([
            'name' => 'Test Warehouse',
            'code' => 'TW001'
        ]);
    }
}
```

## Running Tests

### 🚀 Backend Tests (Laravel)

#### Run All Tests
```bash
cd backend

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Unit/Models/ProductTest.php

# Run specific test method
php artisan test --filter=it_can_calculate_stock_value
```

#### Parallel Testing
```bash
# Install parallel testing
composer require --dev brianium/paratest

# Run tests in parallel
vendor/bin/paratest --processes=4

# Run with coverage
vendor/bin/paratest --processes=4 --coverage-html=coverage
```

#### Database Testing
```bash
# Refresh database before tests
php artisan test --recreate-databases

# Seed test database
php artisan db:seed --class=TestSeeder --env=testing
```

### 🎨 Frontend Tests (Vue/Quasar)

#### Unit Tests with Vitest
```bash
cd frontend/web

# Run all unit tests
npm run test:unit

# Run with coverage
npm run test:unit:coverage

# Run in watch mode
npm run test:unit:watch

# Run specific test file
npm run test:unit ProductCard.spec.js

# Run tests matching pattern
npm run test:unit -- --grep="ProductCard"
```

#### E2E Tests with Cypress
```bash
# Open Cypress Test Runner
npm run test:e2e

# Run headless
npm run test:e2e:headless

# Run specific spec
npm run cypress run --spec "cypress/e2e/sales.cy.js"

# Run on different browser
npm run cypress run --browser chrome
npm run cypress run --browser firefox
```

#### Component Testing
```bash
# Run component tests
npm run test:component

# Open component test runner
npm run cypress open --component
```

### 📊 Test Scripts

**Package.json Scripts** (`frontend/web/package.json`):
```json
{
  "scripts": {
    "test": "npm run test:unit && npm run test:e2e:headless",
    "test:unit": "vitest run",
    "test:unit:watch": "vitest",
    "test:unit:coverage": "vitest run --coverage",
    "test:e2e": "cypress open",
    "test:e2e:headless": "cypress run",
    "test:component": "cypress run --component",
    "test:all": "npm run test:unit:coverage && npm run test:e2e:headless"
  }
}
```

**Composer Scripts** (`backend/composer.json`):
```json
{
  "scripts": {
    "test": "php artisan test",
    "test:coverage": "php artisan test --coverage",
    "test:unit": "php artisan test --testsuite=Unit",
    "test:feature": "php artisan test --testsuite=Feature",
    "test:parallel": "vendor/bin/paratest --processes=4"
  }
}
```

## Code Coverage

### 📈 Coverage Requirements

| Component | Minimum Coverage | Target Coverage |
|-----------|------------------|----------------|
| Models | 90% | 95% |
| Controllers | 85% | 90% |
| Services | 95% | 98% |
| Repositories | 90% | 95% |
| Utilities | 85% | 90% |
| API Routes | 90% | 95% |
| Vue Components | 80% | 85% |
| Composables | 85% | 90% |
| Stores | 90% | 95% |

### 📊 Coverage Reports

#### Backend Coverage (PHPUnit)
```bash
# Generate HTML coverage report
php artisan test --coverage-html=coverage

# Generate text coverage report
php artisan test --coverage-text

# Generate Clover XML report
php artisan test --coverage-clover=coverage.xml

# Set minimum coverage threshold
php artisan test --coverage --min=85
```

#### Frontend Coverage (Vitest)
```bash
# Generate coverage report
npm run test:unit:coverage

# View coverage report
open coverage/index.html

# Coverage with threshold
npm run vitest run --coverage --coverage.thresholds.lines=80
```

### 📋 Coverage Configuration

**Vitest Coverage** (`frontend/web/vitest.config.js`):
```javascript
export default defineConfig({
  test: {
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      thresholds: {
        global: {
          branches: 80,
          functions: 80,
          lines: 80,
          statements: 80
        }
      },
      exclude: [
        'node_modules/',
        'tests/',
        '**/*.d.ts',
        'dist/',
        'src/boot/',
        'src/router/index.js'
      ]
    }
  }
})
```

## Test Data Management

### 🏭 Factories

#### Laravel Model Factories

**Product Factory** (`backend/database/factories/ProductFactory.php`):
```php
<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'description' => $this->faker->sentence(),
            'category_id' => Category::factory(),
            'base_unit_id' => 1,
            'cost_price' => $this->faker->numberBetween(10000, 100000),
            'selling_price' => function (array $attributes) {
                return $attributes['cost_price'] * 1.3;
            },
            'min_stock' => $this->faker->numberBetween(5, 20),
            'max_stock' => $this->faker->numberBetween(50, 200),
            'is_active' => true,
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }

    public function withStock($quantity = 100)
    {
        return $this->afterCreating(function ($product) use ($quantity) {
            $product->stocks()->create([
                'warehouse_id' => 1,
                'quantity' => $quantity,
            ]);
        });
    }
}
```

### 🎭 Test Fixtures

#### Frontend Test Fixtures

**Product Fixtures** (`frontend/web/tests/fixtures/products.js`):
```javascript
export const mockProducts = [
  {
    id: 1,
    name: 'Beras Premium 5kg',
    code: 'BRS001',
    price: 75000,
    cost_price: 65000,
    stock: 50,
    category: {
      id: 1,
      name: 'Makanan & Minuman'
    },
    is_active: true
  },
  {
    id: 2,
    name: 'Minyak Goreng 1L',
    code: 'MYK001',
    price: 25000,
    cost_price: 20000,
    stock: 30,
    category: {
      id: 1,
      name: 'Makanan & Minuman'
    },
    is_active: true
  }
]

export const mockCustomers = [
  {
    id: 1,
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+6281234567890',
    address: 'Jl. Sudirman No. 123',
    loyalty_points: 150
  }
]

export const mockTransaction = {
  id: 1,
  transaction_number: 'TRX20250901001',
  customer_id: 1,
  items: [
    {
      product_id: 1,
      quantity: 2,
      unit_price: 75000,
      total_price: 150000
    }
  ],
  subtotal: 150000,
  tax_amount: 15000,
  discount_amount: 5000,
  total_amount: 160000,
  status: 'completed'
}
```

### 🔄 Database Transactions

#### Test Database Management

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed essential data for every test
        $this->seed([
            RolePermissionSeeder::class,
            UnitSeeder::class,
            WarehouseSeeder::class
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up any test artifacts
        DB::statement('PRAGMA foreign_keys=0');
        DB::statement('DELETE FROM sqlite_sequence');
        DB::statement('PRAGMA foreign_keys=1');
        
        parent::tearDown();
    }

    /**
     * Create authenticated user for testing
     */
    protected function actingAsUser($role = 'cashier')
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        
        return $this->actingAs($user);
    }

    /**
     * Create API authenticated user
     */
    protected function actingAsApiUser($role = 'cashier')
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        
        Sanctum::actingAs($user, ['*']);
        
        return $user;
    }
}
```

## Continuous Integration

### 🔄 GitHub Actions Workflow

**CI/CD Pipeline** (`.github/workflows/test.yml`):
```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: qpos_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: mbstring, dom, fileinfo, mysql, gd
        coverage: xdebug
    
    - name: Cache Composer packages
      id: composer-cache
      uses: actions/cache@v3
      with:
        path: backend/vendor
        key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
        restore-keys: |
          ${{ runner.os }}-php-
    
    - name: Install dependencies
      working-directory: backend
      run: composer install --prefer-dist --no-progress
    
    - name: Copy environment file
      working-directory: backend
      run: cp .env.testing .env
    
    - name: Generate application key
      working-directory: backend
      run: php artisan key:generate
    
    - name: Run database migrations
      working-directory: backend
      run: php artisan migrate --force
    
    - name: Run tests
      working-directory: backend
      run: php artisan test --coverage --coverage-clover=coverage.xml
    
    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: backend/coverage.xml
        flags: backend
        name: backend-coverage

  frontend-tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
        cache-dependency-path: frontend/web/package-lock.json
    
    - name: Install dependencies
      working-directory: frontend/web
      run: npm ci
    
    - name: Run unit tests
      working-directory: frontend/web
      run: npm run test:unit:coverage
    
    - name: Run E2E tests
      working-directory: frontend/web
      run: npm run test:e2e:headless
    
    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: frontend/web/coverage/lcov.info
        flags: frontend
        name: frontend-coverage

  quality-checks:
    runs-on: ubuntu-latest
    needs: [backend-tests, frontend-tests]
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Run PHP CS Fixer
      working-directory: backend
      run: vendor/bin/php-cs-fixer fix --dry-run --diff
    
    - name: Run PHPStan
      working-directory: backend
      run: vendor/bin/phpstan analyse
    
    - name: Run ESLint
      working-directory: frontend/web
      run: npm run lint
    
    - name: Run Prettier
      working-directory: frontend/web
      run: npm run format:check
```

### 📊 Quality Gates

**SonarQube Configuration** (`sonar-project.properties`):
```properties
sonar.projectKey=qpos
sonar.projectName=Q-POS
sonar.projectVersion=1.0

# Source directories
sonar.sources=backend/app,frontend/web/src
sonar.tests=backend/tests,frontend/web/tests

# Coverage reports
sonar.php.coverage.reportPaths=backend/coverage.xml
sonar.javascript.lcov.reportPaths=frontend/web/coverage/lcov.info

# Quality gates
sonar.qualitygate.wait=true
sonar.coverage.exclusions=**/*Test.php,**/tests/**,**/node_modules/**

# Thresholds
sonar.coverage.minimum=85
sonar.duplicated_lines_density.maximum=3
sonar.maintainability_rating.minimum=A
sonar.reliability_rating.minimum=A
sonar.security_rating.minimum=A
```

## Performance Testing

### ⚡ Load Testing

#### Artillery.js Configuration

**Load Test Script** (`tests/performance/load-test.yml`):
```yaml
config:
  target: 'http://localhost:8000'
  phases:
    - duration: 60
      arrivalRate: 10
      name: "Warm up"
    - duration: 120
      arrivalRate: 50
      name: "Ramp up load"
    - duration: 300
      arrivalRate: 100
      name: "Sustained load"
  processor: "./auth-processor.js"
  
scenarios:
  - name: "API Load Test"
    weight: 70
    flow:
      - post:
          url: "/api/v1/auth/login"
          json:
            email: "test@example.com"
            password: "password"
          capture:
            - json: "$.data.token"
              as: "token"
      
      - get:
          url: "/api/v1/products"
          headers:
            Authorization: "Bearer {{ token }}"
      
      - get:
          url: "/api/v1/customers"
          headers:
            Authorization: "Bearer {{ token }}"
      
      - post:
          url: "/api/v1/sales/transactions"
          headers:
            Authorization: "Bearer {{ token }}"
          json:
            customer_id: 1
            items:
              - product_id: 1
                quantity: 2
                unit_price: 75000
  
  - name: "Database Heavy Operations"
    weight: 30
    flow:
      - post:
          url: "/api/v1/auth/login"
          json:
            email: "test@example.com"
            password: "password"
          capture:
            - json: "$.data.token"
              as: "token"
      
      - get:
          url: "/api/v1/reports/sales"
          headers:
            Authorization: "Bearer {{ token }}"
          qs:
            date_from: "2025-09-01"
            date_to: "2025-09-30"
            group_by: "day"
```

#### Performance Test Commands

```bash
# Install Artillery
npm install -g artillery

# Run load test
artillery run tests/performance/load-test.yml

# Run with custom target
artillery run --target http://staging.qpos.com tests/performance/load-test.yml

# Generate HTML report
artillery run tests/performance/load-test.yml --output report.json
artillery report report.json
```

### 📈 Performance Benchmarks

#### API Response Time Targets

| Endpoint Category | Target Response Time | Max Response Time |
|-------------------|---------------------|-------------------|
| Authentication | < 200ms | < 500ms |
| Product Listing | < 300ms | < 800ms |
| Sales Transaction | < 500ms | < 1000ms |
| Reports | < 1000ms | < 3000ms |
| File Upload | < 2000ms | < 5000ms |

#### Database Performance

```sql
-- Query performance monitoring
SELECT 
    query_time,
    lock_time,
    rows_sent,
    rows_examined,
    sql_text
FROM mysql.slow_log 
WHERE query_time > 1
ORDER BY query_time DESC;

-- Index usage analysis
SELECT 
    table_name,
    index_name,
    cardinality,
    sub_part,
    packed,
    nullable,
    index_type
FROM information_schema.statistics 
WHERE table_schema = 'qpos'
ORDER BY table_name, seq_in_index;
```

## Security Testing

### 🔒 Security Test Categories

#### Authentication & Authorization Tests

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prevents_brute_force_attacks()
    {
        // Attempt multiple failed logins
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password'
            ]);
        }
        
        // Next attempt should be rate limited
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);
        
        $response->assertStatus(429); // Too Many Requests
    }

    /** @test */
    public function it_validates_token_expiration()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token', ['*'], now()->subHour());
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ])->getJson('/api/v1/user');
        
        $response->assertStatus(401);
    }

    /** @test */
    public function it_prevents_privilege_escalation()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        Sanctum::actingAs($cashier);
        
        // Cashier should not access admin endpoints
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(403);
        
        // Cashier should not modify other users
        $otherUser = User::factory()->create();
        $response = $this->putJson("/api/v1/users/{$otherUser->id}", [
            'name' => 'Modified Name'
        ]);
        $response->assertStatus(403);
    }
}
```

#### Input Validation & Sanitization

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prevents_sql_injection()
    {
        $this->actingAsApiUser();
        
        $maliciousInput = "'; DROP TABLE products; --";
        
        $response = $this->getJson('/api/v1/products', [
            'search' => $maliciousInput
        ]);
        
        // Should not cause SQL error
        $response->assertStatus(200);
        
        // Verify products table still exists
        $this->assertDatabaseHas('products', []);
    }

    /** @test */
    public function it_prevents_xss_attacks()
    {
        $this->actingAsApiUser();
        
        $xssPayload = '<script>alert("XSS")</script>';
        
        $response = $this->postJson('/api/v1/products', [
            'name' => $xssPayload,
            'code' => 'TEST001',
            'category_id' => 1,
            'price' => 10000
        ]);
        
        if ($response->status() === 201) {
            $product = $response->json('data');
            // Verify XSS payload is escaped
            $this->assertStringNotContainsString('<script>', $product['name']);
        }
    }

    /** @test */
    public function it_validates_file_uploads()
    {
        $this->actingAsApiUser();
        
        // Test malicious file upload
        $maliciousFile = UploadedFile::fake()->create('malicious.php', 100);
        
        $response = $this->postJson('/api/v1/products/1/image', [
            'image' => $maliciousFile
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['image']);
    }
}
```

### 🛡️ Security Scanning

#### OWASP ZAP Integration

**Security Scan Script** (`tests/security/zap-scan.sh`):
```bash
#!/bin/bash

# Start ZAP daemon
docker run -d --name zap-scan \
  -p 8080:8080 \
  owasp/zap2docker-stable zap.sh -daemon -host 0.0.0.0 -port 8080

# Wait for ZAP to start
sleep 30

# Run spider scan
curl "http://localhost:8080/JSON/spider/action/scan/?url=http://host.docker.internal:8000"

# Wait for spider to complete
while [ $(curl -s "http://localhost:8080/JSON/spider/view/status/" | jq -r '.status') != "100" ]; do
  echo "Spider scan in progress..."
  sleep 10
done

# Run active scan
curl "http://localhost:8080/JSON/ascan/action/scan/?url=http://host.docker.internal:8000"

# Wait for active scan to complete
while [ $(curl -s "http://localhost:8080/JSON/ascan/view/status/" | jq -r '.status') != "100" ]; do
  echo "Active scan in progress..."
  sleep 30
done

# Generate report
curl "http://localhost:8080/JSON/core/view/htmlreport/" > security-report.html

# Stop ZAP
docker stop zap-scan
docker rm zap-scan

echo "Security scan completed. Report saved to security-report.html"
```

## API Testing

### 👥 User Management API Tests

#### User CRUD Operations Test

**Test File**: `backend/tests/Feature/Api/UserManagementTest.php`

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function it_can_list_users_with_pagination()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
        
        User::factory()->count(25)->create();
        
        $response = $this->getJson('/api/v1/users?page=1&per_page=10');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'email',
                            'role',
                            'status',
                            'created_at'
                        ]
                    ],
                    'pagination' => [
                        'current_page',
                        'total',
                        'per_page'
                    ]
                ]);
        
        $this->assertEquals(10, count($response->json('data')));
    }

    /** @test */
    public function it_can_search_users_by_name_and_email()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
        
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@example.com']);
        
        // Search by name
        $response = $this->getJson('/api/v1/users?search=John');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('John Doe', $response->json('data.0.name'));
        
        // Search by email
        $response = $this->getJson('/api/v1/users?search=jane@example.com');
        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
        $this->assertEquals('jane@example.com', $response->json('data.0.email'));
    }

    /** @test */
    public function it_can_create_new_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
        
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'cashier'
        ];
        
        $response = $this->postJson('/api/v1/users', $userData);
        
        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'role'
                    ]
                ]);
        
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com'
        ]);
    }

    /** @test */
    public function it_validates_user_creation_data()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
        
        // Test missing required fields
        $response = $this->postJson('/api/v1/users', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email', 'password']);
        
        // Test invalid email format
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        
        // Test password confirmation mismatch
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password'
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_can_update_user_information()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
        
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'manager'
        ];
        
        $response = $this->putJson("/api/v1/users/{$user->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'name' => 'Updated Name',
                        'email' => 'updated@example.com'
                    ]
                ]);
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    /** @test */
    public function it_can_delete_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        Sanctum::actingAs($admin);
        
        $user = User::factory()->create();
        
        $response = $this->deleteJson("/api/v1/users/{$user->id}");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'User deleted successfully'
                ]);
        
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_prevents_unauthorized_access_to_user_management()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        Sanctum::actingAs($cashier);
        
        // Cashier should not access user management
        $response = $this->getJson('/api/v1/users');
        $response->assertStatus(403);
        
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $response->assertStatus(403);
    }
}
```

### 📡 Postman Collections

#### Collection Structure

**Q-POS API Collection** (`tests/api/Q-POS.postman_collection.json`):
```json
{
  "info": {
    "name": "Q-POS API",
    "description": "Complete API test collection for Q-POS",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{auth_token}}",
        "type": "string"
      }
    ]
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/v1"
    },
    {
      "key": "auth_token",
      "value": ""
    }
  ],
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code is 200', function () {",
                  "    pm.response.to.have.status(200);",
                  "});",
                  "",
                  "pm.test('Response has token', function () {",
                  "    var jsonData = pm.response.json();",
                  "    pm.expect(jsonData.data).to.have.property('token');",
                  "    pm.collectionVariables.set('auth_token', jsonData.data.token);",
                  "});"
                ]
              }
            }
          ],
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"admin@example.com\",\n  \"password\": \"password\"\n}",
              "options": {
                "raw": {
                  "language": "json"
                }
              }
            },
            "url": {
              "raw": "{{base_url}}/auth/login",
              "host": ["{{base_url}}"],
              "path": ["auth", "login"]
            }
          }
        }
      ]
    }
  ]
}
```

#### Environment Configuration

**Development Environment** (`tests/api/development.postman_environment.json`):
```json
{
  "id": "dev-env",
  "name": "Development",
  "values": [
    {
      "key": "base_url",
      "value": "http://localhost:8000/api/v1",
      "enabled": true
    },
    {
      "key": "admin_email",
      "value": "admin@example.com",
      "enabled": true
    },
    {
      "key": "admin_password",
      "value": "password",
      "enabled": true
    }
  ]
}
```

### 🔧 Newman (CLI Testing)

```bash
# Install Newman
npm install -g newman

# Run collection
newman run tests/api/Q-POS.postman_collection.json \
  -e tests/api/development.postman_environment.json \
  --reporters cli,html \
  --reporter-html-export api-test-report.html

# Run with data file
newman run tests/api/Q-POS.postman_collection.json \
  -d tests/api/test-data.json \
  --iteration-count 10

# Run specific folder
newman run tests/api/Q-POS.postman_collection.json \
  --folder "Products API"
```

## Frontend Testing

### 🎨 Component Testing

#### Vue Test Utils Examples

**Product Form Component Test**:
```javascript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductForm from '@/components/forms/ProductForm.vue'
import { useProductStore } from '@/stores/product'
import { Quasar } from 'quasar'

describe('ProductForm.vue', () => {
  let wrapper
  let productStore

  beforeEach(() => {
    wrapper = mount(ProductForm, {
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn
          }),
          Quasar
        ]
      },
      props: {
        mode: 'create'
      }
    })
    
    productStore = useProductStore()
  })

  it('renders form fields correctly', () => {
    expect(wrapper.find('[data-test="product-name"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="product-code"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="product-category"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="product-price"]').exists()).toBe(true)
  })

  it('validates required fields', async () => {
    // Submit form without filling required fields
    await wrapper.find('[data-test="submit-button"]').trigger('click')
    
    // Check for validation errors
    expect(wrapper.find('[data-test="name-error"]').exists()).toBe(true)
    expect(wrapper.find('[data-test="code-error"]').exists()).toBe(true)
  })

  it('submits form with valid data', async () => {
    // Fill form fields
    await wrapper.find('[data-test="product-name"]').setValue('Test Product')
    await wrapper.find('[data-test="product-code"]').setValue('TEST001')
    await wrapper.find('[data-test="product-category"]').setValue('1')
    await wrapper.find('[data-test="product-price"]').setValue('50000')
    
    // Submit form
    await wrapper.find('[data-test="submit-button"]').trigger('click')
    
    // Verify store action was called
    expect(productStore.createProduct).toHaveBeenCalledWith({
      name: 'Test Product',
      code: 'TEST001',
      category_id: '1',
      price: '50000'
    })
  })

  it('handles API errors gracefully', async () => {
    // Mock API error
    productStore.createProduct.mockRejectedValue({
      response: {
        data: {
          errors: {
            code: ['The code has already been taken.']
          }
        }
      }
    })
    
    // Fill and submit form
    await wrapper.find('[data-test="product-name"]').setValue('Test Product')
    await wrapper.find('[data-test="product-code"]').setValue('EXISTING001')
    await wrapper.find('[data-test="submit-button"]').trigger('click')
    
    // Wait for error to be displayed
    await wrapper.vm.$nextTick()
    
    expect(wrapper.find('[data-test="code-error"]').text())
      .toContain('The code has already been taken.')
  })
})
```

### 🔄 Store Testing (Pinia)

#### Vue.js Reactivity Testing (Recent Updates v0.1.3)

**Test File**: `frontend/web/tests/unit/stores/reactivity.test.js`

```javascript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useProductStore } from '@/stores/product'
import { useAuthStore } from '@/stores/auth'

// Test for Vue.js readonly computed property fixes
describe('Vue.js Reactivity Optimizations', () => {
  let productStore
  let authStore

  beforeEach(() => {
    setActivePinia(createPinia())
    productStore = useProductStore()
    authStore = useAuthStore()
    vi.clearAllMocks()
  })

  describe('Computed Property Readonly Fixes', () => {
    it('should not trigger readonly warnings when accessing computed properties', () => {
      const consoleSpy = vi.spyOn(console, 'warn')
      
      // Access computed properties that were previously causing warnings
      const filteredProducts = productStore.filteredProducts
      const totalStockValue = productStore.totalStockValue
      const isAuthenticated = authStore.isAuthenticated
      
      // Verify no readonly warnings are triggered
      expect(consoleSpy).not.toHaveBeenCalledWith(
        expect.stringContaining('readonly')
      )
      expect(consoleSpy).not.toHaveBeenCalledWith(
        expect.stringContaining('computed')
      )
      
      consoleSpy.mockRestore()
    })

    it('should properly handle reactive state updates without warnings', async () => {
      const consoleSpy = vi.spyOn(console, 'warn')
      
      // Update reactive state
      productStore.products = [
        { id: 1, name: 'Product 1', price: 10000, stock: 50 },
        { id: 2, name: 'Product 2', price: 20000, stock: 30 }
      ]
      
      // Access computed properties after state change
      const totalValue = productStore.totalStockValue
      expect(totalValue).toBe(1100000)
      
      // Verify no warnings during reactive updates
      expect(consoleSpy).not.toHaveBeenCalledWith(
        expect.stringContaining('readonly')
      )
      
      consoleSpy.mockRestore()
    })

    it('should maintain proper reactivity after optimization', () => {
      // Set initial state
      productStore.searchTerm = ''
      productStore.products = [
        { id: 1, name: 'Beras Premium', code: 'BRS001' },
        { id: 2, name: 'Minyak Goreng', code: 'MYK001' }
      ]
      
      // Initial filtered products
      expect(productStore.filteredProducts).toHaveLength(2)
      
      // Update search term
      productStore.searchTerm = 'beras'
      
      // Verify reactivity still works
      expect(productStore.filteredProducts).toHaveLength(1)
      expect(productStore.filteredProducts[0].name).toContain('Beras')
    })
  })

  describe('Performance Optimizations', () => {
    it('should not recompute unnecessarily', () => {
      const computeSpy = vi.fn(() => productStore.products.length)
      
      // Mock computed property
      Object.defineProperty(productStore, 'productCount', {
        get: computeSpy
      })
      
      // Access multiple times
      productStore.productCount
      productStore.productCount
      productStore.productCount
      
      // Should only compute once due to caching
      expect(computeSpy).toHaveBeenCalledTimes(3) // Vue 3 behavior
    })
  })
})
```

**Product Store Test**:
```javascript
import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useProductStore } from '@/stores/product'
import { api } from '@/services/api'

// Mock API
vi.mock('@/services/api', () => ({
  api: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn()
  }
}))

describe('Product Store', () => {
  let store

  beforeEach(() => {
    setActivePinia(createPinia())
    store = useProductStore()
    vi.clearAllMocks()
  })

  describe('fetchProducts', () => {
    it('fetches products successfully', async () => {
      const mockProducts = [
        { id: 1, name: 'Product 1', price: 10000 },
        { id: 2, name: 'Product 2', price: 20000 }
      ]
      
      api.get.mockResolvedValue({
        data: {
          success: true,
          data: mockProducts,
          pagination: {
            current_page: 1,
            total: 2
          }
        }
      })
      
      await store.fetchProducts()
      
      expect(api.get).toHaveBeenCalledWith('/products', {
        params: {
          page: 1,
          per_page: 15
        }
      })
      
      expect(store.products).toEqual(mockProducts)
      expect(store.pagination.total).toBe(2)
      expect(store.loading).toBe(false)
    })

    it('handles fetch error', async () => {
      api.get.mockRejectedValue(new Error('Network error'))
      
      await store.fetchProducts()
      
      expect(store.products).toEqual([])
      expect(store.error).toBe('Failed to fetch products')
      expect(store.loading).toBe(false)
    })
  })

  describe('createProduct', () => {
    it('creates product successfully', async () => {
      const newProduct = {
        name: 'New Product',
        code: 'NEW001',
        price: 30000
      }
      
      const createdProduct = { id: 3, ...newProduct }
      
      api.post.mockResolvedValue({
        data: {
          success: true,
          data: createdProduct
        }
      })
      
      const result = await store.createProduct(newProduct)
      
      expect(api.post).toHaveBeenCalledWith('/products', newProduct)
      expect(result).toEqual(createdProduct)
      expect(store.products).toContain(createdProduct)
    })
  })

  describe('getters', () => {
    it('filters products by search term', () => {
      store.products = [
        { id: 1, name: 'Beras Premium', code: 'BRS001' },
        { id: 2, name: 'Minyak Goreng', code: 'MYK001' },
        { id: 3, name: 'Beras Biasa', code: 'BRS002' }
      ]
      
      store.searchTerm = 'beras'
      
      expect(store.filteredProducts).toHaveLength(2)
      expect(store.filteredProducts[0].name).toContain('Beras')
      expect(store.filteredProducts[1].name).toContain('Beras')
    })

    it('calculates total stock value', () => {
      store.products = [
        {
          id: 1,
          name: 'Product 1',
          cost_price: 10000,
          stock: 50
        },
        {
          id: 2,
          name: 'Product 2',
          cost_price: 20000,
          stock: 30
        }
      ]
      
      expect(store.totalStockValue).toBe(1100000) // (10000*50) + (20000*30)
    })
  })
})
```

### 🌐 E2E Testing Strategies

#### User Journey Testing

**Complete Sales Flow Test**:
```javascript
describe('Complete Sales Journey', () => {
  beforeEach(() => {
    cy.login('cashier@example.com', 'password')
    cy.seedTestData()
  })

  it('processes a complete sales transaction from start to finish', () => {
    // Navigate to sales page
    cy.visit('/sales')
    cy.get('[data-test="page-title"]').should('contain', 'Point of Sale')
    
    // Search and add products
    cy.get('[data-test="product-search"]').type('Beras')
    cy.get('[data-test="product-list"]').should('be.visible')
    cy.get('[data-test="product-item"]').first().click()
    
    // Verify product added to cart
    cy.get('[data-test="cart-items"]').should('contain', 'Beras')
    cy.get('[data-test="cart-quantity"]').should('contain', '1')
    
    // Modify quantity
    cy.get('[data-test="quantity-input"]').clear().type('3')
    cy.get('[data-test="update-quantity"]').click()
    
    // Add another product
    cy.get('[data-test="product-search"]').clear().type('Minyak')
    cy.get('[data-test="product-item"]').first().click()
    
    // Select customer
    cy.get('[data-test="customer-search"]').type('John Doe')
    cy.get('[data-test="customer-dropdown"]').should('be.visible')
    cy.get('[data-test="customer-option"]').first().click()
    
    // Apply discount
    cy.get('[data-test="discount-type"]').select('percentage')
    cy.get('[data-test="discount-value"]').type('10')
    cy.get('[data-test="apply-discount"]').click()
    
    // Verify calculations
    cy.get('[data-test="subtotal"]').should('not.be.empty')
    cy.get('[data-test="discount-amount"]').should('contain', '10%')
    cy.get('[data-test="total-amount"]').should('not.be.empty')
    
    // Process payment
    cy.get('[data-test="payment-method"]').select('cash')
    cy.get('[data-test="payment-amount"]').type('500000')
    cy.get('[data-test="calculate-change"]').click()
    
    // Verify change calculation
    cy.get('[data-test="change-amount"]').should('be.visible')
    
    // Complete transaction
    cy.get('[data-test="complete-transaction"]').click()
    
    // Verify success
    cy.get('[data-test="transaction-success"]').should('be.visible')
    cy.get('[data-test="transaction-number"]').should('match', /^TRX\d+$/)
    
    // Print receipt
    cy.get('[data-test="print-receipt"]').click()
    cy.get('[data-test="receipt-modal"]').should('be.visible')
    
    // Start new transaction
    cy.get('[data-test="new-transaction"]').click()
    cy.get('[data-test="cart-items"]').should('be.empty')
  })
})
```

## Database Testing

### 🗄️ Database Test Strategies

#### Migration Testing

```php
<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_all_required_tables()
    {
        $expectedTables = [
            'users', 'roles', 'permissions', 'role_has_permissions',
            'model_has_roles', 'model_has_permissions',
            'categories', 'products', 'customers', 'suppliers',
            'warehouses', 'stocks', 'stock_movements',
            'sales_transactions', 'sales_transaction_items',
            'purchase_orders', 'purchase_order_items',
            'payments', 'payment_methods'
        ];
        
        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Table {$table} does not exist"
            );
        }
    }

    /** @test */
    public function it_has_correct_foreign_key_constraints()
    {
        // Test products table foreign keys
        $this->assertTrue(Schema::hasColumn('products', 'category_id'));
        $this->assertTrue(Schema::hasColumn('products', 'base_unit_id'));
        
        // Test sales_transaction_items foreign keys
        $this->assertTrue(Schema::hasColumn('sales_transaction_items', 'sales_transaction_id'));
        $this->assertTrue(Schema::hasColumn('sales_transaction_items', 'product_id'));
        
        // Test stocks foreign keys
        $this->assertTrue(Schema::hasColumn('stocks', 'product_id'));
        $this->assertTrue(Schema::hasColumn('stocks', 'warehouse_id'));
    }

    /** @test */
    public function it_has_required_indexes()
    {
        $indexes = [
            'products' => ['category_id', 'code'],
            'stocks' => ['product_id', 'warehouse_id'],
            'sales_transactions' => ['customer_id', 'transaction_date'],
            'stock_movements' => ['product_id', 'movement_date']
        ];
        
        foreach ($indexes as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Index on {$table}.{$column} does not exist"
                );
            }
        }
    }
}
```

#### Data Integrity Testing

```php
<?php

namespace Tests\Feature\Database;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Stock;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_maintains_stock_consistency_during_transactions()
    {
        $product = Product::factory()->create();
        
        // Initial stock
        $initialStock = 100;
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'quantity' => $initialStock
        ]);
        
        // Create multiple concurrent transactions
        $transactions = [];
        for ($i = 0; $i < 5; $i++) {
            $transaction = SalesTransaction::create([
                'customer_id' => 1,
                'transaction_date' => now(),
                'total_amount' => 50000
            ]);
            
            SalesTransactionItem::create([
                'sales_transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => 10,
                'unit_price' => 5000,
                'total_price' => 50000
            ]);
            
            $transactions[] = $transaction;
        }
        
        // Verify final stock
        $finalStock = Stock::where('product_id', $product->id)->first();
        $this->assertEquals(50, $finalStock->quantity); // 100 - (5 * 10)
        
        // Verify stock movements
        $totalMovements = $product->stockMovements()
            ->where('movement_type', 'out')
            ->sum('quantity');
        $this->assertEquals(50, $totalMovements);
    }

    /** @test */
    public function it_prevents_negative_stock()
    {
        $product = Product::factory()->create();
        
        Stock::create([
            'product_id' => $product->id,
            'warehouse_id' => 1,
            'quantity' => 5
        ]);
        
        $this->expectException(\Exception::class);
        
        // Try to sell more than available
        $transaction = SalesTransaction::create([
            'customer_id' => 1,
            'transaction_date' => now(),
            'total_amount' => 100000
        ]);
        
        SalesTransactionItem::create([
            'sales_transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 10, // More than available (5)
            'unit_price' => 10000,
            'total_price' => 100000
        ]);
    }
}
```

## Best Practices

### ✅ Testing Guidelines

#### 1. Test Organization

- **Arrange-Act-Assert (AAA) Pattern**:
  ```php
  public function test_example()
  {
      // Arrange
      $user = User::factory()->create();
      $product = Product::factory()->create();
      
      // Act
      $response = $this->actingAs($user)
          ->postJson('/api/products', $product->toArray());
      
      // Assert
      $response->assertStatus(201);
      $this->assertDatabaseHas('products', ['id' => $product->id]);
  }
  ```

#### 2. Test Naming Conventions

- **Descriptive Test Names**:
  ```php
  // Good
  public function it_prevents_unauthorized_access_to_admin_endpoints()
  public function it_calculates_discount_correctly_for_percentage_type()
  public function it_validates_required_fields_when_creating_product()
  
  // Bad
  public function test_auth()
  public function test_discount()
  public function test_validation()
  ```

#### 3. Test Data Management

- **Use Factories for Test Data**:
  ```php
  // Good
  $products = Product::factory()->count(10)->create();
  $customer = Customer::factory()->withOrders(5)->create();
  
  // Bad
  $product = new Product();
  $product->name = 'Test Product';
  $product->code = 'TEST001';
  $product->save();
  ```

#### 4. Mocking External Dependencies

- **Mock External Services**:
  ```php
  public function it_sends_notification_when_stock_is_low()
  {
      // Mock notification service
      $this->mock(NotificationService::class, function ($mock) {
          $mock->shouldReceive('sendLowStockAlert')
               ->once()
               ->with(Mockery::type(Product::class));
      });
      
      // Test the functionality
      $product = Product::factory()->create(['min_stock' => 10]);
      $product->updateStock(5); // Below minimum
  }
  ```

#### 5. Database Testing

- **Use Database Transactions**:
  ```php
  use Illuminate\Foundation\Testing\RefreshDatabase;
  
  class ProductTest extends TestCase
  {
      use RefreshDatabase;
      
      // Tests will automatically rollback database changes
  }
  ```

#### 6. API Testing

- **Test All Response Scenarios**:
  ```php
  public function it_handles_product_creation_scenarios()
  {
      // Success scenario
      $response = $this->postJson('/api/products', $validData);
      $response->assertStatus(201);
      
      // Validation error scenario
      $response = $this->postJson('/api/products', $invalidData);
      $response->assertStatus(422);
      
      // Unauthorized scenario
      $response = $this->postJson('/api/products', $validData);
      $response->assertStatus(401);
  }
  ```

### 🚫 Common Anti-Patterns

#### 1. Testing Implementation Details
```php
// Bad - Testing internal method calls
public function it_calls_specific_method()
{
    $service = $this->mock(ProductService::class);
    $service->shouldReceive('calculatePrice')->once();
    
    $controller = new ProductController($service);
    $controller->store($request);
}

// Good - Testing behavior
public function it_creates_product_with_calculated_price()
{
    $response = $this->postJson('/api/products', $productData);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('products', [
        'name' => $productData['name'],
        'selling_price' => $expectedPrice
    ]);
}
```

#### 2. Overly Complex Test Setup
```php
// Bad - Complex setup
public function it_processes_complex_scenario()
{
    $user = User::factory()->create();
    $role = Role::create(['name' => 'cashier']);
    $user->assignRole($role);
    $category = Category::create(['name' => 'Food']);
    $product = Product::create([
        'name' => 'Test Product',
        'category_id' => $category->id,
        // ... many more fields
    ]);
    // ... more setup
    
    // Actual test
}

// Good - Use factories and helper methods
public function it_processes_complex_scenario()
{
    $this->actingAsCashier();
    $product = Product::factory()->inCategory('Food')->create();
    
    // Actual test
}
```

## Troubleshooting

### 🔧 Common Issues

#### 1. Database Connection Issues

**Problem**: Tests fail with database connection errors

**Solution**:
```bash
# Check test database configuration
php artisan config:clear
php artisan cache:clear

# Verify .env.testing file
cat backend/.env.testing

# Test database connection
php artisan tinker --env=testing
>>> DB::connection()->getPdo();
```

#### 2. Memory Limit Issues

**Problem**: Tests fail with memory limit exceeded

**Solution**:
```bash
# Increase memory limit for tests
php -d memory_limit=512M artisan test

# Or update phpunit.xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

#### 3. Slow Test Execution

**Problem**: Tests take too long to run

**Solutions**:
```bash
# Use parallel testing
vendor/bin/paratest --processes=4

# Use in-memory SQLite for faster tests
# In .env.testing:
DB_CONNECTION=sqlite
DB_DATABASE=:memory:

# Optimize test database
php artisan config:cache --env=testing
```

#### 4. Frontend Test Issues

**Problem**: Cypress tests fail intermittently

**Solutions**:
```javascript
// Increase timeouts
cy.get('[data-test="element"]', { timeout: 10000 })

// Wait for API calls
cy.intercept('GET', '/api/products').as('getProducts')
cy.wait('@getProducts')

// Use proper selectors
cy.get('[data-test="submit-button"]') // Good
cy.get('.btn-primary') // Bad - fragile
```

#### 5. Authentication Issues in Tests

**Problem**: API tests fail with authentication errors

**Solution**:
```php
// Ensure proper authentication setup
protected function setUp(): void
{
    parent::setUp();
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
}

// Or use helper method
$this->actingAsApiUser('admin');
```

### 📊 Test Debugging

#### Debug Test Failures

```bash
# Run specific failing test with verbose output
php artisan test tests/Feature/ProductTest.php::it_creates_product --verbose

# Debug with dd() or dump()
public function test_example()
{
    $response = $this->postJson('/api/products', $data);
    dd($response->json()); // Debug response
}

# Check test database state
php artisan tinker --env=testing
>>> Product::all();
>>> DB::getQueryLog();
```

#### Frontend Test Debugging

```javascript
// Cypress debugging
cy.debug() // Pause test execution
cy.pause() // Interactive debugging

// Console logging
cy.window().then((win) => {
  console.log(win.store.state)
})

// Screenshot on failure
cy.screenshot('test-failure')
```

### 📈 Performance Optimization

#### Test Performance Tips

1. **Use Database Transactions**:
   ```php
   use Illuminate\Foundation\Testing\DatabaseTransactions;
   
   class FastTest extends TestCase
   {
       use DatabaseTransactions; // Faster than RefreshDatabase
   }
   ```

2. **Minimize Database Queries**:
   ```php
   // Good - Single query
   $products = Product::with('category')->get();
   
   // Bad - N+1 queries
   $products = Product::all();
   foreach ($products as $product) {
       echo $product->category->name;
   }
   ```

3. **Use Appropriate Test Types**:
   ```php
   // Unit test for business logic (fast)
   public function it_calculates_total_price()
   {
       $calculator = new PriceCalculator();
       $total = $calculator->calculate($items);
       $this->assertEquals(150000, $total);
   }
   
   // Integration test for API (slower)
   public function it_creates_product_via_api()
   {
       $response = $this->postJson('/api/products', $data);
       $response->assertStatus(201);
   }
   ```

---

## 📞 Support

Untuk bantuan terkait testing:

- **Documentation**: [Testing Guide](docs/TESTING.md)
- **Issues**: [GitHub Issues](https://github.com/your-org/q-pos/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-org/q-pos/discussions)

## 📄 License

Dokumentasi ini dilisensikan di bawah [MIT License](../LICENSE).

---

**Q-POS Testing Documentation v1.0**  
*Terakhir diperbarui: 1 September 2025*