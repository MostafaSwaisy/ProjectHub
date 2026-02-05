<template>
    <div class="team-management">
        <div class="section-header">
            <h3 class="section-title">Team Members</h3>
            <span class="member-count">{{ totalMembers }} {{ totalMembers === 1 ? 'member' : 'members' }}</span>
        </div>

        <!-- Add Member Section (only for instructor) -->
        <div v-if="canManageMembers" class="add-member-section">
            <h4 class="subsection-title">Add Team Member</h4>

            <!-- User Search -->
            <div class="search-wrapper">
                <input
                    v-model="searchQuery"
                    type="text"
                    class="search-input"
                    placeholder="Search users by name or email..."
                    @input="handleSearchInput"
                />

                <!-- Search Results Dropdown -->
                <div v-if="searchResults.length > 0" class="search-results">
                    <button
                        v-for="user in searchResults"
                        :key="user.id"
                        class="search-result-item"
                        @click="selectUser(user)"
                    >
                        <div class="user-info">
                            <span class="user-name">{{ user.name }}</span>
                            <span class="user-email">{{ user.email }}</span>
                        </div>
                        <svg class="icon-add" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>

                <!-- No Results -->
                <div v-if="searchQuery.length >= 2 && searchResults.length === 0 && !searchLoading" class="no-results">
                    No users found matching "{{ searchQuery }}"
                </div>

                <!-- Loading -->
                <div v-if="searchLoading" class="search-loading">
                    <div class="spinner-small"></div>
                    Searching...
                </div>
            </div>

            <!-- Selected User to Add -->
            <div v-if="selectedUser" class="selected-user">
                <div class="user-info">
                    <span class="user-name">{{ selectedUser.name }}</span>
                    <span class="user-email">{{ selectedUser.email }}</span>
                </div>

                <select v-model="selectedRole" class="role-select">
                    <option value="viewer">Viewer (Read-only)</option>
                    <option value="editor">Editor (Can edit)</option>
                </select>

                <div class="action-buttons">
                    <button class="btn-add" @click="addMember" :disabled="addingMember">
                        <svg v-if="!addingMember" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <div v-else class="spinner-small"></div>
                        {{ addingMember ? 'Adding...' : 'Add Member' }}
                    </button>
                    <button class="btn-cancel" @click="clearSelection">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Members List -->
        <div class="members-list">
            <div v-if="loading" class="loading-state">
                <div class="spinner"></div>
                <p>Loading members...</p>
            </div>

            <div v-else-if="members.length === 0" class="empty-state">
                <p>No team members yet.</p>
                <p v-if="canManageMembers" class="hint">Add team members using the search above.</p>
            </div>

            <div v-else class="members-grid">
                <div
                    v-for="member in members"
                    :key="member.user_id"
                    class="member-card"
                >
                    <div class="member-info">
                        <div class="member-avatar">
                            {{ member.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="member-details">
                            <span class="member-name">{{ member.name }}</span>
                            <span class="member-email">{{ member.email }}</span>
                        </div>
                    </div>

                    <!-- Role Selector (only for instructor) -->
                    <select
                        v-if="canManageMembers"
                        :value="member.role"
                        class="role-select-small"
                        @change="updateMemberRole(member, $event.target.value)"
                    >
                        <option value="viewer">Viewer</option>
                        <option value="editor">Editor</option>
                    </select>
                    <span v-else class="role-badge" :class="`role-${member.role}`">
                        {{ member.role }}
                    </span>

                    <!-- Remove Button (only for instructor) -->
                    <button
                        v-if="canManageMembers"
                        class="btn-remove"
                        @click="removeMember(member)"
                        title="Remove member"
                    >
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.last_page > 1" class="pagination">
                <button
                    class="page-btn"
                    :disabled="pagination.current_page === 1"
                    @click="loadMembers(pagination.current_page - 1)"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button
                    class="page-btn"
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="loadMembers(pagination.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['member-added', 'member-updated', 'member-removed']);

// State
const members = ref([]);
const loading = ref(false);
const pagination = ref(null);

// Search state
const searchQuery = ref('');
const searchResults = ref([]);
const searchLoading = ref(false);
let searchDebounceTimer = null;

// Add member state
const selectedUser = ref(null);
const selectedRole = ref('viewer');
const addingMember = ref(false);

// Computed
const canManageMembers = computed(() => {
    return props.project?.permissions?.can_manage_members || false;
});

const totalMembers = computed(() => {
    return pagination.value?.total || members.value.length;
});

// Load members on mount
loadMembers();

// Watch for project changes
watch(() => props.project?.id, () => {
    if (props.project?.id) {
        loadMembers();
    }
});

// Methods
async function loadMembers(page = 1) {
    loading.value = true;
    try {
        const response = await axios.get(`/api/projects/${props.project.id}/members`, {
            params: { page, per_page: 20 },
        });

        members.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) {
        console.error('Error loading members:', error);
    } finally {
        loading.value = false;
    }
}

// T079: User search with debounce
function handleSearchInput() {
    // Clear existing timer
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }

    // Clear results if query is too short
    if (searchQuery.value.length < 2) {
        searchResults.value = [];
        return;
    }

    // Debounce search (300ms)
    searchDebounceTimer = setTimeout(() => {
        searchUsers();
    }, 300);
}

async function searchUsers() {
    searchLoading.value = true;
    try {
        const response = await axios.get('/api/users/search', {
            params: { query: searchQuery.value, per_page: 10 },
        });

        // Filter out current members and the project owner
        const currentMemberIds = members.value.map(m => m.user_id);
        searchResults.value = response.data.data.filter(user => {
            return !currentMemberIds.includes(user.id) && user.id !== props.project.instructor_id;
        });
    } catch (error) {
        console.error('Error searching users:', error);
        searchResults.value = [];
    } finally {
        searchLoading.value = false;
    }
}

function selectUser(user) {
    selectedUser.value = user;
    searchQuery.value = '';
    searchResults.value = [];
}

function clearSelection() {
    selectedUser.value = null;
    selectedRole.value = 'viewer';
}

async function addMember() {
    if (!selectedUser.value) return;

    addingMember.value = true;
    try {
        await axios.post(`/api/projects/${props.project.id}/members`, {
            user_id: selectedUser.value.id,
            role: selectedRole.value,
        });

        // Reload members
        await loadMembers();

        // Clear selection
        clearSelection();

        // Emit event
        emit('member-added');
    } catch (error) {
        console.error('Error adding member:', error);
        alert(error.response?.data?.message || 'Failed to add member');
    } finally {
        addingMember.value = false;
    }
}

async function updateMemberRole(member, newRole) {
    try {
        await axios.put(`/api/projects/${props.project.id}/members/${member.user_id}`, {
            role: newRole,
        });

        // Update local state
        const index = members.value.findIndex(m => m.user_id === member.user_id);
        if (index !== -1) {
            members.value[index].role = newRole;
        }

        // Emit event
        emit('member-updated');
    } catch (error) {
        console.error('Error updating member role:', error);
        alert(error.response?.data?.message || 'Failed to update member role');
        // Reload to revert
        loadMembers();
    }
}

async function removeMember(member) {
    if (!confirm(`Remove ${member.name} from this project?`)) {
        return;
    }

    try {
        await axios.delete(`/api/projects/${props.project.id}/members/${member.user_id}`);

        // Reload members
        await loadMembers();

        // Emit event
        emit('member-removed');
    } catch (error) {
        console.error('Error removing member:', error);
        alert(error.response?.data?.message || 'Failed to remove member');
    }
}
</script>

<style scoped>
.team-management {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0;
}

.member-count {
    font-size: 0.875rem;
    color: #94a3b8;
}

.add-member-section {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.75rem;
    padding: 1.5rem;
}

.subsection-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #cbd5e1;
    margin: 0 0 1rem 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.search-wrapper {
    position: relative;
}

.search-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #f8fafc;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-results {
    position: absolute;
    top: calc(100% + 0.5rem);
    left: 0;
    right: 0;
    background: #1e293b;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    max-height: 300px;
    overflow-y: auto;
    z-index: 10;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
}

