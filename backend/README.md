# 🏪 Q-POS Backend API

> Backend API untuk sistem Point of Sales (Q-POS) yang dibangun dengan Laravel

## 📋 Deskripsi

Q-POS Backend adalah REST API yang menyediakan layanan untuk sistem Point of Sales, termasuk manajemen produk, penjualan, inventory, customer, dan laporan bisnis.

## 🚀 Quick Start

### Prerequisites

- PHP >= 8.1
- Composer
- MySQL/PostgreSQL
- Redis (optional, untuk caching)

### Installation

1. **Clone repository dan masuk ke direktori backend**
   ```bash
   cd backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database di file `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=q_pos
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Jalankan migrasi dan seeder**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start development server**
   ```bash
   php artisan serve
   ```

   API akan tersedia di: `http://localhost:8000`

## 🔧 Konfigurasi

### Environment Variables

Konfigurasi penting yang perlu diatur di file `.env`:

```env
# Application
APP_NAME="Q-POS API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=q_pos
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### Permissions Setup

Sistem menggunakan Spatie Laravel Permission untuk role dan permission management:

```bash
# Publish permission migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run permission migrations
php artisan migrate

# Seed roles and permissions
php artisan db:seed --class=RolePermissionSeeder
```

## 📚 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication
API menggunakan Laravel Sanctum untuk autentikasi:

```bash
# Login
POST /api/auth/login
{
  "email": "admin@example.com",
  "password": "password"
}

# Response
{
  "token": "your-access-token",
  "user": {...}
}
```

### Main Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/products` | List products |
| POST | `/api/products` | Create product |
| GET | `/api/sales` | List sales |
| POST | `/api/sales` | Create sale |
| GET | `/api/customers` | List customers |
| GET | `/api/reports/sales` | Sales reports |

**📖 Dokumentasi API lengkap tersedia di:** `/docs/API_DOCUMENTATION.md`

## 🧪 Testing

### Menjalankan Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage
php artisan test --coverage
```

### Test Database

Untuk testing, gunakan database terpisah:

```env
# .env.testing
DB_DATABASE=q_pos_testing
```

## 🔨 Development

### Code Style

```bash
# Format code dengan Laravel Pint
./vendor/bin/pint

# Check code style
./vendor/bin/pint --test
```

### Debugging

```bash
# Enable query logging
php artisan tinker
>>> DB::enableQueryLog();
>>> // Run your code
>>> DB::getQueryLog();

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Queue Workers

```bash
# Start queue worker
php artisan queue:work

# Process failed jobs
php artisan queue:retry all
```

## 📊 Monitoring & Performance

### Health Check
```bash
GET /api/health
```

### Logs
```bash
# View logs
tail -f storage/logs/laravel.log

# Clear logs
echo "" > storage/logs/laravel.log
```

## 🔒 Security

- CORS dikonfigurasi untuk frontend domain
- Rate limiting pada API endpoints
- Input validation dan sanitization
- SQL injection protection via Eloquent ORM
- XSS protection dengan Laravel's built-in features

**📖 Panduan keamanan lengkap:** `/docs/SECURITY.md`

## 📈 Performance

- Database query optimization
- Redis caching untuk data yang sering diakses
- API response caching
- Database indexing untuk query performance

**📖 Panduan performance:** `/docs/PERFORMANCE.md`

## 🗄️ Database

### Schema
Database schema lengkap dan ERD tersedia di `/docs/DATABASE_SCHEMA.md`

### Migrations
```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback
```

### Seeders
```bash
# Create seeder
php artisan make:seeder TableNameSeeder

# Run specific seeder
php artisan db:seed --class=TableNameSeeder
```

## 🚀 Deployment

### Production Setup

1. **Environment**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Optimize for production**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

3. **Set proper permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

## 📞 Support

- **Issues**: Buat issue di repository
- **Documentation**: Lihat folder `/docs`
- **API Guide**: `/docs/API_DOCUMENTATION.md`
- **Testing Guide**: `/docs/TESTING.md`

## 📄 License

MIT License - lihat file `LICENSE` untuk detail lengkap.

---

**Dibuat dengan ❤️ menggunakan Laravel Framework**
