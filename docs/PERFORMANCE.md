# ⚡ Q-POS Performance Guide

> Panduan komprehensif monitoring performa, optimasi sistem, dan best practices untuk memastikan Q-POS berjalan dengan optimal

## 📋 Daftar Isi

- [Overview](#overview)
- [Performance Metrics](#performance-metrics)
- [Monitoring Setup](#monitoring-setup)
- [Frontend Performance](#frontend-performance)
- [Backend Performance](#backend-performance)
- [Database Performance](#database-performance)
- [Infrastructure Monitoring](#infrastructure-monitoring)
- [Performance Testing](#performance-testing)
- [Optimization Strategies](#optimization-strategies)
- [Alerting & Notifications](#alerting--notifications)
- [Performance Troubleshooting](#performance-troubleshooting)
- [Capacity Planning](#capacity-planning)
- [Performance Best Practices](#performance-best-practices)
- [Tools & Technologies](#tools--technologies)
- [Performance Reports](#performance-reports)

## Overview

### 🎯 Performance Objectives

Q-POS dirancang untuk memberikan pengalaman pengguna yang responsif dan reliable dengan target performa sebagai berikut:

#### Response Time Targets

| Operation | Target | Acceptable | Critical |
|-----------|--------|------------|----------|
| **Page Load** | < 2s | < 3s | > 5s |
| **API Response** | < 500ms | < 1s | > 2s |
| **Database Query** | < 100ms | < 300ms | > 1s |
| **Search** | < 1s | < 2s | > 3s |
| **Transaction Processing** | < 3s | < 5s | > 10s |
| **Report Generation** | < 10s | < 30s | > 60s |

#### Throughput Targets

| Metric | Target | Peak Capacity |
|--------|--------|---------------|
| **Concurrent Users** | 100 | 500 |
| **Transactions/minute** | 1,000 | 5,000 |
| **API Requests/second** | 100 | 500 |
| **Database Connections** | 50 | 200 |

#### Availability Targets

- **Uptime**: 99.9% (8.76 hours downtime/year)
- **Recovery Time Objective (RTO)**: < 4 hours
- **Recovery Point Objective (RPO)**: < 1 hour

### 📊 Performance Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Performance Stack                        │
├─────────────────────────────────────────────────────────────┤
│  Frontend Monitoring  │  Application Monitoring            │
│  • Real User Monitoring│  • APM (Application Performance)   │
│  • Synthetic Monitoring│  • Error Tracking                  │
│  • Core Web Vitals    │  • Distributed Tracing            │
├─────────────────────────────────────────────────────────────┤
│  Infrastructure Monitoring  │  Database Monitoring          │
│  • Server Metrics     │  • Query Performance              │
│  • Network Monitoring │  • Connection Pooling             │
│  • Container Metrics  │  • Index Optimization             │
├─────────────────────────────────────────────────────────────┤
│                    Data Collection Layer                    │
│  • Metrics Collection • Log Aggregation • Alerting        │
└─────────────────────────────────────────────────────────────┘
```

## Performance Metrics

### 🔍 Key Performance Indicators (KPIs)

#### User Experience Metrics
```javascript
// Core Web Vitals monitoring
class WebVitalsMonitor {
    constructor() {
        this.metrics = {
            lcp: null, // Largest Contentful Paint
            fid: null, // First Input Delay
            cls: null, // Cumulative Layout Shift
            fcp: null, // First Contentful Paint
            ttfb: null // Time to First Byte
        };
        this.initializeMonitoring();
    }
    
    initializeMonitoring() {
        // Largest Contentful Paint
        new PerformanceObserver((entryList) => {
            const entries = entryList.getEntries();
            const lastEntry = entries[entries.length - 1];
            this.metrics.lcp = lastEntry.startTime;
            this.reportMetric('lcp', lastEntry.startTime);
        }).observe({ entryTypes: ['largest-contentful-paint'] });
        
        // First Input Delay
        new PerformanceObserver((entryList) => {
            const firstInput = entryList.getEntries()[0];
            this.metrics.fid = firstInput.processingStart - firstInput.startTime;
            this.reportMetric('fid', this.metrics.fid);
        }).observe({ entryTypes: ['first-input'] });
        
        // Cumulative Layout Shift
        let clsValue = 0;
        new PerformanceObserver((entryList) => {
            for (const entry of entryList.getEntries()) {
                if (!entry.hadRecentInput) {
                    clsValue += entry.value;
                }
            }
            this.metrics.cls = clsValue;
            this.reportMetric('cls', clsValue);
        }).observe({ entryTypes: ['layout-shift'] });
    }
    
    reportMetric(name, value) {
        // Send to analytics service
        fetch('/api/analytics/performance', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                metric: name,
                value: value,
                timestamp: Date.now(),
                url: window.location.href,
                userAgent: navigator.userAgent
            })
        });
    }
    
    getPerformanceReport() {
        return {
            ...this.metrics,
            navigation: performance.getEntriesByType('navigation')[0],
            resources: performance.getEntriesByType('resource'),
            memory: performance.memory
        };
    }
}
```

#### Application Performance Metrics
```php
// PHP Application Performance Monitoring
class ApplicationPerformanceMonitor {
    private $startTime;
    private $memoryStart;
    private $metrics = [];
    
    public function __construct() {
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage(true);
    }
    
    public function startTimer($operation) {
        $this->metrics[$operation] = [
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true)
        ];
    }
    
    public function endTimer($operation) {
        if (!isset($this->metrics[$operation])) {
            return false;
        }
        
        $this->metrics[$operation]['end_time'] = microtime(true);
        $this->metrics[$operation]['end_memory'] = memory_get_usage(true);
        $this->metrics[$operation]['duration'] = 
            $this->metrics[$operation]['end_time'] - $this->metrics[$operation]['start_time'];
        $this->metrics[$operation]['memory_used'] = 
            $this->metrics[$operation]['end_memory'] - $this->metrics[$operation]['start_memory'];
        
        return $this->metrics[$operation];
    }
    
    public function recordDatabaseQuery($query, $duration, $rows = null) {
        $this->metrics['database_queries'][] = [
            'query' => $this->sanitizeQuery($query),
            'duration' => $duration,
            'rows' => $rows,
            'timestamp' => microtime(true)
        ];
    }
    
    public function recordAPICall($endpoint, $method, $duration, $statusCode) {
        $this->metrics['api_calls'][] = [
            'endpoint' => $endpoint,
            'method' => $method,
            'duration' => $duration,
            'status_code' => $statusCode,
            'timestamp' => microtime(true)
        ];
    }
    
    public function getPerformanceReport() {
        $totalTime = microtime(true) - $this->startTime;
        $totalMemory = memory_get_peak_usage(true) - $this->memoryStart;
        
        return [
            'request_id' => uniqid(),
            'total_time' => $totalTime,
            'total_memory' => $totalMemory,
            'peak_memory' => memory_get_peak_usage(true),
            'operations' => $this->metrics,
            'server_info' => [
                'php_version' => PHP_VERSION,
                'server_load' => sys_getloadavg(),
                'timestamp' => time()
            ]
        ];
    }
    
    private function sanitizeQuery($query) {
        // Remove sensitive data from query for logging
        return preg_replace('/\b\d{4}[\s\-]?\d{4}[\s\-]?\d{4}[\s\-]?\d{4}\b/', '****-****-****-****', $query);
    }
}
```

### 📈 Business Metrics

```php
// Business Performance Metrics
class BusinessMetricsCollector {
    public function collectDailyMetrics() {
        return [
            'transactions' => [
                'total_count' => $this->getTotalTransactions(),
                'total_value' => $this->getTotalTransactionValue(),
                'average_value' => $this->getAverageTransactionValue(),
                'success_rate' => $this->getTransactionSuccessRate()
            ],
            'users' => [
                'active_users' => $this->getActiveUsers(),
                'new_registrations' => $this->getNewRegistrations(),
                'session_duration' => $this->getAverageSessionDuration()
            ],
            'inventory' => [
                'products_sold' => $this->getProductsSold(),
                'low_stock_alerts' => $this->getLowStockAlerts(),
                'inventory_turnover' => $this->getInventoryTurnover()
            ],
            'system' => [
                'error_rate' => $this->getErrorRate(),
                'response_time' => $this->getAverageResponseTime(),
                'uptime' => $this->getSystemUptime()
            ]
        ];
    }
    
    public function generatePerformanceScore() {
        $metrics = $this->collectDailyMetrics();
        
        $scores = [
            'transaction_performance' => $this->scoreTransactionPerformance($metrics['transactions']),
            'user_experience' => $this->scoreUserExperience($metrics['users']),
            'system_reliability' => $this->scoreSystemReliability($metrics['system']),
            'business_efficiency' => $this->scoreBusinessEfficiency($metrics['inventory'])
        ];
        
        $overallScore = array_sum($scores) / count($scores);
        
        return [
            'overall_score' => $overallScore,
            'category_scores' => $scores,
            'grade' => $this->getPerformanceGrade($overallScore),
            'recommendations' => $this->generateRecommendations($scores)
        ];
    }
    
    private function getPerformanceGrade($score) {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }
}
```

## Monitoring Setup

### 🔧 Infrastructure Monitoring

#### Prometheus Configuration
```yaml
# prometheus.yml
global:
  scrape_interval: 15s
  evaluation_interval: 15s

rule_files:
  - "qpos_rules.yml"

alerting:
  alertmanagers:
    - static_configs:
        - targets:
          - alertmanager:9093

scrape_configs:
  - job_name: 'qpos-backend'
    static_configs:
      - targets: ['backend:8000']
    metrics_path: '/metrics'
    scrape_interval: 10s
    
  - job_name: 'qpos-frontend'
    static_configs:
      - targets: ['frontend:3000']
    metrics_path: '/metrics'
    
  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql-exporter:9104']
    
  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx-exporter:9113']
    
  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']
```

#### Grafana Dashboard Configuration
```json
{
  "dashboard": {
    "title": "Q-POS Performance Dashboard",
    "panels": [
      {
        "title": "Response Time",
        "type": "graph",
        "targets": [
          {
            "expr": "histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "95th percentile"
          },
          {
            "expr": "histogram_quantile(0.50, rate(http_request_duration_seconds_bucket[5m]))",
            "legendFormat": "50th percentile"
          }
        ]
      },
      {
        "title": "Request Rate",
        "type": "graph",
        "targets": [
          {
            "expr": "rate(http_requests_total[5m])",
            "legendFormat": "Requests/sec"
          }
        ]
      },
      {
        "title": "Error Rate",
        "type": "singlestat",
        "targets": [
          {
            "expr": "rate(http_requests_total{status=~\"5..\"}[5m]) / rate(http_requests_total[5m]) * 100",
            "legendFormat": "Error Rate %"
          }
        ]
      }
    ]
  }
}
```

#### Application Metrics Collection
```php
// Laravel Metrics Middleware
class MetricsMiddleware {
    public function handle($request, Closure $next) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $startTime;
        $memoryUsed = memory_get_usage(true) - $startMemory;
        
        // Record metrics
        $this->recordMetrics([
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'duration' => $duration,
            'memory_used' => $memoryUsed,
            'user_id' => auth()->id(),
            'timestamp' => time()
        ]);
        
        return $response;
    }
    
    private function recordMetrics($metrics) {
        // Send to Prometheus
        app('prometheus')->histogram(
            'http_request_duration_seconds',
            'HTTP request duration in seconds',
            ['method', 'endpoint', 'status_code']
        )->observe(
            $metrics['duration'],
            [$metrics['method'], $metrics['endpoint'], $metrics['status_code']]
        );
        
        // Store in database for detailed analysis
        DB::table('performance_metrics')->insert($metrics);
        
        // Send to external monitoring service
        if (config('monitoring.external_service.enabled')) {
            $this->sendToExternalService($metrics);
        }
    }
}
```

### 📱 Real User Monitoring (RUM)

```javascript
// Real User Monitoring implementation
class RealUserMonitoring {
    constructor(config) {
        this.config = {
            apiEndpoint: '/api/rum',
            sampleRate: 0.1, // 10% sampling
            bufferSize: 50,
            flushInterval: 30000, // 30 seconds
            ...config
        };
        
        this.buffer = [];
        this.sessionId = this.generateSessionId();
        this.userId = this.getUserId();
        
        this.initializeMonitoring();
    }
    
    initializeMonitoring() {
        // Page load performance
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.collectPageLoadMetrics();
            }, 0);
        });
        
        // Navigation timing
        this.collectNavigationTiming();
        
        // Resource timing
        this.collectResourceTiming();
        
        // User interactions
        this.monitorUserInteractions();
        
        // JavaScript errors
        this.monitorJavaScriptErrors();
        
        // Network information
        this.collectNetworkInfo();
        
        // Start periodic flushing
        setInterval(() => this.flush(), this.config.flushInterval);
    }
    
    collectPageLoadMetrics() {
        const navigation = performance.getEntriesByType('navigation')[0];
        
        if (navigation) {
            this.addMetric({
                type: 'page_load',
                metrics: {
                    dns_lookup: navigation.domainLookupEnd - navigation.domainLookupStart,
                    tcp_connect: navigation.connectEnd - navigation.connectStart,
                    ssl_handshake: navigation.secureConnectionStart > 0 ? 
                        navigation.connectEnd - navigation.secureConnectionStart : 0,
                    ttfb: navigation.responseStart - navigation.requestStart,
                    dom_content_loaded: navigation.domContentLoadedEventEnd - navigation.navigationStart,
                    load_complete: navigation.loadEventEnd - navigation.navigationStart,
                    page_size: this.calculatePageSize()
                }
            });
        }
    }
    
    monitorUserInteractions() {
        ['click', 'scroll', 'keypress'].forEach(eventType => {
            document.addEventListener(eventType, (event) => {
                if (Math.random() < this.config.sampleRate) {
                    this.addMetric({
                        type: 'user_interaction',
                        event_type: eventType,
                        target: event.target.tagName,
                        timestamp: Date.now()
                    });
                }
            });
        });
    }
    
    monitorJavaScriptErrors() {
        window.addEventListener('error', (event) => {
            this.addMetric({
                type: 'javascript_error',
                message: event.message,
                filename: event.filename,
                line: event.lineno,
                column: event.colno,
                stack: event.error ? event.error.stack : null,
                timestamp: Date.now()
            });
        });
        
        window.addEventListener('unhandledrejection', (event) => {
            this.addMetric({
                type: 'promise_rejection',
                reason: event.reason.toString(),
                timestamp: Date.now()
            });
        });
    }
    
    addMetric(metric) {
        this.buffer.push({
            ...metric,
            session_id: this.sessionId,
            user_id: this.userId,
            url: window.location.href,
            user_agent: navigator.userAgent,
            timestamp: metric.timestamp || Date.now()
        });
        
        if (this.buffer.length >= this.config.bufferSize) {
            this.flush();
        }
    }
    
    flush() {
        if (this.buffer.length === 0) return;
        
        const data = [...this.buffer];
        this.buffer = [];
        
        fetch(this.config.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                metrics: data,
                metadata: {
                    session_id: this.sessionId,
                    user_id: this.userId,
                    timestamp: Date.now()
                }
            })
        }).catch(error => {
            console.warn('Failed to send RUM data:', error);
            // Re-add failed metrics to buffer
            this.buffer.unshift(...data);
        });
    }
}

// Initialize RUM
const rum = new RealUserMonitoring({
    apiEndpoint: '/api/performance/rum',
    sampleRate: 0.2
});
```

## Frontend Performance

### 🎨 Vue.js Performance Optimization

```javascript
// Vue.js Performance Plugin
const PerformancePlugin = {
    install(app, options) {
        const config = {
            trackComponents: true,
            trackRoutes: true,
            trackVuex: true,
            ...options
        };
        
        // Component performance tracking
        if (config.trackComponents) {
            app.mixin({
                beforeCreate() {
                    this.$performanceStart = performance.now();
                },
                mounted() {
                    const duration = performance.now() - this.$performanceStart;
                    this.$performance.recordComponentMount(this.$options.name, duration);
                },
                beforeUpdate() {
                    this.$updateStart = performance.now();
                },
                updated() {
                    if (this.$updateStart) {
                        const duration = performance.now() - this.$updateStart;
                        this.$performance.recordComponentUpdate(this.$options.name, duration);
                    }
                }
            });
        }
        
        // Route performance tracking
        if (config.trackRoutes) {
            app.config.globalProperties.$router.beforeEach((to, from, next) => {
                app.config.globalProperties.$routeStart = performance.now();
                next();
            });
            
            app.config.globalProperties.$router.afterEach((to, from) => {
                const duration = performance.now() - app.config.globalProperties.$routeStart;
                app.config.globalProperties.$performance.recordRouteChange(to.path, duration);
            });
        }
        
        // Performance API
        app.config.globalProperties.$performance = {
            recordComponentMount(name, duration) {
                this.sendMetric('component_mount', { name, duration });
            },
            
            recordComponentUpdate(name, duration) {
                this.sendMetric('component_update', { name, duration });
            },
            
            recordRouteChange(path, duration) {
                this.sendMetric('route_change', { path, duration });
            },
            
            recordCustomMetric(name, data) {
                this.sendMetric('custom', { name, ...data });
            },
            
            sendMetric(type, data) {
                fetch('/api/performance/frontend', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        type,
                        data,
                        timestamp: Date.now(),
                        url: window.location.href
                    })
                });
            }
        };
    }
};

