# Feature 025 – Dynamic Landing Background Options

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2025-01-17 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/025-dynamic-landing-backgrounds/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/025-dynamic-landing-backgrounds/tasks.md` |
| Roadmap entry | #025 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The landing page currently displays static background images configured via `landing_background_landscape` and `landing_background_portrait` URL strings. Users want dynamic backgrounds that showcase their photo collections: random public images, latest album covers, and random images from specific albums. This feature adds mode selection enums that determine how background values are interpreted: as static URLs, photo IDs, random selection, latest album covers, or random from a specific album.

Affected modules: **Config** (`App\Models\Config`), **Landing Page** (`App\Http\Resources\GalleryConfigs\LandingPageResource`, `App\Http\Controllers\LandingPageController`), **Query** (`App\Policies\PhotoQueryPolicy`, `App\Policies\AlbumQueryPolicy`), **Frontend** (`resources/js/views/Landing.vue`).

## Goals

1. Add mode selection enums for landscape and portrait backgrounds with 5 modes: static URL, photo ID, random photo, latest album cover, random from album.
2. Allow admins to configure mode and value independently for landscape and portrait orientations.
3. Leverage existing query policies to ensure only publicly accessible photos are used for dynamic backgrounds.
4. Maintain backward compatibility with existing static URL configurations (default mode: `static`).
5. Provide fallback behavior when no public photos/albums exist or referenced IDs are invalid.

## Non-Goals

- Adding landing background preview in the admin settings UI (optional future enhancement).
- Supporting user-specific or authenticated landing backgrounds (landing page is public-only).
- Adding animation/transitions between background images on page load.
- Caching resolved dynamic images (each landing page load resolves fresh).
- Supporting multiple/slideshow backgrounds on a single landing page.
- Random album selection mode (only random photo from album and latest album cover are supported).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-025-01 | Add two new enum config keys: `landing_background_landscape_mode` and `landing_background_portrait_mode` with values: `static`, `photo_id`, `random`, `latest_album_cover`, `random_from_album`. Default: `static`. | When admin saves a mode, it is stored in the enum config. | Config validation ensures value is one of the 5 enum values. | Invalid enum values produce validation error during config update. | — | Issue #1106 |
| FR-025-02 | Existing `landing_background_landscape` and `landing_background_portrait` configs store the value (URL string, photo ID, or album ID) interpreted based on the mode enum. | Value is stored as string; interpretation depends on mode enum. | Config validation ensures value format matches mode requirement (valid ID format for `photo_id`/`random_from_album` modes). | Invalid format produces validation error. | — | Issue #1106 |
| FR-025-03 | When mode is `static`, the value is used directly as a URL (existing behaviour). | `LandingPageResource` returns the value unchanged. | Value should be a valid URL string. | If value is empty or invalid URL, gracefully fallback to default image (`dist/cat.webp`) without throwing exception. | — | Backward compatibility |
| FR-025-04 | When mode is `photo_id`, the landing page fetches the specified photo by ID and returns its URL. No public access check is performed - admin is responsible for selecting appropriate photos. | `LandingPageResource` queries `Photo::find($value)`, returns photo URL directly. | Photo ID must exist in database. | If photo doesn't exist, gracefully fallback to default image (`dist/cat.webp`) without throwing exception. | — | Issue #1106 |
| FR-025-05 | When mode is `random`, the landing page displays a random publicly accessible photo. | `LandingPageResource` queries for a random public photo using `PhotoQueryPolicy`, returns URL for appropriate size variant. | Query uses `PhotoQueryPolicy::applySearchabilityFilter()` with `user=null`. | If no public photos exist, gracefully fallback to default image (`dist/cat.webp`) without throwing exception. | — | Issue #1106 |
| FR-025-06 | When mode is `latest_album_cover`, the landing page displays the cover photo of the most recently published public album. | `LandingPageResource` queries public albums ordered by `published_at DESC, created_at DESC, id DESC`, reads cover ID, fetches photo. | Album query uses `AlbumQueryPolicy::applySearchabilityFilter()` with `user=null`. | If no public albums with covers exist, gracefully fallback to default image (`dist/cat.webp`) without throwing exception. | — | Issue #1106 |
| FR-025-07 | When mode is `random_from_album`, the landing page displays a random photo from the specified public album ID. | `LandingPageResource` verifies album exists and is public, queries photos in that album, selects random photo, returns URL. | Album must exist and be publicly accessible; photo query filtered by album and public access. | If album doesn't exist, is private, or has no public photos, gracefully fallback to default image (`dist/cat.webp`) without throwing exception. | — | Issue #1106 |
| FR-025-08 | The frontend `Landing.vue` component displays the resolved image URLs without modification (no frontend logic for mode resolution). | Frontend receives fully-resolved URLs in `landing_background_landscape` and `landing_background_portrait` fields. | — | If backend returns null/empty URL, frontend displays no background (or a CSS fallback color). | — | Separation of concerns |
| FR-025-09 | Each landing page request resolves dynamic modes fresh (no persistent caching of resolved images). | `LandingPageResource::__construct()` executes mode resolution logic every time. | — | — | — | Freshness requirement |
| FR-025-10 | Landscape and portrait orientations can use different modes and values independently. | Each orientation has its own mode enum and value config; resolution is independent. | — | — | — | Flexibility |
| FR-025-11 | Config UI in admin settings displays mode dropdown and value input field for each orientation. | Translation keys added for each mode enum value and config descriptions. | — | — | — | UX clarity |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-025-01 | Dynamic mode queries execute efficiently (≤100ms p95 latency). | Performance | Query uses indexed columns (`published_at`, `created_at`, `id`); LIMIT 1 or small random sample. | Database indexes on `photos.published_at`, `albums.published_at`. | — |
| NFR-025-02 | No regression in landing page load time when using static URL mode. | Performance | Static URL mode bypasses all query logic (string check only). | — | — |
| NFR-025-03 | Security: Only publicly accessible photos/albums are used. | Security | All queries must apply `PhotoQueryPolicy::applySearchabilityFilter()` or `AlbumQueryPolicy::applySearchabilityFilter()` with `user=null`. | `App\Policies\PhotoQueryPolicy`, `App\Policies\AlbumQueryPolicy` | Privacy requirement |
| NFR-025-04 | Backward compatibility: Existing installations with static URLs continue working unchanged. | Stability | Migration sets default mode to `static` and preserves existing URL values; existing behaviour unchanged. | — | — |
| NFR-025-05 | Graceful fallback when no public content exists or resources not found. | Resilience | All modes return valid URL; never throw exceptions for missing photos/albums. Default fallback URL is `dist/cat.webp` (existing demo image). Resolution method guarantees non-null, non-empty string return value. | Public assets available. | — |
| NFR-025-06 | New code follows PSR-4, strict comparisons, no `empty()`, `in_array()` with `true` third arg, snake_case variables. | Coding conventions | `vendor/bin/php-cs-fixer fix` + `make phpstan` both pass. | — | AGENTS.md |

## UI / Interaction Mock-ups

### Admin Config UI (Settings > Landing Page)

```
┌─────────────────────────────────────────────────────────────┐
│ Landing Page Settings                                       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Background Image (Landscape):                               │
│ Mode: ┌───────────────────────────────────────────────┐    │
│       │ Random photo                                 ▼│    │
│       └───────────────────────────────────────────────┘    │
│   ↳ Options: Static URL, Photo ID, Random photo,           │
│               Latest album cover, Random from album        │
│                                                             │
│ Value: ┌──────────────────────────────────────────────┐    │
│        │ (auto-selected, no input needed)             │    │
│        └──────────────────────────────────────────────┘    │
│   ↳ Depends on mode: URL, photo ID, or album ID            │
│                                                             │
│ Background Image (Portrait):                                │
│ Mode: ┌───────────────────────────────────────────────┐    │
│       │ Static URL                                   ▼│    │
│       └───────────────────────────────────────────────┘    │
│                                                             │
│ Value: ┌──────────────────────────────────────────────┐    │
│        │ dist/cat.webp                                 │    │
│        └──────────────────────────────────────────────┘    │
│   ↳ Enter URL (required for static mode)                   │
│                                                             │
│ [Save]                                                      │
└─────────────────────────────────────────────────────────────┘
```

**Notes:**
- Mode dropdown shows human-readable labels (translated):
  - "Static URL" — uses the value field as a direct URL
  - "Photo ID" — fetches a specific photo by ID from value field
  - "Random photo" — selects a random public photo (value field disabled/hidden)
  - "Latest album cover" — uses the latest album's cover (value field disabled/hidden)
  - "Random from album" — selects random photo from album ID in value field
- Value field behavior changes based on mode:
  - `static`: Text input for URL (required)
  - `photo_id`: Text input for photo ID (required, validated as valid ID format)
  - `random`: Disabled/hidden (no value needed)
  - `latest_album_cover`: Disabled/hidden (no value needed)
  - `random_from_album`: Text input for album ID (required, validated as valid ID format)
- Validation errors shown for invalid mode/value combinations.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|-------------------------------|
| S-025-01 | Existing static URL (mode=`static`, value=`dist/cat.webp`) — landing page displays the static image unchanged (backward compatibility). |
| S-025-02 | Mode=`random` with 10 public photos — landing page displays a random photo from the 10. |
| S-025-03 | Mode=`random` with no public photos — landing page displays fallback image (`dist/cat.webp`). |
| S-025-04 | Mode=`photo_id` with valid photo ID (public or private) — landing page displays the specified photo. |
| S-025-05 | Mode=`photo_id` with non-existent photo ID — landing page displays fallback image. |
| S-025-06 | Mode=`latest_album_cover` with 5 public albums — landing page displays the cover of the most recently published album. |
| S-025-07 | Mode=`latest_album_cover` with public albums but no covers set — landing page falls back to albums' `auto_cover_id_least_privilege` if available, else fallback image. |
| S-025-08 | Mode=`random_from_album` with valid public album ID containing 20 photos — landing page displays a random photo from that album. |
| S-025-09 | Mode=`random_from_album` with private or empty album ID — landing page displays fallback image. |
| S-025-10 | Mixed modes: landscape mode=`random`, portrait mode=`latest_album_cover` — both orientations resolve independently and display different images. |
| S-025-11 | Admin saves config with invalid mode enum value — validation error returned, config not saved. |
| S-025-12 | Landing page requested multiple times with mode=`random` — each request returns a fresh resolution (no caching). |
| S-025-13 | Guest user accesses landing page with mode=`random` — query filters ensure only public photos are candidates (no private photos leak). |

## Test Strategy

- **Core/Application:** Unit tests for dynamic mode resolution logic (mock queries, verify correct filters applied).
- **REST:** Feature tests for `LandingPageController` — verify each mode returns expected photo URLs.
- **CLI:** Not applicable (no CLI changes).
- **UI (JS):** Not applicable (frontend passively displays resolved URLs).
- **Integration:** End-to-end tests creating public/private photos/albums and verifying correct images are resolved for each mode.
- **Security:** Tests ensuring private photos/albums never appear in dynamic backgrounds for guest users.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-025-01 | `LandingPageResource` adds helper method `resolveBackgroundUrl(string $config_value): string` that accepts a config value and returns a fully-resolved image URL. | `App\Http\Resources\GalleryConfigs\LandingPageResource` |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-025-01 | GET /api/Init::landing | Existing endpoint; response includes `landing_background_landscape` and `landing_background_portrait` with resolved URLs. | No schema change (still returns strings). |

### Database Migrations

| ID | Description |
|----|-------------|
| MIG-025-01 | Add two new enum config keys: `landing_background_landscape_mode` and `landing_background_portrait_mode` with enum type `static|photo_id|random|latest_album_cover|random_from_album`, default `static`. |
| MIG-025-02 | Update `landing_background_landscape` and `landing_background_portrait` config rows: update `details` field to reference the new mode configs. |

### Translation Keys

| ID | Key | Description |
|----|-----|-------------|
| TRANS-025-01 | `all_settings.details.landing_background_landscape_mode` | Description for landscape mode enum config. |
| TRANS-025-02 | `all_settings.details.landing_background_portrait_mode` | Description for portrait mode enum config. |
| TRANS-025-03 | `all_settings.details.landing_background_landscape` | Update description to reference mode config. |
| TRANS-025-04 | `all_settings.details.landing_background_portrait` | Update description to reference mode config. |

## Telemetry & Observability

No new telemetry events. Landing page requests are already logged. If mode resolution fails (no public content), existing exception/logging infrastructure captures warnings.

## Documentation Deliverables

- Update roadmap (`docs/specs/4-architecture/roadmap.md`) — add Feature 025.
- Update knowledge map if landing page module is documented.
- Update `_current-session.md`.
- Optional: Add user documentation explaining dynamic landing background modes.

## Fixtures & Sample Data

Existing test helpers (photos, albums with public access permissions) are sufficient for integration tests.

## Spec DSL

```yaml
domain_objects:
  - id: DO-025-01
    name: LandingPageResource
    methods:
      - name: resolveBackgroundUrl
        parameters:
          - name: config_value
            type: string
        returns: string
        description: "Resolves static URLs or dynamic mode keywords to an image URL."

