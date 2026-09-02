# Feature 062 Tasks – Root Album Listing Struct-of-Arrays

_Status: Implemented — T-062-33 (2026-09-02 amendment, `/Albums/smart` real cover_ids, FR-062-16) implemented, `AlbumCategoryV3Test` 16/16 green._
_Last updated: 2026-09-02_

> Keep this checklist aligned with plan.md's increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes.

## Checklist

### I1 – Controller consolidation + route rename

- [x] T-062-00 – Verify no v8 frontend code references the old `/children` paths before renaming (F-062-01, Q-062-12).
  _Intent:_ Ground the "safe to rename, no consumer yet" claim fresh rather than trusting the roadmap note from memory.
  _Verification commands:_
  - `grep -rn "children/buckets\|children/rights\|/children'" resources/js/v8` (expect zero matches)

- [x] T-062-01 – Create `App\Http\Controllers\Gallery\AlbumListing` namespace/folder (F-062-12).
  _Intent:_ Home for the consolidated + new controllers.
  _Verification commands:_ N/A (scaffolding).

- [x] T-062-02 – Port `AlbumBucketController`/`AlbumChildrenDataController`/`AlbumChildrenRightsController` into one `AlbumListing\AlbumChildrenController` (`index()`/`buckets()`/`rights()`), verbatim logic, delete the 3 old files, re-point **and rename** the 3 existing routes: `/Albums/{album_id}/children` → `/Albums/{album_id}`, `/children/buckets` → `/buckets`, `/children/rights` → `/rights` (F-062-01, F-062-12, NFR-062-06, S-062-21/28).
  _Intent:_ Query logic and response shape are a pure move; the URL path is the one deliberate, visible change.
  _Verification commands:_
  - `php artisan test --filter=AlbumBucketsV3Test`
  - `php artisan test --filter=AlbumChildrenDataV3Test`
  - `php artisan test --filter=AlbumChildrenRightsV3Test`
  - `make phpstan`
  - `git diff` on the 3 test files — confirm only URL-string literals changed
  _Notes:_ These three existing test files need their request-URL literal updated (mechanical, one line per test method) but **zero assertion edits** — if an assertion needs changing, the "pure move" claim is false; stop and reconsider.

- [x] T-062-03 – Widen `AlbumChildrenDataResource` with `owner_ids[]` (F-062-06, DO-062-03).
  _Intent:_ Additive field, populated for both sub-album and root tiers (data already selected in the existing query — free).
  _Verification commands:_
  - `php artisan test --filter=AlbumChildrenDataV3Test`

- [x] T-062-04 – Widen `AlbumChildrenRightsResource`'s `owner_id` to nullable, omitted from the payload when null (F-062-06, DO-062-04, Q-062-16).
  _Intent:_ Root context has no single owner to report, for either scope; since the value is always null there, the key is dropped from the JSON entirely (conditional resource field) rather than serialized as a useless `null`. Sub-album/matching-albums tiers, where the value is always real, keep emitting the key unchanged — same conditional, just never triggers there.
  _Verification commands:_
  - `php artisan test --filter=AlbumChildrenRightsV3Test`
  - `make phpstan`
  - Assert the sub-album tier's existing response still includes a real `owner_id` key (regression check on the conditional).

### I2 – `ColumnSortingAlbumType::OWNER_ID` removal + migration

- [x] T-062-05 – Write failing migration test (F-062-08, S-062-13, FX-062-02).
  _Intent:_ Pre-migration fixture with `album_sorting_col='owner_id'` (a row) and `configs.value='owner_id'` (key `sorting_albums_col`); assert both rewritten to `created_at` post-migration; assert idempotent second run.
  _Verification commands:_
  - `php artisan test --filter=OwnerIdSortingMigrationTest`

