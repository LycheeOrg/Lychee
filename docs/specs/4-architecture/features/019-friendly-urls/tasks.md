# Feature 019 Tasks – Friendly URLs (Album Slugs)

_Status: Complete_  
_Last updated: 2026-02-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Database Migration

- [x] T-019-01 – Create migration to add `slug` column to `base_albums` (FR-019-01, NFR-019-06).  
  _Intent:_ Add nullable VARCHAR(250) `slug` column with unique index to `base_albums` table. Implement reversible `down()`.  
  _Files:_ `database/migrations/YYYY_MM_DD_HHMMSS_add_slug_to_base_albums.php`  
  _Verification commands:_  
  - `rm -f database/database.sqlite && touch database/database.sqlite`  
  - `php artisan test --filter=MigrationTest` (or any test that triggers migrations)  
  _Notes:_ Nullable unique index allows multiple NULLs in MySQL/PostgreSQL/SQLite. Place column after `title`. Use `$table->string('slug', 250)->nullable()->unique()->after('title')`.

### I2 – Model & DTO Updates

- [x] T-019-02 – Add `slug` to `BaseAlbumImpl` model (FR-019-01).  
  _Intent:_ Expose slug in the Eloquent model attributes and casts.  
  _Files:_ `app/Models/BaseAlbumImpl.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Add `'slug' => null` to `$attributes`, `'slug' => 'string'` to `$casts`.

- [x] T-019-03 – Add `SLUG_ATTRIBUTE` constant to `RequestAttribute` (FR-019-07).  
  _Intent:_ Define attribute constant for use in request validation rules.  
  _Files:_ `app/Contracts/Http/Requests/RequestAttribute.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ `public const SLUG_ATTRIBUTE = 'slug';`

- [x] T-019-04 – Add `slug` field to `EditableBaseAlbumResource` (FR-019-12).  
  _Intent:_ Include slug in the editable album DTO so the frontend can read/write it.  
  _Files:_ `app/Http/Resources/Editable/EditableBaseAlbumResource.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Add `public ?string $slug` property. Populate from `BaseAlbumImpl::slug`.

- [x] T-019-05 – Add `slug` field to `HeadAlbumResource` (FR-019-12).  
  _Intent:_ Include slug in the album head response so the frontend can use it for navigation.  
  _Files:_ `app/Http/Resources/Models/HeadAlbumResource.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Add `public ?string $slug` property.

### I3 – SlugRule Validation

- [x] T-019-06 – Create `SlugRule` validation rule (FR-019-02, FR-019-03, FR-019-04, DO-019-02).  
  _Intent:_ Custom validation rule enforcing slug format, reserved word check, and uniqueness. Takes optional `$exclude_album_id` in constructor for update scenarios.  
  _Files:_ `app/Rules/SlugRule.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Format regex: `^[a-z][a-z0-9_-]{1,249}$`. Reserved words: `SmartAlbumType::values()` + route segments array (`settings`, `profile`, `login`, `register`, `diagnostics`, `home`, `users`, `sharing`, `jobs`, `maintenance`). Uniqueness: `BaseAlbumImpl::where('slug', $value)->where('id', '!=', $exclude_album_id)->exists()`.

### I4 – SlugRule Unit Tests

- [x] T-019-07 – Write unit tests for `SlugRule` (NFR-019-05, S-019-03, S-019-04, S-019-05).  
  _Intent:_ Test-first coverage for format validation, reserved words, and uniqueness.  
  _Files:_ `tests/Unit/Rules/SlugRuleTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=SlugRuleTest`  
  _Notes:_ Test cases:  
  - Valid: `my-album`, `architecture`, `my-vacation-2025`, `a_b`, `ab` (min), 250-char slug (max)  
  - Invalid format: uppercase (`My-Album`), special chars (`café!`), leading digit (`2025-trip`), leading hyphen (`-bad`), single char (`a`), empty string, >250 chars  
  - Reserved: each SmartAlbumType value (`unsorted`, `recent`, `highlighted`, etc.), route segments (`settings`, `profile`)  
  - Uniqueness: slug already used by another album → fails; same slug on same album (update) → passes

### I5 – ResolveAlbumSlug Middleware

- [x] T-019-08 – Create `ResolveAlbumSlug` middleware (FR-019-05, FR-019-06, DO-019-04).  
  _Intent:_ Middleware that intercepts `album_id` (query/route param) and translates slugs to real IDs before request validation. Short-circuits for 24-char IDs and SmartAlbumType values.  
  _Files:_ `app/Http/Middleware/ResolveAlbumSlug.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Logic:  
  1. Extract `album_id` from `$request->query()` and `$request->route()`  
  2. If `strlen($value) === RandomID::ID_LENGTH` → pass through (no DB query)  
  3. If `SmartAlbumType::tryFrom($value) !== null` → pass through  
  4. Otherwise: `BaseAlbumImpl::where('slug', $value)->value('id')` → replace in request if found  
  5. Also handle `album_ids` array param for batch endpoints  
  6. Call `$next($request)`  

