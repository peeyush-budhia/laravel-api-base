# Architecture

## Overview

Laravel API Base follows a **Layered Architecture** with a **Service Layer Pattern**.

The primary goals are:

- Separation of Concerns
- Maintainability
- Scalability
- Testability
- Reusability
- Clean Code
- SOLID Principles

Business logic is never placed inside Controllers.

---

# High-Level Architecture

```
                Client
                   │
                   ▼
              API Routes
                   │
                   ▼
             Form Requests
                   │
                   ▼
              Controllers
                   │
                   ▼
               Services
                   │
                   ▼
          Models (Eloquent)
                   │
                   ▼
               Database
```

Resources transform data before it is returned to the client.

Exceptions are handled globally.

---

# Request Lifecycle

```
HTTP Request

↓

Route

↓

Middleware

↓

Form Request Validation

↓

Controller

↓

Service

↓

Model

↓

Database

↓

Service

↓

API Resource

↓

API Response

↓

Client
```

---

# Directory Structure

```
app/

├── Console/
├── Enums/
├── Exceptions/
├── Helpers/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   └── Resources/
│
├── Models/
├── Providers/
├── Services/
├── Support/
├── Traits/
└── ...
```

---

# Layer Responsibilities

## Routes

Responsibilities

- Define endpoints
- Apply middleware
- Group API versions

Never

- Business logic
- Validation

Example

```
Route::apiResource('users', UserController::class);
```

---

## Controllers

Controllers must remain thin.

Responsibilities

- Receive request
- Call Service
- Return Resource

Example

```
Request

↓

Service

↓

Resource

↓

Response
```

Controllers should not

- Validate manually
- Query models
- Perform calculations
- Handle business rules

Maximum recommended length

```
100–150 lines
```

---

## Form Requests

Every endpoint that accepts user input must use a Form Request.

Responsibilities

- Validation
- Authorization

Example

```
UserStoreRequest

UserUpdateRequest

LoginRequest
```

Never

```
$request->validate(...)
```

inside Controllers.

---

## Services

The Service Layer contains all business logic.

Responsibilities

- Business rules
- Transactions
- Model interaction
- External services
- Complex calculations

Example

```
UserService

CompanyService

AuthService

RoleService
```

Controllers communicate only with Services.

---

## Models

Models represent database tables.

Responsibilities

- Relationships
- Scopes
- Accessors
- Mutators
- Casts

Avoid

Large business logic.

---

## Resources

Resources transform Models into API responses.

Never return Eloquent models directly.

Correct

```
return new UserResource($user);
```

Avoid

```
return $user;
```

---

# Support

Support contains reusable utilities.

Example

```
ApiResponse

UuidGenerator

Helpers

Constants
```

---

# Traits

Traits should contain reusable behaviour.

Examples

```
HasUuid

HasCompany

HasAudit

Searchable
```

Traits should not contain business logic.

---

# Exceptions

All exceptions are handled centrally.

```
bootstrap/app.php

↓

ExceptionHandler

↓

ApiResponse
```

Controllers should never manually return error responses.

---

# Dependency Injection

Always use Constructor Injection.

Correct

```php
class UserController
{
    public function __construct(
        private readonly UserService $service
    ) {}
}
```

Avoid

```php
new UserService();
```

---

# Database Layer

Application uses Eloquent ORM.

Responsibilities

- Persistence
- Relationships
- Query Scopes

Business logic belongs in Services.

---

# Transactions

Use transactions for multiple database operations.

Example

```php
DB::transaction(function () {

});
```

Never leave partially completed operations.

---

# Authentication

Laravel Sanctum

```
Client

↓

Bearer Token

↓

Sanctum Middleware

↓

Authenticated User
```

---

# Authorization

Authorization should use:

- Policies
- Gates

Never hardcode permissions inside Controllers.

---

# API Responses

All responses must use the shared response helper.

```
ApiResponse::success()

ApiResponse::created()

ApiResponse::validation()

ApiResponse::notFound()

ApiResponse::unauthorized()
```

This ensures consistent API contracts.

---

# Validation

Validation flow

```
Request

↓

Form Request

↓

Controller

↓

Service
```

Services should receive validated data only.

---

# Versioning

API versioning is implemented through routing.

```
routes/

api/

v1/

auth.php

users.php

companies.php
```

Future versions

```
v2/

v3/
```

No code changes required for existing clients.

---

# Testing Strategy

Every feature includes:

- Feature Tests
- Unit Tests (when applicable)

Feature tests verify:

- Success
- Validation
- Authentication
- Authorization
- Database changes

---

# Logging

Use Laravel logging.

Never

```
dd()

dump()

var_dump()

print_r()
```

in production code.

---

# Configuration

Never hardcode values.

Use

```
config()

env()
```

Example

```
config('app.name')
```

---

# SOLID Principles

The project follows SOLID.

## Single Responsibility

Each class has one purpose.

Examples

```
Controller

Service

Resource

Form Request
```

---

## Open / Closed

Extend behaviour.

Avoid modifying stable code unnecessarily.

---

## Liskov Substitution

Child classes must behave like parents.

---

## Interface Segregation

Keep interfaces focused.

---

## Dependency Inversion

Depend on abstractions.

Prefer dependency injection.

---

# Why Service Layer?

This project intentionally uses a **Service Layer** instead of a Repository Pattern.

Reasons

- Eloquent already acts as a repository.
- Reduces unnecessary abstraction.
- Easier to maintain.
- Less boilerplate.
- Better readability.
- Faster development.

Repositories should only be introduced when there is a genuine need to swap data sources or encapsulate highly complex query logic.

---

# Design Principles

The project follows:

- REST
- KISS
- DRY
- SOLID
- Convention over Configuration
- Separation of Concerns

---

# Scalability

Future modules should follow the same architecture.

Example

```
Users

Controller

↓

Service

↓

Model

↓

Resource

↓

Feature Tests
```

Exactly the same structure applies to:

- Companies
- Roles
- Permissions
- Customers
- Products
- Orders
- Payments

---

# Coding Guidelines

Always

- Use strict types
- Constructor Property Promotion
- Dependency Injection
- API Resources
- Form Requests
- Service Layer
- Feature Tests

Avoid

- Fat Controllers
- Duplicate code
- Inline validation
- Inline SQL
- Business logic inside Models
- Returning raw models

---

# Architecture Goals

This architecture is designed to produce APIs that are:

- Clean
- Predictable
- Scalable
- Testable
- Reusable
- Easy to maintain

Every new feature added to Laravel API Base must follow the standards defined in this document.
