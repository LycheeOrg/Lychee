# Open Questions – Feature 075

Open questions for [Feature 075](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-075-01~~ | 075 | High | Where the two extra cover photo ids per album are stored (extra `albums` columns vs child table vs JSON) | Resolved (Option A, spec FR-075-01/02, ADR-075-01) | 2026-09-30 | 2026-09-30 |
| ~~Q-075-02~~ | 075 | High | Which album kinds get distinct side covers: regular albums only, or also tag/person/smart albums (`album_user_thumbs`) | Resolved (Option B, spec FR-075-07..11, ADR-075-01) | 2026-09-30 | 2026-09-30 |
| ~~Q-075-03~~ | 075 | Medium | What the on/off setting gates (exposure only vs computation too) and its default | Resolved (Option B, spec FR-075-04/05) | 2026-09-30 | 2026-09-30 |
| ~~Q-075-04~~ | 075 | Medium | How the two side photos are chosen (ranks 2–3 of the cover query vs one per sub-album vs random) | Resolved (Option A, spec FR-075-02/05) | 2026-09-30 | 2026-09-30 |
| ~~Q-075-05~~ | 075 | Medium | Frontend scope: v8 struct-of-arrays tiles only, or also the v2-fed tiles (v8 flag-off path and v7) | Resolved (Option A, spec NG1, FR-075-06/12, NFR-075-05) | 2026-09-30 | 2026-09-30 |

## Question Details

### ~~Q-075-01~~ – Storage of the two extra cover ids per album

**Resolution:** Option A. See spec FR-075-01, FR-075-02 and [ADR-075-01](../../../6-decisions/ADR-075-01-side-covers-as-precomputed-columns.md).

**Context.** [GitHub discussion #4742](https://github.com/LycheeOrg/Lychee/discussions/4742) asks to bring back the v4 hover effect where two *different* photos fan out behind an album's cover. The fan-out itself still exists in `AlbumThumbVirtual.vue` / `AlbumThumb.vue` (v8) and `AlbumThumb.vue` (v7): three stacked images with `group-hover:-rotate-2` / `group-hover:rotate-6`, but all three receive the same `cover_id`. The only missing piece is two extra photo ids per album.

Today a regular album's cover is precomputed (ADR-0003) into `albums.auto_cover_id_max_privilege` / `albums.auto_cover_id_least_privilege` (nullable `char(24)`, FK `photos.id` ON DELETE SET NULL) by `RecomputeAlbumStatsJob::getPhotoIdForUser()`, a single ordered query (`is_highlighted DESC`, then the album's effective sort) that takes the first row. The v3 struct-of-arrays listings (`BuildAlbumDataResource`, `AlbumRootController`, `AlbumPinnedController`) read those columns straight from the row with no relation load and resolve one `cover_ids[i]` via `AlbumListController::resolveCoverId()`. `GetPhotoAssetRequest::isPhotoOfAlbum()` accepts a photo that matches one of those columns as legitimately representing the album (a cover may live in a descendant).

The ildyria comment in the discussion sums it up: "You would probably need to have two extra pointers to thumbnail images" — "That is pretty much the summary yes."

**Option A (recommended): four more precomputed columns on `albums`.**
`auto_cover_id_max_privilege_2`, `auto_cover_id_max_privilege_3`, `auto_cover_id_least_privilege_2`, `auto_cover_id_least_privilege_3` (nullable `char(24)`, FK `photos.id` ON DELETE SET NULL, mirroring the existing pair). `getPhotoIdForUser()` takes `limit(3)` instead of `first()` on the *same* query, so the job runs no extra query. Listings keep a single `toBase()` row read. `FulfillPreCompute` backfills rows whose new columns are all NULL. `isPhotoOfAlbum()` adds the four columns to its `in_array()`.
- Pros: zero additional read-path cost, the property ADR-0003 and ADR-0010 protect. One query change on the write path. FK integrity on photo deletion, as today. Consistent with the existing dual-privilege model, so the least-privilege side covers can never leak a private photo.
- Cons: four more columns and four more FKs on `albums`. Fixed at two side covers (the UI has exactly two side layers, so this is not a practical limit).

**Option B: a normalized `album_covers` child table** `(album_id, privilege, rank, photo_id)`.
- Pros: any number of covers, no schema change for a future "N covers" variant.
- Cons: every struct-of-arrays listing needs a join or a second query and a client-side regroup, which is exactly the per-album cost Features 057/061/062 removed. More code in job, listings, backfill and asset authorization.

**Option C: one JSON column** holding the ordered id list per privilege.
- Pros: one column per privilege.
- Cons: no FK, so a deleted photo leaves a dangling id (thumb 404s until the next recompute, or a stale asset-authorization match). Not indexable or checkable in SQL. Diverges from the existing columns.

### ~~Q-075-02~~ – Which album kinds get distinct side covers

**Resolution:** Option B. See spec FR-075-07..11 and ADR-075-01. The cache grows by two fixed columns (`photo_id_2`, `photo_id_3`) rather than a `rank` row per cover, for the same row-shaped reasons as Q-075-01; the tag and person listings now read the cache in one batched query for their primary cover as well, which the smart listing already did.

**Context.** Regular `Album` rows have precomputed cover columns. `TagAlbum`, `PersonAlbum` and smart albums instead cache one cover per `(album_id, user_id)` in `album_user_thumbs` (`CachesAlbumUserThumb::getCachedOrLiveThumb()`), materialised lazily on first view and purged on revocation (ADR-0010). Extending those to three photos means a `rank` column on `album_user_thumbs`, a three-row lazy compute, a three-row purge path, and a wider `isComputedAlbumThumb()` exception.

**Option A (recommended): regular albums only.** Tag, person and smart album tiles keep today's single-photo stack. Recorded as a non-goal; a follow-up feature can extend the cache.
- Pros: keeps this feature to the precomputed-column path. No change to the per-viewer cache or its security invariant (ADR-0010).
- Cons: mixed look on the root page when smart/tag/person tiles sit next to regular albums.

**Option B: regular albums plus tag/person/smart albums** via a `rank` column on `album_user_thumbs`.
- Pros: uniform look everywhere.
- Cons: touches the ADR-0010 invariant (purge and exception paths must handle three rows), roughly doubles the backend surface of the feature.

### ~~Q-075-03~~ – What the setting gates, and its default

**Resolution:** Option B. See spec FR-075-04 and FR-075-05 (`album_hover_side_covers_enabled`, default on, gates exposure only).

**Context.** The discussion asks for "a settings option to turn this feature on or off". With Option A of Q-075-01 the write-path cost of computing three ids instead of one is negligible (same query, `limit(3)`). The real runtime cost is on the read side: each visible album tile fetches three thumbnails from `GET /api/v3/Asset/...` instead of one (`ThumbAssetService` per photo id), so the setting is really about network/asset load and taste.

**Option A (recommended): always compute, the setting gates exposure and rendering.** New global boolean config `album_hover_side_covers_enabled` (Gallery category, level 0), **default off**. When off, the v3 listings send `null` side ids (or omit the arrays) and tiles render as today. When on, the ids are sent and the two side layers show them. Turning it on takes effect immediately, no backfill needed, because the columns are already filled by the regular recompute job (and by `FulfillPreCompute` for existing rows after upgrade).
- Pros: the toggle is instant in both directions. The job stays a single code path. Default off keeps the upgrade behaviour unchanged for large instances, which is the owner's stated priority.
- Cons: four columns are maintained even on instances that never enable the effect (cost: two extra ids from an already-running query).

**Option B: same as A, but default on.**
- Pros: the effect is discovered without touching settings, which is what the requester wants.
- Cons: every upgraded instance triples its thumbnail requests per album tile until an admin turns it off.

**Option C: the setting also gates computation.** When off, the job stores NULL side ids; turning it on requires a backfill (`FulfillPreCompute` / `lychee:recompute-album-stats`).
- Pros: zero storage on instances that never use it.
- Cons: two job code paths, a backfill step on enable, and stale sides if the flag is flipped off and on.

### ~~Q-075-04~~ – How the two side photos are chosen

**Resolution:** Option A. See spec FR-075-02 and FR-075-05.

**Context.** `getPhotoIdForUser()` already orders the album's subtree by `is_highlighted DESC`, then the album's effective photo sorting. The cover is row 1.

**Option A (recommended): rows 2 and 3 of the same query.** Deterministic, respects the owner's chosen ordering and highlights, and costs nothing beyond `limit(3)`. When the album has a manual `cover_id`, the resolver skips an auto id equal to `cover_id` so the fan never shows the same photo twice (the job still stores ranks 1–3 of the auto order unchanged).
- Pros: no extra queries. Stable between recomputes. Matches how the cover itself is chosen.
- Cons: in a subtree with many photos from one shoot, the three photos may look alike.

**Option B: one photo from each of the first two sub-albums** (fall back to Option A when there are fewer than two sub-albums with photos).
- Pros: more visual variety for parent albums.
- Cons: at least two extra queries per recompute, and two different selection rules to explain and test.

**Option C: random rows** from the filtered set at each recompute.
- Pros: variety.
- Cons: `ORDER BY RAND()` over a large subtree is a full sort, the exact cost class the owner has been eliminating. Non-deterministic tests and a cover that changes for no visible reason.

### ~~Q-075-05~~ – Frontend scope

**Resolution:** Option A. See spec NG1, FR-075-06, FR-075-12 and NFR-075-05.

**Context.** Three tile implementations render the fan: v8 struct-of-arrays tiles (`AlbumThumbVirtual.vue`, fed by the v3 `AlbumDataResource.cover_ids`, used by the album and root grids), the v8 flag-off tile (`AlbumThumb.vue`, fed by the v2 `ThumbAlbumResource.thumb`), and the v7 tile (same v2 resource). Extending the v2 resource means adding relations or URL fields to `ThumbAlbumResource`, which is built from hydrated `Album` models whose `$with` already eager-loads three cover relations; two to four more eager loads would apply to every `Album` hydration in the v2 API.

**Option A (recommended): v8 struct-of-arrays tiles only.** `AlbumDataResource` (album children and root listings) and the pinned listing gain `side_cover_ids_1` / `side_cover_ids_2` arrays (names to be fixed in the spec); `AlbumThumbVirtual.vue` feeds them to the two back layers. v7 and the v8 flag-off path are non-goals.
- Pros: one data path, no change to v2 resources or `Album::$with`. Matches the v8-only default for refactors in this repository.
- Cons: v7 users and v8 instances with struct-of-arrays off see no change.

**Option B: v8 both paths and v7** by adding `side_thumbs: ThumbResource[]` to `ThumbAlbumResource`.
- Pros: everyone gets the effect.
- Cons: extra eager loads on every v2 `Album` hydration, three tile components to change, and the v2 `ThumbResource` needs URLs (not ids), so the size-variant relations must be loaded too.

**Option C: v8 both paths, not v7.**
- Pros: consistent within v8.
- Cons: same v2 cost as B for a path that is being retired.
