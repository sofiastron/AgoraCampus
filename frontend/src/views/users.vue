<template>
  <div class="d-flex">
    <NavBar />
    
    <div class="flex-grow-1 p-3">
      
      <!-- En-tête -->
      <div class="dashboard-header mb-4">
        <div>
          <h1 class="fw-bold mb-2">Gestion des utilisateurs</h1>
          <p class="text-muted mb-0">{{ totalUsers }} Utilisateurs</p>
        </div>
      </div>

      <!-- Barre de filtres -->
      <div class="filters-bar mb-4">
        <div class="row g-3">
          <div class="col-md-8">
            <input 
              class="form-control" 
              type="search" 
              placeholder="Rechercher un utilisateur..." 
              v-model="searchQuery"
            >
          </div>
          <div class="col-md-4">
            <div class="d-flex">
              <select v-model="roleFilter" class="form-select me-1">
                <option value="">Tous les rôles</option>
                <option value="etudiant">Étudiant</option>
                <option value="enseignant">Enseignant</option>
                <option value="coordinateur">Coordinateur</option>
                <option value="administrateur">Administrateur</option>
              </select>
              <button type="button" class="btn btn-primary" @click="openAddModal">
                Ajouter
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Ajout -->
      <div v-if="showAddModal" class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Ajouter Utilisateur</h5>
            <button type="button" class="btn-close" @click="closeAddModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addUser">
              <div class="mb-3">
                <input type="text" class="form-control" placeholder="Nom" v-model="form.nom" required>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" v-model="form.email" required>
              </div>
              <div class="mb-3">
                <input type="password" class="form-control" placeholder="Mot de passe" v-model="form.password" required>
              </div>
              <div class="mb-3">
                <select class="form-select" v-model="form.role" required>
                  <option value="">Sélectionner un rôle</option>
                  <option value="etudiant">Étudiant</option>
                  <option value="enseignant">Enseignant</option>
                  <option value="coordinateur">Coordinateur</option>
                  <option value="administrateur">Administrateur</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100">Ajouter</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal Modification -->
      <div v-if="showEditModal" class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Modifier Utilisateur</h5>
            <button type="button" class="btn-close" @click="closeEditModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="updateUser">
              <div class="mb-3">
                <input type="text" class="form-control" placeholder="Nom" v-model="editForm.nom" required>
              </div>
              <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" v-model="editForm.email" required>
              </div>
              <div class="mb-3">
                <input type="password" class="form-control" placeholder="Nouveau mot de passe" v-model="editForm.password">
              </div>
              <div class="mb-3">
                <select class="form-select" v-model="editForm.role" required>
                  <option value="">Sélectionner un rôle</option>
                  <option value="etudiant">Étudiant</option>
                  <option value="enseignant">Enseignant</option>
                  <option value="coordinateur">Coordinateur</option>
                  <option value="administrateur">Administrateur</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
            </form>
          </div>
        </div>
      </div>

      <!-- Modal de confirmation de suppression -->
      <div v-if="showDeleteModal" class="modal-overlay">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title text-danger">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Confirmer la suppression
            </h5>
            <button type="button" class="btn-close" @click="closeDeleteModal"></button>
          </div>
          <div class="modal-body">
            <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{{ userToDelete?.nom }}</strong> ?</p>
            <p class="text-muted">Cette action est irréversible.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeDeleteModal">
              Annuler
            </button>
            <button type="button" class="btn btn-danger" @click="confirmDelete">
              <i class="bi bi-trash me-2"></i>Supprimer
            </button>
          </div>
        </div>
      </div>

      <!-- Tableau des utilisateurs -->
      <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Date d'ajout</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td class="fw-medium">{{ user.nom }}</td>
                  <td>{{ user.email }}</td>
                  <td>
                    <span class="badge" :class="getRoleBadgeClass(user.role)">
                      {{ user.role }}
                    </span>
                  </td>
                  <td>{{ formatDate(user.created_at) }}</td>
                  <td class="text-end">
                    <div class="dropdown">
                      <button class="btn btn-link p-0 border-0" 
                              type="button" 
                              @click="toggleDropdown(user.id)">
                        <i class="bi bi-three-dots-vertical fs-5"></i>
                      </button>
                      
                      <div v-if="activeDropdown === user.id" class="dropdown-menu show" style="display: block; position: absolute; right: 0;">
                        <button class="dropdown-item" @click="openEditModal(user)">
                          <i class="bi bi-pencil-square me-2"></i>Modifier
                        </button>
                        <button class="dropdown-item text-danger" @click="openDeleteModal(user)">
                          <i class="bi bi-trash me-2"></i>Supprimer
                        </button>
                        <button class="dropdown-item" @click="viewUser(user.id)">
                          <i class="bi bi-eye me-2"></i>Voir détails
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr v-if="filteredUsers.length === 0">
                  <td colspan="5" class="text-center text-muted py-4">
                    Aucun utilisateur trouvé.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Notification Toast -->
      <div v-if="showToast" class="toast-container">
        <div class="toast show" :class="toastType">
          <div class="toast-body d-flex justify-content-between align-items-center">
            {{ toastMessage }}
            <button type="button" class="btn-close" @click="showToast = false"></button>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
