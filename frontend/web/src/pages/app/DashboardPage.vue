<template>
  <q-page class="dashboard-page theme-bg-secondary">
    <!-- Page Header -->
    <div class="page-header theme-bg-card theme-border-light q-pa-md">
      <div class="row items-center justify-between">
        <div>
          <h1 class="page-title theme-text-primary theme-font-xxl">Dashboard</h1>
          <p class="page-subtitle theme-text-secondary theme-font-md">Ringkasan aktivitas bisnis Anda hari ini</p>
        </div>
        <div class="header-actions">
          <q-btn
            color="primary"
            icon="refresh"
            label="Refresh"
            class="theme-radius-lg theme-font-sm"
            @click="refreshData"
            :loading="loading"
          />
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-section q-mt-md">
      <div class="row q-col-gutter-md">
        <div 
          v-for="stat in stats" 
          :key="stat.id"
          class="col-12 col-sm-6 col-md-3"
        >
          <q-card class="stat-card theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
            <q-card-section>
              <div class="row items-center no-wrap">
                <div class="col">
                  <div class="stat-value theme-text-primary theme-font-xl">{{ formatNumber(stat.value) }}</div>
                  <div class="stat-label theme-text-secondary theme-font-sm">{{ stat.label }}</div>
                  <div class="stat-change theme-font-xs" :class="stat.changeClass">
                    <q-icon :name="stat.changeIcon" size="sm" />
                    {{ stat.change }}
                  </div>
                </div>
                <div class="col-auto">
                  <q-icon 
                    :name="stat.icon" 
                    :color="stat.color" 
                    size="2.5rem"
                  />
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section q-mt-md">
      <div class="row q-col-gutter-md">
        <!-- Sales Chart -->
        <div class="col-12 col-md-8">
          <q-card class="chart-card theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
            <q-card-section>
              <div class="row items-center justify-between q-mb-md">
                <h3 class="chart-title theme-text-primary theme-font-lg">Penjualan 7 Hari Terakhir</h3>
                <q-btn-toggle
                  v-model="salesChartPeriod"
                  :options="chartPeriodOptions"
                  color="primary"
                  size="sm"
                  @update:model-value="updateSalesChart"
                />
              </div>
              <div class="chart-container">
                <canvas ref="salesChart" width="400" height="200"></canvas>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Top Products -->
        <div class="col-12 col-md-4">
          <q-card class="chart-card theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
            <q-card-section>
              <h3 class="chart-title theme-text-primary theme-font-lg q-mb-md">Produk Terlaris</h3>
              <div class="top-products">
                <div 
                  v-for="(product, index) in topProducts" 
                  :key="product.id"
                  class="product-item q-mb-md"
                >
                  <div class="row items-center no-wrap">
                    <div class="col-auto q-mr-md">
                      <q-avatar 
                        :color="getRankColor(index)" 
                        text-color="white" 
                        size="md"
                      >
                        {{ index + 1 }}
                      </q-avatar>
                    </div>
                    <div class="col">
                      <div class="product-name theme-text-primary theme-font-sm">{{ product.name }}</div>
                      <div class="product-sales theme-text-secondary theme-font-xs">{{ product.sold }} terjual</div>
                    </div>
                    <div class="col-auto">
                      <div class="product-revenue theme-text-accent theme-font-sm">{{ formatCurrency(product.revenue) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="activities-section q-mt-md">
      <div class="row q-col-gutter-md">
        <!-- Recent Transactions -->
        <div class="col-12 col-md-6">
          <q-card class="activity-card theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
            <q-card-section>
              <div class="row items-center justify-between q-mb-md">
                <h3 class="chart-title theme-text-primary theme-font-lg">Transaksi Terbaru</h3>
                <q-btn
                  flat
                  color="primary"
                  label="Lihat Semua"
                  @click="goToTransactions"
                />
              </div>
              <q-list separator>
                <q-item 
                  v-for="transaction in recentTransactions" 
                  :key="transaction.id"
                  clickable
                  @click="viewTransaction(transaction.id)"
                >
                  <q-item-section avatar>
                    <q-avatar 
                      :color="getTransactionColor(transaction.type)" 
                      text-color="white"
                    >
                      <q-icon :name="getTransactionIcon(transaction.type)" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="theme-text-primary theme-font-sm">{{ transaction.customer }}</q-item-label>
                    <q-item-label caption class="theme-text-secondary theme-font-xs">{{ transaction.items }} item(s)</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label class="theme-text-accent theme-font-sm">{{ formatCurrency(transaction.total) }}</q-item-label>
                    <q-item-label caption class="theme-text-secondary theme-font-xs">{{ formatTime(transaction.time) }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

        <!-- Low Stock Alert -->
        <div class="col-12 col-md-6">
          <q-card class="activity-card theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
            <q-card-section>
              <div class="row items-center justify-between q-mb-md">
                <h3 class="chart-title theme-text-primary theme-font-lg">Stok Menipis</h3>
                <q-btn
                  flat
                  color="primary"
                  label="Kelola Stok"
                  @click="goToProducts"
                />
              </div>
              <q-list separator>
                <q-item 
                  v-for="product in lowStockProducts" 
                  :key="product.id"
                  clickable
                  @click="viewProduct(product.id)"
                >
                  <q-item-section avatar>
                    <q-avatar color="warning" text-color="white">
                      <q-icon name="warning" />
                    </q-avatar>
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="theme-text-primary theme-font-sm">{{ product.name }}</q-item-label>
                    <q-item-label caption class="theme-text-secondary theme-font-xs">{{ product.category }}</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-item-label class="theme-text-warning theme-font-sm">{{ product.stock }} {{ product.unit }}</q-item-label>
                    <q-item-label caption class="theme-text-warning theme-font-xs">Stok rendah</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from 'stores/app'
import { useQuasar } from 'quasar'
import { dashboardService } from 'src/services'

const router = useRouter()
const appStore = useAppStore()
const $q = useQuasar()

// Reactive data
const loading = ref(false)
const salesChart = ref(null)
const salesChartPeriod = ref('7d')

// Chart period options
const chartPeriodOptions = [
  { label: '7 Hari', value: '7d' },
  { label: '30 Hari', value: '30d' },
  { label: '3 Bulan', value: '3m' }
]

// Mock data - replace with API calls
const stats = ref([
  {
    id: 1,
    label: 'Penjualan Hari Ini',
    value: 2450000,
    change: '+12.5%',
    changeClass: 'text-positive',
    changeIcon: 'trending_up',
    icon: 'point_of_sale',
    color: 'primary'
  },
  {
    id: 2,
    label: 'Transaksi',
    value: 156,
    change: '+8.2%',
    changeClass: 'text-positive',
    changeIcon: 'trending_up',
    icon: 'receipt',
    color: 'secondary'
  },
  {
    id: 3,
    label: 'Produk Terjual',
    value: 324,
    change: '+15.3%',
    changeClass: 'text-positive',
    changeIcon: 'trending_up',
    icon: 'inventory',
    color: 'accent'
  },
  {
    id: 4,
    label: 'Pelanggan Baru',
    value: 23,
    change: '-2.1%',
    changeClass: 'text-negative',
    changeIcon: 'trending_down',
    icon: 'people',
    color: 'positive'
  }
])

const topProducts = ref([
  { id: 1, name: 'Kopi Arabica Premium', sold: 45, revenue: 675000 },
  { id: 2, name: 'Roti Bakar Keju', sold: 38, revenue: 380000 },
  { id: 3, name: 'Jus Jeruk Segar', sold: 32, revenue: 320000 },
  { id: 4, name: 'Nasi Goreng Spesial', sold: 28, revenue: 420000 },
  { id: 5, name: 'Es Teh Manis', sold: 25, revenue: 125000 }
])

const recentTransactions = ref([
  {
    id: 'TRX001',
    customer: 'Ahmad Wijaya',
    items: 3,
    total: 85000,
    time: new Date(Date.now() - 5 * 60000),
    type: 'sale'
  },
  {
    id: 'TRX002',
    customer: 'Siti Nurhaliza',
    items: 2,
    total: 45000,
    time: new Date(Date.now() - 12 * 60000),
    type: 'sale'
  },
  {
    id: 'TRX003',
    customer: 'Budi Santoso',
    items: 5,
    total: 125000,
    time: new Date(Date.now() - 18 * 60000),
    type: 'sale'
  }
])

const lowStockProducts = ref([
  { id: 1, name: 'Gula Pasir', category: 'Bahan Baku', stock: 5, unit: 'kg' },
  { id: 2, name: 'Kopi Bubuk', category: 'Minuman', stock: 2, unit: 'kg' },
  { id: 3, name: 'Roti Tawar', category: 'Makanan', stock: 8, unit: 'pcs' },
  { id: 4, name: 'Susu UHT', category: 'Minuman', stock: 12, unit: 'pcs' }
])

// Methods
const formatNumber = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

const formatTime = (date) => {
  return new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}

const getRankColor = (index) => {
  const colors = ['primary', 'secondary', 'accent', 'positive', 'info']
  return colors[index] || 'grey'
}

const getTransactionColor = (type) => {
  return type === 'sale' ? 'positive' : 'info'
}

const getTransactionIcon = (type) => {
  return type === 'sale' ? 'shopping_cart' : 'inventory'
}

const loadDashboardData = async () => {
  loading.value = true
  try {
    const result = await dashboardService.getDashboardData()
    
    if (result.success) {
      // Update stats
      if (result.data.stats && result.data.stats.length > 0) {
        stats.value = result.data.stats.map(stat => ({
          ...stat,
          changeClass: stat.change && stat.change.startsWith('+') ? 'text-positive' : 'text-negative',
          changeIcon: stat.change && stat.change.startsWith('+') ? 'trending_up' : 'trending_down'
        }))
      }
      
      // Update top products
      if (result.data.topProducts) {
        topProducts.value = result.data.topProducts
      }
      
      // Update recent transactions
      if (result.data.recentTransactions) {
        recentTransactions.value = result.data.recentTransactions.map(transaction => ({
          ...transaction,
          time: new Date(transaction.time || transaction.created_at)
        }))
      }
      
      // Update low stock products
      if (result.data.lowStockProducts) {
        lowStockProducts.value = result.data.lowStockProducts
      }
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Gagal memuat data dashboard',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error loading dashboard data:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat memuat data dashboard',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const refreshData = async () => {
  await loadDashboardData()
}

const updateSalesChart = async () => {
  try {
    const result = await dashboardService.getSalesChartData(salesChartPeriod.value)
    
    if (result.success) {
      // Update chart with new data
      console.log('Chart data updated for period:', salesChartPeriod.value, result.data)
      // Here you would update the actual chart implementation
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Gagal memuat data grafik',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error updating sales chart:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat memuat grafik penjualan',
      position: 'top'
    })
  }
}

const goToTransactions = () => {
  router.push({ name: 'sales' })
}

const goToProducts = () => {
  router.push({ name: 'products' })
}

const viewTransaction = (id) => {
  console.log('View transaction:', id)
  // Navigate to transaction detail
}

const viewProduct = (id) => {
  console.log('View product:', id)
  router.push({ name: 'product-edit', params: { id } })
}

// Lifecycle
onMounted(async () => {
  appStore.setPageTitle('Dashboard')
  appStore.setBreadcrumbs([
    { label: 'Dashboard', icon: 'dashboard' }
  ])
  
  await nextTick()
  // Load dashboard data
  await loadDashboardData()
  // Initialize chart here if using chart library
  // initializeSalesChart()
})
</script>

<style lang="scss" scoped>
.dashboard-page {
  padding: var(--theme-spacing-lg);
  background-color: var(--theme-bg-secondary);
  transition: var(--theme-transition-all);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--theme-spacing-lg);
  padding: var(--theme-spacing-md);
  background: var(--theme-bg-card);
  border: var(--theme-border-light);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
}

.page-title {
  font-size: var(--theme-font-xxl);
  font-weight: 600;
  color: var(--theme-text-primary);
  margin: 0;
  transition: var(--theme-transition-all);
}

.page-subtitle {
  color: var(--theme-text-secondary);
  margin: var(--theme-spacing-xs) 0 0 0;
  transition: var(--theme-transition-all);
}

.page-title h4 {
  font-weight: 600;
  color: var(--theme-text-primary);
  font-size: var(--theme-font-xl);
  transition: var(--theme-transition-all);
}

.stat-card {
  background-color: var(--theme-bg-card);
  border: var(--theme-border-light);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
  
  &:hover {
    transform: translateY(-2px);
    box-shadow: var(--theme-shadow-large);
  }
  
  .stat-value {
    font-size: var(--theme-font-xl);
    font-weight: 700;
    color: var(--theme-text-primary);
    line-height: 1;
    transition: var(--theme-transition-all);
  }
  
  .stat-label {
    font-size: var(--theme-font-sm);
    color: var(--theme-text-secondary);
    margin-top: var(--theme-spacing-xs);
    transition: var(--theme-transition-all);
  }
  
  .stat-change {
    font-size: var(--theme-font-xs);
    font-weight: 600;
    margin-top: var(--theme-spacing-sm);
    display: flex;
    align-items: center;
    gap: var(--theme-spacing-xs);
    transition: var(--theme-transition-all);
  }
}

.chart-card,
.activity-card {
  height: 100%;
  background-color: var(--theme-bg-card);
  border: var(--theme-border-light);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
  
  .chart-title {
    font-size: var(--theme-font-lg);
    font-weight: 600;
    color: var(--theme-text-primary);
    margin: 0;
    transition: var(--theme-transition-all);
  }
}

.chart-container {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--theme-bg-secondary);
  border-radius: var(--theme-radius-lg);
  color: var(--theme-text-secondary);
  transition: var(--theme-transition-all);
}

.top-products {
  .product-item {
    padding: var(--theme-spacing-sm) 0;
    transition: var(--theme-transition-all);
    
    .product-name {
      font-weight: 600;
      color: var(--theme-text-primary);
      font-size: var(--theme-font-sm);
      transition: var(--theme-transition-all);
    }
    
    .product-sales {
      font-size: var(--theme-font-xs);
      color: var(--theme-text-secondary);
      transition: var(--theme-transition-all);
    }
    
    .product-revenue {
      font-weight: 600;
      color: var(--theme-text-accent);
      font-size: var(--theme-font-sm);
      transition: var(--theme-transition-all);
    }
  }
}

.q-item {
  transition: var(--theme-transition-all);
  
  &:hover {
    background-color: var(--theme-bg-hover);
  }
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

// Responsive adjustments
@media (max-width: 768px) {
  .dashboard-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
    
    .row {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
  }
  
  .header-actions {
    width: 100%;
    
    .q-btn {
      width: 100%;
    }
  }
}
</style>