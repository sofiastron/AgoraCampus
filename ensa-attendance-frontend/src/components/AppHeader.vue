<template>
  <header class="app-header">
    <div class="right">
      <span class="user">
        {{ nomEnseignant }}
      </span>
      <button @click="logout">Déconnexion</button>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const nomEnseignant = computed(() => {
  return auth.user?.nom || 'Enseignant'
})

const logout = () => {
  auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-header {
  height: 60px;
  margin-left: 219px;
  background:whitesmoke;
  color: #1f2937;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding: 0 20px;
  border-bottom: 1px solid #e5e7eb;
}

.right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user {
  font-weight: 600;
  color: #3730a3;
}

button {
  background: #5d00ff;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
}
</style>
