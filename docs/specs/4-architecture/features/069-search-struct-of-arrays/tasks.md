# Feature 069 Tasks – Search Struct-of-Arrays

_Status: Draft_  
_Last updated: 2026-09-22_

> Keep this checklist aligned with the [plan](plan.md) increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [open-questions.md](../../open-questions.md) instead of informal notes.

## Checklist

### I0 – Analysis Gate

- [x] T-069-00 – Run the Implementation Drift Gate (plan.md).  
  _Intent:_ Re-read in full every file cited in spec.md/plan.md and confirm signatures still hold: `BuildAlbumDataResource::do()`, `QueryPhotoRatios`/`QueryPhotoDetails`, `GrantsAlbumRights`, `QueryRightsForMatchingAlbums`, `QueryMapPhotos`'s collapse pass, `PhotoQueryPolicy::applySearchabilityFilter()`, `AlbumSearch::queryAlbums()`, `PhotoSearch::sqlQuery()`, `SortingDecorator`, `PhotoGridVirtual.vue`'s `source` branches, `adaptPhotoTile.ts`. Confirm assumptions A1–A4.  
  _Verification commands:_ none (review task)  
  _Notes:_ Record findings in plan.md's Analysis Gate section, including any resolved drift.

### I1 – Config rename (FR-069-03, NFR-069-09, S-069-33)

- [x] T-069-01 – Inventory every `search_pagination_limit` reference.  
  _Intent:_ grep `app/`, `database/`, `lang/`, `resources/js/`, `tests/`; list them in plan.md before changing anything.  
  _Verification commands:_ none (review task)

- [x] T-069-03 – Migration renaming `search_pagination_limit` → `search_result_limit`.  
  _Intent:_ Rename in place so the operator's tuned value and the row's `cat`/`type_range`/`order`/`level` survive; update the description to "maximum photo hits returned per search".  
  _Verification commands:_ `make phpstan`, plus the v2 search suites which read the renamed key  
  _Notes:_ **No migration test** — this project does not test migrations (owner direction, 2026-09-22). The rename is covered indirectly: every v2 search suite reads `search_result_limit` through `SearchController`, so a failed rename breaks them.

- [x] T-069-04 – Update the v2 read site and all 22 locale files.  
  _Intent:_ v2's paginator reads the renamed key (its own meaning as a page size is unchanged); update `lang/en/all_settings.php` and propagate English placeholders to the other 21 locales; regenerate `lang/*.json`.  
  _Verification commands:_ `php artisan lang:json`, `php artisan test --filter=LangTest`  
  _Notes:_ `lang/<locale>/*.php` is hand-edited source; never hand-edit `lang/<locale>.json`. Do **not** run `php-cs-fixer` over `lang/*.php` — it reformats the whole file.

### I2 – Search photo source (FR-069-04/05/12/13, NFR-069-05)

- [x] T-069-05 – Failing test: a photo in 3 albums appears exactly once (S-069-08).  
  _Verification commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`

- [x] T-069-06 – Failing test: `album_ids[i]` is the lowest *accessible* album id (S-069-11).  
  _Intent:_ Include a photo whose lowest-`_lft` album the caller cannot access, to prove the accessibility filter is applied before the collapse.  
  _Verification commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`

- [x] T-069-07 – Failing tests: origin-album subtree scoping, and smart/tag/person album treated as no origin (S-069-12, S-069-13).  
  _Verification commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`

- [x] T-069-08 – Failing test: NSFW excluded/included per `hide_nsfw_in_search` (S-069-15).  
  _Verification commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`