// Usage
app.use(PerformancePlugin, {
    trackComponents: true,
    trackRoutes: true
});
```

### 🚀 Code Splitting & Lazy Loading

```javascript
// Dynamic imports for code splitting
const routes = [
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import(/* webpackChunkName: "dashboard" */ '@/views/Dashboard.vue')
    },
    {
        path: '/products',
        name: 'Products',
        component: () => import(/* webpackChunkName: "products" */ '@/views/Products.vue')
    },
    {
        path: '/sales',
        name: 'Sales',
        component: () => import(/* webpackChunkName: "sales" */ '@/views/Sales.vue')
    },
    {
        path: '/reports',
        name: 'Reports',
        component: () => import(/* webpackChunkName: "reports" */ '@/views/Reports.vue')
    }
];

// Lazy loading components
const LazyDataTable = defineAsyncComponent({
    loader: () => import('@/components/DataTable.vue'),
    loadingComponent: LoadingSpinner,
    errorComponent: ErrorComponent,
    delay: 200,
    timeout: 3000
});

// Image lazy loading
const LazyImage = {
    template: `
        <div class="lazy-image-container">
            <img 
                v-if="loaded" 
                :src="src" 
                :alt="alt"
                @load="onLoad"
                @error="onError"
            />
            <div v-else class="image-placeholder">
                <loading-spinner />
            </div>
        </div>
    `,
    props: ['src', 'alt'],
    data() {
        return {
            loaded: false,
            observer: null
        };
    },
    mounted() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.loadImage();
                    this.observer.unobserve(entry.target);
                }
            });
        });
        
        this.observer.observe(this.$el);
    },
    methods: {
        loadImage() {
            const img = new Image();
            img.onload = () => {
                this.loaded = true;
                this.onLoad();
            };
            img.onerror = this.onError;
            img.src = this.src;
        },
        
        onLoad() {
            this.$emit('load');
        },
        
        onError() {
            this.$emit('error');
        }
    },
    beforeUnmount() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }
};
```

### 📦 Bundle Optimization

```javascript
// webpack.config.js optimization
module.exports = {
    optimization: {
        splitChunks: {
            chunks: 'all',
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name: 'vendors',
                    chunks: 'all',
                    priority: 10
                },
                common: {
                    name: 'common',
                    minChunks: 2,
                    chunks: 'all',
                    priority: 5,
                    reuseExistingChunk: true
                }
            }
        },
        runtimeChunk: {
            name: 'runtime'
        }
    },
    
    // Performance budgets
    performance: {
        maxAssetSize: 250000,
        maxEntrypointSize: 250000,
        hints: 'warning'
    },
    
    // Bundle analyzer
    plugins: [
        new BundleAnalyzerPlugin({
            analyzerMode: 'static',
            openAnalyzer: false,
            reportFilename: 'bundle-report.html'
        })
    ]
};

