import { createRouter, createWebHistory } from 'vue-router'

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
  { name: 'Category', path: '/category/:categoryId?', component: Category, props: true },
  { path: '/delivery', component: Delivery },
  { path: '/contact', component: Contact },
  { path: '/payment', component: Payment },
  {
    path: '/search',
    name: 'SearchResults',
    component: () => import('../src/components/SearchResults.vue')
  }

]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router