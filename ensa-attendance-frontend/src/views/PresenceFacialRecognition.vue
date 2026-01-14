<template>
  <div class="facial-container">
    <div v-if="!isSessionCreated" class="config-card">
      <div class="card-header">
        <i class="fas fa-sliders-h"></i>
        <h2 class="config-title">Configuration de la Séance</h2>
      </div>
      
      <div class="form-body">
        <div class="input-group">
          <label><i class="fas fa-book"></i> Module</label>
          <select v-model="form.module_id" class="styled-input">
            <option value="">-- Sélectionner un module --</option>
            <option value="1">Intelligence Artificielle</option>
            <option value="2">Développement Web</option>
          </select>
        </div>

        <div class="input-group">
          <label><i class="fas fa-calendar-alt"></i> Date</label>
          <input type="date" v-model="form.date" class="styled-input">
        </div>

        <div class="time-grid">
          <div class="input-group">
            <label><i class="fas fa-clock"></i> Début</label>
            <input type="time" v-model="form.heure_debut" class="styled-input">
          </div>
          <div class="input-group">
            <label><i class="fas fa-hourglass-end"></i> Fin</label>
            <input type="time" v-model="form.heure_fin" class="styled-input">
          </div>
        </div>

        <button class="btn-create" @click="handleCreateSession">
          <i class="fas fa-plus-circle"></i> Créer la séance
        </button>
      </div>
    </div>

    <div v-else class="camera-interface">
      <div class="camera-header">
        <div class="session-badge">
          <span class="pulse-icon"></span>
          <span class="status-text">Séance #{{ currentSeanceId }} en cours</span>
        </div>
        <button @click="resetSession" class="btn-change">
          <i class="fas fa-exchange-alt"></i> Changer
        </button>
      </div>
      
      <div class="main-layout">
        <div class="video-section">
          <div class="video-wrapper">
            <video ref="video" autoplay v-show="isCameraOn" class="video-feed"></video>
            <canvas ref="canvas" v-show="false" width="640" height="480"></canvas>
            <div v-if="!isCameraOn" class="camera-placeholder">
              <i class="fas fa-video-slash"></i>
              <p>Caméra prête</p>
            </div>
          </div>

          <div class="controls-row">
            <button @click="toggleCamera" :class="isCameraOn ? 'btn-stop' : 'btn-start'">
              <i :class="isCameraOn ? 'fas fa-stop' : 'fas fa-play'"></i>
              {{ isCameraOn ? 'Désactiver' : 'Activer' }}
            </button>
            
            <button @click="capture" :disabled="!isCameraOn" class="btn-capture">
              <i class="fas fa-users"></i> Capturer & Vérifier
            </button>
          </div>

          <div class="upload-section">
            <p class="divider"><span>OU</span></p>
            <label class="btn-upload">
              <i class="fas fa-cloud-upload-alt"></i> Importer photo de groupe
              <input type="file" @change="handleFileUpload" accept="image/*" style="display: none;">
            </label>
          </div>
        </div>

        <div class="results-section">
          <div v-if="apiMessage" :class="['api-notif', isApiSuccess ? 'success' : 'error']">
            <i :class="isApiSuccess ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
            <div class="notif-text">
              <strong>{{ isApiSuccess ? 'Analyse Terminée' : 'Erreur' }}</strong>
              <p>{{ apiMessage }}</p>
            </div>
          </div>

          <div class="presents-list-container">
            <div class="list-header">
              <i class="fas fa-list-check"></i>
              <span>Appel ({{ listePresents.length }} étudiants)</span>
            </div>
            
            <div class="scroll-area">
              <div v-for="(etudiant, index) in listePresents" :key="index" 
                   :class="['etudiant-card', isEtudiantPresent(etudiant.statut) ? 'border-present' : 'border-absent']">
                <div class="etudiant-info">
                  <span class="etudiant-name">{{ etudiant.nom }}</span>
                  <span :class="isEtudiantPresent(etudiant.statut) ? 'status-present' : 'status-absent'">
                    {{ isEtudiantPresent(etudiant.statut) ? 'Présent' : 'Absent' }}
                  </span>
                </div>
                <span class="etudiant-time">{{ formatHeure(etudiant.heure) }}</span>
              </div>

              <div v-if="listePresents.length === 0" class="empty-list">
                <i class="fas fa-user-clock"></i>
                <p>Aucun étudiant trouvé.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const isSessionCreated = ref(false);
