# Feature Plan 019 – Friendly URLs (Album Slugs)

_Linked specification:_ `docs/specs/4-architecture/features/019-friendly-urls/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-02-27

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Give albums human-readable, shareable URLs by adding optional slugs. Success is measured by:
- Albums resolvable by slug transparently (middleware translates slug → ID before validation)
- All existing ID-based URLs continue working with zero overhead (passthrough for 24-char IDs)
- Slug CRUD via the existing PATCH /Album endpoint with proper validation
- UI integration in the album sidebar with auto-generate and copy-friendly URL preview
- Comprehensive test coverage for all 16 scenarios in the spec
- Code compliant with Lychee PHP and Vue3 conventions

## Scope Alignment

**In scope:**
- Database migration: `slug` column on `base_albums`
- `SlugRule` validation rule (format + reserved words)
- `ResolveAlbumSlug` middleware (slug → ID translation)
- Extend `UpdateAlbumRequest` and `UpdateTagAlbumRequest` to accept `slug`
- Extend `EditableBaseAlbumResource` and `HeadAlbumResource` with `slug` field
- Update `BaseAlbumImpl` model ($attributes, $casts)
- Frontend: slug input in `AlbumProperties.vue`, auto-generate helper, URL preview
- Frontend: Vue Router prefers slug in URL bar when available
- Translations: slug-related keys in 22 languages
- Feature tests for slug CRUD, middleware resolution, authorization

**Out of scope:**
- Hierarchical slugs (Q-019-01 resolved: flat)
- Top-level routes without `/gallery/` prefix (Q-019-02 resolved: gallery-prefixed)
- Slug versioning / history / 301 redirects
- Photo slugs
- Mandatory slugs

## Dependencies & Interfaces

**Backend:**
- `App\Models\BaseAlbumImpl` — receives `slug` column
- `App\Constants\RandomID` — ID_LENGTH used by middleware for passthrough check
- `App\Enum\SmartAlbumType` — values used by middleware and SlugRule for reserved word check
- `App\Contracts\Http\Requests\RequestAttribute` — new SLUG_ATTRIBUTE constant
- `App\Http\Kernel` — middleware alias registration
- `App\Http\Requests\Album\UpdateAlbumRequest` — extended with slug field
- `App\Http\Requests\Album\UpdateTagAlbumRequest` — extended with slug field
- `App\Http\Resources\Editable\EditableBaseAlbumResource` — new slug field
- `App\Http\Resources\Models\HeadAlbumResource` — new slug field

**Frontend:**
- `resources/js/services/album-service.ts` — UpdateAbumData + UpdateTagAlbumData types
- `resources/js/components/forms/album/AlbumProperties.vue` — slug input field
- PrimeVue InputText, Button components
- Vue Router — slug-preferred navigation

**Testing:**
- `BaseApiWithDataTest` — test base class with album fixtures
- In-memory SQLite database (test migrations must include slug column)

## Assumptions & Risks

**Assumptions:**
- Slugs are optional; the vast majority of albums will have no slug
- The 24-char random ID length check is a reliable discriminator between IDs and slugs
- SmartAlbumType values are stable and won't collide with user-chosen slugs
- The existing `PATCH /Album` endpoint can accept an additional optional `slug` field without breaking existing clients (they simply don't send it)

**Risks & Mitigations:**
- **Risk:** Slug uniqueness race condition between concurrent requests
  - **Mitigation:** Database unique index is the ultimate guard; application-level validation is advisory
- **Risk:** Reserved word list may be incomplete
  - **Mitigation:** Build from SmartAlbumType::values() + hardcoded route segments; extensible via config later
- **Risk:** Middleware adds overhead to every album-related request
  - **Mitigation:** Middleware short-circuits immediately for 24-char strings (strlen check); no DB query for normal IDs

## Implementation Drift Gate

Before marking complete:
1. Run full quality gate: `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`, `npm run format`, `npm run check`
2. Verify all 16 scenarios from Branch & Scenario Matrix pass
3. Confirm UI matches mock-ups (slug field in sidebar, auto-generate, URL preview)
4. Verify existing album tests still pass (backward compatibility)
5. Check that middleware short-circuits for 24-char IDs (verify with debug logging or unit test)

Evidence recorded in tasks.md verification notes for each increment.

## Increment Map

### I1 – Database Migration (FR-019-01, NFR-019-06)

**Goal:** Add `slug` column to `base_albums` table.

**Preconditions:** None.

**Steps:**
1. Create migration: `database/migrations/YYYY_MM_DD_HHMMSS_add_slug_to_base_albums.php`
2. Add nullable VARCHAR(250) `slug` column after `title`
3. Add unique index on `slug` (nullable unique = allows multiple NULLs in MySQL/PostgreSQL/SQLite)
4. Implement `down()` that drops the column

**Commands:**
- `php artisan test --filter=MigrationTest` (or just ensure migration runs)
- Reset test DB: `rm -f database/database.sqlite && touch database/database.sqlite`

**Exit:** Migration created and reversible. Test DB runs migrations successfully.

---

### I2 – Model & DTO Updates (FR-019-01, FR-019-12)

**Goal:** Update BaseAlbumImpl model and resource DTOs to include slug.

**Preconditions:** I1 complete.

**Steps:**
1. Add `'slug' => null` to `BaseAlbumImpl::$attributes`
2. Add `'slug' => 'string'` to `BaseAlbumImpl::$casts`
3. Add `public ?string $slug` to `EditableBaseAlbumResource` (Spatie Data)
4. Add `public ?string $slug` to `HeadAlbumResource` (Spatie Data)
5. Add `public const SLUG_ATTRIBUTE = 'slug'` to `RequestAttribute`

**Commands:**
- `make phpstan` — verify no type errors

**Exit:** Model exposes slug, DTOs include slug in responses, no PHPStan errors.

---

### I3 – SlugRule Validation (FR-019-02, FR-019-03, FR-019-04, DO-019-02)

**Goal:** Create custom validation rule for slug format and reserved words.

**Preconditions:** I2 complete (RequestAttribute constant).

**Steps:**
1. Create `app/Rules/SlugRule.php` implementing `ValidationRule`
2. Validate format: `^[a-z][a-z0-9_-]{1,249}$` (starts with letter, lowercase, min 2, max 250)
3. Check reserved words: SmartAlbumType::values() + hardcoded route segments array
4. Check uniqueness: query `BaseAlbumImpl::where('slug', $value)->where('id', '!=', $album_id)` — needs album_id passed in constructor for "update" cases
5. Return descriptive error messages: format error vs reserved vs duplicate

**Commands:**
- `make phpstan` — verify no type errors

**Exit:** SlugRule validates format, reserved words, and uniqueness. PHPStan clean.

---

### I4 – SlugRule Unit Tests (NFR-019-05, S-019-03, S-019-04, S-019-05)

**Goal:** Test-first coverage for SlugRule.

**Preconditions:** I3 complete.

**Steps:**
1. Create `tests/Unit/Rules/SlugRuleTest.php` extending AbstractTestCase
2. Test valid slugs: `my-album`, `architecture`, `my-vacation-2025`, `a_b`
3. Test invalid format: uppercase, special chars, leading digit, leading hyphen, single char, empty string, >250 chars
4. Test reserved words: each SmartAlbumType value, route segments
5. Test edge cases: exactly 2 chars, exactly 250 chars, hyphens vs underscores

**Commands:**
- `php artisan test --filter=SlugRuleTest`

**Exit:** All unit tests green.

---

### I5 – ResolveAlbumSlug Middleware (FR-019-05, FR-019-06, DO-019-04)

**Goal:** Create middleware that translates slug values to real album IDs in the request.

**Preconditions:** I1 complete (slug column exists).

**Steps:**
1. Create `app/Http/Middleware/ResolveAlbumSlug.php`
2. Implement `handle()`:
   - Extract `album_id` from query params and route params
   - For each: if strlen === RandomID::ID_LENGTH → pass through; if SmartAlbumType::tryFrom() !== null → pass through
   - Otherwise: `BaseAlbumImpl::where('slug', $value)->value('id')` → replace value if found, else pass through
   - Also handle array params (`album_ids`, `album_ids.*`) for batch endpoints
3. Register middleware alias `'resolve_album_slug'` in `Kernel.php`
4. Apply middleware to relevant album routes in `routes/api_v2.php` and `routes/web_v2.php`

**Commands:**
- `make phpstan` — verify no type errors
- `php artisan test` — existing tests still pass (middleware passthrough for IDs)

**Exit:** Middleware registered and applied. All existing tests pass (backward compat).

---

### I6 – Middleware Feature Tests (NFR-019-05, S-019-08, S-019-09, S-019-10, S-019-11)

**Goal:** Test middleware slug resolution via integration tests.

**Preconditions:** I5 complete.

**Steps:**
1. Create `tests/Feature_v2/Album/AlbumSlugResolutionTest.php` extending BaseApiWithDataTest
2. Test: album accessible by slug via `GET /Album?album_id={slug}` → 200
3. Test: album accessible by original ID when slug is set → 200
4. Test: non-existent slug → 404
5. Test: 24-char random ID passes through without slug query (existing behaviour)
6. Test: SmartAlbumType value passes through (e.g., `recent`) → success
7. Test: slug resolution respects authorization (private album → 403)
8. Test: resolve album by slug on web route `/gallery/{slug}`

**Commands:**
- `php artisan test --filter=AlbumSlugResolutionTest`

**Exit:** All middleware resolution tests green.

---

### I7 – Extend UpdateAlbumRequest with Slug (FR-019-07, API-019-01)

**Goal:** Accept `slug` field in the PATCH /Album endpoint.

**Preconditions:** I3 (SlugRule), I2 (RequestAttribute constant).

**Steps:**
1. Add `HasSlug` contract interface if needed, or add slug directly
2. Add `RequestAttribute::SLUG_ATTRIBUTE => ['present', 'nullable', new SlugRule($this->album?->id)]` to `UpdateAlbumRequest::rules()`
3. Process slug in `processValidatedValues()`: `$this->slug = $values[RequestAttribute::SLUG_ATTRIBUTE]`
4. Update `AlbumController::updateAlbum()` to save slug
5. Repeat for `UpdateTagAlbumRequest` and its controller method

**Commands:**
- `make phpstan` — verify no type errors

**Exit:** PATCH /Album and PATCH /TagAlbum accept slug. PHPStan clean.

---

### I8 – Slug CRUD Feature Tests (NFR-019-05, S-019-01 through S-019-07, S-019-14, S-019-15)

**Goal:** Feature tests for setting, clearing, and validating slugs via API.

**Preconditions:** I7 complete.

**Steps:**
1. Create `tests/Feature_v2/Album/AlbumSlugCrudTest.php` extending BaseApiWithDataTest
2. Test: set slug on album (204), verify slug in GET response
3. Test: set slug on tag album (204)
4. Test: clear slug (set to null, 204)
5. Test: duplicate slug (422)
6. Test: reserved slug (422)
7. Test: invalid format (422)
8. Test: unauthorized (401) and forbidden (403)
9. Test: album response includes `slug` field (null and non-null)

**Commands:**
- `php artisan test --filter=AlbumSlugCrudTest`

**Exit:** All CRUD tests green.

---

### I9 – Backend Quality Gate (NFR-019-03)

**Goal:** Ensure all backend code passes quality checks.

**Preconditions:** I1–I8 complete.

**Steps:**
1. Run `vendor/bin/php-cs-fixer fix` — formatting
2. Run `php artisan test` — all tests pass
3. Run `make phpstan` — no errors

**Commands:**
- `vendor/bin/php-cs-fixer fix`
- `php artisan test`
- `make phpstan`

**Exit:** Full backend quality gate green.

---

### I10 – Frontend Service & Type Updates (API-019-01, FR-019-12)

**Goal:** Add slug to frontend TypeScript types and service calls.

**Preconditions:** I7 complete (backend accepts slug).

**Steps:**
1. Add `slug: string | null` to `UpdateAbumData` type in `album-service.ts`
2. Add `slug: string | null` to `UpdateTagAlbumData` type
3. Verify album response types include `slug` field (auto-generated or manual)
4. Add slugify helper function in `resources/js/utils/` or inline

**Commands:**
- `npm run check` — TypeScript compiles

**Exit:** Types updated, TypeScript compiles clean.

---

### I11 – Frontend Slug Input in AlbumProperties (FR-019-08, FR-019-09, UI-019-01 through UI-019-06)

**Goal:** Add slug input field with auto-generate button and URL preview to album sidebar.

**Preconditions:** I10 complete.

**Steps:**
1. In `AlbumProperties.vue`, add slug `InputText` after title field
2. Add auto-generate `Button` (⟳ icon) that slugifies current title
3. Add computed URL preview showing `{window.location.origin}/gallery/{slug}`
4. Add client-side validation: format regex, inline error messages
5. Include slug in `saveAlbum()` and `saveTagAlbum()` data objects
6. Read slug from `EditableBaseAlbumResource` in `load()` function

**Commands:**
- `npm run check` — TypeScript compiles
- `npm run format` — formatting applied

**Exit:** Slug field renders in sidebar, auto-generate works, URL preview shown.

---

### I12 – Frontend: Vue Router Slug Navigation (FR-019-10, S-019-16)

**Goal:** When an album has a slug, use it in the URL bar instead of the ID.

**Preconditions:** I10 complete (slug in API responses).

**Steps:**
1. In album listing/navigation components, prefer `slug ?? id` for router-link `:to`
2. Ensure `HeadAlbumResource` slug field is available in album list data
3. Verify direct navigation to `/gallery/{slug}` still works (middleware handles it)
4. No changes to Vue Router route definitions — `:albumId` param accepts any string

**Commands:**
- `npm run check` — TypeScript compiles
- Manual testing: navigate to album with slug, verify URL bar

**Exit:** URL bar shows `/gallery/{slug}` for albums with slugs.

---

### I13 – Translation Strings (FR-019-08)

**Goal:** Add translation keys for slug UI in all 22 languages.

**Preconditions:** I11 complete (know exact key names).

**Steps:**
1. Add English strings to appropriate lang file(s):
   - Slug field label, placeholder, auto-generate tooltip
   - Validation messages: format error, reserved word, already in use
   - Success toast
2. Add English placeholders to other 21 language files

**Commands:**
- `grep -r "slug" lang/` — strings exist in all language files

**Exit:** All 22 language files updated.

---

### I14 – Frontend Quality Gate (NFR-019-04)

**Goal:** Ensure frontend code passes quality checks.

**Preconditions:** I10–I13 complete.

**Steps:**
1. Run `npm run format` — Prettier formatting
2. Run `npm run check` — TypeScript + lint checks

**Commands:**
- `npm run format`
- `npm run check`

**Exit:** Frontend quality gate green.

---

### I15 – Full Integration Quality Gate & Documentation

**Goal:** Final quality gate, manual testing, and documentation updates.

**Preconditions:** I9 and I14 complete.

**Steps:**
1. Full quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`
2. Manual testing: all 16 scenarios from spec
3. Update roadmap status
4. Prepare commit message

