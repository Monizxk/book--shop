<script setup>
import NavBar from './components/NavBar.vue'
import Menu from './components/Menu.vue';
import CategoryTree from "./components/CategoryTree.vue";
import {onMounted, ref, defineExpose} from "vue";



</script>

<template>
  <NavBar/>
  <div class="container">
    <CategoryTree />
    <router-view></router-view>

  </div>
</template>

<style scoped>

.container {
  width: 100%;
  height: 100%;
  margin-top: 90px;
  display: flex;
  flex-direction: row;
}
@media (max-width: 768px) {
  .container {
    flex-direction: column;
  }
}

</style>