const currentSeanceId = ref(null);
const isCameraOn = ref(false);
const video = ref(null);
const canvas = ref(null);
const apiMessage = ref('');
const isApiSuccess = ref(false);
const listePresents = ref([]); 
let stream = null;

const form = reactive({ module_id: '', date: '', heure_debut: '', heure_fin: '' });

/**
 * FONCTION DE VERIFICATION ROBUSTE
 * Gère "present", "Present", "Présent" etc.
 */
function isEtudiantPresent(statut) {
  if (!statut) return false;
  const s = statut.toLowerCase();
  return s.includes('pres') || s.includes('prés');
}

/**
 * NETTOYAGE DE L'HEURE
 * Pour ne pas afficher la date complète "2026-01-14 19:17:59"
 */
function formatHeure(dateStr) {
  if (!dateStr || dateStr === '--:--') return '--:--';
  // Si c'est un format date complet, on garde juste l'heure
  if (dateStr.includes(' ')) {
    return dateStr.split(' ')[1].substring(0, 5);
  }
  return dateStr;
}

async function fetchCurrentPresences() {
  try {
    const response = await axios.get(`http://localhost:8000/api/presences/seance/${currentSeanceId.value}`, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });
    if (response.data.success) {
      listePresents.value = response.data.presences;
    }
  } catch (err) {
    console.error("Erreur synchronisation.");
  }
}

async function handleCreateSession() {
  if (form.module_id && form.date && form.heure_debut) {
    try {
      const response = await axios.post('http://localhost:8000/api/seances/creer', form, {
        headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
      });
      if (response.data.success) {
        currentSeanceId.value = response.data.id;
        isSessionCreated.value = true;
        await fetchCurrentPresences();
      }
    } catch (err) { alert("Erreur création."); }
  } else { alert("Champs vides."); }
}

async function toggleCamera() {
  if (!isCameraOn.value) {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true });
      video.value.srcObject = stream;
      isCameraOn.value = true;
    } catch (err) { alert("Caméra bloquée."); }
  } else { stopCamera(); }
}

function stopCamera() {
  if (stream) stream.getTracks().forEach(t => t.stop());
  isCameraOn.value = false;
}

async function sendImageToBackend(base64Data) {
  apiMessage.value = "Analyse en cours...";
  try {
    const response = await axios.post('http://localhost:8000/api/presence/face-recognition', {
      image: base64Data,
      seance_id: currentSeanceId.value
    }, {
      headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
    });

    isApiSuccess.value = response.data.success;
    
    if (response.data.success) {
      apiMessage.value = `${response.data.names ? response.data.names.length : 0} identifié(s).`;
      // Pause de 300ms pour laisser Laravel finir l'écriture avant de recharger
      setTimeout(async () => {
        await fetchCurrentPresences();
      }, 300);
    } else {
      apiMessage.value = response.data.message;
    }
  } catch (err) {
    isApiSuccess.value = false;
    apiMessage.value = "Erreur réseau.";
  }
}

function capture() {
  const context = canvas.value.getContext('2d');
  context.drawImage(video.value, 0, 0, 640, 480);
  sendImageToBackend(canvas.value.toDataURL('image/jpeg'));
}

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => sendImageToBackend(e.target.result);
  reader.readAsDataURL(file);
}

function resetSession() { 
  stopCamera(); 
  isSessionCreated.value = false; 
  apiMessage.value = ''; 
  listePresents.value = []; 
}
</script>

<style scoped>
.facial-container { max-width: 1100px; margin: 2rem auto; font-family: 'Inter', sans-serif; }

