# ADR-091-01: Granularity-Free Bucket Keys, Bucket Ids Derived at Read Time

- **Status:** Accepted
- **Date:** 2026-10-12
- **Related features/specs:** Feature 091 (docs/specs/4-architecture/features/091-bucketing-refactor/spec.md), Feature 061, Feature 062, Feature 064, Feature 066
- **Related open questions:** Q-091-01, Q-091-03

## Context

Photo and album listings are split into buckets. A regular album stores a value per photo link (`photo_album`) and per child album (`albums`). That value depended on the sort column, the granularity and the title bucket settings of the album, its parent or the instance. Several of these inputs changed without a recompute (global defaults, root-only album recompute), and labels were formatted with the granularity in effect at read time. Tag, person and smart albums computed buckets by reading every photo row into PHP, once for the bucket list and again for every window. Three implementations computed the same value (Carbon on write, string slicing on the live path, SQL on the Timeline).

Measurements (Q-091-01) show that a stored, indexed value keeps window fetches at about 2 ms on a 60,000-photo album, where computing from `photos` costs about 86 ms, and that the bucket list is dominated by the per-link join to `photos`.

## Decision

1. `photo_album.bucket_key` and `albums.bucket_key` store the finest-grained key for the effective sort column: dates to the hour (photos) or day (albums), the title date (`Y-m-d`), the first 255 characters of `title_base`, or the categorical value. Keys depend only on the row and on the sort column (and title bucket mode), never on granularity or prefix length.
2. The bucket id is a prefix of the key, applied in SQL at read time (`SUBSTR`), with its length taken from the granularity or prefix length in effect. Window filters on date-shaped ids are half-open ranges on the key; alphabetical ids filter by prefix; categorical ids by equality.
3. Sources without a stored key (tag, person, smart albums, Timeline) compute the same id in SQL from photo columns. Row-intrinsic values that SQL cannot compute portably are stored on `photos` and written explicitly at each write site: `taken_key` (ADR-091-02) and `title_date_key`.
4. Labels are formatted from the precision of each id.
5. The regular-album bucket list reads `photo_album` only and excludes photos hidden by upload validation with a subquery.
6. Keys are rewritten when the sort column or title bucket mode changes (per album or through the instance defaults, every affected row), in statements of at most 1,000 rows.

## Consequences

### Positive
- A granularity or prefix-length change cannot produce ids and labels that disagree; it needs no recompute.
- No request computes buckets in PHP from every row of a source.
- Bucket list on a 60,000-photo album: 173 → 42 ms for a non-admin viewer, 110 → 15 ms for an admin (SQLite).
- One key computation for writes and one read-time scheme for every source.

### Negative
- Upgrading rewrites every stored key and backfills two `photos` columns.
- Bucket ids for `TAKEN_AT` depend on a stored column that every `taken_at` write site must maintain.

## Alternatives Considered

- **Keep granularity-specific ids, add the missing triggers:** no schema change, but every setting needs a trigger and a global granularity change rewrites every pivot row through the queue.
- **No stored value, compute everything at read time:** nothing to maintain, but windows become about 40× slower on large albums.

## Security / Privacy Impact

None. The validation subquery hides the same photos as the joined filter it replaces.

## Operational Impact

- Upgrade migrations run a chunked backfill over `photos`, `photo_album` and `albums`.
- Changing `sorting_photos_col` or `photo_title_bucket_mode` queues one job that rewrites every `photo_album` key; changing `sorting_albums_col` or `title_bucket_mode` queues one job that rewrites every album key.

## Links

- Related spec sections: `docs/specs/4-architecture/features/091-bucketing-refactor/spec.md` FR-091-02 … FR-091-16
- Related ADRs: ADR-091-02, ADR-071-01
