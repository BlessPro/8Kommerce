# 8Kommerce CI and release pipeline

The pipeline is hosting-independent. GitHub validates the application and publishes a verified Docker image; it does not deploy to Render or a VPS.

## Workflows

- `CI` runs for pull requests to `main`, pushes to `main`, and semantic version tags.
- `Release container` runs only after a successful `CI` push and publishes the exact tested commit to GitHub Container Registry (GHCR).
- Published images include build-provenance and software-bill-of-materials attestations.

The required branch-protection checks should be:

- `Application checks`
- `Production container`

Do not make `Release container` a merge requirement. It runs after code has reached `main`.

## Image names

Successful `main` builds publish:

```text
ghcr.io/blesspro/linkpay:main
ghcr.io/blesspro/linkpay:sha-<short-commit>
```

A version tag such as `v1.0.0` additionally publishes:

```text
ghcr.io/blesspro/linkpay:v1.0.0
```

Use a commit tag or image digest for production. The `main` tag is convenient but mutable.

## Creating a release

Create the tag from a commit that has passed CI:

```bash
git switch main
git pull --ff-only
git tag -a v1.0.0 -m "8Kommerce v1.0.0"
git push origin v1.0.0
```

Use semantic versions: increment the patch for fixes, minor for backward-compatible features, and major for breaking changes.

## Package visibility

New GHCR packages are private by default. Change visibility from the package settings on GitHub only if public anonymous pulls are desired. A private VPS deployment should use a read-only GitHub token with `read:packages`; never copy the workflow token or a personal password to a server.

## Dependency updates

Dependabot checks Composer and npm weekly, and GitHub Actions and Docker images monthly. Treat its pull requests like any other change: wait for CI and review breaking-version release notes before merging.

## Rollback model

Deploy immutable tags or digests on the future VPS. A rollback selects the last known-good `sha-...` tag and restarts the containers. Application rollback does not reverse database migrations, so schema changes must remain backward-compatible.

