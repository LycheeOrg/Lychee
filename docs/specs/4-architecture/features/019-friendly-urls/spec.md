# Feature 019 – Friendly URLs (Album Slugs)

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-27 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/019-friendly-urls/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/019-friendly-urls/tasks.md` |
| Roadmap entry | #019 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

**GitHub issue:** [LycheeOrg/Lychee#330](https://github.com/LycheeOrg/Lychee/issues/330)

## Overview

Albums and tag albums in Lychee are currently identified exclusively by opaque 24-character random Base64 IDs (e.g., `ePN3Y_kA16KtZGXmxv-kdBrg`). This makes shared links cryptic and unfriendly. This feature adds an optional **slug** (alias) column to albums so they can be accessed via human-readable URLs such as `/gallery/my-vacation-2025` instead of `/gallery/ePN3Y_kA16KtZGXmxv-kdBrg`.

**Affected modules:** Database (migration on `base_albums`), Models (`BaseAlbumImpl`), HTTP Middleware (`ResolveAlbumSlug`), REST API (album resolution), UI (album edit forms, slug input). Validation rules (`AlbumIDRule`, `RandomIDRule`) and `AlbumFactory` remain **unchanged** — the middleware translates slugs to real IDs before the request reaches validation.

## Goals

- Allow album owners to assign an optional, unique, URL-safe slug to any album or tag album.
- Resolve albums by slug transparently — existing ID-based URLs continue to work unchanged.
- Provide a UI field in the album sidebar/edit panel to set or clear the slug.
- Offer a one-click auto-generate button that slugifies the album title as a starting point.
- Display the full friendly URL in the sidebar so users can copy it easily.
- Ensure slug uniqueness is enforced globally at both the database and application layers.
- Prevent collisions with SmartAlbum type identifiers (`unsorted`, `recent`, `highlighted`, `on_this_day`, etc.).

## Non-Goals

- **Hierarchical/nested slug paths** (e.g., `/gallery/parent/child`) — slugs are flat, globally unique strings (Q-019-01 resolved: Option A). No dependency on parent structure; renaming/moving a parent doesn't invalidate child slugs.
- **Mandatory slugs** — slugs are entirely optional; albums without slugs continue to use their random ID.
- **Photo slugs** — only albums (Album + TagAlbum) are in scope.
- **Auto-redirect from old ID to slug** — both the ID and slug resolve to the same album; no HTTP redirects are issued.
- **Custom top-level routes** (e.g., `/my-album` without `/gallery/` prefix) — slugs only work within the existing `/gallery/{slug}` route pattern (Q-019-02 resolved: Option A). No collision risk with named routes; no route definition changes required.
- **Slug versioning or history** — renaming a slug produces no redirect from the old slug. The middleware architecture makes a future `slug_history` table with 301 redirects a natural extension, but this is out of scope for the initial implementation.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-019-01 | **Slug column on `base_albums`** — Add nullable, unique `slug` column (VARCHAR 250) to the `base_albums` table. | Migration adds column with unique index. Null values allowed (most albums will have no slug). | Column validated as nullable string, max 250 chars, unique. | Migration rolls back cleanly on failure. | N/A | [Issue #330](https://github.com/LycheeOrg/Lychee/issues/330) |
| FR-019-02 | **Slug format validation** — Slugs must be lowercase, consist of ASCII alphanumeric characters, hyphens, and underscores only. Must start with a letter. Min length 2, max length 250. | Slug `my-vacation-2025` accepted. | Rejects: empty string, uppercase (`My-Album`), special chars (`café`), leading digit (`2025-trip`), leading hyphen (`-album`), single char (`a`). Returns 422 with descriptive error. | N/A | N/A | URL safety, RFC 3986 unreserved chars |
| FR-019-03 | **Slug uniqueness** — Slugs are globally unique across all albums (Album + TagAlbum). | Setting slug `summer-photos` succeeds when no other album has that slug. | Rejects duplicate slug with 422 error: "This slug is already in use." | Database unique constraint prevents race conditions. | N/A | [Issue #330 discussion](https://github.com/LycheeOrg/Lychee/issues/330) |
| FR-019-04 | **Reserved slug protection** — Slugs must not collide with SmartAlbum type identifiers or other reserved words. | Slug `my-album` accepted (not reserved). | Rejects slugs matching SmartAlbumType values (`unsorted`, `recent`, `highlighted`, `on_this_day`, `my-rated-pictures`, `my-best-pictures`) and route segments (`settings`, `profile`, `login`, `register`). Returns 422: "This slug is reserved." | N/A | N/A | SmartAlbumType enum, route collision prevention |
| FR-019-05 | **Slug-to-ID middleware** — A `ResolveAlbumSlug` middleware intercepts requests **before** validation. For each `album_id` (query param, JSON body, or route param), if the value is not a 24-char random ID and not a SmartAlbumType value, the middleware looks up `base_albums.slug` and replaces the value with the real ID. Route parameters are resolved under **both** spellings in use across the routing surface: `albumId` (camelCase, `routes/web_v2.php`) and `album_id` (snake_case, the whole `/api/v3/...` surface plus v2 routes such as `/Album/{album_id}/people`). This second pass is mandatory rather than redundant: `Request::input()` reads the query string and body only and never sees route parameters, so a route segment is invisible to the query/body pass. If no match, the value passes through unchanged and normal 404 handling applies downstream. This keeps `AlbumIDRule`, `RandomIDRule`, and `AlbumFactory` completely unchanged. | `/gallery/my-vacation` → middleware rewrites to `/gallery/{real_id}` → request validation and factory see a standard 24-char ID. | If slug not found in DB, value passes through; downstream validation/factory returns 404. | N/A | N/A | Separation of concerns: slug translation is a transport/HTTP concern |
| FR-019-06 | **API accepts slug as album identifier** — All API endpoints that accept `album_id` also accept a slug value transparently, via the middleware registered on the `api` middleware group — this covers `/api/v2` and `/api/v3` alike, whether the identifier arrives as a query parameter or as a `{album_id}` route segment. Routes that opt out of the `api` group (notably `/Embed/{album_id}`, which uses `withoutMiddleware('api')`) do **not** resolve slugs; they are out of scope for this feature. No changes to individual request classes or controllers. | `GET /Album?album_id=my-vacation` → middleware rewrites to `album_id={real_id}` → returns the album. | Invalid slug/ID returns 404 from downstream validation. | N/A | N/A | API consistency |
| FR-019-07 | **Set slug via API** — New endpoint or extension to existing album edit endpoint to set/clear the slug. | `PATCH /Album` with `slug` field updates the album slug. Setting to `null` or empty string clears it. | Validates format (FR-019-02), uniqueness (FR-019-03), reserved words (FR-019-04). Returns 422 on failure. | 403 if user lacks edit permission. | N/A | User requirement |
| FR-019-08 | **UI slug field in album sidebar** — Album edit/info panel includes a text input for the slug with auto-generate button. | User types or auto-generates slug, saves. Sidebar shows the full friendly URL for copy. | Client-side validation mirrors FR-019-02. Error messages displayed inline. | Server-side validation catches duplicates/reserved words. | N/A | UX requirement |
| FR-019-09 | **Auto-generate slug from title** — A button next to the slug input slugifies the current album title as a starting point. | "My Vacation & Adventures 2025" → `my-vacation-and-adventures-2025`. User can edit before saving. | If title produces an empty or invalid slug (e.g., title is all special chars), show a warning and leave the field empty for manual entry. | N/A | N/A | Convenience, [Issue #330](https://github.com/LycheeOrg/Lychee/issues/330) |
| FR-019-10 | **Vue Router uses slug in URL** — When an album has a slug, the frontend navigates to `/gallery/{slug}` instead of `/gallery/{id}`. Both forms continue to work. | Clicking an album with slug `summer-photos` navigates to `/gallery/summer-photos`. Browser URL bar shows the friendly URL. | Direct navigation to `/gallery/{id}` still works even if album has a slug. | N/A | N/A | UX requirement |
| FR-019-11 | **Authorization unchanged** — Slug resolution does not bypass access controls. The resolved album still goes through the same policy checks. | Public album accessible by slug. Private album returns 403 by slug, same as by ID. | N/A | 401/403 if user lacks permission. | N/A | Security requirement |
| FR-019-12 | **Album data includes slug in API responses** — Album resource/DTO includes the `slug` field (nullable string). | GET /Album response includes `"slug": "my-vacation"` or `"slug": null`. | N/A | N/A | N/A | API contract |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-019-01 | Slug lookup adds negligible latency | Performance | Slug lookup via indexed column should add <5ms to album resolution. Unique B-tree index on `base_albums.slug`. | Database index | Performance standard |
| NFR-019-02 | Backward compatibility — all existing ID-based URLs continue to work | API stability | No existing tests break. All current `/gallery/{id}` and `album_id={id}` patterns resolve identically. Middleware passes 24-char IDs and SmartAlbumType values through without any DB lookup. | Middleware format check (length + SmartAlbumType) | API contract |
| NFR-019-03 | Code follows Lychee PHP conventions | Maintainability | License headers, snake_case variables, strict comparison (===), PSR-4, no `empty()`, `in_array(..., true)`. | php-cs-fixer, phpstan level 6 | [coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-019-04 | Frontend follows Vue3/TypeScript conventions | Maintainability | Template-first, Composition API, `.then()` (no async/await), regular function declarations, axios in services. | Prettier, eslint | [coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-019-05 | Test coverage for slug CRUD and resolution | Quality | Feature tests for: slug set/clear, uniqueness violation, reserved word rejection, format validation, resolution by slug (query param **and** `{album_id}` route segment, the latter covered per v3 route family by `tests/Feature_v3/Album/AlbumSlugV3Test.php`), authorization. Unit tests for slug validation rule and slugify helper. | BaseApiWithDataTest, in-memory SQLite | Testing standard |
| NFR-019-06 | Database migration is reversible | Operability | Down migration drops the `slug` column cleanly. | Laravel migration framework | Deployment standard |

## UI / Interaction Mock-ups

### 1. Album Sidebar — Slug Field

```
┌─────────────────────────────────────────────────────────┐
│  Album Information                                 [✕]  │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Title                                                  │
│  ┌───────────────────────────────────────────────┐      │
│  │  My Vacation & Adventures 2025                │      │
│  └───────────────────────────────────────────────┘      │
│                                                         │
│  Description                                            │
│  ┌───────────────────────────────────────────────┐      │
│  │  Photos from our summer trip...               │      │
│  └───────────────────────────────────────────────┘      │
│                                                         │
│  Friendly URL (slug)                              [⟳]   │  ← Auto-generate button
│  ┌───────────────────────────────────────────────┐      │
│  │  my-vacation-and-adventures-2025              │      │
│  └───────────────────────────────────────────────┘      │
│  https://example.com/gallery/my-vacation-and-…          │  ← Copy-friendly URL preview
│                                                         │
│  License                                                │
│  ┌───────────────────────────────────────────────┐      │
│  │  None                                    ▼    │      │
│  └───────────────────────────────────────────────┘      │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  [ Cancel ]                              [ Save ]       │
└─────────────────────────────────────────────────────────┘
```

### 2. Slug Validation Error

```
┌─────────────────────────────────────────────────────────┐
│  Friendly URL (slug)                              [⟳]   │
│  ┌───────────────────────────────────────────────┐      │
│  │  My Album!                                    │      │  ← Invalid input
│  └───────────────────────────────────────────────┘      │
│  ⚠ Slug must be lowercase with only letters,            │
│    numbers, hyphens, and underscores.                   │
└─────────────────────────────────────────────────────────┘
```

### 3. Slug Already Taken

```
┌─────────────────────────────────────────────────────────┐
│  Friendly URL (slug)                              [⟳]   │
│  ┌───────────────────────────────────────────────┐      │
│  │  summer-photos                                │      │
│  └───────────────────────────────────────────────┘      │
│  ⚠ This slug is already in use by another album.        │
└─────────────────────────────────────────────────────────┘
```

### 4. Gallery URL Bar — Friendly URL Active

```
┌─────────────────────────────────────────────────────────────────┐
│ 🔒 example.com/gallery/my-vacation-and-adventures-2025          │
└─────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-019-01 | Owner sets slug on album: Album updated, sidebar shows friendly URL, navigating to `/gallery/{slug}` loads the album |
| S-019-02 | Owner clears slug: Album slug removed, album accessible only by ID |
| S-019-03 | Owner sets duplicate slug: 422 error, "This slug is already in use" |
| S-019-04 | Owner sets reserved slug (`recent`, `unsorted`): 422 error, "This slug is reserved" |
| S-019-05 | Owner sets invalid slug (uppercase, special chars): 422 error with format message |
| S-019-06 | User without edit permission tries to set slug: 403 Forbidden |
| S-019-07 | Unauthenticated user tries to set slug: 401 Unauthorized |
| S-019-08 | Navigate to `/gallery/{slug}`: Album resolves and loads normally |
| S-019-09 | Navigate to `/gallery/{id}` for album that has slug: Album loads (no redirect) |
| S-019-10 | Navigate to `/gallery/{nonexistent-slug}`: 404 Not Found |
| S-019-11 | API `GET /Album?album_id={slug}`: Returns album data |
| S-019-12 | Auto-generate slug from title: Title slugified, user can edit before saving |
| S-019-13 | Auto-generate slug from title with special chars only: Warning shown, field left empty |
| S-019-14 | Set slug on tag album: Works identically to regular album |
| S-019-15 | Album with slug appears in album listing: `slug` field included in API response |
| S-019-16 | Frontend navigates using slug when available: URL bar shows `/gallery/{slug}` |

