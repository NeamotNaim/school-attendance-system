<template>
  <div class="attendance-recording">
    <div class="page-header">
      <div>
        <h1 class="page-title">Record Attendance</h1>
        <p class="page-subtitle">Mark attendance for students</p>
      </div>
    </div>

    <div class="controls-card">
      <div class="controls-header">
        <h3>Select Class & Date</h3>
      </div>
      <div class="controls">
        <div class="form-group">
          <label>
            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Date
          </label>
          <input v-model="selectedDate" type="date" @change="loadStudents" />
        </div>
        <div class="form-group">
          <label>
            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
              <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            Class <span class="required">*</span>
          </label>
          <select v-model="selectedClass" @change="watchClass">
            <option value="">Select Class</option>
            <option v-for="cls in classes" :key="cls" :value="cls">Class {{ cls }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>
            <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="9" y1="3" x2="9" y2="21"></line>
            </svg>
            Section <span class="required">*</span>
          </label>
          <select v-model="selectedSection" @change="loadStudents" :disabled="!selectedClass">
            <option value="">Select Section</option>
            <option v-for="section in sections" :key="section" :value="section">
              Section {{ section }}
            </option>
          </select>
        </div>
        <div class="form-group">
          <label style="opacity: 0;">Action</label>
          <button 
            @click="loadStudents" 
            :disabled="!selectedClass || !selectedSection"
            class="btn-load"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Load Students
          </button>
        </div>
      </div>
      <div class="bulk-actions">
        <button @click="markAllPresent" class="btn-bulk btn-bulk-present" :disabled="students.length === 0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          Mark All Present
        </button>
        <button @click="markAllAbsent" class="btn-bulk btn-bulk-absent" :disabled="students.length === 0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          Mark All Absent
        </button>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading students...</p>
    </div>

    <div v-else-if="students.length === 0" class="empty-state">
      <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
        <circle cx="9" cy="7" r="4"></circle>
        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
      </svg>
      <h3>No students found</h3>
      <p>Please select a class to load students.</p>
    </div>

    <div v-else>
      <div class="summary-card">
        <div class="summary-item">
          <span class="summary-label">Total Students</span>
          <span class="summary-value">{{ students.length }}</span>
        </div>
        <div class="summary-item summary-present">
          <span class="summary-label">Present</span>
          <span class="summary-value">{{ presentCount }}</span>
        </div>
        <div class="summary-item summary-absent">
          <span class="summary-label">Absent</span>
          <span class="summary-value">{{ absentCount }}</span>
        </div>
        <div class="summary-item summary-late">
          <span class="summary-label">Late</span>
          <span class="summary-value">{{ lateCount }}</span>
        </div>
        <div class="summary-item summary-percentage">
          <span class="summary-label">Attendance Rate</span>
          <span class="summary-value">{{ attendancePercentage }}%</span>
        </div>
      </div>

      <div class="table-card">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Note</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(student, index) in students" :key="student.id">
                <td class="row-number">{{ index + 1 }}</td>
                <td class="font-mono">{{ student.student_id }}</td>
                <td class="font-semibold">{{ student.name }}</td>
                <td>
                  <div class="status-buttons">
                    <button
                      @click="attendanceData[student.id].status = 'present'"
                      :class="['status-btn', 'status-btn-present', { active: attendanceData[student.id].status === 'present' }]"
                      title="Mark Present"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                      </svg>
                      <span>P</span>
                    </button>
                    <button
                      @click="attendanceData[student.id].status = 'absent'"
                      :class="['status-btn', 'status-btn-absent', { active: attendanceData[student.id].status === 'absent' }]"
                      title="Mark Absent"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                      </svg>
                      <span>A</span>
                    </button>
                    <button
                      @click="attendanceData[student.id].status = 'late'"
                      :class="['status-btn', 'status-btn-late', { active: attendanceData[student.id].status === 'late' }]"
                      title="Mark Late"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                      </svg>
                      <span>L</span>
                    </button>
                  </div>
                </td>
                <td>
                  <input
                    v-model="attendanceData[student.id].note"
                    type="text"
                    placeholder="Optional note..."
                    class="note-input"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="action-bar">
        <div class="action-info">
          <p>Ready to save attendance for <strong>{{ selectedDate }}</strong></p>
        </div>
        <button @click="saveAttendance" :disabled="saving || students.length === 0" class="btn-save">
          <svg v-if="!saving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
          </svg>
          <span v-if="!saving">Save Attendance</span>
          <span v-else class="saving-text">
            <span class="spinner-small"></span>
            Saving...
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';

const students = ref([]);
const classes = ref([]);
const sections = ref([]);
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedClass = ref('');
const selectedSection = ref('');
const loading = ref(false);
const saving = ref(false);
const attendanceData = ref({});
const hasExistingAttendance = ref(false);

const presentCount = computed(() => {
  return Object.values(attendanceData.value).filter(a => a.status === 'present').length;
});

const absentCount = computed(() => {
  return Object.values(attendanceData.value).filter(a => a.status === 'absent').length;
});

const lateCount = computed(() => {
  return Object.values(attendanceData.value).filter(a => a.status === 'late').length;
});

const attendancePercentage = computed(() => {
  const total = students.value.length;
  if (total === 0) return 0;
  const recorded = presentCount.value + absentCount.value + lateCount.value;
  return total > 0 ? Math.round((recorded / total) * 100) : 0;
});

const loadStudents = async () => {
  if (!selectedClass.value) {
    students.value = [];
    return;
  }

  loading.value = true;
  try {
    const params = { class: selectedClass.value };
    if (selectedSection.value) params.section = selectedSection.value;

    const response = await api.get('/students', { params: { ...params, per_page: 1000 } });
    students.value = response.data.data;

    // Initialize attendance data
    attendanceData.value = {};
    students.value.forEach((student) => {
      attendanceData.value[student.id] = {
        student_id: student.id,
        status: 'present',
        note: '',
      };
    });

    // Load existing attendance if any
    await loadExistingAttendance();

    // Update sections list
    if (!selectedSection.value) {
      sections.value = [
        ...new Set(students.value.map((s) => s.section)),
      ].sort();
    }
  } catch (error) {
    console.error('Failed to load students:', error);
    alert('Failed to load students. Please try again.');
  } finally {
    loading.value = false;
  }
};

const loadExistingAttendance = async () => {
  try {
    const response = await api.get(`/attendances/daily/${selectedDate.value}`, {
      params: {
        class: selectedClass.value,
        section: selectedSection.value || undefined,
      },
    });
    
    if (response.data.data && response.data.data.length > 0) {
      response.data.data.forEach((record) => {
        if (attendanceData.value[record.student_id]) {
          attendanceData.value[record.student_id].status = record.status;
          attendanceData.value[record.student_id].note = record.note || '';
        }
      });
    }
  } catch (error) {
    // No existing attendance, that's fine
    console.log('No existing attendance found');
  }
};

const fetchClasses = async () => {
  try {
    // Try to get from classes API first
    const classResponse = await api.get('/classes');
    if (classResponse.data.data && classResponse.data.data.length > 0) {
      classes.value = classResponse.data.data
        .filter(c => c.is_active)
        .map(c => c.name)
        .sort();
      return;
    }
  } catch (error) {
    console.log('Classes API not available, falling back to students');
  }

  // Fallback to getting classes from students
  try {
    const response = await api.get('/students', { params: { per_page: 1000 } });
    const allStudents = response.data.data;
    classes.value = [...new Set(allStudents.map((s) => s.class))].sort();
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
    // Try sections API first
    try {
      const response = await api.get('/sections');
      if (response.data.data && response.data.data.length > 0) {
        // Filter sections by class name
        const classSections = response.data.data.filter(s => {
          return s.is_active && s.class && s.class.name === selectedClass.value;
        });
        
        if (classSections.length > 0) {
          sections.value = classSections.map(s => s.name).sort();
          return;
        }
      }
    } catch (error) {
      console.log('Sections API not available, using fallback');
    }

    // Fallback: get sections from students
    const response = await api.get('/students', {
      params: { class: selectedClass.value, per_page: 1000 },
    });
    
    if (response.data.data && response.data.data.length > 0) {
      sections.value = [...new Set(response.data.data.map((s) => s.section))].sort();
    } else {
      sections.value = [];
    }
  } catch (error) {
    console.error('Failed to fetch sections:', error);
    sections.value = [];
  }
};

const markAllPresent = () => {
  Object.keys(attendanceData.value).forEach((key) => {
    attendanceData.value[key].status = 'present';
  });
  updateAttendancePercentage();
};

const markAllAbsent = () => {
  Object.keys(attendanceData.value).forEach((key) => {
    attendanceData.value[key].status = 'absent';
  });
  updateAttendancePercentage();
};

const updateAttendancePercentage = () => {
  // Reactive, updates automatically
};

const saveAttendance = async () => {
  if (students.value.length === 0) {
    alert('No students to save');
    return;
  }

  saving.value = true;
  try {
    const studentsData = Object.values(attendanceData.value).map((data) => ({
      student_id: data.student_id,
      status: data.status,
      note: data.note || null,
    }));

    await api.post('/attendances/bulk', {
      date: selectedDate.value,
      students: studentsData,
    });

    alert('Attendance saved successfully!');
    loadStudents();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save attendance');
  } finally {
    saving.value = false;
  }
};

// Watch for class changes to load sections
const watchClass = async () => {
  if (selectedClass.value) {
    selectedSection.value = '';
    students.value = [];
    await fetchSections();
  } else {
    sections.value = [];
    selectedSection.value = '';
    students.value = [];
  }
};

// Add quick toggle for individual student
const toggleStatus = (studentId) => {
  const current = attendanceData.value[studentId].status;
  const statuses = ['present', 'absent', 'late'];
  const currentIndex = statuses.indexOf(current);
  const nextIndex = (currentIndex + 1) % statuses.length;
  attendanceData.value[studentId].status = statuses[nextIndex];
};

onMounted(() => {
  fetchClasses();
});
</script>

<style scoped>
.attendance-recording {
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

.controls-card {
  background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.controls-header {
  margin-bottom: 1.5rem;
}

.controls-header h3 {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.controls {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.label-icon {
  width: 18px;
  height: 18px;
  color: var(--primary);
}

.form-group input,
.form-group select {
  padding: 0.875rem;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  outline: none;
}

.required {
  color: var(--danger);
}

.btn-load {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
  width: 100%;
}

.btn-load:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
}

.btn-load:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-load svg {
  width: 18px;
  height: 18px;
}

.form-group select:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.bulk-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-bulk {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-bulk svg {
  width: 18px;
  height: 18px;
}

.btn-bulk-present {
  background: linear-gradient(135deg, var(--secondary) 0%, #059669 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-bulk-present:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.btn-bulk-absent {
  background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-bulk-absent:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
}

.btn-bulk:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.loading-container,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  gap: 1rem;
  background: var(--bg-secondary);
  border-radius: 12px;
  border: 1px solid var(--border-color);
}

.loading-container p,
.empty-state p {
  color: var(--text-secondary);
}

.empty-icon {
  width: 64px;
  height: 64px;
  color: var(--text-muted);
}

.empty-state h3 {
  color: var(--text-primary);
  margin: 0;
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

.summary-card {
  background: var(--bg-secondary);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1.5rem;
}

.summary-item {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 1.5rem;
  background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--bg-secondary) 100%);
  border-radius: 12px;
  border-left: 5px solid var(--primary);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.summary-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

.summary-percentage {
  border-left-color: var(--info);
}

.summary-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.summary-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
}

.table-card {
  background: var(--bg-secondary);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  overflow: hidden;
  margin-bottom: 2rem;
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
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

tbody tr {
  border-bottom: 1px solid var(--border-color);
  transition: background 0.2s ease;
}

tbody tr:hover {
  background: var(--bg-tertiary);
}

td {
  padding: 1rem;
  color: var(--text-primary);
  font-size: 0.9375rem;
}

.row-number {
  color: var(--text-muted);
  font-weight: 600;
  text-align: center;
}

.font-mono {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: var(--primary);
}

.font-semibold {
  font-weight: 600;
  color: var(--text-primary);
}

.status-buttons {
  display: flex;
  gap: 0.5rem;
}

.status-btn {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.5rem 0.75rem;
  border: 2px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  background: var(--bg-secondary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 60px;
  justify-content: center;
}

.status-btn svg {
  width: 16px;
  height: 16px;
}

.status-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.status-btn-present {
  border-color: rgba(16, 185, 129, 0.3);
}

.status-btn-present.active {
  background: linear-gradient(135deg, var(--secondary) 0%, #059669 100%);
  border-color: var(--secondary);
  color: white;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.status-btn-absent {
  border-color: rgba(239, 68, 68, 0.3);
}

.status-btn-absent.active {
  background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
  border-color: var(--danger);
  color: white;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.status-btn-late {
  border-color: rgba(245, 158, 11, 0.3);
}

.status-btn-late.active {
  background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
  border-color: var(--warning);
  color: white;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.note-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  font-size: 0.875rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.note-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem;
  background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  flex-wrap: wrap;
  gap: 1.5rem;
}

.action-info p {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin: 0;
}

.action-info strong {
  color: var(--text-primary);
  font-weight: 600;
}

.btn-save {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2.5rem;
  background: linear-gradient(135deg, var(--secondary) 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.btn-save svg {
  width: 20px;
  height: 20px;
}

.saving-text {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@media (max-width: 768px) {
  .controls {
    grid-template-columns: 1fr;
  }
  
  .summary-card {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .action-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .btn-save {
    width: 100%;
    justify-content: center;
  }
}
</style>
