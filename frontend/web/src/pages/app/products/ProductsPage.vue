<template>
  <q-page class="products-page theme-bg-secondary">
    <div class="page-header theme-bg-card theme-border-light">
      <div class="page-title">
        <h4 class="q-ma-none theme-text-primary theme-font-xl">Produk</h4>
        <p class="text-caption q-ma-none theme-text-secondary theme-font-xs">Kelola data produk dan inventori</p>
      </div>
      <div class="header-actions">
        <q-btn
          flat
          icon="file_download"
          label="Export"
          @click="exportProducts"
          class="q-mr-sm theme-radius-lg theme-font-sm"
        />
        <q-btn
          color="primary"
          icon="add"
          label="Tambah Produk"
          @click="showAddDialog = true"
          class="theme-radius-lg theme-font-sm"
        />
      </div>
    </div>

    <!-- Filters -->
    <q-card class="q-mb-md theme-bg-card theme-border-light theme-radius-lg theme-shadow-light">
      <q-card-section>
        <div class="row q-col-gutter-md">
          <div class="col-md-3 col-sm-6 col-xs-12">
            <q-input
              v-model="filters.search"
              placeholder="Cari produk..."
              outlined
              dense
              clearable
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>
          <div class="col-md-2 col-sm-6 col-xs-12">
            <q-select
              v-model="filters.category"
              :options="categoryOptions"
              label="Kategori"
              outlined
              dense
              clearable
              emit-value
              map-options
            />
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
            />
          </div>
          <div class="col-md-2 col-sm-6 col-xs-12">
            <q-select
              v-model="filters.stockStatus"
              :options="stockStatusOptions"
              label="Status Stok"
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
        :rows="filteredProducts"
        :columns="columns"
        :loading="loading"
        :pagination="pagination"
        @request="onRequest"
        row-key="id"
        binary-state-sort
        class="theme-bg-card"
      >
        <template v-slot:body-cell-image="props">
          <q-td :props="props">
            <q-avatar size="40px" square>
              <img
                v-if="props.row.image"
                :src="props.row.image"
                :alt="props.row.name"
              />
              <q-icon v-else name="image" size="24px" color="grey-5" />
            </q-avatar>
          </q-td>
        </template>

        <template v-slot:body-cell-name="props">
          <q-td :props="props">
            <div>
              <div class="text-weight-medium theme-text-primary theme-font-sm">{{ props.row.name }}</div>
              <div class="text-caption text-grey-6 theme-text-secondary theme-font-xs">{{ props.row.sku }}</div>
            </div>
          </q-td>
        </template>

        <template v-slot:body-cell-price="props">
          <q-td :props="props">
            <div class="text-weight-medium theme-text-accent theme-font-sm">
              Rp {{ props.row.price.toLocaleString('id-ID') }}
            </div>
            <div class="text-caption text-grey-6 theme-text-secondary theme-font-xs" v-if="props.row.cost_price">
              HPP: Rp {{ props.row.cost_price.toLocaleString('id-ID') }}
            </div>
          </q-td>
        </template>

        <template v-slot:body-cell-stock="props">
          <q-td :props="props">
            <div class="row items-center q-col-gutter-xs">
              <span :class="getStockClass(props.row.stock, props.row.min_stock) + ' theme-font-sm'">
                {{ props.row.stock }}
              </span>
              <span class="text-grey-6 theme-text-secondary theme-font-xs">{{ props.row.unit }}</span>
            </div>
            <div class="text-caption text-grey-6 theme-text-secondary theme-font-xs" v-if="props.row.min_stock">
              Min: {{ props.row.min_stock }}
            </div>
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
              @click="viewProduct(props.row)"
              class="q-mr-xs"
            >
              <q-tooltip>Detail</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="edit"
              size="sm"
              @click="editProduct(props.row)"
              class="q-mr-xs"
            >
              <q-tooltip>Edit</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="inventory"
              size="sm"
              color="info"
              @click="adjustStock(props.row)"
              class="q-mr-xs"
            >
              <q-tooltip>Adjust Stok</q-tooltip>
            </q-btn>
            <q-btn
              flat
              round
              icon="delete"
              size="sm"
              color="negative"
              @click="deleteProduct(props.row)"
            >
              <q-tooltip>Hapus</q-tooltip>
            </q-btn>
          </q-td>
        </template>
      </q-table>
    </q-card>

    <!-- Add/Edit Dialog -->
    <q-dialog v-model="showAddDialog" persistent maximized>
      <q-card class="theme-bg-card">
        <q-card-section class="row items-center q-pb-none theme-bg-card theme-border-light">
          <div class="text-h6 theme-text-primary theme-font-lg">{{ editMode ? 'Edit' : 'Tambah' }} Produk</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup class="theme-radius-lg" />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveProduct" class="q-gutter-md">
            <div class="row q-col-gutter-lg">
              <!-- Left Column -->
              <div class="col-md-7 col-xs-12">
                <q-card flat bordered>
                  <q-card-section>
                    <div class="text-subtitle2 q-mb-md">Informasi Dasar</div>
                    
                    <div class="row q-col-gutter-md">
                      <div class="col">
                        <q-input
                          v-model="productForm.name"
                          label="Nama Produk *"
                          outlined
                          :rules="[val => !!val || 'Nama produk wajib diisi']"
                        />
                      </div>
                      <div class="col">
                        <q-input
                          v-model="productForm.sku"
                          label="SKU"
                          outlined
                        />
                      </div>
                    </div>

                    <q-input
                      v-model="productForm.description"
                      label="Deskripsi"
                      outlined
                      type="textarea"
                      rows="3"
                    />

                    <div class="row q-col-gutter-md">
                      <div class="col">
                        <q-select
                          v-model="productForm.category_id"
                          :options="categoryOptions"
                          label="Kategori *"
                          outlined
                          emit-value
                          map-options
                          :rules="[val => !!val || 'Kategori wajib dipilih']"
                        />
                      </div>
                      <div class="col">
                        <q-select
                          v-model="productForm.unit_id"
                          :options="unitOptions"
                          label="Satuan *"
                          outlined
                          emit-value
                          map-options
                          :rules="[val => !!val || 'Satuan wajib dipilih']"
                        />
                      </div>
                    </div>
                  </q-card-section>
                </q-card>

                <q-card flat bordered class="q-mt-md">
                  <q-card-section>
                    <div class="text-subtitle2 q-mb-md">Harga & Stok</div>
                    
                    <div class="row q-col-gutter-md">
                      <div class="col">
                        <q-input
                          v-model.number="productForm.cost_price"
                          label="Harga Pokok (HPP)"
                          outlined
                          type="number"
                          step="0.01"
                          min="0"
                        />
                      </div>
                      <div class="col">
                        <q-input
                          v-model.number="productForm.price"
                          label="Harga Jual *"
                          outlined
                          type="number"
                          step="0.01"
                          min="0"
                          :rules="[val => val > 0 || 'Harga jual harus lebih dari 0']"
                        />
                      </div>
                    </div>

                    <div class="row q-col-gutter-md">
                      <div class="col">
                        <q-input
                          v-model.number="productForm.stock"
                          label="Stok Awal"
                          outlined
                          type="number"
                          min="0"
                        />
                      </div>
                      <div class="col">
                        <q-input
                          v-model.number="productForm.min_stock"
                          label="Stok Minimum"
                          outlined
                          type="number"
                          min="0"
                        />
                      </div>
                    </div>

                    <q-checkbox
                      v-model="productForm.track_stock"
                      label="Lacak Stok"
                    />
                  </q-card-section>
                </q-card>
              </div>

              <!-- Right Column -->
              <div class="col-md-4 col-xs-12">
                <q-card flat bordered>
                  <q-card-section>
                    <div class="text-subtitle2 q-mb-md">Gambar Produk</div>
                    
                    <div class="image-upload-area">
                      <q-img
                        v-if="productForm.image"
                        :src="productForm.image"
                        :alt="productForm.name"
                        class="product-image"
                      />
                      <div v-else class="image-placeholder">
                        <q-icon name="image" size="48px" color="grey-5" />
                        <div class="text-grey-6 q-mt-sm">Belum ada gambar</div>
                      </div>
                      
                      <q-btn
                        flat
                        icon="upload"
                        label="Upload Gambar"
                        @click="uploadImage"
                        class="q-mt-md full-width"
                      />
                    </div>
                  </q-card-section>
                </q-card>

                <q-card flat bordered class="q-mt-md">
                  <q-card-section>
                    <div class="text-subtitle2 q-mb-md">Pengaturan</div>
                    
                    <div class="row q-col-gutter-sm">
                      <div class="col">
                        <q-input
                          v-model="productForm.barcode"
                          label="Barcode"
                          outlined
                        />
                      </div>
                      <div class="col-auto">
                        <q-btn
                          flat
                          icon="qr_code_scanner"
                          @click="scanBarcode"
                          class="q-mt-sm"
                        >
                          <q-tooltip>Scan Barcode</q-tooltip>
                        </q-btn>
                        <q-btn
                          flat
                          icon="auto_awesome"
                          @click="generateBarcode"
                          class="q-mt-sm q-ml-xs"
                        >
                          <q-tooltip>Generate Barcode</q-tooltip>
                        </q-btn>
                      </div>
                    </div>

                    <q-input
                      v-model="productForm.notes"
                      label="Catatan"
                      outlined
                      type="textarea"
                      rows="3"
                      class="q-mt-md"
                    />

                    <q-select
                      v-model="productForm.status"
                      :options="statusOptions"
                      label="Status *"
                      outlined
                      emit-value
                      map-options
                      :rules="[val => !!val || 'Status wajib dipilih']"
                      class="q-mt-md"
                    />
                  </q-card-section>
                </q-card>
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-pa-md">
          <q-btn flat label="Batal" @click="closeDialog" />
          <q-btn
            color="primary"
            label="Simpan"
            @click="saveProduct"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- View Dialog -->
    <q-dialog v-model="showViewDialog">
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">Detail Produk</div>
        </q-card-section>

        <q-card-section v-if="selectedProduct">
          <div class="row q-col-gutter-md">
            <div class="col-auto">
              <q-avatar size="80px" square>
                <img
                  v-if="selectedProduct.image"
                  :src="selectedProduct.image"
                  :alt="selectedProduct.name"
                />
                <q-icon v-else name="image" size="40px" color="grey-5" />
              </q-avatar>
            </div>
            <div class="col">
              <div class="text-h6">{{ selectedProduct.name }}</div>
              <div class="text-caption text-grey-6">{{ selectedProduct.sku }}</div>
              <div class="q-mt-sm">
                <q-badge
                  :color="selectedProduct.status === 'active' ? 'positive' : 'negative'"
                  :label="selectedProduct.status === 'active' ? 'Aktif' : 'Tidak Aktif'"
                />
              </div>
            </div>
          </div>

          <q-separator class="q-my-md" />

          <div class="q-gutter-sm">
            <div v-if="selectedProduct.description"><strong>Deskripsi:</strong> {{ selectedProduct.description }}</div>
            <div><strong>Kategori:</strong> {{ getCategoryName(selectedProduct.category_id) }}</div>
            <div><strong>Satuan:</strong> {{ getUnitName(selectedProduct.unit_id) }}</div>
            <div><strong>Harga Jual:</strong> Rp {{ selectedProduct.price.toLocaleString('id-ID') }}</div>
            <div v-if="selectedProduct.cost_price"><strong>HPP:</strong> Rp {{ selectedProduct.cost_price.toLocaleString('id-ID') }}</div>
            <div><strong>Stok:</strong> {{ selectedProduct.stock }} {{ getUnitName(selectedProduct.unit_id) }}</div>
            <div v-if="selectedProduct.min_stock"><strong>Stok Minimum:</strong> {{ selectedProduct.min_stock }}</div>
            <div v-if="selectedProduct.barcode"><strong>Barcode:</strong> {{ selectedProduct.barcode }}</div>
            <div v-if="selectedProduct.notes"><strong>Catatan:</strong> {{ selectedProduct.notes }}</div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Tutup" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Stock Adjustment Dialog -->
    <q-dialog v-model="showStockDialog" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Adjust Stok</div>
          <div class="text-subtitle2 text-grey-6" v-if="selectedProduct">
            {{ selectedProduct.name }}
          </div>
        </q-card-section>

        <q-card-section>
          <div class="q-gutter-md">
            <div class="row items-center q-col-gutter-md">
              <div class="col-auto">
                <strong>Stok Saat Ini:</strong>
              </div>
              <div class="col">
                <span class="text-h6">{{ selectedProduct?.stock || 0 }}</span>
                <span class="text-grey-6 q-ml-sm">{{ getUnitName(selectedProduct?.unit_id) }}</span>
              </div>
            </div>

            <q-select
              v-model="stockAdjustment.type"
              :options="adjustmentTypeOptions"
              label="Tipe Adjustment *"
              outlined
              emit-value
              map-options
            />

            <q-input
              v-model.number="stockAdjustment.quantity"
              label="Jumlah *"
              outlined
              type="number"
              min="1"
              :rules="[val => val > 0 || 'Jumlah harus lebih dari 0']"
            />

            <q-input
              v-model="stockAdjustment.reason"
              label="Alasan"
              outlined
              type="textarea"
              rows="3"
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Batal" @click="closeStockDialog" />
          <q-btn
            color="primary"
            label="Simpan"
            @click="saveStockAdjustment"
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { productService, categoryService, unitService } from 'src/services'

