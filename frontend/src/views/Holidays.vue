<template>
  <div class="holidays-page">
    <div class="page-header">
      <div class="header-text">
        <h1 class="page-title">Holidays & Events</h1>
        <p class="page-subtitle">Manage school holidays, exams, and special events</p>
      </div>
      <button @click="showAddModal = true" class="btn-primary">
        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add Holiday</span>
      </button>
    </div>

    <div class="filters-card">
      <div class="filters">
        <div class="filter-group">
          <label>
            <svg class="filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
            </svg>
            Year
          </label>
          <select v-model="selectedYear" @change="fetchHolidays">
            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label>
            <svg class="filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            </svg>
            Type
          </label>
          <select v-model="selectedType" @change="fetchHolidays">
            <option value="">All Types</option>
            <option value="holiday">🎉 Holiday</option>
            <option value="exam">📝 Exam</option>
            <option value="event">🎪 Event</option>
          </select>
        </div>
        <div class="filter-stats">
          <div class="stat-badge">
            <span class="stat-number">{{ holidays.length }}</span>
            <span class="stat-label">Total</span>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading holidays...</p>
    </div>

    <div v-else class="content-card">
      <div v-if="holidays.length === 0" class="empty-state">
        <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <h3>No holidays found</h3>
        <p>Add holidays to mark non-working days.</p>
      </div>

      <div v-else class="holidays-list">
        <div v-for="holiday in holidays" :key="holiday.id" :class="`holiday-card type-${holiday.type}`">
          <div class="holiday-date-wrapper">
            <div class="holiday-date">
              <span class="date-day">{{ new Date(holiday.date).getDate() }}</span>
              <span class="date-month">{{ new Date(holiday.date).toLocaleString('default', { month: 'short' }) }}</span>
              <span class="date-year">{{ new Date(holiday.date).getFullYear() }}</span>
            </div>
            <div class="holiday-weekday">
              {{ new Date(holiday.date).toLocaleString('default', { weekday: 'long' }) }}
            </div>
          </div>
          <div class="holiday-content">
            <div class="holiday-header">
              <div class="title-group">
                <span class="type-icon">{{ getTypeIcon(holiday.type) }}</span>
                <h3>{{ holiday.title }}</h3>
              </div>
              <span :class="`type-badge type-${holiday.type}`">{{ holiday.type }}</span>
            </div>
            <p class="holiday-description">{{ holiday.description || 'No description provided' }}</p>
            <div class="holiday-meta">
              <span v-if="holiday.is_recurring" class="meta-badge recurring">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"></path>
                  <path d="M21 3v5h-5"></path>
                </svg>
                Recurring Annually
              </span>
              <span class="meta-badge days-until">
                {{ getDaysUntil(holiday.date) }}
              </span>
            </div>
          </div>
          <div class="holiday-actions">
            <button @click="editHoliday(holiday)" class="btn-icon-action btn-edit" title="Edit">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
              </svg>
            </button>
            <button @click="deleteHoliday(holiday.id)" class="btn-icon-action btn-delete" title="Delete">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showAddModal || editingHoliday" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h2>{{ editingHoliday ? 'Edit' : 'Add' }} Holiday</h2>
          <button @click="closeModal" class="btn-close">×</button>
        </div>
        <form @submit.prevent="saveHoliday" class="modal-form">
          <div class="form-group">
            <label>Title <span class="required">*</span></label>
            <input v-model="form.title" required placeholder="e.g., New Year" />
          </div>
          <div class="form-group">
            <label>Date <span class="required">*</span></label>
            <input v-model="form.date" type="date" required />
          </div>
          <div class="form-group">
            <label>Type</label>
            <select v-model="form.type">
              <option value="holiday">Holiday</option>
              <option value="exam">Exam</option>
              <option value="event">Event</option>
            </select>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="form.description" placeholder="Holiday description"></textarea>
          </div>
          <div class="form-group">
            <label>
              <input v-model="form.is_recurring" type="checkbox" />
              Recurring (every year)
            </label>
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

const holidays = ref([]);
const loading = ref(false);
const showAddModal = ref(false);
const editingHoliday = ref(null);
const selectedYear = ref(new Date().getFullYear());
const selectedType = ref('');
const form = ref({
  title: '',
  date: '',
  description: '',
  type: 'holiday',
  is_recurring: false,
});

const years = computed(() => {
  const currentYear = new Date().getFullYear();
  return Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);
});

const fetchHolidays = async () => {
  loading.value = true;
  try {
    const params = {};
    if (selectedYear.value) {
      params.start_date = `${selectedYear.value}-01-01`;
      params.end_date = `${selectedYear.value}-12-31`;
    }
    if (selectedType.value) {
      params.type = selectedType.value;
    }
    const response = await api.get('/holidays', { params });
    holidays.value = response.data.data;
  } catch (error) {
    console.error('Failed to fetch holidays:', error);
  } finally {
    loading.value = false;
  }
};

const editHoliday = (holiday) => {
  editingHoliday.value = holiday;
  form.value = {
    ...holiday,
    date: holiday.date.split('T')[0],
  };
  showAddModal.value = true;
};

const deleteHoliday = async (id) => {
  if (!confirm('Are you sure you want to delete this holiday?')) return;
  try {
    await api.delete(`/holidays/${id}`);
    fetchHolidays();
  } catch (error) {
    alert('Failed to delete holiday');
  }
};

const saveHoliday = async () => {
  try {
    if (editingHoliday.value) {
      await api.put(`/holidays/${editingHoliday.value.id}`, form.value);
    } else {
      await api.post('/holidays', form.value);
    }
    closeModal();
    fetchHolidays();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save holiday');
  }
};

