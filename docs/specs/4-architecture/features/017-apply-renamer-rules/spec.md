# Feature 017 – Apply Renamer Rules & Watermark Confirmation

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-26 |
| Owners | — |
| Linked plan | `docs/specs/4-architecture/features/017-apply-renamer-rules/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/017-apply-renamer-rules/tasks.md` |
| Roadmap entry | #017 |

## Overview

The existing Renamer system (`PATCH /Renamer`) can apply user-defined renaming rules to photos and albums by ID, but no frontend surface exposes this capability. This feature adds:

1. **Album Hero button** — a new toolbar icon in `AlbumHero.vue` that opens a multi-step "Apply Renamer Rules" dialog for the current album's contents.
2. **Context-menu integration** — "Apply Renamer Rules" / "Apply Renamer Rules to Selected" entries on photo and album right-click menus when items are selected.
3. **Multi-step dialog** — Step 1 collects options (target type, scope); Step 2 calls a preview endpoint and displays a scrollable before/after list; Step 3 confirms and executes.
4. **Watermark confirmation** — a small confirmation dialog before executing watermarking from the Album Hero.

Affected modules: frontend (Vue3), backend (PHP controller + new preview endpoint), translations.

## Goals

- G-017-01: Allow users to apply their renamer rules to existing photo and album titles from the gallery UI.
- G-017-02: Provide a preview of the renaming result before committing changes.
- G-017-03: Support both album-wide and selection-based renaming.
- G-017-04: Support recursive (descendants) and current-level-only scoping.
- G-017-05: Warn users about potential timeout when processing all descendants.
- G-017-06: Add a confirmation step before watermarking from the Album Hero.

## Non-Goals

