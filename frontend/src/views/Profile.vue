<template>
  <div class="profile-container">
    <div class="profile-card">
      
      <!-- Photo de profil avec upload -->
      <div class="profile-photo">
        <img :src="previewPhoto || user.photo || defaultPhoto" alt="Photo de profil" />
        <input type="file" @change="handlePhotoChange" accept="image/*" />
        <button v-if="selectedPhoto" @click="uploadPhoto" :disabled="loadingPhoto">
          {{ loadingPhoto ? 'Envoi...' : 'Mettre à jour la photo' }}
        </button>
        <p v-if="photoMessage" :class="{'success-msg': photoSuccess, 'error-msg': !photoSuccess}">
          {{ photoMessage }}
        </p>
      </div>

      <!-- Infos utilisateur -->
      <div class="profile-info">
        <h2>{{ user.nom }}</h2>
        <p>{{ user.email }}</p>
      </div>

      <!-- Formulaire de changement de mot de passe -->
      <div class="password-change">
        <h3>Changer le mot de passe</h3>
        <form @submit.prevent="changePassword">
          <div class="form-group">
            <label>Mot de passe actuel</label>
            <input type="password" v-model="current_password" required />
          </div>

          <div class="form-group">
            <label>Nouveau mot de passe</label>
            <input type="password" v-model="new_password" required />
          </div>

          <div class="form-group">
            <label>Confirmer le nouveau mot de passe</label>
            <input type="password" v-model="new_password_confirmation" required />
          </div>

          <button type="submit" :disabled="loading">
            {{ loading ? 'Enregistrement...' : 'Changer le mot de passe' }}
          </button>
        </form>

        <p v-if="message" :class="{'success-msg': success, 'error-msg': !success}">
          {{ message }}
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "@/axios";

export default {
  name: "PremiumProfile",
  data() {
    return {
      user: {},
      defaultPhoto: "https://i.pravatar.cc/150",
      current_password: "",
      new_password: "",
      new_password_confirmation: "",
      message: "",
      success: false,
      loading: false,
      // Pour la photo
      selectedPhoto: null,
      previewPhoto: null,
      loadingPhoto: false,
      photoMessage: "",
      photoSuccess: false,
    };
  },
  mounted() {
    this.getProfile();
  },
  methods: {
    async getProfile() {
      try {
        const res = await axios.get("/etudiant/profile");
        this.user = res.data;
      } catch (err) {
        console.error("Erreur récupération profil", err);
      }
    },

    // Gestion mot de passe
    async changePassword() {
      this.loading = true;
      this.message = "";
      try {
        const res = await axios.put("/etudiant/profile/change-password", {
          current_password: this.current_password,
          new_password: this.new_password,
          new_password_confirmation: this.new_password_confirmation,
        });
        this.message = res.data.message;
        this.success = true;
        this.current_password = "";
        this.new_password = "";
        this.new_password_confirmation = "";
      } catch (err) {
        this.message =
          err.response?.data?.message || "Erreur lors du changement de mot de passe";
        this.success = false;
      } finally {
        this.loading = false;
      }
    },

    // Gestion photo
    handlePhotoChange(event) {
      const file = event.target.files[0];
      if (file) {
        this.selectedPhoto = file;
        this.previewPhoto = URL.createObjectURL(file);
      }
    },

    async uploadPhoto() {
      if (!this.selectedPhoto) return;
      this.loadingPhoto = true;
      this.photoMessage = "";
      try {
        const formData = new FormData();
        formData.append("photo", this.selectedPhoto);

        const res = await axios.post("/etudiant/profile/photo", formData, {
          headers: { "Content-Type": "multipart/form-data" },
        });

        this.user.photo = res.data.photo; // nouvelle photo depuis serveur
        this.photoMessage = "Photo mise à jour avec succès !";
        this.photoSuccess = true;
        this.selectedPhoto = null;
        this.previewPhoto = null;
      } catch (err) {
        this.photoMessage =
          err.response?.data?.message || "Erreur lors de l'envoi de la photo";
        this.photoSuccess = false;
      } finally {
        this.loadingPhoto = false;
      }
    },
  },
};
</script>

<style scoped>
.profile-container {
  display: flex;
  justify-content: center;
  padding: 30px;
  font-family: 'Segoe UI', sans-serif;
}

.profile-card {
  background: #fff;
  padding: 30px;
  border-radius: 16px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  text-align: center;
}

.profile-photo {
  position: relative;
  margin-bottom: 20px;
}

.profile-photo img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 10px;
  border: 2px solid #667eea;
}

.profile-photo input[type="file"] {
  display: block;
  margin: 0 auto 10px;
}

.profile-photo button {
  padding: 8px 12px;
  border: none;
  border-radius: 8px;
  background: #667eea;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

.profile-photo button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.profile-info h2 {
  margin: 0;
  font-size: 24px;
  font-weight: 600;
}

.profile-info p {
  margin: 5px 0 20px;
  color: #555;
}

.password-change {
  text-align: left;
}

.password-change h3 {
  margin-bottom: 15px;
  font-size: 18px;
  font-weight: 600;
}

.form-group {
  margin-bottom: 15px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #ccc;
}

button {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 8px;
  background: #667eea;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-msg {
  color: #38a169;
  margin-top: 10px;
}

.error-msg {
  color: #c53030;
  margin-top: 10px;
}
</style>
