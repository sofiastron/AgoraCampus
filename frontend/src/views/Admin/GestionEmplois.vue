<template>
  <div class="container">
    <!-- Header élégant avec fond gradient -->
    <div class="header-section">
      <div class="header-gradient">
        <div class="header-content">
          <div class="title-wrapper">
            <div class="title-icon">📅</div>
            <div>
              <h1 class="main-title">Validation des Emplois du Temps</h1>
              <p class="subtitle-text">Visualisez, modifiez et validez les emplois du temps</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtres -->
    <div class="filter-card">
      <div class="filter-header">
        <h3 class="filter-title">
          <span class="filter-icon">🔍</span>
          Filtres
        </h3>
      </div>
      
      <div class="filter-grid">
        <div class="filter-item">
          <label class="filter-label">
            <span class="label-icon">🏫</span>
            Filière
          </label>
          <select v-model="filtreFiliere" @change="chargerEmplois" class="filter-select">
            <option value="">Toutes les filières</option>
            <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
          </select>
        </div>
        
        <div class="filter-item">
          <label class="filter-label">
            <span class="label-icon">📚</span>
            Semestre
          </label>
          <select v-model="filtreSemestre" @change="chargerEmplois" class="filter-select">
            <option value="">Tous les semestres</option>
            <option v-for="s in semestres" :key="s">{{ s }}</option>
          </select>
        </div>
        
        <div class="filter-item">
          <label class="filter-label">
            <span class="label-icon">📊</span>
            Statut
          </label>
          <select v-model="filtreStatut" @change="chargerEmplois" class="filter-select">
            <option value="">Tous les statuts</option>
            <option value="en_attente">En attente</option>
            <option value="valide">Validé</option>
            <option value="rejeté">Rejeté</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div v-if="errorMessage" class="message-toast error show">
      <div class="toast-content">
        <div class="toast-icon error">⚠️</div>
        <div class="toast-message">
          <strong>Erreur</strong>
          <p>{{ errorMessage }}</p>
        </div>
      </div>
      <button @click="errorMessage = ''" class="toast-close">×</button>
    </div>

    <div v-if="successMessage" class="message-toast success show">
      <div class="toast-content">
        <div class="toast-icon success">✅</div>
        <div class="toast-message">
          <strong>Succès</strong>
          <p>{{ successMessage }}</p>
        </div>
      </div>
      <button @click="successMessage = ''" class="toast-close">×</button>
    </div>

    <!-- Tableau principal -->
    <div class="main-card">
      <div class="card-header">
        <div class="header-left">
          <h2 class="card-title">
            <span class="title-icon">📋</span>
            Liste des emplois ({{ emplois.length }})
          </h2>
        </div>
      </div>

      <!-- État de chargement -->
      <div v-if="loading" class="loading-state">
        <div class="spinner-container">
          <div class="spinner"></div>
        </div>
        <p class="loading-text">Chargement...</p>
      </div>

      <!-- État vide -->
      <div v-else-if="emplois.length === 0" class="empty-state-card">
        <div class="empty-illustration">📭</div>
        <h3 class="empty-title">Aucun emploi du temps trouvé</h3>
        <p class="empty-description">
          Aucun emploi du temps ne correspond à vos critères.
        </p>
      </div>

      <!-- Liste des emplois -->
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th class="table-header">
                <div class="header-content">
                  📝 Titre
                </div>
              </th>
              <th class="table-header">
                <div class="header-content">
                  🏫 Filière
                </div>
              </th>
              <th class="table-header">
                <div class="header-content">
                  📚 Semestre
                </div>
              </th>
              <th class="table-header">
                <div class="header-content">
                  🎓 Niveau
                </div>
              </th>
              
              <th class="table-header">
                <div class="header-content">
                  📊 Statut
                </div>
              </th>
              <th class="table-header actions">
                <div class="header-content">
                  ⚙️ Actions
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="emploi in emplois" :key="emploi.id" class="table-row">
              <td class="table-cell">
                <div class="cell-content">
                  <div class="cell-title">{{ emploi.titre || 'Sans titre' }}</div>
                  <div class="cell-subtitle">{{ emploi.description || 'Aucune description' }}</div>
                </div>
              </td>
              <td class="table-cell">
                <div class="filiere-badge">
                  {{ emploi.filiere?.nom || 'Non spécifié' }}
                </div>
              </td>
              <td class="table-cell">
                <span class="semestre-tag">{{ emploi.semestre }}</span>
              </td>
              <td class="table-cell">
                <div class="niveau-info">{{ emploi.niveau || 'Tous niveaux' }}</div>
              </td>
              
              
              <td class="table-cell">
                <div class="status-container">
                  <span class="status-badge" :class="getStatusBadgeClass(emploi.statut)">
                    <span class="status-icon">{{ getStatusIcon(emploi.statut) }}</span>
                    {{ getStatusLabel(emploi.statut) }}
                  </span>
                </div>
              </td>
              <td class="table-cell actions-cell">
                <div class="actions-container">
                  <!-- Bouton Valider (seulement si en attente) -->
                  <button v-if="emploi.statut === 'en_attente'" 
                          @click="validerEmploi(emploi)" 
                          class="action-btn validate-btn"
                          :title="`Valider : ${emploi.titre}`">
                    <span class="btn-icon">✅</span>
                    <span class="btn-text">Valider</span>
                  </button>
                  
                  <!-- Boutons Modifier/Supprimer (seulement si non validé) -->
                  <div v-if="emploi.statut !== 'valide'" class="action-group">
                    <button @click="ouvrirEditModal(emploi)" 
                            class="action-btn edit-btn"
                            :title="`Modifier : ${emploi.titre}`">
                      <span class="btn-icon">✏️</span>
                    </button>
                    <button @click="confirmerSuppression(emploi)" 
                            class="action-btn delete-btn"
                            :title="`Supprimer : ${emploi.titre}`">
                      <span class="btn-icon">🗑️</span>
                    </button>
                  </div>
                  
                  <!-- Message quand validé -->
                  <div v-if="emploi.statut === 'valide'" class="validated-status">
                    <span class="validated-icon">✅</span>
                    <span class="validated-text">Validé</span>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL D'ÉDITION -->
    <div v-if="showEditModal" class="modal-backdrop">
      <div class="modal-content-large">
        <div class="modal-header">
          <h5>Modification : {{ emploiEnEdition.titre }}</h5>
          <button @click="fermerEditModal" class="btn-close"></button>
        </div>
        
        <div class="modal-body">
          <!-- En-tête info -->
          <div class="mb-4">
            <h4>{{ emploiEnEdition.titre || 'Sans titre' }}</h4>
            <div class="d-flex gap-2 mb-2">
              <span class="badge bg-secondary">{{ emploiEnEdition.filiere?.nom }}</span>
              <span class="badge bg-primary">{{ emploiEnEdition.semestre }}</span>
              <span class="badge bg-warning">{{ emploiEnEdition.niveau || 'Tous' }}</span>
              <span class="badge" :class="getStatusBadgeClass(emploiEnEdition.statut)">
                {{ getStatusLabel(emploiEnEdition.statut) }}
              </span>
            </div>
            <p class="text-muted">{{ emploiEnEdition.description }}</p>
            
            <div class="alert alert-light border">
              <strong>Instructions :</strong> Cliquez sur n'importe quelle case pour modifier. La pause déjeuner (12h-14h) reste vide.
            </div>
          </div>
          
          <!-- Navigation groupes -->
          <div v-if="Object.keys(emploiEdit.schedule).length > 1" class="mb-4">
            <div class="btn-group">
              <button v-for="(_, groupe) in emploiEdit.schedule" 
                      :key="groupe"
                      @click="groupeActifEdit = groupe"
                      :class="['btn btn-sm', groupeActifEdit === groupe ? 'btn-primary' : 'btn-outline-secondary']">
                {{ groupe }}
              </button>
            </div>
          </div>

          <!-- Tableau emploi du temps -->
          <div v-for="(schedule, groupe) in emploiEdit.schedule" :key="groupe">
            <div v-if="!groupeActifEdit || groupeActifEdit === groupe" class="mb-4">
              <div class="d-flex justify-content-between mb-3">
                <h6>
                  <span class="badge bg-secondary">{{ groupe }}</span>
                  <small class="text-muted ms-2">{{ calculerHeuresGroupe(groupe) }}h total</small>
                </h6>
                <small class="text-muted">{{ getGroupModules(groupe).length }} modules</small>
              </div>

              <!-- Tableau -->
              <div class="table-responsive">
                <table class="table table-bordered table-sm">
                  <thead>
                    <tr>
                      <th>Jour</th>
                      <th>08h-10h</th>
                      <th>10h-12h</th>
                      <th>12h-14h</th>
                      <th>14h-16h</th>
                      <th>16h-18h</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="jour in jours" :key="jour">
                      <td class="fw-bold">{{ jour }}</td>
                      
                      <!-- Colonnes pour chaque créneau -->
                      <td v-for="creneau in creneaux" :key="creneau" class="cellule">
                        <!-- PAUSE DÉJEUNER -->
                        <div v-if="creneau === '12h-14h'" class="pause">
                          Pause<br>
                          <small>12h-14h</small>
                        </div>
                        
                        <!-- CRÉNEAUX NORMALS -->
                        <div v-else class="cellule-module">
                          <!-- Module existant -->
                          <div v-if="schedule?.[jour]?.[creneau] && schedule[jour][creneau].trim() !== ''">
                            <!-- Input module -->
                            <input type="text" 
                                   v-model="emploiEdit.schedule[groupe][jour][creneau]"
                                   class="form-control form-control-sm mb-1"
                                   placeholder="Module">
                            
                            <!-- Enseignant -->
                            <div class="input-group input-group-sm mb-1">
                              <span class="input-group-text">👨‍🏫</span>
                              <input type="text" 
                                     :value="getEnseignant(groupe, jour, creneau)"
                                     @input="setEnseignant(groupe, jour, creneau, $event.target.value)"
                                     class="form-control"
                                     placeholder="Enseignant">
                            </div>
                            
                            <!-- Salle -->
                            <div class="input-group input-group-sm mb-1">
                              <span class="input-group-text">🏫</span>
                              <input type="text" 
                                     :value="getSalle(groupe, jour, creneau)"
                                     @input="setSalle(groupe, jour, creneau, $event.target.value)"
                                     class="form-control"
                                     placeholder="Salle">
                            </div>
                            
                            <!-- Semaines -->
                            <div v-if="emploiEdit.semaines[groupe]?.[jour]?.[creneau]" class="mt-1">
                              <div class="input-group input-group-sm">
                                <span class="input-group-text">📅</span>
                                <input type="number" 
                                       v-model.number="emploiEdit.semaines[groupe][jour][creneau].semaine_debut"
                                       class="form-control"
                                       placeholder="Début"
                                       min="1" max="52">
                                <span class="input-group-text">-</span>
                                <input type="number" 
                                       v-model.number="emploiEdit.semaines[groupe][jour][creneau].semaine_fin"
                                       class="form-control"
                                       placeholder="Fin"
                                       min="1" max="52">
                              </div>
                            </div>
                          </div>
                          
                          <!-- Cellule vide -->
                          <div v-else>
                            <input type="text" 
                                   v-model="emploiEdit.schedule[groupe][jour][creneau]"
                                   class="form-control form-control-sm mb-1"
                                   placeholder="Ajouter module">
                            
                            <div class="input-group input-group-sm mb-1">
                              <span class="input-group-text">👨‍🏫</span>
                              <input type="text" 
                                     :value="getEnseignant(groupe, jour, creneau)"
                                     @input="setEnseignant(groupe, jour, creneau, $event.target.value)"
                                     class="form-control"
                                     placeholder="Enseignant">
                            </div>
                            
                            <div class="input-group input-group-sm">
                              <span class="input-group-text">🏫</span>
                              <input type="text" 
                                     :value="getSalle(groupe, jour, creneau)"
                                     @input="setSalle(groupe, jour, creneau, $event.target.value)"
                                     class="form-control"
                                     placeholder="Salle">
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="fermerEditModal" class="btn btn-secondary">Fermer</button>
          <button @click="sauvegarderModifications" class="btn btn-primary" :disabled="sauvegardeEnCours || !aModifications()">
            <span v-if="sauvegardeEnCours">
              <span class="spinner-border spinner-border-sm me-1"></span>
              Sauvegarde...
            </span>
            <span v-else>
              Enregistrer
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Modal suppression -->
    <div v-if="showDeleteModal" class="modal-backdrop">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="text-danger">Confirmer la suppression</h5>
          <button @click="showDeleteModal = false" class="btn-close"></button>
        </div>
        <div class="modal-body">
          <p>Supprimer l'emploi :</p>
          <div class="alert alert-warning">
            <strong>{{ emploiASupprimer?.titre || 'Sans titre' }}</strong><br>
            <small>
              Filière: {{ emploiASupprimer?.filiere?.nom }}<br>
              Semestre: {{ emploiASupprimer?.semestre }}<br>
              Niveau: {{ emploiASupprimer?.niveau }}
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showDeleteModal = false" class="btn btn-secondary">Annuler</button>
          <button @click="effectuerSuppression" class="btn btn-danger">
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'GestionEmplois',
  data() {
    return {
      emplois: [],
      filieres: [],
      selection: [],
      filtreFiliere: '',
      filtreSemestre: '',
      filtreStatut: '',
      loading: false,
      errorMessage: '',
      successMessage: '',
      showEditModal: false,
      showDeleteModal: false,
      emploiASupprimer: null,
      emploiEnEdition: null,
      emploiEdit: {
        schedule: {},
        affectations: {},
        semaines: {},
        statistics: {}
      },
      groupeActifEdit: '',
      sauvegardeEnCours: false,
      jours: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
      creneaux: ['08h-10h', '10h-12h', '12h-14h', '14h-16h', '16h-18h'],
      celluleFocusActive: null,
      semestres: ['S1', 'S2', 'S3', 'S4', 'S5', 'S6']
    };
  },
  created() {
    this.chargerFilieres();
    this.chargerEmplois();
  },
  methods: {
    // === VALIDATION ===
    async validerEmploi(emploi) {
      try {
        const response = await axios.post(`/api/planning/${emploi.id}/validate`, {
          statut: 'valide'
        });
        
        if (response.data?.success) {
          this.successMessage = `Emploi "${emploi.titre}" validé avec succès !`;
          emploi.statut = 'valide';
          await this.chargerEmplois();
          setTimeout(() => { this.successMessage = ''; }, 3000);
        } else {
          this.errorMessage = 'Erreur lors de la validation : ' + (response.data?.message || 'Erreur serveur');
        }
      } catch (error) {
        console.error('Erreur détaillée:', error);
        this.errorMessage = 'Erreur : ' + (error.response?.data?.message || error.message || 'Erreur inconnue');
      }
    },

    // === SUPPRESSION ===
    confirmerSuppression(emploi) {
      this.emploiASupprimer = emploi;
      this.showDeleteModal = true;
    },

    async effectuerSuppression() {
      try {
        await axios.delete(`/api/planning/saved/${this.emploiASupprimer.id}`);
        this.successMessage = `Emploi "${this.emploiASupprimer.titre}" supprimé`;
        this.showDeleteModal = false;
        this.emploiASupprimer = null;
        await this.chargerEmplois();
      } catch (error) {
        this.errorMessage = 'Erreur suppression: ' + error.message;
        this.showDeleteModal = false;
      }
    },

    // === CHARGEMENT ===
    async chargerFilieres() {
      try {
        const response = await axios.get('/api/planning/filieres');
        if (response.data?.success) this.filieres = response.data.data || [];
      } catch (error) {
        console.error('Erreur chargement filières:', error);
      }
    },
    
    async chargerEmplois() {
      this.loading = true;
      try {
        const params = {};
        if (this.filtreFiliere) params.filiere_id = this.filtreFiliere;
        if (this.filtreSemestre) params.semestre = this.filtreSemestre;
        if (this.filtreStatut) params.statut = this.filtreStatut;
        
        const response = await axios.get('/api/planning/saved', { params });
        if (response.data?.success) {
          this.emplois = response.data.data || [];
        } else {
          this.emplois = [];
        }
      } catch (error) {
        console.error('Erreur chargement emplois:', error);
        this.emplois = [];
      } finally {
        this.loading = false;
      }
    },
    
    // === ÉDITION ===
    async ouvrirEditModal(emploi) {
      try {
        const response = await axios.get(`/api/planning/saved/${emploi.id}`);
        if (!response.data?.success) {
          this.errorMessage = 'Impossible de charger cet emploi';
          return;
        }
        
        this.emploiEnEdition = response.data.data;
        
        let scheduleData = this.emploiEnEdition.schedule || this.emploiEnEdition.horaires || {};
        
        if (typeof scheduleData === 'string') {
          try {
            scheduleData = JSON.parse(scheduleData);
          } catch (e) {
            scheduleData = {};
          }
        }
        
        if (!scheduleData || Object.keys(scheduleData).length === 0) {
          scheduleData = {};
          const affectationsData = this.emploiEnEdition.affectations || {};
          
          Object.keys(affectationsData).forEach(groupe => {
            scheduleData[groupe] = {};
            this.jours.forEach(jour => {
              scheduleData[groupe][jour] = {};
              this.creneaux.forEach(creneau => {
                const affectation = affectationsData[groupe]?.[jour]?.[creneau];
                let moduleName = '';
                if (affectation?.module) moduleName = affectation.module;
                else if (affectation?.nom) moduleName = affectation.nom;
                else if (affectation?.enseignant?.module) moduleName = affectation.enseignant.module;
                scheduleData[groupe][jour][creneau] = moduleName;
              });
            });
          });
        }
        
        if (Object.keys(scheduleData).length === 0) {
          const filiereCode = this.emploiEnEdition.filiere?.code || 'GI';
          const niveau = this.emploiEnEdition.niveau || 'N2';
          const niveauMatch = niveau.match(/(\d+)/);
          const niveauNum = niveauMatch ? `N${niveauMatch[1]}` : 'N2';
          const groupeParDefaut = `${filiereCode}-${niveauNum}`;
          scheduleData = { [groupeParDefaut]: {} };
          
          this.jours.forEach(jour => {
            scheduleData[groupeParDefaut][jour] = {};
            this.creneaux.forEach(creneau => {
              scheduleData[groupeParDefaut][jour][creneau] = '';
            });
          });
        }
        
        let affectationsData = this.emploiEnEdition.affectations || {};
        let semainesData = this.emploiEnEdition.semaines || {};
        
        if (typeof affectationsData === 'string') {
          try { affectationsData = JSON.parse(affectationsData); } catch (e) { affectationsData = {}; }
        }
        if (typeof semainesData === 'string') {
          try { semainesData = JSON.parse(semainesData); } catch (e) { semainesData = {}; }
        }
        
        this.emploiEdit = {
          schedule: JSON.parse(JSON.stringify(scheduleData)),
          affectations: JSON.parse(JSON.stringify(affectationsData)),
          semaines: JSON.parse(JSON.stringify(semainesData)),
          statistics: JSON.parse(JSON.stringify(this.emploiEnEdition.statistics || {}))
        };
        
        this.initialiserStructures();
        
        const groupes = Object.keys(this.emploiEdit.schedule);
        this.groupeActifEdit = groupes.length > 0 ? groupes[0] : '';
        this.showEditModal = true;
        
      } catch (error) {
        this.errorMessage = 'Erreur: ' + (error.response?.data?.message || error.message);
      }
    },
    
    initialiserStructures() {
      Object.keys(this.emploiEdit.schedule).forEach(groupe => {
        if (!this.emploiEdit.schedule[groupe]) this.emploiEdit.schedule[groupe] = {};
        this.jours.forEach(jour => {
          if (!this.emploiEdit.schedule[groupe][jour]) this.emploiEdit.schedule[groupe][jour] = {};
          this.creneaux.forEach(creneau => {
            if (this.emploiEdit.schedule[groupe][jour][creneau] === undefined) {
              this.emploiEdit.schedule[groupe][jour][creneau] = '';
            }
          });
        });
        
        if (!this.emploiEdit.affectations[groupe]) this.emploiEdit.affectations[groupe] = {};
        if (!this.emploiEdit.semaines[groupe]) this.emploiEdit.semaines[groupe] = {};
        
        this.jours.forEach(jour => {
          if (!this.emploiEdit.affectations[groupe][jour]) this.emploiEdit.affectations[groupe][jour] = {};
          if (!this.emploiEdit.semaines[groupe][jour]) this.emploiEdit.semaines[groupe][jour] = {};
          
          this.creneaux.forEach(creneau => {
            if (!this.emploiEdit.affectations[groupe][jour][creneau]) {
              this.emploiEdit.affectations[groupe][jour][creneau] = {
                enseignant: { nom: '' },
                salle: { nom: '' }
              };
            }
            if (!this.emploiEdit.semaines[groupe][jour][creneau]) {
              this.emploiEdit.semaines[groupe][jour][creneau] = {
                semaine_debut: null,
                semaine_fin: null
              };
            }
          });
        });
      });
    },
    
    getEnseignant(groupe, jour, creneau) {
      const affectation = this.emploiEdit.affectations[groupe]?.[jour]?.[creneau];
      if (!affectation) return '';
      if (affectation.enseignant?.nom) return affectation.enseignant.nom;
      if (affectation.enseignant) return affectation.enseignant;
      return '';
    },

    getSalle(groupe, jour, creneau) {
      const affectation = this.emploiEdit.affectations[groupe]?.[jour]?.[creneau];
      if (!affectation) return '';
      if (affectation.salle?.nom) return affectation.salle.nom;
      if (affectation.salle) return affectation.salle;
      return '';
    },
    
    setEnseignant(groupe, jour, creneau, valeur) {
      if (!this.emploiEdit.affectations[groupe]) this.emploiEdit.affectations[groupe] = {};
      if (!this.emploiEdit.affectations[groupe][jour]) this.emploiEdit.affectations[groupe][jour] = {};
      if (!this.emploiEdit.affectations[groupe][jour][creneau]) this.emploiEdit.affectations[groupe][jour][creneau] = {};
      if (!this.emploiEdit.affectations[groupe][jour][creneau].enseignant) {
        this.emploiEdit.affectations[groupe][jour][creneau].enseignant = {};
      }
      this.emploiEdit.affectations[groupe][jour][creneau].enseignant.nom = valeur;
    },
    
    setSalle(groupe, jour, creneau, valeur) {
      if (!this.emploiEdit.affectations[groupe]) this.emploiEdit.affectations[groupe] = {};
      if (!this.emploiEdit.affectations[groupe][jour]) this.emploiEdit.affectations[groupe][jour] = {};
      if (!this.emploiEdit.affectations[groupe][jour][creneau]) this.emploiEdit.affectations[groupe][jour][creneau] = {};
      if (!this.emploiEdit.affectations[groupe][jour][creneau].salle) {
        this.emploiEdit.affectations[groupe][jour][creneau].salle = {};
      }
      this.emploiEdit.affectations[groupe][jour][creneau].salle.nom = valeur;
    },
    
    fermerEditModal() {
      // Fermer directement sans confirmation
      this.showEditModal = false;
      this.emploiEnEdition = null;
      this.emploiEdit = { schedule: {}, affectations: {}, semaines: {}, statistics: {} };
      this.groupeActifEdit = '';
    },
    
    aModifications() {
      if (!this.emploiEnEdition) return false;
      const original = JSON.stringify({
        schedule: this.emploiEnEdition.schedule,
        affectations: this.emploiEnEdition.affectations,
        semaines: this.emploiEnEdition.semaines
      });
      const edite = JSON.stringify({
        schedule: this.emploiEdit.schedule,
        affectations: this.emploiEdit.affectations,
        semaines: this.emploiEdit.semaines
      });
      return original !== edite;
    },
    
    calculerHeuresGroupe(groupe) {
      if (!this.emploiEdit.schedule[groupe]) return 0;
      let total = 0;
      this.jours.forEach(jour => {
        this.creneaux.forEach(creneau => {
          const module = this.emploiEdit.schedule[groupe][jour][creneau];
          if (module && module.trim() !== '' && creneau !== '12h-14h') total += 2;
        });
      });
      return total;
    },
    
    calculerTotalHeures() {
      let total = 0;
      Object.keys(this.emploiEdit.schedule).forEach(groupe => {
        total += this.calculerHeuresGroupe(groupe);
      });
      return total;
    },
    
    getGroupModules(groupe) {
      const modules = new Set();
      this.jours.forEach(jour => {
        this.creneaux.forEach(creneau => {
          const module = this.emploiEdit.schedule[groupe][jour][creneau];
          if (module && module.trim() !== '' && creneau !== '12h-14h') modules.add(module);
        });
      });
      return Array.from(modules);
    },
    
    getTotalModules() {
      const tousModules = new Set();
      Object.keys(this.emploiEdit.schedule).forEach(groupe => {
        this.getGroupModules(groupe).forEach(module => tousModules.add(module));
      });
      return tousModules.size;
    },
    
    async sauvegarderModifications() {
      if (!this.aModifications()) {
        alert('Aucune modification à sauvegarder');
        return;
      }
      
      this.sauvegardeEnCours = true;
      try {
        const dataToSave = {
          emploi_id: this.emploiEnEdition.id,
          filiere_id: this.emploiEnEdition.filiere_id,
          semestre: this.emploiEnEdition.semestre,
          niveau: this.emploiEnEdition.niveau || '',
          schedule: JSON.parse(JSON.stringify(this.emploiEdit.schedule)),
          affectations: JSON.parse(JSON.stringify(this.emploiEdit.affectations || {})),
          semaines: JSON.parse(JSON.stringify(this.emploiEdit.semaines || {})),
          statistics: JSON.parse(JSON.stringify(this.emploiEdit.statistics || {})),
          details: {
            titre: this.emploiEnEdition.titre,
            description: this.emploiEnEdition.description
          }
        };
        
        const response = await axios.post('/api/planning/save', dataToSave);
        
        if (response.data?.success) {
          this.successMessage = 'Modifications enregistrées !';
          this.showEditModal = false;
          await this.chargerEmplois();
          setTimeout(() => { this.successMessage = ''; }, 3000);
        } else {
          this.errorMessage = 'Erreur: ' + (response.data?.message || 'Sauvegarde échouée');
        }
      } catch (error) {
        this.errorMessage = 'Erreur: ' + (error.response?.data?.message || error.message);
      } finally {
        this.sauvegardeEnCours = false;
      }
    },
    
    // === UTILITAIRES ===
    formatDate(dateString) {
      if (!dateString) return '';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
      } catch (error) {
        return dateString;
      }
    },
    
    getStatusBadgeClass(status) {
      const classes = {
        'en_attente': 'status-waiting',
        'valide': 'status-valid',
        'rejeté': 'status-rejected',
        'archive': 'status-archived'
      };
      return classes[status] || 'status-unknown';
    },
    
    getStatusIcon(status) {
      const icons = {
        'en_attente': '⏳',
        'valide': '✅',
        'rejeté': '❌',
        'archive': '📦'
      };
      return icons[status] || '❓';
    },
    
    getStatusLabel(status) {
      const labels = {
        'en_attente': 'En attente',
        'valide': 'Validé',
        'rejeté': 'Rejeté',
        'archive': 'Archivé'
      };
      return labels[status] || status || 'Inconnu';
    }
  }
};
</script>

