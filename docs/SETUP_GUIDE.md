# Laravel API Base Setup Guide

This guide runs the Laravel backend and React frontend together for local development.

## 1. Install prerequisites

Install PHP 8.4+, Composer 2, Node.js 22+, npm 10+, Git, and MySQL 8 or MariaDB 10.6+. SQLite can be used for backend tests. Make sure `php`, `composer`, `node`, and `npm` are available in your shell.

## 2. Set up the backend

```bash
git clone https://github.com/peeyush-budhia/laravel-api-base.git
cd laravel-api-base
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`. A local SQLite setup can use:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/laravel-api-base/database/database.sqlite
```

For MySQL, set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` instead. Then run:

```bash
php artisan migrate
php artisan serve --host=localhost --port=8000
```

The backend API is now at `http://localhost:8000/api/v1`.

## 3. Configure mail and queues

For local development, keep `MAIL_MAILER=log` or use Mailpit. The database queue is suitable for local onboarding tests:

```bash
php artisan queue:work
```

In a second backend terminal, run scheduled cleanup:

```bash
php artisan schedule:work
```

Administrator-created users receive activation links only after their database transaction commits. Keep the worker running before creating a user.

## 4. Create an administrator

Provision the first administrator interactively:

```bash
php artisan app:provision-super-admin
```

Local and testing environments may use seeded demo data. Production seeding does not create demo accounts.

## 5. Set up the frontend

Open a second shell and clone the UI beside the backend:

```bash
cd ..
git clone https://github.com/peeyush-budhia/laravel-api-base-ui.git
cd laravel-api-base-ui
npm ci
cp .env.example .env
cp vite.config.example.ts vite.config.ts
```

Set the API URL in `.env`:

```env
VITE_API_BASE_URL="http://localhost:8000/api/v1"
```

The tracked Vite example uses `http://localhost:5173` and allows the `localhost` host. The copied `vite.config.ts` is ignored so machine-specific settings remain local.

Start the UI:

```bash
npm run dev
```

Open `http://localhost:5173`.

## 6. Verify the integration

Use the UI to log in with the provisioned administrator, then verify the dashboard, profile, user, role, permission, and audit screens. To test onboarding, create a user from the UI and open the activation link written to the backend log or delivered by the configured mail transport.

Run backend checks from the backend directory:

```bash
composer test
composer lint
composer analyse
composer docs:check
```

Run frontend checks from the frontend directory:

```bash
npm run format:check
npm run lint
npm test
npm run build
```

## Troubleshooting

- `Connection refused` from the UI: confirm the backend is running on port `8000` and `VITE_API_BASE_URL` includes `/api/v1`.
- Activation email missing: confirm `php artisan queue:work` is running and inspect the configured mail output.
- Vite host errors: recreate the ignored config with `cp vite.config.example.ts vite.config.ts`.
- Database errors: verify `.env`, run `php artisan migrate`, and clear cached configuration with `php artisan optimize:clear`.

## Production deployment

For production environment values, CORS allowlists, web-server requirements,
queue workers, scheduler management, health checks, and the release checklist,
see [PRODUCTION.md](PRODUCTION.md).
