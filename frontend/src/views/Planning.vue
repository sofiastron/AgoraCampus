<template>
  <div class="home-page">
    <NavBar />
    
    <div class="main-content">
      <!-- Toast notifications -->
      <div class="toast-container position-fixed top-0 end-0 p-3">
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
      <div class="container-fluid py-4">
        <!-- En-tête -->
        <div class="text-center mb-4">
          <h2 class="mb-2">📅 Emploi du Temps</h2>
          <p class="text-muted">Générez facilement votre emploi du temps</p>
        </div>

        <!-- Filtres -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
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
                <button @click="genererEmploi" class="btn btn-primary w-100" :disabled="loading">
                  <span v-if="loading">⏳ Génération...</span>
                  <span v-else>🎯 Générer</span>
                </button>
              </div>
              <div class="mt-3 text-center">
          <button @click="allerVersGestion" class="btn btn-sm btn-outline-secondary">
            📂 Voir la liste des emplois
          </button>
        </div>
            </div>
          </div>
        </div>

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
                        <th class="text-center">🕛 12h-14h</th>
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
        <div v-if="showSaveModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
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
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea v-model="saveData.description" class="form-control" rows="3" placeholder="Description de l'emploi du temps..."></textarea>
                </div>
                <div class="alert alert-info">
                  <small>
                    <strong>Information :</strong><br>
                    • L'emploi du temps sera enregistré dans la base de données<br>
                    • Vous pourrez le récupérer plus tard<br>
                    • Statut : "en_attente" (à valider par l'admin)
                  </small>
                </div>
              </div>
              <div class="modal-footer">
                <button @click="fermerModals" class="btn btn-secondary" :disabled="saving">Annuler</button>
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
  </div>
</template>

<script>
import axios from 'axios';
import NavBar from '@/components/NavBar.vue';

export default {
  name: 'Planning',
  components: {
    NavBar
  },
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
      emploiExistantId: null
    };
  },
  watch: {
    // Surveiller les changements pour vérifier les doublons
    selectedFiliere() {
      this.verifierEmploiExistant();
    },
    selectedNiveau() {
      this.verifierEmploiExistant();
    },
    'saveData.semestre'() {
      this.verifierEmploiExistant();
    }
  },
  created() {
    this.loadFilieres();
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
    }, allerVersGestion() {
    // Si vous utilisez Vue Router
    this.$router.push('/gestion-emplois');
    
    // Ou si vous voulez ouvrir dans un nouvel onglet
    // window.open('/gestion-emplois', '_blank');
    
    // Ou si c'est une page modale dans le même composant
    // this.showGestionModal = true;
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
/* Structure principale */
.home-page {
  min-height: 100vh;
  display: flex;
  background-color: #f8f9fa;
}
 .home-page:deep(.navbar-vertical) {
  width: 250px;
  min-height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
}

.main-content {
 margin-left: 280px; /* même largeur que la navbar */
  width: calc(100% - 200px);
  transition: margin-left 0.3s ease;
}

/* Si la navbar est réduite */
.home-page.collapsed .main-content {
  margin-left: 70px;
}

/* Styles pour les cellules de tableau */
.cell-module {
  background-color: #e3f2fd;
  border-left: 4px solid #2196f3;
  border-radius: 4px;
  padding: 8px;
  margin: 2px;
  min-height: 90px;
}

.cell-consecutif {
  background-color: #e8f5e9;
  border-left: 4px solid #4caf50;
  border-radius: 4px;
  padding: 8px;
  margin: 2px;
  min-height: 90px;
  font-weight: bold;
}

.cell-anglais {
  background-color: #fff3e0;
  border-left: 4px solid #ff9800;
  border-radius: 4px;
  padding: 8px;
  margin: 2px;
  min-height: 90px;
}

.cell-francais {
  background-color: #f3e5f5;
  border-left: 4px solid #9c27b0;
  border-radius: 4px;
  padding: 8px;
  margin: 2px;
  min-height: 90px;
}

.cell-dejeuner {
  background-color: #fff9c4;
  border-left: 4px solid #ffeb3b;
  border-radius: 4px;
  padding: 8px;
  margin: 2px;
  min-height: 90px;
  font-style: italic;
}

/* Modal */
.modal.show {
  display: block;
  background-color: rgba(0,0,0,0.5);
}

/* Toast notifications */
.toast-container {
  z-index: 1060;
}

.toast {
  min-width: 350px;
}

.toast.show {
  opacity: 1;
}

/* Responsive */
@media (max-width: 992px) {
  .main-content {
    margin-left: 0;
    padding-top: 60px; /* Hauteur de la navbar mobile */
  }
  
  .home-page.collapsed .main-content {
    margin-left: 0;
  }
}

@media (max-width: 768px) {
   .main-content {
    margin-left: 0;
    width: 80%;
     margin-left: 0;
    padding: 1rem;
  }

  .home-page :deep(.navbar-vertical) {
    position: relative;
    width: 100%;
    height: auto;
  }
  .btn-group {
    flex-wrap: wrap;
    gap: 0.25rem;
  }
  
  .btn-group .btn {
    margin-bottom: 0.25rem;
  }
  
  .card-header .d-flex {
    flex-direction: column;
    align-items: start !important;
  }
  
  .card-header .btn-group {
    margin-top: 0.5rem;
    width: 100%;
  }
  
  .table-responsive {
    font-size: 0.8rem;
  }
  
  .toast-container {
    padding: 0.5rem !important;
  }
  
  .toast {
    min-width: auto;
    width: 100%;
  }
}
</style>