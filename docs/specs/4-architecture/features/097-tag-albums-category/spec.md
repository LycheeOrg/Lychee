# Feature 097 – Tag Albums Category

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-10-17 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #097 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
On the root gallery, tag albums and person albums are shown inside the Smart Albums panel: the frontend store `AlbumsState.smartAlbums` concatenates built-in smart albums, tag albums and person albums. Two new admin settings, both off by default, each move one of these groups into its own panel. Affected layers: a config migration, `RootConfig` (served by `GET /api/v2/Albums` and `GET /api/v3/Albums/root/config`), the `AlbumsState` store, the root `Albums.vue` gallery panel in both v7 and v8 (Q-097-03 A), and translations. No change to how tag or person albums are queried, cached or authorised.

## Goals
- New boolean setting `TA_separate_category` (category Smart Albums, default `0`): tag albums in their own "Tag Albums" panel.
- New boolean setting `PA_separate_category` (category Smart Albums, default `0`): person albums in their own "Person Albums" panel (Q-097-02 C).
- With both settings off, the root gallery is unchanged.

## Non-Goals
- Changing tag/person album queries, sorting, caching or rights.
- Making the new panels interactive (selection, context menu): they keep the Smart Albums panel's non-interactive behaviour.
- Album pickers (move/merge dialogs) and the `getAlbumTitle` lookup keep using the full merged list.
- Pages other than the root gallery.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-097-01 | Configs `TA_separate_category` (order 13) and `PA_separate_category` (order 103) exist, boolean, default `0`, category Smart Albums, level 0, not expert. | Migration inserts both rows; `down()` removes them. | Standard config validation (`0\|1`). | — | None | Owner request 2026-10-17; Q-097-02 C |
| FR-097-02 | `RootConfig` exposes `is_tag_albums_separate_category_enabled` and `is_person_albums_separate_category_enabled` (bool). | Fields present in v2 `/Albums` and v3 `/Albums/root/config` responses; TypeScript types regenerated. | — | — | None | Owner request |
| FR-097-03 | Both off: Smart Albums panel shows smart + tag + person albums exactly as today; no extra panel. | Rendering identical to before. | — | — | None | Owner request ("disabled by default") |
| FR-097-04 | `TA_separate_category` on: tag albums are absent from Smart Albums and listed in a "Tag Albums" panel (`gallery.tag_albums`) directly below Smart Albums, above tabs and Featured Albums, outside the tabs. | Panel hidden when it has no albums. | — | — | None | Q-097-01 A |
| FR-097-05 | `PA_separate_category` on: person albums are absent from Smart Albums and listed in a "Person Albums" panel (`gallery.person_albums`) directly below the Tag Albums position (below Smart Albums when tag albums are not separated). | Panel hidden when it has no albums. | — | — | None | Q-097-02 C |
| FR-097-06 | v7 and v8 root galleries both honour the settings. | Same panels in both. | — | — | None | Q-097-03 A |
| FR-097-07 | `is-alone` computations of the root panels take the new panels into account. | A panel hides its header only when no other panel is visible. | — | — | None | Consistency |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-097-01 | No additional request or query: the split is done client-side from data already loaded. | Performance | `AlbumRootConfigV3Test::testRunsNoAlbumOrPhotoQuery` stays green. | `AlbumsState` | Design |
| NFR-097-02 | New strings present in every `lang/*/gallery.php` and `lang/*/all_settings.php`. | i18n | `LangTest` passes. | `lang/` | Coding conventions |

## UI / Interaction Mock-ups

Both settings off (unchanged):
```
+--------------------------------------------------------------+
| Smart Albums                                                 |
| [Unsorted] [Starred] [Recent] [#travel] [#cats] [@Alice]     |
|                                                              |
| Featured Albums                                              |
| [Album A] [Album B]                                          |
|                                                              |
| Albums                                                       |
| [Album 1] [Album 2] [Album 3] [Album 4] ...                  |
+--------------------------------------------------------------+
```

Both settings on:
```
+--------------------------------------------------------------+
| Smart Albums                                                 |
| [Unsorted] [Starred] [Recent]                                |
|                                                              |
| Tag Albums                                                   |
| [#travel] [#cats]                                            |
|                                                              |
| Person Albums                                                |
| [@Alice]                                                     |
|                                                              |
| Featured Albums                                              |
| [Album A] [Album B]                                          |
|                                                              |
| [ My albums | Shared ]   (tabs, when enabled)                |
| Albums                                                       |
| [Album 1] [Album 2] [Album 3] [Album 4] ...                  |
+--------------------------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-097-01 | Migration: both configs present with value `0`. |
| S-097-02 | Both off: root config reports `false` for both; single merged Smart Albums panel. |
| S-097-03 | `TA_separate_category` on: config reports `true`; Smart Albums excludes tag albums; Tag Albums panel lists them. |
| S-097-04 | `PA_separate_category` on: config reports `true`; Smart Albums excludes person albums; Person Albums panel lists them. |
| S-097-05 | Separated group empty: its panel is not rendered. |
| S-097-06 | All built-in smart albums hidden, separated groups present: only the separated panels show. |

## Test Strategy
- **REST API (v2/v3):** `RootConfigSeparateCategoriesTest` (Feature_v2) asserts both flags are `false` by default and follow their config on `GET /Albums`; `AlbumRootConfigV3Test` keeps asserting v3 `config` equals v2 `config` and runs no album query.
- **Translations:** `LangTest`.
- **Frontend:** `vp check --fix`, `vp run typecheck`; browser check on a scratch instance for S-097-02 … 06 (v7 and v8).
- **Docs/Contracts:** regenerate `resources/js/lychee.d.ts` via `php artisan typescript:transform`.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-097-01 | `RootConfig::$is_tag_albums_separate_category_enabled`, `RootConfig::$is_person_albums_separate_category_enabled` (bool) | `app/Http/Resources/GalleryConfigs/RootConfig.php`, `lychee.d.ts` |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-097-01 | REST GET /api/v2/Albums | `config` gains the two fields | `RootAlbumResource` |
| API-097-02 | REST GET /api/v3/Albums/root/config | `config` gains the two fields | `AlbumRootConfigResource` |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-097-01 | Merged Smart Albums | Both settings off |
| UI-097-02 | Separate Tag Albums panel | `TA_separate_category` on and at least one tag album |
| UI-097-03 | Separate Person Albums panel | `PA_separate_category` on and at least one person album |

## Telemetry & Observability
None.

## Documentation Deliverables
- Roadmap row 097; knowledge-map note on the `AlbumsState` smart/tag/person split.
- Translation keys `gallery.tag_albums`, `gallery.person_albums`, `all_settings` entries for both configs.

## Fixtures & Sample Data
None.

## Spec DSL
```
domain_objects:
  - id: DO-097-01
    name: RootConfig
    fields:
      - name: is_tag_albums_separate_category_enabled
        type: boolean
      - name: is_person_albums_separate_category_enabled
        type: boolean
configs:
  - key: TA_separate_category
    type_range: "0|1"
    default: "0"
    category: Smart Albums
  - key: PA_separate_category
    type_range: "0|1"
    default: "0"
    category: Smart Albums
routes:
  - id: API-097-01
    method: GET
    path: /api/v2/Albums
  - id: API-097-02
    method: GET
    path: /api/v3/Albums/root/config
ui_states:
  - id: UI-097-01
    description: Merged Smart Albums panel
  - id: UI-097-02
    description: Separate Tag Albums panel
  - id: UI-097-03
    description: Separate Person Albums panel
```
