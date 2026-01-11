<template>
  <div class="planning-page">
    <!-- Toast notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
      <div v-if="showToast" class="toast show" role="alert">
        <div class="toast-header" :class="toastType === 'warning' ? 'bg-warning' : 'bg-success'">
          <strong class="me-auto">{{ toastTitle }}</strong>
          <button @click="showToast = false" class="btn-close"></button>
        </div>
        <div class="toast-body">
          {{ toastMessage }}
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="container-fluid py-1">
    
      <!-- En-tête -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        
        <!-- Titre à gauche -->
        <div>
          <h2 class="mb-1">📅 Emploi du Temps</h2>
          <p class="text-muted mb-0">Générez facilement votre emploi du temps</p>
        </div>

        <!-- Bouton à droite -->
        <button @click="allerVersGestion" class="btn btn-sm btn-outline-secondary">
          📂 Voir la liste des emplois
        </button>

      </div>

      <!-- Filtres -->
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <div class="filters-container">
            <div class="row g-3">
              <div class="col-md-4">
                <label class="form-label">Filière</label>
                <select v-model="selectedFiliere" class="form-select" @change="onFiliereChange">
                  <option value="">Choisir...</option>
                  <option v-for="f in filieres" :key="f.id" :value="f.id">{{ f.nom }}</option>
                </select>
              </div>
              
              <div class="col-md-4">
                <label class="form-label">Niveau</label>
                <select v-model="selectedNiveau" class="form-select" :disabled="!selectedFiliere">
                  <option value="">Choisir...</option>
                  <option v-for="n in niveaux" :key="n" :value="n">{{ n }}</option>
                </select>
              </div>
              
              <div class="col-md-4 d-flex align-items-end">
                <button @click="genererEmploi" 
                  class="btn btn-primary w-100" 
                  :disabled="loading || emploiValideExiste">
                  <span v-if="loading">⏳ Génération...</span>
                  <span v-else-if="emploiValideExiste">✅ Déjà validé (identique)</span>
                  <span v-else>🎯 Générer</span>
                </button>
              </div>
            </div>
            
            <!-- Section d'information -->
            <div v-if="selectedFiliere && !emploiValideExiste" class="mt-3">
              <small class="text-muted">
                <template v-if="autresEmploisValides && autresEmploisValides.length > 0">
                  ⚠️ D'autres emplois sont validés pour cette filière:
                  <span v-for="emploi in autresEmploisValides" :key="emploi.id" class="badge bg-info ms-1">
                    {{ emploi.niveau }} - {{ emploi.semestre }}
                  </span>
                </template>
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Le reste du template reste inchangé -->
      <!-- Message erreur -->
      <div v-if="errorMessage" class="alert alert-danger">
        ❌ {{ errorMessage }}
        <button @click="errorMessage = ''" class="btn-close float-end"></button>
      </div>

      <!-- Emploi du temps -->
      <div v-if="emploiDuTemps" class="card shadow">
        <div class="card-header bg-white border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📋 Emploi du Temps Généré</h5>
            <div class="btn-group">
              <button @click="exporterExcel" class="btn btn-sm btn-success">
                📊 Excel
              </button>
              <button @click="ouvrirModalSauvegarde" class="btn btn-sm btn-primary">
                💾 Sauvegarder
              </button>
            </div>
          </div>
        </div>
        
        <div class="card-body p-0">
          <!-- Navigation groupes -->
          <div v-if="Object.keys(emploiDuTemps).length > 1" class="bg-light p-3 border-bottom">
            <div class="btn-group">
              <button v-for="(_, groupe) in emploiDuTemps" :key="groupe"
                      @click="activeGroup = groupe"
                      :class="['btn btn-sm', activeGroup === groupe ? 'btn-primary' : 'btn-outline-primary']">
                👥 {{ groupe }}
              </button>
            </div>
          </div>

          <!-- Tableau emploi du temps -->
          <div v-for="(schedule, groupe) in emploiDuTemps" :key="groupe">
            <div v-if="!activeGroup || activeGroup === groupe" class="p-3">
              <!-- En-tête groupe -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                  <h6 class="mb-0">
                    <span class="badge bg-secondary me-2">{{ groupe }}</span>
                    <small class="text-muted">{{ getGroupHours(groupe) }}h total</small>
                  </h6>
                </div>
                <div class="text-muted small">
                  📚 {{ getGroupModules(groupe).length }} modules
                </div>
              </div>

              <!-- Tableau avec créneau 12h-14h -->
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center">📅</th>
                      <th class="text-center">🕗 08h-10h</th>
                      <th class="text-center">🕙 10h-12h</th>
                      <th class="text-center">🕛 </th>
                      <th class="text-center">🕑 14h-16h</th>
                      <th class="text-center">🕓 16h-18h</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="jour in jours" :key="jour">
                      <td class="fw-bold text-center bg-light">{{ jour }}</td>
                      
                      <td v-for="creneau in creneaux" :key="creneau" class="p-2">
                        <div v-if="schedule[jour] && schedule?.[jour]?.[creneau]" 
                             :class="getCellClass(schedule?.[jour]?.[creneau], jour, creneau, groupe)">
                          <!-- Module -->
                          <div class="fw-bold mb-1">{{ schedule?.[jour]?.[creneau] }}</div>
                          
                          <!-- Enseignant -->
                          <div v-if="affectations?.[groupe]?.[jour]?.[creneau]?.enseignant" 
                               class="small text-muted">
                            👨‍🏫 {{ affectations[groupe][jour][creneau].enseignant.nom }}
                          </div>
                          
                          <!-- Salle -->
                          <div v-if="affectations?.[groupe]?.[jour]?.[creneau]?.salle" 
                               class="small text-muted">
                            🏫 {{ affectations[groupe][jour][creneau].salle.nom }}
                          </div>
                          
                          <!-- Semaines -->
                          <div v-if="semaines?.[groupe]?.[jour]?.[creneau]" class="mt-1">
                            <span class="badge bg-dark">
                              📅 S{{ semaines[groupe][jour][creneau].semaine_debut }}-{{ semaines[groupe][jour][creneau].semaine_fin }}
                            </span>
                          </div>
                        </div>
                        <div v-else class="text-center text-muted py-3">—</div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- État vide -->
      <div v-if="!loading && !emploiDuTemps" class="text-center mt-5">
        <div class="card border-0 bg-light">
          <div class="card-body py-5">
            <div class="display-1 text-muted mb-3">📅</div>
            <h4 class="text-muted">Aucun emploi du temps</h4>
            <p class="text-muted">Sélectionnez une filière et un niveau, puis cliquez sur Générer</p>
          </div>
        </div>
      </div>

      <!-- Modal de sauvegarde -->
      <div v-if="showSaveModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5); z-index: 9998">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <span v-if="emploiExisteDeja">🔄 Mettre à jour l'emploi</span>
                <span v-else>💾 Sauvegarder l'emploi du temps</span>
              </h5>
              <button @click="fermerModals" class="btn-close" :disabled="saving"></button>
            </div>
            <div class="modal-body">
              <!-- Avertissement si emploi existe déjà -->
              <div v-if="emploiExisteDeja" class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Un emploi existe déjà pour cette combinaison !</strong><br>
                <small>
                  Filière: <strong>{{ filieres.find(f => f.id == selectedFiliere)?.nom }}</strong><br>
                  Niveau: <strong>{{ selectedNiveau }}</strong><br>
                  Semestre: <strong>{{ saveData.semestre }}</strong>
                </small>
                <div class="mt-2">
                  <small class="text-muted">
                    Si vous continuez, l'ancien emploi sera remplacé.
                  </small>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Semestre</label>
                <select v-model="saveData.semestre" class="form-select" @change="verifierEmploiExistant">
                  <option value="S1">Semestre 1 (S1)</option>
                  <option value="S2">Semestre 2 (S2)</option>
                  <option value="S3">Semestre 3 (S3)</option>
                  <option value="S4">Semestre 4 (S4)</option>
                  <option value="S5">Semestre 5 (S5)</option>
                  <option value="S6">Semestre 6 (S6)</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Titre (optionnel)</label>
                <input v-model="saveData.titre" type="text" class="form-control" 
                       :placeholder="`Emploi ${saveData.semestre} - ${selectedFiliere ? filieres.find(f => f.id == selectedFiliere)?.nom : ''}`">
              </div>
              <div class="alert alert-info">
                <small>
                  <strong>Information :</strong><br>
                  • L'emploi du temps sera enregistré dans la base de données<br>
                  • Vous pourrez le récupérer plus tard<br>
                  • Statut : "en_attente" (à valider par Coordinateur)
                </small>
              </div>
            </div>
            <div class="modal-footer">

              <button @click="sauvegarderEmploi" class="btn btn-primary" :disabled="!saveData.semestre || saving">
                <span v-if="saving">⏳ Sauvegarde...</span>
                <span v-else-if="emploiExisteDeja">🔄 Mettre à jour</span>
                <span v-else>💾 Sauvegarder</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  name: 'Planning',
  data() {
    return {
      // Données pour la génération
      filieres: [],
      niveaux: [],
      
      // Données principales
      selectedFiliere: null,
      selectedNiveau: '',
      
      // Résultats de la génération
      emploiDuTemps: null,
      affectations: {},
      semaines: {},
      statistics: {},
          autresEmploisValides: [],
      // Interface
      jours: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
      creneaux: ['08h-10h', '10h-12h', '12h-14h', '14h-16h', '16h-18h'],
      loading: false,
      errorMessage: '',
      activeGroup: null,
      
      // Modales
      showSaveModal: false,
      saving: false,
      
      // Données de sauvegarde
      saveData: {
        semestre: 'S1',
        titre: '',
        description: 'Généré automatiquement'
      },
      
      // Toast notifications
      showToast: false,
      toastTitle: '',
      toastMessage: '',
      toastType: 'warning',
      toastTimer: null,
      
      // Vérification doublon
      emploiExisteDeja: false,
      emploiExistantId: null,
      // Dans data de Planning.vue
emploiValideExiste: false,

    };
  },
  created() {
  this.loadFilieres();
  
  // Vérifier si un emploi est déjà validé pour les valeurs par défaut
  this.$nextTick(() => {
    if (this.selectedFiliere && this.selectedNiveau && this.saveData.semestre) {
      this.verifierEmploiValide();
    }
  });
},
 // Ajoutez ces watchers
