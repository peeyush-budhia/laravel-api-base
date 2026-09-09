# Backend Production Configuration and Deployment

This guide describes the minimum production configuration for Laravel API
Base. Deploy the API behind a TLS terminating web server and run the queue and
scheduler as managed processes.

## Required environment

Copy `.env.example` to `.env` and set production values. Never commit the file.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.com
FRONTEND_URL=https://app.example.com
CORS_ALLOWED_ORIGINS=https://app.example.com
APP_KEY=base64:<generated-secret>
SCRAMBLE_DOCS_ENABLED=false
SCRAMBLE_DEV_TOOLS=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=laravel_api
DB_PASSWORD=<secret>

QUEUE_CONNECTION=redis
CACHE_STORE=redis
REDIS_HOST=127.0.0.1

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=<secret>
MAIL_PASSWORD=<secret>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Laravel API Base"
```

`CORS_ALLOWED_ORIGINS` accepts a comma-separated list of exact origins, such
as `https://app.example.com,https://admin.example.com`. Do not use `*` in
production. The API uses bearer tokens, so `supports_credentials` remains
disabled.

Keep `SCRAMBLE_DOCS_ENABLED=false` in production. Scramble then does not
register `/docs/api` or `/docs/api.json`, so both paths return `404`. Contract
analysis and export remain available through Artisan and Composer commands.

## Build and deploy

Run these commands from the application release directory:

```bash
composer install --no-dev --classmap-authoritative --no-interaction
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

The web server document root must be the `public/` directory. PHP must be
8.3 or newer. Grant the web and queue users write access to `storage/` and
`bootstrap/cache/`, but keep `.env` outside the public document root.

## Managed processes

Run at least one long-lived queue worker and one scheduler process. Restart
workers after each release so they load the new code:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
php artisan schedule:work
```

Use Supervisor, systemd, or an equivalent process manager to restart failed
workers. The scheduler runs expired-token cleanup, failed-job cleanup, and
audit-log retention according to `FAILED_JOB_RETENTION_DAYS` (3 by default)
and `AUDIT_LOG_RETENTION_DAYS` (30 by default).

## Health and operations

- Probe `GET /up` for process readiness and `GET /api/v1/health` for API health.
- Terminate TLS at the load balancer or web server and forward HTTPS headers.
- Set `APP_DEBUG=false` and review `LOG_LEVEL` before enabling traffic.
- Send application and queue logs to a monitored, rotated destination.
- Back up the database and private avatar storage before every release.
- Run `php artisan about` and `php artisan config:show cors` during verification.

After changing environment values, run `php artisan optimize:clear` followed by
`php artisan config:cache`. Verify an authenticated browser request from every
configured frontend origin and confirm that an unlisted origin is rejected.

## Release checklist

1. Build and test the release artifact in CI.
2. Configure secrets and exact CORS origins.
3. Run migrations with `--force`.
4. Cache configuration, routes, and views.
5. Restart queue workers and confirm scheduler activity.
6. Probe `/up` and `/api/v1/health`.
7. Verify login, account activation email delivery, avatar upload, and a
   permission-protected endpoint from the production frontend.
8. Confirm `/docs/api` and `/docs/api.json` return `404`.
9. Record the deployed commit and retain a rollback artifact.
