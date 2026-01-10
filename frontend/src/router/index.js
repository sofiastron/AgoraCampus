import { createRouter, createWebHistory } from "vue-router";
import AdminLayout from "../layouts/AdminLayout.vue";

import Dashboard from "../views/Dashboard.vue";
import Etudiants from "../views/Admin/Etudiants.vue";
import AjouterEmploi from "../views/Admin/AjouterEmploi.vue";
import ValidationEmploi from "../views/Chef/ValidationEmploi.vue";
import Parametres from "../views/Admin/Parametres.vue";

import enseignants from "../views/Admin/enseignants.vue";

const routes = [
  {
    path: "/",
    component: AdminLayout,
    children: [
      { path: "", component: Dashboard }, // page d'accueil
      { path: "admin/Dashboard", component: Dashboard }, // nouveau chemin pour Dashboard
      { path: "admin/etudiants", component: Etudiants }, // liste étudiants
      { path: "admin/emploi", component: AjouterEmploi }, // ajouter emploi
      { path: "chef/validation", component: ValidationEmploi },
      { path: "admin/enseignants", component: enseignants },
      { path: "admin/parametres", component: Parametres },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
