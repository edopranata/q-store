<template>
  <q-list class="navigation-menu">
    <!-- Dashboard -->
    <q-item
      clickable
      :active="$route.name === 'dashboard'"
      @click="$router.push({ name: 'dashboard' })"
      class="nav-item"
    >
      <q-item-section avatar>
        <q-icon name="dashboard" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ $t('nav.dashboard') }}</q-item-label>
      </q-item-section>
    </q-item>

    <!-- POS Transaction -->
    <q-item
      clickable
      :active="$route.name === 'pos'"
      @click="$router.push({ name: 'pos' })"
      class="nav-item"
    >
      <q-item-section avatar>
        <q-icon name="point_of_sale" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ $t('nav.pos') }}</q-item-label>
      </q-item-section>
    </q-item>

    <!-- Master Data -->
    <q-expansion-item
      ref="masterDataExpansion"
      icon="storage"
      :label="$t('nav.masterData')"
      :model-value="expandedMenus.masterData"
      @update:model-value="updateExpandedState('masterData', $event)"
      class="nav-expansion"
      :class="{ 'nav-expansion--active': isParentActive(['categories', 'units', 'suppliers', 'customers', 'warehouses']) }"
    >
      <q-item
        clickable
        :active="$route.name === 'categories'"
        @click="$router.push({ name: 'categories' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="category" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.categories') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'units'"
        @click="$router.push({ name: 'units' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="straighten" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.units') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'suppliers'"
        @click="$router.push({ name: 'suppliers' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="local_shipping" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.suppliers') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'customers'"
        @click="$router.push({ name: 'customers' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="people" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.customers') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'warehouses'"
        @click="$router.push({ name: 'warehouses' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="warehouse" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.warehouses') }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-expansion-item>

    <!-- Products -->
    <q-item
      clickable
      :active="$route.name === 'products'"
      @click="$router.push({ name: 'products' })"
      class="nav-item"
    >
      <q-item-section avatar>
        <q-icon name="inventory" />
      </q-item-section>
      <q-item-section>
        <q-item-label>{{ $t('nav.products') }}</q-item-label>
      </q-item-section>
    </q-item>

    <!-- Transactions -->
    <q-expansion-item
      ref="transactionsExpansion"
      icon="receipt_long"
      :label="$t('nav.transactions')"
      :model-value="expandedMenus.transactions"
      @update:model-value="updateExpandedState('transactions', $event)"
      class="nav-expansion"
      :class="{ 'nav-expansion--active': isParentActive(['sales', 'purchases', 'stock-adjustments']) }"
    >
      <q-item
        clickable
        :active="$route.name === 'sales'"
        @click="$router.push({ name: 'sales' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="sell" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.sales') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'purchases'"
        @click="$router.push({ name: 'purchases' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="shopping_cart" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.purchases') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'stock-adjustments'"
        @click="$router.push({ name: 'stock-adjustments' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="tune" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.stockAdjustments') }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-expansion-item>

    <!-- Reports -->
    <q-expansion-item
      ref="reportsExpansion"
      icon="assessment"
      :label="$t('nav.reports')"
      :model-value="expandedMenus.reports"
      @update:model-value="updateExpandedState('reports', $event)"
      class="nav-expansion"
      :class="{ 'nav-expansion--active': isParentActive(['sales-report', 'inventory-report']) }"
    >
      <q-item
        clickable
        :active="$route.name === 'sales-report'"
        @click="$router.push({ name: 'sales-report' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="trending_up" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.salesReport') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'inventory-report'"
        @click="$router.push({ name: 'inventory-report' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="inventory_2" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.inventoryReport') }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-expansion-item>

    <!-- Settings -->
    <q-expansion-item
      ref="settingsExpansion"
      icon="settings"
      :label="$t('nav.settings')"
      :model-value="expandedMenus.settings"
      @update:model-value="updateExpandedState('settings', $event)"
      class="nav-expansion"
      :class="{ 'nav-expansion--active': isParentActive(['users', 'roles']) }"
    >
      <q-item
        clickable
        :active="$route.name === 'users'"
        @click="$router.push({ name: 'users' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="people" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.usersManagement') }}</q-item-label>
        </q-item-section>
      </q-item>

      <q-item
        clickable
        :active="$route.name === 'roles'"
        @click="$router.push({ name: 'roles' })"
        class="nav-sub-item"
      >
        <q-item-section avatar>
          <q-icon name="admin_panel_settings" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ $t('nav.rolesManagement') }}</q-item-label>
        </q-item-section>
      </q-item>
    </q-expansion-item>
  </q-list>