## Test Strategy

- **Core/Application:**
  - Unit tests for slug format validation rule (valid patterns, invalid patterns, edge cases)
  - Unit test for slugify helper (title → slug conversion, special chars, unicode)
  - Unit test for reserved word check against SmartAlbumType values
- **Middleware:**
  - 24-char ID passes through untouched (no DB query)
  - SmartAlbumType value passes through untouched (no DB query)
  - Valid slug resolved to real ID in request
  - Unknown slug passes through (404 downstream)
  - Array of mixed slugs/IDs: each element resolved independently
- **REST (Feature tests):**
  - Set slug on album (204)
  - Set slug on tag album (204)
  - Clear slug (204)
  - Duplicate slug (422)
  - Reserved slug (422)
  - Invalid format (422)
  - Unauthorized (401) and forbidden (403)
  - Resolve album by slug via `GET /Album` (200)
  - Resolve album by ID still works when slug set (200)
  - Non-existent slug returns 404
  - Album response includes `slug` field
- **UI (JS):**
  - Slug input renders in album sidebar
  - Auto-generate button produces slugified title
  - Client-side validation messages for invalid input
  - Friendly URL preview displays correctly
  - Router navigates with slug when available
- **Docs/Contracts:** API response schema updated with `slug` field

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-019-01 | `base_albums.slug` — nullable VARCHAR(250) with unique index | Database, BaseAlbumImpl model |
| DO-019-02 | `SlugRule` — Custom validation rule enforcing FR-019-02 format + FR-019-04 reserved words (used only when *setting* a slug, not during resolution) | Application (Rules) |
| DO-019-03 | Slugify helper — Converts album title to URL-safe slug string | Application (helper or service) |
| DO-019-04 | `ResolveAlbumSlug` middleware — Translates slug values to real album IDs in the request before validation runs. Checks: if value is 24-char random ID or SmartAlbumType → pass through; otherwise query `base_albums.slug` → replace with real ID or pass through unchanged. Registered on album route groups. | HTTP Middleware |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-019-01 | PATCH /Album (extended) | Accepts optional `slug` field to set/clear album slug | Extends existing album update endpoint |
| API-019-02 | GET /Album?album_id={slug} | Resolves album by slug (or ID, as before) | No new endpoint — `ResolveAlbumSlug` middleware translates slug to ID before validation; `AlbumFactory` unchanged |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-019-01 | Slug field empty (no slug set) | Default — field blank, no friendly URL preview shown |
| UI-019-02 | Slug field populated | User typed or auto-generated slug; friendly URL preview shown below input |
| UI-019-03 | Slug validation error | Invalid format entered; inline error message displayed |
| UI-019-04 | Slug uniqueness error | Server returned 422 on save; inline error "already in use" |
| UI-019-05 | Auto-generate clicked | Title slugified into the input field; user can edit before saving |
| UI-019-06 | Auto-generate produces empty result | Warning: "Title cannot be converted to a slug. Please enter one manually." |

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-019-01 | Test seeder data | Albums with and without slugs for feature test scenarios |