- N-017-01: Renamer rule management (CRUD) — already implemented in the Settings view.
- N-017-02: Changes to the Renamer engine logic (modes, ordering, enforcement) — out of scope.
- N-017-03: Undo/rollback of applied renames — titles are overwritten in place.
- N-017-04: Batch renaming from Smart Albums or Tag Albums (only model albums supported for the hero button; context menu works everywhere selections exist).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|---------------------|--------|
| FR-017-01 | Album Hero displays an "Apply Renamer Rules" icon button when the user is logged in, has `can_edit` rights, and `is_mod_renamer_enabled` is true (via `leftMenu.initData.modules`). This flag already gates on SE license + `renamer_enabled` config + enforcement rules. | Icon shown in the icon row. | Hidden when conditions not met. | N/A | — | Owner directive |
| FR-017-02 | Clicking the hero button opens Step 1 of the "Apply Renamer Rules" dialog. The dialog presents: (a) target type radio: Photos / Albums, (b) scope radio: Current level only / All descendants, (c) a checklist of the user's renamer rules (fetched from `GET /Renamer`) with checkboxes to include/exclude each rule for this run. Rules are pre-checked based on their `is_enabled` status. At least one rule must be selected. | Dialog opens with radios defaulting to "Photos" and "Current level only", rules loaded and pre-checked. | Both selections and at least one rule are required. Returns error toast if rule fetch fails. | N/A | — | Owner directive |
| FR-017-03 | When "All descendants" is selected, a warning message is displayed: "Processing all descendants may fail due to timeout on large album trees." | Warning text shown below the scope radio. | — | — | — | Owner directive |
| FR-017-04 | Clicking "Preview" in Step 1 calls `POST /Renamer::preview` with `album_id`, `target` (photos\|albums), `scope` (current\|descendants), and `rule_ids[]` (the selected rule IDs). The backend applies only the specified rules and returns an array of `{ id, original_title, new_title }` for items whose title would change. | Step 2 displays a scrollable list of before → after pairs. Empty list shows "No changes to apply." | Returns 422 if album_id missing or invalid, or if `rule_ids` is empty. Returns 403 if user lacks edit rights. Returns 401 if not authenticated. | Returns 500 on unexpected error. | — | Owner directive |
| FR-017-05 | Step 2 displays the preview results in a scrollable container (max-height bounded). Each row shows `original_title → new_title`. Items with no change are excluded. If zero changes, the message reads: "No titles would change. If you haven't configured renamer rules yet, visit Settings → Renamer Rules." with a clickable link to the renamer settings page. | Scrollable list rendered. "Apply" and "Cancel" buttons visible. | If zero changes, "Apply" button is disabled and enhanced message shown with settings link. | — | — | Owner directive + Q-017-02 resolution |
| FR-017-06 | Clicking "Apply" in Step 2 calls `PATCH /Renamer` with the collected `photo_ids[]` or `album_ids[]` (IDs from the preview response) and `rule_ids[]` (the same rules selected in Step 1). On success, album cache is cleared and album is refreshed. | Toast "Renaming applied successfully." Album view refreshes. Dialog closes. | — | Toast with error message on failure. | — | Owner directive |
| FR-017-07 | Context menu: when one or more photos are selected, an "Apply Renamer Rules" item appears (requires `can_edit` and `is_mod_renamer_enabled`). | Clicking opens Step 1 dialog with target type pre-set to "Photos" and locked (radio disabled). Scope radio is **hidden** — photos have no descendants, so scope is meaningless. Only explicit `photo_ids[]` are sent. | Hidden when conditions not met. | — | — | Owner directive + Q-017-01 resolution |
| FR-017-08 | Context menu: when one or more albums are selected, an "Apply Renamer Rules" item appears (requires editable albums and `is_mod_renamer_enabled`). | Clicking opens Step 1 dialog with target type pre-set to "Albums" and locked. Scope radio is **shown**: "Current level" = rename only the selected album titles; "All descendants" = rename selected albums + all sub-albums recursively. Backend receives `album_ids[]` + `scope`. | Hidden when conditions not met. | — | — | Owner directive + Q-017-01 resolution |
| FR-017-09 | When triggered from context menu: **Photos** — preview/apply receive `photo_ids[]` only, no scope parameter. **Albums** — preview/apply receive `album_ids[]` + `scope` (current\|descendants). The preview endpoint accepts both shapes and only previews/renames the targeted items. | Preview and apply use exactly the selected IDs (+ scope for albums). | — | — | — | Owner directive + Q-017-01 resolution |
| FR-017-10 | Watermark confirmation: clicking the watermark icon in AlbumHero opens a small confirmation dialog ("Are you sure you want to watermark all photos in this album?") with "Cancel" and "Confirm" buttons. | Confirming triggers the existing `AlbumService.watermark()` call and success toast. Cancelling closes without action. | — | — | — | Owner directive |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-017-01 | Preview response must complete within 10 s for albums with ≤ 5 000 items. | UX responsiveness | Manual timing / test assertion. | Renamer engine performance. | Owner directive |
| NFR-017-02 | The dialog must be keyboard-accessible (Tab, Enter, Escape). | Accessibility | Manual verification. | PrimeVue Dialog component. | Coding conventions |
| NFR-017-03 | All user-facing strings must be translation-ready via `laravel-vue-i18n`. | i18n | Presence in `lang/en/*.php` + placeholder keys in other `lang/<locale>/*.php` files. | Translation pipeline. | Coding conventions |
| NFR-017-04 | Backend preview endpoint must reuse the existing `Renamer` / `PhotoRenamer` / `AlbumRenamer` classes without duplication. | Maintainability | Code review. | `app/Metadata/Renamer/` | Coding conventions |

## UI / Interaction Mock-ups

### Album Hero — new icon placement

```
 ┌────────────────────────────────────────────────────────────┐
 │  [☰ download] [↗ share] [</> embed] [📊 stats] [🖥 frame]│
 │  [🗺 map] [▶ slideshow] [▰ watermark] [Aa renamer]       │
 │  [☐/≡ view toggle]  [👁 nsfw]                             │
 └────────────────────────────────────────────────────────────┘
         new icon ──────────────────────────┘
```

The "Aa" icon (`pi pi-language` or `pi pi-sort-alpha-down`) is placed near the watermark button. Tooltip: "Apply Renamer Rules".

> **Note:** Visibility uses `leftMenu.initData?.modules.is_mod_renamer_enabled` (not `is_se_enabled`). This composite flag from `ModulesRightsResource` already checks SE license validity, `renamer_enabled` config, and enforcement rules server-side.

