# Open Questions – Feature 063

Open questions for [Feature 063](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-063-05~~ | 063 – Album Timeline Buckets Frontend Adoption | High | `contextMenu.ts` reads `selectedAlbum.is_pinned` for the Pin/Unpin menu label, but tier 2 doesn't supply `is_pinned` and the FR-063-06 adapter never sets it | Resolved (Option A — added `is_pinneds` to tier 2 via new Feature 061 FR-061-27; FR-063-06's adapter maps it straight through) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-06~~ | 063 – Album Timeline Buckets Frontend Adoption | High | `useAlbumFlags.ts` reads `album.is_public`/`album.is_link_required` for the Public/Hidden and Public/Visible tile badges, but tier 2 supplies neither | Resolved (Option A — added `is_publics`/`is_link_requireds` to tier 2 via the same FR-061-27; FR-063-06's adapter maps both straight through) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-07~~ | 063 – Album Timeline Buckets Frontend Adoption | Medium | `AlbumThumbOverlay.vue` reads `album.formatted_min_max ?? album.created_at`; tier 2 has raw `min_taken_at`/`max_taken_at` but not the server-formatted, ordering-aware string | Resolved (Option A — computed client-side; new `phpDateFormat.ts` (FR-063-16) reproduces PHP `date()` semantics from an adapted reference implementation, fed by two new `AlbumConfig`/`RootConfig` fields, `date_format_album_thumb`/`thumb_min_max_order`; new NFR-063-08 correctness bar) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-08~~ | 063 – Album Timeline Buckets Frontend Adoption | High | `AlbumListItem.vue` (list view) has the identical `AlbumThumbImage.vue`/pre-built-URL dependency that motivated `AlbumThumbVirtual.vue` for grid view, with no equivalent fix planned | Resolved (Option A — new `AlbumListItemVirtual.vue` (FR-063-17/DO-063-07), forked from `AlbumListItem.vue` the same way `AlbumThumbVirtual.vue` forks `AlbumThumb.vue`) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-09~~ | 063 – Album Timeline Buckets Frontend Adoption | Medium | FR-063-13's mutation re-fetch trigger list omitted "cover photo changed," despite tile rendering now depending on `cover_id` | Resolved (Option A — cover-photo change added to FR-063-13's trigger list, manual + automatic recompute; audit task added to confirm/add the call site) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-10~~ | 063 – Album Timeline Buckets Frontend Adoption | Medium | FR-063-07/FR-063-14's "measured tile width via a probe tile" mechanism was never concretely specified | Resolved (no probe tile — analytic computation instead: container width via a `getWidth.ts`-style measurement, current breakpoint via `useBreakpoints()`, tile width from a shared breakpoint→formula lookup table also used by the CSS classes, FR-063-14) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-11~~ | 063 – Album Timeline Buckets Frontend Adoption | Medium | No request-cancellation/staleness guard was specified for rapid album-to-album navigation | Resolved (Option A — monotonic request-generation counter on `AlbumsState.ts`, FR-063-18; a response is applied only if its generation still matches the store's current one) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-12~~ | 063 – Album Timeline Buckets Frontend Adoption | Low | No caching/staleness policy was specified for repeat navigation to an already-visited album | Resolved (Option B — short-TTL per-`album_id` cache mirroring v2's `AlbumService` convention, FR-063-19, invalidated by the same trigger set as the mutation re-fetch) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-13~~ | 063 – Album Timeline Buckets Frontend Adoption | Low | Spec-precision review pass found several textual issues, no design decision required | Resolved (all corrected directly — adapted tile object now named `AdaptedAlbumTile` explicitly; FR-063-07's stale "`AlbumThumb`'s own" wording corrected as part of the FR-063-14 rewrite; FR-063-02/FR-063-09's shared flat-fallback path now cross-referenced explicitly) | 2026-08-30 | 2026-08-30 |
| ~~Q-063-14~~ | 063 – Album Timeline Buckets Frontend Adoption | High | User asked to "expand Feature 063 to also support smart albums" — ambiguous between (a) the root gallery's smart-album tiles (Feature 062's `GET /Albums/smart`, shipped with zero frontend consumer, flagged as a follow-up feature) and (b) browsing into a smart album's own detail page (where Feature 061's tier 1/2/3 children endpoints explicitly leave smart albums unresolved, FR-061-24) | Resolved (A — root gallery's smart-album tiles. Investigation found option B is a non-issue in practice: `AlbumState.ts`'s `load()` never calls `loadAlbumsAuto()`/tier 1-2-3 at all for a smart album — it branches on backend-computed `config.is_model_album`/`is_base_album`, both `false` for `BaseSmartAlbum` (`AlbumConfig.php:57-58`), so no v2 or v3 children request is ever attempted for a smart album's own page, flag on or off; nothing to fix there. Option A is a real, unimplemented gap — `Albums.vue`'s Smart Albums section still reads `Top::get()`'s `smart_albums` field unconditionally, regardless of `is_struct_of_array_enabled`; FR-063-20..23) | 2026-09-02 | 2026-09-02 |
| ~~Q-063-15~~ | 063 – Album Timeline Buckets Frontend Adoption | High | `GET /Albums/smart` (Feature 062) deliberately never resolves a cover photo (`cover_ids` always `null`, by design, to stay a zero-SQL-query endpoint), whereas today's v2 root listing shows a real per-viewer cached cover for every smart-album tile (`ThumbAlbumResource`/`CachesAlbumUserThumb`) — accept the resulting regression (generic icon on every flag-on smart-album tile) or amend Feature 062 to add real covers? | Resolved (amend — via the existing, already-populated `AlbumUserThumb` per-viewer cache `BaseSmartAlbum`/`RecomputeAlbumUserThumbsJob` already read/write for the v2 path, batched into one indexed `whereIn('album_id', ...)` query, not a live photo query; FR-062-16. A second, related gap surfaced during scoping: `GetPhotoAssetRequest::isPhotoOfAlbum()` (Feature 056) already has a cache-backed cover exception for `TagAlbum`/`PersonAlbum` but not `BaseSmartAlbum` — without extending it too, a cached smart-album cover that has since fallen out of the live query condition would 403 through the Asset endpoint; extended symmetrically, FR-056-08) | 2026-09-02 | 2026-09-02 |
| ~~Q-063-16~~ | 063 – Album Timeline Buckets Frontend Adoption | High | After Q-063-14 shipped a smart-albums-only slice, user follow-up: *"The point of feature 63 extension is to support StructOfArray on the root gallery page."* — does this mean the whole root gallery page's SoA adoption (all five Feature 062 root/category endpoints — smart, tags, persons, pinned, root own/shared), or a narrower subset? | Resolved (everything, per explicit user choice among three sized options — "Everything (Recommended)" over "own-root grid only" or "flat swaps only". Supersedes Q-063-14's narrower "smart albums only" framing; NFR-063-09/Non-Goals amended accordingly. FR-063-24..30) | 2026-09-03 | 2026-09-03 |
| ~~Q-063-17~~ | 063 – Album Timeline Buckets Frontend Adoption | Medium | Own/shared root albums need the same virtualized-grid-with-sticky-headers treatment sub-album children already have (FR-063-07/10) — reuse those existing components made prop-driven/generic, or fork new root-scope versions? | Resolved (fork — `AlbumRootGridVirtual.vue`/`AlbumRootListViewVirtual.vue`/`AlbumRootPanelVirtual.vue`, mirroring this repo's already-established "fork a shared component rather than branch a mode into it" convention (Q-063-02's own reasoning, and the precedent `AlbumThumbVirtual.vue` itself set forking from `AlbumThumb.vue`). The two contexts read structurally different store fields (root's own/shared scope vs. the currently-browsed album's children) — a genuinely different data source, not merely a rendering-strategy difference a prop could parameterize away cleanly. Pure functions (`buildVirtualAlbumRows`, `computeBucketBoundaries`, `useAlbumTileWidth`, `adaptAlbumChildTile`) and leaf tile components (`AlbumThumbVirtual.vue`) were already store-decoupled and are reused unchanged; only the three store-coupled orchestration components are forked. FR-063-24) | 2026-09-03 | 2026-09-03 |

