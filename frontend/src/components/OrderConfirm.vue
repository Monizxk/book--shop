<template>
  <div class="order-confirm-container">
    <div class="success-header">
      <div class="success-icon">
        <i class="fas fa-check-circle"></i>
      </div>
      <h1 class="success-title">Замовлення успішно оформлено!</h1>
      <p class="success-subtitle">Дякуємо за ваше замовлення. Ми зв'яжемося з вами найближчим часом.</p>
    </div>

    <div class="order-details-grid">
      <!-- Інформація про замовлення -->
      <div class="order-info-section">
        <div class="info-card">
          <h2>Деталі замовлення</h2>
          <div class="order-number">
            <strong>Номер замовлення: {{ orderNumber }}</strong>
          </div>
          <div class="order-date">
            <strong>Дата замовлення:</strong> {{ formattedOrderDate }}
          </div>
          <div class="order-status">
            <strong>Статус:</strong>
            <span class="status-badge">Очікує обробки</span>
          </div>
        </div>

        <!-- Особисті дані -->
        <div class="info-card">
          <h3>Особисті дані</h3>
          <div class="info-row">
            <span class="info-label">ПІБ:</span>
            <span class="info-value">{{ customerInfo.fullName }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ customerInfo.email }}</span>
          </div>
          <div class="info-row">
            <span class="info-label">Телефон:</span>
            <span class="info-value">{{ customerInfo.phone }}</span>
          </div>
        </div>

        <!-- Доставка -->
        <div class="info-card">
          <h3>Доставка</h3>
          <div class="info-row">
            <span class="info-label">Спосіб доставки:</span>
            <span class="info-value">{{ getDeliveryMethodName(customerInfo.deliveryMethod) }}</span>
          </div>
          <div v-if="customerInfo.deliveryMethod !== 'selfPickup'" class="info-row">
            <span class="info-label">Місто:</span>
            <span class="info-value">{{ customerInfo.city }}</span>
          </div>
          <div v-if="customerInfo.deliveryMethod !== 'selfPickup'" class="info-row">
            <span class="info-label">Відділення:</span>
            <span class="info-value">{{ customerInfo.postOffice }}</span>
          </div>
          <div v-if="customerInfo.deliveryMethod === 'selfPickup'" class="info-row">
            <span class="info-label">Адреса самовивозу:</span>
            <span class="info-value">м. Київ, вул. Хрещатик, 1</span>
          </div>
        </div>

        <!-- Оплата -->
        <div class="info-card">
          <h3>Оплата</h3>
          <div class="info-row">
            <span class="info-label">Спосіб оплати:</span>
            <span class="info-value">{{ getPaymentMethodName(customerInfo.paymentMethod) }}</span>
          </div>
        </div>

        <!-- Коментар -->
        <div v-if="customerInfo.comment" class="info-card">
          <h3>Коментар до замовлення</h3>
          <p class="comment-text">{{ customerInfo.comment }}</p>
        </div>
      </div>

      <!-- Товари в замовленні -->
      <div class="order-items-section">
        <div class="items-card">
          <h2>Товари в замовленні</h2>

          <div class="order-items">
            <div v-for="item in orderItems" :key="item.id" class="order-item">
              <div class="item-image">
                <img :src="getImageUrl(item.images?.[0])" :alt="item.title" />
              </div>
              <div class="item-details">
                <div class="item-title">{{ item.title }}</div>
                <div class="item-price">{{ item.price }} ₴</div>
                <div class="item-quantity">Кількість: {{ item.quantity }}</div>
              </div>
              <div class="item-total">
                {{ item.price * item.quantity }} ₴
              </div>
            </div>
          </div>

          <div class="order-summary">
            <div class="summary-row">
              <span>Товари:</span>
              <span>{{ subtotal }} ₴</span>
            </div>
            <div class="summary-row">
              <span>Доставка:</span>
              <span>{{ deliveryCost }} ₴</span>
            </div>
            <div class="summary-row total-row">
              <span>Разом до сплати:</span>
              <span>{{ total }} ₴</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Дії -->
    <div class="order-actions">
      <button @click="printOrder" class="action-btn secondary-btn">
        <i class="fas fa-print"></i>
        Роздрукувати замовлення
      </button>
      <router-link to="/" class="action-btn primary-btn">
        <i class="fas fa-home"></i>
        Повернутися до магазину
      </router-link>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from "axios";

