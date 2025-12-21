import { createRouter, createWebHistory } from 'vue-router'

import TeacherLogin from '@/views/TeacherLogin.vue'
import DashboardPage from '@/views/DashboardPage.vue'
import PresenceHome from '@/views/PresenceHome.vue'
import PresenceQRCode from '@/views/PresenceQRCode.vue'
import PresenceFacialRecognition from '@/views/PresenceFacialRecognition.vue'

const routes = [
  { path: '/', redirect: '/login' },

  {
    path: '/login',
    component: TeacherLogin
  },

  {
    path: '/dashboard',
    component: DashboardPage,
    meta: { requiresAuth: true }
  },

  {
    path: '/presence',
    component: PresenceHome,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'qrcode',
        component: PresenceQRCode
      },
      {
        path: 'facial',
        component: PresenceFacialRecognition
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else {
    next()
  }
})

export default router
