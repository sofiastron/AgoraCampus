<template>
  <div class="dashboard">
    <h1>📊 Tableau de Bord</h1>
    
    <!-- Cartes de statistiques -->
    <div class="cards">
      <div class="card primary">
        <h2>{{ stats.etudiants }}</h2>
        <p>Total Étudiants</p>
        <div class="card-source" v-if="stats.etudiants > 0"></div>
      </div>
      
      <div class="card secondary">
        <h2>{{ stats.enseignants }}</h2>
        <p>Total Enseignants</p>
        <div class="card-source" v-if="stats.enseignants > 0"></div>
      </div>
      
      <div class="card accent">
        <h2>{{ stats.filieres }}</h2>
        <p>Filières</p>
        <div class="card-source" v-if="stats.filieres > 0"></div>
      </div>
    </div>

    <!-- Liste des filières réelles -->
    <div class="filiere-container">
      <div class="filiere-header">
        <h2>📚 Liste des Filières ({{ stats.filieres }})</h2>
        <div class="header-actions">
          <span class="total-count">{{ stats.filieres }} filière(s) en base</span>
        </div>
      </div>

      <!-- Tableau des filières -->
      <div class="table-container" v-if="stats.filieres > 0">
        <table class="filiere-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Code</th>
              <th>Description</th>
              <th>Créée le</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="filiere in filieres" :key="filiere.id">
              <td class="id-cell">{{ filiere.id }}</td>
              <td class="name-cell">
                <div class="filiere-name">
                  <span class="filiere-icon">📚</span>
                  {{ filiere.nom }}
                </div>
              </td>
              <td class="code-cell">
                <span class="code-badge">{{ filiere.code }}</span>
              </td>
              <td class="desc-cell">{{ filiere.description || 'Aucune description' }}</td>
              <td class="date-cell">{{ formatDate(filiere.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="empty-state">
        <div class="empty-icon">📊</div>
        <h3>Aucune filière en base de données</h3>
        <p>Ajoutez des filières via votre administration</p>
      </div>
    </div>

    <!-- Résumé avec données réelles -->
    <div class="summary-section">
      <div class="summary-card">
        <h3>📈 Résumé Global</h3>
        <div class="summary-content">
          <div class="summary-item">
            <span class="summary-label">Total étudiants :</span>
            <div class="summary-value-container">
              <span class="summary-value">{{ stats.etudiants }}</span>
              <span class="value-source" v-if="stats.etudiants > 0">réel</span>
            </div>
          </div>
          <div class="summary-item">
            <span class="summary-label">Total enseignants :</span>
            <div class="summary-value-container">
              <span class="summary-value">{{ enseignantsCount }}</span>
              <span class="value-source" v-if="enseignantsCount > 0">réel</span>
            </div>
          </div>
          <div class="summary-item">
            <span class="summary-label">Ratio étudiants/prof :</span>
            <div class="summary-value-container">
              <span class="summary-value">{{ calculateRatio() }}</span>
              <span class="value-source" v-if="stats.etudiants > 0 && stats.enseignants > 0">réel</span>
            </div>
          </div>
          <div class="summary-item">
            <span class="summary-label">Filières actives :</span>
            <div class="summary-value-container">
              <span class="summary-value">{{ stats.filieres }}</span>
              <span class="value-source" v-if="stats.filieres > 0">réel</span>
            </div>
          </div>
        </div>
        
        <div class="data-info" v-if="stats.lastUpdated">
          <span class="info-icon">🔄</span>
          <span>Données mises à jour : {{ formatTime(stats.lastUpdated) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "@/services/api";
const enseignants = ref([]);



// Données réelles
const filieres = ref([]);
const stats = ref({
  etudiants: 0,
  enseignants: 0,
  filieres: 0,
  enseignantsCount: 0,
  lastUpdated: null
});
const enseignantsCount = computed(() => {
  return Array.isArray(enseignants.value) ? enseignants.value.length : 0;
});


// Fonction pour compter les vrais enregistrements
const countRealRecords = (data) => {
  if (!data) return 0;

  // Cas pagination Laravel / API REST
  if (data.total !== undefined) {
    return Number(data.total);
  }

  // Cas { data: [], total: x }
  if (data.data && data.total !== undefined) {
    return Number(data.total);
  }

  // Cas tableau simple
  if (Array.isArray(data)) {
    return data.length;
  }

  // Cas { data: [] }
  if (data.data && Array.isArray(data.data)) {
    return data.data.length;
  }

  return 0;
};


// Charger toutes les données réelles
onMounted(async () => {
  try {
    // Marquer le début du chargement
    stats.value.lastUpdated = new Date();
    
    // Charger en parallèle les 3 types de données
    const [filieresRes, etudiantsRes, profsRes] = await Promise.allSettled([
      api.get("/filieres"),
      api.get("/etudiants"),
      api.get("/enseignants")
    ]);

    // Traiter les filières
    if (filieresRes.status === 'fulfilled') {
      const data = filieresRes.value.data;
      
      // Compter le nombre réel
      stats.value.filieres = countRealRecords(data);
      
      // Stocker la liste des filières
      if (Array.isArray(data)) {
        filieres.value = data;
      } else if (data && data.data && Array.isArray(data.data)) {
        filieres.value = data.data;
      }
    } else {
      console.error("Erreur filières:", filieresRes.reason);
    }

    // Traiter les étudiants
    if (etudiantsRes.status === 'fulfilled') {
      stats.value.etudiants = countRealRecords(etudiantsRes.value.data);
    } else {
      console.error("Erreur étudiants:", etudiantsRes.reason);
    }

    // Traiter les enseignants
    if (profsRes.status === 'fulfilled') {
      stats.value.enseignants = countRealRecords(profsRes.value.data);
    } else {
      console.error("Erreur enseignants:", profsRes.reason);
    }

    // Vérifier si on a des données
    if (stats.value.etudiants === 0 && stats.value.enseignants === 0 && stats.value.filieres === 0) {
      console.log("Base de données vide, affichage des données de test");
      // Si base vide, afficher quelques données de test pour démo
      filieres.value = [
        {
          id: 1,
          nom: "Génie Informatique",
          code: "GI",
          description: "Filière en génie informatique et développement logiciel",
          created_at: "2025-12-23 17:45:22"
        },
        {
          id: 2,
          nom: "Génie Industriel",
          code: "GIND",
          description: "Filière en génie industriel et management industriel",
          created_at: "2025-12-23 17:45:22"
        }
      ];
      
      stats.value.filieres = filieres.value.length;
      // Pour étudiants et enseignants, on garde 0 car base vide
    }

  } catch (error) {
    console.error("Erreur générale:", error);
    // En cas d'erreur totale, afficher un état vide
  }
});

// Formater la date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR');
};

// Formater l'heure
const formatTime = (date) => {
  if (!date) return 'N/A';
  return date.toLocaleTimeString('fr-FR', { 
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  });
};

// Calculer le ratio réel
const calculateRatio = () => {
  if (stats.value.enseignants === 0) return '∞';
  const ratio = stats.value.etudiants / stats.value.enseignants;
  return ratio.toFixed(1);
};
</script>

<style scoped>
.dashboard {
  padding: 30px;
  background: #f8fafc;
  min-height: 100vh;
}

.dashboard h1 {
  color: #373083;
  font-size: 2rem;
  margin-bottom: 20px;
  font-weight: 600;
}

/* Cartes de statistiques */
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin: 30px 0;
}

.card {
  background: white;
  padding: 25px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s ease;
  position: relative;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}

.card h2 {
  font-size: 3rem;
  margin-bottom: 10px;
  font-weight: 800;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card p {
  color: #64748b;
  font-weight: 500;
  font-size: 1rem;
  margin-bottom: 8px;
}

.card-source {
  font-size: 0.8rem;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 2px 8px;
  border-radius: 10px;
  display: inline-block;
  font-weight: 600;
}

/* Couleurs des cartes */
.card.primary h2 {
  color: #373083;
  background: linear-gradient(45deg, #373083, #4338ca);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.card.secondary h2 {
  color: #4338ca;
  background: linear-gradient(45deg, #4338ca, #6366f1);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.card.accent h2 {
  color: #6366f1;
  background: linear-gradient(45deg, #6366f1, #818cf8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Conteneur filières */
.filiere-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  margin: 30px 0;
}

.filiere-header {
  padding: 20px 25px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.filiere-header h2 {
  color: #1e293b;
  font-size: 1.3rem;
  margin: 0;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.total-count {
  background: #e0e7ff;
  color: #373083;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 500;
}

/* Tableau */
.table-container {
  overflow-x: auto;
}

.filiere-table {
  width: 100%;
  border-collapse: collapse;
}

.filiere-table thead {
  background: #f1f5f9;
}

.filiere-table th {
  padding: 15px 20px;
  text-align: left;
  color: #475569;
  font-weight: 600;
  font-size: 0.9rem;
  border-bottom: 2px solid #e2e8f0;
}

.filiere-table td {
  padding: 15px 20px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
}

/* Cellules spécifiques */
.id-cell {
  color: #94a3b8;
  font-size: 0.9rem;
  font-weight: 500;
  width: 60px;
}

.name-cell {
  color: #1e293b;
  font-weight: 500;
}

.filiere-name {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filiere-icon {
  font-size: 1.2rem;
}

.code-cell {
  width: 100px;
}

.code-badge {
  background: #e0e7ff;
  color: #373083;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
}

.desc-cell {
  color: #64748b;
  font-size: 0.9rem;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.date-cell {
  color: #64748b;
  font-size: 0.9rem;
  width: 120px;
}

/* Lignes alternées */
.filiere-table tbody tr:hover {
  background: #f8fafc;
}

.filiere-table tbody tr:nth-child(even) {
  background: #fafafa;
}

/* État vide */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  font-size: 3.5rem;
  margin-bottom: 20px;
  opacity: 0.2;
  color: #64748b;
}

.empty-state h3 {
  color: #475569;
  margin-bottom: 10px;
  font-size: 1.2rem;
}

.empty-state p {
  color: #94a3b8;
  font-size: 0.95rem;
}

/* Section résumé */
.summary-section {
  margin-top: 30px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  padding: 25px;
}

.summary-card h3 {
  color: #1e293b;
  font-size: 1.2rem;
  margin-bottom: 20px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.summary-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 20px;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.summary-label {
  color: #64748b;
  font-size: 0.95rem;
  font-weight: 500;
}

.summary-value-container {
  display: flex;
  align-items: center;
  gap: 10px;
}

.summary-value {
  color: #373083;
  font-weight: 700;
  font-size: 1.3rem;
  background: white;
  padding: 6px 16px;
  border-radius: 8px;
  min-width: 80px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.value-source {
  font-size: 0.75rem;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 2px 8px;
  border-radius: 10px;
  font-weight: 600;
}

/* Info données */
.data-info {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px;
  background: #f1f5f9;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #64748b;
}

.info-icon {
  font-size: 1rem;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard {
    padding: 15px;
  }
  
  .cards {
    grid-template-columns: 1fr;
    gap: 15px;
  }
  
  .card {
    padding: 20px;
  }
  
  .card h2 {
    font-size: 2.5rem;
  }
  
  .filiere-header {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  
  .filiere-table th,
  .filiere-table td {
    padding: 12px 15px;
  }
  
  .desc-cell {
    max-width: 200px;
  }
  
  .summary-content {
    grid-template-columns: 1fr;
  }
  
  .summary-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .summary-value-container {
    width: 100%;
    justify-content: space-between;
  }
}

@media (max-width: 480px) {
  .dashboard h1 {
    font-size: 1.6rem;
  }
  
  .card h2 {
    font-size: 2rem;
  }
  
  .filiere-icon {
    display: none;
  }
  
  .code-badge {
    font-size: 0.8rem;
    padding: 3px 8px;
  }
}
</style>