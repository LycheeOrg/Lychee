# Feature Plan 025 – Dynamic Landing Background Options

_Linked specification:_ `docs/specs/4-architecture/features/025-dynamic-landing-backgrounds/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-03-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Enable admins to configure dynamic landing page backgrounds that showcase their photo collections. Success means:
- Admins can choose from 5 modes (static URL, photo ID, random, latest album cover, random from album) via enum configs.
- Landing page displays fresh dynamic images on each load, respecting public visibility.
- No performance regression for static URL mode (default).
- Zero security leaks (private photos never appear in public landing backgrounds).
- Backward compatible: existing static URL configs work unchanged (mode defaults to `static`).

## Scope Alignment

### In scope
- Add 2 new enum config keys: `landing_background_landscape_mode` and `landing_background_portrait_mode` with 5 enum values: `static`, `photo_id`, `random`, `latest_album_cover`, `random_from_album`.
- Backend resolution logic in `LandingPageResource` that interprets mode enum and resolves value field accordingly.
- Database migration to add new enum configs and update existing config descriptions.
- Translation keys updated for all 22 languages.
- Integration tests covering all 5 modes and security scenarios.
- Fallback to default image when no public content exists or IDs are invalid.

### Out of scope
- Admin UI preview of landing backgrounds before saving.
- Caching of resolved dynamic images (each request resolves fresh).
- User-specific or authenticated landing backgrounds (public only).
- Background animations or transitions.
- Multiple/slideshow backgrounds.

## Dependencies & Interfaces

- **Query policies:** `App\Policies\PhotoQueryPolicy::applySearchabilityFilter()`, `App\Policies\AlbumQueryPolicy::applySearchabilityFilter()`
- **Resources:** `App\Http\Resources\GalleryConfigs\LandingPageResource`
- **Models:** `App\Models\Photo`, `App\Models\Album`, `App\Models\Config`
- **Size variants:** Photo size variant resolution (medium/large/original)
- **Pre-computed covers:** Album `cover_id` and `auto_cover_id_least_privilege` fields (Feature 003)
- **Frontend:** `resources/js/views/Landing.vue` (no changes needed, passively consumes resolved URLs)

## Assumptions & Risks

### Assumptions
- Database has indexed `published_at`, `created_at`, and `id` columns for efficient queries.
- `auto_cover_id_least_privilege` is reliably pre-computed (Feature 003 delivered this).
- Public access permissions are correctly configured via `AccessPermission` model.

### Risks / Mitigations
- **Risk:** Random queries on large databases (10K+ photos) may be slow.  
  **Mitigation:** Use indexed columns, LIMIT 1, and test with large datasets (NFR-025-01 targets ≤100ms p95).
- **Risk:** No public content exists → broken landing page.  
  **Mitigation:** Fallback to `dist/cat.webp` default image (FR-025-02 through FR-025-05).
- **Risk:** Private photos leak via dynamic modes.  
  **Mitigation:** All queries use `user=null` with query policies; security tests enforce this (S-025-11).

## Implementation Drift Gate

Execute after all increments complete:
1. Run full test suite: `php artisan test --testsuite=Feature_v2`
2. Verify PHPStan: `make phpstan` (level 6, zero errors)
3. Verify formatting: `vendor/bin/php-cs-fixer fix --dry-run` (zero changes)
4. Manual smoke test:
   - Configure each of the 4 dynamic modes in admin settings.
   - Visit landing page 5 times for each mode, verify different images appear (for random modes).
   - Create albums/photos, set some to private, verify private content never appears.
   - Test fallback: remove all public photos, verify `dist/cat.webp` displays.
5. Record findings in Appendix: Drift Gate Results (below).

## Increment Map

### I1 – Database Migration and Backend Stubs (FR-025-01, FR-025-02, NFR-025-04)
**Goal:** Create migration to add mode enum configs; add stub helper method in `LandingPageResource`.

**Preconditions:** None (greenfield).

**Steps:**
1. Create migration `YYYY_MM_DD_HHMMSS_add_dynamic_landing_background_modes.php` extending `BaseConfigMigration`.
2. Migration adds two new enum config rows:
   - `landing_background_landscape_mode`: enum `static|photo_id|random|latest_album_cover|random_from_album`, default `static`
   - `landing_background_portrait_mode`: enum `static|photo_id|random|latest_album_cover|random_from_album`, default `static`
3. Migration updates `details` for existing `landing_background_landscape` and `landing_background_portrait`:
   - New text: "Value interpreted based on mode config (URL, photo ID, or album ID)"
4. Add stub method `LandingPageResource::resolveBackgroundUrl(string $mode, string $value): string` that returns `$value` unchanged.
5. Update constructor to fetch mode configs and call `resolveBackgroundUrl($mode, $value)` for both orientations.
6. Run migration: `php artisan migrate`
7. Write migration test (up/down): verify enum configs added, details updated.

**Commands:**
- `php artisan make:migration add_dynamic_landing_background_modes`
- `php artisan migrate`
- `php artisan test --filter=add_dynamic_landing_background_modes`
- `make phpstan`

**Exit:** Migration passes, stub method compiles, existing tests still green, mode defaults to `static`.

**Estimated Duration:** 45 minutes

---

### I2 – Core Resolution Logic for Static and Photo ID Modes (FR-025-03, FR-025-04, NFR-025-03, NFR-025-05)
**Goal:** Implement `static` and `photo_id` mode resolution in `resolveBackgroundUrl()`.

**Preconditions:** I1 complete.

**Steps:**
1. In `LandingPageResource::resolveBackgroundUrl(string $mode, string $value)`:
   - Add switch statement on `$mode`.
   - Case `static`: return `$value` unchanged (existing behavior).
   - Case `photo_id`: call helper `resolvePhotoById(string $photo_id)`.
2. Implement `resolvePhotoById()`:
   - Query `Photo::query()->with(['size_variants'])->find($photo_id)`.
   - **No public access check** - admin is responsible for selecting appropriate photos.
   - If photo not found, return fallback `dist/cat.webp`.
   - Extract URL from size variant (prefer medium, fallback to original).
3. Write unit tests:
   - Test `static` mode returns value unchanged.
   - Test `photo_id` mode with valid photo (public or private) returns correct URL.
   - Test `photo_id` mode with non-existent ID returns fallback.

**Commands:**
- `php artisan test --filter=LandingPageResourceTest`
- `make phpstan`

**Exit:** `static` and `photo_id` modes work, tests pass, fallback logic verified.

**Estimated Duration:** 60 minutes

---

### I3 – Core Resolution Logic for Random and Latest Album Cover Modes (FR-025-05, FR-025-06, NFR-025-03, NFR-025-05)
**Goal:** Implement `random`, `latest_album_cover`, and `random_from_album` modes.

**Preconditions:** I2 complete.

**Steps:**
1. In `LandingPageResource::resolveBackgroundUrl()` switch statement:
   - Case `random`: call helper `resolveRandomPublicPhoto()`.
   - Case `latest_album_cover`: call helper `resolveLatestPublicAlbumCover()`.
   - Case `random_from_album`: call helper `resolveRandomPhotoFromAlbum(string $album_id)`.
2. Implement `resolveRandomPublicPhoto()`:
   - Query `Photo::query()->with(['size_variants'])`.
   - Apply `PhotoQueryPolicy::applySearchabilityFilter($query, user: null, unlocked_album_ids: [])`.
   - Order by `RAND()` or `RANDOM()`, LIMIT 1.
   - If no result, return fallback `dist/cat.webp`.
   - Extract URL from size variant.
3. Implement `resolveLatestPublicAlbumCover()`:
   - Query `Album::query()->with(['cover', 'cover.size_variants', 'min_privilege_cover', 'min_privilege_cover.size_variants'])`.
   - Apply `AlbumQueryPolicy::applySearchabilityFilter($query, user: null)`.
   - Order by `published_at DESC, created_at DESC, id DESC`, LIMIT 1.
   - Read `cover_id` (prefer) or `auto_cover_id_least_privilege` (fallback).
   - Extract URL from cover photo; fallback if no cover.
4. Implement `resolveRandomPhotoFromAlbum(string $album_id)`:
   - Verify album exists and is public using `AlbumQueryPolicy`.
   - Query photos in album, apply `PhotoQueryPolicy` public filter.
   - Order by `RAND()`, LIMIT 1.
   - Extract URL; fallback if album invalid or no photos.
5. Write unit tests for all 3 modes.

**Commands:**
- `php artisan test --filter=LandingPageResourceTest`
- `make phpstan`

**Exit:** `random`, `latest_album_cover`, and `random_from_album` modes work, tests pass.

**Estimated Duration:** 90 minutes
3
---

### I4 – Integration Tests (S-025-01 through S-025-11, NFR-025-03, NFR-025-04)
**Goal:** End-to-end feature tests covering all scenarios and security requirements.

**Preconditions:** I1-I3 complete.

**Steps:**Mode=`static`, value=URL, verify landing page returns unchanged URL.
   - **S-025-02:** Mode=`random` with 10 public photos, verify random photo returned.
   - **S-025-03:** Mode=`random` with zero public photos, verify fallback returned.
   - **S-025-04:** Mode=`photo_id` with valid photo ID (public or private), verify correct photo returned.
   - **S-025-05:** Mode=`photo_id` with non-existent ID, verify fallback returned.
   - **S-025-06:** Mode=`latest_album_cover` with 5 public albums, verify latest album cover returned.
   - **S-025-07:** Mode=`latest_album_cover` with albums without explicit covers, verify auto cover used.
   - **S-025-08:** Mode=`random_from_album` with valid album ID, verify random photo from that album.
   - **S-025-09:** Mode=`random_from_album` with private/empty album, verify fallback returned.
   - **S-025-10:** Mixed modes: landscape=`random`, portrait=`latest_album_cover`, verify independent resolution.
   - **S-025-11:** Invalid mode enum value, verify validation error.
   - **S-025-12:** Mode=`random`, call multiple times, verify fresh resolution (no caching).
   - **S-025-13 (security):** Mix of public/private photos, mode=`random`solution.
   - **S-025-09:** Attempt to save invalid mode keyword via config update API, verify validation error.
   - **S-025-10:** Call landing endpoint multiple times, verify fresh resolution (no caching).
   - **S-025-11 (security):** Create mix of public/private photos, set `random_photo`, call 100 times, verify private photos NEVER returned.
3. Run tests: `php artisan test --filter=DynamicBackgroundTest`

**Commands:**
- `php artisan test --filter=DynamicBackgroundTest`
- `make phpstan3 scenarios pass, security verified.

