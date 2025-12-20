<template>
  <div class="reset-password-container">
    <h2>Réinitialiser le mot de passe</h2>

    <form @submit.prevent="resetPassword">
      <input
        type="password"
        v-model="password"
        placeholder="Nouveau mot de passe"
        required
      />

      <input
        type="password"
        v-model="password_confirmation"
        placeholder="Confirmer mot de passe"
        required
      />

      <button type="submit">Réinitialiser</button>
    </form>

    <p v-if="success" style="color: green">{{ success }}</p>
    <p v-if="error" style="color: red">{{ error }}</p>

    <router-link to="/">Retour à la connexion</router-link>
  </div>
</template>

<script>
import api from '../axios';

export default {
  name: 'ResetPassword',
  data() {
    return {
      password: '',
      password_confirmation: '',
      token: '',
      email: '',
      success: '',
      error: '',
    };
  },
  mounted() {
    // récupérer token + email depuis l'URL
    this.token = this.$route.query.token;
    this.email = this.$route.query.email;
  },
  methods: {
    async resetPassword() {
      this.success = '';
      this.error = '';

      try {
        const response = await api.post('/reset-password', {
          token: this.token,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation
        });

        this.success = response.data.message;

      } catch (err) {
        this.error =
          err.response?.data?.message || 'Erreur lors de la réinitialisation';
      }
    }
  }
};
</script>

<style scoped>
.reset-password-container {
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
