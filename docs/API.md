# Laravel API Base — API Documentation

## Overview

Laravel API Base provides a versioned REST API for authentication, user management, profile management, roles, permissions, and system health.

The API is designed as a reusable, domain-agnostic Laravel backend foundation.

---

## Base URL

### Local Development

```text
http://example.test/api/v1
```

All application API endpoints are versioned under:

```text
/api/v1
```

### Swagger UI

Interactive API documentation is available at:

```text
http://example.test/docs/api
```

Swagger UI provides the current OpenAPI-based endpoint documentation, request parameters, authentication information, and response schemas.

---

# Authentication

Laravel Sanctum Personal Access Tokens are used for API authentication.

After successful login, the API returns an access token.

Send the token using:

```http
Authorization: Bearer {access_token}
```

Protected endpoints require authentication.

---

# Standard API Response

Successful responses follow the common API response structure:

```json
{
    "success": true,
    "status": 200,
    "message": "Operation completed successfully.",
    "data": {},
    "errors": null,
    "meta": {}
}
```

Validation and other API errors follow the common error structure:

```json
{
    "success": false,
    "status": 422,
    "message": "The given data was invalid.",
    "data": null,
    "errors": {
        "field": ["The field is required."]
    },
    "meta": {}
}
```

---

# API Endpoints

## Health

| Method | Endpoint  | Authentication | Description      |
| ------ | --------- | -------------- | ---------------- |
| GET    | `/health` | No             | Check API health |

---

# Authentication Endpoints

| Method | Endpoint                | Authentication | Description                              |
| ------ | ----------------------- | -------------- | ---------------------------------------- |
| POST   | `/auth/login`           | No             | Authenticate a user                      |
| POST   | `/auth/logout`          | Yes            | Logout the current user                  |
| GET    | `/auth/me`              | Yes            | Get the authenticated user               |
| POST   | `/auth/forgot-password` | No             | Request a password reset                 |
| POST   | `/auth/reset-password`  | No             | Reset the password                       |
| POST   | `/auth/change-password` | Yes            | Change the authenticated user's password |

---

## Login

```http
POST /api/v1/auth/login
```

Example request:

```json
{
    "login": "user@example.com",
    "password": "password",
    "remember_me": true
}
```

The response contains the authenticated user and access token.

---

## Current User

```http
GET /api/v1/auth/me
```

Requires:

```http
Authorization: Bearer {access_token}
```

---

## Logout

```http
POST /api/v1/auth/logout
```

Requires authentication.

---

# Profile

| Method | Endpoint          | Authentication | Description                         |
| ------ | ----------------- | -------------- | ----------------------------------- |
| PUT    | `/profile`        | Yes            | Update authenticated user's profile |
| POST   | `/profile/avatar` | Yes            | Update authenticated user's avatar  |

### Update Profile

```http
PUT /api/v1/profile
```

### Update Avatar

```http
POST /api/v1/profile/avatar
Content-Type: multipart/form-data
```

Form field:

```text
avatar
```

---

# Users

| Method | Endpoint              | Authentication | Permission          |
| ------ | --------------------- | -------------- | ------------------- |
| GET    | `/users`              | Yes            | `users.view`        |
| POST   | `/users`              | Yes            | `users.create`      |
| GET    | `/users/{id}`         | Yes            | `users.view`        |
| PATCH  | `/users/{id}`         | Yes            | `users.update`      |
| DELETE | `/users/{id}`         | Yes            | `users.delete`      |
| PATCH  | `/users/{id}/restore` | Yes            | `users.restore`     |
| DELETE | `/users/{id}/force`   | Yes            | Protected operation |

## User List

```http
GET /api/v1/users
```

Supported query parameters include:

```text
page
per_page
search
sort
direction
trashed
```

`trashed` supports:

```text
without
with
only
```

Example:

```text
GET /api/v1/users?page=1&per_page=20&search=john&sort=created_at&direction=desc&trashed=without
```

---

# Roles