<style scoped>
/* Styles CSS avec les modifications */

.container {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  max-width: 1400px;
  margin: 0 auto;
  padding: 0;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
  min-height: 100vh;
}

/* Header élégant */
.header-section {
  margin-bottom: 30px;
}

.header-gradient {
  background: linear-gradient(135deg, #373083 0%, #4a43a0 100%);
  border-radius: 0 0 20px 20px;
  padding: 40px 30px;
  color: white;
  box-shadow: 0 4px 20px rgba(55, 48, 163, 0.2);
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title-wrapper {
  display: flex;
  align-items: center;
  gap: 20px;
}

.title-icon {
  font-size: 3.5rem;
  opacity: 0.9;
}

.main-title {
  font-size: 2.2rem;
  font-weight: 800;
  margin: 0 0 8px 0;
  color: white;
}

.subtitle-text {
  font-size: 1.1rem;
  opacity: 0.9;
  margin: 0;
}

/* Carte des filtres */
.filter-card {
  background: white;
  border-radius: 16px;
  padding: 25px 30px;
  margin: 0 auto 30px;
  max-width: 1200px;
  box-shadow: 0 5px 20px rgba(55, 48, 163, 0.08);
  border: 1px solid rgba(55, 48, 163, 0.1);
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(55, 48, 163, 0.1);
}

.filter-title {
  color: #373083;
  font-size: 1.3rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
}

.filter-icon {
  font-size: 1.2rem;
}

.filter-refresh {
  background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
  color: white;
  border: none;
  border-radius: 10px;
  padding: 10px 20px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.filter-refresh:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
}

.refresh-icon {
  font-size: 1rem;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
}

.filter-item {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.filter-label {
  font-weight: 600;
  color: #333;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.label-icon {
  color: #373083;
}

.filter-select {
  padding: 12px 15px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  font-size: 1rem;
  background: white;
  transition: all 0.3s;
  color: #333;
}

.filter-select:focus {
  outline: none;
  border-color: #373083;
  box-shadow: 0 0 0 3px rgba(55, 48, 163, 0.1);
}

/* Messages toast */
.message-toast {
  position: fixed;
  top: 30px;
  right: 30px;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  min-width: 350px;
  max-width: 450px;
  animation: slideInRight 0.3s ease;
  backdrop-filter: blur(10px);
}

.message-toast.success {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.95) 0%, rgba(52, 211, 153, 0.95) 100%);
  color: white;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.message-toast.error {
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.95) 0%, rgba(239, 68, 68, 0.95) 100%);
  color: white;
  border: 1px solid rgba(220, 38, 38, 0.2);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-grow: 1;
}

