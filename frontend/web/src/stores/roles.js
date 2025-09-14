import { defineStore } from 'pinia'
import { roleService } from 'src/services/roleService'
import { Notify } from 'quasar'

export const useRolesStore = defineStore('roles', {
  state: () => ({
    roles: [],
    role: null,
    permissions: [],
    rolePermissions: [],
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
        search: ''
      }
    },
    roleOptions: [] // For dropdown/select components
  }),

  getters: {
    getRoles: (state) => state.roles,
    getRole: (state) => state.role,
    getPermissions: (state) => state.permissions,
    getRolePermissions: (state) => state.rolePermissions,
    getRoleOptions: (state) => state.roleOptions,
    loading: (state) => state.isLoading,
    submitting: (state) => state.isSubmitting,
    pagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,

    getRoleById: (state) => (id) => state.roles.find(role => role.id === id),
    filteredRoles: (state) => {
      let filtered = state.roles || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(role =>
          role.name?.toLowerCase().includes(search) ||
          role.description?.toLowerCase().includes(search)
        )
      }
      
      return filtered
    }
  },

  actions: {
    /**
     * Fetch roles with pagination and filters
     * @param {Object} props - Table request properties
     */
    async fetchRoles(props = {}) {
      this.isLoading = true
      try {
        // Build request parameters
        const params = {
          ...roleService.buildPaginationParams(props.pagination || this.table.pagination),
          ...this.table.filters
        }

        // Clean empty parameters
        const cleanParams = roleService.cleanParams(params)
        
        const response = await roleService.getRoles(cleanParams)
        
        if (response.success) {
          this.roles = response.data || []
          
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
          throw new Error(response.message || 'Failed to fetch roles')
        }
      } catch (error) {
        console.error('Error fetching roles:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to fetch roles',
          position: 'top'
        })
        this.roles = []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch single role by ID
     * @param {number} id - Role ID
     */
    async fetchRole(id) {
      this.isLoading = true
      try {
        const response = await roleService.getRole(id)
        
        if (response.success) {
          this.role = response.data
        } else {
          throw new Error(response.message || 'Failed to fetch role')
        }
      } catch (error) {
        console.error('Error fetching role:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to fetch role',
          position: 'top'
        })
        this.role = null
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Create new role
     * @param {Object} roleData - Role data
     */
    async createRole(roleData) {
      this.isSubmitting = true
      try {
        const response = await roleService.createRole(roleData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'Role created successfully',
            position: 'top'
          })
          
          // Refresh roles list
          await this.fetchRoles()
          return response.data
        } else {
          throw new Error(response.message || 'Failed to create role')
        }
      } catch (error) {
        console.error('Error creating role:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to create role',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Update existing role
     * @param {number} id - Role ID
     * @param {Object} roleData - Role data
     */
    async updateRole(id, roleData) {
      this.isSubmitting = true
      try {
        const response = await roleService.updateRole(id, roleData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'Role updated successfully',
            position: 'top'
          })
          
          // Update role in the list
          const index = this.roles.findIndex(role => role.id === id)
          if (index !== -1) {
            this.roles[index] = { ...this.roles[index], ...response.data }
          }
          
          // Update current role if it's the same
          if (this.role && this.role.id === id) {
            this.role = { ...this.role, ...response.data }
          }
          
          return response.data
        } else {
          throw new Error(response.message || 'Failed to update role')
        }
      } catch (error) {
        console.error('Error updating role:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to update role',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Delete role
     * @param {number} id - Role ID
     */
    async deleteRole(id) {
      this.isSubmitting = true
      try {
        const response = await roleService.deleteRole(id)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'Role deleted successfully',
            position: 'top'
          })
          
          // Remove role from the list
          this.roles = this.roles.filter(role => role.id !== id)
          
          // Clear current role if it's the same
          if (this.role && this.role.id === id) {
            this.role = null
          }
          
          return true
        } else {
          throw new Error(response.message || 'Failed to delete role')
        }
      } catch (error) {
        console.error('Error deleting role:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to delete role',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Fetch role permissions
     * @param {number} id - Role ID
     */
    async fetchRolePermissions(id) {
      this.isLoading = true
      try {
        const response = await roleService.getRolePermissions(id)
        
        if (response.success) {
          this.rolePermissions = response.data || []
        } else {
          throw new Error(response.message || 'Failed to fetch role permissions')
        }
      } catch (error) {
        console.error('Error fetching role permissions:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to fetch role permissions',
          position: 'top'
        })
        this.rolePermissions = []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Update role permissions
     * @param {number} id - Role ID
     * @param {Array} permissions - Permission IDs
     */
    async updateRolePermissions(id, permissions) {
      this.isSubmitting = true
      try {
        const response = await roleService.updateRolePermissions(id, permissions)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: 'Role permissions updated successfully',
            position: 'top'
          })
          
          // Refresh role permissions
          await this.fetchRolePermissions(id)
          return response.data
        } else {
          throw new Error(response.message || 'Failed to update role permissions')
        }
      } catch (error) {
        console.error('Error updating role permissions:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to update role permissions',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Fetch all permissions
     */
    async fetchPermissions() {
      try {
        const response = await roleService.getPermissions()
        
        if (response.success) {
          this.permissions = response.data || []
        } else {
          throw new Error(response.message || 'Failed to fetch permissions')
        }
      } catch (error) {
        console.error('Error fetching permissions:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Failed to fetch permissions',
          position: 'top'
        })
        this.permissions = []
      }
    },

    /**
     * Fetch role options for select components
     */
    async fetchRoleOptions() {
      try {
        const options = await roleService.getRoleOptions()
        this.roleOptions = options
        return options
      } catch (error) {
        console.error('Error fetching role options:', error)
        this.roleOptions = []
        return []
      }
    },

    /**
     * Handle table request (pagination, sorting, filtering)
     * @param {Object} props - Table request properties
     */
    async onTableRequest(props) {
      await this.fetchRoles(props)
    },

    /**
     * Set table filters
     * @param {Object} filters - Filter object
     */
    async setFilters(filters) {
      this.table.filters = { ...this.table.filters, ...filters }
      this.table.pagination.page = 1 // Reset to first page
      await this.fetchRoles()
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
     * Clear current role
     */
    clearRole() {
      this.role = null
      this.rolePermissions = []
    },

    /**
     * Clear all filters
     */
    clearFilters() {
      this.table.filters = {
        search: '',
        status: ''
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
     * Search roles by term
     * @param {string} searchTerm - Search term
     */
    async searchRoles(searchTerm) {
      await this.setFilters({ search: searchTerm })
    },


  }
})

export default useRolesStore