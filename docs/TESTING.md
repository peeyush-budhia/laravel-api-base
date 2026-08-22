# Testing Guide

This guide defines the testing standards and workflow for Laravel API Base.

Testing is a core part of the project architecture. Every API endpoint should have appropriate automated tests covering successful behavior, validation, authentication, authorization, and failure scenarios.

---

# Testing Stack

Laravel API Base uses Laravel's testing infrastructure and PHPUnit.

The primary command is:

```bash
php artisan test
```

# Test Structure

Tests are organized according to their purpose.
Typical structure:
tests/
├── Feature/
│ └── Api/
│ ├── Documentation
| | └── ApiDocumentationTest.php
│ ├── Auth/
│ ├── Role/
│ └── User/
└── Unit/
Feature tests are preferred for API behavior because they verify the application through the HTTP layer.

# Running the Test Suite

Run all tests:

```bash
php artisan test
```

Run a specific directory:

```bash
php artisan test tests/Feature/Api
```

Run a specific test:

```bash
php artisan test tests/Feature/Api/ApiDocumentationTest.php
```

Run a specific test method:

```bash
php artisan test --filter=ApiDocumentationTest
```

# Test Environment

Tests should run using the testing environment.
The repository contains:

```text
.env.testing
```

Test configuration should not depend on the developer's local .env.
The test database should be isolated from the development database.

# API Documentation Test Expectations

The OpenAPI document should contain:

- OpenAPI version
- API information
- API version
- Servers
- Paths
- Components
- Security schemes

The API documentation test should also verify representative endpoints.
For example:

```text
/health
/auth/login
/auth/logout
/auth/me
/users
/roles
/profile
```

The exact endpoint list should evolve with the API.

## OpenAPI Validation

Run:

```bash
php artisan scramble:analyze
```

Expected result:

```text
Everything is fine! Documentation is generated without any errors
```

Export the document when manually reviewing it:

```bash
php artisan scramble:export
```

The generated api.json file is a local artifact and should remain ignored by Git unless explicitly required by the project.

# Code Style Testing

Check formatting:

```bash
vendor/bin/pint --test
```

Automatically format:

```bash
vendor/bin/pint
```

Code should be formatted before submitting a Pull Request.

#Regression Testing
Whenever an existing feature is changed:

1. Add or update the relevant test.
2. Run the affected test.
3. Run the complete test suite.
4. Verify the API documentation.
5. Review the generated API contract where appropriate.

The goal is to prevent existing API behavior from being accidentally broken.

# Test Naming

Test names should describe behavior rather than implementation details.
Prefer:

```php
public function test_authenticated_user_can_update_profile(): void
```

over:

```php
public function test_update_profile_method(): void
```

# What Every New Endpoint Should Test

When adding an endpoint, consider this checklist:

- Successful request
- Authentication
- Authorization
- Validation
- Not found
- Response structure
- Database changes
- API Resource output
- Pagination where applicable
- Filtering where applicable
- Sorting where applicable
- OpenAPI documentation

Only include scenarios that are applicable to the endpoint.

# Pre-PR Testing Checklist

Run:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan scramble:analyze
php artisan test
git diff --check
```

All checks should pass before creating the Pull Request.

# CI Testing

The CI pipeline should execute the project's quality checks automatically.
The expected workflow is:

```text
Install Dependencies
↓
Static Analysis
↓
Code Style Check
↓
Run Tests
↓
Build
↓
Deploy
```

A Pull Request should not be merged when required CI checks are failing.

Testing Goals
Maintain:

- 100% feature test coverage for API endpoints where practical
- High Service Layer coverage
- High Query Layer coverage
- Authorization coverage
- Validation coverage
- Regression coverage
- OpenAPI documentation coverage
- Automated CI execution

Every new API endpoint should include appropriate feature tests.
