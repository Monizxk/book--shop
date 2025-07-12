<template>
  <div class="wrapper">
    <v-container class="category-container" v-show="showCategoryTree" :style="{ maxWidth: '300px' }">
      <!-- Mobile Search Bar -->
      <div class="mobile-search-container">
        <input
            v-model="searchQuery"
            @input="handleSearch"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Пошук книг..."
            class="mobile-search-input"
        />
        <button @click="handleSearch" type="button">
          <i class="fas fa-search"></i> Пошук
        </button>
      </div>

      <div class="pa-4 text-center">
        <router-link to="/category" @click="handleLogoClick">
          <h2 class="category-title">Каталог</h2>
        </router-link>

        <Tree
            v-model:selectionKeys="selectedKey"
            v-model:expandedKeys="expandedKeys"
            :value="categoryTree"
            selectionMode="single"
            class="custom-tree"
            @node-select="handleNodeSelect"
            @node-toggle="handleNodeToggle"
        />
      </div>
    </v-container>

    <v-container class="time-container" v-show="showTimeContainer">
      <div class="contact-option">
        <div class="option-header">
          <h3>Графік роботи</h3>
        </div>
        <div class="info-card">
          <ul v-if="!isLoadingWorkingHours">
            <li v-for="(item, idx) in workingHours" :key="idx">
              <strong>{{ item.label }}</strong> {{ item.hours }}
            </li>
          </ul>
          <ul v-else>
            <li><div class="skeleton" style="width: 100%; height: 20px; margin-bottom: 10px;"></div></li>
            <li><div class="skeleton" style="width: 100%; height: 20px; margin-bottom: 10px;"></div></li>
            <li><div class="skeleton" style="width: 100%; height: 20px;"></div></li>
          </ul>
        </div>
      </div>
    </v-container>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, defineProps, defineEmits, onUnmounted } from 'vue'
import Tree from 'primevue/tree';
import router from "../router.js";
import {useRoute} from "vue-router";
import { useCategories } from '../composables/useCategories'

const emit = defineEmits(['select-category'])
const { expandedKeys, collapseCategories } = useCategories()
const route = useRoute()
const showTimeContainer = ref(true)
const isExpanded = ref(false)
const isSelected = ref(false)
const categories = ref([])
const categoryTree = ref([])
const selectedCategory = ref(null)
const products = ref([])
const filteredProducts = ref([])
const breadcrumbItems = ref([])
const workingHours = ref([])
const isLoadingWorkingHours = ref(true)
const selectedKey = ref({});


const fetchCategories = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/categories')
    const data = await response.json()
    categories.value = data
    categoryTree.value = convertCategoriesToTreeData(data)

    if (route.path === '/') {
      expandedKeys.value = {}
    } else {
      expandedKeys.value = {}
      categoryTree.value.forEach(node => {
        expandedKeys.value[node.key] = true
      })
    }
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
  const location = window.location.href

  if (!location.includes("category")) {
    router.push(`/category/${node.data.id}`)
    handleNodeSelect()
    window.location.reload()
  }

  console.log('Selected category:', node.data)

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
    const pathItems = category.path.map((item, index, arr) => ({
      title: item.name,
      disabled: index === arr.length - 1,
      href: index === arr.length - 1 ? '' : `/catalog/${item.id}`
    }))

    breadcrumbItems.value = [
      { title: 'Головна', disabled: false, href: '/' },
      { title: 'Каталог', disabled: false, href: '/category' },
      ...pathItems
    ]
  }
}
const showCategoryTree = ref(true);

async function fetchWorkingHours() {
  isLoadingWorkingHours.value = true
  try {
    const response = await fetch('http://localhost:8000/api/settings')
    const data = await response.json()
    workingHours.value = []
    if (data['working_hours.weekdays.enabled'] === '1') {
      workingHours.value.push({
        label: data['working_hours.weekdays.label'] || 'ПН, ВТ, СР, ЧТ, ПТ',
        hours: data['working_hours.weekdays.hours'] || 'з 9:00 до 18:00'
      })
    }
    if (data['working_hours.saturday.enabled'] === '1') {
      workingHours.value.push({
        label: data['working_hours.saturday.label'] || 'Субота',
        hours: data['working_hours.saturday.hours'] || 'з 10:00 до 15:00'
      })
    }
    if (data['working_hours.sunday.enabled'] === '1') {
      workingHours.value.push({
        label: data['working_hours.sunday.label'] || 'Неділя',
        hours: data['working_hours.sunday.hours'] || 'Вихідний'
      })
    }
  } catch (error) {
    console.error('Помилка при завантаженні графіку роботи:', error)
  } finally {
    isLoadingWorkingHours.value = false
  }
}

