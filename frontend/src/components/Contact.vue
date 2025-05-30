<script setup>
import { ref, watch, onMounted} from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Footer from "./Footer.vue"

const router = useRouter()
const route = useRoute()
const categoryId = route.query.categoryId

const props = defineProps({
  selectedCategory: Object
})

const categories = ref([])
const categoryTree = ref([])
const products = ref([])
const filteredProducts = ref([])
const expandedKeys = ref({})
const breadcrumbItems = ref([
  { title: 'Головна', disabled: false, href: '/' },
  { title: 'Каталог', disabled: false, href: '/category' }
])

watch(() => props.selectedCategory, (newCategory) => {
  if (newCategory) {
    updateBreadcrumbs(newCategory)
    filterProductsByCategory(newCategory)
  } else {
    resetFilter()
  }
})

async function fetchCategories() {
  try {
    const response = await fetch('http://localhost:8000/api/categories')
    const data = await response.json()
    categories.value = data
    categoryTree.value = convertCategoriesToTreeData(data)
  } catch (error) {
    console.error('Помилка при завантаженні категорій:', error)
  }
}
async function fetchProducts() {
  try {
    const response = await fetch('http://localhost:8000/api/products')
    const data = await response.json()
    products.value = data
    filteredProducts.value = data
  } catch (error) {
    console.error('Помилка при завантаженні продуктів:', error)
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
      href: index === arr.length - 1 ? '' : `/catalog/${name.toLowerCase()}`
    }))

    breadcrumbItems.value = [
      { title: 'Головна', disabled: false, href: '/' },
      { title: 'Каталог', disabled: false, href: '/catalog' },
      ...pathItems
    ]
  }
}

function resetFilter() {
  filteredProducts.value = products.value
  breadcrumbItems.value = [
    { title: 'Головна', disabled: false, href: '/' },
    { title: 'Каталог', disabled: false, href: '/catalog' }
  ]
}

function filterProductsByCategory(category) {
  const ids = getCategoryWithChildrenIds(category.id)
  filteredProducts.value = products.value.filter(product =>
      ids.includes(product.category_id)
  )
}
function goToCatalogWithCategory(product) {
  const categoryId = product.category_id
  router.push({ name: 'Category', params: { categoryId } })
}

onMounted(() => {
  fetchCategories()
  fetchProducts()
})

</script>

<template>
  <div>
    <div class="contact-page">
      <section class="product-spacer">
        <div class="contact-header">
          <h1 class="main-title">Контактна інформація магазину "Bookseller"</h1>
        </div>

        <div class="contact-options">
          <div class="contact-option">
            <div class="option-header">
              <div class="option-icon">🤝</div>
              <h3>Контакти</h3>
            </div>
            <div class="info-card">
              <ul>
                <li>Моб:<strong> +380 (63) 755-42-70</strong></li>
                <li>Viber:<strong> +380 (63) 755-42-70</strong></li>
                <li>Email:<strong> bookseller.in.ua@gmail.com</strong></li>
              </ul>
            </div>
          </div>

          <div class="contact-option">
            <div class="option-header">
              <div class="option-icon">🤝</div>
              <h3>Графік роботи</h3>
            </div>
            <div class="info-card">
              <ul>
                <li><strong>ПН, ВТ, СР, ЧТ, ПТ</strong> з 9:00 до 18:00</li>
                <li><strong>Сб:</strong> з 10:00 до 15:00</li>
                <li><strong>Нд:</strong> Вихідний</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="contact-info">
          <p>
            <strong>Маєте питання?</strong> Зв'яжіться з нами, і ми з радістю допоможемо!
            <icon instagram viber></icon>
          </p>
        </div>
      </section>
    </div>
    <Footer />
  </div>
</template>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');


.product-spacer {
  max-width: 1200px;
  margin: 0 auto;
  background: white;
  border-radius: 16px;
  padding: 20px;
  font-family: 'Inter', sans-serif;
  color: #2d3748;
}

/* Header Section */
.contact-header {
  text-align: center;
  margin-bottom: 40px;
}

.main-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1a202c;
  margin-bottom: 5px;
  background: black;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.banner-content h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 8px;
}

.banner-content p {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* contact Options */
.contact-options {
  display: flex;
  flex-wrap: wrap; /* Адаптивність для мобільних */
  justify-content: flex-start;
  gap: 3rem;
  margin-bottom: 20px;

}

.contact-option {
  width: 34rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
  background: #fafafa;
}

.contact-option:hover {
  border-color: #667eea;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
}

.option-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.option-icon {
  font-size: 2rem;
  margin-right: 16px;
  padding: 12px;
  background: linear-gradient(135deg, #33bdd5 0%, #088178 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 60px;
  height: 60px;
}

.option-header h3 {
  font-size: 1.4rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
}

.info-card {
  background: #f8f9fa;
  padding: 24px;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.info-card h4 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-card ul {
  list-style: none;
  margin: 0;
  padding-left: 20px;
}

.info-card li {
  margin-bottom: 8px;
  line-height: 1.5;
  color: #4a5568;
  text-align: left;
}


.contact-info p {
  font-size: 1.1rem;
  color: #000000;
  margin: 0;
}

/* Responsive Design */
@media (max-width: 768px) {

  .product-spacer {
    width: 500px;
  }

  .contact-option {
    width: 220px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }


}
@media (min-width: 380px) and (max-width: 768px) {

  .product-spacer {
    width: 400px;
  }

  .contact-option {
    width: 350px;
  }

  .info-card {
    width: 350px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
  .contact-option {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
    background: #fafafa;
  }
}

@media (min-width: 320px) and (max-width: 380px) {

  .product-spacer {
    width: 300px;
    max-width: 1400px;
  }

  .contact-option {
    width: 260px;
  }

  .info-card {
    width: 260px;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }
  .contact-option {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
    background: #fafafa;
  }
}

</style>