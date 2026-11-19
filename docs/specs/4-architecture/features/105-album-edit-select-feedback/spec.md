# Feature 105 – Album Edit Select Feedback

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-01 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #105 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
The v8 album edit drawer (`resources/js/v8/components/forms/album/AlbumProperties.vue`) holds twelve `USelectMenu` fields. A stored `null` means "use the gallery setting" (photo and album sort column and order, aspect ratio, photo layout, album and photo timeline, date scrubber) or "automatic" (header: random photo, cover: computed cover). Every field with a `null` value shows its stored state through an explicit entry or a placeholder, so the drawer opens with a value in each trigger and each dropdown opens with the applicable entry checked. `PATCH /api/v2/Album` stores every album sort column the validation accepts, `min_taken_at` and `max_taken_at` included.

## Goals
- Every select of the v8 album edit drawer shows which value applies when the drawer opens and checks the matching entry when its dropdown opens.
- A field can be set back to the gallery setting from the drawer.
- Album sort columns `min_taken_at` and `max_taken_at` are stored and returned by `PATCH /api/v2/Album`.
- Header and cover entries show their photo's thumbnail, struct-of-arrays photos included.
- "Set as header" and the webshop buy action see the selected photo's size variants, struct-of-arrays photos included.
- The v8 bulk album edit table and fields dialog show "Default" for fields following the gallery setting and never store a sort column without its order.

## Non-Goals
- N-105-01: v7 album properties (PrimeVue).
- N-105-02: v7 bulk album edit; the shared option lists in `resources/js/config/constants.ts` keep their labels.
- N-105-03: Visual restyling of `USelectMenu` (check icon, highlight colours).
- N-105-04: A header photo outside the loaded page of photos (`buildHeaderId()` returns no option for it).
- N-105-05: Saving an album that stores an SE timeline granularity on an instance without SE (422 from `EnumRequireSupportRule`).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-105-01 | `UpdateAlbumRequest` builds the album sorting criterion from `ColumnSortingAlbumType`. | `album_sorting_column` `created_at`, `title`, `min_taken_at` or `max_taken_at` with an order is stored in `albums.album_sorting_col`/`album_sorting_order` and returned in `editable.album_sorting`. | `Enum(ColumnSortingAlbumType)`; order `required_with` column (422). | – | – | Bug found while reproducing the owner report. |
| FR-105-02 | Photo sort column, album sort column, aspect ratio and photo layout lists start with a "Default" entry whose value is `null`. | `null` shows "Default" in the trigger and checks the "Default" entry; picking "Default" saves `null`. | – | – | – | Q-105-01 Option A. |
| FR-105-03 | Each sort order follows its column. | Column "Default": the order shows "Default", is disabled, saves `null`. Picking a column while the order is "Default" sets the order to Ascending. Picking "Default" as column sets the order to "Default". The order list has no "Default" entry. | The pair sent to the API is either both `null` or both set, so `required_with` never answers 422 from the drawer. | – | – | Q-105-01 Option A. |
| FR-105-04 | Header and cover show "Automatic" as placeholder when unset. | `header_id`/`cover_id` `null`: the trigger shows "Automatic" in placeholder style; clearing the cover shows "Automatic". | – | – | – | Q-105-01 Option A. |
| FR-105-05 | Tag and person albums get FR-105-02/03 and FR-105-06 for the fields they show (photo sort column and order, photo layout, photo timeline). | Same as FR-105-02/03/06. | – | – | – | Q-105-01 Option A. |
| FR-105-06 | The album and photo timeline selects use drawer-local lists: "Default" (`null`) first, the `default` value labelled "Enabled", then `disabled` and the SE granularities. | `null` checks "Default" and saves `null`; a stored `default` checks "Enabled" and saves `default`. Without SE the list is "Default", "Enabled", "disabled". A stored value missing from the list (SE granularity without SE) shows its label in the trigger with no entry checked and is sent unchanged; the API refuses it without SE (`EnumRequireSupportRule`, 422). | – | – | – | Q-105-02 Option A. `null` follows `timeline_*_enabled`, `default` turns the timeline on (`AlbumConfig`). |
| FR-105-08 | Header and cover entries render their photo through `Thumb` (`type="thumb"`). | A photo with a loaded `size_variants.thumb` URL shows it directly; otherwise the entry takes the direct URL from the tile URL store (Feature 102), asked for every photo of the lists when the dropdown opens; without a source, with a protected viewer or on a URL that fails to load, `Thumb` loads `/Asset`. | – | Placeholder icon while the URL is pending or when `/Asset` fails. | – | Owner report 2026-11-01: struct-of-arrays photos carry no `size_variants` until their details load, so the entry rendered `<img>` without `src`. |
| FR-105-09 | "Set as header" (album selection menu, `AlbumPanel.vue`) and the webshop buy action (`buyMeActions.ts` `toggleBuyMe()`) load the selected photo's details first when it is detail-pending (`isDetailPending()`). | The hero shows the chosen photo (`medium`, else `small` URL); the `medium`/`medium2x` prices of a photo that has them are offered. A photo with loaded details (v7, struct-of-arrays off, details already merged) triggers no request. | – | A failed details request leaves the variants empty: the header URL is `null` and the `medium`/`medium2x` prices are dropped. | – | Q-105-03 Option A. |
| FR-105-10 | v8 bulk album edit sort and enum selects. | Table sort-column cells: list starts with "Default" (`null`), a `null` column reads "Default" and checks it; picking a column patches column and order together (`sortingOrderValueFor`: "Default" → both `null`, no stored order → `ASC`, else the stored order). Fields dialog: photo layout, aspect ratio and both sort columns start with "Default", the timelines use the drawer lists (FR-105-06), a ticked field left on "Default" sends `null`; orders start at Ascending; a ticked column is always sent with its order ("Default" → `null`), the order select is then disabled and its checkbox shown ticked; an order ticked alone is sent alone. | `PatchBulkAlbumRequest` unchanged. | Patch error: the cell reverts both values and shows the existing error toast. | – | Q-105-03 Option A. |
| FR-105-07 | New labels `gallery.album.properties.default` ("Default"), `gallery.album.properties.automatic` ("Automatic"), `gallery.album.properties.timeline_enabled` ("Enabled") in all 23 locales. | Translated in each locale. | `LangTest`, `scripts/check_translations.php`. | – | – | AGENTS.md lang rule. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-105-01 | No new dependency. | AGENTS.md dependency rule. | `package.json`/`composer.json` unchanged. | – | AGENTS.md |
| NFR-105-02 | The null mapping and the order coupling live in pure helpers with Vitest coverage. | Straight-line increments. | `vp test` covers `albumSelectOptions.ts`. | Vitest via Vite+. | AGENTS.md |

