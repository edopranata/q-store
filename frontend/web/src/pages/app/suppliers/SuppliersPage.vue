<template>
  <q-page class="suppliers-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">Supplier</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">Kelola data supplier Anda</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Tambah Supplier"
        @click="addSupplier"
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
              placeholder="Cari supplier..."
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
        :rows="suppliers"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        row-key="id"
        binary-state-sort
      >
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
              @click="viewSupplier(props.row)"
              class="q-mr-xs theme-radius-md"
            >
              <q-tooltip>Detail</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="edit"
              size="sm"
              @click="editSupplier(props.row)"
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
              @click="deleteSupplier(props.row)"
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
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? 'Edit' : 'Tambah' }} Supplier</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveSupplier" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="supplierForm.name"
                  label="Nama Supplier *"
                  outlined
                  :rules="[val => !!val || 'Nama supplier wajib diisi']"
                />
              </div>
              <div class="col">
                <q-input
                  v-model="supplierForm.code"
                  label="Kode Supplier"
                  outlined
                />
              </div>
            </div>

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="supplierForm.contact_person"
                  label="Nama Kontak"
                  outlined
                />
              </div>
              <div class="col">
                <q-input
                  v-model="supplierForm.phone"
                  label="Nomor Telepon"
                  outlined
                />
              </div>
            </div>

            <q-input
                v-model="supplierForm.email"
              label="Email"
              outlined
              type="email"
            />

            <q-input
                v-model="supplierForm.address"
              label="Alamat"
              outlined
              type="textarea"
              rows="3"
            />

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="supplierForm.city"
                  label="Kota"
                  outlined
                />
              </div>
              <div class="col">
                <q-input
                  v-model="supplierForm.postal_code"
                  label="Kode Pos"
                  outlined
                />
              </div>
            </div>

            <q-input
                v-model="supplierForm.notes"
              label="Catatan"
              outlined
              type="textarea"
              rows="2"
            />

            <q-select
                v-model="supplierForm.status"
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
            @click="saveSupplier"
            :loading="saving"
            class="theme-radius-md theme-font-sm"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- View Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 400px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section>
          <div class="text-h6 theme-text-primary theme-font-lg">Detail Supplier</div>
        </q-card-section>

        <q-card-section v-if="selectedSupplier">
          <div class="q-gutter-sm">
            <div><strong>Nama:</strong> {{ selectedSupplier.name }}</div>
            <div v-if="selectedSupplier.code"><strong>Kode:</strong> {{ selectedSupplier.code }}</div>
            <div v-if="selectedSupplier.contact_person"><strong>Kontak:</strong> {{ selectedSupplier.contact_person }}</div>
            <div v-if="selectedSupplier.phone"><strong>Telepon:</strong> {{ selectedSupplier.phone }}</div>
            <div v-if="selectedSupplier.email"><strong>Email:</strong> {{ selectedSupplier.email }}</div>
            <div v-if="selectedSupplier.address"><strong>Alamat:</strong> {{ selectedSupplier.address }}</div>
            <div v-if="selectedSupplier.city"><strong>Kota:</strong> {{ selectedSupplier.city }}</div>
            <div v-if="selectedSupplier.postal_code"><strong>Kode Pos:</strong> {{ selectedSupplier.postal_code }}</div>
            <div v-if="selectedSupplier.notes"><strong>Catatan:</strong> {{ selectedSupplier.notes }}</div>
            <div>
              <strong>Status:</strong>
              <q-badge
                :color="selectedSupplier.status === 'active' ? 'positive' : 'negative'"
                :label="selectedSupplier.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
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
import { useSuppliersStore } from 'src/stores/suppliers'

const $q = useQuasar()
const suppliersStore = useSuppliersStore()

// Reactive data
const showAddDialog = ref(false)
const showViewDialog = ref(false)
const editMode = ref(false)
const selectedSupplier = ref(null)
const searchTimeout = ref(null)

const supplierForm = ref({
  id: null,
  name: '',
  code: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  city: '',
  postal_code: '',
  notes: '',
  status: 'active'
})

// Options
const statusOptions = [
  { label: 'Aktif', value: 'active' },
  { label: 'Tidak Aktif', value: 'inactive' }
]

// Table columns
const columns = [
  {
    name: 'name',
    required: true,
    label: 'Nama Supplier',
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
    name: 'contact_person',
    label: 'Kontak',
    align: 'left',
    field: 'contact_person'
  },
  {
    name: 'phone',
    label: 'Telepon',
    align: 'left',
    field: 'phone'
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
const suppliers = computed(() => suppliersStore.all || [])
const pagination = computed(() => suppliersStore.pagination)
const filters = computed({
  get: () => suppliersStore.getFilters,
  set: (value) => suppliersStore.setFilters(value)
})
const loading = computed(() => suppliersStore.loading)
const saving = computed(() => suppliersStore.saving)

// Watch filters for auto-search
watch(() => filters.value.search, (newSearch) => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  searchTimeout.value = setTimeout(() => {
    onRequest({ pagination: pagination.value, filter: newSearch })
  }, 500)
}, { deep: true })

watch(() => filters.value.status, () => {
  onRequest({ pagination: pagination.value })
})

// Methods
const onRequest = async (props) => {
  await suppliersStore.fetchSuppliers(props)
}

const viewSupplier = (supplier) => {
  selectedSupplier.value = supplier
  showViewDialog.value = true
}

const addSupplier = () => {
  editMode.value = false
  Object.assign(supplierForm.value, {
    id: null,
    name: '',
    code: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    postal_code: '',
    notes: '',
    status: 'active'
  })
  showAddDialog.value = true
}

const editSupplier = (supplier) => {
  editMode.value = true
  Object.assign(supplierForm.value, supplier)
  showAddDialog.value = true
}

const deleteSupplier = (supplier) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus supplier "${supplier.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    const success = await suppliersStore.deleteSupplier(supplier.id)
    if (success) {
      $q.notify({
        type: 'positive',
        message: 'Supplier berhasil dihapus'
      })
      await onRequest({ pagination: pagination.value })
    } else {
      $q.notify({
        type: 'negative',
        message: 'Gagal menghapus supplier'
      })
    }
  })
}

const saveSupplier = async () => {
  try {
    if (supplierForm.value.id) {
      await suppliersStore.updateSupplier(supplierForm.value.id, supplierForm.value)
    } else {
      await suppliersStore.createSupplier(supplierForm.value)
    }
    $q.notify({
      type: 'positive',
      message: editMode.value ? 'Supplier berhasil diperbarui' : 'Supplier berhasil ditambahkan'
    })
    closeDialog()
    await onRequest({ pagination: pagination.value })
  } catch (error) {
    console.error('Error saving supplier:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat menyimpan supplier'
    })
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  Object.assign(supplierForm.value, {
    id: null,
    name: '',
    code: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    city: '',
    postal_code: '',
    notes: '',
    status: 'active'
  })
}

// Lifecycle
onMounted(() => {
  onRequest({ pagination: pagination.value })
})
</script>

<style scoped>
.suppliers-page {
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
  border: var(--theme-border-light);
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

@media (max-width: 768px) {
  .suppliers-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
}
</style>