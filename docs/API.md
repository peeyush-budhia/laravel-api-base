# Laravel API Base — API Documentation

## Overview

Resources return backend-owned display metadata: status_label and status_tone
for users, event_label and event_tone for audit records, and description for
permissions. Tone values are semantic and independent of frontend CSS.

Laravel API Base provides a versioned REST API for authentication, user management, profile management, roles, permissions, audit logging, dashboard statistics, and system health.

The v1.0.0 compatibility boundary is recorded in [API_CONTRACT_FREEZE.md](API_CONTRACT_FREEZE.md).

Resources expose machine values together with backend-owned display labels:
users include status and status_label, audit records include event and
event_label, and permissions include description. Clients should render the
label fields instead of maintaining enum label tables.

Browser clients must use an origin listed in `CORS_ALLOWED_ORIGINS`; see
[PRODUCTION.md](PRODUCTION.md) for production CORS and deployment settings.

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

## Authentication Rate Limits

The public authentication endpoints use independent account and IP limits. All
limits use a one-minute window.

| Endpoint                | Account limit | IP limit |
| ----------------------- | ------------- | -------- |
| `/auth/login`           | 5 requests    | 20 requests |
| `/auth/forgot-password` | 3 requests    | 10 requests |
| `/auth/reset-password`  | 5 requests    | 10 requests |

The login account key uses the normalized `login` value. Password-reset account
keys use the normalized `email` value. Identifiers are hashed before being
stored in cache keys.

Exceeding either limit returns HTTP `429` using the standard API error envelope:

```json
{
    "success": false,
    "status": 429,
    "message": "Too many requests. Please try again later.",
    "data": null,
    "errors": null,
    "meta": {}
}
```

## Authentication Token Lifecycle

Sanctum tokens are revoked when authentication or account state changes make
existing sessions unsafe.

| Account action | Token behavior |
| -------------- | -------------- |
| Logout | Revokes the bearer token used for the request |
| Authenticated password change | Preserves the current bearer token and revokes all other personal access tokens |
| Password change without a persisted bearer token | Revokes all personal access tokens |
| Password reset | Revokes all personal access tokens |
| Administrator password change | Revokes all personal access tokens |
| Administrator blocks the account | Revokes all personal access tokens |
| Soft deletion | Revokes all personal access tokens in the deletion transaction |
| Account restoration | Does not restore or recreate previously revoked tokens |

After another session is revoked, requests using its bearer token return HTTP
`401`. A restored user must authenticate again to receive a new token.

## Account Activation

When an administrator creates a user, the API generates an unknown initial
credential and an expiring activation token. The queued onboarding email does
not contain a temporary password. It links to the frontend URL configured by
`FRONTEND_URL`:

```text
{FRONTEND_URL}/activate-account?token={token}&email={email}
```

The activation token uses Laravel's `users` password broker and expires after
`auth.passwords.users.expire` minutes, which defaults to 60. The frontend
collects and confirms the user's new password, then submits the token, email,
password, and password confirmation to `POST /api/v1/auth/reset-password`.
A successful activation consumes the token and sets `email_verified_at` to the
current time because possession of the activation link proves access to the
onboarding address. Login does not mark an email as verified. Changing the email
after the account has been used clears verification, and a later password reset
or login does not restore it automatically.

The activation token and user are created in the same database transaction.
`UserCreatedNotification` implements `ShouldQueueAfterCommit`, so the queued
email is published only after the outermost transaction commits, including when
user creation is called inside another transaction. Before that commit, an
external worker cannot reserve the onboarding job. A rollback removes the user
and token and prevents the queue job from being created, including when the
application uses an external queue such as Redis.

The queued payload contains the activation token required to build the link. It
does not contain a temporary password or the unknown generated credential. The
token remains subject to the password broker's configured expiry and one-time
reset behavior even if email delivery is delayed.

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

## Current User

```http
GET /api/v1/auth/me
```

Requires:

```http
Authorization: Bearer {access_token}
```

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

The file must be a JPG, JPEG, PNG, or WebP image no larger than 5 MB. Uploaded
files are stored under `avatars/{user-uuid}/` on the public disk. User create
and update endpoints do not accept an `avatar` storage path; supplying one
returns a `422` validation response. Clients must use this authenticated upload
endpoint to change an avatar.

When an avatar is replaced, the API updates the database before deleting the
previous file. The previous file is removed only after the transaction commits
and only when it belongs to the authenticated user's avatar directory. A failed
upload, database write, or surrounding transaction preserves the previous file
and removes the new upload. Files outside the user's directory are never
deleted by this flow.

---

# Users

| Method | Endpoint              | Authentication | Permission      |
| ------ | --------------------- | -------------- | --------------- |
| GET    | `/users`              | Yes            | `users.view`    |
| POST   | `/users`              | Yes            | `users.create`  |
| GET    | `/users/{id}`         | Yes            | `users.view`    |
| PATCH  | `/users/{id}`         | Yes            | `users.update`  |
| DELETE | `/users/{id}`         | Yes            | `users.delete`  |
| PATCH  | `/users/{id}/restore` | Yes            | `users.restore` |
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

