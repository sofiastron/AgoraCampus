<script setup>
import api from "../../services/api";
import { ref, onMounted, reactive, computed } from "vue";
 // Si vous utilisez Pinia pour l'authentification

// Initialisation du store d'authentification
const enseignants = ref([]);
const selectedEnseignant = ref(null);
const enseignantToDelete = ref(null);

// =================== PAGINATION ===================
const pagination = reactive({
  currentPage: 1,
  itemsPerPage: 10,
  totalItems: 0,
  totalPages: 1
});

// Computed pour la pagination
const paginatedEnseignants = computed(() => {
  const start = (pagination.currentPage - 1) * pagination.itemsPerPage;
  const end = start + pagination.itemsPerPage;
  return enseignants.value.slice(start, end);
});

const pageNumbers = computed(() => {
  const pages = [];
  for (let i = 1; i <= pagination.totalPages; i++) {
    pages.push(i);
  }
  return pages;
});

// Gestion de la pagination
const goToPage = (page) => {
  if (page >= 1 && page <= pagination.totalPages) {
    pagination.currentPage = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const nextPage = () => {
  if (pagination.currentPage < pagination.totalPages) {
    goToPage(pagination.currentPage + 1);
  }
};

const prevPage = () => {
  if (pagination.currentPage > 1) {
    goToPage(pagination.currentPage - 1);
  }
};

const updatePagination = () => {
  pagination.totalItems = enseignants.value.length;
  pagination.totalPages = Math.ceil(pagination.totalItems / pagination.itemsPerPage);
  
  // Si la page actuelle n'existe plus, revenir à la première page
  if (pagination.currentPage > pagination.totalPages && pagination.totalPages > 0) {
    pagination.currentPage = 1;
  }
};
// =================== FIN PAGINATION ===================

// Formulaire pour ajouter un enseignant
const newEnseignant = reactive({
  nom: "",
  email: "",
  specialite: "",
  statut: "permanenent",
  heuresMax: 20,
  user_id: null // Ajout du champ user_id
});

// Options pour les spécialités et statuts
const specialites = ref(["Informatique", "Mathématiques", "Physique", "Chimie", "Langues", "Droit", "Économie"]);
const statuts = ref([
  { value: "vacataire", label: "Vacataire" },
  { value: "permanent", label: "Permanent" } // Ajout du statut "permanent"
]);

// Computed properties pour les statistiques
const enseignantsCount = computed(() => {
  return Array.isArray(enseignants.value) ? enseignants.value.length : 0;
});

// Charger la liste des enseignants
const loadEnseignants = async () => {
  try {
    const response = await api.get("/enseignants");
    let data = response.data;
    
    if (Array.isArray(data)) {
      enseignants.value = data;
    } else if (data && Array.isArray(data.data)) {
      enseignants.value = data.data;
    } else {
      enseignants.value = [];
    }
    
    // Mettre à jour la pagination après le chargement
    updatePagination();
    
  } catch (error) {
    console.error("Erreur lors du chargement des enseignants:", error);
    enseignants.value = [];
    updatePagination();
  }
};

// Fonctions pour gérer les modales
const showModal = (modalId) => {
  const modalElement = document.getElementById(modalId);
  if (modalElement) {
    const modal = new window.bootstrap.Modal(modalElement);
    modal.show();
  }
};

const hideModal = (modalId) => {
  const modalElement = document.getElementById(modalId);
  if (modalElement) {
    const modal = window.bootstrap.Modal.getInstance(modalElement);
    if (modal) {
      modal.hide();
    }
  }
};

// Ouvrir le modal d'ajout
const openAddModal = () => {
  // Réinitialiser le formulaire
  newEnseignant.nom = "";
  newEnseignant.email = "";
  newEnseignant.specialite = "";
  newEnseignant.statut = "actif";
  newEnseignant.heuresMax = 20;
  // Si vous avez un système d'authentification, récupérez l'ID de l'utilisateur connecté
  // newEnseignant.user_id = authStore.user?.id || 1; // Exemple avec Pinia
  
  showModal('addModal');
};

// Ajouter un nouvel enseignant
const addEnseignant = async () => {
  try {
    // Validation
    if (!newEnseignant.nom.trim()) {
      alert("Veuillez saisir le nom de l'enseignant");
      return;
    }
    if (!newEnseignant.email.trim()) {
      alert("Veuillez saisir l'email de l'enseignant");
      return;
    }
    if (!newEnseignant.specialite) {
      alert("Veuillez sélectionner une spécialité");
      return;
    }
    
    // Ajout d'un user_id temporaire (à adapter selon votre logique)
    // Si vous n'avez pas de système d'authentification, vous pouvez :
    // 1. Utiliser un user_id par défaut
    // 2. Demander à l'utilisateur de sélectionner un user
    // 3. Modifier votre backend pour ne pas rendre user_id obligatoire
    
    const enseignantData = {
      ...newEnseignant,
      user_id: 1, // TEMPORAIRE - À REMPLACER PAR LA BONNE LOGIQUE
      statut: newEnseignant.statut // Correction du champ statut
    };
    
    console.log("Données envoyées:", enseignantData);
    
    await api.post("/enseignants", enseignantData);
    
    // Fermer la modale
    hideModal('addModal');
    
    await loadEnseignants();
    
    // Message de succès
    showToast();
  } catch (error) {
    console.error("Erreur lors de l'ajout:", error);
    if (error.response?.data?.message) {
      alert(`Erreur lors de l'ajout de l'enseignant: ${error.response.data.message}`);
    } else {
      alert("Erreur lors de l'ajout de l'enseignant");
    }
  }
};

// Ouvrir le modal de modification
const openEditModal = (enseignant) => {
  selectedEnseignant.value = { 
    ...enseignant,
    id: enseignant.id || enseignant._id || enseignant.ID,
    heuresMax: enseignant.heures_max_semaine || enseignant.heuresMax || 20 // Correction du nom du champ
  };
  
  showModal('editModal');
};

// Modifier un enseignant
const updateEnseignant = async () => {
  if (!selectedEnseignant.value?.id) {
    alert("ID de l'enseignant non trouvé");
    return;
  }
  
  try {
    const dataToUpdate = {
      nom: selectedEnseignant.value.nom,
      email: selectedEnseignant.value.email,
      specialite: selectedEnseignant.value.specialite,
      statut: selectedEnseignant.value.statut,
      heures_max_semaine: selectedEnseignant.value.heuresMax,
      user_id: selectedEnseignant.value.user_id || 1 // TEMPORAIRE
    };
    
    console.log("Données de mise à jour:", dataToUpdate);
    
    await api.put(`/enseignants/${selectedEnseignant.value.id}`, dataToUpdate);

    // Fermer la modale
    hideModal('editModal');
    
    await loadEnseignants();
    
    // Message de succès
    showToast();
  } catch (error) {
    console.error("Erreur lors de la modification:", error);
    if (error.response?.data?.message) {
      alert(`Erreur lors de la modification: ${error.response.data.message}`);
    } else {
      alert("Erreur lors de la modification");
    }
  }
};

// Ouvrir le modal de suppression
const openDeleteModal = (enseignant) => {
  enseignantToDelete.value = {
    id: enseignant.id || enseignant._id || enseignant.ID,
    nom: enseignant.nom || enseignant.Nom || enseignant.name || 'Non spécifié'
  };
  
  showModal('deleteModal');
};

// Supprimer un enseignant
const deleteEnseignant = async () => {
  if (!enseignantToDelete.value?.id) {
    alert("ID de l'enseignant non trouvé");
    return;
  }
  
  try {
    await api.delete(`/enseignants/${enseignantToDelete.value.id}`);
    
    // Fermer la modale
    hideModal('deleteModal');
    
    await loadEnseignants();
    
    // Message de succès
    showToast();
  } catch (error) {
    console.error("Erreur lors de la suppression:", error);
    alert("Erreur lors de la suppression de l'enseignant");
  }
};

// Afficher un toast personnalisé
const showToast = () => {
  const toast = document.getElementById('customToast');
  if (toast) {
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
    }, 3000);
  }
};

