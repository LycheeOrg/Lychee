# Feature 042 – Photo Display Enrichment

| Field | Value |
|-------|-------|
| Status | Planning |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/042-webshop-order-item-display/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/042-webshop-order-item-display/tasks.md` |
| Roadmap entry | #042 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature enriches the display of photos across two areas of the application with album context and navigation aids.

**Part A — Webshop order detail:** When a customer or administrator views a completed order in `OrderDownload.vue`, each purchased item currently shows only its stored `title` field. Because photo titles do not always reflect the file name and identical filenames can exist across many albums, the item list is often ambiguous and difficult to navigate. This part enriches every order item row with the **album title** from which the photo was purchased and a small **thumbnail** of the photo, making orders immediately recognisable without leaving the page. The change spans the backend (`OrderItemResource`) and the frontend (`OrderDownload.vue`).

**Part B — Admin maintenance views:** Admin maintenance views (`DuplicateLine.vue` in the duplicate-finder and `Moderation.vue` in the moderation queue) currently display the photo title as plain text. This part replaces that plain-text display with a three-state adaptive widget: a clickable `RouterLink` when the photo can be navigated to, a red forbidden icon plus the raw `photo_id` when the album is gone, and an italic/muted title when no identifying information is available. The change spans the frontend components and the `Duplicate` PHP resource, which currently declares `album_id` as non-nullable.

## Goals

### Part A – Webshop Order Item Display

1. Display the album title alongside the photo title for every order item in `OrderDownload.vue`.
2. Display a thumbnail image (THUMB size variant) for every order item.
3. Show a placeholder icon when the photo or its thumbnail has been deleted after purchase.
4. Fetch the new data without introducing N+1 queries (eager-load `items.photo.size_variants` and `items.album`).
5. Keep the `title` stored on `OrderItem` as the authoritative display title (historical record, even if the photo is later deleted).
6. Ensure all existing order tests continue to pass and add new tests for the enriched resource.

### Part B – Admin Maintenance Photo Title Links

7. Render the photo title column in `DuplicateLine.vue` as a clickable `RouterLink` to the album+photo when `album_id` is present.
8. Render the title column in `Moderation.vue` as a clickable `RouterLink` to the album+photo when `album_id` is present.
9. When `album_id` is `null` but a `photo_id` is available, display a small red `pi-ban` icon alongside the `photo_id` in monospace to indicate the album is inaccessible.
10. When both `album_id` and `photo_id` are absent or null, display the photo title in italic muted style (orphaned entry with no identifiers).
11. Encapsulate the three-state logic in a single reusable Vue component (`PhotoTitleLink.vue`) shared between both maintenance views.
12. Make `Duplicate.album_id` nullable in the PHP resource so that the forbidden-icon state is reachable from the duplicate-finder view.

## Non-Goals

- Showing additional photo metadata (EXIF, description, tags) on the order page.
- Modifying the order *list* page (`OrderList.vue`); enrichment applies only to the order *detail/download* page.
- Changing the `OrderItem` database schema or adding new columns.
- Providing a way to navigate to the album from the order item (the existing `RouterLink` to the photo is retained as-is).
- Caching or pre-computing thumbnail URLs at order creation time.
- Modifying any other admin or public-facing views beyond `DuplicateLine.vue` and `Moderation.vue`.
- Changing the album-column or owner-column layout in either admin maintenance view.
- Adding a "navigate to album" link in admin views anywhere other than the photo title itself.
- Changing the backend query logic for duplicate detection or moderation listing.

## Functional Requirements

### Part A – Webshop Order Item Display

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-042-01 | `OrderItemResource` exposes a new `?string $album_title` field containing the title of the album recorded on the order item at display time. | `album_title` is populated from `$item->album?->title` when the album relation is loaded. | `OrderItemResource::fromModel()` is called with an `OrderItem` whose `album` relation is eagerly loaded; `album_title` equals the album's title. | If the album has been deleted, `album_title` is `null`. | None. | Problem statement: "the order would include the Album and title". |
| FR-042-02 | `OrderItemResource` exposes a new `?string $thumb_url` field containing the URL of the THUMB size variant of the purchased photo at display time. | `thumb_url` is populated from the photo's THUMB size variant URL when `items.photo.size_variants` is eagerly loaded. | `OrderItemResource::fromModel()` returns a non-null `thumb_url` for an item whose photo has a THUMB size variant. | If the photo has been deleted or has no THUMB variant, `thumb_url` is `null`. | None. | Problem statement: "along with a thumbnail of the image". |
| FR-042-03 | `OrderResource::fromModel()` eager-loads `items.photo.size_variants` (filtered to thumb-size variants) and `items.album` whenever the order detail is fetched, so that `album_title` and `thumb_url` are always populated without N+1 queries. | `OrderResource::fromModel()` calls `$order->load(...)` including the photo and album relations before building item resources. | Feature test asserts that only a fixed number of queries is executed when loading an order with multiple items (query count assertion or eager-load confirmation). | If relations are not loaded, `album_title` and `thumb_url` default to `null` rather than triggering lazy-load queries. | None. | NFR-042-01. |
| FR-042-04 | `OrderDownload.vue` renders a thumbnail `<img>` element per order item using `item.thumb_url`. When `thumb_url` is `null`, a placeholder icon is shown instead. | The `<img>` element is present with `src` equal to `item.thumb_url` and `loading="lazy"`. The placeholder (`pi pi-image` icon) is shown when `thumb_url` is `null`. | Vue component renders a thumbnail for an item that has a THUMB size variant URL; renders the icon for an item where `thumb_url` is `null`. | N/A. | None. | Problem statement: "thumbnail of the image". |
| FR-042-05 | `OrderDownload.vue` renders the album title per order item using `item.album_title`. When `album_title` is `null`, a translated fallback string (`webshop.orderDownload.unknownAlbum`) is displayed in muted style. | The album title text is visible below the photo title for each item. | Vue component renders album title text for an item with a non-null `album_title`; renders translated fallback for a null `album_title`. | N/A. | None. | Problem statement: "the order would include the Album and title". |
| FR-042-06 | A new i18n key `webshop.orderDownload.unknownAlbum` is added to all language files (or at minimum `en.json`) with value `"Unknown album"`. | `$t('webshop.orderDownload.unknownAlbum')` resolves to a non-empty string in the English locale. | i18n key present in `lang/en/` (or equivalent). | N/A. | None. | Coding convention: all new UI strings must have translation keys. |

### Part B – Admin Maintenance Photo Title Links

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-042-07 | When both `album_id` and `photo_id` are non-null, `PhotoTitleLink` renders the photo title as a `RouterLink` targeting `{ name: 'album', params: { albumId, photoId } }` with `target="_blank"`. | A clickable anchor containing the title text is shown; clicking opens the album page in a new tab. | `album_id` and `photo_id` are non-null strings in the prop; the rendered output contains a `<a>` element with the correct route path. | N/A. | None. | Problem statement: "a clickable link from the photo title to the album". |
| FR-042-08 | When `album_id` is `null` and `photo_id` is a non-null string, `PhotoTitleLink` renders a small red `pi-ban` icon followed by the `photo_id` in monospace font. | Icon and `photo_id` string are visible; no link is rendered. | `album_id` prop is `null`, `photo_id` prop is non-null; rendered output contains `pi pi-ban` icon element and the `photo_id` text. | N/A. | None. | Problem statement: "display a small red forbidden icon and the photo_id". |
| FR-042-09 | When both `album_id` and `photo_id` are `null` (or absent), `PhotoTitleLink` renders the `title` prop in italic muted style (CSS classes `italic text-muted-color`). | Italic muted text is shown; no link and no icon are rendered. | Both `album_id` and `photo_id` props are null/undefined; rendered output contains neither `<a>` nor `pi-ban`; title has `italic` and `text-muted-color` classes. | N/A. | None. | Problem statement: "make the photo title italic and muted". |
| FR-042-10 | `DuplicateLine.vue` uses `PhotoTitleLink` for the photo-title cell (the rightmost title column), replacing the plain `<span>` + separate `<router-link>` icon pattern. | Photo title in duplicate rows is rendered by `PhotoTitleLink`. | Visual regression: duplicate rows show clickable title link for normal entries; forbidden icon for null-album entries if any. | N/A. | None. | Problem statement: applies to admin maintenance views. |
| FR-042-11 | `Moderation.vue` uses `PhotoTitleLink` for the title column (`col_title`), replacing the plain text cell. | Photo title in moderation rows is rendered by `PhotoTitleLink`. | Moderation rows show clickable title link when `photo.album_id` is non-null; forbidden icon when `photo.album_id` is null (unsorted photo). | N/A. | None. | Problem statement: applies to admin maintenance views. |
| FR-042-12 | `Duplicate` PHP resource (`app/Http/Resources/Models/Duplicates/Duplicate.php`) makes `album_id` and `album_title` nullable (`?string`) so that the frontend can receive a `null` album_id for duplicate entries whose album has been deleted. | `Duplicate::fromModel()` accepts a model with a null `album_id` without a type error; the TypeScript interface reflects `album_id: string \| null`. | PHPStan passes; `npm run check` passes after regenerating TypeScript types. | N/A. | None. | Required to enable FR-042-08 in the duplicate-finder view. |

## Non-Functional Requirements

### Part A – Webshop Order Item Display

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-042-01 | Loading an order detail page must not generate additional SQL queries per order item beyond what the eager-load produces. | Performance / N+1 prevention. | Feature test or integration test asserting that `DB::getQueryLog()` count does not grow with item count. | `OrderResource::fromModel()` eager-load strategy. | Coding conventions. |
| NFR-042-02 | Only THUMB-category size variants (`SMALL`, `SMALL2X`, `THUMB`, `THUMB2X`, `PLACEHOLDER`) are loaded for the thumbnail — not ORIGINAL or MEDIUM. | Bandwidth / memory efficiency. | Code review: `size_variants` relation constrained to the five thumb variants via `Thumb::sizeVariantsFilter()` or an equivalent inline `whereIn`. | `App\Models\Extensions\Thumb::sizeVariantsFilter()`. | Existing pattern in `Thumb::createFromQueryable()`. |
| NFR-042-03 | PHPStan level 6 must report 0 errors after changes. | Code quality gate. | `make phpstan` exits 0. | `phpstan.neon` baseline. | Coding conventions. |
| NFR-042-04 | `php-cs-fixer` must report 0 violations after changes. | Code style gate. | `vendor/bin/php-cs-fixer fix --dry-run` exits 0. | `.php-cs-fixer.php`. | Coding conventions. |
| NFR-042-05 | All existing tests must continue to pass. | Regression safety. | `php artisan test` exits 0. | SQLite test database. | Coding conventions. |
| NFR-042-06 | Frontend TypeScript compiler (`vue-tsc`) must report 0 errors. | Frontend type safety. | `npm run check` exits 0. | Generated TypeScript type definitions in `resources/js/`. | Coding conventions. |
| NFR-042-07 | Thumbnail image elements must use lazy loading (`loading="lazy"`) to avoid blocking page render. | Performance / UX. | Code review: `<img>` element has `loading="lazy"` attribute. | Frontend component. | Web performance best practices. |

### Part B – Admin Maintenance Photo Title Links

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-042-08 | `npm run lint` (ESLint) reports 0 violations after all frontend changes. | Frontend code style. | `npm run lint` exits 0. | `.eslintrc` / `eslint.config.mjs`. | Coding conventions. |
| NFR-042-09 | `PhotoTitleLink` is a single-file component usable in both `DuplicateLine.vue` and `Moderation.vue` without duplication. | Maintainability. | Code review: both views import from the same component path. | `resources/js/components/maintenance/PhotoTitleLink.vue`. | DRY principle. |

## UI / Interaction Mock-ups — Part A (Webshop Order Item Display)

### Order Item Row — Current Layout
```
+----------------------------------------------------------+
| [Title]                          [size_variant - license] |
|                                  [price]                  |
| [Download button / input / N/A]                           |
+----------------------------------------------------------+
```

### Order Item Row — New Layout
```
+----------------------------------------------------------+
| [48×48 thumb]  [Title]                                    |
|                [album title / "Unknown album" (muted)]    |
|                [size_variant - license]                   |
|                [Download button / input / N/A]            |
|                                            [price]        |
+----------------------------------------------------------+
```

### Thumbnail States
```
+----------------+    +----------------+
|  ┌──────────┐  |    |  ┌──────────┐  |
|  │  <img>   │  |    |  │ pi-image │  |
|  │ (THUMB)  │  |    │  │  (icon)  │  |
|  └──────────┘  |    |  └──────────┘  |
|  thumb present |    |  thumb absent  |
+----------------+    +----------------+
```

### Album Title Display
```
Photo Title
Album Name                ← normal text or muted "Unknown album" when null
medium - personal
```

## UI / Interaction Mock-ups — Part B (Admin Maintenance Photo Title Links)

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

### Part A – Webshop Order Item Display

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-042-01 | **Album title present.** Order item has a valid `album_id` pointing to an existing album. `OrderItemResource.album_title` equals the album's `title`. `OrderDownload.vue` renders the album name below the photo title. |
| S-042-02 | **Album deleted after purchase.** Order item's `album_id` references a deleted album. `OrderItemResource.album_title` is `null`. `OrderDownload.vue` renders `"Unknown album"` in muted style. |
| S-042-03 | **Thumbnail present.** Order item's photo has a THUMB size variant. `OrderItemResource.thumb_url` is a non-null URL. `OrderDownload.vue` renders `<img>` with `src=thumb_url`. |
| S-042-04 | **Photo deleted after purchase.** Order item's `photo_id` references a deleted photo. `OrderItemResource.thumb_url` is `null`. `OrderDownload.vue` renders the placeholder `pi pi-image` icon. |
| S-042-05 | **Photo exists but has no THUMB variant.** `thumb_url` is `null` (THUMB variant does not exist). Placeholder icon is shown. |
| S-042-06 | **Both album and photo present.** Full happy path: both `album_title` and `thumb_url` are non-null and rendered correctly. |
| S-042-07 | **N+1 prevention.** Loading an order with 5 items produces the same number of SQL queries as loading one item (all photo and album data is eager-loaded). |

### Part B – Admin Maintenance Photo Title Links

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-042-08 | **Happy path — album + photo present.** `album_id` and `photo_id` are both non-null strings. `PhotoTitleLink` renders a `RouterLink` containing the title text targeting the album route with the photo param. |
| S-042-09 | **Inaccessible album — photo exists.** `album_id` is `null`, `photo_id` is a non-null string. `PhotoTitleLink` renders a red `pi-ban` icon followed by the `photo_id` in monospace. No link element is rendered. |
| S-042-10 | **Orphaned entry — no identifiers.** Both `album_id` and `photo_id` are `null`. `PhotoTitleLink` renders the `title` prop in italic muted style. No link element and no icon are rendered. |
| S-042-11 | **DuplicateLine integration.** `DuplicateLine.vue` uses `PhotoTitleLink` for the photo-title column; state 1 applies for all current entries (album_id is present). No regression in existing duplicate-finder behaviour. |
| S-042-12 | **Moderation integration — unsorted photo.** `Moderation.vue` uses `PhotoTitleLink`; a photo with `album_id: null` (unsorted) renders state 2. |
| S-042-13 | **Moderation integration — album present.** A moderation entry with non-null `album_id` renders state 1 (clickable link). |
| S-042-14 | **Duplicate resource — nullable album_id.** `Duplicate::fromModel()` with a model whose `album_id` is `null` produces a `Duplicate` instance with `album_id: null`; PHPStan and TypeScript are both satisfied. |

## Test Strategy

### Part A – Webshop Order Item Display

- **Backend (Unit):**
  - `OrderItemResource::fromModel()` with a fully loaded `OrderItem` (album + photo + THUMB size variant): assert `album_title` and `thumb_url` are non-null.
  - `OrderItemResource::fromModel()` with deleted album (`album` relation returns null): assert `album_title` is `null`.
  - `OrderItemResource::fromModel()` with deleted photo / no THUMB variant: assert `thumb_url` is `null`.

- **Backend (Feature/REST):**
  - `GET /api/v2/Order/{id}` returns JSON with `items[*].album_title` and `items[*].thumb_url` fields.
  - Query count assertion: loading an order with multiple items does not produce per-item SQL queries for album/photo/size_variant.

- **Frontend (TypeScript / `npm run check`):**
  - Confirm `App.Http.Resources.Shop.OrderItemResource` TypeScript interface includes `album_title: string | null` and `thumb_url: string | null` after running the TypeScript transformer.
  - `npm run check` exits 0.

### Part B – Admin Maintenance Photo Title Links

- **Frontend (TypeScript / `npm run check`):**
  - After changes, `npm run check` must exit 0 with `PhotoTitleLink`'s props typed as `album_id: string | null`, `photo_id: string | null`, `title: string`.
  - `DuplicateLine.vue` and `Moderation.vue` must pass type-check with updated prop bindings.

- **Frontend (ESLint / `npm run lint`):**
  - `npm run lint` exits 0 after adding `PhotoTitleLink.vue`.

- **Backend (PHPStan):**
  - `make phpstan` exits 0 after making `Duplicate.album_id` nullable.

- **Backend (PHP tests):**
  - `php artisan test` exits 0; no existing tests should break from the `Duplicate` nullability change.

- **Core / CLI / Docs:** No changes required.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-042-01 | `OrderItemResource` — extended with `album_title: ?string` and `thumb_url: ?string` | application, REST, UI |
| DO-042-02 | `Duplicate` PHP resource — `album_id` and `album_title` made nullable (`?string`). TypeScript interface updated to `album_id: string \| null`. | application, REST, UI |
| DO-042-03 | `PhotoTitleLink` Vue component — props: `title: string`, `album_id: string \| null`, `photo_id: string \| null`. Renders one of three states. | UI |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-042-01 | REST GET `/api/v2/Order/{id}` | Returns `OrderResource` with enriched `items[]` containing `album_title` and `thumb_url`. | Existing endpoint; response schema extended. |

_No new or modified API endpoints for Part B._

### CLI Commands / Flags

_None introduced._

### Telemetry Events

_None introduced._

### Fixtures & Sample Data

_None introduced._

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-042-01 | Thumbnail rendered | `item.thumb_url` is a valid URL → `<img loading="lazy" :src="item.thumb_url">` shown. |
| UI-042-02 | Thumbnail placeholder | `item.thumb_url` is `null` → `<i class="pi pi-image">` shown. |
| UI-042-03 | Album title rendered | `item.album_title` is non-null → album title text shown in muted style below photo title. |
| UI-042-04 | Unknown album fallback | `item.album_title` is `null` → `$t('webshop.orderDownload.unknownAlbum')` rendered in muted style. |
| UI-042-05 | Navigable link | `album_id` and `photo_id` are both non-null → `RouterLink` with title text shown; link targets `{ name: 'album', params: { albumId, photoId } }` with `target="_blank"`. |
| UI-042-06 | Forbidden / inaccessible | `album_id` is `null`, `photo_id` non-null → red `pi pi-ban` icon + `photo_id` in monospace font. |
| UI-042-07 | Orphaned / muted | Both `album_id` and `photo_id` are `null` → title text in `italic text-muted-color` CSS classes; no link or icon. |

## Telemetry & Observability

No new telemetry events are introduced. The feature does not change logging behaviour.

## Documentation Deliverables

- Update `docs/specs/4-architecture/shop-architecture.md` to mention that `OrderItemResource` now includes `album_title` and `thumb_url` for display purposes.
- Update `docs/specs/4-architecture/knowledge-map.md` if the photo thumbnail eager-loading pattern or the three-state photo-title pattern is not already documented.
- Update `docs/specs/4-architecture/roadmap.md` on completion.
- Update `docs/specs/_current-session.md`.

## Fixtures & Sample Data

No new fixture files are required. Existing factory-based tests (`OrderFactory`, `OrderItemFactory`, `PhotoFactory`, album factories) are sufficient.

## Spec DSL

```yaml
domain_objects:
  - id: DO-042-01
    name: OrderItemResource
    fields:
      - name: album_title
        type: "string|null"
        constraints: "null when album deleted or not loaded"
      - name: thumb_url
        type: "string|null"
        constraints: "null when photo deleted or no THUMB variant"
  - id: DO-042-02
    name: Duplicate
    fields:
      - name: album_id
        type: "string|null"
        constraints: "null when the parent album has been deleted"
      - name: album_title
        type: "string|null"
        constraints: "null when the parent album has been deleted"
  - id: DO-042-03
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
routes:
  - id: API-042-01
    method: GET
    path: /api/v2/Order/{id}
    notes: "response.items[].album_title and response.items[].thumb_url added"
