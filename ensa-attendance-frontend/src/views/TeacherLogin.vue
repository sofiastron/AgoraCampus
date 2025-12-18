<template>
  <div class="login-page">
    <h2>Teacher Login</h2>
    <input v-model="email" placeholder="Email" />
    <input v-model="mot_de_passe" type="password" placeholder="Mot de passe" />
    <button @click="login">Se connecter</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const mot_de_passe = ref('')

const login = async () => {
  if (!email.value || !mot_de_passe.value) {
    alert('Veuillez remplir tous les champs !')
    return
  }

  try {
    const response = await axios.post('http://localhost:8000/api/login', {
      email: email.value,
      mot_de_passe: mot_de_passe.value,
    })

    localStorage.setItem('teacher', JSON.stringify(response.data))
    router.push('/teacher/dashboard')
  } catch (error) {
    alert(error.response.data.message || 'Erreur lors du login')
  }
}
</script>
