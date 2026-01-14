<template>
  <header class="topbar">
    <!-- Barre de recherche -->
    <div class="search-container">
      <div class="search-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <input
        type="text"
        placeholder="Rechercher..."
        class="search-input"
        v-model="search"
        @keyup.enter="searchRequest"
      />
    </div>

    <div class="right">
      <!-- Sélecteur de langue -->
      <select class="lang-select" v-model="selectedLanguage">
        <option value="fr">FR</option>
        <option value="en">EN</option>
      </select>

      <!-- Notifications -->
      <div class="notification-wrapper">
        <button class="notification-btn" @click="toggleNotifications">
          <div class="notification-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M18 8C18 6.4087 17.3679 4.88258 16.2426 3.75736C15.1174 2.63214 13.5913 2 12 2C10.4087 2 8.88258 2.63214 7.75736 3.75736C6.63214 4.88258 6 6.4087 6 8C6 15 3 17 3 17H21C21 17 18 15 18 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M13.73 21C13.5542 21.3031 13.3018 21.5547 12.9982 21.7295C12.6946 21.9044 12.3504 21.9965 12 21.9965C11.6496 21.9965 11.3054 21.9044 11.0018 21.7295C10.6982 21.5547 10.4458 21.3031 10.27 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span v-if="notifications.length" class="badge">
              {{ notifications.length }}
            </span>
          </div>
        </button>

        <div v-if="showNotifications" class="notification-dropdown">
          <div class="notification-header">
            <span>Notifications</span>
          </div>
          <div v-if="notifications.length === 0" class="notification-item empty">
            Aucune notification
          </div>
          <div v-else class="notification-list">
            <div
              v-for="(notif, index) in notifications"
              :key="index"
              class="notification-item"
            >
              {{ notif.message }}
            </div>
          </div>
        </div>
      </div>

      <!-- Profil utilisateur -->
      <div class="profile" @click="toggleProfileMenu">
        <img :src="user.photo ? user.photo + '?t=' + timestamp : defaultPhoto" 
             alt="Photo de profil"
             class="profile-img" />
        <span class="profile-name">{{ user.nom }}</span>
        
        <div v-if="showProfileMenu" class="profile-dropdown">
          <div class="profile-info">
            <img :src="user.photo ? user.photo + '?t=' + timestamp : defaultPhoto" 
                 alt="Photo de profil" />
            <div>
              <p class="name">{{ user.nom }}</p>
              <p class="email">{{ user.email || 'Étudiant' }}</p>
            </div>
          </div>
          <div class="profile-actions">
            <button @click="goToProfile" class="profile-action">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Mon profil</span>
            </button>
            <button @click="logout" class="profile-action logout">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
              <span>Déconnexion</span>
            </button>
          </div>
        </div>
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
const showProfileMenu = ref(false)
const selectedLanguage = ref('fr')
const timestamp = ref(Date.now())
const defaultPhoto = 'https://i.pravatar.cc/40'

onMounted(async () => {
  try {
    await loadProfile()
    await loadNotifications()
    
    // Fermer les menus en cliquant ailleurs
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.notification-wrapper')) {
        showNotifications.value = false
      }
      if (!e.target.closest('.profile')) {
        showProfileMenu.value = false
      }
    })
  } catch (e) {
    logout()
  }
})

const loadProfile = async () => {
  try {
    const res = await api.get('/etudiant/profile')
    user.value = res.data
  } catch (err) {
    console.error('Erreur profil', err)
  }
}

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
  showProfileMenu.value = false
}

const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value
  showNotifications.value = false
}

const goToProfile = () => {
  router.push('/profile')
  showProfileMenu.value = false
}

const logout = async () => {
  try {
    await api.post('/etudiant/logout')
  } catch (e) {}
  localStorage.removeItem('token')
  router.push('/')
}

