<template>
  <div class="student-list">
    <div class="header">
      <h1>Students</h1>
      <button @click="showAddModal = true" class="btn-primary">Add Student</button>
    </div>

    <div class="filters">
      <input
        v-model="search"
        type="text"
        placeholder="Search by name or ID..."
        @input="fetchStudents"
      />
      <select v-model="selectedClass" @change="fetchStudents">
        <option value="">All Classes</option>
        <option v-for="cls in classes" :key="cls" :value="cls">{{ cls }}</option>
      </select>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
    <div v-else>
      <table>
        <thead>
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Class</th>
            <th>Section</th>
            <th>Photo</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.id">
            <td>{{ student.student_id }}</td>
            <td>{{ student.name }}</td>
            <td>{{ student.class }}</td>
            <td>{{ student.section }}</td>
            <td>
              <img
                v-if="student.photo"
                :src="student.photo"
                alt="Student photo"
                class="student-photo"
              />
              <span v-else>No photo</span>
            </td>
            <td>
              <button @click="editStudent(student)" class="btn-edit">Edit</button>
              <button @click="deleteStudent(student.id)" class="btn-delete">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="pagination">
        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1">
          Previous
        </button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="changePage(currentPage + 1)" :disabled="currentPage === totalPages">
          Next
        </button>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || editingStudent" class="modal">
      <div class="modal-content">
        <h2>{{ editingStudent ? 'Edit' : 'Add' }} Student</h2>
        <form @submit.prevent="saveStudent">
          <div class="form-group">
            <label>Student ID</label>
            <input v-model="form.student_id" required />
          </div>
          <div class="form-group">
            <label>Name</label>
            <input v-model="form.name" required />
          </div>
          <div class="form-group">
            <label>Class</label>
            <input v-model="form.class" required />
          </div>
          <div class="form-group">
            <label>Section</label>
            <input v-model="form.section" required />
          </div>
          <div class="form-group">
            <label>Photo</label>
            <input type="file" @change="handleFileChange" accept="image/*" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';

const students = ref([]);
const classes = ref([]);
const loading = ref(false);
const search = ref('');
const selectedClass = ref('');
const currentPage = ref(1);
const perPage = ref(15);
const totalPages = ref(1);
const showAddModal = ref(false);
const editingStudent = ref(null);
const form = ref({
  student_id: '',
  name: '',
  class: '',
  section: '',
  photo: null,
});

const fetchStudents = async () => {
  loading.value = true;
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
    };
    if (search.value) params.search = search.value;
    if (selectedClass.value) params.class = selectedClass.value;

    const response = await api.get('/students', { params });
    students.value = response.data.data;
    totalPages.value = response.data.meta.last_page;
  } catch (error) {
    console.error('Failed to fetch students:', error);
  } finally {
    loading.value = false;
  }
};

const fetchClasses = async () => {
  try {
    const response = await api.get('/students');
    const allStudents = response.data.data;
    classes.value = [...new Set(allStudents.map(s => s.class))].sort();
  } catch (error) {
    console.error('Failed to fetch classes:', error);
  }
};

const changePage = (page) => {
  currentPage.value = page;
  fetchStudents();
};

const editStudent = (student) => {
  editingStudent.value = student;
  form.value = { ...student };
};

const deleteStudent = async (id) => {
  if (!confirm('Are you sure you want to delete this student?')) return;
  
  try {
    await api.delete(`/students/${id}`);
    fetchStudents();
  } catch (error) {
    alert('Failed to delete student');
  }
};

const handleFileChange = (event) => {
  form.value.photo = event.target.files[0];
};

const saveStudent = async () => {
  const formData = new FormData();
  formData.append('student_id', form.value.student_id);
  formData.append('name', form.value.name);
  formData.append('class', form.value.class);
  formData.append('section', form.value.section);
  if (form.value.photo) {
    formData.append('photo', form.value.photo);
  }

  try {
    if (editingStudent.value) {
      await api.put(`/students/${editingStudent.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      await api.post('/students', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }
    closeModal();
    fetchStudents();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save student');
  }
};

const closeModal = () => {
  showAddModal.value = false;
  editingStudent.value = null;
  form.value = {
    student_id: '',
    name: '',
    class: '',
    section: '',
    photo: null,
  };
};

onMounted(() => {
  fetchStudents();
  fetchClasses();
});
</script>

<style scoped>
.student-list {
  padding: 2rem;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

h1 {
  margin: 0;
  color: #333;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
}

.filters input,
.filters select {
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
}

.filters input {
  flex: 1;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

.student-photo {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 5px;
}

.btn-primary,
.btn-edit,
.btn-delete,
.btn-secondary {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-edit {
  background: #10b981;
  color: white;
  margin-right: 0.5rem;
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background: white;
  border-radius: 5px;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  box-sizing: border-box;
}

.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}
</style>

