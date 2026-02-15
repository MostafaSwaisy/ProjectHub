# ProjectHub Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-02-01

## Active Technologies
- PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel Framework 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0 (002-dashboard-navigation)
- SQLite (existing schema, NO migrations needed) (002-dashboard-navigation)
- SQLite (existing schema with projects, project_members, boards, columns, tasks tables) (003-projects-management)
- PHP 8.2+ (Laravel 11), JavaScript ES2022 (Vue 3.4.0) + Laravel 11, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Tailwind CSS 4.0.0, Vite 7.0.7 (003-projects-management)
- MySQL/SQLite via Laravel Eloquent ORM (003-projects-management)
- PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7 (004-kanban-task-management)
- SQLite via Laravel Eloquent ORM (existing schema with tasks, subtasks, comments, labels, activities tables) (004-kanban-task-management)

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

## Recent Changes
- 004-kanban-task-management: Added PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0, Vite 7.0.7
- 003-projects-management: Added PHP 8.2+ (Laravel 11), JavaScript ES2022 (Vue 3.4.0) + Laravel 11, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Tailwind CSS 4.0.0, Vite 7.0.7
- 003-projects-management: Added PHP 8.2+ (Laravel 12.x), JavaScript ES2022 (Vue 3.4.0) + Laravel Framework 12, Vue 3.4.0, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2, Tailwind CSS 4.0.0


<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
