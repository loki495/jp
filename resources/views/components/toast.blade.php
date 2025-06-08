<div
  x-data="toastHandler()"
  x-show="visible"
  x-transition
  x-cloak
  class="fixed top-4 right-4 z-50 w-80 rounded-lg shadow-lg px-4 py-3 text-white text-sm"
  :class="{
    'bg-green-600': variant === 'success',
    'bg-red-600': variant === 'danger',
    'bg-blue-600': variant === 'info',
    'bg-yellow-500': variant === 'warning'
  }"
  x-text="message"
></div>

<script>
  function toastHandler() {
    return {
      visible: false,
      message: '',
      variant: 'success',
      timeout: null,

      show(msg, type = 'success', duration = 3000) {
        this.message = msg;
        this.variant = type;
        this.visible = true;
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.visible = false, duration);
      }
    };
  }

  // Global helper to trigger toasts from anywhere
  window.Toast = (msg, type = 'success', duration = 3000) => {
    const toast = document.querySelector('[x-data="toastHandler()"]')?._x_dataStack?.[0];
    if (toast) toast.show(msg, type, duration);
  };
</script>
