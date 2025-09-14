import { api } from 'boot/axios'

/**
 * Dashboard Service
 * Handles API calls for dashboard data
 */

// Get dashboard statistics
export const getDashboardStats = async () => {
  try {
    const response = await api.get('/stats/dashboard')
    return {
      success: true,
      data: response.data.data
    }
  } catch (error) {
    console.error('Error fetching dashboard stats:', error)
    return {
      success: false,
      message: error.response?.data?.message || 'Gagal mengambil data statistik'
    }
  }
}

// Get sales chart data
export const getSalesChartData = async (period = '7d') => {
  try {
    const response = await api.get(`/stats/sales-chart?period=${period}`)
    return {
      success: true,
      data: response.data.data
    }
  } catch (error) {
    console.error('Error fetching sales chart data:', error)
    return {
      success: false,
      message: error.response?.data?.message || 'Gagal mengambil data grafik penjualan'
    }
  }
}

// Get top products
export const getTopProducts = async (limit = 5) => {
  try {
    const response = await api.get(`/stats/top-products?limit=${limit}`)
    return {
      success: true,
      data: response.data.data
    }
  } catch (error) {
    console.error('Error fetching top products:', error)
    return {
      success: false,
      message: error.response?.data?.message || 'Gagal mengambil data produk terlaris'
    }
  }
}

// Get recent transactions
export const getRecentTransactions = async (limit = 5) => {
  try {
    const response = await api.get(`/stats/recent-transactions?limit=${limit}`)
    return {
      success: true,
      data: response.data.data
    }
  } catch (error) {
    console.error('Error fetching recent transactions:', error)
    return {
      success: false,
      message: error.response?.data?.message || 'Gagal mengambil data transaksi terbaru'
    }
  }
}

// Get low stock products
export const getLowStockProducts = async (limit = 10) => {
  try {
    const response = await api.get(`/stats/low-stock?limit=${limit}`)
    return {
      success: true,
      data: response.data.data
    }
  } catch (error) {
    console.error('Error fetching low stock products:', error)
    return {
      success: false,
      message: error.response?.data?.message || 'Gagal mengambil data stok menipis'
    }
  }
}

// Get complete dashboard data
export const getDashboardData = async () => {
  try {
    const [statsResult, topProductsResult, recentTransactionsResult, lowStockResult] = await Promise.all([
      getDashboardStats(),
      getTopProducts(),
      getRecentTransactions(),
      getLowStockProducts()
    ])

    return {
      success: true,
      data: {
        stats: statsResult.success ? statsResult.data : [],
        topProducts: topProductsResult.success ? topProductsResult.data : [],
        recentTransactions: recentTransactionsResult.success ? recentTransactionsResult.data : [],
        lowStockProducts: lowStockResult.success ? lowStockResult.data : []
      }
    }
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
    return {
      success: false,
      message: 'Gagal mengambil data dashboard'
    }
  }
}

const dashboardService = {
  getDashboardStats,
  getSalesChartData,
  getTopProducts,
  getRecentTransactions,
  getLowStockProducts,
  getDashboardData
}

export { dashboardService }
export default dashboardService