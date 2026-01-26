# ProjectHub Analytics - Task Breakdown

## Overview

| Feature Group | Features | Tasks | Estimated Effort |
|---------------|----------|-------|------------------|
| 1. Authentication & Authorization | 2 | 18 | Medium |
| 2. Dashboard Analytics | 5 | 28 | High |
| 3. Kanban Project Board | 9 | 52 | Very High |
| 4. Student Performance Analytics | 7 | 38 | High |
| 5. Common Components | 5 | 22 | Medium |
| **Total** | **28** | **158** | - |

---

## Feature Group 1: Authentication & Authorization

### Feature 1.1: User Authentication

#### Backend Tasks

- [ ] **AUTH-001** Create `RegisterController` with validation
  - Validate name, email (unique, format), password (min 8 chars, 1 number), role
  - Return JWT token on success
  - Files: `app/Http/Controllers/Auth/RegisterController.php`, `app/Http/Requests/RegisterRequest.php`

- [ ] **AUTH-002** Create `LoginController` with JWT token response
  - Validate credentials, return JWT on success
  - Handle invalid credentials with proper error response
  - Files: `app/Http/Controllers/Auth/LoginController.php`

- [ ] **AUTH-003** Create `LogoutController` to invalidate tokens
  - Blacklist current token
  - Clear session data
  - Files: `app/Http/Controllers/Auth/LogoutController.php`

- [ ] **AUTH-004** Create `PasswordResetController`
  - Send reset email with token link
  - Validate token and update password
  - Handle expired tokens
  - Files: `app/Http/Controllers/Auth/PasswordResetController.php`

- [ ] **AUTH-005** Create password reset email template
  - Files: `resources/views/emails/password-reset.blade.php`

#### Frontend Tasks

- [ ] **AUTH-006** Create `Login.vue` page
  - Email/password form with validation
  - Error handling display
  - "Forgot password" link
  - Files: `resources/js/pages/auth/Login.vue`

- [ ] **AUTH-007** Create `Register.vue` page
  - Form with name, email, password, role selection
  - Client-side validation matching backend rules
  - Files: `resources/js/pages/auth/Register.vue`

- [ ] **AUTH-008** Create `ResetPassword.vue` page
  - Request reset form
  - Set new password form (with token)
  - Files: `resources/js/pages/auth/ResetPassword.vue`

- [ ] **AUTH-009** Create `useAuth` composable
  - Login/logout/register methods
  - Token storage management
  - Current user state
  - Files: `resources/js/composables/useAuth.js`

---

### Feature 1.2: Role-Based Access Control

#### Backend Tasks

- [ ] **RBAC-001** Create `roles` migration and `Role` model
  - Roles: admin, instructor, student
  - Add `role_id` to users table
  - Files: `database/migrations/xxxx_create_roles_table.php`, `app/Models/Role.php`

- [ ] **RBAC-002** Create `RoleMiddleware`
  - Check user role against allowed roles
  - Return 403 for unauthorized access
  - Files: `app/Http/Middleware/RoleMiddleware.php`

- [ ] **RBAC-003** Create `ProjectPolicy`
  - `view`: Admin all, Instructor owns, Student member
  - `update`: Admin all, Instructor owns
  - `delete`: Admin all, Instructor owns
  - Files: `app/Policies/ProjectPolicy.php`

- [ ] **RBAC-004** Create `TaskPolicy`
  - Access based on project membership
  - Files: `app/Policies/TaskPolicy.php`

- [ ] **RBAC-005** Create `UserPolicy`
  - Instructors see assigned students only
  - Students see only self
  - Files: `app/Policies/UserPolicy.php`

- [ ] **RBAC-006** Register policies in `AuthServiceProvider`
  - Files: `app/Providers/AuthServiceProvider.php`

#### Frontend Tasks

- [ ] **RBAC-007** Create route guards for role-based navigation
  - Redirect unauthorized users
  - Files: `resources/js/router/guards.js`

- [ ] **RBAC-008** Create `usePermissions` composable
  - Check if user can perform actions
  - Files: `resources/js/composables/usePermissions.js`

- [ ] **RBAC-009** Implement conditional UI rendering based on role
  - Hide/show elements based on permissions
  - Files: Various components

