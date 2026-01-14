<template>
  <div class="layout">
    <!-- Sidebar -->
    <Sidebar />

    <!-- Contenu principal -->
    <div class="content">
      <Topbar />

      <!-- Zone dynamique -->
      <main class="page">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/components/Sidebar.vue'
import Topbar from '@/components/Topbar.vue'
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.page {
  padding: 20px;
  background: #f6f7fb;
  flex: 1;
}
</style>
