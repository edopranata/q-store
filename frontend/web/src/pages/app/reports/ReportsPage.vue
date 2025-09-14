<template>
  <q-page class="reports-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Laporan</h4>
        <p class="text-grey-6 q-ma-none">Analisis dan laporan bisnis</p>
      </div>
    </div>

    <!-- Report Cards -->
    <div class="row q-col-gutter-lg">
      <!-- Sales Report -->
      <div class="col-md-5 col-xs-12">
        <q-card flat bordered class="report-card">
          <q-card-section>
            <div class="card-header">
              <q-icon name="trending_up" size="32px" color="primary" />
              <div class="card-title">
                <h6 class="q-ma-none">Laporan Penjualan</h6>
                <p class="text-grey-6 q-ma-none">Analisis penjualan harian, bulanan, dan tahunan</p>
              </div>
            </div>
            
            <div class="report-filters q-mt-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-select
                    v-model="salesReport.period"
                    :options="periodOptions"
                    label="Periode"
                    outlined
                    dense
                    emit-value
                    map-options
                    @update:model-value="loadSalesReport"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="salesReport.date"
                    label="Tanggal"
                    outlined
                    dense
                    type="date"
                    @update:model-value="loadSalesReport"
                  />
                </div>
              </div>
            </div>
            
            <div class="report-summary q-mt-md">
              <div class="summary-item">
                <span class="label">Total Transaksi:</span>
                <span class="value">{{ salesReport.data.total_transactions }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Total Penjualan:</span>
                <span class="value">{{ formatCurrency(salesReport.data.total_sales) }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Rata-rata per Transaksi:</span>
                <span class="value">{{ formatCurrency(salesReport.data.average_per_transaction) }}</span>
              </div>
            </div>
            
            <div class="card-actions q-mt-md">
              <q-btn
                flat
                icon="visibility"
                label="Lihat Detail"
                @click="viewSalesReport"
              />
              <q-btn
                flat
                icon="download"
                label="Export"
                @click="exportSalesReport"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Inventory Report -->
      <div class="col-md-5 col-xs-12">
        <q-card flat bordered class="report-card">
          <q-card-section>
            <div class="card-header">
              <q-icon name="inventory" size="32px" color="orange" />
              <div class="card-title">
                <h6 class="q-ma-none">Laporan Stok</h6>
                <p class="text-grey-6 q-ma-none">Monitoring stok dan pergerakan inventory</p>
              </div>
            </div>
            
            <div class="report-filters q-mt-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-select
                    v-model="inventoryReport.category_id"
                    :options="categoryOptions"
                    label="Kategori"
                    outlined
                    dense
                    clearable
                    emit-value
                    map-options
                    @update:model-value="loadInventoryReport"
                  />
                </div>
                <div class="col">
                  <q-select
                    v-model="inventoryReport.warehouse_id"
                    :options="warehouseOptions"
                    label="Gudang"
                    outlined
                    dense
                    clearable
                    emit-value
                    map-options
                    @update:model-value="loadInventoryReport"
                  />
                </div>
              </div>
            </div>
            
            <div class="report-summary q-mt-md">
              <div class="summary-item">
                <span class="label">Total Produk:</span>
                <span class="value">{{ inventoryReport.data.total_products }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Stok Rendah:</span>
                <span class="value text-negative">{{ inventoryReport.data.low_stock_count }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Nilai Inventory:</span>
                <span class="value">{{ formatCurrency(inventoryReport.data.total_value) }}</span>
              </div>
            </div>
            
            <div class="card-actions q-mt-md">
              <q-btn
                flat
                icon="visibility"
                label="Lihat Detail"
                @click="viewInventoryReport"
              />
              <q-btn
                flat
                icon="download"
                label="Export"
                @click="exportInventoryReport"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Second Row -->
    <div class="row q-col-gutter-lg q-mt-lg">
      <!-- Purchase Report -->
      <div class="col-md-5 col-xs-12">
        <q-card flat bordered class="report-card">
          <q-card-section>
            <div class="card-header">
              <q-icon name="shopping_cart" size="32px" color="green" />
              <div class="card-title">
                <h6 class="q-ma-none">Laporan Pembelian</h6>
                <p class="text-grey-6 q-ma-none">Analisis pembelian dan supplier</p>
              </div>
            </div>
            
            <div class="report-filters q-mt-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-select
                    v-model="purchaseReport.period"
                    :options="periodOptions"
                    label="Periode"
                    outlined
                    dense
                    emit-value
                    map-options
                    @update:model-value="loadPurchaseReport"
                  />
                </div>
                <div class="col">
                  <q-select
                    v-model="purchaseReport.supplier_id"
                    :options="supplierOptions"
                    label="Supplier"
                    outlined
                    dense
                    clearable
                    emit-value
                    map-options
                    @update:model-value="loadPurchaseReport"
                  />
                </div>
              </div>
            </div>
            
            <div class="report-summary q-mt-md">
              <div class="summary-item">
                <span class="label">Total Purchase Order:</span>
                <span class="value">{{ purchaseReport.data.total_orders }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Total Pembelian:</span>
                <span class="value">{{ formatCurrency(purchaseReport.data.total_purchases) }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Pending Orders:</span>
                <span class="value text-orange">{{ purchaseReport.data.pending_orders }}</span>
              </div>
            </div>
            
            <div class="card-actions q-mt-md">
              <q-btn
                flat
                icon="visibility"
                label="Lihat Detail"
                @click="viewPurchaseReport"
              />
              <q-btn
                flat
                icon="download"
                label="Export"
                @click="exportPurchaseReport"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Financial Report -->
      <div class="col-md-5 col-xs-12">
        <q-card flat bordered class="report-card">
          <q-card-section>
            <div class="card-header">
              <q-icon name="account_balance" size="32px" color="purple" />
              <div class="card-title">
                <h6 class="q-ma-none">Laporan Keuangan</h6>
                <p class="text-grey-6 q-ma-none">Profit, loss, dan cash flow</p>
              </div>
            </div>
            
            <div class="report-filters q-mt-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-select
                    v-model="financialReport.period"
                    :options="periodOptions"
                    label="Periode"
                    outlined
                    dense
                    emit-value
                    map-options
                    @update:model-value="loadFinancialReport"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="financialReport.year"
                    label="Tahun"
                    outlined
                    dense
                    type="number"
                    @update:model-value="loadFinancialReport"
                  />
                </div>
              </div>
            </div>
            
            <div class="report-summary q-mt-md">
              <div class="summary-item">
                <span class="label">Total Revenue:</span>
                <span class="value text-positive">{{ formatCurrency(financialReport.data.total_revenue) }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Total Expenses:</span>
                <span class="value text-negative">{{ formatCurrency(financialReport.data.total_expenses) }}</span>
              </div>
              <div class="summary-item">
                <span class="label">Net Profit:</span>
                <span class="value" :class="financialReport.data.net_profit >= 0 ? 'text-positive' : 'text-negative'">
                  {{ formatCurrency(financialReport.data.net_profit) }}
                </span>
              </div>
            </div>
            
            <div class="card-actions q-mt-md">
              <q-btn
                flat
                icon="visibility"
                label="Lihat Detail"
                @click="viewFinancialReport"
              />
              <q-btn
                flat
                icon="download"
                label="Export"
                @click="exportFinancialReport"
              />
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Reactive data
const categories = ref([])
const warehouses = ref([])
const suppliers = ref([])

// Report data
const salesReport = ref({
  period: 'daily',
  date: new Date().toISOString().split('T')[0],
  data: {
    total_transactions: 0,
    total_sales: 0,
    average_per_transaction: 0
  }
})

const inventoryReport = ref({
  category_id: '',
  warehouse_id: '',
  data: {
    total_products: 0,
    low_stock_count: 0,
    total_value: 0
  }
})

const purchaseReport = ref({
  period: 'monthly',
  supplier_id: '',
  data: {
    total_orders: 0,
    total_purchases: 0,
    pending_orders: 0
  }
})

const financialReport = ref({
  period: 'monthly',
  year: new Date().getFullYear(),
  data: {
    total_revenue: 0,
    total_expenses: 0,
    net_profit: 0
  }
})

// Options
const periodOptions = [
  { label: 'Harian', value: 'daily' },
  { label: 'Mingguan', value: 'weekly' },
  { label: 'Bulanan', value: 'monthly' },
  { label: 'Tahunan', value: 'yearly' }
]

// Computed
const categoryOptions = computed(() => {
  return categories.value.map(cat => ({
    label: cat.name,
    value: cat.id
  }))
})

const warehouseOptions = computed(() => {
  return warehouses.value.map(warehouse => ({
    label: warehouse.name,
    value: warehouse.id
  }))
})

const supplierOptions = computed(() => {
  return suppliers.value.map(supplier => ({
    label: supplier.name,
    value: supplier.id
  }))
})

// Methods
const loadMasterData = async () => {
  try {
    // Mock data - replace with actual API calls
    categories.value = [
      { id: 1, name: 'Minuman' },
      { id: 2, name: 'Makanan' },
      { id: 3, name: 'Snack' }
    ]
    
    warehouses.value = [
      { id: 1, name: 'Gudang Utama' },
      { id: 2, name: 'Gudang Cabang' }
    ]
    
    suppliers.value = [
      { id: 1, name: 'PT Supplier A' },
      { id: 2, name: 'CV Supplier B' }
    ]
  } catch {
    console.error('Failed to load master data')
  }
}

const loadSalesReport = async () => {
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    salesReport.value.data = {
      total_transactions: 125,
      total_sales: 15750000,
      average_per_transaction: 126000
    }
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat laporan penjualan'
    })
  }
}

const loadInventoryReport = async () => {
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    inventoryReport.value.data = {
      total_products: 85,
      low_stock_count: 12,
      total_value: 45000000
    }
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat laporan stok'
    })
  }
}

const loadPurchaseReport = async () => {
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    purchaseReport.value.data = {
      total_orders: 45,
      total_purchases: 8500000,
      pending_orders: 8
    }
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat laporan pembelian'
    })
  }
}

