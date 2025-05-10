import { createMemoryHistory, createRouter } from 'vue-router'

import Menu from './components/Menu.vue'
import Cart from './components/Cart.vue'
import Order from './components/Order.vue'

const routes = [
  { path: '/', component: Menu },
  { path: '/cart', component: Cart },
    { path: '/order', component: Order },
]

const router = createRouter({
  history: createMemoryHistory(),
  routes,
})

export default router