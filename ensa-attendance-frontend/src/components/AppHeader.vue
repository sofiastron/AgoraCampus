<template>
  <header class="app-header">
    <h2>AGORA CAMPUS</h2>

    <div class="right">
      <span class="user">
        {{ auth.user?.nom || 'Enseignant' }}
      </span>
      <button @click="logout">Déconnexion</button>
    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const logout = () => {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-header {
  height: 60px;
  background: #1e293b;
  color: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
}

.right {
  display: flex;
  align-items: center;
  gap: 15px;
}

button {
  background: #ef4444;
  color: white;
  border: none;
  padding: 6px 12px;
  cursor: pointer;
}
</style>