const loadFinancialReport = async () => {
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    financialReport.value.data = {
      total_revenue: 15750000,
      total_expenses: 8500000,
      net_profit: 7250000
    }
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat laporan keuangan'
    })
  }
}

// View report methods
const viewSalesReport = () => {
  $q.notify({
    type: 'info',
    message: 'Detail laporan penjualan akan segera tersedia'
  })
}

const viewInventoryReport = () => {
  $q.notify({
    type: 'info',
    message: 'Detail laporan stok akan segera tersedia'
  })
}

const viewPurchaseReport = () => {
  $q.notify({
    type: 'info',
    message: 'Detail laporan pembelian akan segera tersedia'
  })
}

const viewFinancialReport = () => {
  $q.notify({
    type: 'info',
    message: 'Detail laporan keuangan akan segera tersedia'
  })
}

// Export methods
const exportSalesReport = () => {
  $q.notify({
    type: 'info',
    message: 'Export laporan penjualan akan segera tersedia'
  })
}

const exportInventoryReport = () => {
  $q.notify({
    type: 'info',
    message: 'Export laporan stok akan segera tersedia'
  })
}

const exportPurchaseReport = () => {
  $q.notify({
    type: 'info',
    message: 'Export laporan pembelian akan segera tersedia'
  })
}

