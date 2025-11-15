<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    <div v-if="loading" class="loading">Loading...</div>
    <div v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Students</h3>
          <p class="stat-value">{{ stats.total_students }}</p>
        </div>
        <div class="stat-card present">
          <h3>Present</h3>
          <p class="stat-value">{{ stats.present }}</p>
        </div>
        <div class="stat-card absent">
          <h3>Absent</h3>
          <p class="stat-value">{{ stats.absent }}</p>
        </div>
        <div class="stat-card late">
          <h3>Late</h3>
          <p class="stat-value">{{ stats.late }}</p>
        </div>
        <div class="stat-card">
          <h3>Attendance %</h3>
          <p class="stat-value">{{ stats.attendance_percentage }}%</p>
        </div>
        <div class="stat-card">
          <h3>Recorded</h3>
          <p class="stat-value">{{ stats.recorded }} / {{ stats.total_students }}</p>
        </div>
      </div>
      <div class="chart-container">
        <h2>Monthly Attendance Chart</h2>
        <MonthlyChart :month="currentMonth" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';
import MonthlyChart from '../components/MonthlyChart.vue';

const stats = ref({
  total_students: 0,
  present: 0,
  absent: 0,
  late: 0,
  recorded: 0,
  attendance_percentage: 0,
});
const loading = ref(true);
const currentMonth = ref(new Date().toISOString().slice(0, 7));

const fetchStatistics = async () => {
  try {
    const response = await api.get('/attendances/statistics');
    stats.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStatistics();
  // Refresh every 30 seconds
  setInterval(fetchStatistics, 30000);
});
</script>

<style scoped>
.dashboard {
  padding: 2rem;
}

h1 {
  margin-bottom: 2rem;
  color: #333;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  border-left: 4px solid #667eea;
}

.stat-card.present {
  border-left-color: #10b981;
}

.stat-card.absent {
  border-left-color: #ef4444;
}

.stat-card.late {
  border-left-color: #f59e0b;
}

.stat-card h3 {
  margin: 0 0 0.5rem 0;
  color: #666;
  font-size: 0.9rem;
  font-weight: 500;
}

.stat-value {
  margin: 0;
  font-size: 2rem;
  font-weight: bold;
  color: #333;
}

.chart-container {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.chart-container h2 {
  margin-bottom: 1.5rem;
  color: #333;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}
</style>

