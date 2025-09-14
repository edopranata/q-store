<template>
  <q-page class="settings-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Pengaturan Sistem</h4>
        <p class="text-caption q-ma-none">Kelola konfigurasi aplikasi dan preferensi sistem</p>
      </div>
    </div>

    <div class="row q-col-gutter-lg">
      <!-- Settings Navigation -->
      <div class="col-md-3 col-xs-12">
        <q-card flat bordered>
          <q-list>
            <q-item
              v-for="section in settingSections"
              :key="section.key"
              clickable
              :active="activeSection === section.key"
              @click="activeSection = section.key"
              class="setting-nav-item"
            >
              <q-item-section avatar>
                <q-icon :name="section.icon" />
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ section.label }}</q-item-label>
                <q-item-label caption>{{ section.description }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <!-- Settings Content -->
      <div class="col-md-8 col-xs-12">
        <!-- General Settings -->
        <q-card v-if="activeSection === 'general'" flat bordered>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Pengaturan Umum</div>
            
            <q-form @submit="saveGeneralSettings" class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model="generalSettings.store_name"
                    label="Nama Toko *"
                    outlined
                    :rules="[val => !!val || 'Nama toko wajib diisi']"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="generalSettings.store_code"
                    label="Kode Toko"
                    outlined
                  />
                </div>
              </div>

              <q-input
                v-model="generalSettings.store_address"
                label="Alamat Toko"
                outlined
                type="textarea"
                rows="3"
              />

              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model="generalSettings.store_phone"
                    label="Telepon Toko"
                    outlined
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="generalSettings.store_email"
                    label="Email Toko"
                    outlined
                    type="email"
                  />
                </div>
              </div>

              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-select
                    v-model="generalSettings.currency"
                    label="Mata Uang"
                    outlined
                    :options="currencyOptions"
                    option-label="label"
                    option-value="value"
                    emit-value
                    map-options
                  />
                </div>
                <div class="col">
                  <q-select
                    v-model="generalSettings.timezone"
                    label="Zona Waktu"
                    outlined
                    :options="timezoneOptions"
                    option-label="label"
                    option-value="value"
                    emit-value
                    map-options
                  />
                </div>
              </div>

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Simpan Pengaturan"
                  type="submit"
                  :loading="saving.general"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>

        <!-- POS Settings -->
        <q-card v-if="activeSection === 'pos'" flat bordered>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Pengaturan POS</div>
            
            <q-form @submit="savePOSSettings" class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model.number="posSettings.tax_rate"
                    label="Tarif Pajak (%)"
                    outlined
                    type="number"
                    step="0.01"
                    min="0"
                    max="100"
                  />
                </div>
                <div class="col">
                  <q-select
                    v-model="posSettings.default_payment_method"
                    label="Metode Pembayaran Default"
                    outlined
                    :options="paymentMethodOptions"
                    option-label="name"
                    option-value="id"
                    emit-value
                    map-options
                  />
                </div>
              </div>

              <div class="setting-group">
                <div class="text-subtitle2 q-mb-sm">Opsi Transaksi</div>
                <q-toggle
                  v-model="posSettings.auto_print_receipt"
                  label="Cetak struk otomatis"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="posSettings.allow_discount"
                  label="Izinkan diskon"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="posSettings.require_customer"
                  label="Wajib pilih pelanggan"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="posSettings.show_stock_warning"
                  label="Tampilkan peringatan stok"
                />
              </div>

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Simpan Pengaturan"
                  type="submit"
                  :loading="saving.pos"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>

        <!-- Inventory Settings -->
        <q-card v-if="activeSection === 'inventory'" flat bordered>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Pengaturan Inventori</div>
            
            <q-form @submit="saveInventorySettings" class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model.number="inventorySettings.low_stock_threshold"
                    label="Batas Stok Minimum"
                    outlined
                    type="number"
                    min="0"
                  />
                </div>
                <div class="col">
                  <q-select
                    v-model="inventorySettings.stock_method"
                    label="Metode Penilaian Stok"
                    outlined
                    :options="stockMethodOptions"
                    option-label="label"
                    option-value="value"
                    emit-value
                    map-options
                  />
                </div>
              </div>

              <div class="setting-group">
                <div class="text-subtitle2 q-mb-sm">Opsi Stok</div>
                <q-toggle
                  v-model="inventorySettings.auto_reduce_stock"
                  label="Kurangi stok otomatis saat penjualan"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="inventorySettings.allow_negative_stock"
                  label="Izinkan stok negatif"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="inventorySettings.track_expiry_date"
                  label="Lacak tanggal kedaluwarsa"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="inventorySettings.enable_barcode"
                  label="Aktifkan barcode"
                />
              </div>

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Simpan Pengaturan"
                  type="submit"
                  :loading="saving.inventory"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>

        <!-- Notification Settings -->
        <q-card v-if="activeSection === 'notifications'" flat bordered>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Pengaturan Notifikasi</div>
            
            <q-form @submit="saveNotificationSettings" class="q-gutter-md">
              <div class="setting-group">
                <div class="text-subtitle2 q-mb-sm">Email Notifications</div>
                <q-toggle
                  v-model="notificationSettings.email_low_stock"
                  label="Stok menipis"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="notificationSettings.email_new_order"
                  label="Pesanan baru"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="notificationSettings.email_daily_report"
                  label="Laporan harian"
                  class="q-mb-sm"
                />
              </div>

              <div class="setting-group">
                <div class="text-subtitle2 q-mb-sm">System Notifications</div>
                <q-toggle
                  v-model="notificationSettings.system_low_stock"
                  label="Peringatan stok menipis"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="notificationSettings.system_backup_reminder"
                  label="Pengingat backup"
                  class="q-mb-sm"
                />
                <q-toggle
                  v-model="notificationSettings.system_update_available"
                  label="Update tersedia"
                />
              </div>

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Simpan Pengaturan"
                  type="submit"
                  :loading="saving.notifications"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>

        <!-- Backup Settings -->
        <q-card v-if="activeSection === 'backup'" flat bordered>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Pengaturan Backup</div>
            
            <div class="backup-info q-mb-md">
              <q-banner class="bg-blue-1 text-blue-8">
                <template v-slot:avatar>
                  <q-icon name="info" />
                </template>
                Backup terakhir: {{ lastBackupDate || 'Belum pernah backup' }}
              </q-banner>
            </div>

            <q-form @submit="saveBackupSettings" class="q-gutter-md">
              <q-toggle
                v-model="backupSettings.auto_backup"
                label="Backup otomatis"
                class="q-mb-md"
              />

              <div v-if="backupSettings.auto_backup">
                <q-select
                  v-model="backupSettings.backup_frequency"
                  label="Frekuensi Backup"
                  outlined
                  :options="backupFrequencyOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  class="q-mb-md"
                />

                <q-input
                  v-model="backupSettings.backup_time"
                  label="Waktu Backup"
                  outlined
                  type="time"
                  class="q-mb-md"
                />
              </div>

              <div class="backup-actions q-gutter-md">
                <q-btn
                  color="primary"
                  label="Backup Sekarang"
                  icon="backup"
                  @click="createBackup"
                  :loading="creatingBackup"
                />
                <q-btn
                  color="secondary"
                  label="Restore Backup"
                  icon="restore"
                  @click="showRestoreDialog = true"
                />
              </div>

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Simpan Pengaturan"
                  type="submit"
                  :loading="saving.backup"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <!-- Restore Dialog -->
    <q-dialog v-model="showRestoreDialog">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Restore Backup</div>
        </q-card-section>

        <q-card-section>
          <q-file
            v-model="restoreFile"
            label="Pilih file backup"
            outlined
            accept=".sql,.zip"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Batal" v-close-popup />
          <q-btn
            color="primary"
            label="Restore"
            @click="restoreBackup"
            :loading="restoring"
            :disable="!restoreFile"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Reactive data
const activeSection = ref('general')
const showRestoreDialog = ref(false)
const restoreFile = ref(null)
const creatingBackup = ref(false)
const restoring = ref(false)
const lastBackupDate = ref('')

const saving = ref({
  general: false,
  pos: false,
  inventory: false,
  notifications: false,
  backup: false
})

const settingSections = ref([
  {
    key: 'general',
    label: 'Umum',
    description: 'Pengaturan dasar aplikasi',
    icon: 'settings'
  },
  {
    key: 'pos',
    label: 'POS',
    description: 'Konfigurasi point of sale',
    icon: 'point_of_sale'
  },
  {
    key: 'inventory',
    label: 'Inventori',
    description: 'Manajemen stok dan produk',
    icon: 'inventory'
  },
  {
    key: 'notifications',
    label: 'Notifikasi',
    description: 'Pengaturan pemberitahuan',
    icon: 'notifications'
  },
  {
    key: 'backup',
    label: 'Backup',
    description: 'Cadangan dan pemulihan data',
    icon: 'backup'
  }
])

const generalSettings = ref({
  store_name: 'Toko ABC',
  store_code: 'ABC001',
  store_address: 'Jl. Contoh No. 123, Jakarta',
  store_phone: '021-12345678',
  store_email: 'info@tokoabc.com',
  currency: 'IDR',
  timezone: 'Asia/Jakarta'
})

const posSettings = ref({
  tax_rate: 10,
  default_payment_method: 1,
  auto_print_receipt: true,
  allow_discount: true,
  require_customer: false,
  show_stock_warning: true
})

const inventorySettings = ref({
  low_stock_threshold: 10,
  stock_method: 'fifo',
  auto_reduce_stock: true,
  allow_negative_stock: false,
  track_expiry_date: true,
  enable_barcode: true
})

const notificationSettings = ref({
  email_low_stock: true,
  email_new_order: true,
  email_daily_report: false,
  system_low_stock: true,
  system_backup_reminder: true,
  system_update_available: true
})

const backupSettings = ref({
  auto_backup: true,
  backup_frequency: 'daily',
  backup_time: '02:00'
})

// Options
const currencyOptions = ref([
  { label: 'Indonesian Rupiah (IDR)', value: 'IDR' },
  { label: 'US Dollar (USD)', value: 'USD' },
  { label: 'Euro (EUR)', value: 'EUR' }
])

const timezoneOptions = ref([
  { label: 'Asia/Jakarta (WIB)', value: 'Asia/Jakarta' },
  { label: 'Asia/Makassar (WITA)', value: 'Asia/Makassar' },
  { label: 'Asia/Jayapura (WIT)', value: 'Asia/Jayapura' }
])

const paymentMethodOptions = ref([
  { id: 1, name: 'Tunai' },
  { id: 2, name: 'Kartu Debit' },
  { id: 3, name: 'Kartu Kredit' },
  { id: 4, name: 'Transfer Bank' }
])

const stockMethodOptions = ref([
  { label: 'FIFO (First In First Out)', value: 'fifo' },
  { label: 'LIFO (Last In First Out)', value: 'lifo' },
  { label: 'Average Cost', value: 'average' }
])

const backupFrequencyOptions = ref([
  { label: 'Harian', value: 'daily' },
  { label: 'Mingguan', value: 'weekly' },
  { label: 'Bulanan', value: 'monthly' }
])

// Methods
const loadSettings = async () => {
  try {
    // Mock data - replace with actual API calls
    await new Promise(resolve => setTimeout(resolve, 500))
    lastBackupDate.value = '2024-01-20 02:00:00'
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat pengaturan'
    })
  }
}

