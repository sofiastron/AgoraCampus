<template>
  <div class="forgot-password-container">
    <h2>Mot de passe oublié</h2>

    <form @submit.prevent="sendResetLink">
      <input
        type="email"
        v-model="email"
        placeholder="Email"
        required
      />

      <button type="submit">Envoyer le lien</button>
    </form>

    <p v-if="success" style="color: green">{{ success }}</p>
    <p v-if="error" style="color: red">{{ error }}</p>

    <router-link to="/">Retour à la connexion</router-link>
  </div>
</template>

<script>
import api from '../axios';

export default {
  name: 'ForgotPassword',
  data() {
    return {
      email: '',
      success: '',
      error: '',
    };
  },
  methods: {
    async sendResetLink() {
      this.success = '';
      this.error = '';

      try {
        const response = await api.post('/forgot-password', {
          email: this.email
        });

        this.success = response.data.message;
      } catch (err) {
        this.error =
          err.response?.data?.message || 'Erreur lors de l’envoi';
      }
    }
  }
};
</script>

<style scoped>
.forgot-password-container {
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
