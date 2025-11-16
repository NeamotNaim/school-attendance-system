<template>
  <div class="report-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Monthly Report</h1>
        <p class="page-subtitle">View monthly attendance statistics</p>
      </div>
    </div>

    <div class="filters-card">
      <div class="filters">
        <div class="form-group">
          <label>Month</label>
          <input v-model="selectedMonth" type="month" @change="fetchReport" />
        </div>
        <div class="form-group">
          <label>Class</label>
          <select v-model="selectedClass" @change="onClassChange">
            <option value="">All Classes</option>
            <option v-for="cls in classes" :key="cls" :value="cls">Class {{ cls }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Section</label>
          <select v-model="selectedSection" @change="fetchReport" :disabled="!selectedClass">
            <option value="">All Sections</option>
            <option v-for="section in sections" :key="section" :value="section">Section {{ section }}</option>
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
          <p class="summary-label">Total Present Days</p>
          <p class="summary-value">{{ report.summary?.total_present || 0 }}</p>
        </div>
        <div class="summary-card summary-absent">
          <p class="summary-label">Total Absent Days</p>
          <p class="summary-value">{{ report.summary?.total_absent || 0 }}</p>
        </div>
        <div class="summary-card summary-late">
          <p class="summary-label">Total Late Days</p>
          <p class="summary-value">{{ report.summary?.total_late || 0 }}</p>
        </div>
      </div>

      <div class="table-card">
        <h3>Student Attendance Details</h3>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Class</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Late</th>
                <th>Recorded Days</th>
                <th>School Days</th>
                <th>Attendance %</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="student in report.students" :key="student.student_id">
                <td class="font-mono">{{ student.student_id }}</td>
                <td>{{ student.name }}</td>
                <td><span class="badge badge-primary">{{ student.class }}</span></td>
                <td class="text-success">{{ student.present_days }}</td>
                <td class="text-danger">{{ student.absent_days }}</td>
                <td class="text-warning">{{ student.late_days }}</td>
                <td>{{ student.recorded_days || 0 }}</td>
                <td>{{ student.total_days }}</td>
                <td>
                  <strong :class="getAttendanceClass(student.attendance_percentage)">
                    {{ student.attendance_percentage }}%
                  </strong>
                </td>
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

const selectedMonth = ref(new Date().toISOString().slice(0, 7));
const selectedClass = ref('');
const selectedSection = ref('');
const classes = ref([]);
const sections = ref([]);
const loading = ref(false);
const report = ref(null);

const fetchReport = async () => {
  if (!selectedMonth.value) return;
  
  loading.value = true;
  try {
    const params = { month: selectedMonth.value };
    if (selectedClass.value) params.class = selectedClass.value;
    if (selectedSection.value) params.section = selectedSection.value;
    
    const response = await api.get('/reports/monthly', { params });
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

const fetchSections = async () => {
  if (!selectedClass.value) {
    sections.value = [];
    return;
  }
  
  try {
    const response = await api.get('/students', {
      params: { class: selectedClass.value, per_page: 1000 },
    });
    sections.value = [...new Set(response.data.data.map(s => s.section))].sort();
  } catch (error) {
    console.error('Failed to fetch sections:', error);
  }
};

const onClassChange = () => {
  selectedSection.value = '';
  fetchSections();
  fetchReport();
};

const getAttendanceClass = (percentage) => {
  if (percentage >= 90) return 'text-success';
  if (percentage >= 75) return 'text-warning';
  return 'text-danger';
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

.font-mono {
  font-family: 'Courier New', monospace;
  color: var(--primary);
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.badge-primary {
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary);
}

.text-success {
  color: var(--secondary);
}

.text-danger {
  color: var(--danger);
}

.text-warning {
  color: var(--warning);
}
</style>

