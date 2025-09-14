<template>
  <q-page class="customers-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">Customer</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">Kelola data customer Anda</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Tambah Customer"
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
                 placeholder="Cari customer..."
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
                label="Tipe Customer"
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
    <q-card class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
      <q-table
          :rows="customers"
          :columns="columns"
          :loading="customersStore.isLoading"
          :pagination="pagination"
          @request="onRequest"
          row-key="id"
          class="customers-table"
          server-side-pagination
        >
        <template v-slot:body-cell-type="props">
          <q-td :props="props">
            <q-badge
              :color="props.row.type === 'individual' ? 'blue' : 'purple'"
              :label="props.row.type === 'individual' ? 'Individu' : 'Perusahaan'"
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
              @click="viewCustomer(props.row)"
              class="q-mr-xs theme-radius-md"
            >
              <q-tooltip>Detail</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="edit"
              size="sm"
              @click="editCustomer(props.row)"
              class="q-mr-xs theme-radius-md"
            >
              <q-tooltip>Edit</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="delete"
              size="sm"
              color="negative"
              @click="deleteCustomer(props.row)"
              class="theme-radius-md"
            >
              <q-tooltip>Hapus</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 500px; max-width: 600px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section>
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? 'Edit' : 'Tambah' }} Customer</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveCustomer" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col">
                <q-select
                  v-model="customerForm.type"
                  :options="typeOptions"
                  label="Tipe Customer *"
                  outlined
                  emit-value
                  map-options
                  :rules="[val => !!val || 'Tipe customer wajib dipilih']"
                />
              </div>
              <div class="col">
                <q-input
                  v-model="customerForm.code"
                  label="Kode Customer"
                  outlined
                />
              </div>
            </div>

            <q-input
              v-model="customerForm.name"
              label="Nama Customer *"
              outlined
              :rules="[val => !!val || 'Nama customer wajib diisi']"
            />

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="customerForm.phone"
                  label="Nomor Telepon"
                  outlined
                />
              </div>
              <div class="col">
                <q-input
                  v-model="customerForm.email"
                  label="Email"
                  outlined
                  type="email"
                />
              </div>
            </div>

            <q-input
              v-model="customerForm.address"
              label="Alamat"
              outlined
              type="textarea"
              rows="3"
            />

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="customerForm.city"
                  label="Kota"
                  outlined
                />
              </div>
              <div class="col">
                <q-input
                  v-model="customerForm.postal_code"
                  label="Kode Pos"
                  outlined
                />
              </div>
            </div>

            <div class="row q-col-gutter-md" v-if="customerForm.type === 'company'">
              <div class="col">
                <q-input
                  v-model="customerForm.company_name"
                  label="Nama Perusahaan"
                  outlined
                />
              </div>
              <div class="col">
                <q-input
                  v-model="customerForm.tax_number"
                  label="NPWP"
                  outlined
                />
              </div>
            </div>

            <q-input
              v-model="customerForm.notes"
              label="Catatan"
              outlined
              type="textarea"
              rows="2"
            />

            <q-select
              v-model="customerForm.status"
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
          <q-btn flat label="Batal" @click="closeDialog" class="theme-radius-md theme-font-sm" />
          <q-btn
            color="primary"
            label="Simpan"
            @click="saveCustomer"
            :loading="customersStore.isSaving"
            class="theme-radius-md theme-font-sm"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- View Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 400px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section>
          <div class="text-h6 theme-text-primary theme-font-lg">Detail Customer</div>
        </q-card-section>

        <q-card-section v-if="selectedCustomer">
          <div class="q-gutter-sm">
            <div>
              <strong>Tipe:</strong>
              <q-badge
                :color="selectedCustomer.type === 'individual' ? 'blue' : 'purple'"
                :label="selectedCustomer.type === 'individual' ? 'Individu' : 'Perusahaan'"
                class="q-ml-sm"
              />
            </div>
            <div><strong>Nama:</strong> {{ selectedCustomer.name }}</div>
            <div v-if="selectedCustomer.code"><strong>Kode:</strong> {{ selectedCustomer.code }}</div>
            <div v-if="selectedCustomer.phone"><strong>Telepon:</strong> {{ selectedCustomer.phone }}</div>
            <div v-if="selectedCustomer.email"><strong>Email:</strong> {{ selectedCustomer.email }}</div>
            <div v-if="selectedCustomer.address"><strong>Alamat:</strong> {{ selectedCustomer.address }}</div>
            <div v-if="selectedCustomer.city"><strong>Kota:</strong> {{ selectedCustomer.city }}</div>
            <div v-if="selectedCustomer.postal_code"><strong>Kode Pos:</strong> {{ selectedCustomer.postal_code }}</div>
            <div v-if="selectedCustomer.company_name"><strong>Perusahaan:</strong> {{ selectedCustomer.company_name }}</div>
            <div v-if="selectedCustomer.tax_number"><strong>NPWP:</strong> {{ selectedCustomer.tax_number }}</div>
            <div v-if="selectedCustomer.notes"><strong>Catatan:</strong> {{ selectedCustomer.notes }}</div>
            <div>
              <strong>Status:</strong>
              <q-badge
                :color="selectedCustomer.status === 'active' ? 'positive' : 'negative'"
                :label="selectedCustomer.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
                class="q-ml-sm"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Tutup" color="primary" v-close-popup class="theme-radius-md theme-font-sm" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useCustomersStore } from 'src/stores/customers'

