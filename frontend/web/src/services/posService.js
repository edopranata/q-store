import { api } from 'boot/axios'

class POSService {
  // Get products for POS with search and category filter
  async getProducts(params = {}) {
    const response = await api.get('/pos/products', { params })
    return response.data
  }

  // Get categories for POS
  async getCategories() {
    const response = await api.get('/pos/categories')
    return response.data
  }

  // Create new transaction
  async createTransaction(transactionData) {
    const response = await api.post('/pos/transactions', transactionData)
    return response.data
  }

  // Get transaction history
  async getTransactions(params = {}) {
    const response = await api.get('/pos/transactions', { params })
    return response.data
  }

  // Get transaction by ID
  async getTransaction(id) {
    const response = await api.get(`/pos/transactions/${id}`)
    return response.data
  }

  // Process payment
  async processPayment(transactionId, paymentData) {
    const response = await api.post(`/pos/transactions/${transactionId}/payment`, paymentData)
    return response.data
  }

  // Get customers for POS
  async getCustomers(params = {}) {
    const response = await api.get('/pos/customers', { params })
    return response.data
  }

  // Get payment methods
  async getPaymentMethods() {
    const response = await api.get('/pos/payment-methods')
    return response.data
  }

  // Check product stock
  async checkStock(productId, quantity) {
    const response = await api.post('/pos/check-stock', {
      product_id: productId,
      quantity
    })
    return response.data
  }

  // Apply discount
  async applyDiscount(discountCode, cartData) {
    const response = await api.post('/pos/apply-discount', {
      discount_code: discountCode,
      cart: cartData
    })
    return response.data
  }

  // Print receipt
  async printReceipt(transactionId) {
    const response = await api.get(`/pos/transactions/${transactionId}/receipt`, {
      responseType: 'blob'
    })
    return response.data
  }

  // Get daily sales summary
  async getDailySummary(date = null) {
    const params = date ? { date } : {}
    const response = await api.get('/pos/daily-summary', { params })
    return response.data
  }

  // Hold transaction (save for later)
  async holdTransaction(transactionData) {
    const response = await api.post('/pos/hold-transaction', transactionData)
    return response.data
  }

  // Get held transactions
  async getHeldTransactions() {
    const response = await api.get('/pos/held-transactions')
    return response.data
  }

  // Resume held transaction
  async resumeTransaction(holdId) {
    const response = await api.get(`/pos/held-transactions/${holdId}`)
    return response.data
  }

  // Delete held transaction
  async deleteHeldTransaction(holdId) {
    const response = await api.delete(`/pos/held-transactions/${holdId}`)
    return response.data
  }
}

const posService = new POSService()

export { posService }
export default posService