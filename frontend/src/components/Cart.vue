<template>
  <div class="cart-page">
    <div class="container">
      <h1>Кошик</h1>
      
      <div v-if="cartItems.length === 0" class="empty-cart">
        <div class="empty-cart-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <p>Ваш кошик порожній</p>
        <router-link to="/" class="continue-shopping-btn">
          Перейти до магазину
        </router-link>
      </div>
      
      <div v-else class="cart-content">
        <div class="cart-items">
          <div class="cart-header">
            <div class="product-col">Товар</div>
            <div class="price-col">Ціна</div>
            <div class="quantity-col">Кількість</div>
            <div class="total-col">Сума</div>
            <div class="action-col"></div>
          </div>
          
          <div v-for="item in cartItems" :key="item.id" class="cart-item">
            <div class="product-col">
              <div class="product-image">
                <img :src="item.images ? item.images[0] : ''" alt="Товар" />
              </div>
              <div class="product-details">
                <div class="product-title">{{ item.title }}</div>
              </div>
            </div>
            
            <div class="price-col">{{ item.price }} ₴</div>
            
            <div class="quantity-col">
              <div class="quantity-control">
                <button @click="decreaseQuantity(item)" class="qty-btn">-</button>
                <input type="text" :value="item.quantity" readonly />
                <button @click="increaseQuantity(item)" class="qty-btn">+</button>
              </div>
            </div>
            
            <div class="total-col">{{ item.price * item.quantity }} ₴</div>
            
            <div class="action-col">
              <button @click="remove(item.id)" class="remove-btn">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
        
        <div class="cart-summary">
          <div class="cart-actions">
            <button @click="clearCart" class="clear-cart-btn">
              Очистити кошик
            </button>
            <router-link to="/" class="continue-shopping-btn">
              Продовжити покупки
            </router-link>
          </div>
          
          <div class="cart-totals">
            <h3>Підсумок замовлення</h3>
            <div class="total-row">
              <span>Сума товарів:</span>
              <span>{{ total }} ₴</span>
            </div>
            <div class="total-row">
              <span>Доставка:</span>
              <span>За тарифами перевізника</span>
            </div>
            <div class="total-row grand-total">
              <span>Загальна сума:</span>
              <span>{{ total }} ₴</span>
            </div>
            <button @click="checkout" class="checkout-btn">
              Оформити замовлення
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {ref, computed, onMounted, onUnmounted} from 'vue'
import { useRouter } from 'vue-router'
import { cart } from '../api/cart.js'

export default {
  name: 'CartPage',
  setup() {
    const cartItems = ref([])
    const router = useRouter()

    const checkout = () => router.push('/checkout')
    
    const total = computed(() => {
      return cart.total
    })
    
    const updateCartItems = () => {
      cartItems.value = [...cart.items]
    }
    
    const remove = (id) => {
      cart.remove(id)
      updateCartItems()
    }
    
    const clearCart = () => {
      if (confirm('Ви дійсно хочете очистити кошик?')) {
        cart.clear()
        updateCartItems()
      }
    }
    const decreaseQuantity = (item) => {
      if (item.quantity > 1) {
        cart.updateQuantity(item.id, item.quantity - 1)
        updateCartItems()
      }
    }
    
    const increaseQuantity = (item) => {
      cart.updateQuantity(item.id, item.quantity + 1)
      updateCartItems()
    }

    
    onMounted(() => {
      updateCartItems()
      const Event = new CustomEvent("hideCategoryTree")
      document.dispatchEvent(Event)
    })

    onUnmounted(() => {
      const Event = new CustomEvent("showCategoryTree")
      document.dispatchEvent(Event)
    })
    
    return {
      cartItems,
      total,
      remove,
      clearCart,
      checkout,
      decreaseQuantity,
      increaseQuantity
    }
  }
}
</script>

<style scoped>
.cart-page {
  padding: 30px 0;
  color: black;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

h1 {
  margin-bottom: 30px;
  font-size: 2rem;
  color: black;
}

.empty-cart {
  text-align: center;
  padding: 50px 0;
}

.empty-cart-icon {
  font-size: 4rem;
  color: #ccc;
  margin-bottom: 20px;
}

.empty-cart p {
  font-size: 1.2rem;
  margin-bottom: 20px;
  color: #666;
}

.continue-shopping-btn {
  display: inline-block;
  padding: 12px 24px;
  background-color: #4caf50;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  font-weight: 500;
}

.cart-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: 30px;
}

.cart-header {
  display: grid;
  grid-template-columns: 3fr 1fr 1fr 1fr 50px;
  padding: 15px 0;
  border-bottom: 1px solid #eee;
  font-weight: 500;
  display: none;
}

.cart-item {
  display: grid;
  grid-template-columns: 3fr 1fr 1fr 1fr 50px;
  gap: 15px;
  padding: 20px 0;
  border-bottom: 1px solid #eee;
  align-items: center;
}

.product-col {
  display: flex;
  align-items: center;
}

.product-image {
  width: 80px;
  height: 80px;
  border: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.product-image img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.product-title {
  font-weight: 500;
  color: black;
}

.quantity-control {
  display: flex;
  align-items: center;
  max-width: 100px;
}

.quantity-control input {
  width: 40px;
  height: 36px;
  text-align: center;
  border: 1px solid #ddd;
  border-radius: 0;
}

.qty-btn {
  width: 36px;
  height: 36px;
  border: 1px solid #ddd;
  background: #f8f8f8;
  font-size: 1rem;
  cursor: pointer;
}

.qty-btn:first-child {
  border-radius: 4px 0 0 4px;
}

.qty-btn:last-child {
  border-radius: 0 4px 4px 0;
}

.remove-btn {
  background: none;
  border: none;
  color: #ff5252;
  cursor: pointer;
  padding: 8px;
}

.cart-summary {
  margin-top: 30px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
}

.cart-actions {
  display: flex;
  gap: 10px;
}

.clear-cart-btn {
  padding: 12px 24px;
  background-color: #f44336;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
}

.cart-totals {
  background-color: #f9f9f9;
  padding: 20px;
  border-radius: 4px;
}

.cart-totals h3 {
  margin-bottom: 15px;
  font-size: 1.2rem;
  color: black;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
  color: #666;
}

.grand-total {
  font-weight: 700;
  font-size: 1.1rem;
  color: black;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid #eee;
}

.checkout-btn {
  display: block;
  width: 100%;
  padding: 12px;
  margin-top: 20px;
  background-color: #4caf50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1.1rem;
  font-weight: 500;
}

@media (min-width: 768px) {
  .cart-header {
    display: grid;
  }
}

@media (max-width: 991px) {
  .cart-summary {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 767px) {
  .cart-item {
    grid-template-columns: 1fr;
    gap: 10px;
    padding-bottom: 25px;
  }
  
  .product-col {
    margin-bottom: 10px;
  }
  
  .price-col, .quantity-col, .total-col, .action-col {
    display: flex;
    align-items: center;
  }
  
  .price-col::before {
    content: "Ціна: ";
    width: 100px;
    font-weight: 500;
  }
  
  .quantity-col::before {
    content: "Кількість: ";
    width: 100px;
    font-weight: 500;
  }
  
  .total-col::before {
    content: "Сума: ";
    width: 100px;
    font-weight: 500;
  }
  
  .action-col {
    justify-content: flex-end;
    margin-top: 10px;
  }
  
  .cart-actions {
    flex-direction: column;
  }
}
</style>