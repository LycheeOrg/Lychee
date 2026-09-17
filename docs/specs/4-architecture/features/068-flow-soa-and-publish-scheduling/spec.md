# Feature 068 – Flow Struct-of-Arrays & Publish-Date Scheduling

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-09-17 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/068-flow-soa-and-publish-scheduling/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/068-flow-soa-and-publish-scheduling/tasks.md` |
| Roadmap entry | #068 |

> Guardrail: This specification is the single normative source of truth for the feature. Track
> high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md),
> encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no
> per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for
> architecturally significant clarifications.

## Overview

The v8 Flow page (`/flow`, `resources/js/v8/views/gallery-panels/Flow.vue`) is the one remaining
gallery listing surface still running on the pre-SoA v2 API shape: `App\Actions\Albums\Flow::do()`
eager-loads every album's *entire* photo collection (all size variants, palette, tags, rating,
faces) on every paginated fetch, and the frontend renders every loaded album card as a permanent,
non-virtualized DOM node with eagerly-loaded images. Features 057/062/064/066 already established a
proven Struct-of-Arrays (SoA) pattern (ADR-0009) for exactly this class of problem elsewhere in the
app; this feature ports Flow onto that pattern (Part A) while keeping the v2 path fully intact and
reachable, exactly like those precedents.

Independently, this feature also gives `flow_strategy=opt-in` (introduced by the Flow module but
never wired to any UI) an actual way to set the per-album date it orders/gates by (Part B). Research
during spec drafting found that the column this needs — `base_albums.published_at` — **already
exists**, already drives Flow's opt-in ordering, and is **also** independently used by the Landing
Page's "automatic featured items"/"latest album cover" ordering (Feature 054). The originally
proposed new `flow_datetime`/`flow_datetime_tz` columns are therefore **not** the design used here —
`published_at` is reused and extended in place instead (Decision Card Q-068-01, resolved 2026-09-17,
owner confirmed Option A).

## Goals

- Flow's per-page listing and each card's photo preview are served through a v3 SoA shape,
  coexisting with the v2 path behind the existing `is_struct_of_array_enabled` flag, with response
  size bounded by album-level fields plus a small capped photo preview per card — never a full
  nested photo/size-variant/tag/face graph per album.
- The v8 Flow feed renders through a dynamically-measured virtualizer so DOM/memory usage is bounded
  by the visible viewport, not by total albums ever scrolled past.
- All Flow images gain native lazy-loading, independent of the SoA flag.
- `base_albums.published_at` gains a timezone-aware companion column and is settable from both the
  single-album edit form and the bulk album edit dialog, visible only when `flow_strategy=opt-in`.
- The v2 Flow path and Landing Page's existing `published_at`-ordering behavior remain fully intact
  and byte-identical throughout.

## Non-Goals

- Renaming/forking `published_at` into a Flow-specific column (rejected — see Q-068-01; it is shared
  with Landing Page's Feature 054 ordering, which this feature does not touch beyond gaining the same
  new UI-settable control for free).
- Any change to `flow_strategy=auto`'s ordering (`created_at`) — untouched.
- A dedicated "publish now" one-click action, or any automatic backfill of `published_at` for albums
  that were never opted in — out of scope.
- Removing or deprecating the v2 Flow routes/controller/action/resources/`Flow.vue` rendering path —
  they stay, flag-gated, mirroring every prior SoA-adoption feature (062/064/066).
- Changing `flow_include_photos_from_children`'s existing (already-flagged-as-not-recommended)
  behavior, or any of Flow's other existing config toggles.
- A `scope=own|shared` split, bucket-windowing, or any deep-link mechanism — Flow has no per-owner
  split and no date-bucketed navigation UI to preserve (unlike Timeline/Feature 066).

## Functional Requirements

### Part A — Struct-of-Arrays & rendering performance

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-068-01 | New `GET /api/v3/Flow` returns an **unpaginated**, flat Struct-of-Arrays listing for album-level fields (`ids`, `titles`, `descriptions`, `cover_ids`, `owner_names`, `is_nsfws`, `num_photos`, `num_children`, `min_max_texts`, `published_created_ats`, `diff_published_created_ats`, `statistics`), gated by `is_struct_of_array_enabled`, reusing `App\Actions\Albums\Flow::do()`'s existing query/policy/ordering logic unchanged **except that `flow_max_items` is not applied** — no `page`/cursor, no item cap, mirroring `GET /api/v3/Albums`'s (Feature 057/062) own unpaginated, uncapped, `toBase()`-queried root/tag/person/pinned listings, **not** Timeline's aggregated-count `buckets` tier (each row here is already one whole album, not a reduced count). `flow_max_items` remains meaningful only for the untouched v2 path. | The response returns every album in Flow's current scope/strategy in one call, same set/order as paging through the equivalent v2 `GET /api/Flow` to exhaustion, regardless of `flow_max_items`'s configured value. | `is_struct_of_array_enabled` off → 404/route not hit, frontend uses v2. | Same auth/authorization failures as v2 `FlowRequest`. | None. | ADR-0009; Decision Cards Q-068-04, Q-068-06; this feature. |
| FR-068-02 | New `App\Http\Resources\V3\FlowListResource` mirrors `AlbumListResource`'s (Feature 057/062) parallel-array coding style — manual per-field `foreach` accumulation, not Eloquent `map()`, `toBase()` query, no Eloquent hydration. `is_nsfws[i]` reproduces `FlowItemResource`'s exact *computed* value (`hide_nsfw_in_flow` config check short-circuited against `album->is_recursive_nsfw`), not the raw `is_nsfw` column — this drives `Blur.vue`'s client-side blur trigger and must stay wired the same way in the new card components (T-068-10). | Field-for-field output matches `FlowItemResource`'s v2 data for the same album set (minus the nested `photos` collection), including NSFW blur behavior. | N/A. | N/A. | None. | Precedent: `app/Http/Resources/V3/AlbumListResource.php`; `app/Http/Resources/Flow/FlowItemResource.php`'s `is_nsfw` derivation. |
| FR-068-03 | `GetPhotoRatiosRequest`/`QueryPhotoRatios::do()` gain an optional `limit` param (whole-scope, non-bucket/non-photo-id callers only): caps the returned photo count to the first `limit` rows in the album's existing effective sort order. Omitted → unchanged existing (unbounded) behavior for every existing caller. The `limit` value itself is a plain request param the backend accepts as given — it defines no default, config, or opinion of its own (Flow's specific value is a frontend constant, FR-068-04/Q-068-07). | Flow's per-card carousel fetch requests `limit=12` (FR-068-04), fired only once that card scrolls into (or near) the viewport — never eagerly for every album returned by FR-068-01; existing album-page callers (Feature 064/065) are unaffected when the param is absent. | `sometimes|integer|min:1`, `prohibits:bucket_ids,photo_ids` (mirrors Feature 066's mutual-exclusion pattern). | Invalid value → 422. | None. | Additive extension of Feature 064's `ratios` tier, precedent: Feature 066's `bucket_ids`/`photo_ids` additive params. |
| FR-068-04 | Flow's per-card photo preview is fetched via the existing v3 `GET /api/v3/Albums/{album_id}/Photos?limit=N` (ratios tier, FR-068-03, `N=12` — a fixed frontend constant `FLOW_CAROUSEL_PHOTO_LIMIT`, no config key, per Decision Card Q-068-07) + the existing v3 `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` endpoint for thumbnails — no nested `PhotoResource` objects embedded in the Flow response at all. The album's full `num_photos` (FR-068-01) remains visible on the card regardless of the 12-photo preview cap; opening the album itself still shows every photo via the existing, unaffected per-album photo-listing path. Each album from FR-068-01's flat listing is therefore this tier's own "bucket equivalent": one lazily-triggered photo fetch per album, keyed by album id, not by a date/title bucket id. Fetched results are cached centrally in the v3 store (DO-068-06), keyed by album id — **not** in per-card component-local state — so a card derendered and re-rendered by the virtualizer (FR-068-05) reuses its already-fetched data instead of re-fetching. | Card carousel/header images load via lazy `Asset` requests, not inline in the Flow payload; scrolling a card out of and back into view never triggers a duplicate fetch. | N/A. | N/A. | None. | Reuses Feature 056/064 routes unmodified; required for S-068-15's "no re-show on scroll-back" guarantee. |
| FR-068-05 | The v8 Flow feed (`Flow.vue`, flag on) renders through `@tanstack/vue-virtual`'s DOM-measured (`measureElement`) mode, not the analytic/uniform-geometry mode `PhotoGridVirtual.vue` uses for photo grids — card height depends on variable text/photo content, not a WASM-packed layout. All album cards are already known (FR-068-01 loaded them in one shot); the virtualizer's job is purely rendering-window bookkeeping, not a data-fetch trigger. | Scrolling through the whole album list keeps live DOM nodes bounded to the visible range ± overscan; memory does not grow unboundedly with scroll distance. | N/A. | N/A. | None. | This feature; contrast with Feature 066's analytic layout (Decision Card Q-068-02). |
| FR-068-06 | All Flow images (`HeaderImage.vue`, `TopImages.vue`, `CarouselImages.vue`) gain a native `loading="lazy"` attribute, applied unconditionally (independent of the SoA flag, applies to both v2 and v3 rendering paths). | Below-the-fold images are not fetched until scrolled near viewport, verifiable via browser devtools network tab. | N/A. | N/A. | None. | Quick, flag-independent win; NFR-068-04. |
| FR-068-07 | `FlowItemResource`'s Markdown-converted `description` is cached per album (new `flowDescriptionTag($albumId)` managed-cache tag, reusing `ManagedCacheService`), invalidated whenever that album's `description` is saved. | Repeated Flow fetches touching the same album do not re-run `Markdown::convert()` for an unchanged description. | N/A. | N/A. | None. | Existing `ManagedCacheService`/`ManagedCachePhotoListingInvalidator`-style pattern. |
| FR-068-08 | The v2 Flow routes, `FlowController`, `Flow::do()`, `FlowResource`/`FlowItemResource`/`InitResource`, and `Flow.vue`'s existing (non-virtualized, paginated) rendering path remain fully intact and reachable when `is_struct_of_array_enabled` is off. | Flag off → v2 behavior byte-identical to pre-feature. | N/A. | N/A. | None. | Mirrors Feature 065/066's own coexistence precedent. |
| FR-068-09 | While a card's per-album photo fetch (FR-068-04) is in flight, that card renders a loading-skeleton/placeholder in its carousel/header area (fixed or estimated dimensions, no layout jump once real thumbnails arrive), instead of an empty or missing carousel. | Every card entering the viewport shows a skeleton immediately, replaced by real thumbnails once `ratios`/`Asset` resolve; a card whose album genuinely has zero photos (rare, per `Flow::do()`'s existing "exclude albums without photos" filter) never reaches this state. | N/A. | A failed photo fetch resolves to an empty carousel (no infinite skeleton), consistent with this app's existing error-handling posture for non-critical listing data. | None. | This feature; addresses the user's explicit ask for placeholders while photos load. |

### Part B — Publish-date scheduling for `flow_strategy=opt-in`

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-068-10 | `base_albums` gains `published_at_orig_tz` (`string(31)`, nullable) alongside the existing `published_at` (`dateTime(0)`, nullable, already indexed) — **no rename**. Migration backfills `published_at_orig_tz = date_default_timezone_get()` for every row where `published_at IS NOT NULL` — a best-effort approximation using the admin's *current* default timezone at migration time, not necessarily what was in effect when each historical row was set (same accepted approximation as `taken_at_orig_tz`'s own precedent, `2025_01_24_200235_add_initial_taken_at.php`, a plain schema-add + backfill migration — the closer match than the larger `2021_06_01_181900_refactor_timestamps_anew.php`). | Existing non-null `published_at` rows gain a valid (if approximated) timezone label; new rows always write both columns together going forward. | N/A (migration only). | N/A. | None. | Q-068-01. |
| FR-068-11 | `BaseAlbumImpl` gains `implements HasUTCBasedTimes` (the `UTCBasedTimes` trait is already `use`d by this class, so its required methods already exist); `published_at`'s cast changes from plain `'datetime'` to `DateTimeWithTimezoneCast::class`; `published_at_orig_tz` is added to the class's explicit `$attributes` default array (`null`), per this class's own documented requirement that all attributes be listed explicitly. | `$album->published_at` returns a `Carbon` in its originally-recorded timezone, same as `Photo::taken_at` today. | N/A. | Missing `published_at_orig_tz` for a non-null `published_at` throws `LycheeDomainException` (existing cast behavior, unmodified). Note: unlike `taken_at`/`initial_taken_at` (two real `dateTime` columns whose *mismatched precision* caused `[[project_datetimewithtimezonecast_dirty_check_bug]]`), `published_at_orig_tz` is a plain `string` column with no datetime precision of its own — that specific risk class does not apply to this pair; the cast's already-shipped `compare()` method (the *other*, already-fixed half of that bug) is what's actually being relied on here. | None. | `app/Casts/DateTimeWithTimezoneCast.php`; NFR-068-05. |
| FR-068-12 | Zero behavior change to `AlbumQueryPolicy::joinBaseAlbumOwnerId()`, `LandingPageResource`'s two `orderBy('base_albums.published_at', 'DESC')` call sites (`resolveAutomaticFeaturedItems()`, `resolveLatestAlbumCover()`), or `Flow.php`'s existing `published_at` column references — all operate on the raw SQL column via `DB::table()`/`joinSub()`, unaffected by the PHP-side Eloquent cast change. | Landing Page automatic-featured-items/latest-cover ordering and Flow's opt-in ordering/gating are unchanged before/after this feature. | Existing `LandingPageResource`/`Flow` test suites regression-pass unmodified. | N/A. | None. | NFR-068-06. |
| FR-068-13 | `UpdateAlbumRequest` (`PATCH /Album`) gains a `published_at` field (new `HasPublishedAt` contract + trait, `RequestAttribute::PUBLISHED_AT_ATTRIBUTE`), validated `present|nullable|date` (ISO-8601 string with an explicit UTC offset, e.g. `2026-09-17T10:00:00+02:00`, mirroring how `PhotoEdit.vue`/`PhotoService` already send `taken_at`) — `present`, not `sometimes`, matching this endpoint's own majority convention (every field except the two deliberately-legacy-v7-compatible `slug`/`tags` uses `present`; `AlbumProperties.vue` always sends a complete payload on save, it never partially omits keys). Submitting `null` clears both `published_at`/`published_at_orig_tz`. | Owner sets/clears an album's publish date via the API; the cast splits the submitted instant into `(published_at, published_at_orig_tz)` on write. | Non-parseable/ambiguous (no-offset) string → 422; missing key entirely → 422 (`present` rule), consistent with every other non-legacy field on this endpoint. | N/A. | None. | Mirrors `taken_at`'s existing single-album edit precedent (`PhotoEdit.vue`/`PhotoService::update()`); `present` convention verified against `UpdateAlbumRequest::rules()`. |
| FR-068-14 | `App\Http\Resources\GalleryConfigs\InitConfig` gains `is_flow_opt_in_strategy: bool = flow_enabled && flow_strategy===FlowStrategy::OPT_IN`, surfaced to `LycheeState.ts`, so the frontend can gate the publish-date field's visibility without a dedicated round-trip. Deliberately **not** broadened to also cover Landing Page's independent use of `published_at` (Decision Card Q-068-05, confirmed) — a site using only Landing Page's automatic-featured-items mode has no UI path to set this field, by design, not oversight. | Toggling `flow_strategy` in admin settings changes `is_flow_opt_in_strategy` on the next config fetch; both edit surfaces (FR-068-15/17) react to it. | N/A. | N/A. | None. | Mirrors `is_se_enabled`'s existing derivation pattern in the same class; Q-068-05. |
| FR-068-15 | `AlbumProperties.vue` (single-album edit form) gains a "Flow publish date" field — checkbox-gated `datetime-local` input + timezone `USelectMenu`, mirroring `PhotoEdit.vue`'s `taken_at` editing UX exactly (toggle checkbox, disabled/dashed-border styling when off, combined ISO+offset string built on save) — visible only when `is_flow_opt_in_strategy` is true. Not gated by `is_expert_mode` (this is a primary control for a feature the admin explicitly opted into, not a power-user knob). | Owner toggles the checkbox on, picks a date/time + timezone, saves; the album becomes visible in an opt-in Flow ordered by that instant. | Save disabled/no-op while the checkbox is on and the date field is empty (mirrors `taken_at`'s existing disabled-save guard). | Save failure surfaces the existing generic album-update error toast. | None. | S-068-10. |
| FR-068-16 | `PatchBulkAlbumRequest`/`BulkAlbumPatchData` gain an optional `published_at` field (`sometimes|nullable|date`, same validation as FR-068-13), added to the `after()`/`processValidatedValues()` optional-fields lists; each targeted album's `published_at`/`published_at_orig_tz` is set to the one submitted instant/timezone when present. | Bulk-selecting N albums and submitting a publish date opts all N in at once, at the same absolute instant. | Same `date` validation as FR-068-13; `at least one optional field` guard already covers it (no separate change needed). | Partial-failure behavior matches this endpoint's existing per-field semantics (no special-casing). | None. | Mirrors every other `BulkAlbumPatchData` field's existing pattern. |
| FR-068-17 | `BulkEditFieldsDialog.vue` gains a new "date field" UI category — the dialog's first date-typed field (checkbox + `datetime-local` + timezone select row), added to the metadata section alongside `textFields`/`enumFields`/`sortingPairs`, visible only when `is_flow_opt_in_strategy` is true. | Bulk-editing albums shows the publish-date row only when the opt-in strategy is active; submitting it PATCHes `published_at` for every checked album. | N/A. | N/A. | None. | S-068-11. |
| FR-068-18 | Switching `flow_strategy` back to `auto` does not clear any album's stored `published_at`/`published_at_orig_tz` — `Flow.php`'s `AUTO` branch already ignores the column entirely (`orderByDesc('pc_base_album.created_at')`), so data is preserved for a future re-opt-in and for Landing Page's independent, strategy-agnostic use of the same field. | Toggling strategy back and forth never silently drops previously-set publish dates. | N/A. | N/A. | None. | Existing `Flow.php` `match` behavior, unchanged. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-068-01 | The v3 Flow listing's response size must not scale with any album's photo count — bounded by album-level fields only; each card's photo preview is a separate, capped (`limit`, FR-068-03), lazily-triggered request. Response size scaling with *total published-album count* (unpaginated, FR-068-01) is an accepted tradeoff, not a regression — the same one this codebase already accepts for root/tag/person/pinned album listings (Feature 057/062). | Flow's v2 payload embeds every album's *entire* photo collection today — the single largest identified bottleneck; album-count scaling is a pre-existing, accepted characteristic of every other unpaginated v3 album listing. | Response-size comparison, v2 vs v3, for an album with a large photo count. | FR-068-01, FR-068-03. | Subagent research finding; Decision Card Q-068-04. |
| NFR-068-02 | Zero behavior change to the v2 Flow path when `is_struct_of_array_enabled` is off. | Coexistence requirement, mirrors every prior SoA-adoption feature. | Manual/regression check of `/flow` with the flag off; diff review of v2 files (expect zero changes). | Feature-flag machinery already shipped by Feature 065. | Mirrors Feature 066's own NFR precedent. |
| NFR-068-03 | Live DOM node count for the Flow feed must stay bounded by the visible viewport ± overscan, not by total albums ever scrolled past in the session. | Unbounded DOM growth is the second largest identified bottleneck (no virtualization in `Flow.vue` today). | Manual browser check: DOM node count/memory usage after scrolling through many pages, flag on. | FR-068-05. | This feature. |
| NFR-068-04 | Below-the-fold Flow images are not requested until scrolled near viewport. | No `loading="lazy"` exists anywhere in Flow's components today. | Manual browser devtools network-tab check. | FR-068-06. | This feature. |
| NFR-068-05 | No Carbon usage introduced in any new backend code for this feature (migration, `BaseAlbumImpl` changes, request/DTO handling). | `[[feedback_avoid_carbon_server_side]]`. | Code review / grep for `Carbon`/`DateTime` imports in touched files (the existing `DateTimeWithTimezoneCast`/`UTCBasedTimes` machinery, which does use Carbon internally, is reused unmodified — this NFR applies to *new* code this feature adds). | — | Direct owner instruction (memory). |
| NFR-068-06 | Zero behavior change to Landing Page's `resolveAutomaticFeaturedItems()`/`resolveLatestAlbumCover()` ordering, or to `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s existing join. | These call sites share `published_at` with Flow but are otherwise unrelated to this feature (Feature 054). | Existing `LandingPageResource`-related test suite regression-passes unmodified. | FR-068-12. | This feature. |
| NFR-068-07 | A card's photo-preview loading skeleton must never persist indefinitely — it resolves to either real thumbnails or an empty carousel within one request round-trip, never left in a permanently-loading state on failure. | User-visible "stuck loading" states are a worse UX regression than the empty/eager-load behavior this feature replaces. | Manual check: simulate a failed/slow `ratios` request, confirm the skeleton clears. | FR-068-09. | This feature. |
| NFR-068-08 | Skeleton-to-real-content height changes for a card should minimize visible scroll jank, but — unlike Feature 066's analytically-known placeholder sizing (WASM layout fed uniform ratios) — this feature does **not** specify an exact scroll-compensation mechanism for the case where a card's real height differs from its skeleton's estimated height while partially in view. Accepted as a known, deferred risk (Follow-up), not solved by this spec. | Flow cards are text+photo content with no analytically-predictable geometry (unlike a WASM-packed photo grid), so Feature 066's exact mechanism doesn't transfer directly; inventing a new one is non-trivial and out of proportion to this feature's core ask. | Manual browser check during implementation; if jank proves visually significant, escalate to a Follow-up increment rather than block this feature on it. | FR-068-09. | Gap surfaced during review; deliberately not over-engineered per `[[feedback_no_defensive_dead_code_docs]]`'s spirit (don't build for a risk not yet confirmed real). |

