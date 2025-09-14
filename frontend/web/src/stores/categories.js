import { defineStore } from 'pinia'
import { categoryService } from 'src/services/categoryService'
import { Notify } from 'quasar'

export const useCategoriesStore = defineStore('categories', {
  state: () => ({
    categories: [],
    category: null,
    loading: false,
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
    categoryOptions: [] // For dropdown/select components
  }),

  getters: {
    getCategories: (state) => state.categories,
    getCategory: (state) => state.category,
    getCategoryOptions: (state) => state.categoryOptions,
    isLoading: (state) => state.loading,
    submitting: (state) => state.isSubmitting,
    pagination: (state) => state.table.pagination,
    getFilters: (state) => state.table.filters,
    getActiveCategories: (state) => state.categories.filter(category => category.status === 'active'),
    getCategoryById: (state) => (id) => state.categories.find(category => category.id === id),
    filteredCategories: (state) => {
      let filtered = state.categories || []
      
      if (state.table.filters.search) {
        const search = state.table.filters.search.toLowerCase()
        filtered = filtered.filter(category =>
          category.name?.toLowerCase().includes(search) ||
          category.description?.toLowerCase().includes(search)
        )
      }
      
      if (state.table.filters.status) {
        filtered = filtered.filter(category => category.status === state.table.filters.status)
      }
      
      return filtered
    }
  },

  actions: {
    /**
     * Fetch categories with server-side pagination and filters
     * @param {Object} props - Request props from q-table
     * @returns {Promise<boolean>} Success status
     */
    async fetchCategories(props = {}) {
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
        
        // Build API parameters using service helper
        const paginationParams = categoryService.buildPaginationParams(pagination)
        const params = {
          ...paginationParams,
          search: filter || '',
          status: this.table.filters.status || ''
        }
        
        const response = await categoryService.getCategories(params)
        
        if (response.success) {
          const { data, meta = {} } = response
          
          this.categories = data || []
          
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
          throw new Error(response.message || 'Failed to fetch categories')
        }
      } catch (error) {
        console.error('Fetch categories error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memuat data kategori',
          position: 'top'
        })
        return false
      } finally {
        this.loading = false
      }
    },

    /**
     * Fetch category by ID
     * @param {number} id - Category ID
     * @returns {Promise<boolean>} Success status
     */
    async fetchCategory(id) {
      this.loading = true
      try {
        const response = await categoryService.getCategory(id)
        
        if (response.success) {
          this.category = response.data
          return true
        } else {
          throw new Error(response.message || 'Failed to fetch category')
        }
      } catch (error) {
        console.error('Fetch category error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memuat data kategori',
          position: 'top'
        })
        return false
      } finally {
        this.loading = false
      }
    },

    /**
     * Create new category
     * @param {Object} categoryData - Category data
     * @returns {Promise<boolean>} Success status
     */
    async createCategory(categoryData) {
      this.isSubmitting = true
      try {
        const response = await categoryService.createCategory(categoryData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: response.message || 'Kategori berhasil ditambahkan',
            position: 'top'
          })
          
          // Clear cache and refresh categories list
          categoryService.clearCache()
          await this.fetchCategories()
          return true
        } else {
          throw new Error(response.message || 'Failed to create category')
        }
      } catch (error) {
        console.error('Create category error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal menambahkan kategori',
          position: 'top'
        })
        return false
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Update existing category
     * @param {number} id - Category ID
     * @param {Object} categoryData - Updated category data
     * @returns {Promise<boolean>} Success status
     */
    async updateCategory(id, categoryData) {
      this.isSubmitting = true
      try {
        const response = await categoryService.updateCategory(id, categoryData)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: response.message || 'Kategori berhasil diperbarui',
            position: 'top'
          })
          
          // Update local state
          const index = this.categories.findIndex(cat => cat.id === id)
          if (index !== -1) {
            this.categories[index] = response.data
          }
          
          // Clear cache and refresh categories list to ensure consistency
          categoryService.clearCache()
          await this.fetchCategories()
          return true
        } else {
          throw new Error(response.message || 'Failed to update category')
        }
      } catch (error) {
        console.error('Update category error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal memperbarui kategori',
          position: 'top'
        })
        return false
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Delete category
     * @param {number} id - Category ID
     * @returns {Promise<boolean>} Success status
     */
    async deleteCategory(id) {
      this.isSubmitting = true
      try {
        const response = await categoryService.deleteCategory(id)
        
        if (response.success) {
          Notify.create({
            type: 'positive',
            message: response.message || 'Kategori berhasil dihapus',
            position: 'top'
          })
          
          // Clear cache and remove from local state
          categoryService.clearCache()
          this.categories = this.categories.filter(cat => cat.id !== id)
          
          // Update pagination if needed
          this.table.pagination.rowsNumber = Math.max(0, this.table.pagination.rowsNumber - 1)
          
          return true
        } else {
          throw new Error(response.message || 'Failed to delete category')
        }
      } catch (error) {
        console.error('Delete category error:', error)
        Notify.create({
          type: 'negative',
          message: error.message || 'Gagal menghapus kategori',
          position: 'top'
        })
        return false
      } finally {
        this.isSubmitting = false
      }
    },

    /**
     * Fetch category options for dropdowns
     * @returns {Promise<boolean>} Success status
     */
    async fetchCategoryOptions() {
      try {
        const response = await categoryService.getCategoryOptions()
        
        if (response.success) {
          this.categoryOptions = response.data || []
          return true
        }
        return false
      } catch (error) {
        console.error('Fetch category options error:', error)
        return false
      }
    },

    /**
     * Handle table request (for q-table server-side)
     * @param {Object} props - Request props from q-table
     */
    /**
     * Handle table request for server-side pagination
     * @param {Object} props - Request props from q-table
     * @returns {Promise<boolean>} Success status
     */
    async onTableRequest(props) {
      // Directly fetch categories with the provided props
      // The fetchCategories method will handle pagination and filter updates
      return await this.fetchCategories(props)
    },

    /**
     * Set filters and refresh data
     * @param {Object} filters - Filter object
     */
    async setFilters(filters) {
      this.table.filters = { ...this.table.filters, ...filters }
      
      // Reset to first page when filtering
      this.table.pagination.page = 1
      
      // Fetch data with new filters
      await this.fetchCategories()
    },

    /**
     * Set pagination
     * @param {Object} pagination - Pagination object
     */
    setPagination(pagination) {
      // Ensure this.table.pagination is initialized
      if (!this.table.pagination) {
        this.table.pagination = {
          sortBy: 'name',
          descending: false,
          page: 1,
          rowsPerPage: 15,
          rowsNumber: 0
        }
      }
      
      // Update pagination with new values, properly handling descending toggle
      this.table.pagination = { 
        ...this.table.pagination, 
        ...pagination
      }
    },

    /**
     * Clear current category
     */
    clearCategory() {
      this.category = null
    },

    /**
     * Clear filters
     */
    clearFilters() {
      this.table.filters = {
        search: '',
        status: ''
      }
    },

    /**
     * Reset pagination to default
     */
    resetPagination() {
      this.table.pagination = {
        page: 1,
        rowsPerPage: 15,
        rowsNumber: 0,
        sortBy: 'name',
        descending: false
      }
    },

    /**
     * Search categories
     * @param {string} searchTerm - Search term
     */
    async searchCategories(searchTerm) {
      this.setFilters({ search: searchTerm })
      await this.fetchCategories({ search: searchTerm, page: 1 })
    },

    /**
     * Filter categories by status
     * @param {string} status - Status filter
     */
    async filterByStatus(status) {
      this.setFilters({ status })
      await this.fetchCategories({ status, page: 1 })
    }
  }
})

export default useCategoriesStore