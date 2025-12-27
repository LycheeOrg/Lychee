# Coding Conventions

This document defines the coding standards and conventions for the Lychee project across PHP, Vue3/TypeScript, and documentation.

## PHP Conventions

### File Structure

- **License header:** Every new PHP file must contain the license header at the top.
- **Blank line:** Include a single blank line after the opening `<?php` tag.

```php
<?php

/**
 * License header goes here...
 */

namespace App\Example;
```

### Naming Conventions

- **Variables:** Use `snake_case` for variable names.
  ```php
  $user_name = 'John Doe';
  $album_id = 42;
  ```

- **Classes:** Follow PSR-4 autoloading standard.
  ```php
  namespace App\Http\Controllers;
  
  class PhotoController extends Controller
  ```

### Coding Standards

- **PSR-4:** Apply the PSR-4 coding standard for autoloading.

- **Array checks:** Use `in_array()` with `true` as the third parameter for strict comparison.
  ```php
  // ✅ Correct
  if (in_array($value, $array, true)) {
      // ...
  }
  
  // ❌ Incorrect
  if (in_array($value, $array)) {
      // ...
  }
  ```

- **Conditionals:** Only use booleans in if statements, not integers or strings.
  ```php
  // ✅ Correct
  if ($user->isActive()) {
      // ...
  }
  
  // ❌ Incorrect
  if ($user->status) {  // if status is integer or string
      // ...
  }
  ```

- **Strict comparison:** Use strict comparison (`===`) instead of loose comparison (`==`).
  ```php
  // ✅ Correct
  if ($value === null) {
      // ...
  }
  
  // ❌ Incorrect
  if ($value == null) {
      // ...
  }
  ```

- **Code duplication:** Avoid code duplication in both if and else statements. Extract common code.
  ```php
  // ✅ Correct
  $base_config = getBaseConfig();
  if ($condition) {
      return array_merge($base_config, ['extra' => 'value']);
  }
  return $base_config;
  
  // ❌ Incorrect
  if ($condition) {
      $config = getBaseConfig();
      $config['extra'] = 'value';
      return $config;
  } else {
      $config = getBaseConfig();
      return $config;
  }
  ```

- **Empty function:** Do not use `empty()`. Use explicit checks instead.
  ```php
  // ✅ Correct
  if ($value === null || $value === '' || $value === []) {
      // ...
  }
  
  // ❌ Incorrect
  if (empty($value)) {
      // ...
  }
  ```

### Application-Specific Conventions

- **Request user handling:**
  - `$this->user` is reserved for the user making the request.
  - `$this->user2` is used when a user is provided by the query parameter.

- **Resource classes:** Must extend from `Spatie\LaravelData\Data` instead of `JsonResource`.
  ```php
  use Spatie\LaravelData\Data;
  
  class PhotoData extends Data
  {
      // ...
  }
  ```

- **Views:** Do not use Blade views. The application uses Vue3 for all frontend rendering.

### Money and Currency

When dealing with monetary values:

- **Library:** Use the `moneyphp/money` library for all monetary operations.
- **Storage:** Never use floats or doubles. Store values as integers representing the smallest currency unit (e.g., cents for USD).
  ```php
  // ✅ Correct
  $price_in_cents = 1099;  // Represents $10.99
  $money = new Money($price_in_cents, new Currency('USD'));
  
  // ❌ Incorrect
  $price = 10.99;  // Float - prone to rounding errors
  ```

### Database Transactions

- **Preferred:** Use `DB::transaction(callable)` for database transactions instead of manually calling `DB::beginTransaction()`, `DB::commit()`, and `DB::rollback()`. This ensures that transactions are handled more cleanly and reduces the risk of forgetting to commit or rollback.

```php
// ✅ Correct
DB::transaction(function () {
    // Perform database operations
});

// ❌ Incorrect
DB::beginTransaction();
try {
    // Perform database operations
    DB::commit();
} catch (Exception $e) {
    DB::rollback();
    throw $e;
}
```