---

## Feature Group 2: Dashboard Analytics

### Feature 2.1: Summary Statistics

#### Backend Tasks

- [ ] **DASH-001** Create `DashboardController@stats` endpoint
  - Return projects count, tasks count, team online, overdue count
  - Include 7-day trend data
  - Calculate percentage changes
  - Files: `app/Http/Controllers/DashboardController.php`

- [ ] **DASH-002** Create `DashboardService` for stats calculations
  - Project trends, task completion rates
  - Online members tracking
  - Files: `app/Services/DashboardService.php`

#### Frontend Tasks

- [ ] **DASH-003** Create `SummaryStats.vue` component
  - Grid layout for 4 stat cards
  - Files: `resources/js/components/dashboard/SummaryStats.vue`

- [ ] **DASH-004** Create `StatCard.vue` component
  - Count display with sparkline
  - Percentage change indicator
  - Pulsing effect for overdue
  - Files: `resources/js/components/dashboard/StatCard.vue`

- [ ] **DASH-005** Create `SkeletonLoader.vue` for loading states
  - Animated placeholder
  - Files: `resources/js/components/common/SkeletonLoader.vue`

- [ ] **DASH-006** Implement auto-refresh mechanism (polling/websocket)
  - Files: `resources/js/composables/useAutoRefresh.js`

---

### Feature 2.2: Project Health Matrix

#### Backend Tasks

- [ ] **MATRIX-001** Create `DashboardController@healthMatrix` endpoint
  - 3x3 matrix with project counts
  - Group by timeline_status and budget_status
  - Files: `app/Http/Controllers/DashboardController.php`

- [ ] **MATRIX-002** Add `timeline_status` and `budget_status` to projects migration
  - Enum: behind, on_track, ahead / over_budget, on_budget, under_budget
  - Files: `database/migrations/xxxx_create_projects_table.php`

#### Frontend Tasks

- [ ] **MATRIX-003** Create `ProjectHealthMatrix.vue` component
  - 3x3 grid with axis labels
  - Color intensity based on count
  - Click handler to filter projects
  - Files: `resources/js/components/dashboard/ProjectHealthMatrix.vue`

- [ ] **MATRIX-004** Implement matrix cell click → project list filtering
  - URL query param integration
  - Files: `resources/js/components/dashboard/ProjectHealthMatrix.vue`

---

### Feature 2.3: Progress Chart

#### Backend Tasks

- [ ] **CHART-001** Create `DashboardController@progress` endpoint
  - Weekly completed tasks (bars)
  - Cumulative progress % (line)
  - Support time range: 7, 30, 90, custom
  - Files: `app/Http/Controllers/DashboardController.php`

#### Frontend Tasks

- [ ] **CHART-002** Create `ProgressChart.vue` component
  - Dual-axis chart using Chart.js
  - Time range selector
  - Tooltip on hover
  - Files: `resources/js/components/dashboard/ProgressChart.vue`

- [ ] **CHART-003** Implement chart export functionality
  - PNG export (canvas to image)
  - CSV export (data download)
  - Files: `resources/js/components/dashboard/ProgressChart.vue`

- [ ] **CHART-004** Add chart animation on initial load
  - Files: `resources/js/components/dashboard/ProgressChart.vue`

---

### Feature 2.4: Activity Feed

#### Backend Tasks

- [ ] **FEED-001** Create `activities` migration and `Activity` model
  - user_id, project_id, type, subject_type, subject_id, data, created_at
  - Files: `database/migrations/xxxx_create_activities_table.php`, `app/Models/Activity.php`

- [ ] **FEED-002** Create `ActivityController@index` endpoint
  - Paginated (20 per page)
  - Filter by type (all, comments, status_changes, file_uploads)
  - Files: `app/Http/Controllers/ActivityController.php`

- [ ] **FEED-003** Create activity logging trait/service
  - Auto-log on task changes, comments, file uploads
  - Files: `app/Services/ActivityLogger.php`

- [ ] **FEED-004** Set up broadcasting for real-time activities
  - Files: `app/Events/ActivityCreated.php`

#### Frontend Tasks

