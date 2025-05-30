<template>
  <div class="product-spacer px-4 py-5">
    <h2 class="mb-2">Результати пошуку: "{{ searchQuery }}"</h2>

    <div v-if="loading">
      <p>Завантаження...</p>
    </div>

    <div v-else-if="error" style="color: red;">
      <p>Помилка: {{ error }}</p>
    </div>

    <div v-else-if="products.length" class="products-grid">
      <div
          v-for="product in products"
          :key="product.id"
          class="book-product"
      >
        <div class="book-image">
          <img
              v-if="product.images && product.images.length"
              :src="getImageUrl(product.images[0])"
              alt="Зображення товару"
              class="book-cover"
          />
        </div>
        <div class="book-details">
          <h3 class="book-title">{{ product.title }}</h3>
          <div class="book-price-container">
            <p class="book-price">{{ product.price }} грн.</p>
            <span class="book-stock" v-if="product.in_stock !== false">
              <span class="check-icon">✓</span> В наявності
            </span>
            <span class="book-stock out-of-stock" v-else>
              Немає в наявності
            </span>
          </div>
          <button @click="addToCart(product)" class="buy-button">Купити</button>
        </div>
      </div>
    </div>
    <p v-else>Нічого не знайдено</p>
    <Footer/>
  </div>
</template>
<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { cart } from "../api/cart.js"
import Footer from "./Footer.vue"

const route = useRoute()
const searchQuery = ref(route.query.q || '')
const products = ref([])
const loading = ref(false)
const error = ref(null)
const debugMode = ref(false)

// Check if we're in development mode
const isDevelopment = computed(() => import.meta.env.DEV)

const apiUrl = computed(() => `http://localhost:8000/api/products/search?q=${encodeURIComponent(searchQuery.value)}`)

// Function to get full image URL
function getImageUrl(imagePath) {
  if (!imagePath) return ''
  return `http://localhost:8000/storage/${imagePath}`
}

async function fetchProducts() {
  if (!searchQuery.value) {
    error.value = 'No search query provided'
    products.value = []
    return
  }

  loading.value = true
  error.value = null

  try {
    console.log('Making API request to:', apiUrl.value)

    const res = await axios.get(apiUrl.value)

    console.log('API response status:', res.status)
    console.log('API response data:', res.data)

    // Handle different response formats
    if (Array.isArray(res.data)) {
      products.value = res.data
    } else if (res.data.products && Array.isArray(res.data.products)) {
      products.value = res.data.products
    } else if (res.data.data && Array.isArray(res.data.data)) {
      products.value = res.data.data
    } else {
      console.warn('Unexpected response format:', res.data)
      products.value = []
    }

  } catch (err) {
    console.error('Search API Error:', err)

    // If search endpoint doesn't exist, try to get all products and filter
    try {
      console.log('Trying fallback: fetching all products...')
      const response = await axios.get('http://localhost:8000/api/products')

      let allProducts = []
      if (Array.isArray(response.data)) {
        allProducts = response.data
      } else if (response.data.products) {
        allProducts = response.data.products
      } else if (response.data.data) {
        allProducts = response.data.data
      }

      // Filter products based on search query
      const filtered = allProducts.filter(product => {
        const name = product.name || product.title || ''
        const description = product.description || ''
        const searchTerm = searchQuery.value.toLowerCase()

        return name.toLowerCase().includes(searchTerm) ||
            description.toLowerCase().includes(searchTerm)
      })

      products.value = filtered

    } catch (fallbackErr) {
      console.error('Fallback API Error:', fallbackErr)

      if (err.response) {
        error.value = `Server error: ${err.response.status} - ${err.response.data?.message || err.response.statusText}`
        console.error('Response data:', err.response.data)
      } else if (err.request) {
        error.value = 'Network error: No response from server. Make sure your Laravel backend is running on port 8000.'
      } else {
        error.value = `Request error: ${err.message}`
      }
    }
  } finally {
    loading.value = false
  }
}

// Watch for route query changes
watch(
    () => route.query.q,
    (newQuery) => {
      searchQuery.value = newQuery || ''
      fetchProducts()
    },
    { immediate: true }
)

// Initial fetch
onMounted(() => {
  if (!route.query.q) {
    fetchProducts()
  }
})
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

/* Keep your existing styles */

.main-container,
.main-container p,
.main-container h1,
.main-container h2,
.main-container h3,
.main-container h4,
.main-container h5,
.main-container h6,
.main-container span,
.main-container div,
.main-container li,
.main-container a,
.main-container label {
  color: black !important;
}

.text {
  color: black;
}


.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.book-product {
  border: 1px solid #e0e0e0 !important;
  border-radius: 4px;
  max-height: 450px;
  display: flex;
  flex-direction: column;
  background-color: white;
  transition: box-shadow 0.3s ease;
  overflow: hidden;
  box-sizing: border-box;
  width: 100%;
  overflow-wrap: break-word;
}

