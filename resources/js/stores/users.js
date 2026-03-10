import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useUsers = defineStore('users', () => {
    const users = ref([]);
    const currentUser = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15,
    });

    // Computed
    const isLoading = computed(() => loading.value);
    const hasError = computed(() => error.value !== null);
    const errorMessage = computed(() => error.value);
    const totalUsers = computed(() => pagination.value.total);
    const hasMorePages = computed(() => pagination.value.current_page < pagination.value.last_page);

    // Actions
    const fetchUsers = async (filters = {}, page = 1) => {
        loading.value = true;
        error.value = null;

        try {
            const params = {
                page,
                per_page: filters.per_page || 15,
                ...filters,
            };

            const response = await axios.get('/api/users', { params });

            users.value = response.data.data;
            pagination.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                total: response.data.total,
                per_page: response.data.per_page,
            };
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch users';
            console.error('Error fetching users:', err);
        } finally {
            loading.value = false;
        }
    };

    const fetchUser = async (userId) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.get(`/api/users/${userId}`);
            currentUser.value = response.data.data;
            return currentUser.value;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch user';
            console.error('Error fetching user:', err);
        } finally {
            loading.value = false;
        }
    };

    const updateUser = async (userId, data) => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios.put(`/api/users/${userId}`, data);

            // Update user in list
            const index = users.value.findIndex(u => u.id === userId);
            if (index !== -1) {
                users.value[index] = response.data.data;
            }

            // Update current user if it's the one being edited
            if (currentUser.value?.id === userId) {
                currentUser.value = response.data.data;
            }

            return response.data.data;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to update user';
            console.error('Error updating user:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const deleteUser = async (userId) => {
        loading.value = true;
        error.value = null;

        try {
            await axios.delete(`/api/users/${userId}`);

            // Remove from list
            users.value = users.value.filter(u => u.id !== userId);

            // Clear current user if deleted
            if (currentUser.value?.id === userId) {
                currentUser.value = null;
            }

            return true;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to delete user';
            console.error('Error deleting user:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    const clearError = () => {
        error.value = null;
    };

    const clearCurrentUser = () => {
        currentUser.value = null;
    };

    return {
        users,
        currentUser,
        loading,
        error,
        pagination,
        isLoading,
        hasError,
        errorMessage,
        totalUsers,
        hasMorePages,
        fetchUsers,
        fetchUser,
        updateUser,
        deleteUser,
        clearError,
        clearCurrentUser,
    };
});