/* CONFIG CARD */
.config-card { background: white; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; max-width: 500px; margin: 0 auto; }
.card-header { background: #4a5568; color: white; padding: 1.5rem; display: flex; align-items: center; gap: 12px; }
.card-header h2 { margin: 0; font-size: 1.2rem; }
.form-body { padding: 2rem; }
.input-group { margin-bottom: 1.2rem; }
.input-group label { display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 0.5rem; color: #4a5568; }
.styled-input { width: 100%; padding: 0.8rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 1rem; box-sizing: border-box; }
.time-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.btn-create { width: 100%; background: #48bb78; color: white; border: none; padding: 1rem; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.3s; }

/* CAMERA INTERFACE */
.camera-interface { background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
.camera-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.session-badge { display: flex; align-items: center; gap: 10px; background: #ebf8ff; padding: 8px 16px; border-radius: 20px; color: #2b6cb0; font-weight: bold; }
.pulse-icon { width: 10px; height: 10px; background: #4299e1; border-radius: 50%; animation: pulse 1.5s infinite; }
.btn-change { background: #edf2f7; color: #718096; border: 1px solid #e2e8f0; padding: 6px 15px; border-radius: 6px; cursor: pointer; }

.main-layout { display: grid; grid-template-columns: 1.4fr 1fr; gap: 2rem; }

.video-wrapper { background: #1a202c; border-radius: 12px; overflow: hidden; aspect-ratio: 4/3; display: flex; align-items: center; justify-content: center; }
.video-feed { width: 100%; height: 100%; object-fit: cover; }
.camera-placeholder { color: #718096; text-align: center; }
.camera-placeholder i { font-size: 3rem; margin-bottom: 1rem; }

.controls-row { display: flex; gap: 1rem; margin-top: 1.5rem; }
.controls-row button { flex: 1; padding: 0.8rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold; cursor: pointer; }

.btn-start { background: #3182ce; color: white; border: none; }
.btn-stop { background: #e53e3e; color: white; border: none; }
.btn-capture { background: #38a169; color: white; border: none; }
.btn-capture:disabled { background: #cbd5e0; cursor: not-allowed; }

.divider { display: flex; align-items: center; margin: 1.5rem 0; color: #cbd5e0; font-size: 0.7rem; }
.divider::before, .divider::after { content: ""; flex: 1; border-bottom: 1px solid #edf2f7; }
.divider span { padding: 0 10px; }
.btn-upload { display: block; background: #edf2f7; color: #4a5568; padding: 0.8rem; text-align: center; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 600; }

/* ZONE DES RÉSULTATS */
.results-section { display: flex; flex-direction: column; gap: 1.5rem; }
.api-notif { display: flex; gap: 15px; align-items: center; padding: 1rem; border-radius: 12px; }
.success { background: #f0fff4; color: #276749; border: 1px solid #c6f6d5; }
.error { background: #fff5f5; color: #9b2c2c; border: 1px solid #fed7d7; }

/* LISTE DES ÉTUDIANTS */
.presents-list-container { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; }
.list-header { background: #edf2f7; padding: 12px 15px; font-weight: bold; color: #4a5568; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; }
.scroll-area { max-height: 400px; overflow-y: auto; padding: 10px; }

/* Styles dynamiques basés sur le statut */
.etudiant-card { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: white; border-radius: 8px; margin-bottom: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
.border-present { border-left: 5px solid #48bb78 !important; background-color: #f0fff4; }
.border-absent { border-left: 5px solid #e53e3e !important; background-color: #fff5f5; }

.status-present { font-size: 0.75rem; color: #48bb78; font-weight: bold; }
.status-absent { font-size: 0.75rem; color: #e53e3e; font-weight: bold; }

.etudiant-name { font-weight: 700; color: #2d3748; text-transform: capitalize; display: block; }
.etudiant-time { font-size: 0.8rem; color: #a0aec0; }

.empty-list { text-align: center; color: #a0aec0; padding: 3rem 1rem; }
.empty-list i { font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; }

@keyframes pulse { 0% { transform: scale(1); opacity: 1; } 100% { transform: scale(2); opacity: 0; } }
</style>