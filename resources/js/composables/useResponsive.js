/* useResponsive.js - Composable for responsive breakpoint detection */
/* Phase 2: Foundational - T017 */

import { ref, computed, onMounted, onUnmounted } from 'vue'

/**
 * Composable for responsive breakpoint detection
 * Syncs with CSS variables from design-system.css
 */
export function useResponsive() {
  const screenWidth = ref(0)

  /**
   * Breakpoint definitions (must match CSS variables in design-system.css)
   */
  const breakpoints = {
    mobile: 640,
    tablet: 768,
    desktop: 1024,
    wide: 1280
  }

  /**
   * Initialize window size tracking
   */
  const initResizeListener = () => {
    const handleResize = () => {
      screenWidth.value = window.innerWidth
    }

    // Set initial width
    handleResize()

    // Listen for window resize
    window.addEventListener('resize', handleResize)

    return () => window.removeEventListener('resize', handleResize)
  }

  /**
   * Computed properties for breakpoint detection
   */
  const isMobile = computed(() => screenWidth.value < breakpoints.tablet)
  const isTablet = computed(() => {
    return screenWidth.value >= breakpoints.tablet && screenWidth.value < breakpoints.desktop
  })
  const isDesktop = computed(() => screenWidth.value >= breakpoints.desktop)
  const isWide = computed(() => screenWidth.value >= breakpoints.wide)

  /**
   * Current breakpoint name
   */
  const breakpoint = computed(() => {
    if (isMobile.value) return 'mobile'
    if (isTablet.value) return 'tablet'
    if (isWide.value) return 'wide'
    return 'desktop'
  })

  /**
   * Device type detection
   */
  const isTouchDevice = computed(() => {
    if (typeof window === 'undefined') return false
    return (
      window.matchMedia('(hover: none)').matches ||
      window.matchMedia('(pointer: coarse)').matches
    )
  })

  /**
   * Orientation detection
   */
  const isPortrait = computed(() => {
    return screenWidth.value < window.innerHeight
  })

  const isLandscape = computed(() => {
    return screenWidth.value >= window.innerHeight
  })

  /**
   * Media query helpers
   */
  const matches = (query) => {
    if (typeof window === 'undefined') return false
    return window.matchMedia(query).matches
  }

  const supportsBackdropFilter = computed(() => {
    if (typeof window === 'undefined') return false
    const element = document.createElement('div')
    element.style.backdropFilter = 'blur(10px)'
    return element.style.backdropFilter !== ''
  })

  // Initialize on mount
  onMounted(() => {
    const cleanup = initResizeListener()

    onUnmounted(() => {
      if (cleanup) cleanup()
    })
  })

  return {
    screenWidth,
    breakpoints,
    isMobile,
    isTablet,
    isDesktop,
    isWide,
    breakpoint,
    isTouchDevice,
    isPortrait,
    isLandscape,
    matches,
    supportsBackdropFilter
  }
}

/**
 * Utility function for media query matching
 */
export const useMediaQuery = (query) => {
  const matches = ref(false)

  const handleChange = (e) => {
    matches.value = e.matches
  }

  onMounted(() => {
    if (typeof window === 'undefined') return

    const mediaQuery = window.matchMedia(query)
    matches.value = mediaQuery.matches

    mediaQuery.addEventListener('change', handleChange)

    onUnmounted(() => {
      mediaQuery.removeEventListener('change', handleChange)
    })
  })

  return matches
}

/**
 * Get current viewport dimensions
 */
export const getViewportSize = () => {
  if (typeof window === 'undefined') {
    return { width: 0, height: 0 }
  }

  return {
    width: window.innerWidth,
    height: window.innerHeight
  }
}

/**
 * Debounced window resize listener utility
 */
export const onWindowResize = (callback, delay = 150) => {
  let timeout

  const handleResize = () => {
    clearTimeout(timeout)
    timeout = setTimeout(callback, delay)
  }

  if (typeof window !== 'undefined') {
    window.addEventListener('resize', handleResize)

    return () => {
      window.removeEventListener('resize', handleResize)
      clearTimeout(timeout)
    }
  }

  return () => {}
}
