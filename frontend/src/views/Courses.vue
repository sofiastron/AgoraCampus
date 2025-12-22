<template>
  <div class="courses">
    <h1 class="title">Mes cours</h1>

    <div class="layout">
    <!-- module -->
      <aside class="modules">
        <h3>Modules</h3>
        <ul>
          <li
            v-for="m in modules"
            :key="m.id"
            :class="{ active: m.id === selectedModuleId }"
            @click="selectModule(m)"
          >
            {{ m.titre }}
          </li>
        </ul>
      </aside>

<!-- doc -->
<section class="documents">
        <h3 v-if="selectedModule">
          Documents — {{ selectedModule.titre }}
        </h3>

        <p v-if="!documents.length && selectedModule">
          Aucun document disponible
        </p>

        <div v-for="d in documents" :key="d.id" class="doc-card">
          <h4>{{ d.titre }}</h4>
          <p>{{ d.description }}</p>

          <a
            :href="fileUrl(d.fichier)"
            target="_blank"
            class="btn"
          >
            Télécharger
          </a>
        </div>
</section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/axios'

const modules = ref([])
const documents = ref([])
const selectedModule = ref(null)
const selectedModuleId = ref(null)

// changer le module
onMounted(async () => {
  const res = await axios.get('/etudiant/modules')
  modules.value = res.data


  if (modules.value.length) {
    selectModule(modules.value[0])
  }
})

// selectionner un module
async function selectModule(module) {
  selectedModule.value = module
  selectedModuleId.value = module.id

  const res = await axios.get(
    `/etudiant/modules/${module.id}/documents`
  )

  documents.value = res.data
}

//url fich
function fileUrl(path) {
  return `${import.meta.env.VITE_API_URL}/storage/${path}`
}
</script>

<style scoped>
.title {
  font-size: 24px;
  margin-bottom: 20px;
}

.layout {
  display: grid;
  grid-template-columns: 250px 1fr;
  gap: 20px;
}

.modules {
  background: #fff;
  padding: 15px;
  border-radius: 10px;
}

.modules ul {
  list-style: none;
  padding: 0;
}

.modules li {
  padding: 10px;
  cursor: pointer;
  border-radius: 6px;
}

.modules li.active,
.modules li:hover {
  background: #f1f5f9;
  font-weight: bold;
}

.documents {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
}

.doc-card {
  border-bottom: 1px solid #eee;
  padding: 15px 0;
}

.btn {
  display: inline-block;
  margin-top: 10px;
  padding: 6px 12px;
  background: #2563eb;
  color: white;
  border-radius: 6px;
  text-decoration: none;
}
</style>
