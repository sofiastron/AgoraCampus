<script setup>
import api from "../../services/api";
import { ref, onMounted, reactive } from "vue";

const enseignants = ref([]);
const showAddModal = ref(false);
const showEditModal = ref(false);
const selectedEnseignant = ref(null);

// Formulaire pour ajouter un enseignant
const newEnseignant = reactive({
  nom: "",
  email: "",
  grade: "",
  departement: ""
});

// Options pour les grades et départements
const grades = ref(["enseignant", "Maître de conférences", "Maître assistant", "Assistant", "Chargé de cours"]);
const departements = ref(["Informatique", "Mathématiques", "Physique", "Chimie", "Langues", "Droit", "Économie"]);

// Charger la liste des enseignants
const loadEnseignants = async () => {
  try {
    const response = await api.get("/enseignants");
    enseignants.value = response.data;
  } catch (error) {
    console.error("Erreur lors du chargement des enseignants:", error);
    alert("Erreur lors du chargement des enseignants");
  }
};


const addEnseignant = async () => {
  try {
    // Validation simple
    if (!newEnseignant.nom || !newEnseignant.email || !newEnseignant.grade || !newEnseignant.departement) {
      alert("Veuillez remplir tous les champs");
      return;
    }
    
    await api.post("/enseignants", newEnseignant);
    
 
    Object.keys(newEnseignant).forEach(key => {
      newEnseignant[key] = "";
    });
    
    
    showAddModal.value = false;
    loadEnseignants();
    
    alert("Enseignant ajouté avec succès!");
  } catch (error) {
    console.error("Erreur lors de l'ajout:", error);
    alert("Erreur lors de l'ajout de l'enseignant");
  }
};

// Supprimer un enseignant
const deleteEnseignant = async (id, nom) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer l'enseignant ${nom} ?`)) {
    try {
      await api.delete(`/enseignants/${id}`);
      loadEnseignants();
      alert("Enseignant supprimé avec succès!");
    } catch (error) {
      console.error("Erreur lors de la suppression:", error);
      alert("Erreur lors de la suppression de l'enseignant");
    }
  }
};

// Préparer l'édition d'un enseignant
const prepareEdit = (enseignant) => {
  selectedEnseignant.value = { ...enseignant };
  showEditModal.value = true;
};

// Modifier un enseignant (à implémenter si votre backend le permet)
const updateEnseignant = async () => {
  try {
    await api.put(`/enseignants/${selectedEnseignant.value.id}`, {
      grade: selectedEnseignant.value.grade,
      departement: selectedEnseignant.value.departement
    });

    alert("Enseignant modifié avec succès");
    showEditModal.value = false;
    loadEnseignants();
  } catch (error) {
    console.error(error);
    alert("Erreur lors de la modification");
  }
};



// Charger au montage du composant
onMounted(loadEnseignants);
</script>

