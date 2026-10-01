# Amar Task

A simple to-do list manager built as a technical assessment for the **Junior PHP Developer** position at Amar Assist.

Each user manages their own tasks, and there is a full users CRUD. Every listing has search and pagination (20 items per page).

## Features

- **Authentication**: login, logout and registration (Laravel Breeze)
- **Users CRUD**: create, list, show details, update and delete users
- **Tasks management**: create, edit, delete and mark as completed
- **Per-user isolation**: each user can only see and change their own tasks
- **Search and pagination**: on both listings, 20 items per page
- **Vue listings**: tasks and users lists are Vue components with debounced search, pagination and async actions (complete, reopen, delete) without page reloads
- **Automated tests**: 43 feature tests, including task isolation

## Tech stack

| Layer | Technology |
|---|---|
| Backend | Laravel 9 on PHP 8.2 |
| Frontend | Vue 3 mounted inside Blade views (no SPA) |
| Styling | Tailwind CSS |
| Build tool | Vite |
| Database | MySQL |
| Environment | Docker (Laravel Sail) |
| Tests | PHPUnit with in-memory SQLite |

## Getting started

### Requirements

- Docker (Docker Desktop on Windows or macOS)
- Git
- On Windows, run the commands inside WSL 2 (Ubuntu)

PHP 8.2 runs inside the Sail container, so you do not need PHP installed locally.

### Installation

```bash
git clone https://github.com/devpedro404/Amar-Task.git
cd Amar-Task
cp .env.example .env
```

Install the PHP dependencies without needing PHP on your machine:

```bash
docker run --rm -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" -w /var/www/html \
  laravelsail/php82-composer:latest \
  composer install --ignore-platform-reqs
```

Start the containers, then prepare the application:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Keep `npm run dev` running in its own terminal, then open **http://localhost**.

Optional shortcut:

```bash
alias sail='./vendor/bin/sail'
```

### Demo credentials

The seeder creates a demo user with 50 tasks, plus 25 random users (to exercise pagination):

| Field | Value |
|---|---|
| Email | `demo@doable.test` |
| Password | `password` |

All seeded users use the password `password`.

### Running the tests

```bash
./vendor/bin/sail artisan test
```

Tests run against an **in-memory SQLite** database (configured in `phpunit.xml`), so they never touch your development data.

### Stopping the environment

```bash
./vendor/bin/sail down
```

## Main routes

| Method | URL | Description |
|---|---|---|
| GET | `/tasks` | Tasks page (Vue list). Returns JSON when the request asks for it |
| GET | `/tasks/create` | New task form |
| POST | `/tasks` | Store a task |
| GET | `/tasks/{task}/edit` | Edit form |
| PUT | `/tasks/{task}` | Update a task |
| PATCH | `/tasks/{task}/toggle` | Complete or reopen a task |
| DELETE | `/tasks/{task}` | Delete a task |
| GET | `/users` | Users page (Vue list). Returns JSON when the request asks for it |
| GET | `/users/create` | New user form |
| POST | `/users` | Store a user |
| GET | `/users/{user}` | User details |
| GET | `/users/{user}/edit` | Edit form |
| PUT | `/users/{user}` | Update a user |
| DELETE | `/users/{user}` | Delete a user |

All routes except the authentication ones require a logged-in user.

## Project structure

```
app/
  Actions/
    Tasks/          CreateTask, ToggleTaskCompletion
    Users/          CreateUser, UpdateUser
  Http/
    Controllers/    TaskController, UserController
    Requests/       StoreTaskRequest, UpdateTaskRequest, StoreUserRequest, UpdateUserRequest
  Models/           Task, User
  Policies/         TaskPolicy, UserPolicy
database/
  factories/        TaskFactory, UserFactory
  migrations/       users, tasks and auth tables
  seeders/          DatabaseSeeder
resources/
  js/components/    TaskList.vue, UserList.vue
  views/tasks/      Blade views for tasks
  views/users/      Blade views for users
tests/Feature/      TaskTest, UserTest (plus the Breeze tests)
```

## Technical decisions

- **Task isolation**: the tasks listing is always built from `$request->user()->tasks()`, so another user's tasks are never queried. Every write action (edit, update, toggle, delete) is also guarded by `TaskPolicy`, which answers **403** when the task belongs to someone else.
- **Action classes**: business logic (hashing passwords, toggling completion, creating tasks) lives in small single-purpose action classes, keeping controllers focused on HTTP concerns (Single Responsibility).
- **Form Requests**: validation lives in dedicated request classes, which keeps the controllers short.
- **`completed_at` instead of a boolean**: a nullable timestamp tells whether a task is done and also when it was completed.
- **Search scopes**: `Task` and `User` expose a reusable `search()` scope. Tasks are searched by title and description, users by name and email.
- **Vue only where it pays off**: both listings are Vue components (debounced search, pagination and async actions). Creating and editing use plain Blade forms, which keeps the project simple and avoids a full SPA.
- **One endpoint, two formats**: the controllers return the Blade page for normal requests and JSON when the client asks for JSON (`wantsJson()`), so the Vue components and the tests use the same routes.
- **Users CRUD access**: the assessment does not define roles, so any authenticated user can manage users. A user **cannot delete their own account** (`UserPolicy`).
- **Cascade delete**: deleting a user also deletes their tasks (`cascadeOnDelete` in the migration).
- **Passwords**: hashed on creation, and on update the password only changes when a new one is provided.
- **Safe tests**: `RefreshDatabase` runs on in-memory SQLite, so seeded data in MySQL is never wiped.

## Development workflow

- Feature branches (`feature/...`, `refactor/...`, `chore/...`, `docs/...`) merged into `main` through pull requests
- Small commits in English following Conventional Commits (`feat:`, `fix:`, `test:`, `docs:`, `chore:`, `refactor:`)

## Troubleshooting

- **Port already in use**: set `APP_PORT=8080` (and `FORWARD_DB_PORT=3307` for MySQL) in `.env`, then run `sail down` and `sail up -d`.
- **Page without styles**: make sure `sail npm run dev` is running. `vite.config.js` is already set up so Vite's hot reload works from inside Docker.
- **Docker is not running**: open Docker Desktop and wait until it finishes starting.

## Author

Pedro Enrique, [@devpedro404](https://github.com/devpedro404)

> All code in this repository was written solely to evaluate programming skills for the assessment.