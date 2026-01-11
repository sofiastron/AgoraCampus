

import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'



import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';
// Configurer axios SANS withCredentials
axios.defaults.baseURL = 'http://localhost:8000'
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
// IMPORTANT: Désactiver withCredentials
axios.defaults.withCredentials = false
const app = createApp(App)
app.use(Toast, {
  position: 'top-right',
  timeout: 3000,
  closeOnClick: true,
  pauseOnFocusLoss: true,
  pauseOnHover: true,
  draggable: true,
  draggablePercent: 0.6,
  showCloseButtonOnHover: false,
  hideProgressBar: true,
  closeButton: 'button',
  icon: true,
  rtl: false
});


app.use(router)

app.mount('#app')