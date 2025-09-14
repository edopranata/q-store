<template>
  <q-page class="profile-page">
    <div class="page-header">
      <div class="page-title">
        <h4 class="q-ma-none">Profil Pengguna</h4>
        <p class="text-grey-6 q-ma-none">Kelola informasi profil Anda</p>
      </div>
    </div>

    <div class="row q-col-gutter-lg">
      <!-- Profile Info -->
      <div class="col-md-4 col-xs-12">
        <q-card flat bordered>
          <q-card-section class="text-center">
            <div class="profile-avatar">
              <q-avatar size="120px" color="primary" text-color="white">
                <img v-if="userProfile.avatar" :src="userProfile.avatar" :alt="userProfile.name" />
                <span v-else class="text-h4">{{ getInitials(userProfile.name) }}</span>
              </q-avatar>
              
              <q-btn
                round
                color="primary"
                icon="camera_alt"
                size="sm"
                class="avatar-edit-btn"
                @click="uploadAvatar"
              />
            </div>
            
            <div class="profile-info q-mt-md">
              <h6 class="q-ma-none">{{ userProfile.name }}</h6>
              <p class="text-grey-6 q-ma-none">{{ userProfile.email }}</p>
              <q-badge
                :color="userProfile.is_active ? 'green' : 'red'"
                :label="userProfile.is_active ? 'Aktif' : 'Tidak Aktif'"
                class="q-mt-sm"
              />
            </div>
            
            <div class="profile-stats q-mt-md">
              <div class="stat-item">
                <div class="stat-value">{{ userProfile.stats.login_count }}</div>
                <div class="stat-label">Total Login</div>
              </div>
              <div class="stat-item">
                <div class="stat-value">{{ formatDate(userProfile.last_login) }}</div>
                <div class="stat-label">Login Terakhir</div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Profile Form -->
      <div class="col-md-7 col-xs-12">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Informasi Personal</div>
            
            <q-form @submit="updateProfile" class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model="profileForm.name"
                    label="Nama Lengkap *"
                    outlined
                    :rules="[val => !!val || 'Nama wajib diisi']"
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="profileForm.email"
                    label="Email *"
                    outlined
                    type="email"
                    :rules="[
                      val => !!val || 'Email wajib diisi',
                      val => /.+@.+\..+/.test(val) || 'Format email tidak valid'
                    ]"
                  />
                </div>
              </div>

              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model="profileForm.phone"
                    label="Nomor Telepon"
                    outlined
                  />
                </div>
                <div class="col">
                  <q-input
                    v-model="profileForm.position"
                    label="Jabatan"
                    outlined
                  />
                </div>
              </div>

              <q-input
                v-model="profileForm.address"
                label="Alamat"
                outlined
                type="textarea"
                rows="3"
              />

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Simpan Perubahan"
                  type="submit"
                  :loading="saving"
                />
                <q-btn
                  flat
                  label="Reset"
                  @click="resetForm"
                  class="q-ml-md"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>

        <!-- Change Password -->
        <q-card flat bordered class="q-mt-md">
          <q-card-section>
            <div class="text-subtitle2 q-mb-md">Ubah Password</div>
            
            <q-form @submit="changePassword" class="q-gutter-md">
              <q-input
                v-model="passwordForm.current_password"
                label="Password Saat Ini *"
                outlined
                :type="showCurrentPassword ? 'text' : 'password'"
                :rules="[val => !!val || 'Password saat ini wajib diisi']"
              >
                <template v-slot:append>
                  <q-icon
                    :name="showCurrentPassword ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="showCurrentPassword = !showCurrentPassword"
                  />
                </template>
              </q-input>

              <div class="row q-col-gutter-md">
                <div class="col">
                  <q-input
                    v-model="passwordForm.new_password"
                    label="Password Baru *"
                    outlined
                    :type="showNewPassword ? 'text' : 'password'"
                    :rules="[
                      val => !!val || 'Password baru wajib diisi',
                      val => val.length >= 8 || 'Password minimal 8 karakter'
                    ]"
                  >
                    <template v-slot:append>
                      <q-icon
                        :name="showNewPassword ? 'visibility_off' : 'visibility'"
                        class="cursor-pointer"
                        @click="showNewPassword = !showNewPassword"
                      />
                    </template>
                  </q-input>
                </div>
                <div class="col">
                  <q-input
                    v-model="passwordForm.confirm_password"
                    label="Konfirmasi Password *"
                    outlined
                    :type="showConfirmPassword ? 'text' : 'password'"
                    :rules="[
                      val => !!val || 'Konfirmasi password wajib diisi',
                      val => val === passwordForm.new_password || 'Password tidak cocok'
                    ]"
                  >
                    <template v-slot:append>
                      <q-icon
                        :name="showConfirmPassword ? 'visibility_off' : 'visibility'"
                        class="cursor-pointer"
                        @click="showConfirmPassword = !showConfirmPassword"
                      />
                    </template>
                  </q-input>
                </div>
              </div>

              <div class="form-actions q-mt-lg">
                <q-btn
                  color="primary"
                  label="Ubah Password"
                  type="submit"
                  :loading="changingPassword"
                />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

