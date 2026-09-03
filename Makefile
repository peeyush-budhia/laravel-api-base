SHELL := /bin/sh

APP_SERVICE ?= app

.PHONY: help up down restart build logs ps bash shell artisan composer key migrate seed test

help:
	@printf '%s\n' "Available targets:"
	@printf '%s\n' "  make up         Start the Docker stack"
	@printf '%s\n' "  make down       Stop and remove the Docker stack"
	@printf '%s\n' "  make build      Build the Docker images"
	@printf '%s\n' "  make logs       Follow container logs"
	@printf '%s\n' "  make bash       Open a shell in the app container"
	@printf '%s\n' "  make artisan    Run an Artisan command, e.g. make artisan ARGS='migrate --force'"
	@printf '%s\n' "  make composer   Run a Composer command, e.g. make composer ARGS='install'"
	@printf '%s\n' "  make key        Generate the application key"
	@printf '%s\n' "  make migrate    Run database migrations"
	@printf '%s\n' "  make seed       Run database seeders"
	@printf '%s\n' "  make test       Run the test suite"

up:
	docker compose up --build

down:
	docker compose down

restart:
	docker compose down
	docker compose up --build

build:
	docker compose build

logs:
	docker compose logs -f

ps:
	docker compose ps

bash shell:
	docker compose exec -u root $(APP_SERVICE) sh

artisan:
	docker compose exec -u root $(APP_SERVICE) php artisan $(ARGS)

composer:
	docker compose exec -u root $(APP_SERVICE) composer $(ARGS)

key:
	docker compose exec -u root $(APP_SERVICE) php artisan key:generate

migrate:
	docker compose exec -u root $(APP_SERVICE) php artisan migrate

seed:
	docker compose exec -u root $(APP_SERVICE) php artisan db:seed

test:
	docker compose exec -u root $(APP_SERVICE) php artisan test
