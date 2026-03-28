# ProjectHub

**A modern, full-featured project management platform built with Laravel & Vue 3**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?style=flat-square&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg?style=flat-square)](CONTRIBUTING.md)

[Features](#features) · [Tech Stack](#tech-stack) · [Getting Started](#getting-started) · [API Reference](#api-reference) · [Contributing](#contributing)

---

## Overview

ProjectHub is an open-source project management tool that combines Kanban boards, team collaboration, and role-based access control in a clean, fast single-page application. It is designed to be self-hosted and easy to extend.

- Kanban boards with drag-and-drop task management
- Team collaboration with invitations, roles, and permissions
- Activity tracking and audit logs across all actions
- Soft delete / trash system so nothing is lost permanently
- Admin dashboard for user and system management
- RESTful JSON API — build your own integrations or mobile client

---

## Features

### Project Management

- Create, edit, archive, and duplicate projects
- Invite team members via email with token-based invitations
- Role-based permissions per project: **Owner**, **Lead**, **Member**, **Viewer**
- Project member management with configurable access levels

### Kanban Boards

- Multiple boards per project with custom columns
- Drag-and-drop task reordering across columns
- Task detail panel with full editing capabilities
- Color-coded labels for task categorization
- Task assignment to project members

### Task Management

- Nested subtasks within tasks
- In-line comments and discussion threads
- Due dates with deadline tracking
- Task activity log showing every change
- Bulk operations and advanced filtering

### User & Profile Management

- Token-based authentication via Laravel Sanctum
- User profiles with avatar upload and bio
- Notification system with unread count badge
- User preferences and settings
- Admin panel for managing all users

### Developer Experience

- Fully documented RESTful API
- Comprehensive RBAC permission matrix
- Soft deletes with trash/restore on all entities
- Activity audit log for every model change
- PHPUnit test suite with factories and seeders

---

## Tech Stack

| Layer             | Technology                                             | Version  |
| ----------------- | ------------------------------------------------------ | -------- |
| Backend           | [Laravel](https://laravel.com)                         | 12.47.0  |
| Runtime           | PHP                                                    | 8.2+     |
| Authentication    | [Laravel Sanctum](https://laravel.com/docs/sanctum)    | 4.2.4    |
| Frontend          | [Vue.js](https://vuejs.org/)                           | 3.5.27   |
| Routing           | [Vue Router](https://router.vuejs.org/)                | 4.3.0    |
| State Management  | [Pinia](https://pinia.vuejs.org/)                      | 2.2.0    |
| HTTP Client       | [Axios](https://axios-http.com/)                       | 1.11.0   |
| Styling           | [Tailwind CSS](https://tailwindcss.com/)               | 4.1.18   |
| Build Tool        | [Vite](https://vitejs.dev/)                            | 7.0.7    |
| Database          | SQLite / MySQL / PostgreSQL                            | —        |
| Testing           | [PHPUnit](https://phpunit.de/)                         | 11.x     |

---

## Getting Started

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 20+ and npm 10+
- SQLite (default) or a MySQL / PostgreSQL database

### Installation

#### 1. Clone the repository

```bash
git clone https://github.com/your-username/projecthub.git
cd projecthub
```

#### 2. Install backend dependencies

```bash
composer install
```

#### 3. Install frontend dependencies

```bash
npm install
```

#### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database and mail settings. SQLite is pre-configured and works out of the box.

#### 5. Set up the database

```bash
php artisan migrate --seed
```

This runs all migrations and seeds default roles (`admin`, `instructor`, `student`) plus a test admin user.

#### 6. Start the development server

```bash
composer dev
```

This concurrently starts the Laravel server, queue worker, log watcher, and Vite dev server.

Open <http://localhost:8000> in your browser.

---

### Default Credentials

After seeding, you can log in with:

| Field    | Value                |
| -------- | -------------------- |
| Email    | `admin@example.com`  |
| Password | `password`           |

> Change these immediately in any non-local environment.

---

## Project Structure

```text
projecthub/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # API controllers (Auth, Project, Task, Board, User…)
│   │   ├── Middleware/         # RoleMiddleware, auth guards
│   │   ├── Requests/           # Form request validation
│   │   └── Resources/          # API response transformers
│   ├── Models/                 # 14 Eloquent models
│   ├── Policies/               # 7 authorization policies
│   └── Traits/                 # Shared model traits
├── config/
│   └── permissions.php         # RBAC permission matrix
├── database/
│   ├── migrations/             # 10+ ordered migrations
│   ├── factories/              # Model factories for tests
│   └── seeders/                # Database seeders
├── resources/js/
│   ├── components/             # 40+ Vue components
│   │   ├── auth/               # Login, register, password forms
│   │   ├── kanban/             # Board, columns, cards, task detail
│   │   ├── projects/           # Project cards, modals
│   │   ├── users/              # Admin user management
│   │   ├── profile/            # Profile form, avatar uploader
│   │   └── shared/             # Button, Modal, Input, Toast…
│   ├── composables/            # 11 reusable Vue composables
│   ├── pages/                  # 9 top-level page components
│   ├── stores/                 # 11 Pinia stores
│   ├── router/                 # Vue Router configuration
│   └── api/                    # Axios API client modules
├── routes/
│   ├── api.php                 # All REST API routes
│   └── web.php                 # SPA catch-all route
├── docs/                       # Architecture and API documentation
└── tests/                      # PHPUnit feature & unit tests
```

---

## API Reference

The full API documentation lives in [`docs/`](docs/). Below is a quick overview of the main endpoint groups.

All endpoints (except auth) require a `Bearer` token in the `Authorization` header, obtained at login.

### Authentication

```text
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/password/email
POST   /api/auth/password/reset
```

### Projects

```text
GET    /api/projects
POST   /api/projects
GET    /api/projects/{id}
PUT    /api/projects/{id}
DELETE /api/projects/{id}
POST   /api/projects/{id}/archive
POST   /api/projects/{id}/duplicate

GET    /api/projects/{id}/members
POST   /api/projects/{id}/members
PUT    /api/projects/{id}/members/{user}
DELETE /api/projects/{id}/members/{user}
```

### Boards & Tasks

```text
GET    /api/projects/{id}/boards
POST   /api/projects/{id}/boards
GET    /api/tasks
POST   /api/tasks
PUT    /api/tasks/{id}
DELETE /api/tasks/{id}
POST   /api/tasks/{id}/move

GET    /api/tasks/{id}/subtasks
POST   /api/tasks/{id}/subtasks
GET    /api/tasks/{id}/comments
POST   /api/tasks/{id}/comments
```

### User & Profile

```text
GET    /api/profile
PUT    /api/profile
POST   /api/profile/avatar
DELETE /api/profile/avatar
PUT    /api/profile/password

GET    /api/users              (admin only)
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}
```

### Invitations & Notifications

```text
GET    /api/projects/{id}/invitations
POST   /api/projects/{id}/invitations
POST   /api/invitations/{token}/accept
POST   /api/invitations/{token}/decline

GET    /api/notifications
GET    /api/notifications/unread-count
POST   /api/notifications/{id}/read
POST   /api/notifications/read-all
```

---

## Role-Based Access Control

ProjectHub uses a four-tier permission system per project:

| Permission              | Owner | Lead | Member | Viewer |
| ----------------------- | :---: | :--: | :----: | :----: |
| Manage project settings | ✅    | ❌   | ❌     | ❌     |
| Invite / remove members | ✅    | ✅   | ❌     | ❌     |
| Create / delete tasks   | ✅    | ✅   | ✅     | ❌     |
| Edit tasks              | ✅    | ✅   | ✅     | ❌     |
| Add comments            | ✅    | ✅   | ✅     | ❌     |
| View project            | ✅    | ✅   | ✅     | ✅     |

The full permission matrix is defined in [`config/permissions.php`](config/permissions.php).

---

## Running Tests

```bash
# Run the full PHPUnit test suite
composer test

# Run with coverage report
php artisan test --coverage
```

---

## Production Deployment

```bash
# Build frontend assets
npm run build

# Optimize Laravel for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

For production, switch `DB_CONNECTION` to `mysql` or `pgsql` in `.env` and configure a queue driver (`redis` recommended) for background jobs.

---

## Contributing

Contributions are welcome and greatly appreciated! Please read [CONTRIBUTING.md](CONTRIBUTING.md) before submitting a pull request.

Quick steps:

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/your-feature`
3. Commit your changes following [Conventional Commits](https://www.conventionalcommits.org/)
4. Push and open a pull request against `main`

Please follow our [Code of Conduct](CODE_OF_CONDUCT.md) in all project spaces.

---

## Roadmap

- [ ] Real-time updates via Laravel Echo / WebSockets
- [ ] File attachments on tasks
- [ ] Time tracking per task
- [ ] Public project sharing links
- [ ] Docker Compose setup for one-command deployment
- [ ] Dark mode
- [ ] Mobile-responsive improvements
- [ ] REST API rate limiting dashboard

---

## License

ProjectHub is open-source software released under the [MIT License](LICENSE).