Listing parameters are validated before they are parsed. Pagination values must
be integers; search, sort, direction, trashed, and filter values must be scalar
strings within their length limits. Array inputs such as `search[]=john` return
the standard `422` validation response.

Each endpoint validates sort and filter names against its supported fields.
Unsupported names and invalid direction or trashed values return `422` rather
than being silently ignored. Results are ordered by `id` when no sort is
provided, and valid sorted queries use `id` as a deterministic tie-breaker.

Pagination links retain the original search, filter, sort, direction, and page
size parameters.

## Super-Admin Role Invariant

Only one user may hold the `super-admin` role, including users that have been
soft-deleted. Creating a user with that role or promoting an existing user is
serialized on the protected role record before the assignment is checked. If
another super-admin already exists, the API returns `409 Conflict` with the
`users.super_admin_already_assigned` message.

This locking applies to concurrent API requests and to the
`app:provision-super-admin` command. A failed attempt does not create a partial
user or role assignment.

## User Permission Responses

Every serialized user contains a `permissions` array with the user's effective
permissions. This array combines permissions assigned directly to the user with
permissions inherited through roles. Duplicate names are removed and the
remaining names are sorted alphabetically, so the same user receives the same
permission list from authentication, profile, user-management, dashboard, and
audit responses.

```json
{
    "role": "editor",
    "permissions": [
        "profile.update",
        "users.update",
        "users.view"
    ]
}
```

User collection, dashboard, and audit queries eager-load direct permissions and
`roles.permissions`. Individual user resources load either relationship when it
is missing.

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

## Get All Permissions

```http
GET /api/v1/roles/permissions
```

Returns the permissions available to the application.

## Get Role Permissions

```http
GET /api/v1/roles/{id}/permissions
```

Returns permissions currently assigned to the role.

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

# Audit Logs

Audit logging records important model lifecycle activity for auditable models.

The current auditable models include:

- Users
- Roles
- Permissions

Audit records capture the actor, event, auditable model, changed values, request URL, IP address, user agent, and timestamps.

## Audit Log Endpoint

| Method | Endpoint       | Authentication | Permission       | Description                |
| ------ | -------------- | -------------- | ---------------- | -------------------------- |
| GET    | `/audit-logs`  | Yes            | `audit-logs.view` | List audit logs            |

```http
GET /api/v1/audit-logs
```

The endpoint is protected by:

```text
auth:sanctum
permission:audit-logs.view
```

Supported query parameters:

```text
page
per_page
search
sort
direction
event
user_id
```

Example:

```text
GET /api/v1/audit-logs?page=1&per_page=20&event=updated&user_id={user_id}&sort=created_at&direction=desc
```

### Supported Audit Events

```text
created
updated
deleted
restored
force_deleted
permissions_synced
roles_synced
```

`roles_synced` records role assignments made while creating or updating a
user. `permissions_synced` records changes to a role's permission set. Their
`old_values` and `new_values` contain sorted relationship names:

```json
{
    "event": "roles_synced",
    "old_values": {
        "roles": ["user"]
    },
    "new_values": {
        "roles": ["admin"]
    }
}
```

### Audit Log Fields

Audit log responses contain fields including:

```text
id
event
user
auditable_type
auditable_id
old_values
new_values
url
ip_address
user_agent
created_at
updated_at
```

The `user` relationship identifies the authenticated actor when an audit event was generated in an authenticated request.

### Audit Snapshot Datetimes

Datetime values inside `old_values` and `new_values` use ISO 8601 with a UTC
offset, matching top-level resource timestamps:

```json
{
    "old_values": {
        "email_verified_at": "2026-09-05T10:20:30+00:00"
    },
    "new_values": {
        "email_verified_at": "2026-09-06T14:30:00+00:00"
    }
}
```

New snapshots normalize timestamps and attributes declared with Eloquent date
or datetime casts before storage. Audit responses also normalize historical
`*_at` fields recursively, including nested values. Null values remain null. An
invalid legacy datetime string is returned unchanged so one malformed field
does not prevent the audit record from being read.

### Audited Model Changes

#### User

User creation, updates, deletion, restoration, force deletion, and role
assignments are recorded. A role-only update produces a `roles_synced` audit
even when no user attribute changes.

Sensitive authentication fields such as passwords and remember tokens are excluded from audit values.

#### Role

Role creation, updates, and deletion are recorded.

Role permission synchronization produces a `permissions_synced` audit with the
previous and resulting permission names.

#### Permission

Permission creation, updates, and deletion are recorded.

Audit logging uses the model's UUID as `auditable_id`.

