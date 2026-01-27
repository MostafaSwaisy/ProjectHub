# ProjectHub Analytics - Implementation Checklist

> Track your progress by checking off items as you complete them.
> Use `[x]` to mark completed items.

---

## Quick Stats

| Category | Total | Done | Progress |
|----------|-------|------|----------|
| Setup & Configuration | 12 | 0 | 0% |
| Feature Group 1: Auth | 25 | 0 | 0% |
| Feature Group 2: Dashboard | 45 | 0 | 0% |
| Feature Group 3: Kanban | 75 | 0 | 0% |
| Feature Group 4: Analytics | 65 | 0 | 0% |
| Feature Group 5: Common | 40 | 0 | 0% |
| **Total** | **262** | **0** | **0%** |

---

## Phase 0: Project Setup

### Package Installation

- [ ] Install Laravel Sanctum: `composer require laravel/sanctum`
- [ ] Install Pusher: `composer require pusher/pusher-php-server`
- [ ] Publish Sanctum config: `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
- [ ] Install Vue 3: `npm install vue@3`
- [ ] Install Vue Router: `npm install vue-router@4`
- [ ] Install Pinia: `npm install pinia`
- [ ] Install VueUse: `npm install @vueuse/core`
- [ ] Install HeadlessUI: `npm install @headlessui/vue`
- [ ] Install Heroicons: `npm install @heroicons/vue`
- [ ] Install Chart.js: `npm install chart.js vue-chartjs`
- [ ] Install DnD Kit: `npm install @dnd-kit/core @dnd-kit/sortable`
- [ ] Install date-fns: `npm install date-fns`

### Vite Configuration

- [ ] Configure Vue plugin in `vite.config.js`
- [ ] Set up path aliases (`@/` for `resources/js/`)
- [ ] Configure Tailwind CSS 4

### Directory Structure

- [ ] Create `resources/js/components/` directory
- [ ] Create `resources/js/pages/` directory
- [ ] Create `resources/js/composables/` directory
- [ ] Create `resources/js/stores/` directory
- [ ] Create `resources/js/router/` directory
- [ ] Create `resources/js/utils/` directory
- [ ] Create `app/Services/` directory
- [ ] Create `app/Policies/` directory

---

## Feature Group 1: Authentication & Authorization

### Feature 1.1: User Authentication

#### Migrations & Models

- [ ] Create `roles` migration with columns: id, name, created_at, updated_at
- [ ] Add `role_id` foreign key to users table migration
- [ ] Create `Role` model with `users()` relationship
- [ ] Update `User` model with `role()` relationship
- [ ] Seed default roles: admin, instructor, student

#### Backend - Registration

- [ ] Create `app/Http/Requests/Auth/RegisterRequest.php`
  - [ ] Validate name: required, string, max:255
  - [ ] Validate email: required, email, unique:users
  - [ ] Validate password: required, min:8, regex:/[0-9]/
  - [ ] Validate role: required, exists:roles,name
- [ ] Create `app/Http/Controllers/Auth/RegisterController.php`
  - [ ] Create user with hashed password
  - [ ] Assign role to user
  - [ ] Generate Sanctum token
  - [ ] Return user data with token

#### Backend - Login

- [ ] Create `app/Http/Requests/Auth/LoginRequest.php`
  - [ ] Validate email: required, email
  - [ ] Validate password: required
- [ ] Create `app/Http/Controllers/Auth/LoginController.php`
  - [ ] Verify credentials
  - [ ] Generate Sanctum token
  - [ ] Return user data with token
  - [ ] Return 401 for invalid credentials

#### Backend - Logout

- [ ] Create `app/Http/Controllers/Auth/LogoutController.php`
  - [ ] Delete current access token
  - [ ] Return success response

#### Backend - Password Reset

- [ ] Create `app/Http/Controllers/Auth/PasswordResetController.php`
  - [ ] `sendLink()`: Generate token, send email
  - [ ] `reset()`: Validate token, update password
- [ ] Create password reset email template
- [ ] Add token expiration check (default 60 minutes)

#### API Routes

- [ ] Add `POST /api/register` route
- [ ] Add `POST /api/login` route
- [ ] Add `POST /api/logout` route (protected)
- [ ] Add `POST /api/password/forgot` route
- [ ] Add `POST /api/password/reset` route
- [ ] Add `GET /api/user` route (protected)

#### Frontend - Pages

- [ ] Create `resources/js/pages/auth/Login.vue`
  - [ ] Email input with validation
  - [ ] Password input with validation
  - [ ] Submit button with loading state
  - [ ] Error message display
  - [ ] Link to register page
  - [ ] Link to forgot password
- [ ] Create `resources/js/pages/auth/Register.vue`
  - [ ] Name input with validation
  - [ ] Email input with validation
  - [ ] Password input with validation (show requirements)
  - [ ] Role selection dropdown
  - [ ] Submit button with loading state
  - [ ] Link to login page
- [ ] Create `resources/js/pages/auth/ForgotPassword.vue`
  - [ ] Email input
  - [ ] Submit button
  - [ ] Success message display
- [ ] Create `resources/js/pages/auth/ResetPassword.vue`
  - [ ] New password input
  - [ ] Confirm password input
  - [ ] Submit button
  - [ ] Handle token from URL

#### Frontend - Composables & Stores

- [ ] Create `resources/js/stores/auth.js` (Pinia)
  - [ ] `user` state
  - [ ] `token` state
  - [ ] `isAuthenticated` getter
  - [ ] `login()` action
  - [ ] `register()` action
  - [ ] `logout()` action
  - [ ] `fetchUser()` action
- [ ] Create `resources/js/composables/useAuth.js`
  - [ ] Wrap store with convenience methods
  - [ ] Handle token storage in localStorage

---

### Feature 1.2: Role-Based Access Control

#### Backend - Middleware

- [ ] Create `app/Http/Middleware/RoleMiddleware.php`
  - [ ] Check if user is authenticated
  - [ ] Check if user role matches allowed roles
  - [ ] Return 403 with error message if unauthorized
- [ ] Register middleware in `bootstrap/app.php`
- [ ] Add middleware alias: `role`

#### Backend - Policies

- [ ] Create `app/Policies/ProjectPolicy.php`
  - [ ] `viewAny()`: All authenticated users
  - [ ] `view()`: Admin, owner instructor, or member student
  - [ ] `create()`: Admin, instructor
  - [ ] `update()`: Admin, owner instructor
  - [ ] `delete()`: Admin, owner instructor
- [ ] Create `app/Policies/TaskPolicy.php`
  - [ ] `viewAny()`: Project members
  - [ ] `view()`: Project members
  - [ ] `create()`: Project members
  - [ ] `update()`: Project members
  - [ ] `delete()`: Admin, instructor, task creator
- [ ] Create `app/Policies/UserPolicy.php`
  - [ ] `viewAny()`: Admin, instructors (own students)
  - [ ] `view()`: Admin, instructor (own student), self
  - [ ] `viewAnalytics()`: Admin, instructor (own student), self
- [ ] Register policies in `AuthServiceProvider`

#### Frontend - Route Guards

- [ ] Create `resources/js/router/guards.js`
  - [ ] `requireAuth`: Redirect to login if not authenticated
  - [ ] `requireRole`: Check user role, redirect if unauthorized
  - [ ] `requireGuest`: Redirect to dashboard if authenticated
- [ ] Apply guards to routes in `router/index.js`

#### Frontend - Permission Helpers

- [ ] Create `resources/js/composables/usePermissions.js`
  - [ ] `can(action, resource)` method
  - [ ] `isAdmin()` helper
  - [ ] `isInstructor()` helper
  - [ ] `isStudent()` helper

---

## Feature Group 2: Dashboard Analytics

### Feature 2.1: Summary Statistics

#### Backend

- [ ] Create `app/Http/Controllers/DashboardController.php`
- [ ] Implement `stats()` method
  - [ ] Calculate total projects count
  - [ ] Calculate 7-day project trend data
  - [ ] Calculate active tasks count
  - [ ] Calculate completion percentage
  - [ ] Get online team members (with avatars)
  - [ ] Calculate overdue items count
  - [ ] Calculate percentage changes from previous period
- [ ] Create `app/Services/DashboardService.php`
  - [ ] `getProjectTrend($days)` method
  - [ ] `getCompletionRate()` method
  - [ ] `getOnlineMembers()` method
  - [ ] `calculatePeriodChange()` method
- [ ] Add `GET /api/dashboard/stats` route

#### Frontend

- [ ] Create `resources/js/components/dashboard/SummaryStats.vue`
  - [ ] Grid layout for 4 stat cards
  - [ ] Fetch data on mount
  - [ ] Auto-refresh every 30 seconds
- [ ] Create `resources/js/components/dashboard/StatCard.vue`
  - [ ] Display count value
  - [ ] Display sparkline chart (7-day trend)
  - [ ] Display percentage change badge
  - [ ] Pulsing indicator for overdue (when > 0)
- [ ] Create `resources/js/components/common/SkeletonLoader.vue`
  - [ ] Animated placeholder
  - [ ] Configurable height/width
- [ ] Create `resources/js/components/dashboard/AvatarStack.vue`
  - [ ] Display max 5 avatars
  - [ ] Show "+X" overflow indicator

---

### Feature 2.2: Project Health Matrix

#### Backend

- [ ] Add `timeline_status` enum to projects: behind, on_track, ahead
- [ ] Add `budget_status` enum to projects: over_budget, on_budget, under_budget
- [ ] Implement `healthMatrix()` in DashboardController
  - [ ] Group projects by timeline_status and budget_status
  - [ ] Return 3x3 matrix with counts
- [ ] Add `GET /api/dashboard/health-matrix` route

#### Frontend

- [ ] Create `resources/js/components/dashboard/ProjectHealthMatrix.vue`
  - [ ] Render 3x3 grid
  - [ ] X-axis labels: Behind, On Track, Ahead
  - [ ] Y-axis labels: Over Budget, On Budget, Under Budget
  - [ ] Display count in each cell
  - [ ] Color intensity based on count
  - [ ] Click handler to filter projects
  - [ ] Emit filter event to parent

---

### Feature 2.3: Progress Chart

#### Backend

- [ ] Implement `progress()` in DashboardController
  - [ ] Accept `range` parameter (7, 30, 90, or custom dates)
  - [ ] Calculate weekly completed tasks
  - [ ] Calculate cumulative progress percentage
  - [ ] Return data points for chart
- [ ] Add `GET /api/dashboard/progress` route

#### Frontend

- [ ] Create `resources/js/components/dashboard/ProgressChart.vue`
  - [ ] Dual-axis chart using vue-chartjs
  - [ ] Bar chart for weekly completed tasks
  - [ ] Line chart for cumulative progress
  - [ ] Time range selector (7, 30, 90, custom)
  - [ ] Tooltip on hover showing exact values
  - [ ] Animation on initial load
  - [ ] Responsive sizing
- [ ] Implement PNG export functionality
- [ ] Implement CSV export functionality

---

### Feature 2.4: Activity Feed

#### Backend

- [ ] Create `activities` migration
  - [ ] id, user_id, project_id, type, subject_type, subject_id, data (JSON), created_at
- [ ] Create `app/Models/Activity.php`
  - [ ] Relationships: user, project, subject (morphTo)
  - [ ] Scope: `byType($type)`
- [ ] Create `app/Services/ActivityLogger.php`
  - [ ] `log($type, $subject, $data)` method
  - [ ] Auto-capture user and project context
- [ ] Create `app/Http/Controllers/ActivityController.php`
  - [ ] `index()`: Paginated list, filter by type
- [ ] Create `app/Events/ActivityCreated.php` for broadcasting
- [ ] Add `GET /api/activities` route

#### Frontend

- [ ] Create `resources/js/components/dashboard/ActivityFeed.vue`
  - [ ] Filter tabs: All, Comments, Status Changes, File Uploads
  - [ ] Activity list with infinite scroll
  - [ ] Loading skeleton state
  - [ ] Real-time updates via WebSocket
- [ ] Create `resources/js/components/dashboard/ActivityItem.vue`
  - [ ] User avatar
  - [ ] Action description
  - [ ] Relative timestamp (using date-fns)
  - [ ] Project tag
  - [ ] Click to navigate
- [ ] Create `resources/js/composables/useInfiniteScroll.js`
  - [ ] Detect scroll to bottom
  - [ ] Trigger load more callback
  - [ ] Loading state management

---

### Feature 2.5: Deadline Heatmap Calendar

#### Backend

- [ ] Implement `deadlines()` in DashboardController
  - [ ] Accept `month` parameter (YYYY-MM)
  - [ ] Query tasks with due dates in month
  - [ ] Group by date, count deadlines per day
  - [ ] Return date → count mapping
- [ ] Add `GET /api/dashboard/deadlines` route

#### Frontend

- [ ] Create `resources/js/components/dashboard/DeadlineHeatmap.vue`
  - [ ] Month calendar grid (7 columns × 5-6 rows)
  - [ ] Day of week headers
  - [ ] Color coding: green (1-2), yellow (3-5), red (6+), neutral (0)
  - [ ] Current day border highlight
  - [ ] Month navigation (prev/next)
  - [ ] Slide animation on month change
- [ ] Implement day hover popover
  - [ ] List of deadlines for that day
  - [ ] Task titles with links
- [ ] Implement day click navigation
  - [ ] Navigate to tasks page with date filter

---

## Feature Group 3: Kanban Project Board

### Feature 3.1: Board Layout

#### Backend

- [ ] Create `projects` migration
  - [ ] id, title, description, instructor_id, timeline_status, budget_status, timestamps
- [ ] Create `project_members` migration
  - [ ] project_id, user_id, role (member/viewer), joined_at
- [ ] Create `boards` migration
  - [ ] id, project_id, title, timestamps
- [ ] Create `columns` migration
  - [ ] id, board_id, title, position, wip_limit (default 0), timestamps
- [ ] Create `app/Models/Project.php`
  - [ ] Relationships: instructor, members, boards
- [ ] Create `app/Models/Board.php`
  - [ ] Relationships: project, columns, tasks
  - [ ] Boot: Create default columns on create
- [ ] Create `app/Models/Column.php`
  - [ ] Relationships: board, tasks
  - [ ] `isAtLimit()` method
  - [ ] `isOverLimit()` method
- [ ] Create `app/Http/Controllers/BoardController.php`
  - [ ] `show()`: Return board with columns and tasks
- [ ] Add `GET /api/boards/{board}` route

#### Frontend

- [ ] Create `resources/js/pages/ProjectBoard.vue`
  - [ ] Fetch board data on mount
  - [ ] Render BoardHeader and KanbanBoard
- [ ] Create `resources/js/components/board/KanbanBoard.vue`
  - [ ] Horizontal scrollable container
  - [ ] CSS scroll-snap for columns
  - [ ] Render BoardColumn for each column
  - [ ] Mobile: Single column view with swipe
- [ ] Create `resources/js/components/board/BoardColumn.vue`
  - [ ] Min-width: 280px, max-width: 320px
  - [ ] Column header with title and task count
  - [ ] Task card list
  - [ ] Drop zone for drag-and-drop
- [ ] Create `resources/js/stores/board.js`
  - [ ] Board state
  - [ ] Columns state
  - [ ] Tasks by column

---

### Feature 3.2: Board Header

#### Frontend

- [ ] Create `resources/js/components/board/BoardHeader.vue`
  - [ ] Project title (inline editable)
  - [ ] Member avatars with add button
  - [ ] View toggle (Kanban/List/Timeline)
  - [ ] Filter bar component
- [ ] Implement inline title editing
  - [ ] Click to edit
  - [ ] Enter to save
  - [ ] Escape to cancel
  - [ ] API call to update
- [ ] Create `resources/js/components/board/FilterBar.vue`
  - [ ] Assignee multi-select dropdown
  - [ ] Label filter dropdown
  - [ ] Due date range picker
  - [ ] Search input
  - [ ] Clear all filters button
- [ ] Create `resources/js/composables/useFilters.js`
  - [ ] Filter state management
  - [ ] URL query param sync
  - [ ] Filter application logic

---

### Feature 3.3: Task Cards

#### Backend

- [ ] Create `tasks` migration
  - [ ] id, column_id, title, description (text), assignee_id, priority (enum), due_date, position, timestamps
- [ ] Create `labels` migration
  - [ ] id, project_id, name, color, timestamps
- [ ] Create `task_labels` pivot migration
  - [ ] task_id, label_id
- [ ] Create `app/Models/Task.php`
  - [ ] Relationships: column, assignee, labels, subtasks, comments
  - [ ] `isOverdue()` accessor
  - [ ] `subtaskProgress()` accessor
- [ ] Create `app/Models/Label.php`
- [ ] Create `app/Http/Controllers/TaskController.php`
  - [ ] Full CRUD operations
- [ ] Create `app/Http/Resources/TaskResource.php`
  - [ ] Include computed fields
- [ ] Add task CRUD routes

#### Frontend

- [ ] Create `resources/js/components/board/TaskCard.vue`
  - [ ] Title (2 lines max, ellipsis)
  - [ ] Priority color border (left side)
  - [ ] Assignee avatar
  - [ ] Due date (red if overdue)
  - [ ] Subtask progress bar
  - [ ] Labels (max 3 + overflow count)
  - [ ] Hover: Show quick actions
  - [ ] Shadow elevation on hover
  - [ ] Click: Open detail panel
  - [ ] Draggable attribute

---

### Feature 3.4: Drag and Drop

#### Backend

- [ ] Implement `move()` in TaskController
  - [ ] Accept: task_id, column_id, position
  - [ ] Validate WIP limit
  - [ ] Update task column and position
  - [ ] Reorder affected tasks
  - [ ] Log activity
- [ ] Create `app/Events/TaskMoved.php`
  - [ ] Broadcast to project channel
- [ ] Add `PUT /api/tasks/{task}/move` route

#### Frontend

- [ ] Implement drag-and-drop with @dnd-kit
  - [ ] DndContext provider in KanbanBoard
  - [ ] Draggable wrapper for TaskCard
  - [ ] Droppable wrapper for BoardColumn
- [ ] Create drop placeholder indicator
  - [ ] Visual line/box at drop position
- [ ] Implement optimistic updates
  - [ ] Move card immediately in UI
  - [ ] API call in background
  - [ ] Rollback on error with toast
- [ ] Add touch support for mobile
  - [ ] Touch drag sensors
  - [ ] Touch-friendly drop zones

---

### Feature 3.5: Column WIP Limits

#### Backend

- [ ] Add WIP validation to task move
  - [ ] Check column.wip_limit
  - [ ] Return 422 if would exceed limit

#### Frontend

- [ ] Display WIP badge in column header
  - [ ] Format: "3/5" (current/limit)
  - [ ] Hide if limit is 0 (unlimited)
- [ ] Implement visual indicators
  - [ ] Yellow border: at limit
  - [ ] Red border: over limit
- [ ] Block drops when over limit
  - [ ] Visual feedback (red highlight)
  - [ ] Show toast message
- [ ] Create `resources/js/components/board/ColumnMenu.vue`
  - [ ] Edit column name
  - [ ] Set WIP limit
  - [ ] Delete column (with confirmation)

---

### Feature 3.6: Task Detail Side Panel

#### Frontend

- [ ] Create `resources/js/components/board/TaskDetailPanel.vue`
  - [ ] Slide-out from right
  - [ ] Width: 480px (desktop), full-screen (mobile)
  - [ ] Smooth animation (300ms)
- [ ] Implement panel header
  - [ ] Editable title (inline)
  - [ ] Status dropdown
  - [ ] Priority selector (colors)
  - [ ] Close button (X)
- [ ] Implement tab navigation
  - [ ] Details tab
  - [ ] Subtasks tab
  - [ ] Comments tab
  - [ ] Activity tab
  - [ ] Files tab
- [ ] Create `resources/js/components/board/tabs/DetailsTab.vue`
  - [ ] Rich text description (TipTap or Quill)
  - [ ] Custom fields display
  - [ ] Dependency links
- [ ] Implement close behaviors
  - [ ] Escape key closes
  - [ ] Click outside closes
- [ ] Implement URL deep linking
  - [ ] Update URL with task ID
  - [ ] Open panel on direct URL access

---

### Feature 3.7: Subtasks

#### Backend

- [ ] Create `subtasks` migration
  - [ ] id, task_id, title, is_completed, position, timestamps
- [ ] Create `app/Models/Subtask.php`
- [ ] Create `app/Http/Controllers/SubtaskController.php`
  - [ ] CRUD operations
  - [ ] Toggle completion endpoint
  - [ ] Reorder endpoint
- [ ] Add subtask routes

#### Frontend

- [ ] Create `resources/js/components/board/SubtaskList.vue`
  - [ ] Add subtask input
  - [ ] Subtask items with checkbox
  - [ ] Drag handle for reordering
  - [ ] Delete button (with confirmation)
- [ ] Create `resources/js/components/board/SubtaskProgress.vue`
  - [ ] Progress bar component
  - [ ] "3/5 completed" text
  - [ ] Used on TaskCard and in panel

---

### Feature 3.8: Comments

#### Backend

- [ ] Create `comments` migration
  - [ ] id, task_id, user_id, parent_id (nullable), body (text), edited_at (nullable), timestamps
- [ ] Create `app/Models/Comment.php`
  - [ ] Relationships: task, user, parent, replies
  - [ ] `canEdit()` method (within 15 min)
  - [ ] `isDeleted()` accessor
- [ ] Create `app/Services/MentionService.php`
  - [ ] Extract @mentions from body
  - [ ] Create notifications for mentioned users
- [ ] Create `app/Http/Controllers/CommentController.php`
  - [ ] `store()`: Create with mention processing
  - [ ] `update()`: Validate 15-minute window
  - [ ] `destroy()`: Soft-delete if has replies
- [ ] Add comment routes

#### Frontend

- [ ] Create `resources/js/components/board/CommentThread.vue`
  - [ ] Recursive comment rendering
  - [ ] Max 3 levels deep
  - [ ] Reply button
  - [ ] Relative timestamps
- [ ] Create `resources/js/components/board/CommentItem.vue`
  - [ ] User avatar and name
  - [ ] Markdown rendered body
  - [ ] Edit/Delete buttons (conditional)
  - [ ] "[deleted]" placeholder
- [ ] Create `resources/js/components/board/CommentInput.vue`
  - [ ] Markdown text area
  - [ ] @mention autocomplete
  - [ ] Submit button
- [ ] Implement @mention autocomplete
  - [ ] Trigger on "@" character
  - [ ] Search users API
  - [ ] Insert selected user

---

### Feature 3.9: Swimlanes

#### Backend

- [ ] Create `user_preferences` migration
  - [ ] user_id, key, value (JSON), timestamps
- [ ] Create endpoint to save/load board preferences
- [ ] Add preference routes

#### Frontend

- [ ] Create `resources/js/components/board/Swimlanes.vue`
  - [ ] Grouping options: None, Assignee, Priority, Label
  - [ ] Group tasks into swimlane rows
  - [ ] Collapsible swimlane headers
  - [ ] Task count per swimlane
- [ ] Implement swimlane rendering
  - [ ] Render columns within each swimlane
  - [ ] Maintain card column positions
- [ ] Implement cross-swimlane drag
  - [ ] Update grouping attribute on drop
- [ ] Persist swimlane preference
  - [ ] Save to user_preferences
  - [ ] Load on board open

---

## Feature Group 4: Student Performance Analytics

### Feature 4.1: Student Selector

#### Backend

- [ ] Create `app/Http/Controllers/StudentController.php`
- [ ] Implement `index()` method
  - [ ] Search by name
  - [ ] Filter by instructor's projects
  - [ ] Include photo, name, current project
- [ ] Store recent student views in user_preferences
- [ ] Add `GET /api/students` route

#### Frontend

- [ ] Create `resources/js/components/analytics/StudentSelector.vue`
  - [ ] Searchable dropdown
  - [ ] Student item: photo, name, project
  - [ ] Recent students chips (last 5)
  - [ ] Compare mode toggle
  - [ ] Multi-select (up to 3 in compare mode)
  - [ ] Clear selection button
- [ ] Implement URL state sync
  - [ ] Update URL with student IDs
  - [ ] Read from URL on mount

---

### Feature 4.2: Performance Radar Chart

#### Backend

- [ ] Create `app/Services/PerformanceCalculator.php`
  - [ ] `calculateCodeQuality($student)` method
  - [ ] `calculateDeadlineAdherence($student)` method
  - [ ] `calculateCollaboration($student)` method
  - [ ] `calculateDocumentation($student)` method
  - [ ] `calculateProblemSolving($student)` method
  - [ ] `calculateCommunication($student)` method
  - [ ] `getClassAverage($project)` method
- [ ] Implement `performance()` in StudentController
- [ ] Add `GET /api/students/{id}/performance` route

#### Frontend

- [ ] Create `resources/js/components/analytics/RadarChart.vue`
  - [ ] 6-axis radar chart (Chart.js)
  - [ ] Student score polygon (filled)
  - [ ] Class average overlay (line)
  - [ ] Scale 0-100 on each axis
  - [ ] Animation on load
  - [ ] Legend (student vs average)
- [ ] Implement axis click interaction
  - [ ] Open detail modal for clicked dimension
  - [ ] Show breakdown and trend

---

### Feature 4.3: Contribution Graph

#### Backend

- [ ] Implement `contributions()` in StudentController
  - [ ] Query activities for student by year
  - [ ] Group by date
  - [ ] Calculate contribution counts
  - [ ] Calculate summary stats
- [ ] Add `GET /api/students/{id}/contributions` route

#### Frontend

- [ ] Create `resources/js/components/analytics/ContributionGraph.vue`
  - [ ] 52×7 grid (GitHub style)
  - [ ] Color intensity (4 levels + empty)
  - [ ] Year selector dropdown
  - [ ] Responsive: horizontal scroll on mobile
- [ ] Implement cell hover tooltip
  - [ ] Date
  - [ ] Contribution count
  - [ ] Breakdown by type
- [ ] Display summary statistics
  - [ ] Total contributions
  - [ ] Longest streak
  - [ ] Most active day of week

---

### Feature 4.4: Task Completion Funnel

#### Backend

- [ ] Implement `funnel()` in StudentController
  - [ ] Count tasks at each stage
  - [ ] Calculate percentages
  - [ ] Calculate conversion rates
  - [ ] Identify bottleneck stage
- [ ] Add `GET /api/students/{id}/funnel` route

#### Frontend

- [ ] Create `resources/js/components/analytics/TaskFunnel.vue`
  - [ ] Vertical funnel visualization
  - [ ] 5 stages: Assigned → Started → In Progress → Review → Completed
  - [ ] Count and percentage per stage
  - [ ] Conversion rate between stages
  - [ ] Bottleneck highlight (lowest conversion)
  - [ ] Animation on load
- [ ] Implement stage click
  - [ ] Show task list modal
  - [ ] Filter by clicked stage

---

### Feature 4.5: Comparative Metrics Table

#### Backend

- [ ] Implement `metrics()` in StudentController
  - [ ] Calculate various metrics
  - [ ] Include class averages
  - [ ] Calculate percentile ranks
  - [ ] Include historical data for sparklines
- [ ] Add `GET /api/students/{id}/metrics` route

#### Frontend

- [ ] Create `resources/js/components/analytics/MetricsTable.vue`
  - [ ] Columns: Metric, Score, Average, Percentile, Trend
  - [ ] Sortable columns
  - [ ] Category filter
  - [ ] Conditional formatting (green/red)
  - [ ] Expandable rows
  - [ ] Sticky header
- [ ] Implement column visibility toggle
- [ ] Implement CSV export

---

### Feature 4.6: Skills Gap Analysis

#### Backend

- [ ] Implement `skillsGap()` in StudentController
  - [ ] Define required skill levels
  - [ ] Calculate demonstrated levels
  - [ ] Calculate gaps
  - [ ] Include recommended resources
- [ ] Add `GET /api/students/{id}/skills-gap` route

#### Frontend

- [ ] Create `resources/js/components/analytics/SkillsGap.vue`
  - [ ] Horizontal bar chart
  - [ ] Required level (full bar)
  - [ ] Demonstrated level (filled portion)
  - [ ] Gap highlighted (red/orange)
  - [ ] Sorted by gap size (largest first)
  - [ ] Category filter
  - [ ] Resource recommendations display
- [ ] Implement skill click
  - [ ] Show trend over time modal

---

### Feature 4.7: AI Insights Panel

#### Backend

- [ ] Create `app/Services/AIInsightsService.php`
  - [ ] Analyze recent activity patterns
  - [ ] Generate 3-5 insights
  - [ ] Classify as positive/negative/neutral
- [ ] Create `insight_feedback` migration
  - [ ] insight_id, user_id, is_helpful, timestamps
- [ ] Implement `insights()` in StudentController
- [ ] Add insight feedback endpoint
- [ ] Add routes

#### Frontend

- [ ] Create `resources/js/components/analytics/AIInsights.vue`
  - [ ] Insight cards (3-5)
  - [ ] Type icon (✓ positive, ⚠ negative, ℹ neutral)
  - [ ] Description text
  - [ ] "Learn More" link
  - [ ] Refresh button
  - [ ] Feedback buttons (helpful/not helpful)
  - [ ] Loading state with skeleton

---

## Feature Group 5: Common Components

### Feature 5.1: Theme Toggle

#### Frontend

- [ ] Create `resources/js/composables/useTheme.js`
  - [ ] `isDark` reactive state
  - [ ] `toggle()` method
  - [ ] Save to localStorage
  - [ ] Detect system preference
  - [ ] Apply theme class to document
- [ ] Create `resources/js/components/common/ThemeToggle.vue`
  - [ ] Sun icon (for switching to light)
  - [ ] Moon icon (for switching to dark)
  - [ ] Toggle button in navbar
- [ ] Create theme CSS variables
  - [ ] Light theme colors
  - [ ] Dark theme colors
  - [ ] 300ms transition
- [ ] Update all components to use theme variables

---

### Feature 5.2: RTL Support

#### Frontend

- [ ] Create `resources/js/composables/useDirection.js`
  - [ ] `isRTL` reactive state
  - [ ] `toggle()` method
  - [ ] Save to localStorage
  - [ ] Apply `dir` attribute to document
- [ ] Create `resources/js/components/common/DirectionToggle.vue`
- [ ] Update CSS to use logical properties
  - [ ] Replace `left` with `inset-inline-start`
  - [ ] Replace `right` with `inset-inline-end`
  - [ ] Replace `margin-left` with `margin-inline-start`
  - [ ] Replace `text-align: left` with `text-align: start`
- [ ] Mirror directional icons in RTL
- [ ] Test all layouts in RTL mode

---

### Feature 5.3: Global Search

#### Backend

- [ ] Create `app/Http/Controllers/SearchController.php`
  - [ ] Search projects by title
  - [ ] Search tasks by title
  - [ ] Search users by name
  - [ ] Respect access permissions
  - [ ] Return grouped results
- [ ] Add `GET /api/search` route

#### Frontend

- [ ] Create `resources/js/components/common/GlobalSearch.vue`
  - [ ] Modal overlay
  - [ ] Search input (auto-focus)
  - [ ] Ctrl+K keyboard shortcut
  - [ ] Escape to close
  - [ ] Results grouped by type
  - [ ] Keyboard navigation (arrow keys)
  - [ ] Enter to navigate
  - [ ] Recent searches (when empty)
- [ ] Register global keyboard listener

---

### Feature 5.4: Notifications

#### Backend

- [ ] Create `notifications` migration
  - [ ] id, user_id, type, data (JSON), read_at (nullable), timestamps
- [ ] Create `app/Models/Notification.php`
- [ ] Create `app/Services/NotificationService.php`
  - [ ] `create($user, $type, $data)` method
  - [ ] Notification types: task_assigned, mentioned, deadline_approaching, project_update
- [ ] Create `app/Http/Controllers/NotificationController.php`
  - [ ] `index()`: List notifications
  - [ ] `markAsRead($id)`: Mark single as read
  - [ ] `markAllAsRead()`: Mark all as read
- [ ] Create `app/Events/NotificationCreated.php`
- [ ] Add notification routes

#### Frontend

- [ ] Create `resources/js/components/common/NotificationBell.vue`
  - [ ] Bell icon in navbar
  - [ ] Unread count badge
  - [ ] Dropdown on click
  - [ ] Notification list
  - [ ] Mark as read on click
  - [ ] "Mark all as read" button
  - [ ] Unread highlight styling
- [ ] Create `resources/js/stores/notifications.js`
  - [ ] Notifications state
  - [ ] Unread count getter
  - [ ] Fetch notifications action
  - [ ] Mark as read action
- [ ] Connect to WebSocket for real-time

---

### Feature 5.5: Toast Notifications

#### Frontend

- [ ] Create `resources/js/composables/useToast.js`
  - [ ] `toasts` reactive array
  - [ ] `add(message, type, duration)` method
  - [ ] `remove(id)` method
  - [ ] `success(message)` helper
  - [ ] `error(message)` helper
  - [ ] `warning(message)` helper
  - [ ] `info(message)` helper
  - [ ] Auto-dismiss after 5 seconds
- [ ] Create `resources/js/components/common/Toast.vue`
  - [ ] Success: green styling
  - [ ] Error: red styling
  - [ ] Warning: yellow styling
  - [ ] Info: blue styling
  - [ ] Close button
  - [ ] Slide-in animation
  - [ ] Slide-out animation
- [ ] Create `resources/js/components/common/ToastContainer.vue`
  - [ ] Position: bottom-right
  - [ ] Stack multiple toasts
  - [ ] Manage toast list
- [ ] Add ToastContainer to App.vue

---

## Testing Checklist

### Backend Tests (PHPUnit)

- [ ] Auth registration validation tests
- [ ] Auth login/logout tests
- [ ] Password reset flow tests
- [ ] Role middleware tests
- [ ] Project policy tests
- [ ] Task policy tests
- [ ] Board CRUD tests
- [ ] Task CRUD tests
- [ ] Task move with WIP limit tests
- [ ] Comment threading tests
- [ ] Activity logging tests
- [ ] Dashboard stats tests
- [ ] Search endpoint tests
- [ ] Notification tests

### Frontend Tests (Vitest)

- [ ] useAuth composable tests
- [ ] useTheme composable tests
- [ ] useToast composable tests
- [ ] TaskCard component tests
- [ ] KanbanBoard component tests
- [ ] Login page tests
- [ ] Register page tests

### E2E Tests (Playwright/Cypress)

- [ ] Complete registration flow
- [ ] Complete login flow
- [ ] Create and manage project
- [ ] Kanban board drag-and-drop
- [ ] Task detail panel interactions
- [ ] Dashboard data loading
- [ ] Theme toggle persistence
- [ ] Search functionality

---

## Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Default roles seeded
- [ ] Sanctum configured
- [ ] Pusher/broadcasting configured
- [ ] Assets built (`npm run build`)
- [ ] Cache cleared
- [ ] Queue worker running
- [ ] Scheduler configured
- [ ] SSL certificate installed
- [ ] CORS configured
- [ ] Rate limiting configured

---

## Documentation

- [ ] API documentation (endpoints, request/response)
- [ ] Component documentation (props, events)
- [ ] Setup guide for developers
- [ ] User guide for end users
- [ ] Deployment guide

---

*Last updated: 2026-01-19*
