<template>
  <div class="report-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Weekly Report</h1>
        <p class="page-subtitle">View weekly attendance statistics</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="filters">
        <div class="form-group">
          <label>Start Date</label>
          <input v-model="startDate" type="date" @change="fetchReport" />
        </div>
        <div class="form-group">
          <label>Class</label>
          <select v-model="selectedClass" @change="fetchReport">
            <option value="">All Classes</option>
            <option v-for="cls in classes" :key="cls" :value="cls">{{ cls }}</option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading report...</p>
    </div>

    <div v-else-if="report" class="report-content">
      <div class="summary-cards">
        <div class="summary-card">
          <p class="summary-label">Total Students</p>
          <p class="summary-value">{{ report.summary?.total_students || 0 }}</p>
        </div>
        <div class="summary-card summary-present">
          <p class="summary-label">Average Present</p>
          <p class="summary-value">{{ report.summary?.average_present || 0 }}</p>
        </div>
        <div class="summary-card summary-absent">
          <p class="summary-label">Average Absent</p>
          <p class="summary-value">{{ report.summary?.average_absent || 0 }}</p>
        </div>
        <div class="summary-card summary-late">
          <p class="summary-label">Average Late</p>
          <p class="summary-value">{{ report.summary?.average_late || 0 }}</p>
        </div>
      </div>

      <div class="table-card">
        <h3>Daily Breakdown</h3>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Total</th>
                <th>Attendance %</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="day in report.daily_stats" :key="day.date">
                <td>{{ day.date }}</td>
                <td class="text-success">{{ day.present }}</td>
                <td class="text-danger">{{ day.absent }}</td>
                <td class="text-warning">{{ day.late }}</td>
                <td>{{ day.total }}</td>
                <td><strong>{{ day.attendance_percentage }}%</strong></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../../services/api';

const getStartOfWeek = () => {
  const date = new Date();
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? -6 : 1); // Adjust to Monday
  const monday = new Date(date.setDate(diff));
  return monday.toISOString().split('T')[0];
};

const startDate = ref(getStartOfWeek());
const selectedClass = ref('');
const classes = ref([]);
const loading = ref(false);
const report = ref(null);

const fetchReport = async () => {
  if (!startDate.value) return;
  
  loading.value = true;
  try {
    const response = await api.get('/reports/weekly', {
      params: { 
        start_date: startDate.value,
        class_id: selectedClass.value,
      },
    });
    report.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch report:', error);
  } finally {
    loading.value = false;
  }
};

const fetchClasses = async () => {
  try {
    const response = await api.get('/students', { params: { per_page: 1000 } });
    const allStudents = response.data.data;
    classes.value = [...new Set(allStudents.map(s => s.class))].sort();
  } catch (error) {
    console.error('Failed to fetch classes:', error);
  }
};

onMounted(() => {
  fetchClasses();
  fetchReport();
});
</script>

<style scoped>
.report-page {
  width: 100%;
}

.page-header {
  margin-bottom: 2rem;
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

.filters-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
}

.filters {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 200px;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  gap: 1rem;
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

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  border-left: 4px solid var(--primary);
}

.summary-present {
  border-left-color: var(--secondary);
}

.summary-absent {
  border-left-color: var(--danger);
}

.summary-late {
  border-left-color: var(--warning);
}

.summary-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.summary-value {
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.table-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.table-card h3 {
  margin-bottom: 1rem;
  color: var(--text-primary);
}

.table-container {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
}

th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
}

tbody tr {
  border-bottom: 1px solid var(--border-color);
}

tbody tr:hover {
  background: var(--bg-tertiary);
}

td {
  padding: 1rem;
  color: var(--text-primary);
  font-size: 0.9375rem;
}

.text-success {
  color: var(--secondary);
  font-weight: 600;
}

.text-danger {
  color: var(--danger);
  font-weight: 600;
}

.text-warning {
  color: var(--warning);
  font-weight: 600;
}
</style>

