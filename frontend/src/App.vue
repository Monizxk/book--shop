<script setup>
import { ref } from 'vue'
import CategoryTree from './components/CategoryTree.vue'
import NavBar from './components/NavBar.vue'

const selectedCategory = ref(null)

const onCategorySelected = (category) => {
  selectedCategory.value = category
}
</script>

<template>
  <NavBar/>
  <div class="container">
    <CategoryTree @select-category="onCategorySelected" />
    <router-view :selected-category="selectedCategory" />
  </div>
</template>

<style scoped>

.container {
  width: 100%;
  height: 100%;
  margin-top: 90px;
  display: flex;
  flex-direction: row;
  position: relative;
  overflow-y: auto;
  background-color: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  border: 1px solid #d1d5db;
  border-radius: 8px;
  padding: 1rem;
  z-index: 10;
}
@media (max-width: 768px) {
  .container {
    flex-direction: column;
  }
}

</style>
