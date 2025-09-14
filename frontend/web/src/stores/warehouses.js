import { defineStore } from 'pinia'
import { warehouseService } from 'src/services/warehouseService'
import { Notify } from 'quasar'

export const useWarehousesStore = defineStore('warehouses', {
  state: () => ({
    warehouses: [],
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
    getWarehouses: (state) => state.warehouses,
    getPagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getIsSubmitting: (state) => state.isSubmitting,
    getActiveWarehouses: (state) => state.warehouses.filter(warehouse => warehouse.status === 'active'),
    getWarehouseById: (state) => (id) => state.warehouses.find(warehouse => warehouse.id === id),
    filteredWarehouses: (state) => {
      let filtered = state.warehouses || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(warehouse =>
          warehouse.name?.toLowerCase().includes(search) ||
          warehouse.code?.toLowerCase().includes(search) ||
          warehouse.address?.toLowerCase().includes(search)
        )
      }
      
      if (state.table.filters.status) {
        filtered = filtered.filter(warehouse => warehouse.status === state.table.filters.status)
      }
      
      return filtered
    }
  },

  actions: {
    async fetchWarehouses(props = {}) {
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
        const paginationParams = warehouseService.buildPaginationParams(pagination)
        
        // Build API parameters
        const params = {
          ...paginationParams,
          search: filter || '',
          status: this.table.filters.status || ''
        }
        
        const response = await warehouseService.getWarehouses(params)
        
        if (response && response.data) {
          const { data, meta = {} } = response
          this.warehouses = data || []
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

    async createWarehouse(warehouseData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await warehouseService.createWarehouse(warehouseData)
        return response.data
      } catch (error) {
        console.error('Create warehouse error:', error)
        this.error = error.response?.data?.message || 'Gagal menambahkan warehouse'
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal menambahkan warehouse',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async updateWarehouse(id, warehouseData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await warehouseService.updateWarehouse(id, warehouseData)
        return response.data
      } catch (error) {
        console.error('Update warehouse error:', error)
        this.error = error.response?.data?.message || 'Gagal memperbarui warehouse'
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal memperbarui warehouse',
          position: 'top'
        })
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async deleteWarehouse(id) {
      this.isSubmitting = true
      this.error = null
      
      try {
        await warehouseService.deleteWarehouse(id)
        return true
      } catch (error) {
        console.error('Delete warehouse error:', error)
        this.error = error.response?.data?.message || 'Gagal menghapus warehouse'
        Notify.create({
          type: 'negative',
          message: error.response?.data?.message || 'Gagal menghapus warehouse',
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
     * Search warehouses by term
     * @param {string} searchTerm - Search term
     */
    async searchWarehouses(searchTerm) {
      this.table.filters.search = searchTerm
      this.resetPagination()
      await this.fetchWarehouses()
    },

    /**
     * Filter warehouses by status
     * @param {string} status - Status filter
     */
    async filterByStatus(status) {
      this.table.filters.status = status
      this.resetPagination()
      await this.fetchWarehouses()
    }
  }
})