<template>
  <div class="card">
    <h3>Séance du {{ seance.date }}</h3>
    <p>Module: {{ seance.module }}</p>
    <p>Heure: {{ seance.heureDebut }} - {{ seance.heureFin }}</p>
  </div>
</template>

<script>
export default {
  name: "TeacherSeanceCard",
  props: {
    seance: Object
  }
};
</script>

<style scoped>
.card {
  border: 2px solid #4338ca;
  padding: 1rem;
  margin: 0.5rem;
  border-radius: 5px;
  background-color: #f4f4f4;
}
.card h3 {
  color: #4338ca;
}
</style>
