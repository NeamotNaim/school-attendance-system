<template>
  <div class="holidays-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Holidays</h1>
        <p class="page-subtitle">Manage school holidays and events</p>
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
        <div class="form-group">
          <label>Year</label>
          <select v-model="selectedYear" @change="fetchHolidays">
            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>
        <div class="form-group">
          <label>Type</label>
          <select v-model="selectedType" @change="fetchHolidays">
            <option value="">All Types</option>
            <option value="holiday">Holiday</option>
            <option value="exam">Exam</option>
            <option value="event">Event</option>
          </select>
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
        <div v-for="holiday in holidays" :key="holiday.id" class="holiday-card">
          <div class="holiday-date">
            <span class="date-day">{{ new Date(holiday.date).getDate() }}</span>
            <span class="date-month">{{ new Date(holiday.date).toLocaleString('default', { month: 'short' }) }}</span>
          </div>
          <div class="holiday-content">
            <div class="holiday-header">
              <h3>{{ holiday.title }}</h3>
              <span :class="`type-badge type-${holiday.type}`">{{ holiday.type }}</span>
            </div>
            <p class="holiday-description">{{ holiday.description || 'No description' }}</p>
            <div class="holiday-meta">
              <span v-if="holiday.is_recurring" class="recurring-badge">Recurring</span>
            </div>
          </div>
          <div class="holiday-actions">
            <button @click="editHoliday(holiday)" class="btn-edit">Edit</button>
            <button @click="deleteHoliday(holiday.id)" class="btn-delete">Delete</button>
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

onMounted(() => {
  fetchHolidays();
});
</script>

<style scoped>
.holidays-page {
  width: 100%;
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

.holidays-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.holiday-card {
  display: flex;
  gap: 1.5rem;
  background: var(--bg-tertiary);
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.holiday-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.holiday-date {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 80px;
  padding: 1rem;
  background: var(--primary);
  color: white;
  border-radius: 8px;
  text-align: center;
}

.date-day {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
}

.date-month {
  font-size: 0.875rem;
  text-transform: uppercase;
  margin-top: 0.25rem;
}

.holiday-content {
  flex: 1;
}

.holiday-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.holiday-header h3 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.25rem;
}

.type-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  text-transform: capitalize;
}

.type-holiday {
  background: rgba(16, 185, 129, 0.1);
  color: var(--secondary);
}

.type-exam {
  background: rgba(239, 68, 68, 0.1);
  color: var(--danger);
}

.type-event {
  background: rgba(245, 158, 11, 0.1);
  color: var(--warning);
}

.holiday-description {
  color: var(--text-secondary);
  font-size: 0.9375rem;
  margin-bottom: 0.5rem;
}

.holiday-meta {
  display: flex;
  gap: 0.5rem;
}

.recurring-badge {
  padding: 0.25rem 0.75rem;
  background: rgba(79, 70, 229, 0.1);
  color: var(--primary);
  border-radius: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
}

.holiday-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.holiday-actions button {
  padding: 0.625rem 1rem;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 80px;
}

</style>