const saveGeneralSettings = async () => {
  saving.value.general = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    $q.notify({
      type: 'positive',
      message: 'Pengaturan umum berhasil disimpan'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan pengaturan umum'
    })
  } finally {
    saving.value.general = false
  }
}

const savePOSSettings = async () => {
  saving.value.pos = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    $q.notify({
      type: 'positive',
      message: 'Pengaturan POS berhasil disimpan'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan pengaturan POS'
    })
  } finally {
    saving.value.pos = false
  }
}

const saveInventorySettings = async () => {
  saving.value.inventory = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    $q.notify({
      type: 'positive',
      message: 'Pengaturan inventori berhasil disimpan'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan pengaturan inventori'
    })
  } finally {
    saving.value.inventory = false
  }
}

const saveNotificationSettings = async () => {
  saving.value.notifications = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    $q.notify({
      type: 'positive',
      message: 'Pengaturan notifikasi berhasil disimpan'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan pengaturan notifikasi'
    })
  } finally {
    saving.value.notifications = false
  }
}

const saveBackupSettings = async () => {
  saving.value.backup = true
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    $q.notify({
      type: 'positive',
      message: 'Pengaturan backup berhasil disimpan'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal menyimpan pengaturan backup'
    })
  } finally {
    saving.value.backup = false
  }
}

