<template>
    <!-- T034: NotFound page with design system colors and dark theme -->
    <div class="notfound-container">
        <div class="notfound-content">
            <h1 class="notfound-code">404</h1>
            <p class="notfound-message">Page not found</p>
            <p class="notfound-description">
                The page you're looking for doesn't exist or has been moved.
            </p>
            <router-link to="/" class="home-button">
                ← Go Home
            </router-link>
        </div>
    </div>
</template>

<script setup>
// 404 Not Found component
</script>

<style scoped>
/* T034: Design system consistency - Not Found page */

.notfound-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: var(--black-primary);
    padding: var(--spacing-lg);
}

.notfound-content {
    text-align: center;
    max-width: 500px;
}

.notfound-code {
    font-size: 6rem;
    font-weight: var(--font-bold);
    color: var(--orange-primary);
    margin: 0 0 var(--spacing-md) 0;
    line-height: 1;
}

.notfound-message {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin: 0 0 var(--spacing-md) 0;
}

.notfound-description {
    font-size: var(--text-base);
    color: var(--text-secondary);
    margin: 0 0 var(--spacing-2xl) 0;
    line-height: var(--line-height-relaxed);
}

.home-button {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    background-color: var(--orange-primary);
    color: white;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-weight: var(--font-medium);
    transition: all var(--transition-normal);
    margin-top: var(--spacing-lg);
}

.home-button:hover {
    background-color: var(--orange-light);
    box-shadow: var(--shadow-orange);
    transform: translateY(-2px);
}

.home-button:active {
    transform: translateY(0);
}

/* Mobile responsive */
@media (max-width: 639px) {
    .notfound-code {
        font-size: 4rem;
    }

    .notfound-message {
        font-size: var(--text-xl);
    }
}
</style>