## Question Details

### ~~Q-063-05~~ · Pin/Unpin menu label depends on `is_pinned`, which tier 2 never supplies ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** `is_pinned` added to tier 2's response as new Feature 061 FR-061-27 (`is_pinneds: bool[]` on `AlbumChildrenDataResource`, a plain column, zero extra join). Feature 063's FR-063-06 adapter maps it straight through to the adapted tile object, closing the Pin/Unpin label bug at the source.

**Spec impact:** New Feature 061 FR-061-27, DO-061-08 field-list update, new S-061-44, new Feature 061 tasks T-061-48..50 (unchecked — not yet implemented). Feature 063's FR-063-06 updated; Non-Goals' "no changes to Feature 061's contract" amended to allow this one exception; new S-063-21.

**Preferred option:** 🅰️ (**recommended**) Option A – Add `is_pinned` to tier 2's response

**Question**  
`contextMenu.ts:374` picks the Pin/Unpin menu label from `selectedAlbum.is_pinned` (`selectedAlbum.is_pinned ? "unpin" : "pin"`). Feature 061's tier 2 endpoint (`GET .../children`, FR-061-12's field list) never includes `is_pinned`, and Feature 063's FR-063-06 adapter doesn't set it either — so on the adapted object it's always `undefined`, and the flag-on context menu would show "Pin" even for an already-pinned subalbum. `is_pinned` is a plain boolean column on `albums` (confirmed in `ThumbAlbumResource.php`: `$this->is_pinned = $data instanceof BaseAlbum ? $data->is_pinned : false;`). How should this be fixed?

