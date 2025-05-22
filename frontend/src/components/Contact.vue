<script setup>
import { ref, watch, onMounted} from 'vue'
import { useRoute, useRouter } from 'vue-router'

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
  <div v-for="product in filteredProducts" :key="product.id">
    <div @click="goToCatalogWithCategory(product)">
      {{ product.name }}
    </div>
  </div>
</template>

<style scoped>

</style>