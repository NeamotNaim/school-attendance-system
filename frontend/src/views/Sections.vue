<template>
  <div class="sections-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Sections</h1>
        <p class="page-subtitle">Manage class sections</p>
      </div>
      <button @click="showAddModal = true" class="btn-primary">
        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add Section</span>
      </button>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading sections...</p>
    </div>

    <div v-else class="content-card">
      <div v-if="sections.length === 0" class="empty-state">
        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="9" y1="3" x2="9" y2="21"></line>
        </svg>
        <h3>No sections found</h3>
        <p>Add your first section to get started.</p>
      </div>

      <div v-else class="sections-grid">
        <div v-for="section in sections" :key="section.id" class="section-card">
          <div class="section-header">
            <h3>{{ section.name }}</h3>
            <span class="section-code">{{ section.code }}</span>
          </div>
          <p class="section-class">Class: {{ section.class?.name || 'N/A' }}</p>
          <div class="section-stats">
            <div class="stat-item">
              <span class="stat-label">Capacity</span>
              <span class="stat-value">{{ section.capacity }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Status</span>
              <span :class="`status-badge ${section.is_active ? 'active' : 'inactive'}`">
                {{ section.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          <div class="section-actions">
            <button @click="editSection(section)" class="btn-edit">Edit</button>
            <button @click="deleteSection(section.id)" class="btn-delete">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || editingSection" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editingSection ? 'Edit' : 'Add' }} Section</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        <form @submit.prevent="saveSection" class="modal-form">
          <div class="form-group">
            <label>Class <span class="required">*</span></label>
            <select v-model="form.class_id" required>
              <option value="">Select Class</option>
              <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
            </select>
          </div>
          <div class="form-group">
            <label>Name <span class="required">*</span></label>
            <input v-model="form.name" required placeholder="e.g., Section A" />
          </div>
          <div class="form-group">
            <label>Code <span class="required">*</span></label>
            <input v-model="form.code" required placeholder="e.g., SEC-A" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Capacity</label>
              <input v-model.number="form.capacity" type="number" min="1" />
            </div>
            <div class="form-group">
              <label>Status</label>
              <select v-model="form.is_active">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
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
import { ref, onMounted } from 'vue';
import api from '../services/api';
import { useToast } from '../composables/useToast';
import { useConfirm } from '../composables/useConfirm';

const toast = useToast();
const { confirm } = useConfirm();

const sections = ref([]);
const classes = ref([]);
const loading = ref(false);
const showAddModal = ref(false);
const editingSection = ref(null);
const form = ref({
  class_id: '',
  name: '',
  code: '',
  capacity: 30,
  is_active: true,
});

const fetchSections = async () => {
  loading.value = true;
  try {
    const response = await api.get('/sections');
    sections.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch sections:', error);
  } finally {
    loading.value = false;
  }
};

const fetchClasses = async () => {
  try {
    const response = await api.get('/classes');
    classes.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch classes:', error);
  }
};

const editSection = (section) => {
  editingSection.value = section;
  form.value = { ...section, class_id: section.class_id || section.class?.id };
  showAddModal.value = true;
};

const deleteSection = async (id) => {
  const confirmed = await confirm({
    title: 'Delete Section?',
    message: 'This will permanently delete this section and all associated student attendance records. This action cannot be undone.',
    type: 'danger',
    confirmText: 'Delete Section',
    cancelText: 'Cancel',
  });

  if (!confirmed) return;

  try {
    await api.delete(`/sections/${id}`);
    toast.success('Section deleted successfully!');
    fetchSections();
  } catch (error) {
    toast.error('Failed to delete section. Please try again.');
  }
};

const saveSection = async () => {
  try {
    if (editingSection.value) {
      await api.put(`/sections/${editingSection.value.id}`, form.value);
      toast.success('Section updated successfully!');
    } else {
      await api.post('/sections', form.value);
      toast.success('Section added successfully!');
    }
    closeModal();
    fetchSections();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to save section');
  }
};

const closeModal = () => {
  showAddModal.value = false;
  editingSection.value = null;
  form.value = {
    class_id: '',
    name: '',
    code: '',
    capacity: 30,
    is_active: true,
  };
};

onMounted(() => {
  fetchSections();
  fetchClasses();
});
</script>

<style scoped>
.sections-page {
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
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-icon {
  width: 20px;
  height: 20px;
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

.content-card {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.sections-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.section-card {
  background: var(--bg-tertiary);
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.section-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.section-header h3 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.25rem;
}

.section-code {
  padding: 0.25rem 0.75rem;
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary);
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.section-class {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin-bottom: 1rem;
}

.section-stats {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.status-badge.active {
  background: rgba(16, 185, 129, 0.1);
  color: var(--secondary);
}

.status-badge.inactive {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}

.section-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-edit,
.btn-delete {
  flex: 1;
  padding: 0.625rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-edit {
  background: rgba(16, 185, 129, 0.1);
  color: var(--secondary);
}

.btn-edit:hover {
  background: rgba(16, 185, 129, 0.2);
}

.btn-delete {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-content {
  background: var(--bg-secondary);
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: var(--shadow-xl);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  margin: 0;
  color: var(--text-primary);
}

.btn-close {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-tertiary);
  border: none;
  border-radius: 6px;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 1.5rem;
  line-height: 1;
}

.modal-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--text-primary);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.required {
  color: var(--danger);
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.btn-secondary {
  padding: 0.75rem 1.5rem;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-primary);
  font-weight: 500;
  cursor: pointer;
}
</style>

