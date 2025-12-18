<template>
  <div class="card">
    <h3>{{ document.titre }}</h3>
    <p>Type: {{ document.type }}</p>
    <a :href="document.cheminFichier" target="_blank">Voir le document</a>
  </div>
</template>

<script>
export default {
  name: "TeacherDocumentCard",
  props: {
    document: Object
  }
};
</script>

<style scoped>
.card {
  border: 2px solid #3730a3;
  padding: 1rem;
  margin: 0.5rem;
  border-radius: 5px;
  background-color: #f4f4f4;
}
.card h3 {
  color: #3730a3;
}
a {
  color: #4338ca;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}
</style>
