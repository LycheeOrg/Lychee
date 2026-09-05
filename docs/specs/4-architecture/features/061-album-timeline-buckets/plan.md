# Feature Plan 061 – Album Timeline Bucket Aggregation

_Linked specification:_ `docs/specs/4-architecture/features/061-album-timeline-buckets/spec.md`
_Status:_ Completed
_Last updated:_ 2026-08-29

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Let a future frontend feature render accurate sticky date headers, size a virtual-scroll container, render every subalbum tile for an album's direct children, and know instantly what a right-click/multi-select context menu can offer — all **before** hydrating a single Eloquent model, evaluating a date-truncation function per row, or firing a query on any interaction event — at the confirmed real-world scale of 7,000+ direct children under one parent. This feature ships three endpoints: `GET /api/v3/Albums/{album_id}/children/buckets` (bucket counts + labels), `GET /api/v3/Albums/{album_id}/children` (per-child render data), and `GET /api/v3/Albums/{album_id}/children/rights` (background-fetched permission signals). Success is measured by: all three endpoints completing in low milliseconds against a 7,000-child fixture (buckets endpoint index-served aggregate confirmed via `EXPLAIN`; children endpoint a single join-free `toBase()` query confirmed via query-log; the rights endpoint's per-child grant flags matching direct `AlbumPolicy` calls exactly even under overlapping group grants); the buckets/children endpoints' totals summing exactly to what `AlbumChildrenController::get()` would paginate over for the same caller; and the rights endpoint requiring zero additional queries at the moment of any right-click or multi-select.

## Scope Alignment

