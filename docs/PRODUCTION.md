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
MAIL_SCHEME=tls
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

## Production Docker stack

The production Compose file builds immutable PHP-FPM and Nginx images. The
application image installs authoritative Composer dependencies with
`--no-dev`; source code and `vendor/` are copied into the image instead of
being bind-mounted. Its runtime excludes compilers and development headers and
uses production OPcache settings. Separate containers run the queue worker and
scheduler. MySQL, Redis, and application storage use named volumes and are not
published to the host.

Create the production environment file and replace every placeholder:

```bash
cp .env.docker.production.example .env.production
php artisan key:generate --show
```

Paste the generated key into `APP_KEY`. Set the public HTTPS URLs, exact CORS
origin, database, Redis, and mail credentials. Then build and migrate:

```bash
docker compose --env-file .env.production -f docker-compose.production.yml build
docker compose --env-file .env.production -f docker-compose.production.yml run --rm app php artisan migrate --force
docker compose --env-file .env.production -f docker-compose.production.yml up -d
```

The stack publishes Nginx on `APP_PORT` (8080 by default). TLS should terminate
at a reverse proxy or load balancer in front of that port. Probe the web
container through `/up`; the PHP-FPM container also has an internal FastCGI
health check. The queue worker exits cleanly after one hour and Docker restarts
it, which regularly reloads application code and limits long-lived process
growth.

The Docker image build is also a release gate in `.github/workflows/docker.yml`.
It builds both production targets on release branches and pull requests, so a
release cannot silently depend on Composer development packages or an invalid
Nginx configuration.

After deploying a new image, run migrations before switching traffic and
recreate all application processes:

Set `APP_IMAGE` and `WEB_IMAGE` in `.env.production` to immutable image tags in
your registry before using `pull`. If the deployment host builds images from
source, run `build` in place of `pull`.

```bash
docker compose --env-file .env.production -f docker-compose.production.yml pull
docker compose --env-file .env.production -f docker-compose.production.yml run --rm app php artisan migrate --force
docker compose --env-file .env.production -f docker-compose.production.yml up -d --remove-orphans
```

Use externally managed MySQL or Redis by removing their Compose services and
the corresponding `depends_on` entries, then set `DB_HOST`, `REDIS_HOST`, and
credentials in `.env.production`. Back up the `mysql-data`, `redis-data`, and
`app-storage` volumes when using the bundled services. Do not bake
`.env.production` into an image or commit it.

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