- [ ] **FEED-005** Create `ActivityFeed.vue` component
  - Activity item with avatar, description, timestamp, project tag
  - Filter tabs
  - Infinite scroll
  - Files: `resources/js/components/dashboard/ActivityFeed.vue`

- [ ] **FEED-006** Create `ActivityItem.vue` component
  - Render different activity types
  - Relative timestamp (date-fns)
  - Click navigation
  - Files: `resources/js/components/dashboard/ActivityItem.vue`

- [ ] **FEED-007** Implement infinite scroll pagination
  - Files: `resources/js/composables/useInfiniteScroll.js`

- [ ] **FEED-008** Connect to WebSocket for real-time updates
  - Files: `resources/js/components/dashboard/ActivityFeed.vue`

---

### Feature 2.5: Deadline Heatmap Calendar

#### Backend Tasks

- [ ] **HEATMAP-001** Create `DashboardController@deadlines` endpoint
  - Return deadline counts per day for given month
  - Files: `app/Http/Controllers/DashboardController.php`

#### Frontend Tasks

- [ ] **HEATMAP-002** Create `DeadlineHeatmap.vue` component
  - Month calendar grid
  - Color coding (green→yellow→red)
  - Current day highlight
  - Files: `resources/js/components/dashboard/DeadlineHeatmap.vue`

- [ ] **HEATMAP-003** Implement day hover popover
  - Show deadline list
  - Files: `resources/js/components/dashboard/DeadlineHeatmap.vue`

- [ ] **HEATMAP-004** Implement month navigation with animation
  - Slide transition between months
  - Files: `resources/js/components/dashboard/DeadlineHeatmap.vue`

- [ ] **HEATMAP-005** Implement day click → filtered task list navigation
  - Files: `resources/js/components/dashboard/DeadlineHeatmap.vue`

---

## Feature Group 3: Kanban Project Board

### Feature 3.1: Board Layout

#### Backend Tasks

- [ ] **BOARD-001** Create `boards` migration and `Board` model
  - project_id, title
  - Files: `database/migrations/xxxx_create_boards_table.php`, `app/Models/Board.php`

- [ ] **BOARD-002** Create `columns` migration and `Column` model
  - board_id, title, position, wip_limit
  - Files: `database/migrations/xxxx_create_columns_table.php`, `app/Models/Column.php`

- [ ] **BOARD-003** Create `BoardController` with CRUD
  - Include columns and tasks in response
  - Files: `app/Http/Controllers/BoardController.php`

- [ ] **BOARD-004** Seed default columns on board creation
  - Backlog, To Do, In Progress, Review, Completed
  - Files: `app/Models/Board.php` (boot method)

#### Frontend Tasks

- [ ] **BOARD-005** Create `KanbanBoard.vue` component
  - Horizontal scrollable container
  - Column rendering
  - Files: `resources/js/components/board/KanbanBoard.vue`

- [ ] **BOARD-006** Create `BoardColumn.vue` component
  - Min/max width (280-320px)
  - Task count in header
  - Task list rendering
  - Files: `resources/js/components/board/BoardColumn.vue`

- [ ] **BOARD-007** Implement horizontal scroll with snap points
  - CSS scroll-snap
  - Files: `resources/js/components/board/KanbanBoard.vue`

- [ ] **BOARD-008** Implement mobile view (single column + swipe)
  - Touch swipe navigation
  - Files: `resources/js/components/board/KanbanBoard.vue`

---

### Feature 3.2: Board Header

#### Frontend Tasks

- [ ] **HEADER-001** Create `BoardHeader.vue` component
  - Project title, members, view toggle, filter bar
  - Files: `resources/js/components/board/BoardHeader.vue`

- [ ] **HEADER-002** Implement inline title editing
  - Click to edit, Enter to save, Escape to cancel
  - Files: `resources/js/components/board/BoardHeader.vue`

- [ ] **HEADER-003** Create member avatars display with add button
  - Stacked avatars
  - Add member modal trigger
  - Files: `resources/js/components/board/BoardHeader.vue`

- [ ] **HEADER-004** Create view toggle buttons (Kanban/List/Timeline)
  - Files: `resources/js/components/board/BoardHeader.vue`

