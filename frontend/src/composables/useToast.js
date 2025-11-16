import { reactive } from 'vue';

export const toastState = reactive({
  toasts: [],
});

let toastId = 0;

export function useToast() {
  const addToast = (message, type = 'info', title = '', duration = 4000) => {
    const id = toastId++;
    const toast = {
      id,
      message,
      type,
      title,
    };

    toastState.toasts.push(toast);

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }

    return id;
  };

  const success = (message, title = 'Success', duration = 4000) => {
    return addToast(message, 'success', title, duration);
  };

  const error = (message, title = 'Error', duration = 5000) => {
    return addToast(message, 'error', title, duration);
  };

  const warning = (message, title = 'Warning', duration = 4000) => {
    return addToast(message, 'warning', title, duration);
  };

  const info = (message, title = '', duration = 4000) => {
    return addToast(message, 'info', title, duration);
  };

  return {
    success,
    error,
    warning,
    info,
    addToast,
  };
}

export function removeToast(id) {
  const index = toastState.toasts.findIndex((toast) => toast.id === id);
  if (index > -1) {
    toastState.toasts.splice(index, 1);
  }
}
