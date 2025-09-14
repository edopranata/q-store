import { defineStore } from 'pinia'
import { unitService } from 'src/services/unitService'
import { Notify } from 'quasar'

export const useUnitsStore = defineStore('units', {
  state: () => ({
    units: [],
    unit: null,
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
    unitOptions: [] // For dropdown/select components
  }),

  getters: {
    getUnits: (state) => state.units,
    getUnit: (state) => state.unit,
    getUnitOptions: (state) => state.unitOptions,
    loading: (state) => state.isLoading,
    submitting: (state) => state.isSubmitting,
    pagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    getActiveUnits: (state) => state.units.filter(unit => unit.status === 'active'),
    getUnitById: (state) => (id) => state.units.find(unit => unit.id === id),
    filteredUnits: (state) => {
      let filtered = state.units || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(unit =>
          unit.name?.toLowerCase().includes(search) ||
          unit.symbol?.toLowerCase().includes(search) ||
          unit.description?.toLowerCase().includes(search)
        )
      }
      
      if (state.table.filters.status) {
        filtered = filtered.filter(unit => unit.status === state.table.filters.status)
      }
      
      return filtered
    }
  },

  actions: {
    /**
     * Fetch units with server-side pagination and filters
     * @param {Object} props - Request props from q-table
     * @returns {Promise<boolean>} Success status
     */
    async fetchUnits(props = {}) {
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
        
        // Build API parameters using service helper
        const paginationParams = unitService.buildPaginationParams(pagination)
        const params = {
          ...paginationParams,
          search: filter || '',
          status: this.table.filters.status || ''
        }
        
        const response = await unitService.getUnits(params)
        
        if (response.success) {
          const { data, meta = {} } = response
          
          this.units = data || []
          
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
          throw new Error(response.message || 'Failed to fetch units')
        }
      } catch (error) {
        console.error('Fetch units error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memuat data satuan',
          position: 'top'
        })
        return false
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch unit by ID
     * @param {number} id - Unit ID
     * @returns {Promise<boolean>} Success status
     */
    async fetchUnit(id) {
      this.isLoading = true
      try {
        const response = await unitService.getUnit(id)
        
        if (response.success) {
          this.unit = response.data
          return true
        } else {
          throw new Error(response.message || 'Failed to fetch unit')
        }
      } catch (error) {
        console.error('Fetch unit error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memuat data satuan',
          position: 'top'
        })
        return false
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Create new unit
     * @param {Object} unitData - Unit data
     * @returns {Promise<boolean>} Success status
     */
    async createUnit(unitData) {
      this.isSubmitting = true
      try {
        const response = await unitService.createUnit(unitData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: response.message || 'Satuan berhasil ditambahkan',
            position: 'top'
          })
          
          // Clear cache and refresh units list
          unitService.clearCache()
          await this.fetchUnits()
          return true
        } else {
          throw new Error(response.message || 'Failed to create unit')
        }
      } catch (error) {
        console.error('Create unit error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal menambahkan satuan',
          position: 'top'
        })
        return false
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Update unit
     * @param {number} id - Unit ID
     * @param {Object} unitData - Unit data
     * @returns {Promise<boolean>} Success status
     */
    async updateUnit(id, unitData) {
      this.isSubmitting = true
      try {
        const response = await unitService.updateUnit(id, unitData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: response.message || 'Satuan berhasil diperbarui',
            position: 'top'
          })
          
          // Clear cache and refresh units list
          unitService.clearCache()
          await this.fetchUnits()
          return true
        } else {
          throw new Error(response.message || 'Failed to update unit')
        }
      } catch (error) {
        console.error('Update unit error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memperbarui satuan',
          position: 'top'
        })
        return false
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Delete unit
     * @param {number} id - Unit ID
     * @returns {Promise<boolean>} Success status
     */
    async deleteUnit(id) {
      this.isSubmitting = true
      try {
        const response = await unitService.deleteUnit(id)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: response.message || 'Satuan berhasil dihapus',
            position: 'top'
          })
          
          // Clear cache and refresh units list
          unitService.clearCache()
          await this.fetchUnits()
          return true
        } else {
          throw new Error(response.message || 'Failed to delete unit')
        }
      } catch (error) {
        console.error('Delete unit error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal menghapus satuan',
          position: 'top'
        })
        return false
      } finally {
        this.isSubmitting = false
      }
    },
    
    /**
     * Set pagination
     * @param {Object} pagination - Pagination object
     */
    setPagination(pagination) {
      // Merge with existing pagination, ensuring all required fields are present
      this.table.pagination = {
        sortBy: 'name',
        descending: false,
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
        ...this.table.pagination,
        ...pagination
      }
    },

    /**
     * Set filters with async support
     * @param {Object} filters - Filter object
     * @returns {Promise<boolean>} Success status
     */
    async setFilters(filters) {
      this.table.filters = { ...this.table.filters, ...filters }
      this.table.pagination.page = 1 // Reset to first page
      return await this.fetchUnits()
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
     * Clear current unit
     */
    clearUnit() {
      this.unit = null
    },



    /**
     * Reset pagination to default values
     */
    resetPagination() {
      this.table.pagination = { page: 1, rowsPerPage: 15, rowsNumber: 0, sortBy: 'name', descending: false }
    },

    /**
     * Search units
     * @param {string} searchTerm - Search term
     */
    async searchUnits(searchTerm) {
      this.table.filters.search = searchTerm
      this.resetPagination()
      await this.fetchUnits()
    },

    /**
     * Filter units by status
     * @param {string} status - Status filter
     */
    async filterByStatus(status) {
      this.table.filters.status = status
      this.resetPagination()
      await this.fetchUnits()
    },

    /**
     * Fetch unit options for dropdown/select components
     * @returns {Promise<boolean>} Success status
     */
    async fetchUnitOptions() {
      try {
        const response = await unitService.getUnitOptions()
        
        if (response.success) {
          this.unitOptions = response.data || []
          return true
        } else {
          throw new Error(response.message || 'Failed to fetch unit options')
        }
      } catch (error) {
        console.error('Fetch unit options error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memuat opsi satuan',
          position: 'top'
        })
        return false
      }
    },

    /**
     * Handle table request (for q-table)
     * @param {Object} props - Table request props
     * @returns {Promise<boolean>} Success status
     */
    async onTableRequest(props) {
      return await this.fetchUnits(props)
    }
  }
})

export default useUnitsStore