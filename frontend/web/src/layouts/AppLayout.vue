<template>
  <q-layout view="lHh LpR lFf" class="app-layout theme-bg-primary">
    <!-- Header -->
    <AppHeader @toggle-drawer="toggleLeftDrawer" />

    <!-- Left Drawer (Sidebar) -->
    <AppSidebar v-model="leftDrawerOpen" />

    <!-- Main Content -->
    <q-page-container class="app-content theme-bg-secondary">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import AppHeader from 'src/components/common/AppHeader.vue'
import AppSidebar from 'src/components/common/AppSidebar.vue'

// Drawer state
const leftDrawerOpen = ref(false)

// Methods
const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value
}
</script>

<style lang="scss" scoped>
.app-layout {
  transition: background-color 0.3s ease;
}

.app-content {
  transition: background-color 0.3s ease;
  min-height: 100vh;
}

// Theme-specific overrides
:deep(.q-layout) {
  background-color: var(--theme-bg-primary);
}

:deep(.q-page-container) {
  background-color: var(--theme-bg-secondary);
}
</style>