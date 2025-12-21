<template>
  <div class="presence-qrcode-page">
    <h1>Présence par QR Code</h1>

    <div class="cards-container">
      <!-- Card 1: Formulaire -->
      <div class="card form-card">
        <h2>Configurer la séance</h2>
        <form @submit.prevent="generateQRCode">
          <label>
            Module:
            <select v-model="session.module" required>
              <option disabled value="">Sélectionner un module</option>
              <option v-for="mod in modules" :key="mod.id" :value="mod.name">{{ mod.name }}</option>
            </select>
          </label>

          <label>
            Salle:
            <input type="text" v-model="session.room" placeholder="Ex: B101" required />
          </label>

          <label>
            Date séance:
            <input type="date" v-model="session.date" required />
          </label>

          <label>
            Classe:
            <input type="text" v-model="session.class" placeholder="Ex: 2A" required />
          </label>

          <label>
            Durée validité QR code (minutes):
            <input type="number" v-model.number="session.qrValidity" min="1" max="120" required />
          </label>

          <button type="submit">Générer QR Code</button>
        </form>
      </div>

      <!-- Card 2: QR Code -->
      <div class="card qrcode-card">
        <h2>QR Code de la séance</h2>
        <div v-if="qrCodeData">
          <qrcode-vue :value="qrCodeData" :size="220" />
          <p>Valable pendant {{ session.qrValidity }} minutes</p>
        </div>
        <div v-else>
          <p>Remplissez le formulaire pour générer le QR code</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import QrcodeVue from 'qrcode.vue'

// Exemple de modules, à remplacer par données réelles venant du backend
const modules = [
  { id: 1, name: 'Mathématiques' },
  { id: 2, name: 'Informatique' },
  { id: 3, name: 'Physique' },
]

const session = ref({
  module: '',
  room: '',
  date: '',
  class: '',
  qrValidity: 5,
})

const qrCodeData = ref('')

function generateQRCode() {
  // Créer une chaîne de données à encoder dans le QR code (JSON par ex)
  const data = {
    module: session.value.module,
    room: session.value.room,
    date: session.value.date,
    class: session.value.class,
    qrValidity: session.value.qrValidity,
    generatedAt: new Date().toISOString()
  }

  qrCodeData.value = JSON.stringify(data)
}
</script>

<style scoped>
.presence-qrcode-page {
  max-width: 900px;
  margin: 2rem auto;
  padding: 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #2c3e50;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  font-weight: 700;
  font-size: 2.2rem;
}

.cards-container {
  display: flex;
  gap: 2rem;
  justify-content: center;
  flex-wrap: wrap;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  padding: 2rem;
  flex: 1 1 400px;
  max-width: 450px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.form-card form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

label {
  display: flex;
  flex-direction: column;
  font-weight: 600;
  font-size: 1rem;
  color: #34495e;
}

input[type="text"],
input[type="date"],
input[type="number"],
select {
  margin-top: 0.3rem;
  padding: 0.5rem 0.8rem;
  font-size: 1rem;
  border: 1.5px solid #d1d5db;
  border-radius: 8px;
  transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="date"]:focus,
input[type="number"]:focus,
select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 6px #2563ebaa;
}

button {
  margin-top: 1rem;
  background-color: #2563eb;
  color: white;
  padding: 0.8rem;
  font-size: 1.1rem;
  font-weight: 700;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #1e40af;
}

.qrcode-card p {
  margin-top: 1rem;
  font-size: 1rem;
  color: #555;
}
</style>
