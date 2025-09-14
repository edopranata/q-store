<template>
  <div class="forgot-password-page">
    <!-- Page Header -->
    <div class="page-header q-mb-xl">
      <div class="text-center q-mb-md">
        <q-icon name="lock_reset" size="3rem" color="primary" />
      </div>
      <h1 class="text-h4 text-weight-bold q-ma-none text-center">Lupa Password?</h1>
      <p class="text-body2 text-caption q-mt-sm text-center">
        Masukkan email Anda dan kami akan mengirimkan link untuk reset password
      </p>
    </div>

      <q-form @submit="onSubmit" class="forgot-password-form">
        <div class="q-mb-lg">
          <q-input
            v-model="form.email"
            type="email"
            label="Email"
            outlined
            :rules="emailRules"
            :loading="loading"
            :disable="loading"
            autocomplete="email"
            class="full-width"
            autofocus
          >
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>
        </div>

        <q-btn
          type="submit"
          color="primary"
          label="Kirim Link Reset"
          size="lg"
          :loading="loading"
          :disable="loading || !isFormValid"
          class="full-width q-mb-md"
        />

        <div class="text-center">
          <q-btn
            flat
            no-caps
            color="primary"
            label="Kembali ke Login"
            icon="arrow_back"
            :disable="loading"
            @click="goToLogin"
          />
        </div>
      </q-form>

    <!-- Success Message -->
    <q-card v-if="emailSent" class="success-card q-mt-lg" flat bordered>
      <q-card-section class="text-center q-pa-lg">
        <q-icon name="mark_email_read" size="3rem" color="positive" class="q-mb-md" />
        <h3 class="text-h6 text-weight-bold text-positive q-ma-none q-mb-sm">Email Terkirim!</h3>
        <p class="text-body2 text-grey-7 q-ma-none q-mb-md">
          Kami telah mengirimkan link reset password ke email <strong>{{ form.email }}</strong>.
          Silakan cek inbox atau folder spam Anda.
        </p>
        <div class="q-gutter-sm">
          <q-btn
            color="positive"
            label="Buka Email"
            icon="open_in_new"
            @click="openEmailClient"
            size="md"
            class="q-px-lg"
          />
          <q-btn
            outline
            color="primary"
            label="Kirim Ulang"
            icon="refresh"
            @click="resendEmail"
            :loading="resending"
            :disable="resending || cooldownActive"
            size="md"
            class="q-px-lg"
          />
        </div>
        <div v-if="cooldownActive" class="text-caption text-grey-6 q-mt-sm">
          Kirim ulang dalam {{ cooldownTime }} detik
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'stores/auth'
import { Notify } from 'quasar'

const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const resending = ref(false)
const emailSent = ref(false)
const cooldownActive = ref(false)
const cooldownTime = ref(0)
const cooldownInterval = ref(null)

const form = ref({
  email: ''
})

// Validation rules
const emailRules = [
  val => !!val || 'Email harus diisi',
  val => /.+@.+\..+/.test(val) || 'Format email tidak valid'
]

// Computed
const isFormValid = computed(() => {
  return form.value.email && /.+@.+\..+/.test(form.value.email)
})

// Methods
const onSubmit = async () => {
  if (!isFormValid.value) return
  
  loading.value = true
  
  try {
    const success = await authStore.forgotPassword(form.value.email)
    
    if (success) {
      emailSent.value = true
      startCooldown()
    }
  } catch (error) {
    console.error('Forgot password error:', error)
  } finally {
    loading.value = false
  }
}

const resendEmail = async () => {
  if (cooldownActive.value) return
  
  resending.value = true
  
  try {
    const success = await authStore.forgotPassword(form.value.email)
    
    if (success) {
      Notify.create({
        type: 'positive',
        message: 'Email reset password telah dikirim ulang',
        position: 'top'
      })
      startCooldown()
    }
  } catch (error) {
    console.error('Resend email error:', error)
  } finally {
    resending.value = false
  }
}

