# Feature Plan 069 – Search Struct-of-Arrays

_Linked specification:_ [docs/specs/4-architecture/features/069-search-struct-of-arrays/spec.md](spec.md)  
_Status:_ Draft  
_Last updated:_ 2026-09-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

Search becomes the sixth and final v8 gallery view on the v3 Struct-of-Arrays contract, completing the line started by Features 062/064/066/067/068. Success signals:

- Four new `/api/v3/Search/*` routes, all `toBase()`-only, all gated by `features.struct-of-array`, all managed-cache wrapped.
- The v8 Search page renders through the same virtualization stack as every other view, with page-jump pagination gone.
- v2's `GET /Search`, the v7 frontend, and `SpotlightSearch.vue` are provably unaffected — their existing tests pass unmodified.
- `make phpstan` 0 errors, `php-cs-fixer` clean, `npm run check` green, all new and existing search tests green.

## Scope Alignment

- **In scope:** the four v3 routes and their request/resource/query classes; the `search_pagination_limit` → `search_result_limit` rename; managed-cache keys and invalidation; the v8 Search page's v3 path (service, store, view, result panel, `PhotoGridVirtual` `source="search"`); the truncation hint; docs.
- **Out of scope:** everything in spec.md's Non-Goals — no bucket tier (NG1), no Spotlight migration (NG2), no `date:` semantic change (NG3), no token-grammar change (NG4), no v2 endpoint change (NG5), no `Search::init` port (NG6), no FTS (NG7), no thumbnail bytes in responses (NG8), no fix for Feature 065's `face_count`/`filesize` gaps (NG9).

## Dependencies & Interfaces

| Dependency | Use |
|------------|-----|
| `App\Actions\Search\PhotoSearch` / `AlbumSearch` + strategy registries | Reused verbatim for the **predicate**; only the projection changes. |
| `App\Actions\Search\SearchTokenParser`, `App\DTO\Search\SearchToken` | Reused verbatim (NG4). |
| `App\Actions\Album\StructOfArrays\BuildAlbumDataResource` | Accepts an arbitrary pre-filtered `Builder<Album>` — feeds the album tier with zero new projection code. |
| `App\Actions\Album\StructOfArrays\Traits\GrantsAlbumRights`, `QueryRightsForMatchingAlbums` | Precedent and shared logic for the heterogeneous-parent rights case (FR-069-09). |
| `App\Actions\Photo\StructOfArrays\QueryPhotoRatios` / `QueryPhotoDetails` | Source of the tier-2/tier-3 projection to be generalized (see Risks). |
| `App\Actions\Map\QueryMapPhotos` | Reference implementation for the `album_ids[]` join-and-collapse (FR-069-04, NFR-069-05). |
| `App\Policies\PhotoQueryPolicy` / `AlbumQueryPolicy` | Visibility filters, unchanged (FR-069-13). |
| `ManagedCacheService`, `CacheKeyProvider` | Response caching (FR-069-15). |
| `PhotoGridVirtual.vue` (`source` prop), `AlbumThumbGridVirtual.vue`, `<Thumb>`, `ThumbAssetService`, `adaptPhotoTile.ts`, `adaptAlbumChildTile.ts` | Frontend reuse; `adaptPhotoTile` needs **no** change (see Assumptions). |
| `TimelineState.ts` `tilesV3`/`_syncPhotosStoreV3()` | Pattern for FR-069-20's store isolation. |

## Assumptions & Risks

**Assumptions**

- ~~A1~~ — **Disproved by the drift gate (D3).** `applyBrowsabilityFilter()` never joins `computed_access_permissions`, which `BuildAlbumDataResource` selects `password` from. Resolved by composing `joinBaseAlbumOwnerId()` + `joinSubComputedAccessPermissions()` directly (both already `public`, both LEFT joins, so membership is provably unchanged) via a new `AlbumSearch::sqlQueryAlbums()`. `BuildAlbumDataResource` itself is still used unmodified.
- A2 — `adaptPhotoTile(i, ratios, album_id)` needs no signature change: its third parameter is already per-call, so the search store passes `album_ids[i]`. Confirmed by reading the function.
- A3 — `PhotoGridVirtual.vue`'s `bucketable: false` path renders one flat chunk correctly, since the album path already relies on it for `OWNER_ID`-sorted albums.
- A4 — Search's sort options (`title`/`created_at`/`taken_at`) all map onto `ColumnSortingType` values `SortingDecorator` already handles, including Feature 060's raw title ordering.

