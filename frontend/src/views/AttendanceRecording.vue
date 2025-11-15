<template>
  <div class="attendance-recording">
    <h1>Record Attendance</h1>

    <div class="controls">
      <div class="form-group">
        <label>Date</label>
        <input v-model="selectedDate" type="date" @change="loadStudents" />
      </div>
      <div class="form-group">
        <label>Class</label>
        <select v-model="selectedClass" @change="loadStudents">
          <option value="">Select Class</option>
          <option v-for="cls in classes" :key="cls" :value="cls">{{ cls }}</option>
        </select>
      </div>
      <div class="form-group">
        <label>Section</label>
        <select v-model="selectedSection" @change="loadStudents">
          <option value="">All Sections</option>
          <option v-for="section in sections" :key="section" :value="section">
            {{ section }}
          </option>
        </select>
      </div>
      <button @click="markAllPresent" class="btn-bulk">Mark All Present</button>
      <button @click="markAllAbsent" class="btn-bulk">Mark All Absent</button>
    </div>

    <div v-if="loading" class="loading">Loading students...</div>
    <div v-else-if="students.length === 0" class="no-students">
      No students found. Please select a class.
    </div>
    <div v-else>
      <div class="attendance-summary">
        <p>
          <strong>Total:</strong> {{ students.length }} |
          <strong>Present:</strong> {{ presentCount }} |
          <strong>Absent:</strong> {{ absentCount }} |
          <strong>Late:</strong> {{ lateCount }} |
          <strong>Attendance:</strong> {{ attendancePercentage }}%
        </p>
      </div>

      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Note</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.id">
            <td>{{ student.student_id }}</td>
            <td>{{ student.name }}</td>
            <td>
              <select
                v-model="attendanceData[student.id].status"
                @change="updateAttendancePercentage"
              >
                <option value="present">Present</option>
                <option value="absent">Absent</option>
                <option value="late">Late</option>
              </select>
            </td>
            <td>
              <input
                v-model="attendanceData[student.id].note"
                type="text"
                placeholder="Optional note"
              />
            </td>
          </tr>
        </tbody>
      </table>

      <div class="actions">
        <button @click="saveAttendance" :disabled="saving" class="btn-save">
          {{ saving ? 'Saving...' : 'Save Attendance' }}
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
    students.value.forEach((student) => {
      attendanceData.value[student.id] = {
        student_id: student.id,
        status: 'present',
        note: '',
      };
    });

    // Update sections list
    sections.value = [
      ...new Set(students.value.map((s) => s.section)),
    ].sort();
  } catch (error) {
    console.error('Failed to load students:', error);
  } finally {
    loading.value = false;
  }
};

const fetchClasses = async () => {
  try {
    const response = await api.get('/students', { params: { per_page: 1000 } });
    const allStudents = response.data.data;
    classes.value = [...new Set(allStudents.map((s) => s.class))].sort();
  } catch (error) {
    console.error('Failed to fetch classes:', error);
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
  // This is reactive, so it will update automatically
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
    loadStudents(); // Reload to get updated data
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save attendance');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchClasses();
});
</script>

<style scoped>
.attendance-recording {
  padding: 2rem;
}

h1 {
  margin-bottom: 2rem;
  color: #333;
}

.controls {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 0.5rem;
  color: #555;
  font-weight: 500;
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
}

.btn-bulk {
  padding: 0.75rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
  align-self: flex-end;
}

.btn-bulk:hover {
  background: #5568d3;
}

.attendance-summary {
  background: #f0f9ff;
  padding: 1rem;
  border-radius: 5px;
  margin-bottom: 1rem;
}

.attendance-summary p {
  margin: 0;
  color: #333;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
}

thead {
  background: #667eea;
  color: white;
}

th,
td {
  padding: 1rem;
  text-align: left;
}

tbody tr {
  border-bottom: 1px solid #eee;
}

tbody tr:hover {
  background: #f5f5f5;
}

tbody select,
tbody input {
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 0.9rem;
}

tbody input {
  width: 100%;
}

.actions {
  display: flex;
  justify-content: center;
}

.btn-save {
  padding: 1rem 2rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1.1rem;
  cursor: pointer;
  font-weight: 600;
}

.btn-save:hover:not(:disabled) {
  background: #059669;
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading,
.no-students {
  text-align: center;
  padding: 2rem;
  color: #666;
}
</style>