const $q = useQuasar()

// Store
const customersStore = useCustomersStore()

// Reactive data
const showAddDialog = ref(false)
const showViewDialog = ref(false)
const editMode = ref(false)
const selectedCustomer = ref(null)
const searchTimeout = ref(null)

const customerForm = ref({
  id: null,
  type: 'individual',
  name: '',
  code: '',
  phone: '',
  email: '',
  address: '',
  city: '',
  postal_code: '',
  company_name: '',
  tax_number: '',
  notes: '',
  status: 'active'
})

// Options
const typeOptions = [
  { label: 'Individu', value: 'individual' },
  { label: 'Perusahaan', value: 'company' }
]

const statusOptions = [
  { label: 'Aktif', value: 'active' },
  { label: 'Tidak Aktif', value: 'inactive' }
]

// Table columns
const columns = [
  {
    name: 'name',
    required: true,
    label: 'Nama Customer',
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
    name: 'phone',
    label: 'Telepon',
    align: 'left',
    field: 'phone'
  },
  {
    name: 'email',
    label: 'Email',
    align: 'left',
    field: 'email'
  },
  {
    name: 'city',
    label: 'Kota',
    align: 'left',
    field: 'city'
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
const customers = computed(() => customersStore.getCustomers || [])
const pagination = computed(() => customersStore.getPagination)
const filters = computed(() => customersStore.getFilters)

// Watchers
watch(() => filters.value.search, () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  searchTimeout.value = setTimeout(() => {
    onRequest({ pagination: pagination.value })
  }, 500)
})

watch(() => filters.value.type, () => {
  onRequest({ pagination: pagination.value })
})

watch(() => filters.value.status, () => {
  onRequest({ pagination: pagination.value })
})

// Methods
const onRequest = async (props = {}) => {
  await customersStore.fetchCustomers(props)
}

const viewCustomer = (customer) => {
  selectedCustomer.value = customer
  showViewDialog.value = true
}

const editCustomer = (customer) => {
  editMode.value = true
  customerForm.value = { ...customer }
  showAddDialog.value = true
}

const deleteCustomer = (customer) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus customer "${customer.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await customersStore.deleteCustomer(customer.id)
      $q.notify({
        type: 'positive',
        message: 'Customer berhasil dihapus'
      })
      await onRequest({ pagination: pagination.value })
    } catch (error) {
      console.error('Error deleting customer:', error)
      $q.notify({
        type: 'negative',
        message: 'Gagal menghapus customer'
      })
    }
  })
}

const saveCustomer = async () => {
  try {
    if (customerForm.value.id) {
      await customersStore.updateCustomer(customerForm.value.id, customerForm.value)
    } else {
      await customersStore.createCustomer(customerForm.value)
    }
    $q.notify({
      type: 'positive',
      message: editMode.value ? 'Customer berhasil diperbarui' : 'Customer berhasil ditambahkan'
    })
    closeDialog()
    await onRequest({ pagination: pagination.value })
  } catch (error) {
    console.error('Error saving customer:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat menyimpan customer'
    })
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  customerForm.value = {
    id: null,
    type: 'individual',
    name: '',
    code: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    postal_code: '',
    company_name: '',
    tax_number: '',
    notes: '',
    status: 'active'
  }
}

// Lifecycle
onMounted(() => {
  onRequest()
})
</script>

<style scoped>
.customers-page {
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

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

@media (max-width: 768px) {
  .customers-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
}
</style>