<template>
  <aside class="sidebar">
    <h2 class="logo">AgoraCampus</h2>

    <nav>
      <router-link to="/dashboard"> Dashboard</router-link>
      <router-link to="/courses"> Mes cours</router-link>
      <router-link to="/scan-qr"> Scanner QR</router-link>
      <router-link to="/calendrier"> Calendrier</router-link>
      <router-link to="/messages"> Messages</router-link>
      <router-link to="/profile">Profil</router-link>
    </nav>
  </aside>
</template>

<script setup>
</script>

<style scoped>
.sidebar {
  width: 220px;
  background: #4f46e5;
  color: white;
  min-height: 100vh;
  padding: 20px;
}

.logo {
  margin-bottom: 30px;
  font-size: 20px;
  font-weight: bold;
}

nav {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

a {
  color: white;
  text-decoration: none;
  padding: 10px;
  border-radius: 6px;
}

a.router-link-active {
  background: rgba(255, 255, 255, 0.2);
}
</style>
