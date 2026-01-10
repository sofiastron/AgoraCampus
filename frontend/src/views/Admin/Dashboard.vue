<template>
  <div class="dashboard">
    <h1>📊 Dashboardd</h1>

    <div class="cards">
      <div class="card">
        <h2>{{ stats.etudiants }}</h2>
        <p>Total Étudiants</p>
      </div>

      <div class="card">
        <h2>{{ stats.enseignants }}</h2>
        <p>Enseignants</p>
      </div>
    </div>

    <h2 style="margin-top:40px;">Étudiants par filière</h2>
    <div class="cards">
      <div class="card" v-for="(count, filiere) in stats.parFiliere" :key="filiere">
        <h2>{{ count }}</h2>
        <p>{{ filiere }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

const stats = ref({
  etudiants: 0,
  enseignants: 0,
  parFiliere: {} // nouveau champ pour les statistiques par filière
});

onMounted(async () => {
  const etudiantsData = (await api.get("/etudiants")).data;
  const enseignantsData = (await api.get("/enseignants")).data;

  stats.value.etudiants = etudiantsData.length;
  stats.value.enseignants = enseignantsData.length;

  // Calcul du nombre d'étudiants par filière
  const parFiliere = {};
  etudiantsData.forEach(etudiant => {
    const filiere = etudiant.filiere || "Non définie";
    parFiliere[filiere] = (parFiliere[filiere] || 0) + 1;
  });

  stats.value.parFiliere = parFiliere;
});
</script>

<style scoped>
.dashboard {
  padding: 40px;
}

.cards {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  margin-top: 30px;
}

.card {
  background: white;
  padding: 30px;
  border-radius: 15px;
  width: 200px;
  text-align: center;
  box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.card h2 {
  color: #373083;
  font-size: 2.5rem;
}
</style>
