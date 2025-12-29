<template>
  <div class="presence-page">

    <div class="cards-container">
      <PresenceCard
        title="Présence par QR Code"
        description="Enregistrez la présence via QR Code"
        @click="goToQRCode"
      />
      <PresenceCard
        title="Présence par Reconnaissance Faciale"
        description="Enregistrez la présence via reconnaissance faciale"
        @click="goToFacialRecognition"
      />
    </div>
    <router-view />

  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import PresenceCard from '@/components/PresenceCard.vue'

const router = useRouter()

function goToQRCode() {
  router.push('/presence/qrcode')
}

function goToFacialRecognition() {
  router.push('/presence/facial')
}
</script>

<style scoped>
.presence-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1rem;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

.presence-page h1 {
  font-size: 2.5rem;
  font-weight: 700;
  text-align: center;
  margin-bottom: 2rem;
  color: #1e293b;
}

.cards-container {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
}


</style>