routes:
  - id: API-025-01
    method: GET
    path: /api/Init::landing
    response_fields:
      - landing_background_landscape: string (resolved URL)
      - landing_background_portrait: string (resolved URL)

migrations:
  - id: MIG-025-01
    description: "Add landing_background_landscape_mode and landing_background_portrait_mode enum configs."

  - id: MIG-025-02
    description: "Update config details for landing_background_landscape and landing_background_portrait to reference mode configs."

translation_keys:
  - id: TRANS-025-01
    key: all_settings.details.landing_background_landscape_mode
    languages: [en, de, fr, es, ja, zh_CN, ...] # 22 languages

  - id: TRANS-025-02
    key: all_settings.details.landing_background_portrait_mode
    languages: [en, de, fr, es, ja, zh_CN, ...] # 22 languages

  - id: TRANS-025-03
    key: all_settings.details.landing_background_landscape
    languages: [en, de, fr, es, ja, zh_CN, ...] # 22 languages

  - id: TRANS-025-04
    key: all_settings.details.landing_background_portrait
    languages: [en, de, fr, es, ja, zh_CN, ...] # 22 languages
```

## Appendix

### Related Issue

GitHub issue #1106: [Additional 'landing_background' config options](https://github.com/LycheeOrg/Lychee/issues/1106)

Requested modes:
- random (public) image
- cover photo of random (public) album
- latest (public) image
- cover photo of latest (public) album

### Mode Enum Values

| Enum Value | Behaviour | Value Field Usage |
|------------|-----------|-------------------|
| `static` | Uses the value directly as a URL (existing behaviour). | Required: URL string |
| `photo_id` | Fetches a specific photo by ID and returns its URL. No public access check - admin controls which photo to display. | Required: Photo ID |
| `random` | Selects a random publicly accessible photo. | Ignored (not used) |
| `latest_album_cover` | Selects the cover of the most recently published public album. | Ignored (not used) |
| `random_from_album` | Selects a random photo from the specified album. | Required: Album ID |

### Fallback Logic

**All dynamic mode resolution is graceful - no exceptions thrown for missing resources.**

When a dynamic mode is configured but no eligible content exists (photo not found, album not found, album is private, no public photos available):
1. Return `dist/cat.webp` (default demo image) - never return null or empty string.
2. Frontend displays the fallback image (no error shown to user).
3. Backend logs a notice (optional): "Falling back to default image for landing background mode: {mode}, reason: {reason}".
4. The `resolveBackgroundUrl()` method guarantees to always return a valid URL string.

**This applies to all failure scenarios:**
- `photo_id` mode with non-existent or invalid photo ID → cat picture
- `random` mode with zero public photos → cat picture
- `latest_album_cover` mode with no public albums or albums without covers → cat picture
- `random_from_album` mode with non-existent, private, or empty album → cat picture
- `static` mode with empty/invalid URL → cat picture

### Query Policy Integration

All dynamic mode queries MUST use:
- `PhotoQueryPolicy::applySearchabilityFilter($query, user: null, unlocked_album_ids: [])` for photos.
- `AlbumQueryPolicy::applySearchabilityFilter($query, user: null)` for albums.

This ensures only publicly accessible content (no authentication required) is used.

### Size Variant Selection

For dynamic photos, use:
- Desktop/large screens: `medium` or `large` size variant (prefer larger for better quality).
- Fallback: `original` if no downsized variants exist.
- Optimization: Consider using `medium2x` for high-DPI displays.

Query includes `with(['size_variants'])` to eager-load variants.

### Album Cover Resolution

For album cover modes:
1. Query public albums using `AlbumQueryPolicy`.
2. Select album (random or latest by ordering).
3. Read cover ID: prefer explicit `cover_id`, fallback to `auto_cover_id_least_privilege` (pre-computed for public view).
4. If `auto_cover_id_least_privilege` is null (empty album or no public photos), skip album and try next (or fallback).
5. Load photo by cover ID, return URL.

### Random Selection Strategy

For `random` and `random_from_album` modes:
- **Simple random (initial implementation):** Query all eligible photos, use `RAND()` or `RANDOM()` SQL function, `LIMIT 1`.
- **Optimization (future):** If query result set is large (>1000 records), use offset-based random sampling to reduce query cost.

### Photo ID and Album ID Validation

For `photo_id` and `random_from_album` modes:
- Value field must be a valid ID string (24-character random ID format used by Lychee).
- Backend validates ID format before querying database.
- Invalid format returns validation error during config update.
- Valid format but non-existent resource triggers fallback during resolution (no error shown to end user).

**Note on photo_id mode:** No public accessibility check is performed. Admins are responsible for selecting appropriate photos. This allows admins to use private photos as landing backgrounds if desired (e.g., for logged-in user landing pages in future enhancements).

### Configuration Migration Strategy

**Backward compatible migration:**
1. Add two new enum config keys: `landing_background_landscape_mode` and `landing_background_portrait_mode` with default value `static`.
2. Existing `landing_background_landscape` and `landing_background_portrait` URL values remain unchanged.
3. With default mode `static`, existing installations continue working unchanged (URLs used directly).
4. Admins can change mode via settings UI to enable dynamic backgrounds.

**Migration adds:**
- Two new enum config rows with type_range `static|photo_id|random|latest_album_cover|random_from_album`.
- Update `details` field for existing configs to reference the new mode configs.

---

*Last updated: 2025-01-17*