const $q = useQuasar()

// Reactive data
const loading = ref(false)
const saving = ref(false)
const showAddDialog = ref(false)
const showViewDialog = ref(false)
const showStockDialog = ref(false)
const editMode = ref(false)
const products = ref([])
const categories = ref([])
const units = ref([])
const selectedProduct = ref(null)
const uploading = ref(false)
const filters = ref({
  search: '',
  category: null,
  status: null,
  stockStatus: null
})

const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})

const productForm = ref({
  id: null,
  name: '',
  sku: '',
  description: '',
  category_id: null,
  unit_id: null,
  price: 0,
  cost_price: 0,
  stock: 0,
  min_stock: 0,
  track_stock: true,
  image: '',
  barcode: '',
  notes: '',
  status: 'active'
})

const stockAdjustment = ref({
  type: 'in',
  quantity: 0,
  reason: ''
})

// Options
const statusOptions = [
  { label: 'Aktif', value: 'active' },
  { label: 'Tidak Aktif', value: 'inactive' }
]

const stockStatusOptions = [
  { label: 'Stok Aman', value: 'safe' },
  { label: 'Stok Rendah', value: 'low' },
  { label: 'Stok Habis', value: 'out' }
]

const adjustmentTypeOptions = [
  { label: 'Stok Masuk', value: 'in' },
  { label: 'Stok Keluar', value: 'out' }
]

