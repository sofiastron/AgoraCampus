<script setup>
import api from "../../services/api";
import { ref } from "vue";

const emploi = ref({
  module: "",
  groupe_id: "",
  date: "",
  heure_debut: "",
  heure_fin: ""
});

const submit = async () => {
  await api.post("/emplois", emploi.value);
  alert("Emploi ajouté");
};
</script>

<template>
<form @submit.prevent="submit">
  <input v-model="emploi.module" placeholder="Module" />
  <input v-model="emploi.groupe_id" placeholder="Groupe" />
  <input type="date" v-model="emploi.date" />
  <input type="time" v-model="emploi.heure_debut" />
  <input type="time" v-model="emploi.heure_fin" />
  <button>Ajouter</button>
</form>
</template>
