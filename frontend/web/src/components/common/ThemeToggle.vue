<template>
  <q-btn-dropdown
    :split="split"
    :flat="flat"
    :outline="outline"
    :unelevated="unelevated"
    :rounded="rounded"
    :push="push"
    :glossy="glossy"
    :fab="fab"
    :fab-mini="fabMini"
    :padding="padding"
    :color="color"
    :text-color="textColor"
    :no-caps="noCaps"
    :no-wrap="noWrap"
    :dense="dense ?? true"
    :ripple="ripple"
    :size="size"
    :label="showLabel ? getThemeLabel(currentTheme) : undefined"
    :icon="showIcon ? getThemeIcon(currentTheme) : undefined"
    :class="[
      'theme-toggle',
      customClass,
      {
        'theme-toggle--icon-only': !showLabel && showIcon,
        'theme-toggle--label-only': showLabel && !showIcon,
        'theme-toggle--compact': compact
      }
    ]"
    :style="customStyle"
    v-bind="$attrs"
  >
    <q-list :class="dropdownClass">
      <q-item
        v-for="theme in Object.values(THEME_OPTIONS)"
        :key="theme"
        clickable
        v-close-popup
        @click="handleThemeChange(theme)"
        :active="currentTheme === theme"
        :class="[
          'theme-option',
          optionClass,
          {
            'theme-option--active': currentTheme === theme
          }
        ]"
      >
        <q-item-section avatar v-if="showOptionIcons">
          <q-icon :name="getThemeIcon(theme)" :color="getOptionIconColor(theme)" />
        </q-item-section>
        <q-item-section>
          <q-item-label :class="optionLabelClass">
            {{ getThemeLabel(theme) }}
          </q-item-label>
        </q-item-section>
        <q-item-section side v-if="showActiveIndicator && currentTheme === theme">
          <q-icon 
            :name="activeIndicatorIcon" 
            :color="activeIndicatorColor" 
            :size="activeIndicatorSize"
          />
        </q-item-section>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup>
import { onMounted } from 'vue'
import { useTheme } from 'src/composables/useTheme'

// Props untuk kustomisasi
const props = defineProps({
  // Button styling props
  split: {
    type: Boolean,
    default: true
  },
  flat: {
    type: Boolean,
    default: true
  },
  outline: {
    type: Boolean,
    default: false
  },
  unelevated: {
    type: Boolean,
    default: false
  },
  rounded: {
    type: Boolean,
    default: false
  },
  push: {
    type: Boolean,
    default: false
  },
  glossy: {
    type: Boolean,
    default: false
  },
  fab: {
    type: Boolean,
    default: false
  },
  fabMini: {
    type: Boolean,
    default: false
  },
  padding: {
    type: String,
    default: undefined
  },
  color: {
    type: String,
    default: undefined
  },
  textColor: {
    type: String,
    default: undefined
  },
  noCaps: {
    type: Boolean,
    default: false
  },
  noWrap: {
    type: Boolean,
    default: false
  },
  dense: {
    type: Boolean,
    default: false
  },
  ripple: {
    type: [Boolean, Object],
    default: true
  },
  size: {
    type: String,
    default: undefined
  },
  
  // Display options
  showLabel: {
    type: Boolean,
    default: true
  },
  showIcon: {
    type: Boolean,
    default: true
  },
  showOptionIcons: {
    type: Boolean,
    default: true
  },
  showActiveIndicator: {
    type: Boolean,
    default: true
  },
  compact: {
    type: Boolean,
    default: false
  },
  
  // Custom styling
  customClass: {
    type: [String, Array, Object],
    default: ''
  },
  customStyle: {
    type: [String, Array, Object],
    default: undefined
  },
  dropdownClass: {
    type: [String, Array, Object],
    default: ''
  },
  optionClass: {
    type: [String, Array, Object],
    default: ''
  },
  optionLabelClass: {
    type: [String, Array, Object],
    default: ''
  },
  
  // Active indicator customization
  activeIndicatorIcon: {
    type: String,
    default: 'check'
  },
  activeIndicatorColor: {
    type: String,
    default: 'primary'
  },
  activeIndicatorSize: {
    type: String,
    default: undefined
  },
  
  // Auto initialize theme
  autoInitialize: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits([
  'theme-changed',
  'before-theme-change'
])

// Theme management
const {
  currentTheme,
  THEME_OPTIONS,
  setTheme,
  getThemeIcon,
  getThemeLabel,
  initializeTheme
} = useTheme()

// Handle theme change with events
const handleThemeChange = (theme) => {
  // Emit before change event
  emit('before-theme-change', {
    from: currentTheme.value,
    to: theme
  })
  
  // Set the theme
  setTheme(theme)
  
  // Emit after change event
  emit('theme-changed', {
    theme,
    previous: currentTheme.value
  })
}

// Get option icon color based on theme
const getOptionIconColor = (theme) => {
  if (currentTheme.value === theme) {
    return props.activeIndicatorColor
  }
  return undefined
}

// Initialize theme saat component mounted jika autoInitialize true
onMounted(() => {
  if (props.autoInitialize) {
    initializeTheme()
  }
})

// Expose methods untuk parent component
defineExpose({
  setTheme,
  currentTheme,
  THEME_OPTIONS,
  getThemeIcon,
  getThemeLabel
})
</script>

<style scoped>
.theme-toggle {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.theme-toggle--compact {
  min-width: auto;
}

.theme-toggle--icon-only :deep(.q-btn__content) {
  min-width: auto;
}

.theme-toggle--label-only :deep(.q-btn__content) {
  padding: 0 12px;
}

:deep(.theme-option) {
  transition: all 0.2s ease;
}

:deep(.theme-option:hover) {
  background-color: rgba(25, 118, 210, 0.1);
}

:deep(.theme-option--active) {
  background-color: rgba(25, 118, 210, 0.15);
}

:deep(.theme-option--active .q-item__label) {
  font-weight: 600;
  color: #1976d2;
}

@media (max-width: 768px) {
  .theme-toggle--compact :deep(.q-btn__content) {
    padding: 0 8px;
  }
}
</style>