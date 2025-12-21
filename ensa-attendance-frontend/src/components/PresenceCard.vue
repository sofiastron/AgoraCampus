<template>
  <div class="presence-card" @click="$emit('click')" role="button" tabindex="0" @keyup.enter="$emit('click')">
    <h3>{{ title }}</h3>
    <p>{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  description: String
})
</script>

<style scoped>
.presence-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.1);
  padding: 1.8rem 2.5rem;
  width: 300px;
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  user-select: none;
  display: flex;
  flex-direction: column;
  justify-content: center;
  text-align: center;
}

.presence-card:hover,
.presence-card:focus {
  box-shadow: 0 12px 30px rgba(0,0,0,0.2);
  transform: translateY(-6px);
  outline: none;
}

.presence-card h3 {
  font-size: 1.5rem;
  margin-bottom: 0.8rem;
  color: #2563eb; /* Bleu moderne */
}

.presence-card p {
  font-size: 1rem;
  color: #475569; /* Gris bleu */
  line-height: 1.4;
}
</style>
