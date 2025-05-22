<template>
  <div class="product-spacer px-4 py-5">
    <h2 class="mb-2">Розпродаж</h2>

    <p class="text text-subtitle-1" v-if="!selectedCategory">
      Тут ви знайдете великий вибір книг з різних категорій. Оберіть те, що вам до вподоби!
    </p>
    <p class="text text-subtitle-1" v-else>
      Категорія: {{ selectedCategory.name }} <span v-if="filteredProducts.length">({{ filteredProducts.length }} товарів)</span>
    </p>

    <v-breadcrumbs
        :items="breadcrumbItems"
        divider=">"
    ></v-breadcrumbs>

  <v-row>
    <v-col
        v-for="(product, index) in filteredProducts"
        :key="index"
        cols="12"
        sm="6"
        md="4"
        lg="3"
        class="book-product-container"
    >
      <div class="book-product">
        <div class="book-image">
          <img :src="getImageUrl(product.images[0])" alt="Зображення товару" class="book-cover" />
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
    </v-col>
    <v-col cols="12" v-if="filteredProducts.length === 0" class="text-center py-5">
      <p>У розпродажі немає товарів</p>
    </v-col>
  </v-row>
  <Footer/>
  </div>
</template>

<script setup>
import {defineComponent, onMounted, ref, watch} from "vue";
import Footer from "./Footer.vue";
import {useRoute} from "vue-router";
import {cart} from "../api/cart.js";
const route = useRoute()
const categoryId = route.query.categoryId
import Tree from 'primevue/tree'
import CategoryTree from "./CategoryTree.vue"

const props = defineProps(['selectedCategory', 'categoryId'])

const saleProducts = ref([])
const categories = ref([])
const categoryTree = ref([])
const filteredProducts = ref([])
const expandedKeys = ref({})
const breadcrumbItems = ref([
  { title: 'Головна', disabled: false, href: '/' },
  { title: 'Розпродаж', disabled: true, href: '/' }
])

watch(() => props.selectedCategory, (newCategory) => {
  if (newCategory) {
    updateBreadcrumbs(newCategory)
    filterSaleProductsByCategory(newCategory)
  } else {
    resetFilter()
  }
})

function getImageUrl(imagePath) {
  if (!imagePath) return ''
  return `http://localhost:8000/storage/${imagePath}`
}

function addToCart(product) {
  cart.add(product)
  alert('Товар додано до кошика')
}

async function fetchSaleProducts() {
  try {
    const response = await fetch('http://localhost:8000/api/products/sale');
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json();
    saleProducts.value = data;
    filteredProducts.value = data; // Show all sale products initially
  } catch (error) {
    console.error('Помилка при завантаженні розпродажу:', error);
  }
}

async function fetchCategories() {
  try {
    const response = await fetch('http://localhost:8000/api/categories')
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const data = await response.json()
    categories.value = data
    categoryTree.value = convertCategoriesToTreeData(data)
  } catch (error) {
    console.error('Помилка при завантаженні категорій:', error)
  }
}

function convertCategoriesToTreeData(categories, prefix = '0', parentPath = []) {
  return categories.map((cat, index) => {
    const currentKey = `${prefix}-${index}`
    const currentPath = [...parentPath, cat.name]

    return {
      key: currentKey,
      label: cat.name,
      data: {
        id: cat.id,
        name: cat.name,
        path: currentPath
      },
      children: cat.children ? convertCategoriesToTreeData(cat.children, currentKey, currentPath) : []
    }
  })
}

function getCategoryWithChildrenIds(categoryId) {
  const ids = [categoryId]

  const findChildrenIds = (categories) => {
    categories.forEach(category => {
      if (category.id === categoryId) {
        if (category.children) {
          collectChildrenIds(category.children, ids)
        }
      } else if (category.children) {
        findChildrenIds(category.children)
      }
    })
  }

  findChildrenIds(categories.value)
  return ids
}

function collectChildrenIds(children, ids) {
  children.forEach(child => {
    ids.push(child.id)
    if (child.children) {
      collectChildrenIds(child.children, ids)
    }
  })
}

function updateBreadcrumbs(category) {
  if (category && category.path) {
    const pathItems = category.path.map((name, index, arr) => ({
      title: name,
      disabled: index === arr.length - 1,
      href: index === arr.length - 1? '' : `/catalog/${name.toLowerCase()}`
    }))

    breadcrumbItems.value = [
      { title: 'Головна', disabled: false, href: '/' },
      { title: 'Розпродаж', disabled: false, href: '/' },
      ...pathItems
    ]
  }
}

function resetFilter() {
  filteredProducts.value = saleProducts.value // Reset to sale products, not all products
  breadcrumbItems.value = [
    { title: 'Головна', disabled: false, href: '/' },
    { title: 'Розпродаж', disabled: true, href: '/' }
  ]
}

function filterSaleProductsByCategory(category) {
  const ids = getCategoryWithChildrenIds(category.id)
  // Filter sale products by category, not all products
  filteredProducts.value = saleProducts.value.filter(product =>
      ids.includes(product.category_id)
  )
}

// Single onMounted hook
onMounted(async () => {
  await fetchCategories()
  await fetchSaleProducts()
})
</script>
<style>
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


.product-spacer {
  background-color: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 24px;
  padding: 16px;
  width: 1250px;
  max-width: 1400px; /* Збільшена максимальна ширина для десктопу */
  margin-left: auto;
  margin-right: auto;
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
}

@media (min-width: 350px) and (max-width: 600px){
  .product-spacer {
    width: 300px;
    max-width: 1400px;
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