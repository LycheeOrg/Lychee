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

### Translations

- **Source files:** Translation sources are the PHP arrays in `lang/<locale>/*.php` (e.g. `lang/en/gallery.php`). Only edit these files; use snake_case keys and group related translations in nested arrays.
- **Generated files:** `lang/<locale>.json` and `lang/php_<locale>.json` are generated and git-ignored. Never read, write, or edit them.

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

- **Type generation:** TypeScript types for PHP resources are automatically generated. After modifying PHP resource classes (e.g., `PhotoResource`, `PhotoStatisticsResource`), run:
  ```bash
  php artisan typescript:transform
  ```
  This generates TypeScript definitions in `resources/js/lychee.d.ts` from PHP DTOs, resources, and enums. The generated types are automatically available in the `App.*` namespace (e.g., `App.Http.Resources.Models.PhotoResource`).

### v7 vs v8 Scope

The frontend has two trees. **v8** is everything under `resources/js/v8/`. **v7** is everything else under `resources/js/`, including the shared modules outside `v8/`. Rules marked *v7 only* below do not apply to v8 code.

- **Function declarations (v7 only):** Use regular function declarations, not arrow functions. In v8, both styles are allowed.
  ```typescript
  // ✅ Correct (v7)
  function handleClick() {
      // ...
  }
  
  // ❌ Incorrect (v7)
  const handleClick = () => {
      // ...
  };
  ```

- **Async handling (v7 only):** Do not use `await`/`async`. Use `.then()` instead. In v8, `async`/`await` is allowed.
  ```typescript
  // ✅ Correct (v7)
  fetchData().then((data) => {
      processData(data);
  });
  
  // ❌ Incorrect (v7)
  const data = await fetchData();
  processData(data);
  ```

### UI Components

- **v7 component library:** Use PrimeVue for UI components, and build custom components on top of PrimeVue primitives.
- **v8 component library:** Use Nuxt UI (`@nuxt/ui`, the `<U…>` components) for UI components, and build custom components on top of Nuxt UI primitives. Do not import PrimeVue in v8 code.

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
  
  use Tests\Feature_v2\Base\BaseApiWithDataTest;
  
  class PhotoApiTest extends BaseApiWithDataTest
  {
      // ...
  }
  ```

- **v3 feature tests:** Tests in `tests/Feature_v3/` directory must extend from `Tests\Feature_v3\Base\BaseApiWithDataTest`.

### Database Testing

- **No mocking:** Do not mock the database in tests.
- **SQLite test database:** Tests run against the SQLite file `database/database.sqlite` (configured in `phpunit.xml`), not the development database. Changes are rolled back via the `DatabaseTransactions` trait.

## Documentation Conventions

### Markdown Format

- **Standard:** Use Markdown format for all documentation.
- **Footer:** At the bottom of every documentation file (except files created from `docs/specs/templates/`, which follow the template's own layout), add:
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
2. **Tests:** `php artisan test --filter=<ClassName>` for every test class touched by or related to the change — all must pass. Never run the whole suite locally; CI runs it.
3. **PHPStan:** `make phpstan` — at the level configured in `phpstan.neon`; fix all errors

### Frontend Code Quality

Before committing frontend changes:

1. **Prettier:** `npm run format` — Apply code formatting
2. **Type-check:** `npm run check` — TypeScript type-check (`vue-tsc`) must pass

## Related References

- [Knowledge Map](../4-architecture/knowledge-map.md) - Module and dependency relationships
- [PSR-4 Specification](https://www.php-fig.org/psr/psr-4/) - PHP autoloading standard
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html) - Official Vue3 documentation
- [PrimeVue Documentation](https://primevue.org/) - v7 UI component library
- [Nuxt UI Documentation](https://ui.nuxt.com/) - v8 UI component library

---

*Last updated: September 27, 2026*
