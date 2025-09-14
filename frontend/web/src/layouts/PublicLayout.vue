<template>
  <q-layout view="lHh Lpr lFf" class="theme-bg-primary">
    <!-- Header -->
    <q-header elevated class="bg-primary text-white public-header">
      <q-toolbar class="theme-spacing-md">
        <q-toolbar-title class="text-h5 text-weight-bold theme-font-xl">
          <q-icon name="point_of_sale" size="md" class="q-mr-sm" />
          {{ $t('app.name') }}
        </q-toolbar-title>

        <q-space />

        <!-- Navigation Menu -->
        <q-btn-group flat>
          <q-btn
            flat
            :label="$t('nav.features')"
            @click="scrollToSection('features')"
            class="q-px-lg theme-font-md"
          />
          <q-btn
            flat
            :label="$t('nav.about')"
            @click="scrollToSection('about')"
            class="q-px-lg theme-font-md"
          />
          <q-btn
            flat
            :label="$t('nav.contact')"
            @click="scrollToSection('contact')"
            class="q-px-lg theme-font-md"
          />
        </q-btn-group>

        <q-separator vertical spaced class="q-mx-md" />

        <!-- Theme Toggle -->
        <ThemeToggle 
          :split="false"
          flat
          color="white"
          text-color="white"
          :show-label="false"
          class="q-mr-md"
          custom-class="public-theme-toggle"
          @theme-changed="onThemeChanged"
        />

        <!-- Login Button -->
        <q-btn
          unelevated
          color="secondary"
          :label="$t('auth.login')"
          @click="$router.push({ name: 'login' })"
          class="q-px-xl theme-font-md theme-radius-md"
        />
      </q-toolbar>
    </q-header>

    <!-- Main Content -->
    <q-page-container class="theme-bg-secondary">
      <router-view />
    </q-page-container>

    <!-- Footer -->
    <q-footer class="theme-bg-dark text-white public-footer">
      <q-toolbar class="justify-center theme-spacing-md">
        <div class="text-center">
          <div class="text-body2 theme-font-md">
            © {{ currentYear }} {{ $t('app.name') }} - {{ $t('footer.rights') }}
          </div>
          <div class="text-caption q-mt-xs theme-font-sm theme-text-caption">
            {{ $t('footer.version') }} {{ appVersion }}
          </div>
        </div>
      </q-toolbar>
    </q-footer>
  </q-layout>
</template>

<script setup>
import { computed } from 'vue'
import ThemeToggle from 'src/components/common/ThemeToggle.vue'

const currentYear = computed(() => new Date().getFullYear())
const appVersion = computed(() => process.env.VUE_APP_VERSION || '1.0.0')

const scrollToSection = (sectionId) => {
  const element = document.getElementById(sectionId)
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
  }
}

// Handle theme change event
const onThemeChanged = (event) => {
  console.log('Public layout theme changed:', event)
}
</script>

<style lang="scss" scoped>
@use 'sass:color';

.public-header {
  backdrop-filter: blur(10px);
  background: linear-gradient(135deg, $primary 0%, color.adjust($primary, $lightness: -10%) 100%);
  
  :deep(.public-theme-toggle) {
    .q-btn {
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      transition: all 0.3s ease;
      
      &:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
      }
    }
  }
  transition: all 0.3s ease;
  
  :deep(.q-toolbar) {
    padding: var(--theme-spacing-md);
  }
}

.public-footer {
  background: var(--theme-bg-dark);
  transition: all 0.3s ease;
  
  :deep(.q-toolbar) {
    padding: var(--theme-spacing-md);
  }
}

.q-btn {
  transition: all 0.3s ease;
  border-radius: var(--theme-radius-md);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--theme-shadow-light);
  }
}

:deep(.q-layout) {
  background-color: var(--theme-bg-primary);
  transition: background-color 0.3s ease;
}

:deep(.q-page-container) {
  background-color: var(--theme-bg-secondary);
  transition: background-color 0.3s ease;
}
</style>