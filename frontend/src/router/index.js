import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'

const routes = [
  // Pages publiques
  { path: '/', component: Login },
  { path: '/forgot-password', component: ForgotPassword },
  { path: '/reset-password', component: ResetPassword },

  // Pages avec layout
  {
    path: '/',
    component: DashboardLayout,
    children: [
      {
        path: 'dashboard',
        component: Dashboard
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
