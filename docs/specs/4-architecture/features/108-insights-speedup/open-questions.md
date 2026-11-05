# Open Questions – Feature 108

Open questions for [Feature 108](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-108-01~~ | 108 | High | What fails on the server: (A) owner shares error + scope/period + image/DB, (B) assume the 30 s Octane limit, (C) raise the limit first | Resolved (B, owner, 2026-11-04: spec Overview) | 2026-11-04 | 2026-11-04 |
| ~~Q-108-02~~ | 108 | High | Fix: (A) linear streams, no schema change, (B) + index on `photos.taken_at`, (C) compute in a queued job | Resolved (A, owner, 2026-11-04: FR-108-01 … 03, NFR-108-01 … 04, ADR-085-03 amendment) | 2026-11-04 | 2026-11-04 |
| ~~Q-108-03~~ | 108 | Medium | Edge rows of the album/people counts are buffered on MySQL: (A) keep one query and state the bound, (B) page by photo-ID ranges, (C) unbuffered read on MySQL | Resolved (B, owner, 2026-11-05: NFR-108-02, NFR-108-04, S-108-11, ADR-085-03 amendment) | 2026-11-05 | 2026-11-05 |

## Question Details

### ~~Q-108-01~~ · What fails on the server

**Status:** Resolved (B, owner, 2026-11-04): treated as the 30 s Octane limit (spec Overview).
**Preferred option:** A – owner shares the server error before the fix is specified

**Question**
`GET /api/v3/Insights` does not fail on the development copy (MariaDB 11.8, 67,790 photos, 70,657 album links, 0 faces) but fails on the server. Local timings with Xdebug off (scratchpad `profile3.php`, `explain2.php`): whole instance Library 1.7 s, Year 2024 1.7 s, Range 2005–2026 9.0 s, album scope on the largest tree (47,707 photos) 12.7 s; peak memory 23 MB. The default image (`ghcr.io/lycheeorg/lychee:latest`, FrankenPHP/Octane) stops a request after `LYCHEE_MAX_EXECUTION_TIME` = 30 s (`config/octane.php`). Which failure does the server show?

**Option A (recommended) – owner shares the error**
- Idea: the `storage/logs` entry or HTTP status of the failing request, the scope and period selected, the image (Octane or legacy FPM) and the database driver.
- Pros: confirms the cause (time limit, memory, SQL error) before the spec is written; tells whether faces (0 locally) play a part.
- Cons: one round trip.

**Option B – assume the Octane time limit**
- Idea: treat it as a request over 30 s and fix the superlinear paths measured locally.
- Pros: no wait; those paths need fixing in any case.
- Cons: a memory or driver-specific failure would stay unfixed.

**Option C – raise the limit first**
- Idea: set `LYCHEE_MAX_EXECUTION_TIME` higher on the server as a stopgap, then fix.
- Pros: unblocks the server today.
- Cons: hides the symptom; every user waits as long; Octane workers stay busy.

### ~~Q-108-02~~ · How to make Insights scale

**Status:** Resolved (A, owner, 2026-11-04): linear streams, no schema change (FR-108-01 … 03, NFR-108-01 … 04; ADR-085-03 amended).
**Preferred option:** A – linear streams, no schema change

**Question**
Measured causes (MariaDB `EXPLAIN`): (1) album scope: the `EXISTS` scope becomes a semi-join that MariaDB materialises and sorts (`Start temporary … Using filesort`) on every `lazyById` page, so 48 pages re-sort the whole tree; (2) Year/Range album count: keyset pages over `photo_album ⋈ photos` with an `OR` cursor and an unindexed `taken_at` window rebuild and sort the whole join per page; (3) Year/Range years list: a second full pass over every photo of the scope; the per-period cache key recomputes it for each year opened; (4) about 17 µs of PHP per photo (one `DateTimeImmutable`, seven `format()` calls and one `modify()` per row). (1) and (2) grow with the square of the photos involved. How should Insights scale?

**Option A (recommended) – linear streams, no schema change**
- Idea: album scope pages its photo IDs over `photo_album` by keyset and loads those photos by ID; album and people counts for Year/Range use one `DISTINCT` over the UTC window that is inside the period for every timezone, plus a PHP check of the photos within 14 h of either boundary; the years list is cached per (scope, revision) instead of a second pass per period; local time is read with one `format()` call per row. Prototype (scratchpad `proto.php`): album scope 12.7 s → 1.7 s; album count for 2005–2026 0.17 s in one query.
- Spec impact: NFR-085-06 rewritten for the album scope and the side counts (ADR-085-03 amended); a regression test bounding queries per photo count.
- Pros: same aggregates, same cache; no migration; memory stays flat; each fix testable on SQLite.
- Cons: still one PHP pass per period at first view (≈1.2 s per 70,000 photos locally); planner choices stay driver-dependent.

**Option B – A plus an index on `photos.taken_at`**
- Idea: as A, plus a single-column index so Year/Range windows seek instead of scanning.
- Spec impact: as A plus a migration.
- Pros: Year/Range over a small part of a large library read only that part.
- Cons: the single `taken_at` index was dropped in 2022 (`drop_objectionable_indices`) because it drew the MySQL planner away from the album indices; schema change.

**Option C – compute in a queued job**
- Idea: the endpoint returns the cached result or starts a job and answers "pending"; the page polls.
- Spec impact: new API state, job, front-end polling; ADR-085-03 replaced.
- Pros: no request ever runs the computation; immune to the time limit.
- Cons: first view waits for the queue (sync queue on many installs gives no gain); more moving parts; the quadratic queries still cost the worker.

### ~~Q-108-03~~ · Memory of the edge rows on MySQL

**Status:** Resolved (B, owner, 2026-11-05): edge rows read by photo-ID ranges of at most one page of edge photos (NFR-108-02, NFR-108-04, S-108-11; ADR-085-03 amendment). Raised in the PR #4959 review (CodeRabbit).
**Preferred option:** A – keep one query and state the bound

**Question**
`ComputeInsights::distinctInPeriod()` reads the edge rows of a Year or Range (album and people counts, NFR-108-02) with one `DISTINCT` query through `cursor()`. On MySQL/MariaDB PDO buffers the whole result when the statement runs, so these rows are held in memory at once; the docblock's "memory grows with the distinct values only" is wrong. The rows are the distinct (value, capture time, timezone) triples in the two 28-hour strips around the period's bounds (one 52-hour strip for a one-day range), not the library. Measured on the dev copy (scratchpad `edges.php`): at most 12 rows for any Year 2000–2026; 2,186 rows (≈ 0.15 MB) for a one-day range on the busiest day; for comparison the whole library as one buffered cursor is 65,083 rows, 4.2 MB. Should the edge read be bounded by a constant?

**Option A (recommended) – keep one query, state the bound**
- Idea: fix the docblock and NFR-108-04: the edge rows are held at once and grow with the capture times in the edge strips; the core values grow with albums and people.
- Spec impact: NFR-108-04 wording.
- Pros: two queries per count, plan unchanged, no new code; the bound depends on how many distinct capture times fall within 52 hours, which stays small in real libraries.
- Cons: not constant: a library with hundreds of thousands of distinct capture times within one 52-hour span would hold them (≈ 70 bytes each).

**Option B – page the edge rows by photo-ID ranges**
- Idea: a pager over `photos` (keyset on `id`, edge filter, fixed bindings) yields ID ranges holding at most 1,000 edge photos; the edge query runs once per range with `photos.id BETWEEN`.
- Spec impact: NFR-108-02 (query count) and NFR-108-04.
- Pros: memory bounded by the page size times the links or faces per photo.
- Cons: two more queries per 1,000 edge photos and per count; on the album scope the planner may drive each range query from the tree instead of the ID range and rescan the tree per page (needs `EXPLAIN`); more code.

**Option C – unbuffered read on MySQL**
- Idea: turn `PDO::MYSQL_ATTR_USE_BUFFERED_QUERY` off for the edge cursor only.
- Spec impact: NFR-108-04.
- Pros: constant memory, no extra query.
- Cons: driver-specific branch; connection state to restore on error; no other query may run on the connection while the cursor is open.
