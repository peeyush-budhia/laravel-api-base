# Laravel API Base

Laravel API Base is the backend for the Laravel API Base UI. It provides a versioned Laravel 13 REST API for authentication, onboarding, users, roles, permissions, profiles, avatars, audit logs, and dashboard statistics.

Current release: **v0.9.0**.

## Requirements

- PHP 8.4+
- Composer 2+
- MySQL 8 / MariaDB 10.6+ (SQLite is suitable for local tests)
- Git

## Quick start

```bash
git clone https://github.com/peeyush-budhia/laravel-api-base.git
cd laravel-api-base
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --host=localhost --port=8000
```

The API is available at `http://localhost:8000/api/v1`. For a complete backend-and-frontend setup, see [SETUP_GUIDE.md](SETUP_GUIDE.md).

Create the first production administrator with:

```bash
php artisan app:provision-super-admin
```

Local and testing environments may use seeded demo accounts. Production seeding does not create demo users.

## Background processes

User onboarding notifications use the configured queue connection. Run a worker while testing account creation:

```bash
php artisan queue:work
```

Run the scheduler to execute expired-token, failed-job, and audit-log retention cleanup:

```bash
php artisan schedule:work
```

Retention defaults are three days for failed jobs and thirty days for audit logs. They can be changed with `FAILED_JOB_RETENTION_DAYS` and `AUDIT_LOG_RETENTION_DAYS`.

## API and documentation

All application routes are versioned under `/api/v1`.

- `GET /api/v1/health` — health check
- `POST /api/v1/auth/login` — authenticate and receive a Sanctum token
- `GET /api/v1/users` — permission-protected user listing
- `GET /api/v1/audit-logs` — permission-protected audit listing
- `GET /api/v1/dashboard` — permission-protected dashboard statistics

Interactive OpenAPI documentation is available at `/docs/api` while the application is running. Detailed contracts and authorization rules are in [docs/API.md](docs/API.md) and [docs/API_STANDARDS.md](docs/API_STANDARDS.md).

## Architecture

```text
app/Http       controllers, requests, resources, middleware
app/Services   business workflows
app/Query      reusable listing, filtering, and sorting queries
app/Models     persistence models
database       migrations, factories, and seeders
routes/api     versioned API route files
tests          feature and unit tests
docs           API, development, testing, and release guidance
```

The API uses Sanctum bearer tokens, UUID identifiers, service-layer workflows, standard response envelopes, transaction-aware audit logging, and permission-scoped dashboard caching.

## Quality checks

```bash
composer test
composer lint
composer analyse
composer docs:check
```

GitHub Actions runs formatting, PHPStan, the test suite, and OpenAPI validation before changes can be merged.

## Related frontend

The companion React application is maintained at [laravel-api-base-ui](https://github.com/peeyush-budhia/laravel-api-base-ui). It consumes this API through `/api/v1` and provides account activation, authentication, profile, user, role, permission, audit, and dashboard screens.

## Contributing and license

Use feature branches and read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request. This project is released under the MIT License.
