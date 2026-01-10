<script setup>
import api from "../../services/api";
import { ref, onMounted } from "vue";

const emplois = ref([]);

const load = async () => {
  emplois.value = (await api.get("/emplois")).data;
};

const valider = async (id) => {
  await api.post(`/emplois/${id}/valider`);
  load();
};

onMounted(load);
</script>

<template>
<table>
  <tr>
    <th>Module</th>
    <th>Date</th>
    <th>Statut</th>
    <th>Action</th>
  </tr>
  <tr v-for="e in emplois" :key="e.id">
    <td>{{ e.module }}</td>
    <td>{{ e.date }}</td>
    <td>{{ e.statut }}</td>
    <td>
      <button v-if="e.statut==='en_attente'" @click="valider(e.id)">
        Valider
      </button>
    </td>
  </tr>
</table>
</template>
