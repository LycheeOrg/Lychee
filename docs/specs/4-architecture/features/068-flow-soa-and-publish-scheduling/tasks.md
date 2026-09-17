# Feature 068 Tasks – Flow Struct-of-Arrays & Publish-Date Scheduling

_Status: Draft_
_Last updated: 2026-09-17_

> Keep this checklist aligned with `plan.md`'s increments. Stage tests before implementation,
> record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
> Update `roadmap.md`'s status when all tasks are done.
>
> Q-068-01 was confirmed by the feature owner as Option A (2026-09-17, "obviously A") — see
> `spec.md`'s Q-068-01 Decision Card and `open-questions.md`. Part B (I5 onward) is unblocked.

## Checklist

### I1 – New v3 `Flow` SoA listing tier (unpaginated)

- [ ] T-068-01 – Write `FlowV3Test` covering S-068-01 (whole scope returned in one unpaginated
  response, album set/order parity with v2 paged to exhaustion) before the new
  route/controller/resource exist (F-068-01, F-068-02, S-068-01).
  _Intent:_ Failing test staged first. Assert there is no `page`/cursor param accepted or needed.
  _Verification commands:_ `php artisan test --filter=FlowV3Test` (expect failure/missing route).

- [ ] T-068-02 – Add `GET /api/v3/Flow` route and controller action (no `page`/cursor param, no
  `flow_max_items` cap applied — each album is its own "bucket equivalent," loaded whole-scope, per
  Decision Cards Q-068-04/Q-068-06), gated by `is_struct_of_array_enabled` (F-068-01).
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [ ] T-068-03 – Implement `App\Http\Resources\V3\FlowListResource` (parallel-array accumulation,
  reusing `Flow::do()` unmodified) (F-068-02).
  _Verification commands:_ `php artisan test --filter=FlowV3Test`.

### I2 – Capped photo preview (`ratios` tier `limit` param)

- [ ] T-068-04 – Regression-run existing `PhotoRatiosV3Test` unmodified to establish the
  byte-identical-response floor before touching `QueryPhotoRatios` (NFR-068-01).
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`.

- [ ] T-068-05 – Add `limit()` accessor + `sometimes|integer|min:1` + mutual-exclusion validation to
  `GetPhotoRatiosRequest` (F-068-03, S-068-03).
  _Verification commands:_ `vendor/bin/phpstan analyse`.

- [ ] T-068-06 – Extend `QueryPhotoRatios::do()` to apply `->limit($limit)` when set; whole-scope
  callers unaffected when absent (F-068-03, F-068-04, S-068-02, S-068-04).
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`.

