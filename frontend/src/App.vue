<template>
  <div id="app">
    <Toast />
    <ConfirmDialog />
    <div v-if="isAuthenticated" class="app-layout">
      <!-- Sidebar -->
      <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
        <div class="sidebar-header">
          <div class="sidebar-brand">
            <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
              <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <span v-if="!sidebarCollapsed" class="brand-text">School Attendance</span>
          </div>
        </div>

        <nav class="sidebar-nav">
          <router-link to="/" class="nav-item" exact-active-class="active">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span v-if="!sidebarCollapsed">Dashboard</span>
          </router-link>

          <router-link to="/students" class="nav-item" active-class="active">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span v-if="!sidebarCollapsed">Students</span>
          </router-link>

          <router-link to="/attendance" class="nav-item" active-class="active">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span v-if="!sidebarCollapsed">Attendance</span>
          </router-link>

          <div v-if="!sidebarCollapsed" class="nav-divider"></div>

          <div v-if="!sidebarCollapsed" class="nav-section">
            <p class="nav-section-title">Reports</p>
          </div>

          <router-link to="/reports/daily" class="nav-item" active-class="active" v-if="!sidebarCollapsed">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            <span>Daily Report</span>
          </router-link>

          <router-link to="/reports/weekly" class="nav-item" active-class="active" v-if="!sidebarCollapsed">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Weekly Report</span>
          </router-link>

          <router-link to="/reports/monthly" class="nav-item" active-class="active" v-if="!sidebarCollapsed">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Monthly Report</span>
          </router-link>

          <div v-if="!sidebarCollapsed" class="nav-divider"></div>

          <div v-if="!sidebarCollapsed" class="nav-section">
            <p class="nav-section-title">Settings</p>
          </div>

          <router-link to="/classes" class="nav-item" active-class="active" v-if="!sidebarCollapsed">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 14l9-5-9-5-9 5 9 5z"></path>
              <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
            </svg>
            <span>Classes</span>
          </router-link>

          <router-link to="/sections" class="nav-item" active-class="active" v-if="!sidebarCollapsed">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="9" y1="3" x2="9" y2="21"></line>
            </svg>
            <span>Sections</span>
          </router-link>

          <router-link to="/holidays" class="nav-item" active-class="active" v-if="!sidebarCollapsed">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <span>Holidays</span>
          </router-link>
        </nav>

        <div class="sidebar-footer">
          <button @click="toggleSidebar" class="nav-item toggle-btn">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-if="!sidebarCollapsed">
              <path d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" v-else>
              <path d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
            </svg>
          </button>
          <button @click="handleLogout" class="nav-item logout-btn">
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span v-if="!sidebarCollapsed">Logout</span>
          </button>
        </div>
      </aside>

      <!-- Main Content -->
      <div class="main-wrapper">
        <header class="top-header">
          <div class="header-content">
            <button @click="toggleSidebar" class="mobile-menu-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
              </svg>
            </button>
            <h1 class="page-title">{{ currentPageTitle }}</h1>
            <div class="header-actions">
              <NotificationBell />
              <div class="user-profile" @click="toggleProfileMenu" v-click-outside="closeProfileMenu">
                <div class="user-info">
                  <div class="user-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                      <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                  </div>
                  <div class="user-details">
                    <span class="user-name">{{ userName }}</span>
                    <span class="user-role">{{ userRole }}</span>
                  </div>
                  <svg class="dropdown-icon" :class="{ open: showProfileMenu }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                  </svg>
                </div>
                
                <transition name="dropdown">
                  <div v-if="showProfileMenu" class="profile-dropdown">
                    <div class="dropdown-header">
                      <div class="dropdown-avatar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                          <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                      </div>
                      <div class="dropdown-user-info">
                        <p class="dropdown-name">{{ userName }}</p>
                        <p class="dropdown-email">{{ userEmail }}</p>
                      </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    
                    <div class="dropdown-menu">
                      <button @click="goToProfile" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                          <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>My Profile</span>
                      </button>
                      
                      <button @click="goToSettings" class="dropdown-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <circle cx="12" cy="12" r="3"></circle>
                          <path d="M12 1v6m0 6v6m5.2-13.2l-4.2 4.2m0 6l4.2 4.2M23 12h-6m-6 0H1m18.2 5.2l-4.2-4.2m0-6l-4.2-4.2"></path>
                        </svg>
                        <span>Settings</span>
                      </button>
                      
                      <div class="dropdown-divider"></div>
                      
                      <button @click="handleLogout" class="dropdown-item logout">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                          <polyline points="16 17 21 12 16 7"></polyline>
                          <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Logout</span>
                      </button>
                    </div>
                  </div>
                </transition>
              </div>
            </div>
          </div>
        </header>
        <main class="main-content">
          <router-view />
        </main>
      </div>
    </div>
    <div v-else>
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from './services/api';
import Toast from './components/Toast.vue';
import ConfirmDialog from './components/ConfirmDialog.vue';
import NotificationBell from './components/NotificationBell.vue';

