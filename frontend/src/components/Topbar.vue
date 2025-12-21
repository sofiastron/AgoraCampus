<template>
  <header class="topbar">
    <input
      type="text"
      placeholder="Rechercher..."
      class="search"
      v-model="search"
      @keyup.enter="searchRequest"
    />

    <div class="right">
      <select class="lang">
        <option>FR</option>
        <option>EN</option>
      </select>

      <!-- Notifications -->
      <div class="notification-wrapper">
        <button class="icon" @click="toggleNotifications">
          🔔
          <span v-if="notifications.length" class="badge">
            {{ notifications.length }}
          </span>
        </button>

        <div v-if="showNotifications" class="notification-dropdown">
          <div v-if="notifications.length === 0" class="notification-item">
            Aucune notification
          </div>
          <div
            v-for="(notif, index) in notifications"
            :key="index"
            class="notification-item"
          >
            {{ notif.message }}
          </div>
        </div>
      </div>

      <!-- Profile -->
      <div class="profile">
        <img src="https://i.pravatar.cc/40" />
        <span>{{ user.nom }}</span>
        <button @click="logout">⎋</button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/axios.js'

const router = useRouter()
const user = ref({})
const notifications = ref([])
const search = ref('')
const showNotifications = ref(false)

/* Charger le profil et notifications */
onMounted(async () => {
  try {
    const res = await api.get('/etudiant/profile')
    user.value = res.data
    loadNotifications()
  } catch (e) {
    logout()
  }
})

const loadNotifications = async () => {
  try {
    const res = await api.get('/etudiant/notifications')
    notifications.value = res.data
  } catch (e) {
    console.log('Notifications non chargées')
  }
}


const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

const logout = async () => {
  try {
    await api.post('/etudiant/logout')
  } catch (e) {}

  localStorage.removeItem('token')
  router.push('/')
}

/* Recherche */
const searchRequest = () => {
  console.log('Recherche:', search.value)
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

/* Notification dropdown */
.notification-wrapper {
  position: relative;
}

.notification-dropdown {
  position: absolute;
  right: 0;
  top: 30px;
  width: 250px;
  max-height: 300px;
  overflow-y: auto;
  background: white;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  z-index: 1000;
}

.notification-item {
  padding: 8px 12px;
  border-bottom: 1px solid #eee;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item:hover {
  background: #f5f5f5;
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
