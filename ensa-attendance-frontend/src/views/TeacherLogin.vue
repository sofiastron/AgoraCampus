<template>
  <div class="login-container">
    <form class="login-card" @submit.prevent="handleLogin">
      <h2>Connexion Enseignant</h2>

      <input
        type="email"
        placeholder="Email"
        v-model="email"
        required
      />

      <input
        type="password"
        placeholder="Mot de passe"
        v-model="password"
        required
      />

      <button type="submit" :disabled="auth.loading">
        {{ auth.loading ? 'Connexion...' : 'Se connecter' }}
      </button>

      <p v-if="auth.error" class="error">
        {{ auth.error }}
      </p>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const email = ref('')
const password = ref('')
const auth = useAuthStore()
const router = useRouter()

const handleLogin = async () => {
  await auth.login({
    email: email.value,
    password: password.value
  })

  if (auth.token) {
    router.push('/dashboard')
  }
}
</script>
