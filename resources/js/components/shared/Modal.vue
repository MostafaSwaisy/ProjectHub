<template>
  <Teleport to="body" v-if="isOpen">
    <!-- Backdrop -->
    <Transition name="modal-backdrop">
      <div
        v-if="isOpen"
        class="modal-backdrop"
        @click="closeOnClickOutside && handleClose()"
      />
    </Transition>

    <!-- Modal -->
    <Transition name="modal">
      <div v-if="isOpen" class="modal-overlay">
        <div :class="['modal', `modal-${size}`]" @click.stop>
          <!-- Header -->
          <div class="modal-header">
            <h2 v-if="title" class="modal-title">{{ title }}</h2>
            <slot v-else name="header" />
            <button
              v-if="showClose && !persistent"
              class="modal-close"
              @click="handleClose"
              aria-label="Close modal"
            >
              ✕
            </button>
          </div>

          <!-- Content -->
          <div class="modal-content">
            <slot />
          </div>

          <!-- Footer -->
          <div v-if="$slots.footer" class="modal-footer">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  closeOnClickOutside: {
    type: Boolean,
    default: true
  },
  closeOnEsc: {
    type: Boolean,
    default: true
  },
  showClose: {
    type: Boolean,
    default: true
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'update:isOpen'])

const handleClose = () => {
  if (!props.persistent) {
    emit('update:isOpen', false)
    emit('close')
  }
}

// Handle ESC key
const handleKeydown = (event) => {
  if (event.key === 'Escape' && props.isOpen && props.closeOnEsc && !props.persistent) {
    handleClose()
  }
}

// Add/remove keydown listener
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen) {
      document.addEventListener('keydown', handleKeydown)
      document.body.style.overflow = 'hidden'
    } else {
      document.removeEventListener('keydown', handleKeydown)
      document.body.style.overflow = ''
    }
  }
)
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(8px);
  z-index: calc(var(--z-modal) - 1);
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: var(--z-modal);
  padding: var(--spacing-md);
  overflow-y: auto;
}

.modal {
  background: rgba(21, 21, 21, 0.9);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
  width: 100%;
  animation: modalSlideIn 0.3s ease-out;
}

/* Size variants */
.modal-sm {
  max-width: 400px;
}

.modal-md {
  max-width: 600px;
}

.modal-lg {
  max-width: 800px;
}

.modal-xl {
  max-width: 1000px;
}

.modal-full {
  max-width: 90vw;
  max-height: 90vh;
}

/* Modal sections */
.modal-header {
  padding: var(--spacing-lg);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-md);
}

.modal-title {
  font-size: var(--text-xl);
  font-weight: var(--font-semibold);
  color: var(--text-primary);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: var(--text-secondary);
  font-size: var(--text-xl);
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-md);
  transition: all var(--transition-normal);
}

.modal-close:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: var(--text-primary);
}

.modal-content {
  padding: var(--spacing-lg);
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  padding: var(--spacing-lg);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: flex-end;
  gap: var(--spacing-md);
}

/* Animations */
.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
  transition: all var(--transition-normal);
}

.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
  opacity: 0;
  backdrop-filter: blur(0px);
}

.modal-enter-active,
.modal-leave-active {
  transition: all var(--transition-normal);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(20px);
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Mobile styles */
@media (max-width: 639px) {
  .modal {
    width: 100%;
    max-width: none;
    border-radius: var(--radius-lg);
    max-height: 90vh;
  }

  .modal-sm,
  .modal-md,
  .modal-lg,
  .modal-xl {
    max-width: none;
  }

  .modal-overlay {
    padding: 0;
    align-items: flex-end;
  }

  .modal-header,
  .modal-content,
  .modal-footer {
    padding: var(--spacing-md);
  }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
  .modal,
  .modal-backdrop {
    animation: none;
    transition: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: more) {
  .modal {
    border-width: 2px;
  }
}
</style>
