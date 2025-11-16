<template>
  <teleport to="body">
    <transition name="dialog">
      <div v-if="dialogState.isOpen" class="dialog-overlay" @click="handleOverlayClick">
        <div class="dialog-container" @click.stop>
          <div class="dialog-header">
            <div class="dialog-icon" :class="`dialog-icon-${dialogState.type}`">
              <svg v-if="dialogState.type === 'danger'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
              </svg>
              <svg v-else-if="dialogState.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
              </svg>
              <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
            </div>
          </div>
          
          <div class="dialog-content">
            <h3 class="dialog-title">{{ dialogState.title }}</h3>
            <p class="dialog-message">{{ dialogState.message }}</p>
          </div>
          
          <div class="dialog-actions">
            <button @click="handleCancel" class="dialog-btn dialog-btn-cancel">
              {{ dialogState.cancelText }}
            </button>
            <button @click="handleConfirm" :class="['dialog-btn', 'dialog-btn-confirm', `dialog-btn-${dialogState.type}`]">
              {{ dialogState.confirmText }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { dialogState, handleConfirm, handleCancel, handleOverlayClick } from '../composables/useConfirm';
</script>

<style scoped>
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 1rem;
}

.dialog-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 420px;
  width: 100%;
  overflow: hidden;
}

.dialog-header {
  padding: 1.5rem 1.5rem 0 1.5rem;
  display: flex;
  justify-content: center;
}

.dialog-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.dialog-icon svg {
  width: 28px;
  height: 28px;
}

.dialog-icon-danger {
  background: rgba(239, 68, 68, 0.1);
}

.dialog-icon-danger svg {
  color: #ef4444;
}

.dialog-icon-warning {
  background: rgba(245, 158, 11, 0.1);
}

.dialog-icon-warning svg {
  color: #f59e0b;
}

.dialog-icon-info {
  background: rgba(59, 130, 246, 0.1);
}

.dialog-icon-info svg {
  color: #3b82f6;
}

.dialog-content {
  padding: 1.5rem;
  text-align: center;
}

.dialog-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.dialog-message {
  margin: 0;
  font-size: 0.9375rem;
  color: #64748b;
  line-height: 1.6;
}

.dialog-actions {
  padding: 0 1.5rem 1.5rem 1.5rem;
  display: flex;
  gap: 0.75rem;
}

.dialog-btn {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.dialog-btn-cancel {
  background: #f1f5f9;
  color: #64748b;
}

.dialog-btn-cancel:hover {
  background: #e2e8f0;
  color: #475569;
}

.dialog-btn-confirm {
  color: white;
}

.dialog-btn-danger {
  background: #ef4444;
}

.dialog-btn-danger:hover {
  background: #dc2626;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.dialog-btn-warning {
  background: #f59e0b;
}

.dialog-btn-warning:hover {
  background: #d97706;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.dialog-btn-info {
  background: #3b82f6;
}

.dialog-btn-info:hover {
  background: #2563eb;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Animations */
.dialog-enter-active,
.dialog-leave-active {
  transition: opacity 0.3s ease;
}

.dialog-enter-active .dialog-container,
.dialog-leave-active .dialog-container {
  transition: all 0.3s ease;
}

.dialog-enter-from,
.dialog-leave-to {
  opacity: 0;
}

.dialog-enter-from .dialog-container,
.dialog-leave-to .dialog-container {
  transform: scale(0.95) translateY(-20px);
}

@media (max-width: 640px) {
  .dialog-actions {
    flex-direction: column-reverse;
  }
  
  .dialog-btn {
    width: 100%;
  }
}
</style>