// Service Worker for caching
const CACHE_NAME = 'qpos-v1';
const urlsToCache = [
    '/',
    '/static/css/main.css',
    '/static/js/main.js',
    '/static/images/logo.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                // Return cached version or fetch from network
                return response || fetch(event.request);
            })
    );
});
```

## Backend Performance

### ⚡ Laravel Performance Optimization

```php
// Database Query Optimization
class OptimizedProductRepository {
    public function getProductsWithCategories($limit = 20, $offset = 0) {
        return Product::with(['category', 'images' => function($query) {
                $query->select('id', 'product_id', 'url', 'alt_text')
                      ->where('is_primary', true);
            }])
            ->select('id', 'name', 'sku', 'price', 'category_id', 'stock_quantity')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();
    }
    
    public function searchProducts($query, $filters = []) {
        $builder = Product::query()
            ->select('id', 'name', 'sku', 'price', 'stock_quantity')
            ->where('is_active', true);
        
        // Full-text search
        if (!empty($query)) {
            $builder->whereRaw(
                'MATCH(name, description, sku) AGAINST(? IN BOOLEAN MODE)',
                [$query . '*']
            );
        }
        
        // Apply filters
        if (!empty($filters['category_id'])) {
            $builder->where('category_id', $filters['category_id']);
        }
        
        if (!empty($filters['price_range'])) {
            $builder->whereBetween('price', $filters['price_range']);
        }
        
        return $builder->paginate(20);
    }
}

