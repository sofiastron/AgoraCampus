<template>
  <div class="dashboard">
    <!-- En-tête avec salutation -->
    <div class="header">
      <div class="user-greeting">
        <h1 class="greeting-text">Bonjour, <span class="user-name">{{ profil.user.nom }}</span></h1>
        <p class="greeting-subtext">Voici votre tableau de bord de suivi</p>
      </div>
      <div class="date-display">
        {{ currentDate }}
      </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="stats-grid">
      <div class="stat-card primary">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Taux de présence</p>
          <h2 class="stat-value">{{ tauxPresence }}%</h2>
        </div>
      </div>

      <div class="stat-card success">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 10L10.5 15L8 12.7273" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Présences</p>
          <h2 class="stat-value">{{ stats.presences }}</h2>
        </div>
      </div>

      <div class="stat-card warning">
        <div class="stat-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <div class="stat-content">
          <p class="stat-label">Absences</p>
          <h2 class="stat-value">{{ stats.absences }}</h2>
        </div>
      </div>
    </div>

    <!-- Section graphique et annonces -->
    <div class="main-content">
      <!-- Graphique -->
      <div class="chart-section">
        <div class="section-header">
          <h3>Absences par module</h3>
          <div class="month-selector">
            <label for="month" class="selector-label">Mois :</label>
            <div class="custom-select">
              <select id="month" v-model="selectedMonth" @change="updateChart">
                <option v-for="m in availableMonths" :key="m" :value="m">{{ formatMonth(m) }}</option>
              </select>
              <div class="select-arrow">▼</div>
            </div>
          </div>
        </div>
        <div class="chart-container">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>

      <!-- Annonces -->
      <div class="announcements-section">
        <div class="section-header">
          <h3>Dernières annonces</h3>
          <span class="badge">{{ annonces.length }}</span>
        </div>
        <div class="announcements-list">
          <div v-for="a in annonces" :key="a.id" class="announcement-card">
            <div class="announcement-header">
              <h4 class="announcement-title">{{ a.titre }}</h4>
              <div class="announcement-dot"></div>
            </div>
            <p class="announcement-content">{{ a.contenu }}</p>
            <div class="announcement-footer">
              <span class="teacher-name">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="margin-right: 6px;">
                  <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ a.enseignant?.user?.nom || a.enseignant?.nom || 'Enseignant' }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

          </div>
     
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import axios from '@/axios'
import Chart from 'chart.js/auto'

const chartCanvas = ref(null)
let chartInstance = null

const profil = reactive({ user: { nom: '', email: '' } })
const stats = reactive({ presences: 0, absences: 0 })
const annonces = ref([])
const presences = ref([])
const seancesAujourdHui = ref([])
const selectedMonth = ref('')
const availableMonths = ref([])

const tauxPresence = computed(() => {
  const total = stats.presences + stats.absences
  return total ? Math.round((stats.presences / total) * 100) : 0
})

const currentDate = computed(() => {
  return new Date().toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

function formatMonth(monthString) {
  const [year, month] = monthString.split('-')
  const date = new Date(year, month - 1)
  return date.toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' })
}

function formatTime(timeString) {
  return timeString ? timeString.slice(0, 5) : ''
}

onMounted(async () => {
  try {
    const dash = await axios.get('/etudiant/dashboard')
    profil.user.nom = dash.data.profil.user.nom
    stats.presences = dash.data.stats.presences
    stats.absences = dash.data.stats.absences

    const pres = await axios.get('/etudiant/presences')
    presences.value = pres.data

    generateAvailableMonths()
    selectedMonth.value = availableMonths.value[availableMonths.value.length - 1]

    const modulesRes = await axios.get('/etudiant/modules')
    const today = new Date().toISOString().slice(0, 10)
    seancesAujourdHui.value = []

    await Promise.all(modulesRes.data.map(async (m) => {
      const seancesRes = await axios.get(`/etudiant/modules/${m.id}/seances`)
      seancesRes.data.forEach(s => {
        if (s.date_seance === today) {
          seancesAujourdHui.value.push({
            id: s.id,
            module: { titre: m.titre },
            heure_debut: s.heure_debut
          })
        }
      })
    }))

    if (modulesRes.data.length > 0) {
      const ann = await axios.get(`/etudiant/modules/${modulesRes.data[0].id}/annonces`)
      annonces.value = ann.data
    }

    updateChart()

  } catch (error) {
    console.error("Erreur dashboard :", error)
  }
})

function generateAvailableMonths() {
  const monthsSet = new Set()
  presences.value.forEach(p => {
    const date = new Date(p.horodatage)
    const monthKey = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2,'0')}`
    monthsSet.add(monthKey)
  })
  availableMonths.value = Array.from(monthsSet).sort()
}

function updateChart() {
  const absencesParModule = {}

  presences.value.forEach(p => {
    if (p.statut === 'absent') {
      const date = new Date(p.horodatage)
      const monthKey = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2,'0')}`
      if (monthKey === selectedMonth.value) {
        const module = p.seance.module.titre
        absencesParModule[module] = (absencesParModule[module] || 0) + 1
      }
    }
  })

  const labels = Object.keys(absencesParModule)
  const data = Object.values(absencesParModule)

  if (chartInstance) {
    chartInstance.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')
  const gradient = ctx.createLinearGradient(0, 0, 0, 400)
  gradient.addColorStop(0, 'rgba(99, 102, 241, 0.8)')
  gradient.addColorStop(1, 'rgba(99, 102, 241, 0.1)')

  chartInstance = new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: `Absences (${selectedMonth.value})`,
        data: data,
        backgroundColor: gradient,
        borderColor: 'rgba(99, 102, 241, 1)',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.9)',
          titleColor: '#e2e8f0',
          bodyColor: '#cbd5e1',
          padding: 12,
          cornerRadius: 8
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#64748b'
          }
        },
        y: {
          beginAtZero: true,
          min: 0,
          max: 30,
          grid: {
            color: 'rgba(226, 232, 240, 0.3)'
          },
          ticks: {
            stepSize: 1,
            color: '#64748b',
            callback: function(value) { return Number(value).toFixed(0) }
          }
        }
      }
    }
  })
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  padding: 24px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 32px;
}

