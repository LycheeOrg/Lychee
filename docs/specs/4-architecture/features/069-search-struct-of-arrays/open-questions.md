# Open Questions – Feature 069

Open questions for [Feature 069](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-069-14~~ | 069 – Search Struct-of-Arrays | — | ~~`ConfigManager` is never bound in the container~~ — **the premise was false.** It is bound as `scoped()` by the global `ResolveConfigs` middleware. The 88 `configs` queries were a test-harness artifact of calling the action directly, bypassing middleware. | **Withdrawn (invalid), 2026-09-22.** Through a real HTTP request the same call issues 13 queries total, with **one** full `configs` load. Binding it as a singleton would have been actively harmful under FrankenPHP worker mode. | 2026-09-22 | 2026-09-22 |
| ~~Q-069-13~~ | 069 – Search Struct-of-Arrays | High | Q-069-12 established that v2 reads `configs.grants_full_photo_access` — a **seed value for newly created shares** — as a runtime authorization gate. | Resolved (owner, 2026-09-22: "Fix the over-grants.") — **Option B, widened**: implemented as **Feature 070** across all affected surfaces. The count grew from 5 to 7 during implementation: `PersonPhotosController` passed `should_downgrade: false` unconditionally, and `FlowItemResource` used an album-wide gate. | 2026-09-22 | 2026-09-22 |
| ~~Q-069-12~~ | 069 – Search Struct-of-Arrays | High | v2's search computes `should_downgrade` **once per request** from the raw `grants_full_photo_access` config, ignoring ownership and per-album share grants; the v3 tier evaluates `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` **per photo**. | Resolved (owner, 2026-09-22): **v2 is the defect — it grants full-resolution access more widely than it should.** v3's per-photo check is authoritative. Re-classified Medium → High: this is a rights over-grant, not a cosmetic divergence. See Q-069-13 for whether v2 itself gets fixed. | 2026-09-22 | 2026-09-22 |
| ~~Q-069-11~~ | 069 – Search Struct-of-Arrays | Medium | FR-069-04 promises every photo row carries a viewer-accessible `album_id`, but a photo the viewer *owns* and which sits in **no album at all** is searchable in v2 — it has no containing album to name. | Resolved directly (report the `unsorted` smart album id; dropping the photo would be a silent membership change v2 never made) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-10~~ | 069 – Search Struct-of-Arrays | High | Q-069-02 chose whole-scope unpaginated, which removes the 300/page ceiling that has always bounded search. What bounds the response now, and what becomes of the existing `search_pagination_limit` admin config it made dead? | Resolved (Option A — repurpose as a hard result cap, renamed `search_result_limit`, with an `is_truncated` flag; owner, 2026-09-22) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-01~~ | 069 – Search Struct-of-Arrays | High | Route shape for v3 Search: a dedicated `/api/v3/Search/*` family (Map/Feature 067 precedent) vs. modelling search as a parameterised `SearchAlbum` pseudo-album reached through the existing `/Albums/{album_id}/Photos*` tiers (Timeline/Feature 066 precedent, "zero new photo routes"). | Open | 2026-09-22 | 2026-09-22 |
| ~~Q-069-02~~ | 069 – Search Struct-of-Arrays | High | Photo-result delivery model: bucket-tiered + `bucket_ids[]`-windowed (Timeline), whole-scope unpaginated in one request (Flow/Albums), or keep today's server-side page-jump pagination (`search_pagination_limit`, 300/page). A broad term can match the entire library, so this decides the cost ceiling. | Resolved (Option B — whole-scope unpaginated, two photo tiers only, no bucket tier; owner, 2026-09-22) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-03~~ | 069 – Search Struct-of-Arrays | High | Album half of the search result: migrate to a flat SoA listing (+ separate `/rights` tier, `/Albums/root` precedent), give it a full bucket tier too, or leave album hits on the v2 `ThumbAlbumResource` shape and migrate only photos. | Resolved (Option A — flat SoA listing + separate `/rights` tier; owner, 2026-09-22) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-04~~ | 069 – Search Struct-of-Arrays | Medium | `SpotlightSearch.vue` (300 ms-debounced global quick-search) is a second consumer of `GET /api/v2/Search` and reads `photo.size_variants.thumb.url`, which is always empty on an SoA tile. Migrate it to v3 + `<Thumb>` in this feature, or deliberately leave it on v2? | Resolved (Option A — left on v2, documented; default taken, owner did not object) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-05~~ | 069 – Search Struct-of-Arrays | Medium | `date:` means `photos.taken_at` for photo hits but `base_albums.created_at` for album hits (`AlbumDateStrategy`). Carry that inconsistency into v3 unchanged, or unify album date matching onto `min_taken_at`/`max_taken_at`? | Resolved (Option A — carry today's semantics into v3 unchanged; owner, 2026-09-22) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-06~~ | 069 – Search Struct-of-Arrays | Medium | Keep the client's existing base64 encoding of the `terms` query param in v3, or switch to a plain URL-encoded string now that v3 is greenfield? | Resolved directly (keep base64 — the token grammar embeds `:`/`>=`/`"`/`#`, the client helper already exists, and no proxy/WAF can mangle it) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-07~~ | 069 – Search Struct-of-Arrays | Medium | Search results span albums, but `<Thumb>`/the v3 Asset endpoint need one concrete `album_id` per photo — what resolves it, and how are multi-album photos tie-broken? | Resolved directly (follow Feature 067's Q-067-11 precedent verbatim: join + collapse to the lowest accessible `album_id`, no `Album` hydration, no per-photo access re-check) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-08~~ | 069 – Search Struct-of-Arrays | Medium | v2's photo search joins `photo_album`+`albums` with no `distinct()`, so a photo in N albums yields N rows and inflates `total`. Reproduce for parity, or fix in v3? | Resolved directly (fix — dedupe via the `whereIn` id-subquery pattern `ResolvesPhotoSource` already documents; `total` becomes a true distinct-photo count) | 2026-09-22 | 2026-09-22 |
| ~~Q-069-09~~ | 069 – Search Struct-of-Arrays | Medium | One full-stack feature, or a backend feature plus a separate frontend-adoption feature (the 061→063 / 064→065 split)? | Resolved directly (single full-stack Feature 069 — matches the three most recent SoA features 066/067/068, all full-stack) | 2026-09-22 | 2026-09-22 |

