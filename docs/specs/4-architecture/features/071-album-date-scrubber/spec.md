# Feature 071 – Album Date Scrubber

| Field | Value |
|-------|-------|
| Status | Implemented 2026-09-24 — manual browser verification pending (T-071-19) |
| Last updated | 2026-09-24 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/071-album-date-scrubber/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/071-album-date-scrubber/tasks.md` |
| Roadmap entry | #071 |
| Decisions | [ADR-0011](../../../6-decisions/ADR-0011-date-scrubber-ticks-derived-client-side.md) (Q-071-05) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

## Overview
Feature 066 (T-067) gave the v8 Timeline view a right-hand date scrubber rail (`TimelineDatesV3.vue`). The rail shows year labels and month ticks at rest, a playhead that follows scrolling, a fisheye lens and readout pill on hover, and continuous drag-scrubbing. This feature brings the same rail to **v8 album views** on the Struct-of-Arrays path (`is_struct_of_array_enabled`, `albumStore.isPhotoSoaActive`). It is controlled by a global gallery setting (default **on**), a per-album override, and a viewer show/hide toggle. The rail is offered only on albums whose visible content is a single kind (photos only, or sub-albums only) and whose ordering is date-based.

Per `[[project_v8_migration_scope]]` this is v8-only. v7 and the v2 (non-SoA, paginated) album path are untouched. Per ADR-0011, bucket storage is untouched: the rail's day-level entries are derived in the browser from per-tile dates the album view already loads.

## Goals
- G1: Album views show the Timeline's scrubber rail, with the same visuals and interactions, by reusing `TimelineDatesV3.vue`.
- G2: A global gallery setting, default on (Q-071-03 → B).
- G3: Eligibility: the visible content is exactly one kind (Q-071-01 → A), and its ordering is a date column, or a title sort in `DATE_PREFIX` bucket mode (Q-071-04 → A, extended by the owner).
- G4: Three settings layers: global → per-album override → viewer toggle (Q-071-02 → A).
- G5: Day-precision rail regardless of the album's header granularity, with no change to bucket storage (Q-071-05 → A, ADR-0011).

## Non-Goals
- NG1: v7 frontend and the v2 (non-SoA) album path.
- NG2: The Timeline view. `/timeline`'s rail must behave exactly as today. The only allowed change to `TimelineDatesV3.vue` is the additive, defaulted prop in FR-071-09.
- NG3: Root album listing (`/gallery`), Search, Flow, Map, Tag, Favourites views.
- NG4: Changing how `photo_album.bucket_id` / `base_albums.bucket_id` are stored or computed. A persisted "always smallest granularity" refactor is deferred to its own feature (ADR-0011).
- NG5: Albums that show both sub-albums and photos (Q-071-01 → A). They never get a rail.
- NG6: Non-date orderings other than title `DATE_PREFIX`: title `ALPHABETICAL`, `rating_avg`, `type`, `is_highlighted`, `owner_id`.
- NG7: Bulk album edit (`PatchBulkAlbumRequest`) and the per-album override on smart albums, which have no `base_albums` row.
- NG8: A frontend unit-test runner. None exists (`npm run check` is `vue-tsc` only), and adding one needs dependency approval.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-071-01 | New global config `album_date_scrubber_enabled` (boolean, `Gallery` category, default `1`), editable in admin Settings, with a lang label and description. | Admin toggles it; `AlbumConfig.is_date_scrubber_enabled` follows it for every album without an override. | Standard boolean config validation. | — | None. | Q-071-03 → B. |
| FR-071-02 | New nullable boolean column `base_albums.is_date_scrubber_enabled` (default `NULL`). `NULL` means follow the global setting; `true`/`false` forces it on or off. Editable in the album properties drawer (`AlbumProperties.vue`) as a three-way select (Default / Enabled / Disabled) for every album type whose update request already carries `photo_timeline` (regular, tag and person albums). | The value round-trips through `UpdateAlbumRequest` / `UpdateTagAlbumRequest` / `UpdatePersonAlbumRequest` and the album edit resource. | The request field is `sometimes|nullable|boolean`; a non-boolean → 422. **Omitting the field leaves the stored override unchanged**, the same contract Feature 068 set for `published_at` on this request. The v7 frontend shares these endpoints and never sends the field, so a required field would break v7 album editing, and sending `null` would wipe an override set from v8. | — | None. | Q-071-02 → A (layer 2). |
| FR-071-03 | `AlbumConfig` exposes `is_date_scrubber_enabled: bool` = the album's override if not null, otherwise the global value. Smart albums always use the global value. | — | — | — | None. | Q-071-02 → A. |
| FR-071-04 | `AlbumConfig` exposes `photo_date_scrubber_field: "taken_at" \| "created_at" \| "title" \| null`, the field photo tiles are dated by. It is the effective photo sort column (album override, else global default) when that is `taken_at` or `created_at`; `"title"` when the sort is `title` and `photo_title_bucket_mode = date_prefix`; otherwise `null` (not eligible). | Date-ordered photo grids get a non-null field. | — | Any other column → `null`. | None. | Q-071-04 → A + owner extension. |
| FR-071-05 | `AlbumConfig` exposes `album_date_scrubber_field: "created_at" \| "min_taken_at" \| "max_taken_at" \| "title" \| null` for model albums: the effective child-album sort column when it is one of the three date columns; `"title"` when the sort is `title` and `title_bucket_mode = date_prefix`; otherwise `null`. Always `null` for non-model albums (they have no sub-albums). | — | — | Any other column → `null`. | None. | Q-071-04 → A + owner extension. |
| FR-071-06 | Rail source selection is a pure client helper, `resolveDateScrubberSource()` → `"photos" \| "albums" \| null`. It returns `null` when: the SoA path is inactive; `is_date_scrubber_enabled` is false; either listing has not resolved yet; both visible counts are > 0; or the non-empty kind's `*_date_scrubber_field` is null. "Visible count" is the length of the resolved tier-2 listing (`albumsStore.albums` / `photosStore.photos`, each set in the same response handler as its bucket tier), so what the viewer is allowed to see decides it, not the stored `num_children` / `num_photos`, and it stays correct when a tier is `bucketable: false` (empty `counts`). Smart albums never load a children listing; their album count is 0. | Photos-only, date-ordered → `"photos"`; albums-only, date-ordered → `"albums"`. | — | Anything else → `null`: no rail and no toggle, and the layout is identical to today. | None. | Q-071-01 → A. |
| FR-071-07 | Viewer toggle: a header button (`AlbumHeader.vue`), shown only when the source (FR-071-06) is not null. It hides or shows the rail and is remembered per browser under `localStorage` key `lychee.album_date_scrubber_hidden`. The preference is global across albums. Every storage read and write is wrapped in try/catch; if storage is unavailable, the rail is shown. | Click hides the rail; click again shows it. The preference survives a reload. | — | Storage throws → the rail is shown and the toggle works for the session only. | None. | Q-071-02 → A (layer 3). |
| FR-071-08 | Day-level rail entries (ADR-0011). The active grid (`PhotoGridVirtual.vue` for `"photos"`, `AlbumThumbGridVirtual.vue` for `"albums"`) derives, from each tile's date and real pixel row position, an ordered list of entries `{bucketId, label, count, top, height}` plus `totalHeight`. Each entry covers one consecutive run of tiles sharing the same day key `YYYY-MM-DD`. **Tile date:** photos use `taken_ats` (sort `taken_at`) or `created_ats` (sort `created_at`); albums use `created_ats`, `min_taken_ats` or `max_taken_ats` to match the sort column. Title `DATE_PREFIX` sorts parse the title's leading date with the same regex as `TimelineData::parseDateFromTitle()` (`^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?`, missing month/day → `01`). The day key uses the same raw-value truncation the server's bucketing uses, so rail days agree with header buckets. A tile with no date (null `taken_at`, or no title prefix) contributes to no entry. A day key recurring in a later, non-consecutive run gets a unique `bucketId` suffix (`YYYY-MM-DD#n`), so ids stay unique; the year/month tick parsing (`split("-")`) is unaffected. **Label:** formatted with `phpDateFormat.ts` using the existing `timeline_photo_date_format_day` config (default `j M Y`, the format the Timeline rail already shows for day buckets; the year matters in the lens across year boundaries), for both photo and album grids, exposed as `AlbumConfig.date_scrubber_label_format`. | Entries are fed to `TimelineDatesV3.vue` as a synthesized `{bucket_ids, labels, counts}` plus `bucketLayout` / `totalHeight`. | — | No dated tile → no entries → the source is treated as `null` (no rail). | None. | Q-071-05 → A, ADR-0011. |
| FR-071-09 | `TimelineDatesV3.vue` is reused. The one change is an additive optional prop, `countLabelKey` (default `"gallery.timeline.photos_count"`), for the readout pill's count phrase. The album grid passes a sub-album count key (new lang key `gallery.album.date_scrubber.albums_count`). | With the prop omitted, the Timeline renders exactly as before. | — | — | None. | NG2. |
| FR-071-10 | Rail interactions on albums: drag-scrub calls the grid's `scrollToPixelOffset()`. Tap / drag-release (`load`) scrolls to the entry's `top`, in place, with **no route push** (album URLs have no per-date segment). Rail position 0 is the grid's top; content above the grid (hero, header image) is not on the rail. | — | — | — | None. | Derived. |
| FR-071-11 | Placement: a flex sibling on the right of the album content column in `AlbumPanel.vue`, mirroring `Timeline.vue`, and hidden while a photo is open in the lightbox (as on `/timeline`). | — | — | — | None. | Parity with Feature 066. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-071-01 | No new HTTP request. Everything comes from `AlbumConfig` and the tiers the album view already loads. | Album load latency. | Network panel: request count unchanged, rail on vs off. | Features 061/064. | ADR-0011. |
| NFR-071-02 | `AlbumConfig` eligibility adds no query: the sort columns and title bucket modes come from the already-loaded album and config. | Album head latency. | Query count in the Feature_v2 test is unchanged vs baseline. | — | Derived. |
| NFR-071-03 | Entry derivation is O(n) in visible tiles, runs only when the grid layout changes (not per scroll tick), and adds no per-scroll work beyond what the Timeline rail already does. | Large albums (10k+ photos). | Manual: smooth scroll and scrub on a large album. | — | Derived. |
| NFR-071-04 | Works fully offline. | `[[feedback_offline_only]]` | — | — | Standing owner rule. |
| NFR-071-05 | Correct in LTR/RTL and light/dark (inherited from `TimelineDatesV3.vue`). | Parity. | Manual check. | — | Feature 066. |

