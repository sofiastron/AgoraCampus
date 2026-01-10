<template>
 
 
    <router-view />
  
</template>


<script>
import NavBar from './components/NavBar.vue';

export default {
  name: 'App',
 
}
</script >
<style scoped>
  /* Styles globaux pour les badges */
.badge {
  font-weight: 500;
  padding: 0.35em 0.65em;
}

/* Personnalisation des cartes */
.card {
  border-radius: 10px;
}

/* Styles pour les liens de liste */
.list-group-item {
  padding: 1rem;
  transition: background-color 0.2s;
}

.list-group-item:hover {
  background-color: #f8f9fa;
}

/* Amélioration des bordures des inputs */
.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}
</style>
