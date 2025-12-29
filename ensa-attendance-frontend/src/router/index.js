import { createRouter, createWebHistory } from 'vue-router'

import TeacherLogin from '@/views/TeacherLogin.vue'
import DashboardPage from '@/views/DashboardPage.vue'
import PresenceHome from '@/views/PresenceHome.vue'
import PresenceQRCode from '@/views/PresenceQRCode.vue'
import PresenceFacialRecognition from '@/views/PresenceFacialRecognition.vue'

import DocumentsAnnonces from '@/views/DocumentsAnnonces.vue'
import ListeEtudiants from '@/views/ListeEtudiants.vue'
import EmploiDuTemps from '@/views/EmploiDuTemps.vue'

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
        component: PresenceQRCode,
        meta: { requiresAuth: true }  
      },
      {
        path: 'facial',
        component: PresenceFacialRecognition,
        meta: { requiresAuth: true }
      }
    ]
  },

  {
    path: '/documents-annonces',
    component: DocumentsAnnonces,
    meta: { requiresAuth: true }
  },

  {
    path: '/liste-etudiants',
    component: ListeEtudiants,
    meta: { requiresAuth: true }
  },

  {
    path: '/emploi-du-temps',
    component: EmploiDuTemps,
    meta: { requiresAuth: true }
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