### Step 1 — Options Dialog

```
+--------------------------------------------------------------+
|  Apply Renamer Rules                                    [×]  |
|--------------------------------------------------------------|
|                                                              |
|  Target:   (•) Photos   ( ) Albums                          |
|                                                              |
|  Scope:    (•) Current level only                            |
|            ( ) All descendants                               |
|                                                              |
|  ⚠ Processing all descendants may fail due to timeout        |
|    on large album trees.                   (shown when desc) |
|                                                              |
|  Rules to apply:                                             |
|  ┌──────────────────────────────────────────────────────┐    |
|  │  [✓] #1 Remove extension  (ALL: ".jpg" → "")           │    |
|  │  [✓] #2 Trim whitespace   (TRIM)                      │    |
|  │  [ ] #3 Uppercase first    (UCFIRST)                   │    |
|  │  [✓] #4 Replace underscores (ALL: "_" → " ")          │    |
|  └──────────────────────────────────────────────────────┘    |
|       (scrollable if many rules; filtered by target type)    |
|                                                              |
|  [ Cancel ]                                    [ Preview > ] |
+--------------------------------------------------------------+
```

Rules are fetched via `GET /Renamer` on dialog open. Each rule shows its order, name (`rule` field), and a summary of mode + needle/replacement. Rules are pre-checked based on their `is_enabled` status. Rules are filtered by the selected target type: when "Photos" is selected, only rules with `is_photo_rule=true` are shown; when "Albums", only `is_album_rule=true`. The "Preview" button is disabled when no rules are checked.

When opened from context menu with photos selected, "Target" is locked to "Photos" and greyed out. When opened with albums selected, locked to "Albums".

### Step 2 — Preview Results

```
+--------------------------------------------------------------+
|  Apply Renamer Rules — Preview                          [×]  |
|--------------------------------------------------------------|
|                                                              |
|  The following titles will be changed (12 items):            |
|                                                              |
|  ┌──────────────────────────────────────────────────────┐    |
|  │  IMG_2031          →  img_2031                       │    |
|  │  DSC 0042 (Copy)   →  dsc 0042                      │    |
|  │  PHOTO_001         →  photo_001                      │    |
|  │  …                                                   │    |
|  └──────────────────────────────────────────────────────┘    |
|            (scrollable, max-height ~300px)                    |
|                                                              |
|  [ Cancel ]                                     [ Apply ✓ ]  |
+--------------------------------------------------------------+
```

If no changes: "No titles would change. Your renamer rules produce no modifications for the selected items." Apply button disabled.

### Watermark Confirmation Dialog

