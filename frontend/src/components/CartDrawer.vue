<template>
  <div class="cart-drawer" :class="{ open: isOpen }">
    <div class="cart-drawer-header">
      <h3>Кошик</h3>
      <button class="close-btn" @click="close">×</button>
    </div>
    
    <div class="cart-drawer-content">
      <div v-if="cartItems.length === 0" class="empty-cart">
        <i class="fas fa-shopping-cart cart-icon"></i>
        <p>Кошик порожній</p>
      </div>
      
      <div v-else class="cart-items">
        <div v-for="item in cartItems" :key="item.id" class="cart-item">
          <div class="item-image">
            <img :src="item.images ? item.images[0] : ''" alt="Товар" />
          </div>
          <div class="item-details">
            <div class="item-title">{{ item.title }}</div>
            <div class="item-price">{{ item.quantity }} × {{ item.price }} ₴</div>
          </div>
          <button class="remove-btn" @click="remove(item.id)">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    </div>
    
    <div class="cart-drawer-footer" v-if="cartItems.length > 0">
      <div class="cart-total">
        <span>Разом:</span> 
        <span class="total-price">{{ total }} ₴</span>
      </div>
      <div class="cart-actions">
        <button class="view-cart-btn" @click="goToCart">Перейти до кошика</button>
        <button class="checkout-btn" @click="checkout">Оформити замовлення</button>
      </div>
    </div>
  </div>
  
  <div class="cart-drawer-backdrop" :class="{ open: isOpen }" @click="close"></div>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { cart } from '../api/cart.js'

export default {
  name: 'CartDrawer',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  setup(props, { emit }) {
    const cartItems = ref([])
    const router = useRouter()
    
    const total = computed(() => {
      return cart.total
    })
    
    const updateCartItems = () => {
      cartItems.value = [...cart.items]
    }
    
    const close = () => {
      emit('close')
    }
    
    const remove = (id) => {
      cart.remove(id)
      updateCartItems()
    }
    
    const checkout = () => {
      alert('Замовлення оформлено (симуляція)')
      cart.clear()
      updateCartItems()
      close()
    }
    
    const goToCart = () => {
      router.push('/cart')
      close()
    }
    
    // Оновлюємо список товарів при відкритті кошика
    watch(() => props.isOpen, (newVal) => {
      if (newVal) {
        updateCartItems()
      }
    })
    
    onMounted(() => {
      updateCartItems()
    })
    
    return {
      cartItems,
      total,
      close,
      remove,
      checkout,
      goToCart
    }
  }
}
</script>

<style scoped>
.cart-drawer {
  position: fixed;
  top: 0;
  right: -350px;
  width: 350px;
  height: 100vh;
  background-color: white;
  box-shadow: -2px 0 5px rgba(0, 0, 0, 0.2);
  z-index: 1000;
  transition: right 0.3s ease;
  display: flex;
  flex-direction: column;
}

.cart-drawer.open {
  right: 0;
}

.cart-drawer-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 999;
  display: none;
}

.cart-drawer-backdrop.open {
  display: block;
}

.cart-drawer-header {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-drawer-header h3 {
  margin: 0;
  color: black;
  font-size: 1.2rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.cart-drawer-content {
  flex: 1;
  overflow-y: auto;
  padding: 16px;
}

.empty-cart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: #999;
}

.cart-icon {
  font-size: 3rem;
  margin-bottom: 16px;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.cart-item {
  display: flex;
  align-items: center;
  padding-bottom: 16px;
  border-bottom: 1px solid #f0f0f0;
}

.item-image {
  width: 60px;
  height: 60px;
  overflow: hidden;
  margin-right: 16px;
  border: 1px solid #eee;
  display: flex;
  align-items: center;
  justify-content: center;
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
  color: black;
}

.item-price {
  color: #666;
}

.remove-btn {
  background: none;
  border: none;
  color: #ff5252;
  cursor: pointer;
  padding: 8px;
}

.remove-btn:hover {
  background-color: #f8f8f8;
  border-radius: 50%;
}

.cart-drawer-footer {
  padding: 16px;
  border-top: 1px solid #e0e0e0;
}

.cart-total {
  display: flex;
  justify-content: space-between;
  margin-bottom: 16px;
  font-weight: 500;
  color: black;
}

.total-price {
  font-size: 1.1rem;
  color: black;
}

.cart-actions {
  display: flex;
  gap: 8px;
}

.view-cart-btn, .checkout-btn {
  padding: 10px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 500;
  text-align: center;
  flex: 1;
}

.view-cart-btn {
  background-color: #f0f0f0;
  color: #333;
}

.checkout-btn {
  background-color: #4caf50;
  color: white;
}

@media (max-width: 480px) {
  .cart-drawer {
    width: 300px;
  }
}
</style>