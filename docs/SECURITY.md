# Security Policy

Thank you for helping keep Laravel API Base secure.

We take security vulnerabilities seriously and appreciate responsible disclosure.

---

# Supported Versions

The following versions currently receive security updates.

| Version | Supported |
| ------- | --------- |
| v1.x    | ✅        |
| v0.x    | ✅        |

---

# Reporting a Vulnerability

**Please do not create a public GitHub issue for security vulnerabilities.**

Instead, report vulnerabilities privately.

If GitHub Private Vulnerability Reporting is enabled, please use:

> **Security → Report a vulnerability**

Otherwise, contact the repository maintainer directly.

Please include as much information as possible:

- Description of the vulnerability
- Steps to reproduce
- Proof of concept (if available)
- Potential impact
- Suggested mitigation (optional)

---

# Response Process

Our goal is to respond according to the following timeline.

| Stage                   | Target                        |
| ----------------------- | ----------------------------- |
| Initial acknowledgement | Within 48 hours               |
| Investigation           | Within 7 days                 |
| Security fix            | As soon as possible           |
| Public disclosure       | After a fix has been released |

---

# Scope

Examples of security issues include:

- Authentication bypass
- Authorization issues
- SQL Injection
- Cross-Site Scripting (XSS)
- Remote Code Execution (RCE)
- Sensitive data exposure
- Insecure file uploads
- API authentication weaknesses
- Broken access control

---

# Out of Scope

The following are generally not considered security vulnerabilities:

- Typographical errors
- Documentation improvements
- Feature requests
- Low-risk UI issues
- Denial of service caused by unrealistic traffic volumes

---

# Disclosure Policy

Please allow reasonable time for a fix before publicly disclosing any vulnerability.

Responsible disclosure helps protect all users of this project.

Thank you for helping improve the security of Laravel API Base.

## Production security review

- Keep APP_DEBUG=false, use HTTPS, and restrict CORS_ALLOWED_ORIGINS to exact
  trusted frontend origins.
- Store secrets only in the deployment environment and rotate database, mail,
  queue, and application credentials through the hosting platform.
- Keep the web-server document root at public/; never expose .env, storage, or
  source files.
- Run queue workers and the scheduler under a dedicated least-privilege user.
- Review authentication throttles, Sanctum token expiry, password policy,
  authorization permissions, upload validation, and audit retention before
  each release.
- Protect and rotate logs; production API responses intentionally omit
  exception details.
- Set `SCRAMBLE_DOCS_ENABLED=false` and `SCRAMBLE_DEV_TOOLS=false` so production
  does not register the interactive OpenAPI or JSON specification routes.
- The production PHP-FPM image runs as the non-root `laravel` user and excludes
  Composer, development dependencies, compilers, and build headers. Keep MySQL
  and Redis on the private Compose network and publish only the Nginx port.
- Supply `.env.production` at runtime. Never copy it into an image, commit it,
  or expose the named application-storage volume through another service.