export default {
  name: 'OrderConfirm',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const lastOrder = ref(null)
    const settings = ref({});
    const deliveryCostFromAPI = ref(60)

    // Дані замовлення (можуть передаватися через route params або localStorage)
    const orderNumber = ref('')
    const orderDate = ref(new Date())
    const customerInfo = ref({
      fullName: '',
      orderNumber: '',
      email: '',
      phone: '',
      deliveryMethod: '',
      city: '',
      postOffice: '',
      paymentMethod: '',
      comment: ''
    })
    const orderItems = ref([])

    // Обчислювані властивості
    const formattedOrderDate = computed(() => {
      return orderDate.value.toLocaleDateString('uk-UA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    })

    const subtotal = computed(() => {
      return orderItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    })

// Option 1: Using fetch (if you prefer to remove axios dependency)
    const loadDeliveryCost = async () => {
      try {
        const response = await fetch('http://localhost:8000/api/settings/delivery-cost')

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        const data = await response.json() // This works with fetch
        console.log('Response data:', data)

        deliveryCostFromAPI.value = data.delivery_cost

        console.log('Delivery cost loaded:', deliveryCostFromAPI.value)
      } catch (error) {
        console.error('Error loading delivery cost:', error)
        // Keep default value (60)
      }
    }

    const deliveryCost = computed(() => {
      if (customerInfo.value.deliveryMethod === 'selfPickup') {
        return 0
      }
      return deliveryCostFromAPI.value
    })

    const total = computed(() => {
      return subtotal.value + deliveryCost.value
    })

    // Методи
    const getDeliveryMethodName = (method) => {
      const methods = {
        'novaPoshta': 'Нова Пошта',
        'ukrPoshta': 'Укрпошта',
        'selfPickup': 'Самовивіз'
      }
      return methods[method] || method
    }

    const getPaymentMethodName = (method) => {
      const methods = {
        'cashOnDelivery': 'Накладений платіж',
        'cardOnline': 'Оплата картою онлайн'
      }
      return methods[method] || method
    }

    const getImageUrl = (imagePath) => {
      if (!imagePath) return '/placeholder-image.jpg'
      return `http://localhost:8000/storage/${imagePath}`
    }

    // const generateOrderNumber = () => {
    //   const timestamp = Date.now().toString().slice(-6)
    //   const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
    //   return `${timestamp}${random}`
    // }

    const printOrder = () => {
      window.print()
    }

    const loadOrderData = () => {
      let orderData = null

      const savedOrderData = localStorage.getItem('lastOrderData')

      if (savedOrderData) {
        orderData = JSON.parse(savedOrderData)
        customerInfo.value = orderData.customerInfo || {}
        orderItems.value = orderData.orderItems || []
        orderDate.value = new Date(orderData.orderDate || Date.now())

        localStorage.removeItem('lastOrderData')
      } else if (route.query.orderData) {
        try {
          orderData = JSON.parse(decodeURIComponent(route.query.orderData))
          customerInfo.value = orderData.customerInfo || {}
          orderItems.value = orderData.orderItems || []
          orderDate.value = new Date(orderData.orderDate || Date.now())
        } catch (error) {
          console.error('Помилка при парсингу даних замовлення:', error)
        }
      } else {
        alert('Дані замовлення не знайдено')
        router.push('/')
        return
      }

      orderNumber.value = orderData.orderNumber || 'Без номера'
    }

    onMounted(async () => {
      const res = await axios.get('http://localhost:8000/api/settings');
      settings.value = res.data;
    });


    onMounted(() => {
      loadOrderData()
      loadDeliveryCost()

      const event = new CustomEvent("hideCategoryTree")
      document.dispatchEvent(event)

      const savedOrder = localStorage.getItem('lastOrderData')
      if (savedOrder) {
        lastOrder.value = JSON.parse(savedOrder)
      }

    })

    onUnmounted(() => {
      const event = new CustomEvent("showCategoryTree")
      document.dispatchEvent(event)
    })

    return {
      orderNumber,
      formattedOrderDate,
      customerInfo,
      orderItems,
      subtotal,
      deliveryCost,
      total,
      getDeliveryMethodName,
      getPaymentMethodName,
      getImageUrl,
      printOrder,
      lastOrder
    }
  }
}
</script>

<style scoped>
.order-confirm-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px;
}

.success-header {
  text-align: center;
  margin-bottom: 40px;
  padding: 40px 20px;
  background: linear-gradient(135deg, #4caf50, #45a049);
  border-radius: 12px;
  color: white;
}

.success-icon {
  font-size: 4rem;
  margin-bottom: 16px;
}

.success-title {
  font-size: 2rem;
  margin-bottom: 8px;
  font-weight: 600;
}

.success-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
}

.order-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 32px;
  margin-bottom: 40px;
}

