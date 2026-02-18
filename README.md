# GoTaskade

A task management application where users can create, organize, and track daily tasks.

## Tech Stack

| Layer    | Technology                                                    |
| -------- | ------------------------------------------------------------- |
| Frontend | Nuxt 4, Vue 3, TypeScript, Tailwind CSS v4, Pinia, shadcn-vue |
| Backend  | Laravel 12, PHP 8.4, Laravel Sanctum (SPA auth)              |
| Database | PostgreSQL 17                                                 |
| Testing  | Pest PHP                                                      |
| DevOps   | Docker Compose, pnpm                                          |

## Quick Start (Docker)

> **Prerequisites:** Docker and Docker Compose installed.

```bash
git clone <repository-url> goteam && cd goteam
cp .env.example .env
make build
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
