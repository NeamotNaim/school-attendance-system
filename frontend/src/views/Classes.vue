<template>
  <div class="classes-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Classes</h1>
        <p class="page-subtitle">Manage school classes</p>
      </div>
      <button @click="showAddModal = true" class="btn-primary">
        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add Class</span>
      </button>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading classes...</p>
    </div>

    <div v-else class="content-card">
      <div v-if="classes.length === 0" class="empty-state">
        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
        </svg>
        <h3>No classes found</h3>
        <p>Add your first class to get started.</p>
      </div>

      <div v-else class="classes-grid">
        <div v-for="cls in classes" :key="cls.id" class="class-card">
          <div class="class-header">
            <h3>{{ cls.name }}</h3>
            <span class="class-code">{{ cls.code }}</span>
          </div>
          <p class="class-description">{{ cls.description || 'No description' }}</p>
          <div class="class-stats">
            <div class="stat-item">
              <span class="stat-label">Capacity</span>
              <span class="stat-value">{{ cls.capacity }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Status</span>
              <span :class="`status-badge ${cls.is_active ? 'active' : 'inactive'}`">
                {{ cls.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
          </div>
          <div class="class-actions">
            <button @click="editClass(cls)" class="btn-edit">Edit</button>
            <button @click="deleteClass(cls.id)" class="btn-delete">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || editingClass" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editingClass ? 'Edit' : 'Add' }} Class</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        <form @submit.prevent="saveClass" class="modal-form">
          <div class="form-group">
            <label>Name <span class="required">*</span></label>
            <input v-model="form.name" required placeholder="e.g., Class 10" />
          </div>
          <div class="form-group">
            <label>Code <span class="required">*</span></label>
            <input v-model="form.code" required placeholder="e.g., CLS10" />
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="form.description" placeholder="Class description"></textarea>
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

const classes = ref([]);
const loading = ref(false);
const showAddModal = ref(false);
const editingClass = ref(null);
const form = ref({
  name: '',
  code: '',
  description: '',
  capacity: 30,
  is_active: true,
});

const fetchClasses = async () => {
  loading.value = true;
  try {
    const response = await api.get('/classes');
    classes.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch classes:', error);
  } finally {
    loading.value = false;
  }
};

const editClass = (cls) => {
  editingClass.value = cls;
  form.value = { ...cls };
  showAddModal.value = true;
};

const deleteClass = async (id) => {
  if (!confirm('Are you sure you want to delete this class?')) return;
  try {
    await api.delete(`/classes/${id}`);
    fetchClasses();
  } catch (error) {
    alert('Failed to delete class');
  }
};

const saveClass = async () => {
  try {
    if (editingClass.value) {
      await api.put(`/classes/${editingClass.value.id}`, form.value);
    } else {
      await api.post('/classes', form.value);
    }
    closeModal();
    fetchClasses();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save class');
  }
};

const closeModal = () => {
  showAddModal.value = false;
  editingClass.value = null;
  form.value = {
    name: '',
    code: '',
    description: '',
    capacity: 30,
    is_active: true,
  };
};

onMounted(() => {
  fetchClasses();
});
</script>

<style scoped>
.classes-page {
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

.classes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.class-card {
  background: var(--bg-tertiary);
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.class-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.class-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.class-header h3 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.25rem;
}

.class-code {
  padding: 0.25rem 0.75rem;
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary);
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.class-description {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin-bottom: 1rem;
}

.class-stats {
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

.class-actions {
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

.form-group textarea {
  min-height: 100px;
  resize: vertical;
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

