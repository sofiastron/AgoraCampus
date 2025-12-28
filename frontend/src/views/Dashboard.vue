<template>
  <div>
    <h1 class="title">Bienvenue {{ profil.user.nom }}</h1>

    <!-- Cartes stats -->
    <div class="stats">
      <div class="card">
        <h3>Taux de présence</h3>
        <p>{{ tauxPresence }} %</p>
      </div>

      <div class="card">
        <h3>Présences</h3>
        <p>{{ stats.presences }}</p>
      </div>

      <div class="card">
        <h3>Absences</h3>
        <p>{{ stats.absences }}</p>
      </div>
    </div>

    <!-- Sélection du mois pour le graphe -->
    <div class="box">
      <label for="month">Choisir le mois :</label>
      <select id="month" v-model="selectedMonth" @change="updateChart">
        <option v-for="m in availableMonths" :key="m" :value="m">{{ m }}</option>
      </select>
    </div>

    <!-- Graphe + annonces -->
    <div class="grid">
      <div class="box">
        <h3>Absences par module</h3>
        <canvas ref="chartCanvas"></canvas>
      </div>

      <div class="box">
        <h3>Nouvelles annonces</h3>
        <div v-for="a in annonces" :key="a.id" class="annonce">
          <strong>{{ a.titre }}</strong>
          <p>{{ a.contenu }}</p>
          <small>{{ a.enseignant?.user?.nom || a.enseignant?.nom || 'Enseignant inconnu' }}</small>
        </div>
      </div>
    </div>

    <!-- Séances aujourd’hui -->
    <div class="box">
      <h3>Séances aujourd’hui</h3>
      <ul>
        <li v-for="s in seancesAujourdHui" :key="s.id">
          {{ s.module.titre }} ({{ s.heure_debut }})
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import axios from '@/axios'
import Chart from 'chart.js/auto'

const chartCanvas = ref(null)
let chartInstance = null

// Données
const profil = reactive({ user: { nom: '', email: '' } })
const stats = reactive({ presences: 0, absences: 0 })
const annonces = ref([])
const presences = ref([])
const seancesAujourdHui = ref([])

// Pour graphe
const selectedMonth = ref('')
const availableMonths = ref([]) // format 'YYYY-MM'

// Taux de présence
const tauxPresence = computed(() => {
  const total = stats.presences + stats.absences
  return total ? Math.round((stats.presences / total) * 100) : 0
})

onMounted(async () => {
  try {
    // Dashboard principal
    const dash = await axios.get('/etudiant/dashboard')
    profil.user.nom = dash.data.profil.user.nom
    stats.presences = dash.data.stats.presences
    stats.absences = dash.data.stats.absences

    // Présences pour le graphe
    const pres = await axios.get('/etudiant/presences')
    presences.value = pres.data

    // Générer la liste des mois disponibles
    generateAvailableMonths()

    // Choisir par défaut le dernier mois
    selectedMonth.value = availableMonths.value[availableMonths.value.length - 1]

    // Modules et séances
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

    // Annonces du premier module
    if (modulesRes.data.length > 0) {
      const ann = await axios.get(`/etudiant/modules/${modulesRes.data[0].id}/annonces`)
      annonces.value = ann.data
    }

    // Construire le graphe
    updateChart()

  } catch (error) {
    console.error("Erreur dashboard :", error)
  }
})

// Génère la liste des mois disponibles d'après les présences
function generateAvailableMonths() {
  const monthsSet = new Set()
  presences.value.forEach(p => {
    const date = new Date(p.horodatage)
    const monthKey = `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2,'0')}`
    monthsSet.add(monthKey)
  })
  availableMonths.value = Array.from(monthsSet).sort()
}

// Met à jour le graphe pour le mois sélectionné
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

  chartInstance = new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: `Absences (${selectedMonth.value})`,
        data: data,
        backgroundColor: 'rgba(255, 99, 132, 0.5)'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: true } },
      scales: {
        y: {
          beginAtZero: true,
          min: 0,      // commence à 1
          max: 30,     // limite à 30
          ticks: {
            stepSize: 1,
            callback: function(value) { return Number(value).toFixed(0) }
          }
        }
      }
    }
  })
}
</script>

<style scoped>
.title { font-size: 24px; margin-bottom: 20px; }
.stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
.card, .box { background: white; padding: 20px; border-radius: 10px; }
.grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
.annonce { border-bottom: 1px solid #eee; padding: 10px 0; }
</style>