<template>
  <div class="container">
    <!-- En-tête avec titre et bouton d'ajout -->
    <div class="header">
      <div class="header-content">
        <h1>
          <span class="icon">👨‍🏫</span>
          Gestion des Enseignants
        </h1>
        <p class="subtitle">Administration du corps professoral</p>
      </div>
      <button class="btn-add" @click="showAddModal = true">
        <span class="btn-icon">+</span>
        <span class="btn-text">Ajouter un enseignant</span>
      </button>
    </div>

    <!-- Statistiques -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-content">
          <div class="stat-number">{{ enseignants.length }}</div>
          <div class="stat-label">Enseignants</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div class="stat-content">
          <div class="stat-number">{{ new Set(enseignants.map(e => e.grade)).size }}</div>
          <div class="stat-label">Grades</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🏛️</div>
        <div class="stat-content">
          <div class="stat-number">{{ new Set(enseignants.map(e => e.departement)).size }}</div>
          <div class="stat-label">Départements</div>
        </div>
      </div>
    </div>

    <!-- Tableau des enseignants -->
    <div class="table-container">
      <div class="table-header">
        <h2>Liste des enseignants</h2>
        <div class="table-actions">
          <span class="filter-info">{{ enseignants.length }} enseignant(s) trouvé(s)</span>
        </div>
      </div>

      <div class="table-scroll">
        <table class="enseignants-table">
          <thead>
            <tr>
              <th class="header-cell">ID</th>
              <th class="header-cell">Nom</th>
              <th class="header-cell">Email</th>
              <th class="header-cell">Grade</th>
              <th class="header-cell">Département</th>
              <th class="header-cell actions-header">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="enseignant in enseignants" :key="enseignant.id" class="table-row">
              <td class="data-cell id-cell">{{ enseignant.id }}</td>
              <td class="data-cell name-cell">{{ enseignant.nom }}</td>
              <td class="data-cell email-cell">
                <a :href="`mailto:${enseignant.email}`" class="email-link">
                  {{ enseignant.email }}
                </a>
              </td>
              <td class="data-cell">
                <span class="grade-badge" :class="`grade-${enseignant.grade.toLowerCase().replace(/\s+/g, '-')}`">
                  {{ enseignant.grade }}
                </span>
              </td>
              <td class="data-cell">
                <span class="departement-badge">
                  {{ enseignant.departement }}
                </span>
              </td>
              <td class="data-cell actions-cell">
                <div class="action-buttons">
                  <button class="btn-edit" @click="prepareEdit(enseignant)" title="Modifier">
                    ✏️
                  </button>
                  <button class="btn-delete" @click="deleteEnseignant(enseignant.id, enseignant.nom)" title="Supprimer">
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="enseignants.length === 0" class="empty-row">
              <td colspan="6" class="empty-message">
                <div class="empty-state">
                  <div class="empty-icon">👨‍🏫</div>
                  <h3>Aucun enseignant trouvé</h3>
                  <p>Commencez par ajouter un nouvel enseignant</p>
                  <button class="btn-add-empty" @click="showAddModal = true">
                    Ajouter un enseignant
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal d'ajout d'enseignant -->
    <div v-if="showAddModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h2>Ajouter un nouvel enseignant</h2>
          <button class="modal-close" @click="showAddModal = false">×</button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label for="nom">Nom complet *</label>
            <input 
              type="text" 
              id="nom" 
              v-model="newEnseignant.nom" 
              placeholder="Ex: Jean Dupont"
              class="form-input"
            >
          </div>
          
          <div class="form-group">
            <label for="email">Email *</label>
            <input 
              type="email" 
              id="email" 
              v-model="newEnseignant.email" 
              placeholder="Ex: jean.dupont@universite.com"
              class="form-input"
            >
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="grade">Grade *</label>
              <select id="grade" v-model="newEnseignant.grade" class="form-select">
                <option value="" disabled>Sélectionnez un grade</option>
                <option v-for="grade in grades" :key="grade" :value="grade">
                  {{ grade }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="departement">Département *</label>
              <select id="departement" v-model="newEnseignant.departement" class="form-select">
                <option value="" disabled>Sélectionnez un département</option>
                <option v-for="departement in departements" :key="departement" :value="departement">
                  {{ departement }}
                </option>
              </select>
            </div>
          </div>
          
          <div class="form-info">
            <p>💡 <strong>Information :</strong> Le mot de passe par défaut sera "123456"</p>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn-cancel" @click="showAddModal = false">
            Annuler
          </button>
          <button class="btn-submit" @click="addEnseignant">
            Ajouter l'enseignant
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de modification d'enseignant -->
    <div v-if="showEditModal && selectedEnseignant" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h2>Modifier l'enseignant</h2>
          <button class="modal-close" @click="showEditModal = false">×</button>
        </div>
        
        <div class="modal-body">
          <div class="form-group">
            <label for="edit-nom">Nom complet</label>
            <input 
              type="text" 
              id="edit-nom" 
              v-model="selectedEnseignant.nom" 
              class="form-input"
              disabled
            >
          </div>
          
          <div class="form-group">
            <label for="edit-email">Email</label>
            <input 
              type="email" 
              id="edit-email" 
              v-model="selectedEnseignant.email" 
              class="form-input"
              disabled
            >
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="edit-grade">Grade</label>
              <select id="edit-grade" v-model="selectedEnseignant.grade" class="form-select">
                <option v-for="grade in grades" :key="grade" :value="grade">
                  {{ grade }}
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="edit-departement">Département</label>
              <select id="edit-departement" v-model="selectedEnseignant.departement" class="form-select">
                <option v-for="departement in departements" :key="departement" :value="departement">
                  {{ departement }}
                </option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn-cancel" @click="showEditModal = false">
            Annuler
          </button>
          <button class="btn-submit" @click="updateEnseignant">
            Enregistrer les modifications
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
  min-height: 100vh;
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  padding-bottom: 25px;
  border-bottom: 2px solid rgba(55, 48, 163, 0.1);
}

.header-content h1 {
  color: #373083;
  font-size: 2.5rem;
  font-weight: 800;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 15px;
}

.header-content .icon {
  font-size: 2.8rem;
}

.subtitle {
  color: #666;
  font-size: 1.1rem;
  font-weight: 400;
}

.btn-add {
  background: linear-gradient(135deg, #373083 0%, #4a43a0 100%);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 15px 30px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(55, 48, 163, 0.2);
}

.btn-add:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 20px rgba(55, 48, 163, 0.3);
}

.btn-icon {
  font-size: 1.2rem;
  font-weight: 700;
}

