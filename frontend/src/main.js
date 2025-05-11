import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router.js'
import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import PrimeVue from 'primevue/config';

const vuetify = createVuetify({
    components,
    directives,
  })

const app = createApp(App)
app.use(router)
app.mount('#app')
app.use(vuetify)
app.use(PrimeVue);