## Vue3/TypeScript Conventions

### Component Structure

- **Template order:** Components must follow this structure:
  1. `<template>` first
  2. `<script lang="ts">` second
  3. `<style>` last

```vue
<template>
  <div>
    <!-- Component template -->
  </div>
</template>

<script lang="ts">
// Component logic
</script>

<style>
/* Component styles */
</style>
```

### TypeScript Standards

- **Composition API:** Use TypeScript with Composition API for Vue3.

- **Function declarations:** Use regular function declarations, not arrow functions.
  ```typescript
  // ✅ Correct
  function handleClick() {
      // ...
  }
  
  // ❌ Incorrect
  const handleClick = () => {
      // ...
  };
  ```

- **Async handling:** Do not use `await`/`async` in Vue3. Use `.then()` instead.
  ```typescript
  // ✅ Correct
  fetchData().then((data) => {
      processData(data);
  });
  
  // ❌ Incorrect
  const data = await fetchData();
  processData(data);
  ```

### UI Components

- **Component library:** Use PrimeVue for UI components.
- **Custom components:** Build custom components on top of PrimeVue primitives.

### API Communication

- **Services location:** Place all axios requests in the `services/` directory.
- **Base URL:** Use `${Constants.getApiUrl()}` to specify the base URL.
  ```typescript
  import axios from 'axios';
  import { Constants } from '@/constants';
  
  export function fetchPhotos() {
      return axios.get(`${Constants.getApiUrl()}/photos`);
  }
  ```

## Testing Conventions

### Test Organization

- **Unit tests:** Tests in `tests/Unit/` directory must extend from `AbstractTestCase`.
  ```php
  namespace Tests\Unit;
  
  use Tests\AbstractTestCase;
  
  class ExampleTest extends AbstractTestCase
  {
      // ...
  }
  ```

- **Feature tests:** Tests in `tests/Feature_v2/` directory must extend from `BaseApiWithDataTest`.
  ```php
  namespace Tests\Feature_v2;
  
  use Tests\Feature_v2\BaseApiWithDataTest;
  
  class PhotoApiTest extends BaseApiWithDataTest
  {
      // ...
  }
  ```

### Database Testing

- **No mocking:** Do not mock the database in tests.
- **In-memory database:** Use the in-memory SQLite database for test execution.

## Documentation Conventions

### Markdown Format

- **Standard:** Use Markdown format for all documentation.
- **Footer:** At the bottom of every documentation file, add:
  ```markdown
  ---
  
  *Last updated: [date of the update]*
  ```

### Documentation Structure

- Follow the established structure in `docs/specs/`:
  - `0-overview/` - High-level project documentation
  - `1-concepts/` - Conceptual documentation
  - `2-how-to/` - How-to guides
  - `3-reference/` - Reference documentation (this file)
  - `4-architecture/` - Architecture decisions and designs
  - `5-operations/` - Operational runbooks
  - `6-decisions/` - Architectural Decision Records (ADRs)

## Quality Gates

### PHP Code Quality

Before committing PHP changes:

1. **PHP CS Fixer:** `vendor/bin/php-cs-fixer fix` — Apply code style fixes
2. **Tests:** `php artisan test` — All tests must pass
3. **PHPStan:** `make phpstan` — Level 6 minimum; fix all errors

### Frontend Code Quality

Before committing frontend changes:

1. **Prettier:** `npm run format` — Apply code formatting
2. **Tests:** `npm run check` — All frontend tests must pass

## Related References

- [Knowledge Map](../4-architecture/knowledge-map.md) - Module and dependency relationships
- [PSR-4 Specification](https://www.php-fig.org/psr/psr-4/) - PHP autoloading standard
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html) - Official Vue3 documentation
- [PrimeVue Documentation](https://primevue.org/) - UI component library

---

*Last updated: December 27, 2025*
