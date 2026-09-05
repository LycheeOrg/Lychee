# Feature 061 Tasks – Album Timeline Bucket Aggregation

_Status: Completed_
_Last updated: 2026-08-29_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-061-`) inside the same parentheses immediately after the task title (omit categories that do not apply).

> Scope note (2026-08-29): this feature now ships tiers 1 and 2 of the album virtual-scroll backend together (`GET /Albums/{album_id}/children/buckets` and `GET /Albums/{album_id}/children`) — plus a third, background-fetched permission-signal endpoint (`GET /Albums/{album_id}/children/rights`) for right-click/multi-select context menus. Tier 3 needs no task; it's Feature 056's existing Asset endpoint, unchanged.

## Checklist

### I1 – Schema

- [x] T-061-01 – Migration: add `bucket_id` (nullable string) to `albums`, plus composite `(parent_id, bucket_id)` index (F-061-01).
  _Intent:_ Schema-only change, no data population yet.
  _Verification commands:_
  - `php artisan migrate` / `php artisan migrate:rollback` (sqlite, mysql, pgsql)
  - `make phpstan`
  _Notes:_ No generated/computed columns — one plain column only, per plan.md Risks (multi-driver simplicity) (single column, not four).

### I2 – RecomputeAlbumStatsJob bucket population

- [x] T-061-02 – Unit tests for parent-governed source *and* granularity resolution: root album (global defaults for both), non-root album with parent's explicit sort-column/timeline overrides, non-root album with parent on `DEFAULT`/global default, `OWNER_ID` parent sort column → `bucket_id` always `null` (F-061-02, S-061-12, S-061-13).
  _Intent:_ Tests-first for the parent-governed resolution rule — note this now covers *which source* as well as granularity, not granularity alone.
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsJob`

- [x] T-061-03 – Unit tests for truncation correctness and `NULL` cases: unparseable title (`date_prefix` mode) → `bucket_id = null`; no dated photos (when parent sorts by `min`/`max_taken_at`) → `bucket_id = null`; `OWNER_ID` parent sort column → `bucket_id = null` unconditionally, not computed at all (F-061-02).
  _Intent:_ Tests-first for the single `bucket_id` value's correctness across all source/null cases.
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsJob`

- [x] T-061-04 – Implement `RecomputeAlbumStatsJob::computeBucket()` and wire into `handle()` (F-061-02).
  _Intent:_ Make T-061-02/03 pass.
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumStatsJob`; `make phpstan`

### I3 – RecomputeChildAlbumBucketsJob

- [x] T-061-05 – Unit tests: changing a parent's `album_timeline` **or** `album_sorting_col`/`album_sorting_order` recomputes all direct children's `bucket_id` in one bulk `UPDATE`; zero-children parent is a no-op; unrelated attribute changes on the parent do NOT trigger this job (F-061-03, S-061-14, S-061-15).
  _Intent:_ Tests-first, including the "must be one bulk UPDATE, not N saves" check via query-log assertion, and independent coverage of all three triggering attributes.
  _Verification commands:_ `php artisan test --filter=RecomputeChildAlbumBucketsJob`

- [x] T-061-06 – Implement `RecomputeChildAlbumBucketsJob` and the shared dirty-attribute dispatch (covering `album_timeline`, `album_sorting_col`, `album_sorting_order`) at `AlbumController.php`'s write site (F-061-03).
  _Intent:_ Make T-061-05 pass.
  _Verification commands:_ `php artisan test --filter=RecomputeChildAlbumBucketsJob`; `make phpstan`

### I4 – Backfill command

