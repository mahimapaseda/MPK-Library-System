<template>
  <Teleport to="body">
    <transition name="fade">
      <div v-if="isVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <transition name="scale">
          <div v-if="isVisible" class="glass-card dark:glass-card max-w-md w-full p-6 rounded-lg shadow-xl">
            <!-- Icon/Header -->
            <div class="flex items-center gap-4 mb-4">
              <div
                :class="[
                  'shrink-0 w-12 h-12 rounded-full flex items-center justify-center',
                  typeStyles[type].bg
                ]"
              >
                <svg v-if="type === 'warning'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 0v2m0-12v2m0 4v2" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.268 0 .526.035.777.102.693.187 1.289.603 1.707 1.28m-3.968-1.38A8.002 8.002 0 0 0 4 14c0 4.418 3.582 8 8 8s8-3.582 8-8-3.582-8-8-8z" />
                </svg>
                <svg v-else-if="type === 'error'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ title }}</h3>
            </div>

            <!-- Message -->
            <p class="text-sm text-slate-600 dark:text-slate-300 mb-6">{{ message }}</p>

            <!-- Buttons -->
            <div class="flex gap-3 justify-end">
              <button
                v-if="showCancel"
                @click="cancel"
                class="px-4 py-2 rounded-lg font-medium transition-all
                  text-slate-700 dark:text-slate-300
                  bg-slate-200 dark:bg-slate-700
                  hover:bg-slate-300 dark:hover:bg-slate-600
                  active:scale-95"
              >
                Cancel
              </button>
              <button
                @click="confirm"
                :class="[
                  'px-4 py-2 rounded-lg font-medium transition-all active:scale-95',
                  typeStyles[type].button
                ]"
              >
                {{ confirmText }}
              </button>
            </div>
          </div>
        </transition>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref } from 'vue';

const isVisible = ref(false);
const title = ref('Confirm');
const message = ref('Are you sure?');
const type = ref('warning'); // 'warning', 'error', 'success', 'info'
const showCancel = ref(true);
const confirmText = ref('Confirm');

let resolveCallback = null;

const typeStyles = {
  warning: {
    bg: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    button: 'bg-amber-600 hover:bg-amber-700 dark:bg-amber-600 dark:hover:bg-amber-700 text-white'
  },
  error: {
    bg: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300',
    button: 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-700 text-white'
  },
  success: {
    bg: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
    button: 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white'
  },
  info: {
    bg: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    button: 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white'
  }
};

const showAlert = (options = {}) => {
  return new Promise((resolve) => {
    title.value = options.title || 'Confirm';
    message.value = options.message || 'Are you sure?';
    type.value = options.type || 'warning';
    showCancel.value = options.showCancel !== false;
    confirmText.value = options.confirmText || 'Confirm';

    resolveCallback = resolve;
    isVisible.value = true;
  });
};

const confirm = () => {
  isVisible.value = false;
  if (resolveCallback) {
    resolveCallback(true);
    resolveCallback = null;
  }
};

const cancel = () => {
  isVisible.value = false;
  if (resolveCallback) {
    resolveCallback(false);
    resolveCallback = null;
  }
};

// Allow closing with Escape key
const handleKeydown = (e) => {
  if (e.key === 'Escape' && isVisible.value) {
    cancel();
  }
};

if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleKeydown);
}

defineExpose({
  showAlert
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
  transition: all 0.2s ease;
}

.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>
