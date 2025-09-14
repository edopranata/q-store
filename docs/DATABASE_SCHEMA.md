# 🗄️ Database Schema Documentation

> Dokumentasi lengkap skema database Q-POS dengan relasi antar tabel dan contoh data

## 📋 Daftar Isi

- [Overview](#overview)
- [Database Design Principles](#database-design-principles)
- [Entity Relationship Diagram](#entity-relationship-diagram)
- [Table Structures](#table-structures)
- [Relationships](#relationships)
- [FIFO Implementation](#fifo-implementation)
- [Indexes and Performance](#indexes-and-performance)
- [Sample Data](#sample-data)
- [Migration Scripts](#migration-scripts)
- [Database Views](#database-views)
- [Stored Procedures](#stored-procedures)
- [Backup and Recovery](#backup-and-recovery)

## Overview

Database Q-POS menggunakan **MySQL 8.0+** dengan desain yang mendukung:
- ✅ **FIFO (First In, First Out)** inventory management
- ✅ **Multi-unit** product management
- ✅ **Multi-price** per product
- ✅ **Role-based access control**
- ✅ **Audit trail** untuk semua transaksi
- ✅ **Scalable design** untuk pertumbuhan bisnis

### 📊 Database Statistics

- **Total Tables**: 23 tables
- **Total Relationships**: 35+ foreign key constraints
- **Storage Engine**: InnoDB
- **Character Set**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Estimated Size**: ~50MB untuk 10,000 products, 100,000 transactions

## Database Design Principles

### 🎯 Design Goals

1. **Normalization**: Database dinormalisasi hingga 3NF untuk menghindari redundansi
2. **Performance**: Optimized indexes untuk query yang sering digunakan
3. **Scalability**: Desain yang mendukung pertumbuhan data
4. **Data Integrity**: Foreign key constraints dan validation rules
5. **Audit Trail**: Tracking semua perubahan data penting
6. **Flexibility**: Mendukung berbagai jenis bisnis retail

### 🔧 Technical Specifications

```sql
-- Database Configuration
CREATE DATABASE qpos_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Default Storage Engine
SET default_storage_engine = InnoDB;

-- Timezone Setting
SET time_zone = '+07:00'; -- WIB (Indonesia)
```

## Entity Relationship Diagram

### 🔗 High-Level ERD

```
┌─────────────────────────────────────────────────────────────────┐
│                        MASTER DATA                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │ categories  │    │    units    │    │ warehouses  │         │
│  │             │    │             │    │             │         │
│  │ id (PK)     │    │ id (PK)     │    │ id (PK)     │         │
│  │ name        │    │ name        │    │ name        │         │
│  │ description │    │ symbol      │    │ address     │         │
│  │ is_active   │    │ is_active   │    │ is_active   │         │
│  └─────────────┘    └─────────────┘    └─────────────┘         │
│         │                   │                   │              │
│         ▼                   ▼                   ▼              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │  products   │    │ conversion_ │    │  suppliers  │         │
│  │             │◀───│   units     │    │             │         │
│  │ id (PK)     │    │             │    │ id (PK)     │         │
│  │ name        │    │ id (PK)     │    │ name        │         │
│  │ category_id │────┤ product_id  │    │ contact     │         │
│  │ base_unit_id│────┤ from_unit_id│    │ address     │         │
│  │ description │    │ to_unit_id  │    │ is_active   │         │
│  │ is_active   │    │ factor      │    └─────────────┘         │
│  └─────────────┘    └─────────────┘                            │
│         │                                                       │
│         ▼                                                       │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │product_     │    │product_     │    │product_     │         │
│  │units        │    │prices       │    │barcodes     │         │
│  │             │    │             │    │             │         │
│  │ id (PK)     │    │ id (PK)     │    │ id (PK)     │         │
│  │ product_id  │────┤ product_id  │────┤ product_id  │         │
│  │ unit_id     │    │ unit_id     │    │ barcode     │         │
│  │ is_default  │    │ price       │    │ unit_id     │         │
│  └─────────────┘    │ is_default  │    │ is_active   │         │
│                     └─────────────┘    └─────────────┘         │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                     INVENTORY MANAGEMENT                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │inventory_   │    │stock_       │    │stock_       │         │
│  │stocks       │    │batches      │    │movements    │         │
│  │             │    │             │    │             │         │
│  │ id (PK)     │    │ id (PK)     │    │ id (PK)     │         │
│  │ product_id  │────┤ product_id  │────┤ product_id  │         │
│  │ warehouse_id│    │ warehouse_id│    │ warehouse_id│         │
│  │ unit_id     │    │ unit_id     │    │ unit_id     │         │
│  │ quantity    │    │ quantity    │    │ quantity    │         │
│  │ reserved    │    │ remaining   │    │ type        │         │
│  │ updated_at  │    │ received_at │    │ reference   │         │
│  └─────────────┘    │ expired_at  │    │ created_at  │         │
│                     │ batch_code  │    └─────────────┘         │
│                     │ cost_price  │                            │
│                     └─────────────┘                            │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      SALES MANAGEMENT                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐         │
│  │  customers  │    │sales_       │    │sales_       │         │
│  │             │    │transactions │    │transaction_ │         │
│  │ id (PK)     │    │             │    │items        │         │
│  │ name        │    │ id (PK)     │    │             │         │
│  │ phone       │    │ customer_id │────┤ id (PK)     │         │
│  │ email       │    │ user_id     │    │ transaction_│         │
│  │ address     │    │ total_amount│    │ id          │         │
│  │ loyalty_    │    │ paid_amount │    │ product_id  │────┐    │
│  │ points      │    │ change_     │    │ unit_id     │    │    │
│  │ is_active   │    │ amount      │    │ quantity    │    │    │
│  └─────────────┘    │ status      │    │ unit_price  │    │    │
│         │            │ created_at  │    │ total_price │    │    │
│         ▼            └─────────────┘    └─────────────┘    │    │
│  ┌─────────────┐            │                   │         │    │
│  │customer_    │            ▼                   ▼         │    │
│  │loyalty_     │    ┌─────────────┐    ┌─────────────┐    │    │
│  │points       │    │sales_       │    │item_stock_  │    │    │
│  │             │    │payments     │    │batches      │    │    │
│  │ id (PK)     │    │             │    │             │    │    │
│  │ customer_id │    │ id (PK)     │    │ id (PK)     │    │    │
│  │ points      │    │ transaction_│    │ item_id     │────┘    │
│  │ type        │    │ id          │    │ batch_id    │         │
│  │ description │    │ method      │    │ quantity    │         │
│  │ created_at  │    │ amount      │    │ cost_price  │         │
│  └─────────────┘    │ created_at  │    └─────────────┘         │
│                     └─────────────┘                            │
└─────────────────────────────────────────────────────────────────┘
```

## Table Structures

### 👥 User Management

#### users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    avatar VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_users_email (email),
    INDEX idx_users_active (is_active),
    INDEX idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### roles
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_role_guard (name, guard_name),
    INDEX idx_roles_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### permissions
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    description TEXT NULL,
    module VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_permission_guard (name, guard_name),
    INDEX idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 🏪 Master Data

#### categories
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_categories_parent (parent_id),
    INDEX idx_categories_active (is_active),
    INDEX idx_categories_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### units
```sql
CREATE TABLE units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    symbol VARCHAR(10) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_unit_symbol (symbol),
    INDEX idx_units_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### warehouses
```sql
CREATE TABLE warehouses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    address TEXT NULL,
    phone VARCHAR(20) NULL,
    manager_name VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_warehouses_code (code),
    INDEX idx_warehouses_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 📦 Product Management

#### products
```sql
CREATE TABLE products (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    base_unit_id BIGINT UNSIGNED NOT NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    min_stock DECIMAL(15,4) DEFAULT 0,
    max_stock DECIMAL(15,4) DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (base_unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    INDEX idx_products_category (category_id),
    INDEX idx_products_code (code),
    INDEX idx_products_active (is_active),
    INDEX idx_products_name (name),
    FULLTEXT idx_products_search (name, code, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### product_units
```sql
CREATE TABLE product_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_product_unit (product_id, unit_id),
    INDEX idx_product_units_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### conversion_units
```sql
CREATE TABLE conversion_units (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    from_unit_id BIGINT UNSIGNED NOT NULL,
    to_unit_id BIGINT UNSIGNED NOT NULL,
    factor DECIMAL(15,6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (from_unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    FOREIGN KEY (to_unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_conversion (product_id, from_unit_id, to_unit_id),
    INDEX idx_conversion_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### product_prices
```sql
CREATE TABLE product_prices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    price_type ENUM('retail', 'wholesale', 'member') DEFAULT 'retail',
    price DECIMAL(15,2) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    effective_from DATE NULL,
    effective_to DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    INDEX idx_product_prices_product (product_id),
    INDEX idx_product_prices_type (price_type),
    INDEX idx_product_prices_effective (effective_from, effective_to),
    INDEX idx_product_prices_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### product_barcodes
```sql
CREATE TABLE product_barcodes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    barcode VARCHAR(255) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    INDEX idx_product_barcodes_product (product_id),
    INDEX idx_product_barcodes_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 📊 Inventory Management

#### inventory_stocks
```sql
CREATE TABLE inventory_stocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(15,4) DEFAULT 0,
    reserved_quantity DECIMAL(15,4) DEFAULT 0,
    last_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    UNIQUE KEY unique_inventory (product_id, warehouse_id, unit_id),
    INDEX idx_inventory_product (product_id),
    INDEX idx_inventory_warehouse (warehouse_id),
    INDEX idx_inventory_updated (last_updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### stock_batches
```sql
CREATE TABLE stock_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    batch_code VARCHAR(100) NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    remaining_quantity DECIMAL(15,4) NOT NULL,
    cost_price DECIMAL(15,2) NOT NULL,
    received_at TIMESTAMP NOT NULL,
    expired_at DATE NULL,
    supplier_id BIGINT UNSIGNED NULL,
    reference_type ENUM('purchase', 'adjustment', 'transfer') NOT NULL,
    reference_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    INDEX idx_stock_batches_product (product_id),
    INDEX idx_stock_batches_warehouse (warehouse_id),
    INDEX idx_stock_batches_received (received_at),
    INDEX idx_stock_batches_expired (expired_at),
    INDEX idx_stock_batches_remaining (remaining_quantity),
    INDEX idx_stock_batches_fifo (product_id, warehouse_id, received_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### stock_movements
```sql
CREATE TABLE stock_movements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    movement_type ENUM('in', 'out') NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    reference_type ENUM('purchase', 'sale', 'adjustment', 'transfer') NOT NULL,
    reference_id BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_stock_movements_product (product_id),
    INDEX idx_stock_movements_warehouse (warehouse_id),
    INDEX idx_stock_movements_type (movement_type),
    INDEX idx_stock_movements_reference (reference_type, reference_id),
    INDEX idx_stock_movements_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 👥 Customer Management

#### customers
```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    postal_code VARCHAR(10) NULL,
    loyalty_points INT DEFAULT 0,
    customer_type ENUM('regular', 'member', 'vip') DEFAULT 'regular',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_customers_code (code),
    INDEX idx_customers_phone (phone),
    INDEX idx_customers_email (email),
    INDEX idx_customers_type (customer_type),
    INDEX idx_customers_active (is_active),
    FULLTEXT idx_customers_search (name, phone, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### customer_loyalty_points
```sql
CREATE TABLE customer_loyalty_points (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    points INT NOT NULL,
    type ENUM('earned', 'redeemed', 'expired', 'adjustment') NOT NULL,
    description TEXT NULL,
    reference_type ENUM('sale', 'redemption', 'manual') NULL,
    reference_id BIGINT UNSIGNED NULL,
    expired_at DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_loyalty_customer (customer_id),
    INDEX idx_loyalty_type (type),
    INDEX idx_loyalty_expired (expired_at),
    INDEX idx_loyalty_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 🛒 Sales Management

#### sales_transactions
```sql
CREATE TABLE sales_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_number VARCHAR(100) UNIQUE NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    warehouse_id BIGINT UNSIGNED NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    change_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    status ENUM('pending', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT,
    INDEX idx_sales_customer (customer_id),
    INDEX idx_sales_user (user_id),
    INDEX idx_sales_warehouse (warehouse_id),
    INDEX idx_sales_status (status),
    INDEX idx_sales_created (created_at),
    INDEX idx_sales_amount (total_amount)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### sales_transaction_items
```sql
CREATE TABLE sales_transaction_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    unit_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_price DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaction_id) REFERENCES sales_transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT,
    INDEX idx_sales_items_transaction (transaction_id),
    INDEX idx_sales_items_product (product_id),
    INDEX idx_sales_items_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### item_stock_batches
```sql
CREATE TABLE item_stock_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id BIGINT UNSIGNED NOT NULL,
    batch_id BIGINT UNSIGNED NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    cost_price DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (item_id) REFERENCES sales_transaction_items(id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES stock_batches(id) ON DELETE RESTRICT,
    INDEX idx_item_batches_item (item_id),
    INDEX idx_item_batches_batch (batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### sales_payments
```sql
CREATE TABLE sales_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    transaction_id BIGINT UNSIGNED NOT NULL,
    payment_method ENUM('cash', 'card', 'transfer', 'ewallet', 'credit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    reference_number VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (transaction_id) REFERENCES sales_transactions(id) ON DELETE CASCADE,
    INDEX idx_sales_payments_transaction (transaction_id),
    INDEX idx_sales_payments_method (payment_method),
    INDEX idx_sales_payments_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 🏭 Supplier Management

#### suppliers
```sql
CREATE TABLE suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE NULL,
    contact_person VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    postal_code VARCHAR(10) NULL,
    tax_number VARCHAR(50) NULL,
    payment_terms INT DEFAULT 0, -- days
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_suppliers_code (code),
    INDEX idx_suppliers_active (is_active),
    FULLTEXT idx_suppliers_search (name, contact_person, phone, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Relationships

### 🔗 Foreign Key Relationships

```sql
-- User Management
ALTER TABLE model_has_permissions ADD FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE;
ALTER TABLE model_has_roles ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE;
ALTER TABLE role_has_permissions ADD FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE;
ALTER TABLE role_has_permissions ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE;

-- Product Management
ALTER TABLE products ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT;
ALTER TABLE products ADD FOREIGN KEY (base_unit_id) REFERENCES units(id) ON DELETE RESTRICT;
ALTER TABLE product_units ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;
ALTER TABLE product_units ADD FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE RESTRICT;
ALTER TABLE conversion_units ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;
ALTER TABLE product_prices ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;
ALTER TABLE product_barcodes ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- Inventory Management
ALTER TABLE inventory_stocks ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;
ALTER TABLE inventory_stocks ADD FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE RESTRICT;
ALTER TABLE stock_batches ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;
ALTER TABLE stock_movements ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE;

-- Sales Management
ALTER TABLE sales_transactions ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL;
ALTER TABLE sales_transactions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT;
ALTER TABLE sales_transaction_items ADD FOREIGN KEY (transaction_id) REFERENCES sales_transactions(id) ON DELETE CASCADE;
ALTER TABLE sales_transaction_items ADD FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT;
ALTER TABLE item_stock_batches ADD FOREIGN KEY (item_id) REFERENCES sales_transaction_items(id) ON DELETE CASCADE;
ALTER TABLE item_stock_batches ADD FOREIGN KEY (batch_id) REFERENCES stock_batches(id) ON DELETE RESTRICT;
```

### 📊 Relationship Cardinality

| Parent Table | Child Table | Relationship | Cardinality |
|--------------|-------------|--------------|-------------|
| categories | categories | Self-referencing | 1:N |
| categories | products | One-to-Many | 1:N |
| units | products | One-to-Many | 1:N |
| products | product_units | One-to-Many | 1:N |
| products | product_prices | One-to-Many | 1:N |
| products | product_barcodes | One-to-Many | 1:N |
| products | inventory_stocks | One-to-Many | 1:N |
| products | stock_batches | One-to-Many | 1:N |
| warehouses | inventory_stocks | One-to-Many | 1:N |
| customers | sales_transactions | One-to-Many | 1:N |
| users | sales_transactions | One-to-Many | 1:N |
| sales_transactions | sales_transaction_items | One-to-Many | 1:N |
| sales_transactions | sales_payments | One-to-Many | 1:N |
| suppliers | stock_batches | One-to-Many | 1:N |

## FIFO Implementation

### 🔄 FIFO Logic Flow

```sql
-- FIFO View untuk mendapatkan stock berdasarkan urutan masuk
CREATE VIEW fifo_stock_view AS
SELECT 
    sb.id as batch_id,
    sb.product_id,
    sb.warehouse_id,
    sb.unit_id,
    sb.remaining_quantity,
    sb.cost_price,
    sb.received_at,
    sb.expired_at,
    ROW_NUMBER() OVER (
        PARTITION BY sb.product_id, sb.warehouse_id, sb.unit_id 
        ORDER BY sb.received_at ASC, sb.id ASC
    ) as fifo_order
FROM stock_batches sb
WHERE sb.remaining_quantity > 0
ORDER BY sb.product_id, sb.warehouse_id, sb.unit_id, sb.received_at ASC;
```

### 📝 FIFO Stored Procedure

```sql
DELIMITER //

CREATE PROCEDURE ProcessFIFOSale(
    IN p_product_id BIGINT,
    IN p_warehouse_id BIGINT,
    IN p_unit_id BIGINT,
    IN p_quantity DECIMAL(15,4),
    IN p_item_id BIGINT
)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_batch_id BIGINT;
    DECLARE v_available_qty DECIMAL(15,4);
    DECLARE v_cost_price DECIMAL(15,2);
    DECLARE v_use_qty DECIMAL(15,4);
    DECLARE remaining_qty DECIMAL(15,4) DEFAULT p_quantity;
    
    DECLARE batch_cursor CURSOR FOR
        SELECT batch_id, remaining_quantity, cost_price
        FROM fifo_stock_view
        WHERE product_id = p_product_id 
          AND warehouse_id = p_warehouse_id 
          AND unit_id = p_unit_id
          AND remaining_quantity > 0
        ORDER BY fifo_order;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN batch_cursor;
    
    batch_loop: LOOP
        FETCH batch_cursor INTO v_batch_id, v_available_qty, v_cost_price;
        
        IF done OR remaining_qty <= 0 THEN
            LEAVE batch_loop;
        END IF;
        
        -- Tentukan quantity yang akan digunakan dari batch ini
        SET v_use_qty = LEAST(remaining_qty, v_available_qty);
        
        -- Update stock batch
        UPDATE stock_batches 
        SET remaining_quantity = remaining_quantity - v_use_qty,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = v_batch_id;
        
        -- Insert ke item_stock_batches untuk tracking
        INSERT INTO item_stock_batches (item_id, batch_id, quantity, cost_price, created_at)
        VALUES (p_item_id, v_batch_id, v_use_qty, v_cost_price, CURRENT_TIMESTAMP);
        
        -- Kurangi remaining quantity
        SET remaining_qty = remaining_qty - v_use_qty;
        
    END LOOP;
    
    CLOSE batch_cursor;
    
    -- Jika masih ada sisa quantity yang tidak bisa dipenuhi
    IF remaining_qty > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Insufficient stock for FIFO processing';
    END IF;
    
END //

DELIMITER ;
```

### 🔍 FIFO Query Examples

```sql
-- Cek available stock dengan FIFO
SELECT 
    p.name as product_name,
    u.symbol as unit,
    SUM(fsv.remaining_quantity) as total_available,
    COUNT(fsv.batch_id) as batch_count,
    MIN(fsv.received_at) as oldest_batch,
    MAX(fsv.received_at) as newest_batch
FROM fifo_stock_view fsv
JOIN products p ON fsv.product_id = p.id
JOIN units u ON fsv.unit_id = u.id
WHERE fsv.warehouse_id = 1
GROUP BY fsv.product_id, fsv.unit_id
ORDER BY p.name;

-- Cek batch yang akan expired dalam 30 hari
SELECT 
    p.name as product_name,
    sb.batch_code,
    sb.remaining_quantity,
    sb.expired_at,
    DATEDIFF(sb.expired_at, CURDATE()) as days_to_expire
FROM stock_batches sb
JOIN products p ON sb.product_id = p.id
WHERE sb.expired_at IS NOT NULL
  AND sb.expired_at <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
  AND sb.remaining_quantity > 0
ORDER BY sb.expired_at ASC;
```

## Indexes and Performance

### 🚀 Performance Optimization

#### Critical Indexes
```sql
-- Product search optimization
CREATE INDEX idx_products_search_composite ON products(is_active, category_id, name);
CREATE FULLTEXT INDEX idx_products_fulltext ON products(name, code, description);

-- Inventory performance
CREATE INDEX idx_inventory_composite ON inventory_stocks(warehouse_id, product_id, quantity);
CREATE INDEX idx_stock_batches_fifo_composite ON stock_batches(product_id, warehouse_id, received_at, remaining_quantity);

-- Sales performance
CREATE INDEX idx_sales_date_range ON sales_transactions(created_at, status, total_amount);
CREATE INDEX idx_sales_items_composite ON sales_transaction_items(transaction_id, product_id, created_at);

-- Customer search
CREATE INDEX idx_customers_search_composite ON customers(is_active, customer_type, name);
```

#### Query Optimization Tips
```sql
-- Gunakan covering index untuk query yang sering digunakan
CREATE INDEX idx_product_price_covering ON product_prices(product_id, unit_id, price_type, price, is_default);

-- Partitioning untuk tabel besar (sales_transactions)
ALTER TABLE sales_transactions 
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

## Sample Data

### 🎯 Master Data Examples

#### Categories
```sql
INSERT INTO categories (name, description, is_active) VALUES
('Makanan & Minuman', 'Kategori untuk produk makanan dan minuman', TRUE),
('Elektronik', 'Kategori untuk produk elektronik', TRUE),
('Pakaian', 'Kategori untuk produk pakaian dan fashion', TRUE),
('Kesehatan & Kecantikan', 'Kategori untuk produk kesehatan dan kecantikan', TRUE),
('Rumah Tangga', 'Kategori untuk produk rumah tangga', TRUE);
```

#### Units
```sql
INSERT INTO units (name, symbol, is_active) VALUES
('Piece', 'pcs', TRUE),
('Kilogram', 'kg', TRUE),
('Gram', 'gr', TRUE),
('Liter', 'ltr', TRUE),
('Mililiter', 'ml', TRUE),
('Meter', 'm', TRUE),
('Centimeter', 'cm', TRUE),
('Box', 'box', TRUE),
('Pack', 'pack', TRUE),
('Dozen', 'dzn', TRUE);
```

#### Warehouses
```sql
INSERT INTO warehouses (name, code, address, is_active) VALUES
('Gudang Utama', 'WH001', 'Jl. Industri No. 123, Jakarta', TRUE),
('Gudang Cabang Surabaya', 'WH002', 'Jl. Raya Surabaya No. 456, Surabaya', TRUE),
('Toko Retail Jakarta', 'RT001', 'Jl. Thamrin No. 789, Jakarta', TRUE);
```

### 📦 Product Examples

```sql
-- Insert sample products
INSERT INTO products (name, code, category_id, base_unit_id, description, min_stock, max_stock, is_active) VALUES
('Beras Premium 5kg', 'BRS001', 1, 8, 'Beras premium kualitas terbaik kemasan 5kg', 10, 100, TRUE),
('Minyak Goreng 1L', 'MYK001', 1, 4, 'Minyak goreng kemasan 1 liter', 20, 200, TRUE),
('Smartphone Android', 'SPH001', 2, 1, 'Smartphone Android RAM 4GB Storage 64GB', 5, 50, TRUE),
('Kaos Polo Pria', 'KPS001', 3, 1, 'Kaos polo pria berbahan cotton combed', 15, 150, TRUE);

-- Insert product units
INSERT INTO product_units (product_id, unit_id, is_default) VALUES
(1, 8, TRUE),  -- Beras - Box (default)
(1, 2, FALSE), -- Beras - Kg
(2, 4, TRUE),  -- Minyak - Liter (default)
(2, 5, FALSE), -- Minyak - ML
(3, 1, TRUE),  -- Smartphone - Pcs (default)
(4, 1, TRUE);  -- Kaos - Pcs (default)

-- Insert conversion units
INSERT INTO conversion_units (product_id, from_unit_id, to_unit_id, factor) VALUES
(1, 8, 2, 5.000000),  -- 1 Box Beras = 5 Kg
(2, 4, 5, 1000.000000); -- 1 Liter = 1000 ML

-- Insert product prices
INSERT INTO product_prices (product_id, unit_id, price_type, price, is_default) VALUES
(1, 8, 'retail', 75000.00, TRUE),     -- Beras per box - retail
(1, 8, 'wholesale', 70000.00, FALSE), -- Beras per box - wholesale
(1, 2, 'retail', 15000.00, FALSE),    -- Beras per kg - retail
(2, 4, 'retail', 25000.00, TRUE),     -- Minyak per liter - retail
(2, 5, 'retail', 25.00, FALSE),       -- Minyak per ml - retail
(3, 1, 'retail', 2500000.00, TRUE),   -- Smartphone - retail
(4, 1, 'retail', 150000.00, TRUE);    -- Kaos - retail

-- Insert product barcodes
INSERT INTO product_barcodes (product_id, unit_id, barcode, is_active) VALUES
(1, 8, '8991234567890', TRUE),  -- Beras box
(1, 2, '8991234567891', TRUE),  -- Beras kg
(2, 4, '8991234567892', TRUE),  -- Minyak liter
(3, 1, '8991234567893', TRUE),  -- Smartphone
(4, 1, '8991234567894', TRUE);  -- Kaos
```

### 👥 Customer Examples

```sql
INSERT INTO customers (name, code, phone, email, address, customer_type, loyalty_points, is_active) VALUES
('John Doe', 'CUST001', '081234567890', 'john@email.com', 'Jl. Sudirman No. 123, Jakarta', 'member', 150, TRUE),
('Jane Smith', 'CUST002', '081234567891', 'jane@email.com', 'Jl. Thamrin No. 456, Jakarta', 'vip', 500, TRUE),
('Bob Wilson', 'CUST003', '081234567892', 'bob@email.com', 'Jl. Gatot Subroto No. 789, Jakarta', 'regular', 0, TRUE),
('Alice Brown', 'CUST004', '081234567893', 'alice@email.com', 'Jl. Kuningan No. 321, Jakarta', 'member', 75, TRUE);
```

### 📊 Stock Examples

```sql
-- Insert initial stock
INSERT INTO inventory_stocks (product_id, warehouse_id, unit_id, quantity, reserved_quantity) VALUES
(1, 1, 8, 50.0000, 0.0000),  -- Beras 50 box di gudang utama
(1, 1, 2, 250.0000, 0.0000), -- Beras 250 kg di gudang utama
(2, 1, 4, 100.0000, 0.0000), -- Minyak 100 liter di gudang utama
(3, 1, 1, 25.0000, 0.0000),  -- Smartphone 25 pcs di gudang utama
(4, 1, 1, 75.0000, 0.0000);  -- Kaos 75 pcs di gudang utama

-- Insert stock batches for FIFO
INSERT INTO stock_batches (product_id, warehouse_id, unit_id, batch_code, quantity, remaining_quantity, cost_price, received_at, reference_type) VALUES
(1, 1, 8, 'BATCH001', 30.0000, 30.0000, 65000.00, '2025-09-01 08:00:00', 'purchase'),
(1, 1, 8, 'BATCH002', 20.0000, 20.0000, 67000.00, '2025-09-15 10:00:00', 'purchase'),
(2, 1, 4, 'BATCH003', 60.0000, 60.0000, 22000.00, '2025-09-01 09:00:00', 'purchase'),
(2, 1, 4, 'BATCH004', 40.0000, 40.0000, 23000.00, '2025-09-10 11:00:00', 'purchase'),
(3, 1, 1, 'BATCH005', 15.0000, 15.0000, 2200000.00, '2025-09-05 14:00:00', 'purchase'),
(3, 1, 1, 'BATCH006', 10.0000, 10.0000, 2300000.00, '2025-09-20 16:00:00', 'purchase');
```

### 🛒 Sales Transaction Example

```sql
-- Insert sample sales transaction
INSERT INTO sales_transactions (transaction_number, customer_id, user_id, warehouse_id, subtotal, tax_amount, discount_amount, total_amount, paid_amount, change_amount, status) VALUES
('TRX20250901001', 1, 1, 1, 190000.00, 19000.00, 5000.00, 204000.00, 210000.00, 6000.00, 'completed');

-- Insert transaction items
INSERT INTO sales_transaction_items (transaction_id, product_id, unit_id, quantity, unit_price, discount_amount, total_price) VALUES
(1, 1, 8, 2.0000, 75000.00, 0.00, 150000.00),  -- 2 box beras
(1, 2, 4, 1.0000, 25000.00, 0.00, 25000.00),   -- 1 liter minyak
(1, 4, 1, 1.0000, 15000.00, 0.00, 15000.00);   -- 1 kaos

-- Insert FIFO batch tracking
INSERT INTO item_stock_batches (item_id, batch_id, quantity, cost_price) VALUES
(1, 1, 2.0000, 65000.00),  -- 2 box beras dari batch pertama
(2, 3, 1.0000, 22000.00),  -- 1 liter minyak dari batch pertama
(3, 7, 1.0000, 12000.00);  -- 1 kaos (batch baru untuk contoh)

-- Insert payment
INSERT INTO sales_payments (transaction_id, payment_method, amount, reference_number) VALUES
(1, 'cash', 210000.00, NULL);
```

## Database Views

### 📊 Useful Views

#### Product Stock Summary View
```sql
CREATE VIEW product_stock_summary AS
SELECT 
    p.id as product_id,
    p.name as product_name,
    p.code as product_code,
    c.name as category_name,
    w.name as warehouse_name,
    u.symbol as unit_symbol,
    COALESCE(ist.quantity, 0) as current_stock,
    COALESCE(ist.reserved_quantity, 0) as reserved_stock,
    COALESCE(ist.quantity, 0) - COALESCE(ist.reserved_quantity, 0) as available_stock,
    p.min_stock,
    p.max_stock,
    CASE 
        WHEN COALESCE(ist.quantity, 0) <= p.min_stock THEN 'Low Stock'
        WHEN COALESCE(ist.quantity, 0) >= p.max_stock THEN 'Overstock'
        ELSE 'Normal'
    END as stock_status
FROM products p
CROSS JOIN warehouses w
JOIN categories c ON p.category_id = c.id
JOIN units u ON p.base_unit_id = u.id
LEFT JOIN inventory_stocks ist ON p.id = ist.product_id AND w.id = ist.warehouse_id AND u.id = ist.unit_id
WHERE p.is_active = TRUE AND w.is_active = TRUE;
```

#### Sales Summary View
```sql
CREATE VIEW sales_summary AS
SELECT 
    DATE(st.created_at) as sale_date,
    COUNT(st.id) as transaction_count,
    SUM(st.total_amount) as total_sales,
    SUM(st.tax_amount) as total_tax,
    SUM(st.discount_amount) as total_discount,
    AVG(st.total_amount) as average_transaction,
    w.name as warehouse_name,
    u.name as cashier_name
FROM sales_transactions st
JOIN warehouses w ON st.warehouse_id = w.id
JOIN users u ON st.user_id = u.id
WHERE st.status = 'completed'
GROUP BY DATE(st.created_at), st.warehouse_id, st.user_id;
```

#### Product Performance View
```sql
CREATE VIEW product_performance AS
SELECT 
    p.id as product_id,
    p.name as product_name,
    p.code as product_code,
    c.name as category_name,
    COUNT(sti.id) as times_sold,
    SUM(sti.quantity) as total_quantity_sold,
    SUM(sti.total_price) as total_revenue,
    AVG(sti.unit_price) as average_selling_price,
    MAX(st.created_at) as last_sold_date
FROM products p
JOIN categories c ON p.category_id = c.id
LEFT JOIN sales_transaction_items sti ON p.id = sti.product_id
LEFT JOIN sales_transactions st ON sti.transaction_id = st.id AND st.status = 'completed'
GROUP BY p.id, p.name, p.code, c.name;
```

## Stored Procedures

### 🔧 Utility Procedures

#### Update Inventory Stock
```sql
DELIMITER //

CREATE PROCEDURE UpdateInventoryStock(
    IN p_product_id BIGINT,
    IN p_warehouse_id BIGINT,
    IN p_unit_id BIGINT
)
BEGIN
    DECLARE total_stock DECIMAL(15,4) DEFAULT 0;
    
    -- Calculate total stock from batches
    SELECT COALESCE(SUM(remaining_quantity), 0)
    INTO total_stock
    FROM stock_batches
    WHERE product_id = p_product_id
      AND warehouse_id = p_warehouse_id
      AND unit_id = p_unit_id;
    
    -- Update or insert inventory stock
    INSERT INTO inventory_stocks (product_id, warehouse_id, unit_id, quantity, last_updated_at)
    VALUES (p_product_id, p_warehouse_id, p_unit_id, total_stock, CURRENT_TIMESTAMP)
    ON DUPLICATE KEY UPDATE
        quantity = total_stock,
        last_updated_at = CURRENT_TIMESTAMP;
        
END //

DELIMITER ;
```

#### Generate Transaction Number
```sql
DELIMITER //

CREATE FUNCTION GenerateTransactionNumber(p_prefix VARCHAR(10))
RETURNS VARCHAR(100)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_date VARCHAR(8);
    DECLARE v_sequence INT DEFAULT 1;
    DECLARE v_number VARCHAR(100);
    
    -- Get current date in YYYYMMDD format
    SET v_date = DATE_FORMAT(CURDATE(), '%Y%m%d');
    
    -- Get next sequence number for today
    SELECT COALESCE(MAX(CAST(SUBSTRING(transaction_number, -3) AS UNSIGNED)), 0) + 1
    INTO v_sequence
    FROM sales_transactions
    WHERE transaction_number LIKE CONCAT(p_prefix, v_date, '%');
    
    -- Generate transaction number
    SET v_number = CONCAT(p_prefix, v_date, LPAD(v_sequence, 3, '0'));
    
    RETURN v_number;
END //

DELIMITER ;
```

## Migration Scripts

### 🚀 Database Setup

#### Initial Migration
```sql
-- Create database
CREATE DATABASE IF NOT EXISTS qpos_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE qpos_db;

-- Set timezone
SET time_zone = '+07:00';

-- Create all tables in correct order
SOURCE migrations/001_create_users_table.sql;
SOURCE migrations/002_create_roles_permissions.sql;
SOURCE migrations/003_create_categories_table.sql;
SOURCE migrations/004_create_units_table.sql;
SOURCE migrations/005_create_warehouses_table.sql;
SOURCE migrations/006_create_suppliers_table.sql;
SOURCE migrations/007_create_customers_table.sql;
SOURCE migrations/008_create_products_table.sql;
SOURCE migrations/009_create_inventory_tables.sql;
SOURCE migrations/010_create_sales_tables.sql;
SOURCE migrations/011_create_indexes.sql;
SOURCE migrations/012_create_views.sql;
SOURCE migrations/013_create_procedures.sql;
SOURCE migrations/014_insert_sample_data.sql;
```

#### Version Control
```sql
-- Database version tracking
CREATE TABLE schema_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(20) NOT NULL,
    description TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_version (version)
);

-- Insert initial version
INSERT INTO schema_versions (version, description) 
VALUES ('1.0.0', 'Initial database schema');
```

## Backup and Recovery

### 💾 Backup Strategy

#### Daily Backup Script
```bash
#!/bin/bash
# daily_backup.sh

DB_NAME="qpos_db"
DB_USER="qpos_user"
DB_PASS="your_password"
BACKUP_DIR="/var/backups/qpos"
DATE=$(date +"%Y%m%d_%H%M%S")

# Create backup directory
mkdir -p $BACKUP_DIR

# Full backup
mysqldump -u$DB_USER -p$DB_PASS \
  --single-transaction \
  --routines \
  --triggers \
  --events \
  $DB_NAME > $BACKUP_DIR/qpos_full_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/qpos_full_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "qpos_full_*.sql.gz" -mtime +30 -delete

echo "Backup completed: qpos_full_$DATE.sql.gz"
```

#### Recovery Script
```bash
#!/bin/bash
# restore_backup.sh

if [ $# -ne 1 ]; then
    echo "Usage: $0 <backup_file>"
    exit 1
fi

BACKUP_FILE=$1
DB_NAME="qpos_db"
DB_USER="qpos_user"
DB_PASS="your_password"

# Decompress if needed
if [[ $BACKUP_FILE == *.gz ]]; then
    gunzip -c $BACKUP_FILE | mysql -u$DB_USER -p$DB_PASS $DB_NAME
else
    mysql -u$DB_USER -p$DB_PASS $DB_NAME < $BACKUP_FILE
fi

echo "Database restored from: $BACKUP_FILE"
```

### 🔄 Point-in-Time Recovery

```sql
-- Enable binary logging in my.cnf
[mysqld]
log-bin=mysql-bin
binlog-format=ROW
expire_logs_days=7

-- Create recovery point
FLUSH LOGS;
SHOW MASTER STATUS;
```

## Performance Monitoring

### 📊 Key Metrics

#### Database Size Monitoring
```sql
-- Monitor table sizes
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    table_rows AS 'Row Count'
FROM information_schema.tables 
WHERE table_schema = 'qpos_db'
ORDER BY (data_length + index_length) DESC;

-- Monitor index usage
SELECT 
    s.table_name,
    s.index_name,
    s.cardinality,
    s.sub_part,
    s.packed,
    s.nullable,
    s.index_type
FROM information_schema.statistics s
WHERE s.table_schema = 'qpos_db'
ORDER BY s.table_name, s.seq_in_index;
```

#### Query Performance
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
SET GLOBAL log_queries_not_using_indexes = 'ON';

-- Monitor expensive queries
SELECT 
    query_time,
    lock_time,
    rows_sent,
    rows_examined,
    sql_text
FROM mysql.slow_log
ORDER BY query_time DESC
LIMIT 10;
```

## Security Considerations

### 🔒 Database Security

#### User Privileges
```sql
-- Create application user with minimal privileges
CREATE USER 'qpos_app'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON qpos_db.* TO 'qpos_app'@'localhost';
GRANT EXECUTE ON qpos_db.* TO 'qpos_app'@'localhost';

-- Create read-only user for reporting
CREATE USER 'qpos_readonly'@'localhost' IDENTIFIED BY 'readonly_password';
GRANT SELECT ON qpos_db.* TO 'qpos_readonly'@'localhost';

-- Create backup user
CREATE USER 'qpos_backup'@'localhost' IDENTIFIED BY 'backup_password';
GRANT SELECT, LOCK TABLES, SHOW VIEW, EVENT, TRIGGER ON qpos_db.* TO 'qpos_backup'@'localhost';
```

#### Data Encryption
```sql
-- Enable encryption at rest
ALTER TABLE users ENCRYPTION='Y';
ALTER TABLE customers ENCRYPTION='Y';
ALTER TABLE sales_transactions ENCRYPTION='Y';

-- Encrypt sensitive columns
ALTER TABLE users ADD COLUMN encrypted_phone VARBINARY(255);
ALTER TABLE customers ADD COLUMN encrypted_email VARBINARY(255);
```

---

## 📚 Additional Resources

### Documentation Links
- [MySQL 8.0 Reference Manual](https://dev.mysql.com/doc/refman/8.0/en/)
- [Laravel Database Documentation](https://laravel.com/docs/database)
- [Database Design Best Practices](https://www.mysql.com/why-mysql/white-papers/)

### Tools Recommendations
- **MySQL Workbench**: Visual database design and administration
- **phpMyAdmin**: Web-based MySQL administration
- **Percona Toolkit**: Advanced MySQL performance tools
- **pt-query-digest**: Query performance analysis

---

> 📝 **Note**: Dokumentasi ini akan terus diperbarui seiring dengan perkembangan sistem Q-POS. Pastikan untuk selalu menggunakan versi terbaru dari dokumentasi ini.

> ⚠️ **Warning**: Selalu lakukan backup sebelum melakukan perubahan pada struktur database production.

**Last Updated**: September 2025  
**Version**: 1.0.0  
**Maintainer**: Q-POS Development Team