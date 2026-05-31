# Feature 042 – Order Item Photo Link

| Field | Value |
|-------|-------|
| Status | Planning |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/042-order-item-photo-link/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/042-order-item-photo-link/tasks.md` |
| Roadmap entry | #042 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The webshop Order Download page (`OrderDownload.vue`) currently displays each purchased item's title as plain text only.
When a title matches a file name that appears in multiple albums, operators and customers cannot identify which gallery the image came from.
This feature adds three-state navigation cues to each order item title: a clickable link when the source album still exists, a red forbidden badge with the photo ID when only the album is gone, and an italic/muted title when both the album and photo records have been deleted.

Affected modules: REST (Shop), UI (webshop/OrderDownload).

## Goals

1. Clickable link from an order-item title to the album/photo in the gallery viewer when the album entity still exists.
2. Red forbidden icon (`pi pi-ban`) plus the stored `photo_id` when the album is deleted but the photo record still exists.
3. Italic muted title when both the album and photo records no longer exist in the database.
4. Backend `OrderItemResource` exposes `album_exists` and `photo_exists` boolean flags so the frontend can derive the state without additional API calls.
5. No N+1 queries: album and photo presence checked via eager-loaded relations.

## Non-Goals

- Resurrecting deleted albums or photos.
- Changing how the order-item title itself is stored or displayed in non-webshop views.
- Handling the edge-case of `album_id IS NULL` (album-level purchases) beyond the existing plain-text rendering.
- Modifying the `OrderList.vue` admin table (which shows order-level summaries, not item-level titles).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|----|
| FR-042-01 | `OrderItemResource` exposes `album_exists: bool` and `photo_exists: bool`. | Backend sets `album_exists = true` when the `album` relation is non-null after eager load; `photo_exists = true` when the `photo` relation is non-null. | PHPStan level-6 clean; TypeScript type regenerated via `npm run generate-types` (or equivalent). | If eager-load fails, the flag defaults to `false` so the degraded UI state is shown rather than a broken link. | — | Problem statement; OrderItem model. |
| FR-042-02 | `OrderResource::fromModel()` eager-loads `items.album` and `items.photo` relations whenever items are loaded. | No additional queries when the `OrderItemResource` reads the flags. | PHPStan; `php artisan test`. | Relation load failure surfaced as `album_exists=false`. | — | NFR-042-01 (N+1 prevention). |
| FR-042-03 | **Linked state**: when `album_id` is non-null AND `album_exists === true`, the order-item title renders as a `RouterLink` navigating to `{ name: 'album', params: { albumId, photoId } }`. | Clicking the title opens the correct gallery album/photo view in a new tab or same window. | Vue component renders `<RouterLink>` tag; `album_id` prop non-null. | Not applicable — state is reached only when album exists. | — | Problem statement. |
| FR-042-04 | **Forbidden state**: when `album_id` is non-null AND `album_exists === false` AND `photo_exists === true`, the title cell shows a red `pi pi-ban` icon followed by the `photo_id` string as a textual reference. | User sees: `🚫 <photo_id>` next to the stored title, visually distinguishing the item. | Template conditional; unit/component test. | — | — | Problem statement. |
| FR-042-05 | **Ghost state**: when `album_exists === false` AND `photo_exists === false` (or `album_id` is null and `photo_id` is null), the title renders as italic and muted (`text-muted-color italic`). | Title shows in muted italic styling. | Template conditional; unit/component test. | — | — | Problem statement. |
| FR-042-06 | The existing `RouterLink` on line 118 of `OrderDownload.vue` is replaced by the three-state conditional so no broken links are emitted when IDs are null or entities are deleted. | No 404 navigation errors in the browser console for orders with deleted albums/photos. | Manual smoke test with seeded stale order items; automated test. | — | — | Existing code analysis. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-042-01 | Fetching a single order must not issue more than O(1) additional queries regardless of the number of line items. | Performance / avoid N+1. | No additional query count increase when comparing a 1-item vs 20-item order response in debugbar / query log. | Eloquent eager-loading (`with`/`load`). | Problem statement; Laravel best practice. |
| NFR-042-02 | TypeScript types for `OrderItemResource` stay in sync with the PHP definition. | Type safety across REST/UI boundary. | `npm run generate-types` produces no diff on CI. | Spatie TypeScript Transformer. | Coding conventions. |
| NFR-042-03 | All new PHP conforms to coding conventions: strict comparison, no `empty()`, `in_array()` with third param `true`, license headers in new files. | Consistency. | `vendor/bin/php-cs-fixer fix` leaves no diff; PHPStan level-6 passes. | php-cs-fixer, PHPStan. | `docs/specs/3-reference/coding-conventions.md`. |

## UI / Interaction Mock-ups (required for UI-facing work)

Three states for the title cell inside the order-items list on `OrderDownload.vue`:

```
 Order Items
 ┌───────────────────────────────────────────────────────────────┐
 │  Title / Navigation         Size Variant   License    Price   │
 ├───────────────────────────────────────────────────────────────┤
 │                                                               │
 │  [State 1 – Linked]                                           │
 │  ┌───────────────────────────────────────────────────────┐    │
 │  │ 🔗 beach-sunset-2024.jpg ← RouterLink (primary-color) │    │
 │  │    MEDIUM        · PERSONAL  · $12.00                 │    │
 │  └───────────────────────────────────────────────────────┘    │
 │                                                               │
 │  [State 2 – Forbidden (album deleted, photo still exists)]    │
 │  ┌───────────────────────────────────────────────────────┐    │
 │  │ 🚫 beach-sunset-2024.jpg                              │    │
 │  │    photo_id: AbCdEfGhIj123456                         │    │
 │  │    MEDIUM        · PERSONAL  · $12.00                 │    │
 │  └───────────────────────────────────────────────────────┘    │
 │                                                               │
 │  [State 3 – Ghost (album + photo deleted)]                    │
 │  ┌───────────────────────────────────────────────────────┐    │
 │  │ *beach-sunset-2024.jpg*  ← italic + muted colour      │    │
 │  │    MEDIUM        · PERSONAL  · $12.00                 │    │
 │  └───────────────────────────────────────────────────────┘    │
 └───────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|-------------------------------|