```
+--------------------------------------------------------------+
|  Confirm Watermark                                      [×]  |
|--------------------------------------------------------------|
|                                                              |
|  Are you sure you want to watermark all photos               |
|  in this album?                                              |
|                                                              |
|  This action cannot be undone.                               |
|                                                              |
|  [ Cancel ]                                   [ Confirm ✓ ]  |
+--------------------------------------------------------------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-017-01 | Hero button hidden when user not logged in |
| S-017-02 | Hero button hidden when SE renamer not enabled |
| S-017-03 | Hero button hidden when user lacks `can_edit` |
| S-017-04 | Hero button visible and opens dialog |
| S-017-05 | Step 1: default selections (Photos, Current level) |
| S-017-06 | Step 1: selecting "All descendants" shows warning |
| S-017-07 | Step 1: cancel closes dialog |
| S-017-08 | Preview returns changed items — Step 2 renders list |
| S-017-09 | Preview returns empty — "no changes" message, Apply disabled |
| S-017-10 | Preview unauthorized (401) — error toast |
| S-017-11 | Preview forbidden (403) — error toast |
| S-017-12 | Preview invalid album (422) — error toast |
| S-017-13 | Apply succeeds — toast, cache clear, refresh |
| S-017-14 | Apply fails — error toast, dialog stays open |
| S-017-15 | Context menu: single photo selected — "Apply Renamer Rules" shown |
| S-017-16 | Context menu: multiple photos selected — "Apply Renamer Rules to Selected" shown |
| S-017-17 | Context menu: single album selected — "Apply Renamer Rules" shown |
| S-017-18 | Context menu: multiple albums selected — "Apply Renamer Rules to Selected" shown |
| S-017-19 | Context menu: target type auto-locked based on selection type |
| S-017-20 | Watermark confirmation: cancel does NOT trigger watermarking |
| S-017-21 | Watermark confirmation: confirm triggers watermarking + toast |
| S-017-22 | Step 1: rules fetched and displayed on dialog open |
| S-017-23 | Step 1: rules filtered by selected target type (photo/album) |
| S-017-24 | Step 1: Preview disabled when no rules are checked |
| S-017-25 | Step 1: toggling target type re-filters rule list |
| S-017-26 | Step 1: rule fetch fails — error toast, dialog stays open |

## Test Strategy

- **Core:** Unit tests for the preview logic (fetching items, applying rules, returning diffs).
- **Application:** Feature tests for `POST /Renamer::preview` endpoint — auth (401), forbidden (403), validation (422), success with current-level and descendants scopes, empty-diff response.
- **REST:** Feature tests reuse existing `PATCH /Renamer` coverage. New tests for the preview endpoint.
- **UI (manual):** Verify dialog flow (Step 1 → Step 2 → Apply), context menu items appear/disappear based on conditions, watermark confirmation dialog.
- **Docs/Contracts:** Translation keys present in `lang/en/*.php` and placeholders in other `lang/<locale>/*.php` files.

## Interface & Contract Catalogue

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-017-01 | POST `/Renamer::preview` | Preview renaming results for an album's contents | New endpoint. Returns `{ items: [{ id, original_title, new_title }] }`. Accepts `album_id`, `target` (photos\|albums), `scope` (current\|descendants), `rule_ids[]`, `photo_ids[]?`, `album_ids[]?`. |
| API-017-02 | PATCH `/Renamer` | Apply renaming (existing) | Needs extension: accept optional `rule_ids[]` to filter which rules are applied. When absent, applies all enabled rules (backward-compatible). |
| API-017-03 | GET `/Renamer` | List user's renamer rules (existing) | Already implemented. Used by Step 1 to populate the rule checklist. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-017-01 | Hero button visible | Logged in + can_edit + `is_mod_renamer_enabled` |
| UI-017-02 | Step 1 dialog open | Click hero button or context menu item |
| UI-017-03 | Step 1 descendants warning | Select "All descendants" radio |
| UI-017-04 | Step 2 preview list | Click "Preview" — successful API response |
| UI-017-05 | Step 2 empty state | Preview returns zero changes — shows enhanced message with settings link (Q-017-02) |
| UI-017-06 | Step 2 loading | Waiting for preview API response |
| UI-017-07 | Watermark confirm dialog | Click watermark icon in hero |
| UI-017-08 | Context menu renamer item | Right-click with selection + conditions met |

## Telemetry & Observability

No custom telemetry events. Standard Laravel logging for errors in the preview endpoint.

## Documentation Deliverables

- Roadmap updated with Feature 017 entry.
- Translation keys added to `lang/en/*.php` files (e.g., `gallery.php`, `dialogs.php`) and placeholder entries in all other `lang/<locale>/*.php` files. The `lang/*.json` files are auto-generated and not edited directly.

## Spec DSL

```yaml
routes:
  - id: API-017-01
    method: POST
    path: /api/v2/Renamer::preview
  - id: API-017-02
    method: PATCH
    path: /api/v2/Renamer
  - id: API-017-03
    method: GET
    path: /api/v2/Renamer
ui_states:
  - id: UI-017-01
    description: Hero renamer button visibility
  - id: UI-017-02
    description: Step 1 options dialog
  - id: UI-017-03
    description: Descendants warning
  - id: UI-017-04
    description: Step 2 preview list
  - id: UI-017-05
    description: Empty preview state
  - id: UI-017-06
    description: Preview loading state
  - id: UI-017-07
    description: Watermark confirmation dialog
  - id: UI-017-08
    description: Context menu renamer item
```

---

*Last updated: 2026-02-26*
