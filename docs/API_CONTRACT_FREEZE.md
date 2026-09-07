# v1.0.0 API Contract Freeze

This document freezes the public backend contract consumed by
`laravel-api-base-ui` for the v1.0.0 release. The backend API documentation and
generated OpenAPI output remain authoritative; this file records the rules that
must remain compatible during the release.

## Version and transport

- Base path: `/api/v1`
- Content type: `application/json` for JSON requests and responses
- Authentication: Laravel Sanctum bearer tokens in the `Authorization` header
- Local backend URL: `http://localhost:8000`
- Local frontend URL: `http://localhost:5173`

Every response uses the standard envelope:

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

Errors preserve the same keys, with `success: false`, `data: null`, and field
messages in `errors` when applicable. Clients must branch on the HTTP status
and envelope fields rather than parse message text.

## Frozen endpoint surface

The v1.0.0 endpoint surface is:

| Area | Endpoints |
| --- | --- |
| Health | `GET /health` |
| Auth | `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`, `POST /auth/forgot-password`, `POST /auth/reset-password`, `POST /auth/change-password`, `GET /auth/password-policy` |
| Dashboard | `GET /dashboard` |
| Audit | `GET /audit-logs`, `GET /audit-logs/{id}` |
| Profile | `PUT /profile`, `POST /profile/avatar` |
| Users | `GET/POST /users`, `GET/PUT/DELETE /users/{id}`, `POST /users/{id}/restore`, `DELETE /users/{id}/force` |
| Roles | `GET/POST /roles`, `GET/PUT/DELETE /roles/{id}`, `GET /roles/permissions`, `GET/PUT /roles/{id}/permissions` |

New endpoints may be added under `/api/v1`; existing paths, HTTP methods, and
required fields are frozen for the release.

## Compatibility rules

The following are breaking changes and require a new API version or an explicit
release decision:

- Renaming or removing an endpoint, request field, response field, permission,
  or pagination key.
- Changing a field's type, nullability, meaning, or date format.
- Changing authentication from bearer tokens or changing token revocation
  behavior.
- Changing the standard response or error envelope.
- Changing activation semantics: the activation token is submitted to
  `POST /auth/reset-password`, and successful activation sets
  `email_verified_at` immediately.

Additive response fields and new optional request fields are allowed when the
frontend continues to work with the previous shape. Every additive change must
update both repositories' API documentation and integration types.

## Release checks

Before merging a contract change, run the backend tests, formatting, static
analysis, and OpenAPI check, then run the frontend lint, tests, and production
build. Update `docs/API.md` in the backend and `docs/API.md` in the frontend in
the same change.
