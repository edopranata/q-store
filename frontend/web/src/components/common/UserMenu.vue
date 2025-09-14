<template>
  <q-btn-dropdown
    flat
    dense
    no-caps
    class="user-menu"
  >
    <template v-slot:label>
      <div class="row items-center no-wrap">
        <q-avatar size="32px" class="q-mr-sm">
          <q-icon name="account_circle" size="md" />
        </q-avatar>
        <div class="text-right">
          <div class="text-weight-bold">{{ user?.name || 'User' }}</div>
          <div class="text-caption">{{ user?.email }}</div>
        </div>
      </div>
    </template>

    <q-list>
      <q-item clickable v-close-popup @click="goToProfile">
        <q-item-section avatar>
          <q-icon name="person" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('user.profile') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item clickable v-close-popup @click="goToSettings">
        <q-item-section avatar>
          <q-icon name="settings" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('user.settings') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-separator />

      <q-item clickable v-close-popup @click="logout">
        <q-item-section avatar>
          <q-icon name="logout" color="negative" />
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-negative">{{ $t('auth.logout') }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-list>
  </q-btn-dropdown>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const router = useRouter()
const authStore = useAuthStore()

// User data from auth store
const user = computed(() => authStore.getUser || { name: 'User', email: 'user@qpos.com' })

// Methods
const goToProfile = () => {
  router.push({ name: 'profile' })
}

const goToSettings = () => {
  router.push({ name: 'settings' })
}

const logout = () => {
  $q.dialog({
    title: 'Confirm Logout',
    message: 'Are you sure you want to logout?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    // Use auth store logout method
    await authStore.logout()
    router.push({ name: 'login' })
  })
}
</script>

<style lang="scss" scoped>
.user-menu {
  .q-btn-dropdown__arrow {
    margin-left: 8px;
  }
}

// Responsive adjustments
@media (max-width: $breakpoint-sm-max) {
  .user-menu {
    :deep(.q-btn__content) {
      .text-right {
        display: none;
      }
    }
  }
}
</style>