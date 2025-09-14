<template>
  <q-page class="roles-page theme-bg-secondary">
    <!-- Page Header -->
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">{{ $t('roles.title') }}</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">{{ $t('roles.subtitle') }}</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        :label="$t('roles.add')"
        unelevated
        @click="openAddDialog"
        :loading="rolesStore.loading"
        class="theme-radius-lg theme-font-sm"
      />
    </div>

    <!-- Main Content Card -->
    <q-card class="q-mb-md theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <q-card-section>
        <!-- Filters Section -->
        <div class="row q-gutter-md q-mb-md">
          <div class="col-md-4 col-sm-6 col-xs-12">
            <q-input
              v-model="rolesStore.table.filters.search"
              :placeholder="$t('common.search_placeholder')"
              outlined
              dense
              clearable
              debounce="300"
              @update:model-value="onSearch"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
        </div>

        <!-- Data Table -->
        <q-table
          :rows="rolesStore.roles"
          :columns="columns"
          row-key="id"
          :loading="rolesStore.loading"
          v-model:pagination="pagination"
          @request="onRequest"
          binary-state-sort
          server-side-pagination
          :rows-per-page-options="[10, 15, 25, 50]"
          class="roles-table"
        >
          <!-- Permissions Count Column -->
          <template v-slot:body-cell-permissions_count="props">
            <q-td :props="props">
              <q-chip
                color="info"
                text-color="white"
                size="sm"
                :label="props.row.permissions_count || 0"
                icon="security"
              />
            </q-td>
          </template>

          <!-- Created At Column -->
          <template v-slot:body-cell-created_at="props">
            <q-td :props="props">
              <div class="text-caption text-grey-6">
                {{ formatDate(props.row.created_at) }}
              </div>
            </q-td>
          </template>

          <!-- Actions Column -->
          <template v-slot:body-cell-actions="props">
            <q-td :props="props">
              <div class="q-gutter-xs">
                <q-btn
                  flat
                  round
                  color="primary"
                  icon="visibility"
                  size="sm"
                  @click="viewRole(props.row)"
                  class="theme-radius-md"
                >
                  <q-tooltip>{{ $t('common.view') }}</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  color="warning"
                  icon="edit"
                  size="sm"
                  @click="editRole(props.row)"
                  class="theme-radius-md"
                >
                  <q-tooltip>{{ $t('common.edit') }}</q-tooltip>
                </q-btn>
                <q-btn
                  flat
                  round
                  color="negative"
                  icon="delete"
                  size="sm"
                  @click="confirmDeleteRole(props.row)"
                  :disable="props.row.is_system"
                  class="theme-radius-md"
                >
                  <q-tooltip>{{ $t('common.delete') }}</q-tooltip>
                </q-btn>
              </div>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <!-- Add/Edit Role Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 500px; max-width: 600px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? $t('roles.edit') : $t('roles.add') }}</div>
          <q-space />
          <q-btn icon="close" flat round dense @click="closeDialog" />
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-form @submit="saveRole" class="q-gutter-md">
            <q-input
              v-model="roleForm.name"
              :label="$t('roles.name')"
              outlined
              :rules="[
                val => !!val || $t('validation.required'),
                val => val.length >= 2 || $t('validation.min_length', { min: 2 })
              ]"
              :error="!!formErrors.name"
              :error-message="formErrors.name"
              @input="clearFieldError('name')"
            />
            
            <q-input
              v-model="roleForm.description"
              :label="$t('roles.description')"
              outlined
              type="textarea"
              rows="3"
              :rules="[
                val => !val || val.length >= 5 || $t('validation.min_length', { min: 5 })
              ]"
              :error="!!formErrors.description"
              :error-message="formErrors.description"
              @input="clearFieldError('description')"
            />


          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn
            flat
            :label="$t('common.cancel')"
            @click="closeDialog"
            :disable="rolesStore.submitting"
            class="theme-radius-md theme-font-sm"
          />
          <q-btn
            color="primary"
            :label="$t('common.save')"
            @click="saveRole"
            :loading="rolesStore.submitting"
            unelevated
            class="theme-radius-md theme-font-sm"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>



    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="showDeleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">{{ $t('roles.delete_confirmation') }}</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat :label="$t('common.cancel')" @click="showDeleteDialog = false" />
          <q-btn
            color="negative"
            :label="$t('common.delete')"
            @click="deleteRole"
            :loading="rolesStore.submitting"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQuasar, date } from 'quasar'
import { useRouter } from 'vue-router'
import { useRolesStore } from 'src/stores/roles'

const { t } = useI18n()
const $q = useQuasar()
const router = useRouter()
const rolesStore = useRolesStore()

// Reactive data
const showAddDialog = ref(false)
const showDeleteDialog = ref(false)
const editMode = ref(false)
const roleToDelete = ref(null)

const roleForm = reactive({
  id: null,
  name: '',
  description: ''
})

const formErrors = reactive({
  name: '',
  description: ''
})

