<template>
  <div class="profile-container">
    <div class="profile-card">
      <!-- Photo de profil -->
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
        <p><strong>Email :</strong> {{ user.email }}</p>
        <p v-if="user.cne"><strong>CNE :</strong> {{ user.cne }}</p>
        <p v-if="user.filiere"><strong>Filière :</strong> {{ user.filiere }}</p>
        <p v-if="user.niveau"><strong>Niveau :</strong> {{ user.niveau }}</p>
        <p v-if="user.telephone"><strong>Téléphone :</strong> {{ user.telephone }}</p>
      </div>

      <!-- Bouton pour afficher le formulaire mot de passe -->
      <button class="btn-edit-pass" @click="showPasswordForm = !showPasswordForm">
        {{ showPasswordForm ? 'Annuler' : 'Modifier le mot de passe' }}
      </button>

      <!-- Formulaire de changement de mot de passe -->
      <div v-if="showPasswordForm" class="password-change">
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
      selectedPhoto: null,
      previewPhoto: null,
      loadingPhoto: false,
      photoMessage: "",
      photoSuccess: false,
      showPasswordForm: false, // pour afficher ou cacher le formulaire mot de passe
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
        this.showPasswordForm = false;
      } catch (err) {
        this.message =
          err.response?.data?.message || "Erreur lors du changement de mot de passe";
        this.success = false;
      } finally {
        this.loading = false;
      }
    },

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

        this.user.photo = res.data.photo;
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
  padding: 40px;
  font-family: 'Segoe UI', sans-serif;
  background: #f4f6f8;
  min-height: 100vh;
}

.profile-card {
  background: #fff;
  padding: 30px;
  border-radius: 20px;
  max-width: 450px;
  width: 100%;
  box-shadow: 0 10px 25px rgba(0,0,0,0.15);
  text-align: center;
  transition: transform 0.3s;
}

.profile-card:hover {
  transform: translateY(-5px);
}

.profile-photo {
  position: relative;
  margin-bottom: 20px;
}

.profile-photo img {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 10px;
  border: 3px solid #667eea;
}

.profile-photo input[type="file"] {
  display: block;
  margin: 0 auto 10px;
}

.profile-photo button {
  padding: 8px 15px;
  border: none;
  border-radius: 10px;
  background: #667eea;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.profile-photo button:hover {
  background: #5563c1;
}

.profile-info h2 {
  margin: 0;
  font-size: 26px;
  font-weight: 700;
  color: #333;
}

.profile-info p {
  margin: 5px 0;
  color: #555;
  font-size: 16px;
}

.btn-edit-pass {
  margin: 15px 0;
  padding: 10px 20px;
  border: none;
  border-radius: 10px;
  background: #f6ad55;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-edit-pass:hover {
  background: #dd6b20;
}

.password-change {
  text-align: left;
  margin-top: 15px;
}

.password-change h3 {
  margin-bottom: 15px;
  font-size: 18px;
  font-weight: 600;
}

.form-group {
  margin-bottom: 12px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid #ccc;
}

button[type="submit"] {
  width: 100%;
  padding: 10px;
  border: none;
  border-radius: 10px;
  background: #667eea;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

button[type="submit"]:hover {
  background: #5563c1;
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
