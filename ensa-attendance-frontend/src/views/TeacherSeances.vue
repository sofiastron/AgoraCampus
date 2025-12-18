<template>
  <div class="seances">
    <h2>Mes Séances</h2>
    <div class="cards">
      <TeacherSeanceCard
        v-for="seance in seances"
        :key="seance.id"
        :seance="seance"
      />
    </div>
  </div>
</template>

<script>
import TeacherSeanceCard from "../components/TeacherSeanceCard.vue";

export default {
  name: "TeacherSeances",
  components: { TeacherSeanceCard },
  data() {
    return {
      seances: [
        { id: 1, date: "2025-01-10", module: "Programmation Web", heureDebut: "08:30", heureFin: "10:00" },
        { id: 2, date: "2025-01-11", module: "Base de données", heureDebut: "10:30", heureFin: "12:00" },
      ],
    };
  },
};
</script>

<style scoped>
.seances {
  padding: 2rem;
}
.cards {
  display: flex;
  flex-wrap: wrap;
}
</style>