## Question Details

### ~~Q-069-12~~ · v2 over-grants full-resolution access; v3's per-photo check is correct ✅ RESOLVED

**Status:** Resolved by the owner, 2026-09-22 — "This is actually an issue in v2 api. It gives more rights than it should." Encoded in spec.md's NFR-069-06 and in `SearchV3ParityTest::withoutOriginalUrl()`'s docblock.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Priority:** High (re-classified from Medium — a rights over-grant, not a serialization difference)

**What `should_downgrade` controls**  
Exactly one field. `SizeVariantsResouce` computes `$downgrade = $should_downgrade && !$photo->isVideo() && $size_variants?->hasMedium() === true` and applies it **only** to the `original` variant's `url`; `medium`/`small`/`thumb`/`placeholder` are never affected. It is the "may this viewer obtain the full-resolution file" gate. It never blanks the lightbox: the downgrade requires a `medium` to exist, and `PhotoState.ts`'s `imageViewMode` returns `Medium` in exactly that case, so the displayed image never reads `original.url` when it is null.

**Why the config is the wrong input entirely**  
`grants_full_photo_access` names **two different things**, and v2 confuses them:

| | what it is | default |
|---|---|---|
| `configs.grants_full_photo_access` | category `access_permissions`, description *"Allows access to full resolution **by default**"*. Its only legitimate consumer is `AccessPermission::ofPublic()`/`ofPublicHidden()`, which uses it to **seed the column below on a newly created public share**. | `1` |
| `access_permissions.grants_full_photo_access` | the actual per-share grant that `AlbumPolicy::canAccessFullPhoto()` reads via `public_permissions()`/`current_user_permissions()`. | `0` |

So the config governs *what future shares are created with*; the column governs *what this viewer may actually do now*. Reading the former as a runtime authorization gate is a category error, not merely a coarser check - and because the two defaults point opposite ways (`1` vs `0`), the failure mode is systematic: an album shared with full-photo access explicitly **off** still yields full-resolution URLs.

**The finding**  
`SearchController::search()` sets one value for the entire response:
```php
$should_downgrade = $config_manager->getValueAsBool('grants_full_photo_access') === false;
if (!$album instanceof Album) {
    $should_downgrade = Gate::check(AlbumPolicy::CAN_ACCESS_FULL_PHOTO, [AbstractAlbum::class, null]) === false;
}
```
`AlbumPolicy::canAccessFullPhoto(user, null)` returns that same global config, so **both branches collapse to the one config flag**. v2 search consults neither photo ownership nor any album's `grants_full_photo_access` permission row. With the config at its default (`1`), every viewer who can see a photo at all is handed its full-resolution URL — including photos reached through an album whose share grant deliberately withholds full access.

`PhotoPolicy::canAccessFullPhoto()` (what v3 uses) is the real rule: owner → allowed; cannot see → denied; otherwise OR across the photo's albums of that album's own `public_permissions()`/`current_user_permissions()` `grants_full_photo_access` grant.

**Resolution**  
v3 is correct and stays as-is; v2's behaviour is a defect, not a baseline to preserve. NFR-069-06's parity contract therefore excludes this field deliberately — matching v2 here would mean reproducing an over-grant.

**Scope beyond Search - five surfaces, not one.** The same seed-config-as-gate mistake appears at `AlbumPhotosController:58` (root/smart scope), `Actions\Albums\PositionData:70` (root map), `EmbedController:100`, and `Http\Resources\Timeline\TimelineResource:80`. `AlbumPolicy::canAccessFullPhoto()` itself also falls back to the config in its `$abstract_album === null || instanceof BaseSmartAlbum` branch (line 371), which is what makes the `Gate::check(..., null)` spelling in `SearchController` resolve to the config too.

A narrower variant checks the containing album once per request (`AlbumPhotosController:82/103`, `Actions\Album\PositionData:51`): that at least reads real permission rows, and is only wrong for photos belonging to several albums. `PhotoController` (×3) and `FrameController` already do the correct per-photo check.

Note Feature 067's Q-067-07 previously chose to *reproduce* the v2 mechanism for the Map tier; Q-067-12 later removed `should_downgrade` from that tier entirely, so no shipped code depends on that earlier choice.

---

### ~~Q-069-14~~ · "`ConfigManager` is not bound" ❌ WITHDRAWN — PREMISE WAS FALSE