ui_states:
  - id: UI-042-01
    description: Thumbnail image rendered
  - id: UI-042-02
    description: Thumbnail placeholder icon rendered
  - id: UI-042-03
    description: Album title text rendered
  - id: UI-042-04
    description: Unknown album fallback rendered
  - id: UI-042-05
    description: Navigable RouterLink (album_id + photo_id present)
  - id: UI-042-06
    description: Forbidden icon + photo_id (album_id null, photo_id present)
  - id: UI-042-07
    description: Italic muted title (both null)
```

## Appendix

### Relevant source files — Part A (Webshop Order Item Display)

| File | Relevance |
|------|-----------|
| `app/Http/Resources/Shop/OrderItemResource.php` | DTO to extend with `album_title` and `thumb_url`. |
| `app/Http/Resources/Shop/OrderResource.php` | `fromModel()` to extend the eager-load call. |
| `app/Models/OrderItem.php` | `photo()` and `album()` `BelongsTo` relations already defined. |
| `app/Models/Extensions/Thumb.php` | `sizeVariantsFilter()` helper for restricting size-variant eager loads to thumb variants. |
| `app/Models/SizeVariant.php` | Provides `url` attribute used to build `thumb_url`. |
| `resources/js/views/webshop/OrderDownload.vue` | Frontend view to update with thumbnail and album title. |
| `resources/js/services/webshop-service.ts` | Typed API service; auto-updated by TypeScript transformer. |
| `lang/en/` | i18n file to add `webshop.orderDownload.unknownAlbum`. |

### Relevant source files — Part B (Admin Maintenance Photo Title Links)

| File | Relevance |
|------|-----------|
| `resources/js/components/maintenance/PhotoTitleLink.vue` | New reusable component to create (implements UI-042-05/06/07). |
| `resources/js/components/maintenance/DuplicateLine.vue` | Photo-title cell to update to use `PhotoTitleLink` (FR-042-10). |
| `resources/js/views/admin/Moderation.vue` | Title column to update to use `PhotoTitleLink` (FR-042-11). |
| `app/Http/Resources/Models/Duplicates/Duplicate.php` | `album_id` and `album_title` to make nullable (FR-042-12). |
| `app/Http/Resources/Models/ModerationResource.php` | Already has nullable `album_id` and `album_title`; no change needed. |

### TypeScript transformation note

After modifying `OrderItemResource.php` or `Duplicate.php`, run `php artisan typescript:transform` (or the equivalent npm script) to regenerate the TypeScript interfaces. The resulting interfaces must include:
- `App.Http.Resources.Shop.OrderItemResource`: `album_title: string | null` and `thumb_url: string | null`.
- `App.Http.Resources.Models.Duplicates.Duplicate`: `album_id: string | null` and `album_title: string | null`.

### Size variant URL pattern

`SizeVariant` models expose a `url` attribute via `getUrlAttribute()`. For THUMB variants, `$item->photo->size_variants->getSizeVariant(SizeVariantType::THUMB)?->url` provides the value to assign to `thumb_url`. The `SizeVariants` extension is loaded via the standard eager-load with a `whereIn('type', [...])` filter mirroring `Thumb::sizeVariantsFilter()`.

### RouterLink target route (Part B)

Both `DuplicateLine.vue` and `Moderation.vue` route to `{ name: 'album', params: { albumId: album_id, photoId: photo_id } }` with `target="_blank"` so the admin can inspect the photo without leaving the maintenance page.

### Why album_id is currently non-nullable in Duplicate

The duplicate-finder query (`FindDuplicates`) operates on existing photos and always returns an `album_id`. However, making the field nullable in the resource aligns the PHP type with the theoretical possibility that the album could be deleted between query execution and display, and it makes the `PhotoTitleLink` component fully exercisable from both views.
