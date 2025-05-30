<template>
  <div class="wrapper">
  <v-container class="category-container" v-show="showCategoryTree" :style="{ maxWidth: '300px'}">
    <div class="pa-4 text-center">
      <h2 class="category-title">Категорії</h2>
      <Tree
          :value="categoryTree"
          selectionMode="single"
          :expandedKeys="expandedKeys"
          @node-toggle="handleNodeToggle"
          @node-select="handleNodeSelect"
      />
    </div>
  </v-container>

    <v-container class="time-container" v-show="showTimeContainer">
      <div class="contact-option">
        <div class="option-header">
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
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from 'vue'
import Tree from 'primevue/tree';


const emit = defineEmits(['select-category'])

const showTimeContainer = ref(false)       // Графік спочатку прихований
const isExpanded = ref(false)
const isSelected = ref(false)
const categories = ref([])
const categoryTree = ref([])
const selectedCategory = ref(null)
const products = ref([])
const filteredProducts = ref([])
const expandedKeys = ref({})
const breadcrumbItems = ref([])

const fetchCategories = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/categories')
    const data = await response.json()
    categories.value = data
    categoryTree.value = convertCategoriesToTreeData(data)
  } catch (error) {
    console.error('Помилка при завантаженні категорій:', error)
  }
}

const convertCategoriesToTreeData = (cats, prefix = '0', parentPath = []) => {
  return cats.map((cat, index) => {
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

const handleNodeSelect = (node) => {
  const categoryId = node.data.id
  selectedCategory.value = node.data
  isSelected.value = true
  emit('select-category', node.data)

  filterProductsByCategory(categoryId)

  if (node.children && node.children.length > 0) {
    expandedKeys.value[node.key] = true
  }

  updateBreadcrumbs(node.data)
}

const handleNodeToggle = (node) => {
  expandedKeys.value[node.key] = !expandedKeys.value[node.key]
}

const filterProductsByCategory = (categoryId) => {
  if (!categoryId) {
    filteredProducts.value = products.value
  } else {
    const categoryIds = getCategoryWithChildrenIds(categoryId)
    filteredProducts.value = products.value.filter(p => categoryIds.includes(p.category_id))
  }
}

const getCategoryWithChildrenIds = (categoryId) => {
  const ids = [categoryId]

  const findChildrenIds = (cats) => {
    cats.forEach(cat => {
      if (cat.id === categoryId && cat.children) {
        collectChildrenIds(cat.children, ids)
      } else if (cat.children) {
        findChildrenIds(cat.children)
      }
    })
  }

  findChildrenIds(categories.value)
  return ids
}

const collectChildrenIds = (children, ids) => {
  children.forEach(child => {
    ids.push(child.id)
    if (child.children) {
      collectChildrenIds(child.children, ids)
    }
  })
}

const updateBreadcrumbs = (category) => {
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
const showCategoryTree = ref(true);

onMounted(() => {
  document.addEventListener("hideCategoryTree", () => {
    showCategoryTree.value = false
  })
  document.addEventListener("showCategoryTree", () => {
    showCategoryTree.value = true
  })

  document.addEventListener("hideTimeContainer", () => {
    showTimeContainer.value = false
  })
  document.addEventListener("showTimeContainer", () => {
    showTimeContainer.value = true
  })
})


</script>

<style scoped>
.category-container {
  top: 90px;
  left: 0;
  width: 620px;
  padding: 20px;
  overflow-y: auto;
  z-index: 1000;
}

.category-title {
  font-size: clamp(1.5rem, 5vw, 1.75rem);
}

.wrapper {
  display: block;
}

.contact-option {
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

.time-container {
  display: block;
}


@media (max-width: 768px) {
  .category-container {
    top: 50px;
    left: 1rem;
    width: 300px;
    height: auto; /* Dynamic height based on content */
    max-height: calc(100vh - 100px); /* Prevent overflow beyond viewport */
    overflow-y: auto;
    background-color: #ffffff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 1rem;
    z-index: 10;
  }

  .time-container {
    max-width: 400px;
    margin: 0 auto;
  }


}
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
}

</style>