- [x] T-069-09 – Implement `App\Actions\Search\StructOfArrays\ResolvesSearchPhotoSource`.  
  _Intent:_ Base photo query from tokens + optional origin via `PhotoSearch`'s existing strategy registry; dedup via `whereIn('photos.id', …)`; `applySearchabilityFilter()` unchanged.  
  _Verification commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`, `make phpstan`

- [x] T-069-10 – Implement the `album_ids` join-and-collapse pass.  
  _Intent:_ Mirror `QueryMapPhotos`' mechanism verbatim — `photo_album` → `base_albums` → `computed_access_permissions`, `appendAccessibilityConditions()`, `GROUP BY` + `MIN()`. No model hydration, no per-photo re-check (NFR-069-05).  
  _Verification commands:_ `php artisan test --filter=ResolvesSearchPhotoSourceTest`, `make phpstan`

### I3 – Generalize the photo tier actions (plan R1)

- [x] T-069-11 – Add `fromQuery()` to `QueryPhotoRatios`/`QueryPhotoDetails`; existing `do()` delegates.  
  _Intent:_ Additive only; no behaviour change. Regression-assert Feature 064/066 responses are byte-for-byte identical, the same discipline Feature 068 applied to `Flow::do()`.  
  _Verification commands:_ `php artisan test --filter=QueryPhoto`, `php artisan test --filter=Timeline`, `make phpstan`  
  _Notes:_ If the refactor turns out to be more invasive than additive, stop and log a question rather than reshaping shipped Feature 064/066 code.

### I4 – Tier 2 (FR-069-01/02/14, NFR-069-01..04)

- [x] T-069-12 – Failing tests: cap boundary (S-069-09, S-069-10).  
  _Intent:_ `limit + 1` matches ⇒ `limit` rows + `is_truncated: true`; exactly `limit` matches ⇒ all rows + `is_truncated: false`.  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotosTest`

- [x] T-069-13 – Failing test: rating fields omitted entirely when gated off (S-069-16).  
  _Intent:_ Assert the key is *absent*, not null-filled (`Optional::create()` semantics).  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotosTest`

- [x] T-069-14 – Failing test: `title` sort uses natural ordering with an explicit column prefix (S-069-21, plan R2).  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotosTest`

- [x] T-069-15 – Implement `App\Http\Resources\V3\SearchPhotoResource` (DO-069-01).  
  _Intent:_ `PhotoRatioResource`'s fields minus `bucket_ids`, plus `album_ids[]` and `is_truncated`.  
  _Verification commands:_ `make phpstan`

- [x] T-069-16 – Implement `App\Actions\Search\StructOfArrays\QuerySearchPhotos`.  
  _Intent:_ `toBase()` only; ordering before the `limit + 1` fetch; every gate resolved once per request; no `Carbon`.  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotosTest`, `make phpstan`

### I5 – Album tier (FR-069-07/08/09)

- [x] T-069-17 – Failing test: rights for a multi-parent set (S-069-14).  
  _Intent:_ `owner_id` key absent; `can_delete_children`/`can_move_children` both false; `grants_edit`/`grants_download` index-aligned.  
  _Verification commands:_ `php artisan test --filter=QuerySearchAlbumsTest`

- [x] T-069-18 – Failing test: a photo-only token yields zero albums, never all (S-069-20).  
  _Intent:_ Parity with `addSearchCondition()`'s `whereRaw('1 = 0')` guard.  
  _Verification commands:_ `php artisan test --filter=QuerySearchAlbumsTest`

- [x] T-069-19 – Add `AlbumSearch::sqlQueryAlbums()`, then implement `QuerySearchAlbums` feeding `BuildAlbumDataResource::do()`.  
  _Intent:_ Per drift finding D3, `queryAlbums()` cannot be reused (it returns a `Collection` and applies a conflicting manual `join('base_albums', …)`). Add a public builder-returning sibling mirroring `PhotoSearch`'s own `query()`/`sqlQuery()` pair, composing `joinBaseAlbumOwnerId($q, 'albums.id')` + `joinSubComputedAccessPermissions($q, 'albums.id', 'left', '', false, $user)` + the existing private `addSearchCondition()` + `applyBrowsabilityFilter()`. Both joins are LEFT, so membership stays identical to v2. `BuildAlbumDataResource` is used unmodified; `queryAlbums()` is left untouched (NG5).  
  _Verification commands:_ `php artisan test --filter=QuerySearchAlbumsTest`, `make phpstan`

- [x] T-069-20 – Implement `QuerySearchAlbumRights` via `GrantsAlbumRights`.  
  _Verification commands:_ `php artisan test --filter=QuerySearchAlbumsTest`, `make phpstan`

### I6 – Tier 3 (FR-069-06, NFR-069-06)

- [x] T-069-21 – Failing tests: 300 ids ⇒ 200, 301 ⇒ 422, invisible id silently absent (S-069-18, S-069-19).  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotoDetailsTest`

