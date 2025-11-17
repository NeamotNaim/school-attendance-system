<template>
  <div class="chart-wrapper">
    <div v-if="loading" class="chart-loading">
      <div class="spinner"></div>
      <p>Loading chart data...</p>
    </div>
    <div v-else-if="noData" class="chart-no-data">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="9" y1="9" x2="15" y2="15"></line>
        <line x1="15" y1="9" x2="9" y2="15"></line>
      </svg>
      <p>No attendance data available for this month</p>
      <small>Record some attendance to see the chart</small>
    </div>
    <canvas ref="chartCanvas" v-show="!loading && !noData"></canvas>
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
const noData = ref(false);
let chartInstance = null;

const fetchChartData = async () => {
  if (!props.month) {
    console.log('No month provided to chart');
    return;
  }

  loading.value = true;
  try {
    console.log('Fetching chart data for month:', props.month);
    
    // Fetch class comparison data
    const response = await api.get('/reports/class-comparison', {
      params: { month: props.month },
    });

    console.log('Full API response:', response.data);
    
    const classData = response.data.data;
    
    console.log('Chart data received:', classData);
    console.log('Data type:', typeof classData);
    console.log('Is array:', Array.isArray(classData));
    console.log('Data length:', classData?.length);
    
    if (!classData || !Array.isArray(classData) || classData.length === 0) {
      console.warn('No class data available for chart');
      noData.value = true;
      loading.value = false;
      return;
    }
    
    noData.value = false;
    
    // Group data by class and convert to percentages
    const labels = classData.map((c) => `Class ${c.class_name}`);
    
    // Calculate percentages for present and absent (rounded to 2 decimal places)
    const presentPercentages = classData.map((c) => {
      const totalDays = c.total_days || 1;
      const totalStudents = c.total_students || 1;
      const totalPossibleDays = totalDays * totalStudents;
      const percentage = totalPossibleDays > 0 ? ((c.total_present || 0) / totalPossibleDays * 100) : 0;
      return parseFloat(percentage.toFixed(2));
    });
    
    const absentPercentages = classData.map((c) => {
      const totalDays = c.total_days || 1;
      const totalStudents = c.total_students || 1;
      const totalPossibleDays = totalDays * totalStudents;
      const percentage = totalPossibleDays > 0 ? ((c.total_absent || 0) / totalPossibleDays * 100) : 0;
      return parseFloat(percentage.toFixed(2));
    });
    
    const attendancePercentages = classData.map((c) => {
      const percentage = c.average_attendance_percentage || 0;
      return parseFloat(percentage.toFixed(2));
    });
    
    console.log('Chart labels:', labels);
    console.log('Present %:', presentPercentages);
    console.log('Absent %:', absentPercentages);
    console.log('Attendance %:', attendancePercentages);

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
              label: 'Present %',
              data: presentPercentages,
              backgroundColor: 'rgba(16, 185, 129, 0.8)',
              borderColor: '#10b981',
              borderWidth: 2,
              borderRadius: 6,
            },
            {
              label: 'Absent %',
              data: absentPercentages,
              backgroundColor: 'rgba(239, 68, 68, 0.8)',
              borderColor: '#ef4444',
              borderWidth: 2,
              borderRadius: 6,
            },
            {
              label: 'Attendance %',
              data: attendancePercentages,
              type: 'line',
              backgroundColor: 'rgba(79, 70, 229, 0.1)',
              borderColor: '#4f46e5',
              borderWidth: 3,
              pointBackgroundColor: '#4f46e5',
              pointBorderColor: '#fff',
              pointBorderWidth: 2,
              pointRadius: 5,
              pointHoverRadius: 7,
              yAxisID: 'y1',
              tension: 0.4,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                  size: 13,
                  weight: '600',
                },
                color: '#1e293b',
              },
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.9)',
              padding: 16,
              titleFont: {
                size: 15,
                weight: '700',
              },
              bodyFont: {
                size: 14,
              },
              borderColor: 'rgba(255, 255, 255, 0.2)',
              borderWidth: 1,
              cornerRadius: 8,
              callbacks: {
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed.y !== null) {
                    label += context.parsed.y.toFixed(2) + '%';
                  }
                  return label;
                }
              }
            },
            title: {
              display: true,
              text: 'Class-wise Attendance Comparison',
              font: {
                size: 16,
                weight: '700',
              },
              color: '#1e293b',
              padding: {
                bottom: 20,
              },
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
                  size: 12,
                  weight: '500',
                },
              },
            },
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              beginAtZero: true,
              max: 100,
              title: {
                display: true,
                text: 'Attendance %',
                color: '#64748b',
                font: {
                  size: 12,
                  weight: '600',
                },
              },
              grid: {
                color: 'rgba(0, 0, 0, 0.05)',
              },
              ticks: {
                color: '#64748b',
                font: {
                  size: 11,
                },
                callback: function(value) {
                  return value + '%';
                },
                stepSize: 10,
              },
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              beginAtZero: true,
              max: 100,
              title: {
                display: true,
                text: 'Attendance %',
                color: '#4f46e5',
                font: {
                  size: 12,
                  weight: '600',
                },
              },
              grid: {
                drawOnChartArea: false,
              },
              ticks: {
                color: '#4f46e5',
                font: {
                  size: 11,
                },
                callback: function(value) {
                  return value + '%';
                },
              },
            },
          },
        },
      });
    }
  } catch (error) {
    console.error('Failed to fetch chart data:', error);
    console.error('Error details:', error.response?.data || error.message);
    noData.value = true;
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

.chart-no-data {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 0.75rem;
  color: var(--text-secondary);
  text-align: center;
  padding: 2rem;
}

.chart-no-data svg {
  width: 48px;
  height: 48px;
  color: var(--text-muted);
  opacity: 0.5;
}

.chart-no-data p {
  font-size: 1rem;
  font-weight: 500;
  margin: 0;
  color: var(--text-primary);
}

.chart-no-data small {
  font-size: 0.875rem;
  color: var(--text-secondary);
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