const router = useRouter();
const route = useRoute();
const sidebarCollapsed = ref(false);
const showProfileMenu = ref(false);

const isAuthenticated = computed(() => {
  return !!localStorage.getItem('auth_token');
});

const userName = computed(() => {
  const user = localStorage.getItem('user');
  if (user) {
    try {
      return JSON.parse(user).name || 'User';
    } catch {
      return 'User';
    }
  }
  return 'User';
});

const userEmail = computed(() => {
  const user = localStorage.getItem('user');
  if (user) {
    try {
      return JSON.parse(user).email || 'user@example.com';
    } catch {
      return 'user@example.com';
    }
  }
  return 'user@example.com';
});

const userRole = computed(() => {
  const user = localStorage.getItem('user');
  if (user) {
    try {
      return JSON.parse(user).role || 'Teacher';
    } catch {
      return 'Teacher';
    }
  }
  return 'Teacher';
});

const currentPageTitle = computed(() => {
  const titles = {
    '/': 'Dashboard',
    '/students': 'Students',
    '/attendance': 'Record Attendance',
    '/reports/daily': 'Daily Report',
    '/reports/weekly': 'Weekly Report',
    '/reports/monthly': 'Monthly Report',
    '/classes': 'Classes',
    '/sections': 'Sections',
    '/holidays': 'Holidays',
    '/profile': 'My Profile',
    '/settings': 'Settings',
  };
  return titles[route.path] || 'Dashboard';
});

const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value;
};

const toggleProfileMenu = () => {
  showProfileMenu.value = !showProfileMenu.value;
};

const closeProfileMenu = () => {
  showProfileMenu.value = false;
};

const goToProfile = () => {
  closeProfileMenu();
  router.push('/profile');
};

const goToSettings = () => {
  closeProfileMenu();
  router.push('/settings');
};

const handleLogout = async () => {
  closeProfileMenu();
  try {
    await api.post('/logout');
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    router.push('/login');
  }
};

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  },
};

onMounted(() => {
  // Check screen size and collapse sidebar on mobile
  if (window.innerWidth < 1024) {
    sidebarCollapsed.value = true;
  }
  
  // Add window resize listener
  window.addEventListener('resize', () => {
    if (window.innerWidth < 1024) {
      sidebarCollapsed.value = true;
    } else {
      sidebarCollapsed.value = false;
    }
  });
});
</script>

<style>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: var(--bg-primary);
}

.app-layout {
  display: flex;
  min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
  color: white;
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  box-shadow: var(--shadow-lg);
  position: fixed;
  left: 0;
  top: 0;
  height: 100vh;
  z-index: 1000;
  overflow-y: auto;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.sidebar::-webkit-scrollbar {
  display: none;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  width: 100%;
}

.brand-icon {
  width: 32px;
  height: 32px;
  stroke: white;
  flex-shrink: 0;
}

.brand-text {
  white-space: nowrap;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1.5rem;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9375rem;
  transition: all 0.2s ease;
  border-left: 3px solid transparent;
  position: relative;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: rgba(255, 255, 255, 0.5);
}

.nav-item.active {
  background: rgba(255, 255, 255, 0.15);
  color: white;
  border-left-color: white;
  font-weight: 600;
}



.nav-icon {
  width: 20px;
  height: 20px;
  stroke: currentColor;
  flex-shrink: 0;
}

.nav-item span {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
  min-width: 0;
}

.nav-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 1rem 1.5rem;
}

