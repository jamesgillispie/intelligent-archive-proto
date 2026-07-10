# Fixing security vulnerabilities

This project uses `composer audit --locked` and `npm audit` to detect security
vulnerabilities in dependencies. All commands must run inside the container
using `docker compose run --rm --no-deps app <command>`.

## General approach

1. Run audits first to identify the scope of the problem
2. Work on a separate git worktree branch for the fixes
3. Keep composer and npm changes in separate commits
4. Verify each audit passes clean before committing
5. Never commit a fix without re-running the audit to confirm it
   does not introduce new vulnerabilities

## Composer dependencies

For indirect (transitive) dependencies, use `composer update` with caret
constraints pinned to the minimum fixed version to limit the scope of
changes:

```console
$ docker compose run --rm --no-deps app composer update "vendor/package:^X.Y.Z"
```

Before committing, verify the lock file impact is minimal by checking:

```console
$ git diff --stat composer.lock
```

If the lock file shows a large number of changes beyond the targeted
packages, flag the issue for review before committing. Only `composer.lock`
should change when fixing indirect dependencies; do not modify
`composer.json` constraints unless the vulnerable package is a direct
dependency.

## npm dependencies

For direct dependencies, bump the version constraint in `package.json` to
the minimum tilde range that includes the fix, then regenerate the lock
file:

```console
$ docker compose run --rm --no-deps app npm install
```

If vulnerabilities remain after `npm install`, run:

```console
$ docker compose run --rm --no-deps app npm audit fix
```

The `package-lock.json` will show broad changes when direct dependency
versions are bumped due to transitive dependency resolution. This is
expected.

Do not use `npm audit fix --force` without explicit approval, as it may
upgrade packages beyond their stated semver range.

## Verification

After each set of changes, re-run the respective audit command and confirm
zero vulnerabilities before committing:

```console
$ docker compose run --rm --no-deps app composer audit --locked
$ docker compose run --rm --no-deps app npm audit
```

Once audits pass, validate that the application still works correctly by
running the following commands:

```console
$ docker compose run --rm app composer install
$ docker compose run --rm app php artisan list
$ docker compose run --rm --no-deps app npm run build
```

`composer install` verifies the lock file is consistent and all packages
install without errors. `php artisan list` confirms the Laravel framework
boots and discovers all registered commands. `npm run build` ensures the
frontend assets compile successfully with the updated dependencies.

## Commit structure

Organize fixes into logical commits, each verified independently:

1. Infrastructure changes (e.g. container image updates) go in their own
   commit
2. Composer lock file fixes in a separate commit
3. npm package.json and lock file fixes in a separate commit

Commit messages should explain the security advisories being addressed
(CVE identifiers, nature of the vulnerability) rather than listing the
specific version changes visible in the diff.
