<!-- <template>
  <div class="photo-capture-container">
aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa -->


    <!-- Section Liste des Étudiants -->
    <!-- <div class="students-section">
      <h2 class="section-title">Liste des Étudiants</h2>
      <div class="students-list">
        <div 
          v-for="student in students" 
          :key="student.id"
          class="student-card"
        >
          <div class="student-info">
            <p class="student-name">{{ student.name }}</p>
            <p class="student-code">{{ student.code }}</p>
          </div>
          <button 
            class="remove-btn"
            @click="removeStudent(student.id)"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
      </div>
    </div> -->


<!-- aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa

    <div class="camera-section">
      <h2 class="camera-title">Reconnaissance Faciale</h2>
      
      <div class="camera-container">
        <div v-if="!imagePreview" class="camera-placeholder">
          <div class="camera-icon-wrapper">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
          </div>
        </div>

        <img 
          v-else
          :src="imagePreview"
          class="image-preview"
          alt="Aperçu de la photo de classe"
        />
      </div>

      <p class="camera-instruction">
        {{ imagePreview ? 'Photo chargée - Prêt pour la reconnaissance' : 'Sélectionner une photo de la classe' }}
      </p>
      <p class="camera-subtext">
        Prenez ou uploadez une photo de la classe pour détecter les étudiants
      </p>

      <div class="upload-zone">
        <input 
          type="file" 
          ref="fileInput"
          accept="image/*"
          @change="onFileChange"
          class="file-input"
          id="file-upload"
        />
        <label 
          for="file-upload" 
          class="upload-label"
          :class="{ 'has-image': imagePreview }"
        >
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          {{ imagePreview ? 'Changer la photo' : 'Choisir une photo' }}
        </label>
      </div>

      <div class="button-group">
        <button 
          class="action-btn recognize-btn"
          @click="sendImage"
          :disabled="!image || loading"
        >
          <svg v-if="!loading" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"></path>
            <path d="M12 6v6l4 2"></path>
          </svg>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spinning">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
          </svg>
          {{ loading ? 'Traitement en cours...' : 'Lancer la reconnaissance' }}
        </button>
        
        <button 
          v-if="imagePreview"
          class="action-btn clear-btn"
          @click="clearImage"
          :disabled="loading"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          Effacer
        </button>
      </div>

      <div v-if="successMessage" class="message-toast success-toast">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="message-toast error-toast">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { sendFaceImage } from '../../services/presenceService';

export default {
  name: 'PhotoCapture',
  data() {
    return {
      students: [
        { id: 1, name: 'Charlie Brown', code: 'CS2021003' },
        { id: 2, name: 'Diana Prince', code: 'CS2021004' },
        { id: 3, name: 'Ethan Hunt', code: 'CS2021005' },
        { id: 4, name: 'Fiona Apple', code: 'CS2021006' },
        { id: 5, name: 'George Wilson', code: 'CS2021007' },
        { id: 6, name: 'Hannah Montana', code: 'CS2021008' },
      ],
      image: null,
      imagePreview: null,
      seanceId: 1, // À récupérer dynamiquement selon votre logique
      loading: false,
      successMessage: '',
      errorMessage: ''
    };
  },
  methods: {
    removeStudent(id) {
      this.students = this.students.filter(student => student.id !== id);
      this.displayMessage('Étudiant retiré de la liste', 'success');
    },

    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.image = file;
        this.successMessage = '';
        this.errorMessage = '';
        
        // Créer une prévisualisation
        const reader = new FileReader();
        reader.onload = (event) => {
          this.imagePreview = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    clearImage() {
      this.image = null;
      this.imagePreview = null;
      this.successMessage = '';
      this.errorMessage = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },

    async sendImage() {
      if (!this.image) return;
      
      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';
      
      try {
        await sendFaceImage(this.image, this.seanceId);
        this.successMessage = '✅ Présences mises à jour avec succès';
        
        // Optionnel: réinitialiser après succès
        setTimeout(() => {
          this.clearImage();
        }, 3000);
      } catch (err) {
        console.error('Erreur reconnaissance faciale:', err);
        this.errorMessage = '❌ Erreur lors de la reconnaissance faciale';
      } finally {
        this.loading = false;
      }
    },

    displayMessage(msg, type) {
      if (type === 'success') {
        this.successMessage = msg;
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } else {
        this.errorMessage = msg;
        setTimeout(() => {
          this.errorMessage = '';
        }, 3000);
      }
    }
  }
};
</script> -->

<!-- <style scoped>
.photo-capture-container {
  display: flex;
  height: 100vh;
  background-color: #f9fafb;
}

/* Section Liste des Étudiants */
.students-section {
  width: 384px;
  background-color: white;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  padding: 24px;
}

.section-title {
  font-size: 20px;
  font-weight: bold;
  color: #111827;
  margin-bottom: 24px;
}

.students-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: calc(100vh - 120px);
  overflow-y: auto;
}

