# Laravel API Base

Laravel API Base is the backend for the Laravel API Base UI. It provides a versioned Laravel 13 REST API for authentication, onboarding, users, roles, permissions, profiles, avatars, audit logs, and dashboard statistics.

Latest published release: **v0.9.0**. The `release/v1.0.0` branch contains the
validated **v1.0.0 release candidate**.

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

The API is available at `http://localhost:8000/api/v1`. For a complete backend-and-frontend setup, see [docs/SETUP_GUIDE.md](docs/SETUP_GUIDE.md).

Create the first production administrator with:

```bash
php artisan app:provision-super-admin
```

Production seeding creates all permissions and only the protected `super-admin`
role. Local and testing environments also receive a limited `admin` demo role
and demo accounts; production receives no demo roles or users.

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

When `SCRAMBLE_DOCS_ENABLED=true`, interactive OpenAPI documentation is
available at `/docs/api` while the application is running. Production should
leave this setting disabled and use the committed contract snapshot. Detailed
contracts and authorization rules are in [docs/API.md](docs/API.md),
[docs/API_STANDARDS.md](docs/API_STANDARDS.md), and the
[v1.0.0 contract freeze](docs/API_CONTRACT_FREEZE.md).

Production environment, CORS, worker, scheduler, and deployment instructions are in [docs/PRODUCTION.md](docs/PRODUCTION.md).

Security controls and API failure behavior are documented in [docs/SECURITY.md](docs/SECURITY.md) and [docs/ERROR_HANDLING.md](docs/ERROR_HANDLING.md).

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
composer contract:export
```

GitHub Actions runs dependency validation, formatting, PHPStan, the test suite
with its coverage threshold, MySQL integration tests, OpenAPI analysis, and a
generated-contract freshness check before changes can be merged.

## Related frontend

The companion React application is maintained at [laravel-api-base-ui](https://github.com/peeyush-budhia/laravel-api-base-ui). It consumes this API through `/api/v1` and provides account activation, authentication, profile, user, role, permission, audit, and dashboard screens.

## Contributing and license

Use feature branches and read [docs/CONTRIBUTING.md](docs/CONTRIBUTING.md) before opening a pull request. This project is released under the MIT License.
