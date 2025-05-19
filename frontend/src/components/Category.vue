<template>
  <v-container fluid class=" main-container">
    <v-row justify="center">
      <v-col cols="12" class="">
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

.border {
  border: 1px solid #ccc;
}
.text {
  color: black;
}

.main-container {

}

.category-container {
  position: fixed;
  top: 80px;
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
  margin: 16px 1px 10px 10px;
}

.category-title {
  font-size: clamp(1.5rem, 5vw, 1.75rem);
}

.products-section {
  position: fixed;
  left: 332px; /* 300 + 16 + 16 */
  right: 16px;
  bottom: 16px;
  overflow-y: auto;
  padding: 16px;
  background-color: #f9f9f9;
  border-radius: 8px;
  box-sizing: border-box;
  top: 80px; /* Початкове значення, JS перезапише */
}

.product-spacer {
  background-color: white;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  margin-bottom: 24px;
  padding: 16px;
}

/* --- Мобільна адаптація --- */
@media (max-width: 768px) {
  .main-container {
    padding: 16px;
  }
  .category-container {
    position: relative;
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

  .products-section {
    position: relative;
    top: 50px !important;
    left: auto !important;
    right: auto !important;
    bottom: auto !important;
    width: 100%;
    height: auto;
    overflow-y: visible;
    padding: 12px;
  }
  .product-spacer {

  }
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
  background-color: #1976d2;
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
  background-color: #1565c0;
}

/* Adjust grid layout for better book display */
@media (min-width: 1264px) {
  .book-product-container {
    padding: 0 15px;
    display: flex;
    flex-wrap: wrap;      /* дозволяє карточкам переноситись */
    gap: 15px;            /* відступ між карточками */
    justify-content: flex-start; /* або center — залежно від дизайну */
  }

  .col-lg-3 {
    flex: 0 0 25%;
    max-width: 25%;
    flex-basis: 25%;
  }
}

/* Tablet view */
@media (min-width: 601px) and (max-width: 1263px) {
  .book-image {
    height: 280px;
  }

  .book-cover {
    max-height: 260px;
  }
}

/* Mobile view */
@media (max-width: 600px) {
  .book-product {
    flex-direction: row;
    height: 180px; /* Increased height for mobile */
  }

  .book-image {
    width: 45%;
    height: 100%;
    padding: 8px;
    min-width: 130px;
  }

  .book-cover {
    height: 100%;
    max-height: 160px;
    width: auto;
  }

  .book-details {
    width: 55%;
    padding: 12px;
  }

  .book-title {
    font-size: 14px;
    margin-bottom: 10px;
    min-height: auto;
  }

  .book-price-container {
    margin-bottom: 10px;
    flex-direction: column;
    align-items: flex-start;
  }

  .book-price {
    margin-bottom: 4px;
  }

  .book-stock {
    font-size: 12px;
  }

  .buy-button {
    padding: 8px 12px;
    font-size: 14px;
  }

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

  .product-spacer {
    padding: 12px;
  }
}

@media (max-width: 600px) {
  .book-product {
    flex-direction: row;
    height: auto;
    min-height: 180px;
    width: 100%;  /* Важливо */
    box-sizing: border-box;
  }

  .book-details {
    width: 55%;
    padding: 12px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-sizing: border-box;
  }

  .buy-button {
    width: 100%;  /* повна ширина у межах контейнера */
    padding: 8px 12px;
    font-size: 14px;
    margin: 0;
    box-sizing: border-box;
  }

  .book-image {
    width: 45%;
    height: 100%;
    padding: 8px;
    min-width: 130px;
    box-sizing: border-box;
  }
}


</style>