- [x] T-019-09 – Register middleware and apply to routes (FR-019-05, NFR-019-02).  
  _Intent:_ Register `resolve_album_slug` alias in Kernel and apply to album routes.  
  _Files:_ `app/Http/Kernel.php`, `routes/api_v2.php`, `routes/web_v2.php`  
  _Verification commands:_  
  - `php artisan test` — all existing tests still pass (backward compat)  
  - `make phpstan`  
  _Notes:_ Apply to routes that accept `album_id`: `/Album`, `/Album::head`, `/Album::photos`, `/Album::albums`, `/Album::getTargetListAlbums`, gallery web route, etc. Existing tests must not break — middleware passes through all 24-char IDs unchanged.

### I6 – Middleware Feature Tests

- [x] T-019-10 – Write middleware resolution feature tests (NFR-019-05, S-019-08, S-019-09, S-019-10, S-019-11).  
  _Intent:_ Integration tests verifying slug-to-ID translation works end-to-end.  
  _Files:_ `tests/Feature_v2/Album/AlbumSlugResolutionTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSlugResolutionTest`  
  _Notes:_ Tests:  
  - `GET /Album?album_id={slug}` → 200 (album data returned)  
  - `GET /Album?album_id={id}` for album with slug → 200 (ID still works)  
  - `GET /Album?album_id={nonexistent-slug}` → 404  
  - SmartAlbumType value passes through → success  
  - Private album accessed by slug → 403 (authorization still enforced)  
  - Web route `/gallery/{slug}` → 200

### I7 – Extend Album Update with Slug

- [x] T-019-11 – Add `slug` to `UpdateAlbumRequest` (FR-019-07, API-019-01).  
  _Intent:_ Accept optional slug field when updating an album.  
  _Files:_ `app/Http/Requests/Album/UpdateAlbumRequest.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Add rule: `RequestAttribute::SLUG_ATTRIBUTE => ['present', 'nullable', new SlugRule($this->album?->id)]`. Process in `processValidatedValues()`. Save in controller.

- [x] T-019-12 – Add `slug` to `UpdateTagAlbumRequest` (FR-019-07).  
  _Intent:_ Accept optional slug field when updating a tag album.  
  _Files:_ `app/Http/Requests/Album/UpdateTagAlbumRequest.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Same pattern as T-019-11.

- [x] T-019-13 – Save slug in `AlbumController::updateAlbum()` and `updateTagAlbum()` (FR-019-07).  
  _Intent:_ Persist slug to base_albums when album is updated.  
  _Files:_ `app/Http/Controllers/Gallery/AlbumController.php`  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Access slug from request, set on album's `base_album_impl`, save. Handle both regular and tag album update methods.

### I8 – Slug CRUD Feature Tests

- [x] T-019-14 – Write slug CRUD feature tests (NFR-019-05, S-019-01 through S-019-07, S-019-14, S-019-15).  
  _Intent:_ Feature tests for setting, clearing, and validating slugs via PATCH /Album.  
  _Files:_ `tests/Feature_v2/Album/AlbumSlugCrudTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=AlbumSlugCrudTest`  
  _Notes:_ Tests:  
  - Set slug on regular album → 204, verify slug in GET response (S-019-01, S-019-15)  
  - Set slug on tag album → 204 (S-019-14)  
  - Clear slug (set to null) → 204 (S-019-02)  
  - Duplicate slug → 422 (S-019-03)  
  - Reserved slug → 422 (S-019-04)  
  - Invalid format → 422 (S-019-05)  
  - Unauthorized → 401 (S-019-07)  
  - Forbidden → 403 (S-019-06)

### I9 – Backend Quality Gate

- [x] T-019-15 – Run full backend quality gate (NFR-019-03).  
  _Intent:_ Ensure all backend code passes formatting, tests, and static analysis.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Fix any issues before proceeding to frontend.

### I10 – Frontend Service & Type Updates

