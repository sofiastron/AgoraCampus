<!-- <script setup>
import api from "../../services/api";
import { ref, onMounted } from "vue";

const etudiants = ref([]);

const load = async () => {
  etudiants.value = (await api.get("/etudiants")).data;
};

const del = async (id) => {
  await api.delete(`/etudiants/${id}`);
  load();
};

onMounted(load);
</script>

<template>
  <table border="1">
    <thead>
      <tr>
        <th>Nom</th>
        <th>Email</th>
        <th>CNE</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="e in etudiants" :key="e.id">
        <td>{{ e.nom }}</td>
        <td>{{ e.email }}</td>
        <td>{{ e.cne }}</td>
        <td>
          <button @click="del(e.id)">Supprimer</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
*/ -->
<script setup>
import api from "../../services/api";
import { ref, onMounted } from "vue";

const etudiants = ref([]);

const load = async () => {
  etudiants.value = (await api.get("/etudiants")).data;
};

const del = async (id) => {
  if (confirm("Êtes-vous sûr de vouloir supprimer cet étudiant ?")) {
    await api.delete(`/etudiants/${id}`);
    load();
  }
};

onMounted(load);
</script>

<template>
  <div class="container">
    <div class="header">
      <h1>Gestion des Étudiants</h1>
      <p class="subtitle">Liste des étudiants inscrits dans le système</p>
    </div>

    <div class="table-container">
      <table class="students-table">
        <thead>
          <tr>
            <th class="header-cell">Nom</th>
            <th class="header-cell">Email</th>
            <th class="header-cell">CNE</th>
            <th class="header-cell actions-header">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="e in etudiants" :key="e.id" class="table-row">
            <td class="data-cell">{{ e.nom }}</td>
            <td class="data-cell email-cell">{{ e.email }}</td>
            <td class="data-cell cne-cell">{{ e.cne }}</td>
            <td class="data-cell actions-cell">
              <button class="btn-delete" @click="del(e.id)">
                <span class="btn-icon">🗑️</span>
                <span class="btn-text">Supprimer</span>
              </button>
            </td>
          </tr>
          <tr v-if="etudiants.length === 0" class="empty-row">
            <td colspan="4" class="empty-message">
              Aucun étudiant trouvé
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  
  </div>
</template>

<style scoped>
.container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
  min-height: 100vh;
}

.header {
  text-align: start;
  margin-bottom: 10px;
  padding-bottom: 10px;
  border-bottom: 2px solid rgba(55, 48, 163, 0.1);
}

h1 {
  color: #373083;
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 10px;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.subtitle {
  color: #666;
  font-size: 1.1rem;
  font-weight: 400;
}

.table-container {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(55, 48, 163, 0.1);
  overflow: hidden;
  margin-bottom: 30px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.table-container:hover {
  box-shadow: 0 15px 40px rgba(55, 48, 163, 0.15);
}

.students-table {
  width: 100%;
  border-collapse: collapse;
  border: none;
}

.header-cell {
  background: linear-gradient(to bottom, #373083, #2a2568);
  color: white;
  font-weight: 600;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 20px 15px;
  text-align: left;
  border: none;
  position: relative;
}

.header-cell:after {
  content: '';
  position: absolute;
  right: 0;
  top: 25%;
  height: 50%;
  width: 1px;
  background-color: rgba(255, 255, 255, 0.2);
}

.header-cell:last-child:after {
  display: none;
}

.actions-header {
  text-align: center;
}

.table-row {
  border-bottom: 1px solid #f0f0f0;
  transition: all 0.3s ease;
}

.table-row:hover {
  background-color: rgba(55, 48, 163, 0.03);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(55, 48, 163, 0.05);
}

.data-cell {
  padding: 18px 15px;
  color: #333;
  font-size: 1rem;
  border: none;
}

.email-cell {
  color: #373083;
  font-weight: 500;
}

.cne-cell {
  font-family: 'Courier New', monospace;
  font-weight: 600;
}

.actions-cell {
  text-align: center;
}

.btn-delete {
  background: linear-gradient(to right, #ff4757, #ff3838);
  color: white;
  border: none;
  border-radius: 50px;
  padding: 10px 20px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 10px rgba(255, 71, 87, 0.2);
}

.btn-delete:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(255, 71, 87, 0.3);
}

.btn-delete:active {
  transform: translateY(0);
}

.btn-icon {
  font-size: 1rem;
}

.empty-row {
  border: none;
}

.empty-message {
  text-align: center;
  padding: 50px 20px;
  color: #888;
  font-size: 1.1rem;
  font-style: italic;
}

.footer-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(55, 48, 163, 0.08);
}

.color-sample {
  display: flex;
  align-items: center;
  gap: 15px;
}

.sample-box {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  border: 2px solid #f0f0f0;
}

.student-count {
  background: linear-gradient(to right, #373083, #4a43a0);
  color: white;
  padding: 10px 20px;
  border-radius: 50px;
  font-weight: 600;
  font-size: 1rem;
  box-shadow: 0 4px 10px rgba(55, 48, 163, 0.2);
}

/* Animation pour le chargement des lignes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.table-row {
  animation: fadeInUp 0.5s ease forwards;
}

.table-row:nth-child(1) { animation-delay: 0.1s; }
.table-row:nth-child(2) { animation-delay: 0.2s; }
.table-row:nth-child(3) { animation-delay: 0.3s; }
.table-row:nth-child(4) { animation-delay: 0.4s; }
.table-row:nth-child(5) { animation-delay: 0.5s; }

/* Responsive design */
@media (max-width: 768px) {
  .container {
    padding: 15px;
  }
  
  h1 {
    font-size: 2rem;
  }
  
  .footer-info {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .btn-delete {
    padding: 8px 15px;
    font-size: 0.8rem;
  }
  
  .header-cell, .data-cell {
    padding: 12px 10px;
    font-size: 0.9rem;
  }
}
</style>