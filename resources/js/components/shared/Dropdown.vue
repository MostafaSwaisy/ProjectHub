<template>
  <div class="dropdown" @click.stop>
    <!-- Trigger -->
    <div class="dropdown-trigger" @click="toggleDropdown">
      <slot name="trigger" />
    </div>

    <!-- Menu -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        :class="['dropdown-menu', `dropdown-${position}`]"
        @click.stop
      >
        <slot />
      </div>
    </Transition>
  </div>

  <!-- Click outside handler -->
  <Teleport to="body" v-if="isOpen">
    <div class="dropdown-backdrop" @click="closeDropdown" />
  </Teleport>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  position: {
    type: String,
    default: 'bottom-right',
    validator: (value) =>
      ['bottom-left', 'bottom-right', 'top-left', 'top-right'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['open', 'close'])

const isOpen = ref(false)

const toggleDropdown = () => {
  if (!props.disabled) {
    isOpen.value ? closeDropdown() : openDropdown()
  }
}

const openDropdown = () => {
  isOpen.value = true
  emit('open')
}

const closeDropdown = () => {
  isOpen.value = false
  emit('close')
}

// Handle keydown outside
const handleKeydown = (event) => {
  if (event.key === 'Escape' && isOpen.value) {
    closeDropdown()
  }
}

// Add/remove event listeners
if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleKeydown)
}
</script>

<style scoped>
.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-trigger {
  cursor: pointer;
}

.dropdown-menu {
  position: absolute;
  background: rgba(21, 21, 21, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  z-index: var(--z-dropdown);
  min-width: 200px;
  padding: var(--spacing-sm) 0;
  animation: dropdownIn 0.2s ease-out;
}

/* Position variants */
.dropdown-bottom-left {
  bottom: auto;
  left: 0;
  top: calc(100% + 8px);
}

.dropdown-bottom-right {
  bottom: auto;
  right: 0;
  top: calc(100% + 8px);
}

.dropdown-top-left {
  top: auto;
  left: 0;
  bottom: calc(100% + 8px);
}

.dropdown-top-right {
  top: auto;
  right: 0;
  bottom: calc(100% + 8px);
}

.dropdown-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: calc(var(--z-dropdown) - 1);
}

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all var(--transition-normal);
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

@keyframes dropdownIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
