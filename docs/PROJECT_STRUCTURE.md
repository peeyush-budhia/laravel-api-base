# Project Structure

## Overview

Laravel API Base follows a clean, modular, and scalable directory structure based on Laravel conventions and enterprise development practices.

The goal is to make the project easy to understand, maintain, and extend.

---

# Root Directory

```
laravel-api-base/

├── app/
├── bootstrap/
├── config/
├── database/
├── docs/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/

├── .editorconfig
├── .env
├── .env.example
├── .gitattributes
├── .gitignore
├── composer.json
├── composer.lock
├── phpunit.xml
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
└── LICENSE
```

---

# app/

Contains all application source code.

```
app/

├── Console/
├── Enums/
├── Exceptions/
├── Helpers/
├── Http/
├── Models/
├── Providers/
├── Services/
├── Support/
└── Traits/
```

---

# app/Console

Contains Artisan commands.

Example

```
CreateAdminUserCommand

SyncPermissionsCommand

GenerateApiDocsCommand
```

---

# app/Enums

Application enums.

Example

```
UserStatus

OrderStatus

PaymentStatus
```

Always prefer Enums over magic strings.

---

# app/Exceptions

Contains custom exception handlers and custom exceptions.

Example

```
ExceptionHandler.php

BusinessException.php
```

Never duplicate exception handling in Controllers.

---

# app/Helpers

Global helper functions.

Example

```
helpers.php
```

Only place generic reusable functions here.

---

# app/Http

Contains all HTTP layer components.

```
Http/

Controllers/

Middleware/

Requests/

Resources/
```

---

# Controllers

Responsibilities

- Receive Request
- Call Service
- Return Resource

Controllers must remain thin.

---

# Middleware

Responsibilities

- Authentication
- Authorization
- Logging
- Rate Limiting
- Request Modification

---

# Requests

Contains Form Requests.

Example

```
LoginRequest

UserStoreRequest

UserUpdateRequest
```

Handles

- Validation
- Authorization

---

# Resources

Transforms models into API responses.

Example

```
UserResource

CompanyResource

RoleResource
```

Never return Eloquent models directly.

---

# app/Models

Contains Eloquent models.

Example

```
User

Company

Role

Permission
```

Responsibilities

- Relationships
- Scopes
- Accessors
- Mutators
- Casts

Avoid business logic.

---

# app/Providers

Laravel service providers.

Register

- Bindings
- Macros
- Event listeners

---

# app/Services

Contains business logic.

Example

```
AuthService

UserService

CompanyService

RoleService
```

Controllers communicate only with Services.

---

# app/Support

Reusable application support classes.

Example

```
ApiResponse

Constants

Paginator

ResponseBuilder
```

---

# app/Traits

Reusable functionality.

Example

```
HasUuid

Searchable

HasCompany

HasAudit
```

Traits should not contain business rules.

---

# bootstrap/

Application bootstrap files.

Contains

```
app.php

cache/
```

Responsibilities

- Configure Laravel
- Exception handling
- Middleware registration
- Route loading

---

# config/

Configuration files.

Examples

```
app.php

auth.php

cache.php

database.php

mail.php

queue.php

sanctum.php
```

Never hardcode configuration values.

Always use

```
config()
```

---

# database/

Contains database resources.

```
database/

factories/

migrations/

seeders/
```

---

# factories/

Model factories for testing.

Example

```
UserFactory
```

---

# migrations/

Database schema.

Each migration should perform one task only.

---

# seeders/

Database seeders.

Example

```
DatabaseSeeder

RoleSeeder

PermissionSeeder
```

---

# docs/

Project documentation.

```
API_STANDARDS.md

ARCHITECTURE.md

PROJECT_STRUCTURE.md

BRANCHING_STRATEGY.md

ROADMAP.md
```

This folder contains project standards.

---

# public/

Public entry point.

Contains

```
index.php

favicon.ico
```

Never place application code here.

---

# resources/

Frontend assets.

For API-only projects this directory is minimal.

---

# routes/

Application routes.

Recommended structure

```
routes/

api.php

console.php

web.php

api/

v1/

auth.php

users.php

roles.php

permissions.php
```

As the application grows, split routes by module.

---

# storage/

Application storage.

Contains

```
logs/

framework/

app/
```

Never commit storage contents.

---

# tests/

Contains automated tests.

```
tests/

Feature/

Unit/
```

---

# Feature Tests

Tests complete HTTP workflows.

Examples

```
AuthenticationTest

UserTest

CompanyTest
```

---

# Unit Tests

Tests isolated business logic.

Example

```
UserServiceTest
```

---

# vendor/

Composer dependencies.

Never modify vendor files.

Never commit changes inside vendor.

---

# Environment Files

```
.env

.env.example
```

Only commit

```
.env.example
```

Never commit

```
.env
```

---

# composer.json

Defines

- Packages
- Autoloading
- Scripts

---

# phpunit.xml

Defines testing configuration.

---

# README.md

Main project documentation.

---

# CHANGELOG.md

Version history.

---

# CONTRIBUTING.md

Contribution guidelines.

---

# LICENSE

Project license.

---

# Future Modules

As features are added, the structure should remain consistent.

Example

```
Users

Controller

↓

Request

↓

Service

↓

Model

↓

Resource

↓

Feature Test
```

The same pattern applies to

- Companies
- Customers
- Suppliers
- Products
- Orders
- Payments
- Roles
- Permissions

---

# Folder Responsibilities Summary

| Folder     | Responsibility              |
| ---------- | --------------------------- |
| app/       | Application source code     |
| bootstrap/ | Application bootstrap       |
| config/    | Configuration               |
| database/  | Database schema and seeders |
| docs/      | Project documentation       |
| public/    | Public entry point          |
| resources/ | Frontend resources          |
| routes/    | Route definitions           |
| storage/   | Logs and runtime files      |
| tests/     | Automated tests             |
| vendor/    | Composer packages           |

---

# Development Rules

Always

- Use Form Requests
- Use Services
- Use Resources
- Write Feature Tests
- Keep Controllers thin
- Keep Models lightweight
- Follow SOLID principles

Never

- Place business logic in Controllers
- Return raw Eloquent models
- Hardcode configuration values
- Modify vendor code
- Commit secrets or environment files

---

# Goal

A predictable and organized project structure makes onboarding easier, improves maintainability, and allows every future feature to follow the same architectural conventions without introducing inconsistency.
