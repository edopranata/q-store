import { defineStore } from 'pinia'
import { userService } from 'src/services/userService'
import { Notify } from 'quasar'

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    user: null,
    isLoading: false,
    isSubmitting: false,
    table: {
      pagination: {
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
        sortBy: 'name',
        descending: false
      },
      filters: {
        search: '',
        status: '',
        role: ''
      }
    },
    userOptions: [] // For dropdown/select components
  }),

  getters: {
    getUsers: (state) => state.users,
    getUser: (state) => state.user,
    getUserOptions: (state) => state.userOptions,
    loading: (state) => state.isLoading,
    submitting: (state) => state.isSubmitting,
    pagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    getActiveUsers: (state) => state.users.filter(user => user.status === 'active'),
    getUserById: (state) => (id) => state.users.find(user => user.id === id),
    filteredUsers: (state) => {
      let filtered = state.users || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(user =>
          user.name?.toLowerCase().includes(search) ||
          user.email?.toLowerCase().includes(search) ||
          user.phone?.toLowerCase().includes(search)
        )
      }
      
      if (state.table.filters.status) {
        filtered = filtered.filter(user => user.status === state.table.filters.status)
      }
      
      if (state.table.filters.role) {
        filtered = filtered.filter(user => user.role_id === state.table.filters.role)
      }
      
      return filtered
    }
  },

  actions: {
    /**
     * Fetch users with pagination and filters
     * @param {Object} props - Table request properties
     */
    async fetchUsers(props = {}) {
      this.isLoading = true
      try {
        // Build request parameters
        const params = {
          ...userService.buildPaginationParams(props.pagination || this.table.pagination),
          ...this.table.filters
        }

        // Clean empty parameters
        const cleanParams = userService.cleanParams(params)
        
        const response = await userService.getUsers(cleanParams)
        
        if (response.success) {
          this.users = response.data || []
          
          // Update pagination with server response and sorting info
          if (response.meta) {
            const pagination = props.pagination || this.table.pagination
            this.table.pagination = {
              ...this.table.pagination,
              page: response.meta.current_page,
              rowsPerPage: response.meta.per_page,
              rowsNumber: response.meta.total,
              sortBy: pagination.sortBy || this.table.pagination.sortBy || 'name',
              descending: pagination.descending !== undefined ? pagination.descending : (this.table.pagination.descending || false)
            }
          }
        } else {
          throw new Error(response.message || 'Failed to fetch users')
        }
      } catch (error) {
        console.error('Error fetching users:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to fetch users',
          position: 'top'
        })
        this.users = []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch single user by ID
     * @param {number} id - User ID
     */
    async fetchUser(id) {
      this.isLoading = true
      try {
        const response = await userService.getUser(id)
        
        if (response.success) {
          this.user = response.data
        } else {
          throw new Error(response.message || 'Failed to fetch user')
        }
      } catch (error) {
        console.error('Error fetching user:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to fetch user',
          position: 'top'
        })
        this.user = null
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Create new user
     * @param {Object} userData - User data
     */
    async createUser(userData) {
      this.isSubmitting = true
      try {
        const response = await userService.createUser(userData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'User created successfully',
            position: 'top'
          })
          
          // Refresh users list
          await this.fetchUsers()
          return response.data
        } else {
          throw new Error(response.message || 'Failed to create user')
        }
      } catch (error) {
        console.error('Error creating user:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to create user',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Update existing user
     * @param {number} id - User ID
     * @param {Object} userData - User data
     */
    async updateUser(id, userData) {
      this.isSubmitting = true
      try {
        const response = await userService.updateUser(id, userData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'User updated successfully',
            position: 'top'
          })
          
          // Update user in the list
          const index = this.users.findIndex(user => user.id === id)
          if (index !== -1) {
            this.users[index] = { ...this.users[index], ...response.data }
          }
          
          // Update current user if it's the same
          if (this.user && this.user.id === id) {
            this.user = { ...this.user, ...response.data }
          }
          
          return response.data
        } else {
          throw new Error(response.message || 'Failed to update user')
        }
      } catch (error) {
        console.error('Error updating user:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to update user',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Delete user
     * @param {number} id - User ID
     */
    async deleteUser(id) {
      this.isSubmitting = true
      try {
        const response = await userService.deleteUser(id)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'User deleted successfully',
            position: 'top'
          })
          
          // Remove user from the list
          this.users = this.users.filter(user => user.id !== id)
          
          // Clear current user if it's the same
          if (this.user && this.user.id === id) {
            this.user = null
          }
          
          return true
        } else {
          throw new Error(response.message || 'Failed to delete user')
        }
      } catch (error) {
        console.error('Error deleting user:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to delete user',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Fetch user options for select components
     */
    async fetchUserOptions() {
      try {
        const options = await userService.getUserOptions()
        this.userOptions = options
        return options
      } catch (error) {
        console.error('Error fetching user options:', error)
        this.userOptions = []
        return []
      }
    },

    /**
     * Handle table request (pagination, sorting, filtering)
     * @param {Object} props - Table request properties
     */
    async onTableRequest(props) {
      await this.fetchUsers(props)
    },

    /**
     * Set table filters
     * @param {Object} filters - Filter object
     */
    async setFilters(filters) {
      this.table.filters = { ...this.table.filters, ...filters }
      this.table.pagination.page = 1 // Reset to first page
      await this.fetchUsers()
    },

    /**
     * Set pagination
     * @param {Object} pagination - Pagination object
     */
    setPagination(pagination) {
      this.table.pagination = {
        ...this.table.pagination,
        ...pagination
      }
    },

    /**
     * Clear current user
     */
    clearUser() {
      this.user = null
    },

    /**
     * Clear all filters
     */
    clearFilters() {
      this.table.filters = {
        search: '',
        status: '',
        role: ''
      }
    },

    /**
     * Reset pagination to default
     */
    resetPagination() {
      this.table.pagination = {
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
        sortBy: 'name',
        descending: false
      }
    },

    /**
     * Search users by term
     * @param {string} searchTerm - Search term
     */
    async searchUsers(searchTerm) {
      await this.setFilters({ search: searchTerm })
    },

    /**
     * Filter users by status
     * @param {string} status - Status filter
     */
    async filterByStatus(status) {
      await this.setFilters({ status })
    },

    /**
     * Filter users by role
     * @param {string} role - Role filter
     */
    async filterByRole(role) {
      await this.setFilters({ role })
    }
  }
})

export default useUsersStore