- [x] T-061-07 – Test: new Artisan command recomputes `bucket_id` for every album against a fixture, and issues zero queries against `photos`/`photo_album` (F-061-04, NFR-061-03, S-061-16).
  _Intent:_ Tests-first, including the query-log assertion for NFR-061-03.
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumBuckets`

- [x] T-061-08 – Implement the command, factoring shared computation logic with T-061-04 rather than duplicating it (CLI-061-01).
  _Intent:_ Make T-061-07 pass.
  _Verification commands:_ `php artisan test --filter=RecomputeAlbumBuckets`; `make phpstan`

### I5 – Read endpoint

- [x] T-061-09 – Feature test: flag-off → 403 regardless of caller rights (F-061-09, S-061-01).
  _Intent:_ Tests-first for the flag gate.
  _Verification commands:_ `php artisan test --filter=AlbumBucketsV3Test`

- [x] T-061-10 – Feature tests: `GROUP BY bucket_id` grouping for each parent sort-column case (`created_at`/`min_taken_at`/`max_taken_at`/`title`) including the `"unknown"` sentinel for `NULL` rows (F-061-05/07, S-061-02..05); assert the returned `bucket_ids` array is plain chronological `ORDER BY bucket_id <dir>` with `"unknown"` always last, for a `title`-sorted parent, confirming no dependency on `SortingDecorator`/PHP natural sort.
  _Intent:_ Tests-first for the core grouping behaviour — the endpoint itself has no per-source branching, so these tests exercise different parent fixtures, not different code paths. The ordering assertion specifically guards against accidentally reusing `SortingDecorator` for bucket-list order.
  _Verification commands:_ `php artisan test --filter=AlbumBucketsV3Test`

- [x] T-061-11 – Feature tests: `OWNER_ID` sort column → `bucketable: false`, `{bucket_ids: [], counts: [], labels: []}`, no `GROUP BY` ever runs; `TagAlbum`/`PersonAlbum`/unknown `album_id` → 404; no-access `album_id` → 403; zero-children parent → empty arrays; depth-≥2 subalbum works identically (F-061-06, S-061-06..11).
  _Intent:_ Tests-first for edge branches.
  _Verification commands:_ `php artisan test --filter=AlbumBucketsV3Test`

- [x] T-061-12a – Feature tests: `labels[i]` matches `Carbon::parse(bucket_ids[i])->format($timeline_album_date_format_*)` for date-granularity/`date_prefix`-`TITLE` sources under a non-default format config; `alphabetical`-`TITLE` `labels[i]` equals `bucket_ids[i]` verbatim; `"unknown"` entry's `labels[i]` is the literal string, never `Carbon`-parsed (F-061-18, S-061-31..33).
  _Intent:_ Tests-first for the new `labels` field, independent of the grouping tests above.
  _Verification commands:_ `php artisan test --filter=AlbumBucketsV3Test`

- [x] T-061-12 – Implement `GetAlbumBucketsRequest`, `AlbumBucketResource` (including `labels`), `AlbumBucketController::index()` (`bucketable: false` short-circuit for `OWNER_ID`; post-aggregation `labels` computation), `CacheKeyProvider::albumBucketsKey()`, and the flag-gated route registration (F-061-05..10/18).
  _Intent:_ Make T-061-09..11/12a pass.
  _Verification commands:_ `php artisan test --filter=AlbumBucketsV3Test`; `make phpstan`

- [x] T-061-13 – `EXPLAIN`/`EXPLAIN ANALYZE` verification against a 7,000-child fixture confirming an index-served aggregate (NFR-061-01), and confirm `toBase()` is used (NFR-061-02).
  _Intent:_ NFR verification, not a new automated test (documented in the Implementation Drift Gate report).
  _Verification commands:_ Manual `EXPLAIN` run, results pasted into plan.md's Implementation Drift Gate section.

### I6 – Caching

- [x] T-061-14 – Feature tests: repeat-request cache hit; child add/delete/move invalidates via the shared `albumChildrenTag()`; cross-identity isolation (F-061-08, NFR-061-05, S-061-17..19).
  _Intent:_ Verify zero-new-listener-wiring claim against real cache tags, not mocks.
  _Verification commands:_ `php artisan test --filter=AlbumBucketsV3Test`

- [x] T-061-15 – Unit test: `CacheKeyProvider::albumBucketsKey()` uniqueness across a (guest, user A, user B) × (2+ distinct `album_id`s) matrix (NFR-061-05).
  _Intent:_ Mirrors Feature 053/057's existing key-uniqueness test pattern. Key is `(album_id, user)` only — no sort-column dimension needed since it's implied by `album_id`.
  _Verification commands:_ `php artisan test --filter=CacheKeyProvider`

### I7 – Read path: `GET /api/v3/Albums/{album_id}/children` (tier 2)

- [x] T-061-18 – Feature test: flag-off → 403 regardless of caller rights (F-061-16, S-061-26).
  _Intent:_ Tests-first for the flag gate, mirrors T-061-09.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-19 – Feature test: response's parallel arrays match the FR-061-12/17 field list exactly (including `bucket_id`, with `null` sources surfaced as literal `"unknown"`, never raw `null`), one entry per visible direct child; for a fixture with mixed-visibility children, the set of `ids` returned equals the set `AlbumChildrenController::get()` would paginate over for the same caller (F-061-12, F-061-17, NFR-061-08, S-061-23).
  _Intent:_ Tests-first for the core field-list/parity behaviour and the visibility-filter security requirement.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-27 – Feature test: for the same `(album_id, caller)`, group tier 2's `ids` by their `bucket_id` entries and assert the resulting `{group => count}` map exactly matches tier 1's `{bucket_ids, counts}` — across each bucketable source (`CREATED_AT`/`MIN_TAKEN_AT`/`MAX_TAKEN_AT`/both `TITLE` modes) at least once (`OWNER_ID` excluded, non-bucketable), and confirm a `null`-source child lands under `"unknown"` in both responses identically (F-061-17, S-061-30).
  _Intent:_ Tests-first for the tier1/tier2 correlation contract — this is what makes the two endpoints composable rather than two independent datasets. Depends on `AlbumBucketsV3Test`'s per-source fixtures already existing (I5).
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-20 – Feature test: a child with a >100-char `description` is truncated to exactly 100 chars, asserted via query-log inspection (not just response shape) to confirm SQL-side `SUBSTRING` truncation, not PHP-side (F-061-13, S-061-24).
  _Intent:_ Tests-first, guards against silently moving truncation to PHP.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-21 – Feature test: `cover_id` resolution across all three priority tiers plus a no-cover child, matching `AlbumListResource::cover_ids`' existing resolution for the same children; response contains no `type`/`placeholder` field anywhere (F-061-14, S-061-25).
  _Intent:_ Tests-first for cover resolution and the explicit type/placeholder removal.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-22 – Feature tests: zero-children parent → 200 with empty arrays; unresolvable `album_id` → 404; no-access `album_id` → 403 (F-061-12, S-061-27, S-061-28).
  _Intent:_ Tests-first for edge branches, mirrors T-061-11.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-23 – Implement `GetAlbumChildrenDataRequest`, `AlbumChildrenDataResource` (including `bucket_ids`, with `null` mapped to `"unknown"` before serialization — F-061-17), `AlbumChildrenDataController::index()` (single flat `toBase()` query with `bucket_id` in the select list alongside every other field — zero extra query, zero joins; `AlbumQueryPolicy::applyVisibilityFilter()` applied — easy to drop while keeping the query "flat", guard against this specifically — `SUBSTRING` truncation, `resolveCoverId()` reuse), `CacheKeyProvider::albumChildrenDataKey()`, and the flag-gated route registration (F-061-12..17, NFR-061-08).
  _Intent:_ Make T-061-18..22/27 pass.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`; `make phpstan`

