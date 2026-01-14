<template>
  <FullCalendar
    :plugins="[dayGridPlugin]"
    :events="events"
    initialView="dayGridMonth"
    locale="fr"
    height="auto"
  />
</template>

<script setup>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'

defineProps({
  events: {
    type: Array,
    required: true
  }
})
</script>
