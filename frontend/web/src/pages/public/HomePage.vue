<template>
  <q-page class="home-page theme-bg-primary q-pb-xl">
    <!-- Hero Section -->
    <section class="hero-section theme-bg-gradient">
      <div class="container">
        <div class="row items-center q-col-gutter-xl">
          <div class="col-12 col-md-6">
            <div class="hero-content">
              <h1 class="hero-title theme-text-white theme-font-xxxl">
                Solusi POS Terdepan untuk
                <span class="theme-text-accent">Bisnis Modern</span>
              </h1>
              <p class="hero-subtitle theme-text-white theme-font-lg">
                Q-POS adalah sistem Point of Sale yang dirancang khusus untuk membantu bisnis Anda 
                mengelola penjualan, inventori, dan laporan dengan mudah dan efisien.
              </p>
              <div class="hero-actions q-mt-lg">
                <q-btn
                  color="primary"
                  size="lg"
                  label="Mulai Sekarang"
                  icon="play_arrow"
                  @click="goToLogin"
                  class="q-mr-md theme-radius-lg theme-font-md"
                />
                <q-btn
                  color="grey-7"
                  size="lg"
                  label="Pelajari Lebih Lanjut"
                  icon="info"
                  outline
                  @click="scrollToFeatures"
                  class="theme-radius-lg theme-font-md"
                />
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="hero-image">
              <q-img
                :src="onlinePaymentsImg"
                alt="Online Payments"
                class="rounded-borders shadow-10"
                style="max-width: 100%; height: auto;"
              />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section q-py-xl theme-bg-secondary">
      <div class="container">
        <div class="text-center q-mb-xl">
          <h2 class="section-title theme-text-primary theme-font-xxl">Fitur Unggulan</h2>
          <p class="section-subtitle theme-text-secondary theme-font-lg">
            Dilengkapi dengan fitur-fitur canggih untuk mendukung operasional bisnis Anda
          </p>
        </div>
        
        <div class="row q-col-gutter-lg">
          <div 
            v-for="feature in features" 
            :key="feature.id"
            class="col-12 col-sm-6 col-md-4"
          >
            <q-card class="feature-card full-height theme-bg-card theme-border-light theme-radius-lg">
              <q-card-section class="text-center">
                <q-icon 
                  :name="feature.icon" 
                  size="3rem" 
                  :color="feature.color"
                  class="q-mb-md"
                />
                <h3 class="feature-title theme-text-primary theme-font-xl">{{ feature.title }}</h3>
                <p class="feature-description theme-text-secondary theme-font-md">{{ feature.description }}</p>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section q-py-xl theme-bg-card">
      <div class="container">
        <div class="row items-center q-col-gutter-xl">
          <div class="col-12 col-md-6">
            <div class="benefits-image">
              <q-img
                :src="mobilePaymentsImg"
                alt="Mobile Payments"
                class="rounded-borders theme-radius-lg"
              />
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="benefits-content">
              <h2 class="section-title theme-text-primary theme-font-xxl">Mengapa Memilih Q-POS?</h2>
              <div class="benefits-list q-mt-lg">
                <div 
                  v-for="benefit in benefits" 
                  :key="benefit.id"
                  class="benefit-item q-mb-md"
                >
                  <q-icon 
                    name="check_circle" 
                    color="positive" 
                    size="1.5rem"
                    class="q-mr-md"
                  />
                  <div>
                    <h4 class="benefit-title theme-text-primary theme-font-lg">{{ benefit.title }}</h4>
                    <p class="benefit-description theme-text-secondary theme-font-md">{{ benefit.description }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section q-py-xl theme-bg-primary">
      <div class="container text-center">
        <h2 class="cta-title theme-text-white theme-font-xxl">Siap Meningkatkan Bisnis Anda?</h2>
        <p class="cta-subtitle q-mt-md theme-text-white theme-font-lg">
          Bergabunglah dengan ribuan bisnis yang telah mempercayai Q-POS untuk mengelola operasional mereka
        </p>
        <q-btn
          color="white"
          text-color="primary"
          size="lg"
          label="Mulai Gratis Sekarang"
          icon="rocket_launch"
          @click="goToLogin"
          class="q-mt-lg theme-radius-lg theme-font-md"
        />
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import onlinePaymentsImg from 'src/assets/undraw_online-payments.svg'
import mobilePaymentsImg from 'src/assets/undraw_mobile-payments.svg'

const router = useRouter()

// Features data
const features = ref([
  {
    id: 1,
    icon: 'point_of_sale',
    color: 'primary',
    title: 'Point of Sale',
    description: 'Sistem kasir yang cepat dan mudah digunakan dengan interface yang intuitif'
  },
  {
    id: 2,
    icon: 'inventory_2',
    color: 'secondary',
    title: 'Manajemen Inventori',
    description: 'Kelola stok produk dengan sistem tracking yang akurat dan real-time'
  },
  {
    id: 3,
    icon: 'analytics',
    color: 'accent',
    title: 'Laporan & Analitik',
    description: 'Dashboard analitik lengkap untuk memantau performa bisnis Anda'
  },
  {
    id: 4,
    icon: 'people',
    color: 'positive',
    title: 'Manajemen Pelanggan',
    description: 'Database pelanggan terintegrasi dengan sistem loyalty dan promosi'
  },
  {
    id: 5,
    icon: 'local_shipping',
    color: 'warning',
    title: 'Manajemen Supplier',
    description: 'Kelola hubungan dengan supplier dan proses pembelian dengan efisien'
  },
  {
    id: 6,
    icon: 'security',
    color: 'info',
    title: 'Keamanan Data',
    description: 'Sistem keamanan berlapis untuk melindungi data bisnis Anda'
  }
])

// Benefits data
const benefits = ref([
  {
    id: 1,
    title: 'Mudah Digunakan',
    description: 'Interface yang intuitif dan user-friendly, tidak memerlukan training khusus'
  },
  {
    id: 2,
    title: 'Hemat Biaya',
    description: 'Solusi yang cost-effective dengan ROI yang tinggi untuk bisnis Anda'
  },
  {
    id: 3,
    title: 'Support 24/7',
    description: 'Tim support yang siap membantu Anda kapan saja dibutuhkan'
  },
  {
    id: 4,
    title: 'Update Berkala',
    description: 'Fitur dan keamanan yang selalu up-to-date dengan perkembangan teknologi'
  }
])

// Methods
const goToLogin = () => {
  router.push({ name: 'login' })
}

const scrollToFeatures = () => {
  const element = document.getElementById('features')
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' })
  }
}
</script>

