<template>
  <div>
    <h1 class="title">Bienvenue {{ profil.nom }}</h1>

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

    <!-- Graphe + annonces -->
    <div class="grid">
      <div class="box">
        <h3>Absences par module (ce mois)</h3>
        <canvas ref="chartCanvas"></canvas>
      </div>

      <div class="box">
        <h3>Nouvelles annonces</h3>
        <div v-for="a in annonces" :key="a.id" class="annonce">
          <strong>{{ a.titre }}</strong>
          <p>{{ a.contenu }}</p>
          <small>{{ a.enseignant.user.nom }}</small>
        </div>
      </div>
    </div>

    <!-- Séances aujourd’hui -->
    <div class="box">
      <h3>Séances aujourd’hui</h3>
      <ul>
        <li v-for="s in seancesAujourdHui" :key="s.id">
          {{ s.module.titre }} ({{ s.seance.heure_debut }})
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

const profil = reactive({ nom: '' })
const stats = reactive({ presences: 0, absences: 0 })
const annonces = ref([])
const presences = ref([])
const seancesAujourdHui = ref([])

// Taux de présence
const tauxPresence = computed(() => {
  const total = stats.presences + stats.absences
  return total ? Math.round((stats.presences / total) * 100) : 0
})

onMounted(async () => {
  // Dashboard
  const dash = await axios.get('/etudiant/dashboard')
  profil.nom = dash.data.profil.user.nom
  stats.presences = dash.data.stats.presences
  stats.absences = dash.data.stats.absences

  // Présences (pour graphe + séances)
  const pres = await axios.get('/etudiant/presences')
  presences.value = pres.data

  // Annonces (exemple module 1)
  const ann = await axios.get('/etudiant/annonces/1')
  annonces.value = ann.data

  buildChart()
})

function buildChart() {
  const currentMonth = new Date().getMonth()

  const absencesParModule = {}

  presences.value.forEach(p => {
    const date = new Date(p.horodatage)
    if (
      p.statut === 'absent' &&
      date.getMonth() === currentMonth
    ) {
      const module = p.seance.module.titre
      absencesParModule[module] = (absencesParModule[module] || 0) + 1
    }
  })

  new Chart(chartCanvas.value, {
    type: 'bar',
    data: {
      labels: Object.keys(absencesParModule),
      datasets: [
        {
          label: 'Absences',
          data: Object.values(absencesParModule)
        }
      ]
    }
  })
}
</script>

<style scoped>
.title {
  font-size: 24px;
  margin-bottom: 20px;
}

.stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  margin-bottom: 20px;
}

.card, .box {
  background: white;
  padding: 20px;
  border-radius: 10px;
}

.grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 20px;
}

.annonce {
  border-bottom: 1px solid #eee;
  padding: 10px 0;
}
</style>
