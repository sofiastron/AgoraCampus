<template>
  <div class="documents">
    <h2>Mes Documents</h2>
    <div class="cards">
      <TeacherDocumentCard
        v-for="doc in documents"
        :key="doc.id"
        :document="doc"
      />
    </div>
  </div>
</template>

<script>
import TeacherDocumentCard from "../components/TeacherDocumentCard.vue";

export default {
  name: "TeacherDocuments",
  components: { TeacherDocumentCard },
  data() {
    return {
      documents: [
        { id: 1, titre: "Cours 1", type: "pdf", cheminFichier: "#" },
        { id: 2, titre: "Cours 2", type: "pdf", cheminFichier: "#" },
      ],
    };
  },
};
</script>

<style scoped>
.documents {
  padding: 2rem;
}
.cards {
  display: flex;
  flex-wrap: wrap;
}
</style>
