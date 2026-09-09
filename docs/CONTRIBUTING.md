# Contributing

Thank you for contributing to Laravel API Base.

---

## Branch Strategy

```
main

↓

develop

↓

feature/*
```

Never commit directly to `main`.

---

## Coding Standards

- PSR-12
- Strict Types
- Constructor Property Promotion
- Laravel Pint
- Feature Tests Required

---

## Before Every Commit

Run the release checks relevant to the change:

```bash
composer test
composer lint
composer analyse
composer docs:check
git diff --check
```

When changing Docker or production configuration, also validate the Compose
files and build the affected production image target as documented in
`docs/TESTING.md`.

---

## Commit Message Convention

```
feat:
fix:
docs:
refactor:
test:
chore:
ci:
```

Examples

```
feat: implement user management

fix: resolve authentication issue

docs: update README

test: add authentication feature tests
```

---

## Pull Requests

Every PR should

- Pass all tests
- Follow coding standards
- Include documentation updates
- Keep commits clean

---

## Thank You

Your contributions help improve Laravel API Base.