## UI / Interaction Mock-ups

```
Single-album edit (AlbumProperties.vue), flow_strategy = opt-in
┌───────────────────────────────────────────────────────────┐
│ Flow publish date                                          │
│  ☑  [ 2026-09-17T10:00:00 ▾ ]  [ Europe/Paris        ▾ ]   │
│      (unchecked → field disabled, dashed border, no value) │
└───────────────────────────────────────────────────────────┘
Field is entirely absent from the form when flow_strategy = auto.

Bulk album edit (BulkEditFieldsDialog.vue), flow_strategy = opt-in
┌───────────────────────────────────────────────────────────┐
│ ☐ Flow publish date   [ 2026-09-17T10:00:00 ▾ ] [ UTC ▾ ]  │
│                        (row hidden entirely when auto)     │
└───────────────────────────────────────────────────────────┘

Flow feed (/flow), SoA flag on — all albums loaded in ONE unpaginated
request on page load (FR-068-01). No "Load more" button, no scroll-
triggered sentinel, no page cursor anywhere in this view — the full
album list is already in memory; only DOM rendering is windowed.
┌─────────────────────────────────────────────┐
│ [Album card A — real thumbnails, resolved]  │  ← live DOM node
│ [Album card B — ▓▓▓▓ skeleton, loading] │  ← live DOM node, entered
│                                               │     viewport just now,
│                                               │     ratios/Asset in flight
│ ░░░ (off-screen cards — not live DOM nodes,  │  ← derendered on scroll,
│      metadata already loaded, so scrolling   │     purely a render-
│      back into view is instant, no network) ░│     window recompute
│  images: loading="lazy", carousel capped     │
│  to first `limit` photos via v3 ratios tier  │
│  (bottom of the list is simply the end of    │
│  the already-loaded array — nothing to load) │
└─────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-068-01 | `GET /api/v3/Flow` returns every album in scope in one unpaginated response, same set/order as paging `GET /api/Flow` to exhaustion, minus nested photo objects. |
| S-068-02 | `GET /api/v3/Albums/{album_id}/Photos?limit=5` for a 50-photo album returns exactly 5 photo entries, in the album's existing effective sort order. |
| S-068-03 | `GET /api/v3/Albums/{album_id}/Photos?limit=5&bucket_ids[]=x` → 422 (mutually exclusive, mirrors Feature 066's pattern). |
| S-068-04 | `GET /api/v3/Albums/{album_id}/Photos` (whole-scope, no `limit`) returns byte-identical output to pre-feature — regression guard for Feature 064/065/066 callers. |
| S-068-05 | Flag off → `Flow.vue` uses the v2 store/service/rendering path, unchanged from pre-feature. |
| S-068-06 | Flag on → loading `/flow` fires exactly one `GET /api/v3/Flow` request regardless of album count; scrolling never re-triggers it; DOM node count stays bounded to the visible range ± overscan throughout. |
| S-068-07 | Flow images (header/carousel) do not fire network requests until scrolled near viewport, flag on or off. |
| S-068-08 | Editing an album's description while `flow_strategy=opt-in` invalidates that album's cached Markdown conversion; the next Flow fetch reflects the new description. |
| S-068-15 | A card entering the viewport for the first time renders a loading skeleton immediately, then real thumbnails once its `ratios`/`Asset` requests resolve; a card already scrolled past and back into view does not re-show the skeleton (its photos are already loaded/cached). |
| S-068-16 | A card whose photo fetch fails (simulated) resolves to an empty carousel, not a permanently-stuck skeleton. |
| S-068-09 | `flow_strategy=auto`: single-album edit form and bulk-edit dialog both omit the publish-date field entirely. |
| S-068-10 | `flow_strategy=opt-in`: single-album edit sets a publish date + timezone; the album subsequently appears in Flow, ordered by that instant. |
| S-068-11 | `flow_strategy=opt-in`: bulk-editing 3 albums with a publish date opts all 3 in at the same instant. |
| S-068-12 | Clearing an album's publish date (submitting `null`) removes it from an opt-in Flow (goes back to being excluded, per `Flow.php`'s existing `whereNotNull` gate) without affecting its `created_at`-based display fallback. |
| S-068-13 | Landing Page's "automatic featured items" / "latest album cover" ordering is unchanged before/after this feature, for a fixture with a mix of published/unpublished albums. |
| S-068-14 | Switching `flow_strategy` from `opt-in` back to `auto` and back to `opt-in` again preserves every album's previously-set publish date. |

## Test Strategy

- **Core (query/action layer):** New/extended PHPUnit coverage for `FlowListResource`, the `ratios`
  tier's new `limit` param, `BaseAlbumImpl`'s cast change (dirty-checking parity, per
  `[[project_datetimewithtimezonecast_dirty_check_bug]]`'s lesson), and the migration's backfill
  logic — scoped `--filter=` runs, per `[[feedback_no_full_test_suite]]`.
- **REST:** New `tests/Feature_v3/Flow/` suite covering S-068-01 through S-068-04; existing
  `PhotoRatiosV3Test` regression-run unmodified to prove the `limit` param is additive; new/extended
  `UpdateAlbumRequestTest`/`PatchBulkAlbumRequestTest` cases for `published_at` (S-068-10, S-068-12,
  S-068-11 — request validation/persistence only; S-068-09 is a pure frontend UI scenario, not
  REST-testable, see below); existing Landing-Page-related test coverage regression-run unmodified
  (S-068-13).
- **UI (JS):** `npm run check` (vue-tsc + eslint) for all changed/new frontend files. Scroll/DOM-bound
  virtualization, lazy-image-loading, per-card skeleton-to-real-content transitions, and the two edit
  forms' date-field UX (S-068-06, S-068-07, S-068-09, S-068-10, S-068-11, S-068-15, S-068-16) require
  manual browser verification — flagged pending if no dev environment is available in the
  authoring/implementation session, per `[[feedback_no_mariadb_mysql_access]]` and this repo's
  established precedent (Features 063/065/066/067).
- **Docs/Contracts:** `docs/specs/3-reference/api-design.md` updated for the new `GET /api/v3/Flow`
  route and the `ratios` tier's new `limit` param.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-068-01 | `App\Http\Resources\V3\FlowListResource` — album-level SoA parallel arrays. | Backend |
| DO-068-02 | `GetPhotoRatiosRequest` gains `limit(): ?int` accessor, `sometimes|integer|min:1` + mutual-exclusion validation. | Backend |
| DO-068-03 | `App\Http\Requests\Album\HasPublishedAt` contract + `HasPublishedAtTrait`, `RequestAttribute::PUBLISHED_AT_ATTRIBUTE`. | Backend |
| DO-068-04 | `BulkAlbumPatchData::$published_at: ?Carbon` (validated ISO-8601-with-offset string, coerced during `fromValidated()`). | Backend |
| DO-068-05 | `App\Http\Resources\GalleryConfigs\InitConfig::$is_flow_opt_in_strategy: bool`. | Backend |
| DO-068-06 | `FlowState.ts`-equivalent v3 store additions: `flowV3` (whole-scope SoA arrays, fetched once), per-card `requestCardPhotos(albumId, limit)` + per-card loading-state map (`"idle"\|"loading"\|"loaded"\|"failed"`) driving the skeleton (FR-068-09), photo data cached centrally by album id (FR-068-04). | Frontend |
| DO-068-07 | `AlbumProperties.vue`/`BulkEditFieldsDialog.vue` new publish-date field state (`is_published_at_modified`, date/timezone refs), mirroring `PhotoEdit.vue`'s existing `is_taken_at_modified` pattern. | Frontend |
| DO-068-08 | `FLOW_CAROUSEL_PHOTO_LIMIT = 12` — fixed frontend constant (no config key, Decision Card Q-068-07), passed as `ratios`'s `limit` param by `requestCardPhotos()`. | Frontend |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-068-01 | REST `GET /api/v3/Flow` | Unpaginated, whole-scope album-level SoA listing for the Flow feed (one request, no `page` param). | New route; gated by `is_struct_of_array_enabled`; mirrors `GET /api/v3/Albums`'s unpaginated precedent. |
| API-068-02 | REST `GET /api/v3/Albums/{album_id}/Photos?limit=N` | Capped whole-scope photo preview (existing `ratios` tier, `limit` param additive). | Extends Feature 064's route (API-064-02). |
| API-068-03 | REST `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` | Lazy per-photo thumbnail fetch for Flow cards. | Reuses Feature 056's route unmodified. |
| API-068-04 | REST `PATCH /Album` | Gains optional `published_at` field. | Extends existing v2 route, `UpdateAlbumRequest`. |
| API-068-05 | REST `PATCH /BulkAlbumEdit` | Gains optional `published_at` field. | Extends `PatchBulkAlbumRequest`/`BulkAlbumPatchData` (`Admin\BulkAlbumController::patch`, `routes/api_v2.php:289`). |

### CLI Commands / Flags

None.

### Telemetry Events

None — mirrors this app's existing "no telemetry for listing/editing features" precedent (Features 063/065/066).

### Fixtures & Sample Data

No new committed fixtures. A large-photo-count album (dozens/hundreds of photos) is useful for
manually verifying NFR-068-01's payload-size claim, mirroring prior SoA features' own precedent for
uncommitted scale fixtures.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-068-01 | Flow feed, virtualized (flag on) | All albums loaded in one request; scrolling renders/derenders cards by proximity to viewport (rendering-only), off-screen cards are not live DOM nodes. |
| UI-068-02 | Flow feed, v2 fallback (flag off) | Unchanged `Flow.vue` behavior — paginated fetch, all loaded cards remain live DOM nodes. |
| UI-068-07 | Card photo-preview loading | Card enters viewport → skeleton placeholder renders immediately, replaced by real thumbnails once `ratios`/`Asset` resolve (FR-068-09). |
| UI-068-03 | Single-album edit, opt-in strategy | Publish-date checkbox + datetime + timezone row visible and editable. |
| UI-068-04 | Single-album edit, auto strategy | Publish-date field entirely absent from the form. |
| UI-068-05 | Bulk edit dialog, opt-in strategy | Publish-date row visible in the metadata section. |
| UI-068-06 | Bulk edit dialog, auto strategy | Publish-date row entirely absent. |

## Telemetry & Observability

None — no telemetry events are introduced by this feature.

## Documentation Deliverables

- `docs/specs/3-reference/api-design.md` — document `GET /api/v3/Flow` and the `ratios` tier's new
  `limit` param.
- `docs/specs/4-architecture/knowledge-map.md` — record Flow's SoA adoption and the shared
  `published_at`/`published_at_orig_tz` pattern.
- `docs/specs/3-reference/frontend-gallery.md` — document the Flow feed's dynamically-measured
  virtualization mechanism (contrast with `PhotoGridVirtual.vue`'s analytic mode).
- `docs/specs/4-architecture/roadmap.md` — add Feature 068's entry.

## Fixtures & Sample Data

No new committed fixtures (see Fixtures & Sample Data above under the Interface Catalogue).

## Spec DSL

```
domain_objects:
  - id: DO-068-01
    name: FlowListResource
  - id: DO-068-02
    name: GetPhotoRatiosRequest (extended)
    fields:
      - name: limit
        type: integer
        constraints: "optional, min:1, prohibits bucket_ids/photo_ids"
  - id: DO-068-03
    name: HasPublishedAt contract/trait
  - id: DO-068-04
    name: BulkAlbumPatchData.published_at
  - id: DO-068-05
    name: InitConfig.is_flow_opt_in_strategy
  - id: DO-068-06
    name: Flow frontend v3 store additions
  - id: DO-068-07
    name: Publish-date field UI state (single + bulk edit)
  - id: DO-068-08
    name: FLOW_CAROUSEL_PHOTO_LIMIT frontend constant
    fields:
      - name: value
        type: integer
        constraints: "fixed at 12, no config key (Q-068-07)"
