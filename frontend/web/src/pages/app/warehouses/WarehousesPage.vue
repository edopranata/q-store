<template>
  <q-page class="warehouses-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">Gudang</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">Kelola data gudang Anda</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Tambah Gudang"
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
:model-value="filters.search"
              @update:model-value="val => updateFilters({ search: val })"
              placeholder="Cari gudang..."
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
:model-value="filters.status"
              @update:model-value="val => updateFilters({ status: val })"
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
          :rows="warehouses"
          :columns="columns"
          :loading="warehousesStore.isLoading"
          v-model:pagination="pagination"
          @request="onRequest"
          row-key="id"
          server-side-pagination
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
              @click="viewWarehouse(props.row)"
              class="q-mr-xs theme-radius-md"
            >
              <q-tooltip>Detail</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="edit"
              size="sm"
              @click="editWarehouse(props.row)"
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
              @click="deleteWarehouse(props.row)"
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
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? 'Edit' : 'Tambah' }} Gudang</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveWarehouse" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="warehouseForm.name"
                  label="Nama Gudang *"
                  outlined
                  :rules="[val => !!val || 'Nama gudang wajib diisi']"
                />
              </div>
              <div class="col">
                <q-input
                  v-model="warehouseForm.code"
                  label="Kode Gudang *"
                  outlined
                  :rules="[val => !!val || 'Kode gudang wajib diisi']"
                />
              </div>
            </div>

            <q-input
              v-model="warehouseForm.address"
              label="Alamat"
              outlined
              type="textarea"
              rows="3"
            />

            <div class="row q-col-gutter-md">
              <div class="col">
                <q-input
                  v-model="warehouseForm.manager_name"
                  label="Nama Manager"
                  outlined
                />
              </div>
              <div class="col">
                <q-input
                  v-model="warehouseForm.phone"
                  label="Nomor Telepon"
                  outlined
                />
              </div>
            </div>

            <q-select
              v-model="warehouseForm.status"
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
            @click="saveWarehouse"
            :loading="warehousesStore.getIsSubmitting"
            class="theme-radius-md theme-font-sm"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- View Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 400px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section>
          <div class="text-h6 theme-text-primary theme-font-lg">Detail Gudang</div>
        </q-card-section>

        <q-card-section v-if="selectedWarehouse">
          <div class="q-gutter-sm">
            <div><strong>Nama:</strong> {{ selectedWarehouse.name }}</div>
            <div v-if="selectedWarehouse.code"><strong>Kode:</strong> {{ selectedWarehouse.code }}</div>
            <div v-if="selectedWarehouse.address"><strong>Alamat:</strong> {{ selectedWarehouse.address }}</div>
            <div v-if="selectedWarehouse.manager_name"><strong>Manager:</strong> {{ selectedWarehouse.manager_name }}</div>
            <div v-if="selectedWarehouse.phone"><strong>Telepon:</strong> {{ selectedWarehouse.phone }}</div>
            <div>
              <strong>Status:</strong>
              <q-badge
                :color="selectedWarehouse.status === 'active' ? 'positive' : 'negative'"
                :label="selectedWarehouse.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
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
import { useWarehousesStore } from 'src/stores/warehouses'

const $q = useQuasar()

// Store
const warehousesStore = useWarehousesStore()

// Reactive data
const showAddDialog = ref(false)
const showViewDialog = ref(false)
const editMode = ref(false)
const selectedWarehouse = ref(null)
const searchTimeout = ref(null)

const warehouseForm = ref({
  id: null,
  name: '',
  code: '',
  address: '',
  manager_name: '',
  phone: '',
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
    label: 'Nama Gudang',
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
    name: 'manager_name',
    label: 'Manager',
    align: 'left',
    field: 'manager_name'
  },

  {
    name: 'phone',
    label: 'Telepon',
    align: 'left',
    field: 'phone'
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
const warehouses = computed(() => warehousesStore.getWarehouses || [])
const pagination = computed(() => warehousesStore.pagination)

const filters = computed({
  get: () => warehousesStore.getFilters,
  set: (value) => warehousesStore.setFilters(value)
})

// Watchers
watch(() => filters.value.search, () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }
  searchTimeout.value = setTimeout(() => {
    onRequest({ pagination: pagination.value })
  }, 500)
})

watch(() => filters.value.status, () => {
  onRequest({ pagination: pagination.value })
})

// Methods
const updateFilters = (newFilters) => {
  warehousesStore.setFilters(newFilters)
}

const onRequest = async (props = {}) => {
  await warehousesStore.fetchWarehouses(props)
}

const viewWarehouse = (warehouse) => {
  selectedWarehouse.value = warehouse
  showViewDialog.value = true
}

const editWarehouse = (warehouse) => {
  editMode.value = true
  warehouseForm.value = { ...warehouse }
  showAddDialog.value = true
}

const deleteWarehouse = (warehouse) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus gudang "${warehouse.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await warehousesStore.deleteWarehouse(warehouse.id)
      $q.notify({
        type: 'positive',
        message: 'Gudang berhasil dihapus'
      })
      await onRequest({ pagination: pagination.value })
    } catch {
      $q.notify({
        type: 'negative',
        message: 'Gagal menghapus gudang'
      })
    }
  })
}

const saveWarehouse = async () => {
  try {
    if (warehouseForm.value.id) {
      await warehousesStore.updateWarehouse(warehouseForm.value.id, warehouseForm.value)
    } else {
      await warehousesStore.createWarehouse(warehouseForm.value)
    }
    $q.notify({
      type: 'positive',
      message: editMode.value ? 'Gudang berhasil diperbarui' : 'Gudang berhasil ditambahkan'
    })
    closeDialog()
    await onRequest({ pagination: pagination.value })
  } catch (error) {
    console.error('Error saving warehouse:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat menyimpan gudang'
    })
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  warehouseForm.value = {
    id: null,
    name: '',
    code: '',
    address: '',
    manager_name: '',
    phone: '',
    status: 'active'
  }
}

// Lifecycle
onMounted(() => {
  onRequest()
})
</script>

<style scoped>
.warehouses-page {
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
  box-shadow: var(--theme-shadow-light);
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
  .warehouses-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
}
</style>