## UI / Interaction Mock-ups

Photos-only, date-sorted album, rail visible (resting state):

```
┌─ AlbumHeader ──────────────────────────────────────────── [📅] [⋯] ───┬──────┐
├──────────┬────────────────────────────────────────────────────────────┤      │
│ AlbumNav │  AlbumHero (title, description, actions)                   │ 2026 │
│ Panel    │ ─────────────────────────────────────────────────────────  │  ·   │
│          │  ▌2026                                                     │  ·   │
│          │  ┌────┐┌──────┐┌────┐┌────┐┌───────┐                       │━━━━━━│◄ playhead
│          │  │    ││      ││    ││    ││       │                       │  ·   │
│          │  └────┘└──────┘└────┘└────┘└───────┘                       │ 2025 │
│          │  ▌2025                                                     │  ·   │
│          │  ┌──────┐┌────┐┌────┐┌──────┐                              │  ·   │
│          │  └──────┘└────┘└────┘└──────┘                              │ 2024 │
└──────────┴────────────────────────────────────────────────────────────┴──────┘
   [📅] = viewer toggle (FR-071-07)                        w-16 sticky rail
```

Section headers follow the stored granularity (here `YEAR`). The rail's lens still magnifies individual days (FR-071-08):

```
                                  ┌──────────────────┐ ┌────────────────┐
                                  │ 12 Mar 2026  42  │ │   09 Mar 2026  │
                                  └──────────────────┘ │ ━ 12 Mar 2026 ━│
                                                       │   15 Mar 2026  │
                                                       └────────────────┘
```

