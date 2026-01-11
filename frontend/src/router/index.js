import { createRouter, createWebHistory } from "vue-router";
import AdminLayout from "../layouts/AdminLayout.vue";

// Import des composants
import Dashboard from "../views/Admin/Dashboard.vue";
import Planning from "../views/Admin/Planning.vue";
import GestionEmplois from "../views/Admin/GestionEmplois.vue";
import Etudiants from "../views/Admin/Etudiants.vue";
import ValidationEmploi from "../views/Chef/ValidationEmploi.vue";
import Parametres from "../views/Admin/Parametres.vue";
import Enseignants from "../views/Admin/enseignants.vue"; // Notez le E majuscule

const routes = [
  {
    path: "/",
    component: AdminLayout,
    children: [
      // Redirection depuis la racine
      { path: "", redirect: "/admin/dashboard" },
      
      // Route Dashboard
      { 
        path: "admin/dashboard", 
        name: "Dashboard",
        component: Dashboard 
      },
      
      // Route Étudiants
      { 
        path: "admin/etudiants", 
        name: "Etudiants",
        component: Etudiants 
      },
      
      // Route Enseignants (corrigez le nom du composant)
      { 
        path: "admin/enseignants", 
        name: "Enseignants",
        component: Enseignants 
      },
      
      // Route Planning
      { 
        path: "admin/planning", 
        name: "Planning",
        component: Planning 
      },
      
      // Route Validation (pour chef)
      { 
        path: "chef/validation", 
        name: "Validation",
        component: ValidationEmploi 
      },
      
      // Route Paramètres
      { 
        path: "admin/parametres", 
        name: "Parametres",
        component: Parametres 
      },
      
      // Route Gestion des emplois
      { 
        path: "admin/gestion-emplois", 
        name: "GestionEmplois",
        component: GestionEmplois 
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;