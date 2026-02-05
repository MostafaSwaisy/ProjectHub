<template>
    <div class="filters-container">
        <!-- Status Filter -->
        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select
                v-model="localFilters.status"
                class="filter-select"
                @change="handleFilterChange"
            >
                <option value="">All Statuses</option>
                <option value="On Track">On Track</option>
                <option value="At Risk">At Risk</option>
                <option value="Delayed">Delayed</option>
            </select>
        </div>

        <!-- Role Filter -->
        <div class="filter-group">
            <label class="filter-label">Role</label>
            <select
                v-model="localFilters.role"
                class="filter-select"
                @change="handleFilterChange"
            >
                <option value="all">All Projects</option>
                <option value="owner">My Projects (Owner)</option>
                <option value="member">Member Of</option>
            </select>
        </div>

        <!-- Sort Dropdown -->
        <div class="filter-group">
            <label class="filter-label">Sort By</label>
            <select
                v-model="localFilters.sort"
                class="filter-select"
                @change="handleFilterChange"
            >
                <option value="updated_at">Last Updated</option>
                <option value="created_at">Created Date</option>
                <option value="title">Title (A-Z)</option>
            </select>
        </div>

        <!-- Sort Order Toggle -->
        <div class="filter-group">
            <label class="filter-label">Order</label>
            <button
                class="order-toggle"
                @click="toggleSortOrder"
                :title="localFilters.order === 'desc' ? 'Descending' : 'Ascending'"
            >
                <svg
                    v-if="localFilters.order === 'desc'"
                    class="icon"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
                <svg
                    v-else
                    class="icon"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
                {{ localFilters.order === 'desc' ? 'Desc' : 'Asc' }}
            </button>
        </div>

        <!-- Clear Filters Button -->
        <button
            v-if="hasActiveFilters"
            class="clear-filters-btn"
            @click="clearFilters"
            title="Clear all filters"
        >
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear Filters
        </button>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['update:filters']);

const route = useRoute();
const router = useRouter();

// Local copy of filters
const localFilters = ref({
    status: props.filters.status || '',
    role: props.filters.role || 'all',
    sort: props.filters.sort || 'updated_at',
    order: props.filters.order || 'desc',
});

// Check if there are any active filters (non-default values)
const hasActiveFilters = computed(() => {
    return (
        localFilters.value.status !== '' ||
        localFilters.value.role !== 'all' ||
        localFilters.value.sort !== 'updated_at' ||
        localFilters.value.order !== 'desc'
    );
});

// Load filters from URL query params on mount
onMounted(() => {
    if (route.query.status) {
        localFilters.value.status = route.query.status;
    }
    if (route.query.role) {
        localFilters.value.role = route.query.role;
    }
    if (route.query.sort) {
        localFilters.value.sort = route.query.sort;
    }
    if (route.query.order) {
        localFilters.value.order = route.query.order;
    }

    // Also load from localStorage as fallback
    const savedFilters = localStorage.getItem('projectsFilters');
    if (savedFilters && !route.query.status && !route.query.role && !route.query.sort) {
        const parsed = JSON.parse(savedFilters);
        localFilters.value = { ...localFilters.value, ...parsed };
    }
});

// Watch for prop changes and update local copy
watch(
    () => props.filters,
    (newFilters) => {
        localFilters.value = {
            status: newFilters.status || '',
            role: newFilters.role || 'all',
            sort: newFilters.sort || 'updated_at',
            order: newFilters.order || 'desc',
        };
    },
    { deep: true }
);

const handleFilterChange = () => {
    // Emit filter update
    emit('update:filters', { ...localFilters.value });

    // Update URL params
    updateURLParams();

    // Save to localStorage
    saveFiltersToLocalStorage();
};

const toggleSortOrder = () => {
    localFilters.value.order = localFilters.value.order === 'desc' ? 'asc' : 'desc';
    handleFilterChange();
};

const clearFilters = () => {
    // Reset to default values
    localFilters.value = {
        status: '',
        role: 'all',
        sort: 'updated_at',
        order: 'desc',
    };

    // Emit filter update
    emit('update:filters', { ...localFilters.value });

    // Clear URL params
    router.push({ query: {} });

    // Clear localStorage
    localStorage.removeItem('projectsFilters');
};

const updateURLParams = () => {
    const query = {};

    if (localFilters.value.status) {
        query.status = localFilters.value.status;
    }
    if (localFilters.value.role && localFilters.value.role !== 'all') {
        query.role = localFilters.value.role;
    }
    if (localFilters.value.sort !== 'updated_at') {
        query.sort = localFilters.value.sort;
    }
    if (localFilters.value.order !== 'desc') {
        query.order = localFilters.value.order;
    }

    router.push({ query });
};

const saveFiltersToLocalStorage = () => {
    localStorage.setItem('projectsFilters', JSON.stringify(localFilters.value));
};
</script>

<style scoped>
.filters-container {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 150px;
}

.filter-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filter-select {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #f8fafc;
    padding: 0.625rem 0.875rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
    background-size: 1.25rem;
    padding-right: 2.5rem;
}

.filter-select:hover {
    background-color: rgba(255, 255, 255, 0.08);
    border-color: rgba(148, 163, 184, 0.4);
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-select option {
    background: #1e293b;
    color: #f8fafc;
}

.order-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #f8fafc;
    padding: 0.625rem 0.875rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s;
}

.order-toggle:hover {
    background-color: rgba(255, 255, 255, 0.08);
    border-color: rgba(148, 163, 184, 0.4);
}

.order-toggle .icon {
    width: 1rem;
    height: 1rem;
}

.clear-filters-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    padding: 0.625rem 0.875rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.clear-filters-btn:hover {
    background: rgba(239, 68, 68, 0.25);
    border-color: rgba(239, 68, 68, 0.5);
}

.clear-filters-btn .icon {
    width: 1rem;
    height: 1rem;
}

@media (max-width: 768px) {
    .filters-container {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-group {
        min-width: 100%;
    }

    .order-toggle,
    .clear-filters-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
