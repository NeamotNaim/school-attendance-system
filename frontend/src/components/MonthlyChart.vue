<template>
  <div class="chart-wrapper">
    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <p>Loading chart data...</p>
    </div>
    <canvas ref="chartCanvas" v-show="!loading"></canvas>
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
              backgroundColor: 'rgba(16, 185, 129, 0.8)',
              borderColor: '#10b981',
              borderWidth: 1,
            },
            {
              label: 'Absent',
              data: absentData,
              backgroundColor: 'rgba(239, 68, 68, 0.8)',
              borderColor: '#ef4444',
              borderWidth: 1,
            },
            {
              label: 'Late',
              data: lateData,
              backgroundColor: 'rgba(245, 158, 11, 0.8)',
              borderColor: '#f59e0b',
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                  size: 12,
                  weight: '500',
                },
                color: '#1e293b',
              },
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              padding: 12,
              titleFont: {
                size: 14,
                weight: '600',
              },
              bodyFont: {
                size: 13,
              },
              borderColor: 'rgba(255, 255, 255, 0.1)',
              borderWidth: 1,
            },
          },
          scales: {
            x: {
              grid: {
                display: false,
              },
              ticks: {
                color: '#64748b',
                font: {
                  size: 11,
                },
              },
            },
            y: {
              beginAtZero: true,
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
              },
              ticks: {
                color: '#64748b',
                font: {
                  size: 11,
                },
              },
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
  width: 100%;
}

.chart-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 1rem;
  color: var(--text-secondary);
}

.chart-loading p {
  font-size: 0.9375rem;
  margin: 0;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
