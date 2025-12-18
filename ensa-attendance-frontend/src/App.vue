<template>
  <div id="app">
    <AppHeader />
    <div class="main-container">
      <AppSidebar />
      <div class="content">
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import AppHeader from "./components/AppHeader.vue";
import AppSidebar from "./components/AppSidebar.vue";

export default {
  name: "App",
  components: { AppHeader, AppSidebar },
};
</script>

<style>
#app {
  font-family: 'Segoe UI', sans-serif;
}

.main-container {
  display: flex;
}

.content {
  flex: 1;
  padding: 2rem;
  background-color: #f4f4f4;
  min-height: 100vh;
}
</style>