// Computed
const categoryOptions = computed(() => {
  return categories.value.map(cat => ({
    label: cat.name,
    value: cat.id
  }))
})

const unitOptions = computed(() => {
  return units.value.map(unit => ({
    label: unit.name,
    value: unit.id
  }))
})

const filteredProducts = computed(() => {
  let filtered = [...products.value]
  
  // Search filter
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(search) ||
      product.sku?.toLowerCase().includes(search) ||
      product.barcode?.toLowerCase().includes(search)
    )
  }
  
  // Category filter
  if (filters.value.category) {
    filtered = filtered.filter(product => product.category_id === filters.value.category)
  }
  
  // Status filter
  if (filters.value.status) {
    filtered = filtered.filter(product => product.status === filters.value.status)
  }
  
  // Stock status filter
  if (filters.value.stockStatus) {
    filtered = filtered.filter(product => {
      const stockStatus = getStockStatus(product.stock, product.min_stock)
      return stockStatus === filters.value.stockStatus
    })
  }
  
  return filtered
})

// Table columns
const columns = [
  {
    name: 'image',
    label: 'Gambar',
    align: 'center',
    field: 'image',
    sortable: false
  },
  {
    name: 'name',
    label: 'Nama Produk',
    align: 'left',
    field: 'name',
    sortable: true
  },
  {
    name: 'category',
    label: 'Kategori',
    align: 'left',
    field: 'category_id',
    sortable: true,
    format: (val) => getCategoryName(val)
  },
  {
    name: 'price',
    label: 'Harga',
    align: 'right',
    field: 'price',
    sortable: true
  },
  {
    name: 'stock',
    label: 'Stok',
    align: 'center',
    field: 'stock',
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
    name: 'actions',
    label: 'Aksi',
    align: 'center',
    sortable: false
  }
]

