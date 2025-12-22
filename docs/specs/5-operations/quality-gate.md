# Quality Gate – Usage & Troubleshooting

_Last updated: December 22, 2025_

The quality gate for Lychee enforces code style, static analysis, and test coverage standards across both backend (PHP/Laravel) and frontend (Vue3/TypeScript) code. Run the appropriate checks locally before committing changes, and rely on the CI workflow ([.github/workflows/CICD.yml](../../../.github/workflows/CICD.yml)) for enforcement on every push and pull request.

This guide is aligned with the coding conventions defined in [docs/specs/3-reference/coding-conventions.md](../3-reference/coding-conventions.md) and the quality requirements in [AGENTS.md](../../../AGENTS.md).

## Quick Reference

### Full Quality Gate (Both PHP and Frontend Modified)

```bash
vendor/bin/php-cs-fixer fix    # Apply PHP code style fixes
npm run format                 # Apply frontend code formatting
npm run check                  # Run frontend tests
php artisan test               # Run PHP tests
make phpstan                   # Run static analysis
```

### PHP-Only Changes

```bash
vendor/bin/php-cs-fixer fix    # Apply PHP code style fixes
php artisan test               # Run PHP tests
make phpstan                   # Run static analysis (PHPStan level 6)
```

### Frontend-Only Changes

```bash
npm run format                 # Apply frontend code formatting (Prettier)
npm run check                  # Run frontend tests
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

#### 2. Test Suite: `php artisan test`

Runs the full PHPUnit test suite including unit tests and feature tests.

**Test structure:**
- `tests/Unit/` - Unit tests (extend `AbstractTestCase`)
- `tests/Feature_v2/` - Feature/integration tests (extend `BaseApiWithDataTest`)

**Coverage expectations:**
- All new code must include tests
- Critical paths require comprehensive test coverage
- Use `--filter` to run specific test suites during development

**Example:**
```bash
php artisan test --filter=AlbumTest
```

#### 3. Static Analysis: `make phpstan`

Runs PHPStan at level 6 to catch type errors, undefined variables, and other static analysis violations.

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

#### 2. Frontend Tests: `npm run check`

Runs ESLint, TypeScript type checking, and frontend unit tests.

**What it checks:**
- ESLint rules for Vue3 Composition API
- TypeScript type correctness
- Frontend unit tests
- Import/export consistency

## Standards & Conventions

### PHP Standards (PSR-4)

- **Variables:** `snake_case`
- **Classes:** `PascalCase` with PSR-4 autoloading
- **Methods:** `camelCase`
- **Constants:** `UPPER_SNAKE_CASE`
- **Comparison:** Use strict comparison (`===`) instead of loose (`==`)
- **Arrays:** Use `in_array($value, $array, true)` with strict comparison
- **Empty:** Do not use `empty()` - use explicit checks instead
- **License headers:** Required in all new files

### Vue3/TypeScript Standards

- **Composition API:** All components use `<script setup lang="ts">`
- **Functions:** Use `function name() {}` instead of arrow functions for component methods
- **Async:** Use `.then()` instead of `await`/`async` in Vue components
- **State:** Pinia stores for global state, reactive/ref for local state
- **Services:** API calls in `resources/js/services/` using axios
- **Components:** PrimeVue-based UI components

### Test Requirements

- **Unit tests:** Extend `AbstractTestCase`
- **Feature tests:** Extend `BaseApiWithDataTest`
- **Coverage:** New code must include corresponding tests
- **Naming:** Test methods describe what they test
- **Database:** Use in-memory SQLite (no mocking required)

## CI Integration

GitHub Actions runs the full quality gate on every push and pull request via `.github/workflows/CICD.yml`.

**CI checks include:**
- PHP CS Fixer (must pass with no changes)
- PHPStan level 6 (must pass with no new violations)
- PHPUnit test suite (all tests must pass)
- Frontend formatting and linting
- Frontend tests

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
- **Solution:** Check that migrations are up to date: `php artisan migrate:fresh`

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
