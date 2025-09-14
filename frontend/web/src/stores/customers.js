import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

export const useCustomersStore = defineStore('customers', {
  state: () => ({
    customers: [],
    table: {
      pagination: {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0
      },
      filters: {
        search: '',
        status: ''
      }
    },
    loading: false,
    error: null,
    isSubmitting: false
  }),

  getters: {
    getCustomers: (state) => state.customers,
    getPagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getIsSubmitting: (state) => state.isSubmitting
  },

  actions: {
    async fetchCustomers(props = {}) {
      this.loading = true
      this.error = null
      
      try {
        // Extract pagination and filters from props
        const { pagination = this.table.pagination, filters = this.table.filters } = props
        
        // Build API params
        const params = {
          page: pagination.page,
          per_page: pagination.rowsPerPage,
          sort_by: pagination.sortBy,
          sort_order: pagination.descending ? 'desc' : 'asc'
        }
        
        // Add filters if they exist
        if (filters.search) {
          params.search = filters.search
        }
        
        if (filters.status) {
          params.status = filters.status
        }
        
        const response = await api.get('/customers', { params })
        
        this.customers = response.data.data
        
        // Update pagination with response data
        this.table.pagination = {
          ...pagination,
          rowsNumber: response.data.total,
          page: response.data.current_page,
          rowsPerPage: response.data.per_page
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal memuat data customer'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createCustomer(customerData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await api.post('/customers', customerData)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal menambahkan customer'
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async updateCustomer(id, customerData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await api.put(`/customers/${id}`, customerData)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal memperbarui customer'
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async deleteCustomer(id) {
      this.isSubmitting = true
      this.error = null
      
      try {
        await api.delete(`/customers/${id}`)
        return true
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal menghapus customer'
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    setPagination(pagination) {
      this.table.pagination = { ...this.table.pagination, ...pagination }
    },

    setFilters(filters) {
      this.table.filters = { ...this.table.filters, ...filters }
    },

    clearFilters() {
      this.table.filters = {
        search: '',
        status: ''
      }
    },

    resetPagination() {
      this.table.pagination = {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 10,
        rowsNumber: 0
      }
    }
  }
})