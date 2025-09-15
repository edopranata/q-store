import { defineStore } from 'pinia'
import { customerService } from 'src/services/customerService'
import { Notify } from 'quasar'

export const useCustomersStore = defineStore('customers', {
  state: () => ({
    customers: [],
    table: {
      pagination: {
        sortBy: 'id',
        descending: false,
        page: 1,
        rowsPerPage: 15,
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
    getIsSubmitting: (state) => state.isSubmitting,
    getActiveCustomers: (state) => state.customers.filter(customer => customer.status === 'active'),
    getCustomerById: (state) => (id) => state.customers.find(customer => customer.id === id),
    filteredCustomers: (state) => {
      let filtered = state.customers || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(customer =>
          customer.name?.toLowerCase().includes(search) ||
          customer.email?.toLowerCase().includes(search) ||
          customer.phone?.toLowerCase().includes(search) ||
          customer.address?.toLowerCase().includes(search)
        )
      }
      
      if (state.table.filters.status) {
        filtered = filtered.filter(customer => customer.status === state.table.filters.status)
      }
      
      return filtered
    }
  },

  actions: {
    async fetchCustomers(props = {}) {
      this.loading = true
      try {
        // Initialize pagination if not exists
        if (!this.table.pagination) {
          this.table.pagination = {
            sortBy: 'name',
            descending: false,
            page: 1,
            rowsPerPage: 15,
            rowsNumber: 0
          }
        }
        
        // Extract pagination and filter from props
        const pagination = props.pagination || this.table.pagination
        const filter = props.filter !== undefined ? props.filter : this.table.filters.search
        
        // Build pagination parameters
        const paginationParams = customerService.buildPaginationParams(pagination)
        
        // Build API parameters
        const params = {
          ...paginationParams,
          search: filter || '',
          status: this.table.filters.status || ''
        }
        
        const response = await customerService.getCustomers(params)
        
        if (response && response.data) {
          const { data, meta = {} } = response
          this.customers = data || []
          // Update pagination with server response
          this.table.pagination = {
            ...this.table.pagination,
            page: meta.current_page || 1,
            rowsPerPage: meta.per_page || 15,
            rowsNumber: meta.total || 0,
            sortBy: pagination.sortBy || this.table.pagination.sortBy || 'name',
            descending: pagination.descending !== undefined ? pagination.descending : (this.table.pagination.descending || false)
          }
          
          return true
        } else {
          throw new Error('Failed to fetch suppliers')
        }
      } catch (error) {
        console.error('Fetch suppliers error:', error)
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal memuat data supplier',
          position: 'top'
        })
        return false
      } finally {
        this.loading = false
      }
    },

    async createCustomer(customerData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await customerService.createCustomer(customerData)
        return response.data
      } catch (error) {
        console.error('Create customer error:', error)
        this.error = error.response?.data?.message || 'Gagal menambahkan customer'
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal menambahkan customer',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async updateCustomer(id, customerData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await customerService.updateCustomer(id, customerData)
        return response.data
      } catch (error) {
        console.error('Update customer error:', error)
        this.error = error.response?.data?.message || 'Gagal memperbarui customer'
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal memperbarui customer',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async deleteCustomer(id) {
      this.isSubmitting = true
      this.error = null
      
      try {
        await customerService.deleteCustomer(id)
        return true
      } catch (error) {
        console.error('Delete customer error:', error)
        this.error = error.response?.data?.message || 'Gagal menghapus customer'
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal menghapus customer',
          position: 'top'
        })
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
        rowsPerPage: 15,
        rowsNumber: 0
      }
    },

    /**
     * Search customers by term
     * @param {string} searchTerm - Search term
     */
    async searchCustomers(searchTerm) {
      this.table.filters.search = searchTerm
      this.resetPagination()
      await this.fetchCustomers()
    },

    /**
     * Filter customers by status
     * @param {string} status - Status filter
     */
    async filterByStatus(status) {
      this.table.filters.status = status
      this.resetPagination()
      await this.fetchCustomers()
    }
  }
})