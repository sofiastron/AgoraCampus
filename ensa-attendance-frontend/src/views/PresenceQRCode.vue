<template>
  <div class="container">
    <h2 class="title">Configuration de la Séance</h2>

    <form @submit.prevent="createSeance" class="form">
      <label for="module-select">Module</label>
      <select v-model="form.module_id" id="module-select" required>
        <option disabled value="">-- Sélectionner un module --</option>
        <option v-for="module in modules" :key="module.id" :value="module.id">
          {{ module.titre }}
        </option>
      </select>

      <label for="date">Date</label>
      <input type="date" v-model="form.date" id="date" required />

      <label for="heure_debut">Heure de début</label>
      <input type="time" v-model="form.heure_debut" id="heure_debut" required />

      <label for="heure_fin">Heure de fin</label>
      <input type="time" v-model="form.heure_fin" id="heure_fin" required />

      <button type="submit" :disabled="loading" class="btn">
        {{ loading ? "Création..." : "Créer la séance" }}
      </button>
    </form>

    <transition name="fade">
      <div v-if="seanceId" class="qr-code-container">
        <h3>QR Code de la séance</h3>
        <qrcode-vue :value="qrData" :size="200" />
      </div>
    </transition>

    <p v-if="error" class="error-message">{{ error }}</p>
  </div>
</template>

<script>
import QrcodeVue from "qrcode.vue";
import api from "@/api/axios";

export default {
  components: { QrcodeVue },

  data() {
    return {
      modules: [],
      form: {
        module_id: "",
        date: "",
        heure_debut: "",
        heure_fin: ""
      },
      seanceId: null,
      loading: false,
      error: null
    };
  },

  computed: {
    qrData() {
      return `seance_id:${this.seanceId}`;
    }
  },

  async created() {
    try {
      const res = await api.get("/teacher/modules");
      console.log("Modules reçus:", res.data);


      if (Array.isArray(res.data)) {
        this.modules = res.data;
      } else if (res.data.modules) {
        this.modules = res.data.modules;
      } else {
        this.error = "Format de données inattendu pour les modules.";
        this.modules = [];
      }
    } catch (err) {
      console.error("Erreur lors du chargement des modules:", err);
      this.error = "Erreur lors du chargement des modules.";
    }
  },

  methods: {
    async createSeance() {
      this.error = null;
      this.loading = true;

      try {
        const res = await api.post("/seances", this.form);
        this.seanceId = res.data.seance.id;
      } catch (err) {
        console.error(err);
        if (err.response?.status === 401) {
          this.error = "Session expirée. Veuillez vous reconnecter.";
        } else {
          this.error = "Erreur lors de la création de la séance.";
        }
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.container {
  max-width: 450px;
  margin: 40px auto;
  background: #f9f9f9;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.title {
  text-align: center;
  margin-bottom: 25px;
  color: #3730a3;
  font-weight: 700;
  font-size: 1.8rem;
  letter-spacing: 1.5px;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

label {
  font-weight: 600;
  color: #3730a3;
  margin-bottom: 6px;
}

select,
input[type="date"],
input[type="time"] {
  padding: 10px 12px;
  border-radius: 8px;
  border: 1.8px solid gray;
  font-size: 1rem;
  transition: border-color 0.3s ease;
  color: #000000; 
  -webkit-appearance: none;
  -moz-appearance: none;
  appearance: none;
}

select:focus,
input[type="date"]:focus,
input[type="time"]:focus {
  outline: none;
  border-color: gray;
  box-shadow: 0 0 8px #3730a3aa;
  color: black;
}

.btn {
  background: linear-gradient(135deg, #4338ca, #3730a3);
  color: white;
  font-weight: 700;
  padding: 12px 20px;
  border: none;
  border-radius: 30px;
  cursor: pointer;
  font-size: 1.1rem;
  box-shadow: 0 5px 15px #4338caaa;
  transition: all 0.4s ease;
}

.btn:disabled {
  background: #a5d6a7;
  cursor: not-allowed;
  box-shadow: none;
}

.btn:hover:not(:disabled) {
  box-shadow: 0 8px 25px #3730a3cc;
  transform: translateY(-3px);
}

.qr-code-container {
  margin-top: 25px;
  text-align: center;
  animation: fadeIn 0.8s ease forwards;
}

.error-message {
  color: #d32f2f;
  margin-top: 15px;
  font-weight: 600;
  text-align: center;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.6s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
