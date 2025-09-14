<template>
  <q-layout view="lHh Lpr lFf" class="auth-layout theme-bg-primary">
    <q-page-container class="auth-page-container">
      <div class="auth-wrapper">
        <!-- Left Panel - Branding -->
        <div class="auth-left-panel">
          <div class="branding-content">
            <!-- Logo and App Name -->
            <div class="logo-section">
              <q-icon 
                name="point_of_sale" 
                size="4rem" 
                color="white" 
                class="q-mb-md"
              />
              <h2 class="text-h2 text-weight-bold text-white q-ma-none theme-font-xxl">
                {{ $t('app.name') }}
              </h2>
              <p class="text-h6 text-white q-mt-sm q-mb-xl theme-font-lg">
                {{ $t('app.tagline') }}
              </p>
            </div>

            <!-- Features List -->
            <div class="features-list">
              <div class="feature-item theme-radius-md" v-for="feature in features" :key="feature.icon">
                <q-icon :name="feature.icon" size="md" color="white" class="q-mr-md" />
                <div>
                  <div class="text-body1 text-weight-medium text-white theme-font-md">{{ feature.title }}</div>
                  <div class="text-body2 theme-text-caption theme-font-sm">{{ feature.description }}</div>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div class="branding-footer">
              <div class="text-caption theme-text-caption theme-font-xs">
                © {{ currentYear }} {{ $t('app.name') }} - {{ $t('footer.rights') }}
              </div>
            </div>
          </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="auth-right-panel theme-bg-card">
          <div class="form-content">
            <!-- Back to Home Button -->
            <div class="form-header">
              <q-btn
                flat
                color="primary"
                :label="$t('nav.backToHome')"
                @click="$router.push({ name: 'home' })"
                icon="arrow_back"
                size="sm"
                class="q-mb-lg theme-font-sm"
              />
            </div>

            <!-- Auth Form -->
            <div class="form-container theme-bg-card theme-radius-lg theme-shadow-medium">
              <router-view />
            </div>
          </div>
        </div>
      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, ref } from 'vue'

const currentYear = computed(() => new Date().getFullYear())

const features = ref([
  {
    icon: 'inventory_2',
    title: 'Manajemen Inventori',
    description: 'Kelola stok produk dengan mudah dan akurat'
  },
  {
    icon: 'point_of_sale',
    title: 'Sistem POS Modern',
    description: 'Proses transaksi yang cepat dan efisien'
  },
  {
    icon: 'analytics',
    title: 'Laporan Real-time',
    description: 'Analisis bisnis dengan data yang akurat'
  },
  {
    icon: 'cloud_sync',
    title: 'Sinkronisasi Cloud',
    description: 'Data tersimpan aman di cloud'
  }
])
</script>

<style lang="scss" scoped>
@use 'sass:color';

.auth-layout {
  min-height: 100vh;
}

.auth-page-container {
  min-height: 100vh;
}

.auth-wrapper {
  display: flex;
  min-height: 100vh;
}

// Left Panel - Branding
.auth-left-panel {
  flex: 1;
  background: linear-gradient(135deg, 
    $primary 0%, 
    color.adjust($primary, $lightness: -15%) 50%,
    color.adjust($primary, $lightness: -25%) 100%
  );
  position: relative;
  overflow: hidden;
  
  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 20%, rgba(255,255,255,0.1) 0%, transparent 40%),
      radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 40%),
      radial-gradient(circle at 40% 60%, rgba(255,255,255,0.05) 0%, transparent 50%);
    z-index: 1;
  }
}

.branding-content {
  position: relative;
  z-index: 2;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 3rem;
}

.logo-section {
  text-align: center;
  
  h2 {
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }
}

.features-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 2rem;
}

.feature-item {
  display: flex;
  align-items: flex-start;
  padding: 1rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
  
  &:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: translateX(8px);
  }
}

.branding-footer {
  text-align: center;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
}

// Right Panel - Form
.auth-right-panel {
  flex: 1;
  background: var(--theme-bg-card);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.form-content {
  width: 100%;
  max-width: 450px;
  padding: var(--theme-spacing-xl);
}

.form-header {
  display: flex;
  justify-content: flex-start;
}

.form-container {
  background: var(--theme-bg-card);
  border-radius: var(--theme-radius-lg);
  padding: var(--theme-spacing-lg);
  box-shadow: var(--theme-shadow-medium);
  border: 1px solid var(--theme-border-light);
  transition: all 0.3s ease;
}

// Responsive Design
@media (max-width: $breakpoint-md-max) {
  .auth-wrapper {
    flex-direction: column;
  }
  
  .auth-left-panel {
    min-height: 40vh;
  }
  
  .branding-content {
    padding: 2rem;
    text-align: center;
  }
  
  .features-list {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
  }
  
  .feature-item {
    flex: 1;
    min-width: 200px;
    max-width: 250px;
    
    &:hover {
      transform: translateY(-4px);
    }
  }
  
  .form-content {
    padding: 2rem;
  }
  
  .form-container {
    padding: 1.5rem;
  }
}

@media (max-width: $breakpoint-sm-max) {
  .auth-left-panel {
    min-height: 35vh;
  }
  
  .branding-content {
    padding: 1.5rem;
  }
  
  .logo-section {
    h2 {
      font-size: 2rem;
    }
  }
  
  .features-list {
    flex-direction: column;
    gap: 1rem;
  }
  
  .feature-item {
    max-width: 100%;
  }
  
  .form-content {
    padding: 1.5rem;
  }
  
  .form-container {
    padding: 1rem;
  }
}

@media (max-width: $breakpoint-xs-max) {
  .branding-content {
    padding: 1rem;
  }
  
  .form-content {
    padding: 1rem;
  }
  
  .form-container {
    padding: 1rem;
    border-radius: 8px;
  }
}
</style>