**Status:** Withdrawn as invalid, 2026-09-22, after the owner pushed back: *"Careful, it MUST NOT be a singleton. Singletons are memory leaks in the case of FrankenPHP."*  
**Feature:** F-069 – Search Struct-of-Arrays (spill-over)

**What was claimed, and why it was wrong**  
This entry originally reported that `ConfigManager` was never bound in the container, that ~104 call sites each built a fresh instance with an empty cache, and that 88 of 95 queries in a `details` call were full `configs` reads. The measurement was real; the diagnosis was not.

`ConfigManager` **is** bound — as **`scoped()`**, by `App\Http\Middleware\ResolveConfigs` (registered globally at `app/Http/Kernel.php:30`):

```php
$config = resolve(ConfigManager::class);
app()->scoped(ConfigManager::class, fn () => $config);
```

The original search looked only for `singleton(`/`bind(` in `app/Providers`, `config/` and `bootstrap/`, and so missed a `scoped()` registration made from middleware. The 88-query figure came from a test that invoked the query action **directly**, bypassing the middleware stack, so the scoped binding was never registered and every `resolve()` did construct a new instance. That is a property of the test harness, not of the application.

**Measured through a real HTTP request** (`GET /api/v3/Search/Photos/details`, 3 photos) — 13 queries total:

| # | query |
|---|---|
| 1, 3, 4 | `Schema::hasTable('configs')` guard |
| 2 | `select "key","value" from "configs"` — **one** full load, cached for the request |
| 5, 6 | two single-key `Configs` model reads (a separate code path from `ConfigManager`) |
| 7 | `user_groups`, once |
| 8 | the photo candidate query |
| 9–12 | `size_variants` / `palettes` / `statistics` / `tags`, each batched over all ids |
| 13 | the grouped grants query from D8 |

No lazy `access_permissions` loads at all, and no repeated config loading. Nothing to fix.

**Why the proposed fix would have been harmful.** This application runs under Octane/FrankenPHP worker mode (`laravel/octane` in `composer.json`, `config/octane.php`, Feature 002 and `docs/specs/2-how-to/deploy-worker-mode.md`). There, the application instance survives across requests, so a `singleton()` persists request-scoped state — leaking memory and serving stale config. `scoped()` is precisely the correct binding for this, because Octane flushes scoped bindings between requests. The existing design was already right.

**Audit result (owner asked to verify there are none).** The only `->singleton()` calls in the codebase are the three framework-standard bindings in `bootstrap/app.php` — HTTP kernel, console kernel, exception handler — which are stateless and required by Laravel itself. There are **no application-level singletons**. Every per-request service is registered with `scoped()`: `ConfigManager` and `Watermarker` (`ResolveConfigs`), `VerifyInterface` and `Verify` (`ResolveVerify`). No `->instance()` bindings anywhere.

**Lesson carried forward:** measure through the real request stack before drawing conclusions about query volume — a direct action call bypasses middleware that is load-bearing for object lifetime.

---

### ~~Q-069-13~~ · Should the v2 `/Search` endpoint's over-grant be fixed too? ✅ RESOLVED

**Status:** Resolved (owner, 2026-09-22: "Fix the over-grants.") — implemented as **Feature 070**, scoped to **every** affected surface rather than `/Search` alone. Two more were found during implementation that this entry had not listed: `PersonPhotosController` (`should_downgrade: false`, an unconditional grant) and `FlowItemResource` (album-wide gate). See `docs/specs/4-architecture/features/070-full-photo-access-over-grant/`  
**Feature:** F-069 – Search Struct-of-Arrays (spill-over from Q-069-12)  
**Preferred option:** 🅰️ (**recommended**) Option A – Fix v2 search in place, leave the other two sites to their own change

**Question**  
Q-069-12 established that v2's `/Search` hands out full-resolution URLs based on a global config alone. Feature 069's spec currently says the v2 endpoint is not to be touched (NG5), which was written when this looked like a compatibility question rather than a rights bug. v7 still ships against that endpoint, and `SpotlightSearch.vue` still calls it (NG2), so the fix has real reach.

---

#### 🅰️ (**recommended**) Option A – Fix v2 search in place
- **Idea:** Replace the request-wide value in `SearchController::search()` with the per-photo `!Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $p])` already used by `PhotoController`/`FrameController`, applied inside the existing `$photos->through(...)` closure. Amend NG5 to carve out this one fix.
- **Pros:**
  - ✅ Closes the over-grant for every current consumer, including v7 and Spotlight, not just the v3 path.
  - ✅ The correct mechanism already exists in the same codebase; no new policy code.
  - ✅ Makes v2 and v3 agree, which removes the one documented exception from NFR-069-06's parity contract.
- **Cons:**
  - ❌ One `Gate::check()` per photo per page (up to `search_result_limit`), where v2 previously did none — the cost v2's shortcut was presumably buying.
  - ❌ A visible behaviour change to a shipped endpoint: some viewers lose original-resolution links they currently have.

---

#### 🅱️ Option B – Fix all the raw-config sites together, as their own feature
- **Idea:** Treat it as one rights-correctness feature covering all five seed-config-as-gate sites (`SearchController:50`, `AlbumPhotosController:58`, `Actions\Albums\PositionData:70`, `EmbedController:100`, `TimelineResource:80`), plus a decision on `AlbumPolicy::canAccessFullPhoto()`'s own null/smart-album config fallback and on the album-scoped middle tier.
- **Pros:**
  - ✅ Fixes the class of bug rather than one instance; a viewer cannot simply switch surface to obtain what search now denies.
  - ✅ Gets its own spec, scenario matrix and regression tests, which a cross-cutting rights change warrants.
