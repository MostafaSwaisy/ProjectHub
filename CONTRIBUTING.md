# Contributing to ProjectHub

Thank you for your interest in contributing! This document covers everything you need to know to get started.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Branching Strategy](#branching-strategy)
- [Commit Messages](#commit-messages)
- [Pull Request Process](#pull-request-process)
- [Coding Standards](#coding-standards)
- [Testing](#testing)

---

## Code of Conduct

This project follows our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you agree to uphold these standards. Please report unacceptable behavior to the project maintainers.

---

## How Can I Contribute?

### Reporting Bugs

Before creating a bug report, please check the [existing issues](../../issues) to avoid duplicates.

When filing a bug report, include:

- A clear, descriptive title
- Steps to reproduce the problem
- Expected vs. actual behavior
- Laravel version, PHP version, Node version
- Any relevant error messages or logs

Use the **Bug Report** issue template when creating a new issue.

### Suggesting Features

Feature requests are welcome. Please:

- Check the [roadmap](README.md#roadmap) and existing issues first
- Open an issue with the **Feature Request** template
- Describe the problem your feature solves, not just the solution
- Be open to discussion — we may suggest alternatives

### Submitting Code

- Bug fixes, tests, and documentation improvements are always welcome
- For larger features, open an issue to discuss before writing code
- All contributions must pass the test suite and linting checks

---

## Development Setup

### Prerequisites

- PHP 8.2+, Composer 2.x
- Node.js 20+, npm 10+
- Git

### Steps

```bash
# 1. Fork and clone
git clone https://github.com/your-username/projecthub.git
cd projecthub

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Set up the database
php artisan migrate --seed

# 5. Start development servers
composer dev
```

The app will be available at <http://localhost:8000>.

---

## Branching Strategy

| Branch type     | Naming pattern              | Example                        |
| --------------- | --------------------------- | ------------------------------ |
| Feature         | `feat/<short-description>`  | `feat/task-time-tracking`      |
| Bug fix         | `fix/<short-description>`   | `fix/avatar-upload-validation` |
| Documentation   | `docs/<short-description>`  | `docs/api-reference-update`    |
| Refactor        | `refactor/<description>`    | `refactor/kanban-store`        |
| Tests           | `test/<description>`        | `test/project-policy-coverage` |

- Always branch off `main`
- Keep branches focused on a single concern
- Delete your branch after it is merged

---

## Commit Messages

We follow the [Conventional Commits](https://www.conventionalcommits.org/) specification.

### Format

```text
<type>(<scope>): <short summary>

[optional body]

[optional footer]
```

### Types

| Type       | When to use                                    |
| ---------- | ---------------------------------------------- |
| `feat`     | A new feature                                  |
| `fix`      | A bug fix                                      |
| `docs`     | Documentation only                             |
| `style`    | Formatting, missing semicolons — no logic      |
| `refactor` | Code change that is not a fix or feature       |
| `test`     | Adding or correcting tests                     |
| `chore`    | Build process, dependency updates, tooling     |

### Examples

```text
feat(kanban): add drag-and-drop between boards
fix(auth): prevent login with unverified email
docs(api): add invitation endpoint examples
test(projects): add policy coverage for lead role
```

---

## Pull Request Process

1. **Sync** your fork with `main` before opening a PR
2. **Fill in** the PR template completely
3. **Link** the related issue (e.g., `Closes #42`)
4. **Ensure** all checks pass (tests, linting)
5. **Request** a review from a maintainer
6. **Address** all review comments before merging

PRs are merged using **squash and merge** to keep the commit history clean.

### PR Checklist

- [ ] Tests pass locally (`composer test`)
- [ ] PHP linting passes (`vendor/bin/pint --test`)
- [ ] No unrelated changes included
- [ ] Documentation updated if needed
- [ ] `.env.example` updated if new env vars were added

---

## Coding Standards

### PHP (Backend)

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding style
- Run [Laravel Pint](https://laravel.com/docs/pint) to auto-format: `vendor/bin/pint`
- Use type declarations on all method parameters and return types
- Authorize via Policies — never inline `if ($user->id === $owner->id)` checks

### JavaScript / Vue (Frontend)

- Follow the [Vue 3 Style Guide](https://vuejs.org/style-guide/) (Priority A & B rules)
- Use the Composition API with `<script setup>` syntax
- Keep components small and single-purpose
- Extract reusable logic into composables under `resources/js/composables/`
- Use Pinia stores for cross-component state; avoid prop drilling

### General

- Write self-documenting code; add comments only where the logic is non-obvious
- Do not commit `.env`, secrets, or generated files
- Keep pull requests focused — one feature or fix per PR

---

## Testing

### Running Tests

```bash
# Full test suite
composer test

# Single test file
php artisan test tests/Feature/ProjectTest.php

# With coverage
php artisan test --coverage
```

### Writing Tests

- Place feature tests in `tests/Feature/`, unit tests in `tests/Unit/`
- Use model factories for test data — never hardcode IDs
- Test the behavior (HTTP responses, model state), not implementation details
- Every new feature should include at least one feature test
- Every bug fix should include a regression test

---

## Questions?

If you are unsure about anything, open a [Discussion](../../discussions) or ask in the issue you are working on. We are happy to help.

Thank you for contributing to ProjectHub!
