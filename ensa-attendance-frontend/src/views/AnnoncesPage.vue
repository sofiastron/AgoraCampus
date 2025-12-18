<template>
  <div class="annonces">
    <h2>Mes Annonces</h2>
    <div class="cards">
      <TeacherAnnonceCard
        v-for="ann in annonces"
        :key="ann.id"
        :annonce="ann"
      />
    </div>
  </div>
</template>

<script>
import TeacherAnnonceCard from "../components/TeacherAnnonceCard.vue";

export default {
  name: "TeacherAnnonces",
  components: { TeacherAnnonceCard },
  data() {
    return {
      annonces: [
        { id: 1, titre: "Contrôle", description: "Contrôle le 20 janvier", module: "Programmation Web" },
        { id: 2, titre: "TP", description: "TP rendu le 25 janvier", module: "Base de données" },
      ],
    };
  },
};
</script>

<style scoped>
.annonces {
  padding: 2rem;
}
.cards {
  display: flex;
  flex-wrap: wrap;
}
</style>
