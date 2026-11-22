# Feature 117 – Google Sitemap

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-22 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #117 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Lychee has no sitemap, so search engines only find public albums by crawling the single-page app. This feature adds `GET /sitemap.xml` in the [sitemaps.org](https://www.sitemaps.org/protocol.html) 0.9 format Google reads. It lists the regular albums a guest can browse from the gallery root, and no photos. It is off by default and controlled from the admin settings. Layers: a config migration plus translations, an action built on `AlbumQueryPolicy` with `toBase()` queries, a session-less web route and controller, and a cache tag in the managed cache. The generic settings page shows the new settings, so no Vue component changes.

## Goals
- An admin can switch the sitemap on and off (`sitemap_enable`, default off).
- An admin can limit the sitemap to the gallery root and the top-level albums (`sitemap_root_only`, default off).
- An admin can keep sensitive albums out of it (`hide_nsfw_in_sitemap`, default on).
- The sitemap lists exactly the regular albums a guest can browse from the root, built without hydrating Eloquent models.

## Non-Goals
- N-117-01: photos, and image or video sitemap extensions (`<image:image>`) (owner directive).
- N-117-02: tag albums, smart albums, person albums, and the `/flow`, `/timeline`, `/map`, `/frame`, `/search` pages (Q-117-04 B).
- N-117-03: a sitemap index over several files. The protocol allows 50,000 URLs per file, and the sitemap is capped there (FR-117-08).
- N-117-04: `<changefreq>` and `<priority>`: Google ignores them.
- N-117-05: any change to `public/robots.txt` (Q-117-07 A).
- N-117-06: per-user sitemaps, or anything computed from the requester's rights.
- N-117-07: a new settings category (owner directive).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-117-01 | `sitemap_enable` setting: `0\|1`, default `0`, level 0, category Basics (`config`) next to `is_embed_enabled`. Its `details` text tells the admin to submit the gallery address followed by `/sitemap.xml` to search engines (static text: translations cannot carry `APP_URL`). | On (and FR-117-09 passes): `GET /sitemap.xml` answers 200 with the XML. | Standard config validation. | Off: 404. | None. | Owner directive, Q-117-07 A. |
| FR-117-02 | `sitemap_root_only` setting: `0\|1`, default `0`, level 0, category Basics, `required_keys = sitemap_enable`. On: the sitemap lists the gallery root and the browsable albums whose `parent_id` is `NULL`. Nested albums are left out. | Top-level browsable albums only. | Standard config validation. | — | None. | Owner directive, Q-117-01 A. |
| FR-117-03 | The sitemap is computed from a guest's view whatever the requester: `user = null`, no unlocked albums. An admin or an owner requesting it gets the same document as a guest. | Same XML for every requester. | — | — | None. | Owner directive. |
| FR-117-04 | Only browsable regular albums are listed: `AlbumQueryPolicy::applyVisibilityFilter()` and `applyBrowsabilityFilter()` from the root, guest view. This excludes private albums, password-protected albums, albums that require a direct link, and any album below one of those, for example a public album inside a private one. | Browsable albums are listed. | — | — | None. | Owner directive. |
| FR-117-05 | Photos are never listed. | — | — | — | None. | Owner directive, N-117-01. |
| FR-117-06 | Each album URL is absolute: `route('gallery', ['albumId' => …])`, with the album's slug when it has one and its ID otherwise. The gallery root (`route('gallery')`) is always the first entry, without `<lastmod>`. | Canonical URLs that `ResolveAlbumSlug` resolves. | — | — | None. | Slug support (`base_albums.slug`). |
| FR-117-07 | The response is `Content-Type: application/xml; charset=UTF-8`, with `<loc>` values XML-escaped. Any `Accept` header is accepted. | Well-formed `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`. | — | — | None. | sitemaps.org protocol. |
| FR-117-08 | At most 50,000 `<url>` entries including the root, in tree order (`albums._lft`), so the cut is deterministic. | — | — | Above the cap, the remaining albums are left out and a warning is logged when the list is computed. | `Log::warning` with the cap. | sitemaps.org protocol limit. |
| FR-117-09 | Gallery-wide locks: while `login_required` is on (with or without `login_required_root_only`) or a gallery password is set, `GET /sitemap.xml` answers 404, as if the sitemap were disabled. `GalleryLockState::isSitemapEnabled()` holds the rule, next to `isRssEnabled()` and `isEmbedEnabled()`. | — | — | 404. | None. | Q-117-02 A, ADR-117-01. |
| FR-117-10 | `hide_nsfw_in_sitemap` setting: `0\|1`, default `1`, level 0, category Sensitive (`Mod NSFW`), `required_keys = sitemap_enable`. On: albums marked sensitive and every album below them are left out, read from the stored `albums.is_recursive_nsfw` flag (origin-less check, ADR-103-01 point 3). Off: they are listed like other albums. | — | Standard config validation. | — | None. | Q-117-03 B, owner directive, ADR-103-01. |
| FR-117-11 | Only regular albums (`albums` table) are listed. Tag albums, smart albums and person albums never are, even when public. | — | — | — | None. | Q-117-04 B. |
| FR-117-12 | `<lastmod>` is the later of `base_albums.updated_at` and `albums.max_taken_at` (photos in the album and its descendants), as a W3C datetime in UTC. A `NULL` value is skipped. A `max_taken_at` later than the generation time (wrong camera clock) is skipped. Both skipped: no `<lastmod>`. | — | — | — | None. | Q-117-05 B (amended by the owner). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-117-01 | The album query ends in `toBase()` and selects only `albums.id`, `base_albums.slug`, `base_albums.updated_at`, `albums.max_taken_at`. No Eloquent model is hydrated. The sensitivity filter reads `albums.is_recursive_nsfw` on the row, with no sub-query. | Performance on large galleries. | Code review. | `AlbumQueryPolicy`. | Owner directive. |
| NFR-117-02 | The album rows are cached in `ManagedCacheService` under one key per (`sitemap_root_only`, `hide_nsfw_in_sitemap`) pair, tagged with `CacheKeyProvider::sitemapTag()` and `albumListingGlobalTag()`. `ManagedCacheAlbumListingInvalidator` flushes `sitemapTag()` on album saved, deleted, moved (children changed), access permission changed, computed data updated (photo added or removed), and base album removed. The XML is rendered per request from the cached rows. The response carries `Cache-Control: public, max-age=3600`. | Public, unauthenticated endpoint with an O(n²) worst-case filter. | `ManagedCacheAlbumListingInvalidatorTest` covers each flush. A feature test asserts the header. | `ManagedCacheService`, `CacheKeyProvider`, `ManagedCacheAlbumListingInvalidator`. | Q-117-06 A, ADR-117-01. |
| NFR-117-03 | No change to `public/robots.txt`. Discovery is by submitting the URL given in the `sitemap_enable` details text. | SEO. | Review. | — | Q-117-07 A. |
| NFR-117-04 | The route runs without a session: no `StartSession`, no cookies, no CSRF, no `accept_content_type:html`. It has its own middleware group registered before `web`. | A publicly cacheable response must not carry `Set-Cookie`. Crawlers send `Accept: application/xml` or `*/*`. | A feature test asserts no `Set-Cookie` header and a 200 with `Accept: application/xml`. | `App\Http\Kernel`, `RouteServiceProvider`. | ADR-117-01. |

