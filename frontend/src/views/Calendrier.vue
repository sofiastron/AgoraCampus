<template>
  <div style="background: white; padding: 20px; height: 800px;">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import FullCalendar from '@fullcalendar/vue3'
import timeGridPlugin from '@fullcalendar/timegrid'

// 1️⃣ événements réactifs
const events = ref([])

// 2️⃣ options du calendrier
const calendarOptions = ref({
  plugins: [timeGridPlugin],
  initialView: 'timeGridWeek',
  initialDate: '2025-12-30', // pour inclure tes séances de test
  locale: 'fr',
  slotMinTime: '08:00:00',
  slotMaxTime: '18:00:00',
  slotDuration: '02:00:00',
  allDaySlot: false,
  headerToolbar: {
    left: '',
    center: 'title',
    right: ''
  },
  events // ← passer directement la ref
})

// 3️⃣ appel API pour récupérer les séances
const loadCalendar = async () => {
  try {
    const token = localStorage.getItem('token')
    const response = await axios.get('http://localhost:8000/api/etudiant/calendar', {
  headers: { Authorization: `Bearer ${token}` }
})


    console.log('Données reçues :', response.data) // debug

    // assigner les événements
    events.value = response.data

  } catch (error) {
    console.error('Erreur chargement calendrier:', error)
  }
}

// 4️⃣ charger au montage
onMounted(loadCalendar)
</script>