- **Cons:**
  - ❌ Larger and slower; leaves the known over-grant live in the meantime.
  - ❌ Out of scope for Feature 069 as specified.

---

#### 🅲 Option C – Leave v2 as-is
- **Idea:** v3 is correct; v2 keeps its behaviour until it is retired.
- **Pros:**
  - ✅ Zero risk to v7 and Spotlight; no performance change.
- **Cons:**
  - ❌ Knowingly leaves a rights over-grant in a shipped, reachable endpoint.
  - ❌ The `struct-of-array` flag becomes security-relevant — flag off silently means "more permissive", which nothing in the settings UI communicates.

---

**Next action**  
Feature owner to choose A, B or C. Feature 069 ships correctly under all three.

---

### ~~Q-069-11~~ · Unsorted photos have no containing album to name ✅ RESOLVED DIRECTLY

**Status:** Resolved directly during I4 implementation, 2026-09-22. Encoded in spec.md's FR-069-04 and in `QuerySearchPhotos::buildResource()`; covered by `QuerySearchPhotosTest::testAnUnsortedPhotoReportsTheUnsortedSmartAlbum`.  
**Feature:** F-069 – Search Struct-of-Arrays

**Question**  
FR-069-04 states that a photo with no accessible containing album "cannot appear in the result at all, so the field is never null". Implementation disproved the premise: `PhotoQueryPolicy::appendSearchabilityConditions()` ORs in `photos.owner_id = :uid`, so a user's **unsorted** photos (no `photo_album` row whatsoever) are searchable in v2 and are returned to their owner. The join-and-collapse pass of Q-069-07 finds no album for them, so `album_ids[i]` would have been absent.

**Rationale for the resolution**  
Three options existed: drop such photos from v3 results; allow a null `album_ids[i]`; or name a stand-in album. Dropping them is a silent membership change v2 never made, which NFR-069-06/07 exist to prevent. A null would push a special case onto every frontend consumer and break the `<Thumb :album-id>` contract that makes `album_ids` load-bearing in the first place. Naming the **`unsorted` smart album** (`SmartAlbumType::UNSORTED`) costs nothing: it is where the UI would navigate to find that photo anyway, the v3 Asset endpoint already accepts a smart-album id in its `{album_id}` segment, and `UnsortedAlbum`'s own access rules independently re-authorize the viewer. FR-069-04's "never null" guarantee is therefore preserved, by a different mechanism than the spec first assumed.

---

### ❓ Q-069-10 · What bounds the whole-scope search response, now that pagination is gone? ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-22 — owner selected "Hard cap + is_truncated"). Generalised into **ADR-069-01** (`docs/specs/6-decisions/ADR-069-01-v3-collection-bounding-strategies.md`), which names the four bounding strategies for v3 SoA collections so the next feature need not re-derive this comparison. `search_pagination_limit` is renamed `search_result_limit` by migration and changes meaning to "maximum photo hits per search"; the tier-2 query selects `limit + 1` and reports `is_truncated` when the extra row exists. To be encoded in spec.md's Functional Requirements, the tier-2 resource, and a UI state for the truncation hint.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Preferred option:** 🅰️ (**recommended**) Option A – Repurpose `search_pagination_limit` as a hard result cap

**Question**  
Q-069-02 resolved to whole-scope unpaginated. That removes the `search_pagination_limit` config (default 300, deliberately *reduced* from 1000 by migration `2025_03_01_154728_search_pagination_limit_reduction.php` — so its size has been actively tuned, not left at a default) from the request path entirely. With it goes the only thing that has ever bounded a search response. A two-character term against a large library would return every matching photo's tier-2 row in one body. The owner accepted that trade-off knowingly, but the config key still exists, is admin-visible, and would silently become dead — so what replaces it as the safety valve needs an explicit answer rather than an implicit "nothing."

---

#### 🅰️ (**recommended**) Option A – Repurpose the config as a hard result cap
- **Idea:** `search_pagination_limit` keeps its stored value but changes meaning to "maximum photo hits returned by one search." The query selects `limit + 1` rows; if the extra row exists, the response returns the first `limit` rows plus `is_truncated: true`, and the UI shows a "showing the first N matches — refine your search" hint. Rename the key to `search_result_limit` with a migration, since the old name would be actively misleading.
- **Spec impact:** New FR for the cap + `is_truncated` flag; config-rename migration; a UI state for the truncation hint.
- **Pros:**
  - ✅ Keeps a real, admin-tunable ceiling — the response stays bounded by exactly the number the admin already tuned.
  - ✅ Preserves an existing setting's intent instead of orphaning it.
  - ✅ Still genuinely "whole-scope" for every realistic search; the cap only bites on pathologically broad terms.
  - ✅ Honest to the user: a truncated result says so, rather than silently lying about completeness.
- **Cons:**
  - ❌ A capped result is not strictly "everything that matched" — the client cannot page to the rest, by design.
  - ❌ Config rename touches the settings UI and all 22 locale files.

---

#### 🅱️ Option B – Truly unbounded; retire the config
- **Idea:** No cap. Every match's tier-2 row is returned. `search_pagination_limit` is dropped by migration.
- **Pros:**
  - ✅ Simplest possible contract; literally what Option B of Q-069-02 says.
  - ✅ The result is always complete — no truncation semantics to specify, test or explain.
  - ✅ Tier-2 rows are already the lean projection; the per-photo cost is far below v2's `PhotoResource`.