- [ ] **HEADER-005** Create filter bar component
  - Assignee multi-select
  - Label filter
  - Due date range
  - Search input
  - Files: `resources/js/components/board/FilterBar.vue`

- [ ] **HEADER-006** Implement filter URL persistence
  - Sync filters with query params
  - Files: `resources/js/composables/useFilters.js`

---

### Feature 3.3: Task Cards

#### Backend Tasks

- [ ] **CARD-001** Create `tasks` migration and `Task` model
  - column_id, title, description, assignee_id, priority, due_date, position
  - Files: `database/migrations/xxxx_create_tasks_table.php`, `app/Models/Task.php`

- [ ] **CARD-002** Create `labels` and `task_labels` migrations
  - Files: `database/migrations/xxxx_create_labels_table.php`

- [ ] **CARD-003** Create `TaskController` with CRUD
  - Files: `app/Http/Controllers/TaskController.php`

- [ ] **CARD-004** Create `TaskResource` for API responses
  - Include subtask count, label count
  - Files: `app/Http/Resources/TaskResource.php`

#### Frontend Tasks

- [ ] **CARD-005** Create `TaskCard.vue` component
  - Title (2 lines + ellipsis)
  - Priority color border
  - Assignee avatar
  - Due date (red if overdue)
  - Subtask progress bar
  - Labels (max 3 + overflow)
  - Files: `resources/js/components/board/TaskCard.vue`

- [ ] **CARD-006** Implement card hover effects
  - Shadow elevation
  - Quick action buttons (edit, duplicate, archive)
  - Files: `resources/js/components/board/TaskCard.vue`

- [ ] **CARD-007** Implement card click → open detail panel
  - Files: `resources/js/components/board/TaskCard.vue`

---

### Feature 3.4: Drag and Drop

#### Backend Tasks

- [ ] **DND-001** Create `TaskController@move` endpoint
  - Update column_id and position
  - Check WIP limit
  - Reorder other tasks
  - Files: `app/Http/Controllers/TaskController.php`

- [ ] **DND-002** Broadcast task move event
  - Files: `app/Events/TaskMoved.php`

#### Frontend Tasks

- [ ] **DND-003** Implement drag and drop with @dnd-kit
  - Cross-column drag
  - Same-column reorder
  - Files: `resources/js/components/board/KanbanBoard.vue`

- [ ] **DND-004** Create drop placeholder indicator
  - Visual feedback for drop position
  - Files: `resources/js/components/board/BoardColumn.vue`

- [ ] **DND-005** Implement optimistic UI updates
  - Immediate move, rollback on error
  - Files: `resources/js/stores/tasks.js`

- [ ] **DND-006** Implement touch support for mobile
  - Files: `resources/js/components/board/KanbanBoard.vue`

---

### Feature 3.5: Column WIP Limits

#### Backend Tasks

- [ ] **WIP-001** Add WIP limit validation to task move
  - Block if column at/over limit
  - Files: `app/Http/Controllers/TaskController.php`

#### Frontend Tasks

- [ ] **WIP-002** Display WIP limit badge in column header
  - Format: "3/5"
  - Files: `resources/js/components/board/BoardColumn.vue`

- [ ] **WIP-003** Implement WIP limit visual indicators
  - Yellow border at limit
  - Red border over limit
  - Files: `resources/js/components/board/BoardColumn.vue`

- [ ] **WIP-004** Block drop on columns over limit
  - Visual feedback
  - Files: `resources/js/components/board/KanbanBoard.vue`

- [ ] **WIP-005** Create column menu with WIP limit editor
  - Files: `resources/js/components/board/ColumnMenu.vue`

---

### Feature 3.6: Task Detail Side Panel

#### Frontend Tasks

- [ ] **PANEL-001** Create `TaskDetailPanel.vue` component
  - Slide from right, 480px width
  - Mobile overlay
  - Files: `resources/js/components/board/TaskDetailPanel.vue`

- [ ] **PANEL-002** Create panel header
  - Editable title
  - Status dropdown
  - Priority selector
  - Close button
  - Files: `resources/js/components/board/TaskDetailPanel.vue`

- [ ] **PANEL-003** Create panel tabs
  - Details, Subtasks, Comments, Activity, Files
  - Files: `resources/js/components/board/TaskDetailPanel.vue`

