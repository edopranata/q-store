<template>
  <q-page class="units-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">Satuan Produk</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">Kelola satuan produk Anda</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Tambah Satuan"
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
              placeholder="Cari satuan..."
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
        :rows="units"
        :columns="columns"
        :loading="unitsStore.table.loading"
        v-model:pagination="table.pagination"
        @request="onRequest"
        row-key="id"
        server-side-pagination
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
              icon="edit"
              size="sm"
              @click="editUnit(props.row)"
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
              @click="deleteUnit(props.row)"
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
      <q-card style="min-width: 400px" class="theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium">
        <q-card-section>
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? 'Edit' : 'Tambah' }} Satuan</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveUnit" class="q-gutter-md">
            <q-input
              v-model="unitForm.name"
              label="Nama Satuan *"
              outlined
              :rules="[val => !!val || 'Nama satuan wajib diisi']"
            />
            
            <q-input
              v-model="unitForm.symbol"
              label="Simbol/Singkatan *"
              outlined
              :rules="[val => !!val || 'Simbol wajib diisi']"
            />

            <q-input
              v-model="unitForm.description"
              label="Deskripsi"
              outlined
              type="textarea"
              rows="3"
            />

            <q-select
              v-model="unitForm.status"
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
            @click="saveUnit"
            :loading="unitsStore.table.submitting"
            class="theme-radius-md theme-font-sm"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { useUnitsStore } from 'src/stores/units'

const {table} = useUnitsStore()
const $q = useQuasar()
const unitsStore = useUnitsStore()

// Reactive data
const showAddDialog = ref(false)
const editMode = ref(false)

const unitForm = ref({
  id: null,
  name: '',
  symbol: '',
  description: '',
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
    label: 'Nama Satuan',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'symbol',
    label: 'Simbol',
    align: 'center',
    field: 'symbol',
    sortable: true
  },
  {
    name: 'description',
    label: 'Deskripsi',
    align: 'left',
    field: 'description'
  },
  {
    name: 'products_count',
    label: 'Jumlah Produk',
    align: 'center',
    field: 'products_count',
    sortable: true
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
const units = computed(() => unitsStore.getUnits || [])
const pagination = computed(() => unitsStore.pagination)
const filters = computed({
  get: () => unitsStore.getFilters,
  set: (value) => unitsStore.setFilters(value)
})

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

const searchTimeout = ref(null)

// Methods
const onRequest = async (props) => {
  await unitsStore.fetchUnits(props)
}

const editUnit = (unit) => {
  editMode.value = true
  unitForm.value = { ...unit }
  showAddDialog.value = true
}

const deleteUnit = (unit) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus satuan "${unit.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    const success = await unitsStore.deleteUnit(unit.id)
    if (success) {
      await onRequest({ pagination: pagination.value })
    }
  })
}

const saveUnit = async () => {
  let success = false
  
  if (editMode.value) {
    // Update existing unit
    success = await unitsStore.updateUnit(unitForm.value.id, unitForm.value)
  } else {
    // Add new unit
    success = await unitsStore.createUnit(unitForm.value)
  }
  
  if (success) {
    await onRequest({ pagination: pagination.value })
    closeDialog()
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  unitForm.value = {
    id: null,
    name: '',
    symbol: '',
    description: '',
    status: 'active'
  }
}

// Lifecycle
onMounted(async () => {
  await onRequest({ pagination: pagination.value })
})
</script>

<style scoped>
.units-page {
  padding: var(--theme-spacing-lg);
  background-color: var(--theme-bg-secondary);
  min-height: 100vh;
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
  margin-bottom: var(--theme-spacing-xs);
  transition: var(--theme-transition-all);
}

.page-title p {
  color: var(--theme-text-secondary);
  margin-top: 0;
  transition: var(--theme-transition-all);
}

/* Table styling */
.q-table {
  border-radius: var(--theme-radius-lg);
  overflow: hidden;
  transition: var(--theme-transition-all);
}

.q-table .q-table__top {
  padding: var(--theme-spacing-md) var(--theme-spacing-lg);
}

.q-table .q-table__bottom {
  padding: var(--theme-spacing-md) var(--theme-spacing-lg);
}

/* Dialog styling */
.q-dialog .q-card {
  max-width: 500px;
  width: 100%;
  border-radius: var(--theme-radius-lg);
  transition: var(--theme-transition-all);
}

.q-dialog .q-card-section {
  padding: var(--theme-spacing-lg);
}

.q-dialog .q-card-actions {
  padding: var(--theme-spacing-md) var(--theme-spacing-lg);
}

/* Form styling */
.q-field {
  margin-bottom: var(--theme-spacing-md);
  transition: var(--theme-transition-all);
}

/* Button styling */
.q-btn {
  font-weight: 500;
  text-transform: none;
  transition: var(--theme-transition-all);
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

/* Responsive design */
@media (max-width: 768px) {
  .units-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
  
  .q-dialog .q-card {
    margin: var(--theme-spacing-md);
    max-width: none;
  }
}

@media (max-width: 480px) {
  .units-page {
    padding: var(--theme-spacing-sm);
  }
  
  .q-table .q-table__top,
  .q-table .q-table__bottom {
    padding: var(--theme-spacing-sm) var(--theme-spacing-md);
  }
  
  .q-dialog .q-card-section {
    padding: var(--theme-spacing-md);
  }
  
  .q-dialog .q-card-actions {
    padding: var(--theme-spacing-sm) var(--theme-spacing-md);
  }
}
</style>