- [x] T-062-06 – Remove `ColumnSortingAlbumType::OWNER_ID` (F-062-08, G5).
  _Intent:_ `new Enum(ColumnSortingAlbumType::class)` validation automatically rejects `owner_id` afterward.
  _Verification commands:_
  - `make phpstan` (catch any now-dead/unreachable `match` arm keyed on this specific enum)
  - `php artisan test --filter=UpdateAlbumRequest`
  - `php artisan test --filter=PatchBulkAlbumRequest`

- [x] T-062-07 – Write the migration (`UPDATE albums`/`UPDATE configs`, mirrors `fix_sorting_albums.php`) (F-062-08, NFR-062-07).
  _Intent:_ Rewrite surviving `owner_id` values to `created_at`; no `down()`; touches only `albums.album_sorting_col`/`configs.value`, no other column.
  _Verification commands:_
  - `php artisan test --filter=OwnerIdSortingMigrationTest`
  - Assert no `down()` method exists (or that it's a no-op); assert a second run is a no-op (idempotent).

- [x] T-062-08 – Verify no other code pattern-matches `ColumnSortingAlbumType::OWNER_ID` by name (F-062-08).
  _Intent:_ Confirm the plan.md assumption grep (`AlbumSortingCriterion`, `BulkAlbumPatchData`, `BulkAlbumController`, `SearchSortingType` — none require changes).
  _Verification commands:_
  - `grep -rn "ColumnSortingAlbumType::OWNER_ID" app`
  _Notes:_ Should return zero matches after T-062-06 (compile-time proof via `make phpstan` already covers this, but a literal grep is a fast sanity double-check).

- [x] T-062-09 – Verify/regenerate frontend types and confirm no manual dropdown edit is needed (F-062-08, Test Strategy).
  _Intent:_ Feature 060 precedent says dropdowns auto-narrow from generated types — verify, don't assume.
  _Verification commands:_
  - `npm run check`

### I3 – `RecomputeRootAlbumBucketsJob` + config-change dispatch

- [x] T-062-10 – Write failing `RecomputeRootAlbumBucketsJobTest` (F-062-07, S-062-10/11).
  _Intent:_ One SELECT + one bulk upsert against a root-album fixture; verify `bucket_id` correctness for date and title effective columns (never owner — OWNER_ID no longer reachable post-I2).
  _Verification commands:_
  - `php artisan test --filter=RecomputeRootAlbumBucketsJobTest`

- [x] T-062-11 – Implement `RecomputeRootAlbumBucketsJob` (F-062-07, NFR-062-03).
  _Intent:_ Mirrors `RecomputeChildAlbumBucketsJob`'s shape; `whereIsRoot()`; no constructor argument; exactly one `SELECT` + one bulk `upsert()` regardless of root-album count.
  _Verification commands:_
  - `php artisan test --filter=RecomputeRootAlbumBucketsJobTest`
  - `make phpstan`
  - Query-count assertion (NFR-062-03) against a many-root-album fixture, not just a small one.

- [x] T-062-12 – Dispatch from `SettingsController::setConfigs()` on the 5-key intersection; extend `ALBUM_LISTING_COARSE_FLUSH_CONFIGS` (F-062-07, S-062-10/11/12).
  _Intent:_ `sorting_albums_col`/`sorting_albums_order`/`timeline_albums_granularity`/`title_bucket_mode`/`title_bucket_prefix_length` trigger recompute; unrelated keys do not.
  _Verification commands:_
  - `php artisan test --filter=SettingsControllerTest` (`Bus::fake()`-asserted)

### I4 – `AlbumRootController` (index/buckets/rights, scope-aware)

- [x] T-062-13 – New `GetScopedAlbumsRequest` with `scope` validation (F-062-02, S-062-01..04/18).
  _Intent:_ Authenticated: required `own`\|`shared`. Guest: optional, `shared` only (default), `own` → 422. `authorize()` gates on `features.struct-of-array` first (mirrors 061) — flag off → 403 regardless of `scope`.
  _Verification commands:_
  - Request-level validation tests covering all 4 scope scenarios.
  - Flag-off test asserting 403 (S-062-18).

- [x] T-062-14 – Implement `AlbumRootController::index()`/`buckets()` for `scope=own` (F-062-03, S-062-05).
  _Intent:_ Reuses the exact bucket_id-based mechanism from `AlbumChildrenController`, parameterized by `whereIsRoot()` + owner-equals instead of a parent-id filter.
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test`
  - Query-count/index-usage assertion (NFR-062-01).

- [x] T-062-15 – Implement `AlbumRootController::index()`/`buckets()` for `scope=shared` (F-062-04/05, S-062-06/07/29).
  _Intent:_ Children-data ordered by `owner_id` then normal sort, response `bucket_id` field repurposed to `owner_id`; buckets = live `GROUP BY owner_id`, never via `AlbumBucketComputer`/persisted `bucket_id`. `bucketable` is **unconditionally `true`** — do not special-case an empty result to `false` (Q-062-15). Label resolution is authentication-gated (Q-062-14): authenticated → `users` join + `COALESCE(display_name, username)`; guest → skip the join entirely, hardcode every label to `"unknown"`; grouping/counts identical either way.
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test`
  - Cross-endpoint consistency assertion: grouping children-data rows by `bucket_id` reproduces the buckets endpoint's own grouping (plan.md risk).
  - Query-count/join assertion (NFR-062-02) — explicit guest case asserting **zero** `users` joins, not just correct label content.
  - Empty-result assertion: `bucketable:true` + empty arrays, never `bucketable:false`.

- [x] T-062-16 – Implement `AlbumRootController::rights()` (F-062-06, S-062-08/09).
  _Intent:_ `can_delete_children`/`can_move_children` always `false` non-admin, `true` admin short-circuit; real per-row grants; `owner_id` **omitted from the JSON payload** (not just `null`) for both `own` and `shared` scope, via a conditional resource field (Q-062-16) — sub-album/matching-albums tiers keep emitting the key unchanged.
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test`
  - Assert the response JSON has no `owner_id` key at all for root (both scopes), not merely `owner_id: null`.

- [x] T-062-17 – New `CacheKeyProvider` methods for the root tier, keyed by `(scope, user_id)` (F-062-13, NFR-062-08).
  _Intent:_ Key-uniqueness across (guest-shared, user A own, user A shared, user B own).
  _Verification commands:_
  - `php artisan test --filter=CacheKeyProviderTest`

- [x] T-062-18 – Register `/Albums/root`, `/Albums/root/buckets`, `/Albums/root/rights` ahead of `{album_id}` family (F-062-01).
  _Intent:_ Avoid route-parameter shadowing.
  _Verification commands:_
  - `php artisan route:list --path=Albums`
  - Request-level test hitting `/Albums/root` asserting correct controller resolution.

- [x] T-062-19 – `deduplicate_pinned_albums` parity check (S-062-20).
  _Intent:_ Behaviour identical to v2 for both scopes.
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test`

- [x] T-062-19a – v2 parity fixture: `own ∪ shared` reconstructs `Top::queryRootAlbums()` exactly (F-062-03/04, NFR-062-05).
  _Intent:_ This is the core correctness property of the whole root tier — not "the code was copy-pasted so it must match," an actual assertion. Build one fixture with a mix of owned/shared/pinned/deduplicated root albums for one user; call v2's `Top::get()` and v3's `/Albums/root?scope=own` + `?scope=shared` against it; assert the v3 `own` ∪ `shared` id sets are exactly equal (no overlap, no gap) to v2's `albums` ∪ `shared_albums` id sets. Repeat for a guest against v2's unpartitioned guest result vs. v3's `shared`-only (no `scope`) result.
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test`
  - Explicit set-equality assertion (not spot-checking a few rows) between the v2 and v3 id sets, both authenticated and guest.

### I5 – `AlbumCategoryController` (smart/persons/tags/tagsRights/pinned)

- [x] T-062-20 – New `GetAlbumCategoryRequest` (feature-flag gate only) (F-062-01, S-062-18).
  _Intent:_ Shared by the 3 remaining un-scoped `AlbumCategoryController` methods (`smart()`/`tags()`/`tagsRights()`). `persons()`/`pinned()` use `GetScopedAlbumsRequest` instead (T-062-13, F-062-15) — do not route either through this class.
  _Verification commands:_
  - Request-level validation test.
  - Flag-off test asserting 403 (S-062-18).

- [x] T-062-21 – New `AlbumCategoryListResource` (F-062-09, DO-062-05).
  _Intent:_ `ids[]`/`titles[]`/`cover_ids[]`/`owner_ids[]`, mirrors `AlbumListResource`'s shape.
  _Verification commands:_ N/A (exercised by T-062-22/23/23a/23c/24).

- [x] T-062-22 – Implement `AlbumCategoryController::smart()` (F-062-09, S-062-14).
  _Intent:_ Reuses `AlbumFactory::getAllBuiltInSmartAlbums(false)` + `Gate::check`, zero SQL query.
  _Verification commands:_
  - `php artisan test --filter=AlbumCategoryV3Test`
  - Query-count assertion (zero queries).

- [x] T-062-23 – Implement `AlbumCategoryController::tags()` (F-062-09, S-062-15).
  _Intent:_ `toBase()` query mirroring `Top::queryTagAlbums()`'s filter/sort, no eager loads; cached via existing key/tag methods, un-scoped.
  _Verification commands:_
  - `php artisan test --filter=AlbumCategoryV3Test`
  - Query-count assertion (zero `access_permissions`/`owner`/`userThumbRow` eager loads).

- [x] T-062-23a – Implement `AlbumCategoryController::pinned()`, scope-aware (F-062-15, S-062-15/22/23/24/25).
  _Intent:_ `toBase()` query mirroring `Top::queryPinnedAlbums()`'s exact `is_pinned` join + `sorting_pinned_albums_col`/`sorting_pinned_albums_order` ordering, no eager loads, not restricted to root albums; consumes `GetScopedAlbumsRequest` (T-062-13) for `scope` validation, identical rule to root (own/shared required if authenticated, guest defaults to shared, `own` for guest → 422). `own` = `owner_id = $user->id`; `shared` = `owner_id != $user->id` (unfiltered for guest) returned as **one flat list, never grouped by owner** — do not port root's `GROUP BY owner_id`/bucket logic here. No buckets method/route. Cached via `pinnedAlbumsListingKey`/`-Tag`, widened to take `scope`.
  _Verification commands:_
  - `php artisan test --filter=AlbumCategoryV3Test`
  - Assert a pinned sub-album (not just root) appears in the result.
  - Assert `scope=shared` response is one flat array, not grouped/nested by owner.
  - Assert no `/Albums/pinned/buckets` route exists.

- [x] T-062-23c – Implement `AlbumCategoryController::persons()`, scope-aware (F-062-15, S-062-15/26/27).
  _Intent:_ `toBase()` query mirroring `Top::queryPersonAlbums()`'s filter/sort, no eager loads; consumes `GetScopedAlbumsRequest` for `scope` validation, identical rule to root/pinned. `own`/`shared` predicates mirror T-062-23a exactly (`shared` is one flat list, never grouped by owner, no buckets route). `ai_vision_face_enabled` off → empty block regardless of scope. Cached via `personAlbumsListingKey`/`-Tag`, widened to take `scope`.
  _Verification commands:_
  - `php artisan test --filter=AlbumCategoryV3Test`
  - Assert `scope=shared` response is one flat array, not grouped/nested by owner.
  - Assert no `/Albums/persons/buckets` route exists.

- [x] T-062-23b – Widen `pinnedAlbumsListingKey()` and `personAlbumsListingKey()` with a `scope` argument (F-062-13, S-062-23/26).
  _Intent:_ Key-uniqueness across (guest-shared, user A own, user A shared, user B own) for both pinned and persons, same matrix as root's cache keys (T-062-17).
  _Verification commands:_
  - `php artisan test --filter=CacheKeyProviderTest`

- [x] T-062-24 – New `AlbumCategoryRightsResource` + `AlbumCategoryController::tagsRights()` (F-062-10, DO-062-06, S-062-16).
  _Intent:_ `ids[]`/`grants_edit[]`/`grants_download[]`/`grants_delete[]`; admin short-circuit.
  _Verification commands:_
  - `php artisan test --filter=AlbumCategoryV3Test`

- [x] T-062-25 – Register `/Albums/smart`, `/Albums/persons`, `/Albums/tags`, `/Albums/tags/rights`, `/Albums/pinned` ahead of `{album_id}` family (F-062-01).
  _Intent:_ Avoid route-parameter shadowing.
  _Verification commands:_
  - `php artisan route:list --path=Albums`

- [x] T-062-26 – Per-category cache-invalidation independence check (S-062-17).
  _Intent:_ A tag rename evicts only the `tags` cache entry; `persons` stays cached.
  _Verification commands:_
  - `php artisan test --filter=AlbumCategoryV3Test`

### I6 – Regression, quality gates, docs

- [x] T-062-26a – Verify cache-invalidation coverage directly, not by inspection (F-062-14).
  _Intent:_ F-062-14 asserts the *existing* `ManagedCacheAlbumListingInvalidator` handlers already cover the new root/persons/pinned cache entries via `albumChildrenTag(null)` and the existing category tags, so "no new listener wiring is required." This has never actually been exercised against the new cache keys specifically — verify it rather than trust the spec's own reasoning. For each existing handled event (album save, move, delete, visibility change, tag rename, etc.), populate the relevant new cache entry (root own/shared, persons own/shared, pinned own/shared), trigger the event, and assert the entry is actually evicted.
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest`
  - Add cases to that suite (or a new one) covering the new root/persons/pinned keys specifically, not just the pre-existing ones.

- [x] T-062-27 – Implementation Drift Gate: confirm `routes/api_v2.php`, `AlbumsController.php`, `Top.php`, `RootAlbumResource.php`, `resources/js/v7/**` unchanged (NFR-062-04, S-062-19).
  _Verification commands:_
  - `git diff routes/api_v2.php app/Http/Controllers/Gallery/AlbumsController.php app/Actions/Albums/Top.php app/Http/Resources/Collections/RootAlbumResource.php resources/js/v7`

- [x] T-062-28 – Full new-surface + targeted regression test run (S-062-01..29).
  _Verification commands:_
  - `php artisan test --filter=AlbumRootV3Test`
  - `php artisan test --filter=AlbumCategoryV3Test`
  - `php artisan test --filter=AlbumBucketsV3Test`
  - `php artisan test --filter=AlbumChildrenDataV3Test`
  - `php artisan test --filter=AlbumChildrenRightsV3Test`
  - `php artisan test --filter=Album`
  - `php artisan test --filter=Settings`
  - `php artisan test --filter=Cache`

- [x] T-062-29 – File-count check against NFR-062-09.
  _Intent:_ ≤3 controller files (net), ≤2 new request files, ≤2 new resource files for the 8 new routes.
  _Verification commands:_
  - `git diff --stat` against `app/Http/Controllers/Gallery/AlbumListing/`, `app/Http/Requests/Album/`, `app/Http/Resources/V3/`.

- [x] T-062-30 – Quality gates.
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run --diff`

- [x] T-062-31 – Documentation updates.
  _Intent:_ `api-design.md`, `database-schema.md`, `knowledge-map.md`, `roadmap.md` reflect the new endpoints, the `AlbumListing` namespace, and the `OWNER_ID` removal.
  _Verification commands:_ N/A (doc review).

- [x] T-062-32 – Deployer-facing note about `lychee:recompute-album-buckets` re-run post-migration (F-062-08 note).
  _Intent:_ Ensure this isn't silently lost — surfaced in the migration's own comment at minimum.
  _Verification commands:_ N/A (doc review).

- [x] T-062-33 *(2026-09-02 amendment, precondition for Feature 063's I14)* – `AlbumCategoryController::smart()`: replace the hardcoded `$cover_ids[] = null;` with a batched `AlbumUserThumb::query()->whereIn('album_id', $ids)->where('user_id', '=', Auth::id())->pluck('photo_id', 'album_id')` lookup, mapping each smart album's `get_id()` through it (`null` on a miss) (FR-062-16). Update the method's own docblock — "No live thumb resolution here (would require a photos query, contradicting this endpoint's zero-SQL-query guarantee)" is no longer accurate as written; narrow it to "no *live* resolution" and note the new cache-only lookup.
  _Intent:_ Feature 063's smart-album root-tile addendum depends on this — Feature 063's own I14 tracks it only as a precondition (T-063-41), this is the actual implementation task.
  _Note:_ Implemented as specced, one query total (not per-row) via `whereIn`. Existing `testSmartReturnsSameSetAsV2WithZeroQueries`'s "zero queries" assertion is scoped to *photos* queries specifically (its own comment already anticipated `with_relations=false`-style exceptions) — the new query targets `album_user_thumbs`, not `photos`, so it needed no change and still passes.
  _Verification commands:_ `make phpstan` (0 errors); `vendor/bin/php-cs-fixer fix --dry-run` (clean); `php artisan test --filter=AlbumCategoryV3Test` (16/16 green, includes new `testSmartResolvesRealCoverFromCacheHitAndNullFromCacheMiss`).

## Notes / TODOs

- If `php artisan test` (unfiltered) is attempted, expect the same pre-existing ~600s process-timeout documented for prior features — always use `--filter` runs.
- T-062-02 is the highest-risk task in this feature: it's a route **rename** of an already-shipped feature bundled with a controller move. Treat any need to touch an *assertion* in the existing 061 test files (as opposed to a URL literal) as a signal something was not actually behavior-preserving, and stop to investigate before proceeding.
- `pinned` albums were briefly dropped from scope mid-design, then reinstated as a fifth category endpoint (Q-062-09), then further refined to also take `scope=own|shared` (Q-062-10) — `GET /Albums/pinned?scope=`, no rights endpoint, no buckets endpoint ever (Q-062-11 — a pinned album's real tree position is arbitrary, so its `bucket_id` has no coherent cross-list meaning). `persons` gained the identical `scope=own|shared` treatment in a later follow-up (Q-062-13) — same flat/ungrouped `shared`, same no-buckets rule. `tags`/`smart` deliberately stay un-scoped. Resist the temptation to reuse root's per-owner `GROUP BY owner_id` bucket logic for either persons or pinned (see plan.md's dedicated risk entry on this exact mistake).
- The `/Albums/{album_id}/children[/buckets|/rights]` paths from Feature 061 are renamed to `/Albums/{album_id}[/buckets|/rights]` in this feature (Q-062-12) — confirmed safe via a live grep of `resources/js/v8` finding zero references before committing to this design.
- Post-spec-review corrections (Q-062-14..17, resolved 2026-09-02): (1) guest requests to `/Albums/root/buckets` never trigger the `users` join and always see `"unknown"` labels, even though grouping/counts stay real — do not let this regress to "guests see real names" as a same-day draft briefly had it; (2) `bucketable` is `true` unconditionally for `shared` scope, including on an empty result — do not special-case empty-to-`false`, that's Feature 061's OWNER_ID-only meaning, not this feature's; (3) root rights' `owner_id` key is *absent* from the JSON for both scopes (conditional field), not merely `null` — assert on key presence, not just value; (4) the `/children`-drop rename (Q-062-12) was reconsidered and explicitly kept as specced.
