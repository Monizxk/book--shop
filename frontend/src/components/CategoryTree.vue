<template>
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
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits } from 'vue'
import Tree from 'primevue/tree';


const emit = defineEmits(['select-category'])


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
  fetchCategories()
  document.addEventListener("hideCategoryTree", () => {
    showCategoryTree.value = false
  })
  document.addEventListener("showCategoryTree", () => {
    showCategoryTree.value = true
  })
})


</script>

<style scoped>
.category-container {
  top: 90px;
  left: 0;
  width: 620px;
  height: 100vh;
  padding: 20px;
  overflow-y: auto;
  z-index: 1000;
}

.category-title {
  font-size: clamp(1.5rem, 5vw, 1.75rem);
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
}


</style>
