# Current Session

_Last updated: 2026-09-23_

## Active Features

- **Feature 069 – Search Struct-of-Arrays**: **Implemented, review follow-ups applied.** 53 of 55 tasks `[x]`; the two remaining (T-069-44, T-069-55) are browser-only manual checks that were not performed — no dev environment available. Branch `search-v3`, PR [#4777](https://github.com/LycheeOrg/Lychee/pull/4777), uncommitted.

No other features are Active or Paused. Features 062, 064/065, 066, 067 and 068 are Completed — see roadmap.md.

## Session Summary

### Feature 069 — Review follow-ups from PR #4777 (2026-09-23)

Six review findings were raised on the PR; all six were verified against the code and all six were real. Fixed, with the spec extended (FR-069-23…27, S-069-33…38) and the tasks recorded as increment I14 (T-069-48…55).

- **Cache invalidation was photo-only (security).** `ManagedCacheSearchListingInvalidator` was wired to six photo events and no album or permission ones, so a cached `/Search/albums` result kept being replayed after a share was revoked — a cache hit never re-runs the browsability filter. Eight more events now evict it. The new `ManagedCacheSearchListingInvalidatorTest` asserts the *registration* (via the dispatcher's raw listener map), since the handler itself is one unconditional line.
- **`album_ids` could name an inaccessible album.** `SearchPhotoSource::resolveForSubtree()` trusted the origin's subtree bound alone, but `appendSearchabilityConditions()` admits a viewer-owned photo regardless of its albums' reachability — so a photo could report an album the viewer cannot enter, 403-ing its own Asset URL. It now applies the same accessibility predicate the unscoped branch does. **Feature 067's `QueryMapPhotos::resolveAlbumIdsForSubtree()` has the identical gap** (this code was modelled on it) and was left untouched as out of scope — worth a follow-up.
- **Search tiles laid out from the wrong ratios.** `PhotoGridVirtual` read `albumStore.photoRatiosV3` for `source="search"`: empty unscoped, another album's numbers when scoped. `SearchState` now keeps its own.
- **v3 album hits were invisible to selection, the context menu and the empty-state check**, all of which still read `albumsStore`. `useSelection()` gained an optional album-pool override rather than writing search results into the browsing store (FR-069-20 forbids that).
- **A refresh on an album-scoped search could replace the results** with the origin album's own photos — `albumStore.refresh()` repopulates the shared photo store and was racing the search. It is now awaited first.
- **Tier-3 details could be routed to the wrong loader.** The search branch matched on tile id, and `SearchState` outlives the search route, so a photo in both a search result and the album being browsed hit the search loader and left the album tile unresolved. `PhotosState` now records which v3 store last wrote `photos`.

Verified: `SearchPhotoSourceTest`, `QuerySearch*Test`, `SearchV3*Test`, all four `ManagedCache*InvalidatorTest` classes, `make phpstan`, `npm run check`, `npx eslint`, `prettier --check`. Both browser-only scenarios remain unverified and are recorded as such.


### Feature 069 – Search Struct-of-Arrays — Specced, planned and implemented end to end (2026-09-22)

**Request:** "We implemented v3 Struct-of-Arrays for Timeline, Flow, Albums, Map, we are still missing it on the Search." Followed by "Implement !".

**Owner decisions (Q-069-01/02/03/05/10, all via `AskUserQuestion`):** dedicated `/api/v3/Search/*` family; **whole-scope unpaginated photos with no bucket tier** (Option B — chosen against the recommended bucket-windowed shape); flat SoA album listing + separate `/rights` tier; `date:` semantics carried over untouched; and, as a direct consequence of the unpaginated choice, `search_pagination_limit` renamed `search_result_limit` and repurposed as a hard cap with an `is_truncated` signal.

**Seven questions resolved directly** (Q-069-04/06/07/08/09 up front; **Q-069-11 and Q-069-12 surfaced during implementation** — see below).

**ADR-0010 created.** ADR-0009 fixed v3 collections' *shape* but never their *size*; three bounding strategies had accumulated across six features with no recorded rule for choosing, and Search needed a fourth. The ADR names all four (whole-scope / bucket-windowed / capped-with-truncation / capped-with-refusal) plus the selection rule, and retroactively describes 062/064/066/067/068 without altering them.

**Analysis gate PASSED**, then the **drift gate caught one real error in the plan (D3)**: assumption A1 was false. `BuildAlbumDataResource` selects `computed_access_permissions.password`, but `applyBrowsabilityFilter()` — unlike `applyVisibilityFilter()` — never calls `prepareModelQueryOrFail()` and so joins neither that nor the aliased `base_albums`. Feeding `AlbumSearch::queryAlbums()`'s query into the builder would have failed at SQL level. Prepending `applyVisibilityFilter()` was rejected (it adds a predicate v2's search never applied, i.e. a silent membership change inside a transport migration); instead a new public `AlbumSearch::sqlQueryAlbums()` composes `joinBaseAlbumOwnerId()` + `joinSubComputedAccessPermissions()` directly — both LEFT joins, so membership is provably unchanged.

**Built (backend):** config-rename migration; `SearchPhotoSource`; `QuerySearchPhotos` + new `SearchPhotoResource`; `QuerySearchAlbums`/`QuerySearchAlbumRights`; `QuerySearchPhotoDetails`; `GetSearchV3Request`/`GetSearchV3DetailsRequest`; `Base64EncodedRule`; `SearchListingController` + 4 routes; `CacheKeyProvider` search methods + `ManagedCacheSearchListingInvalidator`.

**Built (frontend):** `search-v3-service.ts`; `SearchState.ts`'s v3 half (`photoTilesV3`/`albumTilesV3`/`isTruncatedV3`, `isSearchSoaActive`, `searchV3()`, `loadPhotoDetailsV3()`); `SearchAlbumGridV3.vue`; v3 branches in `ResultPanel.vue` and `Search.vue`; `source="search"` in `PhotoGridVirtual.vue`; a search branch in `PhotoState.ts`'s details dispatcher; new `results_truncated` key across all 23 locales.

**Changes to shipped code — all strictly additive, all regression-verified:**
- `PhotoSearch::sqlQuery()` gained `$with_relations = true` (Feature 068's `Flow::do()` precedent).
- `QueryPhotoDetails` gained a `fromQuery()` sibling so both `details` tiers share one projection.
- The three ratio `size_variants` joins moved verbatim into a `JoinsRatioSizeVariants` trait — their aliases are load-bearing for the `COALESCE` that reads them, so a second hand-written copy would be a silent-breakage risk.
- `GrantsAlbumRights`' `$owner_id` widened to `string|Optional`.
- `adaptPhotoTile()`'s tier-2 param widened to `Omit<PhotoRatioResource, "bucket_ids">` (it never read that field).

**Two findings that only implementation could surface:**
- **Q-069-11** — FR-069-04's premise was wrong. A photo the owner has in *no* album is searchable in v2 (`appendSearchabilityConditions()` ORs in `owner_id`), so the join-and-collapse finds no album for it. Dropping it would be a silent membership change; it reports the `unsorted` smart album instead, which the Asset endpoint already accepts.
- **Q-069-12** — found by the parity test, not by review. v2 computes `should_downgrade` once per request from `grants_full_photo_access`; the shared Feature 064 projection evaluates `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` per photo. **User-visible:** `size_variants.original.url` can be present in v2 and null in v3 for the same photo. Deliberately not "fixed" — it is Feature 064's shipped behaviour, and overriding it would change that feature rather than this one.

**Two v2 defects fixed as documented divergences:** the missing `distinct()` that inflates `total` for multi-album photos (proven both ways by a test that asserts v2 returns 3 rows where v3 returns 1), and the album half's `owner` N+1.

**Verification:** 90 new tests across 8 classes, all green. All 137 pre-existing v2 search tests pass **unmodified** (NFR-069-07). Features 064/066/067/068 regression-checked. `make phpstan` 0 errors, `php-cs-fixer` clean, `npm run check` + `npm run format` clean, `LangTest` green.

**Not done:** T-069-44, the browser-only check of the v3 rendering path (flag on ⇒ virtualized grids, window scroll, truncation hint, on-demand details). No dev environment available; left unchecked rather than claimed.

## Carried Risks / Follow-ups

- **T-069-44 is the real gap.** The v3 frontend path type-checks and is structurally modelled on Timeline/Flow, but has never been rendered. The highest-risk parts are the dropped scroll wrapper in `Search.vue` and `PhotoGridVirtual`'s `bucketable:false` flat-chunk path under `source="search"` (assumption A3, still unverified).
- `docs/specs/6-decisions/project-constitution.md`, cited by the analysis-gate checklist as a required input, **does not exist** (gate deferral D1). Pre-existing gap; compliance was assessed against AGENTS.md instead.
- Spotlight quick-search stays on v2 (NG2); `date:`'s photo-vs-album semantic mismatch is untouched (NG3). Both are recorded follow-ups, not oversights.

## Environment Notes

- Branch `search-v3`. All Feature 069 work is uncommitted.
- Untracked scratch files present and deliberately left alone: `Todo.todo`, `dependencies.md`, `fixme`.
- `lang/*.json` is gitignored (generated by `php artisan lang:json`); only `lang/<locale>/*.php` is source.
