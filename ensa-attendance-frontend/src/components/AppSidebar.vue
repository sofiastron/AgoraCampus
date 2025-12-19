<template>
  <aside class="sidebar">
    <h3 class="logo">AGORA</h3>

    <nav>
      <RouterLink to="/dashboard">🏠 Dashboard</RouterLink>
      <RouterLink to="/modules">📚 Modules</RouterLink>
      <RouterLink to="/seances">📅 Séances</RouterLink>
      <RouterLink to="/annonces">📢 Annonces</RouterLink>
      <RouterLink to="/documents">📂 Documents</RouterLink>
    </nav>
  </aside>
</template>

<script setup>
import { RouterLink } from 'vue-router'
</script>

<style scoped>
.sidebar {
  width: 220px;
  height: 100vh;
  background: #0f172a;
  color: white;
  padding: 20px;
}

.logo {
  margin-bottom: 30px;
  text-align: center;
}

nav {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

a {
  color: white;
  text-decoration: none;
}

a.router-link-active {
  font-weight: bold;
  color: #38bdf8;
}
</style>
