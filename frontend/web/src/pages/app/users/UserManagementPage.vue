<template>
  <q-page class="users-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">{{ $t('nav.userManagement') }}</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">{{ $t('users.description') }}</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        :label="$t('users.addUser')"
        @click="showAddDialog = true"
        class="theme-radius-lg theme-font-sm"
      />
    </div>

    <!-- Filters -->
    <q-card class="q-mb-md theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-md-4 col-sm-6 col-xs-12">
            <q-input
              v-model="filters.search"
              :placeholder="$t('users.searchPlaceholder')"
              outlined
              dense
              clearable
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-select
              v-model="filters.status"
              :options="statusOptions"
              :label="$t('common.status')"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-select
              v-model="filters.role"
              :options="roleOptions"
              :label="$t('users.role')"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Data Table -->
    <q-card class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
      <q-table
        :rows="users"
        :columns="columns"
        :loading="usersStore.table.loading"
        v-model:pagination="pagination"
        @request="onRequest"
        row-key="id"
        server-side-pagination
        binary-state-sort
      >
        <template v-slot:body-cell-avatar="props">
          <q-td :props="props">
            <q-avatar size="40px" color="primary" text-color="white">
              {{ getInitials(props.row.name) }}
            </q-avatar>
          </q-td>
        </template>

        <template v-slot:body-cell-status="props">
          <q-td :props="props">
            <q-badge
              :color="props.row.is_active ? 'positive' : 'negative'"
              :label="props.row.is_active ? $t('common.active') : $t('common.inactive')"
            />
          </q-td>
        </template>

        <template v-slot:body-cell-roles="props">
          <q-td :props="props">
            <div class="q-gutter-xs">
              <q-chip
                v-for="role in props.row.roles"
                :key="role.id"
                :label="role.name"
                size="sm"
                color="blue-grey-5"
                text-color="white"
              />
            </div>
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn
              flat
              round
              icon="edit"
              size="sm"
              @click="editUser(props.row)"
              class="q-mr-xs theme-radius-md"
            >
              <q-tooltip>{{ $t('common.edit') }}</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              :icon="props.row.is_active ? 'block' : 'check_circle'"
              :color="props.row.is_active ? 'negative' : 'positive'"
              size="sm"
              @click="toggleUserStatus(props.row)"
              class="q-mr-xs theme-radius-md"
            >
              <q-tooltip>{{ props.row.is_active ? $t('users.deactivate') : $t('users.activate') }}</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="delete"
              size="sm"
              color="negative"
              @click="deleteUser(props.row)"
              class="theme-radius-md"
            >
              <q-tooltip>{{ $t('common.delete') }}</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 400px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section>
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? $t('users.editUser') : $t('users.addUser') }}</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveUser" class="q-gutter-md">
            <q-input
              v-model="userForm.name"
              :label="$t('users.name') + ' *'"
              outlined
              :rules="[val => !!val || $t('validation.required')]"
            />
            
            <q-input
              v-model="userForm.username"
              :label="$t('users.username') + ' *'"
              outlined
              :rules="[
                val => !!val || $t('validation.required'),
                val => val.length <= 50 || $t('validation.maxLength', { max: 50 })
              ]"
            />
            
            <q-input
              v-model="userForm.full_name"
              :label="$t('users.fullName') + ' *'"
              outlined
              :rules="[
                val => !!val || $t('validation.required'),
                val => val.length <= 100 || $t('validation.maxLength', { max: 100 })
              ]"
            />
            
            <q-input
              v-model="userForm.email"
              :label="$t('users.email') + ' *'"
              type="email"
              outlined
              :rules="[
                val => !!val || $t('validation.required'),
                val => /.+@.+\..+/.test(val) || $t('validation.email'),
                val => val.length <= 100 || $t('validation.maxLength', { max: 100 })
              ]"
            />
            
            <q-input
              v-if="!editMode"
              v-model="userForm.password"
              :label="$t('users.password') + ' *'"
              type="password"
              outlined
              :rules="[val => !!val || $t('validation.required')]"
            />
            
            <q-input
              v-if="!editMode"
              v-model="userForm.password_confirmation"
              :label="$t('users.confirmPassword') + ' *'"
              type="password"
              outlined
              :rules="[
                val => !!val || $t('validation.required'),
                val => val === userForm.password || $t('validation.passwordMatch')
              ]"
            />
            
            <q-select
              v-model="userForm.roles"
              :options="roleOptions"
              :label="$t('users.roles')"
              multiple
              outlined
              emit-value
              map-options
              use-chips
            />

            <q-toggle
              v-model="userForm.is_active"
              :label="$t('common.active')"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat :label="$t('common.cancel')" @click="closeDialog" class="theme-radius-md theme-font-sm" />
          <q-btn
            color="primary"
            :label="$t('common.save')"
            @click="saveUser"
            :loading="usersStore.table.submitting"
            class="theme-radius-md theme-font-sm"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useI18n } from 'vue-i18n'
import { useUsersStore } from 'src/stores/users'
import { useRolesStore } from 'src/stores/roles'

const $q = useQuasar()
const { t } = useI18n()
const usersStore = useUsersStore()
const rolesStore = useRolesStore()

// Reactive data
const showAddDialog = ref(false)
const editMode = ref(false)

const userForm = ref({
  id: null,
  name: '',
  username: '',
  full_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [],
  is_active: true
})

// Options
const statusOptions = [
  { label: t('common.active'), value: true },
  { label: t('common.inactive'), value: false }
]

