# ADR-092-02: Photo Capture Dates Are Bucketed in the Zone the Photo Was Taken In

- **Status:** Accepted
- **Date:** 2026-10-12
- **Related features/specs:** Feature 092 (docs/specs/4-architecture/features/092-bucketing-refactor/spec.md), Feature 071, Feature 085
- **Related open questions:** Q-092-02

## Context

`photos.taken_at` is stored in UTC and `taken_at_orig_tz` holds the zone the photo was taken in. The photo panel, the v7 timeline grouping and Insights show capture-local time. Bucket paths disagreed: the upload path bucketed the cast (capture-local) value, every other path the raw UTC value. Portable SQL cannot convert zones: MySQL `CONVERT_TZ()` with zone names needs the time zone tables loaded and SQLite has no zone support, so SQL-side sources (Timeline, tag, person, smart albums) cannot derive capture-local dates from `taken_at` alone.

## Decision

1. `photos.taken_key` (`varchar(13)`, indexed) holds `taken_at` converted to `taken_at_orig_tz`, formatted `Y-m-d-H`, `NULL` without `taken_at`. It is assigned explicitly at every write of `taken_at`, never through a model event or cast.
2. Every photo date bucket on `taken_at` reads `taken_key`: stored keys (ADR-092-01) and SQL-side sources alike.
3. `photos.created_at` has no original zone and is bucketed as stored. Album dates (`created_at`, `min_taken_at`, `max_taken_at`) are aggregate instants stored and displayed without a zone and are keyed as stored.
4. The v8 date scrubber counts a photo tile for its day in `taken_at_orig_tz`, so the rail and the header buckets agree (ADR-071-01).

## Consequences

### Positive
- A photo shown at 16:05 in the panel lands in the 16:00 bucket, on every path and every database.
- The Timeline can filter windows by an index range on `taken_key`.

### Negative
- One more column on `photos`, maintained at three write sites and backfilled on upgrade.
- Album date buckets near midnight may differ from the capture-local day of their earliest or latest photo.

## Alternatives Considered

- **UTC everywhere:** no new column, but hour buckets shift by the zone offset and day buckets are wrong near midnight, unlike v7 and the photo panel.
- **Zone conversion in SQL:** not portable across MySQL/MariaDB (time zone tables), PostgreSQL and SQLite.

## Security / Privacy Impact

None.

## Operational Impact

- Upgrade backfills `taken_key` for every photo with a `taken_at`, chunked.

## Links

- Related spec sections: `docs/specs/4-architecture/features/092-bucketing-refactor/spec.md` FR-092-01, FR-092-18
- Related ADRs: ADR-092-01, ADR-071-01
