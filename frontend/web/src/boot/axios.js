import { defineBoot } from '#q-app/wrappers'
import axios from 'axios'
import { Notify, LocalStorage } from 'quasar'

// API Configuration
const API_BASE_URL = process.env.VUE_APP_API_BASE_URL || 'http://127.0.0.1:8000/api/v1'

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 10000
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    // Add auth token if available
    const token = LocalStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

export default defineBoot(({ app, router }) => {
  // Response interceptor with router access
  api.interceptors.response.use(
    (response) => {
      return response
    },
    (error) => {
      const { status, data } = error.response || {}
      
      // Handle different error status codes
      switch (status) {
        case 401:
          // Unauthorized - clear auth and redirect to login
           LocalStorage.remove('auth_token')
           LocalStorage.remove('auth_user')
           LocalStorage.remove('auth_permissions')
           delete api.defaults.headers.common['Authorization']
          
          Notify.create({
            type: 'negative',
            message: data?.message || 'Sesi Anda telah berakhir. Silakan login kembali.',
            position: 'top'
          })
          
          // Use router.push instead of window.location.href for better navigation
          if (router && router.currentRoute.value.name !== 'login') {
            router.push({
              name: 'login',
              query: { 
                redirect: router.currentRoute.value.fullPath,
                reason: 'session_expired'
              }
            })
          }
          break
          
        case 403:
          Notify.create({
            type: 'negative',
            message: data?.message || 'Anda tidak memiliki izin untuk mengakses resource ini.',
            position: 'top'
          })
          break
          
        case 422:
          // Validation errors - handle both Laravel format and simple message
          if (data?.errors) {
            const errorMessages = Object.values(data.errors).flat()
            errorMessages.forEach(message => {
              Notify.create({
                type: 'negative',
                message,
                position: 'top'
              })
            })
          } else if (data?.message) {
            Notify.create({
              type: 'negative',
              message: data.message,
              position: 'top'
            })
          } else {
            Notify.create({
              type: 'negative',
              message: 'Data yang dikirim tidak valid.',
              position: 'top'
            })
          }
          break
          
        case 500:
          Notify.create({
            type: 'negative',
            message: 'Terjadi kesalahan pada server. Silakan coba lagi nanti.',
            position: 'top'
          })
          break
          
        default:
          // Network error or other errors
          if (!error.response) {
            Notify.create({
              type: 'negative',
              message: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
              position: 'top'
            })
          } else {
            Notify.create({
              type: 'negative',
              message: data?.message || 'Terjadi kesalahan yang tidak diketahui.',
              position: 'top'
            })
          }
      }
      
      return Promise.reject(error)
    }
  )
  
  // for use inside Vue files (Options API) through this.$axios and this.$api
  app.config.globalProperties.$axios = axios
  app.config.globalProperties.$api = api
})

export { api }
