<template>
  <div class="dashboard-layout">
    <AppSidebar />

    <div class="dashboard-main">
      <AppHeader />

      <div class="dashboard-content">
        <h1>Dashboard</h1>

        <div class="stats-grid">
          <div class="stat-card">
            <h3>Modules</h3>
            <p>5</p>
          </div>

          <div class="stat-card">
            <h3>Séances</h3>
            <p>12</p>
          </div>

          <div class="stat-card">
            <h3>Présences</h3>
            <p>320</p>
          </div>
        </div>

        <div class="quick-actions">
          <button>➕ Nouvelle séance</button>
          <button>📢 Nouvelle annonce</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import AppSidebar from '../components/AppSidebar.vue'
import AppHeader from '../components/AppHeader.vue'
</script>