// Methods
const loadProducts = async () => {
  try {
    loading.value = true
    const result = await productService.getProducts({
      page: pagination.value.page,
      per_page: pagination.value.rowsPerPage,
      search: filters.value.search,
      category_id: filters.value.category,
      status: filters.value.status,
      stock_status: filters.value.stockStatus
    })
    
    if (result.success) {
      products.value = result.data
      pagination.value.rowsNumber = result.meta.total || result.data.length
    } else {
      $q.notify({
        type: 'negative',
        message: result.message,
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error loading products:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat memuat data produk',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const result = await categoryService.getCategories()
    if (result.success) {
      categories.value = result.data
    }
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const loadUnits = async () => {
  try {
    const result = await unitService.getUnits()
    if (result.success) {
      units.value = result.data
    }
  } catch (error) {
    console.error('Error loading units:', error)
  }
}

const onRequest = (props) => {
  const { page, rowsPerPage, sortBy, descending } = props.pagination
  
  pagination.value.page = page
  pagination.value.rowsPerPage = rowsPerPage
  pagination.value.sortBy = sortBy
  pagination.value.descending = descending
  
  loadProducts()
}

const getCategoryName = (categoryId) => {
  const category = categories.value.find(cat => cat.id === categoryId)
  return category ? category.name : '-'
}

const getUnitName = (unitId) => {
  const unit = units.value.find(u => u.id === unitId)
  return unit ? unit.name : '-'
}

const getStockStatus = (stock, minStock) => {
  if (stock === 0) return 'out'
  if (minStock && stock <= minStock) return 'low'
  return 'safe'
}

const getStockClass = (stock, minStock) => {
  const status = getStockStatus(stock, minStock)
  switch (status) {
    case 'out': return 'text-negative text-weight-bold'
    case 'low': return 'text-warning text-weight-bold'
    default: return 'text-positive text-weight-bold'
  }
}

const viewProduct = (product) => {
  selectedProduct.value = product
  showViewDialog.value = true
}

const editProduct = (product) => {
  editMode.value = true
  productForm.value = { ...product }
  showAddDialog.value = true
}

const deleteProduct = async (product) => {
  try {
    const confirmed = await new Promise((resolve) => {
      $q.dialog({
        title: 'Konfirmasi',
        message: `Apakah Anda yakin ingin menghapus produk "${product.name}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => resolve(true))
        .onCancel(() => resolve(false))
    })

    if (!confirmed) return

    loading.value = true
    const result = await productService.deleteProduct(product.id)
    
    if (result.success) {
      $q.notify({
        type: 'positive',
        message: 'Produk berhasil dihapus',
        position: 'top'
      })
      await loadProducts()
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Gagal menghapus produk',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error deleting product:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat menghapus produk',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const adjustStock = (product) => {
  selectedProduct.value = product
  stockAdjustment.value = {
    type: 'in',
    quantity: 0,
    reason: ''
  }
  showStockDialog.value = true
}

const saveProduct = async () => {
  try {
    saving.value = true
    
    let result
    if (editMode.value) {
      result = await productService.updateProduct(productForm.value.id, productForm.value)
    } else {
      result = await productService.createProduct(productForm.value)
    }
    
    if (result.success) {
      $q.notify({
        type: 'positive',
        message: `Produk berhasil ${editMode.value ? 'diperbarui' : 'ditambahkan'}`,
        position: 'top'
      })
      closeDialog()
      await loadProducts()
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || `Gagal ${editMode.value ? 'memperbarui' : 'menambahkan'} produk`,
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error saving product:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat menyimpan produk',
      position: 'top'
    })
  } finally {
    saving.value = false
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  editMode.value = false
  productForm.value = {
    id: null,
    name: '',
    sku: '',
    description: '',
    category_id: null,
    unit_id: null,
    price: 0,
    cost_price: 0,
    stock: 0,
    min_stock: 0,
    track_stock: true,
    image: '',
    barcode: '',
    notes: '',
    status: 'active'
  }
}

const uploadImage = () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.onchange = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    
    try {
      uploading.value = true
      const result = await productService.uploadImage(file)
      
      if (result.success) {
        productForm.value.image = result.data.url
        $q.notify({
          type: 'positive',
          message: 'Gambar berhasil diupload',
          position: 'top'
        })
      } else {
        $q.notify({
          type: 'negative',
          message: result.message || 'Gagal mengupload gambar',
          position: 'top'
        })
      }
    } catch (error) {
      console.error('Error uploading image:', error)
      $q.notify({
        type: 'negative',
        message: 'Terjadi kesalahan saat mengupload gambar',
        position: 'top'
      })
    } finally {
      uploading.value = false
    }
  }
  input.click()
}

const saveStockAdjustment = async () => {
  if (!selectedProduct.value) return
  
  try {
    saving.value = true
    const result = await productService.adjustStock(selectedProduct.value.id, stockAdjustment.value)
    
    if (result.success) {
      $q.notify({
        type: 'positive',
        message: 'Stok berhasil disesuaikan',
        position: 'top'
      })
      closeStockDialog()
      await loadProducts()
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Gagal menyesuaikan stok',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error adjusting stock:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat menyesuaikan stok',
      position: 'top'
    })
  } finally {
    saving.value = false
  }
}

const closeStockDialog = () => {
  showStockDialog.value = false
  selectedProduct.value = null
  stockAdjustment.value = {
    type: 'in',
    quantity: 0,
    reason: ''
  }
}

const exportProducts = async () => {
  try {
    const result = await productService.exportProducts('excel', filters.value)
    
    if (result.success) {
      // Create download link
      const url = window.URL.createObjectURL(new Blob([result.data]))
      const link = document.createElement('a')
      link.href = url
      link.setAttribute('download', `products_${new Date().toISOString().split('T')[0]}.xlsx`)
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(url)
      
      $q.notify({
        type: 'positive',
        message: 'Data produk berhasil diexport',
        position: 'top'
      })
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Gagal mengexport data produk',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error exporting products:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat mengexport data produk',
      position: 'top'
    })
  }
}

// Barcode scanning functionality
const scanBarcode = () => {
  // This would integrate with a barcode scanning library
  // For now, we'll show a simple input dialog
  $q.dialog({
    title: 'Scan Barcode',
    message: 'Masukkan atau scan barcode produk:',
    prompt: {
      model: '',
      type: 'text',
      placeholder: 'Barcode...'
    },
    cancel: true,
    persistent: true
  }).onOk(async (barcode) => {
    if (barcode) {
      await searchByBarcode(barcode)
    }
  })
}

const searchByBarcode = async (barcode) => {
  try {
    loading.value = true
    const result = await productService.searchByBarcode(barcode)
    
    if (result.success) {
      // Show product details or add to cart (for POS)
      viewProduct(result.data)
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Produk tidak ditemukan',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error searching by barcode:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat mencari produk',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const generateBarcode = async () => {
  try {
    const result = await productService.generateBarcode()
    
    if (result.success) {
      productForm.value.barcode = result.data.barcode
      $q.notify({
        type: 'positive',
        message: 'Barcode berhasil digenerate',
        position: 'top'
      })
    } else {
      $q.notify({
        type: 'negative',
        message: result.message || 'Gagal generate barcode',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error generating barcode:', error)
    $q.notify({
      type: 'negative',
      message: 'Terjadi kesalahan saat generate barcode',
      position: 'top'
    })
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadCategories(),
    loadUnits()
  ])
})
</script>

<style scoped>
.products-page {
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

.header-actions {
  display: flex;
  gap: var(--theme-spacing-xs);
}

.image-upload-area {
  text-align: center;
}

.product-image {
  width: 200px;
  height: 200px;
  border-radius: var(--theme-radius-lg);
}

.image-placeholder {
  width: 200px;
  height: 200px;
  border: 2px dashed var(--theme-border-light);
  border-radius: var(--theme-radius-lg);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  background-color: var(--theme-bg-secondary);
  transition: var(--theme-transition-all);
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

@media (max-width: 768px) {
  .products-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
  
  .header-actions {
    justify-content: stretch;
  }
  
  .header-actions .q-btn {
    flex: 1;
  }
}
</style>