// Caching Strategy
class CacheService {
    private $redis;
    private $defaultTtl = 3600; // 1 hour
    
    public function __construct() {
        $this->redis = Redis::connection();
    }
    
    public function remember($key, $callback, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTtl;
        
        $cached = $this->redis->get($key);
        if ($cached !== null) {
            return json_decode($cached, true);
        }
        
        $result = $callback();
        $this->redis->setex($key, $ttl, json_encode($result));
        
        return $result;
    }
    
    public function tags($tags) {
        return new TaggedCache($this->redis, $tags);
    }
    
    public function invalidatePattern($pattern) {
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }
}

// API Response Optimization
class ApiResponseOptimizer {
    public function optimizeResponse($data, $request) {
        // Field selection
        if ($request->has('fields')) {
            $fields = explode(',', $request->get('fields'));
            $data = $this->selectFields($data, $fields);
        }
        
        // Compression
        if ($this->shouldCompress($request)) {
            return response()->json($data)
                ->header('Content-Encoding', 'gzip')
                ->setContent(gzencode(json_encode($data)));
        }
        
        return response()->json($data);
    }
    
    private function selectFields($data, $fields) {
        if (is_array($data)) {
            return array_intersect_key($data, array_flip($fields));
        }
        
        if (is_object($data)) {
            $result = new stdClass();
            foreach ($fields as $field) {
                if (property_exists($data, $field)) {
                    $result->$field = $data->$field;
                }
            }
            return $result;
        }
        
        return $data;
    }
    
    private function shouldCompress($request) {
        $acceptEncoding = $request->header('Accept-Encoding', '');
        return strpos($acceptEncoding, 'gzip') !== false;
    }
}
```

### 🔄 Queue Performance

```php
// Optimized Queue Jobs
class ProcessSalesReportJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $backoff = [60, 120, 300]; // Exponential backoff
    
    private $dateRange;
    private $userId;
    
    public function __construct($dateRange, $userId) {
        $this->dateRange = $dateRange;
        $this->userId = $userId;
        
        // Set queue based on priority
        $this->onQueue('reports');
    }
    
    public function handle() {
        $startTime = microtime(true);
        
        try {
            // Process in chunks to avoid memory issues
            $chunkSize = 1000;
            $totalProcessed = 0;
            
            Transaction::whereBetween('created_at', $this->dateRange)
                ->chunk($chunkSize, function($transactions) use (&$totalProcessed) {
                    $this->processTransactionChunk($transactions);
                    $totalProcessed += $transactions->count();
                    
                    // Update progress
                    $this->updateProgress($totalProcessed);
                });
            
            $duration = microtime(true) - $startTime;
            
            // Log performance metrics
            Log::info('Sales report generated', [
                'user_id' => $this->userId,
                'duration' => $duration,
                'records_processed' => $totalProcessed,
                'memory_peak' => memory_get_peak_usage(true)
            ]);
            
        } catch (Exception $e) {
            Log::error('Sales report generation failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'duration' => microtime(true) - $startTime
            ]);
            
            throw $e;
        }
    }
    
    public function failed(Exception $exception) {
        // Notify user of failure
        Notification::send(
            User::find($this->userId),
            new ReportGenerationFailed($exception->getMessage())
        );
    }
}

// Queue Monitoring
class QueueMonitor {
    public function getQueueStats() {
        $redis = Redis::connection();
        
        $queues = ['default', 'high', 'reports', 'emails'];
        $stats = [];
        
        foreach ($queues as $queue) {
            $stats[$queue] = [
                'waiting' => $redis->llen("queues:{$queue}"),
                'delayed' => $redis->zcard("queues:{$queue}:delayed"),
                'failed' => $redis->llen("queues:{$queue}:failed"),
                'processing' => $redis->llen("queues:{$queue}:reserved")
            ];
        }
        
        return $stats;
    }
    
    public function getWorkerStats() {
        return [
            'active_workers' => $this->getActiveWorkerCount(),
            'memory_usage' => $this->getWorkerMemoryUsage(),
            'cpu_usage' => $this->getWorkerCpuUsage(),
            'uptime' => $this->getWorkerUptime()
        ];
    }
}
```

## Database Performance

### 🗄️ MySQL Optimization

```sql
-- Index optimization
CREATE INDEX idx_products_active_category ON products(is_active, category_id);
CREATE INDEX idx_transactions_date_status ON transactions(created_at, status);
CREATE INDEX idx_order_items_product ON order_items(product_id, quantity);

-- Full-text search indexes
ALTER TABLE products ADD FULLTEXT(name, description, sku);
ALTER TABLE customers ADD FULLTEXT(name, email, phone);

-- Composite indexes for common queries
CREATE INDEX idx_sales_report ON transactions(created_at, status, total_amount);
CREATE INDEX idx_inventory_lookup ON products(sku, is_active, stock_quantity);

-- Partitioning for large tables
ALTER TABLE transactions
PARTITION BY RANGE (YEAR(created_at)) (
    PARTITION p2024 VALUES LESS THAN (2025),
PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p_future VALUES LESS THAN MAXVALUE
);
```

```php
// Database Performance Monitoring
class DatabasePerformanceMonitor {
    public function analyzeSlowQueries() {
        $slowQueries = DB::select("
            SELECT 
                query_time,
                lock_time,
                rows_sent,
                rows_examined,
                sql_text
            FROM mysql.slow_log 
            WHERE start_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ORDER BY query_time DESC
            LIMIT 20
        ");
        
        return array_map(function($query) {
            return [
                'query_time' => $query->query_time,
                'lock_time' => $query->lock_time,
                'rows_sent' => $query->rows_sent,
                'rows_examined' => $query->rows_examined,
                'efficiency_ratio' => $query->rows_sent / max($query->rows_examined, 1),
                'sql_text' => $this->sanitizeQuery($query->sql_text)
            ];
        }, $slowQueries);
    }
    
    public function getConnectionPoolStats() {
        $stats = DB::select("SHOW STATUS LIKE 'Threads_%'");
        $variables = DB::select("SHOW VARIABLES LIKE 'max_connections'");
        
        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->Variable_name] = $stat->Value;
        }
        
        foreach ($variables as $var) {
            $result[$var->Variable_name] = $var->Value;
        }
        
        return [
            'connected' => $result['Threads_connected'],
            'running' => $result['Threads_running'],
            'cached' => $result['Threads_cached'],
            'max_connections' => $result['max_connections'],
            'connection_usage' => ($result['Threads_connected'] / $result['max_connections']) * 100
        ];
    }
    
    public function optimizeTable($tableName) {
        // Analyze table
        DB::statement("ANALYZE TABLE {$tableName}");
        
        // Optimize table
        DB::statement("OPTIMIZE TABLE {$tableName}");
        
        // Get table stats
        $stats = DB::select("
            SELECT 
                table_rows,
                data_length,
                index_length,
                data_free
            FROM information_schema.tables 
            WHERE table_name = ? AND table_schema = DATABASE()
        ", [$tableName]);
        
        return $stats[0] ?? null;
    }
}
```

### 📊 Query Performance Analysis

```php
// Query Performance Analyzer
class QueryPerformanceAnalyzer {
    private $queries = [];
    