---

#### 🅰️ (**recommended**) Option A – Add `is_pinned` to tier 2's response

- **Idea:** Extend `AlbumChildrenDataResource`/`AlbumChildrenDataController` (Feature 061) to also return `is_pinneds: bool[]`, a plain column read with zero extra joins — same cost class as `is_password_requireds`/`is_nsfws`, already present.
- **Spec impact:** A new FR on Feature 061's spec (mirrors the FR-061-26 precedent — 063 surfaced a real 061 gap, fixed at the source); Feature 063's Non-Goal "No changes to Feature 061's contract" gets the same kind of explicit exception FR-061-26 already required. FR-063-06 updated to map the new field straight through.
- **Pros:**
  - ✅ Fixes the bug at its root — the menu label becomes correct, not just less wrong.
  - ✅ Directly mirrors this feature's own established precedent (FR-061-26) for "found a real 061 gap while drafting 063, fixed at the source."
  - ✅ Trivial marginal cost — one more plain-column array on an endpoint that already returns a dozen.
- **Cons:**
  - ❌ Touches Feature 061 (already "Completed") a second time.
  - ❌ Requires reconciling with the current Non-Goal wording in both specs.

---

#### 🅱️ Option B – Accept as a known regression, documented like the video-icon/blur-placeholder trade-offs

- **Idea:** Leave `is_pinned` unset; the Pin/Unpin label always reads "Pin" on the flag-on path. Document it as a third accepted regression alongside FR-063-15's two existing ones.
- **Spec impact:** One new Non-Goals bullet; no FR/endpoint change.
- **Pros:**
  - ✅ Zero implementation cost, zero risk to Feature 061.
- **Cons:**
  - ❌ Unlike the video-icon/blur-placeholder trade-offs (which degrade gracefully), this one is actively misleading — clicking "Pin" on an already-pinned album is a wrong-labeled action, not just a missing decoration.

---

**Next action**  
Owner to choose 🅰️ or 🅱️; if 🅰️, apply the same amendment pattern FR-061-26 used (new Feature 061 FR + updated Non-Goals in both specs) before I2/I3 tasks begin.

---

### ~~Q-063-06~~ · Public/Hidden and Public/Visible badges depend on fields tier 2 never supplies ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** `is_public`/`is_link_required` added to tier 2's response as part of the same Feature 061 FR-061-27 as Q-063-05 (`is_publics`/`is_link_requireds: bool[]`, resolved from each child's own `public_permissions()` relation — a join distinct from `applyVisibilityFilter()`'s existing one). Feature 063's FR-063-06 adapter maps both straight through.

**Spec impact:** Bundled into the same FR-061-27/DO-061-08/S-061-44/T-061-48..50 amendment as Q-063-05. Feature 063's FR-063-06 updated; new S-063-22.

**Preferred option:** 🅰️ (**recommended**) Option A – Add `is_public`/`is_link_required` to tier 2's response

**Question**  
`useAlbumFlags.ts:23,27` computes `showPublicHiddenFlag`/`showPublicVisibleFlag` from `album.is_public`/`album.is_link_required`, both read directly off the album object passed to `AlbumThumb.vue` — reused unchanged by `AlbumThumbVirtual.vue` per FR-063-15/Goals. Tier 2's field list (FR-061-12) has neither field, and FR-063-06's adapter doesn't set them. Both badges would then be permanently invisible on the flag-on path, regardless of an album's actual public/link-required status. `is_password_required` (a sibling field resolved by the exact same `AlbumProtectionPolicy` computation) is already included in tier 2 — so this isn't a new class of cost, just two fields that got left out. How should this be fixed?

