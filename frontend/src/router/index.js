import { createRouter, createWebHistory } from 'vue-router'

// Pages publiques
import Login from '../views/Login.vue'
import ForgotPassword from '../views/ForgotPassword.vue'
import ResetPassword from '../views/ResetPassword.vue'

// Layout
import DashboardLayout from '@/layouts/DashboardLayout.vue'

// Pages protégées
import Dashboard from '../views/Dashboard.vue'
import Courses from '@/views/Courses.vue'
import ScanQR from '@/views/ScanQR.vue'
import Profile from '@/views/Profile.vue'

const routes = [
  // ======================
  // Pages publiques
  // ======================
  {
    path: '/',
    name: 'Login',
    component: Login
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword
  },

  // ======================
  // Pages protégées
  // ======================
  {
    path: '/dashboard',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
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
        name: 'Courses',
        component: Courses
      }
    ]
  },

  {
    path: '/scan-qr',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'ScanQR',
        component: ScanQR
      }
    ]
  },

  {
    path: '/profile',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Profile',
        component: Profile
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// ======================
// Navigation Guard
// ======================
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Route protégée sans token → login
  if (to.meta.requiresAuth && !token) {
    return next('/')
  }

  // Sinon → continuer
  next()
})

export default router
