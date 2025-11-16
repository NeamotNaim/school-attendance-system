<template>
  <div class="student-list">
    <div class="page-header">
      <div>
        <h1 class="page-title">Students</h1>
        <p class="page-subtitle">Manage student information and records</p>
      </div>
      <button @click="showAddModal = true" class="btn-primary">
        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add Student</span>
      </button>
    </div>

    <div class="filters-card">
      <div class="filters-header">
        <h3>Filters</h3>
        <button v-if="hasActiveFilters" @click="clearFilters" class="btn-clear-filters">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
          Clear Filters
        </button>
      </div>
      <div class="filters">
        <div class="search-wrapper">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Search by name or student ID..."
            @input="fetchStudents"
            class="search-input"
          />
        </div>
        <div class="filter-group">
          <label>Class</label>
          <select v-model="selectedClass" @change="onClassFilterChange" class="filter-select">
            <option value="">All Classes</option>
            <option v-for="cls in availableClasses" :key="cls.id" :value="cls.name">
              {{ cls.name }}
            </option>
          </select>
        </div>
        <div class="filter-group">
          <label>Section</label>
          <select v-model="selectedSection" @change="fetchStudents" class="filter-select" :disabled="!selectedClass">
            <option value="">All Sections</option>
            <option v-for="section in availableSections" :key="section.id" :value="section.name">
              {{ section.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="filter-summary" v-if="students.length > 0">
        <p>Showing <strong>{{ students.length }}</strong> of <strong>{{ totalStudents }}</strong> students</p>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading students...</p>
    </div>

    <div v-else class="content-card">
      <div v-if="students.length === 0" class="empty-state">
        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
          <circle cx="9" cy="7" r="4"></circle>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
        </svg>
        <h3>No students found</h3>
        <p>Try adjusting your search or add a new student.</p>
      </div>

      <div v-else>
        <div class="table-container">
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
                <td class="font-mono">{{ student.student_id }}</td>
                <td class="font-semibold">{{ student.name }}</td>
                <td>
                  <span class="badge badge-primary">{{ student.class }}</span>
                </td>
                <td>
                  <span class="badge badge-secondary">{{ student.section }}</span>
                </td>
                <td>
                  <div class="photo-wrapper">
                    <img
                      v-if="student.photo"
                      :src="student.photo"
                      alt="Student photo"
                      class="student-photo"
                    />
                    <div v-else class="photo-placeholder">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                      </svg>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="action-buttons">
                    <button @click="editStudent(student)" class="btn-action btn-edit" title="Edit">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                      </svg>
                    </button>
                    <button @click="deleteStudent(student.id)" class="btn-action btn-delete" title="Delete">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pagination-wrapper">
          <div class="pagination-info">
            Showing page {{ currentPage }} of {{ totalPages }}
          </div>
          <div class="pagination">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="btn-pagination"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
              Previous
            </button>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="btn-pagination"
            >
              Next
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || editingStudent" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editingStudent ? 'Edit' : 'Add' }} Student</h2>
          <button @click="closeModal" class="btn-close">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        <form @submit.prevent="saveStudent" class="modal-form">
          <div class="form-group">
            <label>Student ID <span class="required">*</span></label>
            <input v-model="form.student_id" required placeholder="e.g., STU001" />
          </div>
          <div class="form-group">
            <label>Full Name <span class="required">*</span></label>
            <input v-model="form.name" required placeholder="Enter student name" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Class <span class="required">*</span></label>
              <select v-model="form.class" @change="onClassChange" required>
                <option value="">Select Class</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.name">
                  {{ cls.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>Section <span class="required">*</span></label>
              <select v-model="form.section" required :disabled="!form.class">
                <option value="">Select Section</option>
                <option v-for="section in sections" :key="section.id" :value="section.name">
                  {{ section.name }}
                </option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Photo</label>
            <input type="file" @change="handleFileChange" accept="image/*" class="file-input" />
          </div>
          <div class="modal-actions">
            <button type="button" @click="closeModal" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save Student</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../services/api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();

const students = ref([]);
const classes = ref([]);
const sections = ref([]);
const allSections = ref([]);
const availableClasses = ref([]);
const availableSections = ref([]);
const loading = ref(false);
const search = ref('');
const selectedClass = ref('');
const selectedSection = ref('');
const currentPage = ref(1);
const perPage = ref(15);
const totalPages = ref(1);
const totalStudents = ref(0);
const showAddModal = ref(false);
const editingStudent = ref(null);
const form = ref({
  student_id: '',
  name: '',
  class: '',
  section: '',
  photo: null,
});

const hasActiveFilters = computed(() => {
  return search.value || selectedClass.value || selectedSection.value;
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
    if (selectedSection.value) params.section = selectedSection.value;

    const response = await api.get('/students', { params });
    students.value = response.data.data;
    totalPages.value = response.data.meta.last_page;
    totalStudents.value = response.data.meta.total;
  } catch (error) {
    console.error('Failed to fetch students:', error);
  } finally {
    loading.value = false;
  }
};

const fetchClasses = async () => {
  try {
    const response = await api.get('/classes');
    const activeClasses = response.data.data.filter(c => c.is_active);
    classes.value = activeClasses;
    availableClasses.value = activeClasses;
  } catch (error) {
    console.error('Failed to fetch classes:', error);
  }
};

const fetchSections = async () => {
  try {
    const response = await api.get('/sections');
    allSections.value = response.data.data;
    updateAvailableSections();
    updateFilterSections();
  } catch (error) {
    console.error('Failed to fetch sections:', error);
  }
};

const updateFilterSections = () => {
  if (selectedClass.value) {
    const selectedClassObj = availableClasses.value.find(c => c.name === selectedClass.value);
    if (selectedClassObj) {
      availableSections.value = allSections.value.filter(s => s.class_id === selectedClassObj.id);
    } else {
      availableSections.value = [];
    }
  } else {
    availableSections.value = [];
  }
};

const updateAvailableSections = () => {
  if (form.value.class) {
    const selectedClassObj = classes.value.find(c => c.name === form.value.class);
    if (selectedClassObj) {
      sections.value = allSections.value.filter(s => s.class_id === selectedClassObj.id);
    } else {
      sections.value = [];
    }
  } else {
    sections.value = [];
  }
};

const onClassChange = () => {
  form.value.section = '';
  updateAvailableSections();
};

const onClassFilterChange = () => {
  selectedSection.value = '';
  updateFilterSections();
  currentPage.value = 1;
  fetchStudents();
};

const clearFilters = () => {
  search.value = '';
  selectedClass.value = '';
  selectedSection.value = '';
  currentPage.value = 1;
  availableSections.value = [];
  fetchStudents();
};

const changePage = (page) => {
  currentPage.value = page;
  fetchStudents();
};

const editStudent = (student) => {
  editingStudent.value = student;
  form.value = {
    student_id: student.student_id,
    name: student.name,
    class: student.class,
    section: student.section,
    photo: null, // Don't copy the photo URL, let user upload new one if needed
  };
  updateAvailableSections();
};

const deleteStudent = async (id) => {
  const confirmed = await confirm({
    title: 'Delete Student?',
    message: 'This will permanently delete this student and all their attendance records. This action cannot be undone.',
    type: 'danger',
    confirmText: 'Delete Student',
    cancelText: 'Cancel',
  });

  if (!confirmed) return;
  
  try {
    await api.delete(`/students/${id}`);
    toast.success('Student deleted successfully!');
    fetchStudents();
  } catch (error) {
    toast.error('Failed to delete student. Please try again.');
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
  if (form.value.photo && form.value.photo instanceof File) {
    formData.append('photo', form.value.photo);
  }

  try {
    if (editingStudent.value) {
      // Laravel doesn't support PUT with FormData, use POST with _method
      formData.append('_method', 'PUT');
      await api.post(`/students/${editingStudent.value.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      await api.post('/students', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    }
    toast.success(editingStudent.value ? 'Student updated successfully!' : 'Student added successfully!');
    closeModal();
    fetchStudents();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Failed to save student';
    const errors = error.response?.data?.errors;
    if (errors) {
      const errorList = Object.values(errors).flat().join(', ');
      toast.error(`${errorMsg}: ${errorList}`);
    } else {
      toast.error(errorMsg);
    }
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
  fetchSections();
});
</script>

<style scoped>
.student-list {
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

.btn-primary {
  display: flex;
  align-items: center;
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
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
}

.btn-icon {
  width: 20px;
  height: 20px;
}

.filters-card {
  background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.filters-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filters-header h3 {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.btn-clear-filters {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: transparent;
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-clear-filters:hover {
  background: var(--bg-primary);
  color: var(--primary);
  border-color: var(--primary);
}

.btn-clear-filters svg {
  width: 14px;
  height: 14px;
}

.filters {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: flex-end;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-width: 180px;
}

.filter-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.filter-summary {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid var(--border-color);
}

.filter-summary p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.filter-summary strong {
  color: var(--primary);
  font-weight: 600;
}

.search-wrapper {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: var(--text-muted);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  transition: all 0.2s ease;
}

.search-input:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  outline: none;
}

.filter-select {
  min-width: 180px;
  padding: 0.875rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  outline: none;
}

.filter-select:disabled {
  background: var(--bg-tertiary);
  color: var(--text-secondary);
  cursor: not-allowed;
  opacity: 0.6;
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

.content-card {
  background: var(--bg-secondary);
  border-radius: 16px;
  box-shadow: var(--shadow-lg);
  border: 1px solid var(--border-color);
  overflow: hidden;
}

.empty-state {
  padding: 4rem 2rem;
  text-align: center;
  color: var(--text-secondary);
}

.empty-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  color: var(--text-muted);
}

.empty-state h3 {
  color: var(--text-primary);
  margin-bottom: 0.5rem;
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

.font-mono {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: var(--primary);
}

.font-semibold {
  font-weight: 600;
  color: var(--text-primary);
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

.badge-secondary {
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}

.photo-wrapper {
  display: flex;
  align-items: center;
}

.student-photo {
  width: 48px;
  height: 48px;
  object-fit: cover;
  border-radius: 10px;
  border: 2px solid var(--border-color);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.student-photo:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.photo-placeholder {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, var(--bg-tertiary) 0%, var(--border-color) 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  border: 2px solid var(--border-color);
}

.photo-placeholder svg {
  width: 24px;
  height: 24px;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-action {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.btn-action svg {
  width: 18px;
  height: 18px;
}

.btn-edit {
  background: rgba(16, 185, 129, 0.1);
  color: var(--secondary);
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.btn-edit:hover {
  background: rgba(16, 185, 129, 0.2);
  transform: scale(1.05);
}

.btn-delete {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: scale(1.05);
}

.pagination-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-top: 1px solid var(--border-color);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.pagination {
  display: flex;
  gap: 0.5rem;
}

.btn-pagination {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-pagination:hover:not(:disabled) {
  background: var(--bg-tertiary);
  border-color: var(--primary);
  color: var(--primary);
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-pagination svg {
  width: 16px;
  height: 16px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 2rem;
  backdrop-filter: blur(4px);
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-content {
  background: var(--bg-secondary);
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow-xl);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--text-primary);
  margin: 0;
}

.btn-close {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-tertiary);
  border: none;
  border-radius: 8px;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background: var(--danger);
  color: white;
  transform: rotate(90deg);
}

.btn-close svg {
  width: 20px;
  height: 20px;
}

.modal-form {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.required {
  color: var(--danger);
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

.form-group select:disabled {
  background: var(--bg-tertiary);
  color: var(--text-secondary);
  cursor: not-allowed;
  opacity: 0.6;
}

.file-input {
  padding: 0.5rem;
  font-size: 0.875rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.btn-secondary {
  padding: 0.875rem 1.5rem;
  background: var(--bg-tertiary);
  border: 2px solid var(--border-color);
  border-radius: 10px;
  color: var(--text-primary);
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--border-color);
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .table-container {
    overflow-x: scroll;
  }
  
  .modal-overlay {
    padding: 1rem;
  }
}
</style>
