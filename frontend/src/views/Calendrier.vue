<template>
  <div style="height: 800px; background: white; padding: 20px;">
    <FullCalendar :options="calendarOptions" />
  </div>
</template>

<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'

const calendarOptions = {
  plugins: [dayGridPlugin],
  initialView: 'dayGridMonth',
  events: [
    {
      title: 'Séance test',
      start: '2025-12-01T09:00:00',
      end: '2025-12-01T11:00:00'
    }
  ]
}
</script>
