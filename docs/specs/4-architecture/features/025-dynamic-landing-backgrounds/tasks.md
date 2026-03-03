# Feature 025 Tasks – Dynamic Landing Background Options

_Status: Draft_  
_Last updated: 2026-03-03_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`NFR-`), and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### Increment I1 – Database Migration and Backend Stubs

- [ ] T-025-01 – Create database migration to add mode enum config keys (FR-025-01, NFR-025-04).  
  _Intent:_ Migration adds `landing_background_landscape_mode` and `landing_background_portrait_mode` enum configs with type_range enum values: `static|photo_id|random|latest_album_cover|random_from_album`. Default value: `static`.  
  _Verification commands:_  
  - `php artisan migrate`  
  - `php artisan test --filter=add_dynamic_landing_background_modes`  
  - `make phpstan`  
  _Notes:_ Use `BaseConfigMigration` class; existing `landing_background_landscape` and `landing_background_portrait` configs remain unchanged (store URL/photo_id/album_id values).

- [ ] T-025-02 – Add stub `resolveBackgroundUrl()` method in `LandingPageResource` (FR-025-01).  
  _Intent:_ Create placeholder method with signature `resolveBackgroundUrl(string $mode, string $value): string` that returns `$value` unchanged initially; will be implemented in I2/I3.  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Update constructor to fetch mode configs and call `resolveBackgroundUrl($mode, $value)` for both landscape/portrait configs.

---

### Increment I2 – Core Resolution Logic for Static and Photo ID Modes

- [ ] T-025-03 – Implement `static` mode resolution (FR-025-02, S-025-01).  
  _Intent:_ When mode is `static`, return `$value` unchanged (URL passthrough).  
  _Verification commands:_  
  - `php artisan test --filter=LandingPageResourceTest::testStaticMode`  
  - `make phpstan`  
  _Notes:_ This is the simplest case and preserves backward compatibility.

- [ ] T-025-04 – Implement `photo_id` mode resolution (FR-025-03, S-025-04, S-025-05).  
  _Intent:_ Fetch photo by ID from `$value`, return size variant URL. No public access check—admin's responsibility to select appropriate photos. Gracefully fallback to `dist/cat.webp` if photo not found (no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=LandingPageResourceTest::testPhotoIdMode`  
  - `php artisan test --filter=LandingPageResourceTest::testPhotoIdNotFound`  
  - `make phpstan`  
  _Notes:_ Do NOT use `PhotoQueryPolicy` for this mode—simple `Photo::find($value)` is sufficient. Never throw exception for missing photo.

---

### Increment I3 – Core Resolution Logic for Dynamic Photo and Album Modes

