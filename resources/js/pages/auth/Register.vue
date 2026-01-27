<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-4">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">ProjectHub</h1>
                <p class="text-gray-600">Create your account</p>
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

            <!-- Register Form -->
            <form @submit.prevent="handleRegister" class="space-y-5">
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Full Name
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="John Doe"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        :disabled="isLoading"
                    />
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                        {{ errors.name }}
                    </p>
                </div>

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
                        Password
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

                <!-- Role Field -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        I am a...
                    </label>
                    <select
                        id="role"
                        v-model="form.role"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        :disabled="isLoading"
                    >
                        <option value="">Select a role</option>
                        <option value="student">Student</option>
                        <option value="instructor">Instructor</option>
                    </select>
                    <p v-if="errors.role" class="mt-1 text-sm text-red-600">
                        {{ errors.role }}
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    :disabled="isLoading || !isPasswordValid"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:bg-gray-400 transition"
                >
                    <span v-if="!isLoading">Create Account</span>
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
                        Creating account...
                    </span>
                </button>
            </form>

            <!-- Divider -->
            <div class="my-6 relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Already have an account?</span>
                </div>
            </div>

            <!-- Login Link -->
            <router-link
                to="/auth/login"
                class="block w-full text-center py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition"
            >
                Sign In
            </router-link>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../../composables/useAuth';

const router = useRouter();
const { register, isLoading, error, clearError } = useAuth();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
});

const errors = ref({});

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

const handleRegister = async () => {
    // Clear previous errors
    errors.value = {};

    // Basic validation
    if (!form.value.name) {
        errors.value.name = 'Name is required';
    }
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
    if (!form.value.role) {
        errors.value.role = 'Please select a role';
    }
    if (!isPasswordValid.value) {
        errors.value.password = 'Password does not meet requirements';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    try {
        await register(form.value);
        // Redirect to dashboard on successful registration
        router.push('/dashboard');
    } catch (err) {
        // Error is set in the composable
        console.error('Registration error:', err);
    }
};
</script>

<style scoped>
input:disabled,
select:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}
</style>
