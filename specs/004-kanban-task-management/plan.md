# Implementation Plan: Kanban Board & Task Management

**Branch**: `004-kanban-task-management` | **Date**: 2026-02-05 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/004-kanban-task-management/spec.md`

## Summary

Full Kanban board implementation with task CRUD, drag-and-drop movement between columns, subtasks with progress tracking, comments with edit window, labels for categorization, filtering/search, task assignment, due date management, and activity logging. The codebase already has substantial infrastructure (models, controllers, basic components) - this feature completes the remaining UI components and advanced features.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0)
**Primary Dependencies**: Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7
**Storage**: SQLite via Laravel Eloquent ORM (existing schema with tasks, subtasks, comments, labels, activities tables)
**Testing**: PHPUnit (backend), Vitest (frontend)
**Target Platform**: Web (responsive - desktop and mobile 768px+)
**Project Type**: Web application (Laravel backend + Vue frontend)
**Performance Goals**: Task board loads 100 tasks within 3 seconds, drag-drop completes within 1 second, search results within 500ms
**Constraints**: Optimistic UI updates with rollback on error, immediate save (no draft workflow), 15-minute comment edit window
**Scale/Scope**: Multi-project task management, boards with ~100 tasks, teams of ~10-20 members

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Note**: The constitution file is currently a template placeholder. The following principles are applied based on project conventions:

| Principle | Status | Notes |
|-----------|--------|-------|
| Existing Patterns | PASS | Follows established Laravel/Vue patterns in codebase |
| Immediate Save | PASS | No draft workflow - all changes persist immediately |
| Test Coverage | PASS | PHPUnit for backend, Vitest for frontend available |
| Authorization | PASS | Uses existing TaskPolicy, project-level permissions |
| API Standards | PASS | RESTful endpoints with Laravel Resources |

**Gate Status**: PASSED - No constitution violations detected.

## Project Structure

### Documentation (this feature)

```text
specs/004-kanban-task-management/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command)
```

### Source Code (repository root)

```text
# Laravel + Vue Web Application Structure

# Backend (Laravel)
app/
├── Http/
│   ├── Controllers/
│   │   ├── TaskController.php         # Task CRUD + move
│   │   ├── BoardController.php        # Board management
│   │   ├── SubtaskController.php      # NEW: Subtask endpoints
│   │   ├── CommentController.php      # NEW: Comment endpoints
│   │   └── LabelController.php        # NEW: Label management
│   ├── Requests/
│   │   ├── StoreTaskRequest.php
│   │   ├── UpdateTaskRequest.php
│   │   ├── MoveTaskRequest.php
│   │   ├── StoreSubtaskRequest.php    # NEW
│   │   ├── StoreCommentRequest.php    # NEW
│   │   └── StoreLabelRequest.php      # NEW
│   └── Resources/
│       ├── TaskResource.php
│       ├── BoardResource.php
│       ├── ColumnResource.php
│       ├── SubtaskResource.php
│       ├── CommentResource.php
│       ├── LabelResource.php
│       └── ActivityResource.php
├── Models/
│   ├── Task.php                       # EXISTS
│   ├── Board.php                      # EXISTS
│   ├── Column.php                     # EXISTS
│   ├── Subtask.php                    # EXISTS
│   ├── Comment.php                    # EXISTS
│   ├── Label.php                      # EXISTS
│   └── Activity.php                   # EXISTS
└── Policies/
    ├── TaskPolicy.php                 # EXISTS
    ├── CommentPolicy.php              # NEW: 15-min edit window
    └── LabelPolicy.php                # NEW: project owner only

# Frontend (Vue 3)
resources/js/
├── components/
│   └── kanban/
│       ├── KanbanBoard.vue            # EXISTS
│       ├── KanbanColumn.vue           # EXISTS
│       ├── TaskCard.vue               # EXISTS
│       ├── TaskModal.vue              # EXISTS - enhance
│       ├── TaskDetailModal.vue        # EXISTS - enhance with comments/activity
│       ├── SubtaskList.vue            # NEW: Subtask management
│       ├── CommentSection.vue         # NEW: Comments with threading
│       ├── ActivityFeed.vue           # NEW: Activity log display
│       ├── LabelManager.vue           # NEW: Create/edit project labels
│       ├── FilterBar.vue              # NEW: Advanced filtering UI
│       └── BoardHeader.vue            # EXISTS - enhance
├── stores/
│   ├── kanban.js                      # EXISTS - UI state
│   ├── tasks.js                       # EXISTS - task data
│   ├── comments.js                    # NEW: comment management
│   └── labels.js                      # NEW: label management
├── composables/
│   ├── useTaskFiltering.js            # EXISTS
│   ├── useDragDrop.js                 # EXISTS
│   └── useCommentEditing.js           # NEW: 15-min edit window logic
└── pages/
    └── projects/
        └── KanbanView.vue             # Main board page

# Database
database/migrations/
├── 2026_01_*_create_tasks_table.php           # EXISTS
├── 2026_01_*_create_subtasks_table.php        # EXISTS
├── 2026_01_*_create_comments_table.php        # EXISTS
├── 2026_01_*_create_labels_table.php          # EXISTS
├── 2026_01_*_create_task_labels_table.php     # EXISTS
├── 2026_01_*_create_boards_table.php          # EXISTS
├── 2026_01_*_create_columns_table.php         # EXISTS
└── 2026_01_*_create_activities_table.php      # EXISTS

# Tests
tests/
├── Feature/
│   ├── TaskControllerTest.php         # EXISTS/Enhance
│   ├── SubtaskControllerTest.php      # NEW
│   ├── CommentControllerTest.php      # NEW
│   └── LabelControllerTest.php        # NEW
└── Unit/
    ├── TaskTest.php
    └── CommentPolicyTest.php          # NEW: edit window tests
```

**Structure Decision**: Using existing Laravel + Vue web application structure. Most backend models and basic frontend components already exist. This feature adds new controllers for subtasks/comments/labels, enhances existing components, and adds missing UI pieces.

## Complexity Tracking

> No complexity violations - feature follows existing patterns and doesn't introduce new architectural concepts.
