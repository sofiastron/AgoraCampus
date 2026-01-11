<template>
  <div class="dashboard">
    <h1>📊 Tableau de Bord Administratif</h1>
    
    <!-- Cartes de statistiques principales -->
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
      
      <!-- Nouvelle carte pour les emplois du temps -->
      <div class="card success">
        <h2>{{ stats.emplois }}</h2>
        <p>Emplois du Temps</p>
        <div class="card-status" v-if="emploisStats">
          <span class="status-dot valid" :title="`${emploisStats.valides} validés`"></span>
          <span class="status-dot waiting" :title="`${emploisStats.en_attente} en attente`"></span>
        </div>
        <div class="card-source" v-if="stats.emplois > 0"></div>
      </div>
    </div>

    <!-- Section emplois du temps -->
    <div class="emplois-section">
      <div class="section-header">
        <h2>📅 Statistiques des Emplois du Temps</h2>
        
      </div>

      <!-- Statistiques détaillées emplois -->
      <div class="emplois-stats" v-if="emploisStats">
        <div class="stats-grid">
          <div class="stat-card total">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
              <div class="stat-value">{{ stats.emplois }}</div>
              <div class="stat-label">Total emplois</div>
            </div>
          </div>
          
          <div class="stat-card valid">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
              <div class="stat-value">{{ emploisStats.valides }}</div>
              <div class="stat-label">Validés</div>
              
            </div>
          </div>
          
          <div class="stat-card waiting">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
              <div class="stat-value">{{ emploisStats.en_attente }}</div>
              <div class="stat-label">En attente</div>
              
            </div>
          </div>
          
          <div class="stat-card rejected">
            <div class="stat-icon">❌</div>
            <div class="stat-content">
              <div class="stat-value">{{ emploisStats.rejetes || 0 }}</div>
              <div class="stat-label">Rejetés</div>
             
            </div>
          </div>
        </div>

        <!-- Distribution par filière -->
        <div class="filiere-distribution" v-if="emploisParFiliere.length > 0">
          <h3>📋 Distribution par Filière</h3>
          <div class="distribution-list">
            <div v-for="item in emploisParFiliere" :key="item.filiere_id" class="distribution-item">
              <div class="filiere-info">
                <span class="filiere-name">{{ item.filiere_nom || 'Sans filière' }}</span>
                <span class="filiere-code">{{ item.filiere_code }}</span>
              </div>
              <div class="distribution-stats">
                <div class="stat-bar">
                  <div class="bar-fill" :style="{ width: calculatePercentage(item.count, stats.emplois) + '%' }"></div>
                </div>
                <div class="stat-count">
                  <span class="count">{{ item.count }}</span>
                  <span class="percentage">({{ calculatePercentage(item.count, stats.emplois) }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Derniers emplois ajoutés -->
        <div class="recent-emplois" v-if="derniersEmplois.length > 0">
          <h3>🆕 Derniers emplois ajoutés</h3>
          <div class="emplois-list">
            <div v-for="emploi in derniersEmplois" :key="emploi.id" class="emploi-item">
              <div class="emploi-header">
                <span class="emploi-title">{{ emploi.titre || 'Sans titre' }}</span>
                <span class="emploi-status" :class="getStatusClass(emploi.statut)">
                  {{ getStatusLabel(emploi.statut) }}
                </span>
              </div>
              <div class="emploi-details">
                <span class="detail">
                  <span class="detail-icon">🏫</span>
                  {{ emploi.filiere?.nom || 'Non spécifié' }}
                </span>
                <span class="detail">
                  <span class="detail-icon">📚</span>
                  {{ emploi.semestre }}
                </span>
                <span class="detail">
                  <span class="detail-icon">📅</span>
                  {{ formatRelativeDate(emploi.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide pour emplois -->
      <div v-else class="empty-state">
        <div class="empty-icon">📅</div>
        <h3>Aucun emploi du temps disponible</h3>
        <p>Commencez par générer des emplois du temps</p>
        <button @click="goToPlanning" class="empty-action">
          <span class="action-icon">➕</span>
          Créer un emploi
        </button>
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
              <th>Emplois</th>
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
              <td class="emplois-cell">
                <span class="emplois-count" v-if="getEmploisCountForFiliere(filiere.id) > 0">
                  {{ getEmploisCountForFiliere(filiere.id) }}
                </span>
                <span v-else class="no-emplois">-</span>
              </td>
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

    <!-- Résumé global avec données réelles -->
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
              <span class="summary-value">{{ stats.enseignants }}</span>
              <span class="value-source" v-if="stats.enseignants > 0">réel</span>
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
            <span class="summary-label">Emplois du temps :</span>
            <div class="summary-value-container">
              <span class="summary-value">{{ stats.emplois }}</span>
              <span class="value-source" v-if="stats.emplois > 0">réel</span>
            </div>
          </div>
          <div class="summary-item">
            <span class="summary-label">Taux validation :</span>
            <div class="summary-value-container">
              <span class="summary-value">{{ calculateValidationRate() }}%</span>
              <span class="value-source" v-if="emploisStats?.valides > 0">réel</span>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from "@/services/api"

const router = useRouter()

// Données réelles
const filieres = ref([])
const stats = ref({
  etudiants: 0,
  enseignants: 0,
  filieres: 0,
  emplois: 0,
  lastUpdated: null
})

// Données des emplois du temps
const emploisData = ref([])
const emploisStats = ref(null)
const emploisParFiliere = ref([])
const derniersEmplois = ref([])

// Fonction pour compter les vrais enregistrements
const countRealRecords = (data) => {
  if (!data) return 0

  // Cas pagination Laravel / API REST
  if (data.total !== undefined) {
    return Number(data.total)
  }

  // Cas { data: [], total: x }
  if (data.data && data.total !== undefined) {
    return Number(data.total)
  }

  // Cas tableau simple
  if (Array.isArray(data)) {
    return data.length
  }

  // Cas { data: [] }
  if (data.data && Array.isArray(data.data)) {
    return data.data.length
  }

  return 0
}

// Charger les statistiques des emplois du temps
const loadEmploisStats = async () => {
  try {
    const response = await api.get('/planning/saved')
    const data = response.data
    
    if (data?.success && data.data) {
      emploisData.value = Array.isArray(data.data) ? data.data : data.data.data || []
      stats.value.emplois = emploisData.value.length
      
      // Calculer les statistiques
      calculateEmploisStats()
    }
  } catch (error) {
    console.error("Erreur chargement emplois:", error)
  }
}

// Calculer les statistiques des emplois
const calculateEmploisStats = () => {
  if (!emploisData.value.length) {
    emploisStats.value = null
    return
  }

  const stats = {
    total: emploisData.value.length,
    valides: 0,
    en_attente: 0,
    rejetes: 0,
    par_filiere: {}
  }

  // Parcourir tous les emplois
  emploisData.value.forEach(emploi => {
    // Compter par statut
    switch (emploi.statut) {
      case 'valide':
        stats.valides++
        break
      case 'en_attente':
        stats.en_attente++
        break
      case 'rejeté':
        stats.rejetes++
        break
    }

    // Compter par filière
    const filiereId = emploi.filiere_id
    if (filiereId) {
      if (!stats.par_filiere[filiereId]) {
        stats.par_filiere[filiereId] = {
          count: 0,
          filiere_nom: emploi.filiere?.nom,
          filiere_code: emploi.filiere?.code
        }
      }
      stats.par_filiere[filiereId].count++
    }
  })

  // Préparer la distribution par filière
  emploisParFiliere.value = Object.entries(stats.par_filiere).map(([id, data]) => ({
    filiere_id: id,
    filiere_nom: data.filiere_nom,
    filiere_code: data.filiere_code,
    count: data.count
  })).sort((a, b) => b.count - a.count)

  // Récupérer les 5 derniers emplois
  derniersEmplois.value = [...emploisData.value]
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 5)

  emploisStats.value = stats
}

// Obtenir le nombre d'emplois par filière
const getEmploisCountForFiliere = (filiereId) => {
  if (!emploisStats.value?.par_filiere[filiereId]) return 0
  return emploisStats.value.par_filiere[filiereId].count
}

// Calculer le pourcentage
const calculatePercentage = (value, total) => {
  if (!total || total === 0) return 0
  return Math.round((value / total) * 100)
}

// Taux de validation
const calculateValidationRate = () => {
  if (!emploisStats.value || stats.value.emplois === 0) return 0
  return calculatePercentage(emploisStats.value.valides, stats.value.emplois)
}

// Formater la date relative
const formatRelativeDate = (dateString) => {
  if (!dateString) return 'N/A'
  
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  
  if (diffHours < 1) {
    return 'À l\'instant'
  } else if (diffHours < 24) {
    return `Il y a ${diffHours}h`
  } else {
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
  }
}

// Obtenir la classe CSS pour le statut
const getStatusClass = (status) => {
  const classes = {
    'valide': 'status-valid',
    'en_attente': 'status-waiting',
    'rejeté': 'status-rejected'
  }
  return classes[status] || 'status-unknown'
}

// Obtenir le libellé du statut
const getStatusLabel = (status) => {
  const labels = {
    'valide': 'Validé',
    'en_attente': 'En attente',
    'rejeté': 'Rejeté'
  }
  return labels[status] || status || 'Inconnu'
}

// Rafraîchir les statistiques
const refreshEmploisStats = async () => {
  await loadEmploisStats()
  stats.value.lastUpdated = new Date()
}

// Aller à la page de génération
const goToPlanning = () => {
  router.push('/Planning')
}

// Charger toutes les données réelles
onMounted(async () => {
  try {
    // Marquer le début du chargement
    stats.value.lastUpdated = new Date()
    
    // Charger en parallèle
    const [filieresRes, etudiantsRes, profsRes] = await Promise.allSettled([
      api.get("/filieres"),
      api.get("/etudiants"),
      api.get("/enseignants")
    ])

    // Traiter les filières
    if (filieresRes.status === 'fulfilled') {
      const data = filieresRes.value.data
      stats.value.filieres = countRealRecords(data)
      
      if (Array.isArray(data)) {
        filieres.value = data
      } else if (data && data.data && Array.isArray(data.data)) {
        filieres.value = data.data
      }
    } else {
      console.error("Erreur filières:", filieresRes.reason)
    }

    // Traiter les étudiants
    if (etudiantsRes.status === 'fulfilled') {
      stats.value.etudiants = countRealRecords(etudiantsRes.value.data)
    } else {
      console.error("Erreur étudiants:", etudiantsRes.reason)
    }

    // Traiter les enseignants
    if (profsRes.status === 'fulfilled') {
      stats.value.enseignants = countRealRecords(profsRes.value.data)
    } else {
      console.error("Erreur enseignants:", profsRes.reason)
    }

    // Charger les statistiques des emplois du temps
    await loadEmploisStats()

    // Vérifier si on a des données
    if (stats.value.etudiants === 0 && stats.value.enseignants === 0 && stats.value.filieres === 0) {
      console.log("Base de données vide, affichage des données de test")
      filieres.value = [
        {
          id: 1,
          nom: "Génie Informatique",
          code: "GI",
          description: "Filière en génie informatique et développement logiciel",
          created_at: new Date().toISOString()
        },
        {
          id: 2,
          nom: "Génie Industriel",
          code: "GIND",
          description: "Filière en génie industriel et management industriel",
          created_at: new Date().toISOString()
        }
      ]
      
      stats.value.filieres = filieres.value.length
    }

  } catch (error) {
    console.error("Erreur générale:", error)
  }
})

// Formater la date
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('fr-FR')
}

// Formater l'heure
const formatTime = (date) => {
  if (!date) return 'N/A'
  return date.toLocaleTimeString('fr-FR', { 
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  })
}

// Calculer le ratio réel
const calculateRatio = () => {
  if (stats.value.enseignants === 0) return '∞'
  const ratio = stats.value.etudiants / stats.value.enseignants
  return ratio.toFixed(1)
}
</script>

<style scoped>
.dashboard {
  padding: 15px;
  margin: 0;
  background: #ffffff;
  min-height: 130vh;
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
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin: 20px 0;
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

.card-status {
  position: absolute;
  top: 15px;
  right: 15px;
  display: flex;
  gap: 5px;
}

.status-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.status-dot.valid {
  background-color: #10b981;
}

.status-dot.waiting {
  background-color: #f59e0b;
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

.card.success h2 {
  color: #10b981;
  background: linear-gradient(45deg, #10b981, #34d399);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Section emplois du temps */
.emplois-section {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  margin: 30px 0;
  overflow: hidden;
}

.section-header {
  padding: 20px 25px;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f8fafc;
}

.section-header h2 {
  color: #1e293b;
  font-size: 1.3rem;
  margin: 0;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.refresh-btn {
  background: linear-gradient(135deg, #3b82f6, #60a5fa);
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
}

.refresh-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.refresh-icon {
  font-size: 1rem;
}

/* Statistiques emplois */
.emplois-stats {
  padding: 25px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 13px;
  margin-bottom: 30px;
}

.stat-card {
  padding: 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.stat-card.total {
  background: linear-gradient(135deg, #e0e7ff, #dbeafe);
  border: 1px solid #c7d2fe;
}

.stat-card.valid {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
  border: 1px solid #86efac;
}

.stat-card.waiting {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border: 1px solid #fcd34d;
}

.stat-card.rejected {
  background: linear-gradient(135deg, #fee2e2, #fecaca);
  border: 1px solid #fca5a5;
}

.stat-icon {
  font-size: 2rem;
  opacity: 0.8;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 2.2rem;
  font-weight: 800;
  line-height: 1;
  margin-bottom: 5px;
}

.stat-label {
  color: #64748b;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 5px;
}

.stat-percentage {
  font-size: 0.8rem;
  color: white;
  background: rgba(0, 0, 0, 0.1);
  padding: 2px 8px;
  border-radius: 10px;
  display: inline-block;
}

/* Distribution par filière */
.filiere-distribution {
  margin-bottom: 30px;
}

.filiere-distribution h3 {
  color: #1e293b;
  font-size: 1.1rem;
  margin-bottom: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.distribution-list {
  background: #f8fafc;
  border-radius: 8px;
  padding: 15px;
  border: 1px solid #e2e8f0;
}

.distribution-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px 0;
  border-bottom: 1px solid #e2e8f0;
}

.distribution-item:last-child {
  border-bottom: none;
}

.filiere-info {
  display: flex;
  flex-direction: column;
  min-width: 150px;
}

.filiere-name {
  color: #1e293b;
  font-weight: 500;
  font-size: 0.95rem;
}

.filiere-code {
  color: #64748b;
  font-size: 0.8rem;
  background: #e2e8f0;
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
  margin-top: 3px;
}

.distribution-stats {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-bar {
  flex: 1;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #373083, #4f46e5);
  border-radius: 4px;
  transition: width 0.3s ease;
}

.stat-count {
  display: flex;
  align-items: center;
  gap: 5px;
  min-width: 80px;
}

.count {
  font-weight: 700;
  color: #373083;
}

.percentage {
  color: #64748b;
  font-size: 0.85rem;
}

/* Derniers emplois */
.recent-emplois h3 {
  color: #1e293b;
  font-size: 1.1rem;
  margin-bottom: 15px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

.emplois-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 15px;
}

.emploi-item {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.2s;
}

.emploi-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.emploi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.emploi-title {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.95rem;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.emploi-status {
  font-size: 0.75rem;
  padding: 3px 8px;
  border-radius: 12px;
  font-weight: 600;
}

.status-valid {
  background: #d1fae5;
  color: #065f46;
}

.status-waiting {
  background: #fef3c7;
  color: #92400e;
}

.status-rejected {
  background: #fee2e2;
  color: #991b1b;
}

.emploi-details {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  font-size: 0.85rem;
  color: #64748b;
}

.detail {
  display: flex;
  align-items: center;
  gap: 5px;
}

.detail-icon {
  opacity: 0.7;
}

/* Bouton action état vide */
.empty-action {
  background: linear-gradient(135deg, #373083, #4f46e5);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s;
  margin-top: 15px;
}

.empty-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(55, 48, 163, 0.2);
}

.action-icon {
  font-size: 1rem;
}

/* Section filières */
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
.emplois-cell {
  width: 80px;
  text-align: center;
}

.emplois-count {
  background: #e0e7ff;
  color: #373083;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
}

.no-emplois {
  color: #94a3b8;
  font-style: italic;
}

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
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .distribution-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .distribution-stats {
    width: 100%;
  }
  
  .emplois-list {
    grid-template-columns: 1fr;
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
  
  .emploi-details {
    flex-direction: column;
    gap: 8px;
  }
}
</style>