## UI / Interaction Mock-ups (required for UI-facing work)

Album with no customised field, expert mode, SE off:

```
Order photos by              asc/desc
[ Default              v ]   [ Default              v ]   <- order disabled
Order albums by              asc/desc
[ Default              v ]   [ Default              v ]   <- order disabled
Set album header             Set album cover
[ Automatic            v ]   [ Automatic            v ]   <- placeholder style
Set album thumbs aspect ratio Set photo layout
[ Default              v ]   [ Default              v ]
Set album timeline mode      Set photo timeline mode
[ Default              v ]   [ Default              v ]
Date scrubber
[ Default              v ]
```

Photo sort column opened, then "Title" picked:

```
[ Default              v ]          [ Title                v ]   [ Ascending            v ]
+------------------------+    ->     order set to Ascending, enabled
| Default              ✓ |
| Upload time            |
| Title                  |
| ...                    |
+------------------------+
```

Photo timeline opened (SE off), album stores `default`:

```
+------------------------+
| Default                |
| Enabled              ✓ |
| disabled               |
+------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-105-01 | PATCH an album with `album_sorting_column = max_taken_at`, order `DESC`: response `album_sorting` is `max_taken_at`/`DESC`, the row holds both. |
| S-105-02 | Same with `min_taken_at`/`ASC`. |
| S-105-03 | PATCH with album sort column `null` and order `null`: stored and returned as `null`. |
| S-105-04 | Album with every field `null`: each sort column, aspect ratio, layout and timeline trigger reads "Default" and its dropdown checks "Default"; header and cover read "Automatic"; both orders read "Default" and are disabled. |
| S-105-05 | Picking a photo sort column with order "Default": order becomes Ascending and is enabled; one save stores both. |
| S-105-06 | Picking "Default" as sort column: order becomes "Default", disabled; save stores both `null`. |
| S-105-07 | Album storing photo timeline `default`: trigger reads "Enabled", the "Enabled" entry is checked; editing the title saves `default` unchanged. |
| S-105-08 | Album storing values for every field: each trigger shows its value and the dropdown checks it (unchanged behaviour). |
| S-105-10 | Struct-of-arrays album, header dropdown opened: every photo entry shows its thumbnail (image loaded, not the placeholder); the cover dropdown likewise. |
| S-105-11 | Photo with a loaded `size_variants.thumb` URL: the entry uses that URL, no tile URL needed. |
| S-105-12 | Struct-of-arrays album, a photo with no details loaded, "Set as header" from the selection menu: the hero shows that photo. |
| S-105-13 | Buy action on a detail-pending photo whose details carry `medium`: details load first, the `medium` price is offered. |
| S-105-14 | Bulk table, album with no photo sort: the cell reads "Default" and the opened select checks it; picking "Title" stores `title`/`ASC`; picking "Default" stores `null`/`null`. |
| S-105-15 | Bulk dialog: photo sort column ticked on "Title" with the order untouched sends `title`/`ASC`; column ticked on "Default" sends `null`/`null`; order ticked alone on Descending sends the order only. |
| S-105-16 | Bulk dialog lists: layout, aspect ratio and sort columns start with "Default"; timelines read Default, Enabled, disabled, then granularities. |
| S-105-09 | Stored timeline granularity absent from the list (no SE): trigger shows its label, no entry checked; the drawer sends it unchanged (the API answers 422 without SE, N-105-05). |

## Test Strategy
- **REST API (v2/v3):** `Tests\Feature_v3\Album\AlbumSortingColumnTest` for S-105-01 … 03 (red before FR-105-01).
- **Frontend (v8):** `resources/js/v8/utils/albumSelectOptions.test.ts` (Vitest) for the null mapping, the timeline list, the order coupling (`sortingOrderFor`, `sortingOrderValueFor`) and the entry thumbnail source (S-105-04 … 07, 09, 11, 14, 15 at helper level); `resources/js/composables/album/buyMeActions.test.ts` for S-105-13; Playwright on the scratch instance for S-105-04 … 08, 10, 12, 14 … 16.
- **Lang:** `LangTest`, `scripts/check_translations.php`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-105-01 | `SelectOption<T \| null>` "Default" entry (`value: null`), drawer-local timeline lists, entry thumbnail source (`optionThumbProps`). | `resources/js/v8/utils/albumSelectOptions.ts` |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-105-01 | REST PATCH /api/v2/Album | Album sorting column parsed as `ColumnSortingAlbumType`. | `UpdateAlbumRequest::processValidatedValues()` |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-105-01 | Field inherits the gallery setting | Trigger "Default", "Default" entry checked. |
| UI-105-02 | Sort column "Default" | Order shows "Default", disabled. |
| UI-105-03 | Header/cover unset | Placeholder "Automatic". |
| UI-105-04 | Timeline stores `default` | Trigger "Enabled", entry checked. |
| UI-105-05 | Header/cover dropdown open | Each photo entry shows its thumbnail; placeholder icon while pending. |
| UI-105-06 | Bulk table sort cell of an album following the gallery setting | Reads "Default"; opened select checks "Default". |
| UI-105-07 | Bulk dialog sort column ticked | Order checkbox shown ticked; order disabled and "Default" for a "Default" column. |

## Documentation Deliverables
- Roadmap row 105, knowledge-map entry for `albumSelectOptions.ts`.

## Spec DSL

```
domain_objects:
  - id: DO-105-01
    name: albumSelectOptions
    module: resources/js/v8/utils/albumSelectOptions.ts
routes:
  - id: API-105-01
    method: PATCH
    path: /api/v2/Album
ui_states:
  - id: UI-105-01
    description: field inherits the gallery setting, "Default" shown and checked
  - id: UI-105-02
    description: sort column Default, order Default and disabled
  - id: UI-105-03
    description: header or cover unset, placeholder Automatic
  - id: UI-105-04
    description: timeline stores default, shown as Enabled
  - id: UI-105-05
    description: header and cover entries show their photo thumbnail
  - id: UI-105-06
    description: bulk table sort cell reads Default for a null column
  - id: UI-105-07
    description: bulk dialog sort column ticked, order follows
```
