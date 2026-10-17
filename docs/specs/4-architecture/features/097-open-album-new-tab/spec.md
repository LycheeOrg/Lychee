# Feature 097 – Open Album in New Tab

| Field | Value |
|-------|-------|
| Status | Implemented 2026-10-17 (drift gate in plan) |
| Last updated | 2026-10-17 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #097 |
| Decisions | — (Q-097-01 → A both trees, Q-097-02 → A Vitest kept; resolved 2026-10-17 in [open-questions.md](open-questions.md)) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Right-clicking an album tile opens a context menu of actions on that album (set cover, rename, merge, move, pin, delete, download). None of them opens the album. This feature adds **Open in a new tab** as the first entry of the single-album menu, in both frontends (v7 PrimeVue menu, v8 Nuxt UI menu, Q-097-01), and verifies that a **middle click** on a tile opens the album in a new tab through the browser, as the tiles are anchors.

Frontend only: two context-menu composables, one shared helper, one label key in every locale. No API, config, storage or `lychee.d.ts` change.

## Goals

- G1 – The first entry of the single-album context menu opens the album in a new browser tab, for every album that gets a menu, whatever the viewer's rights.
- G2 – The new tab lands on the same route the tile's own link targets.
- G3 – Middle click on an album tile (grid and list, v7 and v8) opens the album in a new tab, and nothing in the app prevents it.
- G4 – Left click, modifier clicks, drag selection and the other menu entries behave as before.
- G5 – The v8 root listing's context menu opens for every viewer who has an album to right-click, guests on public albums included (FR-097-06).

## Non-Goals

