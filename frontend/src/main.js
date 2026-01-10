/* import { createApp } from 'vue'
import App from './App.vue'
import router from './router'



createApp(App).use(router).mount('#app')
*/
import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import axios from 'axios'
import "./assets/main.css";


// Configuration Axios pour Laravel
axios.defaults.baseURL = 'http://localhost:8000/api'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Intercepteur pour les tokens
axios.interceptors.request.use(config => {
    const token = localStorage.getItem('auth_token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

const app = createApp(App)
app.use(router)
app.config.globalProperties.$axios = axios
app.mount('#app')