| S-042-01 | Album exists, photo_id set → title is a `RouterLink` linking to `{ name:'album', params:{ albumId, photoId } }`. |
| S-042-02 | Album exists, photo_id null (album purchase) → title is a `RouterLink` linking to `{ name:'album', params:{ albumId } }`. |
| S-042-03 | Album deleted, photo still exists → title area shows `pi-ban` icon (red) + `photo_id` text; no broken link. |
| S-042-04 | Both album and photo deleted → title renders italic and `text-muted-color`; no link or badge. |
| S-042-05 | `album_id` is null AND `photo_id` is null (historical/album-level item) → title renders italic and muted (ghost state). |
| S-042-06 | Order with 20 line items → exactly one additional query batch (eager load of album + photo), not 40 individual queries. |

## Test Strategy

- **Application (PHP Feature tests):**
  - `OrderItemResourceTest` — asserts `album_exists` and `photo_exists` flags for all four combinations (both exist / album only / photo only / neither).
  - Verifies `OrderResource::fromModel()` eager-loads relations (query count assertion with `DB::enableQueryLog()`).
- **UI (Vue component tests):**
  - Three snapshot/selector tests on `OrderDownload.vue` (one per state) using Vitest + Vue Test Utils, verifying `RouterLink` presence/absence and CSS classes.
- **REST:**
  - Existing checkout/order feature tests remain green; add assertions for the new flags in the order JSON response.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-042-01 | `OrderItemResource` — extended with `album_exists: bool` and `photo_exists: bool` fields. | REST, UI |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-042-01 | REST GET `/api/v2/Shop/Order/{id}` | Response payload now includes `album_exists` and `photo_exists` on each item. | Additive change; no breaking modification. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-042-01 | Linked | `item.album_exists === true` → `RouterLink` to album/photo. |
| UI-042-02 | Forbidden | `item.album_exists === false && item.photo_exists === true` → red `pi-ban` icon + `photo_id` text. |
| UI-042-03 | Ghost | `item.album_exists === false && item.photo_exists === false` (or both IDs null) → italic muted title. |

## Telemetry & Observability

No new telemetry events required. The new flags are computed at query time from existing relations.

## Documentation Deliverables

- Update `docs/specs/4-architecture/roadmap.md` — add Feature 042 to Active Features.
- Update `docs/specs/4-architecture/knowledge-map.md` — note the `OrderItemResource` extension under Shop Implementation.

## Fixtures & Sample Data

No new test-vector fixtures required. Existing `OrderItemFactory` and seeded test data cover the scenarios; specific relation presence/absence is controlled inline in the tests via factories.

## Spec DSL

```yaml
domain_objects:
  - id: DO-042-01
    name: OrderItemResource
    fields:
      - name: album_exists
        type: bool
        constraints: "true when album relation is non-null after eager load"
      - name: photo_exists
        type: bool
        constraints: "true when photo relation is non-null after eager load"
routes:
  - id: API-042-01
    method: GET
    path: /api/v2/Shop/Order/{id}
ui_states:
  - id: UI-042-01
    description: Linked — RouterLink to album/photo
  - id: UI-042-02
    description: Forbidden — pi-ban icon + photo_id text
  - id: UI-042-03
    description: Ghost — italic muted title
```

## Appendix

### Existing code note

`OrderDownload.vue` line 118 (as of 2026-05-31) already wraps the title in a `RouterLink` but does not guard for null `album_id` or deleted albums/photos. This feature replaces that unconditional link with the three-state conditional (FR-042-03 through FR-042-05).

`OrderResource::fromModel()` conditionally loads `items.size_variant` only when the order status is `CLOSED`. The new eager-load of `items.album` and `items.photo` should be applied whenever items are loaded (not gated on status) so the existence flags are always accurate.