**Commands:**
- Full quality gate sequence
- Manual scenario verification

**Exit:** Feature complete, all quality gates green, roadmap updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-019-01 | I8 / T-019-10 | Set slug on album, verify in response |
| S-019-02 | I8 / T-019-11 | Clear slug |
| S-019-03 | I8 / T-019-12 | Duplicate slug → 422 |
| S-019-04 | I8 / T-019-12 | Reserved slug → 422 |
| S-019-05 | I8 / T-019-12 | Invalid format → 422 |
| S-019-06 | I8 / T-019-13 | Forbidden → 403 |
| S-019-07 | I8 / T-019-13 | Unauthorized → 401 |
| S-019-08 | I6 / T-019-08 | Navigate by slug → album loaded |
| S-019-09 | I6 / T-019-08 | Navigate by ID when slug exists → album loaded |
| S-019-10 | I6 / T-019-08 | Non-existent slug → 404 |
| S-019-11 | I6 / T-019-08 | API with slug → album returned |
| S-019-12 | I11 / T-019-17 | Auto-generate slug from title |
| S-019-13 | I11 / T-019-17 | Auto-generate empty result → warning |
| S-019-14 | I8 / T-019-11 | Set slug on tag album |
| S-019-15 | I8 / T-019-10 | Slug field in API response |
| S-019-16 | I12 / T-019-18 | Frontend URL bar shows slug |

## Analysis Gate

Not yet completed. Will be run after spec, plan, and tasks agree.

## Exit Criteria

- All 16 scenarios pass (automated + manual)
- `vendor/bin/php-cs-fixer fix` — clean
- `php artisan test` — all green (including new + existing tests)
- `make phpstan` — zero errors
- `npm run format` — clean
- `npm run check` — clean
- Roadmap updated, tasks marked complete
- No regressions in existing album tests

## Follow-ups / Backlog

- **Slug versioning / history:** Middleware architecture supports adding a `slug_history` table with 301 redirects from old slugs. Deferred.
- **Top-level routes:** If demand exists, add optional `/{slug}` catch-all route after all named routes. Deferred (Q-019-02).
- **Hierarchical slugs:** Could build computed `full_path` column from the nested set tree. Much more complex. Deferred (Q-019-01).
- **Bulk slug generation:** Admin command to auto-generate slugs for all existing albums. Nice-to-have.
- **Slug in OpenAPI/Scramble docs:** Update API documentation schema to include slug field.
