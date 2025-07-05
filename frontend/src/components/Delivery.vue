<script setup>
import { ref, onMounted } from 'vue'
import Footer from "./Footer.vue"

const delivery = ref({
  title: 'Доставка в інтернет-магазині «BookSeller»',
  description: 'Швидка та надійна доставка книг по всій Україні через службу «Нова Пошта»',
  np_branch_title: 'Нова Пошта (відділення або поштомат)',
  np_branch_desc: 'Отримайте замовлення у найближчому відділенні або поштоматі Нової Пошти',
  np_branch_price: 'згідно з тарифами компанії Нова Пошта',
  np_address_title: 'Нова Пошта (адресна доставка)',
  np_address_desc: 'Доставка безпосередньо за вказаною адресою у зручний для вас час',
  np_address_price: 'згідно з тарифами компанії Нова Пошта',
  howto_title: 'Як оформити замовлення:',
  howto_list: 'Оберіть потрібні книги та додайте їх до кошика\nПерейдіть до оформлення замовлення\nВкажіть спосіб доставки та адресу\nОберіть зручний спосіб оплати',
  terms_title: '⏰ Терміни доставки:',
  terms_list: 'По Україні: 1-3 робочих дні\nКиїв: 1-2 робочих дні\nВіддалені регіони: 2-4 робочих дні',
})

const isLoading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    isLoading.value = true
    const response = await fetch('http://localhost:8000/api/settings/delivery')
    if (!response.ok) throw new Error('Failed to fetch delivery data')
    const data = await response.json()
    if (data.howto_list) data.howto_list = data.howto_list.replace(/\\n/g, '\n')
    if (data.terms_list) data.terms_list = data.terms_list.replace(/\\n/g, '\n')
    delivery.value = data
  } catch (e) {
    error.value = 'Не вдалося завантажити інформацію про доставку. Спробуйте пізніше.'
    console.error('Error fetching delivery data:', e)
  } finally {
    isLoading.value = false
  }
})
</script>

<template>
  <div class="delivery-page">
    <section class="product-spacer">
      <div v-if="isLoading" class="skeleton-loader">
        <div class="delivery-header">
          <div class="skeleton skeleton-title"></div>
          <div class="skeleton skeleton-description"></div>
        </div>
        <div class="delivery-options">
          <div class="delivery-option">
            <div class="skeleton skeleton-option-title"></div>
            <div class="skeleton skeleton-option-desc"></div>
            <div class="skeleton skeleton-price"></div>
          </div>
          <div class="delivery-option">
            <div class="skeleton skeleton-option-title"></div>
            <div class="skeleton skeleton-option-desc"></div>
            <div class="skeleton skeleton-price"></div>
          </div>
        </div>
        <div class="additional-info">
          <div class="info-card">
            <div class="skeleton skeleton-section-title"></div>
            <div class="skeleton skeleton-list-item"></div>
            <div class="skeleton skeleton-list-item"></div>
            <div class="skeleton skeleton-list-item"></div>
          </div>
          <div class="info-card">
            <div class="skeleton skeleton-section-title"></div>
            <div class="skeleton skeleton-list-item"></div>
            <div class="skeleton skeleton-list-item"></div>
            <div class="skeleton skeleton-list-item"></div>
          </div>
        </div>
      </div>

      <div v-else>
        <div class="delivery-header">
          <h1 class="main-title">{{ delivery.title }}</h1>
          <p class="main-description">{{ delivery.description }}</p>
        </div>

        <div class="delivery-options">
          <div class="delivery-option">
            <div class="option-header">
              <div class="option-icon">📦</div>
              <h3>{{ delivery.np_branch_title }}</h3>
            </div>
            <div class="option-content">
              <div class="option-features">
                <!-- <span class="feature-tag">Зручно</span> -->
                <!-- <span class="feature-tag">Економно</span> -->
              </div>
              <p class="option-description">{{ delivery.np_branch_desc }}</p>
              <p class="pricing-info">
                <strong>Вартість:</strong> {{ delivery.np_branch_price }}
              </p>
            </div>
          </div>

          <div class="delivery-option">
            <div class="option-header">
              <div class="option-icon">🏠</div>
              <h3>{{ delivery.np_address_title }}</h3>
            </div>
            <div class="option-content">
              <div class="option-features">
                <!-- <span class="feature-tag">Комфортно</span> -->
                <!-- <span class="feature-tag">До дверей</span> -->
              </div>
              <p class="option-description">{{ delivery.np_address_desc }}</p>
              <p class="pricing-info">
                <strong>Вартість:</strong> {{ delivery.np_address_price }}
              </p>
            </div>
          </div>
        </div>

        <div class="additional-info">
          <div class="info-card">
            <h4>{{ delivery.howto_title }}</h4>
            <ol>
              <li v-for="(item, idx) in delivery.howto_list.split('\n')" :key="idx">{{ item }}</li>
            </ol>
          </div>

          <div class="info-card">
            <h4>{{ delivery.terms_title }}</h4>
            <ul>
              <li v-for="(item, idx) in delivery.terms_list.split('\n')" :key="idx">{{ item }}</li>
            </ul>
          </div>
        </div>

        <div class="contact-info">
          <p>
            <strong>Маєте питання?</strong> Зв'яжіться з нами, і ми з радістю допоможемо!
          </p>
        </div>
      </div>
    </section>
    <Footer />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.product-spacer {
  background-color: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 24px;
  padding: 16px;
  width: 1250px;
  max-width: 1400px; /* Збільшена максимальна ширина для десктопу */
  margin-left: auto;
  margin-right: auto;
}

