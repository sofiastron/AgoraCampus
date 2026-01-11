<template>
  <div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <!-- Logo/En-tête -->
      <div class="sidebar-header">
        <h2 class="logo">AgoraCampus</h2>
        <div class="user-profile">
          <div class="avatar">
            <span>A</span>
          </div>
          <div class="user-info">
            <p class="user-name">Administrateur</p>
            <p class="user-role">Admin</p>
          </div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/admin/dashboard" class="nav-item" active-class="active">
          <span class="nav-icon">📊</span>
          <span class="nav-text">Tableau de bord</span>
        </router-link>
        
        <router-link to="/admin/etudiants" class="nav-item" active-class="active">
          <span class="nav-icon">👨‍🎓</span>
          <span class="nav-text">Étudiants</span>
        </router-link>
        
        <router-link to="/admin/enseignants" class="nav-item" active-class="active">
          <span class="nav-icon">👨‍🏫</span>
          <span class="nav-text">Enseignants</span>
        </router-link>
        
        <router-link to="/admin/planning" class="nav-item" active-class="active">
          <span class="nav-icon">📅</span>
          <span class="nav-text">Emploi du temps</span>
        </router-link>

        <router-link to="/admin/gestion-emplois" class="nav-item" active-class="active">
          <span class="nav-icon">✅</span>
          <span class="nav-text">Validation Chef</span>
        </router-link>
        
        <div class="nav-divider"></div>
        
        <a href="/logout" class="nav-item logout">
          <span class="nav-icon">🚪</span>
          <span class="nav-text">Déconnexion</span>
        </a>
      </nav>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
      <header class="top-bar">
        <div class="breadcrumb">
          <router-link to="/admin/dashboard" class="breadcrumb-link">Accueil</router-link>
          <span class="breadcrumb-separator">/</span>
          <span class="current-page">{{ currentPageName }}</span>
        </div>
        <div class="header-actions">
          <button class="notification-btn">
            <span class="notification-icon">🔔</span>
            <span class="notification-badge">3</span>
          </button>
          <div class="current-time">
            {{ currentTime }}
          </div>
        </div>
      </header>
      
      <div class="content-wrapper">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'AdminLayout',
  data() {
    return {
      currentTime: '',
      currentPageName: 'Tableau de bord'
    }
  },
  mounted() {
    this.updateTime();
    setInterval(this.updateTime, 60000);
    
    // Mettre à jour le nom de la page actuelle
    this.updatePageName();
    this.$watch(() => this.$route.path, this.updatePageName);
  },
  methods: {
    updateTime() {
      const now = new Date();
      this.currentTime = now.toLocaleTimeString('fr-FR', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false 
      });
    },
    updatePageName() {
      const route = this.$route.path;
      const pageNames = {
        '/admin/dashboard': 'Tableau de bord',
        '/admin/etudiants': 'Étudiants',
        '/admin/enseignants': 'Enseignants',
        '/admin/planning': 'Emploi du temps',
        '/admin/gestion-emplois': 'Validation des Emplois',
        '/admin/parametres': 'Paramètres'
      };
      this.currentPageName = pageNames[route] || 'Admin';
    }
  }
}
</script>

<style>
/* Variables de couleurs globales */
:root {
  --sidebar-bg: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
  --sidebar-text: #ffffff;
  --sidebar-text-soft: rgba(255, 255, 255, 0.85);
  --sidebar-hover: rgba(129, 140, 248, 0.15);
  --sidebar-active: rgba(99, 102, 241, 0.25);
  --sidebar-border: #6366f1;

  --main-bg: #f5f7ff;
  --topbar-bg: #ffffff;
  --shadow: rgba(0, 0, 0, 0.08);

  --text-color: #1e293b;

  /* Couleurs principales */
  --primary-color: #3730a3;
  --secondary-color: #6366f1;
  --accent-color: #818cf8;
  --danger-color: #ef4444;
}
</style>

<style scoped>
.admin-container {
  display: flex;
  min-height: 100vh;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
  background: var(--main-bg);
}

/* Sidebar - Design moderne */
.sidebar {
  width: 280px;
  background: var(--sidebar-bg);
  color: var(--sidebar-text);
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
  position: relative;
  z-index: 100;
  transition: all 0.3s ease;
}

.sidebar-header {
  padding: 30px 25px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(0, 0, 0, 0.1);
}

