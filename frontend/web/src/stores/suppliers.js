import { defineStore } from 'pinia'
import { supplierService } from 'src/services/supplierService'
import { Notify } from 'quasar'

export const useSuppliersStore = defineStore('suppliers', {
  state: () => ({
    suppliers: [],
    supplier: null,
    isLoading: false,
    isSubmitting: false,
    table: {
      pagination: {
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
        sortBy: 'name',
        descending: false
      },
      filters: {
        search: '',
        status: ''
      }
    },
    supplierOptions: [] // For dropdown/select components
  }),

  getters: {
    // Basic getters
    all: (state) => state.suppliers,
    current: (state) => state.supplier,
    loading: (state) => state.isLoading,
    submitting: (state) => state.isSubmitting,
    pagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    getActiveSuppliers: (state) => state.suppliers.filter(supplier => supplier.status === 'active'),
    getSupplierById: (state) => (id) => state.suppliers.find(supplier => supplier.id === id),
    filteredSuppliers: (state) => {
      let filtered = state.suppliers || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(supplier =>
          supplier.name?.toLowerCase().includes(search) ||
          supplier.email?.toLowerCase().includes(search) ||
          supplier.phone?.toLowerCase().includes(search) ||
          supplier.address?.toLowerCase().includes(search)
        )
      }
      
      if (state.table.filters.status) {
        filtered = filtered.filter(supplier => supplier.status === state.table.filters.status)
      }
      
      return filtered
    }
  },

  actions: {
    /**
     * Fetch suppliers with server-side pagination and filters
     * @param {Object} props - Request props from q-table
     * @returns {Promise<boolean>} Success status
     */
    async fetchSuppliers(props = {}) {
      this.isLoading = true
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
        const paginationParams = supplierService.buildPaginationParams(pagination)
        
        // Build API parameters
        const params = {
          ...paginationParams,
          search: filter || '',
          status: this.table.filters.status || ''
        }
        
        const response = await supplierService.getSuppliers(params)
        
        if (response && response.data) {
          const { data, meta = {} } = response
          this.suppliers = data || []
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
        this.isLoading = false
      }
    },

    /**
     * Fetch single supplier by ID
     * @param {number|string} id - Supplier ID
     * @returns {Promise<boolean>} Success status
     */
    async fetchSupplier(id) {
      this.isLoading = true
      try {
        const response = await supplierService.getSupplier(id)
        
        if (response.data.success) {
          this.supplier = response.data.data
          return true
        } else {
          throw new Error(response.data.message || 'Failed to fetch supplier')
        }
      } catch (error) {
        console.error('Fetch supplier error:', error)
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal memuat data supplier',
          position: 'top'
        })
        return false
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Create new supplier
     * @param {Object} supplierData - Supplier data
     * @returns {Promise<boolean>} Success status
     */
    async createSupplier(supplierData) {
      this.isSubmitting = true
      try {
        const response = await supplierService.createSupplier(supplierData)
        
        if (response.data.success) {
          // Add to local state if not using server-side pagination
          if (this.suppliers.length < this.table.pagination.rowsPerPage) {
            this.suppliers.unshift(response.data.data)
          }
          
          Notify.create({
            type: 'positive',
            message: 'Supplier berhasil ditambahkan',
            position: 'top'
          })
          
          return true
        } else {
          throw new Error(response.data.message || 'Failed to create supplier')
        }
      } catch (error) {
        console.error('Create supplier error:', error)
        const message = error.response?.data?.message || 'Gagal menambahkan supplier'
        
        Notify.create({
          type: 'negative',
          message,
          position: 'top'
        })
        
        throw new Error(message)
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Update existing supplier
     * @param {number|string} id - Supplier ID
     * @param {Object} supplierData - Updated supplier data
     * @returns {Promise<boolean>} Success status
     */
    async updateSupplier(id, supplierData) {
      this.isSubmitting = true
      try {
        const response = await supplierService.updateSupplier(id, supplierData)
        
        if (response.data.success) {
          // Update local state
          const index = this.suppliers.findIndex(supplier => supplier.id == id)
          if (index !== -1) {
            this.suppliers[index] = response.data.data
          }
          
          // Update current supplier if it's the same
          if (this.supplier && this.supplier.id == id) {
            this.supplier = response.data.data
          }
          
          Notify.create({
            type: 'positive',
            message: 'Supplier berhasil diperbarui',
            position: 'top'
          })
          
          return true
        } else {
          throw new Error(response.data.message || 'Failed to update supplier')
        }
      } catch (error) {
        console.error('Update supplier error:', error)
        const message = error.response?.data?.message || 'Gagal memperbarui supplier'
        
        Notify.create({
          type: 'negative',
          message,
          position: 'top'
        })
        
        throw new Error(message)
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Delete supplier
     * @param {number|string} id - Supplier ID
     * @returns {Promise<boolean>} Success status
     */
    async deleteSupplier(id) {
      this.isLoading = true
      try {
        const response = await supplierService.deleteSupplier(id)
        
        if (response.data.success) {
          // Remove from local state
          this.suppliers = this.suppliers.filter(supplier => supplier.id != id)
          
          // Clear current supplier if it's the same
          if (this.supplier && this.supplier.id == id) {
            this.supplier = null
          }
          
          Notify.create({
            type: 'positive',
            message: 'Supplier berhasil dihapus',
            position: 'top'
          })
          
          return true
        } else {
          throw new Error(response.data.message || 'Failed to delete supplier')
        }
      } catch (error) {
        console.error('Delete supplier error:', error)
        const message = error.response?.data?.message || 'Gagal menghapus supplier'
        
        Notify.create({
          type: 'negative',
          message,
          position: 'top'
        })
        
        throw new Error(message)
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch supplier options for dropdowns
     * @returns {Promise<boolean>} Success status
     */
    async fetchSupplierOptions() {
      try {
        const response = await supplierService.getSuppliers({ status: 'active', per_page: 1000 })
        
        if (response && response.data) {
          this.supplierOptions = response.data.map(supplier => ({
            label: supplier.name,
            value: supplier.id
          }))
          return true
        } else {
          this.supplierOptions = this.getActiveSuppliers.map(supplier => ({
            label: supplier.name,
            value: supplier.id
          }))
          return true
        }
      } catch (error) {
        console.error('Fetch supplier options error:', error)
        // Fallback to active suppliers
        this.supplierOptions = this.getActiveSuppliers.map(supplier => ({
          label: supplier.name,
          value: supplier.id
        }))
        return false
      }
    },

    /**
     * Set pagination
     * @param {Object} pagination - Pagination object
     */
    setPagination(pagination) {
      this.table.pagination = { ...this.table.pagination, ...pagination }
    },

    /**
     * Set filters
     * @param {Object} filters - Filter object
     */
    setFilters(filters) {
      this.table.filters = { ...this.table.filters, ...filters }
    },

    /**
     * Clear all filters
     */
    clearFilters() {
      this.table.filters = {
        search: '',
        status: ''
      }
    },

    /**
     * Clear current supplier
     */
    clearSupplier() {
      this.supplier = null
    },

    /**
     * Reset pagination to default values
     */
    resetPagination() {
      this.table.pagination = { page: 1, rowsPerPage: 15, rowsNumber: 0, sortBy: 'name', descending: false }
    },

    /**
     * Search suppliers by term
     * @param {string} searchTerm - Search term
     */
    async searchSuppliers(searchTerm) {
      this.table.filters.search = searchTerm
      this.resetPagination()
      await this.fetchSuppliers()
    },

    /**
     * Filter suppliers by status
     * @param {string} status - Status filter
     */
    async filterByStatus(status) {
      this.table.filters.status = status
      this.resetPagination()
      await this.fetchSuppliers()
    }
  }
})