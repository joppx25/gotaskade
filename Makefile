.PHONY: up down build restart logs backend-logs frontend-logs db-logs \
       shell artisan migrate seed test fresh

# Start all services
up:
	docker compose up -d

# Start with build (first-time or after Dockerfile changes)
build:
	docker compose up -d --build

# Stop all services
down:
	docker compose down

# Stop and remove volumes (full reset)
reset:
	docker compose down -v

# Restart all services
restart:
	docker compose restart

# View logs for all services
logs:
	docker compose logs -f

# View individual service logs
backend-logs:
	docker compose logs -f backend

frontend-logs:
	docker compose logs -f frontend

db-logs:
	docker compose logs -f pgsql

# Open a shell in the backend container
shell:
	docker compose exec backend bash

# Run artisan commands (usage: make artisan cmd="migrate:status")
artisan:
	docker compose exec backend php artisan $(cmd)

# Run migrations
migrate:
	docker compose exec backend php artisan migrate

# Run seeders
seed:
	docker compose exec backend php artisan db:seed

# Fresh migration + seed
fresh:
	docker compose exec backend php artisan migrate:fresh --seed

# Run Pest tests
test:
	docker compose exec backend php artisan test
