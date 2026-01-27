import { ref, computed } from 'vue';
import axios from 'axios';

// Global auth state
const user = ref(null);
const token = ref(localStorage.getItem('auth_token'));
const isLoading = ref(false);
const error = ref(null);

// Set axios authorization header if token exists
if (token.value) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
}

export function useAuth() {
    const isAuthenticated = computed(() => !!user.value && !!token.value);

    /**
     * Register a new user
     * @param {Object} credentials - { name, email, password, role }
     * @returns {Promise}
     */
    const register = async (credentials) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/auth/register', credentials);

            // Store token
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

    /**
     * Login user
     * @param {Object} credentials - { email, password }
     * @returns {Promise}
     */
    const login = async (credentials) => {
        isLoading.value = true;
        error.value = null;

        try {
            const response = await axios.post('/api/auth/login', credentials);

            // Store token and user
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

    /**
     * Logout user
     * @returns {Promise}
     */
    const logout = async () => {
        isLoading.value = true;
        error.value = null;

        try {
            await axios.post('/api/auth/logout');
        } catch (err) {
            console.error('Logout error:', err);
        } finally {
            // Clear local state regardless of API response
            user.value = null;
            token.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
            isLoading.value = false;
        }
    };

    /**
     * Request password reset email
     * @param {string} email
     * @returns {Promise}
     */
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

    /**
     * Reset password with token
     * @param {Object} data - { token, email, password, password_confirmation }
     * @returns {Promise}
     */
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

    /**
     * Get current user from API
     * @returns {Promise}
     */
    const fetchCurrentUser = async () => {
        if (!token.value) {
            return null;
        }

        try {
            const response = await axios.get('/api/user');
            user.value = response.data;
            return response.data;
        } catch (err) {
            // Token might be invalid
            token.value = null;
            user.value = null;
            localStorage.removeItem('auth_token');
            delete axios.defaults.headers.common['Authorization'];
            throw err;
        }
    };

    /**
     * Check if user has a specific role
     * @param {string|string[]} roles
     * @returns {boolean}
     */
    const hasRole = (roles) => {
        if (!user.value || !user.value.role) {
            return false;
        }

        const rolesArray = Array.isArray(roles) ? roles : [roles];
        return rolesArray.includes(user.value.role.name);
    };

    /**
     * Clear error message
     */
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
}
