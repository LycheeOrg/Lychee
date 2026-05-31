# Feature 043 – Photo Title Link in Admin Maintenance Views

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/043-photo-title-link/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/043-photo-title-link/tasks.md` |
| Roadmap entry | #043 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Admin maintenance views (`DuplicateLine.vue` in the duplicate-finder and `Moderation.vue` in the moderation queue) currently display the photo title as plain text. This feature replaces that plain-text display with a three-state adaptive widget: a clickable `RouterLink` when the photo can be navigated to, a red forbidden icon plus the raw `photo_id` when the album is gone, and an italic/muted title when no identifying information is available. The change spans the frontend components and potentially the `Duplicate` PHP resource, which currently declares `album_id` as non-nullable.

## Goals

1. Render the photo title column in `DuplicateLine.vue` as a clickable `RouterLink` to the album+photo when `album_id` is present.
2. Render the title column in `Moderation.vue` as a clickable `RouterLink` to the album+photo when `album_id` is present.
3. When `album_id` is `null` but a `photo_id` is available, display a small red `pi-ban` icon alongside the `photo_id` in monospace to indicate the album is inaccessible.
4. When both `album_id` and `photo_id` are absent or null, display the photo title in italic muted style (orphaned entry with no identifiers).
5. Encapsulate the three-state logic in a single reusable Vue component (`PhotoTitleLink.vue`) shared between both maintenance views.
6. Make `Duplicate.album_id` nullable in the PHP resource so that the forbidden-icon state is reachable from the duplicate-finder view.

## Non-Goals

- Modifying any other admin or public-facing views beyond `DuplicateLine.vue` and `Moderation.vue`.
- Changing the album-column or owner-column layout in either view.
- Storing any additional data on `OrderItem` or any other persistence model.
- Adding a "navigate to album" link anywhere other than the photo title itself.
- Changing the backend query logic for duplicate detection or moderation listing.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-043-01 | When both `album_id` and `photo_id` are non-null, `PhotoTitleLink` renders the photo title as a `RouterLink` targeting `{ name: 'album', params: { albumId, photoId } }` with `target="_blank"`. | A clickable anchor containing the title text is shown; clicking opens the album page in a new tab. | `album_id` and `photo_id` are non-null strings in the prop; the rendered output contains a `<a>` element with the correct route path. | N/A. | None. | Problem statement: "a clickable link from the photo title to the album". |
| FR-043-02 | When `album_id` is `null` and `photo_id` is a non-null string, `PhotoTitleLink` renders a small red `pi-ban` icon followed by the `photo_id` in monospace font. | Icon and `photo_id` string are visible; no link is rendered. | `album_id` prop is `null`, `photo_id` prop is non-null; rendered output contains `pi pi-ban` icon element and the `photo_id` text. | N/A. | None. | Problem statement: "display a small red forbidden icon and the photo_id". |
| FR-043-03 | When both `album_id` and `photo_id` are `null` (or absent), `PhotoTitleLink` renders the `title` prop in italic muted style (CSS classes `italic text-muted-color`). | Italic muted text is shown; no link and no icon are rendered. | Both `album_id` and `photo_id` props are null/undefined; rendered output contains neither `<a>` nor `pi-ban`; title has `italic` and `text-muted-color` classes. | N/A. | None. | Problem statement: "make the photo title italic and muted". |
| FR-043-04 | `DuplicateLine.vue` uses `PhotoTitleLink` for the photo-title cell (the rightmost title column), replacing the plain `<span>` + separate `<router-link>` icon pattern. | Photo title in duplicate rows is rendered by `PhotoTitleLink`. | Visual regression: duplicate rows show clickable title link for normal entries; forbidden icon for null-album entries if any. | N/A. | None. | Problem statement: applies to admin maintenance views. |
| FR-043-05 | `Moderation.vue` uses `PhotoTitleLink` for the title column (`col_title`), replacing the plain text cell. | Photo title in moderation rows is rendered by `PhotoTitleLink`. | Moderation rows show clickable title link when `photo.album_id` is non-null; forbidden icon when `photo.album_id` is null (unsorted photo). | N/A. | None. | Problem statement: applies to admin maintenance views. |
| FR-043-06 | `Duplicate` PHP resource (`app/Http/Resources/Models/Duplicates/Duplicate.php`) makes `album_id` and `album_title` nullable (`?string`) so that the frontend can receive a `null` album_id for duplicate entries whose album has been deleted. | `Duplicate::fromModel()` accepts a model with a null `album_id` without a type error; the TypeScript interface reflects `album_id: string \| null`. | PHPStan passes; `npm run check` passes after regenerating TypeScript types. | N/A. | None. | Required to enable FR-043-02 in the duplicate-finder view. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-043-01 | `npm run check` (vue-tsc) reports 0 errors after all frontend changes. | Frontend type safety. | `npm run check` exits 0. | Generated TypeScript type definitions in `resources/js/`. | Coding conventions. |
| NFR-043-02 | `npm run lint` (ESLint) reports 0 violations after all frontend changes. | Frontend code style. | `npm run lint` exits 0. | `.eslintrc` / `eslint.config.mjs`. | Coding conventions. |
| NFR-043-03 | PHPStan level 6 reports 0 errors after the `Duplicate` resource change. | Backend type safety. | `make phpstan` exits 0. | `phpstan.neon` baseline. | Coding conventions. |
| NFR-043-04 | `php-cs-fixer` reports 0 violations after the `Duplicate` resource change. | Code style gate. | `vendor/bin/php-cs-fixer fix --dry-run` exits 0. | `.php-cs-fixer.php`. | Coding conventions. |
| NFR-043-05 | `PhotoTitleLink` is a single-file component usable in both `DuplicateLine.vue` and `Moderation.vue` without duplication. | Maintainability. | Code review: both views import from the same component path. | `resources/js/components/maintenance/PhotoTitleLink.vue`. | DRY principle. |
| NFR-043-06 | All existing PHP tests continue to pass. | Regression safety. | `php artisan test` exits 0. | SQLite test database. | Coding conventions. |

## UI / Interaction Mock-ups

### State 1 – Navigable (album_id and photo_id present)

```
+-----------------------------+
| 🔗 Photo Title (link)       |   ← RouterLink, opens album in new tab
+-----------------------------+
```

### State 2 – Album inaccessible (album_id null, photo_id present)

```
+-----------------------------+
| 🚫 abc123def456  (monospace)|   ← red pi-ban icon + photo_id
+-----------------------------+
```

### State 3 – Orphaned entry (both null)

```
+-----------------------------+
| Photo Title  (muted italic) |   ← no link, no icon
+-----------------------------+
```

### DuplicateLine row — updated layout

```
+-----------------+-----------------------------+------------------+
| [Album Title]   | [Photo Title — state 1/2/3] | [checksum…]      |
|  (link to album)|  (PhotoTitleLink component) |                  |
+-----------------+-----------------------------+------------------+
```

### Moderation row — updated layout

```
+----+--------+-----------------------------+----------+---------+----------+
| ☐  | [thumb]| [Photo Title — state 1/2/3] | [owner]  | [album] | [date]   |
|    |        |  (PhotoTitleLink component) |          |         |          |
+----+--------+-----------------------------+----------+---------+----------+
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-043-01 | **Happy path — album + photo present.** `album_id` and `photo_id` are both non-null strings. `PhotoTitleLink` renders a `RouterLink` containing the title text targeting the album route with the photo param. |
| S-043-02 | **Inaccessible album — photo exists.** `album_id` is `null`, `photo_id` is a non-null string. `PhotoTitleLink` renders a red `pi-ban` icon followed by the `photo_id` in monospace. No link element is rendered. |
| S-043-03 | **Orphaned entry — no identifiers.** Both `album_id` and `photo_id` are `null`. `PhotoTitleLink` renders the `title` prop in italic muted style. No link element and no icon are rendered. |
| S-043-04 | **DuplicateLine integration.** `DuplicateLine.vue` uses `PhotoTitleLink` for the photo-title column; state 1 applies for all current entries (album_id is present). No regression in existing duplicate-finder behaviour. |
| S-043-05 | **Moderation integration — unsorted photo.** `Moderation.vue` uses `PhotoTitleLink`; a photo with `album_id: null` (unsorted) renders state 2. |
| S-043-06 | **Moderation integration — album present.** A moderation entry with non-null `album_id` renders state 1 (clickable link). |
| S-043-07 | **Duplicate resource — nullable album_id.** `Duplicate::fromModel()` with a model whose `album_id` is `null` produces a `Duplicate` instance with `album_id: null`; PHPStan and TypeScript are both satisfied. |

