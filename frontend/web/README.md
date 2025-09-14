# 🖥️ Q-POS Frontend Web

> Frontend web application untuk sistem Point of Sales (Q-POS) yang dibangun dengan Vue.js dan Quasar Framework

## 📋 Deskripsi

Q-POS Frontend adalah aplikasi web modern yang menyediakan antarmuka pengguna untuk sistem Point of Sales, dengan fitur-fitur seperti manajemen produk, proses penjualan, laporan, dan dashboard analytics.

## 🚀 Quick Start

### Prerequisites

- Node.js >= 16.x
- npm atau yarn
- Backend API Q-POS (running di port 8000)

### Installation

1. **Masuk ke direktori frontend web**
   ```bash
   cd frontend/web
   ```

2. **Install dependencies**
   ```bash
   yarn install
   # atau
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   ```

4. **Konfigurasi environment variables di `.env`**
   ```env
   # API Configuration
   VITE_API_BASE_URL=http://localhost:8000/api
   VITE_APP_NAME="Q-POS"
   VITE_APP_VERSION="1.0.0"
   
   # Development
   VITE_APP_ENV=development
   VITE_APP_DEBUG=true
   ```

5. **Start development server**
   ```bash
   yarn dev
   # atau
   npm run dev
   ```

   Aplikasi akan tersedia di: `http://localhost:5173`

## 🔧 Konfigurasi

### Environment Variables

```env
# API Configuration
VITE_API_BASE_URL=http://localhost:8000/api
VITE_API_TIMEOUT=10000

# Application
VITE_APP_NAME="Q-POS"
VITE_APP_VERSION="1.0.0"
VITE_APP_DESCRIPTION="Point of Sales System"

# Development
VITE_APP_ENV=development
VITE_APP_DEBUG=true

# Features
VITE_ENABLE_PWA=true
VITE_ENABLE_ANALYTICS=false
```

### Quasar Configuration

Konfigurasi Quasar tersedia di `quasar.config.js`:

```javascript
// Key configurations
module.exports = {
  framework: {
    config: {
      brand: {
        primary: '#1976D2',
        secondary: '#26A69A',
        // ... other brand colors
      }
    },
    plugins: [
      'Notify',
      'Dialog',
      'Loading',
      'LocalStorage',
      'SessionStorage'
    ]
  }
}
```

## 🏗️ Struktur Proyek

```
src/
├── components/          # Reusable components
│   ├── common/         # Common UI components
│   ├── forms/          # Form components
│   └── charts/         # Chart components
├── layouts/            # Page layouts
├── pages/              # Page components
│   ├── auth/          # Authentication pages
│   ├── dashboard/     # Dashboard pages
│   ├── products/      # Product management
│   ├── sales/         # Sales management
│   └── reports/       # Reports pages
├── router/            # Vue Router configuration
├── stores/            # Pinia stores (state management)
├── composables/       # Vue composables
├── utils/             # Utility functions
├── services/          # API services
└── assets/            # Static assets
```

## 🎨 UI/UX Features

### Design System
- **Framework**: Quasar Framework dengan Material Design
- **Icons**: Material Icons dan Quasar Icons
- **Typography**: Roboto font family
- **Color Scheme**: Customizable brand colors
- **Responsive**: Mobile-first responsive design

### Key Components
- **Dashboard**: Real-time analytics dan metrics
- **POS Interface**: Touch-friendly sales interface
- **Product Management**: CRUD operations dengan image upload
- **Inventory Tracking**: Real-time stock monitoring
- **Reports**: Interactive charts dan export functionality
- **User Management**: Role-based access control

## 🛠️ Development

### Available Scripts

```bash
# Development server dengan hot reload
yarn dev
npm run dev

# Build untuk production
yarn build
npm run build

# Preview production build
yarn preview
npm run preview

# Lint files
yarn lint
npm run lint

# Format files
yarn format
npm run format

# Type checking
yarn type-check
npm run type-check
```

### Code Style

```bash
# ESLint configuration
yarn lint
yarn lint --fix

# Prettier formatting
yarn format

# Pre-commit hooks
# Automatically runs lint and format before commit
```

### State Management

Menggunakan Pinia untuk state management:

```javascript
// stores/auth.js
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    isAuthenticated: false
  }),
  
  actions: {
    async login(credentials) {
      // Login logic
    },
    
    logout() {
      // Logout logic
    }
  }
})
```

### API Integration

```javascript
// services/api.js
import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000
})

// Request interceptor untuk token
api.interceptors.request.use(config => {
  const token = LocalStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

export default api
```

## 🧪 Testing

### Unit Testing

```bash
# Run unit tests
yarn test:unit
npm run test:unit

# Run tests dengan coverage
yarn test:coverage
npm run test:coverage

# Watch mode
yarn test:watch
npm run test:watch
```

### E2E Testing

```bash
# Run E2E tests
yarn test:e2e
npm run test:e2e

# Run E2E tests dengan UI
yarn test:e2e:ui
npm run test:e2e:ui
```

### Testing Structure

```
tests/
├── unit/              # Unit tests
│   ├── components/    # Component tests
│   ├── stores/        # Store tests
│   └── utils/         # Utility tests
├── e2e/               # End-to-end tests
│   ├── specs/         # Test specifications
│   └── fixtures/      # Test data
└── setup/             # Test setup files
```

## 📱 Progressive Web App (PWA)

Aplikasi mendukung PWA features:

- **Offline Support**: Service worker untuk caching
- **Install Prompt**: Dapat diinstall sebagai app
- **Push Notifications**: Notifikasi real-time
- **Background Sync**: Sync data saat online kembali

```javascript
// quasar.config.js - PWA configuration
pwa: {
  workboxMode: 'generateSW',
  injectPwaMetaTags: true,
  swFilename: 'sw.js',
  manifestFilename: 'manifest.json',
  useCredentialsForManifestTag: false
}
```

## 🚀 Build & Deployment

### Production Build

```bash
# Build untuk production
yarn build
npm run build

# Output akan tersedia di folder dist/
```

### Build Optimization

- **Code Splitting**: Automatic route-based code splitting
- **Tree Shaking**: Unused code elimination
- **Asset Optimization**: Image dan asset compression
- **Bundle Analysis**: Analyze bundle size

```bash
# Analyze bundle size
yarn build --analyze
npm run build -- --analyze
```

### Deployment Options

#### Static Hosting (Netlify, Vercel)
```bash
# Build command
yarn build

# Publish directory
dist/
```

#### Docker Deployment
```dockerfile
# Dockerfile
FROM node:16-alpine as build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
```

## 🔒 Security

- **Authentication**: JWT token-based authentication
- **Authorization**: Role-based access control (RBAC)
- **XSS Protection**: Content Security Policy (CSP)
- **HTTPS**: Force HTTPS in production
- **Input Validation**: Client-side validation

## 📈 Performance

### Optimization Strategies
- **Lazy Loading**: Route dan component lazy loading
- **Image Optimization**: WebP format dan lazy loading
- **Caching**: Browser caching dan service worker
- **Bundle Splitting**: Vendor dan app code separation
- **Tree Shaking**: Unused code elimination

### Performance Monitoring
```javascript
// Performance metrics
import { getCLS, getFID, getFCP, getLCP, getTTFB } from 'web-vitals'

getCLS(console.log)
getFID(console.log)
getFCP(console.log)
getLCP(console.log)
getTTFB(console.log)
```

## 🌐 Internationalization (i18n)

```javascript
// i18n configuration
import { createI18n } from 'vue-i18n'

const i18n = createI18n({
  locale: 'id',
  fallbackLocale: 'en',
  messages: {
    en: require('./locales/en.json'),
    id: require('./locales/id.json')
  }
})
```

## 📞 Support & Documentation

- **Component Documentation**: Storybook available at `/storybook`
- **API Documentation**: `/docs/API_DOCUMENTATION.md`
- **User Guide**: `/docs/USER_STORIES.md`
- **Testing Guide**: `/docs/TESTING.md`

### Useful Resources

- [Vue.js Documentation](https://vuejs.org/)
- [Quasar Framework](https://quasar.dev/)
- [Pinia State Management](https://pinia.vuejs.org/)
- [Vite Build Tool](https://vitejs.dev/)

## 📄 License

MIT License - lihat file `LICENSE` untuk detail lengkap.

---

**Dibuat dengan ❤️ menggunakan Vue.js & Quasar Framework**
