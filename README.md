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

The current release line is `v0.8.0`.

- `v0.8.0` released: Audit Logs & Dashboard APIs
- `v0.9.0` planned: Docker, Performance & Infrastructure

See [`docs/ROADMAP.md`](docs/ROADMAP.md) for the full release table and phase history.

---

## Contributing

Please read `CONTRIBUTING.md` before submitting pull requests.

---

## License

MIT License
