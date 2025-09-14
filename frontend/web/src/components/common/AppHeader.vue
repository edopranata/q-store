<template>
  <q-header elevated class="bg-primary text-white app-header">
    <q-toolbar>
      <!-- Menu Toggle -->
      <q-btn
        flat
        dense
        round
        icon="menu"
        @click="$emit('toggle-drawer')"
        class="q-mr-sm"
      />

      <!-- App Title -->
      <q-toolbar-title class="text-h6 text-weight-bold">
        <q-icon name="point_of_sale" size="sm" class="q-mr-sm" />
        {{ $t('app.name') }}
      </q-toolbar-title>

      <q-space />

      <!-- Breadcrumbs -->
      <q-breadcrumbs class="text-white q-mr-lg" v-if="breadcrumbs.length > 0">
        <q-breadcrumbs-el
          v-for="(crumb, index) in breadcrumbs"
          :key="index"
          :label="crumb.label"
          :icon="crumb.icon"
          :to="crumb.to"
          class="text-white"
        />
      </q-breadcrumbs>

      <!-- Theme Toggle -->
      <ThemeToggle 
        class="theme-toggle"
        @theme-changed="onThemeChanged"
      />

      <!-- User Menu -->
      <UserMenu />
    </q-toolbar>
  </q-header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import UserMenu from './UserMenu.vue'
import ThemeToggle from './ThemeToggle.vue'

const route = useRoute()

// Define emits
defineEmits(['toggle-drawer'])

// Handle theme change event
const onThemeChanged = (event) => {
  console.log('Theme changed:', event)
}

// Breadcrumbs
const breadcrumbs = computed(() => {
  const pathSegments = route.path.split('/').filter(segment => segment)
  const crumbs = []
  
  if (pathSegments.length > 1) {
    crumbs.push({
      label: 'Dashboard',
      icon: 'dashboard',
      to: '/app/dashboard'
    })
    
    if (pathSegments.length > 2) {
      const currentPage = pathSegments[2]
      crumbs.push({
        label: currentPage.charAt(0).toUpperCase() + currentPage.slice(1),
        to: route.path
      })
    }
  }
  
  return crumbs
})
</script>

<style lang="scss" scoped>
@use 'sass:color';

.app-header {
  backdrop-filter: blur(10px);
  background: linear-gradient(135deg, $primary 0%, color.adjust($primary, $lightness: -10%) 100%);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.theme-toggle {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  
  &:hover {
    background-color: rgba(255, 255, 255, 0.1);
    transform: scale(1.05);
  }
  
  :deep(.q-btn__content) {
    .q-icon {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
  }
}

:deep(.theme-option) {
  transition: all 0.2s ease;
  
  &:hover {
    background-color: rgba($primary, 0.1);
  }
  
  &.q-item--active {
    background-color: rgba($primary, 0.15);
    color: $primary;
  }
}

// Responsive adjustments
@media (max-width: $breakpoint-md-max) {
  .q-breadcrumbs {
    display: none;
  }
  
  .theme-toggle {
    margin-right: 4px;
  }
}
</style>