## Test Strategy

- **Frontend (TypeScript / `npm run check`):**
  - After changes, `npm run check` must exit 0 with `PhotoTitleLink`'s props typed as `album_id: string | null`, `photo_id: string | null`, `title: string`.
  - `DuplicateLine.vue` and `Moderation.vue` must pass type-check with updated prop bindings.

- **Frontend (ESLint / `npm run lint`):**
  - `npm run lint` exits 0 after adding `PhotoTitleLink.vue`.

- **Backend (PHPStan):**
  - `make phpstan` exits 0 after making `Duplicate.album_id` nullable.

- **Backend (PHP tests):**
  - `php artisan test` exits 0; no existing tests should break from the `Duplicate` nullability change (the query that builds duplicates always returns a non-null `album_id` in practice, but the type allows null).

- **Core / CLI / Docs:** No changes required.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-043-01 | `Duplicate` PHP resource — `album_id` and `album_title` made nullable (`?string`). TypeScript interface updated to `album_id: string \| null`. | application, REST, UI |
| DO-043-02 | `PhotoTitleLink` Vue component — props: `title: string`, `album_id: string \| null`, `photo_id: string \| null`. Renders one of three states. | UI |

### API Routes / Services

_No new or modified API endpoints._

### CLI Commands / Flags

_None introduced._

