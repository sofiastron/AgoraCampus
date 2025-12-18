<template>
  <div class="modules">
    <h2>Mes Modules</h2>
    <div class="cards">
      <TeacherModuleCard v-for="module in modules" :key="module.id" :module="module" />
    </div>
  </div>
</template>

<script>
import TeacherModuleCard from "../components/TeacherModuleCard.vue";

export default {
  name: "TeacherModules",
  components: { TeacherModuleCard },
  data() {
    return {
      modules: [
        { id: 1, description: "Programmation Web" },
        { id: 2, description: "Base de données" },
      ],
    };
  },
};
</script>

<style scoped>
.modules {
  padding: 2rem;
}
.cards {
  display: flex;
  flex-wrap: wrap;
}
</style>
