# ADR-086-01: Album Map Points Endpoint and Server-Side Map Header Decision

- **Status:** Accepted
- **Date:** 2026-10-07
- **Related features/specs:** Feature 086 (docs/specs/4-architecture/features/086-album-map-header/spec.md), Feature 067 (docs/specs/4-architecture/features/067-map-geo-bucketing/spec.md)
- **Related open questions:** Q-086-04, Q-086-05

## Context

The map header (Feature 086) needs every geotagged photo point of an album to fit the map and draw one dot per photo. Two existing sources do not fit:

- `GET /api/v2/Map?album_id=` returns full `PhotoResource`s with size variants, statistics, palette, tags and rating for every geotagged photo: the payload Feature 067 replaced for the Map page because it exhausts memory on large scopes.
- The Feature 067 endpoints (`/api/v3/Map/buckets`, `/Map/Photos`) are viewport-driven and aggregate above 500 photos, and they answer only when the `struct-of-array` feature flag is on (off by default).

The header must also decide, before the page renders, between the map and the random-photo fallback (Q-086-05), which requires knowing whether the viewer may see the album's map and whether any geotagged photo is in scope.

## Decision

1. New endpoint `GET /api/v3/Map/album?album_id=` (`MapListingController::album()`, `GetMapAlbumRequest`, action `QueryAlbumMapPoints`). It returns `MapPointResource` (`ids`, `album_ids`, `latitudes`, `longitudes`) for every distinct geotagged photo in the album's Map page scope (`ResolvesMapPhotoSource::resolveAlbumQuery()`, `map_include_subalbums`), with no clustering and no cap. Authorization is `AlbumPolicy::CAN_ACCESS_MAP`, without the `struct-of-array` flag gate. Responses go through `ManagedCacheService` with the Map cache tags, so Feature 067's invalidation applies.
2. `HeadAlbumResource` decides map or image: `PreFormattedAlbumData::$is_map_header` is `true` when `header_id = 'map'`, `use_album_compact_header` is off, the viewer passes `CAN_ACCESS_MAP`, and `QueryAlbumMapPoints::hasPoints()` (one `EXISTS` query) finds a point. Otherwise the header image is resolved as for `header_id = NULL`.

## Consequences

### Positive
- Lean rows (`toBase()`, four columns, no size variant join) instead of full photo resources.
- The page knows from the album head whether to draw the map, so the header never switches after rendering.
- Works whether or not the `struct-of-array` flag is on.
- Same scope and access rules as the album's Map page (one trait, one policy).

### Negative
- The response grows linearly with the number of geotagged photos in the album (owner's choice: no clustering).
- One extra `EXISTS` query per album head request for map-header albums.

## Alternatives Considered

- **Extent in the album payload, points from the Feature 067 viewport endpoints:** bounded payloads, but count badges instead of dots above 500 photos and a dependency on the `struct-of-array` flag.
- **Extent-only endpoint with viewport points:** extra round trip and the same flag dependency.
- **v2 `/Map` endpoint:** unbounded full photo resources.

## Security / Privacy Impact

- No new data exposure: the points are a subset of what `/api/v2/Map?album_id=` already returns to the same viewer, under the same `CAN_ACCESS_MAP` gate and `all_photos()` searchability filter.
- `album_ids` reuse Feature 067's containing-album resolution, so a dot links only to an album in the requested scope.

## Operational Impact

- Cache entries carry `mapListingTag(album_id)`, `mapListingGlobalTag()` and the user tag; existing listeners flush them on photo changes and on `map_*` setting changes.
- No new configuration.

## Links

- Related spec sections: `docs/specs/4-architecture/features/086-album-map-header/spec.md#functional-requirements` (FR-086-03 … FR-086-06)
- Related ADRs: ADR-0009 (struct-of-arrays collection convention)