- [ ] **PANEL-004** Create Details tab content
  - Rich text description (TipTap/Quill)
  - Custom fields
  - Dependency links
  - Files: `resources/js/components/board/tabs/DetailsTab.vue`

- [ ] **PANEL-005** Implement panel close behavior
  - Escape key
  - Click outside
  - Files: `resources/js/components/board/TaskDetailPanel.vue`

- [ ] **PANEL-006** Implement URL deep linking
  - Update URL with task ID
  - Open panel on direct navigation
  - Files: `resources/js/components/board/TaskDetailPanel.vue`

- [ ] **PANEL-007** Add smooth open/close animation
  - Slide transition
  - Files: `resources/js/components/board/TaskDetailPanel.vue`

---

### Feature 3.7: Subtasks

#### Backend Tasks

- [ ] **SUB-001** Create `subtasks` migration and `Subtask` model
  - task_id, title, is_completed, position
  - Files: `database/migrations/xxxx_create_subtasks_table.php`, `app/Models/Subtask.php`

- [ ] **SUB-002** Create `SubtaskController` with CRUD
  - Include toggle completion endpoint
  - Files: `app/Http/Controllers/SubtaskController.php`

#### Frontend Tasks

- [ ] **SUB-003** Create `SubtaskList.vue` component
  - Add subtask input
  - Subtask items with checkbox
  - Drag to reorder
  - Delete with confirmation
  - Files: `resources/js/components/board/SubtaskList.vue`

- [ ] **SUB-004** Create subtask progress bar
  - Display on task card
  - Display in panel
  - Files: `resources/js/components/board/SubtaskProgress.vue`

---

### Feature 3.8: Comments

#### Backend Tasks

- [ ] **COMMENT-001** Create `comments` migration and `Comment` model
  - task_id, user_id, parent_id, body, edited_at
  - Files: `database/migrations/xxxx_create_comments_table.php`, `app/Models/Comment.php`

- [ ] **COMMENT-002** Create `CommentController` with CRUD
  - Threaded comments (max 3 levels)
  - Edit within 15 minutes validation
  - Files: `app/Http/Controllers/CommentController.php`

- [ ] **COMMENT-003** Create mention processing service
  - Extract @mentions
  - Create notifications
  - Files: `app/Services/MentionService.php`

#### Frontend Tasks

- [ ] **COMMENT-004** Create `CommentThread.vue` component
  - Markdown rendering
  - Threaded replies
  - Relative timestamps
  - Files: `resources/js/components/board/CommentThread.vue`

- [ ] **COMMENT-005** Create comment input with @mention autocomplete
  - User search dropdown
  - Files: `resources/js/components/board/CommentInput.vue`

- [ ] **COMMENT-006** Implement edit/delete functionality
  - Edit button (within 15 min)
  - Delete with "[deleted]" placeholder for threaded
  - Files: `resources/js/components/board/CommentThread.vue`

---

### Feature 3.9: Swimlanes

#### Backend Tasks

- [ ] **SWIM-001** Create `user_preferences` migration
  - Store swimlane preference per board
  - Files: `database/migrations/xxxx_create_user_preferences_table.php`

- [ ] **SWIM-002** Create endpoint for board preferences
  - Files: `app/Http/Controllers/BoardController.php`

#### Frontend Tasks

- [ ] **SWIM-003** Create `Swimlanes.vue` component
  - Group by: None, Assignee, Priority, Label
  - Collapsible rows
  - Task count per swimlane
  - Files: `resources/js/components/board/Swimlanes.vue`

- [ ] **SWIM-004** Implement cross-swimlane drag
  - Update grouping attribute on drop
  - Files: `resources/js/components/board/Swimlanes.vue`

- [ ] **SWIM-005** Persist swimlane preference
  - Save to user preferences
  - Load on board open
  - Files: `resources/js/stores/board.js`

---

## Feature Group 4: Student Performance Analytics

### Feature 4.1: Student Selector

#### Backend Tasks

- [ ] **SELECTOR-001** Create `StudentController@index` endpoint
  - Search by name
  - Include photo, name, current project
  - Files: `app/Http/Controllers/StudentController.php`