const searchRequest = () => {
  const q = search.value.trim().toLowerCase()
  if (!q) return

  // mapping mot-clé → route
  if (q.includes('dash') ) {
    router.push('/dashboard')
  } else if (q.includes('cours')) {
    router.push('/courses')
  } else if (q.includes('scan') || q.includes('qr')) {
    router.push('/ScanQR')
  } else if (q.includes('profi')) {
    router.push('/profile')
  } else if (q.includes('cal') || q.includes('agenda')) {
    router.push('/calendrier')
  } else {
    alert('Aucun résultat pour : ' + search.value)
  }

  // vider le champ après recherche
  search.value = ''
}

const refreshUserPhoto = (newPhotoUrl) => {
  user.value.photo = newPhotoUrl
  timestamp.value = Date.now()
}
</script>

<style scoped>
.topbar {
  height: 64px;
  background: white;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  border-bottom: 1px solid #e5e7eb;
}

.search-container {
  position: relative;
  width: 300px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  color: #111827;
  background: #f9fafb;
  transition: all 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #4338ca;
  background: white;
}

.search-input::placeholder {
  color: #9ca3af;
}

.right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.lang-select {
  padding: 6px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  color: #374151;
  font-size: 14px;
  cursor: pointer;
  appearance: none;
  padding-right: 32px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 8px center;
  background-size: 16px;
}

.lang-select:focus {
  outline: none;
  border-color: #4338ca;
}

.notification-wrapper {
  position: relative;
}

.notification-btn {
  background: none;
  border: none;
  padding: 8px;
  border-radius: 8px;
  cursor: pointer;
  position: relative;
  color: #4b5563;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-btn:hover {
  background: #f3f4f6;
}

.notification-icon {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}

.badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #4338ca;
  color: white;
  font-size: 10px;
  font-weight: 600;
  min-width: 16px;
  height: 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid white;
}

.notification-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 320px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  z-index: 1000;
  overflow: hidden;
}

.notification-header {
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
  font-weight: 600;
  color: #111827;
  background: #f9fafb;
}

.notification-list {
  max-height: 300px;
  overflow-y: auto;
}

.notification-item {
  padding: 12px 16px;
  border-bottom: 1px solid #f3f4f6;
  color: #374151;
  font-size: 14px;
  line-height: 1.4;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item.empty {
  text-align: center;
  color: #6b7280;
  font-style: italic;
}

.profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 4px 8px;
  border-radius: 8px;
  cursor: pointer;
  position: relative;
  user-select: none;
}

.profile:hover {
  background: #f3f4f6;
}

.profile-img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e5e7eb;
}

.profile-name {
  font-size: 14px;
  font-weight: 500;
  color: #111827;
  white-space: nowrap;
}

.profile-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 280px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
  z-index: 1000;
  overflow: hidden;
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #f9fafb;
  border-bottom: 1px solid #f3f4f6;
}

.profile-info img {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #4338ca;
}

.profile-info .name {
  font-weight: 600;
  color: #111827;
  margin: 0 0 4px 0;
  font-size: 14px;
}

.profile-info .email {
  color: #6b7280;
  font-size: 12px;
  margin: 0;
}

.profile-actions {
  padding: 8px 0;
}

.profile-action {
  display: flex;
  align-items: center;
  gap: 12px;
  width: 100%;
  padding: 12px 16px;
  background: none;
  border: none;
  color: #374151;
  font-size: 14px;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s;
}

.profile-action:hover {
  background: #f9fafb;
}

.profile-action.logout {
  color: #dc2626;
}

.profile-action.logout:hover {
  background: #fef2f2;
}

.profile-action svg {
  color: #6b7280;
}

.profile-action.logout svg {
  color: #dc2626;
}

@media (max-width: 768px) {
  .topbar {
    padding: 0 16px;
    height: 56px;
  }
  
  .search-container {
    width: 200px;
  }
  
  .profile-name {
    display: none;
  }
  
  .notification-dropdown {
    width: 280px;
    right: -20px;
  }
  
  .profile-dropdown {
    width: 240px;
    right: -20px;
  }
}
</style>