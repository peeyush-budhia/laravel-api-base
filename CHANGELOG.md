## [0.7.0] - 2026-08-22

### Added

#### API Documentation

- Added `dedoc/scramble` for automatic OpenAPI documentation generation.
- Added Scramble configuration at `config/scramble.php`.
- Added OpenAPI documentation endpoint:
    - `GET /docs/api`
- Added OpenAPI JSON endpoint:
    - `GET /docs/api.json`
- Added automatic API documentation generation from Laravel routes, controllers, requests, resources, and validation rules.
- Added OpenAPI API version information.
- Added API server configuration for `/api/v1`.
- Added Bearer authentication security scheme documentation.
- Added API documentation analysis using `scramble:analyze`.
- Added OpenAPI export support using `scramble:export`.
- Added OpenAPI cache management using Scramble cache commands.

#### API Documentation Tests

- Added feature tests for API documentation availability.
- Added tests for OpenAPI version information.
- Added tests for configured API server URL.
- Added tests for expected API paths.
- Added tests for Bearer authentication security scheme.
- Added API documentation validation to the automated test suite.

#### Developer Documentation

- Added development setup documentation.
- Added testing guide.
- Added release guide.
- Expanded API standards documentation.
- Added API documentation guidance.
- Documented the developer workflow for the Laravel API Base.
- Documented testing and quality assurance procedures.
- Documented release and versioning procedures.

#### Developer Experience

- Improved API documentation through controller and route documentation comments.
- Improved API documentation metadata and descriptions.
- Added API documentation configuration to the project environment.
- Added generated OpenAPI specification to the local development workflow.
- Added API documentation validation to CI.

### Changed

- Updated `README.md` with API documentation and developer workflow information.
- Updated `.env.example` with API documentation configuration.
- Updated API standards documentation to support OpenAPI documentation.
- Updated API controllers with documentation metadata and descriptions.
- Updated the health endpoint with API documentation metadata.
- Updated CI workflow to validate API documentation generation.
- Updated Git ignore rules to exclude the generated `api.json` OpenAPI specification.
- Updated Composer dependencies to include Scramble.
- Updated Composer lock file with the new documentation dependency.
- Updated project documentation structure for the v0.7.0 developer experience improvements.

### Fixed

- Ensured OpenAPI documentation can be generated successfully from the current API.
- Ensured API documentation generation completes without Scramble analysis errors.
- Ensured API documentation tests run successfully in the testing environment.

### Validation

- All automated tests passing.
- `181` tests passing.
- `897` assertions passing.
- Scramble analysis completed successfully.
- OpenAPI specification generated successfully.

---

## [0.6.0] - 2026-08-21

### Added

- Backend foundation stabilization.
- Query infrastructure improvements.
- API improvements and cleanup.
- Improved application foundation for future development.

### Changed

- Refined project architecture.
- Improved backend code organization.
- Updated project documentation.
- Improved application foundation and development workflow.

---

## [0.5.0] - 2026-08-20

### Added

- Query infrastructure.
- API improvements.
- Improved filtering and query handling.
- Improved pagination and sorting capabilities.

### Changed

- Improved API endpoint consistency.
- Improved query handling across API resources.
- Improved service and controller integration.

---

## [0.4.0] - 2026-08-19

### Added

- User lifecycle management.
- User authorization.
- User profile management.
- Avatar management.
- User restore functionality.
- User force-delete functionality.
- Password management.
- Password reset notifications.
- Frontend URL integration.
- Notification testing.

### Changed

- Improved authentication and user management workflows.
- Improved authorization handling.
- Improved user lifecycle management.

---

## [0.3.0] - 2026-08-18

### Added

- Roles and Permissions module.
- Role management.
- Permission management.
- Role assignment.
- Permission assignment.
- Role authorization.
- Permission authorization.
- Role middleware.
- Permission middleware.
- Role search.
- Role sorting.
- Role pagination.
- Role protection.
- Super Admin protection.
- Permission synchronization.
- Feature tests for roles and permissions.

### Changed

- Integrated `spatie/laravel-permission`.
- Improved authorization architecture.

---

## [0.2.0] - 2026-08-17

### Added

- User Management module.
- User CRUD operations.
- User search.
- User filtering.
- User pagination.
- API Resources.
- Form Requests.
- User feature tests.
- Service layer integration.
- Authorization support.

### Changed

- Improved user model and API architecture.
- Improved validation and API response consistency.

---

## [0.1.0] - 2026-08-16

### Added

- Laravel 13 API-only project foundation.
- API versioning with `/api/v1`.
- Standard API response helper.
- Centralized exception handling.
- Laravel Sanctum authentication.
- UUID-based identifiers.
- Base Model.
- Service Layer architecture.
- Authentication module.
- Login endpoint.
- Logout endpoint.
- Current-user endpoint.
- Authentication feature tests.
- Repository documentation.
- Git branching strategy.
- GitHub template preparation.

### Changed

- Established the initial project architecture.
- Established API coding standards.
- Established testing standards.
- Established enterprise-oriented project structure.

---

## Version History

| Version | Status      | Focus                                         |
| ------- | ----------- | --------------------------------------------- |
| v0.1.0  | ✅ Complete | API Foundation & Authentication               |
| v0.2.0  | ✅ Complete | User Management                               |
| v0.3.0  | ✅ Complete | Roles & Permissions                           |
| v0.4.0  | ✅ Complete | User Lifecycle, Authorization & Notifications |
| v0.5.0  | ✅ Complete | Query Infrastructure & API Improvements       |
| v0.6.0  | ✅ Released | Backend Cleanup & Foundation Stabilization    |
| v0.7.0  | 🚧 Next     | API Documentation & Developer Experience      |
| v0.8.0  | 📋 Planned  | Audit Logs & Dashboard APIs                   |
| v0.9.0  | 📋 Planned  | Docker, Performance & Infrastructure          |
| v1.0.0  | 🎯 Target   | Production Ready API Template                 |

---

[Unreleased]: https://github.com/peeyush-budhia/laravel-api-base/compare/v0.7.0...HEAD
[0.7.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.7.0
[0.6.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.6.0
[0.5.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.5.0
[0.4.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.4.0
[0.3.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.3.0
[0.2.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.2.0
[0.1.0]: https://github.com/peeyush-budhia/laravel-api-base/releases/tag/v0.1.0