// Computed properties
const pagination = computed({
  get: () => rolesStore.pagination,
  set: (value) => rolesStore.setPagination(value)
})

// Table columns
const columns = computed(() => [
  {
    name: 'name',
    required: true,
    label: t('roles.name'),
    align: 'left',
    field: 'name',
    sortable: true,
    style: 'width: 200px'
  },
  {
    name: 'description',
    label: t('roles.description'),
    align: 'left',
    field: 'description',
    sortable: true,
    format: (val) => val || '-'
  },
  {
    name: 'permissions_count',
    label: t('roles.permissions_count'),
    align: 'center',
    field: 'permissions_count',
    sortable: true,
    style: 'width: 150px'
  },
  {
    name: 'created_at',
    label: t('common.created_at'),
    align: 'center',
    field: 'created_at',
    sortable: true,
    style: 'width: 150px'
  },
  {
    name: 'actions',
    label: t('common.actions'),
    align: 'center',
    field: 'actions',
    sortable: false,
    style: 'width: 150px'
  }
])

// Methods
const onRequest = async (props) => {
  try {
    // Update local pagination state
    if (props.pagination) {
      pagination.value = {
        ...pagination.value,
        ...props.pagination
      }
    }
    await rolesStore.onTableRequest(props)
  } catch (error) {
    console.error('Error in table request:', error)
    $q.notify({
      type: 'negative',
      message: error.message || t('common.error_occurred'),
      position: 'top'
    })
  }
}

const onSearch = async () => {
  try {
    rolesStore.resetPagination()
    await rolesStore.fetchRoles()
  } catch (error) {
    console.error('Error in search:', error)
  }
}



const openAddDialog = () => {
  editMode.value = false
  resetForm()
  showAddDialog.value = true
}

const viewRole = (role) => {
  router.push({ name: 'role-view', params: { id: role.id } })
}

const editRole = (role) => {
  router.push({ name: 'role-edit', params: { id: role.id } })
}

const confirmDeleteRole = (role) => {
  if (role.is_system) {
    $q.notify({
      type: 'warning',
      message: t('roles.cannot_delete_system_role'),
      position: 'top'
    })
    return
  }
  roleToDelete.value = role
  showDeleteDialog.value = true
}

const saveRole = async () => {
  try {
    clearFormErrors()
    
    // Basic validation
    if (!roleForm.name.trim()) {
      formErrors.name = t('validation.required')
      return
    }

    const roleData = {
      name: roleForm.name.trim(),
      description: roleForm.description.trim()
    }

    if (editMode.value) {
      await rolesStore.updateRole(roleForm.id, roleData)
    } else {
      await rolesStore.createRole(roleData)
    }
    
    closeDialog()
    await rolesStore.fetchRoles()
  } catch (error) {
    console.error('Error saving role:', error)
    
    // Handle validation errors
     if (error.response?.data?.errors) {
       const errors = error.response.data.errors
       Object.keys(errors).forEach(field => {
         if (Object.prototype.hasOwnProperty.call(formErrors, field)) {
           formErrors[field] = errors[field][0]
         }
       })
     } else {
      $q.notify({
        type: 'negative',
        message: error.message || t('common.error_occurred'),
        position: 'top'
      })
    }
  }
}

const deleteRole = async () => {
  try {
    await rolesStore.deleteRole(roleToDelete.value.id)
    showDeleteDialog.value = false
    roleToDelete.value = null
    await rolesStore.fetchRoles()
  } catch (error) {
    console.error('Error deleting role:', error)
    $q.notify({
      type: 'negative',
      message: error.message || t('common.error_occurred'),
      position: 'top'
    })
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  resetForm()
  clearFormErrors()
}

const resetForm = () => {
  roleForm.id = null
  roleForm.name = ''
  roleForm.description = ''
}

const clearFormErrors = () => {
  Object.keys(formErrors).forEach(key => {
    formErrors[key] = ''
  })
}

const clearFieldError = (field) => {
  if (formErrors[field]) {
    formErrors[field] = ''
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return date.formatDate(dateString, 'DD/MM/YYYY HH:mm')
}

// Watchers
watch(() => rolesStore.table.filters.search, () => {
  onSearch()
}, { debounce: 300 })

// Lifecycle
onMounted(async () => {
  try {
    // Initial load with current pagination
    await onRequest({ pagination: pagination.value })
  } catch (error) {
    console.error('Error loading roles:', error)
    $q.notify({
      type: 'negative',
      message: error.message || t('common.error_occurred'),
      position: 'top'
    })
  }
})
</script>

<style scoped>
.roles-page {
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

/* Chip styling */
.q-chip {
  font-weight: 500;
}

/* Permission styling */
.permission-group {
  margin-bottom: 16px;
}

.permission-group .q-item {
  padding: 8px 0;
}

/* Responsive design */
@media (max-width: 768px) {
  .roles-page {
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
  .roles-page {
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