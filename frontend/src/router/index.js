import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import users from '../views/users.vue'
import Planning from '@/views/Planning.vue'
import GestionEmplois from '@/views/GestionEmplois.vue' // Importez la nouvelle page

const routes = [
  { path: '/', component: Home },
  { path: '/users', component: users },
  { path: '/planning', component: Planning },
 
  // NOUVELLE ROUTE : Gestion des emplois
  { path: '/gestion-emplois', component: GestionEmplois },
   
  // Optionnel : Routes pour visualiser/modifier un emploi spécifique
  
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router