## Telemetry & Observability

No custom telemetry events. Standard Laravel logging covers:
- Database query performance for slug lookups (via query log / Clockwork)
- 422 validation errors logged at debug level
- 404s for non-existent slugs follow existing error handling

## Documentation Deliverables

- Roadmap entry updated with feature 019
- Knowledge map updated: `base_albums.slug` column, `ResolveAlbumSlug` middleware, `SlugRule`
- Translation keys added to all 22 language files for slug field label, placeholder, validation messages, auto-generate button tooltip

## Spec DSL

```yaml
domain_objects:
  - id: DO-019-01
    name: base_albums.slug
    type: VARCHAR(250)
    constraints: "nullable, unique, lowercase, alphanumeric + hyphens + underscores, starts with letter, min 2, max 250"
  - id: DO-019-02
    name: SlugRule
    type: validation_rule
    constraints: "format check + reserved word check (used when setting slug, not during resolution)"
  - id: DO-019-03
    name: SlugifyHelper
    type: utility
    behaviour: "title string → URL-safe slug"
  - id: DO-019-04
    name: ResolveAlbumSlug
    type: middleware
    behaviour: "translates slug → real ID in request before validation; passes through 24-char IDs and SmartAlbumType values unchanged"
routes:
  - id: API-019-01
    method: PATCH
    path: /Album
    params: { slug: "string|nullable" }
  - id: API-019-02
    method: GET
    path: /Album
    params: { album_id: "string (ID or slug)" }
ui_states:
  - id: UI-019-01
    description: "Slug field empty"
  - id: UI-019-02
    description: "Slug field populated with preview"
  - id: UI-019-03
    description: "Slug format validation error"
  - id: UI-019-04
    description: "Slug uniqueness error"
  - id: UI-019-05
    description: "Auto-generate successful"
  - id: UI-019-06
    description: "Auto-generate empty result warning"
fixtures:
  - id: FX-019-01
    path: "test seeders"
    purpose: "Albums with/without slugs"
```

