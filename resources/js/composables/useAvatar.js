/**
 * T010: Avatar utilities composable
 * Provides getInitials() and getBgColor() functions for user avatars
 */

/**
 * Extract initials from a user's name
 * @param {string} name - Full name of the user
 * @returns {string} 1-2 character initials (e.g., "JD" for "John Doe")
 */
export function getInitials(name) {
    if (!name) return '??'

    const parts = name.trim().split(' ')
    if (parts.length === 1) {
        return parts[0][0].toUpperCase()
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

/**
 * Get consistent background color for user avatar based on user ID
 * @param {number} userId - User ID
 * @returns {string} Hex color code
 */
export function getBgColor(userId) {
    // Use color palette from design system (avoid orange/blue to prevent confusion with UI accent colors)
    const colors = ['#8B5CF6', '#EC4899', '#10B981', '#F59E0B', '#3B82F6', '#6366F1']
    return colors[userId % colors.length]
}

/**
 * Composable hook for avatar utilities
 * @returns {object} Object containing getInitials and getBgColor functions
 */
export function useAvatar() {
    return {
        getInitials,
        getBgColor
    }
}

export default useAvatar