- [x] T-061-24 – Query-log verification confirming the endpoint issues exactly one query with zero joins (NFR-061-07).
  _Intent:_ NFR verification, not a new automated test (documented in the Implementation Drift Gate report), mirrors T-061-13.
  _Verification commands:_ Manual query-log capture, results pasted into plan.md's Implementation Drift Gate section.

- [x] T-061-25 – Feature tests: repeat-request cache hit; child add/delete/move invalidates via the shared `albumChildrenTag()`; cross-identity isolation (F-061-15, S-061-29).
  _Intent:_ Verify zero-new-listener-wiring claim against real cache tags, not mocks, mirrors T-061-14.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-061-26 – Unit test: `CacheKeyProvider::albumChildrenDataKey()` uniqueness across a (guest, user A, user B) × (2+ distinct `album_id`s) matrix (NFR-061-05).
  _Intent:_ Mirrors T-061-15.
  _Verification commands:_ `php artisan test --filter=CacheKeyProvider`

### I9 – Read path: `GET /api/v3/Albums/{album_id}/children/rights`

- [x] T-061-28 – Feature test: flag-off → 403 regardless of caller rights (F-061-23, S-061-39).
  _Intent:_ Tests-first for the flag gate, mirrors T-061-09/18.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-29 – Feature test: response includes exactly the DO-061-10 field list; `owner_id`/`can_delete_children`/`can_move_children` are single values, not arrays; `ids` matches the same set tier 2 returns for the same `(album_id, caller)` (F-061-19/20).
  _Intent:_ Tests-first for the core shape and whole-response-vs-per-child field split.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-30 – Feature test: a child individually shared with the caller (`grants_edit` on that child's own `access_permissions`) → only that child's `grants_edit` entry is `true`, siblings `false`; a grant on the parent (`base_album_id = album_id`) with `grants_delete = true` → `can_delete_children`/`can_move_children` both `true`, uniformly, regardless of any child's own grants (F-061-20/21, S-061-34/35).
  _Intent:_ Tests-first for the whole-response-vs-per-child scoping split — the core behavioral distinction this endpoint exists to get right.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-31 – Feature test: caller belongs to two groups, each granting a different, non-overlapping subset of rights on the *same* child → that child's response has every granted flag `true` (not just one group's), matching direct `AlbumPolicy::canEdit`/`canDownload` calls for the same child/caller exactly; assert via query-log/EXPLAIN that this is one `GROUP BY`/`MAX()`-aggregated query, not a naive join producing duplicate rows (F-061-21, NFR-061-09, S-061-37).
  _Intent:_ Tests-first for the correctness-critical group-overlap gotcha found during design — this is the single most important test in this increment.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-32 – Feature tests: admin caller → every right `true` for every child, confirmed via query-log that the permission join/`exists()` call never runs (NFR-061-10, S-061-36); guest caller → only public grants considered (S-061-38).
  _Intent:_ Tests-first for the two identity-branch edge cases.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-33 – Implement `GetAlbumChildrenRightsRequest`, `AlbumChildrenRightsResource`, `AlbumChildrenRightsController::index()` (`AlbumQueryPolicy::applyVisibilityFilter()` applied to the base child query, same semantics as the other two endpoints — easy to drop while focused on the permission-join logic, guard against this specifically; `owner_id` resolved from the already-loaded `album_id`; one `exists()` query mirroring `AlbumPolicy::canDelete`'s parent-scoped logic for `can_delete_children`/`can_move_children`; one `LEFT JOIN` against `AlbumQueryPolicy::getComputedAccessPermissionSubQuery(full: true, user: $currentUser)`, `GROUP BY` child id with `MAX()` per `grants_*` column; admin early-return bypassing both queries), `CacheKeyProvider::albumChildrenRightsKey()`, and the flag-gated route registration (F-061-19..23, NFR-061-11).
  _Intent:_ Make T-061-28..32/37 pass.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`; `make phpstan`

- [x] T-061-34 – Verify (and, if missing, add) an invalidation trigger for the shared `albumChildrenTag($album_id)` on `access_permissions` changes (share/unshare, grant edit) against `album_id` or any direct child — resolve FR-061-22's flagged gap one way or the other rather than leaving it assumed; if no new listener is added, document the accepted staleness window explicitly in this task's notes.
  _Intent:_ Close the one cache-invalidation question this increment's design explicitly left open rather than silently assumed.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-35 – Feature tests: repeat-request cache hit; permission change against `album_id` or a direct child invalidates it (per whatever T-061-34 established); cross-identity isolation (F-061-22, S-061-40).
  _Intent:_ Verify the caching claim against real cache tags, not mocks, mirrors T-061-14/25.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

- [x] T-061-36 – Unit test: `CacheKeyProvider::albumChildrenRightsKey()` uniqueness across a (guest, user A, user B) × (2+ distinct `album_id`s) matrix (NFR-061-05).
  _Intent:_ Mirrors T-061-15/26.
  _Verification commands:_ `php artisan test --filter=CacheKeyProvider`

- [x] T-061-37 – Feature test: for a fixed set of children with mixed visibility (some private, not accessible to the caller), the set of `ids` returned by the rights endpoint equals the set `AlbumChildrenController::get()` would paginate over for the same caller — no invisible child's permission data leaks (NFR-061-11, S-061-41).
  _Intent:_ Tests-first for the visibility-filter-parity requirement, mirrors T-061-19's equivalent check for the children endpoint (NFR-061-08) — this endpoint was missing its own equivalent until this pass.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`

### I10 – Documentation

- [x] T-061-16 – Update `docs/specs/3-reference/api-design.md`, `database-schema.md`, `docs/specs/4-architecture/knowledge-map.md`, `docs/specs/4-architecture/roadmap.md`.
  _Intent:_ Documentation Deliverables, covers tier-1, tier-2, and rights-endpoint.
  _Verification commands:_ N/A (review only).

- [x] T-061-17 – Cross-reference this feature from `virtual-scrolling-study.md`'s bucket-decision section and correct its granularity model.
  _Intent:_ Keep the originating study doc from misleading a future reader with the pre-correction (single-global-granularity) model.
  _Verification commands:_ N/A (review only).
  _Notes:_ Done during spec drafting (2026-08-27), ahead of I10 — the correction was made as soon as it was discovered, not deferred.

### I11 – Follow-up: TagAlbum/PersonAlbum support for tiers 2/3 (2026-08-29)

> Requested after I1–I10 shipped: extend `GET .../children` and `GET .../children/rights` to also list a `TagAlbum`/`PersonAlbum`'s matching albums, mirroring `AlbumChildrenController`. Tier 1 (buckets) stays regular-`Album`-only — decided with the user: bucketing has no single governing sort column/granularity for a dynamically-matched, disparately-parented result set (see spec.md Non-Goals amendment).

- [x] T-061-38 – Extract `AlbumRepository::queryMatchingAlbumsForTagPaginated()`/`queryMatchingAlbumsForPersonPaginated()`'s query-building logic into new public, unpaginated `queryMatchingAlbumsForTag()`/`queryMatchingAlbumsForPerson()` methods (behavior-preserving refactor — the two paginated methods now call them) (FR-061-24).
  _Intent:_ Reuse the exact v2 filtering logic (tag `whereHas`/person `whereExists`, `applyBrowsabilityFilter()`) rather than duplicating it.
  _Verification commands:_ `php artisan test tests/Unit/Repositories/AlbumRepositoryTest.php`; `make phpstan`
- [x] T-061-39 – Update `GetAlbumChildrenDataRequest`/`GetAlbumChildrenRightsRequest` to resolve `Album|TagAlbum|PersonAlbum` via `HasAbstractAlbumTrait` (mirrors v2's `GetAlbumChildrenRequest` resolution exactly); `GetAlbumBucketsRequest` unchanged (still `Album`-only) (FR-061-24).
  _Verification commands:_ `make phpstan`
- [x] T-061-40 – `AlbumChildrenDataController`: branch on album type — real `Album` unchanged; `TagAlbum`/`PersonAlbum` use the new `AlbumRepository` query methods, config-gated by `TA_albums_listing_enabled`/`PA_albums_listing_enabled`, with `computed_access_permissions` joined explicitly (that query path has no built-in join for it) (FR-061-24).
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`; `make phpstan`
- [x] T-061-41 – `AlbumChildrenRightsController`: same branch; `can_delete_children`/`can_move_children` always `false` for `TagAlbum`/`PersonAlbum` (including for admin callers — no shared-parent grant concept applies), `grants_edit`/`grants_download` computed identically to the regular-`Album` path (incl. admin short-circuit to `true`) (FR-061-25).
  _Verification commands:_ `php artisan test --filter=AlbumChildrenRightsV3Test`; `make phpstan`
- [x] T-061-42 – Feature tests: `TagAlbum`/`PersonAlbum` children/rights return the correct matching-albums set (incl. empty-when-untagged/unmatched, empty-when-listing-config-disabled); rights `can_delete_children`/`can_move_children` always `false` (incl. for admin); `grants_edit`/`grants_download` still per-album-accurate; caller needs access to the `TagAlbum`/`PersonAlbum` itself, separate from access to any one matching album.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`; `php artisan test --filter=AlbumChildrenRightsV3Test`
- [x] T-061-43 – Regression-check the `AlbumRepository`/`AlbumQueryPolicy` refactors against the existing v2 consumers (`AlbumMatchingAlbumsTest`, `AlbumRepositoryTest`).
  _Verification commands:_ `php artisan test --filter=AlbumMatchingAlbumsTest`; `php artisan test tests/Unit/Repositories/AlbumRepositoryTest.php`
- [x] T-061-44 – Update `docs/specs/3-reference/api-design.md` and `docs/specs/4-architecture/knowledge-map.md` for the tier-2/3 `TagAlbum`/`PersonAlbum` support; amend spec.md's Non-Goals (tier-1-only) and add FR-061-24/25.
  _Verification commands:_ N/A (review only).

### I12 – Follow-up: tier 2 row ordering (2026-08-30)

> Surfaced during Feature 063 spec drafting: `GET .../children` shipped with zero `ORDER BY` clause at all (the shipped test's own comment even said so — "the children endpoint has no bucket_id ordering guarantee of its own"). User chose to fix it at the source rather than have Feature 063 compensate for it client-side (FR-061-26).

- [x] T-061-45 – Feature tests: for a real `Album` parent, the children endpoint's row order (deduplicated `bucket_ids` sequence) matches the buckets endpoint's own `bucket_ids` array exactly (S-061-42); a dedicated `title`/`date_prefix`-mode fixture where an undated child's title starts with a single leading digit (sorts *before* any 4-digit year alphabetically) still lands last, matching the buckets endpoint's mandatory "unknown always last" guarantee, proving the fix isn't cosmetic for the one non-monotonic bucketing mode (S-061-43) (F-061-26).
  _Intent:_ Tests-first, added to the existing `AlbumChildrenDataV3Test.php` rather than a new file.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`
- [x] T-061-46 – Implement the fix in `AlbumChildrenDataController::queryChildren()`: `orderByRaw('(albums.bucket_id IS NULL) ASC')->orderBy('albums.bucket_id', $direction)` then `SortingDecorator`-applied effective-column ordering for a real `Album`; `AlbumSortingCriterion::createDefault()` via `SortingDecorator` for `TagAlbum`/`PersonAlbum` (F-061-26).
  _Intent:_ Make T-061-45 pass; also strengthened the two existing correlation tests (`testBucketIdCorrelatesExactlyWithBucketsEndpoint`/`testBucketIdCorrelationIncludingUnknown`) to assert row order, not just grouped counts, and removed their now-stale "no ordering guarantee" comment.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`; `php artisan test --filter=AlbumChildrenRightsV3Test`; `php artisan test --filter=AlbumBucketsV3Test`; `make phpstan`; `vendor/bin/php-cs-fixer fix --dry-run`
- [x] T-061-47 – Update `docs/specs/3-reference/api-design.md` (tier 2 ordering bullet) to reflect FR-061-26.
  _Verification commands:_ N/A (review only).

### I13 – Follow-up: tier 2 pin/public/link-required fields (2026-08-30)

> Surfaced during Feature 063's ambiguity review (Q-063-05/06 in `docs/specs/4-architecture/open-questions.md`): `contextMenu.ts`'s Pin/Unpin label and the tile's public/hidden badges both read fields (`is_pinned`, `is_public`, `is_link_required`) tier 2 never supplied, which Feature 063's frontend adoption needs to reproduce today's v2-fed tile behavior exactly (FR-061-27).

- [x] T-061-48 – Feature tests: a fixture spanning a pinned child, an unpinned child, a public+no-link-required child, a public+link-required child, and a fully private child — `is_pinneds`/`is_publics`/`is_link_requireds` match `ThumbAlbumResource`'s own resolution for the same children exactly (S-061-44) (F-061-27).
  _Intent:_ Tests-first, added to the existing `AlbumChildrenDataV3Test.php`.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test`
- [x] T-061-49 – Implement in `AlbumChildrenDataController`: add `base_albums.is_pinned` to `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s existing narrow-column `base_albums` subselect (zero extra join, mirrors the `is_nsfw` precedent); add a new narrow-column subquery left join, `public_access_permissions` (over `access_permissions`, pre-filtered to `user_id IS NULL AND user_group_id IS NULL`, selecting only `base_album_id`/`is_link_required`) to resolve `is_public`/`is_link_required` per row — a raw table join was tried first and rejected: `access_permissions`' own `created_at`/`updated_at` columns collided with `SortingDecorator`'s unqualified `ORDER BY created_at` (`testBucketIdCorrelatesExactlyWithBucketsEndpoint` caught this); extend `AlbumChildrenDataResource`'s constructor/SoA arrays with the three new fields (F-061-27).
  _Intent:_ Make T-061-48 pass.
  _Verification commands:_ `php artisan test --filter=AlbumChildrenDataV3Test` (20 passed); `make phpstan` (no errors); `vendor/bin/php-cs-fixer fix --dry-run --config=.php-cs-fixer.php` (0 of 4 files needed fixing); `php artisan typescript:transform` (regenerated `lychee.d.ts` — also picked up several other previously-stale Feature 061 v3 types that had never been regenerated).
- [x] T-061-50 – Update `docs/specs/3-reference/api-design.md` (tier 2 field list) and `docs/specs/4-architecture/knowledge-map.md` to reflect FR-061-27.
  _Verification commands:_ N/A (review only).

## Notes / TODOs

- Root-scope bucketing, photo-side bucket columns, `bucket_id`-windowed tier-2 pagination, full `AlbumRightsResource` parity on the rights endpoint, and frontend adoption (including the actual background-fetch wiring and client-side rights-combination logic) are explicitly out of scope for this feature's tasks — see plan.md Follow-ups. Do not fold them into any task above.
- Tier 3 needs no task at all — Feature 056's Asset endpoint already exists and is unchanged by this feature.
- ~~The `059` feature-number collision~~ — resolved by renumbering this feature to `061` (2026-08-27); no longer relevant.
- ~~This feature is paused for a rework pass~~ — rework pass complete (2026-08-29 correction pass against Feature 060 staleness); ~~back to Draft, I1 may begin~~ — superseded the same day: all tasks below were then implemented and this feature moved to Completed (see status field at the top of this document).
