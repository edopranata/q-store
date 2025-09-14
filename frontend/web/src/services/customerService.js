import { api } from 'boot/axios'

/**
 * Customer Service
 * Handles all API calls related to customers
 */
export const customerService = {
  /**
   * Get all customers with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getCustomers(params = {}) {
    try {
      // Clean empty parameters
      const cleanParams = this.cleanParams(params)
      const response = await api.get('/master/customers', { params: cleanParams })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single customer by ID
   * @param {number} id - Customer ID
   * @returns {Promise} API response
   */
  async getCustomer(id) {
    try {
      const response = await api.get(`/master/customers/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new customer
   * @param {Object} data - Customer data
   * @returns {Promise} API response
   */
  async createCustomer(data) {
    try {
      const response = await api.post('/master/customers', data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing customer
   * @param {number} id - Customer ID
   * @param {Object} data - Updated customer data
   * @returns {Promise} API response
   */
  async updateCustomer(id, data) {
    try {
      const response = await api.put(`/master/customers/${id}`, data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete customer
   * @param {number} id - Customer ID
   * @returns {Promise} API response
   */
  async deleteCustomer(id) {
    try {
      const response = await api.delete(`/master/customers/${id}`)
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

export default customerService