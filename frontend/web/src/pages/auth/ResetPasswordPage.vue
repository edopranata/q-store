<template>
  <div class="reset-password-page">
    <div class="page-header text-center q-mb-lg">
      <h1 class="page-title">Reset Password</h1>
      <p class="page-subtitle">
        Masukkan password baru untuk akun Anda
      </p>
    </div>

    <q-card flat bordered class="reset-password-form">
      <q-card-section>
        <q-form @submit="onSubmit">
        <div class="q-mb-md">
          <q-input
            v-model="form.email"
            type="email"
            label="Email"
            outlined
            :rules="emailRules"
            :loading="loading"
            :disable="loading"
            readonly
            class="full-width"
          >
            <template v-slot:prepend>
              <q-icon name="email" />
            </template>
          </q-input>
        </div>

        <div class="q-mb-md">
          <q-input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            label="Password Baru"
            outlined
            :rules="passwordRules"
            :loading="loading"
            :disable="loading"
            autocomplete="new-password"
            class="full-width"
            autofocus
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="showPassword ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="showPassword = !showPassword"
              />
            </template>
          </q-input>
        </div>

        <div class="q-mb-md">
          <q-input
            v-model="form.password_confirmation"
            :type="showPasswordConfirmation ? 'text' : 'password'"
            label="Konfirmasi Password Baru"
            outlined
            :rules="passwordConfirmationRules"
            :loading="loading"
            :disable="loading"
            autocomplete="new-password"
            class="full-width"
            @keyup.enter="onSubmit"
          >
            <template v-slot:prepend>
              <q-icon name="lock" />
            </template>
            <template v-slot:append>
              <q-icon
                :name="showPasswordConfirmation ? 'visibility_off' : 'visibility'"
                class="cursor-pointer"
                @click="showPasswordConfirmation = !showPasswordConfirmation"
              />
            </template>
          </q-input>
        </div>

        <!-- Password Strength Indicator -->
        <div class="password-strength q-mb-lg">
          <div class="strength-label q-mb-xs">
            <span class="text-caption">Kekuatan Password:</span>
            <span :class="passwordStrengthClass" class="text-caption text-weight-medium">
              {{ passwordStrengthText }}
            </span>
          </div>
          <q-linear-progress
            :value="passwordStrengthValue"
            :color="passwordStrengthColor"
            size="4px"
            class="rounded-borders"
          />
          <div class="strength-requirements q-mt-sm">
            <div 
              v-for="requirement in passwordRequirements" 
              :key="requirement.text"
              class="requirement-item"
              :class="{ 'requirement-met': requirement.met }"
            >
              <q-icon 
                :name="requirement.met ? 'check_circle' : 'radio_button_unchecked'" 
                size="sm"
                :color="requirement.met ? 'positive' : 'grey'"
              />
              <span class="requirement-text">{{ requirement.text }}</span>
            </div>
          </div>
        </div>

        <q-btn
          type="submit"
          color="primary"
          label="Reset Password"
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
      </q-card-section>
    </q-card>

    <!-- Success Message -->
    <q-card v-if="resetSuccess" flat bordered class="success-card q-mt-lg">
      <q-card-section class="text-center">
        <q-icon name="check_circle" size="3rem" color="positive" class="q-mb-md" />
        <h3 class="success-title">Password Berhasil Direset!</h3>
        <p class="success-message">
          Password Anda telah berhasil direset. Silakan login dengan password baru Anda.
        </p>
        <q-btn
          color="positive"
          label="Login Sekarang"
          icon="login"
          @click="goToLogin"
          class="q-mt-md"
        />
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from 'stores/auth'
import { Notify } from 'quasar'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const resetSuccess = ref(false)

const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Validation rules
const emailRules = [
  val => !!val || 'Email harus diisi',
  val => /.+@.+\..+/.test(val) || 'Format email tidak valid'
]

const passwordRules = [
  val => !!val || 'Password harus diisi',
  val => val.length >= 8 || 'Password minimal 8 karakter',
  val => /[A-Z]/.test(val) || 'Password harus mengandung huruf besar',
  val => /[a-z]/.test(val) || 'Password harus mengandung huruf kecil',
  val => /[0-9]/.test(val) || 'Password harus mengandung angka'
]

const passwordConfirmationRules = [
  val => !!val || 'Konfirmasi password harus diisi',
  val => val === form.value.password || 'Password tidak cocok'
]

// Password strength computation
const passwordStrength = computed(() => {
  const password = form.value.password
  if (!password) return 0
  
  let score = 0
  
  // Length check
  if (password.length >= 8) score += 1
  if (password.length >= 12) score += 1
  
  // Character variety checks
  if (/[a-z]/.test(password)) score += 1
  if (/[A-Z]/.test(password)) score += 1
  if (/[0-9]/.test(password)) score += 1
  if (/[^A-Za-z0-9]/.test(password)) score += 1
  
  return score
})

const passwordStrengthValue = computed(() => {
  return passwordStrength.value / 6
})

const passwordStrengthColor = computed(() => {
  const strength = passwordStrength.value
  if (strength <= 2) return 'negative'
  if (strength <= 4) return 'warning'
  return 'positive'
})

const passwordStrengthText = computed(() => {
  const strength = passwordStrength.value
  if (strength <= 2) return 'Lemah'
  if (strength <= 4) return 'Sedang'
  return 'Kuat'
})

const passwordStrengthClass = computed(() => {
  const strength = passwordStrength.value
  if (strength <= 2) return 'text-negative'
  if (strength <= 4) return 'text-warning'
  return 'text-positive'
})

