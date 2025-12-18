<template>
  <div class="card">
    <h3>{{ annonce.titre }}</h3>
    <p>{{ annonce.description }}</p>
    <p>Module: {{ annonce.module }}</p>
  </div>
</template>

<script>
export default {
  name: "TeacherAnnonceCard",
  props: {
    annonce: Object
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
