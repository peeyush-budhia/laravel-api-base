## [0.6.0] - 2026-08-21

### Added

- Role and permission management
- User management
- User profile and avatar management
- User soft delete, restore, and permanent delete
- Role permission synchronization
- Password change and password reset flows
- Remember-me authentication support
- Super Admin protection
- API authorization and permission tests
- Query search, filtering, sorting, and pagination support

### Changed

- Refactored role and permission handling
- Improved user management authorization
- Standardized API query handling

### Removed

- Unused Laravel frontend/Vite assets from the API-only backend
- Unused API curl testing scripts
- Unused `ChangeUserStatusRequest`
- Laravel example test
- Unused favicon and frontend configuration

### Testing

- 176 tests passing
- 868 assertions passing
