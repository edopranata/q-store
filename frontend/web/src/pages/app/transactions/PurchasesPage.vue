<template>
  <q-page class="purchases-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Pembelian</h4>
        <p class="text-grey-6 q-ma-none">Kelola transaksi pembelian</p>
      </div>
      <div class="header-actions">
        <q-btn
          color="primary"
          icon="add"
          label="Pembelian Baru"
          @click="createPurchase"
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
            <q-select
              v-model="filters.supplier_id"
              :options="supplierOptions"
              label="Supplier"
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
      :rows="filteredPurchases"
      :columns="columns"
      :loading="loading"
      :pagination="pagination"
      @request="onRequest"
      row-key="id"
      flat
      bordered
      class="purchases-table"
    >
      <template v-slot:body-cell-purchase_number="props">
        <q-td :props="props">
          <span class="text-primary cursor-pointer" @click="viewPurchase(props.row)">
            {{ props.value }}
          </span>
        </q-td>
      </template>

      <template v-slot:body-cell-supplier="props">
        <q-td :props="props">
          <div>
            <div class="text-weight-medium">{{ props.row.supplier_name }}</div>
            <div v-if="props.row.supplier_phone" class="text-caption text-grey-6">
              {{ props.row.supplier_phone }}
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
              @click="viewPurchase(props.row)"
              title="Lihat Detail"
            />
            <q-btn
              flat
              dense
              icon="print"
              @click="printPurchase()"
              title="Cetak Purchase Order"
            />
            <q-btn
              v-if="props.row.status === 'draft'"
              flat
              dense
              icon="edit"
              @click="editPurchase(props.row)"
              title="Edit"
            />
            <q-btn
              v-if="props.row.status === 'draft'"
              flat
              dense
              icon="delete"
              color="negative"
              @click="deletePurchase(props.row)"
              title="Hapus"
            />
          </q-btn-group>
        </q-td>
      </template>
    </q-table>

    <!-- View Purchase Dialog -->
    <q-dialog v-model="showViewDialog" maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Detail Pembelian</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section v-if="selectedPurchase">
          <div class="row q-col-gutter-lg">
            <!-- Purchase Info -->
            <div class="col-md-6 col-xs-12">
              <q-card flat bordered>
                <q-card-section>
                  <div class="text-subtitle2 q-mb-md">Informasi Pembelian</div>
                  
                  <div class="info-grid">
                    <div class="info-item">
                      <span class="label">No. Purchase:</span>
                      <span class="value">{{ selectedPurchase.purchase_number }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Tanggal:</span>
                      <span class="value">{{ formatDate(selectedPurchase.purchase_date) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Supplier:</span>
                      <span class="value">{{ selectedPurchase.supplier_name }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Status:</span>
                      <q-badge
                        :color="getStatusColor(selectedPurchase.status)"
                        :label="getStatusLabel(selectedPurchase.status)"
                      />
                    </div>
                    <div class="info-item">
                      <span class="label">Status Pembayaran:</span>
                      <q-badge
                        :color="getPaymentStatusColor(selectedPurchase.payment_status)"
                        :label="getPaymentStatusLabel(selectedPurchase.payment_status)"
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
                      <span class="value">{{ formatCurrency(selectedPurchase.subtotal) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Diskon:</span>
                      <span class="value">{{ formatCurrency(selectedPurchase.discount) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Pajak:</span>
                      <span class="value">{{ formatCurrency(selectedPurchase.tax) }}</span>
                    </div>
                    <div class="info-item total">
                      <span class="label">Total:</span>
                      <span class="value">{{ formatCurrency(selectedPurchase.total) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Dibayar:</span>
                      <span class="value">{{ formatCurrency(selectedPurchase.paid_amount) }}</span>
                    </div>
                    <div class="info-item">
                      <span class="label">Sisa:</span>
                      <span class="value">{{ formatCurrency(selectedPurchase.remaining_amount) }}</span>
                    </div>
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- Purchase Items -->
          <q-card flat bordered class="q-mt-md">
            <q-card-section>
              <div class="text-subtitle2 q-mb-md">Item Pembelian</div>
              
              <q-table
                :rows="selectedPurchase.items || []"
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

const $q = useQuasar()

// Reactive data
const loading = ref(false)
const purchases = ref([])
const suppliers = ref([])
const selectedPurchase = ref(null)
const showViewDialog = ref(false)

const filters = ref({
  search: '',
  status: '',
  supplier_id: '',
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
  { label: 'Ordered', value: 'ordered' },
  { label: 'Received', value: 'received' },
  { label: 'Cancelled', value: 'cancelled' }
]

// Computed
const supplierOptions = computed(() => {
  return suppliers.value.map(supplier => ({
    label: supplier.name,
    value: supplier.id
  }))
})

// Table columns
const columns = [
  {
    name: 'purchase_number',
    label: 'No. Purchase',
    field: 'purchase_number',
    align: 'left',
    sortable: true
  },
  {
    name: 'purchase_date',
    label: 'Tanggal',
    field: 'purchase_date',
    align: 'left',
    sortable: true,
    format: val => formatDate(val)
  },
  {
    name: 'supplier',
    label: 'Supplier',
    field: 'supplier_name',
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
const filteredPurchases = computed(() => {
  let result = purchases.value
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(purchase => 
      purchase.purchase_number.toLowerCase().includes(search) ||
      purchase.supplier_name.toLowerCase().includes(search)
    )
  }
  
  if (filters.value.status) {
    result = result.filter(purchase => purchase.status === filters.value.status)
  }
  
  if (filters.value.supplier_id) {
    result = result.filter(purchase => purchase.supplier_id === filters.value.supplier_id)
  }
  
  if (filters.value.date_from) {
    result = result.filter(purchase => purchase.purchase_date >= filters.value.date_from)
  }
  
  if (filters.value.date_to) {
    result = result.filter(purchase => purchase.purchase_date <= filters.value.date_to)
  }
  
  return result
})

// Methods
const loadSuppliers = async () => {
  try {
    // Mock data - replace with actual API call
    suppliers.value = [
      { id: 1, name: 'PT Supplier A' },
      { id: 2, name: 'CV Supplier B' },
      { id: 3, name: 'UD Supplier C' }
    ]
  } catch {
    console.error('Failed to load suppliers')
  }
}

const loadPurchases = async () => {
  loading.value = true
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    purchases.value = [
      {
        id: 1,
        purchase_number: 'PO-2024-001',
        purchase_date: '2024-01-20',
        supplier_id: 1,
        supplier_name: 'PT Supplier A',
        supplier_phone: '021-1234567',
        subtotal: 500000,
        discount: 25000,
        tax: 50000,
        total: 525000,
        paid_amount: 525000,
        remaining_amount: 0,
        status: 'received',
        payment_status: 'paid',
        items: [
          {
            product_name: 'Kopi Arabica Premium',
            quantity: 10,
            price: 25000,
            subtotal: 250000
          },
          {
            product_name: 'Teh Hijau',
            quantity: 5,
            price: 50000,
            subtotal: 250000
          }
        ]
      },
      {
        id: 2,
        purchase_number: 'PO-2024-002',
        purchase_date: '2024-01-21',
        supplier_id: 2,
        supplier_name: 'CV Supplier B',
        supplier_phone: '021-7654321',
        subtotal: 300000,
        discount: 0,
        tax: 30000,
        total: 330000,
        paid_amount: 0,
        remaining_amount: 330000,
        status: 'ordered',
        payment_status: 'pending',
        items: [
          {
            product_name: 'Snack Mix Premium',
            quantity: 12,
            price: 25000,
            subtotal: 300000
          }
        ]
      }
    ]
    
    pagination.value.rowsNumber = purchases.value.length
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat data pembelian'
    })
  } finally {
    loading.value = false
  }
}

const onRequest = (props) => {
  pagination.value = props.pagination
  loadPurchases()
}

const applyFilters = () => {
  // Trigger reactivity
}

const resetFilters = () => {
  filters.value = {
    search: '',
    status: '',
    supplier_id: '',
    date_from: '',
    date_to: ''
  }
}

const createPurchase = () => {
  $q.notify({
    type: 'info',
    message: 'Fitur pembelian baru akan segera tersedia'
  })
}

const viewPurchase = (purchase) => {
  selectedPurchase.value = purchase
  showViewDialog.value = true
}

const editPurchase = (purchase) => {
  $q.notify({
    type: 'info',
    message: `Edit pembelian ${purchase.purchase_number} akan segera tersedia`
  })
}

const printPurchase = () => {
  $q.notify({
    type: 'info',
    message: 'Fitur cetak purchase order akan segera tersedia'
  })
}

const deletePurchase = (purchase) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus pembelian ${purchase.purchase_number}?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      // Mock delete - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = purchases.value.findIndex(p => p.id === purchase.id)
      if (index > -1) {
        purchases.value.splice(index, 1)
      }
      
      $q.notify({
        type: 'positive',
        message: 'Pembelian berhasil dihapus'
      })
    } catch {
      $q.notify({
        type: 'negative',
        message: 'Gagal menghapus pembelian'
      })
    }
  })
}

const getStatusColor = (status) => {
  const colors = {
    draft: 'orange',
    ordered: 'blue',
    received: 'green',
    cancelled: 'red'
  }
  return colors[status] || 'grey'
}

const getStatusLabel = (status) => {
  const labels = {
    draft: 'Draft',
    ordered: 'Dipesan',
    received: 'Diterima',
    cancelled: 'Dibatalkan'
  }
  return labels[status] || status
}

const getPaymentStatusColor = (status) => {
  const colors = {
    pending: 'orange',
    paid: 'green',
    partial: 'blue',
    overdue: 'red'
  }
  return colors[status] || 'grey'
}

const getPaymentStatusLabel = (status) => {
  const labels = {
    pending: 'Pending',
    paid: 'Lunas',
    partial: 'Sebagian',
    overdue: 'Terlambat'
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
onMounted(async () => {
  await Promise.all([
    loadSuppliers(),
    loadPurchases()
  ])
})
</script>

<style scoped>
.purchases-page {
  padding: 24px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}

.page-title h4 {
  font-weight: 600;
  color: #1976d2;
}

.purchases-table {
  background: white;
}

.info-grid {
  display: grid;
  gap: 12px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-item.total {
  font-weight: 600;
  font-size: 1.1em;
  border-bottom: 2px solid #1976d2;
}

.info-item .label {
  font-weight: 500;
  color: #666;
}

.info-item .value {
  color: #333;
}

@media (max-width: 768px) {
  .purchases-page {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
}
</style>