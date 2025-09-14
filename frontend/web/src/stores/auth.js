import { defineStore } from 'pinia'
import { api } from 'boot/axios'
import { Notify, LocalStorage } from 'quasar'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: LocalStorage.getItem('auth_token') || null,
    permissions: LocalStorage.getItem('auth_permissions') || [],
    isLoading: false,
    isAuthenticated: false
  }),

  getters: {
    getUser: (state) => state.user,
    getToken: (state) => state.token,
    getPermissions: (state) => state.permissions,
    isLoggedIn: (state) => !!state.token && !!state.user,
    hasPermission: (state) => (permission) => {
      return state.permissions.includes(permission)
    },
    hasAnyPermission: (state) => (permissions) => {
      return permissions.some(permission => state.permissions.includes(permission))
    },
    hasAllPermissions: (state) => (permissions) => {
      return permissions.every(permission => state.permissions.includes(permission))
    }
  },

  actions: {
    /**
     * Login user
     * @param {Object} credentials - Login credentials
     * @param {string} credentials.username - Username or email
     * @param {string} credentials.password - User password
     * @returns {Promise<boolean>} Success status
     */
    async login(credentials) {
      this.isLoading = true
      try {
        const response = await api.post('/auth/login', credentials)
        
        // Handle different response formats from Laravel Sanctum
        let user, token, permissions
        
        if (response.data.success) {
          // Standard API response format
          const data = response.data.data
          user = data.user
          token = data.token
          permissions = data.permissions || []
        } else if (response.data.token) {
          // Direct token response
          token = response.data.token
          user = response.data.user
          permissions = response.data.permissions || []
        } else if (response.data.access_token) {
          // Laravel Sanctum format
          token = response.data.access_token
          user = response.data.user
          permissions = response.data.permissions || []
        } else {
          throw new Error('Format respons login tidak valid')
        }
        
        this.user = user
        this.token = token
        this.permissions = permissions
        this.isAuthenticated = true
        
        // Store in localStorage
        LocalStorage.set('auth_token', token)
        LocalStorage.set('auth_user', user)
        LocalStorage.set('auth_permissions', permissions)
        
        // Set authorization header
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
        
        Notify.create({
          type: 'positive',
          message: `Selamat datang, ${user.name || user.username}!`,
          position: 'top'
        })
        
        return true
      } catch (error) {
        console.error('Login error:', error)
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Login failed',
          position: 'top'
        })
        return false
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Logout user
     */
    async logout() {
      this.isLoading = true
      try {
        // Call logout API if token exists
        if (this.token) {
          await api.post('/auth/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear state and localStorage
        this.user = null
        this.token = null
        this.permissions = []
        this.isAuthenticated = false
        
        LocalStorage.remove('auth_token')
        LocalStorage.remove('auth_user')
        LocalStorage.remove('auth_permissions')
        
        // Remove authorization header
        delete api.defaults.headers.common['Authorization']
        
        this.isLoading = false
        
        Notify.create({
          type: 'info',
          message: 'Logged out successfully',
          position: 'top'
        })
      }
    },

    /**
     * Initialize auth state from localStorage
     */
    initializeAuth() {
      const token = LocalStorage.getItem('auth_token')
      const user = LocalStorage.getItem('auth_user')
      const permissions = LocalStorage.getItem('auth_permissions')
      
      if (token && user) {
        this.token = token
        this.user = user
        this.permissions = permissions || []
        this.isAuthenticated = true
        
        // Set authorization header
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`
      }
    },

    /**
     * Refresh user data
     */
    async refreshUser() {
      if (!this.token) return false
      
      try {
        const response = await api.get('/auth/me')
        
        if (response.data.success) {
          this.user = response.data.data.user
          this.permissions = response.data.data.permissions || []
          
          // Update localStorage
          LocalStorage.set('auth_user', this.user)
          LocalStorage.set('auth_permissions', this.permissions)
          
          return true
        }
      } catch (error) {
        console.error('Refresh user error:', error)
        // If refresh fails, logout user
        await this.logout()
        return false
      }
    },

    /**
     * Forgot password
     * @param {string} email - User email
     * @returns {Promise<boolean>} Success status
     */
    async forgotPassword(email) {
      this.isLoading = true
      try {
        const response = await api.post('/auth/forgot-password', { email })
        
        if (response.data.success) {
          Notify.create({
            type: 'positive',
            message: 'Password reset link sent to your email',
            position: 'top'
          })
          return true
        } else {
          throw new Error(response.data.message || 'Failed to send reset link')
        }
      } catch (error) {
        console.error('Forgot password error:', error)
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to send reset link',
          position: 'top'
        })
        return false
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Reset password
     * @param {Object} data - Reset password data
     * @param {string} data.token - Reset token
     * @param {string} data.email - User email
     * @param {string} data.password - New password
     * @param {string} data.password_confirmation - Password confirmation
     * @returns {Promise<boolean>} Success status
     */
    async resetPassword(data) {
      this.isLoading = true
      try {
        const response = await api.post('/auth/reset-password', data)
        
        if (response.data.success) {
          Notify.create({
            type: 'positive',
            message: 'Password reset successfully',
            position: 'top'
          })
          return true
        } else {
          throw new Error(response.data.message || 'Failed to reset password')
        }
      } catch (error) {
        console.error('Reset password error:', error)
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Failed to reset password',
          position: 'top'
        })
        return false
      } finally {
        this.isLoading = false
      }
    }
  }
})