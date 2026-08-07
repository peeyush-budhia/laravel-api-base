# Branching Strategy

## Overview

Laravel API Base follows a lightweight Git Flow branching model that balances stability with rapid feature development.

The primary objectives are:

- Stable production code
- Isolated feature development
- Clean Git history
- Easy collaboration
- Predictable releases

---

# Branch Structure

The repository maintains the following permanent branches.

```
main
develop
```

Temporary branches include:

```
feature/*
release/*
hotfix/*
```

---

# Branch Responsibilities

## main

The **main** branch always represents production-ready code.

Rules:

- Always deployable
- Protected branch
- No direct commits
- Merge only from release or hotfix branches
- Tagged for every release

Example:

```
main
```

---

## develop

The **develop** branch contains the latest completed development work.

Rules:

- Base branch for all new features
- May contain unreleased functionality
- Continuously integrated

Example:

```
develop
```

---

## Feature Branches

Every new feature must be developed in its own branch.

Naming convention:

```
feature/<feature-name>
```

Examples:

```
feature/users

feature/roles

feature/permissions

feature/companies

feature/customers

feature/dashboard

feature/reports
```

Never develop directly on:

- main
- develop

---

## Release Branches

When preparing a production release:

```
release/v1.0.0
```

Purpose:

- Final testing
- Documentation updates
- Version bump
- Bug fixes only

No new features should be added.

---

## Hotfix Branches

Urgent production fixes use:

```
hotfix/login-error

hotfix/token-expiry

hotfix/security-patch
```

Hotfixes are created from:

```
main
```

and merged back into:

- main
- develop

---

# Development Workflow

## Step 1

Start from develop.

```
git checkout develop

git pull origin develop
```

---

## Step 2

Create a feature branch.

```
git checkout -b feature/users
```

---

## Step 3

Develop the feature.

Commit frequently using meaningful commit messages.

---

## Step 4

Push feature branch.

```
git push -u origin feature/users
```

---

## Step 5

Open a Pull Request.

```
feature/users

↓

develop
```

---

## Step 6

Review

The feature should be reviewed before merging.

Checklist:

- Code review
- Tests passing
- Documentation updated
- No debugging code
- Coding standards followed

---

## Step 7

Merge

Merge using:

```
--no-ff
```

to preserve branch history.

Example:

```
git checkout develop

git merge --no-ff feature/users
```

---

## Step 8

Delete feature branch.

```
git branch -d feature/users

git push origin --delete feature/users
```

---

# Release Workflow

Create release branch.

```
git checkout develop

git checkout -b release/v1.0.0
```

Perform:

- Testing
- Documentation
- Version updates

Merge into main.

```
git checkout main

git merge release/v1.0.0
```

Tag release.

```
git tag v1.0.0

git push origin main --tags
```

Merge back into develop.

```
git checkout develop

git merge release/v1.0.0
```

Delete release branch.

---

# Hotfix Workflow

Create branch.

```
git checkout main

git checkout -b hotfix/login-error
```

Fix issue.

Commit.

Push.

Merge into:

```
main

develop
```

Delete branch.

---

# Commit Message Convention

This project follows the Conventional Commits specification.

## Features

```
feat: add user management
```

---

## Fixes

```
fix: resolve login validation
```

---

## Documentation

```
docs: update API standards
```

---

## Refactoring

```
refactor: simplify auth service
```

---

## Tests

```
test: add authentication feature tests
```

---

## Chores

```
chore: update dependencies
```

---

## CI/CD

```
ci: configure GitHub Actions
```

---

# Pull Request Checklist

Every Pull Request should satisfy:

- Builds successfully
- Tests pass
- No merge conflicts
- Documentation updated
- Feature complete
- No commented code
- No debug statements
- Follows coding standards

---

# Branch Protection

Recommended GitHub settings.

Protect:

```
main

develop
```

Enable:

- Require Pull Requests
- Require status checks
- Require conversation resolution
- Prevent force push
- Prevent branch deletion

---

# Versioning

Semantic Versioning is used.

```
MAJOR.MINOR.PATCH
```

Examples

```
v1.0.0

v1.1.0

v1.2.0

v2.0.0
```

---

# Repository Lifecycle

```
main
        │
        ▼
develop
        │
        ▼
feature/users
        │
        ▼
Pull Request
        │
        ▼
develop
        │
        ▼
release/v1.0.0
        │
        ▼
main
```

---

# Git Best Practices

Always

- Pull before starting work
- Keep branches focused
- Write descriptive commit messages
- Rebase or merge frequently from develop
- Delete merged feature branches
- Tag releases

Never

- Commit directly to main
- Force push protected branches
- Mix unrelated features
- Commit secrets or `.env`
- Leave unfinished code on shared branches

---

# Current Repository Workflow

The Laravel API Base repository follows this workflow:

```
main
│
├── Production-ready code
│
develop
│
├── Integration branch
│
feature/*
│
├── users
├── roles
├── permissions
├── companies
├── customers
├── reports
└── dashboard
```

Every future project generated from this template should follow the same branching strategy to ensure consistency, traceability, and maintainable Git history.
