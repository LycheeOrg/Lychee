# Quality Gate – Usage & Troubleshooting

_Last updated: September 27, 2026_

The quality gate for Lychee enforces code style, static analysis, and test coverage standards across both backend (PHP/Laravel) and frontend (Vue3/TypeScript) code. Run the appropriate checks locally before committing changes, and rely on the CI workflow ([.github/workflows/CICD.yml](../../../.github/workflows/CICD.yml)) for enforcement on every push and pull request.

This guide is aligned with the coding conventions defined in [docs/specs/3-reference/coding-conventions.md](../3-reference/coding-conventions.md) and the quality requirements in [AGENTS.md](../../../AGENTS.md).

## Quick Reference

### Full Quality Gate (Both PHP and Frontend Modified)

```bash
vendor/bin/php-cs-fixer fix    # Apply PHP code style fixes
npm run format                 # Apply frontend code formatting
npm run check                  # TypeScript type-check (vue-tsc)
php artisan test --filter=<ClassName>   # Tests touched by/related to the change
make phpstan                   # Run static analysis
```

### PHP-Only Changes

```bash
vendor/bin/php-cs-fixer fix    # Apply PHP code style fixes
php artisan test --filter=<ClassName>   # Tests touched by/related to the change
make phpstan                   # Run static analysis (level from phpstan.neon)
```

### Frontend-Only Changes

```bash
npm run format                 # Apply frontend code formatting (Prettier)
npm run check                  # TypeScript type-check (vue-tsc)
```

## Commands in Detail

### PHP Backend Quality Checks

#### 1. Code Style Formatting: `vendor/bin/php-cs-fixer fix`

Applies PSR-4 coding standards and Lychee-specific formatting rules using PHP CS Fixer.

**When to run:**
- Before every commit that touches `.php` files
- After making any PHP code changes
- To fix automated formatting violations

**Configuration:** `.php-cs-fixer.php` at repository root

**Common fixes:**
- Line length adjustments
- Import statement organization
- Spacing and indentation
- Brace placement

#### 2. Tests: `php artisan test --filter=<ClassName>`

Run every test class touched by or related to the change. Never run `php artisan test` unfiltered or a whole `--testsuite=` locally; CI runs the full suite. Never run two test commands concurrently: they share the SQLite test database.

**Test structure:**
- `tests/Unit/` - Unit tests (extend `AbstractTestCase`)
- `tests/Feature_v2/` - Feature/integration tests (extend `Tests\Feature_v2\Base\BaseApiWithDataTest`)
- `tests/Feature_v3/` - v3 API feature tests (extend `Tests\Feature_v3\Base\BaseApiWithDataTest`)

**Coverage expectations:**
- All new code must include tests
- Critical paths require comprehensive test coverage

**Example:**
```bash
php artisan test --filter=AlbumTest
```

#### 3. Static Analysis: `make phpstan`

Runs PHPStan at the level configured in `phpstan.neon` to catch type errors, undefined variables, and other static analysis violations.

**Baseline:** `phpstan-baseline.neon` tracks accepted violations; new code must not add to it.

**When to run:**
- After implementing new features
- After refactoring existing code
- Before committing PHP changes

**Common issues:**
- Type mismatches
- Undefined properties/methods
- Invalid PHPDoc annotations
- Missing return types

**Troubleshooting:**
```bash
vendor/bin/phpstan analyse --memory-limit=2G    # If you need more memory
vendor/bin/phpstan analyse --debug              # For detailed error messages
```

### Frontend Quality Checks

#### 1. Code Formatting: `npm run format`

Applies Prettier formatting to Vue, TypeScript, JavaScript, and CSS files.

**When to run:**
- Before every commit that touches frontend files
- After making Vue/TypeScript/CSS changes

**Configuration:** `.prettierrc` and `eslint.config.ts`

#### 2. Type-check: `npm run check`

Runs `vue-tsc --noEmit` to check TypeScript type correctness across the frontend. ESLint is a separate script (`npm run lint`).

## Standards & Conventions

Coding, Vue (v7/v8), and testing standards live only in [coding-conventions.md](../3-reference/coding-conventions.md). Do not restate them here.

## CI Integration

GitHub Actions runs the full quality gate on every push and pull request via `.github/workflows/CICD.yml`.

**CI checks include:**
- PHP CS Fixer (must pass with no changes)
- PHPStan at the configured level (must pass with no new violations)
- Full PHPUnit test suite (all tests must pass)
- Frontend formatting and linting
- Frontend type-check

**Before pushing:**
1. Run the appropriate quality checks locally
2. Fix all violations
3. Commit only when checks are green
4. Push to trigger CI validation

## Troubleshooting

### PHP CS Fixer Issues

**Problem:** "Files were modified by cs-fixer"
- **Solution:** Run `vendor/bin/php-cs-fixer fix` locally and commit the changes

**Problem:** Formatting conflicts with IDE
- **Solution:** Configure your IDE to use the project's `.php-cs-fixer.php` configuration

### PHPStan Issues

**Problem:** "Parameter type mismatch"
- **Solution:** Add proper type hints to method parameters and return types

**Problem:** "Access to undefined property"
- **Solution:** Add PHPDoc annotations or use proper accessor methods

**Problem:** "Memory limit reached"
- **Solution:** Increase memory limit: `vendor/bin/phpstan analyse --memory-limit=2G`

### Test Failures

**Problem:** Database-related test failures
- **Solution:** Reset only the test database: `rm database/database.sqlite && touch database/database.sqlite`, then rerun the scoped tests (migrations apply automatically). **Never** run `migrate:fresh`, `migrate:reset` or `db:wipe`—they destroy the development database.

**Problem:** Random test failures
- **Solution:** Ensure test isolation - each test should clean up after itself

**Problem:** "Class not found" in tests
- **Solution:** Run `composer dump-autoload` to rebuild autoload files

### Frontend Issues

**Problem:** TypeScript type errors
- **Solution:** Check generated types: `php artisan typescript:transform`

**Problem:** ESLint violations
- **Solution:** Run `npm run format` to auto-fix formatting issues

**Problem:** Vue component errors
- **Solution:** Ensure proper Composition API usage (no `await`, use `function` not arrow functions)

## Performance Tips

### Speed Up Local Development

- **Parallel test execution:** PHPUnit runs tests in parallel by default
- **Focused test runs:** Use `--filter` to run specific test classes
- **Cache warming:** Composer and npm cache dependencies automatically
- **Skip heavy tests:** During rapid iteration, focus on unit tests before running full suite

### Optimize CI Runs

- **Cached dependencies:** CI caches Composer and npm dependencies
- **Matrix builds:** Tests run in parallel across PHP versions
- **Fail fast:** CI stops on first failure to save time

## Related Documentation

- [Coding Conventions](../3-reference/coding-conventions.md) - Detailed PHP and Vue3 coding standards
- [Backend Architecture](../4-architecture/backend-architecture.md) - Laravel structure and patterns
- [API Design](../3-reference/api-design.md) - RESTful API conventions
- [AGENTS.md](../../../AGENTS.md) - Development workflow and commit protocol

---

*Last updated: December 22, 2025*