- [ ] **SELECTOR-002** Track recent student views
  - Store in user_preferences or separate table
  - Files: `app/Http/Controllers/StudentController.php`

#### Frontend Tasks

- [ ] **SELECTOR-003** Create `StudentSelector.vue` component
  - Searchable dropdown
  - Student photo, name, project
  - Recent students chips (last 5)
  - Compare mode (up to 3)
  - Clear selection button
  - Files: `resources/js/components/analytics/StudentSelector.vue`

- [ ] **SELECTOR-004** Implement URL state sync
  - Selected student IDs in URL
  - Files: `resources/js/components/analytics/StudentSelector.vue`

---

### Feature 4.2: Performance Radar Chart

#### Backend Tasks

- [ ] **RADAR-001** Create `StudentController@performance` endpoint
  - Calculate 6 dimensions (0-100)
  - Include class average
  - Files: `app/Http/Controllers/StudentController.php`

- [ ] **RADAR-002** Create performance calculation service
  - Code Quality, Deadline Adherence, Collaboration, Documentation, Problem Solving, Communication
  - Files: `app/Services/PerformanceCalculator.php`

#### Frontend Tasks

- [ ] **RADAR-003** Create `RadarChart.vue` component
  - 6-axis radar using Chart.js
  - Student polygon + class average overlay
  - Animation on load
  - Legend
  - Files: `resources/js/components/analytics/RadarChart.vue`

- [ ] **RADAR-004** Implement axis click → detail modal
  - Files: `resources/js/components/analytics/RadarChart.vue`

---

### Feature 4.3: Contribution Graph

#### Backend Tasks

- [ ] **CONTRIB-001** Create `StudentController@contributions` endpoint
  - Daily contribution counts for year
  - Breakdown by type
  - Summary stats (total, streak, most active day)
  - Files: `app/Http/Controllers/StudentController.php`

#### Frontend Tasks

- [ ] **CONTRIB-002** Create `ContributionGraph.vue` component
  - 52x7 grid (GitHub style)
  - Color intensity (4 levels + empty)
  - Year selector
  - Files: `resources/js/components/analytics/ContributionGraph.vue`

- [ ] **CONTRIB-003** Implement cell hover tooltip
  - Date, count, breakdown
  - Files: `resources/js/components/analytics/ContributionGraph.vue`

- [ ] **CONTRIB-004** Display summary statistics
  - Total, longest streak, most active day
  - Files: `resources/js/components/analytics/ContributionGraph.vue`

---

### Feature 4.4: Task Completion Funnel

#### Backend Tasks

- [ ] **FUNNEL-001** Create `StudentController@funnel` endpoint
  - 5 stages with counts
  - Conversion rates
  - Identify bottleneck
  - Files: `app/Http/Controllers/StudentController.php`

#### Frontend Tasks

- [ ] **FUNNEL-002** Create `TaskFunnel.vue` component
  - Vertical funnel visualization
  - Count + percentage per stage
  - Conversion rates between stages
  - Bottleneck highlight
  - Animation on load
  - Files: `resources/js/components/analytics/TaskFunnel.vue`

- [ ] **FUNNEL-003** Implement stage click → task list
  - Files: `resources/js/components/analytics/TaskFunnel.vue`

---

### Feature 4.5: Comparative Metrics Table

#### Backend Tasks

- [ ] **METRICS-001** Create `StudentController@metrics` endpoint
  - Metrics with student score, class average, percentile
  - Historical data for sparklines
  - Files: `app/Http/Controllers/StudentController.php`

#### Frontend Tasks

- [ ] **METRICS-002** Create `MetricsTable.vue` component
  - Columns: Name, Score, Average, Percentile, Sparkline
  - Sortable columns
  - Category filter
  - Conditional formatting (green/red)
  - Expandable rows
  - Sticky header
  - Files: `resources/js/components/analytics/MetricsTable.vue`

- [ ] **METRICS-003** Implement column visibility toggle
  - Files: `resources/js/components/analytics/MetricsTable.vue`

- [ ] **METRICS-004** Implement CSV export
  - Files: `resources/js/components/analytics/MetricsTable.vue`

---

### Feature 4.6: Skills Gap Analysis