.student-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px;
  background-color: #faf5ff;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.student-card:hover {
  background-color: #f3e8ff;
}

.student-info {
  flex: 1;
}

.student-name {
  font-weight: 600;
  color: #581c87;
  margin: 0 0 4px 0;
}

.student-code {
  font-size: 14px;
  color: #9333ea;
  margin: 0;
}

.remove-btn {
  background: none;
  border: none;
  color: #c084fc;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.remove-btn:hover {
  color: #9333ea;
}

/* Section Caméra */
.camera-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px;
}

.camera-title {
  font-size: 24px;
  font-weight: bold;
  color: #111827;
  margin-bottom: 32px;
}

.camera-container {
  width: 640px;
  height: 480px;
  margin-bottom: 32px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.camera-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #faf5ff 0%, #e0e7ff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.camera-icon-wrapper {
  width: 192px;
  height: 192px;
  background-color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  color: #a855f7;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.camera-instruction {
  color: #4b5563;
  margin-bottom: 8px;
  font-size: 16px;
}

.camera-subtext {
  font-size: 14px;
  color: #9ca3af;
  margin-bottom: 24px;
}

/* Zone d'upload */
.upload-zone {
  margin-bottom: 24px;
}

.file-input {
  display: none;
}

.upload-label {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.upload-label:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
}

.upload-label.has-image {
  background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
}