| Method    | Endpoint                  | Authentication | Permission                 |
| --------- | ------------------------- | -------------- | -------------------------- |
| GET       | `/roles`                  | Yes            | `roles.view`               |
| POST      | `/roles`                  | Yes            | `roles.create`             |
| GET       | `/roles/{id}`             | Yes            | `roles.view`               |
| PUT/PATCH | `/roles/{id}`             | Yes            | `roles.update`             |
| DELETE    | `/roles/{id}`             | Yes            | `roles.delete`             |
| GET       | `/roles/permissions`      | Yes            | Role/permission access     |
| GET       | `/roles/{id}/permissions` | Yes            | Role/permission access     |
| PUT       | `/roles/{id}/permissions` | Yes            | `roles.manage-permissions` |

## Role List

```http
GET /api/v1/roles
```

Supported query parameters:

```text
page
per_page
search
sort
direction
```

---

## Create Role

```http
POST /api/v1/roles
```

Example:

```json
{
    "name": "Manager"
}
```

---

## Update Role

```http
PUT /api/v1/roles/{id}
```

Example:

```json
{
    "name": "Senior Manager"
}
```

Role details and permission synchronization are intentionally separate operations.

---

## Get All Permissions

```http
GET /api/v1/roles/permissions
```

Returns the permissions available to the application.

---

## Get Role Permissions

```http
GET /api/v1/roles/{id}/permissions
```

Returns permissions currently assigned to the role.

---

## Synchronize Role Permissions

```http
PUT /api/v1/roles/{id}/permissions
```

Example:

```json
{
    "permissions": ["users.view", "users.create", "users.update"]
}
```

The supplied permission list becomes the role's synchronized permission set.

---

# Authorization

The API uses role- and permission-based authorization.

Current application permissions include:

## Roles

```text
roles.view
roles.create
roles.update
roles.delete
roles.manage-permissions
```

## Users

```text
users.view
users.create
users.update
users.delete
users.restore
```

Authorization is enforced on the backend. Frontend permission checks are for user-interface behavior and must not be treated as a security boundary.

---

# User Lifecycle

Users support soft deletion.

The lifecycle includes:

```text
Active
  ↓
Soft Deleted
  ↓
Restored
  ↓
Active
```

A permanently deleted user cannot be restored.

---

# Pagination

Paginated endpoints return:

```json
{
    "success": true,
    "status": 200,
    "message": "Records retrieved successfully.",
    "data": [],
    "errors": null,
    "meta": {
        "current_page": 1,
        "per_page": 20,
        "total": 100,
        "last_page": 5,
        "from": 1,
        "to": 20,
        "path": "http://example.test/api/v1/users",
        "links": {
            "first": "...",
            "last": "...",
            "prev": null,
            "next": "..."
        }
    }
}
```

---

# HTTP Status Codes

| Status | Meaning                            |
| -----: | ---------------------------------- |
|    200 | Successful request                 |
|    201 | Resource created                   |
|    204 | Successful request with no content |
|    400 | Bad request                        |
|    401 | Unauthenticated                    |
|    403 | Forbidden                          |
|    404 | Resource not found                 |
|    409 | Conflict                           |
|    422 | Validation error                   |
|    429 | Too many requests                  |
|    500 | Server error                       |

---

# API Versioning

The current API version is:

```text
v1
```

All versioned API endpoints use:

```text
/api/v1
```

Future breaking changes should be introduced through a new API version rather than silently changing the existing contract.

---

# API Documentation

Swagger UI is the primary interactive API reference for the backend:

```text
http://example.test/docs/api
```

The documentation should be kept synchronized with the actual API implementation.

---

# Related Documentation

- `README.md` — Project overview and development setup
- `docs/ROADMAP.md` — Project roadmap and release plan
- `docs/ARCHITECTURE.md` — Backend architecture
- `docs/AUTHENTICATION.md` — Authentication details
- `docs/DEVELOPMENT.md` — Development workflow
- `docs/TESTING.md` — Testing standards

---

# Frontend Integration

The separate Laravel API Base UI project consumes this API.

The frontend should use:

```text
http://example.test/api/v1
```

for local API requests.

The frontend project should treat this backend API as an independent service and should not depend on Laravel server-rendered views.

---

# Notes

- API identifiers use UUIDs.
- Authentication uses Laravel Sanctum Personal Access Tokens.
- API responses use a consistent response structure.
- Validation is handled through Laravel Form Requests.
- API output is handled through Laravel API Resources.
- Business-domain functionality should remain outside the reusable API Base.
- Swagger UI should be considered the live interactive reference for the implemented API contract.
