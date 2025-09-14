import { defineStore } from 'pinia'
import { api } from 'src/boot/axios'

export const useWarehousesStore = defineStore('warehouses', {
  state: () => ({
    warehouses: [],
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
    getWarehouses: (state) => state.warehouses,
    getPagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    isLoading: (state) => state.loading,
    getError: (state) => state.error,
    getIsSubmitting: (state) => state.isSubmitting
  },

  actions: {
    async fetchWarehouses(props = {}) {
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
        
        const response = await api.get('/warehouses', { params })
        
        this.warehouses = response.data.data
        
        // Update pagination with response data
        this.table.pagination = {
          ...pagination,
          rowsNumber: response.data.total,
          page: response.data.current_page,
          rowsPerPage: response.data.per_page
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal memuat data warehouse'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createWarehouse(warehouseData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await api.post('/warehouses', warehouseData)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal menambahkan warehouse'
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async updateWarehouse(id, warehouseData) {
      this.isSubmitting = true
      this.error = null
      
      try {
        const response = await api.put(`/warehouses/${id}`, warehouseData)
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal memperbarui warehouse'
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    async deleteWarehouse(id) {
      this.isSubmitting = true
      this.error = null
      
      try {
        await api.delete(`/warehouses/${id}`)
        return true
      } catch (error) {
        this.error = error.response?.data?.message || 'Gagal menghapus warehouse'
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