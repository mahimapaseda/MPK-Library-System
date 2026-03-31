import { ref } from 'vue';

let alertComponentRef = null;

export function useAlert() {
  const setAlertComponent = (component) => {
    alertComponentRef = component;
  };

  const alert = (message, options = {}) => {
    if (!alertComponentRef) {
      console.warn('AlertDialog component not mounted');
      return Promise.reject('AlertDialog not available');
    }

    return alertComponentRef.showAlert({
      title: options.title || 'Alert',
      message,
      type: options.type || 'info',
      showCancel: false,
      confirmText: 'OK',
      ...options
    });
  };

  const confirm = (message, options = {}) => {
    if (!alertComponentRef) {
      console.warn('AlertDialog component not mounted');
      // Fallback to browser confirm for safety
      return Promise.resolve(window.confirm(message));
    }

    return alertComponentRef.showAlert({
      title: options.title || 'Confirm',
      message,
      type: options.type || 'warning',
      showCancel: true,
      confirmText: options.confirmText || 'Confirm',
      ...options
    });
  };

  return {
    alert,
    confirm,
    setAlertComponent
  };
}
