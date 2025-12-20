<template>
  <div class="login-container">
    <h2>Connexion</h2>

    <form @submit.prevent="login">
      <input
        type="email"
        v-model="email"
        placeholder="Email"
        required
      />
      <input
        type="password"
        v-model="password"
        placeholder="Mot de passe"
        required
      />

      <button type="submit">Se connecter</button>
      
    </form>

    <p v-if="success" style="color: green">{{ success }}</p>
    <p v-if="error" style="color: red">{{ error }}</p>
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
      success: '',
      error: '',
    };
  },
  methods: {
    async login() {
      this.error = '';
      this.success = '';

      try {
        const response = await api.post('/login', {
          email: this.email,
          password: this.password,
        });
        localStorage.setItem('token', response.data.token);
        
        this.$router.push('/dashboard');

      } catch (err) {
        this.error =
          err.response?.data?.message || 'Erreur de connexion';
      }
    },
  },
};
</script>

<style scoped>
.login-container {
  max-width: 400px;
  margin: 100px auto;
  text-align: center;
}
input {
  display: block;
  width: 100%;
  margin-bottom: 10px;
}
</style>