import axios from 'axios';
import NavBar from '@/components/NavBar.vue';

export default {
  name: 'UsersManagement',
  components: { NavBar },
  
  data() {
    return {
      users: [],
      searchQuery: '',
      roleFilter: '',
      activeDropdown: null,
      showAddModal: false,
      showEditModal: false,
      showDeleteModal: false,
      showToast: false,
      toastMessage: '',
      toastType: 'success',
      form: {
        nom: '',
        email: '',
        role: '',
        password: ''
      },
      editForm: {
        id: null,
        nom: '',
        email: '',
        role: '',
        password: ''
      },
      userToDelete: null
    }
  },
  
  computed: {
    totalUsers() {
      return this.users.length;
    },
    
    filteredUsers() {
      if (!this.searchQuery && !this.roleFilter) return this.users;
      
      return this.users.filter(user => {
        const searchMatch = user.nom.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                           user.email.toLowerCase().includes(this.searchQuery.toLowerCase());
        const roleMatch = !this.roleFilter || 
                         user.role.toLowerCase() === this.roleFilter.toLowerCase();
        return searchMatch && roleMatch;
      });
    }
  },
  
  mounted() {
    this.fetchUsers();
    document.addEventListener('click', this.closeDropdowns);
  },
  
  beforeUnmount() {
    document.removeEventListener('click', this.closeDropdowns);
  },
  
  methods: {
    closeDropdowns(event) {
      if (!event.target.closest('.dropdown')) {
        this.activeDropdown = null;
      }
    },
    
    toggleDropdown(userId) {
      this.activeDropdown = this.activeDropdown === userId ? null : userId;
    },
    
    async fetchUsers() {
      try {
        const response = await axios.get('http://localhost:8000/api/users');
        this.users = response.data;
      } catch (error) {
        console.error("Erreur lors de la récupération des utilisateurs :", error);
        this.showNotification('Erreur lors du chargement des utilisateurs', 'error');
      }
    },
    
    openAddModal() {
      this.form = { nom: '', email: '', role: '', password: '' };
      this.showAddModal = true;
    },
    
    closeAddModal() {
      this.showAddModal = false;
    },
    
    openEditModal(user) {
      this.activeDropdown = null;
      this.editForm = {
        id: user.id,
        nom: user.nom,
        email: user.email,
        role: user.role,
        password: ''
      };
      this.showEditModal = true;
    },
    
    closeEditModal() {
      this.showEditModal = false;
    },
    
    // OUVERTURE DU MODAL DE SUPPRESSION
    openDeleteModal(user) {
      this.activeDropdown = null;
      this.userToDelete = user;
      this.showDeleteModal = true;
    },
    
    closeDeleteModal() {
      this.showDeleteModal = false;
      this.userToDelete = null;
    },
    
    // CONFIRMATION ET SUPPRESSION
  async confirmDelete() {
  if (!this.userToDelete) return;
  
  try {
    // URL CORRECTE : /api/users/{id}
    await axios.delete(`http://localhost:8000/api/users/${this.userToDelete.id}`);
    
    // Message de succès
    this.showNotification('Utilisateur supprimé avec succès', 'success');
    
    // Fermer le modal
    this.closeDeleteModal();
    
    // Rafraîchir la liste
    this.fetchUsers();
    
  } catch (error) {
    console.error('Erreur détaillée:', error);
    
    // Afficher un message d'erreur clair
    if (error.response) {
      this.showNotification(
        `Erreur ${error.response.status}: ${error.response.data?.message || 'Erreur serveur'}`,
        'error'
      );
    } else if (error.request) {
      this.showNotification('Pas de réponse du serveur. Vérifiez que Laravel est démarré.', 'error');
    } else {
      this.showNotification('Erreur: ' + error.message, 'error');
    }
  }
},async addUser() {
  try {
    // Utilisez '/addusers' comme dans votre route API
    await axios.post('http://localhost:8000/api/addusers', this.form);
    this.showNotification('Utilisateur ajouté avec succès', 'success');
    this.closeAddModal();
    this.fetchUsers();
  } catch (error) {
    console.error(error);
    this.showNotification('Erreur lors de l\'ajout', 'error');
  }
},
    
    async updateUser() {
      try {
        const data = { ...this.editForm };
        if (!data.password) delete data.password;
        
        await axios.put(`http://localhost:8000/api/users/${this.editForm.id}`, data);
        this.showNotification('Utilisateur modifié avec succès', 'success');
        this.closeEditModal();
        this.fetchUsers();
      } catch (error) {
        console.error(error);
        this.showNotification('Erreur lors de la modification', 'error');
      }
    },
    
    viewUser(userId) {
      this.activeDropdown = null;
      alert(`Voir les détails de l'utilisateur ID: ${userId}`);
    },
    
    getRoleBadgeClass(role) {
      const roleLower = role.toLowerCase();
      if (roleLower === 'etudiant') return 'bg-primary';
      if (roleLower === 'enseignant') return 'bg-success';
      if (roleLower === 'coordinateur') return 'bg-warning';
      if (roleLower === 'administrateur') return 'bg-danger';
      return 'bg-secondary';
    },
    
    formatDate(dateString) {
      if (!dateString) return '';
      return new Date(dateString).toLocaleDateString('fr-FR');
    },
    
    showNotification(message, type = 'success') {
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;
      
      setTimeout(() => {
        this.showToast = false;
      }, 3000);
    }
  }
}
</script>

<style scoped>
.flex-grow-1 {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.dashboard-header {
  background-color: white;
  padding: 25px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.filters-bar {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card {
  border-radius: 10px;
  overflow: hidden;
}

.table th {
  font-weight: 600;
  padding: 1rem;
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.table td {
  padding: 1rem;
  vertical-align: middle;
}

.badge {
  padding: 0.4em 0.8em;
  font-weight: 500;
  font-size: 0.85rem;
}

/* Dropdown */
.dropdown {
  position: relative;
}

.dropdown-menu {
  min-width: 150px;
  box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
  border: 1px solid rgba(0,0,0,0.1);
}

.dropdown-item {
  cursor: pointer;
  padding: 0.5rem 1rem;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

.dropdown-item.text-danger:hover {
  background-color: #f8d7da;
}

/* Modal overlay */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 10px;
  width: 500px;
  max-width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Toast notification */
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1100;
}

.toast {
  min-width: 300px;
  border: none;
  border-radius: 8px;
  animation: toastSlideIn 0.3s;
}

@keyframes toastSlideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast.success {
  background-color: #28a745;
  color: white;
}

.toast.error {
  background-color: #dc3545;
  color: white;
}

.toast-body {
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>