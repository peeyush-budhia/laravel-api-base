# API Error Handling Review

The backend always renders /api/* failures as the standard JSON envelope,
regardless of the request's Accept header. Validation, authentication,
authorization, not-found, method, throttling, and conflict failures use stable
status codes and public messages. Headers such as Allow and Retry-After are
preserved.

In production, 5xx responses never include exception messages, SQL, stack
traces, credentials, file paths, or other diagnostic details. Diagnostics are
written to the configured server log and should be protected and rotated.
Development may include exception details for local debugging; never enable
APP_DEBUG on a public deployment.

The frontend consumes the envelope through its Axios interceptor and displays
the normalized status, message, and errors fields. UI components must not
inspect Axios internals or display raw network errors.
