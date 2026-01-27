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
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
        'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
</style>