<style lang="scss" scoped>
.home-page {
  min-height: 100vh;
  background-color: var(--theme-bg-primary);
  transition: var(--theme-transition-all);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 var(--theme-spacing-lg);
}

// Hero Section
.hero-section {
  padding: var(--theme-spacing-xxxl) 0;
  background: var(--theme-gradient-primary);
  color: var(--theme-text-white);
  transition: var(--theme-transition-all);
  
  .hero-title {
    font-size: var(--theme-font-xxxl);
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: var(--theme-spacing-lg);
    color: var(--theme-text-white);
    
    @media (max-width: 768px) {
      font-size: var(--theme-font-xxl);
    }
    
    span {
      color: var(--theme-accent);
    }
  }
  
  .hero-subtitle {
    font-size: var(--theme-font-lg);
    line-height: 1.6;
    opacity: 0.9;
    margin-bottom: var(--theme-spacing-xl);
    color: var(--theme-text-white);
  }
  
  .hero-image {
    text-align: center;
    
    :deep(.q-img) {
      border-radius: var(--theme-radius-lg);
      box-shadow: var(--theme-shadow-large);
    }
  }
  
  :deep(.q-btn) {
    border-radius: var(--theme-radius-lg);
    font-size: var(--theme-font-md);
    transition: var(--theme-transition-all);
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: var(--theme-shadow-medium);
    }
  }
}

// Features Section
.features-section {
  padding: var(--theme-spacing-xxxl) 0;
  background-color: var(--theme-bg-secondary);
  transition: var(--theme-transition-all);
  
  .section-title {
    font-size: var(--theme-font-xxl);
    font-weight: 600;
    color: var(--theme-text-primary);
    margin-bottom: var(--theme-spacing-lg);
  }
  
  .section-subtitle {
    font-size: var(--theme-font-lg);
    color: var(--theme-text-secondary);
    max-width: 600px;
    margin: 0 auto;
  }
  
  .feature-card {
    transition: var(--theme-transition-all);
    border: var(--theme-border-light);
    background-color: var(--theme-bg-card);
    border-radius: var(--theme-radius-lg);
    
    &:hover {
      transform: translateY(-5px);
      box-shadow: var(--theme-shadow-large);
    }
    
    .feature-title {
      font-size: var(--theme-font-xl);
      font-weight: 600;
      color: var(--theme-text-primary);
      margin-bottom: var(--theme-spacing-sm);
    }
    
    .feature-description {
      color: var(--theme-text-secondary);
      line-height: 1.6;
      font-size: var(--theme-font-md);
    }
  }
}

// Benefits Section
.benefits-section {
  padding: var(--theme-spacing-xxxl) 0;
  background-color: var(--theme-bg-card);
  transition: var(--theme-transition-all);
  
  .benefits-image {
    text-align: center;
    
    :deep(.q-img) {
      border-radius: var(--theme-radius-lg);
      box-shadow: var(--theme-shadow-medium);
    }
  }
  
  .section-title {
    font-size: var(--theme-font-xxl);
    color: var(--theme-text-primary);
  }
  
  .benefit-item {
    display: flex;
    align-items: flex-start;
    padding: var(--theme-spacing-sm) 0;
    
    .benefit-title {
      font-size: var(--theme-font-lg);
      font-weight: 600;
      color: var(--theme-text-primary);
      margin-bottom: var(--theme-spacing-xs);
    }
    
    .benefit-description {
      color: var(--theme-text-secondary);
      line-height: 1.5;
      font-size: var(--theme-font-md);
    }
  }
}

// CTA Section
.cta-section {
  padding: var(--theme-spacing-xxxl) 0;
  background-color: var(--theme-bg-primary);
  transition: var(--theme-transition-all);
  
  .cta-title {
    font-size: var(--theme-font-xxl);
    font-weight: 600;
    margin-bottom: var(--theme-spacing-lg);
    color: var(--theme-text-white);
    
    @media (max-width: 768px) {
      font-size: var(--theme-font-xl);
    }
  }
  
  .cta-subtitle {
    font-size: var(--theme-font-lg);
    opacity: 0.9;
    max-width: 600px;
    margin: 0 auto;
    color: var(--theme-text-white);
  }
  
  :deep(.q-btn) {
    border-radius: var(--theme-radius-lg);
    font-size: var(--theme-font-md);
    transition: var(--theme-transition-all);
    
    &:hover {
      transform: translateY(-2px);
      box-shadow: var(--theme-shadow-medium);
    }
  }
}

// Responsive adjustments
@media (max-width: 768px) {
  .hero-section,
  .features-section,
  .benefits-section,
  .cta-section {
    padding: 60px 0;
  }
  
  .container {
    padding: 0 15px;
  }
}
</style>