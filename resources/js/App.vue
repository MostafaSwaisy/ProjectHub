<template>
    <div id="app">
        <router-view />
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuth } from './composables/useAuth';

const { fetchCurrentUser, token } = useAuth();

// Try to restore user session on mount
onMounted(async () => {
    if (token.value) {
        try {
            await fetchCurrentUser();
        } catch (err) {
            console.error('Failed to restore user session:', err);
        }
    }
});
</script>

<style>
/* T018: Global styles with dark theme from design system */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html,
body,
#app {
    width: 100%;
    height: 100%;
    background-color: var(--black-primary);
    color: var(--text-primary);
    font-family: var(--font-family);
    font-size: var(--text-base);
    line-height: var(--line-height-base);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    overflow-x: hidden;
}

/* Ensure app container fills viewport */
#app {
    display: flex;
    flex-direction: column;
}
</style>
