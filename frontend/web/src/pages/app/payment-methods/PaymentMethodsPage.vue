<template>
  <q-page class="payment-methods-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Metode Pembayaran</h4>
        <p class="text-grey-6 q-ma-none">Kelola metode pembayaran yang tersedia</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Tambah Metode"
        @click="showAddDialog = true"
      />
    </div>

    <!-- Filters -->
    <q-card class="q-mb-md">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-md-4 col-sm-6 col-xs-12">
            <q-input
              v-model="filters.search"
              placeholder="Cari metode pembayaran..."
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
              v-model="filters.type"
              :options="typeOptions"
              label="Tipe"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
          </div>
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-select
              v-model="filters.status"
              :options="statusOptions"
              label="Status"
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
    <q-card>
      <q-table
        :rows="filteredPaymentMethods"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        row-key="id"
        binary-state-sort
      >
        <template v-slot:body-cell-icon="props">
          <q-td :props="props">
            <q-icon
              :name="props.row.icon"
              size="sm"
              :color="props.row.color"
            />
          </q-td>
        </template>

        <template v-slot:body-cell-type="props">
          <q-td :props="props">
            <q-badge
              :color="getTypeColor(props.row.type)"
              :label="getTypeLabel(props.row.type)"
            />
          </q-td>
        </template>

        <template v-slot:body-cell-status="props">
          <q-td :props="props">
            <q-badge
              :color="props.row.status === 'active' ? 'positive' : 'negative'"
              :label="props.row.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
            />
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn
              flat
              round
              icon="visibility"
              size="sm"
              @click="viewPaymentMethod(props.row)"
              class="q-mr-xs"
            >
              <q-tooltip>Detail</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="edit"
              size="sm"
              @click="editPaymentMethod(props.row)"
              class="q-mr-xs"
            >
              <q-tooltip>Edit</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="delete"
              size="sm"
              color="negative"
              @click="deletePaymentMethod(props.row)"
            >
              <q-tooltip>Hapus</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 500px; max-width: 600px">
        <q-card-section>
          <div class="text-h6">{{ editMode ? 'Edit' : 'Tambah' }} Metode Pembayaran</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="savePaymentMethod" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="paymentMethodForm.name"
                  label="Nama Metode *"
                  outlined
                  :rules="[val => !!val || 'Nama metode wajib diisi']"
                />
              </div>
              <div class="col">
                <q-input
                  v-model="paymentMethodForm.code"
                  label="Kode"
                  outlined
                />
              </div>
            </div>

            <q-select
              v-model="paymentMethodForm.type"
              :options="typeOptions"
              label="Tipe *"
              outlined
              emit-value
              map-options
              :rules="[val => !!val || 'Tipe wajib dipilih']"
            />

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-select
                  v-model="paymentMethodForm.icon"
                  :options="iconOptions"
                  label="Icon"
                  outlined
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section avatar>
                        <q-icon :name="scope.opt.value" />
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ scope.opt.label }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                  <template v-slot:selected>
                    <div class="row items-center q-col-gutter-sm">
                      <q-icon :name="paymentMethodForm.icon" v-if="paymentMethodForm.icon" />
                      <span>{{ getIconLabel(paymentMethodForm.icon) }}</span>
                    </div>
                  </template>
                </q-select>
              </div>
              <div class="col">
                <q-select
                  v-model="paymentMethodForm.color"
                  :options="colorOptions"
                  label="Warna"
                  outlined
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section avatar>
                        <div
                          class="color-preview"
                          :style="{ backgroundColor: scope.opt.value }"
                        ></div>
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ scope.opt.label }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
              </div>
            </div>

            <q-input
              v-model="paymentMethodForm.description"
              label="Deskripsi"
              outlined
              type="textarea"
              rows="3"
            />

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model.number="paymentMethodForm.fee_percentage"
                  label="Biaya (%)"
                  outlined
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                />
              </div>
              <div class="col">
                <q-input
                  v-model.number="paymentMethodForm.fee_fixed"
                  label="Biaya Tetap"
                  outlined
                  type="number"
                  step="0.01"
                  min="0"
                />
              </div>
            </div>

            <q-input
              v-model="paymentMethodForm.notes"
              label="Catatan"
              outlined
              type="textarea"
              rows="2"
            />

            <q-select
              v-model="paymentMethodForm.status"
              :options="statusOptions"
              label="Status *"
              outlined
              emit-value
              map-options
              :rules="[val => !!val || 'Status wajib dipilih']"
            />
          </q-form>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Batal" @click="closeDialog" />
          <q-btn
            color="primary"
            label="Simpan"
            @click="savePaymentMethod"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- View Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Detail Metode Pembayaran</div>
        </q-card-section>

        <q-card-section v-if="selectedPaymentMethod">
          <div class="q-gutter-sm">
            <div class="row items-center q-col-gutter-sm">
              <q-icon
                :name="selectedPaymentMethod.icon"
                :color="selectedPaymentMethod.color"
                size="md"
              />
              <strong>{{ selectedPaymentMethod.name }}</strong>
            </div>
            <div v-if="selectedPaymentMethod.code"><strong>Kode:</strong> {{ selectedPaymentMethod.code }}</div>
            <div>
              <strong>Tipe:</strong>
              <q-badge
                :color="getTypeColor(selectedPaymentMethod.type)"
                :label="getTypeLabel(selectedPaymentMethod.type)"
                class="q-ml-sm"
              />
            </div>
            <div v-if="selectedPaymentMethod.description"><strong>Deskripsi:</strong> {{ selectedPaymentMethod.description }}</div>
            <div v-if="selectedPaymentMethod.fee_percentage > 0">
              <strong>Biaya Persentase:</strong> {{ selectedPaymentMethod.fee_percentage }}%
            </div>
            <div v-if="selectedPaymentMethod.fee_fixed > 0">
              <strong>Biaya Tetap:</strong> Rp {{ selectedPaymentMethod.fee_fixed.toLocaleString('id-ID') }}
            </div>
            <div v-if="selectedPaymentMethod.notes"><strong>Catatan:</strong> {{ selectedPaymentMethod.notes }}</div>
            <div>
              <strong>Status:</strong>
              <q-badge
                :color="selectedPaymentMethod.status === 'active' ? 'positive' : 'negative'"
                :label="selectedPaymentMethod.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
                class="q-ml-sm"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Tutup" color="primary" v-close-popup />
        </q-card-actions>
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
const saving = ref(false)
const showAddDialog = ref(false)
const showViewDialog = ref(false)
const editMode = ref(false)
const paymentMethods = ref([])
const selectedPaymentMethod = ref(null)
const filters = ref({
  search: '',
  type: null,
  status: null
})

