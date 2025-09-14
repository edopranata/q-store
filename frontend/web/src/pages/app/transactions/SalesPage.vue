<template>
  <q-page class="sales-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Penjualan</h4>
        <p class="text-grey-6 q-ma-none">Kelola transaksi penjualan</p>
      </div>
      <div class="header-actions">
        <q-btn
          color="primary"
          icon="add"
          label="Transaksi Baru"
          @click="createSale"
        />
      </div>
    </div>

    <!-- Filters -->
    <q-card flat bordered class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md items-end">
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-input
              v-model="filters.search"
              label="Cari transaksi..."
              outlined
              dense
              clearable
              @input="applyFilters"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
          
          <div class="col-md-2 col-sm-6 col-xs-12">
            <q-select
              v-model="filters.status"
              :options="statusOptions"
              label="Status"
              outlined
              dense
              clearable
              emit-value
              map-options
              @update:model-value="applyFilters"
            />
          </div>
          
          <div class="col-md-2 col-sm-6 col-xs-12">
            <q-input
              v-model="filters.date_from"
              label="Dari Tanggal"
              outlined
              dense
              type="date"
              @update:model-value="applyFilters"
            />
          </div>
          
          <div class="col-md-2 col-sm-6 col-xs-12">
            <q-input
              v-model="filters.date_to"
              label="Sampai Tanggal"
              outlined
              dense
              type="date"
              @update:model-value="applyFilters"
            />
          </div>
          
          <div class="col-auto">
            <q-btn
              flat
              icon="refresh"
              @click="resetFilters"
              title="Reset Filter"
            />
          </div>
        </div>
      </q-card-section>
    </q-card>

    <!-- Data Table -->
    <q-table
      :rows="filteredSales"
      :columns="columns"
      :loading="loading"
      :pagination="pagination"
      @request="onRequest"
      row-key="id"
      flat
      bordered
      class="sales-table"
    >
      <template v-slot:body-cell-invoice_number="props">
        <q-td :props="props">
          <span class="text-primary cursor-pointer" @click="viewSale(props.row)">
            {{ props.value }}
          </span>
        </q-td>
      </template>

      <template v-slot:body-cell-customer="props">
        <q-td :props="props">
          <div>
            <div class="text-weight-medium">{{ props.row.customer_name || 'Walk-in Customer' }}</div>
            <div v-if="props.row.customer_phone" class="text-caption text-grey-6">
              {{ props.row.customer_phone }}
            </div>
          </div>
        </q-td>
      </template>

      <template v-slot:body-cell-total="props">
        <q-td :props="props">
          <div class="text-weight-medium">
            {{ formatCurrency(props.value) }}
          </div>
        </q-td>
      </template>

      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-badge
            :color="getStatusColor(props.value)"
            :label="getStatusLabel(props.value)"
          />
        </q-td>
      </template>

      <template v-slot:body-cell-payment_status="props">
        <q-td :props="props">
          <q-badge
            :color="getPaymentStatusColor(props.value)"
            :label="getPaymentStatusLabel(props.value)"
          />
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-btn-group flat>
            <q-btn
              flat
              dense
              icon="visibility"
              @click="viewSale(props.row)"
              title="Lihat Detail"
            />
            <q-btn
              flat
              dense
              icon="print"
              @click="printInvoice()"
              title="Cetak Invoice"
            />
            <q-btn
              v-if="props.row.status === 'draft'"
              flat
              dense
              icon="edit"
              @click="editSale(props.row)"
              title="Edit"
            />
            <q-btn
              v-if="props.row.status === 'draft'"
              flat
              dense
              icon="delete"
              color="negative"
              @click="deleteSale(props.row)"
              title="Hapus"
            />
          </q-btn-group>
        </q-td>
      </template>
    </q-table>

    <!-- View Sale Dialog -->
    <q-dialog v-model="showViewDialog" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Detail Penjualan</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedSale">
          <div class="row q-col-gutter-lg">
            <!-- Sale Info -->
            <div class="col-md-6 col-xs-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle2 q-mb-md">Informasi Transaksi</div>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <span class="label">No. Invoice:</span>
                      <span class="value">{{ selectedSale.invoice_number }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Tanggal:</span>
                      <span class="value">{{ formatDate(selectedSale.sale_date) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Customer:</span>
                      <span class="value">{{ selectedSale.customer_name || 'Walk-in Customer' }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Status:</span>
                      <q-badge
                        :color="getStatusColor(selectedSale.status)"
                        :label="getStatusLabel(selectedSale.status)"
                      />
                    </div>
                    <div class="info-item">
                      <span class="label">Status Pembayaran:</span>
                      <q-badge
                        :color="getPaymentStatusColor(selectedSale.payment_status)"
                        :label="getPaymentStatusLabel(selectedSale.payment_status)"
                      />
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>

            <!-- Payment Info -->
            <div class="col-md-5 col-xs-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle2 q-mb-md">Informasi Pembayaran</div>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <span class="label">Subtotal:</span>
                      <span class="value">{{ formatCurrency(selectedSale.subtotal) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Diskon:</span>
                      <span class="value">{{ formatCurrency(selectedSale.discount) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Pajak:</span>
                      <span class="value">{{ formatCurrency(selectedSale.tax) }}</span>
                    </div>
                    <div class="info-item total">
                      <span class="label">Total:</span>
                      <span class="value">{{ formatCurrency(selectedSale.total) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Dibayar:</span>
                      <span class="value">{{ formatCurrency(selectedSale.paid_amount) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Kembalian:</span>
                      <span class="value">{{ formatCurrency(selectedSale.change_amount) }}</span>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- Sale Items -->
          <q-card flat bordered class="q-mt-md">
            <q-card-section>
              <div class="text-subtitle2 q-mb-md">Item Penjualan</div>
              
              <q-table
                :rows="selectedSale.items || []"
                :columns="itemColumns"
                flat
                hide-pagination
                :pagination="{ rowsPerPage: 0 }"
              >
                <template v-slot:body-cell-subtotal="props">
                  <q-td :props="props">
                    {{ formatCurrency(props.value) }}
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter } from 'vue-router'

const $q = useQuasar()
const router = useRouter()

// Reactive data
const loading = ref(false)
const sales = ref([])
const selectedSale = ref(null)
const showViewDialog = ref(false)

const filters = ref({
  search: '',
  status: '',
  date_from: '',
  date_to: ''
})

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})

// Options
const statusOptions = [
  { label: 'Draft', value: 'draft' },
  { label: 'Completed', value: 'completed' },
  { label: 'Cancelled', value: 'cancelled' }
]

// Table columns
const columns = [
  {
    name: 'invoice_number',
    label: 'No. Invoice',
    field: 'invoice_number',
    align: 'left',
    sortable: true
  },
  {
    name: 'sale_date',
    label: 'Tanggal',
    field: 'sale_date',
    align: 'left',
    sortable: true,
    format: val => formatDate(val)
  },
  {
    name: 'customer',
    label: 'Customer',
    field: 'customer_name',
    align: 'left'
  },
  {
    name: 'total',
    label: 'Total',
    field: 'total',
    align: 'right',
    sortable: true
  },
  {
    name: 'status',
    label: 'Status',
    field: 'status',
    align: 'center'
  },
  {
    name: 'payment_status',
    label: 'Status Pembayaran',
    field: 'payment_status',
    align: 'center'
  },
  {
    name: 'actions',
    label: 'Aksi',
    field: 'actions',
    align: 'center'
  }
]

const itemColumns = [
  {
    name: 'product_name',
    label: 'Produk',
    field: 'product_name',
    align: 'left'
  },
  {
    name: 'quantity',
    label: 'Qty',
    field: 'quantity',
    align: 'center'
  },
  {
    name: 'price',
    label: 'Harga',
    field: 'price',
    align: 'right',
    format: val => formatCurrency(val)
  },
  {
    name: 'subtotal',
    label: 'Subtotal',
    field: 'subtotal',
    align: 'right'
  }
]

// Computed
const filteredSales = computed(() => {
  let result = sales.value
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(sale => 
      sale.invoice_number.toLowerCase().includes(search) ||
      (sale.customer_name && sale.customer_name.toLowerCase().includes(search))
    )
  }
  
  if (filters.value.status) {
    result = result.filter(sale => sale.status === filters.value.status)
  }
  
  if (filters.value.date_from) {
    result = result.filter(sale => sale.sale_date >= filters.value.date_from)
  }
  
  if (filters.value.date_to) {
    result = result.filter(sale => sale.sale_date <= filters.value.date_to)
  }
  
  return result
})

// Methods
const loadSales = async () => {
  loading.value = true
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    sales.value = [
      {
        id: 1,
        invoice_number: 'INV-2024-001',
        sale_date: '2024-01-20',
        customer_name: 'John Doe',
        customer_phone: '081234567890',
        subtotal: 100000,
        discount: 5000,
        tax: 10000,
        total: 105000,
        paid_amount: 105000,
        change_amount: 0,
        status: 'completed',
        payment_status: 'paid',
        items: [
          {
            product_name: 'Kopi Arabica',
            quantity: 2,
            price: 25000,
            subtotal: 50000
          },
          {
            product_name: 'Teh Hijau',
            quantity: 1,
            price: 50000,
            subtotal: 50000
          }
        ]
      },
      {
        id: 2,
        invoice_number: 'INV-2024-002',
        sale_date: '2024-01-21',
        customer_name: null,
        customer_phone: null,
        subtotal: 75000,
        discount: 0,
        tax: 7500,
        total: 82500,
        paid_amount: 82500,
        change_amount: 0,
        status: 'completed',
        payment_status: 'paid',
        items: [
          {
            product_name: 'Snack Mix',
            quantity: 3,
            price: 25000,
            subtotal: 75000
          }
        ]
      }
    ]
    
    pagination.value.rowsNumber = sales.value.length
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat data penjualan'
    })
  } finally {
    loading.value = false
  }
}

const onRequest = (props) => {
  pagination.value = props.pagination
  loadSales()
}

const applyFilters = () => {
  // Trigger reactivity
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    date_from: '',
    date_to: ''
  }
}

const createSale = () => {
  router.push({ name: 'pos' })
}

const viewSale = (sale) => {
  selectedSale.value = sale
  showViewDialog.value = true
}

const editSale = (sale) => {
  router.push({ name: 'sale-edit', params: { id: sale.id } })
}

const printInvoice = () => {
  $q.notify({
    type: 'info',
    message: 'Fitur cetak invoice akan segera tersedia'
  })
}

const deleteSale = (sale) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus transaksi ${sale.invoice_number}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      // Mock delete - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = sales.value.findIndex(s => s.id === sale.id)
      if (index > -1) {
        sales.value.splice(index, 1)
      }
      
      $q.notify({
        type: 'positive',
        message: 'Transaksi berhasil dihapus'
      })
    } catch {
      $q.notify({
        type: 'negative',
        message: 'Gagal menghapus transaksi'
      })
    }
  })
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'orange',
    completed: 'green',
    cancelled: 'red'
  }
  return colors[status] || 'grey'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    completed: 'Selesai',
    cancelled: 'Dibatalkan'
  }
  return labels[status] || status
}

