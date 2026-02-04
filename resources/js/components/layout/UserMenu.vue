<template>
    <!-- T020-T021: User Menu Dropdown Component -->
    <div class="user-menu" ref="menuRef">
        <!-- User Avatar Button -->
        <button class="user-avatar-btn" @click="toggleMenu" :aria-expanded="isOpen">
            <div class="avatar" :style="{ backgroundColor: getBgColor(user?.id || 0) }">
                <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="user?.name" class="avatar-img" />
                <span v-else class="avatar-initials">{{ getInitials(user?.name || 'User') }}</span>
            </div>
            <span class="user-name">{{ user?.name || 'User' }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="chevron-icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition name="dropdown">
            <div v-if="isOpen" class="dropdown-menu">
                <!-- User Info Header -->
                <div class="dropdown-header">
                    <div class="user-info">
                        <p class="user-name-text">{{ user?.name }}</p>
                        <p class="user-email">{{ user?.email }}</p>
                    </div>
                </div>

                <div class="dropdown-divider"></div>

                <!-- Menu Items -->
                <nav class="dropdown-nav">
                    <button class="menu-item" @click="navigateTo('/profile')" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="menu-icon">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                        <span>Profile</span>
                    </button>

                    <button class="menu-item" @click="navigateTo('/settings')" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="menu-icon">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                        </svg>
                        <span>Settings</span>
                    </button>

                    <button class="menu-item" @click="navigateTo('/preferences')" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="menu-icon">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                            />
                        </svg>
                        <span>Preferences</span>
                    </button>
                </nav>

                <div class="dropdown-divider"></div>

                <!-- Logout Button -->
                <button class="menu-item logout-btn" @click="handleLogout" :disabled="isLoggingOut">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="menu-icon">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                    </svg>
                    <span>{{ isLoggingOut ? 'Logging out...' : 'Logout' }}</span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { getInitials, getBgColor } from '../../composables/useAvatar'

const router = useRouter()
const { user, logout } = useAuth()

const isOpen = ref(false)
const isLoggingOut = ref(false)
const menuRef = ref(null)

// Toggle menu dropdown
const toggleMenu = () => {
    isOpen.value = !isOpen.value
}

// Navigate to route and close menu
const navigateTo = (path) => {
    isOpen.value = false
    router.push(path)
}

// T021: Handle logout
const handleLogout = async () => {
    isLoggingOut.value = true
    isOpen.value = false

    try {
        await logout()
        // Redirect to login page
        router.push('/auth/login')
    } catch (error) {
        console.error('Logout failed:', error)
    } finally {
        isLoggingOut.value = false
    }
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        isOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.user-menu {
    position: relative;
}

/* User Avatar Button */
.user-avatar-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.375rem 0.75rem 0.375rem 0.375rem;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 9999px;
    color: #f8fafc;
    cursor: pointer;
    transition: all 200ms ease-out;
}

.user-avatar-btn:hover {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(148, 163, 184, 0.3);
}

.avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    flex-shrink: 0;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-initials {
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 500;
}

.chevron-icon {
    width: 1rem;
    height: 1rem;
    color: #94a3b8;
}

@media (max-width: 640px) {
    .user-name {
        display: none;
    }
}

/* Dropdown Menu */
.dropdown-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 16rem;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.75rem;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    z-index: 50;
}

.dropdown-header {
    padding: 1rem;
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-name-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #f8fafc;
}

.user-email {
    font-size: 0.75rem;
    color: #94a3b8;
}

.dropdown-divider {
    height: 1px;
    background: rgba(148, 163, 184, 0.2);
    margin: 0.5rem 0;
}

.dropdown-nav {
    padding: 0.5rem;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.625rem 0.75rem;
    background: transparent;
    border: none;
    border-radius: 0.5rem;
    color: #cbd5e1;
    font-size: 0.875rem;
    text-align: left;
    cursor: pointer;
    transition: all 200ms ease-out;
}

.menu-item:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}

.menu-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.menu-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.logout-btn {
    color: #f87171;
}

.logout-btn:hover:not(:disabled) {
    background: rgba(248, 113, 113, 0.1);
    color: #fca5a5;
}

/* Dropdown Transition */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 200ms ease-out, transform 200ms ease-out;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-0.5rem);
}

.dropdown-enter-to,
.dropdown-leave-from {
    opacity: 1;
    transform: translateY(0);
}
</style>
