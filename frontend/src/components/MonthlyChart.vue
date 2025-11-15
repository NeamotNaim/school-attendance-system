<template>
  <div class="chart-wrapper">
    <div v-if="loading" class="loading">Loading chart data...</div>
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import api from '../services/api';

Chart.register(...registerables);

const props = defineProps({
  month: {
    type: String,
    required: true,
  },
});

const chartCanvas = ref(null);
const loading = ref(false);
let chartInstance = null;

const fetchChartData = async () => {
  if (!props.month) return;

  loading.value = true;
  try {
    const response = await api.get('/attendances/report', {
      params: { month: props.month },
    });

    const report = response.data.data;
    const labels = report.students.map((s) => s.name);
    const presentData = report.students.map((s) => s.present_days);
    const absentData = report.students.map((s) => s.absent_days);
    const lateData = report.students.map((s) => s.late_days);

    if (chartInstance) {
      chartInstance.destroy();
    }

    if (chartCanvas.value) {
      chartInstance = new Chart(chartCanvas.value, {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Present',
              data: presentData,
              backgroundColor: '#10b981',
            },
            {
              label: 'Absent',
              data: absentData,
              backgroundColor: '#ef4444',
            },
            {
              label: 'Late',
              data: lateData,
              backgroundColor: '#f59e0b',
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    }
  } catch (error) {
    console.error('Failed to fetch chart data:', error);
  } finally {
    loading.value = false;
  }
};

watch(() => props.month, fetchChartData);
onMounted(fetchChartData);
</script>

<style scoped>
.chart-wrapper {
  position: relative;
  height: 400px;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}
</style>

