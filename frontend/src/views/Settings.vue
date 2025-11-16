<template>
  <div class="settings-page">
    <div class="settings-header">
      <h2>Settings</h2>
      <p>Manage your application preferences and configurations</p>
    </div>

    <div class="settings-content">
      <!-- Appearance Settings -->
      <div class="settings-section">
        <div class="section-header">
          <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="5"></circle>
              <line x1="12" y1="1" x2="12" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="23"></line>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
              <line x1="1" y1="12" x2="3" y2="12"></line>
              <line x1="21" y1="12" x2="23" y2="12"></line>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
            </svg>
            <div>
              <h3>Appearance</h3>
              <p>Customize how the application looks</p>
            </div>
          </div>
        </div>

        <div class="settings-options">
          <div class="setting-item">
            <div class="setting-info">
              <label>Theme</label>
              <p>Choose your preferred color theme</p>
            </div>
            <select v-model="settings.theme" @change="saveSettings" class="setting-select">
              <option value="light">Light</option>
              <option value="dark">Dark</option>
              <option value="auto">Auto</option>
            </select>
          </div>

          <div class="setting-item">
            <div class="setting-info">
              <label>Compact Mode</label>
              <p>Reduce spacing for more content</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="settings.compactMode" @change="saveSettings" />
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="settings-section">
        <div class="section-header">
          <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <div>
              <h3>Notifications</h3>
              <p>Manage notification preferences</p>
            </div>
          </div>
        </div>

        <div class="settings-options">
          <div class="setting-item">
            <div class="setting-info">
              <label>Email Notifications</label>
              <p>Receive updates via email</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="settings.emailNotifications" @change="saveSettings" />
              <span class="toggle-slider"></span>
            </label>
          </div>

          <div class="setting-item">
            <div class="setting-info">
              <label>Attendance Reminders</label>
              <p>Get reminded to mark attendance</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="settings.attendanceReminders" @change="saveSettings" />
              <span class="toggle-slider"></span>
            </label>
          </div>

          <div class="setting-item">
            <div class="setting-info">
              <label>Report Notifications</label>
              <p>Notify when reports are generated</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="settings.reportNotifications" @change="saveSettings" />
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Attendance Settings -->
      <div class="settings-section">
        <div class="section-header">
          <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <div>
              <h3>Attendance</h3>
              <p>Configure attendance recording options</p>
            </div>
          </div>
        </div>

        <div class="settings-options">
          <div class="setting-item">
            <div class="setting-info">
              <label>Default Status</label>
              <p>Default attendance status for students</p>
            </div>
            <select v-model="settings.defaultStatus" @change="saveSettings" class="setting-select">
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="none">None</option>
            </select>
          </div>

          <div class="setting-item">
            <div class="setting-info">
              <label>Auto-save</label>
              <p>Automatically save attendance changes</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="settings.autoSave" @change="saveSettings" />
              <span class="toggle-slider"></span>
            </label>
          </div>

          <div class="setting-item">
            <div class="setting-info">
              <label>Show Student Photos</label>
              <p>Display student photos in attendance</p>
            </div>
            <label class="toggle-switch">
              <input type="checkbox" v-model="settings.showStudentPhotos" @change="saveSettings" />
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
      </div>

      <!-- Data & Privacy -->
      <div class="settings-section">
        <div class="section-header">
          <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <div>
              <h3>Data & Privacy</h3>
              <p>Manage your data and privacy settings</p>
            </div>
          </div>
        </div>

        <div class="settings-options">
          <div class="setting-item">
            <div class="setting-info">
              <label>Data Export</label>
              <p>Download your data in CSV format</p>
            </div>
            <button @click="exportData" class="btn-action">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" y1="15" x2="12" y2="3"></line>
              </svg>
              Export Data
            </button>
          </div>

          <div class="setting-item">
            <div class="setting-info">
              <label>Clear Cache</label>
              <p>Clear application cache and temporary data</p>
            </div>
            <button @click="clearCache" class="btn-action">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="1 4 1 10 7 10"></polyline>
                <polyline points="23 20 23 14 17 14"></polyline>
                <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
              </svg>
              Clear Cache
            </button>
          </div>
        </div>
      </div>

      <!-- About -->
      <div class="settings-section">
        <div class="section-header">
          <div class="section-title">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div>
              <h3>About</h3>
              <p>Application information</p>
            </div>
          </div>
        </div>

        <div class="settings-options">
          <div class="info-item">
            <span class="info-label">Version</span>
            <span class="info-value">1.0.0</span>
          </div>
          <div class="info-item">
            <span class="info-label">Last Updated</span>
            <span class="info-value">November 2024</span>
          </div>
          <div class="info-item">
            <span class="info-label">License</span>
            <span class="info-value">MIT License</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';

