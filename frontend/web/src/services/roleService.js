import { api } from 'boot/axios'

/**
 * Role Service
 * Handles all API calls related to roles
 */
export const roleService = {
  // Cache for role options
  _roleOptionsCache: null,
  _cacheTimestamp: null,
  _cacheExpiry: 5 * 60 * 1000, // 5 minutes

  /**
   * Get all roles with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getRoles(params = {}) {
    try {
      // Clean empty parameters
      const cleanParams = this.cleanParams(params)
      const response = await api.get('/management/roles', { params: cleanParams })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single role by ID
   * @param {number} id - Role ID
   * @returns {Promise} API response
   */
  async getRole(id) {
    try {
      const response = await api.get(`/management/roles/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new role
   * @param {Object} data - Role data
   * @returns {Promise} API response
   */
  async createRole(data) {
    try {
      const response = await api.post('/management/roles', data)
      this.clearCache()
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing role
   * @param {number} id - Role ID
   * @param {Object} data - Role data
   * @returns {Promise} API response
   */
  async updateRole(id, data) {
    try {
      const response = await api.put(`/management/roles/${id}`, data)
      this.clearCache()
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete role
   * @param {number} id - Role ID
   * @returns {Promise} API response
   */
  async deleteRole(id) {
    try {
      const response = await api.delete(`/management/roles/${id}`)
      this.clearCache()
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get role permissions
   * @param {number} id - Role ID
   * @returns {Promise} API response
   */
  async getRolePermissions(id) {
    try {
      const response = await api.get(`/management/roles/${id}/permissions`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update role permissions
   * @param {number} id - Role ID
   * @param {Array} permissions - Permission IDs
   * @returns {Promise} API response
   */
  async updateRolePermissions(id, permissions) {
    try {
      const response = await api.put(`/management/roles/${id}/sync-permissions`, { permissions })
      this.clearCache()
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get all permissions
   * @returns {Promise} API response
   */
  async getPermissions() {
    try {
      const response = await api.get('/management/permissions')
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Clean empty parameters
   * @param {Object} params - Parameters to clean
   * @returns {Object} Cleaned parameters
   */
  cleanParams(params) {
    const cleaned = {}
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
        cleaned[key] = params[key]
      }
    })
    return cleaned
  },

  /**
   * Build pagination parameters
   * @param {Object} pagination - Pagination object
   * @returns {Object} Pagination parameters
   */
  buildPaginationParams(pagination = {}) {
    return {
      page: pagination.page || 1,
      per_page: pagination.rowsPerPage || 10,
      sort_by: pagination.sortBy || 'id',
      sort_order: pagination.descending ? 'desc' : 'asc'
    }
  },

  /**
   * Get role options for select components
   * @param {Object} options - Options for filtering
   * @param {boolean} forceRefresh - Force refresh cache
   * @returns {Promise} Role options
   */
  async getRoleOptions(options = {}, forceRefresh = false) {
    try {
      // Check cache
      const now = Date.now()
      if (!forceRefresh && this._roleOptionsCache && this._cacheTimestamp && (now - this._cacheTimestamp) < this._cacheExpiry) {
        return this._roleOptionsCache
      }

      const params = {
        per_page: options.limit || 100,
        status: options.status || 'active'
      }

      const response = await api.get('/options/roles', { params })
      
      // The backend now returns properly formatted options
      const roleOptions = response.data.data || []

      // Cache the result
      this._roleOptionsCache = roleOptions
      this._cacheTimestamp = now

      return roleOptions
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Clear cache
   */
  clearCache() {
    this._roleOptionsCache = null
    this._cacheTimestamp = null
  },

  /**
   * Handle API errors
   * @param {Error} error - Error object
   * @returns {Error} Formatted error
   */
  handleError(error) {
    if (error.response) {
      // Server responded with error status
      const message = error.response.data?.message || 'An error occurred'
      const status = error.response.status
      const errors = error.response.data?.errors || {}
      
      return {
        message,
        status,
        errors,
        original: error
      }
    } else if (error.request) {
      // Request was made but no response received
      return {
        message: 'Network error - please check your connection',
        status: 0,
        errors: {},
        original: error
      }
    } else {
      // Something else happened
      return {
        message: error.message || 'An unexpected error occurred',
        status: 0,
        errors: {},
        original: error
      }
    }
  }
}

export default roleService