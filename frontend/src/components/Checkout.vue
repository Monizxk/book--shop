<template>
  <div class="checkout-container">
    <h1 class="checkout-title">Оформлення замовлення</h1>
    
    <div class="checkout-grid">
      <!-- Ліва колонка з формою -->
      <div class="checkout-form-container">
        <form @submit.prevent="submitOrder" class="checkout-form">
          <div class="form-section">
            <h2>Особисті дані</h2>
            
            <div class="form-group">
              <label for="fullName">ПІБ *</label>
              <input 
                type="text" 
                id="fullName" 
                v-model="form.fullName" 
                required 
                placeholder="Введіть ваше повне ім'я"
              />
              <div v-if="errors.fullName" class="error-message">{{ errors.fullName }}</div>
            </div>
            
            <div class="form-group">
              <label for="email">Email *</label>
              <input 
                type="email" 
                id="email" 
                v-model="form.email" 
                required 
                placeholder="example@mail.com"
              />
              <div v-if="errors.email" class="error-message">{{ errors.email }}</div>
            </div>
            
            <div class="form-group">
              <label for="phone">Телефон *</label>
              <input 
                type="tel" 
                id="phone" 
                v-model="form.phone" 
                required 
                placeholder="+380"
              />
              <div v-if="errors.phone" class="error-message">{{ errors.phone }}</div>
            </div>
          </div>
          
          <div class="form-section">
            <h2>Доставка</h2>
            
            <div class="form-group">
              <label>Спосіб доставки *</label>
              <div class="delivery-options">
                <label class="radio-label">
                  <input 
                    type="radio" 
                    v-model="form.deliveryMethod" 
                    value="novaPoshta" 
                    required
                  />
                  <span>Нова Пошта</span>
                </label>
                <label class="radio-label">
                  <input 
                    type="radio" 
                    v-model="form.deliveryMethod" 
                    value="ukrPoshta" 
                    required
                  />
                  <span>Укрпошта</span>
                </label>
                <label class="radio-label">
                  <input 
                    type="radio" 
                    v-model="form.deliveryMethod" 
                    value="selfPickup" 
                    required
                  />
                  <span>Самовивіз</span>
                </label>
              </div>
              <div v-if="errors.deliveryMethod" class="error-message">{{ errors.deliveryMethod }}</div>
            </div>
            
            <div v-if="form.deliveryMethod === 'selfPickup'" class="form-group">
              <p class="info-text">Самовивіз доступний за адресою: м. Київ, вул. Хрещатик, 1</p>
            </div>
            
            <div v-if="form.deliveryMethod === 'novaPoshta' || form.deliveryMethod === 'ukrPoshta'">
              <div class="form-group">
                <label for="city">Місто *</label>
                <input 
                  type="text" 
                  id="city" 
                  v-model="form.city" 
                  required 
                  placeholder="Введіть місто"
                />
                <div v-if="errors.city" class="error-message">{{ errors.city }}</div>
              </div>
              
              <div class="form-group">
                <label for="postOffice">Відділення / Поштомат *</label>
                <input 
                  type="text" 
                  id="postOffice" 
                  v-model="form.postOffice" 
                  required 
                  placeholder="Номер відділення або поштомату"
                />
                <div v-if="errors.postOffice" class="error-message">{{ errors.postOffice }}</div>
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h2>Оплата</h2>
            
            <div class="form-group">
              <label>Спосіб оплати *</label>
              <div class="payment-options">
                <label class="radio-label">
                  <input 
                    type="radio" 
                    v-model="form.paymentMethod" 
                    value="cashOnDelivery" 
                    required
                  />
                  <span>Накладений платіж</span>
                </label>
                <label class="radio-label">
                  <input 
                    type="radio" 
                    v-model="form.paymentMethod" 
                    value="cardOnline" 
                    required
                  />
                  <span>Оплата картою онлайн</span>
                </label>
              </div>
              <div v-if="errors.paymentMethod" class="error-message">{{ errors.paymentMethod }}</div>
            </div>
          </div>
          
          <div class="form-section">
            <h2>Коментар до замовлення</h2>
            
            <div class="form-group">
              <textarea 
                id="comment" 
                v-model="form.comment" 
                placeholder="Додаткова інформація до замовлення (за бажанням)"
                rows="3"
              ></textarea>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              {{ isSubmitting ? 'Обробка...' : 'Підтвердити замовлення' }}
            </button>
          </div>
        </form>
      </div>
      
      <!-- Права колонка з інформацією про замовлення -->
      <div class="order-summary-container">
        <div class="order-summary">
          <h2>Ваше замовлення</h2>
          
          <div class="order-items">
            <div v-for="item in cartItems" :key="item.id" class="order-item">
              <div class="item-info">
                <div class="item-title">{{ item.title }}</div>
                <div class="item-quantity">{{ item.quantity }} × {{ item.price }} ₴</div>
              </div>
              <div class="item-total">{{ item.quantity * item.price }} ₴</div>
            </div>
          </div>
          
          <div class="order-totals">
            <div class="total-row">
              <span>Товари:</span>
              <span>{{ subtotal }} ₴</span>
            </div>
            <div class="total-row">
              <span>Доставка:</span>
              <span>{{ deliveryCost }} ₴</span>
            </div>
            <div class="total-row grand-total">
              <span>Разом до сплати:</span>
              <span>{{ total }} ₴</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { cart } from '../api/cart.js'

