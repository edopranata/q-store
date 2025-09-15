<template>
  <q-page class="categories-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">Kategori Produk</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">Kelola kategori produk Anda</p>
      </div>
      <q-btn
        color="primary"
        icon="add"
        label="Tambah Kategori"
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
              placeholder="Cari kategori..."
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
        :rows="categories"
        :columns="columns"
        :loading="categoriesStore.table.loading"
        v-model:pagination="pagination"
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
              @click="editCategory(props.row)"
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
              @click="deleteCategory(props.row)"
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
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? 'Edit' : 'Tambah' }} Kategori</div>
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveCategory" class="q-gutter-md">
            <q-input
              v-model="categoryForm.name"
              label="Nama Kategori *"
              outlined
              :rules="[val => !!val || 'Nama kategori wajib diisi']"
            />
            
            <q-input
              v-model="categoryForm.description"
              label="Deskripsi"
              outlined
              type="textarea"
              rows="3"
            />

            <q-select
              v-model="categoryForm.status"
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
            @click="saveCategory"
            :loading="categoriesStore.table.submitting"
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
import { useCategoriesStore } from 'src/stores/categories'

const $q = useQuasar()
const categoriesStore = useCategoriesStore()

// Reactive data
const showAddDialog = ref(false)
const editMode = ref(false)

const categoryForm = ref({
  id: null,
  name: '',
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
    label: 'Nama Kategori',
    align: 'left',
    field: 'name',
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
const categories = computed(() => categoriesStore.getCategories || [])
const pagination = computed(() => categoriesStore.table.pagination)
const filters = computed({
  get: () => categoriesStore.getFilters,
  set: (value) => categoriesStore.setFilters(value)
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
  await categoriesStore.fetchCategories(props)
}

const editCategory = (category) => {
  editMode.value = true
  categoryForm.value = { ...category }
  showAddDialog.value = true
}

const deleteCategory = (category) => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: `Apakah Anda yakin ingin menghapus kategori "${category.name}"?`,
    cancel: true,
    persistent: true
  }).onOk(async () => {
    const success = await categoriesStore.deleteCategory(category.id)
    if (success) {
      await onRequest({ pagination: pagination.value })
    }
  })
}

const saveCategory = async () => {
  let success = false
  
  if (editMode.value) {
    // Update existing category
    success = await categoriesStore.updateCategory(categoryForm.value.id, categoryForm.value)
  } else {
    // Add new category
    success = await categoriesStore.createCategory(categoryForm.value)
  }
  
  if (success) {
    await onRequest({ pagination: pagination.value })
    closeDialog()
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  categoryForm.value = {
    id: null,
    name: '',
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
.categories-page {
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
  .categories-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
}
</style>