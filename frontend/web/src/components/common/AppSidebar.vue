<template>
  <q-drawer
    :model-value="modelValue"
    @update:model-value="$emit('update:model-value', $event)"
    show-if-above
    bordered
    class="app-drawer"
    :width="280"
    :breakpoint="1024"
  >
    <!-- Drawer Header -->
    <div class="drawer-header bg-primary text-white q-pa-md">
      <div class="text-h6 text-weight-bold">
        <q-icon name="dashboard" class="q-mr-sm" />
        {{ $t('nav.dashboard') }}
      </div>
      <div class="text-caption opacity-70">
        {{ $t('app.description') }}
      </div>
    </div>

    <!-- Navigation Menu -->
    <NavigationMenu />
  </q-drawer>
</template>

<script setup>
import NavigationMenu from './NavigationMenu.vue'

// Define props
defineProps({
  modelValue: {
    type: Boolean,
    default: false
  }
})

// Define emits
defineEmits(['update:model-value'])
</script>

<style lang="scss" scoped>
@use 'sass:color';

.app-drawer {
  .drawer-header {
    background: linear-gradient(135deg, $primary 0%, color.adjust($primary, $lightness: -15%) 100%);
  }
}
</style>