// Charger au montage du composant
onMounted(() => {
  loadEnseignants();
});
</script>

<template>
  <div class="container">
    <!-- Toast personnalisé -->
    <div id="customToast" class="custom-toast">
      <div class="toast-content">
        <i class="bi bi-check-circle-fill"></i>
        <span class="message">Opération effectuée avec succès</span>
      </div>
    </div>

    <!-- En-tête -->
    <div class="header">
      <div class="header-content">
        <h1>
          <span class="icon">👨‍🏫</span>
          Gestion des Enseignants
        </h1>
        <p class="subtitle">Administration du corps professoral</p>
      </div>
      <button class="btn-add" @click="openAddModal">
        <span class="btn-icon">+</span>
        <span class="btn-text">Ajouter un enseignant</span>
      </button>
    </div>

    <!-- Statistiques -->
    <div class="stats-cards">
      <div class="stat-card">
        <div class="stat-icon">👨‍🏫</div>
        <div class="stat-content">
          <div class="stat-number">{{ enseignantsCount }}</div>
          <div class="stat-label">Enseignants</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
          <div class="stat-number">{{ new Set(enseignants.map(e => e.statut)).size }}</div>
          <div class="stat-label">Statuts</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div class="stat-content">
          <div class="stat-number">{{ new Set(enseignants.map(e => e.specialite)).size }}</div>
          <div class="stat-label">Spécialités</div>
        </div>
      </div>
    </div>

    <!-- Tableau -->
    <div class="table-container">
      <div class="table-header">
        <div class="table-info">
          <h2>Liste des enseignants</h2>
          <div class="table-actions">
            <span class="filter-info">
              Affichage {{ ((pagination.currentPage - 1) * pagination.itemsPerPage) + 1 }} 
              à {{ Math.min(pagination.currentPage * pagination.itemsPerPage, enseignantsCount) }} 
              sur {{ enseignantsCount }} enseignant(s)
            </span>
          </div>
        </div>
        <div class="table-settings">
          <div class="items-per-page">
            <label for="itemsPerPage">Par page :</label>
            <select id="itemsPerPage" v-model="pagination.itemsPerPage" @change="updatePagination" class="page-select">
              <option value="5">5</option>
              <option value="10">10</option>
              <option value="20">20</option>
              <option value="50">50</option>
            </select>
          </div>
        </div>
      </div>

      <div class="table-scroll">
        <table class="enseignants-table">
          <thead>
            <tr>
              <th class="header-cell">Nom</th>
              <th class="header-cell">Email</th>
              <th class="header-cell">Spécialité</th>
              <th class="header-cell">Statut</th>
              <th class="header-cell actions-header">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="enseignant in paginatedEnseignants" :key="enseignant.id || enseignant._id" class="table-row">
              <td class="data-cell name-cell fw-semibold">{{ enseignant.nom || 'Non spécifié' }}</td>
              <td class="data-cell email-cell">
                <a v-if="enseignant.email" :href="`mailto:${enseignant.email}`" class="email-link">
                  {{ enseignant.email }}
                </a>
                <span v-else class="text-muted">Non spécifié</span>
              </td>
              <td class="data-cell">
                <span v-if="enseignant.specialite" class="departement-badge">
                  {{ enseignant.specialite }}
                </span>
                <span v-else class="text-muted">Non spécifié</span>
              </td>
              <td class="data-cell">
                <span v-if="enseignant.statut" 
                      class="statut-badge"
                      :class="{
                        'statut-actif': enseignant.statut === 'actif',
                        'statut-inactif': enseignant.statut === 'inactif',
                        'statut-conge': enseignant.statut === 'congé',
                        'statut-retraite': enseignant.statut === 'retraite'
                      }">
                  {{ enseignant.statut === 'actif' ? 'Actif' : 
                     enseignant.statut === 'inactif' ? 'Inactif' : 
                     enseignant.statut === 'congé' ? 'En congé' : 
                     enseignant.statut === 'retraite' ? 'Retraité' : enseignant.statut }}
                </span>
                <span v-else class="text-muted">Non spécifié</span>
              </td>
              <td class="data-cell actions-cell">
                <div class="action-buttons">
                  <button class="btn-edit" @click="openEditModal(enseignant)" title="Modifier">
                    ✏️
                  </button>
                  <button class="btn-delete" @click="openDeleteModal(enseignant)" title="Supprimer">
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="enseignants.length === 0" class="empty-row">
              <td colspan="5" class="empty-message">
                <div class="empty-state">
                  <div class="empty-icon">👨‍🏫</div>
                  <h3>Aucun enseignant trouvé</h3>
                  <p>Commencez par ajouter un nouvel enseignant</p>
                  <button class="btn-add-empty" @click="openAddModal">
                    Ajouter un enseignant
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div class="pagination-container" v-if="enseignantsCount > pagination.itemsPerPage">
        <div class="pagination">
          <button 
            class="pagination-btn prev" 
            @click="prevPage" 
            :disabled="pagination.currentPage === 1"
            :class="{ 'disabled': pagination.currentPage === 1 }"
          >
            <span class="btn-icon">←</span>
            Précédent
          </button>
          
          <div class="page-numbers">
            <button 
              v-for="page in pageNumbers" 
              :key="page" 
              @click="goToPage(page)"
              :class="['page-number', { 'active': page === pagination.currentPage }]"
              :aria-label="`Page ${page}`"
            >
              {{ page }}
            </button>
          </div>
          
          <button 
            class="pagination-btn next" 
            @click="nextPage" 
            :disabled="pagination.currentPage === pagination.totalPages"
            :class="{ 'disabled': pagination.currentPage === pagination.totalPages }"
          >
            Suivant
            <span class="btn-icon">→</span>
          </button>
        </div>
        
        <div class="pagination-info">
          <span class="page-info">
            Page {{ pagination.currentPage }} sur {{ pagination.totalPages }}
          </span>
          <span class="items-info">
            {{ enseignantsCount }} enseignant(s) au total
          </span>
        </div>
      </div>
    </div>

    <!-- Modal d'ajout (Bootstrap) -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
          <div class="modal-header">
            <h2 class="modal-title">Ajouter un nouvel enseignant</h2>
            <button type="button" class="btn-close custom-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="nom" class="form-label">Nom complet *</label>
              <input 
                type="text" 
                id="nom" 
                v-model="newEnseignant.nom" 
                placeholder="Ex: Jean Dupont"
                class="form-input"
                required
              >
            </div>
            
            <div class="form-group">
              <label for="email" class="form-label">Email *</label>
              <input 
                type="email" 
                id="email" 
                v-model="newEnseignant.email" 
                placeholder="Ex: jean.dupont@universite.com"
                class="form-input"
                required
              >
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="specialite" class="form-label">Spécialité *</label>
                <select id="specialite" v-model="newEnseignant.specialite" class="form-select" required>
                  <option value="" disabled>Sélectionnez une spécialité</option>
                  <option v-for="specialite in specialites" :key="specialite" :value="specialite">
                    {{ specialite }}
                  </option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="statut" class="form-label">Statut *</label>
                <select id="statut" v-model="newEnseignant.statut" class="form-select" required>
                  <option value="" disabled>Sélectionnez un statut</option>
                  <option v-for="statut in statuts" :key="statut.value" :value="statut.value">
                    {{ statut.label }}
                  </option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="heuresMax" class="form-label">Heures maximum par semaine *</label>
              <input 
                type="number" 
                id="heuresMax" 
                v-model.number="newEnseignant.heuresMax" 
                class="form-input"
                min="1" 
                max="40"
                placeholder="Ex: 20"
                required
              >
              <div class="form-info">
                <p>💡 Entre 1 et 40 heures par semaine</p>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" data-bs-dismiss="modal">
              Annuler
            </button>
            <button class="btn-submit" @click="addEnseignant">
              Ajouter l'enseignant
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de modification (Bootstrap) -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
          <div class="modal-header">
            <h2 class="modal-title">Modifier l'enseignant</h2>
            <button type="button" class="btn-close custom-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="selectedEnseignant">
            <div class="form-group">
              <label for="edit-nom" class="form-label">Nom complet</label>
              <input 
                type="text" 
                id="edit-nom" 
                v-model="selectedEnseignant.nom" 
                class="form-input"
                required
              >
            </div>
            
            <div class="form-group">
              <label for="edit-email" class="form-label">Email</label>
              <input 
                type="email" 
                id="edit-email" 
                v-model="selectedEnseignant.email" 
                class="form-input"
                required
              >
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="edit-specialite" class="form-label">Spécialité</label>
                <select id="edit-specialite" v-model="selectedEnseignant.specialite" class="form-select" required>
                  <option value="" disabled>Sélectionnez une spécialité</option>
                  <option v-for="specialite in specialites" :key="specialite" :value="specialite">
                    {{ specialite }}
                  </option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="edit-statut" class="form-label">Statut</label>
                <select id="edit-statut" v-model="selectedEnseignant.statut" class="form-select" required>
                  <option value="" disabled>Sélectionnez un statut</option>
                  <option v-for="statut in statuts" :key="statut.value" :value="statut.value">
                    {{ statut.label }}
                  </option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="edit-heuresMax" class="form-label">Heures maximum par semaine</label>
              <input 
                type="number" 
                id="edit-heuresMax" 
                v-model.number="selectedEnseignant.heuresMax" 
                class="form-input"
                min="1" 
                max="40"
                required
              >
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" data-bs-dismiss="modal">
              Annuler
            </button>
            <button class="btn-submit" @click="updateEnseignant">
              Enregistrer les modifications
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de suppression (Bootstrap) -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-modal">
          <div class="modal-header warning-header">
            <h2 class="modal-title">Confirmer la suppression</h2>
            <button type="button" class="btn-close custom-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" v-if="enseignantToDelete">
            <div class="warning-content">
              <div class="warning-icon">⚠️</div>
              <h3>Êtes-vous sûr de vouloir supprimer cet enseignant ?</h3>
              <p class="warning-text">
                Vous êtes sur le point de supprimer l'enseignant 
                <strong>"{{ enseignantToDelete.nom }}"</strong>.
                <br>Cette action est irréversible.
              </p>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-cancel" data-bs-dismiss="modal">
              Annuler
            </button>
            <button class="btn-delete-modal" @click="deleteEnseignant">
              Supprimer 
            </button>
          </div>
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
  flex-wrap: wrap;
  gap: 15px;
}