- **Cons:**
  - ❌ Nothing stands between a broad term and a full-library response body — the exact failure mode Feature 067 was created to remove from the Map.
  - ❌ Removes an admin lever that was actively tuned down as recently as 2025.

---

#### 🅲 Option C – Map-style refuse-and-fall-back
- **Idea:** Feature 067's own resolution: fetch `cap + 1`; if over the cap, return an **empty** result plus a flag telling the client the search is too broad to render, prompting the user to refine.
- **Pros:**
  - ✅ Exact precedent already shipped and tested in this codebase (`QueryMapPhotos`).
  - ✅ Hard bound with no partial-result ambiguity.
- **Cons:**
  - ❌ Returning nothing for a broad search is far worse UX here than on a map — the Map has bucket badges to fall back to; search would have a blank grid.
  - ❌ Discards work the query already did.

---

**Next action**  
Feature owner to choose A, B or C before spec.md's tier-2 resource and NFR cost ceiling are written.

---

### ❓ Q-069-01 · Route shape for v3 Search — dedicated family, or a parameterised pseudo-album? ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-22 — owner selected "Dedicated /Search/* family"). To be encoded in spec.md's Interface & Contract Catalogue.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Preferred option:** 🅰️ (**recommended**) Option A – Dedicated `/api/v3/Search/*` family

**Question**  
Every prior SoA feature picked one of two shapes. Feature 067 (Map) added a dedicated route family (`/Map/buckets`, `/Map/Photos`, `/Map/tracks`) because its scope is not an album. Feature 066 (Timeline) instead modelled its scope as `App\SmartAlbums\TimelineAlbum` and reached the *existing* `/Albums/{album_id}/Photos[/buckets|/details]` tiers with `album_id='timeline'` — "zero new routes" — because `ResolvesPhotoSource::resolvePhotoQuery()`'s `BaseSmartAlbum` branch just calls `$album->photos()`.

Search fits neither cleanly. Like Timeline it is a cross-album photo scope, so the pseudo-album trick is mechanically available. But unlike every existing smart album, a "search album" is **parameterised per request** (the token list, plus an optional origin album to search within) rather than being a fixed singleton — `AlbumFactory` would have to construct it from request state, and every cache key would need a token digest. Search also returns a second, co-equal result set (albums), which has no pseudo-album expression at all and needs its own route regardless.

---

#### 🅰️ (**recommended**) Option A – Dedicated `/api/v3/Search/*` family
- **Idea:** `GET /api/v3/Search/albums`, `/Search/Photos/buckets`, `/Search/Photos`, `/Search/Photos/details`, all taking `terms` (+ optional `album_id` origin, `sorting_column`, `sorting_order`). Mirrors Map's naming exactly. New `App\Actions\Search\StructOfArrays\*` query classes that reuse the existing `PhotoSearch`/`AlbumSearch` strategy registries for the *predicate*, and Feature 064's bucket/ratio/detail *projection* logic for the shape. The v2 `GET /Search` route stays untouched alongside.
- **Spec impact:** 4 new routes, a new `SearchListingController`, new request classes, new `CacheKeyProvider` methods keyed on a token digest.
- **Pros:**
  - ✅ The search term stays an honest request parameter instead of being smuggled into a model identity.
  - ✅ Both halves of the result (albums + photos) live in one coherent, symmetric family.
  - ✅ No change to `AlbumFactory`, `SmartAlbumType`, `AlbumPolicy` or any existing v3 route — zero blast radius on Features 064/066.
  - ✅ Cache keys are naturally scoped to the token digest; no risk of a search response leaking into an album-scoped cache entry.
- **Cons:**
  - ❌ Duplicates the tier-2/tier-3 projection code unless it is deliberately factored into a shared trait (extra refactoring surface).
  - ❌ Four new routes rather than one.

---

#### 🅱️ Option B – `SearchAlbum` pseudo-album on the existing photo tiers
- **Idea:** New `App\SmartAlbums\SearchAlbum` (parameterised by tokens + origin) reached as `album_id='search'` with `terms` as an extra query param on the existing `/Albums/{album_id}/Photos*` routes. Only the album half gets a new route (`/Search/albums`).
- **Spec impact:** `AlbumFactory` gains request-state-dependent construction; `SmartAlbumType::SEARCH` added; `GetPhoto{Buckets,Ratios,Details}Request` all gain a `terms` rule; `ResolvesPhotoSource::resolveEffectiveSorting()` gains a `SearchAlbum` branch for `SearchSortingType`.
- **Pros:**
  - ✅ Reuses `QueryPhotoBuckets`/`QueryPhotoRatios`/`QueryPhotoDetails` verbatim — no projection duplication at all.
  - ✅ Follows the most recent cross-album precedent (Feature 066).
- **Cons:**
  - ❌ Makes an album's *identity* depend on request parameters, which nothing in `AlbumFactory`/`AlbumPolicy` currently assumes — the existing photo-tier request classes and their cache keys would all have to become token-aware, i.e. the blast radius lands squarely on the already-shipped Features 064/066 rather than on new code.
  - ❌ `terms` becomes a parameter that is meaningful for exactly one value of `{album_id}` and ignored for every other — an awkward contract for an endpoint three other features already depend on.
  - ❌ Still needs a separate album-results route, so it does not actually achieve "zero new routes."

