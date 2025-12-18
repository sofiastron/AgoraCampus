<template>
  <div class="page">
    <AppSidebar />
    <main class="content">
      <h1>Dashboard</h1>
    </main>
  </div>
</template>

<script setup>
import AppSidebar from "@/components/AppSidebar.vue";
</script>

<style scoped>
.page {
  display: flex;
}
.content {
  padding: 2rem;
}
</style>
