<template>
  <div class="navbar-vertical d-flex flex-column flex-shrink-0 bg-white border-end shadow-sm" 
       :style="{ width: isCollapsed ? '70px' : '280px' }">
    
    <!-- En-tête avec logo -->
    <div class="navbar-header p-3 border-bottom">
      <div class="d-flex align-items-center justify-content-between">
        <!-- Logo et titre (caché quand réduit) -->
        <div v-if="!isCollapsed" class="d-flex align-items-center">
          <div class="logo bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" 
               style="width: 42px; height: 41px;">
            <span class="text-white fw-bold"><i class="bi bi-mortarboard" @click="toggleCollapse"></i>
            </span>
            
          </div>
          <div>
            <h5 class="fw-bold text-primary mb-0">EduAdmin</h5>
            <small class="text-muted">Gestion scolaire</small>
          
          </div>
          
        </div>
        
        <!-- Logo seul quand réduit -->
        <div v-else class="logo bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
             style="width: 42px; height: 43px;">
          <span class="text-white fw-bold" @click="toggleCollapse" ><i class="bi bi-mortarboard"></i></span>
        </div>
    
      </div>
    </div>

    <!-- Menu de navigation -->
    <div class="nav-container flex-grow-1 overflow-auto py-3">
      <ul class="nav flex-column">
        <!-- Tableau de bord -->
       <li class="nav-item">
  <router-link
    to="/"
    class="nav-link"
    :class="{ 'active': activeItem === 'Home' }"
    @click="setActiveItem('Home')"
  >
    <div class="d-flex align-items-center">
      <i class="bi bi-house-door fs-5"></i>
      <span v-if="!isCollapsed" class="ms-3">Tableau de bord</span>
    </div>

    <div
      v-if="!isCollapsed && activeItem === 'Home'"
      class="active-indicator"
    ></div>
  </router-link>
</li>

        
        <!-- Utilisateurs (actif) -->
         <li class="nav-item">
  <router-link
    to="/users"
    class="nav-link"
    :class="{ '': activeItem === 'users' }"
    @click="setActiveItem('users')"
  >
    <div class="d-flex align-items-center">
    <i class="bi bi-people fs-5"></i>
      <span v-if="!isCollapsed" class="ms-3">Utilisateurs</span>
    </div>

    <div
      v-if="!isCollapsed && activeItem === 'Home'"
      class="active-indicator"
    ></div>
  </router-link>
</li>
       
        
        <!-- Emplois du temps -->
         <li class="nav-item">
  <router-link
    to="planning"
    class="nav-link"
    :class="{ '': activeItem === 'planning' }"
    @click="setActiveItem('palnning')"
  >
    <div class="d-flex align-items-center">
    <i class="bi bi-calendar-week fs-5"></i>  
      <span v-if="!isCollapsed" class="ms-3">Emplois de temps</span>
    </div>

    <div
      v-if="!isCollapsed && activeItem === 'Home'"
      class="active-indicator"
    ></div>
  </router-link>
</li>
        
        <!-- Paramètres (actif) -->
         <li>
          <router-link
    to="emploi"
    class="nav-link"
    :class="{ '': activeItem === 'planning' }"
    @click="setActiveItem('palnning')"
  >
    <div class="d-flex align-items-center">
     <i class="bi bi-gear fs-5"></i>
              <span v-if="!isCollapsed" class="ms-3">Paramètres</span>
    </div>

    <div
      v-if="!isCollapsed && activeItem === 'Home'"
      class="active-indicator"
    ></div>
  </router-link>
</li>
        
        
      </ul>
      
      <!-- Séparateur -->
      <div v-if="!isCollapsed" class="border-top my-4"></div>
      
      <!-- Section administrateur (visible uniquement quand étendu) -->
      <div v-if="!isCollapsed" class="px-3">
        <div class="card border-0 bg-light">
          <div class="card-body p-3">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                   style="width: 36px; height: 36px;">
                <span class="fw-bold">AD</span>
              </div>
              <div>
                <div class="fw-semibold">Admin User</div>
                <div class="small text-muted">admin@ecole.fr
                </div>
                 
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer de la navbar -->
    <div class="navbar-footer p-2 border-top">
      <!-- Version réduite - juste icônes -->
    
             <button class="btn btn-sm btn-outline-secondary ms-1" @click="toggleCollapse" title="Réduire">
              <i :class="collapseIcon"></i></button>
          </div>
           
  
      
      <!-- Version étendue - texte complet -->
    
        

  </div>
</template>

<script>
export default {
  name: 'NavBar',
  data() {
    return {
      isCollapsed: false,
      activeItem: 'users' // Par défaut, Utilisateurs est actif
    }
  },
  computed: {
    collapseIcon() {
      return this.isCollapsed ? 'bi-chevron-right' : 'bi-chevron-left'
    }
  },
  methods: {
    toggleCollapse() {
      this.isCollapsed = !this.isCollapsed
      this.$emit('toggle-collapse', this.isCollapsed)
    },
    setActiveItem(item) {
      this.activeItem = item
      this.$emit('nav-item-changed', item)
    }
  },
  mounted() {
    // Initialiser les éléments actifs basés sur l'URL ou les props
    const path = window.location.pathname
    if (path.includes('dashboard')) this.activeItem = 'dashboard'
   
  }
}
</script>

<style scoped>
.navbar-vertical {
  height: 100vh;
  transition: width 0.3s ease;
  position: sticky;
  top: 0;
  z-index: 100;
}

.nav-container {
  height: calc(100vh - 130px);
}

.nav-link {
  padding: 0.75rem 1.25rem;
  color: #495057;
  border-radius: 0;
  border-left: 3px solid transparent;
  transition: all 0.2s;
  position: relative;
}

.nav-link:hover:not(.active) {
  background-color: #f8f9fa;
  color: #0d6efd;
  border-left-color: #dee2e6;
}

.nav-link.active {
  background-color: #e7f1ff;
  color: #0d6efd !important;
  border-left-color: #0d6efd;
  font-weight: 500;
}

.nav-link .active-indicator {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 6px;
  height: 6px;
  background-color: #0d6efd;
  border-radius: 50%;
}

.logo {
  transition: all 0.3s ease;
}

/* Style pour la version réduite */
.navbar-vertical[style*="width: 70px"] .nav-link {
  padding: 0.75rem;
  display: flex;
  justify-content: center;
}

.navbar-vertical[style*="width: 70px"] .nav-link i {
  margin: 0 !important;
  font-size: 1.25rem;
}

.navbar-vertical[style*="width: 70px"] .nav-link span {
  display: none;
}

.navbar-vertical[style*="width: 70px"] .nav-link .badge {
  display: none;
}

.navbar-vertical[style*="width: 70px"] .navbar-footer .btn {
  padding: 0.25rem;
  width: 32px;
  height: 32px;
}

/* Tooltips pour la version réduite */
.navbar-vertical[style*="width: 70px"] .nav-link {
  position: relative;
}

.navbar-vertical[style*="width: 70px"] .nav-link::after {
  content: attr(title);
  position: absolute;
  left: 100%;
  top: 50%;
  transform: translateY(-50%);
  background-color: #333;
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s;
  z-index: 1000;
  margin-left: 10px;
}

.navbar-vertical[style*="width: 70px"] .nav-link:hover::after {
  opacity: 1;
  visibility: visible;
}

/* Scrollbar personnalisée */
.nav-container::-webkit-scrollbar {
  width: 4px;
}

.nav-container::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.nav-container::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.nav-container::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>