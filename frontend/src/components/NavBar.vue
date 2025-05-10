<template>
  <section id="header">
    <div class="logo">
      <router-link to="/"><i class="fas fa-book"></i>Bookstore</router-link>
    </div>

    <div class="nav-container">
      <div id="mobile">
        <i id="bar" class="fas fa-bars" @click="toggleMobileMenu"></i>
      </div>

      <!-- Navbar/Sidebar -->
      <ul id="navbar" :class="{ active: isMobileMenuOpen }">
        <div class="sidebar-header"></div>
        <li><router-link :class="{ active: isCurrentRoute('/') }" to="/"><i class="fas fa-book-open"></i>Shop</router-link></li>
        <li><router-link :class="{ active: isCurrentRoute('/favorites') }" to="/favorites"><i class="fas fa-heart"></i>Favorites</router-link></li>
        <li>
          <a href="#" @click.prevent="toggleCartDrawer" :class="{ active: isCurrentRoute('/cart') }">
            <i class="fas fa-shopping-cart"></i>Cart
            <span class="cart-count" v-if="cartCount > 0">{{ cartCount }}</span>
          </a>
        </li>
      </ul>

      <!-- Sidebar Overlay -->
      <div class="sidebar-overlay" :class="{ active: isMobileMenuOpen }" @click="toggleMobileMenu"></div>
    </div>
    
    <!-- Висувний кошик -->
    <CartDrawer :isOpen="isCartOpen" @close="closeCartDrawer" />
  </section>
</template>

<script>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { cart } from '../api/cart.js'
import CartDrawer from './CartDrawer.vue'

export default {
  name: 'NavBar',
  components: {
    CartDrawer
  },
  setup() {
    const isMobileMenuOpen = ref(false)
    const isCartOpen = ref(false)
    const router = useRouter()
    const route = useRoute()

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value
      // Якщо відкриваємо мобільне меню, закриваємо кошик
      if (isMobileMenuOpen.value && isCartOpen.value) {
        isCartOpen.value = false
      }
    }
    
    const isCurrentRoute = (path) => {
      return route.path === path
    }
    
    const cartCount = computed(() => {
      return cart.items.reduce((sum, item) => sum + item.quantity, 0)
    })
    
    const toggleCartDrawer = () => {
      isCartOpen.value = !isCartOpen.value
      // Якщо відкриваємо кошик, закриваємо мобільне меню
      if (isCartOpen.value && isMobileMenuOpen.value) {
        isMobileMenuOpen.value = false
      }
    }
    
    const closeCartDrawer = () => {
      isCartOpen.value = false
    }
    
    // Закриваємо меню при зміні маршруту
    watch(() => route.path, () => {
      isMobileMenuOpen.value = false
      isCartOpen.value = false
    })

    return {
      isMobileMenuOpen,
      isCartOpen,
      cartCount,
      toggleMobileMenu,
      isCurrentRoute,
      toggleCartDrawer,
      closeCartDrawer
    }
  }
}
</script>

<style>
/* Стилі для вашого навбару залишаються без змін */
</style>