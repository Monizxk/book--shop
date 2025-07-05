<template>
  <div class="product-spacer px-4 py-5">
    <h2 class="mb-2">Результати пошуку: "{{ searchQuery }}"</h2>

    <!-- Состояние загрузки -->
    <div v-if="loading" class="loading-container">
      <div class="search-loader">
        <div class="loader-spinner"></div>
        <p class="loader-text">Пошук товарів...</p>
      </div>
      <div class="products-grid">
        <ProductSkeleton v-for="n in 8" :key="n" />
      </div>
    </div>

    <!-- Состояние ошибки -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <h3>Помилка пошуку</h3>
      <p class="error-message">{{ error }}</p>
      <button @click="retrySearch" class="retry-button">Спробувати ще раз</button>
    </div>

    <!-- Результаты поиска -->
    <div v-else-if="products.length" class="search-results">
      <div class="results-header">
        <p class="results-count">Знайдено {{ products.length }} товарів</p>
      </div>
      <div class="products-grid">
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
    </div>

    <!-- Нет результатов -->
    <div v-else class="no-results">
      <div class="no-results-icon">🔍</div>
      <h3>Нічого не знайдено</h3>
      <p>Спробуйте змінити пошуковий запит або перевірте правопис</p>
      <div class="search-suggestions">
        <p>Поради для пошуку:</p>
        <ul>
          <li>Використовуйте більш загальні терміни</li>
          <li>Перевірте правопис</li>
          <li>Спробуйте синоніми</li>
        </ul>
      </div>
    </div>

    <Footer />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { cart } from '../api/cart.js'
import { debounce } from 'lodash'
import Footer from './Footer.vue'
import ProductSkeleton from './ProductSkeleton.vue'

const route = useRoute()
const searchQuery = ref(route.query.q || '')
const products = ref([])
const loading = ref(false)
const error = ref(null)
const debugMode = ref(false)

const isDevelopment = computed(() => import.meta.env.DEV)
const apiUrl = computed(() => `http://localhost:8000/api/products/search?q=${encodeURIComponent(searchQuery.value)}`)

function getImageUrl(imagePath) {
  if (!imagePath) return ''
  return `http://localhost:8000/storage/${imagePath}`
}

const debouncedFetchProducts = debounce(async () => {
  if (!searchQuery.value) {
    error.value = 'No search query provided'
    products.value = []
    loading.value = false
    return
  }

  loading.value = true
  error.value = null
  products.value = [] // Очищаем предыдущие результаты

  try {
    console.log('Making API request to:', apiUrl.value)
    const res = await axios.get(apiUrl.value)

    console.log('API response status:', res.status)
    console.log('API response data:', res.data)

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

      const filtered = allProducts.filter(product => {
        const name = product.name || product.title || ''
        const description = product.description || ''
        const searchTerm = searchQuery.value.toLowerCase()
        return name.toLowerCase().includes(searchTerm) || description.toLowerCase().includes(searchTerm)
      })

      products.value = filtered
    } catch (fallbackErr) {
      console.error('Fallback API Error:', fallbackErr)
      if (err.response) {
        error.value = `Server error: ${err.response.status} - ${err.response.data?.message || err.response.statusText}`
      } else if (err.request) {
        error.value = 'Network error: No response from server. Make sure your Laravel backend is running on port 8000.'
      } else {
        error.value = `Request error: ${err.message}`
      }
    }
  } finally {
    loading.value = false
  }
}, 500)

// Функция повторного поиска
function retrySearch() {
  debouncedFetchProducts()
}

watch(
    () => route.query.q,
    (newQuery) => {
      searchQuery.value = newQuery || ''
      debouncedFetchProducts()
    },
    {immediate: true}
)

onMounted(() => {
  if (!route.query.q) {
    debouncedFetchProducts()
  }
})

function addToCart(product) {
  cart.addItem(product)
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

/* Основные стили */
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

/* Стили загрузки */
.loading-container {
  text-align: center;
}

.search-loader {
  margin: 40px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
}

.loader-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #232faf;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.loader-text {
  font-size: 18px;
  color: #666;
  margin: 0;
}

/* Стили ошибки */
.error-container {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.error-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.error-container h3 {
  font-size: 24px;
  color: #333 !important;
  margin-bottom: 10px;
}

.error-message {
  font-size: 16px;
  margin-bottom: 30px;
  color: #666 !important;
}

.retry-button {
  background-color: #232faf;
  color: white !important;
  border: none;
  border-radius: 6px;
  padding: 12px 24px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.retry-button:hover {
  background-color: #330050;
}

/* Стили результатов */
.results-header {
  margin: 20px 0;
  padding-bottom: 10px;
  border-bottom: 1px solid #e0e0e0;
}

.results-count {
  font-size: 16px;
  color: #666 !important;
  margin: 0;
}

/* Стили отсутствия результатов */
.no-results {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.no-results-icon {
  font-size: 64px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.no-results h3 {
  font-size: 24px;
  color: #333 !important;
  margin-bottom: 10px;
}

.no-results > p {
  font-size: 16px;
  margin-bottom: 30px;
  color: #666 !important;
}

.search-suggestions {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;
  text-align: left;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.search-suggestions p {
  font-weight: 600;
  margin-bottom: 10px;
  color: #333 !important;
}

.search-suggestions ul {
  margin: 0;
  padding-left: 20px;
}

.search-suggestions li {
  margin-bottom: 5px;
  color: #666 !important;
}

/* Стили продуктов */
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
  margin: 0 auto;
}

.book-cover {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
  margin: 0 auto;
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
  margin: 0;
  box-sizing: border-box;
}

.buy-button:hover {
  background-color: #330050;
}

/* Адаптивные стили */
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

@media (min-width: 350px) and (max-width: 600px) {
  .product-spacer {
    width: 350px;
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

h2 {
  font-size: clamp(1.25rem, 4vw, 1.75rem);
}

.text-subtitle-1 {
  font-size: clamp(0.9rem, 3vw, 1rem);
}
</style>