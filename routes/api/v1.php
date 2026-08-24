<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| API Version 1 Routes
|--------------------------------------------------------------------------
|
| Register all V1 route modules here.
|
*/
require __DIR__.'/v1/health.php';
require __DIR__.'/v1/auth.php';
require __DIR__.'/v1/dashboard.php';
require __DIR__.'/v1/users.php';
require __DIR__.'/v1/roles.php';
require __DIR__.'/v1/audit-logs.php';

// Future modules
// require __DIR__ . '/v1/companies.php';
// require __DIR__ . '/v1/suppliers.php';
// require __DIR__ . '/v1/purchasers.php';
// require __DIR__ . '/v1/bills.php';
// require __DIR__ . '/v1/payments.php';
// require __DIR__ . '/v1/reports.php';
