<template>
  <div class="login-page theme-bg-card">
    <!-- Page Header -->
    <div class="page-header q-mb-xl">
      <h1 class="text-h4 text-weight-bold q-ma-none theme-text-primary theme-font-xxl">Masuk ke Akun</h1>
      <p class="text-body2 q-mt-sm theme-text-secondary theme-font-md">Silakan masukkan kredensial Anda untuk melanjutkan</p>
    </div>

      <q-form @submit="onSubmit" class="login-form">
        <div class="q-mb-md">
          <q-input
            v-model="form.username"
            type="text"
            label="Username atau Email"
            outlined
            :rules="usernameRules"
            :loading="loading"
            :disable="loading"
            autocomplete="username"
            class="full-width"
          >
            <template v-slot:prepend>
              <q-icon name="person" />
            </template>
          </q-input>
        </div>

        <div class="q-mb-md">
          <q-input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            label="Password"
            outlined
            :rules="passwordRules"
            :loading="loading"
            :disable="loading"
            autocomplete="current-password"
            class="full-width"
            @keyup.enter="onSubmit"
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

        <div class="row items-center justify-between q-mb-lg">
          <q-checkbox
            v-model="form.remember"
            label="Ingat saya"
            :disable="loading"
            class="theme-text-primary theme-font-sm"
          />
          <q-btn
            flat
            no-caps
            color="primary"
            label="Lupa password?"
            :disable="loading"
            @click="goToForgotPassword"
            class="theme-font-sm theme-radius-sm"
          />
        </div>

        <q-btn
          type="submit"
          color="primary"
          label="Masuk"
          size="lg"
          :loading="loading"
          :disable="loading || !isFormValid"
          class="full-width q-mb-md theme-font-md theme-radius-md"
        />

        <div class="text-center">
          <p class="theme-text-secondary theme-font-sm">
            Belum punya akun?
            <q-btn
              flat
              no-caps
              color="primary"
              label="Hubungi Administrator"
              :disable="loading"
              @click="contactAdmin"
              class="theme-font-sm theme-radius-sm"
            />
          </p>
        </div>
      </q-form>

    <!-- Demo Credentials -->
    <q-expansion-item
      icon="info"
      label="Demo Credentials"
      header-class="text-primary theme-font-md"
      class="demo-credentials q-mt-lg theme-bg-secondary theme-radius-md"
    >
      <q-card flat bordered class="theme-bg-card theme-border-light">
        <q-card-section class="q-pa-md">
          <div class="q-mb-sm theme-text-secondary theme-font-sm">Gunakan kredensial berikut untuk demo:</div>
          <div class="demo-item q-mb-xs">
            <span class="theme-text-primary theme-font-sm"><strong>Admin:</strong> qpos_admin / admin123</span>
            <q-btn
              flat
              dense
              icon="content_copy"
              size="sm"
              @click="copyDemoCredentials('qpos_admin', 'admin123')"
              class="q-ml-sm theme-radius-sm"
            />
          </div>
          <div class="demo-item q-mb-xs">
            <span class="theme-text-primary theme-font-sm"><strong>Kasir:</strong> qpos_cashier / cashier123</span>
            <q-btn
              flat
              dense
              icon="content_copy"
              size="sm"
              @click="copyDemoCredentials('qpos_cashier', 'cashier123')"
              class="q-ml-sm theme-radius-sm"
            />
          </div>
          <div class="demo-item">
            <span class="theme-text-primary theme-font-sm"><strong>Manager:</strong> qpos_manager / manager123</span>
            <q-btn
              flat
              dense
              icon="content_copy"
              size="sm"
              @click="copyDemoCredentials('qpos_manager', 'manager123')"
              class="q-ml-sm theme-radius-sm"
            />
          </div>
        </q-card-section>
      </q-card>
    </q-expansion-item>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from 'stores/auth'
import { Notify, copyToClipboard } from 'quasar'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const showPassword = ref(false)
const form = ref({
  username: '',
  password: '',
  remember: false
})

// Validation rules
const usernameRules = [
  val => !!val || 'Username atau email harus diisi',
  val => val.length >= 3 || 'Username minimal 3 karakter'
]

const passwordRules = [
  val => !!val || 'Password harus diisi',
  val => val.length >= 6 || 'Password minimal 6 karakter'
]

// Computed
const isFormValid = computed(() => {
  return form.value.username && 
         form.value.password && 
         form.value.username.length >= 3 && 
         form.value.password.length >= 6
})

