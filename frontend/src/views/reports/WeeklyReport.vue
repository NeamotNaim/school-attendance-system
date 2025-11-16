<template>
  <div class="report-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Weekly Report</h1>
        <p class="page-subtitle">View weekly attendance statistics</p>
      </div>
      <div class="export-buttons" v-if="report && report.daily_stats && report.daily_stats.length > 0">
        <button @click="handleExport('csv')" class="btn-export">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="12" y1="18" x2="12" y2="12"></line>
            <line x1="9" y1="15" x2="15" y2="15"></line>
          </svg>
          CSV
        </button>
        <button @click="handleExport('excel')" class="btn-export">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
          Excel
        </button>
        <button @click="handleExport('pdf')" class="btn-export">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          PDF
        </button>
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
import { useExport } from '../../composables/useExport';

const { exportToCSV, exportToExcel, exportToPDF } = useExport();

const getStartOfWeek = () => {
  const date = new Date();
  const day = date.getDay();
  const diff = date.getDate() - day + (day === 0 ? -6 : 1); // Adjust to Monday
  const monday = new Date(date.setDate(diff));
  return monday.toISOString().split('T')[0];
};

const startDate = ref(getStartOfWeek());
const selectedClass = ref('');
const selectedSection = ref('');
const classes = ref([]);
const sections = ref([]);
const loading = ref(false);
const report = ref(null);

const fetchReport = async () => {
  if (!startDate.value) return;
  
  loading.value = true;
  try {
    const params = { start_date: startDate.value };
    if (selectedClass.value) params.class = selectedClass.value;
    if (selectedSection.value) params.section = selectedSection.value;
    
    const response = await api.get('/reports/weekly', { params });
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

const handleExport = (format) => {
  if (!report.value || !report.value.daily_stats || report.value.daily_stats.length === 0) {
    return;
  }

  const exportData = report.value.daily_stats.map((item) => ({
    'Date': item.date || '',
    'Present': item.present || 0,
    'Absent': item.absent || 0,
    'Late': item.late || 0,
    'Total Students': item.total || 0,
    'Attendance %': item.attendance_percentage || 0,
  }));

  const endDate = new Date(startDate.value);
  endDate.setDate(endDate.getDate() + 6);
  const filename = `Weekly_Report_${startDate.value}_to_${endDate.toISOString().split('T')[0]}${selectedClass.value ? `_Class_${selectedClass.value}` : ''}${selectedSection.value ? `_Section_${selectedSection.value}` : ''}`;
  const title = `Weekly Attendance Report - ${startDate.value} to ${endDate.toISOString().split('T')[0]}`;

  if (format === 'csv') {
    exportToCSV(exportData, filename);
  } else if (format === 'excel') {
    exportToExcel(exportData, filename, title);
  } else if (format === 'pdf') {
    exportToPDF(exportData, filename, title);
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
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.export-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: white;
  color: var(--primary);
  border: 2px solid var(--primary);
  border-radius: 8px;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-export:hover {
  background: var(--primary);
  color: white;
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-export svg {
  width: 16px;
  height: 16px;
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