#### Backend Tasks

- [ ] **GAP-001** Create `StudentController@skillsGap` endpoint
  - Required vs demonstrated levels
  - Recommended resources
  - Files: `app/Http/Controllers/StudentController.php`

#### Frontend Tasks

- [ ] **GAP-002** Create `SkillsGap.vue` component
  - Horizontal bar chart
  - Gap highlighted (red/orange)
  - Sorted by gap size
  - Category filter
  - Resources display
  - Files: `resources/js/components/analytics/SkillsGap.vue`

- [ ] **GAP-003** Implement skill click → trend view
  - Files: `resources/js/components/analytics/SkillsGap.vue`

---

### Feature 4.7: AI Insights Panel

#### Backend Tasks

- [ ] **AI-001** Create `StudentController@insights` endpoint
  - Generate 3-5 insights
  - Based on recent data changes
  - Files: `app/Http/Controllers/StudentController.php`

- [ ] **AI-002** Create AI insights service
  - Pattern detection
  - Insight generation
  - Files: `app/Services/AIInsightsService.php`

- [ ] **AI-003** Create insight feedback storage
  - helpful/not helpful tracking
  - Files: `database/migrations/xxxx_create_insight_feedback_table.php`

#### Frontend Tasks

- [ ] **AI-004** Create `AIInsights.vue` component
  - 3-5 insights display
  - Type icon (positive/negative/neutral)
  - Description + "Learn More" link
  - Refresh button
  - Feedback buttons
  - Loading state
  - Files: `resources/js/components/analytics/AIInsights.vue`

---

## Feature Group 5: Common Components

### Feature 5.1: Theme Toggle

#### Frontend Tasks

- [ ] **THEME-001** Create `useTheme` composable
  - Dark/light toggle
  - localStorage persistence
  - System preference detection
  - Files: `resources/js/composables/useTheme.js`

- [ ] **THEME-002** Create `ThemeToggle.vue` component
  - Sun/moon icons
  - Toggle button in navbar
  - Files: `resources/js/components/common/ThemeToggle.vue`

- [ ] **THEME-003** Implement CSS theme variables
  - Dark and light color schemes
  - 300ms transition
  - Files: `resources/css/themes.css`

- [ ] **THEME-004** Update all components to use theme variables
  - Files: Various

---

### Feature 5.2: RTL Support

#### Frontend Tasks

- [ ] **RTL-001** Create `useDirection` composable
  - LTR/RTL toggle
  - localStorage persistence
  - Files: `resources/js/composables/useDirection.js`

- [ ] **RTL-002** Create direction toggle button
  - Files: `resources/js/components/common/DirectionToggle.vue`

- [ ] **RTL-003** Implement RTL CSS using logical properties
  - Replace left/right with start/end
  - Mirror directional icons
  - Files: Various CSS files

- [ ] **RTL-004** Test and fix RTL layout issues
  - Sidebar position
  - Text alignment
  - Files: Various

---

### Feature 5.3: Global Search

#### Backend Tasks

- [ ] **SEARCH-001** Create `SearchController` endpoint
  - Search projects, tasks, users
  - Paginated results grouped by type
  - Files: `app/Http/Controllers/SearchController.php`

#### Frontend Tasks

- [ ] **SEARCH-002** Create `GlobalSearch.vue` modal
  - Ctrl+K keyboard shortcut
  - Search input (auto-focus)
  - Results grouped by type
  - Keyboard navigation
  - Recent searches
  - Files: `resources/js/components/common/GlobalSearch.vue`

- [ ] **SEARCH-003** Implement search result navigation
  - Enter to navigate
  - Escape to close
  - Files: `resources/js/components/common/GlobalSearch.vue`

---

### Feature 5.4: Notifications

#### Backend Tasks

- [ ] **NOTIF-001** Create `notifications` migration and model
  - user_id, type, data, read_at
  - Files: `database/migrations/xxxx_create_notifications_table.php`, `app/Models/Notification.php`

- [ ] **NOTIF-002** Create `NotificationController`
  - List notifications
  - Mark as read
  - Mark all as read
  - Files: `app/Http/Controllers/NotificationController.php`

