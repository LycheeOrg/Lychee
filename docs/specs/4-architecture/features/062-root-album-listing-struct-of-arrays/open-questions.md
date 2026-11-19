# Open Questions – Feature 062

Open questions for [Feature 062](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-062-01~~ | 062 – Root Album Listing Struct-of-Arrays | High | Guest privacy when the effective root sort column is `OWNER_ID` — bucket by owner anyway vs. fall back to `bucketable:false` for guests | Superseded twice — first by Q-062-08 (`OWNER_ID` removed as a config value, original premise gone), then a same-day "guests see names too" follow-up was itself reversed by Q-062-14 (authoritative: guests never see real names, `users` join skipped entirely) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-02~~ | 062 – Root Album Listing Struct-of-Arrays | High | `scope` request shape — required `own`/`shared` split per call vs. one optional combined/unpartitioned list | Resolved (required `own`/`shared` for authenticated callers; guest defaults to, and is limited to, `shared` — `scope=own` for a guest is 422; FR-062-02) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-03~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | Route shape for a parent-less "children" concept — literal `/Albums/root/...` path vs. a new `::`-flat route family vs. `?parent_id=root` on the existing sub-album routes | Resolved (literal `/Albums/root`, `/Albums/root/buckets`, `/Albums/root/rights`, registered ahead of `/Albums/{album_id}/...`; dropped the "children" suffix — root is its own top-level listing, not children of a virtual parent; FR-062-01) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-04~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | Tag/person/pinned root categories — one combined endpoint vs. separate endpoints per category | Superseded (revised to **separate** routes per category — `/Albums/smart`, `/Albums/persons`, `/Albums/tags` — not one combined endpoint; `pinned` dropped from this feature's scope entirely; FR-062-09) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-05~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | Should a guest be allowed to request `scope=own`? | Resolved (no — 422; silently returning empty would hide a client bug) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-06~~ | 062 – Root Album Listing Struct-of-Arrays | High | How does `shared`-scope owner-grouping coexist with "we do not want to bucket by `owner_id`"? | Resolved (two independent mechanisms — the persisted `bucket_id` column stays exclusively date/title-derived everywhere, forever; `shared` scope's owner-grouping is a separate, live, read-time `GROUP BY owner_id` that never touches `AlbumBucketComputer`/the `bucket_id` column; the response's `bucket_id` field is repurposed to carry `owner_id` for that scope only, so the client-side "group rows by `bucket_id`" contract stays uniform across scopes; FR-062-04/05) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-07~~ | 062 – Root Album Listing Struct-of-Arrays | Low | Should `ColumnSortingType::OWNER_ID` (the broader, internal enum) also be removed alongside `ColumnSortingAlbumType::OWNER_ID`? | Resolved (no — only the configurable `ColumnSortingAlbumType::OWNER_ID` is removed; `ColumnSortingType::OWNER_ID` stays, needed internally for `Top::queryRootAlbums()`'s existing hardcoded sort and this feature's own `shared`-scope `ORDER BY owner_id`; NG7) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-08~~ | 062 – Root Album Listing Struct-of-Arrays | High | Remove `OWNER_ID` as a selectable `sorting_albums_col`/`album_sorting_col` value entirely, or keep supporting it (with owner-aware bucketing)? | Resolved (remove — user direction; `configs.type_range` already excludes it since Feature 060's dropdown narrowing, so this closes a dead config path rather than opening a new capability; migrate surviving `owner_id` values to `created_at`; FR-062-08) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-09~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | `pinned` albums — dropped from category-endpoint scope (Q-062-04) or reinstated? | Resolved (reinstated — `GET /Albums/pinned`, fifth flat category endpoint, no rights endpoint; per user follow-up "Oh good point, I forgot about the pinned albums. Add it back."; FR-062-09) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-10~~ | 062 – Root Album Listing Struct-of-Arrays | High | Should `/Albums/pinned` also get an own/shared split, and how should `shared` be structured (per-owner buckets like root, or flat)? | Resolved (yes — reuses root's `scope=own\|shared` via the same `GetScopedAlbumsRequest`; `shared` is one flat, ungrouped list, not per-owner buckets, per user follow-up: "the ones not owned... are all grouped" interpreted as one lump, consistent with pinned never being bucketable at all; FR-062-15) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-11~~ | 062 – Root Album Listing Struct-of-Arrays | High | Why is `/Albums/pinned` never bucketable, in either the date/title or owner-grouping sense? | Resolved (a pinned album's real tree position is arbitrary — not necessarily root — so its `bucket_id` is governed by its actual parent's sort settings; mixing values from unrelated parents into one pinned bucket list would be incoherent; per user: "this should solve the potential conflict between the bucket sizes as pinned albums are possibly not as root"; NG3/NG9) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-12~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | Should Feature 061's already-shipped `/Albums/{album_id}/children[/buckets\|/rights]` paths be renamed to drop `/children`, matching root's naming? | Resolved (yes, per user proposal — safe since Feature 061 shipped with no v8 frontend consumer yet; path-only rename, response shape untouched; existing 061 test files need only their request-URL literals updated, zero assertion changes; FR-062-01/12, NFR-062-06) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-13~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | Should `/Albums/persons` also get the own/shared split, given `PersonAlbum` rows carry a real `owner_id`? | Resolved (yes, per user proposal — reuses the exact pattern already established for `/Albums/pinned` (flat shared, no buckets); `/Albums/tags`/`/Albums/smart` stay un-scoped, not requested; FR-062-09/15) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-14~~ | 062 – Root Album Listing Struct-of-Arrays | High | Should unauthenticated guests receive real owner display names via `/Albums/root/buckets`'s `shared`-scope labels? | Resolved (Option B — no join/names for guests; grouping stays real, labels become `"unknown"`) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-15~~ | 062 – Root Album Listing Struct-of-Arrays | High | Does `bucketable:false` mean "structurally can't group" (Feature 061's convention) or "zero rows this time" (this spec's current FR-062-05 wording)? | Resolved (Option A — keep 061's meaning; empty shared result is `bucketable:true` with empty arrays) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-16~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | Is `/Albums/root/rights`'s `owner_id` field null for `scope=own` too, or only for `scope=shared`? | Resolved (Option A, refined — null unconditionally, both scopes; key omitted from the JSON payload entirely since it's always null for root) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-17~~ | 062 – Root Album Listing Struct-of-Arrays | Low | Does dropping `/children` from `/Albums/{album_id}` (and `/Albums/root`, `/persons`, `/pinned`) read as "fetch one album" when it actually returns a child/collection listing? | Resolved (Option A — keep the rename as specced, no change) | 2026-09-02 | 2026-09-02 |
| ~~Q-062-18~~ | 062 – Root Album Listing Struct-of-Arrays | High | With the SoA flag on, the gallery still calls v2 `GET /Albums` (full `Top::get()`) only to read `config` + `rights` — new v3 `/Albums/root/config` vs. reuse `Timeline::init` vs. embed in `/Albums/root` vs. keep the v2 call | Resolved (A — `GET /api/v3/Albums/root/config`, spec FR-062-17, S-062-30..33) | 2026-09-29 | 2026-09-29 |
| ~~Q-062-19~~ | 062 – Root Album Listing Struct-of-Arrays | High | `/Albums/smart` cover on an `album_user_thumbs` miss — resolve live and seed the cache vs. keep cache-only (FR-062-16), now that the SoA gallery no longer calls v2 `Top::get()` | Resolved (A — live resolution on a miss, spec FR-062-16, S-062-14, S-062-34) | 2026-09-30 | 2026-09-30 |
| ~~Q-062-20~~ | 062 – Root Album Listing Struct-of-Arrays | Medium | `on_this_day` cover cached in `album_user_thumbs` never expires — the row seeded on day N keeps serving day N's photo on every later day. Validate on read vs. daily scheduled purge vs. date-stamp column vs. never cache | Resolved (A — validate the cached row on read, spec FR-062-16, S-062-35, S-062-36) | 2026-10-03 | 2026-10-03 |

## Question Details

### ~~Q-062-20~~ · Daily expiry of the cached `on_this_day` cover ✅ RESOLVED

**Status:** Resolved (Option A, 2026-10-03 — folded into spec FR-062-16, S-062-35, S-062-36)
**Feature:** F-062 – Root Album Listing Struct-of-Arrays
**Preferred option:** Option A – Validate the cached row on read

**Question**
`OnThisDayAlbum`'s membership depends on the current date (`Carbon::today()` month/day), but its cover is cached per viewer in `album_user_thumbs` (FR-062-16, `CachesAlbumUserThumb::getCachedOrLiveThumb()`). Nothing invalidates that row when the day changes: `RecomputeAlbumUserThumbsJob` only runs on photo writes, and `PurgeAlbumUserThumbs` only on access revocation. The cover seeded on day N is served on every later day, even though its photo is no longer in the album. Laravel's scheduler (`app/Console/Kernel.php`) runs only where `schedule:run` is wired: `Dockerfile-legacy` adds a cron entry, the main `Dockerfile` and `docker-compose.yaml` do not. How should the `on_this_day` cover expire?

**SoA cover path.** The SoA root gallery gets covers in two steps: `GET /api/v3/Albums/smart` returns `cover_ids` (from the batched `album_user_thumbs` pluck, or `get_thumb()` on a miss), then each tile loads `GET /api/v3/Asset/on_this_day/{photo_id}/{size_variant}`. `GetPhotoAssetRequest::isPhotoOfAlbum()` lets that photo through if the viewer's `album_user_thumbs` row still points at it; otherwise it falls back to the live check `photos()->whereKey()->exists()`. On the client, `AlbumCategoryV3Service.getSmart()` goes through `axios-cache-interceptor` (in-memory, default 5-minute TTL, id `albums_v3_smart`), and `AlbumsState.baseSmartAlbums` keeps the adapted tiles until the next `loadSmartAlbumsV3()`. Neither endpoint is cached server-side. So the client can hold a `cover_id` for a while after the server's cached row has changed, and the row decides whether that old id still loads.

#### Option A (recommended) – Validate the cached row on read
- **Idea:** `BaseSmartAlbum` gets an overridable "is this cached cover still valid" hook (default: always valid). `OnThisDayAlbum` overrides it by checking the cached photo against its own smart condition (`photos()->whereKey($photo_id)->exists()`, a primary-key lookup through the same SQL as the live query, so timezone handling matches). A stale row is recomputed live and overwritten. `AlbumSmartController::smart()` routes `on_this_day` through `get_thumb()` instead of trusting the batched pluck.
- **Spec impact:** FR-062-16 gains the validity hook and the `on_this_day` exception from the batched lookup; new scenario for a row seeded on a previous day.
- **Pros:** correct on every install, with or without a scheduler or queue; self-heals at the first view of the day; one extra indexed query for one album; under SoA, the row is replaced in the same `/Albums/smart` request that sends the new `cover_id`, so the client never holds an id whose row has already gone (except a second open tab of the same viewer, until it reloads).
- **Cons:** `/Albums/smart` always runs one `photos` query (the PK check) for `on_this_day`, so the "zero photos query when fully cached" guarantee gets an exception; `GetPhotoAssetRequest::isComputedAlbumThumb()` keeps accepting yesterday's row until the viewer next loads the listing (harmless, ADR-0010 keeps the row's access valid).

#### Option B – Daily scheduled purge
- **Idea:** New artisan command deleting `album_user_thumbs` rows where `album_id = 'on_this_day'`, registered `->daily()` in `Console\Kernel::schedule()`. The next read re-seeds lazily.
- **Spec impact:** new FR for the command and schedule entry; no change to FR-062-16.
- **Pros:** literally "reset every day"; tiny change; read path untouched, the zero-query guarantee holds.
- **Cons:** does nothing where the scheduler is not running, which includes the main Docker image; under SoA, a tile still holding yesterday's `cover_id` (open tab, `baseSmartAlbums` not reloaded) loses its row at purge time, so its `/Asset/on_this_day/...` request falls to the live check and returns 403 (broken image) until the listing reloads; purge time follows the server timezone, not each viewer's day; rows stay stale if cron misses a run.

#### Option C – Date-stamp column on `album_user_thumbs`
- **Idea:** Add a `computed_on` date column, written by every seeding path; `on_this_day` rows whose date is not today count as a miss. The batched lookup filters on it.
- **Spec impact:** schema change (migration), every writer of the table updated, FR-062-16 amended.
- **Pros:** keeps the single batched query, so `/Albums/smart` keeps its zero-`photos`-query guarantee; generalises to any future date-dependent smart album; `isComputedAlbumThumb()` can ignore the date, so an old tile's asset still loads until the listing reloads.
- **Cons:** persistence change touching every write site (`CachesAlbumUserThumb`, `RecomputeAlbumUserThumbsJob`) for one album; stale rows still accumulate until overwritten.

#### Option D – Never cache the `on_this_day` cover
- **Idea:** Treat `on_this_day` like `SA_random_thumbs`: always compute live, never write a row.
- **Spec impact:** FR-062-16 exception for `on_this_day`.
- **Pros:** simplest; always correct.
- **Cons:** the live query filters on `MONTH()`/`DAY()` of `taken_at`/`created_at`, which no index serves, so every `/Albums/smart` call that misses the client cache (each page load, or after 5 minutes) scans the viewer's visible photos; asset requests also always take the live check.

### Q-062-19 · Smart-album cover on a cache miss

**Status:** Resolved (Option A, 2026-09-30 — folded into spec FR-062-16, S-062-14, S-062-34)
**Feature:** F-062 – Root Album Listing Struct-of-Arrays
**Preferred option:** Option A – Live resolution on a miss

**Question**
FR-062-16 makes `/Albums/smart` cache-only: an `album_user_thumbs` miss yields `cover_ids[i] = null`, relying on another path to seed the row. `RecomputeAlbumUserThumbsJob` only refreshes viewers who already have a row, and the only path that creates one for a smart album is `BaseSmartAlbum::getThumbAttribute()` (`CachesAlbumUserThumb::getCachedOrLiveThumb()`), reached through v2 `Top::get()`. With FR-062-17 the SoA gallery never calls `Top::get()`, so a new user, a first-time guest, or a viewer whose rows were purged (`PurgeAlbumUserThumbs`) keeps coverless smart-album tiles indefinitely. How should `/Albums/smart` handle a miss?

#### Option A (recommended) – Live resolution on a miss
- **Idea:** For each visible smart album absent from the batched `album_user_thumbs` lookup, read `$smart_album->get_thumb()`, which resolves live through the permission-filtered query and seeds the row. Hits stay one batched query.
- **Spec impact:** FR-062-16 changes from cache-only to cached-or-live; the "zero photos query" guarantee holds only when every cover is cached.
- **Pros:** covers appear on first view and self-heal after a purge; bounded to the handful of smart albums, only on a miss; reuses the existing seeding path.
- **Cons:** a cold request runs up to one live photos query per smart album (what v2 `Top::get()` did on every load); an empty smart album re-runs its live query on every load, since nothing is cached for it.

#### Option B – Keep cache-only, warm the cache elsewhere
- **Idea:** Keep `/Albums/smart` cache-only; dispatch `RecomputeAlbumUserThumbsJob` for missing (viewer, smart album) pairs, e.g. from the request, so a later load finds the row.
- **Spec impact:** FR-062-16 unchanged; new dispatch path.
- **Pros:** the endpoint itself never runs a photos query.
- **Cons:** the first load still shows no cover; the job only refreshes existing rows today, so it needs a new "create for viewer" mode; queue dependency for a display concern.

#### Option C – Accept coverless smart albums until another path seeds them
- **Idea:** No change.
- **Pros:** no work.
- **Cons:** with the v2 call gone, most viewers never get a smart-album cover.

### Q-062-18 · Drop the v2 `GET /Albums` call from the SoA root gallery

**Status:** Resolved (Option A, 2026-09-29 — folded into spec FR-062-17, DO-062-07, API-062-10, S-062-30..33)
**Feature:** F-062 – Root Album Listing Struct-of-Arrays
**Preferred option:** Option A – `GET /api/v3/Albums/root/config` returning `{config, rights}`

**Question**
With `is_struct_of_array_enabled` on, `AlbumsState.load()` still calls v2 `GET /api/v2/Albums`, which runs the full `Top::get()` (smart, tag, person, pinned, own and shared album queries with Eloquent hydration), only to read `config` (`RootConfig`) and `rights` (`RootAlbumRightsResource`). Every album list it returns is discarded. How should the SoA path obtain these two fields? There is no per-album or root `.../config` route today; `GET /api/v2/Timeline::init` (`InitResource`) already returns `new RootConfig()` + `new RootAlbumRightsResource()` with no album query, but without `login_required:root` and with timeline-only fields.

#### Option A (recommended) – New `GET /api/v3/Albums/root/config` returning `{config, rights}`
- **Idea:** A v3 route (middleware `login_required:root`, `cache_control`) returning a small resource holding the existing `RootConfig` and `RootAlbumRightsResource`, the same construction `Timeline::init` already uses. `AlbumsState.load()` calls it instead of v2 `GET /Albums` when the flag is on; the v2 call stays for the flag-off path.
- **Spec impact:** new FR in Feature 062 (route, request, resource); frontend `AlbumsState.load()` branches before the v2 call.
- **Pros:** removes the heaviest request on the root page; zero album queries; keeps the `login_required:root` 401 that opens the login modal; `config` and `rights` stay in one response, as the page consumes them today.
- **Cons:** one new route and resource.

#### Option B – Reuse `GET /api/v2/Timeline::init`
- **Idea:** Call `Timeline::init` from `AlbumsState.load()` when the flag is on and read its `config`/`rights`.
- **Spec impact:** frontend only, plus adding `login_required:root` to `Timeline::init` (or handling the private-gallery case elsewhere).
- **Pros:** no new route.
- **Cons:** couples the root gallery to a timeline endpoint; carries unused timeline fields; changing its middleware affects the Timeline page.

#### Option C – Add `config` to an existing v3 response
- **Idea:** Embed `RootConfig` in `GET /api/v3/Albums/root` and take rights from `GET /Rights`.
- **Spec impact:** changes the DO shape of an existing Feature 062 SoA resource.
- **Pros:** no new route.
- **Cons:** mixes page configuration into a SoA list resource (ADR-0009 shape); the `own`/`shared` scope split makes config duplicated or scope-dependent; the list is cached per user while config is global.

#### Option D – Keep the v2 call
- **Idea:** No change.
- **Pros:** no work.
- **Cons:** every root gallery load pays for the full v2 `Top::get()` plus a duplicate rights computation.

### ~~Q-062-14~~ · Guest exposure of owner display names via root's `shared`-scope bucket labels ✅ RESOLVED

**Status:** Resolved — Option B  
**Feature:** 062 – Root Album Listing Struct-of-Arrays  
**Resolved:** 2026-09-02

**Resolution:** The `users` join and label resolution are skipped entirely for unauthenticated callers — not just hidden after the fact, the join never executes (defense in depth, and avoids a pointless query). Owner-based grouping/counts are still computed for guests (the mechanism itself is unaffected — a guest still sees "3 buckets" if 3 distinct owners have shared albums), but every label falls back to the literal string `"unknown"`, mirroring the existing `bucket_id ?? 'unknown'` convention already used elsewhere in this endpoint family. Authenticated callers still get real `COALESCE(display_name, username)` labels, unchanged.

**Spec impact:** FR-062-05 gains an explicit guest branch (no join, `labels` hardcoded `'unknown'`); new scenario for guest label anonymization with real grouping intact.

**Preferred option:** 🅱️ (**recommended**) Option B – No names for guests

**Question**  
FR-062-05 has `/Albums/root/buckets`'s `shared` scope resolve bucket labels via `COALESCE(display_name, username)` joined on `owner_id`, with no authentication gate stated in the FR itself. Since guests default to `shared` scope (FR-062-02) and have no `own` scope available, this means an unauthenticated visitor to the gallery would see the real display names of every user who owns a visible root album, in bucket headers. An earlier draft of this spec had an explicit NFR forbidding this; it was dropped when `OWNER_ID` was removed as a *sort column* option, on the reasoning that "shared is the only thing a guest can request, so there's nothing left to protect." That reasoning conflates two different things (which sort *column* an admin can configure vs. whether label lookups run for anonymous callers) and was decided unilaterally during a fast pivot, not requested by the user. Should guests see real names here?

---

#### 🅰️ Option A – Guests see names (current spec text, unchanged)

- **Idea:** Keep FR-062-05 as written — the `users` join and label resolution run unconditionally for `shared` scope, regardless of caller.
- **Spec impact:** None — spec already reflects this.
- **Pros:**  
  - ✅ Simplest implementation — one code path, no guest branch.  
  - ✅ Matches the fact that "who shared this album" is arguably already implicit, visible content (the album itself is public).
- **Cons:**  
  - ❌ Exposes potentially real names (not usernames) of every contributing user to any anonymous visitor, without them opting in to that exposure.  
  - ❌ Reverses a more conservative decision from an earlier draft without an explicit product call.  
  - ❌ Inconsistent with this codebase's general instinct elsewhere (e.g. `AlbumAccessPermissionListController` deliberately narrows columns to avoid over-exposing user data) even though the concrete data here (display name) is lower-sensitivity than a password hash.

---

#### 🅱️ (**recommended**) Option B – No names for guests

- **Idea:** Restrict the `users` join / label resolution to authenticated callers only. A guest's `shared`-scope buckets still group by `owner_id` (the grouping/count mechanism is unaffected) but each label falls back to a neutral placeholder (e.g. `"unknown"`, mirroring the existing `bucket_id ?? 'unknown'` convention already used elsewhere in this same endpoint family) or the response omits `labels` for that scope when unauthenticated.
- **Spec impact:** FR-062-05 gains an explicit guest branch; NFR list gains back a privacy requirement (mirrors the dropped draft NFR-062-07); new scenario for "guest sees buckets but no names."
- **Pros:**  
  - ✅ No new exposure surface introduced by this feature.  
  - ✅ Matches the spirit of the original (pre-pivot) design intent.
- **Cons:**  
  - ❌ One more conditional branch in `AlbumRootController::buckets()`.  
  - ❌ A guest's bucket headers become less informative ("3 albums" instead of "Alice: 3 albums").
Owner to choose 🅰️ or 🅱️ before I1 — 🅰️ is the lower-risk default absent a demonstrated performance need.

---

**Next action**  

User to confirm which behavior is intended before FR-062-05/NG9/NFR list are finalized; if Option B, add the guest branch and a dedicated scenario ID.

---

### ~~Q-062-15~~ · Does `bucketable:false` mean "structurally ungroupable" or "zero results this time"? ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 062 – Root Album Listing Struct-of-Arrays  
**Resolved:** 2026-09-02

**Resolution:** `bucketable` keeps Feature 061's exact meaning — it describes whether the grouping *mechanism* is available, not whether there happens to be data. `shared` scope is unconditionally `bucketable:true` for both guests and authenticated callers (owner-based grouping is always a coherent mechanism for that scope); a zero-shared-albums result returns `bucketable:true` with empty `bucket_ids`/`counts`/`labels` arrays, exactly like 061's own empty-children behavior today. `bucketable:false` remains reserved for genuine structural incapability (there is none left in this scope, since `OWNER_ID` is no longer reachable as a bucket dimension at all per Q-062-08).

**Spec impact:** FR-062-05's "`bucketable:false` only for an empty result" parenthetical is removed; S-062-07 updated to expect `bucketable:true` + empty arrays instead of `bucketable:false`.

**Preferred option:** 🅰️ (**recommended**) Option A – Keep Feature 061's meaning; empty ≠ false

**Question**  
Feature 061's shipped `AlbumBucketController` sets `bucketable:false` in exactly one case — the `OWNER_ID`-configured-column short-circuit, a *structural* incapability to bucket. A normal query that legitimately returns zero children still yields `bucketable:true` with empty arrays. FR-062-05 (this spec) instead says `shared`-scope `bucketable` is "always `true`... `bucketable:false` only for an empty result" — the opposite mapping: `false` now signals "no data," not "can't group." A frontend built against 061's convention (skip rendering sticky headers / virtual-scroll setup when `bucketable:false`, because there's no groupable dimension) would misinterpret a merely-empty `shared` result the same way as a genuinely ungroupable one, or vice versa depending on which convention it was actually written against. Which meaning should `bucketable` carry for the new endpoints?

---

#### 🅰️ (**recommended**) Option A – Keep 061's meaning: `bucketable` describes the mechanism, not the data

- **Idea:** `shared` scope is always `bucketable:true` (owner-grouping is always a valid mechanism for this scope), even when the result set happens to be empty — `bucket_ids`/`counts`/`labels` are just empty arrays in that case, exactly like 061's own empty-children behavior today.
- **Spec impact:** FR-062-05's parenthetical ("`bucketable:false` only for an empty result") is deleted; add a scenario asserting `bucketable:true` + empty arrays for a zero-shared-albums caller.
- **Pros:**  
  - ✅ One consistent meaning for `bucketable` across every endpoint in this family (Feature 061 and 062 alike) — a frontend written once against the field never needs a per-endpoint branch.  
  - ✅ Matches existing shipped behavior exactly, zero surprise for anyone who already built against 061.
- **Cons:**  
  - ❌ None identified — this is the lower-risk, already-proven option.

---

#### 🅱️ Option B – Keep this spec's current wording; `bucketable:false` also covers "empty"

- **Idea:** Accept the dual meaning as written: `false` means either "can't group" or "nothing to group."
- **Spec impact:** None — spec already reflects this. Would need an explicit callout in Documentation Deliverables so frontend authors don't assume 061's convention.
- **Pros:**  
  - ✅ Arguably saves the frontend a round trip in the empty case (no point rendering an empty virtual-scroll container either way).
- **Cons:**  
  - ❌ Silently changes the meaning of a field name reused verbatim from a shipped feature, for only one of its two call sites — a subtle trap for anyone extending this family later.  
  - ❌ No test coverage currently distinguishes the two cases from each other.

---

**Next action**  
User to pick A or B; if A, remove the parenthetical from FR-062-05 and add the empty-but-bucketable scenario; if B, add an explicit cross-reference note in FR-062-05 warning that this diverges from 061.

---

### ~~Q-062-16~~ · Is `/Albums/root/rights`'s `owner_id` null for `scope=own`, or only for `scope=shared`? ✅ RESOLVED

**Status:** Resolved — Option A, refined  
**Feature:** 062 – Root Album Listing Struct-of-Arrays  
**Resolved:** 2026-09-02

**Resolution:** `owner_id` stays `null` unconditionally for root's rights response, for both `own` and `shared` scope (Option A, as recommended). Follow-up question during resolution — "if it is always null do we need it?" — is correct: a field that is *always* null on this endpoint carries zero information and is just noise. Refined resolution: the key is **omitted from the JSON payload entirely** for root's rights response (via a conditional resource field, e.g. Laravel's `whenNotNull()`), rather than serialized as a useless `"owner_id": null`. The sub-album tier and the `TagAlbum`/`PersonAlbum` matching-albums tier — where `owner_id` is always a real, meaningful value — are unaffected: the shared `AlbumChildrenRightsResource` class still emits the key there, unchanged, since the same conditional only omits it when the value is actually null.

**Spec impact:** FR-062-06 reworded to state the omission explicitly; DO-062-04 updated (`nullable, omitted from the payload when null` rather than just `nullable`).

**Preferred option:** 🅰️ (**recommended**) Option A – Null unconditionally, both scopes

**Question**  
FR-062-06 widens `AlbumChildrenRightsResource`'s top-level `owner_id` to nullable "because root has no single owner to report," stated without a scope carve-out. But under `scope=own`, every row genuinely does share one real owner — the caller — the exact fact FR-062-03 relies on to justify its own `own`-scope simplifications for buckets. Should `own`-scope root rights report the caller's real `owner_id`, or stay null like `shared` scope?

---

#### 🅰️ (**recommended**) Option A – Null unconditionally, both scopes

- **Idea:** `owner_id` is always `null` on this resource regardless of `scope`, matching FR-062-06 as currently written.
- **Spec impact:** None — spec already reflects this; just needs an explicit sentence confirming it's deliberate, not an oversight.
- **Pros:**  
  - ✅ One code path, no scope-conditional logic in the rights controller.  
  - ✅ The field is genuinely not useful here anyway — a caller requesting `own` scope already knows whose albums they are by definition.
- **Cons:**  
  - ❌ Slightly inconsistent with the rest of this feature, where `own` scope is treated as meaningfully simpler than `shared` almost everywhere else.

---

#### 🅱️ Option B – Populate with the caller's id for `own` scope

- **Idea:** `owner_id = (string) $user->id` when `scope=own`; stays `null` for `shared`.
- **Spec impact:** FR-062-06 gains a scope-conditional clause; DO-062-04 updated.
- **Pros:**  
  - ✅ More information for "free" — no extra query, `$user->id` is already known.
- **Cons:**  
  - ❌ Adds a branch to a resource whose main job (per-row grants) doesn't otherwise vary by scope, for a field of doubtful client value.

---

**Next action**  
User to confirm; low effort either way, but FR-062-06 should say which explicitly rather than leaving it inferable.

---

### ~~Q-062-17~~ · Does dropping `/children` make `/Albums/{album_id}` read as "fetch one album"? ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 062 – Root Album Listing Struct-of-Arrays  
**Resolved:** 2026-09-02

**Resolution:** Keep the rename exactly as specced — `/Albums/{album_id}`, `/Albums/{album_id}/buckets`, `/Albums/{album_id}/rights` (Q-062-12 stands, reconsideration confirmed no change). The naming tradeoff is accepted knowingly rather than reverted.

**Spec impact:** None — spec already reflects this; Appendix note added confirming the decision was revisited and stands.

**Preferred option:** 🅰️ (**recommended**) Option A – Keep the rename, accept the naming tradeoff

**Question**  
By ordinary REST convention, `GET /resource/{id}` fetches that one resource's own fields. This feature renames `/Albums/{album_id}/children` (self-documenting: "this album's children") to `/Albums/{album_id}` (Q-062-12), which actually returns a Struct-of-Arrays **collection** of that album's children, not the album's own metadata. The same pattern now applies to `/Albums/root`, `/Albums/persons`, `/Albums/pinned` — all singular/scope-shaped paths returning collections. This was an accepted tradeoff for the *new* root-family paths; renaming an *already-shipped* Feature 061 path to match raises the stakes slightly, since it's the first time this ambiguity touches a path that isn't brand new. Is the consistency win worth it, or should the rename be reconsidered now that it's not just new surface?

---

#### 🅰️ (**recommended**) Option A – Keep the rename, accept the naming tradeoff

- **Idea:** Proceed exactly as specified (Q-062-12) — `/Albums/{album_id}`, `/Albums/{album_id}/buckets`, `/Albums/{album_id}/rights`.
- **Spec impact:** None — spec already reflects this.
- **Pros:**  
  - ✅ Consistent scheme across every endpoint in this family: `/Albums/<scope-or-id>` = "list what's under this," `/buckets` and `/rights` as siblings.  
  - ✅ Confirmed zero v8 consumers reference the old path — genuinely free to change.  
  - ✅ Shorter, cleaner URLs, matching the user's own stated preference.
- **Cons:**  
  - ❌ A future API consumer's first instinct reading `GET /Albums/{id}` may be "this returns the album," not "this returns its children" — discoverable only via documentation, not the URL itself.

---

#### 🅱️ Option B – Keep `/children` on the sub-album tier only; only root-level (parent-less) paths drop it

- **Idea:** Revert Q-062-12 — sub-album tier stays `/Albums/{album_id}/children[/buckets|/rights]` (self-documenting, matches real REST expectations for a real resource id). Root/persons/pinned/smart/tags keep the shorter form, since they were never going to collide with a "fetch this resource" reading in the first place (there's no single "smart album" or "pinned album" resource by that name).
- **Spec impact:** Revert FR-062-01/FR-062-12's rename clause, NFR-062-06's URL-literal caveat, S-062-28, Q-062-12's resolution, and all "8 new + 3 renamed" route-count language back to "8 new, 3 re-pointed, paths unchanged."
- **Pros:**  
  - ✅ Removes all rename-related risk and test-file churn (T-062-02, T-062-00) entirely.  
  - ✅ `/children` after a real `{album_id}` is the one place in this whole route family where the "collection, not the resource" cue is actually load-bearing (elsewhere the segment itself, e.g. `smart`/`pinned`, already isn't a plausible single-resource name).
- **Cons:**  
  - ❌ Breaks the "`/Albums/<scope-or-id>` always means list" consistency the rest of the feature establishes.  
  - ❌ Reintroduces the asymmetry this revision was explicitly trying to remove.

---

**Next action**  
User to confirm Option A (as currently specced) or revert to Option B; low urgency since either is a small, mechanical spec edit at this stage, before implementation starts.