**Estimated Duration:** 12s pass, security verified.

**Estimated Duration:** 90 minutes

---

### I5 – Translation Updates (FR-025-09, TRANS-025-01, TRANS-025-02)
**Goal:** Update config description translations for all 22 languages.

**Preconditions:** I4 complete (functionality verified).

**Steps:**_mode`: "Background mode for landscape orientation"
   - `landing_background_portrait_mode`: "Background mode for portrait orientation"
   - `landing_background_landscape`: "Value interpreted based on mode (URL, photo ID, or album ID)
1. Update `lang/en/all_settings.php`:
   - `landing_background_landscape`: "URL or dynamic mode: random_photo, random_album_cover, latest_photo, latest_album_cover"
   - `landing_background_portrait`: Same description.
2. Copy English text to all other language files (22 total):
   - `ar`, `bg`, `cz`, `de`, `el`, `es`, `fa`, `fr`, `hu`, `it`, `ja`, `nl`, `no`, `pl`, `pt`, `ru`, `sk`, `sv`, `vi`, `zh_CN`, `zh_TW`.
3. Optional: Request native-speaker translations for non-English languages (future follow-up).
4. Rebuild translation files (if using translation compiler).
5. Verify in admin UI: config descriptions display correctly.

**Commands:**
- Manual file edits.
- `php artisan test` (sanity check, no translation-specific failures).

**Exit:** All 22 language files updated, descriptions visible in admin UI.

**Estimated Duration:** 45 minutes

---

### I6 – Performance Validation (NFR-025-01, NFR-025-02)
**Goal:** Benchmark dynamic mode queries, ensure ≤100ms p95 latency.

**Preconditions:** I4 complete.

**Steps:**
1. Seed test database with 10,000 photos (mix of public/private) and 500 albums.
2. Use database query logging or profiling tool (e.g., Laravel Telescope, MySQL slow query log).
3. Call landing endpoint 100 times for each dynamic mode.
4. Measure query execution time (p50, p95, p99).
5. If queries exceed 100ms p95:
   - Verify indexes exist on `published_at`, `created_at`, `id`.
   - Add indexes if missing.
   - Consider query optimization (e.g., reduce eager loading if unnecessary).
6. Verify static URL mode has zero query overhead (string check only).
7. Document findings in Appendix: Performance Benchmarks (below).

**Commands:**
- `php artisan db:seed --class=LargePhotoSeeder` (custom seeder)
- `php artisan test --filter=DynamicBackgroundPerformanceTest` (optional custom test)
- Database profiler analysis.

**Exit:** All dynamic modes meet ≤100ms p95 target, static mode has no overhead.

**Estimated Duration:** 60 minutes

---

### I7 – Quality Gates and Documentation (NFR-025-06)
**Goal:** Pass all quality checks, update roadmap and knowledge map.

**Preconditions:** I1-I6 complete.

**Steps:**
1. Run full test suite: `php artisan test` — all tests pass.
2. Run PHPStan: `make phpstan` — zero errors.
3. Run PHP-CS-Fixer: `vendor/bin/php-cs-fixer fix` — zero changes.
4. Update `docs/specs/4-architecture/roadmap.md`:
   - Move Feature 025 from Active to Completed.
   - Add completion date and summary.
5. Update knowledge map if landing page module is documented:
   - Note that `LandingPageResource` supports dynamic background modes.
6. Update `_current-session.md` with feature summary.
7. Commit all changes with conventional commit message:
   - `feat(landing): add dynamic background modes (random/latest photo/album cover)`
   - Reference spec, plan, tasks in commit message.
   - Body includes `Spec impact: docs/specs/4-architecture/features/025-*` lines.

**Commands:**
- `php artisan test`
- `make phpstan`
- `vendor/bin/php-cs-fixer fix`
- `git add .`
- `./scripts/codex-commit-review.sh` (prepare commit message)

**Exit:** All quality gates pass, documentation updated, ready for commit.

**Estimated Duration:** 30 minutes

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-025-01 | I2, I4 / T-025-04, T-025-10 | Static mode backward compatibility |
| S-025-02 | I3, I4 / T-025-07, T-025-11 | Random mode |
| S-025-03 | I3, I4 / T-025-07, T-025-12 | Random mode fallback |
| S-025-04 | I2, I4 / T-025-05, T-025-13 | Photo ID mode with valid ID |
| S-025-05 | I2, I4 / T-025-05, T-025-14 | Photo ID mode fallback |
| S-025-06 | I3, I4 / T-025-08, T-025-15 | Latest album cover mode |
| S-025-07 | I3, I4 / T-025-08, T-025-16 | Album auto cover fallback |
| S-025-08 | I3, I4 / T-025-09, T-025-17 | Random from album mode |
| S-025-09 | I3, I4 / T-025-09, T-025-18 | Random from album fallback |
| S-025-10 | I4 / T-025-19 | Mixed modes (independent resolution) |
| S-025-11 | I4 / T-025-20 | Enum validation |
| S-025-12 | I4 / T-025-21 | No caching (fresh resolution) |
| S-025-13 | I4 / T-025-22 | Security test (no private photos leak) |

## Analysis Gate

_To be completed before implementation:_

**Checklist:**
- [ ] Spec reviewed by LycheeOrg maintainers.
- [ ] Query policy integration confirmed with existing `PhotoQueryPolicy`/`AlbumQueryPolicy` interfaces.
- [ ] Database schema reviewed (two new enum config keys needed).
- [ ] Enum value list agreed: `static|photo_id|random|latest_album_cover|random_from_album`.
- [ ] Translation strategy agreed (English first, copy to other languages, request native translations later).
- [ ] Performance target agreed (≤100ms p95 for dynamic modes).

**Findings:** (To be filled after review)

## Exit Criteria

- [ ] All 73 scenarios (S-025-01 through S-025-13) pass in integration tests.
- [ ] PHPStan level 6: zero errors.
- [ ] PHP-CS-Fixer: zero violations.
- [ ] Full test suite (`php artisan test`): all tests pass.
- [ ] Performance validated: dynamic modes ≤100ms p95, static mode zero overhead.
- [ ] Two new enum configs added with 5 mode values each.
- [ ] Translations updated for all 22 languages (4 keys: 2 mode descriptions + 2 value descriptions)ms p95, static mode zero overhead.
- [ ] Translations updated for all 22 languages.
- [ ] Roadmap, knowledge map, and `_current-session.md` updated.
- [ ] Commit prepared with conventional message and spec references.

## Follow-ups / Backlog

- [ ] **Admin UI preview:** Add live preview of landing background in settings UI (optional future enhancement).
- [ ] **Native translations:** Request translations for non-English languages from native speakers (current plan uses English text copied to all languages).
- [ ] **Caching strategy:** Consider Redis/memcached caching of resolved URLs for high-traffic sites (optional performance enhancement).
- [ ] **Background animation:** Add CSS fade-in/transition effects for landing backgrounds (optional visual enhancement).
- [ ] **Multiple backgrounds:** Support slideshow/carousel of multiple backgrounds on landing page (future feature request).

## Appendix

### Drift Gate Results

_To be filled after I7 complete._

**Test results:**
- Full test suite: PASS / FAIL (errors listed)
- PHPStan: PASS / FAIL (errors listed)
- PHP-CS-Fixer: PASS / FAIL (violations listed)

**ManuMode=`static`: verified URL displayed directly.
- [ ] Mode=`photo_id`: verified specific photo displayed.
- [ ] Mode=`random`: verified 5 requests show different photos.
- [ ] Mode=`latest_album_cover`: verified correct album cover displayed.
- [ ] Mode=`random_from_album`: verified random photo from specified album.
- [ ] Fallback: removed all public photos, verified `dist/cat.webp` displayed.
- [ ] Security: set 5 private + 5 public photos, mode=`random`fied `dist/cat.webp` displayed.
- [ ] Security: set 5 private + 5 public photos, verified private never appeared in 20 landing page loads.

**Findings:** (To be filled)

---

### Performance Benchmarks

_To be filled after I6 complete._

**Test environment:**
- Database: MySQL/PostgreSQL/SQLite
- Dataset: 10,000 photos, 500 albums (50% public, 50% private)

**Results:**

| Mode | (mode=`static`) | — | — | — | Zero query overhead |
| Photo ID (mode=`photo_id`) | — ms | — ms | — ms | Single photo fetch |
| Random (mode=`random`) | — ms | — ms | — ms | |
| Latest album cover (mode=`latest_album_cover`) | — ms | — ms | — ms | |
| Random from album (mode=`random_from_album`)s | — ms | — ms | |
| `random_album_cover` | — ms | — ms | — ms | |
| `latest_album_cover` | — ms | — ms | — ms | |

**Index verification:**
- [ ] `photos.published_at` indexed: YES / NO
- [ ] `photos.created_at` indexed: YES / NO
- [ ] `photos.id` indexed: YES / NO (primary key)
- [ ] `albums.published_at` indexed: YES / NO
- [ ] `albums.created_at` indexed: YES / NO
- [ ] `albums.id` indexed: YES / NO (primary key)

---

*Last updated: 2025-01-17*