const startCooldown = () => {
  cooldownActive.value = true
  cooldownTime.value = 60 // 60 seconds cooldown
  
  cooldownInterval.value = setInterval(() => {
    cooldownTime.value--
    
    if (cooldownTime.value <= 0) {
      clearInterval(cooldownInterval.value)
      cooldownActive.value = false
    }
  }, 1000)
}

const openEmailClient = () => {
  // Try to open default email client
  const emailDomain = form.value.email.split('@')[1]
  let emailUrl = 'mailto:'
  
  // Popular email providers
  const emailProviders = {
    'gmail.com': 'https://mail.google.com',
    'yahoo.com': 'https://mail.yahoo.com',
    'outlook.com': 'https://outlook.live.com',
    'hotmail.com': 'https://outlook.live.com'
  }
  
  if (emailProviders[emailDomain]) {
    emailUrl = emailProviders[emailDomain]
  }
  
  window.open(emailUrl, '_blank')
}

const goToLogin = () => {
  router.push({ name: 'login' })
}

// Lifecycle
onMounted(() => {
  // Clear any existing auth state
  if (authStore.isLoggedIn) {
    router.push({ name: 'dashboard' })
  }
})

onUnmounted(() => {
  if (cooldownInterval.value) {
    clearInterval(cooldownInterval.value)
  }
})
</script>

<style lang="scss" scoped>
.forgot-password-page {
  width: 100%;
  background-color: var(--theme-bg-card);
  padding: var(--theme-spacing-lg);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
}

.page-header {
  text-align: center;
  
  h1 {
    line-height: 1.2;
    color: var(--theme-text-primary);
    font-size: var(--theme-font-xxl);
    transition: var(--theme-transition-all);
  }
  
  p {
    line-height: 1.5;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    color: var(--theme-text-secondary);
    font-size: var(--theme-font-md);
    transition: var(--theme-transition-all);
  }
}

.forgot-password-form {
  .q-field {
    margin-bottom: 0;
    
    &.q-field--outlined {
      .q-field__control {
        border-radius: var(--theme-radius-md);
        background-color: var(--theme-bg-secondary);
        border-color: var(--theme-border-light);
        transition: var(--theme-transition-all);
      }
    }
  }
  
  .q-btn {
    border-radius: var(--theme-radius-md);
    font-weight: 500;
    text-transform: none;
    transition: var(--theme-transition-all);
  }
}

.success-card {
  border: var(--theme-border-light);
  background: var(--theme-bg-success);
  border-radius: var(--theme-radius-lg);
  transition: var(--theme-transition-all);
  
  .q-btn {
    border-radius: var(--theme-radius-md);
    font-weight: 500;
    text-transform: none;
    transition: var(--theme-transition-all);
  }
}

// Success card animation
.success-card {
  animation: fadeInScale 0.5s ease-out;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

// Universal transition
* {
  transition: var(--theme-transition-all);
}

// Focus states
.q-field--focused {
  .q-field__control {
    box-shadow: var(--theme-shadow-focus);
    border-color: var(--theme-primary);
  }
}

// Button hover effects
.q-btn {
  transition: var(--theme-transition-all);
  
  &:hover:not(.q-btn--disable) {
    transform: translateY(-1px);
    box-shadow: var(--theme-shadow-medium);
  }
  
  &.q-btn--unelevated {
    &:hover:not(.q-btn--disable) {
      box-shadow: var(--theme-shadow-medium);
    }
  }
  
  &.q-btn--outline {
    &:hover:not(.q-btn--disable) {
      box-shadow: var(--theme-shadow-light);
    }
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .forgot-password-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    h1 {
      font-size: var(--theme-font-xl);
    }
    
    p {
      font-size: var(--theme-font-sm);
    }
  }
  
  .success-card {
    .q-gutter-sm {
      flex-direction: column;
      gap: var(--theme-spacing-sm);
      
      .q-btn {
        width: 100%;
      }
    }
  }
}

@media (max-width: 480px) {
  .forgot-password-page {
    padding: var(--theme-spacing-sm);
  }
  
  .success-card {
    .q-card-section {
      padding: var(--theme-spacing-md);
    }
  }
}
</style>