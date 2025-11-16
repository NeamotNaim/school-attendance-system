<template>
  <div class="dashboard">
    <div class="dashboard-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Welcome back! Here's your attendance overview.</p>
      </div>
      <div class="date-badge">
        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <span>{{ currentDate }}</span>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading dashboard data...</p>
    </div>

    <div v-else class="dashboard-content">
      <!-- Statistics Cards -->
      <div class="stats-grid">
        <div class="stat-card stat-card-primary">
          <div class="stat-card-header">
            <div class="stat-icon stat-icon-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            </div>
            <div class="stat-card-info">
              <p class="stat-label">Total Students</p>
              <p class="stat-value">{{ stats.total_students || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-success">
          <div class="stat-card-header">
            <div class="stat-icon stat-icon-success">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
            </div>
            <div class="stat-card-info">
              <p class="stat-label">Present Today</p>
              <p class="stat-value">{{ stats.present || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-danger">
          <div class="stat-card-header">
            <div class="stat-icon stat-icon-danger">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
            </div>
            <div class="stat-card-info">
              <p class="stat-label">Absent Today</p>
              <p class="stat-value">{{ stats.absent || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-warning">
          <div class="stat-card-header">
            <div class="stat-icon stat-icon-warning">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
              </svg>
            </div>
            <div class="stat-card-info">
              <p class="stat-label">Late Today</p>
              <p class="stat-value">{{ stats.late || 0 }}</p>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-info">
          <div class="stat-card-header">
            <div class="stat-icon stat-icon-info">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
              </svg>
            </div>
            <div class="stat-card-info">
              <p class="stat-label">Attendance Rate</p>
              <p class="stat-value">{{ stats.attendance_percentage || 0 }}%</p>
            </div>
          </div>
        </div>

        <div class="stat-card stat-card-secondary">
          <div class="stat-card-header">
            <div class="stat-icon stat-icon-secondary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
            </div>
            <div class="stat-card-info">
              <p class="stat-label">Recorded</p>
              <p class="stat-value">{{ stats.recorded || 0 }} / {{ stats.total_students || 0 }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <div class="chart-card">
          <div class="chart-header">
            <div>
              <h2 class="chart-title">Monthly Attendance Chart</h2>
              <p class="chart-subtitle">Attendance overview for {{ currentMonth }}</p>
            </div>
          </div>
          <div class="chart-body">
            <MonthlyChart :month="currentMonth" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';
import MonthlyChart from '../components/MonthlyChart.vue';

const stats = ref({
  total_students: 0,
  present: 0,
  absent: 0,
  late: 0,
  recorded: 0,
  not_recorded: 0,
  attendance_percentage: 0,
});
const loading = ref(true);
const currentMonth = ref(new Date().toISOString().slice(0, 7));

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

const fetchStatistics = async () => {
  try {
    const response = await api.get('/attendances/statistics');
    const data = response.data.data;
    
    // Ensure all values have defaults
    stats.value = {
      total_students: data.total_students || 0,
      present: data.present || 0,
      absent: data.absent || 0,
      late: data.late || 0,
      recorded: data.recorded || 0,
      not_recorded: data.not_recorded || 0,
      attendance_percentage: data.attendance_percentage || 0,
    };
  } catch (error) {
    console.error('Failed to fetch statistics:', error);
    // Set default values on error
    stats.value = {
      total_students: 0,
      present: 0,
      absent: 0,
      late: 0,
      recorded: 0,
      not_recorded: 0,
      attendance_percentage: 0,
    };
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStatistics();
  setInterval(fetchStatistics, 30000);
});
</script>

<style scoped>
.dashboard {
  width: 100%;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.page-subtitle {
  color: var(--text-secondary);
  font-size: 0.9375rem;
}

.date-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  color: var(--text-primary);
  font-weight: 500;
  box-shadow: var(--shadow-sm);
}

.date-badge .icon {
  width: 18px;
  height: 18px;
  color: var(--primary);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  gap: 1rem;
}

.loading-container p {
  color: var(--text-secondary);
  font-size: 0.9375rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-lg);
}

.stat-card-header {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-icon-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-icon-success {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
}

.stat-icon-danger {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  color: white;
}

.stat-icon-warning {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  color: white;
}

.stat-icon-info {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
}

.stat-icon-secondary {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
  color: white;
}

.stat-card-info {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
}

.charts-section {
  display: grid;
  gap: 1.5rem;
}

.chart-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.chart-header {
  margin-bottom: 1.5rem;
}

.chart-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.chart-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.chart-body {
  min-height: 400px;
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-header {
    flex-direction: column;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
}
</style>