function handleSearch() {
  if (searchQuery.value.trim() !== '') {
    router.push({ name: 'SearchResults', query: { q: searchQuery.value } })
  }
}

const handleLogoClick = () => {
  expandedKeys.value = {}
}


onMounted(() => {
  fetchCategories()
  fetchWorkingHours()

  watch(() => route.path, (newPath) => {
    if (newPath === '/') {
      expandedKeys.value = {}
    }
  })

  const hideCategoryListener = () => {
    console.log('hideCategoryTree triggered')
    showCategoryTree.value = false
  }
  const showCategoryListener = () => {
    console.log('showCategoryTree triggered')
    showCategoryTree.value = true
  }
  const hideTimeListener = () => {
    console.log('hideTimeContainer triggered')
    showTimeContainer.value = false
    console.log('showTimeContainer:', showTimeContainer.value)
  }
  const showTimeListener = () => {
    console.log('showTimeContainer triggered')
    showTimeContainer.value = true
    console.log('showTimeContainer:', showTimeContainer.value)
  }

  document.addEventListener("hideCategoryTree", hideCategoryListener)
  document.addEventListener("showCategoryTree", showCategoryListener)
  document.addEventListener("hideTimeContainer", hideTimeListener)
  document.addEventListener("showTimeContainer", showTimeListener)

  onUnmounted(() => {
    document.removeEventListener("hideCategoryTree", hideCategoryListener)
    document.removeEventListener("showCategoryTree", showCategoryListener)
    document.removeEventListener("hideTimeContainer", hideTimeListener)
    document.removeEventListener("showTimeContainer", showTimeListener)
  })
})
</script>

<style>
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 4px;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.v-breadcrumbs {
  color: black;
}

/* Контент вузла */
.custom-tree .p-tree-node-content {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  margin: 2px 0;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  position: relative;
  flex-direction: row-reverse;
}

/* Ховер ефект */
.custom-tree .p-tree-node-content:hover {
  background-color: #f5f5f5 !important;
  border-left-color: #90caf9 !important;
}

/* Активна/вибрана категорія */
.custom-tree .p-tree-node-content.p-tree-node-selected {
  background-color: #e3f2fd !important;
  color: #1976d2 !important;
  font-weight: 600 !important;
  border-left-color: #2196f3 !important;
  border-left-width: 4px !important;
}

/* Кнопка розгортання/згортання */
.custom-tree .p-tree-node-toggle-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  margin-right: 8px;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 50%;
  transition: background-color 0.2s ease;
}

.custom-tree .p-tree-node-toggle-button:hover {
  background-color: #e0e0e0 !important;
}

.custom-tree .p-tree-node-toggle-icon {
  width: 12px;
  height: 12px;
  color: #757575;
}

/* Лейбл вузла */
.custom-tree .p-tree-node-label {
  flex: 1;
  padding: 0 4px;
  font-size: 14px;
  line-height: 1.4;
  text-decoration: none;
  color: inherit;
}

/* Іконка вузла */
.custom-tree .p-tree-node-icon {
  margin-right: 8px;
  width: 16px;
  height: 16px;
}

/* Відступи для вкладених категорій (зменшено) */
.custom-tree .p-tree-node {
  padding-left: 0;
}

.custom-tree .p-tree-node { padding-left: 0px; }

.custom-tree .p-tree-node-children .p-tree-node > .p-tree-node-content {
  padding-left: 0;
}

.custom-tree .p-tree-node-children .p-tree-node-children .p-tree-node > .p-tree-node-content {
  padding-left: 56px;
}

.custom-tree .p-tree-node-children .p-tree-node-children .p-tree-node-children .p-tree-node > .p-tree-node-content {
  padding-left: 80px;
}

