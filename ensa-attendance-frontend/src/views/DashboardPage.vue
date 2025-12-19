<template>
  <div class="dashboard-container">
    <h1>Dashboard Enseignant</h1>

    <div v-if="loading" class="loading">Chargement des données...</div>

    <div v-else-if="error" class="error">
      Erreur : {{ error }}
    </div>

    <div v-else class="stats-grid">
      <div class="stat-card total-etudiants">
        <h2>Total Étudiants</h2>
        <p>{{ stats.totalEtudiants }}</p>
      </div>

      <div class="stat-card total-modules">
        <h2>Total Modules</h2>
        <p>{{ stats.totalModules }}</p>
      </div>

      <div class="stat-card total-seances">
        <h2>Total Séances</h2>
        <p>{{ stats.totalSeances }}</p>
      </div>

      <div class="stat-card seances-aujourdhui">
        <h2>Séances Aujourd'hui</h2>
        <p>{{ stats.seancesToday }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '../api/axios'

const stats = ref({
  totalEtudiants: 0,
  totalModules: 0,
  totalSeances: 0,
  seancesToday: 0,
})

const loading = ref(false)
const error = ref(null)

async function fetchStats() {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/teacher/dashboard-stats', {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    })
    Object.assign(stats.value, response.data)
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Erreur inconnue'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})
</script>

<style scoped>
.dashboard-container {
  max-width: 900px;
  margin: 0 auto;
  padding: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit,minmax(180px,1fr));
  gap: 1rem;
  margin-top: 2rem;
}

.stat-card {
  background-color: #3730a3;
  color: white;
  border-radius: 8px;
  padding: 1.5rem;
  text-align: center;
  box-shadow: 0 2px 8px rgba(55,48,163,0.3);
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: scale(1.05);
}

.stat-card h2 {
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.stat-card p {
  font-size: 2.5rem;
  font-weight: bold;
}

.loading {
  font-size: 1.2rem;
  color: #4338ca;
  text-align: center;
}

.error {
  color: #ff4d4f;
  font-weight: bold;
  text-align: center;
}
</style>
