<template>
  <div class="card">
    <h3>{{ module.description }}</h3>
  </div>
</template>

<script>
export default {
  name: "TeacherModuleCard",
  props: {
    module: Object
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
</style>