- [x] T-019-16 – Add `slug` to frontend types and service (API-019-01, FR-019-12).  
  _Intent:_ Update TypeScript types and album service to include slug.  
  _Files:_ `resources/js/services/album-service.ts`  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Add `slug: string | null` to `UpdateAbumData` and `UpdateTagAlbumData` types. Verify album response type includes slug (auto-generated from Spatie Data or manual update needed).

### I11 – Frontend Slug Input UI

- [x] T-019-17 – Add slug input field to `AlbumProperties.vue` (FR-019-08, FR-019-09, UI-019-01 through UI-019-06, S-019-12, S-019-13).  
  _Intent:_ Slug text input with auto-generate button, URL preview, client-side validation.  
  _Files:_ `resources/js/components/forms/album/AlbumProperties.vue`  
  _Verification commands:_  
  - `npm run check`  
  - `npm run format`  
  _Notes:_  
  - Add InputText for slug after title field  
  - Add auto-generate Button (⟳ icon) that slugifies title: lowercase, replace `&` with `and`, replace non-alnum with `-`, collapse multiple hyphens, strip leading/trailing hyphens, strip leading digits  
  - Add computed URL preview: `${window.location.origin}/gallery/${slug}` (show only when slug is non-empty)  
  - Client-side validation: regex `^[a-z][a-z0-9_-]{1,249}$`, inline error message  
  - Include slug in `saveAlbum()` and `saveTagAlbum()` data objects  
  - Read slug from `EditableBaseAlbumResource` in `load()` function

### I12 – Frontend Vue Router Slug Navigation

- [x] T-019-18 – Use slug in URL bar when available (FR-019-10, S-019-16).  
  _Intent:_ When navigating to an album that has a slug, use the slug in the URL instead of the ID.  
  _Files:_ Album navigation components (gallery listing, breadcrumbs)  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ In router-link `:to`, use `album.slug ?? album.id` for the albumId param. `HeadAlbumResource` includes slug. No Vue Router route definition changes needed — `:albumId` accepts any string.

### I13 – Translation Strings

- [x] T-019-19 – Add English translation strings for slug UI (FR-019-08).  
  _Intent:_ Add all slug-related translation keys to English language file.  
  _Files:_ Appropriate lang file(s) under `lang/`  
  _Verification commands:_  
  - `grep -r "slug" lang/`  
  _Notes:_ Keys: field label ("Friendly URL"), placeholder ("my-album-name"), auto-generate tooltip ("Generate from title"), validation errors (format, reserved, duplicate), success toast.

- [x] T-019-20 – Add translation placeholders to other 21 languages.  
  _Intent:_ Copy English strings as placeholders to all other language files.  
  _Files:_ `lang/` (22 language files)  
  _Verification commands:_  
  - `grep -r "slug" lang/ | wc -l` — should match expected count  
  _Notes:_ Placeholder text in English; community can translate later.

### I14 – Frontend Quality Gate

- [x] T-019-21 – Run frontend quality gate (NFR-019-04).  
  _Intent:_ Ensure Vue/TypeScript code passes formatting and lint checks.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Verify: template-first, Composition API, regular functions (not arrow), `.then()` (not async/await).

### I15 – Final Integration & Documentation

- [x] T-019-22 – Run full quality gate (NFR-019-03, NFR-019-04).  
  _Intent:_ Final combined backend + frontend quality gate.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `npm run check`  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ All must pass.

- [x] T-019-23 – Manual scenario verification (S-019-01 through S-019-16).  
  _Intent:_ Walk through all 16 scenarios from the spec manually.  
  _Verification commands:_  
  - Manual browser testing  
  _Notes:_ Record pass/fail for each scenario. Fix any failures before marking complete.

- [x] T-019-24 – Update roadmap and mark feature complete.  
  _Intent:_ Record feature completion in roadmap.  
  _Files:_ `docs/specs/4-architecture/roadmap.md`  
  _Verification commands:_  
  - Review roadmap entry for Feature 019  
  _Notes:_ Move from Active to Completed, add completion date and summary.

## Notes / TODOs

- SlugRule needs the current album's ID in its constructor for the uniqueness check during updates (exclude self). The request class passes `$this->album?->id` after `processValidatedValues()` resolves the album — verify the validation timing works correctly (rules run before processValidatedValues, so the ID may need to be extracted earlier in the request lifecycle).
- The middleware must handle both query params (`$request->query('album_id')`) and route params (`$request->route('albumId')`) — note the different naming conventions (snake_case vs camelCase).
- Check whether the auto-generated TypeScript types from Spatie Data (`php artisan typescript:transform`) pick up the new `slug` field automatically, or if manual type updates are needed.
