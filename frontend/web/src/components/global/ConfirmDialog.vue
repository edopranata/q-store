<template>
  <q-dialog
    v-model="isOpen"
    persistent
    @hide="onHide"
  >
    <q-card class="confirm-dialog" style="min-width: 350px; max-width: 500px;">
      <!-- Header -->
      <q-card-section class="dialog-header">
        <div class="row items-center no-wrap">
          <q-icon
            :name="iconName"
            :color="iconColor"
            size="2rem"
            class="q-mr-md"
          />
          <div class="col">
            <h6 class="dialog-title q-ma-none">{{ title }}</h6>
          </div>
        </div>
      </q-card-section>

      <!-- Content -->
      <q-card-section class="dialog-content">
        <p class="dialog-message q-ma-none">{{ message }}</p>
        
        <!-- Additional details -->
        <div v-if="details" class="dialog-details q-mt-md">
          <q-separator class="q-mb-md" />
          <div class="text-caption text-grey-6">{{ details }}</div>
        </div>
        
        <!-- Custom content slot -->
        <slot />
      </q-card-section>

      <!-- Actions -->
      <q-card-actions align="right" class="dialog-actions">
        <q-btn
          flat
          :label="cancelLabel"
          @click="cancel"
          :disable="loading"
          class="q-mr-sm"
        />
        
        <q-btn
          :color="confirmColor"
          :label="confirmLabel"
          @click="confirm"
          :loading="loading"
          :disable="loading"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'warning',
    validator: (value) => ['info', 'warning', 'error', 'success', 'question'].includes(value)
  },
  title: {
    type: String,
    default: 'Konfirmasi'
  },
  message: {
    type: String,
    required: true
  },
  details: {
    type: String,
    default: ''
  },
  confirmLabel: {
    type: String,
    default: 'Ya'
  },
  cancelLabel: {
    type: String,
    default: 'Batal'
  },
  loading: {
    type: Boolean,
    default: false
  },
  persistent: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'confirm',
  'cancel',
  'hide'
])

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const iconName = computed(() => {
  const icons = {
    info: 'info',
    warning: 'warning',
    error: 'error',
    success: 'check_circle',
    question: 'help'
  }
  return icons[props.type] || 'warning'
})

const iconColor = computed(() => {
  const colors = {
    info: 'info',
    warning: 'warning',
    error: 'negative',
    success: 'positive',
    question: 'primary'
  }
  return colors[props.type] || 'warning'
})

const confirmColor = computed(() => {
  const colors = {
    info: 'primary',
    warning: 'warning',
    error: 'negative',
    success: 'positive',
    question: 'primary'
  }
  return colors[props.type] || 'warning'
})

// Methods
const confirm = () => {
  emit('confirm')
}

const cancel = () => {
  isOpen.value = false
  emit('cancel')
}

const onHide = () => {
  emit('hide')
}
</script>

<style lang="scss" scoped>
.confirm-dialog {
  .dialog-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
    
    .dialog-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2c3e50;
    }
  }
  
  .dialog-content {
    padding: 0 1.5rem 1rem 1.5rem;
    
    .dialog-message {
      font-size: 1rem;
      line-height: 1.5;
      color: #2c3e50;
    }
    
    .dialog-details {
      background-color: #f8f9fa;
      padding: 0.75rem;
      border-radius: 4px;
      border-left: 3px solid #dee2e6;
    }
  }
  
  .dialog-actions {
    padding: 1rem 1.5rem 1.5rem 1.5rem;
  }
}

// Type-specific styling
.confirm-dialog {
  &.type-error {
    .dialog-header {
      background-color: #fef2f2;
    }
  }
  
  &.type-warning {
    .dialog-header {
      background-color: #fffbeb;
    }
  }
  
  &.type-success {
    .dialog-header {
      background-color: #f0fdf4;
    }
  }
  
  &.type-info {
    .dialog-header {
      background-color: #eff6ff;
    }
  }
}

// Responsive adjustments
@media (max-width: 480px) {
  .confirm-dialog {
    min-width: 300px;
    
    .dialog-actions {
      flex-direction: column;
      gap: 0.5rem;
      
      .q-btn {
        width: 100%;
      }
    }
  }
}
</style>