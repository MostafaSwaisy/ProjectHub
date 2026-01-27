<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">ProjectHub</h1>
                <p class="text-gray-600">Sign in to your account</p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 text-sm">{{ error }}</p>
                <button
                    @click="clearError"
                    class="text-red-600 text-xs mt-2 hover:underline"
                >
                    Dismiss
                </button>
            </div>

            <!-- Login Form -->
            <form @submit.prevent="handleLogin" class="space-y-5">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="you@example.com"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        :disabled="isLoading"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                        {{ errors.email }}
                    </p>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <router-link
                            to="/auth/forgot-password"
                            class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
                        >
                            Forgot?
                        </router-link>
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        :disabled="isLoading"
                    />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                        {{ errors.password }}
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:bg-gray-400 transition"
                >
                    <span v-if="!isLoading">Sign In</span>
                    <span v-else class="flex items-center justify-center">
                        <svg
                            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            />
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            />
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Don't have an account?</span>
                </div>
            </div>

            <!-- Register Link -->
            <router-link
                to="/auth/register"
                class="block w-full text-center py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition"
            >
                Create Account
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../composables/useAuth';

const router = useRouter();
const { login, isLoading, error, clearError } = useAuth();

const form = ref({
    email: '',
    password: '',
});

const errors = ref({});

const handleLogin = async () => {
    // Clear previous errors
    errors.value = {};

    // Basic validation
    if (!form.value.email) {
        errors.value.email = 'Email is required';
    }
    if (!form.value.password) {
        errors.value.password = 'Password is required';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    try {
        await login(form.value);
        // Redirect to dashboard on successful login
        router.push('/dashboard');
    } catch (err) {
        // Error is set in the composable
        console.error('Login error:', err);
    }
};
</script>

<style scoped>
input:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