</template>

<script setup>
import { reactive, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { LocalStorage } from 'quasar'

const route = useRoute()

// State untuk expanded menus
const expandedMenus = reactive({
  masterData: false,
  transactions: false,
  reports: false,
  settings: false
})

// Key untuk LocalStorage
const EXPANDED_MENUS_KEY = 'nav-expanded-menus'

// Mapping route names ke parent menu
const routeToParentMap = {
  categories: 'masterData',
  units: 'masterData',
  suppliers: 'masterData',
  customers: 'masterData',
  warehouses: 'masterData',
  sales: 'transactions',
  purchases: 'transactions',
  'stock-adjustments': 'transactions',
  'sales-report': 'reports',
  'inventory-report': 'reports',
  users: 'settings',
  roles: 'settings'
}

// Function untuk mengecek apakah parent menu aktif
const isParentActive = (childRoutes) => {
  return childRoutes.includes(route.name)
}

// Function untuk update expanded state
const updateExpandedState = (menuKey, isExpanded) => {
  expandedMenus[menuKey] = isExpanded
  saveExpandedState()
}

// Function untuk save state ke LocalStorage
const saveExpandedState = () => {
  LocalStorage.set(EXPANDED_MENUS_KEY, expandedMenus)
}

// Function untuk load state dari LocalStorage
const loadExpandedState = () => {
  const saved = LocalStorage.getItem(EXPANDED_MENUS_KEY)
  if (saved) {
    Object.assign(expandedMenus, saved)
  }
}

// Function untuk auto-expand parent menu berdasarkan route aktif
const autoExpandParentMenu = () => {
  const currentRoute = route.name
  const parentMenu = routeToParentMap[currentRoute]
  
  if (parentMenu) {
    expandedMenus[parentMenu] = true
    saveExpandedState()
  }
}

// Watch route changes untuk auto-expand parent menu
watch(
  () => route.name,
  () => {
    autoExpandParentMenu()
  },
  { immediate: true }
)

// Load state saat component mounted
onMounted(() => {
  loadExpandedState()
  autoExpandParentMenu()
})
</script>

<style lang="scss" scoped>
.navigation-menu {
  .nav-item {
    margin: 4px 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
    
    &:hover {
      background-color: rgba($primary, 0.1);
      transform: translateX(4px);
    }
    
    &.q-item--active {
      background-color: rgba($primary, 0.15);
      color: $primary;
      font-weight: 600;
      
      .q-icon {
        color: $primary;
      }
    }
  }
  
  .nav-sub-item {
    margin: 2px 16px 2px 24px;
    border-radius: 6px;
    transition: all 0.3s ease;
    
    &:hover {
      background-color: rgba($primary, 0.08);
      transform: translateX(4px);
    }
    
    &.q-item--active {
      background-color: rgba($primary, 0.12);
      color: $primary;
      font-weight: 500;
      
      .q-icon {
        color: $primary;
      }
    }
  }
  
  .nav-expansion {
    margin: 4px 8px;
    border-radius: 8px;
    
    &.nav-expansion--active {
      :deep(.q-expansion-item__toggle) {
        .q-item {
          background-color: rgba($primary, 0.15);
          color: $primary;
          font-weight: 600;
          
          .q-icon {
            color: $primary;
          }
        }
      }
    }
    
    :deep(.q-expansion-item__container) {
      .q-item {
        border-radius: 8px;
        transition: all 0.3s ease;
        
        &:hover {
          background-color: rgba($primary, 0.1);
        }
      }
    }
  }
}
</style>