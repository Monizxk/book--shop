<template>
  <section id="header">
    <div class="logo">
      <router-link to='/' @click="handleLogoClick">
        <i class="fas fa-book"></i>Bookstore
      </router-link>
    </div>

    <!-- Десктопний пошук -->
    <div class="search-container">
      <input
          v-model="searchQuery"
          @input="handleSearch"
          @keyup.enter="handleSearch"
          type="text"
          placeholder="Пошук книг..."
          class="search-input"
      />
      <button @click="handleSearch" type="button">
        <i class="fas fa-search"></i>
      </button>
    </div>

    <div class="nav-container">
      <div id="mobile">
        <i class="fa-solid fa-bars" style="font-size: 24px; color: #088178;" @click="toggleMobileMenu"></i>
      </div>

      <!-- Navbar/Sidebar -->
      <ul id="navbar" :class="{ active: isMobileMenuOpen }">
        <div class="sidebar-header"></div>

        <!-- Мобільний пошук в sidebar -->
<!--        <div class="mobile-search-container">-->
<!--          <input-->
<!--              v-model="searchQuery"-->
<!--              @input="handleSearch"-->
<!--              @keyup.enter="handleSearch"-->
<!--              type="text"-->
<!--              placeholder="Пошук книг..."-->
<!--              class="mobile-search-input"-->
<!--          />-->
<!--          <button @click="handleSearch" type="button">-->
<!--            <i class="fas fa-search"></i> Пошук-->
<!--          </button>-->
<!--        </div>-->

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
        <!-- Контакти внизу sidebar -->
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
import { useCategories } from '../composables/useCategories'

const searchQuery = ref('')
const router = useRouter()
const { collapseCategories } = useCategories()

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

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value
      // Якщо відкриваємо мобільне меню, закриваємо кошик
      if (isMobileMenuOpen.value && isCartOpen.value) {
        isCartOpen.value = false
      }
    }

    function handleSearch() {
      if (searchQuery.value.trim() !== '') {
        router.push({ name: 'SearchResults', query: { q: searchQuery.value } })
        // Закриваємо мобільне меню після пошуку
        if (isMobileMenuOpen.value) {
          isMobileMenuOpen.value = false
        }
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

    async function fetchContacts() {
      try {
        const response = await fetch('http://localhost:8000/api/settings/contacts')
        const data = await response.json()
        contacts.value = data
      } catch (error) {
        // fallback: дефолтные значения уже заданы
      }
    }

    const handleLogoClick = (event) => {
      event.preventDefault() // Запобігаємо стандартній навігації router-link
      collapseCategories()

      // Примусова навігація на головну сторінку
      router.push('/').then(() => {
        // Якщо потрібно перезавантажити сторінку
        window.location.reload()
      })
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
/* Десктопний пошук */
.search-container {
  display: flex;
  align-items: center;
  position: relative;
  margin: 0 20px;
}

.search-container .search-input {
  width: 300px;
  padding: 8px 50px 8px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 25px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.3s ease;
  margin: 0;
}

.search-container .search-input:focus {
  border-color: #088178;
}

.search-container button {
  position: absolute;
  right: 5px;
  background: #088178;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.3s ease;
}

.search-container button:hover {
  background: #066e62;
}

/* Мобільний пошук в sidebar */
.mobile-search-container {
  display: none;
  width: 100%;
  padding: 20px 30px;
  border-bottom: 2px solid #f0f0f0;
  background: #f9f9f9;
  position: sticky;
  top: 0;
  z-index: 1002;
}

.mobile-search-container .mobile-search-input {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 25px;
  font-size: 16px;
  outline: none;
  transition: border-color 0.3s ease;
  background: white;
  margin: 0;
}

.mobile-search-container .mobile-search-input:focus {
  border-color: #088178;
}

.mobile-search-container button {
  width: 100%;
  background: #088178;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 25px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  margin-top: 10px;
  transition: background-color 0.3s ease;
}

.mobile-search-container button:hover {
  background: #066e62;
}

/* Адаптивність */
@media (max-width: 1250px) {
  /* Ховаємо десктопний пошук */
  .search-container {
    display: none !important;
  }

  /* Показуємо мобільний пошук в sidebar */
  .mobile-search-container {
    display: block;
  }
}

@media (max-width: 799px) {
  /* Ховаємо десктопний пошук */
  .search-container {
    display: none !important;
  }

  /* Показуємо мобільний пошук в sidebar */
  .mobile-search-container {
    display: block;
  }
}

@media (max-width: 480px) {
  .mobile-search-container {
    padding: 15px 20px;
  }
}
</style>