.table-info {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.table-info h2 {
  color: #373083;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.table-settings {
  display: flex;
  align-items: center;
  gap: 15px;
}

.items-per-page {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
  font-size: 0.9rem;
}

.page-select {
  padding: 6px 12px;
  border: 1px solid #e0e0e0;
  border-radius: 6px;
  background: white;
  font-size: 0.9rem;
  color: #333;
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
  min-width: 800px;
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
.departement-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
  background-color: #f0f9ff;
  color: #0369a1;
  border: 1px solid rgba(2, 132, 199, 0.2);
}

.statut-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
}

.statut-actif {
  background-color: #e6f7ee;
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.statut-inactif {
  background-color: #f0f0f0;
  color: #666;
  border: 1px solid rgba(102, 102, 102, 0.2);
}

.statut-conge {
  background-color: #fff7e6;
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.statut-retraite {
  background-color: #e6f3ff;
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
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

/* Toast personnalisé */
.custom-toast {
  position: fixed;
  top: 20px;
  right: 20px;
  background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
  color: white;
  padding: 15px 25px;
  border-radius: 10px;
  box-shadow: 0 5px 20px rgba(16, 185, 129, 0.3);
  z-index: 10000;
  display: none;
  animation: slideInRight 0.3s ease;
}

.custom-toast.show {
  display: block;
  animation: slideInRight 0.3s ease;
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* ============ PAGINATION ============ */
.pagination-container {
  padding: 20px 30px;
  border-top: 1px solid #f0f0f0;
  background: #f8fafc;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 15px;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: 1px solid #e0e0e0;
  background: white;
  color: #666;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(.disabled) {
  border-color: #373083;
  color: #373083;
  background: #f0f2ff;
}

.pagination-btn.disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 5px;
}

.page-number {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #e0e0e0;
  background: white;
  color: #666;
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
}

.page-number:hover:not(.active) {
  border-color: #373083;
  color: #373083;
  background: #f0f2ff;
}

.page-number.active {
  background: linear-gradient(135deg, #373083 0%, #4a43a0 100%);
  color: white;
  border-color: #373083;
  font-weight: 600;
}

.pagination-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: #666;
  font-size: 0.85rem;
  padding-top: 10px;
  border-top: 1px solid #e0e0e0;
}

.page-info {
  font-weight: 500;
}

.items-info {
  color: #888;
}
/* ============ FIN PAGINATION ============ */

/* Modales Bootstrap personnalisées */
.modal {
  --bs-modal-zindex: 1055;
  --bs-modal-width: 600px;
  --bs-modal-padding: 2px;
  --bs-modal-margin: 2px;
  --bs-modal-bg: transparent;
  --bs-modal-border-color: transparent;
  --bs-modal-border-width: 0;
  --bs-modal-border-radius: 20px;
  --bs-modal-box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  --bs-modal-inner-border-radius: 20px;
  --bs-modal-header-padding-x: 0;
  --bs-modal-header-padding-y: 0;
  --bs-modal-header-border-color: transparent;
  --bs-modal-footer-border-color: transparent;
}

.modal-dialog-centered {
  display: flex;
  align-items: center;
  min-height: calc(100% - var(--bs-modal-margin) * 2);
}

.custom-modal {
  background: white;
  border-radius: 20px;
  box-shadow: var(--bs-modal-box-shadow);
  overflow: hidden;
}

.custom-modal .modal-header {
  padding: 25px 50px;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.custom-modal .modal-header h2 {
  color: #373083;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
}

.custom-modal .warning-header h2 {
  color: #dc2626;
}

.custom-close {
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
  opacity: 1;
}

.custom-close:hover {
  background-color: #f0f0f0;
  color: #333;
}

.custom-modal .modal-body {
  padding: 30px;
}

.warning-content {
  text-align: center;
  padding: 20px 0;
}

.warning-icon {
  font-size: 4rem;
  margin-bottom: 20px;
  color: #dc2626;
}

.warning-text {
  color: #666;
  line-height: 1.6;
  margin-top: 15px;
}

/* Formulaires dans les modales */
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
  padding: 10px 10px;
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
  gap: 10px;
}


.custom-modal .modal-footer {
  padding: 20px 30px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: flex-end;
  gap: 15px;
}

.btn-cancel, .btn-submit, .btn-delete-modal {
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

.btn-delete-modal {
  background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);
}

.btn-delete-modal:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(220, 38, 38, 0.3);
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
  
  .table-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .table-settings {
    width: 100%;
    justify-content: space-between;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .modal {
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
  
  .custom-modal .modal-header,
  .custom-modal .modal-body,
  .custom-modal .modal-footer {
    padding: 20px;
  }
  
  .pagination {
    flex-wrap: wrap;
  }
  
  .page-numbers {
    order: 3;
    width: 100%;
    justify-content: center;
    margin-top: 10px;
  }
  
  .pagination-btn {
    order: 1;
  }
  
  .pagination-btn.next {
    order: 2;
  }
  
  .pagination-info {
    flex-direction: column;
    gap: 5px;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .page-number {
    width: 35px;
    height: 35px;
    font-size: 0.85rem;
  }
  
  .pagination-btn {
    padding: 8px 15px;
    font-size: 0.85rem;
  }
}
</style>