const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})

const paymentMethodForm = ref({
  id: null,
  name: '',
  code: '',
  type: '',
  icon: 'payment',
  color: 'primary',
  description: '',
  fee_percentage: 0,
  fee_fixed: 0,
  notes: '',
  status: 'active'
})

// Options
const typeOptions = [
  { label: 'Tunai', value: 'cash' },
  { label: 'Transfer Bank', value: 'bank_transfer' },
  { label: 'Kartu Kredit', value: 'credit_card' },
  { label: 'Kartu Debit', value: 'debit_card' },
  { label: 'E-Wallet', value: 'e_wallet' },
  { label: 'QRIS', value: 'qris' },
  { label: 'Lainnya', value: 'other' }
]

const statusOptions = [
  { label: 'Aktif', value: 'active' },
  { label: 'Tidak Aktif', value: 'inactive' }
]

const iconOptions = [
  { label: 'Payment', value: 'payment' },
  { label: 'Credit Card', value: 'credit_card' },
  { label: 'Account Balance Wallet', value: 'account_balance_wallet' },
  { label: 'Money', value: 'money' },
  { label: 'QR Code', value: 'qr_code' },
  { label: 'Mobile Payment', value: 'mobile_friendly' },
  { label: 'Bank', value: 'account_balance' }
]

