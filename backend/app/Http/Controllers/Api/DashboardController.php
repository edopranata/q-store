<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\SalesTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(): JsonResponse
    {
        try {
            // Mock data for now - replace with actual database queries
            $stats = [
                'total_sales' => 150000000,
                'total_transactions' => 1250,
                'total_products' => 450,
                'low_stock_count' => 12,
                'sales_growth' => 15.5,
                'transaction_growth' => 8.2
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Dashboard statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top selling products
     */
    public function topProducts(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);
            
            // Mock data for now - replace with actual database queries
            $topProducts = [
                [
                    'id' => 1,
                    'name' => 'Kopi Arabica Premium',
                    'sales_count' => 125,
                    'revenue' => 3750000,
                    'category' => 'Minuman'
                ],
                [
                    'id' => 2,
                    'name' => 'Nasi Gudeg Jogja',
                    'sales_count' => 98,
                    'revenue' => 2940000,
                    'category' => 'Makanan'
                ],
                [
                    'id' => 3,
                    'name' => 'Es Teh Manis',
                    'sales_count' => 87,
                    'revenue' => 1305000,
                    'category' => 'Minuman'
                ],
                [
                    'id' => 4,
                    'name' => 'Ayam Bakar Madu',
                    'sales_count' => 76,
                    'revenue' => 3040000,
                    'category' => 'Makanan'
                ],
                [
                    'id' => 5,
                    'name' => 'Jus Alpukat',
                    'sales_count' => 65,
                    'revenue' => 1625000,
                    'category' => 'Minuman'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => array_slice($topProducts, 0, $limit),
                'message' => 'Top products retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent transactions
     */
    public function recentTransactions(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 5);
            
            // Mock data for now - replace with actual database queries
            $recentTransactions = [
                [
                    'id' => 'TRX-001',
                    'customer_name' => 'Ahmad Wijaya',
                    'total_amount' => 125000,
                    'items_count' => 3,
                    'payment_method' => 'Cash',
                    'created_at' => Carbon::now()->subMinutes(15)->toISOString()
                ],
                [
                    'id' => 'TRX-002',
                    'customer_name' => 'Siti Nurhaliza',
                    'total_amount' => 87500,
                    'items_count' => 2,
                    'payment_method' => 'QRIS',
                    'created_at' => Carbon::now()->subMinutes(32)->toISOString()
                ],
                [
                    'id' => 'TRX-003',
                    'customer_name' => 'Budi Santoso',
                    'total_amount' => 156000,
                    'items_count' => 4,
                    'payment_method' => 'Debit Card',
                    'created_at' => Carbon::now()->subHour()->toISOString()
                ],
                [
                    'id' => 'TRX-004',
                    'customer_name' => 'Maya Sari',
                    'total_amount' => 45000,
                    'items_count' => 1,
                    'payment_method' => 'Cash',
                    'created_at' => Carbon::now()->subHours(2)->toISOString()
                ],
                [
                    'id' => 'TRX-005',
                    'customer_name' => 'Andi Pratama',
                    'total_amount' => 234000,
                    'items_count' => 6,
                    'payment_method' => 'QRIS',
                    'created_at' => Carbon::now()->subHours(3)->toISOString()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => array_slice($recentTransactions, 0, $limit),
                'message' => 'Recent transactions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve recent transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            
            // Mock data for now - replace with actual database queries
            $lowStockProducts = [
                [
                    'id' => 1,
                    'name' => 'Gula Pasir 1kg',
                    'current_stock' => 5,
                    'minimum_stock' => 20,
                    'unit' => 'kg',
                    'category' => 'Bahan Baku',
                    'last_restock' => Carbon::now()->subDays(7)->toISOString()
                ],
                [
                    'id' => 2,
                    'name' => 'Kopi Bubuk Robusta',
                    'current_stock' => 3,
                    'minimum_stock' => 15,
                    'unit' => 'kg',
                    'category' => 'Bahan Baku',
                    'last_restock' => Carbon::now()->subDays(10)->toISOString()
                ],
                [
                    'id' => 3,
                    'name' => 'Susu UHT 1L',
                    'current_stock' => 8,
                    'minimum_stock' => 25,
                    'unit' => 'pcs',
                    'category' => 'Bahan Baku',
                    'last_restock' => Carbon::now()->subDays(5)->toISOString()
                ],
                [
                    'id' => 4,
                    'name' => 'Tepung Terigu 1kg',
                    'current_stock' => 2,
                    'minimum_stock' => 10,
                    'unit' => 'kg',
                    'category' => 'Bahan Baku',
                    'last_restock' => Carbon::now()->subDays(12)->toISOString()
                ],
                [
                    'id' => 5,
                    'name' => 'Minyak Goreng 2L',
                    'current_stock' => 4,
                    'minimum_stock' => 12,
                    'unit' => 'botol',
                    'category' => 'Bahan Baku',
                    'last_restock' => Carbon::now()->subDays(8)->toISOString()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => array_slice($lowStockProducts, 0, $limit),
                'message' => 'Low stock products retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve low stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales chart data
     */
    public function salesChart(Request $request): JsonResponse
    {
        try {
            $period = $request->get('period', '7d');
            
            // Mock data for now - replace with actual database queries
            $chartData = [
                'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                'datasets' => [
                    [
                        'label' => 'Penjualan (Rp)',
                        'data' => [1200000, 1500000, 1800000, 1350000, 2100000, 2400000, 1900000],
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'borderColor' => 'rgb(59, 130, 246)',
                        'borderWidth' => 2,
                        'fill' => true
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $chartData,
                'message' => 'Sales chart data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sales chart data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}