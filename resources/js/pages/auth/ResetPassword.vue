<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">ProjectHub</h1>
                <p class="text-gray-600">Set your new password</p>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700 text-sm mb-3">{{ successMessage }}</p>
                <router-link
                    to="/auth/login"
                    class="text-green-600 text-sm hover:underline font-medium"
                >
                    Go to Sign In →
                </router-link>
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
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        New Password
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="••••••••"
                        required
                        @input="validatePassword"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        :disabled="isLoading"
                    />
                    <p class="mt-1 text-xs text-gray-600">
                        Password requirements:
                    </p>
                    <ul class="mt-2 text-xs space-y-1">
                        <li :class="[
                            passwordRequirements.minLength ? 'text-green-600' : 'text-red-600'
                        ]">
                            ✓ At least 8 characters
                        </li>
                        <li :class="[
                            passwordRequirements.hasNumber ? 'text-green-600' : 'text-red-600'
                        ]">
                            ✓ At least one number
                        </li>
                        <li :class="[
                            passwordRequirements.hasLetter ? 'text-green-600' : 'text-red-600'
                        ]">
                            ✓ At least one letter
                        </li>
                    </ul>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirm Password
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        placeholder="••••••••"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        :disabled="isLoading"
                    />
                    <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">
                        {{ errors.password_confirmation }}
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="isLoading || !isPasswordValid"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:bg-gray-400 transition"
                >
                    <span v-if="!isLoading">Reset Password</span>
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
                        Resetting...
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
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '../../composables/useAuth';

const route = useRoute();
const { resetPassword, isLoading, error, clearError } = useAuth();

const form = ref({
    token: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});
const successMessage = ref('');

const passwordRequirements = ref({
    minLength: false,
    hasNumber: false,
    hasLetter: false,
});

const isPasswordValid = computed(() => {
    return (
        passwordRequirements.value.minLength &&
        passwordRequirements.value.hasNumber &&
        passwordRequirements.value.hasLetter &&
        form.value.password === form.value.password_confirmation &&
        form.value.password.length > 0
    );
});

const validatePassword = () => {
    const password = form.value.password;
    passwordRequirements.value.minLength = password.length >= 8;
    passwordRequirements.value.hasNumber = /[0-9]/.test(password);
    passwordRequirements.value.hasLetter = /[a-zA-Z]/.test(password);
};

onMounted(() => {
    // Get token and email from query parameters
    form.value.token = route.query.token || '';
    form.value.email = route.query.email || '';

    if (!form.value.token || !form.value.email) {
        error.value = 'Invalid reset link. Please request a new password reset.';
    }
});

const handleSubmit = async () => {
    // Clear previous errors
    errors.value = {};
    successMessage.value = '';

    // Basic validation
    if (!form.value.email) {
        errors.value.email = 'Email is required';
    }
    if (!form.value.password) {
        errors.value.password = 'Password is required';
    }
    if (!form.value.password_confirmation) {
        errors.value.password_confirmation = 'Password confirmation is required';
    }
    if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = 'Passwords do not match';
    }
    if (!isPasswordValid.value) {
        errors.value.password = 'Password does not meet requirements';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    try {
        await resetPassword(form.value);
        successMessage.value = 'Password reset successfully! You can now sign in with your new password.';
    } catch (err) {
        // Error is set in the composable
        console.error('Password reset error:', err);
    }
};
</script>

<style scoped>
input:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