- N-097-01 – Ctrl/Meta+click and Shift+click keep toggling the selection (`useSelection().albumSelect`); they do not open a tab.
- N-097-02 – No "open all in new tabs" entry on the multi-album menu: browsers allow one window per user gesture.
- N-097-03 – Photo context menus and photo tiles.
- N-097-04 – Smart albums (not in `selectableAlbums`, so they get no context menu today).
- N-097-05 – A `vp test` step in CI (Q-097-02, owner follow-up).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-097-01 | The single-album context menu (one album selected: regular, tag, pinned or shared album, on the root listing, inside an album, on tag albums, in search results) lists `gallery.menus.open_in_new_tab` **first**, before every other entry, with `access: true` (not gated by any right). Icon `lucide:external-link` in v8, `pi pi-external-link` in v7. A divider follows it when at least one later entry is accessible, so the actions form their own group; a viewer with no right sees the entry alone, with no trailing divider. | Owner right-clicks an album → `Open in a new tab`, divider, `Set Album Cover` (inside an album) / `Rename`, … | Guest right-clicks a public album → the menu holds `Open in a new tab` only. | — | none | Owner request 2026-10-17; Q-097-01 (both frontends) |
| FR-097-02 | Activating the entry opens the selected album in a new tab: `albumHref(router, id)` resolves `{ name: albumRoutes().album, params: { albumId: id } }` through the router (`album` on the gallery and tag listings, `flow-album` on the flow, `tag` while browsing a tag photo, the same mapping as the tile's `router-link`), and `openInNewTab(href)` calls `window.open(href, "_blank", "noopener")`: `noopener` alone requests a tab, not a popup window, and detaches `window.opener`. The current tab keeps its listing, scroll position and selection. | Choose the entry on album `a1` from `/gallery` → new tab at `/gallery/a1`. | — | The browser blocks the popup (user setting): nothing happens in the current tab. | none | Owner request |
| FR-097-03 | The multi-album menu (two or more albums selected) has no such entry (N-097-02). | Right-click with two albums selected → `Merge Selected`, … only. | — | — | none | N-097-02 |
| FR-097-04 | Middle click on an album tile opens the album in a new tab through the browser's own anchor behaviour: every album tile is a `router-link` (`AlbumThumb.vue`, `AlbumThumbVirtual.vue`, the two links of `AlbumListItem.vue` and `AlbumListItemVirtual.vue`, both trees), the drag selection ignores every button but the left one (`dragAndSelect.ts` `canStart`), and the selection handlers act on `click` only. No code is added for this; the feature verifies it (S-097-05, S-097-06). | Middle click on a tile → new tab on the album route, current tab unchanged. | — | — | none | Owner request |
| FR-097-06 | The v8 root listing's `UContextMenu` is disabled only when there is no album to right-click at all: its `disabled` guard reads `albumsStore.selectableAlbums.length === 0` (own, pinned and shared scopes, the pool `useSelection` resolves against), not the own-scope `albums` list alone. A guest, whose public albums arrive through the shared scope, and a user who only has albums shared with them get the menu. | Guest right-clicks a public root album → the menu opens with `Open in a new tab`. | — | — | none | Found while verifying S-097-02: with `albums.length === 0` the guest got no menu. |
| FR-097-05 | Left click on a tile navigates in the current tab, Ctrl/Meta/Shift+click toggle the selection, and every other context-menu entry keeps its label, order and gating. | — | — | — | none | G4 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-097-01 | No new dependency, no API or `lychee.d.ts` change. | AGENTS.md | `package.json`, `composer.json`, `resources/js/lychee.d.ts` unchanged. | — | AGENTS.md |
| NFR-097-02 | The label exists, translated, in every `lang/<locale>/gallery.php` (23 locales). | `LangTest::testLanguageConsistency` | `php artisan test --filter=LangTest` green. | — | Lychee translation rule |
| NFR-097-03 | URL resolution and tab opening live in a shared pure helper (`resources/js/composables/album/openAlbumInNewTab.ts`); the helper and the first-entry rule of both composables are covered by Vitest unit tests run with `vp test` (Q-097-02). | AGENTS.md (test-first, straight-line increments) | `vp test` green. | Vite+ (Feature 096) | AGENTS.md |
| NFR-097-04 | Works offline: the v8 icon comes from the bundled Lucide collection, the v7 icon from the bundled PrimeIcons font. | Lychee runs with no network | Code review. | `resources/js/v8/icons.ts` | Offline rule |

## UI / Interaction Mock-ups

v8 (`UContextMenu`), the owner right-clicks the tile of "Holidays":

```
+--------------------------------------------------------------+
|  Albums                                                      |
|                                                              |
|   ┌────────┐  ┌────────┐  ┌────────┐                         |
|   │  Trip  │  │Holidays│  │ Family │                         |
|   │        │  │   ▸    │  │        │                         |
|   └────────┘  └───┬────┘  └────────┘                         |
|                   │ ┌──────────────────────────┐             |
|                   └─│ ↗ Open in a new tab      │ ◄ FR-097-01 |
|                     ├──────────────────────────┤ ◄ divider   |
|                     │ ▣ Set Album Cover        │ (inside an  |
|                     │ ✎ Rename                 │  album only)|
|                     │ ✎ Apply Renamer          │             |
|                     │ ☺ Scan for Faces         │             |
|                     │ ⇲ Merge                  │             |
|                     │ ▭ Move                   │             |
|                     │ ⌖ Pin                    │             |
|                     │ 🗑 Delete                 │             |
|                     │ ⤓ Download               │             |
|                     └──────────────────────────┘             |
+--------------------------------------------------------------+
```

v7 (PrimeVue `ContextMenu`) shows the same list with PrimeIcons glyphs. A guest on a public album:

```
┌──────────────────────────┐
│ ↗ Open in a new tab      │   no divider, no other entry
└──────────────────────────┘
```

Middle click (FR-097-04), both trees, grid and list:

```
  [ tile ] ──middle click──► new tab: /gallery/<albumId>
  current tab: listing, scroll position and selection unchanged
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-097-01 | Owner right-clicks one album on the root listing (v8) → first entry `Open in a new tab`, then a divider, then `Rename`, … |
| S-097-02 | Guest right-clicks a public root album (no download grant) → the menu opens (FR-097-06) and holds `Open in a new tab` only, no divider; with a download grant: `Open in a new tab`, divider, `Download`. |
| S-097-03 | Choosing the entry from `/gallery` opens a new tab at `/gallery/<id>`; the current tab is unchanged. |
| S-097-04 | `albumHref` maps the current view to the tile's route: `gallery`/`tags` → `album`, `flow` → `flow-album`, `tag` with a photo → `tag`. |
| S-097-05 | Middle click on a grid tile (v8 virtual grid, v7 grid) opens a new tab at the album route; the current tab stays. |
| S-097-06 | Middle click on a list-row link (list view) opens a new tab at the album route. |
| S-097-07 | Two albums selected, right-click → no `Open in a new tab` entry. |
| S-097-08 | Left click on a tile navigates in the current tab; Ctrl+click toggles the selection. |
| S-097-09 | v7: S-097-01, S-097-03 and S-097-05 hold with the PrimeVue menu. |
| S-097-10 | Inside an album (v8 `AlbumPanel.vue`), the owner's menu starts with `Open in a new tab`, divider, `Set Album Cover`, … |

## Test Strategy

- **Models / Actions:** none.
- **REST API (v2/v3):** none.
- **Artisan commands:** none.
- **Frontend (v7/v8):** Vitest (`vp test`): `openAlbumInNewTab.test.ts` (S-097-03, S-097-04), `contextMenu.test.ts` in v8 and v7 (S-097-01, S-097-02, S-097-07, callback → `window.open`). Browser check on a scratch instance with Playwright (S-097-01 … 10) in both trees.
- **Docs/Contracts:** `LangTest` for the locales; no generated type change.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| — | none | — |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| — | none | — | — |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| — | none | — |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| — | none | — |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| — | none | — |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-097-01 | Single-album context menu | Right-click on one album → `Open in a new tab` first (FR-097-01). |
| UI-097-02 | New tab | Entry chosen or middle click → album route in a new tab (FR-097-02, FR-097-04). |

## Telemetry & Observability

None.

## Documentation Deliverables

- Roadmap row 097; knowledge map entry for the context-menu composables and the shared helper; this feature's plan (analysis gate, drift gate) and tasks.

## Fixtures & Sample Data

None.

## Spec DSL

```
domain_objects: []
routes: []
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-097-01
    description: Single-album context menu starts with Open in a new tab
  - id: UI-097-02
    description: Album opened in a new tab (menu entry or middle click)
helpers:
  - module: resources/js/composables/album/openAlbumInNewTab.ts
    functions: [albumHref, openInNewTab]
labels:
  - key: gallery.menus.open_in_new_tab
    locales: 23
```
