<template>
  <q-page class="pos-page theme-bg-secondary">
    <div class="pos-container">
      <!-- Header -->
      <div class="pos-header theme-bg-card theme-border-light">
        <div class="pos-header-left">
          <q-btn
            flat
            round
            icon="menu"
            @click="$q.dark.toggle()"
            class="q-mr-sm theme-radius-lg"
          />
          <div class="pos-title">
            <div class="text-h6 theme-text-primary theme-font-lg">Point of Sale</div>
            <div class="text-caption theme-text-secondary theme-font-xs">Kasir #001</div>
          </div>
        </div>
        <div class="pos-header-right">
          <q-btn
            flat
            icon="shopping_cart"
            :label="`${cartItems.length} items`"
            @click="showCart = !showCart"
            class="q-mr-sm theme-radius-lg theme-font-sm"
          />
          <q-btn
            flat
            icon="receipt"
            label="Riwayat"
            @click="showHistory = true"
            class="theme-radius-lg theme-font-sm"
          />
        </div>
      </div>

      <!-- Main Content -->
      <div class="pos-content">
        <!-- Product Search & Categories -->
        <div class="pos-left">
          <!-- Search -->
          <div class="pos-search">
            <q-input
              v-model="searchQuery"
              placeholder="Cari produk..."
              outlined
              dense
              clearable
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
            </q-input>
          </div>

          <!-- Categories -->
          <div class="pos-categories">
            <q-btn
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :color="selectedCategory === category.id ? 'primary' : 'grey-3'"
              :text-color="selectedCategory === category.id ? 'white' : 'dark'"
              @click="selectedCategory = category.id"
              class="q-ma-xs"
              no-caps
            />
          </div>

          <!-- Products Grid -->
          <div class="pos-products">
            <div class="products-grid">
              <q-card
                v-for="product in filteredProducts"
                :key="product.id"
                class="product-card cursor-pointer theme-bg-card theme-border-light theme-radius-lg theme-shadow-medium"
                @click="addToCart(product)"
              >
                <q-img
                  :src="product.image || '/placeholder-product.png'"
                  height="120px"
                  class="product-image theme-radius-lg"
                />
                <q-card-section class="product-info">
                  <div class="text-subtitle2 text-weight-medium theme-text-primary theme-font-sm">{{ product.name }}</div>
                  <div class="text-caption theme-text-secondary theme-font-xs">{{ product.category }}</div>
                  <div class="text-h6 text-primary q-mt-xs theme-text-accent theme-font-md">
                    {{ formatCurrency(product.price) }}
                  </div>
                  <div class="text-caption theme-text-secondary theme-font-xs">
                    Stok: {{ product.stock }} {{ product.unit }}
                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </div>

        <!-- Cart & Checkout -->
        <div class="pos-right theme-bg-secondary theme-border-light" :class="{ 'show-cart': showCart }">
          <q-card class="cart-card theme-bg-card theme-radius-lg">
            <q-card-section class="cart-header">
              <div class="text-h6 theme-text-primary theme-font-lg">Keranjang Belanja</div>
              <q-btn
                flat
                round
                icon="close"
                @click="showCart = false"
                class="mobile-only"
              />
            </q-card-section>

            <q-separator />

            <q-card-section class="cart-items">
              <div v-if="cartItems.length === 0" class="text-center text-caption q-pa-lg">
                <q-icon name="shopping_cart" size="48px" class="q-mb-md theme-text-secondary" />
                <div class="theme-text-secondary theme-font-sm">Keranjang kosong</div>
              </div>

              <div v-else>
                <div
                  v-for="item in cartItems"
                  :key="item.id"
                  class="cart-item q-mb-md theme-bg-card theme-border-light theme-radius-lg"
                >
                  <div class="item-info">
                    <div class="text-subtitle2 theme-text-primary theme-font-sm">{{ item.name }}</div>
                    <div class="text-caption theme-text-secondary theme-font-xs">{{ formatCurrency(item.price) }}</div>
                  </div>
                  <div class="item-controls">
                    <q-btn
                      flat
                      round
                      icon="remove"
                      size="sm"
                      @click="decreaseQuantity(item)"
                    />
                    <span class="quantity">{{ item.quantity }}</span>
                    <q-btn
                      flat
                      round
                      icon="add"
                      size="sm"
                      @click="increaseQuantity(item)"
                    />
                    <q-btn
                      flat
                      round
                      icon="delete"
                      size="sm"
                      color="negative"
                      @click="removeFromCart(item)"
                      class="q-ml-sm"
                    />
                  </div>
                  <div class="item-total theme-text-accent theme-font-sm">
                    {{ formatCurrency(item.price * item.quantity) }}
                  </div>
                </div>
              </div>
            </q-card-section>

            <q-separator />

            <!-- Cart Summary -->
            <q-card-section class="cart-summary theme-bg-card">
              <div class="summary-row">
                <span class="theme-text-secondary theme-font-sm">Subtotal:</span>
                <span class="theme-text-primary theme-font-sm">{{ formatCurrency(subtotal) }}</span>
              </div>
              <div class="summary-row">
                <span class="theme-text-secondary theme-font-sm">Pajak (10%):</span>
                <span class="theme-text-primary theme-font-sm">{{ formatCurrency(tax) }}</span>
              </div>
              <div class="summary-row total theme-border-light">
                <span class="text-weight-bold theme-text-primary theme-font-md">Total:</span>
                <span class="text-weight-bold text-h6 theme-text-accent theme-font-lg">{{ formatCurrency(total) }}</span>
              </div>
            </q-card-section>

            <q-separator />

            <!-- Checkout Actions -->
            <q-card-section class="checkout-actions theme-bg-card">
              <q-btn
                color="negative"
                outline
                label="Clear"
                @click="clearCart"
                :disable="cartItems.length === 0"
                class="q-mb-sm theme-radius-lg theme-font-sm"
                style="width: 100%"
              />
              <q-btn
                color="primary"
                label="Checkout"
                @click="showCheckout = true"
                :disable="cartItems.length === 0"
                class="theme-radius-lg theme-font-sm"
                style="width: 100%"
              />
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Mobile Cart Toggle -->
    <q-btn
      v-if="!showCart"
      fab
      color="primary"
      icon="shopping_cart"
      class="mobile-cart-fab"
      @click="showCart = true"
    >
      <q-badge v-if="cartItems.length > 0" color="red" floating>{{ cartItems.length }}</q-badge>
    </q-btn>

    <!-- Transaction History Dialog -->
    <q-dialog v-model="showHistory">
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Riwayat Transaksi</div>
        </q-card-section>
        <q-card-section>
          <div class="text-center text-caption">
            Fitur riwayat transaksi akan segera hadir
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Tutup" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Checkout Dialog -->
    <q-dialog v-model="showCheckout" persistent>
      <q-card style="min-width: 400px">
        <q-card-section>
          <div class="text-h6">Checkout</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <!-- Customer Selection -->
          <q-select
            v-model="selectedCustomer"
            :options="customers"
            option-label="name"
            option-value="id"
            label="Pilih Customer (Opsional)"
            clearable
            class="q-mb-md"
          >
            <template v-slot:append>
              <q-btn
                flat
                round
                dense
                icon="add"
                @click="showCustomerSelect = true"
              />
            </template>
          </q-select>

          <!-- Payment Method -->
          <q-select
            v-model="selectedPaymentMethod"
            :options="paymentMethods"
            option-label="name"
            option-value="id"
            label="Metode Pembayaran"
            class="q-mb-md"
          />

          <!-- Discount Code -->
           <div class="row q-col-gutter-sm q-mb-md">
             <q-input
               v-model="discountCode"
               label="Kode Diskon (Opsional)"
               class="col"
             />
             <q-btn
               color="primary"
               label="Terapkan"
               @click="applyDiscount"
               :disable="!discountCode"
               style="height: 56px"
             />
           </div>

          <!-- Cash Received (for cash payment) -->
          <q-input
            v-if="selectedPaymentMethod?.type === 'cash'"
            v-model.number="cashReceived"
            type="number"
            label="Uang Diterima"
            prefix="Rp"
            class="q-mb-md"
          />

          <!-- Notes -->
          <q-input
            v-model="notes"
            label="Catatan (Opsional)"
            type="textarea"
            rows="2"
            class="q-mb-md"
          />

          <!-- Order Summary -->
          <div class="q-mb-md">
            <div class="text-subtitle2 q-mb-sm">Ringkasan Pesanan:</div>
            <div v-for="item in cartItems" :key="item.id" class="row justify-between q-mb-xs">
              <span>{{ item.name }} x{{ item.quantity }}</span>
              <span>{{ formatCurrency(item.price * item.quantity) }}</span>
            </div>
            <q-separator class="q-my-sm" />
            <div class="row justify-between">
              <span>Subtotal:</span>
              <span>{{ formatCurrency(subtotal) }}</span>
            </div>
            <div class="row justify-between">
              <span>Pajak:</span>
              <span>{{ formatCurrency(tax) }}</span>
            </div>
            <div v-if="discountAmount > 0" class="row justify-between">
              <span>Diskon:</span>
              <span class="text-negative">-{{ formatCurrency(discountAmount) }}</span>
            </div>
            <q-separator class="q-my-sm" />
            <div class="row justify-between text-h6">
              <span>Total:</span>
              <span>{{ formatCurrency(total) }}</span>
            </div>
            <div v-if="selectedPaymentMethod?.type === 'cash' && cashReceived > 0" class="row justify-between q-mt-sm">
              <span>Kembalian:</span>
              <span>{{ formatCurrency(cashReceived - total) }}</span>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Batal" @click="showCheckout = false" />
          <q-btn
            color="primary"
            label="Proses Pembayaran"
            @click="checkout"
            :disable="!selectedPaymentMethod || (selectedPaymentMethod?.type === 'cash' && cashReceived < total)"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Customer Selection Dialog -->
    <q-dialog v-model="showCustomerSelect">
      <q-card style="min-width: 300px">
        <q-card-section>
          <div class="text-h6">Pilih Customer</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <q-list>
            <q-item
              v-for="customer in customers"
              :key="customer.id"
              clickable
              @click="selectedCustomer = customer; showCustomerSelect = false"
            >
              <q-item-section>
                <q-item-label>{{ customer.name }}</q-item-label>
                <q-item-label caption>{{ customer.phone || customer.email }}</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Tutup" @click="showCustomerSelect = false" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useQuasar } from 'quasar'
