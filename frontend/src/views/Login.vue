<template>
  <div class="login-wrapper">
    <div class="login-card">
      <h2>Connexion</h2>

      <form @submit.prevent="login">
        <div class="input-group">
          <input
            type="email"
            v-model="email"
            placeholder="Email"
            required
          />
        </div>

        <div class="input-group">
          <input
            type="password"
            v-model="password"
            placeholder="Mot de passe"
            required
          />
        </div>

        <button type="submit">{{ loading ? 'Connexion...' : 'Se connecter' }}</button>
      </form>

      <p class="forgot">
        <router-link to="/forgot-password">Mot de passe oublié ?</router-link>
      </p>

      <p v-if="success" class="success-msg">{{ success }}</p>
      <p v-if="error" class="error-msg">{{ error }}</p>
    </div>
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
      loading: false,
    };
  },
  methods: {
    async login() {
      this.error = '';
      this.success = '';
      this.loading = true;

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
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Fond dégradé */
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea, #764ba2);
  font-family: 'Segoe UI', sans-serif;
}

/* Card login */
.login-card {
  background: #fff;
  padding: 40px 30px;
  border-radius: 20px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.2);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.login-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 25px 60px rgba(0,0,0,0.25);
}

.login-card h2 {
  margin-bottom: 30px;
  color: #333;
  font-weight: 700;
}

/* Inputs stylisés */
.input-group {
  margin-bottom: 20px;
}

.input-group input {
  width: 100%;
  padding: 12px 15px;
  border-radius: 12px;
  border: 1px solid #ccc;
  font-size: 16px;
  transition: border 0.3s ease, box-shadow 0.3s ease;
}

.input-group input:focus {
  border-color: #667eea;
  box-shadow: 0 0 8px rgba(102, 126, 234, 0.4);
  outline: none;
}

/* Bouton */
button[type="submit"] {
  width: 100%;
  padding: 12px 0;
  background: #667eea;
  color: #fff;
  border: none;
  border-radius: 15px;
  font-weight: 600;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.3s ease, transform 0.2s ease;
}

button[type="submit"]:hover {
  background: #5563c1;
  transform: translateY(-2px);
}

button[type="submit"]:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Lien mot de passe oublié */
.forgot {
  margin-top: 15px;
}

.forgot a {
  color: #667eea;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.forgot a:hover {
  color: #5563c1;
}

/* Messages succès / erreur */
.success-msg {
  color: #38a169;
  margin-top: 15px;
  font-weight: 500;
}

.error-msg {
  color: #c53030;
  margin-top: 15px;
  font-weight: 500;
}
</style>