/* Header Section */
.delivery-header {
  text-align: center;
  margin-bottom: 40px;
}

.main-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 16px;
  background: black;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.main-description {
  font-size: 1.2rem;
  color: #4a5568;
  max-width: 600px;
  margin: 0 auto;
  line-height: 1.6;
}

/* Free Delivery Banner */
.free-delivery-banner {
  display: flex;
  align-items: center;
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
  padding: 24px;
  border-radius: 12px;
  margin-bottom: 40px;
  box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3);
}

.banner-icon {
  font-size: 3rem;
  margin-right: 20px;
}

.banner-content h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 8px;
}

.banner-content p {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* Delivery Options */
.delivery-options {
  display: grid;
  gap: 24px;
  margin-bottom: 40px;
}

.delivery-option {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  background: #fafafa;
}

.option-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.option-icon {
  font-size: 2rem;
  margin-right: 16px;
  padding: 12px;
  background: linear-gradient(135deg, #33bdd5 0%, #088178 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
}

.option-header h3 {
  font-size: 1.4rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.option-features {
  display: flex;
  gap: 8px;
  margin-bottom: 12px;
}

.feature-tag {
  background: #e6fffa;
  color: #00b5ad;
  padding: 4px 12px;
  border-radius: 16px;
  font-size: 0.85rem;
  font-weight: 500;
}

.option-description {
  font-size: 1rem;
  line-height: 1.6;
  color: #4a5568;
  margin-bottom: 12px;
}

.pricing-info {
  font-size: 1rem;
  color: #2d3748;
  padding: 12px;
  background: #f7fafc;
  border-radius: 8px;
  border-left: 4px solid #667eea;
}

/* Additional Info */
.additional-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

.info-card {
  background: #f8f9fa;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.info-card h4 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-card ol,
.info-card ul {
  margin: 0;
  padding-left: 0;      /* Убираем отступ */
  list-style: none;     /* Убираем маркеры (точки, цифры) */
  text-align: left;     /* Выравнивание текста по левому краю */
  display: flex;
  flex-direction: column;
  align-items: flex-start; /* Элементы выровнены по левому краю */
  justify-content: center; /* Центровка по вертикали внутри контейнера (если нужно) */
}


.info-card li {
  margin-bottom: 8px;
  line-height: 1.5;
  color: #4a5568;
}

.contact-info p {
  font-size: 1.1rem;
  color: #000000;
  margin: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.responsive-text {
  font-size: clamp(0.9rem, 2vw, 1rem);
  line-height: 1.6;
}

.section-divider {
  border: 0;
  height: 1px;
  background: linear-gradient(to right, transparent, #e2e8f0, transparent);
  margin: 40px 0;
}

.error-message {
  background: #fefcbf;
  color: #744210;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #ecc94b;
  margin-bottom: 24px;
  text-align: center;
}

/* Skeleton Loader */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 4px;
  margin-bottom: 15px;
}

.skeleton-title {
  width: 60%;
  height: 2.5rem;
  margin: 0 auto;
}

.skeleton-description {
  width: 80%;
  height: 1.2rem;
  margin: 0 auto;
}

.skeleton-option-title {
  width: 50%;
  height: 1.5rem;
}

.skeleton-option-desc {
  width: 100%;
  height: 1rem;
}

.skeleton-price {
  width: 70%;
  height: 1rem;
}

.skeleton-section-title {
  width: 50%;
  height: 1.5rem;
}

.skeleton-list-item {
  width: 100%;
  height: 1rem;
  margin-bottom: 10px;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .product-spacer {
    width: 500px;
  }

  .delivery-option {
    width: 220px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
}

@media (min-width: 380px) and (max-width: 768px) {
  .product-spacer {
    width: 400px;
  }

  .delivery-option {
    width: 350px;
  }

  .info-card {
    width: 350px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
}

@media (min-width: 320px) and (max-width: 380px) {
  .product-spacer {
    width: 300px;
    max-width: 1400px;
  }

  .delivery-option {
    width: 260px;
  }

  .info-card {
    width: 260px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
}
</style>