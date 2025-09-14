import { api } from 'boot/axios'

/**
 * Product Service
 * Handles all product-related API calls
 */
class ProductService {
  /**
   * Get all products with pagination and filters
   */
  async getProducts(params = {}) {
    try {
      const response = await api.get('/product/products', { params })
      return {
        success: true,
        data: response.data.data,
        meta: response.data.meta || {},
        message: 'Products retrieved successfully'
      }
    } catch (error) {
      console.error('Error fetching products:', error)
      return {
        success: false,
        data: [],
        meta: {},
        message: error.response?.data?.message || 'Failed to fetch products'
      }
    }
  }

  /**
   * Get single product by ID
   */
  async getProduct(id) {
    try {
      const response = await api.get(`/product/products/${id}`)
      return {
        success: true,
        data: response.data.data,
        message: 'Product retrieved successfully'
      }
    } catch (error) {
      console.error('Error fetching product:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to fetch product'
      }
    }
  }

  /**
   * Create new product
   */
  async createProduct(productData) {
    try {
      const response = await api.post('/product/products', productData)
      return {
        success: true,
        data: response.data.data,
        message: 'Product created successfully'
      }
    } catch (error) {
      console.error('Error creating product:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to create product'
      }
    }
  }

  /**
   * Update existing product
   */
  async updateProduct(id, productData) {
    try {
      const response = await api.put(`/product/products/${id}`, productData)
      return {
        success: true,
        data: response.data.data,
        message: 'Product updated successfully'
      }
    } catch (error) {
      console.error('Error updating product:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to update product'
      }
    }
  }

  /**
   * Delete product
   */
  async deleteProduct(id) {
    try {
      await api.delete(`/product/products/${id}`)
      return {
        success: true,
        message: 'Product deleted successfully'
      }
    } catch (error) {
      console.error('Error deleting product:', error)
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to delete product'
      }
    }
  }

  /**
   * Upload product image
   */
  async uploadImage(file) {
    try {
      const formData = new FormData()
      formData.append('image', file)
      
      const response = await api.post('/master/products/upload-image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      return {
        success: true,
        data: response.data.data,
        message: 'Image uploaded successfully'
      }
    } catch (error) {
      console.error('Error uploading image:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to upload image'
      }
    }
  }

  /**
   * Adjust product stock
   */
  async adjustStock(id, adjustmentData) {
    try {
      const response = await api.post(`/products/${id}/adjust-stock`, adjustmentData)
      return {
        success: true,
        data: response.data.data,
        message: 'Stock adjusted successfully'
      }
    } catch (error) {
      console.error('Error adjusting stock:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to adjust stock'
      }
    }
  }

  /**
   * Get stock history for a product
   */
  async getStockHistory(id, params = {}) {
    try {
      const response = await api.get(`/products/${id}/stock-history`, { params })
      return {
        success: true,
        data: response.data.data,
        meta: response.data.meta || {},
        message: 'Stock history retrieved successfully'
      }
    } catch (error) {
      console.error('Error fetching stock history:', error)
      return {
        success: false,
        data: [],
        meta: {},
        message: error.response?.data?.message || 'Failed to fetch stock history'
      }
    }
  }

  /**
   * Search products by barcode
   */
  async searchByBarcode(barcode) {
    try {
      const response = await api.get(`/products/search/barcode/${barcode}`)
      return {
        success: true,
        data: response.data.data,
        message: 'Product found'
      }
    } catch (error) {
      console.error('Error searching by barcode:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Product not found'
      }
    }
  }

  /**
   * Generate barcode for product
   */
  async generateBarcode() {
    try {
      const response = await api.post('/master/products/generate-barcode')
      return {
        success: true,
        data: response.data.data,
        message: 'Barcode generated successfully'
      }
    } catch (error) {
      console.error('Error generating barcode:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to generate barcode'
      }
    }
  }

  /**
   * Export products to Excel/CSV
   */
  async exportProducts(format = 'excel', filters = {}) {
    try {
      const response = await api.get('/master/products/export', {
        params: { format, ...filters },
        responseType: 'blob'
      })
      
      return {
        success: true,
        data: response.data,
        message: 'Products exported successfully'
      }
    } catch (error) {
      console.error('Error exporting products:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to export products'
      }
    }
  }

  /**
   * Import products from Excel/CSV
   */
  async importProducts(file) {
    try {
      const formData = new FormData()
      formData.append('file', file)
      
      const response = await api.post('/master/products/import', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      
      return {
        success: true,
        data: response.data.data,
        message: 'Products imported successfully'
      }
    } catch (error) {
      console.error('Error importing products:', error)
      return {
        success: false,
        data: null,
        message: error.response?.data?.message || 'Failed to import products'
      }
    }
  }

  /**
   * Get low stock products
   */
  async getLowStockProducts() {
    try {
      const response = await api.get('/master/products/low-stock')
      return {
        success: true,
        data: response.data.data,
        message: 'Low stock products retrieved successfully'
      }
    } catch (error) {
      console.error('Error fetching low stock products:', error)
      return {
        success: false,
        data: [],
        message: error.response?.data?.message || 'Failed to fetch low stock products'
      }
    }
  }
}

const productService = new ProductService()

export { productService }
export default productService