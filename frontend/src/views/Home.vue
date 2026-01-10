<template>
  <div class="home-page d-flex">
    <!-- Navbar -->
    <NavBar />

    <!-- Contenu principal -->
    <div class="main-content flex-grow-1 p-2">
      <div class="dashboard-header mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h1 class="fw-bold mb-2">Tableau de bord</h1>
            <p class="text-muted mb-0">Vue d'ensemble de votre établissement</p>
          </div>
          <div class="search-container">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input 
                type="text" 
                class="form-control border-start-0 ps-0" 
                placeholder="Rechercher..."
                aria-label="Rechercher"
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Statistiques principales -->
      <div class="stats-cards row g-4 mb-4">
        <div class="col-md-3">
          <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-2">Élèves</h6>
                  <h3 class="fw-bold mb-0">1,245</h3>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10">
                  <i class="bi bi-people-fill text-primary fs-4"></i>
                </div>
              </div>
              <div class="mt-3">
                <span class="text-success small">
                  <i class="bi bi-arrow-up-right me-1"></i>
                  +5.2% ce mois
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-2">Enseignants</h6>
                  <h3 class="fw-bold mb-0">78</h3>
                </div>
                <div class="stat-icon bg-success bg-opacity-10">
                  <i class="bi bi-person-badge-fill text-success fs-4"></i>
                </div>
              </div>
              <div class="mt-3">
                <span class="text-success small">
                  <i class="bi bi-arrow-up-right me-1"></i>
                  +2 nouveaux
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-2">Classes</h6>
                  <h3 class="fw-bold mb-0">42</h3>
                </div>
                <div class="stat-icon bg-warning bg-opacity-10">
                  <i class="bi bi-house-door-fill text-warning fs-4"></i>
                </div>
              </div>
              <div class="mt-3">
                <span class="text-muted small">
                  <i class="bi bi-dash me-1"></i>
                  Stable
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-3">
          <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-2">Taux de présence</h6>
                  <h3 class="fw-bold mb-0">94.2%</h3>
                </div>
                <div class="stat-icon bg-info bg-opacity-10">
                  <i class="bi bi-clipboard-check-fill text-info fs-4"></i>
                </div>
              </div>
              <div class="mt-3">
                <span class="text-danger small">
                  <i class="bi bi-arrow-down-right me-1"></i>
                  -1.3% cette semaine
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
         <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
       <th scope="col">First</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td><select class="form-select" aria-label="Default select example">
  <option selected>Open this select menu</option>
  <option value="1">One</option>
  <option value="2">Two</option>
  <option value="3">Three</option>
</select></td>
      <td>Otto</td>
      <td>@mdo</td>
         <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
      <td></td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>John</td>
      <td>Doe</td>
      <td>@social</td>
    </tr>
      <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>John</td>
      <td>Doe</td>
      <td>@social</td>
    </tr>
      <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td>John</td>
      <td>Doe</td>
      <td>@social</td>
    </tr>
  </tbody>
</table>
       

      <!-- Tableau des dernières activités -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
              <h5 class="fw-bold mb-0">Activités récentes</h5>
              <button class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download me-1"></i>
                Exporter
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Utilisateur</th>
                      <th>Action</th>
                      <th>Date</th>
                      <th>Statut</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="bi bi-person"></i>
                          </div>
                          <span>Jean Dupont</span>
                        </div>
                      </td>
                      <td>Modification des notes</td>
                      <td>Aujourd'hui, 09:15</td>
                      <td><span class="badge bg-success">Terminé</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="bi bi-person"></i>
                          </div>
                          <span>Marie Martin</span>
                        </div>
                      </td>
                      <td>Création d'un nouvel emploi du temps</td>
                      <td>Hier, 14:30</td>
                      <td><span class="badge bg-success">Terminé</span></td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="avatar-sm bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="bi bi-person"></i>
                          </div>
                          <span>Admin Système</span>
                        </div>
                      </td>
                      <td>Mise à jour des paramètres</td>
                      <td>22 Mars, 11:45</td>
                      <td><span class="badge bg-success">Terminé</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue';

export default {
  name: 'Home',
  components: {
    NavBar
  }
}
</script>

<style scoped>
.home-page {
   height: 100vh; 
  background-color: #f8f9fa;
}

.main-content {
  /* Ajustez selon la largeur de votre navbar */
  transition: margin-left 0.3s ease;
}

/* Si votre navbar est réduite */
.home-page.collapsed .main-content {
  margin-left: 70px;
}

.dashboard-header {
  background-color: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.search-container {
  width: 300px;
  
}

.search-container .input-group {
  border: 1px solid #dee2e6;
  border-radius: 8px;
  overflow: hidden;
}

.search-container input {
  border: none;
  box-shadow: none;
}

.search-container input:focus {
  box-shadow: none;
  border-color: transparent;
}

.stat-card {
  border-radius: 10px;
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-placeholder {
  height: 300px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.avatar-sm {
  width: 32px;
  height: 32px;
  font-size: 0.875rem;
}

.table {
  margin-bottom: 0;
}

.table th {
  border-top: none;
  font-weight: 600;
  color: #6c757d;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.table td {
  vertical-align: middle;
  border-top: 1px solid #f1f3f4;
}

/* Responsive design */
@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
    padding: 1rem;
  }
  
  .dashboard-header {
    flex-direction: column;
  }
  
  .search-container {
    width: 100%;
    margin-top: 1rem;
  }
  
  .stats-cards .col-md-3 {
    margin-bottom: 1rem;
  }
}
</style>