<template>
  <div class="scanner-container">
    <!-- Carte principale avec style premium -->
    <div class="scanner-card">
      <!-- En-tête élégant -->
      <div class="scanner-header">
        <h1 class="scanner-title">
          <span class="icon-scanner">📷</span>
          Scanner le QR Code
        </h1>
        <p class="scanner-subtitle">
          Scannez le QR Code du professeur pour enregistrer votre présence
        </p>
      </div>

     
      <div class="scanner-wrapper">
        <div class="scanner-frame">
          
          <div class="frame-decoration top-left-corner"></div>
          <div class="frame-decoration top-right-corner"></div>
          <div class="frame-decoration bottom-left-corner"></div>
          <div class="frame-decoration bottom-right-corner"></div>
        
          <div class="camera-container">
            <div id="reader" class="scanner-viewport"></div>
            
         
            <div class="scan-animation">
              <div class="scan-line"></div>
            </div>
      
            <div class="scan-guide">
              <div class="guide-text">Positionnez le QR Code dans le cadre</div>
              <div class="guide-grid">
                <div class="grid-corner tl"></div>
                <div class="grid-corner tr"></div>
                <div class="grid-corner bl"></div>
                <div class="grid-corner br"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- État de scan -->
        <div class="scan-status">
          <div class="status-indicator active">
            <div class="status-dot"></div>
            <span class="status-text">Scanner actif</span>
          </div>
        </div>
      </div>

      <!-- Résultat du scan -->
      <div v-if="result" class="scan-result-card">
        <div class="result-header">
          <h3 class="result-title">
            
            QR Code détecté !
          </h3>
        </div>
        
        <div class="result-details">
          <div class="result-item">
            <span class="result-label">ID de séance:</span>
            <span class="result-value badge">{{ seanceId }}</span>
          </div>
          <div class="result-item">
            <span class="result-label">Code scanné:</span>
            <span class="result-code">{{ result }}</span>
          </div>
        </div>

        
        <div class="action-section">
          <button 
            @click="enregistrerPresence" 
            class="btn-premium"
            :disabled="!seanceId"
          >
            
            <span class="btn-text">Enregistrer la présence</span>
            <span class="btn-badge">Présent</span>
          </button>
          
          <!-- Option pour rescanner -->
          <button 
            v-if="result" 
            @click="resetScanner" 
            class="btn-secondary"
          >
            Scanner un autre code
          </button>
        </div>
      </div>

      <div class="instructions-card">
        <h4 class="instructions-title">
         
          Comment scanner
        </h4>
        <ul class="instructions-list">
          <li>Assurez-vous d'avoir autorisé l'accès à la caméra</li>
          <li>Placez le QR Code dans le cadre ci-dessus</li>
          <li>Maintenez le code stable pour une détection rapide</li>
          <li>Cliquez sur "Enregistrer la présence" après détection</li>
        </ul>
      </div>

      
      <div v-if="errorMessage" class="error-message">
    
        {{ errorMessage }}
      </div>
    </div>
  </div>
</template>

<script>
import { Html5Qrcode } from "html5-qrcode";
import axios from "@/axios";

