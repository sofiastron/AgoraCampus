<template>
  <aside class="sidebar">
    <!-- Logo -->
    <div class="logo">
      <div class="logo-icon">🎓</div>
      <div>
        <h2>AgoraCampus</h2>
        <p>Portail Enseignant</p>
      </div>
    </div>

    <!-- Menu -->
    <nav class="menu">
      <ul>
        <li
          v-for="item in menu"
          :key="item.label"
          :class="{ active: route.path === item.path }"
          @click="go(item.path)"
        >
          <span class="icon">{{ item.icon }}</span>
          <span class="text">{{ item.label }}</span>
        </li>
        <li class="photo-capture-item">
          <button class="photo-capture-btn" @click="go('/face-recognition')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
              <circle cx="12" cy="13" r="4"></circle>
            </svg>
            Photo Capture
          </button>
        </li>
      </ul>
    </nav>

    <!-- Profile -->
    <div class="profile">
      <div class="avatar">
        {{ teacher.nom ? teacher.nom[0] : "?" }}
      </div>
      <div class="info">
        <p class="name">Dr. {{ teacher.nom }}</p>
        <p class="role">{{ teacher.specialite }}</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();

const menu = [
  { label: "Dashboard", icon: "📊", path: "/teacher/dashboard" },
  { label: "Documents", icon: "📄", path: "/teacher/documents" },
  { label: "QR Code", icon: "🔳", path: "/teacher/qrcode" },
  { label: "Attendance", icon: "📝", path: "/teacher/attendance" },
];

const teacher = ref({
  nom: "",
  specialite: ""
});

onMounted(() => {
  const data = localStorage.getItem("teacher");
  if (data) {
    teacher.value = JSON.parse(data);
  }
});

const go = (path) => {
  router.push(path);
};
</script>

<style scoped>
.sidebar {
  width: 260px;
  min-height: 100vh;
  background: #f9f9ff;
  padding: 1.2rem;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  box-shadow: 4px 0 10px rgba(0,0,0,0.05);
}
.logo {
  display: flex;
  gap: 0.8rem;
}
.logo-icon {
  font-size: 2rem;
}
.menu ul {
  list-style: none;
  padding: 0;
}
.menu li {
  padding: 10px;
  border-radius: 10px;
  cursor: pointer;
  color: #4f46e5;
}
.menu li.active {
  background: linear-gradient(90deg, #3730a3, #6366f1);
  color: white;
}
.profile {
  background: #eef2ff;
  padding: 1rem;
  border-radius: 14px;
  display: flex;
  gap: 1rem;
}
.avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #3730a3;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Styles pour le bouton Photo Capture */
.photo-capture-item {
  padding: 0 !important;
  margin-top: 10px;
}

.photo-capture-btn {
  width: 100%;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: white;
  border: none;
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 15px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
  transition: all 0.3s ease;
}

.photo-capture-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(79, 70, 229, 0.4);
}

.photo-capture-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3);
}
</style>