// Reactive data
const saving = ref(false)
const changingPassword = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const userProfile = ref({
  id: 1,
  name: 'John Doe',
  email: 'john.doe@example.com',
  phone: '081234567890',
  position: 'Store Manager',
  address: 'Jl. Contoh No. 123, Jakarta',
  avatar: '',
  is_active: true,
  last_login: '2024-01-20T10:30:00Z',
  stats: {
    login_count: 145
  }
})

const profileForm = ref({
  name: '',
  email: '',
  phone: '',
  position: '',
  address: ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: ''
})

// Methods
const loadProfile = async () => {
  try {
    // Mock data - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // Copy profile data to form
    profileForm.value = {
      name: userProfile.value.name,
      email: userProfile.value.email,
      phone: userProfile.value.phone,
      position: userProfile.value.position,
      address: userProfile.value.address
    }
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat data profil'
    })
  }
}

const updateProfile = async () => {
  saving.value = true
  try {
    // Mock save - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Update user profile
    Object.assign(userProfile.value, profileForm.value)
    
    $q.notify({
      type: 'positive',
      message: 'Profil berhasil diperbarui'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal memperbarui profil'
    })
  } finally {
    saving.value = false
  }
}

const changePassword = async () => {
  changingPassword.value = true
  try {
    // Mock change password - replace with actual API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Reset password form
    passwordForm.value = {
      current_password: '',
      new_password: '',
      confirm_password: ''
    }
    
    $q.notify({
      type: 'positive',
      message: 'Password berhasil diubah'
    })
  } catch {
    $q.notify({
      type: 'negative',
      message: 'Gagal mengubah password'
    })
  } finally {
    changingPassword.value = false
  }
}

const resetForm = () => {
  profileForm.value = {
    name: userProfile.value.name,
    email: userProfile.value.email,
    phone: userProfile.value.phone,
    position: userProfile.value.position,
    address: userProfile.value.address
  }
}

const uploadAvatar = () => {
  $q.notify({
    type: 'info',
    message: 'Fitur upload avatar akan segera tersedia'
  })
}

const getInitials = (name) => {
  if (!name) return ''
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadProfile()
})
</script>

<style scoped>
.profile-page {
  padding: 24px;
}

.page-header {
  margin-bottom: 24px;
}

.page-title h4 {
  font-weight: 600;
  color: #1976d2;
}

.profile-avatar {
  position: relative;
  display: inline-block;
}

.avatar-edit-btn {
  position: absolute;
  bottom: 0;
  right: 0;
}

.profile-info h6 {
  font-weight: 600;
  color: #333;
}

.profile-stats {
  display: flex;
  justify-content: space-around;
  padding: 16px 0;
  border-top: 1px solid #e0e0e0;
}

.stat-item {
  text-align: center;
}

.stat-value {
  font-size: 1.2em;
  font-weight: 600;
  color: #1976d2;
}

.stat-label {
  font-size: 0.8em;
  color: #666;
  margin-top: 4px;
}

.form-actions {
  text-align: right;
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

@media (max-width: 768px) {
  .profile-page {
    padding: 16px;
  }
  
  .profile-stats {
    flex-direction: column;
    gap: 16px;
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