---

#### 🅰️ (**recommended**) Option A – Add `is_public`/`is_link_required` to tier 2's response

- **Idea:** Extend `AlbumChildrenDataResource` to also expose `is_publics: bool[]`/`is_link_requireds: bool[]`, resolved from the same `AlbumProtectionPolicy` call tier 2 already makes for `is_password_required` — no new policy computation, just two more fields read off an object already in hand.
- **Spec impact:** Same shape as Q-063-05 Option A — a Feature 061 FR addition, Non-Goal exception, FR-063-06 field-mapping update.
- **Pros:**
  - ✅ Fixes both badges correctly.
  - ✅ Free to compute — `AlbumProtectionPolicy::ofBaseAlbum()` is already called per row for `is_password_required`; these are two more properties off the same object.
- **Cons:**
  - ❌ Same Feature 061 re-touch cost as Q-063-05.

---

#### 🅱️ Option B – Accept as a known regression

- **Idea:** Leave both fields unset; neither badge ever renders on flag-on tiles.
- **Spec impact:** One new Non-Goals bullet.
- **Pros:**
  - ✅ Zero cost.
- **Cons:**
  - ❌ A real, visible information loss for any instance with `is_public_hidden_flag_enabled`/`is_public_visible_flag_enabled` turned on — those badges exist specifically to communicate an album's sharing state at a glance.

---

**Next action**  
Owner to choose 🅰️ or 🅱️; if bundled with Q-063-05's Option A, land as one combined FR-061 amendment rather than two separate ones.

---

### ~~Q-063-07~~ · Tile date subtitle silently shows the wrong date when tier 2 lacks `formatted_min_max` ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** Computed client-side, not added to tier 2. New `phpDateFormat.ts` (FR-063-16/DO-063-08) reproduces PHP `date()` format-character semantics, adapted from a reference community implementation, then replicates `ThumbAlbumResource::formatMinMaxDate()`'s join/ordering logic using tier 2's already-present raw `min_taken_at`/`max_taken_at`. Since `date_format_album_thumb`/`thumb_min_max_order` are free-text/enum configs not previously exposed to the frontend, both were added as new fields on the pre-existing `AlbumConfig`/`RootConfig` resources (not a Feature 061 touch — these resources predate and sit outside Feature 061's contract). New NFR-063-08 sets the correctness bar (byte-for-byte match against real PHP `date()` output); confirmed non-trivial in scope since the config is genuinely free-text (any valid PHP format string), not a fixed handful of tokens — flagged as an explicit plan.md Risk (unrecognized format characters must pass through literally, matching PHP's own behavior).

**Spec impact:** New FR-063-16, DO-063-08, NFR-063-08, S-063-23; new plan.md Increment I13; `AlbumConfig`/`RootConfig` field additions noted in the Interface Catalogue.

**Preferred option:** 🅰️ (**recommended**) Option A – Compute the formatted string client-side in the adapter

**Question**  
`AlbumThumbOverlay.vue:54,58` renders `props.album.formatted_min_max ?? props.album.created_at` as the tile's date subtitle (shown when `album_subtitle_type` is `'takedate'` or `'creation'`). `formatted_min_max` is a server-computed string (`ThumbAlbumResource::formatMinMaxDate()`) that combines `min_taken_at`/`max_taken_at` with the `thumb_min_max_order` config's ordering choice. Tier 2 supplies the raw `min_taken_at`/`max_taken_at` values but not this formatted string, and FR-063-06's adapter doesn't compute an equivalent — so every flag-on tile configured for `'takedate'` would silently fall back to showing `created_at` instead, with no error or visible sign anything is wrong. How should this be fixed?

---

#### 🅰️ (**recommended**) Option A – Compute the formatted string client-side in the adapter

