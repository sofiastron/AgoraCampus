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
</style>