---

#### 🅲 Option C – One combined `GET /api/v3/Search` returning both halves
- **Idea:** A single endpoint returning albums and photos as two SoA blocks in one body, closest to today's v2 `ResultsResource`.
- **Spec impact:** One route; no tiering.
- **Pros:**
  - ✅ Fewest round trips; simplest client.
  - ✅ Smallest diff from today's frontend data flow.
- **Cons:**
  - ❌ Forfeits the tiering that is the entire point of the SoA work — the heavy per-photo payload is fetched for every hit whether or not it is ever rendered.
  - ❌ No way to window a library-wide result set (see Q-069-02); reintroduces exactly the unbounded-fetch problem Feature 067 was created to remove.

---

**Next action**  
Feature owner to choose A, B or C before spec.md's Interface & Contract Catalogue is written.

---

### ❓ Q-069-02 · Photo-result delivery model — bucket-windowed, whole-scope, or keep pagination? ✅ RESOLVED

**Status:** Resolved (**Option B**, 2026-09-22 — owner selected "Whole-scope unpaginated", not the recommended Option A). Consequences: **no bucket tier for search photos at all**; exactly two photo tiers (`/Search/Photos` whole-scope + `/Search/Photos/details` on demand); the frontend renders one flat, unbucketed chunk, reusing `PhotoGridVirtual.vue`'s already-supported `bucketable:false` path. The unbounded-response risk listed under Option B's cons is accepted knowingly; see Q-069-10 for what now bounds it. To be encoded in spec.md's Functional Requirements + a dedicated NFR.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Preferred option:** 🅰️ (**recommended**) Option A – Bucket-tiered and `bucket_ids[]`-windowed

**Question**  
Today's search paginates photos server-side at `search_pagination_limit` (300/page) and the frontend renders a classic page-jump `UPagination` above and below the grid. Every SoA view instead loads its scope once and virtualises rendering. Search is the hardest case for "load everything": a two-character term against a large library can match essentially every photo, and unlike an album (bounded by its own contents) or Flow (bounded by album count) there is no natural ceiling. Search's sort options (`SearchSortingType` = `title` | `created_at` | `taken_at`) are all bucketable by the existing `PhotoBucketComputer` machinery.

---

#### 🅰️ (**recommended**) Option A – Bucket-tiered + `bucket_ids[]`-windowed (Timeline model)
- **Idea:** Three tiers exactly as Feature 064/066: `buckets` returns per-bucket counts/labels for the whole result set (one cheap `GROUP BY`), `ratios` is fetched incrementally windowed by `bucket_ids[]`, `details` is fetched on demand. Bucket ids are computed live (no materialised column exists for a cross-album search), SQL-pushdown bounded via `PhotoBucketComputer::bucketDateRange()` exactly as Timeline already does.
- **Spec impact:** Three photo tiers; NFR pinning cost to "bounded by the requested bucket window, never total match count."
- **Pros:**
  - ✅ The only option whose cost does not scale with how broad the search term is.
  - ✅ Reuses Timeline's already-proven live-bucket mechanism for a global scope — same problem, same solution.
  - ✅ Gives the frontend a scrubber/sticky-header affordance search has never had, and drops page-jump entirely.
  - ✅ Frontend cost is near-zero: `PhotoGridVirtual.vue`'s `source` prop was designed as this exact seam (`"album" | "timeline"` → add `"search"`).
- **Cons:**
  - ❌ Most implementation work of the three.
  - ❌ A result set sorted by `title` buckets into alphabetical prefixes, which is a less intuitive scrubber than dates.

---

#### 🅱️ Option B – Whole-scope unpaginated in one request (Flow/Albums model)
- **Idea:** One `GET .../Photos` returning every match's tier-2 row at once; `details` still on demand.
- **Spec impact:** Two tiers; an explicit, documented unbounded-response NFR.
- **Pros:**
  - ✅ Much simpler — no bucket tier, no live bucket computation, no windowing state in the store.
  - ✅ Matches `GET /api/v3/Albums` and `GET /api/v3/Flow`'s existing unpaginated precedent.
- **Cons:**
  - ❌ Reintroduces the unbounded whole-scope fetch that Feature 067 explicitly existed to eliminate — a broad term returns the entire library's tier-2 rows in one body.
  - ❌ No natural bound, unlike Flow (album count) or an album (its own contents).

---

#### 🅲 Option C – Keep server-side page-jump pagination, SoA payload only
- **Idea:** Same `page`/`per_page`/`total` contract as v2, but each page's body is SoA-shaped instead of `PhotoResource[]`.
- **Spec impact:** Smallest possible frontend change — `UPagination` stays.
- **Pros:**
  - ✅ Bounded by construction, no new mechanism at all.
  - ✅ Lowest-risk migration; preserves a UX users already know.
- **Cons:**
  - ❌ Leaves search as the one view that still paginates while every other view virtualises — the inconsistency this feature exists to remove.
  - ❌ Gains only the payload-size win, none of the interaction win.

---

**Next action**  
Feature owner to choose A, B or C before spec.md's Functional Requirements are written.

---

### ❓ Q-069-03 · Album half of the search result — how far to migrate? ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-22 — owner selected "Flat SoA + /rights tier"). To be encoded in spec.md's Interface & Contract Catalogue.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Preferred option:** 🅰️ (**recommended**) Option A – Flat SoA listing + separate `/rights` tier