## Appendix

### A. Slug Format Examples

| Input title | Auto-generated slug | Valid? |
|-------------|-------------------|--------|
| My Vacation 2025 | `my-vacation-2025` | ✓ |
| Haddenham Steam Rally | `haddenham-steam-rally` | ✓ |
| Cats & Cocktails | `cats-and-cocktails` | ✓ |
| Landmark - 25 | `landmark-25` | ✓ |
| Architecture | `architecture` | ✓ |
| 2025 Trip | `trip-2025` (auto-gen skips leading digits) | ✓ (after adjustment) |
| *** | _(empty — cannot slugify)_ | ✗ |

### B. Reserved Slugs (derived from SmartAlbumType + route segments)

- `unsorted`, `recent`, `highlighted`, `on_this_day`
- `my-rated-pictures`, `my-best-pictures`
- `settings`, `profile`, `login`, `register`, `diagnostics`, `home`
- `users`, `sharing`, `jobs`, `maintenance`

### C. Middleware Resolution Flow (`ResolveAlbumSlug`)

```
Request arrives with album_id (query param, route param, or array element)
  ┌─ Is it exactly 24 chars (RandomID length)?  → Pass through unchanged
  ├─ Is it a SmartAlbumType value?               → Pass through unchanged
  └─ Otherwise:
       Query: SELECT id FROM base_albums WHERE slug = :value
       ├─ Found  → Replace value in request with the real ID
       └─ Not found → Pass through unchanged (will 404 downstream)
```