.button-group {
  display: flex;
  gap: 16px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 32px;
  font-weight: 600;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.recognize-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.recognize-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

.clear-btn {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.clear-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

/* Messages */
.message-toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.3s ease-out;
  font-weight: 600;
}

.success-toast {
  background-color: #10b981;
  color: white;
}

.error-toast {
  background-color: #ef4444;
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateY(100px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Animation de chargement */
.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Scrollbar personnalisée */
.students-list::-webkit-scrollbar {
  width: 8px;
}

.students-list::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 4px;
}

.students-list::-webkit-scrollbar-thumb {
  background: #c084fc;
  border-radius: 4px;
}

.students-list::-webkit-scrollbar-thumb:hover {
  background: #a855f7;
}
</style> -->


<template>
  <div class="photo-capture-container">
    <!-- Section Caméra / Upload -->
    <div class="camera-section">
      <h2 class="camera-title">Reconnaissance Faciale</h2>
      
      <div class="camera-container">
        <!-- Zone de prévisualisation -->
        <div v-if="!imagePreview && !cameraActive" class="camera-placeholder">
          <div class="camera-icon-wrapper">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
          </div>
        </div>

        <!-- Caméra active en direct -->
        <video 
          v-if="cameraActive"
          ref="videoElement"
          class="camera-video"
          autoplay
          playsinline
        ></video>

        <!-- Aperçu de l'image capturée/uploadée -->
        <img 
          v-if="imagePreview && !cameraActive"
          :src="imagePreview"
          class="image-preview"
          alt="Aperçu de la photo de classe"
        />

        <!-- Canvas caché pour capturer l'image -->
        <canvas ref="canvasElement" style="display: none;"></canvas>
      </div>

      <p class="camera-instruction">
        {{ getInstructionText() }}
      </p>
      <p class="camera-subtext">
        Prenez ou uploadez une photo de la classe pour détecter les étudiants
      </p>

      <!-- Zone d'upload et boutons caméra -->
      <div class="controls-group">
        <!-- Bouton Upload -->
        <div class="upload-zone">
          <input 
            type="file" 
            ref="fileInput"
            accept="image/*"
            @change="onFileChange"
            class="file-input"
            id="file-upload"
          />
          <label 
            for="file-upload" 
            class="upload-label"
            :class="{ 'has-image': imagePreview }"
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="17 8 12 3 7 8"></polyline>
              <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            {{ imagePreview ? 'Changer la photo' : 'Choisir une photo' }}
          </label>
        </div>

        <!-- Bouton Activer Caméra -->
        <button 
          v-if="!cameraActive && !imagePreview"
          class="camera-control-btn"
          @click="activateCamera"
        >
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
            <circle cx="12" cy="13" r="4"></circle>
          </svg>
          Activer la Caméra
        </button>
      </div>

      <!-- Boutons d'action quand la caméra est active -->
      <div v-if="cameraActive" class="button-group">
        <button 
          class="action-btn capture-btn"
          @click="capturePhoto"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <circle cx="12" cy="12" r="6"></circle>
          </svg>
          Prendre la Photo
        </button>
        
        <button 
          class="action-btn stop-btn"
          @click="stopCamera"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          </svg>
          Annuler
        </button>
      </div>

      <!-- Boutons d'action quand une image est chargée -->
      <div v-if="imagePreview && !cameraActive" class="button-group">
        <button 
          class="action-btn recognize-btn"
          @click="sendImage"
          :disabled="!image || loading"
        >
          <svg v-if="!loading" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"></path>
            <path d="M12 6v6l4 2"></path>
          </svg>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spinning">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
          </svg>
          {{ loading ? 'Traitement en cours...' : 'Lancer la reconnaissance' }}
        </button>
        
        <button 
          class="action-btn clear-btn"
          @click="clearImage"
          :disabled="loading"
        >
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
          </svg>
          Effacer
        </button>
      </div>

      <!-- Messages de statut -->
      <div v-if="successMessage" class="message-toast success-toast">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="message-toast error-toast">
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { sendFaceImage } from '../../services/presenceService';

export default {
  name: 'PhotoCapture',
  data() {
    return {
      students: [
        { id: 1, name: 'Charlie Brown', code: 'CS2021003' },
        { id: 2, name: 'Diana Prince', code: 'CS2021004' },
        { id: 3, name: 'Ethan Hunt', code: 'CS2021005' },
        { id: 4, name: 'Fiona Apple', code: 'CS2021006' },
        { id: 5, name: 'George Wilson', code: 'CS2021007' },
        { id: 6, name: 'Hannah Montana', code: 'CS2021008' },
      ],
      image: null,
      imagePreview: null,
      cameraActive: false,
      stream: null,
      seanceId: 1, // À récupérer dynamiquement selon votre logique
      loading: false,
      successMessage: '',
      errorMessage: ''
    };
  },
  methods: {
    getInstructionText() {
      if (this.cameraActive) {
        return 'Positionnez la classe dans le cadre';
      } else if (this.imagePreview) {
        return 'Photo chargée - Prêt pour la reconnaissance';
      } else {
        return 'Sélectionner ou prendre une photo de la classe';
      }
    },

    removeStudent(id) {
      this.students = this.students.filter(student => student.id !== id);
      this.displayMessage('Étudiant retiré de la liste', 'success');
    },

    async activateCamera() {
      try {
        this.stream = await navigator.mediaDevices.getUserMedia({ 
          video: { 
            width: { ideal: 1280 },
            height: { ideal: 720 },
            facingMode: 'user' // Ou 'environment' pour la caméra arrière
          } 
        });
        
        this.$nextTick(() => {
          if (this.$refs.videoElement) {
            this.$refs.videoElement.srcObject = this.stream;
            this.cameraActive = true;
            this.displayMessage('Caméra activée', 'success');
          }
        });
      } catch (error) {
        console.error('Erreur lors de l\'activation de la caméra:', error);
        this.displayMessage('Impossible d\'activer la caméra', 'error');
      }
    },

    stopCamera() {
      if (this.stream) {
        this.stream.getTracks().forEach(track => track.stop());
        this.stream = null;
        this.cameraActive = false;
      }
    },

    capturePhoto() {
      const video = this.$refs.videoElement;
      const canvas = this.$refs.canvasElement;
      
      if (video && canvas) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0);
        
        // Convertir en blob et créer une prévisualisation
        canvas.toBlob((blob) => {
          this.image = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' });
          this.imagePreview = URL.createObjectURL(blob);
          
          // Arrêter la caméra après la capture
          this.stopCamera();
          
          this.displayMessage('Photo capturée avec succès !', 'success');
        }, 'image/jpeg', 0.95);
      }
    },

    onFileChange(e) {
      const file = e.target.files[0];
      if (file) {
        this.image = file;
        this.successMessage = '';
        this.errorMessage = '';
        
        // Créer une prévisualisation
        const reader = new FileReader();
        reader.onload = (event) => {
          this.imagePreview = event.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    clearImage() {
      this.image = null;
      this.imagePreview = null;
      this.successMessage = '';
      this.errorMessage = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },

    async sendImage() {
      if (!this.image) return;
      
      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';
      
      try {
        await sendFaceImage(this.image, this.seanceId);
        this.successMessage = '✅ Présences mises à jour avec succès';
        
        // Optionnel: réinitialiser après succès
        setTimeout(() => {
          this.clearImage();
        }, 3000);
      } catch (err) {
        console.error('Erreur reconnaissance faciale:', err);
        this.errorMessage = '❌ Erreur lors de la reconnaissance faciale';
      } finally {
        this.loading = false;
      }
    },

    displayMessage(msg, type) {
      if (type === 'success') {
        this.successMessage = msg;
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } else {
        this.errorMessage = msg;
        setTimeout(() => {
          this.errorMessage = '';
        }, 3000);
      }
    }
  },

  beforeUnmount() {
    this.stopCamera();
  }
};
</script>

<style scoped>
.photo-capture-container {
  display: flex;
  height: 100vh;
  background-color: #f9fafb;
}

/* Section Caméra */
.camera-section {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px;
}

.camera-title {
  font-size: 24px;
  font-weight: bold;
  color: #111827;
  margin-bottom: 32px;
}

.camera-container {
  width: 640px;
  height: 480px;
  margin-bottom: 32px;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  background-color: #000;
}

.camera-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #faf5ff 0%, #e0e7ff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.camera-icon-wrapper {
  width: 192px;
  height: 192px;
  background-color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  color: #a855f7;
}

.camera-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-preview {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.camera-instruction {
  color: #4b5563;
  margin-bottom: 8px;
  font-size: 16px;
}

.camera-subtext {
  font-size: 14px;
  color: #9ca3af;
  margin-bottom: 24px;
}

/* Zone de contrôles */
.controls-group {
  display: flex;
  gap: 16px;
  margin-bottom: 24px;
}

.upload-zone {
  display: inline-block;
}

.file-input {
  display: none;
}

.upload-label {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.upload-label:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
}

.upload-label.has-image {
  background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
}

.camera-control-btn {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.camera-control-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 8px -1px rgba(0, 0, 0, 0.15);
}

.button-group {
  display: flex;
  gap: 16px;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px 32px;
  font-weight: 600;
  border-radius: 12px;
  border: none;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.capture-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.capture-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

.recognize-btn {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.recognize-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

.stop-btn,
.clear-btn {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.stop-btn:hover:not(:disabled),
.clear-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
}

/* Messages */
.message-toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.3s ease-out;
  font-weight: 600;
}

.success-toast {
  background-color: #10b981;
  color: white;
}

.error-toast {
  background-color: #ef4444;
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateY(100px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

/* Animation de chargement */
.spinning {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>