**Question**  
Search returns albums and photos side by side. Album hits are returned **unpaginated and unbounded** today, each serialised through `ThumbAlbumResource` — which per album resolves `AlbumProtectionPolicy`, `AlbumPolicy::isUnlocked()`, `get_thumb()`, a 17-boolean `AlbumRightsResource`, several config reads, and `$data->owner->name` with no eager load (an N+1, since `AlbumSearch::queryAlbums()` never loads `owner`). Album hit counts are normally small, but nothing bounds them.

---

#### 🅰️ (**recommended**) Option A – Flat SoA listing + separate `/rights` tier
- **Idea:** `GET /api/v3/Search/albums` returns an `AlbumDataResource`-shaped SoA body (reusing Feature 062's existing resource and its `cover_ids[]` → Asset-endpoint thumbnail pattern), with the rights booleans split into a second, separately-cached tier exactly as `/Albums/root/rights` already does. No bucket tier.
- **Spec impact:** One (or two, with `/rights`) new routes; reuses an existing resource class and the existing `adaptAlbumChildTile.ts` frontend adapter.
- **Pros:**
  - ✅ Kills the `owner` N+1 and the per-album policy/thumb resolution in one step.
  - ✅ Reuses `AlbumThumbGridVirtual.vue`/`AlbumThumbVirtual.vue` and `combineAlbumChildRights()` unchanged.
  - ✅ Symmetric with the photo half, and with how root album listing already works.
- **Cons:**
  - ❌ Two round trips for the album half instead of one.

---

#### 🅱️ Option B – Full bucket tier for albums too
- **Idea:** Give album hits the same `buckets`/`data`/`rights` triad as `/Albums/root`.
- **Pros:**
  - ✅ Perfectly uniform with the album-listing family; bounded even for a pathological term.
- **Cons:**
  - ❌ Album hit counts are small in practice — bucketing solves a problem this half does not have.
  - ❌ Sticky alphabetical/date headers over a handful of album tiles is visual noise.

---

#### 🅲 Option C – Leave album hits on the v2 `ThumbAlbumResource` shape
- **Idea:** Migrate only photos; the album grid keeps calling v2.
- **Pros:**
  - ✅ Smallest scope; album rendering code untouched.
- **Cons:**
  - ❌ The page would hold two different data contracts at once, and keep the v2 endpoint's `owner` N+1 forever.
  - ❌ Leaves the one genuinely unbounded part of the search response unaddressed.

---

**Next action**  
Feature owner to choose A, B or C before spec.md's Interface & Contract Catalogue is written.

---

### ❓ Q-069-04 · `SpotlightSearch.vue` — migrate to v3, or deliberately leave on v2? ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-22 — proposed as the working default in chat, owner did not object). Spotlight stays on `GET /api/v2/Search`; to be recorded in spec.md's Non-Goals.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Preferred option:** 🅰️ (**recommended**) Option A – Leave Spotlight on v2, document it

**Question**  
`GET /api/v2/Search` has a second frontend consumer besides the Search page: `resources/js/v8/components/modals/SpotlightSearch.vue` (with `useSpotlightRemoteSearch.ts`), the 300 ms-debounced global quick-search palette. It renders each hit via `photo.size_variants.thumb?.url` — always empty on an SoA tile, since `adaptPhotoTile()` fills `size_variants` with placeholders. So it either migrates or it must be consciously left behind.

---

#### 🅰️ (**recommended**) Option A – Leave Spotlight on v2, document the split
- **Idea:** Spotlight keeps calling `GET /api/v2/Search`, which survives this feature anyway (the v7 frontend still uses it, and every prior SoA feature kept its v2 route coexisting).
- **Pros:**
  - ✅ Spotlight shows a short, capped, non-virtualised top-N list — it gains nothing from tiering or bucketing.
  - ✅ Keeps this feature's frontend scope to the one view that actually benefits.
  - ✅ Zero risk to a working, debounced hot path.
- **Cons:**
  - ❌ Two search data paths coexist in v8 until some later feature unifies them.

---

#### 🅱️ Option B – Migrate Spotlight too
- **Idea:** Point Spotlight at the new tier-2 endpoint and swap its `<img>` for `<Thumb>`.
- **Pros:**
  - ✅ One search contract across v8; the v2 route becomes v7-only.
  - ✅ Spotlight's thumbnails start flowing through the ref-counted, LRU-cached `ThumbAssetService` like every other v8 thumbnail.
- **Cons:**
  - ❌ Under Option A of Q-069-02, Spotlight would have to fetch a bucket tier just to show ten rows — strictly more round trips than today.
  - ❌ Expands frontend scope for a surface with no performance problem.

---

**Next action**  
Feature owner to choose A or B before plan.md's frontend increments are scoped.

---

### ❓ Q-069-05 · `date:` means `taken_at` for photos but `created_at` for albums ✅ RESOLVED

**Status:** Resolved (Option A, 2026-09-22 — owner selected "Keep current semantics"). `AlbumDateStrategy` is carried into v3 untouched; to be recorded in spec.md's Non-Goals.  
**Feature:** F-069 – Search Struct-of-Arrays  
**Preferred option:** 🅰️ (**recommended**) Option A – Carry the current semantics into v3 unchanged

**Question**  
`DateStrategy` matches `photos.taken_at`, but `AlbumDateStrategy` matches `base_albums.created_at` — the album's *upload* date, not the date of the photos in it. So `date:2019-07-04` returns photos taken that day alongside albums merely created that day. `base_albums` has no `taken_at`, but `albums` already carries the `min_taken_at`/`max_taken_at` aggregates the album SoA tier selects anyway, so a consistent reading is mechanically available. This is a visible behaviour change, not a shape change, so it is called out rather than bundled silently.

---

#### 🅰️ (**recommended**) Option A – Carry today's semantics into v3 unchanged
- **Idea:** `AlbumDateStrategy` keeps matching `base_albums.created_at`; v3 reproduces v2's result set exactly.
- **Pros:**
  - ✅ Keeps this feature a pure shape/transport migration — the diff stays reviewable as such.
  - ✅ Existing `AlbumSearchTest` assertions stay valid, so any change in results during the migration is a real regression rather than an intended one.
- **Cons:**
  - ❌ Leaves a genuinely confusing semantic in place.

---

#### 🅱️ Option B – Unify onto the album's photo date range
- **Idea:** `date:X` matches an album when `X` falls within `[min_taken_at, max_taken_at]`; operators map onto the appropriate bound.
- **Pros:**
  - ✅ `date:` finally means one thing across both halves of the result.
  - ✅ Uses columns the album tier already selects — no extra join.
- **Cons:**
  - ❌ A behaviour change landing inside a migration feature, which muddies regression analysis.
  - ❌ Albums with no dated photos (both bounds null) silently stop matching any `date:` query.

---

#### 🅲 Option C – Unify, and add an explicit `created:` modifier for the old meaning
- **Idea:** Option B, plus a new modifier preserving today's album-creation-date search.
- **Pros:**
  - ✅ No capability is lost; both meanings become expressible and explicit.
- **Cons:**
  - ❌ Grows the token grammar, the advanced-search panel and the docs — unrequested scope on top of an already large feature.

---

**Next action**  
Feature owner to choose A, B or C before spec.md's Functional Requirements are written.

---

### ~~Q-069-06~~ · Keep base64 `terms` encoding in v3? ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-22 — keep base64. To be recorded in spec.md's request-contract section once authored.  
**Feature:** F-069 – Search Struct-of-Arrays

**Rationale**  
The token grammar embeds `:`, `>=`, `<=`, `"`, `#` and `*` (`SearchTokenParser`'s `KNOWN_MODIFIERS` plus `extractOperator()`/`validateColour()`), all of which are either reserved or routinely mangled by intermediate proxies in a raw query string. The client already has a UTF-8-safe `base64encode()` helper (`resources/js/services/search-service.ts:40`) and the server already round-trips it via `base64_decode($values['terms'], true)`. Lightweight ambiguity, no design trade-off worth the owner's time — noted here for the record only.

---

### ~~Q-069-07~~ · Per-photo `album_ids[]` resolution for cross-album results ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-22 — follow Feature 067's Q-067-11 precedent verbatim. To be recorded in spec.md's tier-2 field catalogue once authored.  
**Feature:** F-069 – Search Struct-of-Arrays

**Rationale**  
Search results span albums, but `<Thumb>` and the v3 Asset endpoint (`/api/v3/Asset/{album_id}/{photo_id}/{variant}`) each need one concrete, viewer-accessible `album_id` per photo. Feature 067 hit precisely this and resolved it: a separate join-and-collapse pass (`photo_album` → `base_albums` → `computed_access_permissions`, `AlbumQueryPolicy::appendAccessibilityConditions()`, `GROUP BY` + `MIN()` to the lowest accessible `album_id`), with no `Album` model hydration and no per-photo access re-check. Same problem, same mechanism — reused rather than re-decided. Note this also means the frontend adapter needs a **per-row** album id, where `adaptPhotoTile(i, ratios, album_id)` currently takes one album id for the whole batch.

---

### ~~Q-069-08~~ · Multi-album photos duplicate rows and inflate `total` ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-22 — fix in v3. To be recorded in spec.md as an explicit, called-out divergence from v2.  
**Feature:** F-069 – Search Struct-of-Arrays

**Rationale**  
`PhotoQueryPolicy::prepareModelQueryOrFail()` left-joins `photo_album` and `albums` with no `distinct()`, so a photo in N albums yields N rows — inflating `total`, wasting page slots and repeating tiles. `ResolvesPhotoSource` already documents this exact hazard and the fix (a `whereIn('photos.id', …)` id-subquery rather than joining on the returned query), adopted there for `BaseSmartAlbum`. v3 is greenfield (per AGENTS.md's backward-compat stance), so reproducing a counting bug for parity would need an explicit owner instruction; absent one, it is fixed and the divergence documented. Consequence to state in the spec: v3's `total` will be lower than v2's for any library with multi-album photos.

---

### ~~Q-069-09~~ · One full-stack feature, or a backend/frontend split? ✅ RESOLVED DIRECTLY

**Status:** Resolved directly, 2026-09-22 — one full-stack Feature 069.  
**Feature:** F-069 – Search Struct-of-Arrays

**Rationale**  
Features 061→063 and 064→065 split backend and frontend adoption into separate features, but the three most recent SoA features (066 Timeline, 067 Map, 068 Flow) were each authored and delivered full-stack in one feature, and that is now the established cadence. Search has exactly one primary frontend consumer, so a split would create a backend feature with no consumer to validate it against — the situation Feature 061 explicitly flagged as a downside.
