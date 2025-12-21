<template>
  <div>
    <h2>Reconnaissance faciale</h2>

    <input type="file" accept="image/*" @change="onFileChange" />

    <button type="button" :disabled="!image || loading" @click.prevent="sendImage">
        Lancer la reconnaissance
    </button>


    <p v-if="loading">Traitement en cours...</p>
    <p v-if="successMessage" style="color: green">{{ successMessage }}</p>
    <p v-if="errorMessage" style="color: red">{{ errorMessage }}</p>
  </div>
</template>

<script>
import { sendFaceImage } from '@/services/presenceService';

export default {
  data() {
    return {
      image: null,
      seanceId: 1, // temporaire
      loading: false,
      successMessage: '',
      errorMessage: ''
    };
  },
  methods: {
    onFileChange(e) {
      this.image = e.target.files[0];
      this.successMessage = '';
      this.errorMessage = '';
    },

    async sendImage() {
      this.loading = true;
      this.successMessage = '';
      this.errorMessage = '';

      try {
        await sendFaceImage(this.image, this.seanceId);
        this.successMessage = '✅ Présences mises à jour avec succès';
      } catch (err) {
        this.errorMessage = '❌ Erreur lors de la reconnaissance faciale';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