- [x] T-069-22 – Failing test: EXIF/GPS/location omitted when gated off (S-069-17).  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotoDetailsTest`

- [x] T-069-23 – Implement `QuerySearchPhotoDetails` on I3's `fromQuery()`.  
  _Intent:_ The search predicate is the authorization filter; the projection is the existing `PhotoDetailResource` one, unchanged.  
  _Verification commands:_ `php artisan test --filter=QuerySearchPhotoDetailsTest`, `make phpstan`

### I7 – Requests, controller, routes, gating (FR-069-10/11)

- [x] T-069-24 – Failing tests: parameter validation (S-069-05, S-069-06, S-069-07).  
  _Intent:_ Missing `terms` ⇒ 422; non-base64 `terms` ⇒ 422; bad `sorting_column` ⇒ 422; `sorting_column` without `sorting_order` ⇒ 422.  
  _Verification commands:_ `php artisan test --filter=SearchV3GatingTest`

- [x] T-069-25 – Failing tests: gating (S-069-02, S-069-03, S-069-04).  
  _Intent:_ Guest with `search_public` on/off; feature flag off ⇒ 403 on all four routes and v2 unaffected.  
  _Verification commands:_ `php artisan test --filter=SearchV3GatingTest`

- [x] T-069-26 – Implement the four request classes.  
  _Intent:_ Reuse `HasSearchTokensTrait`/`HasAbstractAlbumTrait`; feature-flag check in `authorize()` before the existing `search_public` + `CAN_ACCESS` checks.  
  _Verification commands:_ `php artisan test --filter=SearchV3GatingTest`, `make phpstan`

- [x] T-069-27 – Implement `SearchListingController` and register the four routes.  
  _Intent:_ Register after the `/Albums/...` family in `routes/api_v3.php`; literal `/Search/albums` and `/Search/Photos` segments, no wildcard collision.  
  _Verification commands:_ `php artisan test --filter=SearchV3`, `make phpstan`

### I8 – Caching (FR-069-15)

- [x] T-069-28 – Failing tests: cache hit and cache isolation (S-069-23, S-069-24).  
  _Intent:_ Different terms, and different users, never share an entry.  
  _Verification commands:_ `php artisan test --filter=SearchV3CacheTest`

- [x] T-069-29 – Implement `CacheKeyProvider` methods and wire `rememberIf()` in all four actions.  
  _Intent:_ Key on token digest + origin + sort + user id + `unlockedAlbumsDigest()`. The digest, never the raw term, so no user-entered text reaches cache-event logs.  
  _Verification commands:_ `php artisan test --filter=SearchV3CacheTest`, `make phpstan`

- [x] T-069-30 – Wire invalidation tags for photo/album mutation.  
  _Verification commands:_ `php artisan test --filter=SearchV3CacheTest`, `make phpstan`

- [x] T-069-48 – Failing test: `ManagedCacheSearchListingInvalidatorTest` (S-069-33, S-069-34).  
  _Intent:_ Dispatch each album-data and access-permission event through the **real** dispatcher, not the listener directly — the listener body is one line, so the thing actually under test is the `EventServiceProvider` registration.  
  _Verification commands:_ `php artisan test --filter=ManagedCacheSearchListingInvalidatorTest`

- [x] T-069-49 – Register the album-data and access-permission events on `ManagedCacheSearchListingInvalidator` (FR-069-23, FR-069-24).  
  _Intent:_ T-069-30 wired the photo half only; the album half of the search cache was never invalidated, so a revoked grant could be replayed from cache (FR-069-24).  
  _Verification commands:_ `php artisan test --filter=ManagedCacheSearchListingInvalidatorTest`, `php artisan test --filter=SearchV3CacheTest`, `make phpstan`

### I9 – REST scenario + parity tests (NFR-069-06/07)

- [x] T-069-31 – `SearchV3PhotosTest` + `SearchV3AlbumsTest` (S-069-01).  
  _Verification commands:_ `php artisan test --filter=SearchV3PhotosTest`, `php artisan test --filter=SearchV3AlbumsTest`

- [x] T-069-32 – `SearchV3ParityTest` (S-069-22, NFR-069-06).  
  _Intent:_ Tier 2 + tier 3 reconstruct v2's `PhotoResource` field-for-field, except NG9's gaps. Fixture deliberately contains no multi-album photo (plan R6).  
  _Verification commands:_ `php artisan test --filter=SearchV3ParityTest`

- [x] T-069-33 – Confirm every existing v2 search test passes unmodified (S-069-31, S-069-32).  
  _Verification commands:_ `php artisan test --filter=SearchTest`, `php artisan test --filter=PhotoSearchTest`, `php artisan test --filter=AlbumSearchTest`, `php artisan test --filter=SearchSortingTest`, `php artisan test --filter=SearchTokenParserTest`  
  _Notes:_ If any needs editing, that is a regression — stop and investigate rather than adjusting the assertion.

### I10 – Frontend service + types

- [x] T-069-34 – `resources/js/services/search-v3-service.ts`.  
  _Intent:_ `getAlbums`/`getAlbumRights`/`getPhotos`/`getPhotoDetails`, mirroring `photo-children-v3-service.ts`'s cache-id conventions; keep the existing `base64encode()` helper for `terms`.  
  _Verification commands:_ `npm run check`

- [x] T-069-35 – Regenerate `resources/js/lychee.d.ts`.  
  _Intent:_ New `App.Http.Resources.V3.SearchPhotoResource` present.  
  _Verification commands:_ `npm run check`

### I11 – `SearchState.ts` v3 state (FR-069-16/20/21/22)

- [x] T-069-36 – Add `isSearchSoaActive` getter and v3 state fields.  
  _Intent:_ Tiles/rights/truncation held store-locally, not in shared `AlbumsState`/`PhotosState` (FR-069-20).  
  _Verification commands:_ `npm run check`

- [x] T-069-37 – Store-local → `photosStore` compaction for lightbox compatibility only (S-069-30).  
  _Intent:_ Mirror `TimelineState.ts`'s `_syncPhotosStoreV3()`; pass `album_ids[i]` as `adaptPhotoTile`'s third argument (A2 — no signature change).  
  _Verification commands:_ `npm run check`

- [x] T-069-38 – On-demand tier-3 details with dedup + 300-id chunking (S-069-28).  
  _Intent:_ Lightbox open prefetches the photo plus its two neighbours; grid scrolling never triggers a fetch.  
  _Verification commands:_ `npm run check`

- [x] T-069-39 – Reuse the existing `requestToken` guard for v3 responses (S-069-29).  
  _Verification commands:_ `npm run check`

### I12 – View layer (FR-069-17/18/19)

- [x] T-069-40 – Add `"search"` to `PhotoGridVirtual.vue`'s `source` prop.  
  _Intent:_ Every divergent spot is already a `source.value === …` branch; confirm A3 (the `bucketable: false` flat-chunk path).  
  _Verification commands:_ `npm run check`

- [x] T-069-41 – v3 branch of `ResultPanel.vue`.  
  _Intent:_ `PhotoGridVirtual source="search"` for the photo half; no `UPagination` on this path. **Deviation (D6):** the album half renders through a new, small, non-virtualized `SearchAlbumGridV3.vue` rather than `AlbumThumbGridVirtual`. That component reads its tiles/buckets/boundaries from `AlbumState`/`AlbumsState` rather than props, so reusing it would mean either pushing search results through the album-browsing stores (the state bleed FR-069-20 exists to stop — and it would read a *previously browsed album's* boundaries) or adding a prop-override path to a shipped component. Album hit counts are small by the same premise that gave the album half no bucket tier (Q-069-03), so a plain responsive grid is the right shape. The tile itself (`AlbumThumbVirtual.vue`) is reused unmodified — it is fully prop-driven and already renders covers via `<Thumb>`.  
  _Verification commands:_ `npm run check`

- [x] T-069-42 – v3 branch of `Search.vue` without the nested scroll wrapper (plan R5).  
  _Intent:_ Drop `overflow-y-auto`/`h-[calc(100vh-3.5rem)]` on the v3 path **only**; the v2 branch keeps its wrapper.  
  _Verification commands:_ `npm run check`

- [x] T-069-43 – Truncation hint row (UI-069-03, S-069-27).  
  _Intent:_ Rendered only when `is_truncated` is true; absent entirely otherwise. New translation key, propagated to all 22 locales.  
  _Verification commands:_ `php artisan lang:json`, `php artisan test --filter=LangTest`, `npm run check`

- [ ] T-069-44 – Manual browser verification of S-069-25, S-069-26, S-069-27, S-069-28.  
  _Intent:_ Flag off ⇒ today's behaviour; flag on ⇒ virtualized grids, window scroll, hint, on-demand details.  
  _Verification commands:_ manual  
  _Notes:_ If no browser/dev environment is available, leave unchecked and say so — do not claim it.

### I13 – Docs and gates

- [ ] T-069-45 – Knowledge-map section for Feature 069.  
  _Verification commands:_ none (docs)

- [ ] T-069-46 – Roadmap entry; refresh `_current-session.md`.  
  _Verification commands:_ none (docs)

- [ ] T-069-47 – Full quality gate (NFR-069-08, NFR-069-10).  
  _Intent:_ Also confirm the two cross-cutting NFRs the increment headers do not name: licence headers + `===`/no-`empty()`/`in_array(..., true)`/snake_case across every new file (NFR-069-08), and that no new external network dependency was introduced anywhere in the feature (NFR-069-10).  
  _Verification commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `make phpstan`, scoped `php artisan test --filter=…` runs for every touched test class  
  _Notes:_ Never run `php artisan test` unfiltered or a whole `--testsuite=`; always scope to `--filter=<ClassName>`.

### I14 – Review follow-ups (PR #4777)

- [x] T-069-50 – Failing test + fix: origin-scoped `album_ids` must skip an inaccessible in-subtree album (FR-069-25, S-069-35).  
  _Intent:_ `resolveForSubtree()` trusted the subtree bound alone; the viewer-owned-photo escape in `appendSearchabilityConditions()` makes that unsound.  
  _Verification commands:_ `php artisan test --filter=SearchPhotoSourceTest`, `make phpstan`

- [x] T-069-51 – Keep the photo tier's `ratios` in `SearchState` and feed them to the analytic layout (FR-069-26, S-069-36).  
  _Intent:_ `PhotoGridVirtual` was reading `albumStore.photoRatiosV3` for `source="search"` — empty on an unscoped search, another album's numbers on a scoped one.  
  _Verification commands:_ `npm run check`

- [x] T-069-52 – Resolve album selection, the context-menu gate and `noData` from `albumTilesV3` on the v3 path (FR-069-26, S-069-37).  
  _Intent:_ `useSelection()` gains an optional album-pool override rather than the v3 path writing into `AlbumsState` (FR-069-20 forbids that).  
  _Verification commands:_ `npm run check`

- [x] T-069-53 – Await the scoped album's refresh before starting the search request (FR-069-27, S-069-38).  
  _Verification commands:_ `npm run check`

- [x] T-069-54 – Route the tier-3 details fetch by who last wrote `photosStore.photos`, not by tile-id membership.  
  _Intent:_ `SearchState` is only cleared explicitly, so after navigating from a search into an album a photo present in both routed its details fetch to the search loader and left the album tile unresolved.  
  _Verification commands:_ `npm run check`

- [x] T-069-56 – Count only interactable entries in `selectEverything()` (FR-069-26, S-069-39).  
  _Intent:_ The branch conditions counted the raw pools while the assignments filtered by `canInteractAlbum()`/`canInteractPhoto()`. Latent before this feature — the browsing pool is usually uniformly interactable — but the v3 search pool spans owners, so mixed rights are normal there.  
  _Verification commands:_ `npm run check`

- [ ] T-069-55 – Manual browser verification of S-069-36, S-069-37, S-069-38, S-069-39.  
  _Verification commands:_ manual  
  _Notes:_ Same constraint as T-069-44 — leave unchecked while no browser/dev environment is available.

## Notes / TODOs

- T-069-44 is the one genuinely browser-only task; every other verification is automated.
- T-069-48/49 are numbered after I13 but belong to I8: they close a gap found in T-069-30 after the rest of the feature had shipped.
- I14 collects the fixes made in response to the review on PR #4777; each one is a defect in already-written Feature 069 code, not new scope.
- Increment I3 touches shipped Feature 064/066 code. It must stay strictly additive — if it cannot, stop and log a question rather than reshaping working code.
- The `is_truncated` flag lives on the tier-2 photo resource only; the album tier is uncapped, matching v2's own unbounded album behaviour.