### Telemetry Events

_None introduced._

### Fixtures & Sample Data

_None introduced._

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-043-01 | Navigable link | `album_id` and `photo_id` are both non-null → `RouterLink` with title text shown; link targets `{ name: 'album', params: { albumId, photoId } }` with `target="_blank"`. |
| UI-043-02 | Forbidden / inaccessible | `album_id` is `null`, `photo_id` non-null → red `pi pi-ban` icon + `photo_id` in monospace font. |
| UI-043-03 | Orphaned / muted | Both `album_id` and `photo_id` are `null` → title text in `italic text-muted-color` CSS classes; no link or icon. |

## Telemetry & Observability

No new telemetry events are introduced. The feature does not change logging behaviour.

## Documentation Deliverables

- Update `docs/specs/4-architecture/knowledge-map.md` if the three-state photo-title pattern is not already documented.
- Update `docs/specs/4-architecture/roadmap.md` on completion.
- Update `docs/specs/_current-session.md`.

## Fixtures & Sample Data

No new fixture files are required. Existing factories are sufficient for backend tests.

## Spec DSL

```yaml
domain_objects:
  - id: DO-043-01
    name: Duplicate
    fields:
      - name: album_id
        type: "string|null"
        constraints: "null when the parent album has been deleted"
      - name: album_title
        type: "string|null"
        constraints: "null when the parent album has been deleted"
  - id: DO-043-02
    name: PhotoTitleLink
    fields:
      - name: title
        type: string
        constraints: "always provided; shown as plain text in state 3"
      - name: album_id
        type: "string|null"
        constraints: "null triggers state 2 or 3"
      - name: photo_id
        type: "string|null"
        constraints: "null triggers state 3"
ui_states:
  - id: UI-043-01
    description: Navigable RouterLink (album_id + photo_id present)
  - id: UI-043-02
    description: Forbidden icon + photo_id (album_id null, photo_id present)
  - id: UI-043-03
    description: Italic muted title (both null)
```

## Appendix

### Relevant source files

| File | Relevance |
|------|-----------|
| `resources/js/components/maintenance/PhotoTitleLink.vue` | New reusable component to create (implements UI-043-01/02/03). |
| `resources/js/components/maintenance/DuplicateLine.vue` | Photo-title cell to update to use `PhotoTitleLink` (FR-043-04). |
| `resources/js/views/admin/Moderation.vue` | Title column to update to use `PhotoTitleLink` (FR-043-05). |
| `app/Http/Resources/Models/Duplicates/Duplicate.php` | `album_id` and `album_title` to make nullable (FR-043-06). |
| `app/Http/Resources/Models/ModerationResource.php` | Already has nullable `album_id` and `album_title`; no change needed. |

### RouterLink target route

Both views route to `{ name: 'album', params: { albumId: album_id, photoId: photo_id } }` with `target="_blank"` so the admin can inspect the photo without leaving the maintenance page.

### Why album_id is currently non-nullable in Duplicate

The duplicate-finder query (`FindDuplicates`) operates on existing photos and always returns an `album_id`. However, making the field nullable in the resource aligns the PHP type with the theoretical possibility that the album could be deleted between query execution and display, and it makes the `PhotoTitleLink` component fully exercisable from both views.
