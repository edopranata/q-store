import { api } from 'boot/axios'

/**
 * Warehouse Service
 * Handles all API calls related to warehouses
 */
export const warehouseService = {
  /**
   * Get all warehouses with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getWarehouses(params = {}) {
    try {
      const response = await api.get('/master/warehouses', { params })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single warehouse by ID
   * @param {number} id - Warehouse ID
   * @returns {Promise} API response
   */
  async getWarehouse(id) {
    try {
      const response = await api.get(`/master/warehouses/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new warehouse
   * @param {Object} data - Warehouse data
   * @returns {Promise} API response
   */
  async createWarehouse(data) {
    try {
      const response = await api.post('/master/warehouses', data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing warehouse
   * @param {number} id - Warehouse ID
   * @param {Object} data - Updated warehouse data
   * @returns {Promise} API response
   */
  async updateWarehouse(id, data) {
    try {
      const response = await api.put(`/master/warehouses/${id}`, data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete warehouse
   * @param {number} id - Warehouse ID
   * @returns {Promise} API response
   */
  async deleteWarehouse(id) {
    try {
      const response = await api.delete(`/master/warehouses/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
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

export default warehouseService