/* New book product styling */

.book-product-container {
  margin-bottom: 20px;
  padding: 0 10px;
}

.book-product {
  border: 1px solid #e0e0e0 !important;
  border-radius: 4px;
  max-height: 450px;
  display: flex;
  flex-direction: column;
  background-color: white;
  transition: box-shadow 0.3s ease;
  overflow: hidden;
  max-width: 100%;
  box-sizing: border-box;
  width: 100%;
  overflow-wrap: break-word;

}

.book-product:hover {
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.book-image {
  padding: 8px;
  background-color: #f5f5f5;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 200px;
  height: 250px;
  flex-shrink: 0;
  overflow: hidden;
  margin: 0 auto; /* центр по горизонталі, якщо блоку потрібно центруватись у контейнері */
}

.book-cover {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block; /* прибирає можливі нижні відступи */
  margin: 0 auto; /* центр по горизонталі */
}

.book-details {
  padding: 15px;
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.book-title {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 15px;
  line-height: 1.3;
  color: #333;
  min-height: 42px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.book-price-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  margin-bottom: 15px;
}

.book-price {
  font-size: 20px;
  font-weight: 700;
  color: #e53935 !important;
  margin: 0;
}

.book-stock {
  font-size: 14px;
  color: #43a047 !important;
  display: flex;
  align-items: center;
}

.check-icon {
  color: #43a047 !important;
  font-weight: bold;
  margin-right: 5px;
}

.out-of-stock {
  color: #e53935 !important;
}

.buy-button {
  background-color: #232faf;
  color: white !important;
  border: none;
  border-radius: 4px;
  padding: 10px 15px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: background-color 0.3s ease;
  margin: 0; /* прибираємо margin */
  box-sizing: border-box; /* враховує padding і border у ширину */
}


.buy-button:hover {
  background-color: #330050;
}
/* Адаптивна сітка для різних розмірів екрану */
@media (max-width: 1264px) {
  .product-spacer {
    width: 1000px;
    max-width: 1400px;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
  }

  .book-image {
    width: 70%;
  }

  .book-details {
    font-size: 3rem;
  }

  .book-title {
    font-size: 1.1rem;
  }

  .book-price {
    font-size: 1rem;
  }

  .book-stock {
    font-size: 0.9rem;
  }

  .buy-button {
    padding: 6px;
    font-size: 0.9rem;
    height: 34px;
  }

  .product-spacer h2 {
    font-size: 2.25rem;
  }
}

@media (min-width: 960px) and (max-width: 1263px) {
  .product-spacer {
    width: 800px;
    max-width: 1400px;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  }

  .book-image {
    width: 70%;
  }

  .book-details {
    font-size: 3rem;
  }

  .book-title {
    font-size: 1.1rem;
  }

  .book-price {
    font-size: 1rem;
  }

  .book-stock {
    font-size: 0.9rem;
  }

  .buy-button {
    padding: 6px;
    font-size: 0.9rem;
    height: 34px;
  }

  .product-spacer h2 {
    font-size: 2.25rem;
  }
}

@media (min-width: 600px) and (max-width: 959px) {
  .product-spacer {
    width: 500px;
    max-width: 1400px;
  }
  .products-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
  }

}

@media (min-width: 350px) and (max-width: 600px){
  .product-spacer {
    width: 300px;
    max-width: 1400px;
  }
  .products-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .book-image {
    width: 70%;
  }

  .book-details {
    font-size: 3rem;
  }

  .book-title {
    font-size: 1.1rem;
  }

  .book-price {
    font-size: 1rem;
  }

  .book-stock {
    font-size: 0.9rem;
  }

  .buy-button {
    padding: 6px;
    font-size: 0.9rem;
    height: 34px;
  }

  .product-spacer h2 {
    font-size: 2.25rem;
  }
}

@media (min-width: 320px) and (max-width: 350px) {
  .product-spacer {
    width: 280px;
  }
  .products-grid {
    grid-template-columns: 250px;
    gap: 10px;
  }

  .book-product {
    width: 280px;
  }
  .book-image {
    width: 60%;
  }

  .book-details {
    font-size: 2rem;
  }

  .book-title {
    font-size: 1.1rem;
  }

  .book-price {
    font-size: 1rem;
  }

  .book-stock {
    font-size: 0.9rem;
  }

  .buy-button {
    padding: 6px;
    font-size: 0.9rem;
    height: 34px;
  }

  .product-spacer h2 {
    font-size: 2.25rem;
  }
}
/* Адаптивні розміри шрифтів */
h2 {
  font-size: clamp(1.25rem, 4vw, 1.75rem);
}

.text-subtitle-1 {
  font-size: clamp(0.9rem, 3vw, 1rem);
}

</style>