- **Idea:** Replicate `formatMinMaxDate()`'s logic (string concatenation ordered by the `thumb_min_max_order` config) in the FR-063-06 adapter, using tier 2's already-present raw `min_taken_at`/`max_taken_at` plus the same config value already available client-side (mirrors FR-063-04's established precedent: "the client already knows its own identity ... combining ... is a small, well-defined client computation — deliberately not replicated server-side").
- **Spec impact:** No change to Feature 061's contract at all — this is a pure frontend addition to FR-063-06, since the only missing ingredient (the ordering config) is a config value, not new endpoint data.
- **Pros:**
  - ✅ Fixes the bug with zero backend change — doesn't touch Feature 061.
  - ✅ Directly mirrors an approach this spec has already used and justified once (FR-063-04).
- **Cons:**
  - ❌ Duplicates `formatMinMaxDate()`'s date-formatting/ordering logic in two languages (PHP for v2, TS for v3) — a small ongoing drift risk if the server-side format ever changes.

---

#### 🅱️ Option B – Extend tier 2 to supply the pre-formatted string

- **Idea:** Add `formatted_min_maxes: (string\|null)[]` to `AlbumChildrenDataResource`, computed server-side per row.
- **Spec impact:** Feature 061 FR addition, same Non-Goal exception pattern as Q-063-05/02.
- **Pros:**
  - ✅ Single source of truth for the format — no client/server duplication risk.
- **Cons:**
  - ❌ Runs against Feature 061's own stated design philosophy for this endpoint — Non-Goals already excludes label/date-string computation from tier 2 once for exactly this reason ("requires a join to `photos`/size-variant rows to resolve" — this case doesn't need a join, but it is the same class of per-row string formatting 061 avoided elsewhere on principle).
  - ❌ A third Feature 061 re-touch in the same drafting pass, on top of Q-063-05/02.

---

**Next action**  
Owner to choose 🅰️ or 🅱️ — 🅰️ avoids further Feature 061 changes and is a smaller, self-contained fix; land before I3.

---

### ~~Q-063-08~~ · List view has the identical cover-image problem as grid view, with no equivalent fix planned ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** New `AlbumListItemVirtual.vue` (FR-063-17/DO-063-07), forked from `AlbumListItem.vue` exactly the way `AlbumThumbVirtual.vue` forks `AlbumThumb.vue` — `ListBadge.vue`/layout logic reused unchanged, `AlbumThumbImage.vue` replaced with `<Thumb>` at the row's existing fixed thumbnail size. `AlbumListViewVirtual.vue` mounts it per row.

**Spec impact:** New FR-063-17, DO-063-07, S-063-24; plan.md Increment I6 expanded to include it.

**Preferred option:** 🅰️ (**recommended**) Option A – Fork `AlbumListItem.vue` the same way `AlbumThumbVirtual.vue` forks `AlbumThumb.vue`

**Question**  
`AlbumListItem.vue` (the flag-off list-row component) imports and uses `AlbumThumbImage.vue` directly for its own thumbnail — the exact same pre-built-URL dependency that motivated forking `AlbumThumbVirtual.vue` (FR-063-15) for grid tiles. But FR-063-10/DO-063-04 (`AlbumListViewVirtual.vue`) only describe reusing the grid's row-flattening composable at `itemsPerRow = 1` — neither mentions a list-row equivalent of `AlbumThumbVirtual.vue`. As currently specified, list view would either try to reuse `AlbumListItem.vue` unchanged (breaking identically to how `AlbumThumb.vue` would have) or reuse `AlbumThumbVirtual.vue` itself (wrong layout — a full square/aspect-ratio tile doesn't fit a list row). How should list view resolve its cover image?

---

#### 🅰️ (**recommended**) Option A – Fork `AlbumListItem.vue` into a new `AlbumListItemVirtual.vue`