routes:
  - id: API-068-01
    method: GET
    path: /api/v3/Flow
  - id: API-068-02
    method: GET
    path: /api/v3/Albums/{album_id}/Photos
  - id: API-068-03
    method: GET
    path: /api/v3/Asset/{album_id}/{photo_id}/{size_variant}
  - id: API-068-04
    method: PATCH
    path: /Album
  - id: API-068-05
    method: PATCH
    path: /Albums (bulk)
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-068-01
    description: Flow feed, virtualized (flag on)
  - id: UI-068-02
    description: Flow feed, v2 fallback (flag off)
  - id: UI-068-03
    description: Single-album edit, opt-in strategy
  - id: UI-068-04
    description: Single-album edit, auto strategy
  - id: UI-068-05
    description: Bulk edit dialog, opt-in strategy
  - id: UI-068-06
    description: Bulk edit dialog, auto strategy
  - id: UI-068-07
    description: Card photo-preview loading skeleton
```

## Appendix

### Decision Cards

### ❓ Q-068-01 · Reuse `published_at`, or introduce a new `flow_datetime` column? ✅ RESOLVED

**Status:** Resolved — 🅰️ Option A (owner: "obviously A", 2026-09-17)
**Feature:** F-068 – Flow Struct-of-Arrays & Publish-Date Scheduling
**Preferred option:** 🅰️ (**recommended**) Option A – Reuse and extend `published_at` in place

**Question**
The request that motivated this feature assumed a new `flow_datetime`/`flow_datetime_tz` column pair
was needed. Research found `base_albums.published_at` already exists (added by
`2025_06_14_121958_add_flow_config.php`), already drives `Flow.php`'s opt-in ordering/gating, and is
**also** independently used by `LandingPageResource::resolveAutomaticFeaturedItems()`/
`resolveLatestAlbumCover()` (`orderBy('base_albums.published_at', 'DESC')`, Feature 054) and by
`AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s shared join. It currently has no timezone awareness
(plain `'datetime'` cast) and no write path (no UI, no request-validation rule anywhere). Should this
feature reuse and extend that existing column, or introduce the originally-proposed new one?