// Table columns
const columns = [
  {
    name: 'avatar',
    label: '',
    field: 'avatar',
    align: 'center',
    style: 'width: 60px'
  },
  {
    name: 'name',
    required: true,
    label: t('users.name'),
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'username',
    label: t('users.username'),
    align: 'left',
    field: 'username',
    sortable: true
  },
  {
    name: 'full_name',
    label: t('users.fullName'),
    align: 'left',
    field: 'full_name',
    sortable: true
  },
  {
    name: 'email',
    label: t('users.email'),
    align: 'left',
    field: 'email',
    sortable: true
  },
  {
    name: 'roles',
    label: t('users.roles'),
    align: 'left',
    field: 'roles',
  },
  {
    name: 'status',
    label: t('common.status'),
    align: 'center',
    field: 'is_active',
    sortable: false
  },
  {
    name: 'created_at',
    label: t('common.createdAt'),
    align: 'center',
    field: 'created_at',
    sortable: true,
    format: (val) => new Date(val).toLocaleDateString('id-ID')
  },
  {
    name: 'actions',
    label: t('common.actions'),
    align: 'center'
  }
]

// Computed
const users = computed(() => usersStore.getUsers || [])
const pagination = computed(() => usersStore.pagination)
const filters = computed({
  get: () => usersStore.getFilters,
  set: (value) => usersStore.setFilters(value)
})

const roleOptions = computed(() => rolesStore.getRoleOptions || [])

// Watch filters for auto-search
watch(() => filters.value.search, (newSearch) => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  searchTimeout.value = setTimeout(() => {
    onRequest({ pagination: pagination.value, filter: newSearch })
  }, 500)
}, { deep: true })

watch(() => filters.value.status, () => {
  onRequest({ pagination: pagination.value })
})

watch(() => filters.value.role, () => {
  onRequest({ pagination: pagination.value })
})

const searchTimeout = ref(null)

// Methods
const onRequest = async (props) => {
  await usersStore.fetchUsers(props)
}

const editUser = (user) => {
  editMode.value = true
  
  // Convert role names to role IDs for form
  const roleIds = user.roles ? user.roles.map(role => {
    const roleOption = roleOptions.value.find(option => option.label === role.name)
    return roleOption ? roleOption.value : role.name
  }) : []
  
  userForm.value = {
    ...user,
    password: '',
    password_confirmation: '',
    roles: roleIds
  }
  showAddDialog.value = true
}

const deleteUser = (user) => {
  $q.dialog({
    title: t('users.deleteConfirm'),
    message: `${t('users.deleteMessage')} "${user.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    const success = await usersStore.deleteUser(user.id)
    if (success) {
      await onRequest({ pagination: pagination.value })
    }
  })
}

const toggleUserStatus = async (user) => {
  const newStatus = !user.is_active
  const actionText = newStatus ? t('users.activate') : t('users.deactivate')
  
  $q.dialog({
    title: t('users.statusConfirm'),
    message: `${t('users.statusMessage')} "${user.name}" ${actionText.toLowerCase()}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    const success = await usersStore.updateUser(user.id, {
      is_active: newStatus
    })
    if (success) {
      await onRequest({ pagination: pagination.value })
    }
  })
}

const saveUser = async () => {
  let success = false
  
  // Convert role IDs to role names for backend
  const roleNames = Array.isArray(userForm.value.roles) 
    ? userForm.value.roles.map(roleId => {
        const roleOption = roleOptions.value.find(option => option.value === roleId)
        return roleOption ? roleOption.label : roleId
      })
    : []
  
  // Prepare form data with role names
  const formData = {
    ...userForm.value,
    roles: roleNames
  }
  
  if (editMode.value) {
    // Update existing user
    success = await usersStore.updateUser(userForm.value.id, formData)
  } else {
    // Add new user
    success = await usersStore.createUser(formData)
  }
  
  if (success) {
    await onRequest({ pagination: pagination.value })
    closeDialog()
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  userForm.value = {
    id: null,
    name: '',
    username: '',
    full_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    is_active: true
  }
}

const getInitials = (name) => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .substring(0, 2)
}

// Lifecycle
onMounted(async () => {
  await rolesStore.fetchRoleOptions()
  await onRequest({ pagination: pagination.value })
})
</script>

<style scoped>
.users-page {
  padding: 20px;
  min-height: 100vh;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  padding: 24px;
}

.page-title h4 {
  font-weight: 600;
  margin-bottom: 4px;
}

.page-title p {
  margin-top: 0;
}

/* Table styling */
.q-table {
  border-radius: 12px;
  overflow: hidden;
}

.q-table .q-table__top {
  padding: 16px 24px;
}

.q-table .q-table__bottom {
  padding: 16px 24px;
}

/* Dialog styling */
.q-dialog .q-card {
  max-width: 600px;
  width: 100%;
}

.q-dialog .q-card-section {
  padding: 24px;
}

.q-dialog .q-card-actions {
  padding: 16px 24px;
}

/* Form styling */
.q-field {
  margin-bottom: 16px;
}

/* Button styling */
.q-btn {
  font-weight: 500;
  text-transform: none;
}

/* Avatar styling */
.q-avatar {
  margin-right: 12px;
}

/* Chip styling */
.q-chip {
  font-weight: 500;
}

/* Responsive design */
@media (max-width: 768px) {
  .users-page {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
    padding: 20px;
  }
  
  .q-dialog .q-card {
    margin: 16px;
    max-width: none;
  }
}

@media (max-width: 480px) {
  .users-page {
    padding: 12px;
  }
  
  .page-header {
    padding: 16px;
  }
  
  .q-table .q-table__top,
  .q-table .q-table__bottom {
    padding: 12px 16px;
  }
  
  .q-dialog .q-card-section {
    padding: 20px;
  }
  
  .q-dialog .q-card-actions {
    padding: 12px 20px;
  }
}
</style>