- **Idea:** Same pattern as FR-063-15: a new component under `.../albumModule/Virtualized/`, reusing `AlbumListItem.vue`'s badge (`ListBadge.vue`) and layout logic unchanged, replacing its `AlbumThumbImage.vue` usage with `<Thumb :album-id :photo-id type="thumb">` at the row's fixed thumbnail size (`h-8 md:h-5`, matching FR-063-10's already-specified fixed row height).
- **Spec impact:** New FR (e.g. FR-063-16), new DO, new task(s) in I6; `AlbumListViewVirtual.vue` renders this new component per row instead of an unspecified placeholder.
- **Pros:**
  - ✅ Full parity with the grid path — list view actually shows correct, live cover images, matching FR-063-10's own stated goal ("cover both view modes, not just the default grid").
  - ✅ Directly reuses the exact same `<Thumb>`/`ThumbAssetService` mechanism, no new infrastructure.
- **Cons:**
  - ❌ Another new component + task, modest but real scope growth.

---

#### 🅱️ Option B – Scope list view down for v1 (icon-only rows, no cover pixels)

- **Idea:** Flag-on list rows render without a real cover thumbnail (a generic icon placeholder) until a follow-up feature adds the fork.
- **Spec impact:** FR-063-10 amended to explicitly drop cover-image rendering for list view; a Follow-up entry added.
- **Pros:**
  - ✅ Smaller scope for this feature.
- **Cons:**
  - ❌ Directly contradicts FR-063-10's own cited Source ("User instruction: cover both view modes, not just the default grid") — this would be reneging on an already-stated goal, not just deferring a nice-to-have.
  - ❌ A visibly worse list view than today's flag-off one (which does show real thumbnails).

---

**Next action**  
Owner to choose 🅰️ or 🅱️ before I6 begins — this affects I6's task list and DO-063-04's own scope either way.

---

### ~~Q-063-09~~ · `cover_id` changes aren't in FR-063-13's mutation re-fetch trigger list ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** "Cover photo change" (manual selection or automatic `auto_cover_id_*` recompute) added to FR-063-13's trigger list. A new task audits whether an existing v2 call site already fires on a cover change, wiring the re-fetch there or adding a new one if none exists — not assumed away.

**Spec impact:** FR-063-13 updated; Goals/plan.md Increment I9 updated; new task for the audit itself.

**Preferred option:** 🅰️ (**recommended**) Option A – Add "cover photo changed" to the trigger list

**Question**  
FR-063-13 re-fetches tier 1+2 after create/delete/move/rename/lock-unlock/visibility-change — mirroring `AlbumService.clearCache()`'s existing v2 call sites. None of those cover an explicit cover-photo change (user picks a new cover for a subalbum) or an automatic cover recompute (e.g. `RecomputeAlbumStatsJob` updating `auto_cover_id_max_privilege`/`auto_cover_id_least_privilege` after the current cover photo is deleted from within that subalbum). Since FR-063-15 now makes tile rendering depend on `cover_id`, a cover-only change with no accompanying metadata mutation would leave that tile showing a stale image indefinitely, until some unrelated re-fetch happens to occur. Should this be added as a trigger?

---

#### 🅰️ (**recommended**) Option A – Add cover-change events to FR-063-13's trigger list

- **Idea:** Wire the store's re-fetch action alongside whatever existing call site(s) already fire on a manual cover change (if any exist today for v2) and on `AlbumComputedDataUpdated`-style recompute events, mirroring Feature 053's own precedent for reacting to `RecomputeAlbumStatsJob` completion (Q-053-04).
- **Spec impact:** FR-063-13's trigger list gains "cover photo changed (manual or automatic recompute)."
- **Pros:**
  - ✅ Closes a real staleness gap in a feature whose entire value proposition is "correct cover pixels."
  - ✅ Small, additive change — one more call site on an already-established re-fetch action.
- **Cons:**
  - ❌ Requires confirming whether a per-cover-change event/call site already exists to hook, or a new one needs adding (audit needed, similar to Feature 058's FR-058-12 invalidation-net audit this FR already cites as its own precedent).

---

#### 🅱️ Option B – Accept as a known staleness gap

- **Idea:** Leave cover changes untriggered; the tile keeps its old cover pixels until an unrelated re-fetch/navigation happens to refresh it.
- **Spec impact:** A note added to FR-063-13's Failure path column.
- **Pros:**
  - ✅ No additional audit/wiring work.
- **Cons:**
  - ❌ Directly undercuts this feature's core promise — a subalbum grid whose thumbnails can silently go stale after the exact kind of edit (changing a cover) users make often.

---

**Next action**  
Owner to choose 🅰️ or 🅱️; if 🅰️, audit existing cover-change call sites (manual set + recompute-job completion) before wiring I9's re-fetch action.

---

### ~~Q-063-10~~ · The "probe tile" tile-width-measurement mechanism is never concretely specified ✅ RESOLVED

**Status:** Resolved — neither original option; a third approach found during resolution  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** Owner asked "how are we doing it right now?" rather than picking 🅰️/🅱️ below — investigation found this codebase already has two relevant, reusable pieces neither original option accounted for: `resources/js/layouts/getWidth.ts` (existing container-width measurement — `window.innerWidth` minus real computed padding/scrollbar, used by the photo-layout composables) and `useBreakpoints`/`breakpointsTailwind` (`@vueuse/core`, already used in `AlbumNavPanel.vue`/`useAdminTiles.ts` for reactive Tailwind-breakpoint detection). Resolved by combining both: measure the scroll container's width the `getWidth.ts` way (no tile involved), read the current breakpoint reactively via `useBreakpoints()` (extended with this project's actual `3xl`/`4xl` thresholds — not fully located during this pass, flagged as a plan.md Risk to source during implementation), then compute tile width from the *same* `calc()` formula already encoded in the width classes, via one shared lookup table both the CSS and this JS computation read from. No probe tile, no DOM measurement of any tile element at all — a stronger outcome than either original option (🅰️'s probe tile, 🅱️'s circular first-real-row measurement).

**Spec impact:** FR-063-14 rewritten in full; new DO-063-09 (`albumTileWidth.ts`); plan.md's original width-duplication Risk marked resolved with the new reasoning; new `3xl`/`4xl`-sourcing Risk added.

**Original options below, superseded:**

**Preferred option (superseded):** 🅰️ (**recommended**) Option A – A dedicated, permanently-mounted hidden probe element

**Question**  
FR-063-07/FR-063-14 both depend on a "measured tile width," and plan.md's Risks section says this comes from "e.g. via a one-time `getBoundingClientRect()` on a mounted probe tile" — but no FR, DO, or task actually pins down what this probe tile *is*: a real `AlbumThumbVirtual.vue` instance rendered purely for measurement, a bare `<div>` carrying the same width/`aspect-*` classes, or something else — and whether it lives permanently off-screen or is borrowed from whichever row happens to mount first. `itemsPerRow`/row-height math (FR-063-14) can't be implemented without this being pinned down. How should tile width actually be measured?

---

#### 🅰️ (**recommended**) Option A – A dedicated, permanently-mounted hidden probe element

- **Idea:** A single element (`visibility: hidden` or `position: absolute` off-screen — not `display: none`, which drops layout — so it still has real dimensions) carrying the exact same responsive width classes `AlbumThumbVirtual.vue` uses, mounted once per grid instance, remeasured on every `ResizeObserver` callback.
- **Spec impact:** A concrete implementation note added to FR-063-07/DO-063-02/03, e.g. as a new small DO for the probe element itself.
- **Pros:**
  - ✅ Always available to measure, independent of whether any real row has mounted yet — no chicken-and-egg problem on first paint.
  - ✅ Decoupled from real data — measuring doesn't depend on a specific child existing.
- **Cons:**
  - ❌ One extra always-present DOM node per grid instance (negligible at this feature's scale, but worth naming explicitly).

---

#### 🅱️ Option B – Measure the first real mounted tile row directly

- **Idea:** No separate probe — read `getBoundingClientRect()` off whatever `AlbumThumbVirtual.vue` instance the virtualizer happens to mount first.
- **Spec impact:** Simpler DOM (no extra hidden node), but `itemsPerRow` is unknown until *something* has already mounted — and `itemsPerRow` is exactly what's needed to decide how many tiles to mount in the first place.
- **Pros:**
  - ✅ No extra always-present DOM node.
- **Cons:**
  - ❌ Circular dependency on first paint: the virtualizer needs `itemsPerRow` to know what to mount, but this option needs something mounted to learn `itemsPerRow`.

---

**Next action**  
Owner to choose 🅰️ or 🅱️ (🅱️'s circularity likely rules it out) before I4/I5 tasks begin — this is a concrete blocker for T-063-10 (implementing `virtualAlbumRows.ts`'s width input).

---

### ~~Q-063-11~~ · No stale-response guard specified for rapid album-to-album navigation ✅ RESOLVED

**Status:** Resolved — Option A  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** A monotonic request-generation counter on `AlbumsState.ts` (the token variant of Option A, not `AbortController`) — incremented per `loadAlbumsV3()` call, captured per in-flight fetch, a response applied only if its generation still matches the store's current one.

**Spec impact:** New FR-063-18; Goals/plan.md Increment I1 updated; new S-063-26.

**Preferred option:** 🅰️ (**recommended**) Option A – Guard against stale responses with a generation token/`AbortController`

**Question**  
FR-063-01 triggers a new tier 1+2 fetch on every album navigation; FR-063-03 fires tier 3 immediately after. Neither FR states what happens if the user navigates from album A to album B before album A's fetch(es) resolve — could A's late-arriving response overwrite the store after B's own (later-started, possibly-faster) fetch has already rendered, producing a visible flash of the wrong album's children? This is distinct from the same-session-mutation race FR-063-02's Failure path already covers (`sum(counts) !== children.length`) — that race is about internal consistency of one album's own two responses; this one is about which album's data wins when two different albums' fetches overlap.

---

#### 🅰️ (**recommended**) Option A – Guard with a generation token or `AbortController`

- **Idea:** `AlbumsState.ts` tracks the currently-requested `album_id` (or a monotonic counter) at fetch-start; when a response arrives, it's applied only if it still matches the current target — otherwise discarded. `AbortController` is the more thorough version (also cancels the in-flight HTTP request, not just its effect).
- **Spec impact:** New clause on FR-063-01 (and FR-063-03 by extension) describing the guard.
- **Pros:**
  - ✅ Common, well-understood SPA pattern — closes a real, easy-to-hit race (fast back-and-forth browsing is normal usage, not an edge case).
  - ✅ Cheap to implement — a few lines in the fetch action.
- **Cons:**
  - ❌ One more piece of state to keep correct (the "current generation" tracking itself must not have its own bugs).

---

#### 🅱️ Option B – Accept as a rare, self-correcting staleness window

- **Idea:** No guard; if it happens, the user briefly sees album A's stale children under album B's header, self-corrected whenever the (usually near-simultaneous) later response also lands.
- **Spec impact:** None — status quo.
- **Pros:**
  - ✅ Zero implementation cost.
  - ✅ Mirrors this spec's own already-accepted staleness-window precedent (plan.md Risks, cross-session mutation race).
- **Cons:**
  - ❌ Unlike that precedent (rare, cross-session), this race is triggered by ordinary fast browsing — plausibly common, not rare, and directly visible (wrong album's content, not just a stale count).

---

**Next action**  
Owner to choose 🅰️ or 🅱️ before I1 — if 🅰️, add to T-063-01's implementation notes.

---

### ~~Q-063-12~~ · No caching/staleness policy specified for repeat navigation to an already-visited album ✅ RESOLVED

**Status:** Resolved — Option B  
**Feature:** 063 – Album Timeline Buckets Frontend Adoption  
**Resolved:** 2026-08-30

**Resolution:** Owner chose Option B over the recommended Option A — a short-TTL, per-`album_id` in-memory cache on `AlbumsState.ts`, mirroring v2's `AlbumService` convention, invalidated by the exact same trigger set FR-063-13's mutation re-fetch already uses (now including Q-063-09's cover-change trigger).

**Spec impact:** New FR-063-19; Goals/plan.md Increment I1 updated; new S-063-25; DO-063-01 gains the cache as new store state.

**Preferred option (not chosen):** 🅰️ Option A – No caching; always re-fetch fresh

**Question**  
FR-063-01 says navigation "triggers a new `AlbumsState.ts` action that fetches tier 1... and tier 2," but doesn't say whether this always re-fetches from the network or checks for a recent cached response first — unlike v2's `AlbumService`, which has an explicit `clearCache()` convention this spec's own FR-063-13 mirrors for invalidation, implying v2 *does* cache. Should v3 navigation behave the same way?

---

#### 🅰️ (**recommended**) Option A – No caching; always re-fetch fresh on every navigation

- **Idea:** `loadAlbumsV3()` always issues fresh tier 1+2 requests, with no short-lived cache layer.
- **Spec impact:** None needed beyond stating this explicitly in FR-063-01.
- **Pros:**
  - ✅ Simplest possible correctness story — never a second staleness surface to reason about alongside FR-063-13's invalidation net.
  - ✅ Avoids doubling FR-063-13's already-established mutation-invalidation list for a second cache layer.
- **Cons:**
  - ❌ Slightly more network traffic on rapid back-and-forth browsing between the same few albums than a cached approach would need.

---

#### 🅱️ Option B – Short-lived cache keyed by `album_id`, mirroring v2's `AlbumService`

- **Idea:** Cache tier 1+2 responses per `album_id` for some TTL/until invalidated, reusing FR-063-13's mutation list as the same cache's invalidation triggers.
- **Spec impact:** New cache mechanism + explicit statement that FR-063-13's trigger list now serves double duty (re-fetch action *and* cache invalidation).
- **Pros:**
  - ✅ Faster perceived navigation when revisiting a recently-browsed album.
- **Cons:**
  - ❌ A second cache surface to keep in sync with FR-063-13's invalidation net — any future addition to that list (e.g. Q-063-09's cover-change trigger) must now also be remembered for this cache, doubling the blast radius of a missed trigger.
