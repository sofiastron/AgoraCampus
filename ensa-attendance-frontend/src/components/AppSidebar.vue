<template>
  <aside class="sidebar">
    <div class="logo">
      <img src="@/assets/logo.png" alt="Logo" />
      <h2>AGORA CAMPUS</h2>
    </div>

    <nav>
      <ul>
        <li>
          <router-link to="/dashboard" active-class="active">
            Dashboard
          </router-link>
        </li>

        <li>
          <router-link to="/documents-annonces" active-class="active">
            Documents & Annonces
          </router-link>
        </li>

        <li>
          <router-link to="/presence" active-class="active">
            Présence
          </router-link>
        </li>

        <li>
          <router-link to="/liste-etudiants" active-class="active">
            Liste des étudiants
          </router-link>
        </li>

        <li>
          <router-link to="/emploi-du-temps" active-class="active">
            Emploi du temps
          </router-link>
        </li>
      </ul>
    </nav>
  </aside>
</template>

<script setup>

</script>

<style scoped>
.sidebar {
  width: 220px;
  background-color: white;
  color: blue;
  height: 100vh;
  padding: 1rem;
  box-sizing: border-box;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 2rem;
}

.logo img {
  width: 80px;
  height: 80px;
  margin-top: -70px;
}

.logo h2 {
  font-size: 1.2rem;
  font-weight: bold;
  margin-top: -50px;
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar li {
  margin-bottom: 1rem;
}

.sidebar a {
  color: blue;
  text-decoration: none;
  font-weight: 600;
  display: block;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  transition: background-color 0.2s;
}

.sidebar a.active,
.sidebar a:hover {
  background-color: #4338ca;
  color: white;
}
</style>
