import { createRouter, createWebHistory } from "vue-router";
import TeacherLogin from "../views/TeacherLogin.vue";
import DashboardPage from "../views/DashboardPage.vue";
import TeacherModules from "../views/TeacherModules.vue";
import TeacherSeances from "../views/TeacherSeances.vue";
import TeacherDocuments from "../views/TeacherDocuments.vue";
import AnnoncesPage from "../views/AnnoncesPage.vue";

const routes = [
  { path: "/", name: "Login", component: TeacherLogin },
  { path: "/dashboard", name: "Dashboard", component: DashboardPage},
  { path: "/modules", name: "Modules", component: TeacherModules },
  { path: "/seances", name: "Seances", component: TeacherSeances },
  { path: "/documents", name: "Documents", component: TeacherDocuments },
  { path: "/annonces", name: "Annonces", component: AnnoncesPage },
  {
  path: '/face-recognition',
  component: () => import('@/views/FaceRecognition.vue')
  }

];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
