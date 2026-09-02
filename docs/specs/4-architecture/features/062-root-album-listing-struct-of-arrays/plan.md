# Feature Plan 062 – Root Album Listing Struct-of-Arrays

_Linked specification:_ `docs/specs/4-architecture/features/062-root-album-listing-struct-of-arrays/spec.md`
_Status:_ Implemented
_Last updated:_ 2026-09-02

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md).

## Vision & Success Criteria

Bring the root gallery view to parity with Feature 061's sub-album virtual-scroll backend, while simultaneously (a) closing a real, silently-shipped config gap (`OWNER_ID` as a selectable sort column, dead since Feature 060's dropdown narrowing but never removed from the enum) and (b) re-organizing the album-listing v3 controller surface so it doesn't grow one file per route. Success = 8 new endpoints plus 3 renamed ones, all query-count-bounded, `own`/`shared` scope semantics matching today's `Top::get()` partition exactly (and consistently applied wherever it's requested — root, persons, pinned), `bucket_id` provably never owner-derived anywhere, zero v2 behavioural change, Feature 061's existing tests passing with URL-literal-only edits (proof of a clean consolidation), and the file-count ceiling in NFR-062-09 met.

## Scope Alignment

- **In scope:**
  - Controller consolidation: `AlbumBucketController`+`AlbumChildrenDataController`+`AlbumChildrenRightsController` → `Gallery\AlbumListing\AlbumChildrenController`.
  - New `Gallery\AlbumListing\AlbumRootController` (index/buckets/rights, scope-aware).
  - New `Gallery\AlbumListing\AlbumCategoryController` (smart/persons/tags/tagsRights/pinned).
  - New shared `GetScopedAlbumsRequest` (root's 3 methods + `persons()`/`pinned()` — 5 methods across 2 controllers), `GetAlbumCategoryRequest` (the remaining 3 un-scoped `AlbumCategoryController` methods: `smart()`/`tags()`/`tagsRights()`).
  - Route rename: Feature 061's already-shipped `/Albums/{album_id}/children[/buckets\|/rights]` → `/Albums/{album_id}[/buckets\|/rights]` (Q-062-12) — path only, response shape untouched.
  - New shared `AlbumCategoryListResource`, `AlbumCategoryRightsResource`; widened `AlbumChildrenDataResource`/`AlbumChildrenRightsResource`.
  - New `RecomputeRootAlbumBucketsJob` + `SettingsController::setConfigs()` dispatch + coarse-flush-list extension.
  - `ColumnSortingAlbumType::OWNER_ID` removal + migration.
  - New `CacheKeyProvider` methods for the root tier.
  - Route file reorganization (`routes/api_v3.php`), grouped by category with clear comments.
  - Tests for all of the above (new + Feature 061 regression).
- **Out of scope:**
  - v2 (`Top`, `AlbumsController`, `RootAlbumResource`, `TopAlbumDTO`) — untouched (NG1).
  - v8 frontend adoption (NG2) — follow-up feature.
  - Bucket tier for tag/person category listings (NG3).
  - Person/smart/pinned album rights endpoints (NG4).
  - `deduplicate_pinned_albums` semantics change (NG6). (Note: `pinned` albums *as a category listing* — `GET /Albums/pinned` — is now in scope, reinstated per Q-062-09; only its de-duplication *semantics* stay unchanged.)
  - `ColumnSortingType::OWNER_ID` (broader enum) removal (NG7) — stays.

## Dependencies & Interfaces

- Feature 061's existing query/resource logic — ported into the consolidated controller, not rewritten from scratch (reduces regression risk; NFR-062-06 requires its existing tests to pass unmodified).
- Feature 057's `AlbumListResource` — shape precedent for `AlbumCategoryListResource`.
- `App\Actions\Albums\Top` — read-only reference for exact v2 query/partition/label semantics; NOT modified.
- `AlbumQueryPolicy::applyVisibilityFilter()`, `joinSubComputedAccessPermissions()` — reused verbatim.
- `App\Http\Controllers\Admin\SettingsController::setConfigs()` — new dispatch site.
- `App\Services\Cache\CacheKeyProvider` / `ManagedCacheService` / `ManagedCacheAlbumListingInvalidator` — new key methods only; existing tag-eviction wiring reused.
- `App\Enum\ColumnSortingAlbumType` — one case removed.
- `database/migrations/2026_08_30_093447_fix_sorting_albums.php` / `2026_08_28_000005_migrate_sorting_config_to_unified_title.php` — exact structural precedent for the new migration.
- `config('features.struct-of-array')` — reused gate.

## Assumptions & Risks

- **Assumptions:**
  - No code outside `ColumnSortingAlbumType` itself pattern-matches on `ColumnSortingAlbumType::OWNER_ID` by name (verified — grep found only `AlbumSortingCriterion`, `BulkAlbumPatchData`, `UpdateAlbumRequest`/`PatchBulkAlbumRequest` (generic `Enum` rule), `BulkAlbumController`, `SearchSortingType` (no OWNER_ID case in its own mapping); none require code changes beyond the enum itself).
  - `configs.type_range` for `sorting_albums_col` already excludes `owner_id` (Feature 060 migration) — no `type_range` update needed in the new migration, only the two `value`-rewrite `UPDATE`s.
  - Any frontend `album_sorting_col` dropdown is generated from the backend enum (Feature 060 precedent: "all dropdowns consume the shared, now-narrowed `constants.ts` arrays") — verify via `npm run check` after `lychee.d.ts` regeneration rather than assuming no frontend edit is needed.
- **Risks / Mitigations:**
  - **Risk:** Merging 3 controllers into 1 plus renaming their routes in the same increment introduces a behavioural regression that gets misattributed to "just a rename." **Mitigation:** port method bodies verbatim, change the route path as a mechanical string edit only (no logic touched), run Feature 061's existing test suite immediately after — a passing run with only URL-literal diffs on the test files (never assertion diffs) is the acceptance gate (NFR-062-06) before touching anything else.
  - **Risk:** Registering `/Albums/root`, `/Albums/smart`, `/Albums/persons`, `/Albums/tags`, `/Albums/pinned` after `/Albums/{album_id}/...` would let Laravel match the param route first (now a **one-segment** wildcard, `/Albums/{album_id}`, since the rename drops `/children` — same collision shape as before, just one segment shorter). **Mitigation:** register literal routes first; add a route-list assertion.
  - **Risk:** Renaming a route that already shipped in a completed feature (061) reads as risky by default, even though nothing consumes it yet. **Mitigation:** explicitly verify (not just assume) via `grep -r "children/buckets\|children/rights\|/children'" resources/js` that no v8 code references the old path before renaming — the roadmap's "no v8 frontend consumer yet" claim should be confirmed fresh, not taken as still-true from memory.
  - **Risk:** `shared`-scope children-data repurposing the `bucket_id` response field to mean `owner_id` could be missed in one of the two code paths (buckets vs. children-data), breaking the "group rows by `bucket_id` reproduces buckets" client contract for that scope only. **Mitigation:** a dedicated scenario test (S-062-06) asserting cross-endpoint consistency, not just each endpoint in isolation.
  - **Risk:** The `OWNER_ID` enum removal migration runs on an install with existing `album_sorting_col='owner_id'` rows whose `bucket_id` is currently `null` (uncomputed, since `AlbumBucketComputer` already short-circuits `OWNER_ID` to `null` today) — after migrating to `created_at`, those rows still show `bucket_id=null` until a backfill runs. **Mitigation:** explicit deployer-facing note (not automated) to re-run `lychee:recompute-album-buckets`; call this out in the migration's own comment and in tasks.md.
  - **Risk:** `php artisan test` unfiltered may hit the same pre-existing ~600s process-timeout documented for prior features. **Mitigation:** targeted `--filter` runs only ([[feedback_no_full_test_suite]]).
  - **Risk:** `persons()`/`pinned()`'s `shared` scope is implemented by copy-pasting root's `shared`-scope owner-grouping logic (they're adjacent in the same increment and superficially similar — "not owned by me") — accidentally giving either a `GROUP BY owner_id`/per-owner-bucket response, or a buckets route. **Mitigation:** S-062-23/25/26/27 explicitly assert both categories' `shared` results are one flat list and that no `/Albums/persons/buckets` or `/Albums/pinned/buckets` route exists; review this diff specifically for absence, not just presence, of grouping logic.
  - **Risk:** The guest label-gating (Q-062-14) is implemented as an `if ($user !== null)` branch around only the *label formatting* step, while the `users` join itself still executes unconditionally "just in case" — technically hides names from the response but doesn't achieve NFR-062-02's actual goal (zero join for a guest) and leaves a pointless query. **Mitigation:** S-062-29 asserts on join/query count, not just on response content — a guest response with correct `"unknown"` labels but a `users` join still present should fail this test.
  - **Risk:** `bucketable:true`-always (Q-062-15) gets implemented by keeping the old `if (empty) bucketable:false` special-case out of inertia, since it "looks harmless" to leave in. **Mitigation:** S-062-07 explicitly asserts `bucketable:true` on a zero-result fixture — a lingering empty-check special-case fails this test immediately.
  - **Risk:** FR-062-14's claim ("no new invalidation listener wiring required") is an assumption carried over from how `albumChildrenTag(null)` already behaves for `Top::get()`'s existing root cache entry — it was never actually verified against the *new* root/persons/pinned cache keys specifically. If it's wrong, root/persons/pinned listings could silently serve stale data after a mutation, with no test catching it until a user reports it. **Mitigation:** I6 verifies this directly (trigger each handled event, assert eviction) rather than treating the spec's claim as self-evidently true.
  - **Risk:** NFR-062-05 (the `own ∪ shared` = v2's exact result guarantee) is easy to treat as "obviously true because the query is copy-pasted from `Top::queryRootAlbums()`" and skip actually testing — but the port introduces new code paths (separate `own`/`shared` queries instead of one query + PHP partition) where a subtle predicate mismatch (e.g. an off-by-one in the owner-equality direction, or a missed `deduplicate_pinned_albums` join on one branch) would silently produce a wrong partition that still "looks right" in casual testing. **Mitigation:** a dedicated task builds the actual v2-vs-v3 comparison fixture and asserts set equality, not just spot-checks a few rows.

## Implementation Drift Gate

Before merging, re-run: `git diff` on `routes/api_v2.php`, `app/Http/Controllers/Gallery/AlbumsController.php`, `app/Actions/Albums/Top.php`, `app/Http/Resources/Collections/RootAlbumResource.php`, `resources/js/v7/**` — all must be empty (NFR-062-04). Record the confirming command output here once run, plus the file-count tally for NFR-062-09 and the `EXPLAIN`/query-plan evidence for NFR-062-01/02.

**Confirmed 2026-09-02:** `git diff --stat routes/api_v2.php app/Http/Controllers/Gallery/AlbumsController.php app/Actions/Albums/Top.php app/Http/Resources/Collections/RootAlbumResource.php resources/js/v7` — empty. File-count tally (NFR-062-09): 3 net controller files (`AlbumChildrenController` consolidated + `AlbumRootController`/`AlbumCategoryController` new) under `app/Http/Controllers/Gallery/AlbumListing/`; 2 new request files (`GetScopedAlbumsRequest`, `GetAlbumCategoryRequest`); 2 new resource files (`AlbumCategoryListResource`, `AlbumCategoryRightsResource`) plus 2 widened existing ones (`AlbumChildrenDataResource`, `AlbumChildrenRightsResource`) — ceiling met exactly. `own` scope buckets confirmed index-served via the pre-existing `(parent_id, bucket_id)` composite index reused unchanged (root's own-scope query shape mirrors the sub-album tier's exact `GROUP BY` verbatim). All 32 tasks (T-062-00..32, including T-062-19a/23a/23b/23c) implemented and green.

## Increment Map

1. **I1 – Controller consolidation + route rename (behavior-preserving; path is the one visible change)**
   - _Goal:_ FR-062-12, NFR-062-06 — merge Feature 061's 3 controllers into `Gallery\AlbumListing\AlbumChildrenController`, rename the 3 existing routes (drop `/children`), delete the 3 old files. Query logic/response shape untouched — only the class boundary and the URL path change.
   - _Preconditions:_ spec.md merged.
   - _Steps:_ Create `AlbumListing` namespace/folder. Port `AlbumBucketController::index()`/`queryBuckets()`/`computeLabels()`/`formatBucketLabel()` → `AlbumChildrenController::buckets()` + private helpers, verbatim. Port `AlbumChildrenDataController` → `AlbumChildrenController::index()` + private helpers, verbatim (this is also where `owner_ids[]` gets added to the resource, per FR-062-06's widening — do this in the SAME commit as the port, not a separate one, to keep the diff reviewable as "move + one additive field"). Port `AlbumChildrenRightsController` → `AlbumChildrenController::rights()` + private helpers, verbatim (nullable `owner_id` widening here too). Delete the 3 old files. Update route registrations in `routes/api_v3.php`: `/Albums/{album_id}/children` → `/Albums/{album_id}`, `/Albums/{album_id}/children/buckets` → `/Albums/{album_id}/buckets`, `/Albums/{album_id}/children/rights` → `/Albums/{album_id}/rights`. Update Feature 061's existing test files' request-URL literals to match (mechanical find/replace of the path string only — do not touch any assertion).
   - _Commands:_ `php artisan test --filter=AlbumBucketsV3Test`, `--filter=AlbumChildrenDataV3Test`, `--filter=AlbumChildrenRightsV3Test` (confirmed exact class names: `tests/Feature_v3/Album/{AlbumBucketsV3Test,AlbumChildrenDataV3Test,AlbumChildrenRightsV3Test}.php`), `make phpstan`.
   - _Exit:_ S-062-21/28 green — `git diff` on the 3 test files shows only URL-string lines changed, zero assertion edits.

2. **I2 – `ColumnSortingAlbumType::OWNER_ID` removal + migration**
   - _Goal:_ FR-062-08, G5, NFR-062-07 (migration idempotency/scope).
   - _Preconditions:_ None (independent of I1).
   - _Steps:_ Write failing migration test first (FX-062-02 fixture: pre-migration `owner_id` values in both `albums.album_sorting_col` and `configs`). Remove the enum case. Write the migration (mirrors `fix_sorting_albums.php` exactly). Run `make phpstan` to catch any now-impossible `match` arm or unreachable-code warning in `AlbumBucketComputer`/anywhere else that switches on this specific enum (not `ColumnSortingType`).
   - _Commands:_ `php artisan test --filter=OwnerIdSortingMigration` (or similar), `make phpstan`.
   - _Exit:_ S-062-13 green.

3. **I3 – `RecomputeRootAlbumBucketsJob` + config-change dispatch**
   - _Goal:_ FR-062-07, G6, NFR-062-03 (job query-count bound).
   - _Preconditions:_ I2 complete (job's correctness is easiest to verify against a config surface that can no longer produce `OWNER_ID` at all).
   - _Steps:_ Write failing `RecomputeRootAlbumBucketsJobTest` first. Implement the job. Wire `SettingsController::setConfigs()`'s key-intersection dispatch (`Bus::fake()`-asserted). Extend `ALBUM_LISTING_COARSE_FLUSH_CONFIGS`.
   - _Commands:_ `php artisan test --filter=RecomputeRootAlbumBucketsJobTest`, `php artisan test --filter=SettingsControllerTest`.
   - _Exit:_ S-062-10/11/12 green.

4. **I4 – `AlbumRootController` (index/buckets/rights, scope-aware) + `GetScopedAlbumsRequest`**
   - _Goal:_ FR-062-01..06, FR-062-13 (root-tier cache keys).
   - _Preconditions:_ I1 (namespace exists), I3 (own-scope buckets must reflect a correctly-kept-live `bucket_id`).
   - _Steps:_ New `GetScopedAlbumsRequest` (scope validation per FR-062-02) — build it generically from the start, since I5 also consumes it for `persons()`/`pinned()`, not just root. New `AlbumRootController` — `index()`/`buckets()`/`rights()`. `own` scope reuses the exact query shape `AlbumChildrenController` already established in I1 (parameterized by `whereIsRoot()`+owner-equals instead of `where('parent_id',...)`). `shared` scope is new: owner-ordered children-data with the `bucket_id`-field repurposing (FR-062-04), live `GROUP BY owner_id` buckets. Buckets are **unconditionally `bucketable:true`** for `shared` scope — never `false`, even when the result is empty (Q-062-15; don't special-case an empty `GROUP BY` result the way the OWNER_ID short-circuit does). Bucket **labels** are gated on authentication (Q-062-14): authenticated → `users` join, `COALESCE(display_name, username)`; guest → skip the join entirely, hardcode every label to `"unknown"` (grouping/counts stay real either way). Rights mirrors FR-062-06: always-false delete/move for non-admin, and `owner_id` is **omitted from the JSON payload** (not just null) for both scopes via a conditional resource field (Q-062-16). New `CacheKeyProvider` methods. Register `/Albums/root`, `/Albums/root/buckets`, `/Albums/root/rights` ahead of `{album_id}`.
   - _Commands:_ `php artisan test --filter=AlbumRootV3Test`, `make phpstan`.
   - _Exit:_ S-062-01..09, S-062-18..20, S-062-29 green; NFR-062-01/02/05/08 evidenced.

5. **I5 – `AlbumCategoryController` (smart/persons/tags/tagsRights/pinned)**
   - _Goal:_ FR-062-09..11, FR-062-13 (persons/pinned cache-key widening), FR-062-15.
   - _Preconditions:_ I1 (namespace exists); I4 (`GetScopedAlbumsRequest` must already exist for `persons()`/`pinned()` to consume — controllers share this one request class).
   - _Steps:_ New `GetAlbumCategoryRequest` for the 3 remaining un-scoped methods. New `AlbumCategoryController` — `smart()` (reuses `AlbumFactory::getAllBuiltInSmartAlbums(false)` + `Gate::check`, no query), `tags()` (mirrors `Top::queryTagAlbums()`'s filter/sort exactly, `toBase()` instead of eager-loaded Eloquent), `tagsRights()` — these three consume `GetAlbumCategoryRequest`. `persons()` and `pinned()` both consume `GetScopedAlbumsRequest` instead (built in I4): each mirrors its respective `Top::queryPersonAlbums()`/`queryPinnedAlbums()`'s filter/join/sort exactly (`persons()` keeps the `ai_vision_face_enabled` empty-block gate regardless of scope; `pinned()` keeps its own `sorting_pinned_albums_col` config, `is_pinned` subquery, not restricted to root) but each adds the owner-equals/not-equals predicate per scope; `shared` scope returns one flat list for **both** — do **not** add any `GROUP BY`/ordering-by-owner logic to either, that's root-specific (FR-062-04/05) and explicitly not wanted here (NG9). No buckets method/route for `persons()` or `pinned()`. Widen `personAlbumsListingKey()`/`pinnedAlbumsListingKey()` with the `scope` argument. New `AlbumCategoryListResource`, `AlbumCategoryRightsResource`. Register the 5 routes ahead of `{album_id}`.
   - _Commands:_ `php artisan test --filter=AlbumCategoryV3Test`, `make phpstan`.
   - _Exit:_ S-062-14..18/22..27 green.

6. **I6 – Regression, quality gates, docs**
   - _Goal:_ NFR-062-04, FR-062-14 (cache-invalidation coverage — verify, don't just assert), full scenario matrix, documentation deliverables, NFR-062-09 file-count check.
   - _Preconditions:_ I1–I5 complete.
   - _Steps:_ Implementation Drift Gate diff check. Verify FR-062-14's claim directly: trigger each existing `ManagedCacheAlbumListingInvalidator`-handled event (album save/move/delete/visibility-change etc.) against a populated root/persons/pinned cache and confirm the new `rootAlbum*`/`pinnedAlbumsListing*`/`personAlbumsListing*` cache entries are actually evicted — do not just trust that `albumChildrenTag(null)` and the existing category tags already cover this because the spec says so. Full new-surface + Feature 061's unmodified regression suite + `--filter=Album`/`--filter=Settings`/`--filter=Cache`. `make phpstan`, `php-cs-fixer`. File-count tally against NFR-062-09. Update `api-design.md`, `database-schema.md`, `knowledge-map.md`, `roadmap.md`.
   - _Commands:_ `php artisan test --filter=Album`, `php artisan test --filter=Settings`, `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run --diff`.
   - _Exit:_ All quality gates clean; roadmap row updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-062-01..04 | I4 | Guest/authenticated scope validation. |
| S-062-05 | I4 | `own` scope, own-bucket mechanism reused. |
| S-062-06..07 | I4 | `shared` scope owner grouping + empty case. |
| S-062-08..09 | I4 | Root rights, admin vs non-admin. |
| S-062-10..12 | I3 | Config-change recompute dispatch. |
| S-062-13 | I2 | Enum removal + migration. |
| S-062-14 | I5 | Smart albums listing. |
| S-062-15 | I5 | Person/tag/pinned albums listing, zero eager loads. |
| S-062-16 | I5 | Tag albums rights. |
| S-062-17 | I5 | Per-category cache independence. |
| S-062-18 | I4/I5 | Flag off → 403. |
| S-062-19 | I6 | v2 byte-identical regression. |
| S-062-20 | I4 | `deduplicate_pinned_albums` preserved. |
| S-062-29 | I4 | Guest bucket labels are `"unknown"` with zero `users` join; grouping/counts stay real; authenticated caller sees real names on the identical fixture. |
| S-062-21 | I1 | Consolidation regression (061 tests unmodified). |
| S-062-23 | I5 | Pinned own/shared split; shared is flat, not per-owner. |
| S-062-24 | I5 | Pinned guest = public-only, no extra logic. |
| S-062-25 | I5 | No buckets route/response shape for pinned, ever. |
| S-062-22 | I5 | Pinned listing includes sub-albums, not root-restricted; own `sorting_pinned_albums_col` config unaffected by FR-062-08. |
| S-062-26 | I5 | Persons own/shared split; shared is flat, not per-owner. |
| S-062-27 | I5 | Persons guest = public-only; no buckets route for persons either. |
| S-062-28 | I1 | Sub-album route rename: old paths gone, new paths byte-identical response. |

## Analysis Gate

Not yet run — record date/reviewer and findings here once the spec is reviewed and implementation begins.

## Exit Criteria

- All FR/NFR rows in spec.md have a corresponding green test.
- Feature 061's pre-existing test files pass with zero assertion edits (URL-literal-only diff, per NFR-062-06).
- Full new `Feature_v3` test surface green; `--filter=Album`/`--filter=Settings`/`--filter=Cache` regression runs green.
- `make phpstan`: 0 errors on touched files. `php-cs-fixer`: clean.
- Implementation Drift Gate diff (v2 files) empty.
- File-count ceiling (NFR-062-09) met.
- Docs updated (api-design.md, database-schema.md, knowledge-map.md, roadmap.md).
- Deployer-facing note about re-running `lychee:recompute-album-buckets` post-migration is present somewhere a real deployer will see it (migration comment at minimum; consider a roadmap/changelog line).

## Follow-ups / Backlog

- Frontend-adoption feature (mirrors Feature 063) to wire the v8 root/landing view to these 8 endpoints and retire `Top`/`RootAlbumResource`/`TopAlbumDTO` reliance from v8.
- Person/smart/pinned album rights endpoints, if a future need arises (NG4/FR-062-11 — technically straightforward, mirrors FR-062-10, just not requested now).
