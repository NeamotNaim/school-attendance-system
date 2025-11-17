<template>
  <div class="notification-bell" v-click-outside="closeDropdown">
    <button @click="toggleDropdown" class="bell-button" :class="{ 'has-unread': unreadCount > 0 }">
      <span class="bell-icon">🔔</span>
      <span v-if="unreadCount > 0" class="badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>

    <transition name="dropdown">
      <div v-if="showDropdown" class="notifications-dropdown">
        <div class="dropdown-header">
          <h3>Notifications</h3>
          <button v-if="notifications.length > 0" @click="markAllAsRead" class="mark-all-read">
            Mark all read
          </button>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading notifications...</p>
        </div>

        <div class="notifications-list" v-else-if="notifications.length > 0">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="notification-item"
            :class="[
              `notification-${notification.color}`,
              { 'unread': !notification.is_read }
            ]"
            @click="handleNotificationClick(notification)"
          >
            <div class="notification-icon">
              {{ notification.icon }}
            </div>
            <div class="notification-content">
              <h4>{{ notification.title }}</h4>
              <p>{{ notification.message }}</p>
              <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
            </div>
            <button @click.stop="deleteNotification(notification.id)" class="delete-btn">
              <span class="delete-icon">×</span>
            </button>
          </div>
        </div>

        <div v-else class="empty-state">
          <span class="empty-icon">🔔</span>
          <p>No notifications</p>
        </div>

        <div class="dropdown-footer" v-if="notifications.length > 0">
          <button @click="clearRead" class="clear-btn">Clear read notifications</button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../services/api';

const showDropdown = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const loading = ref(false);
let pollInterval = null;

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value;
  if (showDropdown.value) {
    fetchNotifications();
  }
};

const closeDropdown = () => {
  showDropdown.value = false;
};

const fetchNotifications = async () => {
  loading.value = true;
  try {
    const response = await api.get('/notifications');
    console.log('Notifications response:', response.data);
    notifications.value = response.data.data || [];
    unreadCount.value = response.data.unread_count || 0;
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
    console.error('Error details:', error.response?.data);
    // If unauthorized, user might not be logged in
    if (error.response?.status === 401) {
      console.warn('User not authenticated');
    }
  } finally {
    loading.value = false;
  }
};

const fetchUnreadCount = async () => {
  try {
    const response = await api.get('/notifications/unread-count');
    unreadCount.value = response.data.count;
  } catch (error) {
    console.error('Failed to fetch unread count:', error);
  }
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await markAsRead(notification.id);
  }
  // You can add navigation logic here based on notification type
};

const markAsRead = async (id) => {
  try {
    await api.post(`/notifications/${id}/read`);
    const notification = notifications.value.find(n => n.id === id);
    if (notification) {
      notification.is_read = true;
    }
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (error) {
    console.error('Failed to mark as read:', error);
  }
};

const markAllAsRead = async () => {
  try {
    await api.post('/notifications/mark-all-read');
    notifications.value.forEach(n => n.is_read = true);
    unreadCount.value = 0;
  } catch (error) {
    console.error('Failed to mark all as read:', error);
  }
};

const deleteNotification = async (id) => {
  try {
    await api.delete(`/notifications/${id}`);
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      const notification = notifications.value[index];
      if (!notification.is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
      notifications.value.splice(index, 1);
    }
  } catch (error) {
    console.error('Failed to delete notification:', error);
  }
};

const clearRead = async () => {
  try {
    console.log('Clearing read notifications...');
    const response = await api.post('/notifications/clear-read');
    console.log('Clear response:', response.data);
    notifications.value = notifications.value.filter(n => !n.is_read);
    console.log('Remaining notifications:', notifications.value.length);
  } catch (error) {
    console.error('Failed to clear read notifications:', error);
    console.error('Error details:', error.response?.data);
  }
};

const formatTime = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000); // seconds

  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  
  return date.toLocaleDateString();
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
  fetchUnreadCount();
  // Poll for new notifications every 30 seconds
  pollInterval = setInterval(fetchUnreadCount, 30000);
});

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval);
  }
});
</script>

<style scoped>
.notification-bell {
  position: relative;
}

.bell-button {
  position: relative;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-tertiary);
  border: 1px solid var(--border-color);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.bell-button:hover {
  background: var(--bg-primary);
  border-color: var(--primary);
}

.bell-button.has-unread {
  animation: ring 2s ease-in-out infinite;
}

@keyframes ring {
  0%, 100% { transform: rotate(0deg); }
  10%, 30% { transform: rotate(-10deg); }
  20%, 40% { transform: rotate(10deg); }
}

.bell-icon {
  font-size: 20px;
  line-height: 1;
  display: block;
}

.badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  background: #ef4444;
  color: white;
  border-radius: 9px;
  font-size: 11px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--bg-secondary);
}

.notifications-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 380px;
  max-height: 500px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-color);
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.dropdown-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
}

.mark-all-read {
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid #4f46e5;
  color: #4f46e5;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.mark-all-read:hover {
  background: #4f46e5;
  color: white;
}

.notifications-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-bottom: 1px solid var(--border-color);
  cursor: pointer;
  transition: background 0.2s ease;
  position: relative;
}

.notification-item:hover {
  background: var(--bg-tertiary);
}

.notification-item.unread {
  background: rgba(79, 70, 229, 0.05);
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: var(--primary);
}

.notification-icon {
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 20px;
}

.notification-green .notification-icon {
  background: rgba(16, 185, 129, 0.1);
}

.notification-blue .notification-icon {
  background: rgba(59, 130, 246, 0.1);
}

.notification-yellow .notification-icon {
  background: rgba(245, 158, 11, 0.1);
}

.notification-red .notification-icon {
  background: rgba(239, 68, 68, 0.1);
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-content h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
}

.notification-content p {
  margin: 0 0 0.5rem 0;
  font-size: 0.8125rem;
  color: var(--text-secondary);
  line-height: 1.4;
}

.notification-time {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.delete-btn {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  border-radius: 4px;
  transition: all 0.2s ease;
  opacity: 0;
}

.notification-item:hover .delete-btn {
  opacity: 1;
}

.delete-btn:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}

.delete-icon {
  font-size: 20px;
  line-height: 1;
  font-weight: 300;
}

.loading-state {
  padding: 3rem 1rem;
  text-align: center;
  color: var(--text-secondary);
}

.loading-state .spinner {
  width: 32px;
  height: 32px;
  border: 3px solid var(--border-color);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state p {
  margin: 0;
  font-size: 0.9375rem;
}

.empty-state {
  padding: 3rem 1rem;
  text-align: center;
  color: var(--text-secondary);
}

.empty-icon {
  font-size: 48px;
  display: block;
  margin: 0 auto 1rem;
  opacity: 0.3;
}

.empty-state p {
  margin: 0;
  font-size: 0.9375rem;
}

.dropdown-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--border-color);
  text-align: center;
}

.clear-btn {
  padding: 0.5rem 1rem;
  background: transparent;
  border: 1px solid var(--border-color);
  color: var(--text-secondary);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.clear-btn:hover {
  background: var(--bg-tertiary);
  border-color: var(--text-secondary);
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

@media (max-width: 640px) {
  .notifications-dropdown {
    width: calc(100vw - 2rem);
    right: -1rem;
  }
}
</style>