.user-greeting .greeting-text {
  font-size: 28px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 8px 0;
}

.user-greeting .user-name {
  color: #6366f1;
}

.greeting-subtext {
  color: #64748b;
  font-size: 16px;
  margin: 0;
}

.date-display {
  background: white;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 500;
  color: #475569;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  display: flex;
  align-items: center;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.stat-card.primary {
  border-left: 4px solid #6366f1;
}

.stat-card.success {
  border-left: 4px solid #10b981;
}

.stat-card.warning {
  border-left: 4px solid #f59e0b;
}

.stat-icon {
  width: 56px;
  height: 56px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  flex-shrink: 0;
}

.stat-card.primary .stat-icon {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.stat-card.success .stat-icon {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.stat-card.warning .stat-icon {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

.stat-content .stat-label {
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
  margin: 0 0 4px 0;
}

.stat-content .stat-value {
  color: #0f172a;
  font-size: 32px;
  font-weight: 700;
  margin: 0;
}

.main-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
  margin-bottom: 32px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  font-size: 20px;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.badge {
  background: #6366f1;
  color: white;
  font-size: 14px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
}

.chart-section, .announcements-section {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.chart-container {
  height: 300px;
  position: relative;
}

.month-selector {
  display: flex;
  align-items: center;
  gap: 12px;
}

.selector-label {
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
}

.custom-select {
  position: relative;
  min-width: 200px;
}

.custom-select select {
  width: 100%;
  padding: 10px 40px 10px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  background: white;
  color: #0f172a;
  font-size: 14px;
  font-weight: 500;
  appearance: none;
  cursor: pointer;
  transition: border-color 0.2s ease;
}

.custom-select select:focus {
  outline: none;
  border-color: #6366f1;
}

.select-arrow {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  pointer-events: none;
}

.announcements-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.announcement-card {
  background: #f8fafc;
  border-radius: 12px;
  padding: 20px;
  border: 1px solid #e2e8f0;
  transition: border-color 0.3s ease;
}

.announcement-card:hover {
  border-color: #6366f1;
}

.announcement-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.announcement-title {
  font-size: 16px;
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.announcement-dot {
  width: 8px;
  height: 8px;
  background: #6366f1;
  border-radius: 50%;
}

.announcement-content {
  color: #475569;
  font-size: 14px;
  line-height: 1.5;
  margin: 0 0 16px 0;
}

.announcement-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.teacher-name {
  display: flex;
  align-items: center;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
}

.sessions-section {
  background: white;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.sessions-count {
  background: #10b981;
  color: white;
  font-size: 14px;
  font-weight: 600;
  padding: 4px 12px;
  border-radius: 20px;
}

.sessions-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.session-card {
  display: flex;
  align-items: center;
  padding: 20px;
  background: #f8fafc;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  transition: all 0.3s ease;
}

.session-card:hover {
  border-color: #6366f1;
  transform: translateX(4px);
}

.session-time {
  min-width: 80px;
}

.time-badge {
  background: #6366f1;
  color: white;
  font-size: 14px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 20px;
}

.session-info {
  flex: 1;
  margin: 0 20px;
}

.session-title {
  font-size: 16px;
  font-weight: 600;
  color: #0f172a;
  margin: 0 0 4px 0;
}

.session-status {
  color: #10b981;
  font-size: 13px;
  font-weight: 500;
  margin: 0;
}

.session-action .join-btn {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #6366f1;
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.session-action .join-btn:hover {
  background: #4f46e5;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .main-content {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .header {
    flex-direction: column;
    gap: 16px;
  }
  
  .date-display {
    align-self: flex-start;
  }
}
</style>