---

#### 🅰️ (**recommended**) Option A – Reuse and extend `published_at` in place

- **Idea:** Add only the missing `published_at_orig_tz` companion column (`string(31)`, nullable) and
  swap `published_at`'s cast to `DateTimeWithTimezoneCast::class` (mirrors `Photo::taken_at`'s
  existing pattern). No rename, no new column.
- **Spec impact:** FR-068-10 through FR-068-18 are written against this option as-is.
- **Pros:**
  - ✅ One single "editorial publish instant" concept, not two overlapping ones with unclear precedence.
  - ✅ Landing Page's "automatic" featured-items mode gets the same new UI-settable control for free,
    at zero extra implementation cost.
  - ✅ Smallest possible schema/behavior diff — one additive column, one cast change.
- **Cons:**
  - ❌ The field the UI exposes is literally named "publish date," not "Flow date" — a possible (minor)
    naming/mental-model mismatch with how this conversation originally framed the feature.
  - ❌ Any future feature that wants a *Flow-only* semantic (distinct from Landing Page's use) would
    need its own column later anyway.

---

#### 🅱️ Option B – New, additional `flow_datetime`/`flow_datetime_orig_tz` columns

- **Idea:** Introduce the new columns as originally proposed; migrate `Flow.php`'s opt-in
  ordering/gating to them; leave `published_at`/Landing Page untouched.
- **Spec impact:** FR-068-10 through FR-068-18 would need a full rewrite against a new column; Q-068-03's
  scope boundary (Part B is schema-additive only) would need revisiting.
- **Pros:**
  - ✅ Matches the exact wording of the original request.
  - ✅ Flow's own semantics can evolve independently of Landing Page's, if they ever should diverge.
- **Cons:**
  - ❌ Forks one concept ("when did this get published") into two overlapping columns/names on the
    same table, with no clear rule for which one wins if they're ever both set differently.
  - ❌ Landing Page keeps its own field permanently un-settable from any UI — the exact gap this
    feature exists to close, just left open for a second, unrelated feature.

---

#### 🅲 Option C – New `flow_datetime`, and migrate Landing Page's ordering to it too

- **Idea:** Introduce `flow_datetime` and also repoint `LandingPageResource`'s two ordering call
  sites and `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s join at it — a true rename in effect.
- **Spec impact:** Expands this feature's scope to include Landing Page's ordering behavior, which is
  currently a Non-Goal.
- **Pros:**
  - ✅ Ends with one column, correctly named for what it now represents.
- **Cons:**
  - ❌ Widest blast radius of the three — touches a feature (054) this spec otherwise leaves alone.
  - ❌ No part of the original request asked for Landing Page changes.

---

**Resolution:** Option A confirmed by the feature owner ("obviously A") — `published_at` is reused and
extended in place, no `flow_datetime` column is introduced. FR-068-10 through FR-068-18 stand as
written. `plan.md`'s Analysis Gate and I5 blocker, and `open-questions.md`'s Q-068-01 row, updated to
reflect this resolution; Part B implementation may now proceed.

**Q-068-02 — Full v3 SoA conversion of Flow (Part A), or a smaller fix (server-side photo cap +
lazy-loading + virtualization) without a new API shape?**

- **Context:** Concrete bottlenecks found: unbounded per-album nested photo eager-loading, no
  virtualization, no lazy image loading. The user explicitly asked "see if we can implement Struct of
  Array in it," and this repo has an established, proven SoA pattern (ADR-0009; Features 057/062/
  064/066) for exactly this class of listing.
- **Options considered:** (A) Minimal fix — cap the `photos` eager-load server-side inside
  `Flow.php`/`FlowItemResource` (e.g. `photos()->limit(N)`), add `loading="lazy"`, virtualize the
  card list — no new route, no SoA shape, smallest diff. (B) Full SoA conversion — new v3 `Flow`
  listing tier (album-level parallel arrays) + reuse of the existing v3 photo tiers (capped `limit`
  param, FR-068-03) for per-card previews, gated by `is_struct_of_array_enabled`, coexisting with v2
  exactly like Features 064/065/066.
- **Decision:** (B), matching the explicit ask and this repo's now-established architectural
  pattern — a minimal fix would leave Flow as the one remaining AoS/non-SoA listing surface,
  an inconsistency future work would have to explain. The Increment Map in `plan.md` still sequences
  the flag-independent quick wins (lazy-loading, virtualization of the *existing* v2 payload) before
  the larger SoA increments, since they deliver most of the perceived speedup immediately and
  de-risk the harder architecture work that follows — mirrors this repo's own increment-ordering
  practice of doing safe wins before harder work first.
- **Resolution date:** 2026-09-17 (decided directly for this spec, mirrors Q-066-02's precedent of a
  design choice resolved without owner round-trip).
- **Spec impact:** FR-068-01 through FR-068-09, NFR-068-01 through NFR-068-04, NFR-068-07. (Refined
  by Q-068-04, below — the album-level tier's own shape was revisited after this decision.)

**Q-068-04 — Should the v3 Flow album-level tier be paginated (mirroring v2's page-based fetch), or
loaded whole-scope in one request (each album its own "bucket equivalent"), with per-card photo
fetch + loading placeholders driven purely by scroll position?**

- **Context:** Raised directly by the feature owner after Q-068-02/FR-068-01's first draft, which
  carried over v2's `LengthAwarePaginator`/page-based fetch for the album-level tier out of habit.
  The owner asked: could each album instead be treated like a bucket — load every published album's
  metadata up front (cheap, scalar fields only), virtual-scroll over the resulting list, and fetch a
  given card's photos only once it scrolls into view, showing a placeholder meanwhile?
- **Options considered:** (A) Keep v2's page-based fetch for the album-level tier too (original
  FR-068-01 draft) — an intersection-observer sentinel triggers `GET /api/v3/Flow?page=N`, exactly
  like `Flow.vue` does today. (B) Load the entire album-level listing in one unpaginated request
  (mirrors `GET /api/v3/Albums`'s existing root/tag/person/pinned precedent, Feature 057/062 — not
  Timeline's aggregated-`buckets`-tier precedent, since here each row is already one whole album, not
  a reduced count), with the virtualizer doing rendering-only windowing and each card's photo fetch
  (already windowed per FR-068-03/04) gaining an explicit loading-skeleton state.
- **Decision:** (B). It is a strictly better fit than the original (A) draft: `GET /api/v3/Albums`
  already proves this codebase treats whole-album-listing tiers as unpaginated-by-default (only the
  *photo* tiers inside an album get windowed, per Feature 064/066's own precedent) — carrying v2's
  pagination into the v3 tier was an unexamined holdover, not a deliberate choice. (B) also simplifies
  the frontend materially: no page cursor to track, no "load more" trigger for album metadata, no
  re-fetch-on-scroll-back-up gap — only the per-card photo fetch and its skeleton state are
  scroll-driven, exactly matching FR-068-03/04's design, which needed no change.
- **Accepted tradeoff:** response size for the album-level tier now scales with total published-album
  count, not a bounded page. This is the same tradeoff Feature 057/062 already accept for root/tag/
  person/pinned listings (see NFR-068-01) — not a new risk class introduced here.
- **Resolution date:** 2026-09-17 (feature owner: "Should it be paginated? Couldn't we make each album
  a bucket equivalent? ... load all the published albums with virtual scroll and then when an album is
  in view we load the photos? Using place holders while the photos are loading?").
- **Spec impact:** FR-068-01 (rewritten, unpaginated), FR-068-05 (clarified: rendering-only
  windowing), new FR-068-09 (loading-skeleton requirement), new NFR-068-07 (skeleton must resolve, not
  hang), NFR-068-01 (clarified: album-count scaling accepted), new scenarios S-068-15/16.

**Q-068-05 — Should the publish-date UI field's visibility (FR-068-14/15/17) be broadened beyond
`flow_strategy=opt-in`, given Q-068-01's justification that Landing Page benefits too?**

- **Context:** Raised during full-feature review: `is_flow_opt_in_strategy` (FR-068-14) gates the
  field on `flow_strategy=opt-in`, but Landing Page's `resolveAutomaticFeaturedItems()` (Feature 054)
  uses the same `published_at` column regardless of Flow's strategy or even whether Flow is enabled —
  and `flow_strategy` defaults to `auto`. As drafted, a site using only Landing Page's automatic mode
  has no UI path to set this field.
- **Options considered:** (A) Keep opt-in-only gating, as drafted. (B) Broaden to `flow_enabled` (any
  strategy). (C) Broaden further to `flow_enabled OR` Landing Page's automatic-featured-items mode
  active.
- **Decision:** (A), confirmed by the feature owner. The field stays tied to `flow_strategy=opt-in`
  exactly as drafted; Landing-Page-only sites remain without a UI path to set `published_at` (direct
  API/DB access only). This keeps the feature tightly scoped to what was actually asked for — the
  "free benefit" to Landing Page from Q-068-01 is real (the column becomes timezone-aware and
  API-settable) but is not fully realized as a *UI-accessible* benefit for a Landing-Page-only site.
  Logged here so this limitation is a documented, deliberate choice, not an oversight.
- **Resolution date:** 2026-09-17 (feature owner selected the recommended option).
- **Spec impact:** FR-068-14 stands as drafted; no FR changes. This limitation is a candidate
  Follow-up if ever requested.

**Q-068-06 — What happens to v2's `flow_max_items` config now that the v3 tier is unpaginated
(Q-068-04)?**

- **Context:** `flow_max_items` exists specifically to bound v2's page size for the exact scale
  concern Q-068-04's unpaginated design reopens. Left unaddressed in the original draft.
- **Options considered:** (A) Ignore it entirely in v3 — always return every album in scope;
  `flow_max_items` becomes v2-only (still respected by the untouched v2 path). (B) Reuse it as a hard
  `->limit()` safety cap on the v3 response, silently truncating above it. (C) A new, separate hard
  ceiling distinct from `flow_max_items` (mirrors Feature 064's `photo_ids[]` 300-cap pattern,
  422-above-N rather than silent truncation).
- **Decision:** (A), confirmed by the feature owner. `flow_max_items` remains v2-only; v3 has no
  album-count cap, matching `GET /api/v3/Albums`'s own precedent (no equivalent cap exists there
  either). This is consistent with NFR-068-01's already-accepted tradeoff (response size scales with
  published-album count) — adding a cap now would be solving a problem not yet confirmed real, for a
  scale this codebase already accepts elsewhere.
- **Resolution date:** 2026-09-17 (feature owner selected the recommended option).
- **Spec impact:** FR-068-01 clarified: v3 ignores `flow_max_items`, no param, no cap. A future hard
  ceiling (Option C) remains a candidate Follow-up if pathological-scale installs ever report it as a
  real problem.

**Q-068-07 — What value/mechanism should FR-068-03's per-card `limit` param use?**

- **Context:** FR-068-03 introduced a `limit` param for each card's photo preview but left the actual
  number as an unpinned placeholder ("e.g. `limit=12`"). v2 today shows *every* photo in an album's
  carousel — capping it is a genuine, user-visible behavior change, not just a backend optimization.
- **Options considered:** (A) A fixed constant (e.g. 12), same for every install, no new config. (B) A
  new config key (mirrors `flow_carousel_height`/`flow_image_header_height`'s existing pattern),
  admin-adjustable. (C) No cap — fetch every photo, just via the lazy v3 `ratios` call instead of
  eager v2 embedding (preserves current UX exactly, but reintroduces per-album-photo-count scaling
  into NFR-068-01 for large albums, partially undoing this feature's own performance goal).
- **Decision:** (A), confirmed by the feature owner. A fixed constant, `FLOW_CAROUSEL_PHOTO_LIMIT =
  12`, defined once on the frontend (the backend `ratios` endpoint just accepts whatever `limit` value
  it's given — no backend constant needed). No new config key. The album's full `num_photos` count
  (already in FR-068-01's payload) remains visible on the card; opening the album itself still shows
  every photo via the existing, unaffected per-album photo-listing path (Feature 064/065).
- **Resolution date:** 2026-09-17 (feature owner selected the recommended option).
- **Spec impact:** FR-068-03/FR-068-04 pinned to the concrete value 12; new DO for the frontend
  constant. A configurable cap (Option B) remains a candidate Follow-up if ever requested.

**Q-068-03 — One combined feature doc (Part A + Part B), or split into two features?**

- **Context:** Part A (performance/SoA) and Part B (publish-date scheduling) are independently
  implementable — neither depends on the other. Prior precedent in this repo splits docs when
  backend/frontend halves ship at genuinely different times (061/063, 064/065), and combines them
  when scoped together as one effort (066).
- **Decision:** One combined doc (this one), because the user asked for "feature 68" covering the
  whole conversation in a single request. FR/NFR IDs are grouped into two clearly separated ranges
  (Part A: 01–08/01–04; Part B: 10–18/05–06) precisely so this can be split into two features later
  (e.g. if the owner wants to ship Part B — a small, self-contained schema+UI change — well ahead of
  Part A's larger SoA work) without renumbering. The Increment Map in `plan.md` orders Part B's
  increments first for exactly this reason: it is the smaller, more concretely-scoped half and can
  ship independently while Part A's SoA work proceeds.
- **Resolution date:** 2026-09-17.
- **Spec impact:** Doc structure only; no FR/NFR impact.
