# Release Guide

This guide defines the release process for Laravel API Base.

The project follows a controlled branch and release workflow so that `main` always represents a stable version of the API Base template.

---

# Branches

The primary branches are:

```text
main
develop
```

## main

`main` contains the stable release history.
Production-ready versions are tagged from `main`.
Direct pushes to `main` should not be used.

## develop

`develop` contains the integration state for the next release.
Feature branches normally merge into `develop`.

# Feature Development

Create a feature branch from develop:

```bash
git switch develop

git pull --ff-only origin develop

git switch -c feature/my-feature
```

Examples:

```text
feature/api-documentation
feature/audit-logs
feature/dashboard-api
docs/update-testing-guide
fix/authentication
```

# Pull Request Flow

The normal development flow is:

```text
feature/*
    ↓
develop
    ↓
main
    ↓
Git Tag
```

Feature Pull Requests should target:

```text
develop
```

Release Pull Requests should target:

```text
main
```

# Versioning

Laravel API Base follows semantic versioning:

```text
MAJOR.MINOR.PATCH
```

Examples:

```text
1.0.0
0.7.0
0.7.1
```

# Version Types

## MAJOR

A major release may contain breaking changes.
Example:

```text
v1.0.0
v2.0.0
```

Use a major version when API compatibility is intentionally broken.

## MINOR

A minor release adds functionality without intentionally breaking the existing API contract.
Example:

```text
v0.7.0
v0.8.0
v0.9.0
```

For the current 0.x development phase, minor versions represent significant feature milestones..

## PATCH

A patch release contains fixes and small non-breaking improvements.
Example:

```text
v0.7.1
v0.7.2
```

# Release Preparation

Before preparing a release, update the local repository.

```bash
git switch develop

git pull --ff-only origin develop
```

Confirm the working tree:

```bash
git status
```

The working tree should be clean before starting the release process.

# Run Quality Checks

Run code style validation:

```bash
vendor/bin/pint --test
```

Run static analysis:

```bash
vendor/bin/phpstan analyse
```

Run API documentation analysis:

```bash
php artisan scramble:analyze
```

Run the complete test suite:

```bash
php artisan test
```

Check for whitespace errors:

```bash
git diff --check
```

All checks should pass.

# Review the Changelog

Update:

```text
CHANGELOG.md
```

The changelog should document notable changes included in the release.
Use the Keep a Changelog style already adopted by the project.
Typical categories:

```md
### Added

### Changed

### Fixed

### Removed

### Security
```

Do not document every internal commit. Record meaningful changes that are relevant to users of the API Base template.

# Review the Roadmap

Update:

```text
docs/ROADMAP.md
```

The roadmap should reflect:

- Completed phases
- Current version
- Next version
- Planned work
- Released milestones

Do not mark a feature as completed until it has actually been implemented and tested.

# Release Pull Request

After the release documentation is prepared, push the changes:

```bash
git push origin develop
```

Create a Pull Request:

```text
develop → main
```

The release Pull Request should be reviewed before merging.

# Merge the Release

After approval, merge:

```text
develop → main
```

Update the local repository:

```bash
git switch main

git pull --ff-only origin main
```

Confirm the latest commit:

```bash
git log --oneline --decorate -5
```

# Verify the Release

Run the final checks on main:

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan scramble:analyze
php artisan test
git diff --check
```

The release should only be tagged after these checks pass.

# Create the Git Tag

Create an annotated tag.
Example:

```bash
git tag -a v0.7.0 -m "Release v0.7.0"
```

Verify:

```bash
git tag --list --sort=-version:refname | head
```

Inspect the tag:

```bash
git show v0.7.0
```

# Push the Tag

Push the tag:

```bash
git push origin v0.7.0
```

Verify the remote tag:

```bash
git ls-remote --tags origin
```

# GitHub Release

After pushing the tag, create a GitHub Release for:

```text
v0.7.0
```

The release should include a concise summary of the important changes.
The changelog can be used as the source for the release notes.

# Release Verification

After the release is published, verify:

```text
Git tag exists
       ↓
GitHub Release exists
       ↓
main contains release commit
       ↓
CHANGELOG.md contains release
       ↓
ROADMAP.md reflects release
       ↓
CI is passing
```

# Example v0.7.0 Release

The planned focus for:

```text
v0.7.0
```

is:

```text
Postman
API Documentation
Developer Experience
```

Potential release contents include:

- OpenAPI documentation
- Scramble integration
- API documentation tests
- Postman collection
- Postman environment
- Development guide
- Testing guide
- Release guide
- Updated README
- Updated CHANGELOG

Only include functionality that has actually been implemented and tested.

# Hotfix Releases

For a critical issue in main, create a dedicated fix branch from main.
Example:

```bash
git switch main

git pull --ff-only origin main

git switch -c fix/critical-issue
```

After fixing and testing:

```text
fix/*
   ↓
main
```

If the fix should also exist in develop, ensure the change is incorporated there as well.

# Release Checklist

Before tagging a release:

- Working tree is clean
- Version is correct
- CHANGELOG.md is updated
- ROADMAP.md is updated
- README.md is updated where necessary
- Pint passes
- PHPStan passes
- Scramble analysis passes
- All tests pass
- git diff --check passes
- Release PR merged into main
- Release commit is present on main
- Git tag created
- Git tag pushed
- GitHub Release created

# Release Principle

A release is not only a Git tag.
A release represents a verified state of the repository where:

- Code is tested.
- Documentation is updated.
- API documentation is generated successfully.
- Quality checks pass.
- The changelog describes the release.
- The roadmap reflects the project state.
- The release is traceable through Git history and tags.
