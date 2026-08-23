# Laravel API Base

> A production-ready Laravel 13 REST API Starter Kit with Authentication, API Versioning, Service Layer Architecture, UUID Support, Standardized API Responses, and Comprehensive Testing.

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

```
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

```
/api/v1/*
```

Example

```
POST /api/v1/auth/login

GET /api/v1/auth/me

POST /api/v1/auth/logout
```

---

## Authentication

Authentication is powered by Laravel Sanctum.

```
Authorization: Bearer <token>
```

---

## Standard API Response

Success

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

Error

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

```
main
    │
develop
    │
feature/*
```

---

## Roadmap

- API Foundation
- User Management
- Roles & Permissions
- File Upload
- Search / Filter / Sort
- Notifications
- Swagger/OpenAPI
- GitHub Actions

---

## Contributing

Please read CONTRIBUTING.md before submitting pull requests.

---

## License

MIT License
