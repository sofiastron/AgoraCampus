<template>
  <div class="dashboard-container">
   
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
  max-width: 1100px;
  margin: 3rem auto;
  padding: 2rem;
  font-family: 'Inter', 'Segoe UI', sans-serif;
  color: #0f172a;
  animation: fadeIn 0.8s ease;
}

/* TITRE */
h1 {
  text-align: center;
  font-size: 2.4rem;
  font-weight: 700;
  margin-bottom: 2.5rem;
  background: linear-gradient(90deg, #6366f1, #22d3ee);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* GRID STATS */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 2rem;
}

/* CARTES */
.stat-card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 20px 45px rgba(99, 102, 241, 0.15);
  transition: all 0.4s ease;
  animation: slideUp 0.7s ease forwards;
}

.stat-card:hover {
  transform: translateY(-10px) scale(1.03);
  box-shadow: 0 30px 70px rgba(99, 102, 241, 0.25);
}

.stat-card h2 {
  font-size: 1.2rem;
  color: #475569;
  margin-bottom: 0.6rem;
}

.stat-card p {
  font-size: 3rem;
  font-weight: 800;
  background: linear-gradient(90deg, #6366f1, #22d3ee);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

/* LOADING */
.loading {
  margin-top: 4rem;
  text-align: center;
  font-size: 1.3rem;
  font-weight: 600;
  color: #6366f1;
  animation: pulse 1.4s infinite;
}

/* ERREUR */
.error {
  text-align: center;
  margin-top: 3rem;
  font-size: 1.2rem;
  font-weight: 600;
  color: #ef4444;
}

/* SECTION AUJOURD'HUI */
.today-section {
  margin-top: 4rem;
  animation: fadeIn 1s ease;
}

.today-section h2 {
  font-size: 1.7rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  color: #1e293b;
}

/* LISTE SEANCES */
.seance-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.4rem;
}

.seance-card {
  background: linear-gradient(135deg, #6366f1, #22d3ee);
  color: white;
  padding: 1.4rem;
  border-radius: 16px;
  box-shadow: 0 18px 35px rgba(34, 211, 238, 0.35);
  transition: all 0.35s ease;
  animation: slideUp 0.6s ease forwards;
}

.seance-card:hover {
  transform: translateY(-8px) scale(1.03);
  box-shadow: 0 25px 55px rgba(34, 211, 238, 0.5);
}

.seance-card p {
  margin: 0.4rem 0;
  font-size: 0.95rem;
}

/* AUCUNE SEANCE */
.no-seance {
  text-align: center;
  margin-top: 2rem;
  font-size: 1.1rem;
  font-weight: 600;
  color: #64748b;
}

/* ANIMATIONS */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes pulse {
  0% {
    opacity: 0.6;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.6;
  }
}

</style>