- **In scope:** One new `albums.bucket_id` column + composite index; `RecomputeAlbumStatsJob` extended to populate it from the album's *parent's* resolved sort column and granularity, with a `TITLE`-specific branch on the new instance-wide `title_bucket_mode`/`title_bucket_prefix_length` config — `OWNER_ID`-sorted parents' children get `bucket_id = null`, never computed; new `RecomputeChildAlbumBucketsJob` for the parent-`album_sorting_col`/`album_sorting_order`/`album_timeline`-changed case; new backfill Artisan command (also the sole mechanism for propagating a `title_bucket_mode`/`title_bucket_prefix_length` config change, since those are instance-wide only); `GET /api/v3/Albums/{album_id}/children/buckets` (request/resource/controller/route), gated by `STRUCT_OF_ARRAY_ENABLED`, including a read-time-computed `labels` field that reuses `TimelineData`'s existing `timeline_album_date_format_*` config keys, independently implemented (not a shared code path); `GET /api/v3/Albums/{album_id}/children` (request/resource/controller/route), same flag gate, single flat join-free `toBase()` query, whole-album-at-once, `cover_id`-only (no thumbnail type/placeholder), SQL-truncated `description`, each child's own `bucket_id` (`"unknown"` for `null`) as the join key back to the buckets endpoint; `GET /api/v3/Albums/{album_id}/children/rights` (request/resource/controller/route), same flag gate, background-fetched, whole-album-at-once — one whole-response `owner_id` + `can_delete_children`/`can_move_children` (uniform across all direct children, cheap), plus per-child `grants_edit`/`grants_download` via a `LEFT JOIN` against `AlbumQueryPolicy::getComputedAccessPermissionSubQuery()`, `GROUP BY`/`MAX()`-aggregated to correctly handle overlapping group grants, with explicit admin-bypass replication; caching for all three endpoints via existing `ManagedCacheService`/`CacheKeyProvider` infrastructure, reusing the existing `albumChildrenTag()` invalidation net.
- **Out of scope:** Any frontend consumer of any endpoint (including the actual background-fetch wiring and client-side rights-combination logic for the rights endpoint); a tier-3 (pixel) endpoint (already exists — Feature 056's Asset endpoint, unchanged); `bucket_id`-windowed pagination for the children endpoint (whole-album-at-once for now); `OWNER_ID` bucketing (non-bucketable — direct siblings always share one owner, so it can never produce more than one bucket); full `AlbumRightsResource` parity on the rights endpoint (scoped to "core menu rights"); the combined `can_*` boolean computation itself (server ships raw `grants_*` signals only); root-scope (`album_id` omitted) bucketing; photo-side bucket columns/endpoint; changes to `Timeline.php::dates()`, `TimelineData`, or the `album_timeline` `DEFAULT`/`DISABLED` resolution quirk.

## Dependencies & Interfaces

- `App\Jobs\RecomputeAlbumStatsJob` (extend, do not replace).
- `App\Listeners\RecomputeAlbumStatsOnAlbumChange`/`RecomputeAlbumStatsOnPhotoChange` (existing dispatch sites — no changes needed, the job itself grows).
- `App\Http\Resources\Traits\HasTimelineData::getAlbumTimeline()` (reused, unchanged, for granularity resolution).
- `App\Models\Album::getEffectiveAlbumSorting()` (reused, for resolving *which* source a parent's `bucket_id` computation reads).
- `App\Http\Resources\Models\Utils\TimelineData::parseDateFromTitle()` (reused, unchanged, for the `title` source case).
- `App\Policies\AlbumQueryPolicy::applyVisibilityFilter()`, `App\Repositories\AlbumRepository` (query-scoping precedent).
- `App\Services\Cache\ManagedCacheService`/`CacheKeyProvider` (Feature 052/053).
- `App\Http\Resources\Rights\ModulesRightsResource::$is_struct_of_array_enabled` (Feature 058 flag, reused as-is).
- `routes/api_v3.php`, `App\Enum\ColumnSortingType`, `App\Enum\TimelineAlbumGranularity`.
- `App\Enum\TitleBucketMode` (new, DO-061-06); `App\Repositories\ConfigManager` (reused, for the new instance-wide `title_bucket_mode`/`title_bucket_prefix_length` keys — CFG-061-01/02).
- `App\Http\Controllers\Gallery\AlbumListController::resolveCoverId()` (Feature 057, reused unchanged for the children endpoint's `cover_id` resolution — FR-061-14).
- `App\Http\Resources\V3\AlbumListResource`/`AlbumListController` (Feature 057, direct query-construction template for the children endpoint's join-free `toBase()` query).
- `timeline_album_date_format_year`/`_month`/`_day` config keys (existing, read via `configs()->getValueAsString()`, same keys `TimelineData::fromAlbum` already reads). Reused as *config values only*, not as a shared code path — `AlbumBucketController`'s `labels` computation is an independent implementation.
- `Actions\Album\Create::set_parent()`/`Move`/`Merge`/`Transfer` + `Album::fixOwnershipOfChildren()` (existing, referenced not modified — the ownership-uniformity invariant `OWNER_ID`-exclusion relies on).
- `App\Policies\AlbumQueryPolicy::getComputedAccessPermissionSubQuery()`/`joinSubComputedAccessPermissions()` (`AlbumQueryPolicy.php:519-609`, existing, reused with a real `$user` instead of the `null`/public-only case its one current call site — `AlbumListController.php:88` — passes).
- `App\Policies\AlbumPolicy::canDelete()` (`AlbumPolicy.php:281-308`, referenced not modified — its parent-scoped `AccessPermission` query is mirrored, not called, for FR-061-20).
- `App\Policies\AlbumQueryPolicy`'s existing admin-bypass pattern (`applyVisibilityFilter()`/`applyReachabilityFilter()`, lines 62-64/177-179) — replicated for the rights endpoint, since `getComputedAccessPermissionSubQuery()` has no admin-awareness of its own.

## Assumptions & Risks

- **Assumptions:** The 7,000-direct-children and (deferred) 700k-photo scale figures already confirmed in `virtual-scrolling-study.md` remain the operative scale targets; `HasTimelineData::getAlbumTimeline()`'s existing resolution semantics (including the `DEFAULT`/`DISABLED` quirk) are correct-as-shipped and not to be "fixed" as a side effect of this feature.
- **Risks / Mitigations:**
  - *Risk:* `RecomputeAlbumStatsJob` growing a fourth responsibility (`bucket_id`, alongside counts/dates/covers) makes it a larger single point of failure. *Mitigation:* keep the bucket computation in a clearly separated private method within the job (mirrors its existing `computeTakenAtRange`/`computeMaxPrivilegeCover` decomposition), so a bucket-computation bug cannot silently break count/cover computation or vice versa.
  - *Risk:* `RecomputeChildAlbumBucketsJob` (FR-061-03) at 7,000+ children must be a single bulk `UPDATE`, not 7,000 individual model saves. *Mitigation:* explicit NFR-level code-review check before this task is marked done; a per-child-row loop is a correctness bug for this feature's whole premise, not just a style nit.
  - *Risk:* Two independent parent attributes (`album_sorting_col`/`album_sorting_order` and `album_timeline`) must both trigger `RecomputeChildAlbumBucketsJob` — easy to wire only one and miss the other. *Mitigation:* a single shared dirty-attribute check covering all three attribute names, tested independently for each (T-061-05).
  - *Risk:* Multi-driver (sqlite/mysql/pgsql) index/migration syntax divergence, same class of issue `Timeline.php::dates()` already has to special-case. *Mitigation:* the new migration only adds plain columns + plain composite indexes (no computed/generated columns, no driver-specific `DATE_FORMAT` expressions) — this sidesteps the multi-driver problem entirely, unlike the live-query approach it replaces.
  - *Risk:* `title_bucket_mode`/`title_bucket_prefix_length` being instance-wide-only means a config change silently does nothing to existing rows until the FR-061-04 backfill command is manually re-run — easy for an admin (or a future settings-UI feature) to change the config and assume it took effect immediately. *Mitigation:* this mirrors the existing `sorting_albums_col`/`timeline_albums_granularity` instance-default behavior exactly (S-061-16), so no new operational surprise is introduced; still worth an explicit log/warning line in the config-update path pointing at the backfill command, flagged for I5/I10 rather than assumed.
  - *Risk:* `alphabetical` mode's bucket sizes can skew heavily for real-world title text (e.g. many albums starting "Vacation..."), same as the pre-existing "busy month" skew for date buckets. *Mitigation:* none needed for correctness — a large bucket is still a valid, contiguous, fetchable chunk — but worth a one-line callout in the eventual frontend-adoption feature's docs so consumers don't assume near-uniform bucket sizes.
  - *Risk:* The children endpoint's whole-album-at-once fetch shape returns all 7,000+ children's metadata in one response for the largest confirmed parent, which is a real payload-size tradeoff against the incremental-fetch benefit virtual scrolling otherwise provides. *Mitigation:* explicitly accepted as a "for now" decision (see Follow-ups) — a SoA response at 13 fields × 7,000 rows is still small relative to today's fully-hydrated v2 page-by-page responses; re-visit with real payload numbers once I7 lands rather than pre-optimizing here.
  - *Risk:* `AlbumQueryPolicy::getComputedAccessPermissionSubQuery(full: true, ...)` applies no internal `GROUP BY` — a caller belonging to multiple groups with separate grants on the same child can produce duplicate joined rows with different flag values if the rights endpoint's own query doesn't aggregate them. *Mitigation:* `GROUP BY` child id, `MAX()` each `grants_*` column (FR-061-21) — verified against direct `AlbumPolicy` calls for the overlap case (NFR-061-09).
  - *Risk:* The rights endpoint's cache tag (`albumChildrenTag($album_id)`) was designed around child add/delete/move/sort-order changes, never a permission-grant change — an unverified assumption here would silently serve stale permission data after a share/unshare. *Mitigation:* FR-061-22 requires this be explicitly verified (and fixed if missing) during I9, not assumed.

## Implementation Drift Gate

Run the Analysis Gate checklist (`docs/specs/5-operations/analysis-gate-checklist.md`) once this plan and `tasks.md` exist, before I1 begins. Record the outcome under "Analysis Gate" below. Run the Implementation Drift Gate section of the same checklist once all tasks are `[x]` and record findings before marking this feature Complete.

## Increment Map

1. **I1 – Schema: `bucket_id` column + index**
   - _Goal:_ Add the one nullable `bucket_id` column and its composite index to `albums`.
   - _Preconditions:_ Spec approved (this plan committed).
   - _Steps:_ Write migration (up/down); confirm it runs clean on sqlite/mysql/pgsql test configs.
   - _Commands:_ `php artisan migrate`, `php artisan migrate:rollback` (both directions on all three drivers per CI matrix).
   - _Exit:_ FR-061-01 satisfied; `make phpstan` clean on the migration file.

2. **I2 – Write path: `RecomputeAlbumStatsJob` bucket population**
   - _Goal:_ Populate `bucket_id` whenever the job already runs.
   - _Preconditions:_ I1 merged.
   - _Steps:_ Add a private `computeBucket(Album $album): ?string` method resolving the album's *parent's* effective sort column (source) and, for `CREATED_AT`/`MIN_TAKEN_AT`/`MAX_TAKEN_AT`, granularity, reading the matching source off the album's own row, and truncating (`Y`/`Y-m`/`Y-m-d` format, matching `TimelineData`'s `time_date`, so the buckets endpoint can re-parse it for `labels`); for `TITLE`, branch on the new instance-wide `title_bucket_mode` config between the existing date-parse path and a new `title_base`-prefix path (`title_bucket_prefix_length` characters); for `OWNER_ID`, return `null` unconditionally — not bucketable, never computed; wire into `handle()` alongside the existing `save()`; unit tests first (root album, non-root album with explicit parent overrides, non-root album with parent on `DEFAULT`/global default, `TITLE` in both `date_prefix` and `alphabetical` modes, unparseable title, no dated photos, `OWNER_ID` always yielding `null`).
   - _Commands:_ `php artisan test --filter=RecomputeAlbumStatsJob`.
   - _Exit:_ FR-061-02 satisfied; S-061-12/13 pass.

3. **I3 – Write path: `RecomputeChildAlbumBucketsJob` + parent-setting-change triggers**
   - _Goal:_ Cover the two propagation directions I2 doesn't (parent's own `album_sorting_col`/`album_sorting_order` or `album_timeline` changing).
   - _Preconditions:_ I2 merged.
   - _Steps:_ New job, single bulk `UPDATE` over direct children (see Risks); shared dirty-attribute check covering all three attribute names + dispatch at the existing `AlbumController.php` write site; tests first (each of the three attributes changed independently, an unrelated attribute change not triggering it, zero-children no-op).
   - _Commands:_ `php artisan test --filter=RecomputeChildAlbumBucketsJob`.
   - _Exit:_ FR-061-03 satisfied; S-061-14/15 pass; code review confirms single bulk `UPDATE` (Risks mitigation).

4. **I4 – Backfill Artisan command**
   - _Goal:_ Full-table recompute, `albums`-only.
   - _Preconditions:_ I2 merged (reuses its computation logic, factored to be callable from both the job and the command).
   - _Steps:_ New command; assert zero `photos`-table queries via query-log assertion in its test; test coverage includes a re-run after changing `title_bucket_mode`/`title_bucket_prefix_length` correctly updating existing `TITLE`-sorted albums' `bucket_id` (S-061-22 — this command is the *only* propagation path for those two configs, since they carry no per-album trigger); tests first.
   - _Commands:_ `php artisan test --filter=RecomputeAlbumBuckets`.
   - _Exit:_ FR-061-04/CLI-061-01/FR-061-11 satisfied; NFR-061-03 verified.

5. **I5 – Read path: `GET /api/v3/Albums/{album_id}/children/buckets`**
   - _Goal:_ Request/Resource/Controller/Route, flag-gated, cached.
   - _Preconditions:_ I1 merged (column must exist to query); I2 ideally merged too so tests have real data to group.
   - _Steps:_ `GetAlbumBucketsRequest` (`album_id` bound from the route segment via `prepareForValidation()`, mirroring `GetPhotoAssetRequest`'s pattern rather than `GetAlbumChildrenRequest`'s query-string one); `AlbumBucketResource` (SoA, including `labels`); `AlbumBucketController::index()` (`toBase()` `GROUP BY bucket_id` query, `"unknown"` sentinel for `NULL`, `bucketable: false` short-circuit derived from the parent's own sort-column setting — no column-selection logic needed at read time, and `OWNER_ID` always takes this branch; when bucketable, a post-aggregation PHP step computes `labels[i]` per distinct `bucket_id` — `Carbon::parse()->format($timeline_album_date_format_*)` for date-granularity/`date_prefix`-`TITLE` sources, verbatim passthrough for `alphabetical`-`TITLE` and `"unknown"`); `CacheKeyProvider::albumBucketsKey()`; route registration (`GET /Albums/{album_id}/children/buckets`) with flag gate; tests first (S-061-01..11, S-061-31..33).
   - _Commands:_ `php artisan test --filter=AlbumBucketsV3Test`.
   - _Exit:_ FR-061-05..10/18 satisfied; NFR-061-01/02/04/05 verified (code review + `EXPLAIN` check + key-uniqueness unit test + confirmation that `labels` computation is bounded by bucket count, not row count).

6. **I6 – Caching/invalidation verification**
   - _Goal:_ Confirm the shared `albumChildrenTag()` reuse actually invalidates correctly with zero new listener code.
   - _Preconditions:_ I5 merged.
   - _Steps:_ Tests exercising S-061-16/17/18 against the real cache-tag wiring (not mocked), confirming no new listener needed.
   - _Commands:_ `php artisan test --filter=AlbumBucketsV3Test`.
   - _Exit:_ FR-061-08 fully verified; if reuse turns out incomplete, escalate to open-questions.md rather than silently adding new listener wiring outside this plan.

7. **I7 – Read path: `GET /api/v3/Albums/{album_id}/children`**
   - _Goal:_ Request/Resource/Controller/Route for the per-direct-child render-data endpoint, flag-gated, cached, single flat join-free query.
   - _Preconditions:_ I1 not required (this endpoint does not touch `bucket_id`'s computation); can start in parallel with I2–I6, but shares the flag gate and cache-tag pattern I5 establishes, so sequencing after I5 avoids duplicating that groundwork.
   - _Steps:_ `GetAlbumChildrenDataRequest` (mirrors `GetAlbumBucketsRequest`, DO-061-01, including its route-segment `album_id` binding); `AlbumChildrenDataResource` (SoA, DO-061-08, field list per FR-061-12/17 — including `bucket_id`, `null` mapped to `"unknown"`); `AlbumChildrenDataController::index()` — single `Album::query()->where('parent_id', '=', $album_id)->select([...])->toBase()->get()` (select list includes `bucket_id` alongside every other field — zero extra query), `SUBSTRING` for `description` (FR-061-13), `cover_id` resolved via `AlbumListController::resolveCoverId()`'s exact logic (FR-061-14), **zero joins** (NFR-061-07); `CacheKeyProvider::albumChildrenDataKey()`; route registration (`GET /Albums/{album_id}/children`, registered before its `/buckets` child route in `routes/api_v3.php`) with flag gate; tests first (S-061-23..30).
   - _Commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`.
   - _Exit:_ FR-061-12..17 satisfied; NFR-061-07 verified (code review + query-log assertion of exactly one query, zero joins); NFR-061-08 verified (mixed-visibility fixture test, S-061-23); S-061-30 passes (buckets/children endpoint `bucket_id` correlation).

8. **I9 – Read path: `GET /api/v3/Albums/{album_id}/children/rights`**
   - _Goal:_ Request/Resource/Controller/Route for the background-fetched permission-signal endpoint, flag-gated, cached, with correct group-grant aggregation and admin bypass.
   - _Preconditions:_ None from I1–I7 (touches neither `bucket_id` nor the children endpoint's resource) — can be built in parallel, sequenced last here only because it shares I5/I7's flag-gate/cache-tag groundwork.
   - _Steps:_ `GetAlbumChildrenRightsRequest` (mirrors DO-061-01/07's route-segment `album_id` binding); `AlbumChildrenRightsResource` (`owner_id`, `can_delete_children`, `can_move_children`, `ids`, two per-child `grants_edit`/`grants_download` arrays — `grants_upload`/`grants_full_photo_access` deliberately excluded, neither right is offered by the right-click menu on a selection of albums — DO-061-10); `AlbumChildrenRightsController::index()` — `AlbumQueryPolicy::applyVisibilityFilter()` applied with the same semantics as the other two endpoints (NFR-061-11); resolve `album_id` once (gets `owner_id` for free), one `exists()` query for `can_delete_children`/`can_move_children` (mirrors `AlbumPolicy::canDelete`'s parent-scoped query verbatim, FR-061-20), one `LEFT JOIN` against `AlbumQueryPolicy::getComputedAccessPermissionSubQuery(full: true, user: $currentUser)` **grouped by child id, `MAX()`-aggregating each `grants_*` column** (FR-061-21 — the `GROUP BY`/`MAX()` is not optional, see NFR-061-09), admin callers short-circuit to all-`true` without either query running (NFR-061-10); `CacheKeyProvider::albumChildrenRightsKey()`; route registration (`GET /Albums/{album_id}/children/rights`) with flag gate; tests first (S-061-34..41).
   - _Commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`.
   - _Exit:_ FR-061-19..23 satisfied; NFR-061-09 verified (multi-group-overlap fixture test, S-061-37, cross-checked against direct `AlbumPolicy` calls for the same child/caller); NFR-061-10 verified (admin fixture + query-log assertion the join/`exists()` never runs, S-061-36); NFR-061-11 verified (mixed-visibility fixture test, S-061-41); FR-061-22's cache-invalidation gap explicitly resolved one way or the other (new listener, or documented accepted staleness) before this increment closes — not left ambiguous.

9. **I10 – Documentation**
   - _Goal:_ Satisfy Documentation Deliverables.
   - _Preconditions:_ I1–I9 merged.
   - _Steps:_ Update `api-design.md`, `database-schema.md`, `knowledge-map.md`, `roadmap.md`; confirm the `virtual-scrolling-study.md` cross-reference is current.
   - _Commands:_ N/A (docs only).
   - _Exit:_ All Documentation Deliverables checked off.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-061-01 | I5 / T-061-1x | Flag-off 403. |
| S-061-02..05 | I5 / T-061-1x | Per-source `bucket_id` grouping + `"unknown"` sentinel. |
| S-061-06 | I5 / T-061-1x | `OWNER_ID` → `bucketable: false`, `bucket_id` never computed. |
| S-061-07..09 | I5 / T-061-1x | 404/403 branches. |
| S-061-10 | I5 / T-061-1x | Zero-children case. |
| S-061-11 | I5 / T-061-1x | Depth-≥2 subalbum, no special-casing. |
| S-061-12 | I2 / T-061-0x | Photo add/remove propagation. |
| S-061-13 | I2 / T-061-0x | Move changes effective parent (source and/or granularity). |
| S-061-14 | I3 / T-061-0x | Parent's own `album_timeline` change. |
| S-061-15 | I3 / T-061-0x | Parent's own `album_sorting_col` change. |
| S-061-16 | I4 / T-061-0x | Global sort/granularity-config change + backfill. |
| S-061-17..19 | I6 / T-061-2x | Cache hit/invalidation/identity-isolation. |
| S-061-20..21 | I2 / T-061-0x | `title_bucket_mode = alphabetical`, `title_base`-prefix bucketing, contiguity with row order. |
| S-061-22 | I4 / T-061-0x | `title_bucket_mode`/`title_bucket_prefix_length` config change + backfill re-run. |
| S-061-23 | I7 / T-061-19 | Children-endpoint field-list/parity check against v2's total visible child set (NFR-061-08). |
| S-061-24 | I7 / T-061-20 | SQL-side `description` truncation (query-log assertion, not just response shape). |
| S-061-25 | I7 / T-061-21 | `cover_id` three-tier resolution + no-cover case; no `type`/`placeholder` in payload. |
| S-061-26 | I7 / T-061-18 | Flag-off 403. |
| S-061-27..28 | I7 / T-061-22 | Zero-children 200; 404/403 branches — mirrors S-061-01/09/10. |
| S-061-29 | I7 / T-061-25 | Children-endpoint cache hit + shared-tag invalidation — mirrors S-061-17/18. |
| S-061-30 | I7 / T-061-27 | Buckets/children endpoint `bucket_id` correlation — grouping children by `bucket_id` reproduces the buckets endpoint's `{bucket_ids, counts}` exactly (FR-061-17). |
| S-061-31 | I5 / T-061-12a | `labels[i]` matches `TimelineData`'s own Carbon-formatting output for the same config/date, for a non-default `timeline_album_date_format_month` (FR-061-18). |
| S-061-32 | I5 / T-061-12a | `alphabetical`-mode `TITLE`: `labels[i]` equals `bucket_ids[i]` verbatim. |
| S-061-33 | I5 / T-061-12a | `"unknown"` entry's `labels[i]` is the literal string, never Carbon-parsed. |
| S-061-34 | I9 / T-061-30 | Per-child `grants_edit` varies correctly; whole-response `owner_id`/`can_delete_children`/`can_move_children` identical regardless. |
| S-061-35 | I9 / T-061-30 | `can_delete_children`/`can_move_children` from a grant on the parent itself, not any child. |
| S-061-36 | I9 / T-061-32 | Admin caller → all rights `true`, join/`exists()` query never runs (NFR-061-10). |
| S-061-37 | I9 / T-061-31 | Multi-group overlapping grants on one child → `MAX()`-merged correctly, matches direct `AlbumPolicy` calls (NFR-061-09). |
| S-061-38 | I9 / T-061-32 | Guest caller → public-only grants. |
| S-061-39 | I9 / T-061-28 | Flag-off 403. |
| S-061-40 | I9 / T-061-35 | Cache hit + permission-change invalidation (or documented staleness gap). |
| S-061-41 | I9 / T-061-37 | Mixed-visibility parity — rights-endpoint `ids` match `AlbumChildrenController::get()`'s visible set (NFR-061-11). |

## Analysis Gate

**Run 2026-08-29, against `docs/specs/5-operations/analysis-gate-checklist.md`'s pre-implementation section, against the current 37-task state (I1–I10).** Result: **PASS**.

1. **Specification completeness** — PASS. FR/NFR/scenario tables populated for all three endpoints; no UI-impacting work (backend-contract-only, Non-Goals).
2. **Open questions review** — PASS. No blocking `Open` entries for this feature in `open-questions.md`. No architecturally-significant decision here lacks an ADR reference beyond what's already cited (ADR-0009 for SoA shape).
3. **Plan alignment** — PASS. Plan references the correct spec/tasks files; increment/task cross-references verified against the actual task IDs in `tasks.md`.
4. **Tasks coverage** — PASS. Every FR maps to at least one task; every scenario maps to at least one task via the Scenario Tracking table above. Tests-first ordering holds throughout; increments stay bite-sized.
5. **Constitution compliance** — PASS. No planned work bypasses spec-first/test-first; increments stay near-straight-line (e.g. the children endpoint's query stays a single flat `toBase()` call per NFR-061-07, no branching reintroduced).
6. **Tooling readiness** — PASS. Verification commands present on every task.

No remaining blocking items. Cleared to begin I1.

*(This is the pre-implementation gate result, dated 2026-08-29, kept as the historical record it is — all increments I1–I12 have since completed; see the Implementation Drift Gate section immediately below for the post-implementation verification, and the "Completed" status field at the top of this document.)*

## Implementation Drift Gate

**I5 (T-061-13), run 2026-08-29** against a disposable 7,000-child sqlite fixture (copy of the test DB, not the shared test database — no test pollution): `EXPLAIN QUERY PLAN` for `AlbumBucketController::queryBuckets()`'s actual query shape (`WHERE parent_id = ? GROUP BY bucket_id ORDER BY (bucket_id IS NULL) ASC, bucket_id ASC`) returns:

```
QUERY PLAN
|--SEARCH albums USING COVERING INDEX albums_parent_id_bucket_id_index (parent_id=?)
`--USE TEMP B-TREE FOR ORDER BY
```

Confirms NFR-061-01: the `parent_id` filter and `GROUP BY bucket_id` are served entirely by the composite index (`COVERING INDEX`), never a table scan or a per-row date-truncation function. The `TEMP B-TREE FOR ORDER BY` sorts only the already-aggregated result set (bounded by distinct-bucket count, ~24 in this fixture — never by the 7,000-row fixture size), which is expected and outside NFR-061-01's scope. `->toBase()->get()` is used in `AlbumBucketController::queryBuckets()` (`app/Http/Controllers/Gallery/AlbumBucketController.php`), confirmed by code review (NFR-061-02).

**I7 (T-061-24), run 2026-08-29**: query-log capture of a full `GET /Albums/{album_id}/children` request against `AlbumChildrenDataController::queryChildren()` shows exactly one query against `albums` for the endpoint's own data fetch (every other logged query is pre-existing request-pipeline overhead — config bootstrap, the `GetAlbumChildrenDataRequest`'s own album resolution/`Gate::check` authorization, `Auth::user()`'s eager-loaded relations — none of it issued by `queryChildren()` itself). That one query carries exactly the two joins `AlbumQueryPolicy::applyVisibilityFilter()` always adds (`base_albums`, `computed_access_permissions`) and zero joins added by this feature (NFR-061-07). Confirms `is_nsfw` needed widening `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s existing `base_albums` subselect by one column (`app/Policies/AlbumQueryPolicy.php`) rather than adding a third join — verified against the full v2 `AlbumRepository`/`Top`/`Flow`/`AlbumSearch` consumers of that shared method (41 tests, all passing) to confirm no v2 regression from widening a shared join's column list.

## Exit Criteria

- All FR/NFR rows in `spec.md` map to at least one passing test.
- Full `php artisan test` suite green; `make phpstan` clean; `vendor/bin/php-cs-fixer fix --dry-run` clean.
- `EXPLAIN`/`EXPLAIN ANALYZE` evidence attached to the Implementation Drift Gate report confirming NFR-061-01 (index-served aggregate) against a 7,000-child fixture.
- Query-log evidence attached to the Implementation Drift Gate report confirming NFR-061-07 (the children endpoint is exactly one join-free query) against the same fixture.
- Multi-group-overlap fixture evidence (S-061-37) attached to the Implementation Drift Gate report confirming NFR-061-09 (per-child `grants_*` values match direct `AlbumPolicy` calls exactly, including the overlap case); admin-fixture query-log evidence confirming NFR-061-10 (join/`exists()` skipped entirely for admin callers); mixed-visibility fixture evidence (S-061-41) confirming NFR-061-11.
- FR-061-22's cache-invalidation gap explicitly resolved (new listener wired, or staleness window documented) — not left as an open question at completion.
- Documentation Deliverables (I10) complete.
- Implementation Drift Gate report recorded in this plan.

## Follow-ups / Backlog

- **`bucket_id`-windowed children-endpoint pagination**: the children endpoint ships whole-album-at-once for now. If a 7,000-child parent's payload proves too large in practice, add a `bucket_id` query param (using the buckets endpoint's own bucket labels as the window key) rather than redesigning the endpoint — a "for now" decision, not a settled one.
- **Frontend adoption feature** (mirrors Feature 058): wire sticky date headers + virtual-scroll windowing to the buckets/children endpoints, background-fetch the rights endpoint right after the children endpoint renders, implement the client-side owner/grant-combination logic this spec deliberately leaves server-side-unimplemented, and stop calling the v2 paginated listing once all three are adopted — the actual retirement step for `AlbumChildrenController`/`GetAlbumChildrenRequest`/`AlbumRepository::getChildrenPaginated()`. **Drafted as Feature 063** (`docs/specs/4-architecture/features/063-album-timeline-buckets-adoption/`); drafting it surfaced that the children endpoint had shipped with zero row ordering at all — fixed at the source here instead (I12, FR-061-26), rather than compensating for it client-side in 063.
- **Full `AlbumRightsResource` parity on the rights endpoint**: scoped to "core menu rights" for now — `can_make_purchasable`, `can_import_from_server`, the AI-vision face-permission fields, and `can_upload`/`can_access_original` are all excluded, the last two specifically because the right-click menu on a selection of albums doesn't offer either action. Re-open any of them only if a future context-menu design actually adds that action; not tracked as a default expansion.
- **`access_permissions`-change cache invalidation for the rights endpoint**: FR-061-22/T-061-34 requires this be verified (and fixed if missing) during I9 itself, not deferred — listed here only as a pointer in case it's ever found to need follow-up work beyond I9's scope (e.g. a genuinely new event/listener class, not just wiring an existing one).
- **Cover media `type` / blur `placeholder` for the children endpoint**: dropped outright, not deferred — no video badge, no blur-up placeholder for the virtual-scrolled album grid. Not tracked as a follow-up to re-add; re-open only if a future feature actively requests it.
- Root-scope (`album_id` omitted) bucketing — needs its own query design against `Actions\Albums\Top`'s ownership-partitioned, mixed-album-type result set; also blocks full v2 retirement, since `Top::queryRootAlbums()` has no v3 replacement yet either.
- Photo-side bucket columns/endpoint (deferred per `virtual-scrolling-study.md`).
- Investigate whether `album_timeline`'s `DISABLED` override should actually suppress bucketing for that parent's children (currently resolves identically to `DEFAULT` per `HasTimelineData::getAlbumTimeline()`) — flagged, not fixed, by this feature.
