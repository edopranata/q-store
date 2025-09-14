<template>
  <q-page class="q-pa-md">
    <!-- Page Header -->
    <div class="row items-center justify-between q-mb-md">
      <div class="col">
        <div class="row items-center q-gutter-sm">
          <q-btn
            flat
            round
            icon="arrow_back"
            @click="$router.push({ name: 'roles' })"
          />
          <div>
            <h4 class="q-ma-none text-weight-bold">{{ $t('roles.edit') }}</h4>
            <p class="text-grey-6 q-ma-none">{{ $t('roles.edit_subtitle') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Card -->
    <q-card>
      <q-card-section>
        <q-form @submit="saveRole" class="q-gutter-md">
          <!-- Basic Role Information -->
          <div class="row q-gutter-md">
            <div class="col-md-6 col-sm-12">
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
            </div>
            <div class="col-md-6 col-sm-12">
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
            </div>
          </div>

          <!-- Permissions Section -->
          <div class="q-mt-lg">
            <div class="text-h6 q-mb-md">{{ $t('roles.permissions') }}</div>
            
            <!-- Loading State -->
            <div v-if="rolesStore.loading" class="text-center q-pa-md">
              <q-spinner size="40px" />
              <div class="q-mt-sm text-grey-6">{{ $t('common.loading') }}...</div>
            </div>

            <!-- Permissions Groups -->
            <div v-else class="q-gutter-md">
              <div v-for="(group, groupName) in groupedPermissions" :key="groupName">
                <q-expansion-item
                  :label="getGroupLabel(groupName)"
                  :caption="`${group.length} ${$t('roles.permissions').toLowerCase()}`"
                  default-opened
                  header-class="text-weight-medium"
                >
                  <div class="q-pa-md">
                    <!-- Select All for Group -->
                    <div class="row items-center q-mb-md">
                      <q-checkbox
                        :model-value="isGroupSelected(group)"
                        :indeterminate="isGroupIndeterminate(group)"
                        @update:model-value="toggleGroup(group, $event)"
                        :label="$t('common.select_all')"
                        class="text-weight-medium"
                      />
                    </div>
                    
                    <!-- Individual Permissions -->
                    <div class="row q-gutter-sm">
                      <div 
                        v-for="permission in group" 
                        :key="permission.id"
                        class="col-md-4 col-sm-6 col-xs-12"
                      >
                        <q-checkbox
                          v-model="selectedPermissions"
                          :val="permission.id"
                          :label="permission.description || permission.name"
                          class="full-width"
                        />
                      </div>
                    </div>
                  </div>
                </q-expansion-item>
              </div>
            </div>
          </div>
        </q-form>
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          flat
          :label="$t('common.cancel')"
          @click="$router.push({ name: 'roles' })"
          :disable="rolesStore.submitting"
        />
        <q-btn
          color="primary"
          :label="$t('common.save')"
          @click="saveRole"
          :loading="rolesStore.submitting"
          unelevated
        />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQuasar } from 'quasar'
import { useRouter, useRoute } from 'vue-router'
import { useRolesStore } from 'src/stores/roles'

const { t } = useI18n()
const $q = useQuasar()
const router = useRouter()
const route = useRoute()
const rolesStore = useRolesStore()

// Reactive data
const roleForm = reactive({
  name: '',
  description: ''
})

const formErrors = reactive({
  name: '',
  description: ''
})

const selectedPermissions = ref([])

// Computed properties
const groupedPermissions = computed(() => {
  const permissions = rolesStore.getPermissions || []
  const groups = {}
  
  permissions.forEach(permission => {
    // Extract group name from permission name
    // Example: api.v1.product.products.index -> product
    const parts = permission.name.split('.')
    let groupName = 'other'
    
    if (parts.length >= 3) {
      groupName = parts[2] // Get the module name (product, auth, etc.)
    }
    
    if (!groups[groupName]) {
      groups[groupName] = []
    }
    
    groups[groupName].push(permission)
  })
  
  return groups
})

// Methods
const getGroupLabel = (groupName) => {
  const labels = {
    'auth': t('permissions.groups.auth'),
    'product': t('permissions.groups.product'),
    'master': t('permissions.groups.master'),
    'transaction': t('permissions.groups.transaction'),
    'report': t('permissions.groups.report'),
    'stats': t('permissions.groups.stats'),
    'options': t('permissions.groups.options'),
    'other': t('permissions.groups.other')
  }
  
  return labels[groupName] || groupName.charAt(0).toUpperCase() + groupName.slice(1)
}

const isGroupSelected = (group) => {
  return group.every(permission => selectedPermissions.value.includes(permission.id))
}

const isGroupIndeterminate = (group) => {
  const selectedCount = group.filter(permission => 
    selectedPermissions.value.includes(permission.id)
  ).length
  
  return selectedCount > 0 && selectedCount < group.length
}

const toggleGroup = (group, selected) => {
  if (selected) {
    // Add all permissions in group
    group.forEach(permission => {
      if (!selectedPermissions.value.includes(permission.id)) {
        selectedPermissions.value.push(permission.id)
      }
    })
  } else {
    // Remove all permissions in group
    group.forEach(permission => {
      const index = selectedPermissions.value.indexOf(permission.id)
      if (index > -1) {
        selectedPermissions.value.splice(index, 1)
      }
    })
  }
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

    // Update role basic info
    await rolesStore.updateRole(route.params.id, roleData)
    
    // Update role permissions
    await rolesStore.updateRolePermissions(route.params.id, selectedPermissions.value)
    
    // Navigate back to roles list
    router.push({ name: 'roles' })
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

const loadRoleData = async () => {
  try {
    // Fetch role data
    await rolesStore.fetchRole(route.params.id)
    
    // Fetch all permissions
    await rolesStore.fetchPermissions()
    
    // Fetch role permissions
    await rolesStore.fetchRolePermissions(route.params.id)
    
    // Populate form
    if (rolesStore.role) {
      roleForm.name = rolesStore.role.name || ''
      roleForm.description = rolesStore.role.description || ''
    }
    
    // Set selected permissions
    selectedPermissions.value = rolesStore.rolePermissions.map(p => p.id) || []
  } catch (error) {
    console.error('Error loading role data:', error)
    $q.notify({
      type: 'negative',
      message: error.message || t('common.error_occurred'),
      position: 'top'
    })
  }
}

// Lifecycle
onMounted(() => {
  loadRoleData()
})
</script>

<style lang="scss" scoped>
.q-expansion-item {
  border: 1px solid $grey-4;
  border-radius: 8px;
  margin-bottom: 8px;
}

.q-expansion-item__content {
  background-color: $grey-1;
}

.q-checkbox {
  margin-bottom: 8px;
}

.text-caption {
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
</style>