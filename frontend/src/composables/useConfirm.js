import { reactive } from 'vue';

export const dialogState = reactive({
  isOpen: false,
  title: '',
  message: '',
  type: 'info', // 'danger', 'warning', 'info'
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  onConfirm: null,
  onCancel: null,
  closeOnOverlay: true,
});

export function useConfirm() {
  const confirm = ({
    title = 'Are you sure?',
    message = 'This action cannot be undone.',
    type = 'warning',
    confirmText = 'Confirm',
    cancelText = 'Cancel',
    closeOnOverlay = true,
  } = {}) => {
    return new Promise((resolve) => {
      dialogState.isOpen = true;
      dialogState.title = title;
      dialogState.message = message;
      dialogState.type = type;
      dialogState.confirmText = confirmText;
      dialogState.cancelText = cancelText;
      dialogState.closeOnOverlay = closeOnOverlay;
      
      dialogState.onConfirm = () => {
        dialogState.isOpen = false;
        resolve(true);
      };
      
      dialogState.onCancel = () => {
        dialogState.isOpen = false;
        resolve(false);
      };
    });
  };

  return {
    confirm,
  };
}

export function handleConfirm() {
  if (dialogState.onConfirm) {
    dialogState.onConfirm();
  }
}

export function handleCancel() {
  if (dialogState.onCancel) {
    dialogState.onCancel();
  }
}

export function handleOverlayClick() {
  if (dialogState.closeOnOverlay) {
    handleCancel();
  }
}