watch: {
  selectedFiliere() {
    if (typeof this.verifierEmploiExistant === 'function') {
      this.verifierEmploiExistant();
    }
    if (typeof this.verifierEmploiValide === 'function') {
      this.verifierEmploiValide();
    }
  },
  selectedNiveau() {
    if (typeof this.verifierEmploiExistant === 'function') {
      this.verifierEmploiExistant();
    }
    if (typeof this.verifierEmploiValide === 'function') {
      this.verifierEmploiValide();
    }
  },
  'saveData.semestre'() {
    if (typeof this.verifierEmploiExistant === 'function') {
      this.verifierEmploiExistant();
    }
    if (typeof this.verifierEmploiValide === 'function') {
      this.verifierEmploiValide();
    }
  }
},
  
  methods: {
    async loadFilieres() {
      try {
        console.log('🔍 Début chargement filières...');
        const response = await axios.get('/api/planning/filieres');
        console.log('📡 Réponse API filières:', response);
        
        if (response.data?.success) {
          this.filieres = response.data.data || [];
          console.log('✅ Filières chargées:', this.filieres);
        } else {
          console.warn('⚠️ Pas de succès dans la réponse:', response.data);
          this.filieres = [];
        }
      } catch (error) {
        console.error('❌ Erreur chargement filières:', error);
        this.errorMessage = 'Erreur lors du chargement des filières. Vérifiez que le serveur Laravel est démarré.';
        this.filieres = [];
        
        // Données de test temporaires
        this.filieres = [
          { id: 1, nom: 'Informatique' },
          { id: 2, nom: 'Génie Civil' },
          { id: 3, nom: 'Gestion' },
          { id: 4, nom: 'Mécanique' }
        ];
        
        this.showToastMessage(
          'Mode test activé',
          'Utilisation de données de test. Le serveur Laravel n\'est pas accessible.',
          'warning'
        );
      }
    },
    
    allerVersGestion() {
      // Utiliser Vue Router pour naviguer
      this.$router.push('/admin/gestion-emplois');
    },
    
    async onFiliereChange() {
      if (!this.selectedFiliere) {
        this.niveaux = [];
        this.selectedNiveau = '';
        return;
      }
      
      try {
        console.log('🔍 Chargement niveaux pour filière:', this.selectedFiliere);
        const response = await axios.get(`/api/planning/niveaux/${this.selectedFiliere}`);
        console.log('📡 Réponse API niveaux:', response);
        
        if (response.data?.success) {
          this.niveaux = response.data.data || [];
          this.selectedNiveau = '';
          this.verifierEmploiExistant();
          console.log('✅ Niveaux chargés:', this.niveaux);
        }
      } catch (error) {
        console.error('❌ Erreur chargement niveaux:', error);
        this.errorMessage = 'Erreur lors du chargement des niveaux';
        this.niveaux = [];
        this.selectedNiveau = '';
        
        // Données de test temporaires
        this.niveaux = ['1ère année', '2ème année', '3ème année'];
      }
    },
    
// Dans methods, ajoutez ces méthodes :

async verifierEmploiExistant() {
  if (!this.selectedFiliere || !this.saveData.semestre || !this.selectedNiveau) {
    this.emploiExisteDeja = false;
    return;
  }
  
  try {
    const params = {
      filiere_id: this.selectedFiliere,
      niveau: this.selectedNiveau,
      semestre: this.saveData.semestre
    };
    
    const response = await axios.post('/api/planning/check-duplicate', params);
    
    if (response.data?.exists) {
      this.emploiExisteDeja = true;
      this.emploiExistantId = response.data.emploi_id;
      
      this.showToastMessage(
        'Emploi du temps existe déjà',
        `Un emploi existe déjà pour ${this.filieres.find(f => f.id == this.selectedFiliere)?.nom} - ${this.selectedNiveau} - ${this.saveData.semestre}`,
        'warning'
      );
    } else {
      this.emploiExisteDeja = false;
      this.emploiExistantId = null;
    }
  } catch (error) {
    console.log('Vérification doublon échouée:', error.message);
    this.emploiExisteDeja = false;
    this.emploiExistantId = null;
  }
},

async verifierEmploiValide() {
  console.log('🔍 Vérification emploi valide démarrée');
  
  if (!this.selectedFiliere || !this.selectedNiveau || !this.saveData.semestre) {
    console.log('❌ Données insuffisantes');
    this.emploiValideExiste = false;
    this.autresEmploisValides = [];
    return;
  }
  
  try {
    // Vérifier combinaison exacte
    const paramsExact = {
      filiere_id: this.selectedFiliere,
      niveau: this.selectedNiveau,
      semestre: this.saveData.semestre
    };
    
    console.log('📡 Envoi requête exacte:', paramsExact);
    const responseExact = await axios.get('/api/emploi-temps/verifier-valide', { params: paramsExact });
    
    if (responseExact.data?.success && responseExact.data.existe) {
      this.emploiValideExiste = true;
      this.autresEmploisValides = [];
      return;
    }
    
    this.emploiValideExiste = false;
    
    // Récupérer tous les emplois validés de cette filière
    const responseFiliere = await axios.get(`/api/emploi-temps/filiere/${this.selectedFiliere}/valides`);
    
    if (responseFiliere.data?.success) {
      this.autresEmploisValides = responseFiliere.data.data;
    }
    
  } catch (error) {
    console.log('Vérification échouée:', error.message);
    this.emploiValideExiste = false;
    this.autresEmploisValides = [];
  }
},

// Modifiez onFiliereChange
async onFiliereChange() {
  if (!this.selectedFiliere) {
    this.niveaux = [];
    this.selectedNiveau = '';
    return;
  }
  
  try {
    console.log('🔍 Chargement niveaux pour filière:', this.selectedFiliere);
    const response = await axios.get(`/api/planning/niveaux/${this.selectedFiliere}`);
    console.log('📡 Réponse API niveaux:', response);
    
    if (response.data?.success) {
      this.niveaux = response.data.data || [];
      this.selectedNiveau = '';
      // Ne pas appeler verifierEmploiExistant ici car le niveau n'est pas encore sélectionné
    }
  } catch (error) {
    console.error('❌ Erreur chargement niveaux:', error);
    this.errorMessage = 'Erreur lors du chargement des niveaux';
    this.niveaux = [];
    this.selectedNiveau = '';
  }
},

    
    showToastMessage(title, message, type = 'warning') {
      this.toastTitle = title;
      this.toastMessage = message;
      this.toastType = type;
      this.showToast = true;
      
      if (this.toastTimer) {
        clearTimeout(this.toastTimer);
      }
      
      this.toastTimer = setTimeout(() => {
        this.showToast = false;
      }, 5000);
    },
    
    async genererEmploi() {
      if (!this.selectedFiliere) {
        this.errorMessage = 'Veuillez sélectionner une filière';
        return;
      }
      
      if (!this.selectedNiveau) {
        this.errorMessage = 'Veuillez sélectionner un niveau';
        return;
      }
      
      this.loading = true;
      this.errorMessage = '';
      this.emploiDuTemps = null;
      this.affectations = {};
      this.semaines = {};
      
      try {
        const params = {
          filiere_id: this.selectedFiliere,
          niveau: this.selectedNiveau
        };
        
        console.log('🚀 Envoi requête génération:', params);
        const response = await axios.post('/api/planning/generate', params);
        console.log('✅ Réponse reçue:', response.data);
        
        if (response.data?.success) {
          this.emploiDuTemps = response.data.schedule || {};
          this.affectations = response.data.affectations || {};
          this.semaines = response.data.semaines || {};
          this.statistics = response.data.statistics || {};
          
          const groups = Object.keys(this.emploiDuTemps);
          if (groups.length > 0) {
            this.activeGroup = groups[0];
          }
          
          console.log('✅ Emploi généré avec succès, groupes:', groups);
          this.verifierEmploiExistant();
        } else {
          this.errorMessage = response.data?.message || 'Erreur lors de la génération';
        }
      } catch (error) {
        console.error('❌ Erreur génération:', error);
        if (error.code === 'ERR_NETWORK') {
          this.errorMessage = 'Serveur non disponible. Assurez-vous que le serveur Laravel est démarré.';
        } else {
          this.errorMessage = 'Erreur: ' + (error.response?.data?.message || error.message);
        }
      } finally {
        this.loading = false;
      }
    },
    
    ouvrirModalSauvegarde() {
      if (!this.emploiDuTemps || Object.keys(this.emploiDuTemps).length === 0) {
        this.showToastMessage('Attention', 'Veuillez d\'abord générer un emploi du temps', 'warning');
        return;
      }
      
      this.showSaveModal = true;
      
      const filiereNom = this.selectedFiliere 
        ? this.filieres.find(f => f.id == this.selectedFiliere)?.nom 
        : '';
      this.saveData.titre = `Emploi ${this.saveData.semestre} - ${filiereNom} - ${this.selectedNiveau}`;
      
      this.verifierEmploiExistant();
    },
    
    fermerModals() {
      this.showSaveModal = false;
      this.saving = false;
      this.emploiExisteDeja = false;
      this.emploiExistantId = null;
    },
    
    async sauvegarderEmploi() {
      console.log('🚀 Début sauvegarde');
      
      if (!this.emploiDuTemps) {
        this.showError('Aucun emploi du temps à sauvegarder');
        return;
      }
      
      // DEBUG
      console.log('=== DONNÉES ===');
      console.log('Filière:', this.selectedFiliere);
      console.log('Semestre:', this.saveData.semestre);
      console.log('Niveau:', this.selectedNiveau);
      console.log('Existe déjà:', this.emploiExisteDeja);
      console.log('ID existant:', this.emploiExistantId);
      
      this.saving = true;
      
      try {
        const dataToSave = {
          filiere_id: this.selectedFiliere,
          semestre: this.saveData.semestre,
          niveau: this.selectedNiveau || '',
          schedule: this.emploiDuTemps,
          affectations: this.affectations,
          semaines: this.semaines,
          statistics: this.statistics,
          details: {
            titre: this.saveData.titre || `Emploi ${this.saveData.semestre}`,
            description: this.saveData.description || ''
          },
          // IMPORTANT: Toujours envoyer emploi_id même pour mise à jour
          emploi_id: this.emploiExisteDeja ? this.emploiExistantId : null
        };
        
        console.log('📤 Envoi à POST /api/planning/save:', dataToSave);
        
        // UTILISEZ UNIQUEMENT CETTE ROUTE
        const response = await axios.post('/api/planning/save', dataToSave);
        
        console.log('✅ Réponse:', response.data);
        
        if (response.data?.success) {
          const message = this.emploiExisteDeja 
            ? '✅ Emploi mis à jour avec succès!' 
            : '✅ Emploi sauvegardé avec succès!';
          
          alert(message); // Utilisez alert pour être sûr
          this.showSaveModal = false;
          
          // Réinitialiser
          this.saveData = {
            semestre: 'S1',
            titre: '',
            description: ''
          };
          
          this.emploiExisteDeja = false;
          this.emploiExistantId = null;
          this.emploiExistantInfo = null;
          
        } else {
          alert('❌ Erreur: ' + (response.data?.message || 'Inconnue'));
        }
        
      } catch (error) {
        console.error('❌ Erreur:', error);
        
        if (error.response?.status === 500) {
          alert('❌ Erreur 500 Laravel: ' + (error.response.data?.message || 'Vérifiez les logs'));
          console.error('Détails erreur:', error.response.data);
        } else {
          alert('❌ Erreur: ' + error.message);
        }
        
      } finally {
        this.saving = false;
      }
    },
    
    exporterExcel() {
      if (!this.emploiDuTemps) return;
      
      try {
        let html = '<table border="1"><tr><th colspan="6">Emploi du Temps</th></tr>';
        
        Object.keys(this.emploiDuTemps).forEach(groupe => {
          const schedule = this.emploiDuTemps[groupe];
          
          html += `<tr><td colspan="6"><strong>${groupe}</strong></td></tr>`;
          html += '<tr><th>Jour</th><th>08h-10h</th><th>10h-12h</th><th>12h-14h</th><th>14h-16h</th><th>16h-18h</th></tr>';
          
          this.jours.forEach(jour => {
            html += `<tr><td>${jour}</td>`;
            this.creneaux.forEach(creneau => {
              const module = schedule[jour]?.[creneau] || '';
              html += `<td>${module}</td>`;
            });
            html += '</tr>';
          });
        });
        
        html += '</table>';
        
        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `emploi_temps_${new Date().toISOString().split('T')[0]}.xls`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showToastMessage('Succès', 'Fichier Excel généré avec succès!', 'success');
      } catch (error) {
        console.error('Erreur export:', error);
        this.showToastMessage('Erreur', 'Erreur lors de l\'export Excel', 'warning');
      }
    },
    
    getCellClass(module, jour, creneau, groupe) {
      if (!module) return '';
      
      const moduleLower = module.toLowerCase();
      
      if (moduleLower.includes('anglais')) {
        return 'cell-anglais';
      }
      
      if (moduleLower.includes('français') || moduleLower.includes('francais')) {
        return 'cell-francais';
      }
      
      if (creneau === '12h-14h') {
        return 'cell-dejeuner';
      }
      
      if (this.isModuleConsecutif(module, jour, creneau, groupe)) {
        return 'cell-consecutif';
      }
      
      return 'cell-module';
    },
    
    isModuleConsecutif(module, jour, creneau, groupe) {
      if (!this.emploiDuTemps?.[groupe]?.[jour]) return false;
      
      const schedule = this.emploiDuTemps[groupe][jour];
      const creneauIndex = this.creneaux.indexOf(creneau);
      
      if (creneauIndex > 0) {
        const prevCreneau = this.creneaux[creneauIndex - 1];
        if (schedule[prevCreneau] === module) {
          return true;
        }
      }
      
      if (creneauIndex < this.creneaux.length - 1) {
        const nextCreneau = this.creneaux[creneauIndex + 1];
        if (schedule[nextCreneau] === module) {
          return true;
        }
      }
      
      return false;
    },
    
    getGroupHours(groupe) {
      const schedule = this.emploiDuTemps[groupe];
      if (!schedule) return 0;
      
      let total = 0;
      this.jours.forEach(jour => {
        this.creneaux.forEach(creneau => {
          if (schedule[jour]?.[creneau]) total += 2;
        });
      });
      
      return total;
    },
    
    getGroupModules(groupe) {
      const schedule = this.emploiDuTemps[groupe];
      if (!schedule) return [];
      
      const modules = new Set();
      this.jours.forEach(jour => {
        this.creneaux.forEach(creneau => {
          const module = schedule[jour]?.[creneau];
          if (module) modules.add(module);
        });
      });
      
      return Array.from(modules);
    }
  }
};
</script>