- [ ] T-025-05 – Implement `random` mode resolution (FR-025-04, NFR-025-03, NFR-025-05, S-025-02, S-025-03).  
  _Intent:_ Query public photos using `PhotoQueryPolicy`, select random photo, return size variant URL, gracefully fallback to `dist/cat.webp` if none found (no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=LandingPageResourceTest::testRandomMode`  
  - `php artisan test --filter=LandingPageResourceTest::testRandomFallback`  
  - `make phpstan`  
  _Notes:_ Use `PhotoQueryPolicy::applySearchabilityFilter($query, user: null, unlocked_album_ids: [])`. Always return valid URL.

- [ ] T-025-06 – Implement `latest_album_cover` mode resolution (FR-025-05, NFR-025-03, NFR-025-05, S-025-06, S-025-07).  
  _Intent:_ Query public albums using `AlbumQueryPolicy`, order by `published_at DESC, created_at DESC, id DESC`, extract cover ID (prefer explicit `cover_id`, fallback to `auto_cover_id_least_privilege`), return photo URL. Gracefully fallback to `dist/cat.webp` if no albums or no cover found (no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=LandingPageResourceTest::testLatestAlbumCoverMode`  
  - `php artisan test --filter=LandingPageResourceTest::testLatestAlbumCoverAutoCover`  
  - `make phpstan`  
  _Notes:_ Use `AlbumQueryPolicy::applySearchabilityFilter($query, user: null)`. Always return valid URL.

- [ ] T-025-07 – Implement `random_from_album` mode resolution (FR-025-06, NFR-025-03, NFR-025-05, S-025-08, S-025-09).  
  _Intent:_ Fetch album by ID from `$value`, verify it's public using `AlbumQueryPolicy`, select random photo from album's photos, return photo URL. Gracefully fallback to `dist/cat.webp` if album not found, not public, or has no photos (no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=LandingPageResourceTest::testRandomFromAlbumMode`  
  - `php artisan test --filter=LandingPageResourceTest::testRandomFromAlbumNotFound`  
  - `php artisan test --filter=LandingPageResourceTest::testRandomFromAlbumNotPublic`  
  - `make phpstan`  
  _Notes:_ Album must be public; photos within album can be private (album context grants access). Always return valid URL.

---

### Increment I4 – Integration Tests

- [ ] T-025-08 – Integration test: static mode backward compatibility (S-025-01, NFR-025-04).  
  _Intent:_ Set mode=`static`, value=`https://example.com/bg.jpg`, call landing endpoint, verify unchanged URL returned.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testStaticMode`  
  _Notes:_ Create `tests/Feature_v2/Landing/DynamicBackgroundTest.php` extending `BaseApiWithDataTest`.

- [ ] T-025-09 – Integration test: photo_id mode with valid photo (S-025-04).  
  _Intent:_ Create photo (public or private), set mode=`photo_id`, value=photo ID, call endpoint, verify photo URL returned.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testPhotoIdMode`  
  _Notes:_ Test both public and private photos—both should work (no access check).

- [ ] T-025-10 – Integration test: photo_id mode with invalid ID (S-025-05).  
  _Intent:_ Set mode=`photo_id`, value=non-existent ID, call endpoint, verify graceful fallback `dist/cat.webp` returned (no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testPhotoIdNotFound`  

- [ ] T-025-11 – Integration test: random mode with multiple public photos (S-025-02).  
  _Intent:_ Create 10 public photos, set mode=`random`, call endpoint 10 times, verify photo IDs vary (at least 3 different photos returned).  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testRandomMode`  
  _Notes:_ Probabilistic test; may need multiple runs.

- [ ] T-025-12 – Integration test: random mode with zero public photos (S-025-03).  
  _Intent:_ Set mode=`random` with no public photos in database, verify graceful fallback `dist/cat.webp` returned (no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testRandomFallback`  

- [ ] T-025-13 – Integration test: latest_album_cover mode (S-025-06).  
  _Intent:_ Create 5 public albums with different `published_at` dates and explicit covers, set mode=`latest_album_cover`, verify most recent album's cover returned.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testLatestAlbumCoverMode`  

- [ ] T-025-14 – Integration test: album auto cover fallback (S-025-07).  
  _Intent:_ Create public album with `auto_cover_id_least_privilege` but no explicit `cover_id`, set mode=`latest_album_cover`, verify auto cover used.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testAlbumAutoCoverFallback`  

- [ ] T-025-15 – Integration test: random_from_album mode with valid public album (S-025-08).  
  _Intent:_ Create public album with 10 photos, set mode=`random_from_album`, value=album ID, call endpoint 10 times, verify photo IDs from that album are returned and vary.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testRandomFromAlbumMode`  

- [ ] T-025-16 – Integration test: random_from_album mode with private album (S-025-09).  
  _Intent:_ Create private album with photos, set mode=`random_from_album`, value=album ID, call endpoint, verify graceful fallback `dist/cat.webp` returned (album not public, no exception thrown).  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testRandomFromAlbumNotPublic`  

- [ ] T-025-17 – Integration test: mixed landscape/portrait modes (S-025-10).  
  _Intent:_ Set landscape mode=`random`, portrait mode=`latest_album_cover`, verify both orientations resolve independently.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testMixedOrientationModes`  

- [ ] T-025-18 – Integration test: config validation for invalid mode enum (S-025-11).  
  _Intent:_ Attempt to update mode config with invalid value (e.g., `foo_bar`), verify validation error returned.  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testInvalidModeValidation`  
  _Notes:_ Type_range validation should reject non-enum values.

- [ ] T-025-19 – Integration test: no caching (fresh resolution per request) (S-025-12, FR-025-07).  
  _Intent:_ Call landing endpoint 3 times with mode=`random`, verify query executes each time (no cached result).  
  _Verification commands:_  
  - `php artisan test --filter=DynamicBackgroundTest::testNoCaching`  
  _Notes:_ Use database query logging to verify queries executed.

- [ ] T-025-20 – Security test: private photos never appear in random mode (S-025-13, NFR-025-03).  
  _Intent:_ Create 5 private photos + 5 public photos, set mode=`random`, call endpoint 100 times, assert private photo IDs never returned.  
  _Notes:_ Critical security test; must never fail.

---

### Increment I5 – Translation Updates

- [ ] T-025-21 – Update English translations for landing background mode config descriptions (FR-025-09, TRANS-025-01, TRANS-025-02).  
  _Intent:_ Add `lang/en/all_settings.php` entries for `landing_background_landscape_mode` and `landing_background_portrait_mode` with descriptions listing enum values: "static|photo_id|random|latest_album_cover|random_from_album".  
  _Verification commands:_  
  - Manual verification in admin UI.  
  - `php artisan test` (sanity check)  
  _Notes:_ Mode config descriptions explain enum options; existing value config descriptions remain unchanged.

- [ ] T-025-22 – Copy translations to all 21 other language files (FR-025-09).  
  _Intent:_ Copy English mode config description text to `ar`, `bg`, `cz`, `de`, `el`, `es`, `fa`, `fr`, `hu`, `it`, `ja`, `nl`, `no`, `pl`, `pt`, `ru`, `sk`, `sv`, `vi`, `zh_CN`, `zh_TW`.  
  _Verification commands:_  
  - Manual spot-check 3-5 language files.  
  _Notes:_ Future follow-up: request native translations from community.

---

### Increment I6 – Performance Validation

- [ ] T-025-23 – Create performance test seeder (NFR-025-01).  
  _Intent:_ Seed test database with 10,000 photos (50% public, 50% private) and 500 albums.  
  _Verification commands:_  
  - `php artisan db:seed --class=LargePhotoSeeder`  
  - Verify record counts in database.  
  _Notes:_ Create `database/seeds/LargePhotoSeeder.php` (optional; can be done manually).

- [ ] T-025-24 – Benchmark dynamic mode query latency (NFR-025-01).  
  _Intent:_ Call landing endpoint 100 times for each dynamic mode (random, latest_album_cover, random_from_album), measure query execution time (p50, p95, p99), verify ≤100ms p95.  
  _Verification commands:_  
  - Database query profiler (e.g., Laravel Telescope, MySQL slow query log).  
  - Record results in `plan.md` Appendix: Performance Benchmarks.  
  _Notes:_ If p95 >100ms, verify indexes exist and optimize queries.

- [ ] T-025-25 – Verify static mode has zero query overhead (NFR-025-02).  
  _Intent:_ Set mode=`static`, call endpoint, verify no database queries executed (aside from config fetch).  
  _Verification commands:_  
  - Database query logging.  
  - Assert query count = 0 for photo/album queries.  
  _Notes:_ Static mode should return URL without any photo/album queries.

---

### Increment I7 – Quality Gates and Documentation

- [ ] T-025-26 – Run full test suite (NFR-025-06).  
  _Intent:_ Execute all tests, verify no regressions.  
  _Verification commands:_  
  - `php artisan test`  
  _Notes:_ All tests must pass.

- [ ] T-025-27 – Run PHPStan static analysis (NFR-025-06).  
  _Intent:_ Verify type safety, zero errors at level 6.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Fix any errors before proceeding.

- [ ] T-025-28 – Run PHP-CS-Fixer code style check (NFR-025-06).  
  _Intent:_ Ensure code follows PSR-4, strict comparisons, snake_case variables, etc.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ Should report zero changes if code already compliant.

- [ ] T-025-29 – Update roadmap with Feature 025 completion.  
  _Intent:_ Move Feature 025 from Active to Completed in `docs/specs/4-architecture/roadmap.md`, add completion date and summary.  
  _Verification commands:_  
  - Manual verification of roadmap file.  
  _Notes:_ Summary: "Dynamic landing backgrounds support 5 modes (static, photo_id, random, latest_album_cover, random_from_album), enum-based config, backward compatible, 20 tests, translations for 22 languages."

- [ ] T-025-30 – Update knowledge map (if applicable).  
  _Intent:_ Note that `LandingPageResource` supports dynamic background modes with enum-based configuration.  
  _Verification commands:_  
  - Manual verification of knowledge map file.  
  _Notes:_ Optional; skip if landing page module not documented in knowledge map.

- [ ] T-025-31 – Update `_current-session.md`.  
  _Intent:_ Add Feature 025 summary to current session documentation.  
  _Verification commands:_  
  - Manual verification.  
  _Notes:_ Brief summary: "Feature 025 (Dynamic Landing Backgrounds) complete: 5 modes (enum-based config), query policy integration, 20 tests, 22 translations."

- [ ] T-025-32 – Prepare commit with conventional message.  
  _Intent:_ Stage all changes, run `./scripts/codex-commit-review.sh`, prepare commit command with spec impact references.  
  _Verification commands:_  
  - `git add .`  
  - `./scripts/codex-commit-review.sh`  
  - Review generated commit message.  
  _Notes:_ Commit message format: `feat(landing): add dynamic background modes with enum config`. Body includes `Spec impact: docs/specs/4-architecture/features/025-*` lines. Operator runs `git commit` command locally.

---

## Notes / TODOs

- **Performance:** If p95 latency exceeds 100ms during T-025-24, add indexes on `published_at`/`created_at` columns if missing.
- **Translations:** T-025-22 uses English text copied to all languages; future follow-up: request native translations from community contributors.
- **Config validation:** T-025-18 relies on type_range enum validation; existing config infrastructure should handle this automatically.
- **Size variant selection:** Photo URL resolution prefers `medium` size variant, fallback to `original` if unavailable. Adjust if UX team requests different variant.
- **Photo ID mode security:** T-025-09 tests that photo_id mode works with both public and private photos—admin's responsibility to select appropriate photos. No automatic public access enforcement.
- **Graceful fallback:** All dynamic mode resolution is graceful—no exceptions thrown for missing photos/albums. Always returns a valid URL (`dist/cat.webp` fallback).

---

*Last updated: 2025-01-17*