    public function startProfiling() {
        DB::enableQueryLog();
        
        DB::listen(function($query) {
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
                'connection' => $query->connectionName
            ];
        });
    }
    
    public function analyzeQueries() {
        $analysis = [
            'total_queries' => count($this->queries),
            'total_time' => array_sum(array_column($this->queries, 'time')),
            'slow_queries' => [],
            'duplicate_queries' => [],
            'n_plus_one' => []
        ];
        
        // Find slow queries (> 100ms)
        $analysis['slow_queries'] = array_filter($this->queries, function($query) {
            return $query['time'] > 100;
        });
        
        // Find duplicate queries
        $queryGroups = [];
        foreach ($this->queries as $query) {
            $key = md5($query['sql']);
            if (!isset($queryGroups[$key])) {
                $queryGroups[$key] = [];
            }
            $queryGroups[$key][] = $query;
        }
        
        $analysis['duplicate_queries'] = array_filter($queryGroups, function($group) {
            return count($group) > 1;
        });
        
        // Detect N+1 queries
        $analysis['n_plus_one'] = $this->detectNPlusOneQueries();
        
        return $analysis;
    }
    
    private function detectNPlusOneQueries() {
        $patterns = [];
        $nPlusOne = [];
        
        foreach ($this->queries as $query) {
            $pattern = preg_replace('/\d+/', '?', $query['sql']);
            
            if (!isset($patterns[$pattern])) {
                $patterns[$pattern] = 0;
            }
            $patterns[$pattern]++;
            
            // If same pattern appears more than 10 times, likely N+1
            if ($patterns[$pattern] > 10) {
                $nPlusOne[] = [
                    'pattern' => $pattern,
                    'count' => $patterns[$pattern],
                    'example' => $query
                ];
            }
        }
        
        return $nPlusOne;
    }
    
    public function generateOptimizationSuggestions() {
        $analysis = $this->analyzeQueries();
        $suggestions = [];
        
        if (!empty($analysis['slow_queries'])) {
            $suggestions[] = [
                'type' => 'slow_queries',
                'message' => 'Found ' . count($analysis['slow_queries']) . ' slow queries',
                'action' => 'Add indexes or optimize query structure'
            ];
        }
        
        if (!empty($analysis['duplicate_queries'])) {
            $suggestions[] = [
                'type' => 'duplicate_queries',
                'message' => 'Found duplicate queries',
                'action' => 'Implement query result caching'
            ];
        }
        
        if (!empty($analysis['n_plus_one'])) {
            $suggestions[] = [
                'type' => 'n_plus_one',
                'message' => 'Detected potential N+1 query problems',
                'action' => 'Use eager loading with with() method'
            ];
        }
        
        return $suggestions;
    }
}
```

## Infrastructure Monitoring

### 🖥️ Server Monitoring

```bash
#!/bin/bash
# System performance monitoring script

MONITOR_LOG="/var/log/system-performance.log"
ALERT_THRESHOLD_CPU=80
ALERT_THRESHOLD_MEMORY=85
ALERT_THRESHOLD_DISK=90

log_metric() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$MONITOR_LOG"
}

get_cpu_usage() {
    top -bn1 | grep "Cpu(s)" | awk '{print $2}' | awk -F'%' '{print $1}'
}

get_memory_usage() {
    free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}'
}

get_disk_usage() {
    df -h / | awk 'NR==2 {print $5}' | sed 's/%//'
}

get_load_average() {
    uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//'
}

get_network_stats() {
    # Get network interface statistics
    cat /proc/net/dev | grep eth0 | awk '{print "rx_bytes:" $2 ",tx_bytes:" $10}'
}

check_system_health() {
    CPU_USAGE=$(get_cpu_usage)
    MEMORY_USAGE=$(get_memory_usage)
    DISK_USAGE=$(get_disk_usage)
    LOAD_AVG=$(get_load_average)
    NETWORK_STATS=$(get_network_stats)
    
    # Log metrics
    log_metric "CPU:${CPU_USAGE}% MEM:${MEMORY_USAGE}% DISK:${DISK_USAGE}% LOAD:${LOAD_AVG} NET:${NETWORK_STATS}"
    
    # Check thresholds and send alerts
    if (( $(echo "$CPU_USAGE > $ALERT_THRESHOLD_CPU" | bc -l) )); then
        send_alert "High CPU usage: ${CPU_USAGE}%"
    fi
    
    if (( $(echo "$MEMORY_USAGE > $ALERT_THRESHOLD_MEMORY" | bc -l) )); then
        send_alert "High memory usage: ${MEMORY_USAGE}%"
    fi
    
    if [ "$DISK_USAGE" -gt "$ALERT_THRESHOLD_DISK" ]; then
        send_alert "High disk usage: ${DISK_USAGE}%"
    fi
}

send_alert() {
    local message="$1"
    
    # Send to monitoring service
    curl -X POST "$MONITORING_WEBHOOK" \
        -H "Content-Type: application/json" \
        -d "{\"text\":\"ALERT: $message on $(hostname)\"}"
    
    # Log alert
    log_metric "ALERT: $message"
}

# Run monitoring
check_system_health
```

### 📊 Application Performance Monitoring

```php
// APM (Application Performance Monitoring)
class ApplicationPerformanceMonitor {
    private $traces = [];
    private $currentTrace = null;
    
