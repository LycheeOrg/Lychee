# ADR-085-03: Insights are computed in one streamed PHP pass, cached per library revision

- **Status:** Accepted
- **Date:** 2026-10-05
- **Related features/specs:** Feature 085 (docs/specs/4-architecture/features/085-library-insights/spec.md), Feature 108 (docs/specs/4-architecture/features/108-insights-speedup/spec.md)
- **Related open questions:** Q-085-07, Q-085-08, Q-108-01, Q-108-02, Q-108-03, Q-108-04, Q-108-05, Q-108-06

## Context

Phase 1 of Insights needs counts by local day, week, month, weekday and hour, streaks and breaks, histograms with median and mode, device and lens rankings and storage totals, for one owner or the whole instance, over a Library, Year or Range period. Lychee supports MySQL/MariaDB, PostgreSQL and SQLite. `taken_at` is stored in UTC with the original timezone name in `taken_at_orig_tz` (Q-085-08 requires local capture time), which portable SQL cannot apply per row. Streaks, medians and modes need ordered series rather than plain `GROUP BY` results.

## Decision

- One action reads the scope's photos once through a `DB::table` cursor in chunks, selecting only the columns listed in NFR-085-06, and feeds every phase-1 accumulator in the same loop. Local time is computed in PHP with native `DateTimeZone` objects, cached per distinct timezone string.
- For a Year or Range period, SQL narrows to the UTC window widened by 14 hours on each side (the widest timezone offset); PHP keeps the photos whose local date falls in the period.
- Album membership is an `EXISTS` flag in the projection. Albums with a photo in the period and distinct people come from two more streams over `photo_album` and `faces`, joined to the same windowed photos, filtered the same way and collected as ID sets; for the Library period they are plain `COUNT` queries.
- The result is stored in the Laravel cache under (scope, period, revision). The revision is the photo count and latest `updated_at` of the scope's photos and albums, so any upload, edit or deletion produces a new key. Entries expire after one hour, which bounds staleness for changes that touch neither (album membership, face assignment).
- The action returns one resource served by `GET /api/v3/Insights`.

## Amendment 2026-11-04 (Feature 108, Q-108-02 A)

On about 70,000 photos two parts of the decision grew with the square of the photos involved and crossed the 30 s Octane request limit: the album scope (`EXISTS` semi-join materialised and sorted again on every `lazyById` page) and the album and people counts of a Year or Range (keyset pages over `photo_album ⋈ photos` sorted again on every page). Without schema change:

- An album scope pages its photo IDs by keyset on `photo_album.photo_id`, testing each link's album with a correlated scalar subquery on `albums` (primary key, `_lft` within the tree), then loads each page by ID (NFR-108-01). Every query binds a fixed number of values, never a list that grows with the tree (NFR-108-04). Owner and whole-instance scopes keep `lazyById`.
- Albums with a photo in the period and distinct people take one `DISTINCT` over the core UTC window — the period narrowed by 14 hours on each side, inside the period in every timezone — then read the photos of the rest of the widened window by pages of at most 1,000 IDs, whose rows are checked in PHP (NFR-108-02, Q-108-03 B). They are no longer collected as streamed ID sets, and no read holds more than one page of edge photos.
- The scope's years list is cached under (scope, revision) beside the aggregates, so Year and Range reuse it instead of streaming the scope again (FR-108-03).
- A capture time whose timezone is more than 14 hours from UTC at that instant (an offset such as `+15:00`, or a local mean time before standard time) is read in UTC, so the widened and core windows hold for every stored value (FR-108-04).

The aggregates, the cache key and the revision are unchanged. Measured on the dev copy (MariaDB 11.8, 67,790 photos): album scope 12.3 s → 1.6 s, Range 2005–2026 9.4 s → 1.9 s, Library 1.8 s → 1.3 s.

## Amendment 2026-11-06 (Feature 108, Q-108-04, Q-108-05 A)

With the 2026-11-04 amendment deployed, a server with 85,743 photos (MariaDB 11.8.9, default 128 MB buffer pool) still crossed the 30 s limit inside the owner Library stream. A dump of that server showed why: the stream reads each photo's original size variant from its clustered `size_variants` row, which photo IDs (random strings) reach in random `size_variants.id` order, and the pass no longer fits the buffer pool, so every pass reads thousands of pages from disk. Decision:

- The index `size_variants (photo_id, type, ratio)` is widened to `(photo_id, type, ratio, filesize, width, height)` (NFR-108-06). The projection's left join then reads the originals from the index alone, in photo-ID order like every other read of the pass; Insights code is unchanged. The widened index replaces the old one rather than adding a fourth index on `size_variants`; queries on the `(photo_id, type, ratio)` prefix keep their plans.
- The single pass remains linear in the photos of the scope. Libraries ten to fifty times this size need a different computation (background result, SQL aggregation over `taken_key`, or incremental aggregates): Q-108-06, deferred.

Measured on the server dump (MariaDB 11.8): owner Library 2.74 s → 1.56 s with 6,447 → 0 disk page reads when repeated (21,561 → 2,314 cold), identical results.

## Consequences

### Positive
- Same SQL on all three drivers; local time is exact.
- One streamed pass per request (plus the side counts); accumulators keep memory flat in the library size.
- No new table, listener or integrity check.

### Negative
- The first request after a change scans the whole scope: seconds on very large libraries or the whole instance.
- Album membership and face-assignment changes can show up to one hour late.
- If the first-load cost becomes a problem, precomputed summary tables (Option C) are the next step, behind the same action and resource.

## Alternatives Considered

- **SQL `GROUP BY` per widget:** fast per chart, but UTC only (or per-driver timezone tables), many queries, three dialects.
- **Precomputed summary tables kept current by jobs:** instant reads, but new tables, listeners and integrity checks, and streaks, modes and medians are hard to maintain incrementally.

## Security / Privacy Impact

The cache key includes the scope, so one owner's aggregates are never served for another scope. Scope rights are checked before the cache lookup (ADR-085-02).

## Operational Impact

- CPU and I/O on the first request per revision; cache storage of one aggregate per (scope, period, revision).
- No maintenance task: expired entries leave the cache by TTL.

## Links

- Related spec sections: `docs/specs/4-architecture/features/085-library-insights/spec.md#non-functional-requirements` (NFR-085-06), FR-085-15, API-085-01
- Related ADRs: ADR-085-02
