<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">ProjectHub</h1>
                <p class="text-gray-600">Reset your password</p>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700 text-sm">{{ successMessage }}</p>
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

            <!-- Form -->
            <form v-if="!successMessage" @submit.prevent="handleSubmit" class="space-y-5">
                <div>
                    <p class="text-sm text-gray-600 mb-4">
                        Enter your email address and we'll send you a link to reset your password.
                    </p>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input
                        id="email"
                        v-model="email"
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

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="isLoading"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:bg-gray-400 transition"
                >
                    <span v-if="!isLoading">Send Reset Link</span>
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
                        Sending...
                    </span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-6">
                <router-link
                    to="/auth/login"
                    class="block text-center text-sm text-indigo-600 hover:text-indigo-700 font-medium"
                >
                    ← Back to Sign In
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuth } from '../../composables/useAuth';

const { requestPasswordReset, isLoading, error, clearError } = useAuth();

const email = ref('');
const errors = ref({});
const successMessage = ref('');

const handleSubmit = async () => {
    // Clear previous errors
    errors.value = {};
    successMessage.value = '';

    // Basic validation
    if (!email.value) {
        errors.value.email = 'Email is required';
        return;
    }

    try {
        await requestPasswordReset(email.value);
        successMessage.value = 'If an account exists with this email, you will receive a password reset link shortly.';
    } catch (err) {
        // Error is set in the composable
        console.error('Password reset request error:', err);
    }
};
</script>

<style scoped>
input:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
