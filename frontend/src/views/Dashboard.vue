<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <h1>📊 Tableau de Bord</h1>
      <div class="header-actions">
        <button @click="refreshData" class="refresh-btn">
          🔄 Actualiser
        </button>
        <div class="last-update">
          Dernière mise à jour : {{ lastUpdate }}
        </div>
      </div>
    </div>

    <!-- Cartes principales Étudiants et Enseignants -->
    <div class="main-stats">
      <div class="main-card">
        <div class="card-icon student-icon">👨‍🎓</div>
        <div class="card-content">
          <h3>{{ formatNumber(stats.etudiants) }}</h3>
          <p>Total Étudiants</p>
          <div class="card-subtitle">
            Répartis sur {{ Object.keys(stats.parFiliere || {}).length }} filières
          </div>
        </div>
      </div>

      <div class="main-card">
        <div class="card-icon teacher-icon">👨‍🏫</div>
        <div class="card-content">
          <h3>{{ formatNumber(stats.enseignants) }}</h3>
          <p>Total Enseignants</p>
          <div class="card-subtitle">
            Répartis sur {{ Object.keys(stats.parDepartement || {}).length }} départements
          </div>
        </div>
      </div>
    </div>

    <!-- Deux colonnes pour filières et départements -->
    <div class="detailed-stats">
      <!-- Colonne gauche : Étudiants par filière -->
      <div class="stats-column">
        <div class="section-header">
          <h2>🎯 Étudiants par Filière</h2>
          <div class="total-badge">
            Total: {{ stats.etudiants }}
          </div>
        </div>
        
        <div class="stats-list">
          <div 
            v-for="(count, filiere) in stats.parFiliere" 
            :key="filiere"
            class="stats-item"
          >
            <div class="item-header">
              <div class="item-name">{{ filiere }}</div>
              <div class="item-count">{{ count }}</div>
            </div>
            <div class="progress-bar">
              <div 
                class="progress-fill"
                :style="{ width: getPercentage(count, stats.etudiants) + '%' }"
              ></div>
            </div>
            <div class="percentage">
              {{ getPercentage(count, stats.etudiants) }}%
            </div>
          </div>
        </div>
      </div>

      <!-- Colonne droite : Enseignants par département -->
      <div class="stats-column">
        <div class="section-header">
          <h2>🏫 Enseignants par Département</h2>
          <div class="total-badge">
            Total: {{ stats.enseignants }}
          </div>
        </div>
        
        <div class="stats-list">
          <div 
            v-for="(count, departement) in stats.parDepartement" 
            :key="departement"
            class="stats-item"
          >
            <div class="item-header">
              <div class="item-name">{{ departement }}</div>
              <div class="item-count">{{ count }}</div>
            </div>
            <div class="progress-bar">
              <div 
                class="progress-fill dept-fill"
                :style="{ width: getPercentage(count, stats.enseignants) + '%' }"
              ></div>
            </div>
            <div class="percentage">
              {{ getPercentage(count, stats.enseignants) }}%
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Résumé des tops -->
    <div class="top-summary">
      <div class="top-card">
        <h3>🏆 Top Filière</h3>
        <div v-if="topFiliere" class="top-content">
          <div class="top-name">{{ topFiliere.name }}</div>
          <div class="top-stats">
            {{ topFiliere.count }} étudiants
            <span class="top-percent">({{ getPercentage(topFiliere.count, stats.etudiants) }}%)</span>
          </div>
        </div>
        <div v-else class="no-data">Aucune donnée</div>
      </div>

      <div class="top-card">
        <h3>🏆 Top Département</h3>
        <div v-if="topDepartement" class="top-content">
          <div class="top-name">{{ topDepartement.name }}</div>
          <div class="top-stats">
            {{ topDepartement.count }} enseignants
            <span class="top-percent">({{ getPercentage(topDepartement.count, stats.enseignants) }}%)</span>
          </div>
        </div>
        <div v-else class="no-data">Aucune donnée</div>
      </div>
    </div>

    <!-- Chargement -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p>Chargement des statistiques...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "../services/api";

const stats = ref({
  etudiants: 0,
  enseignants: 0,
  parFiliere: {},
  parDepartement: {}
});

const loading = ref(false);
const lastUpdate = ref('');

const topFiliere = computed(() => {
  const filieres = Object.entries(stats.value.parFiliere || {});
  if (filieres.length === 0) return null;
  
  const [name, count] = filieres.reduce((max, current) => 
    current[1] > max[1] ? current : max
  );
  
  return { name, count };
});

const topDepartement = computed(() => {
  const departements = Object.entries(stats.value.parDepartement || {});
  if (departements.length === 0) return null;
  
  const [name, count] = departements.reduce((max, current) => 
    current[1] > max[1] ? current : max
  );
  
  return { name, count };
});

const getPercentage = (count, total) => {
  if (!total || total === 0) return 0;
  return ((count / total) * 100).toFixed(1);
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('fr-FR').format(num);
};

