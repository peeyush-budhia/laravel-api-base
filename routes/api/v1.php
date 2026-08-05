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
require_once __DIR__ . '/v1/health.php';
require_once __DIR__ . '/v1/auth.php';
require_once __DIR__ . '/v1/users.php';


// Future modules
// require_once __DIR__ . '/v1/roles.php';
// require_once __DIR__ . '/v1/permissions.php';
// require_once __DIR__ . '/v1/companies.php';
// require_once __DIR__ . '/v1/suppliers.php';
// require_once __DIR__ . '/v1/purchasers.php';
// require_once __DIR__ . '/v1/bills.php';
// require_once __DIR__ . '/v1/payments.php';
// require_once __DIR__ . '/v1/reports.php';