// Methods
const onSubmit = async () => {
  if (!isFormValid.value) return
  
  loading.value = true
  
  try {
    const success = await authStore.login({
      username: form.value.username,
      password: form.value.password,
      remember: form.value.remember
    })
    
    if (success) {
      // Redirect to intended page or dashboard
      const redirectTo = route.query.redirect || '/app/dashboard'
      router.push(redirectTo)
    }
  } catch (error) {
    console.error('Login error:', error)
  } finally {
    loading.value = false
  }
}

const goToForgotPassword = () => {
  router.push({ name: 'forgot-password' })
}

const contactAdmin = () => {
  Notify.create({
    type: 'info',
    message: 'Silakan hubungi administrator untuk membuat akun baru',
    position: 'top',
    actions: [
      {
        label: 'OK',
        color: 'white'
      }
    ]
  })
}

const copyDemoCredentials = async (username, password) => {
  form.value.username = username
  form.value.password = password
  
  try {
    await copyToClipboard(`Username: ${username}\nPassword: ${password}`)
    Notify.create({
      type: 'positive',
      message: 'Kredensial demo telah disalin dan diisi ke form',
      position: 'top'
    })
  } catch {
    Notify.create({
      type: 'positive',
      message: 'Kredensial demo telah diisi ke form',
      position: 'top'
    })
  }
}

// Lifecycle
onMounted(() => {
  // Clear any existing auth state
  if (authStore.isLoggedIn) {
    router.push({ name: 'dashboard' })
  }
  
  // Focus on username input
  setTimeout(() => {
    const usernameInput = document.querySelector('input[type="text"]')
    if (usernameInput) {
      usernameInput.focus()
    }
  }, 100)
})
</script>

<style lang="scss" scoped>
.login-page {
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
  }
  
  p {
    color: var(--theme-text-secondary);
    font-size: var(--theme-font-md);
  }
}

.login-form {
  :deep(.q-field) {
    margin-bottom: var(--theme-spacing-sm);
    
    &.q-field--outlined {
        .q-field__control {
          border-radius: var(--theme-radius-md);
          background-color: var(--theme-bg-secondary);
          border-color: var(--theme-border-light);
          transition: var(--theme-transition-all);
        }
      
      &.q-field--focused .q-field__control {
        border-color: var(--theme-primary);
        box-shadow: 0 0 0 2px var(--theme-primary-alpha);
      }
    }
    
    .q-field__label {
      color: var(--theme-text-secondary);
    }
    
    .q-field__native {
      color: var(--theme-text-primary);
    }
  }
  
  :deep(.q-btn) {
    border-radius: var(--theme-radius-md);
    font-weight: 500;
    text-transform: none;
    transition: var(--theme-transition-all);
    
    &:hover:not(.q-btn--disable) {
      transform: translateY(-1px);
      box-shadow: var(--theme-shadow-medium);
    }
  }
  
  :deep(.q-checkbox) {
    .q-checkbox__label {
      color: var(--theme-text-primary);
      font-size: var(--theme-font-sm);
    }
  }
}

.demo-credentials {
  border: 1px solid var(--theme-border-light);
  border-radius: var(--theme-radius-md);
  background: var(--theme-bg-secondary);
  transition: var(--theme-transition-all);
  
  :deep(.q-expansion-item__header) {
    background-color: var(--theme-bg-secondary);
    color: var(--theme-text-primary);
    border-radius: var(--theme-radius-md) var(--theme-radius-md) 0 0;
  }
  
  .demo-item {
    font-size: var(--theme-font-sm);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--theme-spacing-xs) 0;
    
    span {
      flex: 1;
      color: var(--theme-text-primary);
    }
    
    strong {
      color: var(--theme-text-primary);
      font-weight: 600;
    }
  }
  
  :deep(.q-card) {
    background-color: var(--theme-bg-card);
    border-color: var(--theme-border-light);
  }
}

// Focus states
.q-field--focused {
  .q-field__control {
    box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.2);
  }
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

// Button hover effects
.q-btn {
  transition: var(--theme-transition-all);
  
  &:hover:not(.q-btn--disable) {
    transform: translateY(-1px);
  }
  
  &.q-btn--unelevated {
    &:hover:not(.q-btn--disable) {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .login-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    h1 {
      font-size: var(--theme-font-xl);
    }
  }
  
  .demo-credentials {
    .demo-item {
      font-size: var(--theme-font-xs);
      
      span {
        word-break: break-all;
      }
    }
  }
}

@media (max-width: 480px) {
  .login-page {
    padding: var(--theme-spacing-sm);
  }
}
</style>