# ADR-0011: Date Scrubber Ticks Are Derived Client-Side; Bucket Storage Stays Unchanged

- **Status:** Accepted
- **Date:** 2026-09-24
- **Related features/specs:** Feature 071 (docs/specs/4-architecture/features/071-album-date-scrubber/spec.md), Feature 066 (T-067 Timeline Scrubber), Features 061/064 (persisted `bucket_id`)
- **Related open questions:** Q-071-05

## Context

Feature 071 brings the Timeline's date scrubber rail to album views. Album grids are grouped by the persisted `bucket_id` (on `base_albums` for sub-albums, on the `photo_album` pivot for photos). That `bucket_id` is truncated at the album's resolved granularity, and `timeline_photos_granularity` defaults to `YEAR`. A rail built from those buckets would show year ticks only, and its fisheye lens would magnify years only.

The owner asked whether bucketing should instead always persist the smallest granularity and roll it up at read time. That change would reach `photo_album.bucket_id`, `base_albums.bucket_id`, all four recompute jobs, the `lychee:recompute-buckets` backfill, `QueryPhotoBuckets` / `ratios` grouping, the Timeline's bucket-windowed fetch (`PhotoBucketComputer::bucketDateRange()`), and managed-cache invalidation keys. It would also need a decision on `HOUR`, which is finer than `DAY`.

Every tier the album view already loads carries per-item dates: the photo `ratios` tier returns `taken_ats`, `created_ats` and `titles`, and album tier 2 returns `created_ats`, `min_taken_ats`, `max_taken_ats` and `titles`.

## Decision

The rail's resting ticks, lens labels and readout are derived **in the browser** from each tile's own date and real pixel position. The persisted `bucket_id` keeps driving the section headers only. Bucket storage, the recompute jobs and the bucket tiers are not changed by Feature 071.

A persisted "always smallest granularity" refactor is not rejected. It is deferred to its own feature, to be judged on its own benefits: free granularity changes and simpler cache invalidation.

## Consequences

### Positive
- Feature 071 needs no bucketing migration and no new request (NFR-071-01).
- The rail gets day precision whatever granularity the headers use.
- The high-blast-radius storage change is not coupled to a UI feature.

### Negative
- Two notions of "date grouping" coexist: stored buckets for headers, client-derived dates for rail ticks.
- For title `DATE_PREFIX` ordering, the client must reproduce `TimelineData::parseDateFromTitle()`'s leading-date regex (`^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?`). Any change there must be mirrored in the client.

## Alternatives Considered

- **B: Refactor bucket storage first (prerequisite feature), then 071.** One source of truth, but it blocks 071 behind a risky migration, and day buckets inflate the bucket tiers on large libraries.
- **C: Rail uses stored buckets only.** Least work, but with the default `YEAR` granularity the rail is sparse and the lens is close to useless.

## Security / Privacy Impact

None. The data involved is already in responses the viewer is authorised to receive.

## Operational Impact

None. No jobs, migrations or cache keys change.
