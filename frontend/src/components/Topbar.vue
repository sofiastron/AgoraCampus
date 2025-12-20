<template>
  <header class="topbar">
    <input
      type="text"
      placeholder="Rechercher..."
      class="search"
    />

    <div class="right">
      <select class="lang">
        <option>FR</option>
        <option>EN</option>
      </select>

      <button class="icon">
        🔔
        <span class="badge">3</span>
      </button>

      <div class="profile">
        <img src="https://i.pravatar.cc/40" />
        <span>Étudiant</span>
        <button @click="logout">⎋</button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

const logout = () => {
  localStorage.removeItem('token')
  router.push('/')
}
</script>

<style scoped>
.topbar {
  height: 60px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px;
  border-bottom: 1px solid #eee;
}

.search {
  padding: 8px 12px;
  width: 250px;
}

.right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.icon {
  position: relative;
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: red;
  color: white;
  font-size: 10px;
  padding: 2px 5px;
  border-radius: 50%;
}

.profile {
  display: flex;
  align-items: center;
  gap: 8px;
}

.profile img {
  border-radius: 50%;
}
</style>
