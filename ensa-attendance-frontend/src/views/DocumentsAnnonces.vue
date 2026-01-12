<template>
  <div class="documents-annonces-container dashboard-content">

    <!-- Onglets -->
    <div class="tabs">
      <button 
        :class="{ active: activeTab === 'documents' }" 
        @click="activeTab = 'documents'"
      >
        Documents
      </button>
      <button 
        :class="{ active: activeTab === 'annonces' }" 
        @click="activeTab = 'annonces'"
      >
        Annonces
      </button>
    </div>

    <!-- Section Documents -->
    <div v-if="activeTab === 'documents'" class="section">
      <div class="section-header">
        <h2>Documents par Module</h2>
        <select v-model="selectedModule" class="module-select">
          <option value="">-- Sélectionner un module --</option>
          <option v-for="module in modules" :key="module.id" :value="module.id">
            {{ module.titre }}
          </option>
        </select>
        <button @click="showUploadModal = true" class="btn-primary">
           Ajouter un document
        </button>
      </div>

      <div v-if="selectedModule" class="cards-grid">
        <TeacherDocumentCard 
          v-for="doc in filteredDocuments" 
          :key="doc.id" 
          :document="doc"
          @delete="deleteDocument"
        />
      </div>
      <p v-else class="empty-state">Sélectionnez un module pour voir les documents</p>
    </div>

    <!-- Section Annonces -->
    <div v-if="activeTab === 'annonces'" class="section">
      <div class="section-header">
        <h2>Annonces</h2>
        <button @click="showAnnonceModal = true" class="btn-primary">
          Publier une annonce
        </button>
      </div>

      <div class="cards-grid">
        <TeacherAnnonceCard 
          v-for="annonce in annonces" 
          :key="annonce.id" 
          :annonce="annonce"
          @edit="editAnnonce"
          @delete="deleteAnnonce"
        />
      </div>
    </div>

    <!-- Modal Upload Document -->
    <div v-if="showUploadModal" class="modal-overlay" @click.self="showUploadModal = false">
      <div class="modal">
        <h3>📤 Ajouter un document</h3>
        <form @submit.prevent="uploadDocument">
          <input v-model="newDocument.titre" placeholder="Titre" required />
          <select v-model="newDocument.module_id" required>
            <option value="">-- Module --</option>
            <option v-for="module in modules" :key="module.id" :value="module.id">
              {{ module.titre }}
            </option>
          </select>
          <input type="file" @change="handleFileUpload" accept=".pdf,.ppt,.pptx,.doc,.docx" required />
          <div class="modal-actions">
            <button type="submit" class="btn-primary">Ajouter</button>
            <button type="button" @click="showUploadModal = false" class="btn-secondary">Annuler</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Annonce -->
    <div v-if="showAnnonceModal" class="modal-overlay" @click.self="showAnnonceModal = false">
      <div class="modal">
        <h3>{{ editMode ? '✏️ Modifier' : '📢 Publier' }} une annonce</h3>
        <form @submit.prevent="saveAnnonce">
          <input v-model="currentAnnonce.titre" placeholder="Titre" required />
          <textarea v-model="currentAnnonce.contenu" placeholder="Contenu" rows="5" required></textarea>
          <div class="modal-actions">
            <button type="submit" class="btn-primary">{{ editMode ? 'Modifier' : 'Publier' }}</button>
            <button type="button" @click="closeAnnonceModal" class="btn-secondary">Annuler</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch  } from 'vue'
import api from '../api/axios'
import TeacherDocumentCard from '../components/TeacherDocumentCard.vue'
import TeacherAnnonceCard from '../components/TeacherAnnonceCard.vue'

// État
const activeTab = ref('documents')
const modules = ref([])
const selectedModule = ref('')
const documents = ref([])
const annonces = ref([])

const showUploadModal = ref(false)
const showAnnonceModal = ref(false)
const editMode = ref(false)

const newDocument = ref({ titre: '', module_id: '', fichier: null })
const currentAnnonce = ref({ titre: '', contenu: '' })

watch(selectedModule, (newVal) => {
  if (newVal) {
    loadDocuments()
  } else {
    documents.value = []
  }
})

const filteredDocuments = computed(() => 
  documents.value.filter(d => d.module_id == selectedModule.value)
)

async function loadModules() {
  const res = await api.get('/teacher/modules')
  modules.value = res.data.modules
}

async function loadDocuments() {
  if (!selectedModule.value) return
  const res = await api.get(`/documents/module/${selectedModule.value}`)
  documents.value = res.data.documents
}

