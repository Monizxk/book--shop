<template>
  <v-container fluid class="my-5 main-container">
    <v-row justify="center">
      <v-col cols="12" class="top-spacer px-4 py-6">
        <p class="text text-subtitle-1 text-center">
          Тут ви можете знайти книги на будь-який смак. Оберіть категорію або перегляньте всі товари нижче.
        </p>
      </v-col>
    </v-row>

    <v-row justify="center">
      <v-col cols="12">
        <v-row>
          <v-col cols="12" sm="4" md="3" class="border category-container">
            <div class="pa-4 text-center">
              <h2 class="category-title">Категорії</h2>
              <Tree 
                :value="categoryTree" 
                selectionMode="single"
                @node-select="handleNodeSelect"
                :expandedKeys="expandedKeys"
                @node-toggle="handleNodeToggle"
              />
            </div>
          </v-col>

          <!-- Товари -->
          <v-col cols="12" sm="8" md="9" class="products-section">
            <div class="product-spacer px-4 py-5">
              <h2 class="mb-2">Наші товари</h2>

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
            </div>

            <v-row>
              <v-col 
                v-for="(product, index) in filteredProducts" 
                :key="index" 
                cols="12" 
                sm="6" 
                md="4" 
                lg="3" 
                class="border product-container"
              >
                <div class="product pa-4 text-center d-flex flex-column justify-center">
                  <img :src="product.images[0]" alt="Зображення товару" />
                  <h3 class="product-title">{{ product.title }}</h3>
                  <p>{{ product.price }} ₴</p>
                  <p class="product-description">{{ product.description }}</p>
                  <button @click="addToCart(product)">Купити</button>
                </div>
              </v-col>
              <v-col cols="12" v-if="filteredProducts.length === 0" class="text-center py-5">
                <p>У цій категорії немає товарів</p>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
import Tree from 'primevue/tree';
import { cart } from '../api/cart.js'


export default {
  name: 'GridLayout',
  components: { Tree },

  data() {
    return {
      categories: [],
      categoryTree: [],
      selectedCategory: null,
      products: [],
      filteredProducts: [],
      expandedKeys: {},
      breadcrumbItems: [
        { title: 'Головна', disabled: false, href: '/' },
        { title: 'Каталог', disabled: false, href: '/catalog' }
      ]
    };
  },

  watch: {
    selectedCategory(newCategory) {
      if (newCategory) {
        this.updateBreadcrumbs(newCategory);
      } else {
        this.breadcrumbItems = [
          { title: 'Головна', disabled: false, href: '/' },
          { title: 'Каталог', disabled: false, href: '/catalog' }
        ];
      }
    }
  },

  methods: {

    getImageUrl(imagePath) {
    if (!imagePath) return '';
    return `http://localhost:8000/storage/${imagePath}`;
    },

    addToCart(product) {
      cart.add(product)
      alert('Товар додано до кошика')
    },
    async fetchCategories() {
      try {
        const response = await fetch('http://localhost:8000/api/categories');
        const data = await response.json();
        this.categories = data;
        this.categoryTree = this.convertCategoriesToTreeData(data);
      } catch (error) {
        console.error('Помилка при завантаженні категорій:', error);
      }
    },

    async fetchProducts() {
      try {
        const response = await fetch('http://localhost:8000/api/products');
        const data = await response.json();
        this.products = data;
        this.filteredProducts = data;
      } catch (error) {
        console.error('Помилка при завантаженні продуктів:', error);
      }
    },

    convertCategoriesToTreeData(categories, prefix = '0', parentPath = []) {
      return categories.map((cat, index) => {
        const currentKey = `${prefix}-${index}`;
        const currentPath = [...parentPath, cat.name];
        
        return {
          key: currentKey,
          label: cat.name,
          data: { 
            id: cat.id,
            name: cat.name,
            path: currentPath
          },
          children: cat.children ? this.convertCategoriesToTreeData(cat.children, currentKey, currentPath) : []
        };
      });
    },

    handleNodeSelect(node) {
      const categoryId = node.data.id;
      this.selectedCategory = node.data;
      this.filterProductsByCategory(categoryId);
      
      // При виборі вузла також розгортаємо його
      if (node.children && node.children.length > 0) {
        this.expandedKeys[node.key] = true;
      }
    },

    handleNodeToggle(node) {
      // Обробка розгортання/згортання вузлів
      this.expandedKeys[node.key] = !this.expandedKeys[node.key];
    },

    filterProductsByCategory(categoryId) {
      if (!categoryId) {
        // Якщо категорія не вибрана, показуємо всі товари
        this.filteredProducts = this.products;
      } else {
        // Отримуємо список всіх ID категорій, включаючи підкатегорії
        const categoryIds = this.getCategoryWithChildrenIds(categoryId);
        
        // Фільтруємо товари за всіма ID категорій
        this.filteredProducts = this.products.filter(p => categoryIds.includes(p.category_id));
      }
    },
    
    // Рекурсивно збираємо ID всіх підкатегорій
    getCategoryWithChildrenIds(categoryId) {
      const ids = [categoryId];
      
      const findChildrenIds = (categories) => {
        categories.forEach(category => {
          if (category.id === categoryId) {
            if (category.children) {
              this.collectChildrenIds(category.children, ids);
            }
          } else if (category.children) {
            findChildrenIds(category.children);
          }
        });
      };
      
      findChildrenIds(this.categories);
      return ids;
    },
    
    collectChildrenIds(children, ids) {
      children.forEach(child => {
        ids.push(child.id);
        if (child.children) {
          this.collectChildrenIds(child.children, ids);
        }
      });
    },

    updateBreadcrumbs(category) {
      if (category && category.path) {
        const pathItems = category.path.map((name, index, arr) => {
          return {
            title: name,
            disabled: index === arr.length - 1, 
            href: index === arr.length - 1 ? '' : `/catalog/${name.toLowerCase()}`
          };
        });
        
        this.breadcrumbItems = [
          { title: 'Головна', disabled: false, href: '/' },
          { title: 'Каталог', disabled: false, href: '/catalog' },
          ...pathItems
        ];
      }
    },

    resetFilter() {
      this.selectedCategory = null;
      this.filteredProducts = this.products;
      this.breadcrumbItems = [
        { title: 'Головна', disabled: false, href: '/' },
        { title: 'Каталог', disabled: false, href: '/catalog' }
      ];
    }
  },

  mounted() {
    this.fetchCategories();
    this.fetchProducts();
  }
}
</script>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

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

.border {
  border: 1px solid #ccc;
}
.text {
  color: black;
}

.main-container {
  max-width: none;
  width: 100%;
  padding: 0 16px;
}

.category-container {
  max-height: 500px;
  overflow-y: auto;
  margin-bottom: 16px;
}

.category-title {
  font-size: clamp(1.5rem, 5vw, 1.75rem);
}

.product-container {
  min-height: 250px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  margin-bottom: 16px;
}

.product-title {
  font-size: clamp(1rem, 4vw, 1.25rem);
  margin-bottom: 8px;
}

.product-description {
  font-size: clamp(0.875rem, 3vw, 1rem);
  margin-bottom: 12px;
}

.products-section {
  padding-top: 16px;
}

.product-spacer {
  background-color: #f9f9f9;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 24px;
  padding: 16px;
}

@media (max-width: 600px) {
  .main-container {
    padding: 0 8px;
  }

  .top-spacer {
    padding: 16px 8px;
  }

  .category-container {
    max-height: 300px;
    margin-bottom: 12px;
  }

  .product-container {
    min-height: 200px;
  }

  .product-spacer {
    padding: 12px;
  }
}
</style>  