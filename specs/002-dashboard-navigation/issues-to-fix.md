# Dashboard & Navigation Issues Tracker

**Feature**: 002-dashboard-navigation
**Date**: 2026-02-03
**Status**: Issues identified during integration testing

---

## Summary

During the dashboard and navigation integration, several issues were identified. This document tracks what was fixed and what remains to be addressed.

---

## FIXED Issues

### 1. Authentication Persistence on Page Reload
**Status**: ✅ FIXED
**Commit**: `62be738`

**Problem**: Users were being redirected to login after page refresh despite having a valid token.

**Root Cause**: Router navigation guard was checking `isAuthenticated.value` (requires both token AND user) before fetching user data. On page reload, user is null until fetched.

**Fix**: Changed check from `isAuthenticated.value` to `token.value` in `resources/js/router/index.js:162`

```javascript
// BEFORE (broken)
if (isAuthenticated.value && !user.value) {

// AFTER (fixed)
if (token.value && !user.value) {
```

---

### 2. Missing CSS Variables for StatCard
**Status**: ✅ FIXED
**Commit**: `b708cab`

**Problem**: StatCard components were invisible/unstyled because CSS variables were undefined.

**Root Cause**: StatCard.vue used variables not defined in design-system.css:
- `--glass-bg`
- `--glass-border`
- `--glass-border-hover`
- `--orange-gradient`
- `--red-secondary`

**Fix**: Added missing variables to `resources/js/styles/design-system.css`:
```css
--glass-bg: rgba(255, 255, 255, 0.05);
--glass-border: rgba(255, 255, 255, 0.1);
--glass-border-hover: rgba(255, 255, 255, 0.2);
--orange-gradient: linear-gradient(135deg, var(--orange-primary) 0%, var(--orange-dark) 100%);
--red-secondary: #DC2626;
```

---

### 3. StatCard Icon Sizing
**Status**: ✅ FIXED
**Commit**: `b708cab`

**Problem**: Icons in stat cards were not rendering at correct size.

**Root Cause**: SVG icons passed via v-html needed explicit sizing.

**Fix**: Added deep selector in `StatCard.vue`:
```css
.stat-card__icon :deep(svg) {
  width: 20px;
  height: 20px;
  stroke: currentColor;
}
```

---

### 4. Dashboard Stats Showing 0 for All Values
**Status**: ✅ FIXED (via Tinker)

**Problem**: Dashboard showed "No Projects Yet" empty state with all stats at 0.

**Root Cause**: Logged-in user "Mostafa Swaisy" (ID 11) had no projects. Seeded test data was created for "Admin User" (ID 1).

**Fix**: Added user to existing projects via Tinker. Need to make seeder smarter or add current user to projects.

**Recommended Permanent Fix**: Update `DashboardTestSeeder.php` to also add projects for the currently logged-in user or create a setup command.

---

## REMAINING Issues to Fix

### 5. Navigation View Not Updating (NEEDS VERIFICATION)
**Status**: ⚠️ NEEDS TESTING
**Priority**: HIGH

**Problem**: User reported that clicking sidebar navigation links changes the URL but the view doesn't update.

**Possible Causes**:
1. Vue Router component caching
2. AppLayout slot not re-rendering
3. Page components not properly lazy-loaded

**Files to Check**:
- `resources/js/router/index.js` - Route definitions
- `resources/js/layouts/AppLayout.vue` - Slot rendering
- `resources/js/pages/Settings.vue` - Example placeholder page
- `resources/js/pages/Team.vue`
- `resources/js/pages/MyTasks.vue`
- `resources/js/pages/projects/ProjectsList.vue`

**Test Steps**:
1. Login and navigate to dashboard
2. Click "Projects" in sidebar
3. Verify URL changes to `/projects`
4. Verify page content changes from "Dashboard" to "Projects"
5. Repeat for all navigation items

---

### 6. Vue Router Transition Warning
**Status**: ⚠️ COSMETIC
**Priority**: LOW

**Problem**: Console shows warning:
```
[Vue Router warn]: <router-view> can no longer be used directly inside <transition> or <keep-alive>.
```

**Location**: Need to find where router-view is wrapped with transition.

**Files to Check**:
- `resources/js/App.vue`
- Any file using `<transition>` with `<router-view>`

