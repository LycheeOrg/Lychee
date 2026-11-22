# ADR-117-01: Sitemap gating and caching

- **Status:** Accepted
- **Date:** 2026-11-22
- **Related features/specs:** Feature 117 (docs/specs/4-architecture/features/117-google-sitemap/spec.md)
- **Related open questions:** Q-117-02, Q-117-06

## Context

`GET /sitemap.xml` is public and unauthenticated. It lists the albums a guest can browse from the root (FR-117-03, FR-117-04). Three settings decide whether a guest can browse at all: `login_required`, `login_required_root_only` and `gallery_password`. The browsability filter (`AlbumQueryPolicy::applyBrowsabilityFilter()`) runs a `NOT EXISTS` over each album's ancestor path, O(n²) in the worst case, so anyone could trigger an expensive query repeatedly.

The managed cache already caches album listings. Its coarse tag `albumListingGlobalTag()` is flushed only by `AlbumListingCacheFlushRequested`. Album saves, moves, deletions, permission changes and stats recomputes flush narrower tags. A sitemap entry carrying only the global tag would therefore go stale after ordinary changes.

The `web` middleware group starts a session, sets cookies and rejects requests that do not accept HTML (`/feed` is on an exception list for this reason). A response that shared caches may store must not carry `Set-Cookie`.

## Decision

1. **Gating.** `GalleryLockState::isSitemapEnabled()` is true only when `sitemap_enable` is on, `login_required` is off and no gallery password is set. Otherwise the route answers 404, the same as when the sitemap is disabled. This sits next to `isRssEnabled()` and `isEmbedEnabled()`.
2. **Guest view only.** The query always runs with `user = null` and no unlocked albums, whoever sends the request. Nothing about the requester changes the result, so the result can be cached and shared.
3. **Server cache.** The selected rows (`id`, `slug`, `updated_at`, `max_taken_at`) are cached through `ManagedCacheService::remember()`, one key per (`sitemap_root_only`, `hide_nsfw_in_sitemap`) pair. The key carries `CacheKeyProvider::sitemapTag()` and `albumListingGlobalTag()`. `ManagedCacheAlbumListingInvalidator` flushes `sitemapTag()` in `handleAlbumSaved`, `handleAlbumDeleted`, `handleAlbumChildrenChanged`, `handleAccessPermissionChanged`, `handleAlbumComputedDataUpdated` and `handleBaseAlbumRemoved`. The global flush still clears it through the global tag. Rows, not XML, are cached, so the URLs always use the current request's root URL. The gating checks of point 1 run before the cache lookup.
4. **HTTP cache.** The response carries `Cache-Control: public, max-age=3600`.
5. **Session-less route.** A dedicated `sitemap` middleware group (`ResolveConfigs`, `installation:complete`) and `routes/web_sitemap.php`, registered before `web`. No session, cookies, CSRF or content-type check.

## Consequences

### Positive
- The expensive query runs once per relevant change, not once per request.
- Crawlers and proxies may cache the document for an hour.
- Lock settings take effect immediately, because they are checked before the cache.

### Negative
- One more tag to keep in mind: a future event that changes album visibility must flush `sitemapTag()` too.
- Every photo upload batch flushes the sitemap entry (through `AlbumComputedDataUpdated`), which is acceptable.
- A crawler may see a removed album for up to an hour (HTTP cache). The album page then answers as it would for any guest.

## Alternatives Considered

- Gating: an empty `<urlset>` instead of 404. Rejected: Search Console reports an empty sitemap, which is more confusing.
- Cache under `albumListingGlobalTag()` only. Rejected: not flushed by ordinary album changes.
- Reuse `albumListingV3Tag()`, which every relevant handler already flushes. Rejected: couples the sitemap to Feature 057's v3 listing cache.
- HTTP caching only, or no caching. Rejected: an unauthenticated O(n²) query on every request.
- Keep the route in the `web` group with an `AcceptContentType` exception, like `/feed`. Rejected: the response would carry session cookies next to `Cache-Control: public`.

## Security / Privacy Impact

- The document only contains what a guest already sees on the gallery pages. Locked galleries expose nothing.
- No session or cookie is created for the request, so a shared cache cannot store a session cookie.
- Sensitive albums are left out by default (`hide_nsfw_in_sitemap`, FR-117-10).

## Operational Impact

- Disabling the managed cache (`managed_cache_enabled`) makes every request run the query. The HTTP cache still applies.
- The `sitemap_enable` details text tells the admin which URL to submit to search engines.

## Links

- Related spec sections: `docs/specs/4-architecture/features/117-google-sitemap/spec.md#functional-requirements` (FR-117-03, FR-117-04, FR-117-09), `#non-functional-requirements` (NFR-117-02, NFR-117-04)
- Related ADRs: ADR-102-02 (config, verify and locale middleware first in each group)
