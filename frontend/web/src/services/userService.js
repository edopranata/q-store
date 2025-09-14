import { api } from 'boot/axios'

/**
 * User Service
 * Handles all API calls related to users
 */
export const userService = {
  // Cache for user options
  _userOptionsCache: null,
  _cacheTimestamp: null,
  _cacheExpiry: 5 * 60 * 1000, // 5 minutes

  /**
   * Get all users with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getUsers(params = {}) {
    try {
      // Clean empty parameters
      const cleanParams = this.cleanParams(params)
      const response = await api.get('/management/users', { params: cleanParams })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single user by ID
   * @param {number} id - User ID
   * @returns {Promise} API response
   */
  async getUser(id) {
    try {
      const response = await api.get(`/management/users/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new user
   * @param {Object} data - User data
   * @returns {Promise} API response
   */
  async createUser(data) {
    try {
      const response = await api.post('/management/users', data)
      this.clearCache()
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing user
   * @param {number} id - User ID
   * @param {Object} data - User data
   * @returns {Promise} API response
   */
  async updateUser(id, data) {
    try {
      const response = await api.put(`/management/users/${id}`, data)
      this.clearCache()
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete user
   * @param {number} id - User ID
   * @returns {Promise} API response
   */
  async deleteUser(id) {
    try {
      const response = await api.delete(`/management/users/${id}`)
      this.clearCache()
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
   * Get user options for select components
   * @param {Object} options - Options for filtering
   * @param {boolean} forceRefresh - Force refresh cache
   * @returns {Promise} User options
   */
  async getUserOptions(options = {}, forceRefresh = false) {
    try {
      // Check cache
      const now = Date.now()
      if (!forceRefresh && this._userOptionsCache && this._cacheTimestamp && (now - this._cacheTimestamp) < this._cacheExpiry) {
        return this._userOptionsCache
      }

      const params = {
        per_page: options.limit || 100,
        status: options.status || 'active',
        fields: 'id,name,email'
      }

      const response = await api.get('/management/users', { params })
      const users = response.data.data || []
      
      const userOptions = users.map(user => ({
        label: `${user.name} (${user.email})`,
        value: user.id,
        user: user
      }))

      // Cache the result
      this._userOptionsCache = userOptions
      this._cacheTimestamp = now

      return userOptions
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Clear cache
   */
  clearCache() {
    this._userOptionsCache = null
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

export default userService