# 🏪 Q-POS (Quick Point of Sale)

> Sistem Point of Sale modern berbasis web dengan arsitektur microservices untuk bisnis retail

[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![Quasar](https://img.shields.io/badge/Quasar-2.x-blue.svg)](https://quasar.dev)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## 📋 Daftar Isi

- [Overview](#overview)
- [Fitur Utama](#fitur-utama)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Teknologi](#teknologi)
- [Instalasi](#instalasi)
- [Quick Start](#quick-start)
- [Dokumentasi](#dokumentasi)
- [Status Pengembangan](#status-pengembangan)
- [Kontribusi](#kontribusi)
- [Lisensi](#lisensi)

## Overview

Q-POS adalah sistem Point of Sale enterprise yang dirancang untuk mendukung operasional bisnis retail modern. Sistem ini menggunakan arsitektur microservices dengan backend Laravel REST API dan frontend Quasar Vue.js SPA, menyediakan fitur-fitur canggih seperti multi-warehouse management, FIFO inventory tracking, multi-unit pricing, dan customer segmentation.

### 🎯 Target Pengguna
- Toko retail kecil hingga menengah
- Minimarket dan supermarket
- Distributor dan wholesaler
- Bisnis dengan multiple outlet
- Franchise dan chain store

### 🌟 Keunggulan
- **Modern Web-based**: Akses dari browser tanpa instalasi software
- **Responsive Design**: Optimal di desktop, tablet, dan mobile
- **Real-time Updates**: Sinkronisasi data real-time antar device
- **Multi-warehouse**: Kelola multiple gudang dan outlet
- **FIFO Inventory**: Sistem inventory First In First Out
- **Multi-pricing**: Harga berbeda untuk customer segment
- **Offline Capable**: Dapat beroperasi tanpa koneksi internet

## Fitur Utama

### 🛍️ Point of Sale
- Interface kasir yang intuitif dan cepat
- Barcode scanning dan product lookup
- Multi-payment method (Cash, Card, Transfer, E-wallet)
- Split payment dan partial payment
- Receipt printing dan email receipt
- Customer loyalty program

### 📦 Inventory Management
- **FIFO Stock Tracking**: First In First Out inventory
- **Multi-warehouse**: Kelola stock di multiple lokasi
- **Real-time Stock**: Update stock real-time
- **Stock Movement**: Audit trail perpindahan barang
- **Low Stock Alert**: Notifikasi stock minimum
- **Batch Tracking**: Tracking batch dan expiry date

### 💰 Multi-pricing System
- **Multi-unit**: Jual dalam berbagai satuan (Pcs, Dus, Kg)
- **Customer Segmentation**: Harga berbeda untuk retail/wholesale/distributor
- **Quantity Pricing**: Harga berdasarkan quantity minimum
- **Time-based Pricing**: Harga berdasarkan periode waktu
- **Promotional Pricing**: Sistem diskon dan promosi

### 👥 Customer Management
- Customer database dengan segmentasi
- Credit limit dan payment terms
- Purchase history dan analytics
- Loyalty points dan rewards
- Customer communication tools

### 📊 Reporting & Analytics
- Sales reports (daily, monthly, yearly)
- Inventory reports dan stock analysis
- Financial reports dan profit analysis
- Customer behavior analytics
- Performance dashboard dengan charts

### 🔐 User Management
- Role-based access control (RBAC)
- Multi-user dengan permission system
- User activity logging
- Secure authentication dengan Laravel Sanctum

## Arsitektur Sistem

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │    Backend      │    │    Database     │
│   (Quasar)      │◄──►│   (Laravel)     │◄──►│    (MySQL)      │
│                 │    │                 │    │                 │
│ • Vue.js 3      │    │ • REST API      │    │ • 23 Tables     │
│ • Quasar UI     │    │ • Sanctum Auth  │    │ • FIFO Views    │
│ • Pinia Store   │    │ • RBAC System   │    │ • Relationships │
│ • PWA Ready     │    │ • Queue Jobs    │    │ • Indexes       │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Komponen Utama

#### Backend (Laravel 11.x)
- **API Layer**: RESTful API dengan resource controllers
- **Authentication**: Laravel Sanctum token-based auth
- **Authorization**: Spatie Laravel Permission (RBAC)
- **Database**: MySQL dengan 23 tables dan proper relationships
- **Queue System**: Background job processing
- **Testing**: PHPUnit dengan feature dan unit tests

#### Frontend (Quasar Vue.js)
- **SPA Framework**: Quasar Framework dengan Vue 3
- **State Management**: Pinia untuk global state
- **HTTP Client**: Axios dengan interceptors
- **UI Components**: Material Design dengan Quasar components
- **PWA**: Progressive Web App capabilities
- **Internationalization**: Multi-language support

## Teknologi

### Backend Stack
- **Framework**: Laravel 11.x
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Laravel Permission
- **API**: RESTful API dengan JSON responses
- **Testing**: PHPUnit
- **Code Quality**: PSR-12, PHPStan

### Frontend Stack
- **Framework**: Quasar Framework 2.16.0
- **JavaScript**: Vue.js 3.5.20
- **State Management**: Pinia 3.0.1
- **HTTP Client**: Axios 1.2.1
- **Build Tool**: Vite
- **UI Library**: Quasar Material Design
- **Styling**: SCSS dengan Quasar variables

### Development Tools
- **Version Control**: Git
- **Code Editor**: VS Code dengan extensions
- **API Testing**: Postman/Insomnia
- **Database**: MySQL Workbench/phpMyAdmin
- **Package Manager**: Composer (PHP), npm/yarn (JS)

## Instalasi

### 📋 Prerequisites
- PHP 8.1+ dengan extensions (BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML)
- Composer 2.0+
- Node.js 18+ dan npm/yarn
- MySQL 8.0+
- Git

### 🚀 Instalasi Backend

```bash
# Clone repository
git clone <repository-url>
cd q-pos

# Install PHP dependencies
cd backend
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database di .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qpos_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations dan seeders
php artisan migrate
php artisan db:seed

# Start development server
php artisan serve --host=127.0.0.1 --port=8000
```

### 🎨 Instalasi Frontend

```bash
# Install frontend dependencies
cd frontend/web
npm install
# atau
yarn install

# Configure API endpoint di .env
VUE_APP_API_URL=http://127.0.0.1:8000/api/v1

# Start development server
npm run dev
# atau
yarn dev
```

## Quick Start

### 🔑 Demo Credentials

Setelah menjalankan seeders, gunakan akun demo berikut:

```
Administrator:
- Username: qpos_admin
- Password: admin123

Manager:
- Username: qpos_manager  
- Password: admin123

Cashier:
- Username: qpos_cashier
- Password: cashier123
```

### 🎯 Langkah Pertama

1. **Akses aplikasi** di `http://localhost:5173`
2. **Login** dengan salah satu demo account
3. **Setup master data**:
   - Categories: Makanan, Minuman, dll
   - Units: PCS, DUS, KG, dll
   - Suppliers: Data supplier
   - Customers: Data customer
4. **Input produk** dengan multi-unit dan multi-pricing
5. **Mulai transaksi** di POS interface

### 📱 Akses URL
- **Frontend**: http://localhost:5173
- **Backend API**: http://127.0.0.1:8000/api/v1
- **API Documentation**: http://127.0.0.1:8000/docs (coming soon)

## Dokumentasi

Dokumentasi lengkap tersedia di direktori `/docs`:

- [📐 Architecture](docs/ARCHITECTURE.md) - Arsitektur sistem dan komponen
- [🗄️ Database Schema](docs/DATABASE_SCHEMA.md) - Skema database dan relasi
- [🔌 API Documentation](docs/API_DOCUMENTATION.md) - Spesifikasi REST API
- [🧪 Testing Guide](docs/TESTING.md) - Panduan testing dan QA
- [🗺️ Roadmap](docs/ROADMAP.md) - Rencana pengembangan fitur
- [📝 User Stories](docs/USER_STORIES.md) - Functional requirements
- [🔒 Security Guide](docs/SECURITY.md) - Panduan keamanan sistem
- [⚡ Performance Guide](docs/PERFORMANCE.md) - Optimasi performa

## Status Pengembangan

### 📊 Progress Overview
- **Backend**: ~75% Complete
- **Frontend**: ~45% Complete
- **Overall**: ~60% Complete

### ✅ Completed Features
- ✅ Authentication & Authorization system
- ✅ Master data management (Categories, Units, Products)
- ✅ Multi-unit dan multi-pricing system
- ✅ Basic inventory management
- ✅ User interface dengan Quasar components
- ✅ API integration dengan error handling
- ✅ Database schema dengan FIFO support

### 🔄 In Progress
- 🔄 POS transaction interface
- 🔄 Customer dan supplier management
- 🔄 Inventory stock movements
- 🔄 Reporting dashboard

### ⏳ Planned Features
- ⏳ Purchase order management
- ⏳ Advanced reporting dan analytics
- ⏳ Mobile app (iOS/Android)
- ⏳ Multi-warehouse operations
- ⏳ Barcode scanning
- ⏳ Receipt printing
- ⏳ Offline mode

### 📅 Roadmap
Lihat [CHANGELOG.md](CHANGELOG.md) untuk history perubahan dan [docs/ROADMAP.md](docs/ROADMAP.md) untuk rencana pengembangan.

## Kontribusi

### 🤝 Cara Berkontribusi

1. Fork repository ini
2. Buat branch untuk fitur baru (`git checkout -b feature/amazing-feature`)
3. Commit perubahan (`git commit -m 'Add amazing feature'`)
4. Push ke branch (`git push origin feature/amazing-feature`)
5. Buat Pull Request

### 📋 Development Guidelines

- Ikuti PSR-12 coding standards untuk PHP
- Gunakan ESLint dan Prettier untuk JavaScript
- Tulis unit tests untuk fitur baru
- Update dokumentasi jika diperlukan
- Pastikan semua tests pass sebelum submit PR

### 🐛 Bug Reports

Laporkan bug melalui GitHub Issues dengan informasi:
- Versi aplikasi dan browser
- Langkah reproduksi bug
- Expected vs actual behavior
- Screenshot atau video jika diperlukan
- Log error dari browser console

### 💡 Feature Requests

Ajukan fitur baru melalui GitHub Issues dengan:
- Deskripsi fitur yang diinginkan
- Use case dan business value
- Mockup atau wireframe jika ada
- Prioritas dan timeline yang diharapkan

## Lisensi

Project ini menggunakan [MIT License](LICENSE). Silakan gunakan untuk keperluan komersial maupun non-komersial.

## Support & Contact

### 📞 Tim Pengembangan
- **Lead Developer**: Available untuk pertanyaan teknis
- **Project Manager**: Available untuk feature requests
- **QA Team**: Available untuk bug reports

### 🌐 Links
- **Repository**: [GitHub](https://github.com/your-username/q-pos)
- **Documentation**: [Docs](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-username/q-pos/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-username/q-pos/discussions)

---

**Developed with ❤️ for Indonesian Retail Business**

*Q-POS - Solusi POS Modern untuk Bisnis Masa Depan*