const passwordRequirements = computed(() => {
  const password = form.value.password
  return [
    {
      text: 'Minimal 8 karakter',
      met: password.length >= 8
    },
    {
      text: 'Mengandung huruf besar',
      met: /[A-Z]/.test(password)
    },
    {
      text: 'Mengandung huruf kecil',
      met: /[a-z]/.test(password)
    },
    {
      text: 'Mengandung angka',
      met: /[0-9]/.test(password)
    }
  ]
})

// Computed
const isFormValid = computed(() => {
  return form.value.email && 
         form.value.password && 
         form.value.password_confirmation &&
         form.value.password === form.value.password_confirmation &&
         form.value.password.length >= 8 &&
         /[A-Z]/.test(form.value.password) &&
         /[a-z]/.test(form.value.password) &&
         /[0-9]/.test(form.value.password)
})

// Methods
const onSubmit = async () => {
  if (!isFormValid.value) return
  
  loading.value = true
  
  try {
    const success = await authStore.resetPassword({
      token: form.value.token,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })
    
    if (success) {
      resetSuccess.value = true
    }
  } catch (error) {
    console.error('Reset password error:', error)
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  router.push({ name: 'login' })
}

// Lifecycle
onMounted(() => {
  // Get token and email from query parameters
  const token = route.query.token
  const email = route.query.email
  
  if (!token || !email) {
    Notify.create({
      type: 'negative',
      message: 'Link reset password tidak valid atau sudah kadaluarsa',
      position: 'top'
    })
    router.push({ name: 'forgot-password' })
    return
  }
  
  form.value.token = token
  form.value.email = email
  
  // Clear any existing auth state
  if (authStore.isLoggedIn) {
    router.push({ name: 'dashboard' })
  }
})
</script>

<style lang="scss" scoped>
.reset-password-page {
  width: 100%;
  background-color: var(--theme-bg-card);
  padding: var(--theme-spacing-lg);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
}

.page-header {
  text-align: center;
  margin-bottom: var(--theme-spacing-xl);
  
  .page-title {
    font-size: var(--theme-font-xxl);
    font-weight: 600;
    color: var(--theme-primary);
    margin: 0 0 var(--theme-spacing-sm) 0;
    line-height: 1.2;
    transition: var(--theme-transition-all);
  }
  
  .page-subtitle {
    color: var(--theme-text-secondary);
    margin: 0;
    font-size: var(--theme-font-md);
    line-height: 1.5;
    transition: var(--theme-transition-all);
  }
}

.reset-password-form {
  border-radius: var(--theme-radius-md);
  background: var(--theme-bg-secondary);
  padding: var(--theme-spacing-lg);
  border: var(--theme-border-light);
  transition: var(--theme-transition-all);
  
  .q-field {
    margin-bottom: var(--theme-spacing-sm);
    
    &.q-field--outlined {
      .q-field__control {
        border-radius: var(--theme-radius-md);
        background-color: var(--theme-bg-card);
        border-color: var(--theme-border-light);
        transition: var(--theme-transition-all);
      }
    }
  }
  
  &:hover {
    box-shadow: var(--theme-shadow-medium);
  }
}

.password-strength {
  background-color: var(--theme-bg-secondary);
  padding: var(--theme-spacing-md);
  border-radius: var(--theme-radius-md);
  border: var(--theme-border-light);
  transition: var(--theme-transition-all);
  
  .strength-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--theme-spacing-sm);
    color: var(--theme-text-primary);
    font-size: var(--theme-font-md);
  }
  
  .strength-requirements {
    .requirement-item {
      display: flex;
      align-items: center;
      gap: var(--theme-spacing-sm);
      margin-bottom: var(--theme-spacing-xs);
      transition: var(--theme-transition-all);
      
      .requirement-text {
        font-size: var(--theme-font-sm);
        color: var(--theme-text-secondary);
        transition: var(--theme-transition-all);
      }
      
      &.requirement-met {
        .requirement-text {
          color: var(--theme-success);
        }
      }
    }
  }
}

.success-card {
  border-radius: var(--theme-radius-lg);
  border: 2px solid var(--theme-success);
  background: var(--theme-bg-success);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
  
  .success-title {
    font-size: var(--theme-font-xl);
    font-weight: 600;
    color: var(--theme-success);
    margin: 0 0 var(--theme-spacing-sm) 0;
    transition: var(--theme-transition-all);
  }
  
  .success-message {
    color: var(--theme-text-primary);
    line-height: 1.6;
    margin: 0;
    font-size: var(--theme-font-md);
    transition: var(--theme-transition-all);
  }
}

// Universal transition
* {
  transition: var(--theme-transition-all);
}

// Button hover effects
.q-btn {
  border-radius: var(--theme-radius-md);
  transition: var(--theme-transition-all);
  
  &:hover:not(.q-btn--disable) {
    transform: translateY(-1px);
    box-shadow: var(--theme-shadow-medium);
  }
}

// Focus states
.q-field--focused {
  .q-field__control {
    box-shadow: var(--theme-shadow-focus);
    border-color: var(--theme-primary);
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .reset-password-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    margin-bottom: var(--theme-spacing-lg);
    
    .page-title {
      font-size: var(--theme-font-xl);
    }
  }
  
  .reset-password-form {
    padding: var(--theme-spacing-md);
  }
  
  .password-strength {
    padding: var(--theme-spacing-sm);
    
    .strength-label {
      flex-direction: column;
      align-items: flex-start;
      gap: var(--theme-spacing-xs);
    }
  }
}

@media (max-width: 480px) {
  .reset-password-page {
    padding: var(--theme-spacing-sm);
  }
  
  .reset-password-form {
    padding: var(--theme-spacing-sm);
  }
}
</style>