.info-card, .items-card {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.info-card h2, .items-card h2 {
  font-size: 1.4rem;
  margin-bottom: 20px;
  color: #333;
  border-bottom: 2px solid #4caf50;
  padding-bottom: 8px;
}

.info-card h3 {
  font-size: 1.2rem;
  margin-bottom: 16px;
  color: #333;
}

.order-number {
  background: #e8f5e9;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 12px;
  font-size: 1.1rem;
}

.order-date, .order-status {
  margin-bottom: 8px;
}

.status-badge {
  background: #ff9800;
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f0f0f0;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-weight: 500;
  color: #666;
  flex: 1;
}

.info-value {
  color: #333;
  flex: 1;
  text-align: right;
}

.comment-text {
  background: #f9f9f9;
  padding: 12px;
  border-radius: 6px;
  font-style: italic;
  color: #666;
}

.order-items {
  margin-bottom: 24px;
}

.order-item {
  display: flex;
  align-items: center;
  padding: 16px 0;
  border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
  border-bottom: none;
}

.item-image {
  width: 60px;
  height: 60px;
  border: 1px solid #eee;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  overflow: hidden;
}

.item-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.item-details {
  flex: 1;
}

.item-title {
  font-weight: 500;
  margin-bottom: 4px;
  color: #333;
}

.item-price {
  color: #4caf50;
  font-weight: 500;
  margin-bottom: 2px;
}

.item-quantity {
  color: #666;
  font-size: 0.9rem;
}

.item-total {
  font-size: 1.1rem;
  font-weight: 600;
  color: #333;
}

.order-summary {
  border-top: 2px solid #e0e0e0;
  padding-top: 16px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #666;
}

.total-row {
  font-size: 1.2rem;
  font-weight: 600;
  color: #333;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
}

.order-actions {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 40px;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: 500;
  font-size: 1rem;
  transition: all 0.3s;
  cursor: pointer;
  border: none;
}

.primary-btn {
  background: #4caf50;
  color: white;
}

.primary-btn:hover {
  background: #45a049;
}

.secondary-btn {
  background: #f5f5f5;
  color: #333;
  border: 1px solid #ddd;
}

.secondary-btn:hover {
  background: #e0e0e0;
}

@media (max-width: 768px) {
  .order-details-grid {
    grid-template-columns: 1fr;
  }

  .success-header {
    padding: 30px 15px;
  }

  .success-title {
    font-size: 1.6rem;
  }

  .info-row {
    flex-direction: column;
    gap: 4px;
  }

  .info-value {
    text-align: left;
  }

  .order-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .item-image {
    margin-right: 0;
  }

  .order-actions {
    flex-direction: column;
  }
}

@media print {
  .order-actions {
    display: none;
  }

  .success-header {
    background: none !important;
    color: black !important;
  }

  .info-card, .items-card {
    box-shadow: none;
    border: 1px solid #ddd;
  }
}
</style>