export default {
  name: 'CheckoutPage',
  setup() {
    const router = useRouter()
    const cartItems = ref([])
    const isSubmitting = ref(false)
    
    const form = ref({
      fullName: '',
      email: '',
      phone: '',
      deliveryMethod: '',
      city: '',
      postOffice: '',
      paymentMethod: '',
      comment: ''
    })
    
    const errors = ref({
      fullName: '',
      email: '',
      phone: '',
      deliveryMethod: '',
      city: '',
      postOffice: '',
      paymentMethod: ''
    })
    
    const subtotal = computed(() => {
      return cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
    })
    
    const deliveryCost = computed(() => {
      // Логіка розрахунку вартості доставки
      if (form.value.deliveryMethod === 'selfPickup') {
        return 0
      }
      // Для прикладу - фіксована ціна доставки
      return 60
    })
    
    const total = computed(() => {
      return subtotal.value + deliveryCost.value
    })
    
    const validateForm = () => {
      let isValid = true
      
      // Очистимо попередні помилки
      for (let key in errors.value) {
        errors.value[key] = ''
      }
      
      // Перевірка ПІБ
      if (!form.value.fullName.trim()) {
        errors.value.fullName = 'Введіть ваше ПІБ'
        isValid = false
      }
      
      // Перевірка email
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!form.value.email.trim() || !emailRegex.test(form.value.email)) {
        errors.value.email = 'Введіть коректний email'
        isValid = false
      }
      
      // Перевірка телефону
      const phoneRegex = /^\+?[0-9]{10,13}$/
      if (!form.value.phone.trim() || !phoneRegex.test(form.value.phone)) {
        errors.value.phone = 'Введіть коректний номер телефону'
        isValid = false
      }
      
      // Перевірка способу доставки
      if (!form.value.deliveryMethod) {
        errors.value.deliveryMethod = 'Виберіть спосіб доставки'
        isValid = false
      }
      
      // Перевірка міста і відділення для доставки Новою Поштою або Укрпоштою
      if (form.value.deliveryMethod === 'novaPoshta' || form.value.deliveryMethod === 'ukrPoshta') {
        if (!form.value.city.trim()) {
          errors.value.city = 'Введіть місто'
          isValid = false
        }
        
        if (!form.value.postOffice.trim()) {
          errors.value.postOffice = 'Введіть номер відділення'
          isValid = false
        }
      }
      
      // Перевірка способу оплати
      if (!form.value.paymentMethod) {
        errors.value.paymentMethod = 'Виберіть спосіб оплати'
        isValid = false
      }
      
      return isValid
    }
    
    const submitOrder = async () => {
      if (!validateForm()) {
        // Прокрутити до першої помилки
        const firstErrorElement = document.querySelector('.error-message')
        if (firstErrorElement) {
          firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' })
        }
        return
      }
      
      isSubmitting.value = true
      
      try {
        // Тут буде відправка замовлення на сервер
        // Імітуємо відправку замовлення
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Успішне завершення замовлення
        alert('Замовлення успішно оформлено!')
        
        // Очищення кошика
        cart.clear()
        
        // Перенаправлення на сторінку підтвердження
        router.push('/order-confirmation')
      } catch (error) {
        alert('Помилка при оформленні замовлення. Спробуйте ще раз.')
      } finally {
        isSubmitting.value = false
      }
    }
    
    onMounted(() => {
      // Завантаження товарів з кошика
      cartItems.value = [...cart.items]
      
      // Якщо кошик порожній, перенаправляємо на головну
      if (cartItems.value.length === 0) {
        alert('Ваш кошик порожній')
        router.push('/')
      }
    })
    
    return {
      form,
      errors,
      cartItems,
      subtotal,
      deliveryCost,
      total,
      isSubmitting,
      submitOrder
    }
  }
}
</script>

<style scoped>
.checkout-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 16px;
}

.checkout-title {
  font-size: 1.8rem;
  margin-bottom: 32px;
  color: #333;
  text-align: center;
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 32px;
}

.checkout-form-container, .order-summary-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.checkout-form {
  padding: 24px;
}

.form-section {
  margin-bottom: 24px;
  padding-bottom: 24px;
  border-bottom: 1px solid #e0e0e0;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.form-section h2 {
  font-size: 1.3rem;
  margin-bottom: 16px;
  color: #333;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #4caf50;
  outline: none;
}

.radio-label {
  display: flex;
  align-items: center;
  margin-bottom: 8px;
  cursor: pointer;
}

.radio-label input {
  margin-right: 8px;
}

.delivery-options,
.payment-options {
  margin-top: 8px;
}

.error-message {
  color: #ff5252;
  font-size: 0.9rem;
  margin-top: 4px;
}

.info-text {
  background-color: #e8f5e9;
  padding: 12px;
  border-radius: 4px;
  color: #2e7d32;
}

.form-actions {
  margin-top: 32px;
}

.submit-btn {
  width: 100%;
  padding: 14px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s;
}

.submit-btn:hover {
  background-color: #45a049;
}

.submit-btn:disabled {
  background-color: #a5d6a7;
  cursor: not-allowed;
}

/* Стилі для блоку суми замовлення */
.order-summary {
  padding: 24px;
}

.order-summary h2 {
  font-size: 1.3rem;
  margin-bottom: 16px;
  color: #333;
}

.order-items {
  margin-bottom: 16px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: 12px 0;
  border-bottom: 1px solid #f0f0f0;
}

.item-info {
  flex: 1;
}

.item-title {
  font-weight: 500;
  margin-bottom: 4px;
  color: #333;
}

.item-quantity {
  color: #666;
  font-size: 0.9rem;
}

.item-total {
  font-weight: 500;
  color: #333;
}

.order-totals {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #666;
}

.grand-total {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e0e0e0;
  font-weight: 600;
  font-size: 1.1rem;
  color: #333;
}

@media (max-width: 768px) {
  .checkout-grid {
    grid-template-columns: 1fr;
  }
  
  .order-summary-container {
    order: -1;
  }
}
</style>