import { posService } from 'src/services'

const $q = useQuasar()

// Reactive data
const loading = ref(false)
const searchQuery = ref('')
const selectedCategory = ref(null)
const cartItems = ref([])
const showCart = ref(false)
const showHistory = ref(false)
const showCheckout = ref(false)
const showCustomerSelect = ref(false)

// Data
const categories = ref([])
const products = ref([])
const customers = ref([])
const paymentMethods = ref([])
const transactions = ref([])

// Selected data
const selectedCustomer = ref(null)
const selectedPaymentMethod = ref(null)
const discountCode = ref('')
const discountAmount = ref(0)
const notes = ref('')
const cashReceived = ref(0)

// Computed properties
const filteredProducts = computed(() => {
  let filtered = products.value

  // Filter by category
  if (selectedCategory.value) {
    filtered = filtered.filter(product => product.categoryId === selectedCategory.value)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(query) ||
      product.category.toLowerCase().includes(query)
    )
  }

  return filtered
})

const subtotal = computed(() => {
  return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const tax = computed(() => {
  return subtotal.value * 0.1
})

const total = computed(() => {
  return subtotal.value + tax.value - discountAmount.value
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

// Load data functions
const loadProducts = async () => {
  try {
    loading.value = true
    const params = {
      search: searchQuery.value,
      category_id: selectedCategory.value
    }
    const response = await posService.getProducts(params)
    products.value = response.data || []
  } catch (error) {
    console.error('Error loading products:', error)
    $q.notify({
      type: 'negative',
      message: 'Gagal memuat produk',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await posService.getCategories()
    categories.value = [
      { id: null, name: 'Semua' },
      ...(response.data || [])
    ]
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const loadCustomers = async () => {
  try {
    const response = await posService.getCustomers()
    customers.value = response.data || []
  } catch (error) {
    console.error('Error loading customers:', error)
  }
}

const loadPaymentMethods = async () => {
  try {
    const response = await posService.getPaymentMethods()
    paymentMethods.value = response.data || []
  } catch (error) {
    console.error('Error loading payment methods:', error)
  }
}

const loadTransactions = async () => {
  try {
    const response = await posService.getTransactions()
    transactions.value = response.data || []
  } catch (error) {
    console.error('Error loading transactions:', error)
  }
}

const addToCart = async (product) => {
  try {
    // Check stock first
    const stockCheck = await posService.checkStock(product.id, 1)
    if (!stockCheck.available) {
      $q.notify({
        type: 'negative',
        message: 'Stok tidak mencukupi',
        position: 'top'
      })
      return
    }

    const existingItem = cartItems.value.find(item => item.id === product.id)
    
    if (existingItem) {
      existingItem.quantity++
    } else {
      cartItems.value.push({
        ...product,
        quantity: 1
      })
    }
    
    $q.notify({
      type: 'positive',
      message: `${product.name} ditambahkan ke keranjang`,
      position: 'top'
    })
  } catch (error) {
    console.error('Error adding to cart:', error)
    $q.notify({
      type: 'negative',
      message: 'Gagal menambahkan ke keranjang',
      position: 'top'
    })
  }
}

const increaseQuantity = (item) => {
  item.quantity++
}

const decreaseQuantity = (item) => {
  if (item.quantity > 1) {
    item.quantity--
  } else {
    removeFromCart(item)
  }
}

const removeFromCart = (item) => {
  const index = cartItems.value.findIndex(cartItem => cartItem.id === item.id)
  if (index > -1) {
    cartItems.value.splice(index, 1)
  }
}

const clearCart = () => {
  $q.dialog({
    title: 'Konfirmasi',
    message: 'Apakah Anda yakin ingin mengosongkan keranjang?',
    cancel: true,
    persistent: true
  }).onOk(() => {
    cartItems.value = []
    $q.notify({
      type: 'info',
      message: 'Keranjang dikosongkan',
      position: 'top'
    })
  })
}

const applyDiscount = async () => {
  if (!discountCode.value) {
    discountAmount.value = 0
    return
  }

  try {
    const response = await posService.applyDiscount(discountCode.value, {
      items: cartItems.value,
      subtotal: subtotal.value
    })
    
    if (response.success) {
      discountAmount.value = response.discount_amount || 0
      $q.notify({
        type: 'positive',
        message: `Diskon ${formatCurrency(discountAmount.value)} berhasil diterapkan`,
        position: 'top'
      })
    } else {
      discountAmount.value = 0
      $q.notify({
        type: 'negative',
        message: response.message || 'Kode diskon tidak valid',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error applying discount:', error)
    discountAmount.value = 0
    $q.notify({
      type: 'negative',
      message: 'Gagal menerapkan diskon',
      position: 'top'
    })
  }
}

const checkout = async () => {
  try {
    const transactionData = {
      customer_id: selectedCustomer.value?.id,
      payment_method_id: selectedPaymentMethod.value?.id,
      items: cartItems.value.map(item => ({
        product_id: item.id,
        quantity: item.quantity,
        price: item.price
      })),
      subtotal: subtotal.value,
      tax: tax.value,
      total: total.value,
      discount_code: discountCode.value,
      discount_amount: discountAmount.value,
      notes: notes.value,
      cash_received: cashReceived.value
    }

    $q.loading.show({
      message: 'Memproses transaksi...'
    })

    const response = await posService.createTransaction(transactionData)
    
    if (response.success) {
      cartItems.value = []
      selectedCustomer.value = null
      selectedPaymentMethod.value = null
      discountCode.value = ''
      discountAmount.value = 0
      notes.value = ''
      cashReceived.value = 0
      showCheckout.value = false
      
      $q.notify({
        type: 'positive',
        message: 'Transaksi berhasil diproses!',
        position: 'top'
      })
      
      // Reload transactions
      await loadTransactions()
    }
  } catch (error) {
    console.error('Error processing checkout:', error)
    $q.notify({
      type: 'negative',
      message: 'Gagal memproses transaksi',
      position: 'top'
    })
  } finally {
    $q.loading.hide()
  }
}

// Watchers
watch(searchQuery, () => {
  loadProducts()
})

watch(selectedCategory, () => {
  loadProducts()
})

// Lifecycle
onMounted(async () => {
  // Auto show cart on desktop
  if ($q.screen.gt.sm) {
    showCart.value = true
  }
  
  // Load initial data
  await Promise.all([
    loadCategories(),
    loadProducts(),
    loadCustomers(),
    loadPaymentMethods(),
    loadTransactions()
  ])
})
</script>

<style scoped>
.pos-page {
  height: 100vh;
  overflow: hidden;
  background-color: var(--theme-bg-secondary);
  transition: var(--theme-transition-all);
}

.pos-container {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.pos-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--theme-spacing-md);
  background-color: var(--theme-bg-card);
  border-bottom: var(--theme-border-light);
  z-index: 10;
  transition: var(--theme-transition-all);
}

.pos-header-left {
  display: flex;
  align-items: center;
}

.pos-title {
  margin-left: 8px;
}

.pos-header-right {
  display: flex;
  align-items: center;
}

.pos-content {
  flex: 1;
  display: flex;
  overflow: hidden;
}

.pos-left {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: var(--theme-spacing-md);
  overflow: hidden;
}

.pos-search {
  margin-bottom: var(--theme-spacing-md);
}

.pos-categories {
  margin-bottom: var(--theme-spacing-md);
  display: flex;
  flex-wrap: wrap;
}

.pos-products {
  flex: 1;
  overflow-y: auto;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: var(--theme-spacing-md);
}

.product-card {
  transition: var(--theme-transition-all);
  background-color: var(--theme-bg-card);
  border: var(--theme-border-light);
}

.product-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--theme-shadow-medium);
}

.product-image {
  background-color: var(--theme-bg-secondary);
}

.product-info {
  padding: var(--theme-spacing-sm);
}

.pos-right {
  width: 400px;
  background-color: var(--theme-bg-secondary);
  border-left: var(--theme-border-light);
  transition: var(--theme-transition-all);
}

.cart-card {
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: var(--theme-bg-card);
  transition: var(--theme-transition-all);
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--theme-spacing-md);
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: var(--theme-spacing-md);
}

.cart-item {
  display: flex;
  align-items: center;
  padding: var(--theme-spacing-sm);
  background-color: var(--theme-bg-card);
  border-radius: var(--theme-radius-lg);
  box-shadow: var(--theme-shadow-light);
  border: var(--theme-border-light);
  transition: var(--theme-transition-all);
}

.item-info {
  flex: 1;
}

.item-controls {
  display: flex;
  align-items: center;
  margin: 0 var(--theme-spacing-sm);
}

.quantity {
  margin: 0 var(--theme-spacing-xs);
  min-width: 24px;
  text-align: center;
  font-weight: 500;
  color: var(--theme-text-primary);
}

.item-total {
  font-weight: 500;
  min-width: 80px;
  text-align: right;
  color: var(--theme-text-accent);
}

.cart-summary {
  padding: var(--theme-spacing-md);
  background-color: var(--theme-bg-card);
  transition: var(--theme-transition-all);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: var(--theme-spacing-xs);
}

.summary-row.total {
  margin-top: var(--theme-spacing-xs);
  padding-top: var(--theme-spacing-xs);
  border-top: var(--theme-border-light);
  transition: var(--theme-transition-all);
}

.checkout-actions {
  padding: var(--theme-spacing-md);
  background-color: var(--theme-bg-card);
  transition: var(--theme-transition-all);
}

.mobile-cart-fab {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 100;
}

/* Mobile Styles */
@media (max-width: 768px) {
  .pos-content {
    position: relative;
  }
  
  .pos-right {
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 100%;
    z-index: 20;
    transform: translateX(100%);
  }
  
  .pos-right.show-cart {
    transform: translateX(0);
  }
  
  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }
  
  .mobile-only {
    display: block;
  }
}

@media (min-width: 769px) {
  .mobile-only {
    display: none;
  }
  
  .mobile-cart-fab {
    display: none;
  }
}

/* Theme transitions for all elements */
* {
  transition: var(--theme-transition-all);
}
</style>