export default {
  name: 'PremiumScanner',
  data() {
    return {
      result: null,
      seanceId: null,
      html5QrCode: null,
      errorMessage: null,
      isScanning: false
    };
  },
  mounted() {
    this.initScanner();
  },
  beforeUnmount() {
    this.stopScanner();
  },
  methods: {
    async initScanner() {
      try {
        //  une instance de scanner
        this.html5QrCode = new Html5Qrcode("reader");
        
        // camera disponibles
        const cameras = await Html5Qrcode.getCameras();
        
        if (cameras && cameras.length > 0) {
          this.isScanning = true;
          this.errorMessage = null;
          
          // demarer le scanner avec la première caméra
          await this.html5QrCode.start(
            cameras[0].id,
            {
              fps: 10,
              qrbox: { 
                width: 250,
                height: 250
              },
              aspectRatio: 1.0
            },
            (decodedText) => {
              this.handleScanSuccess(decodedText);
            },
            (errorMessage) => {
              console.debug("Scan en cours...", errorMessage);
            }
          );
        } else {
          this.errorMessage = "Aucune caméra disponible. Veuillez connecter une caméra.";
        }
      } catch (err) {
        console.error("Erreur d'initialisation du scanner : ", err);
        this.errorMessage = "Impossible d'accéder à la caméra. Veuillez vérifier les permissions.";
        this.isScanning = false;
      }
    },

    handleScanSuccess(decodedText) {
      console.log("QR Code détecté : ", decodedText);
      
      const id = parseInt(decodedText);
      
      if (!isNaN(id)) {
        this.seanceId = id;
        this.result = decodedText;
        this.stopScanner();
      } else {
        this.errorMessage = "QR Code invalide. Veuillez scanner un code valide.";
      }
    },

    async stopScanner() {
      if (this.html5QrCode && this.isScanning) {
        try {
          await this.html5QrCode.stop();
          this.isScanning = false;
        } catch (err) {
          console.error("Erreur lors de l'arrêt du scanner : ", err);
        }
      }
    },

    resetScanner() {
      this.result = null;
      this.seanceId = null;
      this.errorMessage = null;
      this.initScanner();
    },

    async enregistrerPresence() {
      if (!this.seanceId) {
        this.errorMessage = "Aucune séance détectée. Veuillez scanner un QR Code valide.";
        return;
      }

      try {
        const response = await axios.post(`/api/presences/${this.seanceId}`, { 
          statut: "présent" 
        });
        
        this.showNotification( response.data.message, "success");
        
        setTimeout(() => {
          this.resetScanner();
        }, 2000);
        
      } catch (err) {
        console.error("Erreur lors de l'enregistrement : ", err);
        
        let message = "Erreur lors de l'enregistrement de la présence.";
        if (err.response && err.response.data) {
          message = err.response.data.message || message;
        }
        
        this.errorMessage = message;
        this.showNotification( message, "error");
      }
    },

    showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.textContent = message;
      notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 12px 20px;
        background: ${type === 'success' ? '#4CAF50' : '#f44336'};
        color: white;
        border-radius: 8px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
        font-size: 14px;
      `;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
          document.body.removeChild(notification);
        }, 300);
      }, 3000);
    }
  }
};
</script>

<style scoped>

.scanner-container {
  width: 100%;
  min-height: calc(100vh - 64px); 
  padding: 20px;
  background: #f8fafc;
  font-family: 'Segoe UI', 'Roboto', sans-serif;
  box-sizing: border-box;
  margin-top: 0;
}

.scanner-card {
  background: white;
  border-radius: 16px;
  padding: 24px;
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  box-shadow: 
    0 4px 20px rgba(0, 0, 0, 0.05),
    0 2px 8px rgba(0, 0, 0, 0.03);
  border: 1px solid #e9ecef;
  box-sizing: border-box;
}

.scanner-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #667eea, #764ba2);
  border-radius: 16px 16px 0 0;
}

.scanner-header {
  text-align: center;
  margin-bottom: 24px;
}

.scanner-title {
  color: #2d3748;
  font-size: 24px;
  font-weight: 700;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.scanner-subtitle {
  color: #718096;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.5;
}

.scanner-wrapper {
  position: relative;
  margin: 20px 0;
}

.scanner-frame {
  background: #f8f9fa;
  border-radius: 16px;
  padding: 20px;
  position: relative;
  border: 1px solid #e2e8f0;
}

.frame-decoration {
  position: absolute;
  width: 20px;
  height: 20px;
  border: 2px solid #667eea;
}

.top-left-corner {
  top: -1px;
  left: -1px;
  border-right: none;
  border-bottom: none;
  border-radius: 12px 0 0 0;
}

.top-right-corner {
  top: -1px;
  right: -1px;
  border-left: none;
  border-bottom: none;
  border-radius: 0 12px 0 0;
}

.bottom-left-corner {
  bottom: -1px;
  left: -1px;
  border-right: none;
  border-top: none;
  border-radius: 0 0 0 12px;
}

.bottom-right-corner {
  bottom: -1px;
  right: -1px;
  border-left: none;
  border-top: none;
  border-radius: 0 0 12px 0;
}

.camera-container {
  position: relative;
  width: 100%;
  height: 350px; /* Hauteur réduite pour s'adapter */
  background: #1a202c;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

#reader {
  width: 100% !important;
  height: 100% !important;
  position: relative;
}

/* Override pour le scanner HTML5QRCode */
#reader video {
  object-fit: cover;
  width: 100% !important;
  height: 100% !important;
}

.scan-animation {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 100%;
  pointer-events: none;
  z-index: 2;
}

.scan-line {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, transparent, #00ff00, transparent);
  animation: scan 2s ease-in-out infinite;
  box-shadow: 0 0 8px #00ff00;
  border-radius: 2px;
}

.scan-guide {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 220px; /* Réduit pour s'adapter */
  height: 220px;
  pointer-events: none;
  z-index: 1;
}

.guide-text {
  position: absolute;
  top: -35px;
  left: 0;
  right: 0;
  text-align: center;
  color: white;
  font-size: 12px;
  font-weight: 500;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.guide-grid {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border: 1px dashed rgba(255, 255, 255, 0.3);
  border-radius: 10px;
}

.grid-corner {
  position: absolute;
  width: 16px;
  height: 16px;
  border: 2px solid #667eea;
}

.grid-corner.tl {
  top: -2px;
  left: -2px;
  border-right: none;
  border-bottom: none;
}

.grid-corner.tr {
  top: -2px;
  right: -2px;
  border-left: none;
  border-bottom: none;
}

.grid-corner.bl {
  bottom: -2px;
  left: -2px;
  border-right: none;
  border-top: none;
}

.grid-corner.br {
  bottom: -2px;
  right: -2px;
  border-left: none;
  border-top: none;
}

.scan-status {
  margin-top: 15px;
  text-align: center;
}

.status-indicator {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #f0fff4;
  padding: 6px 12px;
  border-radius: 16px;
  border: 1px solid #c6f6d5;
}

.status-dot {
  width: 8px;
  height: 8px;
  background: #38a169;
  border-radius: 50%;
  animation: pulse 1.5s ease-in-out infinite;
}

.status-text {
  color: #38a169;
  font-weight: 600;
  font-size: 12px;
}

.scan-result-card {
  background: linear-gradient(135deg, #f0f4ff 0%, #f0f7ff 100%);
  border-radius: 12px;
  padding: 20px;
  margin-top: 24px;
  border: 1px solid #e2e8f0;
  animation: slideIn 0.3s ease;
}

.result-header {
  margin-bottom: 16px;
}

.result-title {
  color: #2d3748;
  font-size: 18px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
}

.result-details {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 20px;
}

.result-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  padding: 10px 16px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  flex-wrap: wrap;
}

.result-label {
  color: #4a5568;
  font-weight: 600;
  font-size: 13px;
}

.result-value {
  font-weight: 700;
  font-size: 16px;
  color: #2d3748;
}

.result-code {
  font-family: 'Monaco', 'Courier New', monospace;
  background: #f8f9fa;
  padding: 5px 10px;
  border-radius: 5px;
  color: #2d3748;
  font-size: 13px;
  word-break: break-all;
  max-width: 200px;
}

.badge {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 4px 12px;
  border-radius: 16px;
  font-weight: 600;
  font-size: 13px;
}

.action-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn-premium {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 14px 24px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.2s ease;
  box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
}

.btn-premium:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-premium:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-icon {
  font-size: 18px;
}

.btn-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 3px 10px;
  border-radius: 10px;
  font-size: 13px;
  margin-left: auto;
}

.btn-secondary {
  background: white;
  color: #4a5568;
  border: 1px solid #e2e8f0;
  padding: 10px 20px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: #f8f9fa;
  border-color: #cbd5e0;
}

.instructions-card {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 16px;
  margin-top: 24px;
  border-left: 4px solid #667eea;
}

.instructions-title {
  color: #2d3748;
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.instructions-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.instructions-list li {
  padding: 6px 0;
  color: #4a5568;
  font-size: 13px;
  position: relative;
  padding-left: 24px;
}

.instructions-list li::before {
  content: '→';
  position: absolute;
  left: 0;
  color: #667eea;
  font-weight: bold;
  font-size: 14px;
}

.error-message {
  background: #fff5f5;
  color: #c53030;
  padding: 12px 16px;
  border-radius: 8px;
  margin-top: 16px;
  border-left: 4px solid #f56565;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  font-size: 13px;
}

/* Animations */
@keyframes scan {
  0% {
    top: 0;
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    top: 100%;
    opacity: 0;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideOut {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(100%);
  }
}

@media (max-width: 1200px) {
  .scanner-container {
    padding: 15px;
  }
  
  .scanner-card {
    padding: 20px;
  }
  
  .camera-container {
    height: 300px;
  }
}

@media (max-width: 768px) {
  .scanner-container {
    padding: 12px;
    min-height: calc(100vh - 56px); /* Hauteur réduite pour mobile */
  }
  
  .scanner-card {
    padding: 16px;
    border-radius: 12px;
  }
  
  .scanner-title {
    font-size: 20px;
  }
  
  .camera-container {
    height: 250px;
  }
  
  .scan-guide {
    width: 180px;
    height: 180px;
  }
  
  .result-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .result-code {
    max-width: 100%;
    font-size: 12px;
  }
  
  .btn-premium {
    flex-direction: column;
    gap: 8px;
    padding: 12px 16px;
  }
  
  .btn-badge {
    margin-left: 0;
    margin-top: 4px;
  }
}

/* Pour les très petits écrans (téléphones) */
@media (max-width: 480px) {
  .scanner-container {
    padding: 8px;
  }
  
  .scanner-card {
    padding: 12px;
  }
  
  .scanner-title {
    font-size: 18px;
    flex-direction: column;
    gap: 8px;
  }
  
  .camera-container {
    height: 200px;
  }
  
  .scan-guide {
    width: 150px;
    height: 150px;
  }
  
  .guide-text {
    top: -25px;
    font-size: 11px;
  }
}
</style>