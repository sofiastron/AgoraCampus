import { createRouter, createWebHistory } from 'vue-router';
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';
import ForgotPassword from '../views/ForgotPassword.vue';
import ResetPassword from '../views/ResetPassword.vue';

const routes = [
  { path: '/', component: Login },
 
  { path: '/dashboard', component: Dashboard },
  { path: '/forgot-password', component: ForgotPassword },
  { path: '/reset-password', component: ResetPassword },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
