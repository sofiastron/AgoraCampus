<template>
  <div class="emploi-container">
    <h1 class="title">Mon Emploi du Temps</h1>

    <div v-if="seances.length === 0" class="no-data">
      <p>Aucune séance programmée pour le moment.</p>
    </div>

    <div v-else class="schedule-grid">
      <div v-for="seance in seances" :key="seance.id" class="seance-card">
        <div class="card-header">
          <span class="module-name">{{ seance.module.titre }}</span>
          <button @click="ouvrirAppel(seance.id)" class="camera-btn" title="Faire l'appel">
            <i class="fas fa-camera"></i>
          </button>
        </div>
        
        <div class="card-body">
          <p><i class="far fa-calendar"></i> {{ formatDate(seance.date) }}</p>
          <p><i class="far fa-clock"></i> {{ seance.heure_debut }} - {{ seance.heure_fin }}</p>
        </div>
        
        <div class="card-footer">
          <span class="type">Cours Présentiel</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const seances = ref([]);
const router = useRouter();

// 1. Récupérer les données depuis Laravel
const fetchSeances = async () => {
  try {
    // Cette route doit renvoyer les données de la table 'seances' liée aux 'modules'
    const response = await axios.get('http://localhost:8000/api/enseignant/seances');
    seances.value = response.data;
  } catch (error) {
    console.error("Erreur lors du chargement de l'emploi du temps", error);
  }
};

// 2. Rediriger vers la page de capture avec l'ID de la séance
const ouvrirAppel = (id) => {
  router.push({ 
    name: 'PresenceFacialRecognition', 
    query: { seance_id: id } 
  });
};

// Formater la date proprement
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    weekday: 'long', day: 'numeric', month: 'long'
  });
};

onMounted(fetchSeances);
</script>

<style scoped>
.emploi-container { padding: 20px; }
.schedule-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}
.seance-card {
  background: white;
  border-radius: 12px;
  padding: 15px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  border-left: 5px solid #4f46e5;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.module-name { font-weight: bold; color: #1e293b; }
.camera-btn {
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  cursor: pointer;
  transition: 0.3s;
}
.camera-btn:hover { background: #4338ca; transform: scale(1.1); }
.card-body p { margin: 5px 0; color: #64748b; font-size: 0.9em; }
</style>