Album properties drawer, new field:

```
  Photo timeline      [ Default            ▾ ]
  Date scrubber       [ Default (Enabled)  ▾ ]   ← Default / Enabled / Disabled
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-071-01 | Global on, override null, photos-only, sorted by `taken_at` → rail shown. |
| S-071-02 | Global off, override `true` → rail shown. Global on, override `false` → no rail, no toggle. |
| S-071-03 | Album has both visible sub-albums and photos → no rail. |
| S-071-04 | Album has hidden sub-albums the viewer cannot see, plus photos → visible albums count is 0 → rail shown on photos. |
| S-071-05 | Albums-only, sorted by `max_taken_at` → rail on the album grid; the pill uses the albums count phrase. |
| S-071-06 | Photos-only, sorted by `title` with `photo_title_bucket_mode = date_prefix` → rail built from parsed title dates; undated titles are skipped. |
| S-071-07 | Photos-only, sorted by `title` with `alphabetical` mode, or by `rating_avg` / `type` / `is_highlighted` → no rail. |
| S-071-08 | Viewer hides the rail → it stays hidden after a reload and on other albums; with storage unavailable, it is shown. |
| S-071-09 | Drag → continuous scrub; release/tap → scrolls to the day's top; URL unchanged. |
| S-071-10 | Smart album (e.g. Recent) → follows the global value only, with photos eligibility per its sort column. |
| S-071-11 | v2 / non-SoA path → no rail. `/timeline` → unchanged (NG2). |
| S-071-12 | Photos-only, sorted by `taken_at`, where every photo lacks `taken_at` → no entries → no rail. |

## Test Strategy
- **Backend, Feature_v2** (`BaseApiWithDataTest`):
  - `AlbumConfigDateScrubberTest`: FR-071-03/04/05 across global on/off × override null/true/false; each photo sort column including both title modes; each album sort column including both title modes; smart album; tag album. Covers S-071-01/02/06/07/10.
  - `UpdateAlbumDateScrubberTest`: round-trip for album, tag album and person album (null/true/false), 422 on a non-boolean, and omitting the field leaves the override unchanged.
- **Static:** `make phpstan`, `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check` (vue-tsc).
- **Frontend helpers** (`resolveDateScrubberSource`, day-entry derivation, title-prefix parser) are pure and typed. With no JS test runner (NG8), each helper's branch table goes in the plan and is verified manually through S-071-03…12.
- **Manual browser check:** S-071-01…12, including LTR/RTL, light/dark, and a large album.

## Interface & Contract Catalogue

| Surface | Change |
|---------|--------|
| `configs` | + `album_date_scrubber_enabled` (bool, `Gallery`, default `1`) |
| `base_albums` | + `is_date_scrubber_enabled` nullable boolean, default `NULL` |
| `UpdateAlbumRequest`, `UpdateTagAlbumRequest`, `UpdatePersonAlbumRequest` | + optional `is_date_scrubber_enabled: ?bool` (omitted = unchanged) |
| Album edit resource (properties drawer source) | + `is_date_scrubber_enabled: ?bool` |
| `AlbumConfig` | + `is_date_scrubber_enabled: bool`, `photo_date_scrubber_field: ?string`, `album_date_scrubber_field: ?string`, `date_scrubber_label_format: string` |
| `TimelineDatesV3.vue` | + optional prop `countLabelKey` (default preserves current behaviour) |
| `PhotoGridVirtual.vue`, `AlbumThumbGridVirtual.vue` | emit `scrubberLayoutChanged` / `scrollOffsetChanged` and expose `scrollToPixelOffset()` in album mode (Timeline-mode events unchanged) |
| `localStorage` | `lychee.album_date_scrubber_hidden` (`"1"` = hidden) |
| lang (`lang/<locale>/*.php`, then `php artisan lang:json`) | setting label/description, properties select label/options, toggle tooltip, `gallery.album.date_scrubber.albums_count` |

## Telemetry & Observability
None.

## Documentation Deliverables
- Knowledge-map entry: `TimelineDatesV3.vue` now has two consumers (Timeline, AlbumPanel).
- Roadmap progress; ADR-0011.