const updateLastUpdateTime = () => {
  const now = new Date();
  lastUpdate.value = now.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  });
};

const refreshData = async () => {
  await fetchData();
  updateLastUpdateTime();
};

const fetchData = async () => {
  loading.value = true;
  
  try {
    const [etudiantsData, enseignantsData] = await Promise.all([
      api.get("/etudiants").then(res => res.data),
      api.get("/enseignants").then(res => res.data)
    ]);

    // Statistiques de base
    stats.value.etudiants = etudiantsData.length;
    stats.value.enseignants = enseignantsData.length;

    // Étudiants par filière
    const parFiliere = {};
    etudiantsData.forEach(etudiant => {
      const filiere = etudiant.filiere || "Non définie";
      parFiliere[filiere] = (parFiliere[filiere] || 0) + 1;
    });

    // Enseignants par département
    const parDepartement = {};
    enseignantsData.forEach(enseignant => {
      const departement = enseignant.departement || "Non défini";
      parDepartement[departement] = (parDepartement[departement] || 0) + 1;
    });

    stats.value.parFiliere = parFiliere;
    stats.value.parDepartement = parDepartement;

  } catch (error) {
    console.error('Erreur lors du chargement des données:', error);
  } finally {
    loading.value = false;
    updateLastUpdateTime();
  }
};

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.dashboard {
  padding: 30px;
  background: #f5f7fa;
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.dashboard-header h1 {
  color: #2c3e50;
  font-size: 2.2rem;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.refresh-btn {
  background: #4a6cf7;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.refresh-btn:hover {
  background: #3a5ce5;
  transform: translateY(-2px);
}

.last-update {
  color: #7f8c8d;
  font-size: 0.9rem;
}

.main-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.main-card {
  background: white;
  border-radius: 16px;
  padding: 30px;
  display: flex;
  align-items: center;
  gap: 25px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  transition: transform 0.3s ease;
}

.main-card:hover {
  transform: translateY(-5px);
}

.card-icon {
  font-size: 3.5rem;
  width: 80px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: white;
}

.student-icon {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.teacher-icon {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-content h3 {
  color: #2c3e50;
  font-size: 2.8rem;
  margin: 0 0 8px 0;
}

.card-content p {
  color: #4a6cf7;
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 8px 0;
}

.card-subtitle {
  color: #7f8c8d;
  font-size: 0.95rem;
}

.detailed-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 30px;
}

.stats-column {
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #f0f2f5;
}

.section-header h2 {
  color: #2c3e50;
  font-size: 1.5rem;
  margin: 0;
}

.total-badge {
  background: #4a6cf7;
  color: white;
  padding: 6px 15px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
}

.stats-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.stats-item {
  background: #f8f9ff;
  border-radius: 12px;
  padding: 20px;
  border-left: 4px solid #4a6cf7;
}

.stats-column:last-child .stats-item {
  border-left-color: #f5576c;
}

.stats-column:last-child .progress-fill {
  background: linear-gradient(90deg, #f093fb, #f5576c);
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.item-name {
  color: #2c3e50;
  font-weight: 600;
  font-size: 1.1rem;
  flex: 1;
}

.item-count {
  background: #4a6cf7;
  color: white;
  padding: 6px 15px;
  border-radius: 15px;
  font-weight: bold;
  font-size: 1rem;
  min-width: 60px;
  text-align: center;
}

.stats-column:last-child .item-count {
  background: #f5576c;
}

.progress-bar {
  height: 6px;
  background: #e0e6ff;
  border-radius: 3px;
  overflow: hidden;
  margin-bottom: 8px;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4a6cf7, #667eea);
  border-radius: 3px;
  transition: width 1s ease-in-out;
}

.percentage {
  color: #7f8c8d;
  font-size: 0.9rem;
  text-align: right;
  font-weight: 600;
}

.top-summary {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 25px;
}

.top-card {
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  border-top: 4px solid #4a6cf7;
}

.top-card:last-child {
  border-top-color: #f5576c;
}

.top-card h3 {
  color: #2c3e50;
  font-size: 1.3rem;
  margin: 0 0 20px 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.top-content {
  text-align: center;
}

.top-name {
  font-size: 1.8rem;
  color: #2c3e50;
  font-weight: 700;
  margin-bottom: 10px;
}

.top-stats {
  color: #4a6cf7;
  font-size: 1.1rem;
  font-weight: 600;
}

.top-card:last-child .top-stats {
  color: #f5576c;
}

.top-percent {
  color: #7f8c8d;
  font-weight: 500;
  font-size: 0.9rem;
}

.no-data {
  text-align: center;
  color: #95a5a6;
  font-style: italic;
  padding: 20px 0;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.loading-spinner {
  width: 60px;
  height: 60px;
  border: 4px solid #f0f2f5;
  border-top: 4px solid #4a6cf7;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 1200px) {
  .detailed-stats {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .main-stats {
    grid-template-columns: 1fr;
  }
  
  .top-summary {
    grid-template-columns: 1fr;
  }
}
</style>