.logo {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 25px;
  color: white;
  letter-spacing: 0.5px;
  background: linear-gradient(45deg, #3498db, #2ecc71);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  position: relative;
  padding-left: 10px;
}

.logo::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 70%;
  background: linear-gradient(180deg, #3498db, #2ecc71);
  border-radius: 2px;
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 15px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.user-profile:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.4rem;
  border: 3px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.user-info {
  flex: 1;
}

.user-name {
  font-weight: 600;
  font-size: 1rem;
  margin-bottom: 4px;
  color: white;
}

.user-role {
  font-size: 0.85rem;
  opacity: 0.9;
  background: rgba(52, 152, 219, 0.2);
  padding: 3px 10px;
  border-radius: 12px;
  display: inline-block;
  color: #3498db;
}

/* Navigation Styles */
.sidebar-nav {
  flex: 1;
  padding: 25px 0;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
}

.sidebar-nav::-webkit-scrollbar {
  width: 5px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.3);
  border-radius: 10px;
}

.nav-item {
  padding: 16px 25px;
  color: var(--sidebar-text-soft);
  text-decoration: none;
  border-left: 4px solid transparent;
  display: flex;
  align-items: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  margin: 2px 15px;
  border-radius: 8px;
  position: relative;
  overflow: hidden;
}

.nav-item::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, rgba(52, 152, 219, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.nav-item:hover {
  background: var(--sidebar-hover);
  color: var(--sidebar-text);
  transform: translateX(5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.nav-item:hover::before {
  opacity: 1;
}

.nav-item.active {
  background: var(--sidebar-active);
  border-left-color: var(--sidebar-border);
  color: white;
  box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
  transform: translateX(0);
}

.nav-item.active .nav-icon {
  transform: scale(1.1);
}

.nav-item.active::after {
  content: '';
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  background: var(--sidebar-border);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7);
  }
  70% {
    box-shadow: 0 0 0 6px rgba(52, 152, 219, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
  }
}

.nav-icon {
  font-size: 1.3rem;
  margin-right: 15px;
  width: 24px;
  text-align: center;
  transition: transform 0.3s ease;
}

.nav-text {
  font-size: 0.95rem;
  font-weight: 500;
  letter-spacing: 0.3px;
}

.nav-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
  margin: 20px 25px;
}

.logout {
  margin-top: 10px;
  color: rgba(255, 255, 255, 0.7);
  background: rgba(231, 76, 60, 0.1);
  border-left-color: transparent !important;
}

.logout:hover {
  color: white;
  background: rgba(231, 76, 60, 0.2);
  border-left-color: var(--danger-color) !important;
}

/* Main Content Styles */
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.top-bar {
  background: var(--topbar-bg);
  padding: 20px 30px;
  box-shadow: 0 2px 15px var(--shadow);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 50;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
}

.breadcrumb {
  display: flex;
  align-items: center;
  font-size: 0.95rem;
}

.breadcrumb-link {
  color: var(--primary-color);
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
  padding: 5px 10px;
  border-radius: 6px;
}

.breadcrumb-link:hover {
  text-decoration: none;
  background: rgba(52, 152, 219, 0.1);
  transform: translateY(-1px);
}

.breadcrumb-separator {
  margin: 0 12px;
  color: #cbd5e0;
  font-weight: 300;
}

.current-page {
  color: var(--text-color);
  font-weight: 600;
  padding: 5px 15px;
  background: linear-gradient(45deg, #f8fafc, #ffffff);
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 25px;
}

.notification-btn {
  position: relative;
  background: none;
  border: none;
  font-size: 1.4rem;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  width: 45px;
  height: 45px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.notification-btn:hover {
  transform: translateY(-2px) rotate(10deg);
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: var(--danger-color);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
  animation: bounce 2s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.current-time {
  font-weight: 600;
  color: white;
  font-size: 1rem;
  background: linear-gradient(45deg, #3498db, #2ecc71);
  padding: 8px 20px;
  border-radius: 25px;
  border: none;
  box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
  min-width: 100px;
  text-align: center;
  letter-spacing: 1px;
}

.content-wrapper {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

/* Animation d'entrée pour le contenu */
.content-wrapper > * {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 1024px) {
  .sidebar {
    width: 240px;
  }
  
  .sidebar-header {
    padding: 20px;
  }
  
  .nav-item {
    padding: 14px 20px;
    margin: 2px 10px;
  }
}

@media (max-width: 768px) {
  .admin-container {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    transform: translateY(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar.active {
    transform: translateY(0);
  }
  
  .sidebar-header {
    padding: 20px;
  }
  
  .sidebar-nav {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 15px;
    max-height: 300px;
    overflow-y: auto;
  }
  
  .nav-item {
    flex: 1;
    min-width: 140px;
    justify-content: flex-start;
    padding: 12px 15px;
    border-left: none;
    border-bottom: 3px solid transparent;
    margin: 0;
    border-radius: 6px;
  }
  
  .nav-item:hover,
  .nav-item.active {
    border-left: none;
    border-bottom-color: var(--sidebar-border);
    transform: translateY(-2px);
  }
  
  .nav-item.active::after {
    display: none;
  }
  
  .nav-text {
    font-size: 0.85rem;
  }
  
  .nav-icon {
    margin-right: 10px;
  }
  
  .main-content {
    margin-top: 70px;
  }
  
  .top-bar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    padding: 15px 20px;
    z-index: 900;
  }
  
  .content-wrapper {
    padding: 20px 15px;
    margin-top: 60px;
  }
  
  .nav-divider {
    display: none;
  }
}

@media (max-width: 480px) {
  .sidebar-nav {
    flex-direction: column;
    gap: 5px;
  }
  
  .nav-item {
    min-width: 100%;
  }
  
  .header-actions {
    gap: 15px;
  }
  
  .current-time {
    padding: 6px 12px;
    font-size: 0.9rem;
    min-width: 80px;
  }
  
  .notification-btn {
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
  }
}
</style>