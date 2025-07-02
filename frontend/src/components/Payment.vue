<script setup>
import { ref, onMounted } from 'vue'
import Footer from "./Footer.vue"

const payment = ref({})
const isLoadingPayment = ref(true) // Состояние загрузки данных об оплате

onMounted(async () => {
  isLoadingPayment.value = true
  try {
    const response = await fetch('http://localhost:8000/api/settings/payment')
    const data = await response.json()
    payment.value = data
  } catch (e) {
    // Fallback: дефолтные значения в случае ошибки
    payment.value = {
      title: 'Оплата в інтернет-магазині',
      np_title: 'Нова Пошта (відділення або поштомат)',
      np_card: 'Оплата платіжною карткою Visa / Mastercard (Без комісії)',
      np_link: 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек',
      np_bank: 'Безготівковий переказ (за IBAN) згідно рахунку.',
      np_bank_note: 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.',
      np_cod: 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми',
      np_cod_note: 'Зверніть увагу, що доставка при накладеному платежі є платною!',
      np_cod_important: 'Для оплати при отриманні потрібно внести 20% від суми',
      np_address_title: 'Нова Пошта (адресна доставка)',
      np_address_card: 'Оплата платіжною карткою Visa / Mastercard (Без комісії)',
      np_address_link: 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек',
      np_address_bank: 'Безготівковий переказ (за IBAN) згідно рахунку.',
      np_address_bank_note: 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.',
      np_address_cod: 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми',
      np_address_cod_note: 'Зверніть увагу, що доставка при накладеному платежі є платною!',
      np_address_cod_important: 'Для оплати при отриманні потрібно внести 20% від суми',
    }
  } finally {
    isLoadingPayment.value = false
  }
})
</script>

<template>
  <div>
    <div class="delivery-page">
      <section class="product-spacer">
        <div class="delivery-header">
          <h1 class="main-title" v-if="!isLoadingPayment">{{ payment.title }}</h1>
          <div v-else class="skeleton" style="width: 60%; height: 36px; margin-bottom: 20px;"></div>
        </div>

        <div class="delivery-options">
          <div class="delivery-option">
            <div class="option-header">
              <div class="option-icon">📦</div>
              <h3 v-if="!isLoadingPayment">{{ payment.np_title }}</h3>
              <div v-else class="skeleton" style="width: 40%; height: 24px;"></div>
            </div>
            <div class="option-content">
              <div class="option-features"></div>
              <h4 v-if="!isLoadingPayment">Оплата карткою</h4>
              <div v-else class="skeleton" style="width: 30%; height: 20px; margin-bottom: 10px;"></div>
              <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_card }}</p>
              <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
              <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_link }}</p>
              <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
              <h4 v-if="!isLoadingPayment">Банківський переказ</h4>
              <div v-else class="skeleton" style="width: 30%; height: 20px; margin-bottom: 10px;"></div>
              <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_bank }}</p>
              <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
              <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_bank_note }}</p>
              <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
              <h4 v-if="!isLoadingPayment">Оплата при отриманні</h4>
              <div v-else class="skeleton" style="width: 30%; height: 20px; margin-bottom: 10px;"></div>
              <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_cod }}</p>
              <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
              <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_cod_note }}</p>
              <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
              <p class="pricing-info" v-if="!isLoadingPayment">
                <strong>{{ payment.np_cod_important }}</strong>
              </p>
              <div v-else class="skeleton" style="width: 60%; height: 16px;"></div>
            </div>
          </div>

          <div class="delivery-options">
            <div class="delivery-option">
              <div class="option-header">
                <div class="option-icon">📦</div>
                <h3 v-if="!isLoadingPayment">{{ payment.np_address_title }}</h3>
                <div v-else class="skeleton" style="width: 40%; height: 24px;"></div>
              </div>
              <div class="option-content">
                <div class="option-features"></div>
                <h4 v-if="!isLoadingPayment">Оплата карткою</h4>
                <div v-else class="skeleton" style="width: 30%; height: 20px; margin-bottom: 10px;"></div>
                <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_address_card }}</p>
                <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
                <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_address_link }}</p>
                <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
                <h4 v-if="!isLoadingPayment">Банківський переказ</h4>
                <div v-else class="skeleton" style="width: 30%; height: 20px; margin-bottom: 10px;"></div>
                <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_address_bank }}</p>
                <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
                <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_address_bank_note }}</p>
                <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
                <h4 v-if="!isLoadingPayment">Оплата при отриманні</h4>
                <div v-else class="skeleton" style="width: 30%; height: 20px; margin-bottom: 10px;"></div>
                <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_address_cod }}</p>
                <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
                <p class="option-description" v-if="!isLoadingPayment">{{ payment.np_address_cod_note }}</p>
                <div v-else class="skeleton" style="width: 80%; height: 16px; margin-bottom: 10px;"></div>
                <p class="pricing-info" v-if="!isLoadingPayment">
                  <strong>{{ payment.np_address_cod_important }}</strong>
                </p>
                <div v-else class="skeleton" style="width: 60%; height: 16px;"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="contact-info">
          <p v-if="!isLoadingPayment">
            <strong>Маєте питання?</strong> Зв'яжіться з нами, і ми з радістю допоможемо!
          </p>
          <p v-else>
            <div class="skeleton" style="width: 60%; height: 16px; margin-bottom: 10px;"></div>
            <div class="skeleton" style="width: 20%; height: 16px;"></div>
          </p>
        </div>
      </section>
    </div>
    <Footer />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.product-spacer {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  padding: 20px;
  font-family: 'Inter', sans-serif;
  color: #2d3748;
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
  margin-bottom: 5px;
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
  margin-bottom: 20px;
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
  margin-bottom: 20px;
}

.delivery-option {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  background: #fafafa;
}

.delivery-option:hover {
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
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
  padding-left: 20px;
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