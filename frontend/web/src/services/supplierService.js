import { api } from 'boot/axios'

/**
 * Supplier Service
 * Handles all API calls related to suppliers
 */
export const supplierService = {
  /**
   * Get all suppliers with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getSuppliers(params = {}) {
    try {
      // Clean empty parameters
      const cleanParams = this.cleanParams(params)
      const response = await api.get('/master/suppliers', { params: cleanParams })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single supplier by ID
   * @param {number} id - Supplier ID
   * @returns {Promise} API response
   */
  async getSupplier(id) {
    try {
      const response = await api.get(`/master/suppliers/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new supplier
   * @param {Object} data - Supplier data
   * @returns {Promise} API response
   */
  async createSupplier(data) {
    try {
      const response = await api.post('/master/suppliers', data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing supplier
   * @param {number} id - Supplier ID
   * @param {Object} data - Updated supplier data
   * @returns {Promise} API response
   */
  async updateSupplier(id, data) {
    try {
      const response = await api.put(`/master/suppliers/${id}`, data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete supplier
   * @param {number} id - Supplier ID
   * @returns {Promise} API response
   */
  async deleteSupplier(id) {
    try {
      const response = await api.delete(`/master/suppliers/${id}`)
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

export default supplierService