The existing `AlbumFactory` resolution order is **unchanged**:
```
  1. SmartAlbumType::tryFrom(album_id) → smart album
  2. Album::find(album_id)             → regular album
  3. TagAlbum::find(album_id)          → tag album
  4. Throw ModelNotFoundException       → 404
```

Because Lychee IDs are 24-char Base64 strings that can contain uppercase, `+`, `/`, and `=`, while slugs are restricted to lowercase + hyphens + underscores, there is **zero chance of collisions** between a valid slug and a valid random ID. The middleware's length check (`strlen === 24`) is sufficient to distinguish the two formats.

### D. Why Middleware (Not AlbumFactory)

| Concern | Middleware | Factory modification |
|---------|-----------|---------------------|
| Separation of concerns | Slug translation is a transport/HTTP concern | Mixes URL aliasing into domain resolution |
| Validation rules | `AlbumIDRule`, `RandomIDRule` stay strict and unchanged | Must widen rules to accept arbitrary strings |
| Factory | Untouched — always receives real IDs | Gains slug fallback logic |
| Slug versioning/redirects | Natural extension point (301 from old slug) | Awkward to retrofit in the factory |
| DB overhead | Same — query only fires for non-ID strings | Same |
| Batch endpoints (array of IDs) | Iterates array, replaces matching slugs | Automatic but pollutes factory with slug knowledge |
