<template>
  <div class="profile-page">
    <div class="profile-header">
      <div class="profile-avatar-large">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
      </div>
      <div class="profile-header-info">
        <h2>{{ user.name }}</h2>
        <p class="profile-role">{{ user.role }}</p>
      </div>
    </div>

    <div class="profile-content">
      <div class="profile-section">
        <div class="section-header">
          <h3>Personal Information</h3>
          <button v-if="!editMode" @click="enableEdit" class="btn-edit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            Edit Profile
          </button>
        </div>

        <form @submit.prevent="saveProfile" class="profile-form">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input
                v-model="formData.name"
                type="text"
                :disabled="!editMode"
                placeholder="Enter your full name"
                required
              />
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input
                v-model="formData.email"
                type="email"
                :disabled="!editMode"
                placeholder="Enter your email"
                required
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Phone Number</label>
              <input
                v-model="formData.phone"
                type="tel"
                :disabled="!editMode"
                placeholder="Enter your phone number"
              />
            </div>

            <div class="form-group">
              <label>Role</label>
              <input
                v-model="formData.role"
                type="text"
                disabled
                class="disabled-input"
              />
            </div>
          </div>

          <div class="form-group">
            <label>Address</label>
            <textarea
              v-model="formData.address"
              :disabled="!editMode"
              placeholder="Enter your address"
              rows="3"
            ></textarea>
          </div>

          <div v-if="editMode" class="form-actions">
            <button type="button" @click="cancelEdit" class="btn-secondary">
              Cancel
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              <span v-if="!saving">Save Changes</span>
              <span v-else>Saving...</span>
            </button>
          </div>
        </form>
      </div>

      <div class="profile-section">
        <div class="section-header">
          <h3>Change Password</h3>
        </div>

        <form @submit.prevent="changePassword" class="profile-form">
          <div class="form-group">
            <label>Current Password</label>
            <input
              v-model="passwordData.current_password"
              type="password"
              placeholder="Enter current password"
              required
            />
          </div>

          <div class="form-group">
            <label>New Password</label>
            <input
              v-model="passwordData.new_password"
              type="password"
              placeholder="Enter new password"
              required
            />
          </div>

          <div class="form-group">
            <label>Confirm New Password</label>
            <input
              v-model="passwordData.confirm_password"
              type="password"
              placeholder="Confirm new password"
              required
            />
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-primary" :disabled="changingPassword">
              <span v-if="!changingPassword">Update Password</span>
              <span v-else>Updating...</span>
            </button>
          </div>
        </form>
      </div>

      <div class="profile-section">
        <div class="section-header">
          <h3>Account Statistics</h3>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1);">
              <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            </div>
            <div class="stat-info">
              <p class="stat-label">Member Since</p>
              <p class="stat-value">{{ memberSince }}</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon" style="background: rgba(79, 70, 229, 0.1);">
              <svg viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2">
                <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
              </svg>
            </div>
            <div class="stat-info">
              <p class="stat-label">Last Login</p>
              <p class="stat-value">{{ lastLogin }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

const router = useRouter();
const editMode = ref(false);
const saving = ref(false);
const changingPassword = ref(false);

const user = reactive({
  name: '',
  email: '',
  phone: '',
  role: '',
  address: '',
  created_at: '',
  last_login: '',
});

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  role: '',
  address: '',
});

const passwordData = reactive({
  current_password: '',
  new_password: '',
  confirm_password: '',
});

const memberSince = computed(() => {
  if (!user.created_at) return 'N/A';
  return new Date(user.created_at).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
});

const lastLogin = computed(() => {
  if (!user.last_login) return 'Just now';
  return new Date(user.last_login).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
});

const loadUserData = () => {
  const userData = localStorage.getItem('user');
  if (userData) {
    try {
      const parsed = JSON.parse(userData);
      Object.assign(user, {
        name: parsed.name || '',
        email: parsed.email || '',
        phone: parsed.phone || '',
        role: parsed.role || 'Teacher',
        address: parsed.address || '',
        created_at: parsed.created_at || new Date().toISOString(),
        last_login: parsed.last_login || new Date().toISOString(),
      });
      Object.assign(formData, user);
    } catch (error) {
      console.error('Error parsing user data:', error);
    }
  }
};

const enableEdit = () => {
  editMode.value = true;
};

const cancelEdit = () => {
  editMode.value = false;
  Object.assign(formData, user);
};

const saveProfile = async () => {
  saving.value = true;
  try {
    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 1000));
    
    Object.assign(user, formData);
    localStorage.setItem('user', JSON.stringify(user));
    
    editMode.value = false;
    alert('Profile updated successfully!');
  } catch (error) {
    console.error('Error saving profile:', error);
    alert('Failed to update profile. Please try again.');
  } finally {
    saving.value = false;
  }
};

const changePassword = async () => {
  if (passwordData.new_password !== passwordData.confirm_password) {
    alert('New passwords do not match!');
    return;
  }

  if (passwordData.new_password.length < 6) {
    alert('Password must be at least 6 characters long!');
    return;
  }

  changingPassword.value = true;
  try {
    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 1000));
    
    alert('Password changed successfully!');
    passwordData.current_password = '';
    passwordData.new_password = '';
    passwordData.confirm_password = '';
  } catch (error) {
    console.error('Error changing password:', error);
    alert('Failed to change password. Please try again.');
  } finally {
    changingPassword.value = false;
  }
};

onMounted(() => {
  loadUserData();
});
</script>

<style scoped>
.profile-page {
  max-width: 900px;
  margin: 0 auto;
}

.profile-header {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  border-radius: 16px;
  padding: 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-md);
}

.profile-avatar-large {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.profile-avatar-large svg {
  width: 40px;
  height: 40px;
  color: white;
}

.profile-header-info h2 {
  margin: 0 0 0.5rem 0;
  color: white;
  font-size: 1.75rem;
  font-weight: 700;
}

.profile-role {
  margin: 0;
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
}

.profile-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.profile-section {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.section-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
}

.btn-edit {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: var(--primary);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-edit:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-edit svg {
  width: 16px;
  height: 16px;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  color: var(--text-primary);
  font-size: 0.9375rem;
}

.form-group input,
.form-group textarea {
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  font-size: 0.9375rem;
  transition: all 0.2s ease;
  background: var(--bg-primary);
  color: var(--text-primary);
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.form-group input:disabled,
.form-group textarea:disabled {
  background: var(--bg-tertiary);
  color: var(--text-secondary);
  cursor: not-allowed;
}

.disabled-input {
  background: var(--bg-tertiary) !important;
  color: var(--text-secondary) !important;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 0.5rem;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.9375rem;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: var(--bg-tertiary);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background: var(--border-color);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-primary);
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-info {
  flex: 1;
}

.stat-label {
  margin: 0 0 0.25rem 0;
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.stat-value {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
}

@media (max-width: 768px) {
  .profile-header {
    flex-direction: column;
    text-align: center;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .section-header {
    flex-direction: column;
    gap: 1rem;
    align-items: flex-start;
  }

  .btn-edit {
    width: 100%;
    justify-content: center;
  }
}
</style>
