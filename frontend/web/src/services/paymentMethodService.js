import { api } from 'boot/axios'

/**
 * Payment Method Service
 * Handles all API calls related to payment methods
 */
export const paymentMethodService = {
  /**
   * Get all payment methods with optional filters
   * @param {Object} params - Query parameters
   * @returns {Promise} API response
   */
  async getPaymentMethods(params = {}) {
    try {
      const response = await api.get('/master/payment-methods', { params })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Get single payment method by ID
   * @param {number} id - Payment Method ID
   * @returns {Promise} API response
   */
  async getPaymentMethod(id) {
    try {
      const response = await api.get(`/master/payment-methods/${id}`)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Create new payment method
   * @param {Object} data - Payment Method data
   * @returns {Promise} API response
   */
  async createPaymentMethod(data) {
    try {
      const response = await api.post('/master/payment-methods', data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Update existing payment method
   * @param {number} id - Payment Method ID
   * @param {Object} data - Updated payment method data
   * @returns {Promise} API response
   */
  async updatePaymentMethod(id, data) {
    try {
      const response = await api.put(`/master/payment-methods/${id}`, data)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  /**
   * Delete payment method
   * @param {number} id - Payment Method ID
   * @returns {Promise} API response
   */
  async deletePaymentMethod(id) {
    try {
      const response = await api.delete(`/master/payment-methods/${id}`)
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

export default paymentMethodService