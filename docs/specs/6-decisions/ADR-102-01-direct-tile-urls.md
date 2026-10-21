# ADR-102-01: Direct thumbnail URLs for grid tiles on unprotected instances

- **Status:** Accepted
- **Date:** 2026-10-21
- **Related features/specs:** Feature 102 (docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md), Feature 056 (docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md)
- **Related open questions:** Q-102-04

## Context

Every v8 grid or list tile loads its image through `GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` (Feature 056, ADR-0008). Each call is a PHP request that reads the session, checks album access and photo membership, and streams the file. Albums of 5,000–7,000 photos make this the most frequent request in the application.

The listing that drew those tiles has already authorized the album for the viewer. On an instance with no link protection (secure links off, no temporary link required for the viewer), the files under `/uploads` are reachable by path anyway: the photo view already hands out such URLs for `medium` and `original` through `UrlGenerator`. Guarding each thumbnail in PHP therefore protects nothing the web server would not serve.

v8 does not rely on `SecurePathController` (the signed/encrypted `image/{path}` route). Protected images go through the MAC approach (`X-Mac` header, ADR-0008), and a request header cannot ride on a plain `<img src>`.

Putting URLs into the whole-album ratios tier was measured on a 7,000-photo album: file paths carry 128 random bits each and do not compress, so each URL array adds ≈150 KB gzip to a 229 KB response, paid upfront even when only the first screen is viewed.

## Decision

1. A viewport-scoped tier, `GET /api/v3/Albums/{album_id}/Photos/urls?photo_ids[]=…`, returns the grid and list thumbnail URLs for the photos the client is about to render. It authorizes the album once per request, with the same rules as the album's other `/Photos` tiers.
2. A URL is returned only when the file needs no Lychee-side protection for this viewer:
   - local disk: `secure_image_link_enabled` is off and `UrlGenerator::shouldNotUseSignedUrl()` is true; the URL is the disk's plain URL of the watermark-aware path;
   - S3: the bucket URL or S3's own presigned URL, exactly what `/Asset` would redirect to.
3. Otherwise the entry is `null`, and the tile loads through `/Asset` with the MAC (Feature 056, made lean by FR-102-02 … 04). `SecurePathController` is never involved.
4. `/Asset` keeps ADR-0008's per-request authorization for every image it serves, and stays the path for covers, the map, Flow, search and faces.

## Consequences

### Positive
- On an unprotected instance, grid and list images cost no PHP: one request per window of tiles, then the web server serves the files and the browser caches them.
- Payload grows with what is viewed, not with album size.
- Fallbacks, watermarks, S3 and per-viewer protection are resolved server-side, per row.

### Negative
- Tiles have two loading paths (direct `src`, or `/Asset`).
- One round trip before a window's images start loading.
- On unprotected instances, revoking access to an album does not revoke thumbnail URLs a viewer already holds. This is the same exposure the photo view already has for `medium`/`original`.

## Alternatives Considered

- **URLs in the ratios tier (one stem per photo):** URLs at first render, but ≈147 KB gzip upfront on 7,000 photos, and the client becomes coupled to the naming strategy (legacy paths, watermark suffixes, per-variant formats).
- **Every tile on `/Asset`:** one path and per-request authorization everywhere, but ≈3–4 ms of PHP per tile on first view.
- **`UrlGenerator` URLs everywhere, signed `image/{path}` included:** conflicts with v8 not relying on `SecurePathController`.

## Security / Privacy Impact

- No new exposure: a direct URL is only issued when the file is already publicly reachable by path, or protected by S3's own signature.
- Instances that enable secure or temporary links keep per-request authorization for every tile through `/Asset`.
- The URL tier itself is authorized (album access, photo membership through the album scope) before any path is disclosed.

## Operational Impact

- Fewer PHP requests per page view; image traffic moves to the web server's static file handling.
- No new configuration.

## Links

- Related spec sections: `docs/specs/4-architecture/features/102-asset-endpoint-speedup/spec.md#functional-requirements`
- Related ADRs: ADR-0008, ADR-0009
