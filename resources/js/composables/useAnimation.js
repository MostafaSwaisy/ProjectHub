/* useAnimation.js - Composable for animation utilities and accessibility */
/* Phase 2: Foundational - T016 */

import { ref, onMounted, onUnmounted } from 'vue'

/**
 * Composable for animation utilities
 * Detects prefers-reduced-motion and provides animation helpers
 */
export function useAnimation() {
  const prefersReducedMotion = ref(false)

  /**
   * Initialize reduced motion detection
   */
  const initReducedMotionDetection = () => {
    if (typeof window === 'undefined') return

    // Check initial preference
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)')
    prefersReducedMotion.value = mediaQuery.matches

    // Listen for changes
    const handleChange = (event) => {
      prefersReducedMotion.value = event.matches
    }

    mediaQuery.addEventListener('change', handleChange)

    return () => mediaQuery.removeEventListener('change', handleChange)
  }

  /**
   * Determine if animation should play
   * @param {string} type - 'continuous' or 'triggered'
   * @returns {boolean}
   */
  const shouldPlayAnimation = (type = 'triggered') => {
    if (prefersReducedMotion.value) return false

    // For continuous animations (like floating orbs), be more conservative
    if (type === 'continuous') {
      return !prefersReducedMotion.value
    }

    return true
  }

  /**
   * Get appropriate transition duration
   * @param {'fast' | 'normal' | 'slow'} type
   * @returns {string}
   */
  const getTransitionDuration = (type = 'normal') => {
    if (prefersReducedMotion.value) return '0.01ms'

    const durations = {
      fast: 'var(--transition-fast)',
      normal: 'var(--transition-normal)',
      slow: 'var(--transition-slow)'
    }

    return durations[type] || durations.normal
  }

  /**
   * Delay execution to allow for layout recalculation
   */
  const delayFrame = (callback) => {
    requestAnimationFrame(() => {
      requestAnimationFrame(callback)
    })
  }

  /**
   * Animate element with optional callback
   */
  const animateElement = (element, keyframes, options = {}) => {
    if (!element || prefersReducedMotion.value) {
      return Promise.resolve()
    }

    return element.animate(keyframes, {
      duration: 300,
      ...options
    }).finished
  }

  // Initialize on mount
  onMounted(() => {
    const cleanup = initReducedMotionDetection()

    onUnmounted(() => {
      if (cleanup) cleanup()
    })
  })

  return {
    prefersReducedMotion,
    shouldPlayAnimation,
    getTransitionDuration,
    delayFrame,
    animateElement
  }
}

/**
 * Simple helper to check if user prefers reduced motion
 */
export const checkReducedMotion = () => {
  if (typeof window === 'undefined') return false
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

/**
 * Apply reduced motion styles to an element
 */
export const applyReducedMotionStyles = (element) => {
  if (!element) return

  if (checkReducedMotion()) {
    element.style.animationDuration = '0.01ms'
    element.style.transitionDuration = '0.01ms'
  }
}
