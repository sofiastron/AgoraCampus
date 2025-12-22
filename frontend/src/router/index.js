import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Courses from '@/views/Courses.vue'
const routes = [

  { path: '/', component: Login },
  { path: '/forgot-password', component: ForgotPassword },
  { path: '/reset-password', component: ResetPassword },

  {
    path: '/dashboard',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        component: Dashboard
      }
    ]
  },
   {
    path: '/courses',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        component: Courses
      }
    ]
  }
  
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})


router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    return next('/')
  }

  // Si connecter → pas retourner au login
  if ((to.path === '/' || to.path === '/login') && token) {
    return next('/dashboard')
  }

  next()
})

export default router
