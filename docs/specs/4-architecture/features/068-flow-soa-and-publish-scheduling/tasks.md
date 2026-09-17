# Feature 068 Tasks – Flow Struct-of-Arrays & Publish-Date Scheduling

_Status: Implemented (28 of 33 tasks green; T-068-11b manual browser check outstanding)_
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

- [x] T-068-01 – Write `FlowV3Test` covering S-068-01 (whole scope returned in one unpaginated
  response, album set/order parity with v2 paged to exhaustion) before the new
  route/controller/resource exist (F-068-01, F-068-02, S-068-01).
  _Verification commands:_ `php artisan test --filter=FlowV3Test` — 6/6 passed (also covers the
  auth/flag-gate distinction: unauthenticated → 401, authenticated+flag-off → 403).

- [x] T-068-02 – Add `GET /api/v3/Flow` route and `FlowListController::index()` (no `page`/cursor
  param, no `flow_max_items` cap applied — each album is its own "bucket equivalent," loaded
  whole-scope, per Decision Cards Q-068-04/Q-068-06), gated by `is_struct_of_array_enabled` via new
  `GetFlowListRequest` (mirrors `FlowRequest`'s auth exactly, plus the SoA flag) (F-068-01).
  _Verification commands:_ `vendor/bin/phpstan analyse` — clean. `Flow::do()` gained an optional
  `$with_relations = true` param (default preserves v2 byte-for-byte — confirmed via
  `FlowTest`/`FlowInitTest` regression, 10/10 passed); `FlowListController` calls `do(false)` and
  adds its own `v3_`-prefixed `base_albums`/`users`/`statistics` joins via the existing
  `AlbumQueryPolicy::joinBaseAlbumOwnerId()` helper — using `addSelect()` throughout, never the
  replacing `select()`, since `Flow::do()` may already have added its own virtual
  `is_recursive_nsfw` select (only when `hide_nsfw_in_flow` is off) that a `select()` call would
  silently drop. Discovered mid-implementation: v2's cover resolution is simply the explicit
  `cover_id` column (no auto-cover fallback, no Gate-based `should_downgrade` check) — Flow's
  `cover()` relation is `hasOne(Photo::class,'id','cover_id')`, nothing more — so `cover_ids[i]` is a
  plain column read, and `CAN_READ_METRICS`'s gate logic (`AlbumPolicy::canReadMetrics()`) is
  replicated as a plain `match` over `metrics_access`/`owner_id`/`may_administrate`, no `Gate::check()`
  needed. This confirms `toBase()`, no-Eloquent-hydration (matching `AlbumListResource`'s pattern,
  FR-068-02) is fully achievable with zero v2 behavior drift.

- [x] T-068-03 – Implement `App\Http\Resources\V3\FlowListResource` (parallel-array accumulation,
  reusing `Flow::do()` unmodified) (F-068-02).
  _Verification commands:_ `php artisan test --filter=FlowV3Test`; `vendor/bin/phpstan analyse` /
  `vendor/bin/php-cs-fixer fix --dry-run --diff` clean. Min/max-date and published-date formatting use
  native `date()`/`Safe\strtotime()`, not Carbon (NFR-068-05) — Carbon is used only for
  `diffForHumans()` (no native equivalent), mirroring `FlowItemResource`'s own existing v2 precedent
  exactly, not new Carbon usage introduced by this feature.

### I2 – Capped photo preview (`ratios` tier `limit` param)

- [x] T-068-04 – Regression-run existing `PhotoRatiosV3Test` unmodified to establish the
  byte-identical-response floor before touching `QueryPhotoRatios` (NFR-068-01).
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test` — 24/24 passed.

- [x] T-068-05 – Add `limit()` accessor + `sometimes|integer|min:1` + mutual-exclusion validation to
  `GetPhotoRatiosRequest` (F-068-03, S-068-03).
  _Verification commands:_ `vendor/bin/phpstan analyse` — clean.

- [x] T-068-06 – Extend `QueryPhotoRatios::do()` to apply `->limit($limit)` when set; whole-scope
  callers unaffected when absent (F-068-03, F-068-04, S-068-02, S-068-04). Also extended
  `CacheKeyProvider::photoRatiosScopeDigest()` with a `limit:N` digest marker and
  `PhotoChildrenController::index()` to pass `$request->limit()` through, so a limited and
  whole-scope request for the same album never collide in cache.
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test`; `vendor/bin/phpstan
  analyse` — both clean.

- [x] T-068-07 – Add new `PhotoRatiosV3Test` cases for S-068-02/S-068-03/S-068-04 (limit returns
  exactly N in effective sort order; limit+bucket_ids/photo_ids → 422; invalid limit → 422).
  _Verification commands:_ `php artisan test --filter=PhotoRatiosV3Test` — 28/28 passed;
  `vendor/bin/phpstan analyse` / `vendor/bin/php-cs-fixer fix --dry-run --diff` clean.

### I3 – Frontend v3 store + dynamically-measured virtualized rendering

- [x] T-068-08 – Add `FLOW_CAROUSEL_PHOTO_LIMIT = 12` frontend constant (DO-068-08, no config key,
  Q-068-07, defined in `FlowState.ts`). New Flow v3 store additions: `flowV3` (whole-scope
  album-level arrays, fetched once via `loadV3()`, no pagination state), `cardLoadStateV3`
  (`"idle"|"loading"|"loaded"|"failed"` per album id), `cardPhotosV3` (centralized, not
  component-local — required for S-068-15), and `requestCardPhotos(albumId)`, which no-ops if that
  album id is already `"loading"`/`"loaded"` (DO-068-06, F-068-04, F-068-05).
  _Verification commands:_ `npm run check` — exit 0. Also extended `photo-children-v3-service.ts`'s
  `PhotoRatiosScope` union with a `{limit: number}` variant and `flow-service.ts` with `getV3()`.
  Navigation links (`next_photo_id`/`previous_photo_id`, always `null` from `adaptPhotoTile()` for
  SoA-sourced tiles) are linked by array position within each card's own capped preview, mirroring
  `PhotosState.ts.rebuildNavigationLinks()`'s exact approach — required for `LigtBox.vue`'s
  next/previous to work at all on the v3 path (`photoStore.hasNext`/`hasPrevious` derive directly
  from these fields).

- [x] T-068-09 – New dynamically-measured (`measureElement`) virtualized card list in `Flow.vue`,
  rendered in a `v-else` branch alongside the (untouched) v2 template, active when
  `flowState.isFlowSoaActive`. Uses `useWindowVirtualizer` (mirrors this codebase's own established
  precedent — `AlbumListViewVirtual.vue`/`PhotoGridVirtual.vue`/`Timeline.vue` — rather than a nested
  scroll container; `Timeline.vue`'s own documented pitfall applies identically: a nested
  `overflow-y-auto` div desyncs the virtualizer's visibility calc from its own render offset, so the
  v3 branch deliberately omits the `h-svh overflow-y-auto` wrapper the v2 branch keeps). Rendering-
  window bookkeeping only — every album is already loaded; `requestCardPhotos()` fires once per card
  from that card's own `onMounted()`, which only happens once the virtualizer decides to render it,
  making mounting itself the lazy trigger (no separate IntersectionObserver needed) (F-068-05,
  NFR-068-03).
  _Verification commands:_ `npm run check` — exit 0; `npx eslint`/`npx prettier --check` — clean.

- [x] T-068-10 – New `AlbumCardV3.vue` (v3 SoA counterpart to `AlbumCard.vue`, per
  `[[project_v8_migration_scope]]` — forked, not edited in place, since v2's `AlbumCard.vue`/
  `CarouselImages.vue`/`TopImages.vue`/`HeaderImage.vue` expect a nested `photos: PhotoResource[]`
  with pre-resolved `size_variants.*.url` strings, which the v3 per-card preview fundamentally
  doesn't have). Renders images via the existing `<Thumb :album-id :photo-id type>` component (the
  established v3 Asset-endpoint wrapper — already handles its own async fetch/cache/cleanup, so no
  new image-loading plumbing was needed), reuses `Blur.vue`/`AlbumStatistics.vue`/`MiniIcon.vue`
  unmodified (F-068-04, F-068-02).
  _Verification commands:_ `npm run check` — exit 0; `npx eslint`/`npx prettier --check` — clean.
  `is_nsfws[i]` (FR-068-02's computed, config-gated value) flows through `AdaptedFlowTile.isNsfw` →
  `AlbumCardV3.vue`'s `props.album.isNsfw` → `<Blur v-if="props.album.isNsfw">`, same trigger
  mechanism as v2's `AlbumCard.vue`.
  **Manual browser verification of actual rendering/visual parity PENDING — no dev environment
  available this session.**

### I3b – Per-card photo-preview loading skeleton

- [x] T-068-11a – New `AlbumCardSkeletonV3.vue`, shown by `AlbumCardV3.vue` while
  `cardLoadStateV3[albumId]` is `"loading"`/`"idle"`; swaps to real content on `"loaded"`; on
  `"failed"`, `cardPhotosV3[albumId]` is `[]`, so neither the skeleton nor any header/top-images/
  carousel markup renders at all — an empty photo-preview section, not an infinite skeleton
  (F-068-09, NFR-068-07, S-068-15, S-068-16).
  _Verification commands:_ `npm run check` — exit 0; `npx eslint`/`npx prettier --check` — clean.

- [ ] T-068-11b – Manual verification: a card scrolled into view for the first time shows the
  skeleton then real content; a card already `"loaded"` and scrolled back into view shows real
  content immediately, never re-shows the skeleton; a simulated failed fetch resolves to an empty
  carousel, not a stuck skeleton (S-068-15, S-068-16).
  _Verification commands:_ Manual, browser devtools.
  **PENDING — no dev environment available this session.** Code-level reasoning for why this should
  hold: `cardPhotosV3`/`cardLoadStateV3` are store-level (not component-local), so remounting an
  already-`"loaded"` card's `onMounted()`→`requestCardPhotos()` call is a no-op per T-068-08's dedup
  check — but the actual DOM/visual behavior is unverified.

### I4 – Lazy-loading + description caching

- [x] T-068-12 – Add `loading="lazy"` to every `<img>` in `HeaderImage.vue`/`TopImages.vue`/
  `CarouselImages.vue` (v8 only, per `[[project_v8_migration_scope]]` — v7 untouched) (F-068-06,
  S-068-07).
  _Verification commands:_ `npm run check` — exit 0, clean. Manual devtools network-tab check
  **PENDING** — no browser available this session.

- [x] T-068-13 – Add `flowDescriptionTag($albumId)` managed-cache wrapping `Markdown::convert()` in
  `FlowItemResource` (v2) and `FlowListController` (v3); invalidate via the existing `AlbumSaved`
  event handler in `ManagedCacheAlbumListingInvalidator::handleAlbumSaved()` — this single event
  already fires from **both** `AlbumController::updateAlbum()` (single-album) and
  `BulkEditAlbumsAction::do()` (bulk-edit), so one handler covers both description-save call sites
  with no per-site wiring needed (no model hook, per `[[feedback_no_hooks_explicit_writes]]`)
  (F-068-07, S-068-08).
  _Verification commands:_ `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest` —
  24/24 passed (new `testAlbumSavedEvictsFlowDescriptionTagForSavedAlbumOnly` case);
  `php artisan test --filter="FlowV3Test|FlowTest|FlowInitTest"` — 16/16 passed; `vendor/bin/phpstan
  analyse` / `vendor/bin/php-cs-fixer fix --dry-run --diff` clean across all touched files.

### I5 – `published_at_orig_tz` column + cast wiring

- [x] T-068-14 – Confirm Q-068-01 resolved as Option A in `spec.md` and `open-questions.md` before
  proceeding.
  _Verification commands:_ None (documentation check). Confirmed 2026-09-17, owner: "obviously A."

- [x] T-068-15 – Write `PublishedAtCastTest` asserting `published_at`'s cast round-trips a
  timezone-aware instant correctly, and that dirty-checking works via the cast's already-shipped
  `compare()` method (F-068-10, F-068-11, NFR-068-05).
  _Verification commands:_ `php artisan test --filter=PublishedAtCastTest` — 4/4 passed
  (round-trip, null round-trip, reassignment-not-dirty, clear-sets-both-columns-null).

- [x] T-068-16 – New migration `2026_09_17_000001_add_published_at_orig_tz_to_base_albums.php`
  (precedent: `2025_01_24_200235_add_initial_taken_at.php`): adds `published_at_orig_tz`
  (`string(31)`, nullable) to `base_albums`; backfills `date_default_timezone_get()` for existing
  non-null `published_at` rows — documented in the migration's own docblock as a best-effort
  approximation (F-068-10).
  _Verification commands:_ `vendor/bin/phpstan analyse` — clean; exercised implicitly by every
  passing test in this session (migration runs as part of the test DB setup).

- [x] T-068-17 – Add `HasUTCBasedTimes` to `BaseAlbumImpl`'s `implements` clause; change
  `published_at`'s cast to `DateTimeWithTimezoneCast::class`; add `published_at_orig_tz`'s cast
  (F-068-11).
  _Verification commands:_ `php artisan test --filter=PublishedAtCastTest`; `vendor/bin/phpstan
  analyse` — clean.
  _Real bug found and fixed:_ `published_at` (pre-existing) and `published_at_orig_tz` were **not**
  in `BaseAlbumImpl::$attributes`'s explicit default array. `ForwardsToParentImplementation::setAttribute()`
  decides whether to forward a write to `base_class` or keep it on the child `Album` model by
  checking `array_key_exists($key, $base_class->getAttributes())` — for a freshly-constructed
  (not-yet-refetched) model, an unlisted key fails that check, so `$album->published_at = $x` was
  silently written onto the wrong model (`Album`, which has no such column), causing a `ModelDBException`
  ("no such column: published_at" against the `albums` table) the first time this feature's write
  path (T-068-21) actually exercised it. This was a latent, pre-existing gap for `published_at` too
  (nothing ever wrote to it via a fresh in-memory assignment before this feature). Fixed by adding
  both keys to `$attributes` (`null` defaults); root-caused and confirmed via a standalone debug
  script isolating the underlying `QueryException` before fixing.

### I6 – Regression guard for Landing Page / `AlbumQueryPolicy` / `Flow.php`

- [x] T-068-18 – Regression-run existing Landing-Page-related and `Flow.php`-related test suites
  unmodified (F-068-12, NFR-068-06, S-068-13).
  _Verification commands:_ `php artisan test --filter=LandingPage` — 17/17 passed
  (`LandingFeaturedItemsAutomaticTest`, `LandingFeaturedItemsManualTest`, `LandingPageContentTest`,
  `LandingPageSeGatingTest`); `php artisan test --filter=FlowTest` / `--filter=FlowInitTest` — 10/10
  passed.

- [x] T-068-19 – Diff review confirming zero changes to `AlbumQueryPolicy::joinBaseAlbumOwnerId()`
  and `Flow.php`'s existing `published_at` references.
  _Verification commands:_ `git diff --stat -- app/Policies/AlbumQueryPolicy.php
  app/Actions/Albums/Flow.php` — `AlbumQueryPolicy.php`: zero diff (only called, never edited);
  `Flow.php`: 8 insertions/2 deletions, entirely the `$with_relations` param plumbing (FR-068-01's
  own additive change), no other line touched.

### I7 – Single-album edit write path + UI

- [x] T-068-20 – Write `AlbumUpdateTest` cases for S-068-10 (set), S-068-12 (clear via `null`), plus
  an "omitting leaves existing value untouched" case (F-068-13).
  _Verification commands:_ `php artisan test --filter=AlbumUpdateTest` — 18/18 passed. (S-068-09,
  the field being entirely absent from the *form*, is a frontend UI scenario, not request-validation
  layer — covered by T-068-24 instead, per the Test Strategy correction noted in spec.md.)

- [x] T-068-21 – Add `HasPublishedAt` contract + trait, `RequestAttribute::PUBLISHED_AT_ATTRIBUTE`,
  `UpdateAlbumRequest` rule + `processValidatedValues()` wiring; wire `AlbumController::updateAlbum()`
  to apply it only `if ($request->publishedAtProvided())` (F-068-13).
  _Verification commands:_ `php artisan test --filter=AlbumUpdateTest`; `php artisan
  test --filter=UpdateAlbumRequestTest` (updated its `testRules()` expectation) — both green;
  `vendor/bin/phpstan analyse` / `vendor/bin/php-cs-fixer fix --dry-run --diff` clean.
  _Real bug found and fixed (Decision Card Q-068-08):_ first attempt used `present|nullable|date`
  (matching an earlier, untested "correction" made during the full-spec review) — running it against
  the real `AlbumUpdateTest`/`AlbumUpdateFocusTest` suites produced 11 failures, since every existing
  caller of this endpoint omits this brand-new key. Reverted to `sometimes|nullable|date` (matching
  `slug`/`tags`'s own precedent on this endpoint) and added `publishedAtProvided(): bool` so
  "not sent" and "sent as null" stay distinguishable.

- [x] T-068-22 – Add `InitConfig::$is_flow_opt_in_strategy` + `LycheeState.ts` field (F-068-14).
  _Verification commands:_ `vendor/bin/phpstan analyse` — clean; `php artisan test
  --filter=WhiteLabelInitTest` — 2/2 passed (only existing test touching `InitConfig`); `php artisan
  typescript:transform` (regenerates `resources/js/lychee.d.ts` — required after any
  `#[TypeScript()]`-annotated PHP class change, not run automatically by `npm run check`); `npm run
  check` — exit 0, clean.

- [x] T-068-23 – Extend `UpdateAbumData`/`AlbumService.updateAlbum()` (TypeScript) with optional
  `published_at` (F-068-13).
  _Verification commands:_ `npm run check` — exit 0.

- [x] T-068-24 – Add `AlbumProperties.vue`'s publish-date field (checkbox + `datetime-local` +
  timezone select, reusing the existing `timeZoneOptions` from `@/config/constants`), mirroring
  `PhotoEdit.vue`'s `taken_at` pattern, visible only when `is_model_album && is_flow_opt_in_strategy`
  is true (F-068-15, S-068-09, S-068-10, S-068-12).
  _Verification commands:_ `npm run check` — exit 0; `npx eslint` / `npx prettier --check` — clean.
  Also added `published_at` to `EditableBaseAlbumResource` (backend read path — discovered this was
  missing: the form's `load()` needs the album's *current* publish date to populate the field, which
  nothing had wired yet) with regression coverage (`AlbumHeadEndpointTest`, 15/15 passed) and
  `php artisan typescript:transform` regeneration. Default-to-"now" UX added for the first time the
  checkbox is enabled with no existing date (no EXIF-like source to pre-fill from, unlike
  `taken_at`). Added `gallery.album.properties.flow_publish_date`/`flow_publish_date_toggle`
  translation keys to `lang/en/gallery.php` and, as English placeholders, to all 21 other locales
  (`[[project_lang_files_are_php_source]]` — hand-edited PHP source, `lang:json` regenerates the
  gitignored JSON build artifacts, never hand-edited); `LangTest` — 2/2 passed.
  **Manual browser verification of the actual UI PENDING — no dev environment available this
  session.**

### I8 – Bulk-edit write path + UI

- [x] T-068-25 – Write `PatchTest` cases for S-068-11 (set same instant for multiple albums, clear
  via `null`) (F-068-16).
  _Verification commands:_ `php artisan test --filter=PatchTest` — 41/41 passed.

- [x] T-068-26 – Add `published_at` to `PatchBulkAlbumRequest`'s rules/`after()`/
  `processValidatedValues()` optional-fields lists and to `BulkAlbumPatchData`; apply it in
  `BulkEditAlbumsAction::do()`'s Group 1 mass `update()` (F-068-16).
  _Verification commands:_ `php artisan test --filter=PatchTest`; `vendor/bin/phpstan analyse` /
  `vendor/bin/php-cs-fixer fix --dry-run --diff` clean.
  _Real subtlety found and handled:_ `BulkEditAlbumsAction` applies `base_albums` changes via a raw
  `BaseAlbumImpl::query()->whereIn(...)->update([...])` mass update, which bypasses Eloquent's custom
  cast `set()` logic entirely (mass updates only ever write plain scalar values) — so
  `DateTimeWithTimezoneCast`'s split into `(published_at, published_at_orig_tz)` had to be reproduced
  manually (`(new BaseAlbumImpl())->fromDateTime($carbon)` + `$carbon->getTimezone()->getName()`),
  the same way a normal per-model save would do it. Verified via the new `PatchTest` cases, both
  green on first run.

- [x] T-068-27 – Add a new "date field" UI category to `BulkEditFieldsDialog.vue` (checkbox +
  `datetime-local` + timezone select row; new `dateFields`/`editDateValues`/`editDateTzValues`
  arrays alongside the existing `textFields`/`enumFields`/`sortingPairs`/`boolFields`, date and
  timezone tracked separately and combined into one ISO string only at submit time), visible only
  when `is_flow_opt_in_strategy` is true (F-068-17, S-068-11).
  _Verification commands:_ `npm run check` — exit 0; `npx eslint` / `npx prettier --check` — clean.
  Also extended `BulkAlbumEditService`'s `PatchPayload` type with optional `published_at`, and added
  `bulk_album_edit.field_published_at` to `lang/en/bulk_album_edit.php` + all 21 other locales as
  English placeholders; `LangTest` — 2/2 passed.
  **Manual browser verification PENDING — no dev environment available this session.**

### I9 – Strategy-toggle data-preservation check

- [x] T-068-28 – Scoped feature test (`FlowV3Test::testTogglingStrategyPreservesPublishedAtAndReordersCorrectly`):
  set opt-in with two published albums (verifying OPT_IN's `published_at desc` ordering), toggle to
  auto, confirm both albums' `published_at` survived untouched via `->fresh()`, toggle back to
  opt-in, confirm the same ordering re-emerges (F-068-18, S-068-14).
  _Verification commands:_ `php artisan test --filter=FlowV3Test` — 7/7 passed; `vendor/bin/phpstan
  analyse` / `vendor/bin/php-cs-fixer fix --dry-run --diff` clean.

### I10 – Documentation

- [x] T-068-29 – Update `docs/specs/3-reference/api-design.md` with `GET /api/v3/Flow` and the
  `ratios` tier's new `limit` param.
  _Verification commands:_ None (docs only).

- [x] T-068-30 – Update `docs/specs/4-architecture/knowledge-map.md` with Flow's SoA adoption and
  the shared `published_at`/`published_at_orig_tz` pattern.
  _Verification commands:_ None (docs only).

- [x] T-068-31 – Update `docs/specs/3-reference/frontend-gallery.md` with the Flow feed's
  dynamically-measured virtualization mechanism.
  _Verification commands:_ None (docs only).

- [x] T-068-32 – Move Feature 068's `roadmap.md` row to reflect true completion status (or split into
  separate Part A / Part B notes if the two halves shipped at different times, per Q-068-03).
  _Verification commands:_ None (docs only). Both parts shipped together in one implementation pass,
  so no split was needed.

## Notes / TODOs

- S-068-06, S-068-07, S-068-10, S-068-11 require a live browser/dev environment this authoring
  session does not have access to (`[[feedback_no_mariadb_mysql_access]]`) — each corresponding task
  above is explicitly flagged; do not mark these `[x]` on typecheck-pass alone, only after real
  manual verification, per `[[feedback_verify_before_declaring_fixed]]`.
- T-068-16's migration-testing approach is conditional on what precedent (if any) this repo has for
  testing migrations directly at implementation time — if none, code review of the `--pretend` output
  is the fallback verification.
