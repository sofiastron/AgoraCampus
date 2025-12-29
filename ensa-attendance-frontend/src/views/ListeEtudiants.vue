<template>
  <div class="liste-etudiants-container">
    <h1>Liste des étudiants</h1>

    <div class="filters">
      <label>
        Module :
        <select v-model="filters.moduleId" @change="loadSeances">
          <option value="">Tous les modules</option>
          <option v-for="module in modules" :key="module.id" :value="module.id">
            {{ module.titre }}
          </option>
        </select>
      </label>

      <label>
        Séance :
        <select v-model="filters.seanceId" :disabled="!filters.moduleId">
          <option value="">Toutes les séances</option>
          <option v-for="seance in seances" :key="seance.id" :value="seance.id">
            {{ seance.date }} ({{ seance.heure_debut }} - {{ seance.heure_fin }})
          </option>
        </select>
      </label>

      <label>
        Statut :
        <select v-model="filters.statut">
          <option value="">Tous</option>
          <option value="present">Présents</option>
          <option value="absent">Absents</option>
        </select>
      </label>

      <button @click="fetchEtudiants">Filtrer</button>
    </div>

    <div class="etudiants-list">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Module</th>
            <th>Présence</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="etudiant in etudiants" :key="etudiant.id">
            <td>{{ etudiant.nom }}</td>
            <td>{{ etudiant.prenom }}</td>
            <td>{{ etudiant.module_titre }}</td>
            <td :class="{'present': etudiant.statut === 'present', 'absent': etudiant.statut === 'absent'}">
              {{ etudiant.statut }}
            </td>
          </tr>
          <tr v-if="etudiants.length === 0">
            <td colspan="4" class="empty-row">Aucun étudiant trouvé</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '../api/axios'

const modules = ref([])
const seances = ref([])
const etudiants = ref([])

const filters = reactive({
  moduleId: '',
  seanceId: '',
  statut: ''
})

async function loadModules() {
  try {
    const res = await axios.get('/teacher/modules')
    modules.value = res.data.modules || res.data
  } catch (error) {
    console.error('Erreur chargement modules:', error)
  }
}

async function loadSeances() {
  if (!filters.moduleId) {
    seances.value = []
    filters.seanceId = ''
    return
  }
  try {
    const res = await axios.get(`/seances/module/${filters.moduleId}`)
    seances.value = res.data
  } catch (error) {
    console.error('Erreur chargement séances:', error)
  }
}

async function fetchEtudiants() {
  try {
    const params = {}
    if (filters.moduleId) params.module_id = filters.moduleId
    if (filters.seanceId) params.seance_id = filters.seanceId
    if (filters.statut) params.statut = filters.statut

    const res = await axios.get('/teacher/etudiants-presence', { params })
    etudiants.value = res.data
  } catch (error) {
    console.error('Erreur chargement étudiants:', error)
  }
}

onMounted(() => {
  loadModules()
  fetchEtudiants()
})
</script>

<style scoped>


.liste-etudiants-container {
  max-width: 1000px;
  margin: 3rem auto;
  padding: 2.5rem;
  background: linear-gradient(135deg, #f7f9fc, #e0ebf8);
  border-radius: 15px;
  box-shadow: 0 10px 35px rgba(50, 50, 93, 0.1);
  font-family: 'Poppins', sans-serif;
  color: #2c3e50;
}

h1 {
  text-align: center;
  font-weight: 800;
  font-size: 2.8rem;
  color: #3730a3;
  margin-bottom: 3rem;
}

.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1.8rem;
  justify-content: center;
  margin-bottom: 2.5rem;
}

.filters label {
  display: flex;
  flex-direction: column;
  font-weight: 600;
  font-size: 1rem;
  color: #4338ca;
  min-width: 200px;
}

.filters select {
  margin-top: 0.6rem;
  padding: 0.6rem 0.9rem;
  border-radius: 8px;
  border: 2px solid #3730a3;
  font-size: 1.1rem;
  cursor: pointer;
  background-color: white;
  box-shadow: inset 0 2px 4px rgb(0 0 0 / 0.05);
  color: #000;
}

.filters select:hover {
  border-color: #4338ca;
  box-shadow: 0 0 10px #4338caaa;
}

.filters select:focus {
  outline: none;
  border-color: #3730a3;
  box-shadow: 0 0 12px #3730a3bb;
}

.filters button {
  align-self: flex-end;
  padding: 0.65rem 2rem;
  background: linear-gradient(135deg, #4338ca, #3730a3);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  box-shadow: 0 6px 15px #4338caaa;
  transition: all 0.3s ease;
  user-select: none;
}

.filters button:hover {
  background: linear-gradient(135deg, #3730a3, #4338ca);
  box-shadow: 0 8px 20px #3730a3cc;
  transform: translateY(-3px);
}


.etudiants-list {
  overflow-x: auto;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 10px;
  font-size: 1.1rem;
  min-width: 700px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

thead tr {
  background-color: #3730a3;
  color: white;
  font-weight: 700;
  font-size: 1.1rem;
  letter-spacing: 0.05em;
}

thead th {
  padding: 1rem 1.3rem;
  text-align: left;
}

tbody tr {
  background: #f9fbfc;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: default;
}

tbody tr:hover {
  transform: scale(1.02);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

tbody td {
  padding: 1rem 1.3rem;
  vertical-align: middle;
  color: #2c3e50;
  border: none;
}

tbody tr td:first-child {
  font-weight: 700;
  color: #4338ca;
}

.present {
  color: #27ae60;
  font-weight: 700;
  text-transform: capitalize;
  background: #d4f8dc;
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  display: inline-block;
  box-shadow: 0 0 8px #27ae6011;
}

.present:hover {
  background: #27ae60;
  color: white;
  box-shadow: 0 0 12px #27ae60cc;
}

.absent {
  color: #e74c3c;
  font-weight: 700;
  text-transform: capitalize;
  background: #f9d6d5;
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  display: inline-block;
  box-shadow: 0 0 8px #e74c3c11;
}

.absent:hover {
  background: #e74c3c;
  color: white;
  box-shadow: 0 0 12px #e74c3ccc;
}

.empty-row {
  text-align: center;
  font-style: italic;
  color: #7f8c8d;
  padding: 2rem 0;
}



@media (max-width: 700px) {
  .filters {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .filters label,
  .filters button {
    width: 100%;
  }

  table {
    font-size: 0.95rem;
    min-width: 100%;
  }
}
</style>