**Fix Pattern**:
```vue
<!-- BEFORE (causes warning) -->
<transition>
  <router-view />
</transition>

<!-- AFTER (correct) -->
<router-view v-slot="{ Component }">
  <transition>
    <component :is="Component" />
  </transition>
</router-view>
```

---

### 7. StatCard Visual Styling
**Status**: ⚠️ NEEDS IMPROVEMENT
**Priority**: MEDIUM

**Problem**: StatCard boxes appear too dark/plain compared to the design reference.

**Current Look**: Dark cards with subtle borders
**Expected Look**: More visible glassmorphic effect with better contrast

**Files to Update**:
- `resources/js/components/dashboard/StatCard.vue`
- `resources/js/styles/design-system.css`

**Possible Improvements**:
1. Increase glass-bg opacity: `rgba(255, 255, 255, 0.08)` instead of `0.05`
2. Add subtle glow/shadow
3. Adjust border color for more visibility
4. Add gradient background

---

### 8. Empty State CTA Button
**Status**: ⚠️ MISSING FUNCTIONALITY
**Priority**: MEDIUM

**Problem**: "No Projects Yet" empty state doesn't have a working "Create Project" button.

**Location**: `resources/js/components/dashboard/DashboardStats.vue`

**Fix**: Add button that links to project creation modal or /projects page with ?create=true query param.

---

### 9. Overdue Tasks Red Highlight
**Status**: ⚠️ NEEDS VERIFICATION
**Priority**: MEDIUM

**Problem**: Overdue card should display in red when count > 0.

**Test Steps**:
1. Ensure overdue_tasks > 0 in API response
2. Verify card has red border and text
3. Verify icon is red

---

### 10. Mobile Responsive Testing
**Status**: ⚠️ NOT TESTED
**Priority**: MEDIUM

**Test Checklist**:
- [ ] Sidebar collapses on mobile (< 768px)
- [ ] Hamburger menu appears in navbar
- [ ] Sidebar slides in when hamburger clicked
- [ ] Overlay appears behind sidebar
- [ ] Click overlay closes sidebar
- [ ] Stat cards stack vertically on mobile
- [ ] Navigation auto-closes sidebar after selection

---

## Test Data Setup

### Login Credentials for Testing

**Admin User (has all seeded data)**:
- Email: `admin@example.com`
- Password: `password`
- Projects: 3
- Active Tasks: ~50
- Overdue Tasks: ~10

**Current User Added to Projects**:
- User: Mostafa Swaisy (ID 11)
- Added to: Website Redesign, Mobile App Development, API Integration
- Role: Editor

### Re-seed Data Command
```bash
php artisan db:seed --class=DashboardTestSeeder
```

### Clear Dashboard Cache
```bash
php artisan tinker
>>> Cache::forget("dashboard.stats.11");  # Replace 11 with user ID
```

---

## Files Modified in This Session

| File | Change | Status |
|------|--------|--------|
| `resources/js/router/index.js` | Fixed auth check | ✅ Committed |
| `resources/js/styles/design-system.css` | Added CSS variables | ✅ Committed |
| `resources/js/components/dashboard/StatCard.vue` | Icon sizing fix | ✅ Committed |
| `resources/js/stores/dashboard.js` | New file - dashboard store | ✅ Committed |
| `resources/js/components/dashboard/DashboardStats.vue` | New file - stats component | ✅ Committed |
| `resources/js/pages/Dashboard.vue` | Integrated DashboardStats | ✅ Committed |
| `database/seeders/DashboardTestSeeder.php` | Test data seeder | ✅ Committed |

---

## Git Commits from This Session

1. `ae9b47a` - feat: Complete Phase 4 dashboard statistics frontend (US2)
2. `62be738` - fix: Resolve authentication persistence on page reload
3. `b708cab` - fix: Add missing CSS variables and fix StatCard icon styling

---

## Tomorrow's Action Plan

1. **First**: Test navigation - click all sidebar links and verify views change
2. **Second**: Fix Vue Router transition warning if affecting functionality
3. **Third**: Improve StatCard visual styling
4. **Fourth**: Add Create Project button to empty state
5. **Fifth**: Test mobile responsiveness
6. **Sixth**: Run full regression test (login → dashboard → navigate → logout)

---

## Related Documentation

- [Implementation Progress](implementation-progress.md)
- [Tasks Checklist](tasks.md)
- [Database Analysis](database-analysis.md)
- [API Contracts](contracts/dashboard-stats.md)
