<template>
  <div class="chart-wrapper">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({
      responsive: true,
      plugins: {
        legend: {
          position: 'top'
        }
      }
    })
  }
})

const chartData = ref(props.data)
const chartOptions = ref(props.options)

watch(() => props.data, (newData) => {
  chartData.value = newData
})
watch(() => props.options, (newOptions) => {
  chartOptions.value = newOptions
})
</script>


<style scoped>
.chart-wrapper {
  width: 100%;
  height: 600px;        /* 🔥 augmente ici */
  max-width: 2000px;    /* 🔥 largeur max */
  margin: 0 auto;
}
</style>