async function loadAnnonces() {
  const res = await api.get('/annonces')
  annonces.value = res.data.annonces
}

function handleFileUpload(e) {
  newDocument.value.fichier = e.target.files[0]
}

async function uploadDocument() {
  const formData = new FormData()
  formData.append('titre', newDocument.value.titre)
  formData.append('module_id', newDocument.value.module_id)
  formData.append('fichier', newDocument.value.fichier)

  await api.post('/documents', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
  })

  showUploadModal.value = false
  newDocument.value = { titre: '', module_id: '', fichier: null }
  loadDocuments()
}

async function deleteDocument(id) {
  if (confirm('Supprimer ce document ?')) {
    await api.delete(`/documents/${id}`)
    loadDocuments()
  }
}

async function saveAnnonce() {
  if (editMode.value) {
    await api.put(`/annonces/${currentAnnonce.value.id}`, currentAnnonce.value)
  } else {
    await api.post('/annonces', currentAnnonce.value)
  }
  closeAnnonceModal()
  loadAnnonces()
}

function editAnnonce(annonce) {
  currentAnnonce.value = { ...annonce }
  editMode.value = true
  showAnnonceModal.value = true
}

async function deleteAnnonce(id) {
  if (confirm('Supprimer cette annonce ?')) {
    await api.delete(`/annonces/${id}`)
    loadAnnonces()
  }
}

function closeAnnonceModal() {
  showAnnonceModal.value = false
  editMode.value = false
  currentAnnonce.value = { titre: '', contenu: '' }
}

onMounted(() => {
  loadModules()
  loadAnnonces()
})
</script>

<style scoped>
.documents-annonces-container {
  padding: 25px;
  animation: fadeIn 0.4s ease;
  background: #f8fafc;
  min-height: 100vh;
}

/* Onglets */
.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 30px;
}
.tabs button {
  padding: 12px 25px;
  border-radius: 12px;
  font-weight: 600;
  background: #e0e7ff;
  color: #3730a3;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}
.tabs button.active {
  background: #4338ca;
  color: white;
  box-shadow: 0 4px 12px rgba(67, 56, 202, 0.3);
}
.tabs button:hover:not(.active) {
  background: #c7d2fe;
}

/* Section header */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  gap: 15px;
  flex-wrap: wrap;
}

.section-header h2 {
  margin: 0;
  color: #1e293b;
  font-size: 1.75rem;
  font-weight: 700;
}

.module-select {
  padding: 10px 15px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  color: #475569;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 200px;
}

.module-select:focus {
  outline: none;
  border-color: #4338ca;
  box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.1);
}

/* Cards Grid */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
}

/* Empty state */
.empty-state {
  text-align: center;
  color: #94a3b8;
  font-size: 1.1rem;
  padding: 60px 20px;
  background: white;
  border-radius: 14px;
  border: 2px dashed #e2e8f0;
}

/* Buttons */
.btn-primary {
  background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
  color: white;
  padding: 12px 24px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  box-shadow: 0 4px 12px rgba(67, 56, 202, 0.2);
}
.btn-primary:hover {
  background: linear-gradient(135deg, #3730a3 0%, #312e81 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(67, 56, 202, 0.3);
}

.btn-secondary {
  background: #f3f4f6;
  color: #3730a3;
  padding: 12px 24px;
  border-radius: 10px;
  border: 2px solid #e5e7eb;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}
.btn-secondary:hover {
  background: #e5e7eb;
  border-color: #d1d5db;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.3s ease;
}

.modal {
  background: white;
  padding: 35px 30px;
  width: 90%;
  max-width: 500px;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: slideUp 0.3s ease;
}

.modal h3 {
  margin: 0 0 25px 0;
  color: #1e293b;
  font-size: 1.5rem;
  font-weight: 700;
}

.modal input, 
.modal textarea, 
.modal select {
  width: 100%;
  padding: 12px 16px;
  margin-bottom: 18px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 14px;
  font-family: inherit;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.modal input:focus, 
.modal textarea:focus, 
.modal select:focus {
  outline: none;
  border-color: #4338ca;
  box-shadow: 0 0 0 3px rgba(67, 56, 202, 0.1);
}

.modal textarea {
  resize: vertical;
  min-height: 120px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 25px;
}

/* Animations */
@keyframes fadeIn {
  from { 
    opacity: 0; 
  }
  to { 
    opacity: 1; 
  }
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

/* Responsive */
@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .module-select {
    width: 100%;
  }
  
  .cards-grid {
    grid-template-columns: 1fr;
  }
}
</style>