const closeModal = () => {
  showAddModal.value = false;
  editingHoliday.value = null;
  form.value = {
    title: '',
    date: '',
    description: '',
    type: 'holiday',
    is_recurring: false,
  };
};

const getTypeIcon = (type) => {
  const icons = {
    holiday: '🎉',
    exam: '📝',
    event: '🎪'
  };
  return icons[type] || '📅';
};

const getDaysUntil = (date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const holidayDate = new Date(date);
  holidayDate.setHours(0, 0, 0, 0);
  const diffTime = holidayDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays < 0) return 'Past';
  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Tomorrow';
  if (diffDays <= 7) return `In ${diffDays} days`;
  if (diffDays <= 30) return `In ${Math.ceil(diffDays / 7)} weeks`;
  return `In ${Math.ceil(diffDays / 30)} months`;
};

onMounted(() => {
  fetchHolidays();
});
</script>

<style scoped>
.holidays-page {
  width: 100%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.header-text {
  flex: 1;
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

.filters {
  display: flex;
  gap: 1.5rem;
  align-items: flex-end;
  flex-wrap: wrap;
}

.filter-group {
  flex: 1;
  min-width: 200px;
}

.filter-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.875rem;
}

.filter-icon {
  width: 16px;
  height: 16px;
  color: var(--primary);
}

.filter-group select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  transition: all 0.2s ease;
}

.filter-group select:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.filter-stats {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stat-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: var(--primary);
  color: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.75rem;
  opacity: 0.9;
  margin-top: 0.25rem;
}

.holidays-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.holiday-card {
  display: flex;
  gap: 1.5rem;
  background: var(--bg-secondary);
  border-radius: 16px;
  padding: 1.5rem;
  border: 2px solid var(--border-color);
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.holiday-card::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 6px;
  background: var(--primary);
  transition: width 0.3s ease;
}

.holiday-card.type-holiday::before {
  background: linear-gradient(180deg, #10b981 0%, #059669 100%);
}

.holiday-card.type-exam::before {
  background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
}

.holiday-card.type-event::before {
  background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
}

.holiday-card:hover {
  transform: translateX(4px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary);
}

.holiday-card:hover::before {
  width: 8px;
}

.holiday-date-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  min-width: 100px;
}

.holiday-date {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100px;
  padding: 1rem;
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.holiday-card.type-holiday .holiday-date {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.holiday-card.type-exam .holiday-date {
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.holiday-card.type-event .holiday-date {
  background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.date-day {
  font-size: 2.5rem;
  font-weight: 700;
  line-height: 1;
}

.date-month {
  font-size: 0.875rem;
  text-transform: uppercase;
  margin-top: 0.25rem;
  opacity: 0.9;
}

.date-year {
  font-size: 0.75rem;
  opacity: 0.8;
  margin-top: 0.125rem;
}

.holiday-weekday {
  font-size: 0.8125rem;
  color: var(--text-secondary);
  font-weight: 500;
  text-align: center;
}

.holiday-content {
  flex: 1;
}

.holiday-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
  gap: 1rem;
}

.title-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
}

.type-icon {
  font-size: 1.5rem;
  line-height: 1;
}

.holiday-header h3 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.375rem;
  font-weight: 700;
  line-height: 1.3;
}

.type-badge {
  padding: 0.375rem 1rem;
  border-radius: 20px;
  font-size: 0.8125rem;
  font-weight: 600;
  text-transform: capitalize;
  white-space: nowrap;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.type-holiday {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.1) 100%);
  color: #059669;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.type-exam {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.1) 100%);
  color: #dc2626;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.type-event {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.1) 100%);
  color: #d97706;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.holiday-description {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.holiday-meta {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.meta-badge {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.875rem;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 500;
}

.meta-badge svg {
  width: 14px;
  height: 14px;
}

.meta-badge.recurring {
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary);
  border: 1px solid rgba(79, 70, 229, 0.2);
}

.meta-badge.days-until {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
  border: 1px solid rgba(59, 130, 246, 0.2);
  font-weight: 600;
}

.holiday-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  align-items: center;
}

.btn-icon-action {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.btn-icon-action svg {
  width: 18px;
  height: 18px;
}

.btn-icon-action.btn-edit {
  background: rgba(16, 185, 129, 0.1);
  color: var(--secondary);
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.btn-icon-action.btn-edit:hover {
  background: rgba(16, 185, 129, 0.2);
  transform: scale(1.05);
}

.btn-icon-action.btn-delete {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
  border: 1px solid rgba(239, 68, 68, 0.2);
}

.btn-icon-action.btn-delete:hover {
  background: rgba(239, 68, 68, 0.2);
  transform: scale(1.05);
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
  border-radius: 16px;
  border: 2px dashed var(--border-color);
}

.content-card {
  background: var(--bg-secondary);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 2rem;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
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
  margin: 0;
  color: var(--text-primary);
  font-size: 1.5rem;
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
  font-size: 1.5rem;
  line-height: 1;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background: var(--danger);
  color: white;
  transform: rotate(90deg);
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
  font-size: 0.9375rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.9375rem;
  background: var(--bg-secondary);
  color: var(--text-primary);
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
  font-family: inherit;
}

.form-group input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
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
  padding: 0.875rem 1.5rem;
  background: var(--bg-tertiary);
  border: 2px solid var(--border-color);
  border-radius: 10px;
  color: var(--text-primary);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: var(--border-color);
  transform: translateY(-1px);
}

@media (max-width: 768px) {
  .holiday-card {
    flex-direction: column;
    gap: 1rem;
  }

  .holiday-date-wrapper {
    flex-direction: row;
    width: 100%;
    justify-content: space-between;
  }

  .holiday-actions {
    flex-direction: row;
    justify-content: flex-end;
  }

  .filters {
    flex-direction: column;
  }

  .filter-group {
    width: 100%;
  }
}

</style>