.custom-tree .p-tree-node-children .p-tree-node-children .p-tree-node-children .p-tree-node-children .p-tree-node > .p-tree-node-content {
  padding-left: 104px;
}

/* Візуальні лінії для показу ієрархії */
.custom-tree .p-tree-node-content::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(
      to bottom,
      transparent 0%,
      #bdbdbd 20%,
      #bdbdbd 80%,
      transparent 100%
  );
  opacity: 0.3;
}

/* Анімація при розгортанні/згортанні */
.custom-tree .p-tree-node-children {
  transition: all 0.3s ease;
}

/* Стилі для фокусу (доступність) */
.custom-tree .p-tree-node-content:focus {
  outline: 2px solid #2196f3 !important;
  outline-offset: 2px !important;
}

/* Додаткові стилі для кращого вигляду */
.custom-tree .p-tree-node-content.p-tree-node-selected .p-tree-node-label {
  color: inherit !important;
}

/* Responsive стилі */
@media (max-width: 768px) {
  .custom-tree {
    max-width: 100%;
  }

  .custom-tree .p-tree-node-content {
    padding: 10px 8px;
  }

  .custom-tree .p-tree-node[aria-level="2"] > .p-tree-node-content {
    padding-left: 20px; /* Зменшено з 24px */
  }

  .custom-tree .p-tree-node[aria-level="3"] > .p-tree-node-content {
    padding-left: 32px; /* Зменшено з 40px */
  }

  .custom-tree .p-tree-node[aria-level="4"] > .p-tree-node-content {
    padding-left: 44px; /* Зменшено з 56px */
  }
}

.category-container,
.contact-option {
  width: 300px;
  position: sticky;
  background: #fafafa;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  box-shadow: none;
  transition: all 0.3s ease;
  z-index: 10;
  color: black;
}

.p-tree .p-treenode {
  padding-left: 0.5rem;
}

.p-tree .p-treenode .p-treenode-children > .p-treenode {
  padding-left: 1rem; /* Зменшено з 1.5rem */
}

.p-tree .p-treenode .p-treenode-children > .p-treenode .p-treenode-children > .p-treenode {
  padding-left: 1.5rem; /* Зменшено з 2.5rem */
}

.p-treenode-content.p-highlight {
  background-color: #e0f3ff; /* Світло-синій фон */
  color: #004a77; /* Текст */
  font-weight: bold;
  border-radius: 6px;
}

.category-container,
.contact-option {
  width: 300px;
  position: sticky;
  background: #fafafa;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  box-shadow: none;
  transition: all 0.3s ease;
  z-index: 10;
  color: black;
}

.p-tree .p-treenode {
  padding-left: 0.5rem;
}

.p-tree .p-treenode .p-treenode-children > .p-treenode {
  padding-left: 1.5rem;
}

.p-tree .p-treenode .p-treenode-children > .p-treenode .p-treenode-children > .p-treenode {
  padding-left: 2.5rem;
}

.p-treenode-content.p-highlight {
  background-color: #e0f3ff; /* Світло-синій фон */
  color: #004a77; /* Текст */
  font-weight: bold;
  border-radius: 6px;
}


.category-title {
  font-size: clamp(1.5rem, 5vw, 1.75rem);
}

.wrapper {
  display: block;
}


.contact-option:hover {
  position: sticky;
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

@media (max-width: 1200px) {
  .category-container,
  .contact-option {
    width: 300px;
    position: sticky;
  }
}


@media (max-width: 768px) {
  .category-container,
  .contact-option {
    position: relative;
    top: auto;
    left: auto;
    margin: 0 auto;
    width: 100%;
    max-width: 350px;
    height: auto;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
  }

  .main-title {
    font-size: 2rem;
  }

  .main-description {
    font-size: 1rem;
  }

  .time-container {
    max-width: 400px;
    margin: 0 auto;
  }

  .product-spacer {
    width: 100%;
    max-width: 500px;
  }
}

@media (min-width: 380px) and (max-width: 768px) {
  .category-container,
  .contact-option,
  .info-card {
    max-width: 350px;
  }
}

@media (min-width: 320px) and (max-width: 380px) {
  .category-container,
  .contact-option,
  .info-card {
    max-width: 260px;
  }
}
</style>