const createBackup = async () => {
  creatingBackup.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 3000))
    lastBackupDate.value = new Date().toLocaleString('id-ID')
    $q.notify({
      type: 'positive',
      message: 'Backup berhasil dibuat'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal membuat backup'
    })
  } finally {
    creatingBackup.value = false
  }
}

const restoreBackup = async () => {
  restoring.value = true
  try {
    await new Promise(resolve => setTimeout(resolve, 2000))
    showRestoreDialog.value = false
    restoreFile.value = null
    $q.notify({
      type: 'positive',
      message: 'Backup berhasil dipulihkan'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memulihkan backup'
    })
  } finally {
    restoring.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadSettings()
})
</script>

<style scoped>
.settings-page {
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

.setting-nav-item {
  border-radius: var(--theme-radius-md);
  margin: var(--theme-spacing-xs) var(--theme-spacing-sm);
  transition: var(--theme-transition-all);
}

.setting-nav-item.q-item--active {
  background-color: var(--theme-bg-hover);
  color: var(--theme-primary);
}

.setting-group {
  padding: var(--theme-spacing-md);
  background-color: var(--theme-bg-card);
  border-radius: var(--theme-radius-md);
  margin: var(--theme-spacing-md) 0;
  border: var(--theme-border-light);
  transition: var(--theme-transition-all);
}

.setting-group .text-subtitle2 {
  color: var(--theme-text-primary);
  font-weight: 600;
  transition: var(--theme-transition-all);
}

.form-actions {
  text-align: right;
  padding-top: var(--theme-spacing-md);
  border-top: var(--theme-border-light);
}

.backup-info {
  margin-bottom: var(--theme-spacing-lg);
}

.backup-actions {
  display: flex;
  gap: var(--theme-spacing-md);
  margin: var(--theme-spacing-lg) 0;
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}

@media (max-width: 768px) {
  .settings-page {
    padding: var(--theme-spacing-md);
  }
  
  .page-header {
    flex-direction: column;
    gap: var(--theme-spacing-md);
    align-items: stretch;
  }
  
  .backup-actions {
    flex-direction: column;
  }
  
  .backup-actions .q-btn {
    width: 100%;
  }
  
  .form-actions {
    text-align: center;
  }
  
  .form-actions .q-btn {
    width: 100%;
  }
}
</style>