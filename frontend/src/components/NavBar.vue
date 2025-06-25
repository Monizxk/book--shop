<template>
  <section id="header">
    <div class="logo">
      <router-link to="/"><i class="fas fa-book"></i>Bookstore</router-link>
    </div>

    <input
        v-model="searchQuery"
        @input="handleSearch"
        @keyup.enter="handleSearch"
        type="text"
        placeholder="Пошук книг..."
        class="search-input"
    />

    <div class="nav-container">
      <div id="mobile">
        <i class="fa-solid fa-bars" style="font-size: 24px; color: #088178;" @click="toggleMobileMenu"></i>
      </div>




      <!-- Navbar/Sidebar -->
      <ul id="navbar" :class="{ active: isMobileMenuOpen }">
        <div class="sidebar-header"></div>
        <li><router-link :class="{ active: isCurrentRoute('/') }" to="/"><i class="fas fa-book-open"></i>Головна</router-link></li>
        <li><router-link :class="{ active: isCurrentRoute('/category') }" to="/category"><i class="fas fa-heart"></i>Каталог</router-link></li>
        <li><router-link :class="{ active: isCurrentRoute('/contact') }" to="/contact"><i class="fas fa-heart"></i>Контакти</router-link></li>
        <li><router-link :class="{ active: isCurrentRoute('/delivery') }" to="/delivery"><i class="fas fa-heart"></i>Доставка</router-link></li>
        <li><router-link :class="{ active: isCurrentRoute('/payment') }" to="/payment"><i class="fas fa-heart"></i>Оплата</router-link></li>
        <li>
          <a href="#" @click.prevent="toggleCartDrawer" :class="{ active: isCurrentRoute('/cart') }">
            <i class="fas fa-shopping-cart"></i>Корзина
            <span class="cart-count" v-if="cartCount > 0">{{ cartCount }}</span>
          </a>
        </li>
        <!-- Усередині <ul id="navbar"> додай внизу -->
        <li class="contact-nav">
          <i class="fas fa-phone-alt"></i> {{ contacts.phone }}<br />
          <i class="fas fa-envelope"></i> {{ contacts.email }}
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

const searchQuery = ref('')
const router = useRouter()


export default {
  name: 'NavBar',
  components: {
    CartDrawer
  },
  setup() {
    const searchQuery = ref('')
    const isMobileMenuOpen = ref(false)
    const isCartOpen = ref(false)
    const router = useRouter()
    const route = useRoute()

    const contacts = ref({
      phone: '+380 (63) 755-42-70',
      email: 'bookseller.in.ua@gmail.com'
    })

    // function handleSearch() {
    //   if (searchQuery.value.trim() !== '') {
    //     router.push({ name: 'SearchResults', query: { q: searchQuery.value } })
    //   }
    // }

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value
      // Якщо відкриваємо мобільне меню, закриваємо кошик
      if (isMobileMenuOpen.value && isCartOpen.value) {
        isCartOpen.value = false
      }
    }

    function handleSearch() {
      router.push({ name: 'SearchResults', query: { q: searchQuery.value } })
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


    async function fetchContacts() {
      try {
        const response = await fetch('http://localhost:8000/api/settings/contacts')
        const data = await response.json()
        contacts.value = data
      } catch (error) {
        // fallback: дефолтные значения уже заданы
      }
    }
    
    // Закриваємо меню при зміні маршруту
    watch(() => route.path, () => {
      isMobileMenuOpen.value = false
      isCartOpen.value = false
    })

    onMounted(() => {
      fetchContacts()
    })

    return {
      searchQuery,
      isMobileMenuOpen,
      isCartOpen,
      cartCount,
      toggleMobileMenu,
      isCurrentRoute,
      toggleCartDrawer,
      handleSearch,
      contacts,
      closeCartDrawer
    }
  }
}
</script>

<style>

.search-input {
  padding: 5px;
  border-radius: 4px;
  border: 1px solid #ccc;
  margin: 5px;
}

</style>