<template>
  <aside class="sidebar">
    <!-- Logo et en-tête -->
    <div class="sidebar-header">
      <div class="logo-container">
        <div class="logo-icon">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 17L12 22L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M2 12L12 17L22 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
        <h1 class="logo-text">Agora<span>Campus</span></h1>
      </div>
      <div class="user-profile">
        <div class="profile-avatar">
          {{ getUserInitials }}
        </div>
        <div class="profile-info">
          <p class="profile-name">{{ user.nom || 'Utilisateur' }}</p>
          <p class="profile-role">Étudiant</p>
        </div>
      </div>
    </div>

    <!-- Navigation principale -->
    <nav class="sidebar-nav">
      <div class="nav-section">
        <p class="section-label">Navigation</p>
        <div class="nav-items">
          <router-link to="/dashboard" class="nav-item" active-class="active">
            <div class="nav-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 9H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 21V9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="nav-text">Dashboard</span>
            <div class="nav-badge" v-if="notificationCount > 0">{{ notificationCount }}</div>
          </router-link>

          <router-link to="/courses" class="nav-item" active-class="active">
            <div class="nav-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M12 19L19 12L22 15L15 22L12 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18 13L16.5 5.5L2 2L5.5 16.5L13 18L18 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M2 2L9.5 9.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="nav-text">Mes cours</span>
          </router-link>

          <router-link to="/ScanQR" class="nav-item" active-class="active">
            <div class="nav-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M3 9H6V15H3V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 3V6H15V3H9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M9 21V18H15V21H9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 9H18V15H21V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 3H18V6H21V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 18H18V21H21V18Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 3H6V6H3V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 18H6V21H3V18Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="nav-text">Scanner QR</span>
          </router-link>

          <router-link to="/calendrier" class="nav-item" active-class="active">
            <div class="nav-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M19 4H5C3.89543 4 3 4.89543 3 6V20C3 21.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V5C21 3.89543 20.1046 3 19 3H5C3.89543 3 3 3.89543 3 5V19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M16 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M8 2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 10H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="nav-text">Calendrier</span>
          </router-link>
        </div>
      </div>

  
      <div class="nav-section">
        <p class="section-label">Compte</p>
        <div class="nav-items">
          <router-link to="/profile" class="nav-item" active-class="active">
            <div class="nav-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="nav-text">Profil</span>
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Section infos -->
    <div class="sidebar-footer">
      <div class="presence-indicator">
        <div class="indicator-dot active"></div>
        <span>Connecté</span>
      </div>
      <div class="sidebar-action">
        <button class="logout-btn" @click="handleLogout">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 17L21 12L16 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 12H9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Déconnexion</span>
        </button>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/axios.js'

const router = useRouter()
const notificationCount = ref(2)
const user = ref({
  nom: '',
  email: ''
})

const getUserInitials = computed(() => {
  if (!user.value.nom) return 'UT'
  
  return user.value.nom
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2)
})

onMounted(async () => {
  await loadProfile()
})

const loadProfile = async () => {
  try {
    const res = await api.get('/etudiant/profile')
    user.value = res.data
  } catch (err) {
    console.error('Erreur profil', err)
  }
}

const handleLogout = async () => {
  try {
    await api.post('/etudiant/logout')
  } catch (e) {
    console.log('Erreur déconnexion:', e)
  }
  localStorage.removeItem('token')
  router.push('/')
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  min-height: 100vh;
  background: #4338ca;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 10;
}

.sidebar-header {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.logo-text {
  font-size: 20px;
  font-weight: 700;
  color: white;
  letter-spacing: -0.5px;
}

.logo-text span {
  font-weight: 800;
  opacity: 0.9;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  transition: all 0.3s ease;
}

.user-profile:hover {
  background: rgba(255, 255, 255, 0.15);
}

.profile-avatar {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
  color: white;
  flex-shrink: 0;
}

.profile-info {
  flex: 1;
  overflow: hidden;
}

.profile-name {
  font-size: 14px;
  font-weight: 600;
  color: white;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.profile-role {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  margin: 2px 0 0 0;
}

.sidebar-nav {
  flex: 1;
  padding: 24px 16px;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 24px;
}

.section-label {
  font-size: 11px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 12px 16px;
}

.nav-items {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.15);
  color: white;
}

.nav-item.active .nav-icon {
  color: white;
}

.nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.7);
  transition: color 0.3s ease;
  flex-shrink: 0;
}

.nav-text {
  font-size: 14px;
  font-weight: 500;
  flex: 1;
}

.nav-badge {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
  min-width: 20px;
  text-align: center;
}

.nav-badge.new {
  background: rgba(255, 255, 255, 0.25);
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  margin-top: auto;
}

.presence-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  padding: 8px 12px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
}

.indicator-dot {
  width: 8px;
  height: 8px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
  position: relative;
}

.indicator-dot.active::after {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 50%;
}

.presence-indicator span {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.9);
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 12px 16px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 10px;
  color: white;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.logout-btn svg {
  flex-shrink: 0;
}

/* Scrollbar stylisée */
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 72px;
  }
  
  .logo-text,
  .profile-info,
  .nav-text,
  .section-label,
  .presence-indicator span,
  .logout-btn span {
    display: none;
  }
  
  .logo-container {
    justify-content: center;
    margin-bottom: 20px;
  }
  
  .logo-icon {
    margin: 0;
  }
  
  .user-profile {
    justify-content: center;
    padding: 8px;
  }
  
  .profile-avatar {
    width: 36px;
    height: 36px;
  }
  
  .nav-item {
    justify-content: center;
    padding: 14px;
  }
  
  .nav-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 9px;
    padding: 1px 5px;
    min-width: 16px;
  }
  
  .logout-btn {
    justify-content: center;
    padding: 14px;
  }
}
</style>