const colorOptions = [
  { label: 'Primary', value: 'primary' },
  { label: 'Secondary', value: 'secondary' },
  { label: 'Positive', value: 'positive' },
  { label: 'Negative', value: 'negative' },
  { label: 'Warning', value: 'warning' },
  { label: 'Info', value: 'info' },
  { label: 'Dark', value: 'dark' }
]

// Table columns
const columns = [
  {
    name: 'icon',
    label: 'Icon',
    align: 'center',
    field: 'icon'
  },
  {
    name: 'name',
    required: true,
    label: 'Nama Metode',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'code',
    label: 'Kode',
    align: 'center',
    field: 'code'
  },
  {
    name: 'type',
    label: 'Tipe',
    align: 'center',
    field: 'type',
    sortable: true
  },
  {
    name: 'fee_percentage',
    label: 'Biaya (%)',
    align: 'center',
    field: 'fee_percentage',
    format: (val) => val > 0 ? `${val}%` : '-'
  },
  {
    name: 'fee_fixed',
    label: 'Biaya Tetap',
    align: 'right',
    field: 'fee_fixed',
    format: (val) => val > 0 ? `Rp ${val.toLocaleString('id-ID')}` : '-'
  },
  {
    name: 'status',
    label: 'Status',
    align: 'center',
    field: 'status',
    sortable: true
  },
  {
    name: 'created_at',
    label: 'Dibuat',
    align: 'center',
    field: 'created_at',
    sortable: true,
    format: (val) => new Date(val).toLocaleDateString('id-ID')
  },
  {
    name: 'actions',
    label: 'Aksi',
    align: 'center'
  }
]

// Computed
const filteredPaymentMethods = computed(() => {
  let filtered = paymentMethods.value

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(method =>
      method.name.toLowerCase().includes(search) ||
      method.code?.toLowerCase().includes(search) ||
      method.description?.toLowerCase().includes(search)
    )
  }

  if (filters.value.type) {
    filtered = filtered.filter(method => method.type === filters.value.type)
  }

  if (filters.value.status) {
    filtered = filtered.filter(method => method.status === filters.value.status)
  }

  return filtered
})

// Methods
const getTypeColor = (type) => {
  const colors = {
    cash: 'positive',
    bank_transfer: 'info',
    credit_card: 'warning',
    debit_card: 'secondary',
    e_wallet: 'primary',
    qris: 'dark',
    other: 'grey'
  }
  return colors[type] || 'grey'
}

const getTypeLabel = (type) => {
  const option = typeOptions.find(opt => opt.value === type)
  return option ? option.label : type
}

const getIconLabel = (icon) => {
  const option = iconOptions.find(opt => opt.value === icon)
  return option ? option.label : icon
}