- [ ] **NOTIF-003** Create notification types and triggers
  - Task assigned
  - Mentioned in comment
  - Deadline approaching
  - Project update
  - Files: `app/Services/NotificationService.php`

- [ ] **NOTIF-004** Set up notification broadcasting
  - Files: `app/Events/NotificationCreated.php`

#### Frontend Tasks

- [ ] **NOTIF-005** Create `NotificationBell.vue` component
  - Bell icon in navbar
  - Unread count badge
  - Dropdown with notifications
  - Mark as read on click
  - Mark all as read
  - Files: `resources/js/components/common/NotificationBell.vue`

- [ ] **NOTIF-006** Implement real-time notification updates
  - WebSocket connection
  - Files: `resources/js/components/common/NotificationBell.vue`

---

### Feature 5.5: Toast Notifications

#### Frontend Tasks

- [ ] **TOAST-001** Create `useToast` composable
  - add(message, type, duration)
  - Types: success, error, warning, info
  - Auto-dismiss (5s)
  - Files: `resources/js/composables/useToast.js`

- [ ] **TOAST-002** Create `Toast.vue` component
  - Position: bottom-right
  - Color by type
  - Close button
  - Stack multiple
  - Files: `resources/js/components/common/Toast.vue`

- [ ] **TOAST-003** Create `ToastContainer.vue`
  - Manages toast stack
  - Slide in/out animations
  - Files: `resources/js/components/common/ToastContainer.vue`

---

## Priority Order

### Phase 1: Critical Path (Weeks 1-2)
1. AUTH-001 to AUTH-009 (Authentication)
2. RBAC-001 to RBAC-009 (Authorization)
3. BOARD-001 to BOARD-004 (Board backend)
4. CARD-001 to CARD-004 (Task backend)

### Phase 2: Core Features (Weeks 3-4)
5. BOARD-005 to BOARD-008 (Board frontend)
6. CARD-005 to CARD-007 (Task cards)
7. DND-001 to DND-006 (Drag and drop)
8. WIP-001 to WIP-005 (WIP limits)

### Phase 3: Task Management (Weeks 5-6)
9. PANEL-001 to PANEL-007 (Detail panel)
10. SUB-001 to SUB-004 (Subtasks)
11. COMMENT-001 to COMMENT-006 (Comments)
12. SWIM-001 to SWIM-005 (Swimlanes)

### Phase 4: Dashboard (Weeks 7-8)
13. DASH-001 to DASH-006 (Summary stats)
14. MATRIX-001 to MATRIX-004 (Health matrix)
15. CHART-001 to CHART-004 (Progress chart)
16. FEED-001 to FEED-008 (Activity feed)
17. HEATMAP-001 to HEATMAP-005 (Deadline heatmap)

### Phase 5: Analytics (Weeks 9-10)
18. SELECTOR-001 to SELECTOR-004 (Student selector)
19. RADAR-001 to RADAR-004 (Radar chart)
20. CONTRIB-001 to CONTRIB-004 (Contribution graph)
21. FUNNEL-001 to FUNNEL-003 (Task funnel)
22. METRICS-001 to METRICS-004 (Metrics table)
23. GAP-001 to GAP-003 (Skills gap)
24. AI-001 to AI-004 (AI insights)

### Phase 6: Polish (Weeks 11-12)
25. HEADER-001 to HEADER-006 (Board header)
26. THEME-001 to THEME-004 (Theme toggle)
27. RTL-001 to RTL-004 (RTL support)
28. SEARCH-001 to SEARCH-003 (Global search)
29. NOTIF-001 to NOTIF-006 (Notifications)
30. TOAST-001 to TOAST-003 (Toast notifications)

---

## Task Dependencies

```
AUTH-* ─────────────────────────────────┐
                                        ▼
RBAC-* ─────────────────────────────► BOARD-* ───► CARD-* ───► DND-*
                                        │              │          │
                                        │              ▼          ▼
                                        │          PANEL-* ───► WIP-*
                                        │              │
                                        │              ▼
                                        │          SUB-* ───► COMMENT-*
                                        │
                                        ▼
                                    SWIM-* ───► DASH-* ───► Analytics
                                                  │
                                                  ▼
                                            Common Components
```