const exportFinancialReport = () => {
  $q.notify({
    type: 'info',
    message: 'Export laporan keuangan akan segera tersedia'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount || 0)
}

// Lifecycle
onMounted(async () => {
  await loadMasterData()
  await Promise.all([
    loadSalesReport(),
    loadInventoryReport(),
    loadPurchaseReport(),
    loadFinancialReport()
  ])
})
</script>

<style scoped>
.reports-page {
  padding: var(--theme-spacing-lg);
  background-color: var(--theme-bg-secondary);
  transition: var(--theme-transition-all);
}

.page-header {
  margin-bottom: var(--theme-spacing-lg);
  padding: var(--theme-spacing-md);
  background-color: var(--theme-bg-card);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-light);
  transition: var(--theme-transition-all);
}

.page-title h4 {
  font-weight: 600;
  color: var(--theme-text-primary);
  font-size: var(--theme-font-xl);
  transition: var(--theme-transition-all);
}

.report-card {
  height: 100%;
  background-color: var(--theme-bg-card);
  border: var(--theme-border-light);
  border-radius: var(--theme-radius-lg);
  transition: var(--theme-transition-all);
}

.report-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--theme-shadow-medium);
}

.card-header {
  display: flex;
  align-items: flex-start;
  gap: var(--theme-spacing-md);
}

.card-title h6 {
  font-weight: 600;
  color: var(--theme-text-primary);
  font-size: var(--theme-font-lg);
}

.report-summary {
  background: var(--theme-bg-secondary);
  border-radius: var(--theme-radius-md);
  padding: var(--theme-spacing-md);
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--theme-spacing-xs);
}

.summary-item:last-child {
  margin-bottom: 0;
}

.summary-item .label {
  font-weight: 500;
  color: var(--theme-text-secondary);
}

.summary-item .value {
  font-weight: 600;
  color: var(--theme-text-primary);
}

.card-actions {
  display: flex;
  gap: var(--theme-spacing-xs);
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

@media (max-width: 768px) {
  .reports-page {
    padding: var(--theme-spacing-md);
  }
  
  .card-header {
    flex-direction: column;
    gap: var(--theme-spacing-sm);
  }
  
  .card-actions {
    flex-direction: column;
  }
  
  .card-actions .q-btn {
    width: 100%;
  }
}
</style>