    public function startTrace($name, $type = 'custom') {
        $trace = [
            'id' => uniqid(),
            'name' => $name,
            'type' => $type,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'spans' => [],
            'metadata' => [
                'request_id' => request()->header('X-Request-ID', uniqid()),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ];
        
        $this->traces[$trace['id']] = $trace;
        $this->currentTrace = $trace['id'];
        
        return $trace['id'];
    }
    
    public function addSpan($name, $type, $startTime = null, $endTime = null) {
        if (!$this->currentTrace) {
            return false;
        }
        
        $span = [
            'name' => $name,
            'type' => $type,
            'start_time' => $startTime ?? microtime(true),
            'end_time' => $endTime,
            'duration' => null,
            'metadata' => []
        ];
        
        if ($span['end_time']) {
            $span['duration'] = $span['end_time'] - $span['start_time'];
        }
        
        $this->traces[$this->currentTrace]['spans'][] = $span;
        
        return count($this->traces[$this->currentTrace]['spans']) - 1;
    }
    
    public function endSpan($spanIndex, $metadata = []) {
        if (!$this->currentTrace || !isset($this->traces[$this->currentTrace]['spans'][$spanIndex])) {
            return false;
        }
        
        $span = &$this->traces[$this->currentTrace]['spans'][$spanIndex];
        $span['end_time'] = microtime(true);
        $span['duration'] = $span['end_time'] - $span['start_time'];
        $span['metadata'] = array_merge($span['metadata'], $metadata);
        
        return true;
    }
    
    public function endTrace($traceId = null) {
        $traceId = $traceId ?? $this->currentTrace;
        
        if (!isset($this->traces[$traceId])) {
            return false;
        }
        
        $trace = &$this->traces[$traceId];
        $trace['end_time'] = microtime(true);
        $trace['duration'] = $trace['end_time'] - $trace['start_time'];
        $trace['end_memory'] = memory_get_usage(true);
        $trace['memory_used'] = $trace['end_memory'] - $trace['start_memory'];
        $trace['peak_memory'] = memory_get_peak_usage(true);
        
        // Send to APM service
        $this->sendToAPM($trace);
        
        // Clear current trace
        if ($this->currentTrace === $traceId) {
            $this->currentTrace = null;
        }
        
        return $trace;
    }
    
    private function sendToAPM($trace) {
        // Send to external APM service (e.g., New Relic, Datadog)
        if (config('apm.enabled')) {
            Http::post(config('apm.endpoint'), [
                'trace' => $trace,
                'service' => config('app.name'),
                'environment' => config('app.env'),
                'timestamp' => time()
            ]);
        }
        
        // Store in database for analysis
        DB::table('performance_traces')->insert([
            'trace_id' => $trace['id'],
            'name' => $trace['name'],
            'type' => $trace['type'],
            'duration' => $trace['duration'],
            'memory_used' => $trace['memory_used'],
            'spans_count' => count($trace['spans']),
            'metadata' => json_encode($trace['metadata']),
            'created_at' => now()
        ]);
    }
    
    public function getPerformanceInsights($timeRange = '1 hour') {
        $traces = DB::table('performance_traces')
            ->where('created_at', '>=', now()->sub($timeRange))
            ->get();
        
        return [
            'total_traces' => $traces->count(),
            'average_duration' => $traces->avg('duration'),
            'p95_duration' => $traces->sortBy('duration')->values()[floor($traces->count() * 0.95)]->duration ?? 0,
            'slowest_traces' => $traces->sortByDesc('duration')->take(10),
            'memory_usage' => [
                'average' => $traces->avg('memory_used'),
                'peak' => $traces->max('memory_used')
            ],
            'trace_types' => $traces->groupBy('type')->map->count()
        ];
    }
}
```

## Performance Testing

### 🧪 Load Testing

```javascript
// K6 Load Testing Script
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

// Custom metrics
const errorRate = new Rate('errors');

// Test configuration
export let options = {
    stages: [
        { duration: '2m', target: 10 },   // Ramp up
        { duration: '5m', target: 50 },   // Stay at 50 users
        { duration: '2m', target: 100 },  // Ramp up to 100
        { duration: '5m', target: 100 },  // Stay at 100
        { duration: '2m', target: 0 },    // Ramp down
    ],
    thresholds: {
        http_req_duration: ['p(95)<500'], // 95% of requests under 500ms
        http_req_failed: ['rate<0.1'],    // Error rate under 10%
        errors: ['rate<0.1'],
    },
};

// Test data
const BASE_URL = 'https://api.qpos.com';
const API_TOKEN = 'your-api-token';

// Test scenarios
export default function() {
    const scenarios = [
        testLogin,
        testProductList,
        testProductSearch,
        testCreateTransaction,
        testGenerateReport
    ];
    
    // Randomly select a scenario
    const scenario = scenarios[Math.floor(Math.random() * scenarios.length)];
    scenario();
    
    sleep(1);
}

function testLogin() {
    const payload = {
        email: 'test@example.com',
        password: 'password123'
    };
    
    const response = http.post(`${BASE_URL}/auth/login`, JSON.stringify(payload), {
        headers: {
            'Content-Type': 'application/json',
        },
    });
    
    const success = check(response, {
        'login status is 200': (r) => r.status === 200,
        'login response time < 1s': (r) => r.timings.duration < 1000,
        'login returns token': (r) => JSON.parse(r.body).token !== undefined,
    });
    
    errorRate.add(!success);
}

function testProductList() {
    const response = http.get(`${BASE_URL}/products?limit=20`, {
        headers: {
            'Authorization': `Bearer ${API_TOKEN}`,
        },
    });
    
    const success = check(response, {
        'product list status is 200': (r) => r.status === 200,
        'product list response time < 500ms': (r) => r.timings.duration < 500,
        'product list returns data': (r) => JSON.parse(r.body).data.length > 0,
    });
    
    errorRate.add(!success);
}

function testProductSearch() {
    const searchTerms = ['laptop', 'phone', 'tablet', 'mouse', 'keyboard'];
    const term = searchTerms[Math.floor(Math.random() * searchTerms.length)];
    
    const response = http.get(`${BASE_URL}/products/search?q=${term}`, {
        headers: {
            'Authorization': `Bearer ${API_TOKEN}`,
        },
    });
    
    const success = check(response, {
        'search status is 200': (r) => r.status === 200,
        'search response time < 1s': (r) => r.timings.duration < 1000,
    });
    
    errorRate.add(!success);
}

function testCreateTransaction() {
    const payload = {
        customer_id: Math.floor(Math.random() * 100) + 1,
        items: [
            {
                product_id: Math.floor(Math.random() * 50) + 1,
                quantity: Math.floor(Math.random() * 5) + 1,
                price: 29.99
            }
        ],
        payment_method: 'cash'
    };
    
    const response = http.post(`${BASE_URL}/transactions`, JSON.stringify(payload), {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${API_TOKEN}`,
        },
    });
    
    const success = check(response, {
        'transaction status is 201': (r) => r.status === 201,
        'transaction response time < 2s': (r) => r.timings.duration < 2000,
        'transaction returns id': (r) => JSON.parse(r.body).id !== undefined,
    });
    
    errorRate.add(!success);
}

function testGenerateReport() {
    const response = http.get(`${BASE_URL}/reports/sales?period=daily`, {
        headers: {
            'Authorization': `Bearer ${API_TOKEN}`,
        },
    });
    
    const success = check(response, {
        'report status is 200': (r) => r.status === 200,
        'report response time < 5s': (r) => r.timings.duration < 5000,
    });
    
    errorRate.add(!success);
}
```

### 🔬 Stress Testing

```python
# Locust stress testing
from locust import HttpUser, task, between
import random
import json

class QPOSUser(HttpUser):
    wait_time = between(1, 3)
    
    def on_start(self):
        # Login and get token
        response = self.client.post("/auth/login", json={
            "email": "test@example.com",
            "password": "password123"
        })
        
        if response.status_code == 200:
            self.token = response.json().get("token")
            self.client.headers.update({"Authorization": f"Bearer {self.token}"})
    
    @task(3)
    def view_products(self):
        self.client.get("/products?limit=20")
    
    @task(2)
    def search_products(self):
        search_terms = ["laptop", "phone", "tablet", "mouse", "keyboard"]
        term = random.choice(search_terms)
        self.client.get(f"/products/search?q={term}")
    
    @task(1)
    def create_transaction(self):
        payload = {
            "customer_id": random.randint(1, 100),
            "items": [{
                "product_id": random.randint(1, 50),
                "quantity": random.randint(1, 5),
                "price": round(random.uniform(10, 100), 2)
            }],
            "payment_method": random.choice(["cash", "card", "digital"])
        }
        self.client.post("/transactions", json=payload)
    
    @task(1)
    def view_dashboard(self):
        self.client.get("/dashboard/stats")
```

### 📊 Performance Benchmarking

```bash
#!/bin/bash
# Performance benchmarking script

BENCHMARK_URL="https://api.qpos.com"
RESULTS_DIR="./benchmark-results"
DATE=$(date +"%Y%m%d_%H%M%S")

mkdir -p "$RESULTS_DIR"

echo "Starting Q-POS Performance Benchmark - $DATE"

# API Endpoint Benchmarks
echo "Testing API endpoints..."
ab -n 1000 -c 10 "$BENCHMARK_URL/products" > "$RESULTS_DIR/products_$DATE.txt"
ab -n 500 -c 5 "$BENCHMARK_URL/transactions" > "$RESULTS_DIR/transactions_$DATE.txt"
ab -n 100 -c 2 "$BENCHMARK_URL/reports/sales" > "$RESULTS_DIR/reports_$DATE.txt"

# Database Performance
echo "Testing database performance..."
mysql -u root -p$DB_PASSWORD -e "
    SELECT 'Product Queries' as test_name, 
           COUNT(*) as query_count,
           AVG(query_time) as avg_time,
           MAX(query_time) as max_time
    FROM mysql.slow_log 
    WHERE sql_text LIKE '%products%' 
    AND start_time >= DATE_SUB(NOW(), INTERVAL 1 HOUR);
" > "$RESULTS_DIR/db_performance_$DATE.txt"

# Memory and CPU Usage
echo "Collecting system metrics..."
top -bn1 | head -20 > "$RESULTS_DIR/system_stats_$DATE.txt"
free -h >> "$RESULTS_DIR/system_stats_$DATE.txt"
df -h >> "$RESULTS_DIR/system_stats_$DATE.txt"

echo "Benchmark completed. Results saved to $RESULTS_DIR"
```

## Optimization Strategies

### ⚡ Frontend Optimization

```javascript
// Performance optimization techniques
class PerformanceOptimizer {
    constructor() {
        this.initializeOptimizations();
    }
    
    initializeOptimizations() {
        this.enableVirtualScrolling();
        this.implementImageLazyLoading();
        this.optimizeEventListeners();
        this.enableServiceWorkerCaching();
    }
    
    enableVirtualScrolling() {
        // Virtual scrolling for large lists
        const VirtualList = {
            props: ['items', 'itemHeight'],
            data() {
                return {
                    scrollTop: 0,
                    containerHeight: 400
                };
            },
            computed: {
                visibleItems() {
                    const start = Math.floor(this.scrollTop / this.itemHeight);
                    const end = Math.min(
                        start + Math.ceil(this.containerHeight / this.itemHeight) + 1,
                        this.items.length
                    );
                    return this.items.slice(start, end).map((item, index) => ({
                        ...item,
                        index: start + index
                    }));
                },
                totalHeight() {
                    return this.items.length * this.itemHeight;
                },
                offsetY() {
                    return Math.floor(this.scrollTop / this.itemHeight) * this.itemHeight;
                }
            },
            methods: {
                onScroll(event) {
                    this.scrollTop = event.target.scrollTop;
                }
            }
        };
        
        return VirtualList;
    }
    
    implementImageLazyLoading() {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
    
    optimizeEventListeners() {
        // Debounced search
        const debounce = (func, wait) => {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        };
        
        // Throttled scroll
        const throttle = (func, limit) => {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        };
        
        return { debounce, throttle };
    }
}
```

### 🔧 Backend Optimization

```php
// Backend optimization strategies
class BackendOptimizer {
    public function optimizeQueries() {
        // Query optimization examples
        return [
            'eager_loading' => $this->demonstrateEagerLoading(),
            'query_scopes' => $this->demonstrateQueryScopes(),
            'database_indexing' => $this->demonstrateDatabaseIndexing(),
            'caching_strategies' => $this->demonstrateCaching()
        ];
    }
    
    private function demonstrateEagerLoading() {
        // Bad: N+1 query problem
        $badExample = Product::all();
        foreach ($badExample as $product) {
            echo $product->category->name; // This creates N+1 queries
        }
        
        // Good: Eager loading
        $goodExample = Product::with(['category', 'images'])->get();
        foreach ($goodExample as $product) {
            echo $product->category->name; // Only 1 additional query
        }
        
        return 'Use eager loading to prevent N+1 queries';
    }
    
    private function demonstrateQueryScopes() {
        // Define reusable query scopes
        class Product extends Model {
            public function scopeActive($query) {
                return $query->where('is_active', true);
            }
            
            public function scopeInStock($query) {
                return $query->where('stock_quantity', '>', 0);
            }
            
            public function scopeByCategory($query, $categoryId) {
                return $query->where('category_id', $categoryId);
            }
        }
        
        // Usage
        $products = Product::active()
            ->inStock()
            ->byCategory(1)
            ->get();
        
        return 'Use query scopes for reusable and readable queries';
    }
    
    private function demonstrateCaching() {
        // Multi-level caching strategy
        $cacheKey = 'products:active:' . md5(serialize($filters));
        
        // Level 1: Application cache (Redis)
        $products = Cache::remember($cacheKey, 3600, function() use ($filters) {
            return Product::active()
                ->with('category')
                ->where($filters)
                ->get();
        });
        
        // Level 2: Query result cache
        DB::enableQueryLog();
        $result = DB::table('products')
            ->where('is_active', true)
            ->remember(1800) // Cache for 30 minutes
            ->get();
        
        // Level 3: HTTP cache headers
        return response()->json($products)
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('ETag', md5(json_encode($products)));
    }
}
```

## Alerting & Notifications

### 🚨 Alert Configuration

```yaml
# Prometheus alerting rules
groups:
  - name: qpos-performance
    rules:
      - alert: HighResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 2
        for: 2m
        labels:
          severity: warning
        annotations:
          summary: "High response time detected"
          description: "95th percentile response time is {{ $value }}s"
      
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) / rate(http_requests_total[5m]) > 0.05
        for: 1m
        labels:
          severity: critical
        annotations:
          summary: "High error rate detected"
          description: "Error rate is {{ $value | humanizePercentage }}"
      
      - alert: DatabaseSlowQueries
        expr: mysql_slow_queries_total > 10
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "Database slow queries detected"
          description: "{{ $value }} slow queries in the last 5 minutes"
      
      - alert: HighMemoryUsage
        expr: (node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes) / node_memory_MemTotal_bytes > 0.85
        for: 5m
        labels:
          severity: warning
        annotations:
          summary: "High memory usage"
          description: "Memory usage is {{ $value | humanizePercentage }}"
```

### 📧 Notification System

```php
// Performance alert notification system
class PerformanceAlertManager {
    private $channels = [];
    private $thresholds = [];
    
    public function __construct() {
        $this->loadConfiguration();
    }
    
    public function checkPerformanceMetrics() {
        $metrics = $this->collectCurrentMetrics();
        
        foreach ($this->thresholds as $metric => $threshold) {
            if ($this->isThresholdExceeded($metrics[$metric], $threshold)) {
                $this->sendAlert($metric, $metrics[$metric], $threshold);
            }
        }
    }
    
    private function collectCurrentMetrics() {
        return [
            'response_time_p95' => $this->getResponseTimeP95(),
            'error_rate' => $this->getErrorRate(),
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'database_connections' => $this->getDatabaseConnections(),
            'queue_size' => $this->getQueueSize()
        ];
    }
    
    private function sendAlert($metric, $currentValue, $threshold) {
        $alert = [
            'metric' => $metric,
            'current_value' => $currentValue,
            'threshold' => $threshold['value'],
            'severity' => $threshold['severity'],
            'timestamp' => now(),
            'server' => gethostname()
        ];
        
        // Send to configured channels
        foreach ($this->channels as $channel) {
            $this->sendToChannel($channel, $alert);
        }
        
        // Log alert
        Log::warning('Performance alert triggered', $alert);
    }
    
    private function sendToChannel($channel, $alert) {
        switch ($channel['type']) {
            case 'slack':
                $this->sendSlackAlert($channel, $alert);
                break;
            case 'email':
                $this->sendEmailAlert($channel, $alert);
                break;
            case 'webhook':
                $this->sendWebhookAlert($channel, $alert);
                break;
        }
    }
    
    private function sendSlackAlert($channel, $alert) {
        $message = [
            'text' => "🚨 Performance Alert: {$alert['metric']}",
            'attachments' => [[
                'color' => $alert['severity'] === 'critical' ? 'danger' : 'warning',
                'fields' => [
                    ['title' => 'Metric', 'value' => $alert['metric'], 'short' => true],
                    ['title' => 'Current Value', 'value' => $alert['current_value'], 'short' => true],
                    ['title' => 'Threshold', 'value' => $alert['threshold'], 'short' => true],
                    ['title' => 'Server', 'value' => $alert['server'], 'short' => true]
                ]
            ]]
        ];
        
        Http::post($channel['webhook_url'], $message);
    }
}
```

## Performance Best Practices

### 📋 Development Guidelines

```markdown
## Frontend Best Practices

### 1. Component Optimization
- Use `v-memo` for expensive computations
- Implement proper key attributes for v-for loops
- Avoid inline functions in templates
- Use computed properties instead of methods for derived data

### 2. Bundle Optimization
- Implement code splitting at route level
- Use dynamic imports for heavy components
- Optimize images (WebP format, proper sizing)
- Minimize and compress CSS/JS assets

### 3. Network Optimization
- Implement request caching
- Use HTTP/2 server push
- Minimize API calls with batch requests
- Implement proper error handling and retries

## Backend Best Practices

### 1. Database Optimization
- Use appropriate indexes
- Implement query result caching
- Avoid N+1 query problems
- Use database connection pooling

### 2. API Design
- Implement pagination for large datasets
- Use field selection to reduce payload size
- Implement proper HTTP caching headers
- Use compression (gzip) for responses

### 3. Caching Strategy
- Implement multi-level caching
- Use cache tags for efficient invalidation
- Monitor cache hit rates
- Set appropriate TTL values
```

### 🔍 Code Review Checklist

```markdown
## Performance Code Review Checklist

### Database Queries
- [ ] No N+1 query problems
- [ ] Appropriate use of eager loading
- [ ] Proper indexing for query conditions
- [ ] Query result caching where appropriate
- [ ] Pagination for large datasets

### API Endpoints
- [ ] Response time under target thresholds
- [ ] Proper HTTP status codes
- [ ] Appropriate caching headers
- [ ] Input validation and sanitization
- [ ] Error handling and logging

### Frontend Components
- [ ] Proper use of Vue.js reactivity
- [ ] Efficient event handling
- [ ] Image optimization and lazy loading
- [ ] Bundle size impact assessment
- [ ] Accessibility considerations

### Caching
- [ ] Appropriate cache keys
- [ ] Proper cache invalidation
- [ ] Cache hit rate monitoring
- [ ] TTL values justified

### Monitoring
- [ ] Performance metrics collection
- [ ] Error tracking implementation
- [ ] Alert thresholds configured
- [ ] Logging for debugging
```

## Tools & Technologies

### 📊 Monitoring Stack

| Tool | Purpose | Configuration |
|------|---------|---------------|
| **Prometheus** | Metrics collection | Custom metrics, alerting rules |
| **Grafana** | Visualization | Dashboards, alerts |
| **New Relic** | APM | Application monitoring |
| **Sentry** | Error tracking | Real-time error monitoring |
| **Redis** | Caching | Session storage, query cache |
| **Nginx** | Load balancing | Request routing, SSL termination |

### 🧪 Testing Tools

| Tool | Purpose | Use Case |
|------|---------|----------|
| **K6** | Load testing | API performance testing |
| **Locust** | Stress testing | User behavior simulation |
| **Apache Bench** | Benchmarking | Quick performance tests |
| **Lighthouse** | Frontend audit | Core Web Vitals |
| **WebPageTest** | Performance analysis | Detailed performance insights |

### 📈 Analytics Tools

| Tool | Purpose | Metrics |
|------|---------|----------|
| **Google Analytics** | User behavior | Page views, user flow |
| **Hotjar** | User experience | Heatmaps, session recordings |
| **Mixpanel** | Event tracking | Custom events, funnels |
| **DataDog** | Infrastructure | System metrics, logs |

## Performance Reports

### 📊 Daily Performance Report

```php
// Automated daily performance report
class DailyPerformanceReport {
    public function generate() {
        $report = [
            'date' => now()->toDateString(),
            'summary' => $this->generateSummary(),
            'metrics' => $this->collectMetrics(),
            'alerts' => $this->getAlerts(),
            'recommendations' => $this->generateRecommendations()
        ];
        
        $this->sendReport($report);
        return $report;
    }
    
    private function generateSummary() {
        return [
            'overall_health' => $this->calculateHealthScore(),
            'total_requests' => $this->getTotalRequests(),
            'average_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
            'uptime' => $this->getUptime()
        ];
    }
    
    private function collectMetrics() {
        return [
            'frontend' => [
                'page_load_time' => $this->getPageLoadTime(),
                'core_web_vitals' => $this->getCoreWebVitals(),
                'bundle_size' => $this->getBundleSize()
            ],
            'backend' => [
                'api_response_time' => $this->getApiResponseTime(),
                'database_performance' => $this->getDatabasePerformance(),
                'cache_hit_rate' => $this->getCacheHitRate()
            ],
            'infrastructure' => [
                'cpu_usage' => $this->getCpuUsage(),
                'memory_usage' => $this->getMemoryUsage(),
                'disk_usage' => $this->getDiskUsage()
            ]
        ];
    }
}
```

---

## 📞 Support & Resources

### 🆘 Performance Issues

Jika mengalami masalah performa:

1. **Immediate Issues**: Hubungi tim DevOps di Slack #devops-alerts
2. **Performance Degradation**: Buat ticket di sistem monitoring
3. **Optimization Requests**: Diskusi dengan tim development

### 📚 Additional Resources

- [Laravel Performance Best Practices](https://laravel.com/docs/performance)
- [Vue.js Performance Guide](https://vuejs.org/guide/best-practices/performance.html)
- [MySQL Performance Tuning](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)
- [Web Performance Optimization](https://web.dev/performance/)

### 🔗 Monitoring Dashboards

- **Production Dashboard**: https://grafana.qpos.com/dashboard/production
- **API Metrics**: https://grafana.qpos.com/dashboard/api
- **Infrastructure**: https://grafana.qpos.com/dashboard/infrastructure
- **Business Metrics**: https://grafana.qpos.com/dashboard/business

---

**Last Updated**: September 2025  
**Version**: 1.0.0  
**Maintainer**: DevOps Team