import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    // State
    const user = ref(null);
    const token = ref(localStorage.getItem('auth_token'));
    const isLoading = ref(false);
    const error = ref(null);

    // Set axios authorization header if token exists
    if (token.value) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
    }

    // Computed
    const isAuthenticated = computed(() => !!user.value && !!token.value);

    // Actions
    const register = async (credentials) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/auth/register', credentials);

            const { token: newToken, user: newUser } = response.data;
            token.value = newToken;
            user.value = newUser;
            localStorage.setItem('auth_token', newToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Registration failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    const login = async (credentials) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/auth/login', credentials);

            const { token: newToken, user: newUser } = response.data;
            token.value = newToken;
            user.value = newUser;
            localStorage.setItem('auth_token', newToken);
            axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;

            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Login failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    const logout = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            await axios.post('/api/auth/logout');
        } catch (err) {
            console.error('Logout error:', err);
        } finally {
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
            isLoading.value = false;
        }
    };

    const requestPasswordReset = async (email) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/auth/password/email', { email });
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Password reset request failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    const resetPassword = async (data) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/auth/password/reset', data);
            return response.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Password reset failed';
            throw err;
        } finally {
            isLoading.value = false;
        }
    };

    const fetchCurrentUser = async () => {
        if (!token.value) {
            return null;
        }

        try {
            const response = await axios.get('/api/user');
            user.value = response.data;
            return response.data;
        } catch (err) {
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
            throw err;
        }
    };

    const hasRole = (roles) => {
        if (!user.value || !user.value.role) {
            return false;
        }

        const rolesArray = Array.isArray(roles) ? roles : [roles];
        return rolesArray.includes(user.value.role.name);
    };

    const clearError = () => {
        error.value = null;
    };

    return {
        // State
        user,
        token,
        isLoading,
        error,
        isAuthenticated,

        // Methods
        register,
        login,
        logout,
        requestPasswordReset,
        resetPassword,
        fetchCurrentUser,
        hasRole,
        clearError,
    };
});
