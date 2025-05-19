import { createMemoryHistory, createRouter } from 'vue-router'

import Menu from './components/Menu.vue'
import Cart from './components/Cart.vue'
import Order from './components/Order.vue'
import Checkout from './components/Checkout.vue'
import Category from './components/Category.vue'
import Delivery from "./components/Delivery.vue";
import Contact from "./components/Contact.vue";
import Payment from "./components/Payment.vue";


const routes = [
  { path: '/', component: Menu },
  { path: '/cart', component: Cart },
  { path: '/order', component: Order },
  { path: '/checkout', component: Checkout },
  { path: '/category', component: Category },
  { path: '/delivery', component: Delivery },
  { path: '/contact', component: Contact },
  { path: '/payment', component: Payment },
]

const router = createRouter({
  history: createMemoryHistory(),
  routes,
})

export default router