const getPaymentStatusColor = (status) => {
  const colors = {
    pending: 'orange',
    paid: 'green',
    partial: 'blue',
    refunded: 'purple'
  }
  return colors[status] || 'grey'
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    paid: 'Lunas',
    partial: 'Sebagian',
    refunded: 'Dikembalikan'
  }
  return labels[status] || status
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadSales()
})
</script>

<style scoped>
.sales-page {
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
  background-color: var(--theme-bg-card);
  border: var(--theme-border-light);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-medium);
  transition: var(--theme-transition-all);
}

.page-title h4 {
  font-weight: 600;
  color: var(--theme-text-primary);
  font-size: var(--theme-font-xl);
  transition: var(--theme-transition-all);
}

.sales-table {
  background: var(--theme-bg-card);
  border-radius: var(--theme-radius-lg);
  transition: var(--theme-transition-all);
}

.info-grid {
  display: grid;
  gap: var(--theme-spacing-sm);
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--theme-spacing-xs) 0;
  border-bottom: var(--theme-border-light);
  transition: var(--theme-transition-all);
}

.info-item.total {
  font-weight: 600;
  font-size: var(--theme-font-lg);
  border-bottom: 2px solid var(--theme-primary);
}

.info-item .label {
  font-weight: 500;
  color: var(--theme-text-secondary);
}

.info-item .value {
  color: var(--theme-text-primary);
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

@media (max-width: 768px) {
  .sales-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
}
</style>