# Laravel API Base

> A production-ready Laravel 13 REST API Starter Kit with Authentication, API Versioning, Service Layer Architecture, UUID Support, Standardized API Responses, API Documentation, and Comprehensive Testing.

![Laravel](https://img.shields.io/badge/Laravel-13.x-red)
![PHP](https://img.shields.io/badge/PHP-8.4-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen)

---

## Features

- Laravel 13
- PHP 8.4
- REST API Architecture
- API Versioning
- Laravel Sanctum Authentication
- UUID Primary Keys
- Service Layer Pattern
- Docker support
- Form Request Validation
- API Resources
- Standard API Responses
- Global Exception Handling
- Health Check Endpoint
- User Management
- Roles & Permissions
- User Profile Management
- Avatar Management
- Password Management
- Notifications
- Swagger/OpenAPI API Documentation
- Feature Testing
- Laravel Pint
- GitHub Actions Ready
- Template Repository Ready

---

## Requirements

- PHP 8.4+
- Composer 2.x
- MySQL 8 / MariaDB 10.6+
- Docker and Docker Compose
- Laravel 13
- Git

---

## Quick Start

```bash
git clone git@github.com:peeyush-budhia/laravel-api-base.git

cd laravel-api-base

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan serve
```

### Docker

The repository includes a Docker-based local stack with PHP-FPM, Nginx, MySQL, and Mailpit.

```bash
cp .env.docker.example .env
docker compose up --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Open the API at `http://localhost:8080`.

If you want to point the API container at a different frontend URL, update `FRONTEND_URL` in `.env`.

Common Docker shortcuts are available through `make`, for example `make up`, `make migrate`, and `make test`.
The helper targets run inside the app container as `root` so they can write to mounted project files and storage directories.

---

## Project Structure

```text
app/
bootstrap/
config/
database/
routes/
tests/
docs/
scripts/
```

---

## API Versioning

```text
/api/v1/*
```

Examples:

```text
POST /api/v1/auth/login
GET  /api/v1/auth/me
POST /api/v1/auth/logout
GET  /api/v1/users
GET  /api/v1/roles
```

---

## API Documentation

The project provides API documentation through Swagger/OpenAPI.

### Swagger UI

When running the application locally:

```text
http://example.test/docs/api
```

The Swagger UI provides interactive documentation for the available API endpoints, request parameters, authentication, responses, and schemas.

### Documentation Files

Project documentation is available in the `docs/` directory.

Important documentation includes:

- `docs/API.md` — API usage and endpoint documentation
- `docs/ROADMAP.md` — Project roadmap and planned evolution

---

## Frontend

The Laravel API Base backend is designed to work with a separate frontend application.

### Laravel API Base UI

Frontend repository:

https://github.com/peeyush-budhia/laravel-api-base-ui

The frontend is built as a separate application and consumes this Laravel API through the versioned `/api/v1` endpoints.

---

## Authentication

Authentication is powered by Laravel Sanctum.

```text
Authorization: Bearer <token>
```

Login, forgot-password, and reset-password requests are rate limited by both
account identifier and IP address. See [`docs/API.md`](docs/API.md#authentication-rate-limits)
for the active limits and `429` response contract.

Password changes, password resets, account blocking, and soft deletion revoke
affected Sanctum sessions. See the
[`token lifecycle`](docs/API.md#authentication-token-lifecycle) for the exact
behavior.

Administrator-created users receive an expiring activation link after their
database transaction commits. Temporary passwords are not sent by email. See
the [`account activation`](docs/API.md#account-activation) contract.
The companion `laravel-api-base-ui` project provides the public activation page
and submits the token through the existing reset-password endpoint.

Demonstration users are seeded only in local/testing environments. Production
administrators must be created with `php artisan app:provision-super-admin` as
described in [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md#4-configure-database).

---

## Standard API Response

### Success

```json
{
    "success": true,
    "status": 200,
    "message": "Request completed successfully.",
    "data": {},
    "errors": null,
    "meta": {}
}
```

### Error

```json
{
    "success": false,
    "status": 422,
    "message": "Validation failed.",
    "data": null,
    "errors": {},
    "meta": {}
}
```

---

## Running Tests

```bash
php artisan test
```

---

## Code Style

```bash
vendor/bin/pint
```

---

## Git Workflow

```text
main
 │
develop
 │
feature/*
```

---

## Roadmap

The current development line is `v0.9.0`.

- `v0.8.0` completed: Audit Logs & Dashboard APIs
- `v0.9.0` completed: Docker, Performance & Infrastructure

See [`docs/ROADMAP.md`](docs/ROADMAP.md) for the full release table and phase history.

---

## Contributing

Please read `CONTRIBUTING.md` before submitting pull requests.

---

## License

MIT License
