# GoTaskade

A task management application where users can create, organize, and track daily tasks. Built as a decoupled SPA (frontend) + API (backend) architecture, containerized with Docker for a single-command setup.

## Tech Stack

| Layer    | Technology                                                    |
| -------- | ------------------------------------------------------------- |
| Frontend | Nuxt 4, Vue 3, TypeScript, Tailwind CSS v4, Pinia, shadcn-vue |
| Backend  | Laravel 12, PHP 8.4, Laravel Sanctum (SPA auth)              |
| Database | PostgreSQL 17                                                 |
| Testing  | Pest PHP                                                      |
| DevOps   | Docker Compose, pnpm                                          |

## Architectural Decisions

**Why PostgreSQL?** The app organizes tasks by date and supports search. PostgreSQL handles date operations, indexing, and full-text search more robustly than other, and scales without a migration if the app grows.

**Why Sanctum SPA authentication?** Since the frontend and API live on the same top-level domain (`localhost`), Sanctum's cookie-based session auth avoids the complexity of token management on the client while providing CSRF protection out of the box. The frontend is rendered entirely client-side (`ssr: false`) because authenticated state lives in browser cookies, which are not available during server-side rendering.

**Why the Repository pattern?** Controllers delegate all database logic to repository classes behind an interface (`TaskRepositoryInterface`). This keeps controllers thin, makes the data layer swappable, and allows unit-testing business logic without hitting the database.

**Why Policies for authorization?** Laravel Policies decouple access rules from controller logic. Each policy method (`view`, `update`, `delete`) checks ownership (`$user->id === $task->user_id`), so authorization is centralized and testable independently.

**Why JsonResource classes?** All API responses go through `TaskResource` / `UserResource` / `TaskCollection`. This centralizes data transformations, ensures a uniform `{ data: ... }` envelope, and prevents accidental exposure of internal fields like `password`.

**Why Pinia for state?** Pinia is the official Vue 3 state manager. The task store handles API calls, caching, and optimistic updates in one place, keeping components focused on presentation. A `useApi` composable wraps `$fetch` with automatic CSRF token injection and error normalization.

## Quick Start (Docker)

> **Prerequisites:** Docker and Docker Compose installed.

```bash
git clone <repository-url> goteam && cd goteam
cp .env.example .env
make build
make shell
composer install
php artisan db:seed
```

That's it. The backend entrypoint handles dependency installation, key generation, migrations, and seeding automatically.

| Service  | URL                    |
| -------- | ---------------------- |
| Frontend | http://localhost:3000  |
| Backend  | http://localhost:80    |
| Database | localhost:5432         |

### Default Users

| Email                  | Password   |
| ---------------------- | ---------- |
| john.doe@example.com   | password   |
| jane.doe@example.com   | password   |

## Common Commands

All commands run inside the containers via Docker -- no local PHP or Node required.

```bash
# Backend shell
make shell

# Run any artisan command
make artisan cmd="migrate:status"
make artisan cmd="route:list"

# Fresh migration + seed
make fresh

# Run tests
make test
```

## Running Tests

```bash
make test
```

## Makefile Commands

| Command             | Description                              |
| ------------------- | ---------------------------------------- |
| `make build`        | Build images and start all services      |
| `make up`           | Start services                           |
| `make down`         | Stop services                            |
| `make reset`        | Stop services and wipe all volumes       |
| `make logs`         | Tail logs for all services               |
| `make shell`        | Open a bash shell in the backend         |
| `make test`         | Run Pest test suite                      |
| `make fresh`        | Fresh migration + seed                   |
| `make artisan cmd=` | Run any artisan command                  |

## Project Structure

```
goteam/
├── .docker/
│   ├── backend/        # PHP Dockerfile + entrypoint
│   └── frontend/       # Node Dockerfile
├── backend/            # Laravel 12 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Resources/   # JsonResource classes
│   │   ├── Models/
│   │   ├── Policies/         # Authorization policies
│   │   └── Repositories/     # Repository pattern
│   └── tests/
│       ├── Feature/          # Auth & Task endpoint tests
│       └── Unit/             # Policy unit tests
├── frontend/           # Nuxt 4 SPA
│   └── app/
│       ├── components/
│       ├── composables/
│       ├── layouts/
│       ├── pages/
│       └── stores/           # Pinia stores
├── docker-compose.yml
└── Makefile
```