const loadPaymentMethods = async () => {
  loading.value = true
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    paymentMethods.value = [
      {
        id: 1,
        name: 'Tunai',
        code: 'CASH',
        type: 'cash',
        icon: 'money',
        color: 'positive',
        description: 'Pembayaran dengan uang tunai',
        fee_percentage: 0,
        fee_fixed: 0,
        notes: 'Metode pembayaran utama',
        status: 'active',
        created_at: '2024-01-15T10:30:00Z'
      },
      {
        id: 2,
        name: 'Transfer Bank BCA',
        code: 'BCA',
        type: 'bank_transfer',
        icon: 'account_balance',
        color: 'info',
        description: 'Transfer melalui Bank BCA',
        fee_percentage: 0,
        fee_fixed: 2500,
        notes: 'Biaya admin bank',
        status: 'active',
        created_at: '2024-01-16T14:20:00Z'
      },
      {
        id: 3,
        name: 'Kartu Kredit',
        code: 'CC',
        type: 'credit_card',
        icon: 'credit_card',
        color: 'warning',
        description: 'Pembayaran dengan kartu kredit',
        fee_percentage: 2.5,
        fee_fixed: 0,
        notes: 'MDR 2.5%',
        status: 'active',
        created_at: '2024-01-17T09:15:00Z'
      },
      {
        id: 4,
        name: 'GoPay',
        code: 'GOPAY',
        type: 'e_wallet',
        icon: 'account_balance_wallet',
        color: 'primary',
        description: 'Pembayaran melalui GoPay',
        fee_percentage: 1.5,
        fee_fixed: 0,
        notes: 'E-wallet populer',
        status: 'active',
        created_at: '2024-01-18T11:45:00Z'
      },
      {
        id: 5,
        name: 'QRIS',
        code: 'QRIS',
        type: 'qris',
        icon: 'qr_code',
        color: 'dark',
        description: 'Quick Response Code Indonesian Standard',
        fee_percentage: 0.7,
        fee_fixed: 0,
        notes: 'Standard nasional',
        status: 'active',
        created_at: '2024-01-19T16:30:00Z'
      }
    ]
    pagination.value.rowsNumber = paymentMethods.value.length
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat data metode pembayaran'
    })
  } finally {
    loading.value = false
  }
}

const onRequest = (props) => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  
  pagination.value.page = page
  pagination.value.rowsPerPage = rowsPerPage
  pagination.value.sortBy = sortBy
  pagination.value.descending = descending
  
  loadPaymentMethods()
}

const viewPaymentMethod = (paymentMethod) => {
  selectedPaymentMethod.value = paymentMethod
  showViewDialog.value = true
}

const editPaymentMethod = (paymentMethod) => {
  editMode.value = true
  paymentMethodForm.value = { ...paymentMethod }
  showAddDialog.value = true
}

const deletePaymentMethod = (paymentMethod) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus metode pembayaran "${paymentMethod.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      // Mock delete - replace with actual API call
      await new Promise(resolve => setTimeout(resolve, 500))
      
      const index = paymentMethods.value.findIndex(pm => pm.id === paymentMethod.id)
      if (index > -1) {
        paymentMethods.value.splice(index, 1)
      }
      
      $q.notify({
        type: 'positive',
        message: 'Metode pembayaran berhasil dihapus'
      })
    } catch {
      $q.notify({
        type: 'negative',
        message: 'Gagal menghapus metode pembayaran'
      })
    }
  })
}

const savePaymentMethod = async () => {
  saving.value = true
  try {
    // Mock save - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    if (editMode.value) {
      // Update existing payment method
      const index = paymentMethods.value.findIndex(pm => pm.id === paymentMethodForm.value.id)
      if (index > -1) {
        paymentMethods.value[index] = { ...paymentMethodForm.value }
      }
      $q.notify({
        type: 'positive',
        message: 'Metode pembayaran berhasil diperbarui'
      })
    } else {
      // Add new payment method
      const newPaymentMethod = {
        ...paymentMethodForm.value,
        id: Date.now(),
        created_at: new Date().toISOString()
      }
      paymentMethods.value.unshift(newPaymentMethod)
      $q.notify({
        type: 'positive',
        message: 'Metode pembayaran berhasil ditambahkan'
      })
    }
    
    closeDialog()
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan metode pembayaran'
    })
  } finally {
    saving.value = false
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  paymentMethodForm.value = {
    id: null,
    name: '',
    code: '',
    type: '',
    icon: 'payment',
    color: 'primary',
    description: '',
    fee_percentage: 0,
    fee_fixed: 0,
    notes: '',
    status: 'active'
  }
}

// Lifecycle
onMounted(() => {
  loadPaymentMethods()
})
</script>

<style scoped>
.payment-methods-page {
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

.color-preview {
  width: 20px;
  height: 20px;
  border-radius: var(--theme-radius-sm);
  border: var(--theme-border-light);
  transition: var(--theme-transition-all);
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

@media (max-width: 768px) {
  .payment-methods-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
}
</style>