Audit writes participate in the same database transaction as the audited
change. Dashboard cache invalidation is registered after the audit is written
and executes only after the outermost transaction commits. If the transaction
rolls back, the audit and relationship change are rolled back and the existing
dashboard cache remains valid.

### Pagination

Audit logs use the standard API pagination structure described below.

---

# Dashboard

The dashboard API provides aggregated statistics for the application's administrative interface.

## Dashboard Endpoint

| Method | Endpoint     | Authentication | Permission        | Description                  |
| ------ | ------------ | -------------- | ----------------- | ---------------------------- |
| GET    | `/dashboard` | Yes            | `dashboard.view`  | Get dashboard statistics     |

```http
GET /api/v1/dashboard
```

The endpoint is protected by:

```text
auth:sanctum
permission:dashboard.view
```

A user must have both a valid Sanctum token and the `dashboard.view` permission.

`dashboard.view` grants access to aggregate dashboard statistics. Detailed user
and audit data use their existing module permissions:

| Dashboard data | Additional permission | Behavior without permission |
| -------------- | --------------------- | --------------------------- |
| `users.recent` | `users.view` | Returns an empty array |
| `users.recently_active` | `users.view` | Returns an empty array |
| `audit.recent` | `audit-logs.view` | Returns an empty array |

Summary totals, `users.by_status`, and `audit.by_event` remain available with
`dashboard.view`. The response structure does not change when detailed data is
hidden.

## Dashboard Response

The dashboard response is organized into:

```text
summary
users
audit
```

### Summary

The summary contains:

```text
summary.users.total
summary.users.active
summary.users.inactive
summary.users.suspended
summary.roles.total
summary.permissions.total
summary.audit_logs.total
```

The permission count is limited to permissions using the `sanctum` guard.

### User Statistics

The `users` section contains:

```text
users.by_status
users.recent
users.recently_active
```

`by_status` groups users by their status.

`recent` contains up to five recently created users when the viewer has
`users.view`; otherwise, it is an empty array.

`recently_active` contains up to five users ordered by their latest login
activity when the viewer has `users.view`; otherwise, it is an empty array.

Recent dashboard users are summary records containing identity, avatar, status,
verification, login, and lifecycle timestamps. They do not include role or
permission collections.

### Audit Statistics

The `audit` section contains:

```text
audit.by_event
audit.recent
```

`by_event` contains audit-log counts grouped by event.

`recent` contains up to six recent audit logs and includes the associated audit
actor when available. It requires `audit-logs.view`; otherwise, it is an empty
array.

Audit actors are limited to their ID, name, email, and avatar. Role and
permission collections are excluded from dashboard audit entries.

Dashboard user-status and audit-event totals are calculated from the same
grouped datasets used by `users.by_status` and `audit.by_event`, so the summary
does not issue duplicate count queries.

Dashboard responses are cached separately for each combination of user-detail
and audit-detail access. A response cached for one permission scope cannot be
served to another scope. Changes recorded by auditable models, user role
assignments, and role permission synchronization invalidate every scoped cache
variant after their database transaction commits.

### Example Response Shape

```json
{
    "success": true,
    "status": 200,
    "message": "Dashboard retrieved successfully.",
    "data": {
        "summary": {
            "users": {
                "total": 100,
                "active": 90,
                "inactive": 5,
                "suspended": 5
            },
            "roles": {
                "total": 5
            },
            "permissions": {
                "total": 25
            },
            "audit_logs": {
                "total": 250
            }
        },
        "users": {
            "by_status": {
                "active": 90,
                "inactive": 5,
                "suspended": 5
            },
            "recent": [],
            "recently_active": []
        },
        "audit": {
            "by_event": {
                "created": 100,
                "updated": 120,
                "deleted": 20,
                "restored": 5,
                "force_deleted": 5
            },
            "recent": []
        }
    },
    "errors": null,
    "meta": {}
}
```

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

## Audit Logs

```text
audit-logs.view
```

## Dashboard

```text
dashboard.view
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

The `links` URLs retain the listing parameters used for the current request, so
following `next` or `last` preserves the active filters and sort order.

---

# HTTP Status Codes

Requests under `/api/*` always receive the standard JSON error envelope,
regardless of the request's `Accept` header. This also applies when the header
is absent.

Framework response headers are preserved alongside the envelope. For example,
method-not-allowed responses include `Allow`, and throttled responses include
`Retry-After` when supplied by the rate limiter.

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

The repository also contains the OpenAPI/Swagger implementation used to document the API.

The documentation should be kept synchronized with the actual API implementation.

---

# Related Documentation

- `README.md` — Project overview and development setup
- `docs/ROADMAP.md` — Project roadmap and release plan
- `docs/ARCHITECTURE.md` — Backend architecture
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
- Audit logging is implemented through the reusable `Auditable` model trait.
- Dashboard data is aggregated by the `DashboardService`.
- Swagger/OpenAPI remains the primary API documentation mechanism.
