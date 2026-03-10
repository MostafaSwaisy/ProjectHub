# ProjectHub Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-03-10

## Active Technologies
- PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel Framework 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0 (002-dashboard-navigation)
- SQLite (existing schema, NO migrations needed) (002-dashboard-navigation)
- SQLite (existing schema with projects, project_members, boards, columns, tasks tables) (003-projects-management)
- PHP 8.2+ (Laravel 11), JavaScript ES2022 (Vue 3.4.0) + Laravel 11, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Tailwind CSS 4.0.0, Vite 7.0.7 (003-projects-management)
- MySQL/SQLite via Laravel Eloquent ORM (003-projects-management)
- PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7 (004-kanban-task-management)
- SQLite via Laravel Eloquent ORM (existing schema with tasks, subtasks, comments, labels, activities tables) (004-kanban-task-management)
- PHP 8.2+ (Laravel 12.47.0), JavaScript ES2022 (Vue 3.5.27) + Laravel 12, Vue 3.5.27, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2.4, Tailwind CSS 4.1.18, Vite 7.0.7 (006-user-management)
- SQLite via Laravel Eloquent ORM (extended schema with users avatar_url/bio, invitations table, updated project_members roles) (006-user-management)

- JavaScript ES2022, Vue.js 3.4.0 + Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Tailwind CSS 4.0.0, Vite 7.0.7 (001-modern-ui)

## Project Structure

```text
backend/
frontend/
tests/
```

## Commands

npm test; npm run lint

## Code Style

JavaScript ES2022, Vue.js 3.4.0: Follow standard conventions

## 006-User-Management Feature (Phase 1-2 Complete)

- **Models**: User (avatar_url, bio), Invitation (token-based), ProjectMember (new roles: lead), existing extended
- **Policies**: ProjectPolicy, TaskPolicy, InvitationPolicy with permission matrix support
- **Config**: permissions.php (owner/lead/member/viewer RBAC matrix)
- **Database**: Extended users table, new invitations table, migrated project_members roles
- **Frontend**: useProjectPermissions composable with permission checks
- **Routes**: Profile, Users, Invitations, Notifications API endpoints registered

## Recent Changes
- 006-user-management: Phase 1-2 foundational infrastructure complete (T001-T015)
- 005-soft-delete: Added PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7
- 004-kanban-task-management: Added PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7


<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