/* Cartes de statistiques */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 16px;
  padding: 25px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 5px 20px rgba(55, 48, 163, 0.08);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  font-size: 2.5rem;
  background: linear-gradient(135deg, #373083 0%, #4a43a0 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.stat-number {
  font-size: 2.2rem;
  font-weight: 800;
  color: #373083;
  line-height: 1;
}

.stat-label {
  color: #666;
  font-size: 0.9rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Tableau */
.table-container {
  background-color: white;
  border-radius: 20px;
  box-shadow: 0 10px 40px rgba(55, 48, 163, 0.1);
  overflow: hidden;
  margin-bottom: 30px;
}

.table-header {
  padding: 25px 30px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.table-header h2 {
  color: #373083;
  font-size: 1.5rem;
  font-weight: 700;
}

.filter-info {
  color: #666;
  font-size: 0.9rem;
  background: #f8f9ff;
  padding: 8px 15px;
  border-radius: 50px;
}

.table-scroll {
  overflow-x: auto;
}

.enseignants-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 900px;
}

.header-cell {
  background: linear-gradient(to bottom, #373083, #2a2568);
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 20px 15px;
  text-align: left;
  border: none;
  position: relative;
}

.header-cell:after {
  content: '';
  position: absolute;
  right: 0;
  top: 25%;
  height: 50%;
  width: 1px;
  background-color: rgba(255, 255, 255, 0.2);
}

.header-cell:last-child:after {
  display: none;
}

.actions-header {
  text-align: center;
}

.table-row {
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.3s ease;
}

.table-row:hover {
  background-color: rgba(55, 48, 163, 0.03);
}

.data-cell {
  padding: 20px 15px;
  color: #333;
  font-size: 1rem;
  border: none;
}

.id-cell {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #666;
}

.name-cell {
  font-weight: 600;
  color: #373083;
}

.email-link {
  color: #4a43a0;
  text-decoration: none;
  transition: color 0.2s;
}

.email-link:hover {
  color: #373083;
  text-decoration: underline;
}

/* Badges */
.grade-badge, .departement-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
}

.grade-badge {
  background-color: #eef2ff;
  color: #373083;
  border: 1px solid rgba(55, 48, 163, 0.2);
}

.departement-badge {
  background-color: #f0f9ff;
  color: #0369a1;
  border: 1px solid rgba(2, 132, 199, 0.2);
}

/* Boutons d'action */
.action-buttons {
  display: flex;
  gap: 10px;
  justify-content: center;
}

.btn-edit, .btn-delete {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.btn-edit {
  background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
}

.btn-edit:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
}

.btn-delete {
  background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
}

.btn-delete:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
}

/* État vide */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.3;
}

.empty-state h3 {
  color: #666;
  margin-bottom: 10px;
  font-size: 1.3rem;
}

.empty-state p {
  color: #888;
  margin-bottom: 25px;
}

.btn-add-empty {
  background: linear-gradient(135deg, #373083 0%, #4a43a0 100%);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 12px 25px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-add-empty:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(55, 48, 163, 0.3);
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal {
  background: white;
  border-radius: 20px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
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

.modal-header {
  padding: 25px 30px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h2 {
  color: #373083;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.8rem;
  color: #666;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.modal-close:hover {
  background-color: #f0f0f0;
  color: #333;
}

.modal-body {
  padding: 30px;
}

.form-group {
  margin-bottom: 25px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #333;
}

.form-input, .form-select {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s;
  background-color: white;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: #373083;
  box-shadow: 0 0 0 3px rgba(55, 48, 163, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-info {
  background-color: #f8f9ff;
  padding: 15px;
  border-radius: 10px;
  margin-top: 20px;
  font-size: 0.9rem;
  color: #666;
}

.modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.btn-cancel, .btn-submit {
  padding: 12px 25px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-cancel {
  background-color: #f0f0f0;
  color: #666;
}

.btn-cancel:hover {
  background-color: #e0e0e0;
}

.btn-submit {
  background: linear-gradient(135deg, #373083 0%, #4a43a0 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(55, 48, 163, 0.2);
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(55, 48, 163, 0.3);
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-row {
  animation: fadeInUp 0.5s ease forwards;
}

.table-row:nth-child(1) { animation-delay: 0.05s; }
.table-row:nth-child(2) { animation-delay: 0.1s; }
.table-row:nth-child(3) { animation-delay: 0.15s; }
.table-row:nth-child(4) { animation-delay: 0.2s; }
.table-row:nth-child(5) { animation-delay: 0.25s; }

/* Responsive */
@media (max-width: 768px) {
  .container {
    padding: 20px;
  }
  
  .header {
    flex-direction: column;
    align-items: flex-start;
    gap: 20px;
  }
  
  .header-content h1 {
    font-size: 2rem;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .modal {
    width: 95%;
    margin: 10px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 8px;
  }
  
  .btn-edit, .btn-delete {
    width: 35px;
    height: 35px;
  }
}
</style>