<style scoped>
/* Variables de couleurs principales */
:root {
  --primary-color: #2c3e50;
  --primary-light: #3498db;
  --secondary-color: #182d2e;
  --success-color: #a727ae;
  --warning-color: #f39c12;
  --danger-color: #e74c3c;
  --info-color: #4a43a0;
  --light-bg: #f8f9fa;
  --border-color: #e0e0e0;
}

.planning-page {
  min-height: 100vh;
  background-color: var(--light-bg);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* En-tête */
.d-flex.justify-content-between {
  background: white;
  padding: 25px 0;
  border-bottom: 1px solid var(--border-color);
}

.d-flex.justify-content-between h2 {
  color: var(--primary-color);
  font-weight: 600;
  font-size: 1.8rem;
  margin: 0;
}

.d-flex.justify-content-between p {
  color: var(--secondary-color);
  margin: 5px 0 0 0;
  font-size: 0.95rem;
}

/* Bouton Voir la liste */
.btn-outline-secondary {
  border: 1px solid var(--primary-color);
  color: var(--primary-color);
  padding: 8px 20px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.btn-outline-secondary:hover {
  background-color: var(--primary-color);
  color: white;
  transform: translateY(-1px);
}

/* Filtres */
.card.shadow-sm {
  background: white;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  margin-bottom: 25px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.card.shadow-sm .card-body {
  padding: 20px;
}

/* Conteneur filtres */
.filters-container {
  width: 100%;
}

/* Formulaires */
.form-label {
  color: var(--primary-color);
  font-weight: 500;
  font-size: 0.9rem;
  margin-bottom: 8px;
}

.form-select {
  border: 1px solid #373083;
  border-radius: 6px;
  padding: 10px 15px;
  font-size: 0.95rem;
  width: 100%;
  transition: all 0.3s ease;
}

.form-control {
  border: 1px solid #373083;
  border-radius: 6px;
  padding: 10px 15px;
  font-size: 0.95rem;
  width: 100%;
  transition: all 0.3s ease;
}

.form-select:focus, .form-control:focus {
  border-color: var(--primary-light);
  box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.form-select:disabled {
  background-color: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

/* Bouton principal */
.btn-primary {
  background-color: var(--primary-color);
  border: none;
  border-radius: 6px;
  padding: 12px;
  font-weight: 500;
  font-size: 1rem;
  transition: all 0.3s ease;
  width: 100%;
}

.btn-primary:hover {
  background-color: var(--primary-light);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(52, 152, 219, 0.2);
}

.btn-primary:disabled {
  background-color: var(--secondary-color);
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

/* Informations supplémentaires */
.text-muted small {
  font-size: 0.85rem;
}

.badge.bg-info {
  background-color: var(--info-color) !important;
  font-size: 0.8rem;
  padding: 4px 8px;
  margin: 2px;
}

/* Alertes */
.alert {
  border-radius: 6px;
  border: none;
  padding: 15px;
  margin-bottom: 20px;
}

.alert-danger {
  background-color: #fde8e8;
  color: var(--danger-color);
  border-left: 4px solid var(--danger-color);
}

.alert-danger .btn-close {
  opacity: 0.7;
}

/* Carte emploi du temps */
.card.shadow {
  border: 1px solid var(--border-color);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  margin-bottom: 25px;
}

.card.shadow .card-header {
  background: white;
  border-bottom: 1px solid var(--border-color);
  padding: 15px 20px;
}

.card.shadow .card-header h5 {
  color: var(--primary-color);
  font-weight: 600;
  margin: 0;
}

.card.shadow .card-body {
  padding: 0;
}

/* Navigation groupes */
.bg-light {
  background-color: #f8f9fa !important;
  padding: 15px 20px !important;
}

.btn-group .btn-sm {
  padding: 6px 15px;
  font-size: 0.85rem;
  border-radius: 4px;
}

.btn-primary {
  background-color: var(--primary-color) !important;
  border-color: var(--primary-color) !important;
}

.btn-outline-primary {
  color: var(--primary-color);
  border-color: var(--primary-color);
}

.btn-outline-primary:hover {
  background-color: var(--primary-color);
  color: white;
}

/* En-tête groupe */
.d-flex.justify-content-between.align-items-center {
  padding: 15px 20px 10px;
  border-bottom: 1px solid var(--border-color);
}

.badge.bg-secondary {
  background-color: var(--secondary-color) !important;
  padding: 5px 10px;
  font-weight: 500;
}

.text-muted {
  color: var(--secondary-color) !important;
  font-size: 0.9rem;
}

/* Tableau */
.table-responsive {
  padding: 0 20px 20px;
}

.table {
  margin-bottom: 0;
  border-collapse: separate;
  border-spacing: 0;
}

.table thead {
  background-color: #f8f9fa;
}

.table th {
  color: var(--primary-color);
  font-weight: 600;
  font-size: 0.9rem;
  padding: 12px 10px;
  border-bottom: 2px solid var(--border-color);
  text-align: center;
}

.table td {
  padding: 0;
  border: 1px solid var(--border-color);
}

.table tbody tr:first-child td {
  border-top: none;
}

.table tbody tr:last-child td {
  border-bottom: 1px solid var(--border-color);
}

.table tbody tr td:first-child {
  border-left: none;
}

.table tbody tr td:last-child {
  border-right: none;
}

/* Cellules de tableau */
[class^="cell-"] {
  padding: 12px;
  min-height: 100px;
  border-radius: 4px;
  margin: 2px;
}

.cell-module {
  background-color: #e8f4fd;
  border-left: 4px solid #3498db;
}

.cell-consecutif {
  background-color: #e8f7ef;
  border-left: 4px solid #27ae60;
}

.cell-anglais {
  background-color: #fef5e7;
  border-left: 4px solid #f39c12;
}

.cell-francais {
  background-color: #f4ecf7;
  border-left: 4px solid #8e44ad;
}

.cell-dejeuner {
  background-color: #fef9e7;
  border-left: 4px solid #f1c40f;
  font-style: italic;
  color: #7f8c8d;
}

/* Contenu cellules */
.cell-module .fw-bold {
  color: var(--primary-color);
  font-size: 0.95rem;
  margin-bottom: 8px;
}

.small.text-muted {
  font-size: 0.85rem;
  color: #666 !important;
  margin-bottom: 4px;
}

.badge.bg-dark {
  background-color: #2c3e50 !important;
  font-size: 0.75rem;
  padding: 3px 8px;
  margin-top: 5px;
}

/* État vide */
.card.border-0.bg-light {
  background-color: #f8f9fa !important;
  border: 1px solid var(--border-color) !important;
  border-radius: 8px;
}

.display-1 {
  font-size: 4rem;
  opacity: 0.3;
  margin-bottom: 20px;
}

.text-muted {
  color: var(--secondary-color) !important;
}

/* Modal */
.modal-content {
  border: none;
  border-radius: 8px;
  box-shadow: 0 5px 30px rgba(0,0,0,0.2);
}

.modal-header {
  background-color: var(--primary-color);
  color: white;
  border-radius: 8px 8px 0 0;
  padding: 20px;
}

.modal-header h5 {
  color: white;
  margin: 0;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid var(--border-color);
}

/* Alert dans modal */
.alert-warning {
  background-color: #fff3cd;
  color: #856404;
  border-left: 4px solid #ffc107;
}

.alert-info {
  background-color: #d1ecf1;
  color: #0c5460;
  border-left: 4px solid #17a2b8;
}

/* Toast */
.toast-container {
  z-index: 9999;
}

.toast {
  border-radius: 6px;
  border: none;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.toast-header.bg-warning {
  background-color: #ffc107 !important;
  color: #212529;
}

.toast-header.bg-success {
  background-color: #28a745 !important;
  color: white;
}

/* Boutons d'action */
.btn-group .btn-sm {
  margin-left: 5px;
}

.btn-success {
  background-color: #229954;
  border-color: #05ae4c;
}



/* Responsive */
@media (max-width: 768px) {
  .container-fluid {
    padding: 15px !important;
  }
  
  .d-flex.justify-content-between {
    padding: 15px 0;
  }
  
  .d-flex.justify-content-between h2 {
    font-size: 1.5rem;
  }
  
  .d-flex.justify-content-between.align-items-center {
    flex-direction: column;
    align-items: flex-start !important;
  }
  
  .btn-group {
    margin-top: 15px;
    width: 100%;
    justify-content: center;
  }
  
  .btn-group .btn-sm {
    flex: 1;
    margin: 0 2px;
  }
  
  .table-responsive {
    padding: 0 10px 15px;
  }
  
  .table th, .table td {
    padding: 8px 5px;
    font-size: 0.85rem;
  }
  
  [class^="cell-"] {
    min-height: 80px;
    padding: 8px;
  }
  
  .cell-module .fw-bold {
    font-size: 0.85rem;
  }
  
  .small.text-muted {
    font-size: 0.8rem;
  }
  
  .modal-dialog {
    margin: 20px;
  }
  
  .toast-container {
    padding: 10px !important;
  }
  
  .toast {
    min-width: auto;
    width: 100%;
  }
  
  /* Ajustement des filtres sur mobile */
  .row.g-3 {
    margin: 0 !important;
  }
  
  .col-md-4 {
    margin-bottom: 15px;
  }
  
  .col-md-4.d-flex.align-items-end {
    margin-bottom: 0;
  }
}

/* Animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.card, .filter-card, .table-container {
  animation: fadeIn 0.3s ease-out;
}
</style>