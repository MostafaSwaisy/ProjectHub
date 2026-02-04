<template>
  <div class="animated-background">
    <!-- Orbs container -->
    <div class="orbs-container">
      <div
        v-for="(color, index) in colors"
        :key="index"
        class="orb"
        :style="{ backgroundColor: color }"
      />
    </div>

    <!-- Animated gradient background -->
    <div class="bg-gradient" />

    <!-- Content slot (optional) -->
    <slot />
  </div>
</template>

<script setup>
defineProps({
  orbCount: {
    type: Number,
    default: 3
  },
  colors: {
    type: Array,
    default: () => ['#FF6B35', '#2563EB', '#8B5CF6']
  }
})
</script>

<style scoped>
.animated-background {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
  background-color: var(--black-primary);
}

.orbs-container {
  position: absolute;
  inset: 0;
  overflow: hidden;
}

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0.8;
  will-change: transform;
}

/* Orb positioning */
.orb:nth-child(1) {
  width: 300px;
  height: 300px;
  top: -100px;
  left: -100px;
  animation: orbFloat 15s ease-in-out infinite;
}

.orb:nth-child(2) {
  width: 400px;
  height: 400px;
  bottom: -150px;
  right: -150px;
  animation: orbFloatAlt 20s ease-in-out infinite;
  animation-delay: -5s;
}

.orb:nth-child(3) {
  width: 350px;
  height: 350px;
  top: 50%;
  right: -50px;
  transform: translateY(-50%);
  animation: orbFloatAlt2 18s ease-in-out infinite;
  animation-delay: -10s;
}

/* Keyframe animations */
@keyframes orbFloat {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(30px, -30px) scale(1.05);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.95);
  }
}

@keyframes orbFloatAlt {
  0%, 100% {
    transform: translate(0, 0) scale(1);
  }
  33% {
    transform: translate(-40px, 20px) scale(0.9);
  }
  66% {
    transform: translate(25px, -25px) scale(1.1);
  }
}

@keyframes orbFloatAlt2 {
  0%, 100% {
    transform: translateY(-50%) translate(0, 0) scale(1);
  }
  33% {
    transform: translateY(-50%) translate(20px, 35px) scale(1.05);
  }
  66% {
    transform: translateY(-50%) translate(-30px, -15px) scale(0.95);
  }
}

.bg-gradient {
  position: absolute;
  inset: 0;
  background: var(--gradient-dark);
  opacity: 0.5;
  pointer-events: none;
}

/* Respect reduced motion preference */
@media (prefers-reduced-motion: reduce) {
  .orb {
    animation: none;
    opacity: 0.3;
  }
}
</style>