**Risks / Mitigations**

- R1 — **Projection duplication.** The tier-2/tier-3 bodies overlap heavily with `QueryPhotoRatios`/`QueryPhotoDetails`, which today take an `AbstractAlbum` via `ResolvesPhotoSource`. *Mitigation:* generalize those actions with an additive `fromQuery(FixedQueryBuilder $query, …)` entry point and have the existing `do()` delegate to it, so search supplies its own base query and no projection is copied. Verify byte-for-byte that existing Feature 064/066 responses are unchanged (the same discipline Feature 068 applied to `Flow::do()`'s `$with_relations` param).
- R2 — **Unqualified `ORDER BY` ambiguity.** `ColumnSortingType::getRawOrderExpression()` emits unqualified `title_base`/`title_index` when the prefix is empty. The photo query joins `photo_album`/`albums`, and the album query joins `base_albums`. *Mitigation:* pass an explicit `photos.`/`base_albums.` prefix in both tiers rather than relying on the empty default; assert ordering in tests on both drivers.
- R3 — **Cap interacts with ordering.** `is_truncated` is only meaningful if the `limit + 1` fetch happens *after* ordering. *Mitigation:* ordering is applied by `SortingDecorator` before the limit; covered by S-069-09/10's boundary tests.
- R4 — **Config rename blast radius.** `search_pagination_limit` is referenced by the v2 controller, the settings UI and 22 locale files. *Mitigation:* v2 keeps reading the renamed key (its meaning as a page size is unchanged for v2's own paginator); grep for every reference before the migration; regenerate `lang/*.json` via `php artisan lang:json`, never by hand.
- R5 — **Nested scroll container removal.** Dropping `Search.vue`'s `overflow-y-auto` wrapper is the exact pitfall `Timeline.vue:30-38` and `Flow.vue:30-39` document. *Mitigation:* do it only inside the v3 branch of the template, leaving the v2 branch's wrapper intact.
- R6 — **Parity test sensitivity.** NFR-069-06's v2-vs-v3 comparison will legitimately differ on multi-album photos (Q-069-08). *Mitigation:* the parity fixture deliberately contains no multi-album photo; a separate test (S-069-08) covers the divergence explicitly.

## Implementation Drift Gate

Before I2, re-read every file cited in spec.md and this plan **in full** and confirm the signatures/line references still hold — specifically `BuildAlbumDataResource::do()`, `QueryPhotoRatios`/`QueryPhotoDetails`'s entry points, `GrantsAlbumRights`, `QueryMapPhotos`'s collapse pass, `PhotoQueryPolicy::applySearchabilityFilter()`, `AlbumSearch::queryAlbums()`, and `PhotoGridVirtual.vue`'s `source` branches. Record findings in this section as an "Analysis Gate" subsection, including any resolved drift, before writing code. Re-run the gate if implementation pauses for more than a session.

## Increment Map

1. **I1 – Config rename `search_pagination_limit` → `search_result_limit`**
   - _Goal:_ FR-069-03, NFR-069-09.
   - _Preconditions:_ Analysis Gate section started.
   - _Steps:_ grep every reference; write the migration preserving the stored value; update the v2 controller's read; update `lang/en/all_settings.php` and propagate placeholders to the other 21 locales; regenerate `lang/*.json` via `php artisan lang:json`.
   - _Commands:_ `php artisan test --filter=LangTest`, `make phpstan`
   - _Exit:_ `LangTest::testLanguageConsistency` green; v2 search tests still green (they read the renamed key, so they are the rename's real coverage — migrations are not unit-tested here, per owner direction).

2. **I2 – Search photo source: dedup + `album_ids` collapse**
   - _Goal:_ FR-069-04, FR-069-05, FR-069-12, FR-069-13, NFR-069-05.
   - _Steps:_ failing unit tests first; then `App\Actions\Search\StructOfArrays\ResolvesSearchPhotoSource` — builds the base photo query from tokens + optional origin via `PhotoSearch`'s existing strategies, dedups via `whereIn('photos.id', …)`, and provides the separate `album_ids` collapse pass modelled on `QueryMapPhotos`.
   - _Commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`, `make phpstan`
   - _Exit:_ S-069-08, S-069-11, S-069-12, S-069-13, S-069-15 green.

3. **I3 – Generalize `QueryPhotoRatios`/`QueryPhotoDetails` to accept a base query**
   - _Goal:_ R1 mitigation; no behaviour change.
   - _Steps:_ additive `fromQuery()` entry point; existing `do()` delegates; regression-assert Feature 064/066 responses byte-for-byte.
   - _Commands:_ `php artisan test --filter=QueryPhoto`, `php artisan test --filter=Timeline`, `make phpstan`
   - _Exit:_ Existing 064/066 tests green, unmodified.

4. **I4 – `SearchPhotoResource` + `QuerySearchPhotos` (tier 2)**
   - _Goal:_ FR-069-01, FR-069-02, FR-069-14, NFR-069-01..04.
   - _Steps:_ failing tests for the cap boundary and the once-per-request gates; then the resource (DO-069-01) and the query action, including the `limit + 1` fetch and `is_truncated`.
   - _Commands:_ `php artisan test --filter=QuerySearchPhotosTest`, `make phpstan`
   - _Exit:_ S-069-09, S-069-10, S-069-16, S-069-21 green.

5. **I5 – `QuerySearchAlbums` + `QuerySearchAlbumRights` (album tier)**
   - _Goal:_ FR-069-07, FR-069-08, FR-069-09.
   - _Steps:_ add a public `AlbumSearch::sqlQueryAlbums()` returning the builder (mirroring the existing `query()`/`sqlQuery()` pair on `PhotoSearch`), composing `joinBaseAlbumOwnerId()` + `joinSubComputedAccessPermissions()` + the existing private `addSearchCondition()` + `applyBrowsabilityFilter()` per drift finding D3; feed it into `BuildAlbumDataResource::do()` unmodified; build rights via `GrantsAlbumRights` with `owner_id` omitted and both `can_*_children` false. Leave `queryAlbums()` untouched (NG5).
   - _Commands:_ `php artisan test --filter=QuerySearchAlbums`, `make phpstan`
   - _Exit:_ S-069-14, S-069-20 green.

6. **I6 – `QuerySearchPhotoDetails` (tier 3)**
   - _Goal:_ FR-069-06, NFR-069-06.
   - _Steps:_ failing tests for the 300-id cap and the invisible-id case; then the action, reusing I3's `fromQuery()` with the search predicate as the authorization filter.
   - _Commands:_ `php artisan test --filter=QuerySearchPhotoDetails`, `make phpstan`
   - _Exit:_ S-069-18, S-069-19, S-069-22 green.

7. **I7 – Request classes, controller, routes, gating**
   - _Goal:_ FR-069-10, FR-069-11, API-069-01..04.
   - _Steps:_ four request classes reusing `HasSearchTokensTrait`; `SearchListingController`; route registration; feature-flag + `search_public` + `CAN_ACCESS` gating.
   - _Commands:_ `php artisan test --filter=SearchV3GatingTest`, `make phpstan`
   - _Exit:_ S-069-03..07 green.

8. **I8 – Managed-cache keys and invalidation**
   - _Goal:_ FR-069-15.
   - _Steps:_ `CacheKeyProvider` methods keyed on token digest + origin + sort + user + `unlockedAlbumsDigest()`; wire `rememberIf()` in all four actions; tags for invalidation on photo/album mutation.
   - _Commands:_ `php artisan test --filter=SearchV3Cache`, `make phpstan`
   - _Exit:_ S-069-23, S-069-24 green.

9. **I9 – REST scenario + parity tests**
   - _Goal:_ NFR-069-06, NFR-069-07.
   - _Steps:_ `SearchV3PhotosTest`, `SearchV3AlbumsTest`, `SearchV3DetailsTest`, `SearchV3ParityTest`; confirm every existing v2 search test passes unmodified.
   - _Commands:_ `php artisan test --filter=SearchV3`, `php artisan test --filter=SearchTest`, `php artisan test --filter=PhotoSearchTest`, `php artisan test --filter=AlbumSearchTest`, `php artisan test --filter=SearchSortingTest`
   - _Exit:_ S-069-01, S-069-02, S-069-22, S-069-32 green.

10. **I10 – Frontend service client + generated types**
    - _Goal:_ FR-069-16 (transport groundwork).
    - _Steps:_ `resources/js/services/search-v3-service.ts` mirroring `photo-children-v3-service.ts`'s shape; regenerate `lychee.d.ts`.
    - _Commands:_ `npm run check`
    - _Exit:_ New `App.Http.Resources.V3` types present; type-check green.

11. **I11 – `SearchState.ts` v3 state**
    - _Goal:_ FR-069-16, FR-069-20, FR-069-21, FR-069-22.
    - _Steps:_ `isSearchSoaActive` getter; v3 tiles/rights/truncation state held store-locally; `_syncPhotosStoreV3()`-style compaction for lightbox only; on-demand details with dedup + 300-id chunking; reuse the existing `requestToken` guard.
    - _Commands:_ `npm run check`
    - _Exit:_ S-069-29, S-069-30 covered by review; type-check green.

12. **I12 – `Search.vue` / `ResultPanel.vue` dual path + `source="search"`**
    - _Goal:_ FR-069-17, FR-069-18, FR-069-19.
    - _Steps:_ add `"search"` to `PhotoGridVirtual.vue`'s `source` prop; v3 branch of `ResultPanel.vue` rendering `AlbumThumbGridVirtual` + `PhotoGridVirtual`, no `UPagination`; v3 branch of `Search.vue` without the nested scroll wrapper (R5); truncation hint row (UI-069-03).
    - _Commands:_ `npm run format`, `npm run check`
    - _Exit:_ S-069-25, S-069-26, S-069-27, S-069-28 verified in a browser where available, otherwise recorded as unverified.

13. **I13 – Docs, knowledge map, quality gates**
    - _Goal:_ Documentation Deliverables.
    - _Steps:_ knowledge-map section; roadmap entry moved to Completed; `_current-session.md` refreshed; full quality gate.
    - _Commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `make phpstan`, scoped `php artisan test --filter=…` runs
    - _Exit:_ All gates green; every task `[x]`.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-069-01 | I9 / T-069-31 | End-to-end happy path. |
| S-069-02, S-069-03 | I7 / T-069-25 | `search_public` gating. |
| S-069-04 | I7 / T-069-25 | Feature-flag gating. |
| S-069-05, S-069-06, S-069-07 | I7 / T-069-24 | Parameter validation. |
| S-069-08 | I2 / T-069-05 | Dedup; documented divergence from v2. |
| S-069-09, S-069-10 | I4 / T-069-12 | Cap boundary, off-by-one. |
| S-069-11 | I2 / T-069-06 | `album_ids` collapse. |
| S-069-12, S-069-13 | I2 / T-069-07 | Origin-album scoping. |
| S-069-14 | I5 / T-069-17 | Heterogeneous-parent rights. |
| S-069-15 | I2 / T-069-08 | NSFW gating. |
| S-069-16 | I4 / T-069-13 | Rating gate resolved once per request. |
| S-069-17 | I6 / T-069-22 | EXIF/GPS/location gates. |
| S-069-18, S-069-19 | I6 / T-069-21 | Details scoping and 300-id cap. |
| S-069-20 | I5 / T-069-18 | Album `1=0` guard parity. |
| S-069-21 | I4 / T-069-14 | Natural title ordering (R2). |
| S-069-22 | I9 / T-069-32 | `PhotoResource` reconstruction parity. |
| S-069-23, S-069-24 | I8 / T-069-28 | Cache hit + isolation. |
| S-069-25, S-069-26 | I12 / T-069-44 | Dual-path rendering (browser-only). |
| S-069-27 | I12 / T-069-43 | Truncation hint. |
| S-069-28 | I11 / T-069-38 | On-demand details. |
| S-069-29 | I11 / T-069-39 | Stale-response guard. |
| S-069-30 | I11 / T-069-37 | Store isolation. |
| S-069-31, S-069-32 | I9 / T-069-33 | v2 and Spotlight untouched. |

## Analysis Gate

**Run:** 2026-09-22 · **Reviewer:** Claude (agent), pending owner acknowledgement · **Outcome:** PASS with two noted deferrals.

Checklist per [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md):

1. **Specification completeness**
   - ✅ Objectives, 22 FRs, 10 NFRs populated.
   - ✅ All 10 resolved questions (Q-069-01..10) encoded in the spec's normative sections, not only in the Appendix: Q-069-01→API-069-01..04; Q-069-02→NG1 + FR-069-01; Q-069-03→FR-069-07/08/09; Q-069-04→NG2; Q-069-05→NG3; Q-069-06→FR-069-11; Q-069-07→FR-069-04; Q-069-08→FR-069-05; Q-069-09→feature structure; Q-069-10→FR-069-02/03 + NFR-069-01.
   - ✅ ASCII mock-ups present for both the v2 and v3 paths, including the truncation hint.
2. **Open questions review**
   - ✅ Zero `Open` rows remain for Feature 069 (10 of 10 resolved; verified by grep).
   - ✅ ADR created: **ADR-0010** (v3 collection bounding strategies), generalising Q-069-02/Q-069-10. Linked from spec.md's NG1, FR-069-02, NFR-069-01 and Documentation Deliverables, and from Q-069-10's entry in the open-questions log. ADR-0009 (response shape) reviewed and confirmed unamended.
3. **Plan alignment**
   - ✅ Plan references the correct spec and tasks files; dependencies and success criteria match the spec's wording.
4. **Tasks coverage**
   - ✅ All 22 FRs map to at least one increment/task (verified by cross-reading the increment headers). NFR-069-08 (coding conventions) and NFR-069-10 (offline-only) are cross-cutting and covered by T-069-47's quality gate rather than by a dedicated task — recorded here deliberately rather than padding the checklist.
   - ✅ Every increment sequences failing tests before implementation (I2, I4, I5, I6, I7, I8 each open with explicit failing-test tasks).
   - ✅ All 48 tasks are self-contained slices scoped to ≤90 minutes.
   - ✅ All 33 scenarios are mapped in Scenario Tracking; success, validation and failure branches are each represented.
5. **Constitution compliance**
   - ⚠️ `docs/specs/6-decisions/project-constitution.md`, cited by the checklist as an input, **does not exist** in this repository. Pre-existing documentation gap, not introduced by this feature; compliance was assessed against AGENTS.md's Guardrails & Governance section instead. Logged as a follow-up below.
   - ✅ Spec-first, clarification-gate, test-first and documentation-sync principles all satisfied; no new dependencies are added, so dependency control is not engaged.
   - ✅ Control-flow complexity: the bounding logic is isolated in one helper (`limit + 1` fetch + truncation flag) rather than branching through the projection; gate resolution is hoisted once per request (FR-069-14) rather than branching per row.
   - ✅ ADRs reviewed for relevance to this feature: ADR-0008 (Asset endpoint authorization — governs how `album_ids[]` is consumed downstream) and ADR-0009 (SoA response shape). Neither requires amendment.
6. **Tooling readiness**
   - ✅ Verification commands documented against every task; all test runs scoped with `--filter=<ClassName>` per the standing instruction never to run the suite unfiltered.

### Implementation Drift Gate — run 2026-09-22 (T-069-00)

Re-read every cited file. One **real drift found and resolved**, plus two confirmations.

**D3 (drift, resolved) — Assumption A1 was false as written.** `BuildAlbumDataResource::do()` selects `computed_access_permissions.password` and `base_albums.*` columns, but `AlbumSearch::queryAlbums()` joins **only** `base_albums`, as a plain inner join on the real table, and `AlbumQueryPolicy::applyBrowsabilityFilter()` — unlike `applyVisibilityFilter()` — does **not** call `prepareModelQueryOrFail()` and therefore never joins `computed_access_permissions` (its own `appendUnreachableAlbumsCondition()` uses a correlated subquery with its own `inner_`-prefixed joins instead). Feeding `queryAlbums()`'s query straight into the builder would have failed at SQL level on an unknown column.

Two candidate fixes were considered:

- *Rejected* — prepend `applyVisibilityFilter()` (which joins both via `prepareModelQueryOrFail()`), matching `QueryChildrenForAlbum`'s composition. This also adds a `WHERE is_link_required = false OR owner_id = :uid` predicate that v2's search never applied. Browsability is *probably* strictly stronger for the target album (`appendUnreachableAlbumsCondition()` deliberately includes the target among the inner nodes it tests), but "probably" is not good enough against NFR-069-06/07's parity requirement, and a silent membership change inside a transport migration is exactly what NG3 exists to avoid.
- *Chosen* — compose the two joins directly. Both helpers are already `public`: `joinBaseAlbumOwnerId($query, 'albums.id')` (with `$full = true`, which supplies precisely `title`/`title_base`/`title_index`/`created_at`/`description`/`is_nsfw`/`is_pinned`/`owner_id`) and `joinSubComputedAccessPermissions($query, 'albums.id', 'left', '', false, $user)`. Both are LEFT joins, so **result membership is provably unchanged** from v2 — only the available column set grows.

Consequence for the plan: the v3 album tier cannot reuse `AlbumSearch::queryAlbums()` as-is, because that method both returns a `Collection` (it calls `->get()`) and applies the conflicting manual `join('base_albums', …)`. I5 instead adds a new **public `AlbumSearch::sqlQueryAlbums()`** returning the builder — mirroring the `query()`/`sqlQuery()` pair `PhotoSearch` already has in the same class — reusing the existing `private addSearchCondition()`. `queryAlbums()` itself is left untouched for v2 (NG5).

**Confirmed unchanged:** `BuildAlbumDataResource::do(Builder, ?User)`; `QueryRightsForMatchingAlbums`'s heterogeneous-parent pattern; `QueryMapPhotos`' collapse pass; `PhotoQueryPolicy::applySearchabilityFilter()`; `PhotoSearch::sqlQuery()` already returns a builder (no equivalent refactor needed on the photo side); `adaptPhotoTile()`'s per-call third argument (A2 holds).

**Still unverified:** A3 (`PhotoGridVirtual`'s `bucketable:false` flat-chunk path) — re-confirmed at I12, not here.

**D4 (lightweight adjustment, I2) — `ResolvesSearchPhotoSource` trait became a `SearchPhotoSource` class.** The plan named it after `App\Actions\Photo\StructOfArrays\ResolvesPhotoSource`/`App\Actions\Map\ResolvesMapPhotoSource`, both traits. Those are traits because they hang off an `AbstractAlbum` their consumers already hold; the search source has no album at all, takes only tokens plus an optional origin, and is therefore worth resolving from the container and testing directly. Implemented as `App\Actions\Search\StructOfArrays\SearchPhotoSource` with two public methods (`query()`, `resolveAlbumIds()`). No spec change — spec.md names no class for this.

**D5 (I2, confirmed empirically) — the Q-069-08 duplicate-row divergence is real and now has a regression guard.** `SearchPhotoSourceTest::testPhotoBelongingToThreeAlbumsIsReturnedExactlyOnce` asserts both halves: the v3 source returns the photo once, *and* the v2 query it derives from returns it three times for a photo in three albums. Without the second assertion the first would pass vacuously if the dedup were ever dropped.

**D6 (I12) — the album half uses a new non-virtualized grid, not `AlbumThumbGridVirtual`.** See T-069-41's note for the full rationale. Summary: that component is store-coupled, not prop-driven, and the stores it reads are the album-browsing ones a search must not touch (FR-069-20). A new `SearchAlbumGridV3.vue` reuses the prop-driven tile (`AlbumThumbVirtual.vue`) in a plain responsive grid instead. No shipped component changed.

**D7 (I12) — `PhotoState.ts`'s on-demand details dispatcher gained a search branch, checked first.** Ordering is load-bearing: an album-scoped search leaves `albumStore` holding the origin album, so `isPhotoSoaActive` can be true on the search route; falling through to it would fetch details from `/Albums/{origin}/Photos/details`, which silently returns nothing for any result outside that album. A search tile cannot be recognised by its `album_id` the way a Timeline tile can (it carries a real one, FR-069-04), so membership in `searchStore.photoTilesV3` is the gate.

**D8 (post-implementation, owner-directed) — the `details` tier's N+1 removed, and the `albums` eager load with it.** Owner direction: "downloadable & full size access needs to be computed without the N+1 issue" / "minimize the number of queries and eager loads."

`PhotoPolicy::canAccessFullPhoto()` reduces over `$photo->albums`, and each album's `public_permissions()`/`current_user_permissions()` reads that album's `access_permissions` relation. `QueryPhotoDetails` eager-loaded `albums` but not *their* permissions, so every (photo, album) pair cost a `select * from access_permissions where base_album_id in (?)` — **measured at 6 such queries for 4 photos**.

New `App\Actions\Photo\StructOfArrays\ResolvesPhotoGrants` + `App\DTO\PhotoGrants` resolve the same answer in **one grouped query**, reusing the `computed_access_permissions` sub-query the album policies already use: join to `photo_album`, `GROUP BY photo_id`, `MAX()`/`bool_or()` to OR the grants across every containing album — which is precisely `PhotoPolicy`'s own `reduction()` semantics. Ownership is read off the already-selected `photos.owner_id` (no query); `canSee()` needs no check because every row reaching the projection survived the tier's own visibility-filtered candidate query; an admin short-circuits without querying at all.

Because the Gate call was the *only* consumer of `albums`, that eager load was dropped from both entry points. Result on the same call: **6 permission queries → 1; 154 total → 95.** Equivalence proven by Feature 064's own `PhotoDetailsV3Test` (16 tests) and `SearchV3ParityTest` passing unchanged.

This benefits Features 064 and 066 as well, not just 069 — all three share this projection.

**Verified through the real HTTP stack:** `GET /api/v3/Search/Photos/details` for 3 photos issues **13 queries total** — one config load, one candidate query, four batched relation loads, one grouped grants query, and no lazy `access_permissions` loads. (An earlier note here claimed 88 redundant `configs` queries and a missing container binding; that was a test-harness artifact of calling the action directly and bypassing the `ResolveConfigs` middleware — withdrawn, see Q-069-14.)

**Deferrals / follow-ups from this gate**

- D1 — The missing `project-constitution.md` should either be written or the checklist's Inputs list corrected. Out of scope for Feature 069; carried to Follow-ups.
- D2 — Assumptions A1 (`BuildAlbumDataResource` accepts `AlbumSearch::queryAlbums()`'s query shape) and A3 (`PhotoGridVirtual`'s `bucketable:false` flat-chunk path) are asserted from reading, not from running code. Both are re-confirmed by T-069-00's drift gate before the increments that depend on them (I5 and I12 respectively).

## Exit Criteria

- All tasks in [tasks.md](tasks.md) marked `[x]`.
- All 33 scenarios covered by an automated test, or explicitly recorded as manually verified / not verifiable this session.
- `vendor/bin/php-cs-fixer fix` clean; `make phpstan` 0 errors; `npm run format` + `npm run check` green.
- Every pre-existing v2 search test passes **unmodified** (NFR-069-07).
- `resources/js/lychee.d.ts` regenerated and committed.
- roadmap.md, knowledge-map.md and `_current-session.md` updated.

## Follow-ups / Backlog

- Migrating `SpotlightSearch.vue` to v3 (NG2) if the v2 endpoint is ever retired.
- Unifying `date:` semantics across photos and albums (NG3, Q-069-05 Options B/C).
- A full-text/FTS index for plain-text matching (NG7) — the leading-`%` `LIKE` remains unindexable.
- Closing Feature 065's `face_count` / `preformatted.filesize` gaps (NG9) for every SoA consumer at once.
- Retiring `AlbumSearch::queryTagAlbums()` and `PhotoSearch::query()`, both dead code in `app/` (tests only).
- Writing `docs/specs/6-decisions/project-constitution.md`, or correcting the analysis-gate checklist's Inputs list which cites it (gate deferral D1).
