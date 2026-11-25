# Decisions

We track significant choices as Architecture Decision Records (ADRs). Each ADR is immutable; superseded entries should point at their replacements.

Numbering convention: new ADRs use `ADR-<NNN>-<YY>`, where `<NNN>` is the feature that raised the decision and `<YY>` is a two-digit sequence within that feature (`ADR-072-01`, `ADR-072-02`, …). Numbering per feature means parallel features cannot collide. ADRs created before this rule keep their global `ADR-XXXX` numbers; a decision that spans several features belongs to the feature that raised it.

| ADR | Status   | Summary                            |
|-----|----------|------------------------------------|
| [ADR-0003](ADR-0003-album-computed-fields-precomputation.md) | Accepted | Album Computed Fields Pre-computation Strategy |
| [ADR-0004](ADR-0004-multi-group-permission-merge.md) | Accepted | Multi-Group Permission Merge Policy |
| [ADR-0005](ADR-0005-nuxt-ui-migration.md) | Accepted, partially superseded by **ADR-0006** | Replace PrimeVue with Nuxt UI (standalone Vue mode) |
| [ADR-0006](ADR-0006-nuxt-ui-dual-tree-toggle.md) | Accepted | Dual-tree, feature-flag-gated cutover strategy for the Nuxt UI migration |
| [ADR-0007](ADR-0007-v8-admin-setup-gate-bypass.md) | Accepted | Exempting a dedicated route from the `admin_user:set` gate to serve v8's admin-setup page |
| [ADR-0008](ADR-0008-v3-asset-endpoint-signing-and-authorization.md) | Accepted | Temporary-link signing and authorization model for the v3 asset endpoint |
| [ADR-0009](ADR-0009-api-v3-response-shape-precedent.md) | Accepted | API v3 response-shape precedent — Struct-of-Arrays for collections, binary passthrough for single-item endpoints |
| [ADR-0010](ADR-0010-album-user-thumb-cache-purge-on-revocation.md) | Accepted | Purge cached album covers on revocation rather than re-checking permissions per asset request |
| [ADR-0011](ADR-0011-move-grant-separate-from-edit.md) | Accepted | Move grant separate from Edit, and cross-owner guards |
| [ADR-069-01](ADR-069-01-v3-collection-bounding-strategies.md) | Accepted | Bounding strategies for v3 Struct-of-Arrays collection endpoints |
| [ADR-071-01](ADR-071-01-date-scrubber-ticks-derived-client-side.md) | Accepted | Date Scrubber Ticks Are Derived Client-Side; Bucket Storage Stays Unchanged |
| [ADR-074-01](ADR-074-01-gallery-password-visitor-unlock.md) | Accepted | Gallery password as a per-visitor unlock, not a guest account |
| [ADR-074-02](ADR-074-02-gallery-unlock-encrypted-cookie.md) | Accepted | Gallery unlock stored in an encrypted cookie, not the server-side session |
| [ADR-074-03](ADR-074-03-password-config-type.md) | Accepted | Write-only `password` config type (hashed on write, masked on read) |
| [ADR-082-01](ADR-082-01-in-house-webgl2-sphere-renderer.md) | Accepted | In-house TypeScript + WebGL2 sphere renderer for 360° photos |
| [ADR-089-01](ADR-089-01-moderation-history-event-log.md) | Accepted | Moderation history as an append-only event log |
| [ADR-089-02](ADR-089-02-nsfw-findings-dismissal-deletes.md) | Accepted | Dismissing an NSFW finding deletes it |
| [ADR-092-01](ADR-092-01-granularity-free-bucket-keys.md) | Accepted | Granularity-free bucket keys, bucket ids derived at read time |
| [ADR-092-02](ADR-092-02-capture-local-photo-date-buckets.md) | Accepted | Photo capture dates bucketed in the zone the photo was taken in |
| [ADR-096-01](ADR-096-01-vite-plus-single-frontend-toolchain.md) | Accepted | Vite+ (`vp`: Vite 8, Oxlint, Oxfmt, `vp staged`) as the single frontend toolchain; `vue-tsc` and the npm bootstrap kept |
| [ADR-100-01](ADR-100-01-motion-photo-reader-in-php-exif.md) | Accepted | The Google Motion Photo reader (legacy `MicroVideoOffset` and Motion Photo 1.0 `Container:Directory`) lives in `lychee-org/php-exif` (`PHPExif\MotionPhoto`, run by `Reader::read()` for every adapter, result in `MicroVideoOffset`) |
| [ADR-101-01](ADR-101-01-docker-imagemagick-jxl.md) | Accepted | Docker image ships ImageMagick 7 built against libjxl (pinned, source-built unless a trixie package with JXL exists); JXL smoke test in the Docker workflow |
| [ADR-103-01](ADR-103-01-stored-recursive-nsfw-flag.md) | Accepted | Stored recursive NSFW flag on albums (`albums.is_recursive_nsfw`), written explicitly; no-origin sensitivity checks read it |
| [ADR-110-01](ADR-110-01-album-tile-min-width.md) | Accepted | v8 album tiles sized by a minimum CSS px width (`album_thumb_min_width`), covers chosen by screen density (`Thumb.vue` FR-102-06 rule) |
| [ADR-113-01](ADR-113-01-s3-asset-url-response.md) | Accepted | S3 size variants answer `200` JSON `{ url }` (`AssetUrlResource`, `no-store`) instead of a redirect, so the browser never preflights the bucket; v8 `Thumb` retries `/Asset` once |
| [ADR-114-01](ADR-114-01-duplicate-skip-job-outcome.md) | Accepted | Duplicate skips, and since Q-115-13 rejected duplicates, end `ImportImageJob`/`ProcessImageJob` as a handled outcome (`JobStatus::SKIPPED`, one `info` line, no failed job); rethrown on the `sync` queue |
| [ADR-115-01](ADR-115-01-per-user-import-root.md) | Accepted | Import from server confined to a per-user import root (`users.server_path`): containment before disk access, links never followed, job re-check, owner-scoped album reuse and delete-missing, `/` for the owner and first admin |
| [ADR-116-01](ADR-116-01-upload-status-cache.md) | Accepted | Upload outcomes kept 24 h in a shared cache (`upload-status:<provisional id>`, `database` store when the queue is async and the default driver is per-container), readable only by the uploader through `GET /api/v2/Photo::uploadStatus` |
| [ADR-117-01](ADR-117-01-sitemap-gating-and-caching.md) | Accepted | `GET /sitemap.xml` answers 404 while disabled, `login_required` is on or a gallery password is set; guest view only; rows cached under a dedicated `sitemapTag()` flushed by the album-listing invalidator, `Cache-Control: public, max-age=3600`, session-less route group |
| [ADR-118-01](ADR-118-01-split-user-rights.md) | Accepted | User rights split into `may_upload`, `may_share`, `may_edit`; upload implies the other two, enforced when users are written so each policy reads one flag; owner branches need their flag; sharers give only grants they hold; import from server needs `server_path` alone |

## Templates

- Use [adr-template.md](../templates/adr-template.md) when creating new ADRs.
- Each ADR documents context, decision, consequences, alternatives, security/privacy, operational impact, and reference links, plus pointers to the affected specs/open-questions.
