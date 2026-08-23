# Development Guide

This guide explains how to set up, run, test, and work with Laravel API Base during development.

Laravel API Base is a backend-only Laravel API starter template. Business-specific functionality should be implemented in projects that use this repository as their foundation.

---

## Requirements

The development environment should provide:

- PHP 8.4+
- Composer 2.x
- MariaDB / MySQL
- Git
- Node.js and npm (when frontend tooling is required)
- Laravel 13 compatible environment

Optional development services:

- Redis
- Mailpit
- Docker

---

# Project Setup

## 1. Clone the Repository

```bash
git clone git@github.com:peeyush-budhia/laravel-example.git

cd laravel-example
```

## 2. Install PHP Dependencies

```bash
composer install
```

## 3. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Review the environment configuration:

```bash
nano .env
```

At minimum, configure:

- Application URL
- Database connection
- API version
- Mail configuration

## 4. Configure Database

Create the development database and configure the database variables in .env.

```env
Example:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api_base
DB_USERNAME=laravel
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

If seeders are available:

```bash
php artisan db:seed
```

## Application Configuration

The application API is versioned.
The current API base path is:

```text
/api/v1
```

The local development API is expected to be available at:

```text
http://example.test/api/v1
```

The exact local domain depends on the development environment.

## Running the Application

If using Laravel's built-in development server:

```bash
php artisan serve
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

If using Nginx or another local web server, use the configured application domain.

## API Health Check

The health endpoint can be used to verify that the API is running:

```http
GET /api/v1/health
```

Example using curl:

```bash
curl http://example.test/api/v1/health
```

## API Documentation

Laravel API Base uses Scramble to generate OpenAPI documentation.
The interactive API documentation is available at:

```text
/docs/api
```

The OpenAPI document is available at:

```text
/docs/api.json
```

For the local environment:

```text
http://example.test/docs/api
```

```text
http://example.test/docs/api.json
```

## OpenAPI Generation

Check the documentation generation process:

```bash
php artisan scramble:analyze
```

Generate/export the OpenAPI document:

```bash
php artisan scramble:export
```

The generated api.json file is a development artifact and should not be committed to the repository unless the project explicitly decides otherwise.

Clear the generated OpenAPI cache:

```bash
php artisan scramble:clear
```

Warm the OpenAPI cache:

```bash
php artisan scramble:cache
```

## Code Style

Laravel API Base uses Laravel Pint for code formatting.
Check the code:

```bash
vendor/bin/pint --test
```

Automatically format the code:

```bash
vendor/bin/pint
```

Only commit intentionally formatted changes.

## tatic Analysis

Laravel API Base uses Larastan for static analysis.
Run:

```bash
vendor/bin/phpstan analyse
```

Before submitting a pull request, make sure static analysis completes successfully.

## Running Tests

Run the complete test suite:

```bash
php artisan test
```

Run a specific test:

```bash
php artisan test tests/Feature/Api/ApiDocumentationTest.php
```

Run tests with coverage when coverage tooling is available:

```bash
php artisan test --coverage
```

## Development Workflow

Development should follow the project's branch strategy.

### Main Branch

```text
main
```

`main` represents the stable branch.
Direct pushes to main should not be used.
Changes should reach main through a Pull Request.

### Develop Branch

```text
develop
```

`develop` is the integration branch for the next release.
Feature branches should normally be created from develop.

### Feature Branches

Use descriptive branch names.
Examples:

```text
feature/api-documentation
feature/audit-logs
feature/dashboard-api
fix/authentication-error
docs/update-testing-guide
```

Create a feature branch:

```bash
git switch develop

git pull --ff-only origin develop

git switch -c feature/my-feature
```

## Commit Messages

Use clear, conventional commit messages.
Examples:

```text
feat: add API documentation
fix: correct role authorization
test: add OpenAPI documentation tests
docs: add development guide
refactor: simplify API response handling
chore: update dependencies
```

Keep commits focused on a single logical change.

## Pull Request Workflow

Before opening a Pull Request:

```bash
git status
```

Check formatting:

```bash
vendor/bin/pint --test
```

Run static analysis:

```bash
vendor/bin/phpstan analyse
```

Run tests:

```bash
php artisan test
```

Check documentation generation:

```bash
php artisan scramble:analyze
```

Check whitespace errors:

```bash
git diff --check
```

Review the final diff:

```bash
git diff
```

Push the feature branch:

```bash
git push -u origin feature/my-feature
```

Open a Pull Request against:

```bash
develop
```

## API Development Guidelines

New API endpoints should follow the existing architecture.
A typical API module should use:

- Route
- Controller
- Form Request
- Service
- API Resource
- Model
- Feature Tests
  Where appropriate, use:
- Policies
- Enums
- Constants
- Query infrastructure
- Shared API response helpers

## API Versioning

API routes are versioned under:

```text
/api/v1
```

Future breaking API changes should use a new API version rather than silently changing the existing contract.
For example:

```text
/api/v1
/api/v2
```

Existing API versions should remain stable unless a deliberate deprecation strategy is introduced.

## API Responses

API responses should use the project's standard response structure.
Do not introduce inconsistent response formats for individual endpoints.
When adding a new endpoint:

1. Define the successful response.
2. Define validation failures.
3. Define authorization failures.
4. Define authentication failures where applicable.
5. Define not-found behavior.
6. Ensure the responses are represented correctly in API documentation.

## Authentication

Authentication uses Laravel Sanctum.
Protected endpoints should use the appropriate authentication middleware.
When developing authenticated endpoints, test:

- Unauthenticated requests
- Authenticated requests
- Invalid tokens
- Authorization failures
- Successful access

## Documentation Guidelines

Documentation is part of the implementation.
When adding or changing an API endpoint:

- Update PHPDoc where necessary.
- Ensure request validation is documented.
- Ensure response resources are documented.
- Verify Scramble can generate the endpoint correctly.
- Add or update feature tests.
- Update developer documentation when the workflow changes.

## Environment Files

Never commit local secrets.
Do not commit:

```text
.env
```

Use:

```text
.env.example
```

for documenting required environment variables.
Never place:

- Passwords
- API keys
- Access tokens
- Production credentials
- Private secrets
  in committed files.

## Local Development Checklist

Before starting development:

```bash
git switch develop
git pull --ff-only origin develop

composer install

php artisan migrate

php artisan test
```

Before committing:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan scramble:analyze
php artisan test
git diff --check
```

Before opening a Pull Request:

```bash
git status
git diff
git push -u origin <branch-name>
```

## Recommended Development Cycle

Update develop
↓
Create feature branch
↓
Implement change
↓
Add/update tests
↓
Update documentation
↓
Run Pint
↓
Run PHPStan
↓
Run Scramble analysis
↓
Run PHPUnit
↓
Review git diff
↓
Push branch
↓
Create Pull Request → develop

Important Principle
Laravel API Base is a reusable technical foundation.
Do not add business-specific modules merely because they are required by one consuming application.
Business-specific functionality belongs in the project built on top of this template.
Examples:

- Company Management
- Customer Management
- Supplier Management
- Billing
- Inventory
- Industry-specific workflows
  These should remain outside the core API Base unless they become genuinely reusable infrastructure.