.nav-section {
  padding: 0.5rem 1.5rem;
  margin-top: 0.5rem;
}

.nav-section-title {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
}

.toggle-btn {
  background: rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  justify-content: flex-end;
  padding-right: 1.5rem;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.sidebar.collapsed .toggle-btn {
  justify-content: center;
  padding-right: 1.5rem;
}

.logout-btn {
  background: rgba(255, 255, 255, 0.05);
}

.logout-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

.sidebar-footer {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0;
  padding-bottom: 1rem;
  margin-top: auto;
}

/* Main Content Area */
.main-wrapper {
  flex: 1;
  margin-left: 280px;
  display: flex;
  flex-direction: column;
  transition: margin-left 0.3s ease;
  min-height: 100vh;
}

.sidebar.collapsed ~ .main-wrapper {
  margin-left: 80px;
}

.top-header {
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
  padding: 1.5rem 2rem;
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.user-profile {
  position: relative;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  background: var(--bg-tertiary);
  border-radius: 10px;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid var(--border-color);
}

.user-info:hover {
  background: var(--bg-primary);
  border-color: var(--primary);
  box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.user-avatar svg {
  width: 20px;
  height: 20px;
}

.user-details {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.user-name {
  font-weight: 600;
  font-size: 0.9375rem;
  color: var(--text-primary);
  line-height: 1.2;
}

.user-role {
  font-size: 0.75rem;
  color: var(--text-secondary);
  line-height: 1.2;
}

.dropdown-icon {
  width: 16px;
  height: 16px;
  color: var(--text-secondary);
  transition: transform 0.2s ease;
  margin-left: 0.25rem;
}

.dropdown-icon.open {
  transform: rotate(180deg);
}

.profile-dropdown {
  position: absolute;
  top: calc(100% + 0.5rem);
  right: 0;
  width: 280px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  padding: 1.25rem;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: white;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.dropdown-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dropdown-avatar svg {
  width: 24px;
  height: 24px;
  color: white;
}

.dropdown-user-info {
  flex: 1;
  min-width: 0;
}

.dropdown-name {
  font-weight: 600;
  font-size: 1rem;
  margin: 0 0 0.25rem 0;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-email {
  font-size: 0.8125rem;
  margin: 0;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-menu {
  padding: 0.5rem;
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.9375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.dropdown-item:hover {
  background: var(--bg-tertiary);
  color: var(--primary);
}

.dropdown-item svg {
  width: 18px;
  height: 18px;
  color: var(--text-secondary);
  transition: color 0.2s ease;
}

.dropdown-item:hover svg {
  color: var(--primary);
}

.dropdown-item.logout {
  color: #ef4444;
}

.dropdown-item.logout:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.dropdown-item.logout svg {
  color: #ef4444;
}

.dropdown-item.logout:hover svg {
  color: #dc2626;
}

.dropdown-divider {
  height: 1px;
  background: var(--border-color);
  margin: 0.5rem 0;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-enter-to,
.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.main-content {
  flex: 1;
  padding: 2rem;
  max-width: 1400px;
  width: 100%;
  margin: 0 auto;
}

.mobile-menu-btn {
  display: none;
  width: 40px;
  height: 40px;
  align-items: center;
  justify-content: center;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 8px;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.mobile-menu-btn:hover {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}

.mobile-menu-btn svg {
  width: 20px;
  height: 20px;
}

/* Responsive */
@media (max-width: 1024px) {
  .mobile-menu-btn {
    display: flex;
  }

  .sidebar {
    transform: translateX(0);
  }
  
  .sidebar.collapsed {
    transform: translateX(-100%);
  }
  
  .main-wrapper {
    margin-left: 0;
  }
}

@media (max-width: 768px) {
  .main-content {
    padding: 1rem;
  }
  
  .top-header {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .sidebar.collapsed ~ .main-wrapper {
    margin-left: 0;
  }
  
  .sidebar {
    width: 280px;
  }
  
  .sidebar.collapsed {
    transform: translateX(-100%);
  }
  
  .user-details {
    display: none;
  }
  
  .dropdown-icon {
    margin-left: 0;
  }
  
  .profile-dropdown {
    right: -1rem;
  }
}


</style>
