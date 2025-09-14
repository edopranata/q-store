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
            <h4 class="q-ma-none text-weight-bold">{{ $t('roles.view_details') }}</h4>
            <p class="text-grey-6 q-ma-none">{{ $t('roles.view_subtitle') }}</p>
          </div>
        </div>
      </div>
      <div class="col-auto">
        <q-btn
          color="primary"
          icon="edit"
          :label="$t('common.edit')"
          unelevated
          @click="$router.push({ name: 'role-edit', params: { id: route.params.id } })"
          :loading="rolesStore.loading"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="rolesStore.loading" class="text-center q-pa-xl">
      <q-spinner size="60px" color="primary" />
      <div class="q-mt-md text-grey-6">{{ $t('common.loading') }}...</div>
    </div>

    <!-- Main Content -->
    <div v-else-if="rolesStore.role" class="q-gutter-md">
      <!-- Basic Information Card -->
      <q-card>
        <q-card-section>
          <div class="text-h6 q-mb-md">{{ $t('roles.basic_information') }}</div>
          
          <div class="row q-gutter-md">
            <div class="col-md-6 col-sm-12">
              <div class="q-mb-md">
                <div class="text-caption text-grey-6 q-mb-xs">{{ $t('roles.name') }}</div>
                <div class="text-body1 text-weight-medium">{{ rolesStore.role.name }}</div>
              </div>
            </div>
            
            <div class="col-md-6 col-sm-12">
              <div class="q-mb-md">
                <div class="text-caption text-grey-6 q-mb-xs">{{ $t('roles.description') }}</div>
                <div class="text-body1">{{ rolesStore.role.description || '-' }}</div>
              </div>
            </div>
          </div>
          
          <div class="row q-gutter-md">
            <div class="col-md-6 col-sm-12">
              <div class="q-mb-md">
                <div class="text-caption text-grey-6 q-mb-xs">{{ $t('roles.permissions_count') }}</div>
                <div class="text-body1">
                  <q-chip
                    color="info"
                    text-color="white"
                    size="sm"
                    :label="rolesStore.rolePermissions.length || 0"
                    icon="security"
                  />
                </div>
              </div>
            </div>
            
            <div class="col-md-6 col-sm-12">
              <div class="q-mb-md">
                <div class="text-caption text-grey-6 q-mb-xs">{{ $t('common.created_at') }}</div>
                <div class="text-body1">{{ formatDate(rolesStore.role.created_at) }}</div>
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <!-- Permissions Card -->
      <q-card>
        <q-card-section>
          <div class="text-h6 q-mb-md">{{ $t('roles.permissions') }}</div>
          
          <!-- No Permissions State -->
          <div v-if="rolesStore.rolePermissions.length === 0" class="text-center q-pa-md">
            <q-icon name="security" size="48px" color="grey-4" />
            <div class="q-mt-sm text-grey-6">{{ $t('roles.no_permissions') }}</div>
          </div>

          <!-- Permissions Groups -->
          <div v-else class="q-gutter-md">
            <div v-for="(group, groupName) in groupedPermissions" :key="groupName">
              <q-expansion-item
                :label="getGroupLabel(groupName)"
                :caption="`${group.length} ${$t('roles.permissions').toLowerCase()}`"
                default-opened
                header-class="text-weight-medium"
                icon="folder"
              >
                <div class="q-pa-md bg-grey-1">
                  <!-- Permissions List -->
                  <div class="row q-gutter-sm">
                    <div 
                      v-for="permission in group" 
                      :key="permission.id"
                      class="col-md-4 col-sm-6 col-xs-12"
                    >
                      <q-card flat bordered class="permission-card">
                        <q-card-section class="q-pa-sm">
                          <div class="row items-center q-gutter-xs">
                            <q-icon name="check_circle" color="positive" size="16px" />
                            <div class="text-body2 text-weight-medium">
                              {{ permission.description || permission.name }}
                            </div>
                          </div>
                          <div v-if="permission.description && permission.description !== permission.name" class="text-caption text-grey-6 q-mt-xs">
                            {{ permission.name }}
                          </div>
                        </q-card-section>
                      </q-card>
                    </div>
                  </div>
                </div>
              </q-expansion-item>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </div>

    <!-- Error State -->
    <div v-else class="text-center q-pa-xl">
      <q-icon name="error" size="48px" color="negative" />
      <div class="q-mt-sm text-grey-6">{{ $t('common.error_loading_data') }}</div>
      <q-btn
        flat
        color="primary"
        :label="$t('common.retry')"
        @click="loadRoleData"
        class="q-mt-md"
      />
    </div>
  </q-page>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useQuasar, date } from 'quasar'
import { useRoute } from 'vue-router'
import { useRolesStore } from 'src/stores/roles'

const { t } = useI18n()
const $q = useQuasar()
const route = useRoute()
const rolesStore = useRolesStore()

// Computed properties
const groupedPermissions = computed(() => {
  const permissions = rolesStore.rolePermissions || []
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
  
  // Sort groups by name
  const sortedGroups = {}
  Object.keys(groups).sort().forEach(key => {
    sortedGroups[key] = groups[key].sort((a, b) => 
      (a.description || a.name).localeCompare(b.description || b.name)
    )
  })
  
  return sortedGroups
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

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return date.formatDate(dateString, 'DD/MM/YYYY HH:mm')
}

const loadRoleData = async () => {
  try {
    // Fetch role data
    await rolesStore.fetchRole(route.params.id)
    
    // Fetch role permissions
    await rolesStore.fetchRolePermissions(route.params.id)
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
  border: var(--theme-border-light);
  border-radius: var(--theme-radius-md);
  margin-bottom: var(--theme-spacing-xs);
}

.permission-card {
  transition: var(--theme-transition-all);
  
  &:hover {
    box-shadow: var(--theme-shadow-medium);
  }
}

.text-caption {
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.q-chip {
  font-weight: 500;
}
</style>