.search-result-item {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem 1rem;
    border: none;
    background: transparent;
    color: #f8fafc;
    text-align: left;
    cursor: pointer;
    transition: background 0.2s;
}

.search-result-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.user-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.user-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #f8fafc;
}

.user-email {
    font-size: 0.75rem;
    color: #94a3b8;
}

.icon-add {
    width: 1.25rem;
    height: 1.25rem;
    color: #667eea;
}

.no-results,
.search-loading {
    padding: 1rem;
    text-align: center;
    color: #94a3b8;
    font-size: 0.875rem;
}

.search-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.spinner-small {
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.selected-user {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.1);
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 0.5rem;
}

.role-select,
.role-select-small {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #f8fafc;
    padding: 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    cursor: pointer;
}

.role-select:focus,
.role-select-small:focus {
    outline: none;
    border-color: #667eea;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-add,
.btn-cancel {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-add {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-add:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

.btn-add:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-cancel {
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.btn-cancel:hover {
    background: rgba(255, 255, 255, 0.1);
}

.icon {
    width: 1rem;
    height: 1rem;
}

.members-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.loading-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.spinner {
    width: 2rem;
    height: 2rem;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

.hint {
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.members-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.member-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.member-card:hover {
    background: rgba(255, 255, 255, 0.05);
}

.member-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.member-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 1rem;
}

.member-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.member-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #f8fafc;
}

.member-email {
    font-size: 0.75rem;
    color: #94a3b8;
}

.role-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: capitalize;
}

.role-viewer {
    background: rgba(148, 163, 184, 0.2);
    color: #cbd5e1;
}

.role-editor {
    background: rgba(102, 126, 234, 0.2);
    color: #a5b4fc;
}

.btn-remove {
    background: transparent;
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
    padding: 0.5rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove:hover {
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.5);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1rem;
}

.page-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #cbd5e1;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.page-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: #94a3b8;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .member-card {
        flex-direction: column;
        align-items: stretch;
    }

    .member-info {
        flex-direction: row;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>
