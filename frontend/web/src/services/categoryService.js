import { api } from 'boot/axios'

/**
 * Category Service
 * Handles all API calls related to categories
 */
export const categoryService = {
  // Cache for category options
  _categoryOptionsCache: null,
  _cacheTimestamp: null,
  _cacheExpiry: 5 * 60 * 1000, // 5 minutes
  /**
   * Get all categories with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getCategories(params = {}) {
    try {
      // Clean empty parameters
      const cleanParams = this.cleanParams(params)
      const response = await api.get('/master/categories', { params: cleanParams })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single category by ID
   * @param {number} id - Category ID
   * @returns {Promise} API response
   */
  async getCategory(id) {
    try {
      const response = await api.get(`/master/categories/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new category
   * @param {Object} data - Category data
   * @returns {Promise} API response
   */
  async createCategory(data) {
    try {
      const response = await api.post('/master/categories', data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing category
   * @param {number} id - Category ID
   * @param {Object} data - Updated category data
   * @returns {Promise} API response
   */
  async updateCategory(id, data) {
    try {
      const response = await api.put(`/master/categories/${id}`, data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete category
   * @param {number} id - Category ID
   * @returns {Promise} API response
   */
  async deleteCategory(id) {
    try {
      const response = await api.delete(`/master/categories/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Clean empty parameters from request
   * @param {Object} params - Parameters to clean
   * @returns {Object} Cleaned parameters
   */
  cleanParams(params) {
    const cleaned = {}
    Object.keys(params).forEach(key => {
      if (params[key] !== '' && params[key] !== null && params[key] !== undefined) {
        cleaned[key] = params[key]
      }
    })
    return cleaned
  },

  /**
   * Build pagination parameters
   * @param {Object} pagination - Pagination object
   * @returns {Object} API-ready pagination parameters
   */
  buildPaginationParams(pagination = {}) {
    return {
      page: pagination.page || 1,
      per_page: pagination.rowsPerPage || 15,
      sort_by: pagination.sortBy || 'name',
      sort_order: pagination.descending ? 'desc' : 'asc'
    }
  },

  /**
   * Get category options for dropdowns with caching
   * @param {Object} options - Options for filtering
   * @param {boolean} forceRefresh - Force refresh cache
   * @returns {Promise} API response
   */
  async getCategoryOptions(options = {}, forceRefresh = false) {
    try {
      // Check cache validity
      const now = Date.now()
      const isCacheValid = this._categoryOptionsCache && 
                          this._cacheTimestamp && 
                          (now - this._cacheTimestamp) < this._cacheExpiry
      
      // Return cached data if valid and no force refresh
      if (isCacheValid && !forceRefresh && Object.keys(options).length === 0) {
        return this._categoryOptionsCache
      }
      
      const params = {
        status: 'active',
        per_page: 1000,
        ...options
      }
      const cleanParams = this.cleanParams(params)
      const response = await api.get('/master/categories', { params: cleanParams })
      
      // Cache the response if no custom options
      if (Object.keys(options).length === 0) {
        this._categoryOptionsCache = response.data
        this._cacheTimestamp = now
      }
      
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Clear category options cache
   */
  clearCache() {
    this._categoryOptionsCache = null
    this._cacheTimestamp = null
  },

  /**
   * Handle API errors
   * @param {Object} error - Axios error object
   * @returns {Object} Formatted error
   */
  handleError(error) {
    if (error.response) {
      // Server responded with error status
      const { status, data } = error.response
      return {
        status,
        message: data.message || 'Terjadi kesalahan pada server',
        errors: data.errors || null
      }
    } else if (error.request) {
      // Request was made but no response received
      return {
        status: 0,
        message: 'Tidak dapat terhubung ke server',
        errors: null
      }
    } else {
      // Something else happened
      return {
        status: 0,
        message: error.message || 'Terjadi kesalahan yang tidak diketahui',
        errors: null
      }
    }
  }
}

export default categoryService