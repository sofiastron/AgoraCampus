<template>
  <div class="container mt-4">
    <h2>Scanner le QR Code du professeur</h2>
    
    <div id="reader" style="width:500px"></div>
    
    <div v-if="result">
      <p>QR Code scanné : {{ result }}</p>
      <button @click="enregistrerPresence">Enregistrer présence</button>
    </div>
  </div>
</template>

<script>
import { Html5Qrcode } from "html5-qrcode";
import axios from "@/axios";

export default {
  data() {
    return {
      result: null,
      seanceId: null
    };
  },
  mounted() {
    const html5QrCode = new Html5Qrcode("reader");

    Html5Qrcode.getCameras().then(cameras => {
      if (cameras && cameras.length) {
        html5QrCode.start(
          cameras[0].id,
          {
            fps: 10,    // frames par seconde
            qrbox: 250  // zone de scan
          },
          qrCodeMessage => {
            console.log("QR Code détecté : ", qrCodeMessage);
            // On suppose que le QR code contient l'ID de sceance
            this.seanceId = parseInt(qrCodeMessage);
            this.result = qrCodeMessage;
            html5QrCode.stop(); // stop après scan
          },
          errorMessage => {
            // console.log(errorMessage);
          }
        );
      }
    }).catch(err => {
      console.error("Erreur accès caméra : ", err);
      alert("Impossible d'accéder à la caméra. Autorisez la caméra dans votre navigateur.");
    });
  },
  methods: {
    enregistrerPresence() {
      if (!this.seanceId) return;

      axios.post(`/api/presences/${this.seanceId}`, { statut: "présent" })
        .then(res => {
          alert(res.data.message);
        })
        .catch(err => {
          console.error(err);
          alert("Erreur lors de l'enregistrement de la présence.");
        });
    }
  }
};
</script>
