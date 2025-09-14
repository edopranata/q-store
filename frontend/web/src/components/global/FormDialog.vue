<template>
  <q-dialog
    v-model="isOpen"
    :persistent="persistent"
    :maximized="maximized"
    :full-width="fullWidth"
    :full-height="fullHeight"
    @hide="onHide"
  >
    <q-card class="form-dialog" :style="cardStyle">
      <!-- Header -->
      <q-card-section class="dialog-header">
        <div class="row items-center justify-between">
          <div>
            <h6 class="dialog-title q-ma-none">{{ title }}</h6>
            <p v-if="subtitle" class="dialog-subtitle q-ma-none text-grey-6">{{ subtitle }}</p>
          </div>
          
          <q-btn
            flat
            round
            dense
            icon="close"
            @click="close"
            v-if="showCloseButton"
          />
        </div>
      </q-card-section>

      <q-separator />

      <!-- Content -->
      <q-card-section class="dialog-content" :class="contentClass">
        <q-form ref="formRef" @submit="onSubmit" @reset="onReset">
          <slot :form-data="formData" :errors="errors" :loading="loading" />
        </q-form>
      </q-card-section>

      <!-- Actions -->
      <q-card-actions class="dialog-actions" :align="actionsAlign">
        <slot name="actions" :form-data="formData" :loading="loading" :close="close" :submit="submit">
          <q-btn
            flat
            :label="cancelLabel"
            @click="close"
            :disable="loading"
            v-if="showCancelButton"
          />
          
          <q-btn
            color="primary"
            :label="submitLabel"
            type="submit"
            :loading="loading"
            @click="submit"
            v-if="showSubmitButton"
          />
        </slot>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  formData: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  loading: {
    type: Boolean,
    default: false
  },
  persistent: {
    type: Boolean,
    default: false
  },
  maximized: {
    type: Boolean,
    default: false
  },
  fullWidth: {
    type: Boolean,
    default: false
  },
  fullHeight: {
    type: Boolean,
    default: false
  },
  width: {
    type: [String, Number],
    default: '500px'
  },
  maxWidth: {
    type: [String, Number],
    default: '90vw'
  },
  showCloseButton: {
    type: Boolean,
    default: true
  },
  showCancelButton: {
    type: Boolean,
    default: true
  },
  showSubmitButton: {
    type: Boolean,
    default: true
  },
  cancelLabel: {
    type: String,
    default: 'Batal'
  },
  submitLabel: {
    type: String,
    default: 'Simpan'
  },
  actionsAlign: {
    type: String,
    default: 'right',
    validator: (value) => ['left', 'center', 'right', 'around', 'between', 'evenly'].includes(value)
  },
  contentClass: {
    type: String,
    default: ''
  },
  validateOnSubmit: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'submit',
  'cancel',
  'close',
  'show',
  'hide'
])

// Composables
const $q = useQuasar()

// Reactive data
const formRef = ref(null)

// Computed
const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const cardStyle = computed(() => {
  const styles = {}
  
  if (!props.maximized && !props.fullWidth) {
    styles.width = typeof props.width === 'number' ? `${props.width}px` : props.width
    styles.maxWidth = typeof props.maxWidth === 'number' ? `${props.maxWidth}px` : props.maxWidth
  }
  
  return styles
})

// Methods
const close = () => {
  isOpen.value = false
  emit('close')
  emit('cancel')
}

const submit = async () => {
  if (props.validateOnSubmit && formRef.value) {
    const isValid = await formRef.value.validate()
    if (!isValid) {
      $q.notify({
        type: 'negative',
        message: 'Mohon periksa kembali data yang diisi',
        position: 'top'
      })
      return
    }
  }
  
  emit('submit', props.formData)
}

const onSubmit = () => {
  submit()
}

const onReset = () => {
  emit('reset')
}

const onHide = () => {
  emit('hide')
}

// Watch for dialog open/close
watch(isOpen, (newValue) => {
  if (newValue) {
    emit('show')
  }
})
</script>

<style lang="scss" scoped>
.form-dialog {
  .dialog-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    
    .dialog-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #2c3e50;
    }
    
    .dialog-subtitle {
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }
  }
  
  .dialog-content {
    max-height: 70vh;
    overflow-y: auto;
    
    // Custom scrollbar
    &::-webkit-scrollbar {
      width: 6px;
    }
    
    &::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }
    
    &::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
      
      &:hover {
        background: #a8a8a8;
      }
    }
  }
  
  .dialog-actions {
    background-color: #f8f9fa;
    border-top: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .form-dialog {
    .dialog-header {
      .row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
      }
    }
    
    .dialog-content {
      max-height: 60vh;
    }
    
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