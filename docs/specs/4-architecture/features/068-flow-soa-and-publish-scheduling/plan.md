# Feature Plan 068 – Flow Struct-of-Arrays & Publish-Date Scheduling

_Linked specification:_ `docs/specs/4-architecture/features/068-flow-soa-and-publish-scheduling/spec.md`
_Status:_ Draft
_Last updated:_ 2026-09-17

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs
> from `spec.md` where relevant, log any new high- or medium-impact questions in
> [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications
> are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

Flow reaches feature/performance parity with this app's other SoA-converted listings while gaining
a genuinely new capability (a user-settable, timezone-aware publish date driving
`flow_strategy=opt-in`). Success signals: (1) a v3 Flow page's response size no longer scales with
album photo counts; (2) the Flow feed's live DOM node count stays bounded while scrolling; (3) Flow
images respect viewport-proximity lazy-loading; (4) an album owner can set/clear a publish date from
both the single-album and bulk-edit surfaces, visible only under `flow_strategy=opt-in`; (5) the v2
Flow path and Landing Page's independent use of `published_at` are provably untouched throughout.

**Blocking dependency (resolved):** Q-068-01 (reuse `published_at` vs. introduce a new column) was
confirmed by the feature owner as Option A ("obviously A", 2026-09-17) — reuse and extend
`published_at` in place. Part B implementation may proceed; see Analysis Gate below.

## Scope Alignment

- **In scope:** New v3 `Flow` SoA listing tier + `FlowListResource`; `ratios` tier's additive `limit`
  param; per-card lazy Asset-based photo previews; dynamically-measured virtualized rendering;
  `loading="lazy"` on all Flow images; Markdown-description caching; `published_at_orig_tz` column +
  `DateTimeWithTimezoneCast` wiring on `BaseAlbumImpl`; `published_at` write path on both
  `UpdateAlbumRequest` and `PatchBulkAlbumRequest`/`BulkAlbumPatchData`; `InitConfig.is_flow_opt_in_strategy`;
  publish-date UI on `AlbumProperties.vue` and `BulkEditFieldsDialog.vue`; flag/strategy gating
  throughout; documentation updates.
- **Out of scope:** Removing/altering v2 Flow; renaming `published_at`; changing Landing Page's
  ordering behavior beyond zero-diff verification; any change to `flow_strategy=auto`; a "publish
  now" one-click action; bucket-windowing or deep-linking (Flow has neither concept).

## Dependencies & Interfaces

- Feature 062 (`AlbumListResource`) — pattern precedent for `FlowListResource`'s manual per-field
  accumulation style, not reused code (Flow's fields are unrelated to album-listing fields).
- Feature 064/065/066 (`GetPhotoRatiosRequest`/`QueryPhotoRatios`, `PhotoAssetController`,
  `PhotoGridVirtual.vue`'s general virtualization approach) — extended (`limit` param) or used as a
  pattern reference, not forked.
- `App\Casts\DateTimeWithTimezoneCast`, `App\Models\Extensions\UTCBasedTimes`,
  `App\Contracts\Models\HasUTCBasedTimes` — existing, reused unmodified; `BaseAlbumImpl` gains the
  interface implementation (trait already present).
- `App\Http\Resources\GalleryConfigs\InitConfig` / `LycheeState.ts` — extended with one new boolean.
- Existing managed-cache subsystem (`ManagedCacheService`) — extended with a new description-cache
  tag.
- `[[feedback_avoid_carbon_server_side]]`, `[[feedback_no_hooks_explicit_writes]]`,
  `[[feedback_no_full_test_suite]]`, `[[feedback_no_mariadb_mysql_access]]`,
  `[[feedback_no_defensive_dead_code_docs]]`, `[[project_datetimewithtimezonecast_dirty_check_bug]]`,
  `[[feedback_verify_before_declaring_fixed]]` — all apply throughout.

## Assumptions & Risks

- **Assumptions:** `BaseAlbumImpl`'s already-`use`d `UTCBasedTimes` trait fully satisfies
  `HasUTCBasedTimes`'s two methods with no adaptation needed (to verify in I5). The installed
  `@tanstack/vue-virtual` version's `measureElement` dynamic-sizing mode is sufficient for Flow's
  variable-height cards without a custom estimation heuristic (to verify in I3).
- **Risks / Mitigations:**
  - *Risk:* Markdown-description caching (FR-068-07) adds invalidation-correctness complexity for
    comparatively low payoff if Flow's description rendering cost proves negligible in practice.
    *Mitigation:* treat as the lowest-priority increment (I4); safe to defer/drop without blocking
    any other increment if profiling shows it isn't worth it.
  - *Risk:* No local dev/browser environment available in the authoring session — all
    scroll-behavior/lazy-loading/date-field UX must be manually verified later, not assumed correct
    from typechecks alone (mirrors Features 063/065/066/067's own documented gap).

## Implementation Drift Gate

Before marking any increment complete, re-read the touched files' current state (not just the diff
being authored) and confirm: (a) no Eloquent model events/mutators were introduced for derived state
(`[[feedback_no_hooks_explicit_writes]]`); (b) no Carbon import was introduced in any *new* backend
code (NFR-068-05 — the existing cast/trait machinery's own internal Carbon usage is unaffected); (c)
`published_at`/`published_at_orig_tz` share the same explicit datetime precision digit, per
`[[project_datetimewithtimezonecast_dirty_check_bug]]`'s lesson; (d) `make phpstan` /
`vendor/bin/php-cs-fixer fix --dry-run --diff` / `npm run check` are clean for every changed file
before moving to the next increment, not batched at the end. Record any drift found and its fix
directly in this file's increment entries, not a separate log.

## Increment Map

1. **I1 – New v3 `Flow` SoA listing tier**
   - _Goal:_ FR-068-01, FR-068-02.
   - _Preconditions:_ None (first code increment; independent of Q-068-01).
   - _Steps:_ New `GET /api/v3/Flow` route; new `Gallery\FlowListController` (or extend
     `FlowController` with a `v3Index()` method — decide during implementation which keeps
     `FlowController` smaller); new `App\Http\Resources\V3\FlowListResource` reusing
     `Flow::do()` unmodified, mapping the same album set into parallel arrays (mirrors
     `AlbumListResource`'s coding style). Tests first: new `FlowV3Test` covering S-068-01.
   - _Commands:_ `php artisan test --filter=FlowV3Test`, `vendor/bin/phpstan analyse`,
     `vendor/bin/php-cs-fixer fix --dry-run --diff`.
   - _Exit:_ `GET /api/v3/Flow` returns the same album set/order as v2, minus nested photos.

2. **I2 – Capped photo preview (`ratios` tier `limit` param)**
   - _Goal:_ FR-068-03, FR-068-04, NFR-068-01.
   - _Preconditions:_ None (independent of I1; reuses existing Feature 064 machinery).
   - _Steps:_ `GetPhotoRatiosRequest` gains `limit()` + validation (mutually exclusive with
     `bucket_ids`/`photo_ids`, mirrors Feature 066's `prohibits` pattern); `QueryPhotoRatios::do()`
     applies `->limit($limit)` when set, whole-scope callers unaffected when absent. Regression-run
     existing `PhotoRatiosV3Test` unmodified first to establish the floor, then add S-068-02/S-068-03/
     S-068-04 cases.
   - _Commands:_ `php artisan test --filter=PhotoRatiosV3Test`, `vendor/bin/phpstan analyse`.
   - _Exit:_ Capped preview requests return exactly `limit` photos; whole-scope callers byte-identical.

3. **I3 – Frontend v3 store + dynamically-measured virtualized rendering**
   - _Goal:_ FR-068-05, NFR-068-03.
   - _Preconditions:_ I1, I2.
   - _Steps:_ New v3 store additions (DO-068-06) fetching `flowV3` (album-level arrays) and, per
     card, `requestCardPhotos(albumId, limit)`; new virtualized card list using
     `@tanstack/vue-virtual`'s `measureElement` mode (not `PhotoGridVirtual.vue`'s analytic mode);
     card components (`AlbumCard.vue`, `CarouselImages.vue`, `TopImages.vue`, `HeaderImage.vue`)
     adapted to consume the new per-card photo-preview fetch instead of a nested `photos` array.
   - _Commands:_ `npm run check`.
   - _Exit:_ Dev-console/manual verification: scrolling loads/unloads cards by proximity; DOM node
     count stays bounded (flagged pending if no browser available this session).

4. **I4 – Lazy-loading + description caching (flag-independent quick wins)**
   - _Goal:_ FR-068-06, FR-068-07, NFR-068-04.
   - _Preconditions:_ None (applies to both v2 and v3 rendering paths; can land any time, including
     before I1–I3).
   - _Steps:_ Add `loading="lazy"` to every `<img>` in `HeaderImage.vue`/`TopImages.vue`/
     `CarouselImages.vue`; add `flowDescriptionTag($albumId)` cache wrapping
     `Markdown::convert()` in `FlowItemResource`/`FlowListResource`, invalidated on album `description`
     save (extend the existing album-save cache-invalidation listener, no new hook — per
     `[[feedback_no_hooks_explicit_writes]]`, call explicitly at the existing save call site).
   - _Commands:_ `npm run check`; `php artisan test --filter=` (whichever suite covers album-save
     cache invalidation).
   - _Exit:_ Network tab confirms lazy-loading (flagged pending if no browser available this
     session); repeated Flow fetches for an unchanged album skip re-running `Markdown::convert()`.

5. **I5 – `published_at_orig_tz` column + cast wiring**
   - _Goal:_ FR-068-10, FR-068-11, FR-068-12, NFR-068-05, NFR-068-06.
   - _Preconditions:_ None (Q-068-01 confirmed as Option A by the feature owner, 2026-09-17).
   - _Steps:_ New migration: add `published_at_orig_tz` (`string(31)`, nullable) to `base_albums`,
     backfill `date_default_timezone_get()` for existing non-null `published_at` rows; add
     `HasUTCBasedTimes` to `BaseAlbumImpl`'s `implements` clause; change `published_at`'s cast to
     `DateTimeWithTimezoneCast::class`; add `published_at_orig_tz` to the explicit `$attributes`
     array. Tests first: a `BaseAlbumImplTest`/extension of existing model tests verifying the cast
     round-trips correctly and dirty-checking works across the precision-parity lesson from
     `[[project_datetimewithtimezonecast_dirty_check_bug]]`.
   - _Commands:_ `php artisan test --filter=BaseAlbumImplTest` (or the actual closest existing test
     class name, found during implementation), `vendor/bin/phpstan analyse`.
   - _Exit:_ Existing `published_at` reads/writes (Flow, Landing Page, `AlbumQueryPolicy`) unchanged;
     new cast correctly round-trips a timezone-aware instant.

6. **I6 – Regression guard for Landing Page / `AlbumQueryPolicy` / `Flow.php`**
   - _Goal:_ FR-068-12, NFR-068-06.
   - _Preconditions:_ I5.
   - _Steps:_ Regression-run existing Landing-Page-related tests and `Flow.php`-related tests
     unmodified; diff-review `AlbumQueryPolicy::joinBaseAlbumOwnerId()` (expect zero changes).
   - _Commands:_ Existing Landing Page/Flow test suites' `--filter=` runs.
   - _Exit:_ S-068-13 passes; zero diff in the three untouched call sites.

7. **I7 – Single-album edit write path + UI**
   - _Goal:_ FR-068-13, FR-068-14, FR-068-15.
   - _Preconditions:_ I5.
   - _Steps:_ `HasPublishedAt` contract + trait, `RequestAttribute::PUBLISHED_AT_ATTRIBUTE`,
     `UpdateAlbumRequest` rule (`sometimes|nullable|date`) + `processValidatedValues()` wiring;
     `InitConfig::$is_flow_opt_in_strategy` + `LycheeState.ts` field; `AlbumProperties.vue`'s new
     publish-date field (checkbox + `datetime-local` + timezone select), mirroring `PhotoEdit.vue`'s
     `taken_at` pattern; `UpdateAbumData`/`AlbumService.updateAlbum()` extended with `published_at`.
     Tests first: `UpdateAlbumRequestTest` cases for S-068-09, S-068-10, S-068-12.
   - _Commands:_ `php artisan test --filter=UpdateAlbumRequestTest`, `vendor/bin/phpstan analyse`,
     `npm run check`.
   - _Exit:_ Owner can set/clear a publish date via the single-album edit form when
     `flow_strategy=opt-in`; field absent when `auto`.

8. **I8 – Bulk-edit write path + UI**
   - _Goal:_ FR-068-16, FR-068-17.
   - _Preconditions:_ I7 (reuses `is_flow_opt_in_strategy`, mirrors the same field UX).
   - _Steps:_ `PatchBulkAlbumRequest`/`BulkAlbumPatchData` gain `published_at` (validation +
     optional-fields lists); `BulkEditFieldsDialog.vue` gains a new "date field" UI category
     (first of its kind in this dialog — new `dateFields`/`editDateValues` array alongside the
     existing `textFields`/`enumFields`/`sortingPairs`/`boolFields`). Tests first:
     `PatchBulkAlbumRequestTest` cases for S-068-11.
   - _Commands:_ `php artisan test --filter=PatchBulkAlbumRequestTest`, `npm run check`.
   - _Exit:_ Bulk-editing N albums with a publish date opts all N in at the same instant; row absent
     when `auto`.

9. **I9 – Strategy-toggle data-preservation check**
   - _Goal:_ FR-068-18.
   - _Preconditions:_ I5–I8.
   - _Steps:_ Manual/scripted check: set `flow_strategy=opt-in`, set a publish date on an album,
     toggle to `auto`, toggle back to `opt-in`, confirm the date survived and the album re-appears
     correctly ordered.
   - _Commands:_ Manual (or a scoped feature test exercising the same sequence).
   - _Exit:_ S-068-14 passes.

10. **I10 – Documentation**
    - _Goal:_ Documentation Deliverables in spec.md.
    - _Preconditions:_ I1–I9 complete (or Part A/Part B completed independently — see Q-068-03;
      documentation may be split into two passes if the two halves ship at different times).
    - _Steps:_ Update `api-design.md`, `knowledge-map.md`, `frontend-gallery.md`; move Feature 068's
      roadmap.md row to reflect true completion status.
    - _Commands:_ None (docs only).
    - _Exit:_ All docs updated; roadmap.md reflects true completion status.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-068-01 | I1 | New v3 Flow tier, album set/order parity. |
| S-068-02 | I2 | `limit` param, exact count. |
| S-068-03 | I2 | Mutual-exclusion validation. |
| S-068-04 | I2 | Whole-scope regression guard. |
| S-068-05 | I3 | v2 fallback, flag off. |
| S-068-06 | I3 | Bounded DOM, scroll up/down. |
| S-068-07 | I4 | Lazy-loading. |
| S-068-08 | I4 | Description-cache invalidation. |
| S-068-09 | I7 | Field absent under `auto`. |
| S-068-10 | I7 | Single-album set. |
| S-068-11 | I8 | Bulk set. |
| S-068-12 | I7 | Clear via `null`. |
| S-068-13 | I6 | Landing Page regression guard. |
| S-068-14 | I9 | Strategy-toggle data preservation. |

## Analysis Gate

Q-068-01 confirmed by the feature owner as Option A (2026-09-17) — recorded in `spec.md`'s Q-068-01
Decision Card and `open-questions.md` (marked resolved). No remaining blocker for I5 onward; the
full increment map (I1–I10) may proceed.

## Exit Criteria

- All 10 increments' exit criteria met (or Part A's I1–I4/I10 alone, if Part B ships separately per
  Q-068-03).
- `vendor/bin/phpstan analyse` and `vendor/bin/php-cs-fixer fix --dry-run --diff` clean across all
  touched backend files.
- `npm run check` clean across all touched frontend files.
- All new/changed scoped PHPUnit test filters pass; existing `PhotoRatiosV3Test` and Landing-Page-
  related suites regression-pass unmodified.
- Manual browser verification of S-068-06, S-068-07, S-068-10, S-068-11 completed (or explicitly
  flagged as outstanding, not silently assumed) before the feature is marked Complete in
  `roadmap.md`.
- Documentation Deliverables (I10) applied.

## Follow-ups / Backlog

- Revisit whether Markdown-description caching (I4/FR-068-07) is worth its invalidation complexity
  once real Flow usage/traffic patterns are observed (flagged as a Risk above).
- Consider a "publish now" one-click convenience action for the publish-date field, if requested
  after this feature ships (explicitly out of scope here).
- If Q-068-01 is instead resolved as Option B/C, this plan's I5–I9 need a full rewrite against a real
  new `flow_datetime` column, and Landing Page's ordering scope must be separately re-planned.
