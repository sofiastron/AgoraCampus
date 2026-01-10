<template>
  <div class="container mt-4">
    <!-- En-tête -->
    <div class="header mb-4">
      <h2>Gestion des Emplois du Temps</h2>
      <p class="text-muted">Visualisez et modifiez les emplois sauvegardés</p>
      <button @click="$router.push('/Planning')" class="btn btn-secondary btn-sm">
        ← Retour à la génération
      </button>
    </div>

    <!-- Filtres -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Filière</label>
            <select v-model="filtreFiliere" class="form-control" @change="chargerEmplois">
              <option value="">Toutes les filières</option>
              <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
            </select>
          </div>
          
          <div class="col-md-4 mb-3">
            <label>Semestre</label>
            <select v-model="filtreSemestre" class="form-control" @change="chargerEmplois">
              <option value="">Tous les semestres</option>
              <option v-for="s in semestres" :key="s">{{ s }}</option>
            </select>
          </div>
          
          <div class="col-md-4 mb-3">
            <label>Statut</label>
            <select v-model="filtreStatut" class="form-control" @change="chargerEmplois">
              <option value="">Tous les statuts</option>
              <option value="en_attente">En attente</option>
              <option value="valide">Validé</option>
              <option value="rejeté">Rejeté</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages -->
    <div v-if="errorMessage" class="alert alert-danger">
      {{ errorMessage }}
      <button @click="errorMessage = ''" class="btn-close"></button>
    </div>

    <div v-if="successMessage" class="alert alert-success">
      {{ successMessage }}
      <button @click="successMessage = ''" class="btn-close"></button>
    </div>

    <!-- Tableau des emplois -->
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between">
          <h5 class="mb-0">Liste des emplois ({{ emplois.length }})</h5>
          <button @click="chargerEmplois" class="btn btn-light btn-sm">
            Actualiser
          </button>
        </div>
      </div>

      <div class="card-body">
        <!-- Chargement -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">Chargement...</p>
        </div>

        <!-- Vide -->
        <div v-else-if="emplois.length === 0" class="text-center py-5">
          <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
          <h5 class="text-muted mt-3">Aucun emploi du temps</h5>
        </div>

        <!-- Liste -->
        <div v-else>
          <table class="table">
            <thead>
              <tr>
                <th width="40">
                  <input type="checkbox" 
                         @change="toggleSelectionAll" 
                         :checked="selection.length === emplois.length"
                         class="form-check-input">
                </th>
                <th>Titre</th>
                <th>Filière</th>
                <th>Semestre</th>
                <th>Niveau</th>
                <th>Créé le</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="emploi in emplois" :key="emploi.id">
                <td>
                  <input type="checkbox" 
                         :value="emploi.id" 
                         v-model="selection"
                         class="form-check-input">
                </td>
                <td>
                  <strong>{{ emploi.titre || 'Sans titre' }}</strong>
                  <small class="d-block text-muted">{{ emploi.description || '' }}</small>
                </td>
                <td>
                  <span class="badge bg-secondary">{{ emploi.filiere?.nom }}</span>
                </td>
                <td>
                  <span class="badge bg-primary">{{ emploi.semestre }}</span>
                </td>
                <td>{{ emploi.niveau || 'Tous' }}</td>
                <td>{{ formatDate(emploi.created_at) }}</td>
                <td>
                  <span class="badge" :class="getStatusBadgeClass(emploi.statut)">
                    {{ getStatusLabel(emploi.statut) }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <button @click="ouvrirEditModal(emploi)" class="btn btn-sm btn-outline-primary">
                      Modifier
                    </button>
                    <button @click="confirmerSuppression(emploi)" class="btn btn-sm btn-outline-danger">
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
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
              <!-- En-tête groupe -->
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

          <!-- Résumé -->
          <div class="card mt-4">
            <div class="card-header">
              <h6 class="mb-0">Résumé des modifications</h6>
            </div>
            <div class="card-body">
              <div class="row text-center">
                <div class="col">
                  <small>Groupes</small>
                  <div class="h5">{{ Object.keys(emploiEdit.schedule).length }}</div>
                </div>
                <div class="col">
                  <small>Heures</small>
                  <div class="h5">{{ calculerTotalHeures() }}h</div>
                </div>
                <div class="col">
                  <small>Modules</small>
                  <div class="h5">{{ getTotalModules() }}</div>
                </div>
                <div class="col">
                  <small>Modifiés</small>
                  <div class="h5" :class="aModifications() ? 'text-warning' : 'text-success'">
                    {{ aModifications() ? 'Oui' : 'Non' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button @click="fermerEditModal" class="btn btn-secondary">Annuler</button>
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
          <p class="text-danger"><small>Cette action est irréversible !</small></p>
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

    toggleSelectionAll() {
      this.selection = this.selection.length === this.emplois.length ? [] : this.emplois.map(e => e.id);
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
      if (this.sauvegardeEnCours) return;
      if (this.aModifications() && !confirm('Modifications non sauvegardées. Quitter ?')) return;
      
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
        'en_attente': 'bg-warning text-dark',
        'valide': 'bg-success',
        'rejeté': 'bg-danger',
        'archive': 'bg-secondary'
      };
      return classes[status] || 'bg-secondary';
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

<style scoped>/* Header */
.header {
  border-bottom: 1px solid #ccc;
  padding-bottom: 1rem;
  margin-bottom: 1.5rem;
}

/* Modals */
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

/* Tableau */
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

/* Input group simple */
.input-group-text {
  background-color: #f5f5f5;
  border: 1px solid #ccc;
  font-size: 0.85rem;
  padding: 5px;}

.input-group-sm input {
  font-size: 0.85rem;
  padding: 0.25rem;
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
}
/* Backdrop full screen */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  z-index: 1050;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Modal full page */
.modal-content-large {
  width: 100%;
  height: 100%;
  max-width: 100%;
  max-height: 100%;
  border-radius: 0;
  background-color: #fff;
  overflow-y: auto;
  padding: 1rem;
  box-shadow: none;
}

/* Header & Footer fixes */
.modal-header, .modal-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #ddd;
}

.modal-footer {
  border-top: 1px solid #ddd;
  border-bottom: none;
}

/* Tableau prend toute la largeur */
.table-responsive {
  width: 100%;
  overflow-x: auto;
}

/* Cellules */
.cellule, .cellule-module, .pause {
  min-height: 100px;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-content-large {
    padding: 0.5rem;
  }
  .cellule {
    min-height: 80px;
  }
}


</style>