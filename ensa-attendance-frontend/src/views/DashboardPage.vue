<template>
  <div class="dashboard-container">
    <h1>Dashboard Enseignant</h1>

    <div v-if="loading" class="loading">Chargement des données...</div>
    <div v-else-if="error" class="error">Erreur : {{ error }}</div>

    <div v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <h2>Total Étudiants</h2>
          <p>{{ stats.totalEtudiants }}</p>
        </div>

        <div class="stat-card">
          <h2>Total Modules</h2>
          <p>{{ stats.totalModules }}</p>
        </div>

        <div class="stat-card">
          <h2>Séances Aujourd’hui</h2>
          <p>{{ stats.seancesTodayCount }}</p>
        </div>
      </div>

      <div class="today-section">
        <h2>Séances d’aujourd’hui</h2>

        <div v-if="stats.seancesToday.length" class="seance-list">
          <div
            v-for="seance in stats.seancesToday"
            :key="seance.id"
            class="seance-card"
          >
            <p><strong>Module :</strong> {{ seance.module.titre }}</p>
            <p><strong>Heure :</strong> {{ seance.heure_debut }} - {{ seance.heure_fin }}</p>
          </div>
        </div>

        <div v-else class="no-seance">
          Aucune séance aujourd’hui
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import axios from '../api/axios'

const stats = reactive({
  totalEtudiants: 0,
  totalModules: 0,
  seancesTodayCount: 0,
  seancesToday: [],
})

const loading = ref(false)
const error = ref(null)

const fetchStats = async () => {
  loading.value = true
  error.value = null

  try {
    const { data } = await axios.get('/teacher/dashboard-stats', {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    })

    console.log('Dashboard stats:', data)

    stats.totalEtudiants = data.totalEtudiants
    stats.totalModules = data.totalModules
    stats.seancesToday = data.seancesToday
    stats.seancesTodayCount = data.seancesToday.length
  } catch (e) {
    error.value = e.response?.data?.message || e.message || 'Erreur inconnue'
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)
</script>

<style scoped>
.dashboard-container {
  max-width: 900px;
  margin: 2rem auto;
  padding: 1rem;
  font-family: Arial, sans-serif;
  color: #333;
}

h1 {
  text-align: center;
  color: #3730a3;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.stat-card {
  background: #3730a3;
  color: white;
  padding: 2rem;
  border-radius: 8px;
  text-align: center;
  user-select: none;
  box-shadow: 0 2px 8px rgba(55, 48, 163, 0.3);
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: scale(1.05);
}

.stat-card h2 {
  margin-bottom: 1rem;
  font-size: 1.25rem;
  font-weight: 600;
}

.stat-card p {
  font-size: 2.8rem;
  font-weight: bold;
  letter-spacing: 1px;
  margin: 0;
  color: #fff !important; 
}

.loading {
  font-size: 1.2rem;
  color: #4338ca;
  text-align: center;
  margin-top: 3rem;
}

.error {
  color: #ff4d4f;
  font-weight: bold;
  text-align: center;
  margin-top: 3rem;
}

.today-section {
  margin-top: 3rem;
}

.seance-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.seance-card {
  background: #4338ca;
  color: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 1px 6px rgba(55, 48, 163, 0.7);
  transition: transform 0.2s ease;
}
.seance-card:hover {
  transform: scale(1.05);
}

.no-seance {
  margin-top: 1rem;
  font-weight: bold;
  color: #999;
  text-align: center;
}
</style>