const settings = reactive({
  theme: 'light',
  compactMode: false,
  emailNotifications: true,
  attendanceReminders: true,
  reportNotifications: true,
  defaultStatus: 'present',
  autoSave: true,
  showStudentPhotos: true,
});

const loadSettings = () => {
  const savedSettings = localStorage.getItem('app_settings');
  if (savedSettings) {
    try {
      Object.assign(settings, JSON.parse(savedSettings));
    } catch (error) {
      console.error('Error loading settings:', error);
    }
  }
};

const saveSettings = () => {
  localStorage.setItem('app_settings', JSON.stringify(settings));
  showNotification('Settings saved successfully!');
};

const exportData = () => {
  showNotification('Data export started. You will receive an email when ready.');
};

const clearCache = () => {
  if (confirm('Are you sure you want to clear the cache? This will refresh the page.')) {
    localStorage.removeItem('app_cache');
    showNotification('Cache cleared successfully!');
    setTimeout(() => {
      window.location.reload();
    }, 1000);
  }
};

const showNotification = (message) => {
  alert(message);
};

onMounted(() => {
  loadSettings();
});
</script>

<style scoped>
.settings-page {
  max-width: 900px;
  margin: 0 auto;
}

.settings-header {
  margin-bottom: 2rem;
}

.settings-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
  color: var(--text-primary);
}

.settings-header p {
  margin: 0;
  color: var(--text-secondary);
  font-size: 1rem;
}

.settings-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.settings-section {
  background: var(--bg-secondary);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
}

.section-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.section-title {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.section-title > svg {
  width: 24px;
  height: 24px;
  color: var(--primary);
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.section-title h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
}

.section-title p {
  margin: 0;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.settings-options {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.setting-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: var(--bg-primary);
  border-radius: 10px;
  border: 1px solid var(--border-color);
}

.setting-info {
  flex: 1;
}

.setting-info label {
  display: block;
  font-weight: 500;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
  font-size: 0.9375rem;
}

.setting-info p {
  margin: 0;
  font-size: 0.8125rem;
  color: var(--text-secondary);
}

.setting-select {
  padding: 0.5rem 1rem;
  border: 1px solid var(--border-color);
  border-radius: 8px;
  background: var(--bg-secondary);
  color: var(--text-primary);
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 120px;
}

.setting-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 26px;
  flex-shrink: 0;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: var(--border-color);
  transition: 0.3s;
  border-radius: 26px;
}

.toggle-slider:before {
  position: absolute;
  content: '';
  height: 20px;
  width: 20px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: var(--primary);
}

input:checked + .toggle-slider:before {
  transform: translateX(22px);
}

.btn-action {
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
  font-size: 0.875rem;
  white-space: nowrap;
}

.btn-action:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.btn-action svg {
  width: 16px;
  height: 16px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: var(--bg-primary);
  border-radius: 8px;
  border: 1px solid var(--border-color);
}

.info-label {
  font-weight: 500;
  color: var(--text-secondary);
  font-size: 0.9375rem;
}

.info-value {
  font-weight: 600;
  color: var(--text-primary);
  font-size: 0.9375rem;
}

@media (max-width: 768px) {
  .setting-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .setting-select,
  .btn-action {
    width: 100%;
    justify-content: center;
  }

  .toggle-switch {
    align-self: flex-end;
  }
}
</style>