## UI / Interaction Mock-ups
The settings are rendered by the generic admin settings page. No Vue changes.

```
Admin › Settings › Basics
┌──────────────────────────────────────────────────────────────┐
│ …                                                            │
│ Enable embeds                                          [ off ]│
│ Enable the sitemap                                     [ off ]│
│   Lists the albums a guest can browse, without photos.       │
│   Submit <gallery URL>/sitemap.xml to search engines.        │
│ Only list the gallery root and top-level albums        [ off ]│
│   (shown only when the sitemap is enabled)                   │
└──────────────────────────────────────────────────────────────┘

Admin › Settings › Sensitive
┌──────────────────────────────────────────────────────────────┐
│ …                                                            │
│ Do not list sensitive albums in the sitemap            [ on  ]│
│   (shown only when the sitemap is enabled)                   │
└──────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-117-01 | `sitemap_enable` off: 404. |
| S-117-02 | On, public root album and its public child: both listed, root URL first. |
| S-117-03 | Private album (no public permission): not listed. |
| S-117-04 | Password-protected public album: not listed, nor its children. |
| S-117-05 | Public album that requires a link: not listed, nor its children. |
| S-117-06 | Public album inside a private album: not listed. |
| S-117-07 | Album with a slug: `<loc>` uses the slug. |
| S-117-08 | Requested by an admin: same XML as a guest. |
| S-117-09 | `sitemap_root_only` on: the public child is left out, the public root album stays. |
| S-117-10 | Photos never appear. |
| S-117-11 | `login_required` on: 404. |
| S-117-12 | `login_required` and `login_required_root_only` on: 404. |
| S-117-13 | Gallery password set: 404. |
| S-117-14 | `hide_nsfw_in_sitemap` on: a sensitive public album and its public child are left out. Off: both listed. |
| S-117-15 | Public tag album: not listed. |
| S-117-16 | `<lastmod>`: later of `updated_at` and `max_taken_at`, future `max_taken_at` skipped, both missing: no element. |
| S-117-17 | Headers: `application/xml; charset=UTF-8`, `Cache-Control: public, max-age=3600`, no `Set-Cookie`, 200 with `Accept: application/xml`. |
| S-117-18 | XML escaping of a slug-less ID and a slug (`&`, `<`, quotes never appear unescaped). |
| S-117-19 | Cache: each listed invalidator event flushes `sitemapTag()`. |
| S-117-20 | More albums than the cap: the list stops at the cap and a warning is logged. |

## Test Strategy
- **Unit (pure helpers):** `SitemapLastmodTest` (S-117-16), `SitemapRendererTest` (S-117-07, S-117-18). `ManagedCacheAlbumListingInvalidatorTest` extended (S-117-19). The cap (S-117-20) is tested on the action with a small cap injected through its constructor default.
- **Feature_v2 (HTTP):** `SitemapTest` on `BaseApiWithDataTest` fixtures (`album4`/`subAlbum4` public, `album1` private) plus albums built in the test for password, link, sensitive, slug and tag album cases. Each test sets the settings it needs and restores them.
- **Migrations:** the three config rows exist with their defaults, categories and `required_keys` (`SitemapTest` reads them through `ConfigManager`).
- **Frontend:** none.

## Interface & Contract Catalogue

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-117-01 | `GET /sitemap.xml` (web route, session-less group `sitemap`) | sitemaps.org 0.9 `<urlset>` | 404 when disabled or locked (FR-117-01, FR-117-09). |

### Configuration
| ID | Key | Default | Category | Notes |
|----|-----|---------|----------|-------|
| CFG-117-01 | `sitemap_enable` | `0` | Basics (`config`) | `0\|1` |
| CFG-117-02 | `sitemap_root_only` | `0` | Basics (`config`) | `0\|1`, requires `sitemap_enable` |
| CFG-117-03 | `hide_nsfw_in_sitemap` | `1` | Sensitive (`Mod NSFW`) | `0\|1`, requires `sitemap_enable` |

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-117-01 | Sitemap row: `id`, `slug`, `updated_at`, `max_taken_at` (strings or null, as stored). Cached as plain arrays. | actions, cache |

## Telemetry & Observability
A single `Log::warning` when the cap truncates the list (FR-117-08). No other events.

## Documentation Deliverables
- Roadmap row 117.
- Knowledge map: the sitemap action, controller, middleware group and cache tag.
- `lang/*/all_settings.php`: descriptions and details for the three keys, in all 23 languages.
- ADR-117-01.

## Spec DSL

```
routes:
  - id: API-117-01
    method: GET
    path: /sitemap.xml
    middleware_group: sitemap
configs:
  - id: CFG-117-01
    key: sitemap_enable
    default: 0
    cat: config
  - id: CFG-117-02
    key: sitemap_root_only
    default: 0
    cat: config
    requires: sitemap_enable
  - id: CFG-117-03
    key: hide_nsfw_in_sitemap
    default: 1
    cat: Mod NSFW
    requires: sitemap_enable
domain_objects:
  - id: DO-117-01
    name: sitemap row
    fields: [id, slug, updated_at, max_taken_at]
```