.toast-icon {
  font-size: 1.8rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
}

.toast-message {
  flex-grow: 1;
}

.toast-message strong {
  display: block;
  margin-bottom: 5px;
  font-size: 1.1rem;
}

.toast-message p {
  margin: 0;
  font-size: 0.95rem;
  opacity: 0.9;
}

.toast-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s;
}

.toast-close:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Carte principale */
.main-card {
  background: white;
  border-radius: 20px;
  margin: 0 auto 40px;
  max-width: 1200px;
  box-shadow: 0 10px 40px rgba(55, 48, 163, 0.1);
  overflow: hidden;
  border: 1px solid rgba(55, 48, 163, 0.1);
}

.card-header {
  padding: 25px 30px;
  background: linear-gradient(to right, #f8f9ff, #ffffff);
  border-bottom: 1px solid rgba(55, 48, 163, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.card-title {
  color: #373083;
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
}

.title-icon {
  font-size: 1.8rem;
}

/* États */
.loading-state {
  padding: 60px 30px;
  text-align: center;
}

.spinner-container {
  margin-bottom: 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(55, 48, 163, 0.1);
  border-top: 4px solid #373083;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

.loading-text {
  color: #666;
  font-size: 1.1rem;
}

.empty-state-card {
  padding: 80px 30px;
  text-align: center;
}

.empty-illustration {
  font-size: 5rem;
  margin-bottom: 25px;
  opacity: 0.2;
}

.empty-title {
  color: #666;
  font-size: 1.4rem;
  margin-bottom: 15px;
}

.empty-description {
  color: #888;
  margin-bottom: 30px;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

/* Tableau */
.table-wrapper {
  overflow-x: auto;
  padding: 0 30px 30px;
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.table-header {
  background: linear-gradient(to bottom, #373083, #2a2568);
  color: white;
  font-weight: 600;
  font-size: 0.95rem;
  padding: 18px 15px;
  text-align: left;
  border: none;
  position: relative;
  white-space: nowrap;
}

.table-header.actions {
  text-align: center;
}

.header-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.table-row {
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(55, 48, 163, 0.05);
}

.table-row:hover {
  background-color: rgba(55, 48, 163, 0.02);
}

.table-cell {
  padding: 20px 15px;
  color: #333;
  font-size: 0.95rem;
  border: none;
}

.actions-cell {
  text-align: center;
}

.cell-content {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.cell-title {
  font-weight: 600;
  color: #373083;
}

.cell-subtitle {
  color: #666;
  font-size: 0.85rem;
}

.filiere-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  background-color: #f0f9ff;
  color: #0369a1;
  border: 1px solid rgba(2, 132, 199, 0.2);
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.semestre-tag {
  display: inline-block;
  padding: 6px 12px;
  background: linear-gradient(135deg, #e6f3ff 0%, #dbeafe 100%);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.niveau-info {
  color: #666;
  font-size: 0.9rem;
}

.date-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
  font-size: 0.9rem;
}

.status-container {
  display: flex;
  justify-content: center;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-align: center;
  min-width: 120px;
  justify-content: center;
}

.status-waiting {
  background-color: #fff7e6;
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.status-valid {
  background-color: #e6f7ee;
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.status-rejected {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid rgba(220, 38, 38, 0.2);
}

.status-archived {
  background-color: #f3f4f6;
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.2);
}

.status-unknown {
  background-color: #f3f4f6;
  color: #6b7280;
  border: 1px solid rgba(107, 114, 128, 0.2);
}

.status-icon {
  font-size: 0.9rem;
}

/* Actions */
.actions-container {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 40px;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  border-radius: 10px;
}

.validate-btn {
  background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
  color: white;
  padding: 8px 16px;
  font-size: 0.85rem;
  gap: 6px;
  box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
}

.validate-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
}

.action-group {
  display: flex;
  gap: 8px;
}

.edit-btn, .delete-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.edit-btn {
  background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
}

.edit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);
}

.delete-btn {
  background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
}

.delete-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
}

.btn-icon {
  font-size: 0.9rem;
}

.btn-text {
  font-size: 0.85rem;
}

/* Message "Validé" */
.validated-status {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background-color: #e6f7ee;
  border: 1px solid rgba(16, 185, 129, 0.2);
  border-radius: 20px;
  color: #10b981;
  font-weight: 600;
  font-size: 0.85rem;
}

.validated-icon {
  font-size: 0.9rem;
}

.validated-text {
  font-size: 0.85rem;
}

/* Modales */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.4);
  z-index: 1050;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: #fff;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
  max-height: 90%;
  overflow-y: auto;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  padding: 1rem;
}

.modal-content-large {
  width: 100%;
  height: 95%;
  max-width: 1200px;
  background: #fff;
  border-radius: 8px;
  overflow-y: auto;
  padding: 1rem;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}

.modal-header, .modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding: 0.75rem 1rem;
}

.modal-footer {
  border-top: 1px solid #eee;
  border-bottom: none;
}

/* Tableau d'édition */
.table th, .table td {
  padding: 0.5rem;
  vertical-align: middle;
}

.cellule {
  min-height: 100px;
  padding: 6px;
  vertical-align: top;
}

.cellule-module {
  background-color: #f9f9f9;
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 5px;
}

.pause {
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  border-radius: 4px;
  text-align: center;
  color: #555;
  padding: 10px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* Badges */
.badge {
  font-size: 0.8rem;
  padding: 0.35em 0.5em;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content-large {
    width: 100%;
    height: 100%;
    border-radius: 0;
  }
  
  .cellule {
    min-height: 20px;
  }

  .table-responsive {
    font-size: 0.85rem;
  }
  
  .filter-grid {
    grid-template-columns: 1fr;
  }
  
  .header-content {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .table-wrapper {
    padding: 0 15px 15px;
  }
  
  .actions-container {
    flex-direction: column;
    gap: 8px;
  }
  
  .action-group {
    justify-content: center;
  }
}

/* Animations */
@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>