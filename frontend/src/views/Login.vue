<template>
  <div class="login-page">
    <h1>Connexion</h1>
    <form @submit.prevent="login">
      <input type="email" v-model="email" placeholder="Email" required />
      <input type="password" v-model="password" placeholder="Mot de passe" required />
      <button type="submit">Se connecter</button>
    </form>
    <p v-if="error" style="color:red">{{ error }}</p>
  </div>
</template>

<script>
import api from '../axios';

export default {
  name: 'Login',
  data() {
    return {
      email: '',
      password: '',
      error: '',
    };
  },
  methods: {
    async login() {
      try {
        const response = await api.post('/login', {
          email: this.email,
          password: this.password,
        });

        localStorage.setItem('token', response.data.token);

        this.$router.push('/etudiant/dashboard');
      } catch (err) {
        this.error = err.response?.data?.message || 'Erreur de connexion';
      }
    },
  },
};
</script>

<style scoped>
.login-page {
  max-width: 400px;
  margin: 100px auto;
  text-align: center;
}
</style>