- [ ] T-068-07 – Add new `PhotoRatiosV3Test` cases for S-068-02/S-068-03/S-068-04.
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`.

### I3 – Frontend v3 store + dynamically-measured virtualized rendering

- [ ] T-068-08 – Add `FLOW_CAROUSEL_PHOTO_LIMIT = 12` frontend constant (DO-068-08, no config key,
  Q-068-07). New Flow v3 store additions: `flowV3` (whole-scope album-level arrays, fetched once on
  load, no pagination state), a per-card loading-state map (`"idle"|"loading"|"loaded"|"failed"`), a
  per-card photo-data cache keyed by album id (centralized, not component-local — required for
  S-068-15), and `requestCardPhotos(albumId, limit=FLOW_CAROUSEL_PHOTO_LIMIT)`, which no-ops if that
  album id is already cached (DO-068-06, F-068-04, F-068-05).
  _Verification commands:_ `npm run check`.

- [ ] T-068-09 – New dynamically-measured (`measureElement`) virtualized card list, replacing
  `Flow.vue`'s plain `v-for`/`TransitionGroup` when the SoA flag is on — rendering-window bookkeeping
  only (every album is already loaded), triggering `requestCardPhotos()` only for cards entering the
  visible range ± overscan (F-068-05, NFR-068-03).
  _Verification commands:_ `npm run check`.

- [ ] T-068-10 – Adapt `AlbumCard.vue`/`CarouselImages.vue`/`TopImages.vue`/`HeaderImage.vue` to
  consume the new per-card photo-preview fetch (via `ratios?limit=N` + `Asset`) instead of a nested
  `photos` array; confirm `Blur.vue`'s NSFW blur trigger still reads off `is_nsfws[i]` correctly in
  the new card path (F-068-04, F-068-02).
  _Verification commands:_ `npm run check`.

- [ ] T-068-11 – Dev-console/manual verification: `/flow` fires exactly one `GET /api/v3/Flow`
  request on load regardless of album count; scrolling renders/derenders cards by proximity without
  re-fetching album metadata; DOM node count stays bounded (S-068-06).
  _Verification commands:_ Manual, browser devtools (flag as pending if no dev environment available
  this session).

### I3b – Per-card photo-preview loading skeleton

- [ ] T-068-11a – Add skeleton/placeholder markup for a card's carousel/header area while its
  loading-state is `"loading"`; swap to real content on `"loaded"`; swap to an empty carousel (no
  infinite skeleton) on `"failed"` (F-068-09, NFR-068-07, S-068-15, S-068-16).
  _Verification commands:_ `npm run check`.

- [ ] T-068-11b – Manual verification: a card scrolled into view for the first time shows the
  skeleton then real content; a card already `"loaded"` and scrolled back into view shows real
  content immediately, never re-shows the skeleton; a simulated failed fetch resolves to an empty
  carousel, not a stuck skeleton (S-068-15, S-068-16).
  _Verification commands:_ Manual, browser devtools (flag as pending if no dev environment available
  this session).

### I4 – Lazy-loading + description caching

- [ ] T-068-12 – Add `loading="lazy"` to every `<img>` in `HeaderImage.vue`/`TopImages.vue`/
  `CarouselImages.vue` (F-068-06, S-068-07).
  _Verification commands:_ `npm run check`; manual devtools network-tab check (flag pending if no
  browser available).

- [ ] T-068-13 – Add `flowDescriptionTag($albumId)` managed-cache wrapping `Markdown::convert()` in
  `FlowItemResource`/`FlowListResource`; invalidate explicitly at **every** description-save call
  site — `UpdateAlbumRequest`'s single-album path AND `PatchBulkAlbumRequest`'s bulk-edit path both
  write `description` and must both trigger invalidation (no model hook, per
  `[[feedback_no_hooks_explicit_writes]]`) (F-068-07, S-068-08).
  _Verification commands:_ `php artisan test --filter=` (whichever suite covers album-save cache
  invalidation, found during implementation) — add a bulk-edit-specific case if the existing suite
  only covers the single-album path.

### I5 – `published_at_orig_tz` column + cast wiring

- [x] T-068-14 – Confirm Q-068-01 resolved as Option A in `spec.md` and `open-questions.md` before
  proceeding.
  _Verification commands:_ None (documentation check). Confirmed 2026-09-17, owner: "obviously A."

- [ ] T-068-15 – Write a model test asserting `published_at`'s cast round-trips a timezone-aware
  instant correctly, and that dirty-checking works via the cast's already-shipped `compare()` method
  (F-068-10, F-068-11, NFR-068-05). Note: `[[project_datetimewithtimezonecast_dirty_check_bug]]`'s
  *precision-mismatch* half doesn't apply here (only one of the two new columns is a `dateTime`); its
  *dirty-check* half (the `compare()` fix) is what this test actually exercises.
  _Verification commands:_ `php artisan test --filter=` (new/closest existing `BaseAlbumImpl`-related
  test class) — expect failure first.

- [ ] T-068-16 – New migration (precedent: `2025_01_24_200235_add_initial_taken_at.php`): add
  `published_at_orig_tz` (`string(31)`, nullable) to `base_albums`; backfill
  `date_default_timezone_get()` for existing non-null `published_at` rows — document in the migration
  itself that this is a best-effort approximation (current default timezone at migration time, not
  necessarily what was in effect historically) (F-068-10).
  _Verification commands:_ `php artisan migrate --pretend` review; scoped migration test if this
  repo has a precedent for testing migrations directly (else code review only).

- [ ] T-068-17 – Add `HasUTCBasedTimes` to `BaseAlbumImpl`'s `implements` clause; change
  `published_at`'s cast to `DateTimeWithTimezoneCast::class`; add `published_at_orig_tz` to the
  explicit `$attributes` array (F-068-11).
  _Verification commands:_ `php artisan test --filter=` (T-068-15's test class); `vendor/bin/phpstan
  analyse`.

### I6 – Regression guard for Landing Page / `AlbumQueryPolicy` / `Flow.php`

- [ ] T-068-18 – Regression-run existing Landing-Page-related and `Flow.php`-related test suites
  unmodified (F-068-12, NFR-068-06, S-068-13).
  _Verification commands:_ Existing suites' `--filter=` runs (exact class names found during
  implementation).

- [ ] T-068-19 – Diff review confirming zero changes to `AlbumQueryPolicy::joinBaseAlbumOwnerId()`
  and `Flow.php`'s existing `published_at` references.
  _Verification commands:_ `git diff --stat -- app/Policies/AlbumQueryPolicy.php
  app/Actions/Albums/Flow.php` — expect no unintended changes beyond this feature's own additions.

### I7 – Single-album edit write path + UI

- [ ] T-068-20 – Write `UpdateAlbumRequestTest` cases for S-068-09 (field absent under `auto` —
  N/A at the request-validation layer, covered instead by the frontend task T-068-24), S-068-10
  (set), S-068-12 (clear via `null`) before implementing (F-068-13).
  _Verification commands:_ `php artisan test --filter=UpdateAlbumRequestTest` — expect failure first.

- [ ] T-068-21 – Add `HasPublishedAt` contract + trait, `RequestAttribute::PUBLISHED_AT_ATTRIBUTE`,
  `UpdateAlbumRequest` rule (`present|nullable|date` — matches this endpoint's own convention, not
  `sometimes`) + `processValidatedValues()` wiring (F-068-13).
  _Verification commands:_ `php artisan test --filter=UpdateAlbumRequestTest`; `vendor/bin/phpstan
  analyse`.

- [ ] T-068-22 – Add `InitConfig::$is_flow_opt_in_strategy` + `LycheeState.ts` field (F-068-14).
  _Verification commands:_ `vendor/bin/phpstan analyse`; `npm run check`.

- [ ] T-068-23 – Extend `UpdateAbumData`/`AlbumService.updateAlbum()` (TypeScript) with
  `published_at` (F-068-13).
  _Verification commands:_ `npm run check`.

- [ ] T-068-24 – Add `AlbumProperties.vue`'s publish-date field (checkbox + `datetime-local` +
  timezone select), mirroring `PhotoEdit.vue`'s `taken_at` pattern, visible only when
  `is_flow_opt_in_strategy` is true (F-068-15, S-068-09, S-068-10, S-068-12).
  _Verification commands:_ `npm run check`; manual verification of both strategy states (flag
  pending if no browser available this session).

### I8 – Bulk-edit write path + UI

- [ ] T-068-25 – Write `PatchBulkAlbumRequestTest` cases for S-068-11 before implementing (F-068-16).
  _Verification commands:_ `php artisan test --filter=PatchBulkAlbumRequestTest` — expect failure
  first.

- [ ] T-068-26 – Add `published_at` to `PatchBulkAlbumRequest`'s rules/`after()`/
  `processValidatedValues()` optional-fields lists and to `BulkAlbumPatchData` (F-068-16).
  _Verification commands:_ `php artisan test --filter=PatchBulkAlbumRequestTest`; `vendor/bin/phpstan
  analyse`.

- [ ] T-068-27 – Add a new "date field" UI category to `BulkEditFieldsDialog.vue` (checkbox +
  `datetime-local` + timezone select row; new `dateFields`/`editDateValues` array alongside the
  existing `textFields`/`enumFields`/`sortingPairs`/`boolFields`), visible only when
  `is_flow_opt_in_strategy` is true (F-068-17, S-068-11).
  _Verification commands:_ `npm run check`; manual verification (flag pending if no browser
  available this session).

### I9 – Strategy-toggle data-preservation check

- [ ] T-068-28 – Manual (or scoped feature test) check: set opt-in, set a publish date, toggle to
  auto, toggle back to opt-in, confirm the date survived and ordering is correct (F-068-18,
  S-068-14).
  _Verification commands:_ Manual, or a new scoped PHPUnit test exercising the same sequence.

### I10 – Documentation

- [ ] T-068-29 – Update `docs/specs/3-reference/api-design.md` with `GET /api/v3/Flow` and the
  `ratios` tier's new `limit` param.
  _Verification commands:_ None (docs only).

- [ ] T-068-30 – Update `docs/specs/4-architecture/knowledge-map.md` with Flow's SoA adoption and
  the shared `published_at`/`published_at_orig_tz` pattern.
  _Verification commands:_ None (docs only).

- [ ] T-068-31 – Update `docs/specs/3-reference/frontend-gallery.md` with the Flow feed's
  dynamically-measured virtualization mechanism.
  _Verification commands:_ None (docs only).

- [ ] T-068-32 – Move Feature 068's `roadmap.md` row to reflect true completion status (or split into
  separate Part A / Part B notes if the two halves shipped at different times, per Q-068-03).
  _Verification commands:_ None (docs only).

## Notes / TODOs

- S-068-06, S-068-07, S-068-10, S-068-11 require a live browser/dev environment this authoring
  session does not have access to (`[[feedback_no_mariadb_mysql_access]]`) — each corresponding task
  above is explicitly flagged; do not mark these `[x]` on typecheck-pass alone, only after real
  manual verification, per `[[feedback_verify_before_declaring_fixed]]`.
- T-068-16's migration-testing approach is conditional on what precedent (if any) this repo has for
  testing migrations directly at implementation time — if none, code review of the `--pretend` output
  is the fallback verification.
