<template>
  <q-page class="product-edit-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Edit Produk</h4>
        <p class="text-grey-6 q-ma-none">Perbarui informasi produk</p>
      </div>
      <div class="header-actions">
        <q-btn
          flat
          icon="arrow_back"
          label="Kembali"
          @click="$router.push({ name: 'products' })"
        />
      </div>
    </div>

    <div v-if="loading" class="flex flex-center q-pa-xl">
      <q-spinner size="50px" color="primary" />
    </div>

    <q-form v-else @submit="saveProduct" class="q-gutter-md">
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
                    label="Stok Saat Ini"
                    outlined
                    type="number"
                    min="0"
                    readonly
                    hint="Gunakan fitur adjust stok untuk mengubah stok"
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
                
                <q-btn
                  v-if="productForm.image"
                  flat
                  icon="delete"
                  label="Hapus Gambar"
                  color="negative"
                  @click="removeImage"
                  class="q-mt-sm full-width"
                />
              </div>
            </q-card-section>
          </q-card>

          <q-card flat bordered class="q-mt-md">
            <q-card-section>
              <div class="text-subtitle2 q-mb-md">Pengaturan</div>
              
              <q-input
                v-model="productForm.barcode"
                label="Barcode"
                outlined
              />

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

          <q-card flat bordered class="q-mt-md">
            <q-card-section>
              <div class="text-subtitle2 q-mb-md">Informasi Tambahan</div>
              
              <div class="info-item">
                <span class="label">Dibuat:</span>
                <span class="value">{{ formatDate(productForm.created_at) }}</span>
              </div>
              
              <div class="info-item">
                <span class="label">Diperbarui:</span>
                <span class="value">{{ formatDate(productForm.updated_at) }}</span>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div class="form-actions q-mt-lg">
        <q-btn
          flat
          label="Batal"
          @click="$router.push({ name: 'products' })"
          class="q-mr-md"
        />
        <q-btn
          color="primary"
          label="Simpan Perubahan"
          type="submit"
          :loading="saving"
        />
      </div>
    </q-form>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { useRouter, useRoute } from 'vue-router'

const $q = useQuasar()
const router = useRouter()
const route = useRoute()

// Reactive data
const loading = ref(true)
const saving = ref(false)
const categories = ref([])
const units = ref([])

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
  status: 'active',
  created_at: null,
  updated_at: null
})

// Options
const statusOptions = [
  { label: 'Aktif', value: 'active' },
  { label: 'Tidak Aktif', value: 'inactive' }
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

// Methods
const loadCategories = async () => {
  try {
    // Mock data - replace with actual API call
    categories.value = [
      { id: 1, name: 'Minuman' },
      { id: 2, name: 'Makanan' },
      { id: 3, name: 'Snack' }
    ]
  } catch {
    console.error('Failed to load categories')
  }
}

const loadUnits = async () => {
  try {
    // Mock data - replace with actual API call
    units.value = [
      { id: 1, name: 'Kg' },
      { id: 2, name: 'Pcs' },
      { id: 3, name: 'Box' }
    ]
  } catch {
    console.error('Failed to load units')
  }
}

const loadProduct = async () => {
  try {
    const productId = route.params.id
    
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Mock product data
    productForm.value = {
      id: parseInt(productId),
      name: 'Kopi Arabica Premium',
      sku: 'KAP001',
      description: 'Kopi arabica premium dari dataran tinggi',
      category_id: 1,
      unit_id: 1,
      price: 85000,
      cost_price: 65000,
      stock: 50,
      min_stock: 10,
      track_stock: true,
      image: 'https://via.placeholder.com/150x150?text=Kopi',
      barcode: '1234567890123',
      notes: 'Produk unggulan',
      status: 'active',
      created_at: '2024-01-15T10:30:00Z',
      updated_at: '2024-01-20T14:20:00Z'
    }
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat data produk'
    })
    router.push({ name: 'products' })
  } finally {
    loading.value = false
  }
}

const saveProduct = async () => {
  saving.value = true
  try {
    // Mock save - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    $q.notify({
      type: 'positive',
      message: 'Produk berhasil diperbarui'
    })
    
    // Redirect to products list
    router.push({ name: 'products' })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan perubahan'
    })
  } finally {
    saving.value = false
  }
}

const uploadImage = () => {
  // Mock image upload
  $q.notify({
    type: 'info',
    message: 'Fitur upload gambar akan segera tersedia'
  })
}

const removeImage = () => {
  $q.dialog({
    title: 'Konfirmasi Hapus',
    message: 'Apakah Anda yakin ingin menghapus gambar produk?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    productForm.value.image = ''
    $q.notify({
      type: 'positive',
      message: 'Gambar berhasil dihapus'
    })
  })
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadCategories(),
    loadUnits(),
    loadProduct()
  ])
})
</script>

<style scoped>
.product-edit-page {
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

.image-upload-area {
  text-align: center;
}

.product-image {
  width: 200px;
  height: 200px;
  border-radius: 8px;
}

.image-placeholder {
  width: 200px;
  height: 200px;
  border: 2px dashed #ddd;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.form-actions {
  text-align: right;
  padding: 24px 0;
  border-top: 1px solid #e0e0e0;
}

.info-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.info-item .label {
  font-weight: 500;
  color: #666;
}

.info-item .value {
  color: #333;
}

@media (max-width: 768px) {
  .product-edit-page {
    padding: 16px;
  }
  
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .form-actions {
    text-align: center;
  }
  
  .form-actions .q-btn {
    width: 100%;
    margin: 4px 0;
  }
}
</style>