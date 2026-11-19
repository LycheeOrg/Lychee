# Open Questions – Feature 108

Open questions for [Feature 108](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-108-01~~ | 108 | High | What fails on the server: (A) owner shares error + scope/period + image/DB, (B) assume the 30 s Octane limit, (C) raise the limit first | Resolved (B, owner, 2026-11-04: spec Overview) | 2026-11-04 | 2026-11-04 |
| ~~Q-108-02~~ | 108 | High | Fix: (A) linear streams, no schema change, (B) + index on `photos.taken_at`, (C) compute in a queued job | Resolved (A, owner, 2026-11-04: FR-108-01 … 03, NFR-108-01 … 04, ADR-085-03 amendment) | 2026-11-04 | 2026-11-04 |
| ~~Q-108-03~~ | 108 | Medium | Edge rows of the album/people counts are buffered on MySQL: (A) keep one query and state the bound, (B) page by photo-ID ranges, (C) unbuffered read on MySQL | Resolved (B, owner, 2026-11-05: NFR-108-02, NFR-108-04, S-108-11, ADR-085-03 amendment) | 2026-11-05 | 2026-11-05 |
| ~~Q-108-04~~ | 108 | High | Still 30 s on the server after #4959 (owner Library stream, 85,743 photos): (A) read the server's DB/buffer pool/disk and one logged request first, (B) compute in a queued job, (C) blind fix of the `size_variants` lookups | Resolved (owner imported the server dump locally, 2026-11-06: working set > 128 MB pool, random `size_variants` reads; fix in Q-108-05) | 2026-11-06 | 2026-11-06 |
| ~~Q-108-05~~ | 108 | High | Fix for the `size_variants` reads: (A) widen `(photo_id, type, ratio)` to cover `filesize, width, height`, (B) add a separate covering index, (C) no schema change, queued job | Resolved (A, owner, 2026-11-06: N-108-01, NFR-108-04, NFR-108-06, S-108-12, ADR-085-03 amendment) | 2026-11-06 | 2026-11-06 |
| Q-108-06 | 108 | Medium | Libraries ×10–×50 (0.9–4.3 M photos): (A) compute in the background and serve the last result, (B) aggregate in SQL over `taken_key`, (C) incremental aggregate tables; none needed at today's size | Open | 2026-11-06 | 2026-11-06 |

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

### ~~Q-108-04~~ · Still over 30 s on the server after #4959

**Status:** Resolved (owner, 2026-11-06): the owner imported the server dump into the dev database (`lychee`, MariaDB 11.8.6, same 128 MB pool). One owner Library pass: 4.05 s with 21,625 disk page reads, then 2.53 s with 8,424 (dev copy before: 0 disk reads); `ANALYZE FORMAT=JSON` puts most reads on the `size_variants` lookups (4,222 of 5,003, then 2,807 of 3,226). Cause: the pass's working set no longer fits the pool and the originals are read in random `size_variants.id` order. Server disk not reported; ≈ 1 ms or more per read would make the 30 s. Fix decided in Q-108-05.
**Preferred option:** A – read the server facts and one logged request first

**Question**
With 6db4ac35e deployed (trace lines `ComputeInsights.php:72`, `CachedInsights.php:43` match it), the server log shows `GET /api/v3/Insights` → 500 after 30,182 ms: `Maximum execution time of 30 seconds exceeded` inside a `lazyById` page of the main stream, so an owner or whole-instance scope (the page opens on the viewer's own library, Library period). Server: 85,743 photos, 1,017 albums, 550,491 size variants (owner, 2026-11-06). Dev copy (MariaDB 11.8, 67,790 photos, 869 albums, 433,122 size variants, Xdebug off, scratchpad `bench.php`, `explain.php`, `io.php`): owner 1 Library 1.3–2.3 s, whole instance Library 1.4 s, Year 2024 0.7–0.8 s, Range 2005–2026 1.9 s; every page plan is a range in `photos.id` order (no filesort), 10–30 ms per page warm. One owner Library pass makes 734,919 buffer-pool page reads and 0 disk reads: the pool is the 128 MB default and holds 7,342 of 8,112 pages (≈ 115 MB). Scaled to the server (× 1.26 photos, × 1.27 size variants) that working set is ≈ 145 MB, above a default 128 MB pool, and `size_variants` is visited in random order (photo IDs are random strings, `size_variants.id` is an auto-increment), so each pass would read from disk on every page instead of from memory. Owner, 2026-11-06: the server runs MariaDB 11.8.9 with `innodb_buffer_pool_size` 128 MB (the default), so the working set does not fit; the disk and CPU are still unknown, and the hypothesis is not yet confirmed by a run with a larger pool. MariaDB 11.8 caps runtime resizing at `innodb_buffer_pool_size_max`, which defaults to the startup size (128 MB on the dev copy too), so a larger pool needs a restart. Owner, 2026-11-06: the MariaDB container is not changed (no larger pool, no restart test); read-only queries on the server are fine instead. Diagnostics sent (scratchpad `server.sql`): schema sizes sharing the pool, `ANALYZE FORMAT=JSON` of one page and of the whole owner pass twice, whose `r_engine_stats.pages_read_count` / `pages_read_time_ms` show the disk reads per table (dev copy: page 16 ms, pass 0.31 s, ≈ 100 disk reads). How should the server failure be pinned down?

**Option A (recommended) – read the server facts and one logged request first**
- Idea: restart MariaDB once with `--innodb-buffer-pool-size=512M` (also raises `innodb_buffer_pool_size_max`) and load Insights twice; a fast second load confirms the pool as the cause. The owner also reports `SELECT VERSION(), @@innodb_buffer_pool_size;` (or the driver if not MySQL/MariaDB), the storage under the database (SSD, HDD, SD card, network share) and any CPU/memory limits on the containers. Optionally one request with `DB_LOG_SQL=true`, `DB_LOG_SQL_EXPLAIN=true`, `DB_LOG_SQL_MIN_TIME=0` (and `LYCHEE_MAX_EXECUTION_TIME=300` so the pass completes; Octane restarted to reload config): the log then holds the URL (scope, period), each page's time and its `EXPLAIN`; summed SQL time vs request time splits database from PHP. Settings reverted afterwards.
- Spec impact: none until the cause is known; the outcome goes into the spec Overview and decides the next requirement.
- Pros: tells a buffer-pool/disk limit (server setting, no code), a different plan (engine/version) and a slow CPU (PHP share) apart; Q-108-01 B guessed and the server still fails.
- Cons: one round trip; `DB_LOG_SQL` logs every query of the instance while on.

**Option B – compute in a queued job**
- Idea: Q-108-02 C: the endpoint returns the cached result or queues the computation and answers "pending"; the page polls.
- Spec impact: new API state, job, front-end polling; ADR-085-03 replaced.
- Pros: no request ever runs the computation, whatever the cause or the hardware.
- Cons: declined on 2026-11-04; installs on the `sync` queue gain nothing; more moving parts; the pass still costs the worker its 30+ s.

**Option C – blind fix of the `size_variants` lookups**
- Idea: assume the buffer-pool hypothesis and stop visiting `size_variants` in random order, e.g. a covering index on `(photo_id, type, filesize, width, height)` so the originals are read in `photo_id` order alongside `photos`.
- Spec impact: migration (reverses N-108-01's "no index"), NFR-108-04.
- Pros: shrinks the random reads of each pass if the hypothesis holds.
- Cons: unproven, like the first fix; schema change; does nothing for a slow CPU or a different plan.

### ~~Q-108-05~~ · How to remove the random `size_variants` reads

**Status:** Resolved (A, owner, 2026-11-06: "Let's first ship the index solution"): migration widening the ratio index (N-108-01, NFR-108-04, NFR-108-06, S-108-12; ADR-085-03 amendment). Validated by hand on the local server dump first at the owner's request ("update the index and validate").
**Preferred option:** A – widen the `(photo_id, type, ratio)` index

**Validation (owner request, 2026-11-06)**
`ALTER TABLE size_variants DROP INDEX size_variants_photo_id_type_ratio_index, ADD INDEX size_variants_photo_id_type_ratio_filesize_width_height_index (photo_id, type, ratio, filesize, width, height), ALGORITHM=INPLACE, LOCK=NONE` on the local `lychee` (server dump, 550,491 size variants): 1.1 s, no lock; `size_variants` indexes 167 → 155 MB. Every Insights page query reads the originals from the new index only (`ref`, `Using index`, unhinted). `ComputeInsights::do()` (scratchpad `bench.php`, Xdebug off), two runs each, result hashes identical before and after for all six cases:

| Case | Before (run 1 / run 2) | After (run 1 / run 2) | Disk reads before → after (run 2) |
|------|------------------------|-----------------------|-----------------------------------|
| Owner 1, Library | 5.04 / 2.74 s | 2.05 / 1.56 s | 6,447 → 0 |
| Whole instance, Library | 3.68 / 2.66 s | 1.77 / 1.68 s | 7,190 → 0 |
| Owner 1, Year 2024 | 2.26 / 2.59 s | 1.22 / 1.03 s | 6,085 → 0 |
| Whole instance, Year 2024 | 1.29 / 1.45 s | 0.88 / 0.87 s | 2,997 → 0 |
| Whole instance, Range 2005–2026 | 4.87 / 4.55 s | 2.31 / 2.34 s | 14,048 → 0 |
| Album scope, largest tree | 2.74 / 2.67 s | 1.82 / 1.74 s | 5,830 → 0 |

Run 1 disk reads (owner 1, Library): 21,561 → 2,314. SQL share of owner 1 Library: 1.57–3.60 s → 0.61–0.97 s. The two `ratio` readers keep a covering plan on the new index: search `ratio:` token (`RatioStrategy`, `EXISTS … type = 1 AND ratio > x`) 184 ms, album header landscape pick (`HasHeaderUrl::randomPhotoIdWithVariant`) 46 ms, both `Using index`. Revert: `ALTER TABLE size_variants DROP INDEX size_variants_photo_id_type_ratio_filesize_width_height_index, ADD INDEX size_variants_photo_id_type_ratio_index (photo_id, type, ratio);`

**Question**
On the server dump (Q-108-04), each photo of the Insights stream looks up its original (`type = 1`) through `size_variants_photo_id_type_unique` and then reads `filesize`, `width`, `height` from the clustered row; photo IDs are random strings and `size_variants.id` is an auto-increment, so those reads land on random pages of an 87 MB table and miss the 128 MB pool. Measured on session-only `TEMPORARY` copies of `size_variants` (scratchpad `cover.php`, `paged.php`; nothing written to the schema), full owner 1 stream as `ComputeInsights` pages it (78 pages, 78,287 rows, same bytes): today 3.16 s / 22,551 disk reads cold and 2.1 s / ≈ 9,000 repeated; with `(photo_id, type, ratio, filesize, width, height)` 1.13 s / 6,027 cold and 1.4 s / ≈ 6,500 repeated, every read in photo-ID order. MariaDB picks the wider index unhinted (`ref`, `Using index`) for the paged query and the single pass. Tried and dropped: driving the stream from `size_variants` in clustered order (≈ 6,200 disk reads per pass, no gain, changes the row order); an in-memory map of originals (memory grows with the library, NFR-108-04). Which fix?

**Option A (recommended) – widen the existing `(photo_id, type, ratio)` index**
- Idea: one migration replaces `size_variants_photo_id_type_ratio_index` by `(photo_id, type, ratio, filesize, width, height)`; Insights code unchanged. The 2023 index (`2023_07_07_143908_add_ratio_size_variants`, size-variant queries by photo and type) keeps its prefix, and no code names it.
- Spec impact: N-108-01 and NFR-108-04 ("no index, no migration") amended; new NFR for the covering read; migration test; plan measurements; ADR-085-03 note.
- Pros: one migration, no Insights code; disk reads ÷ 3.7 cold and all in key order (sequential on any disk); same results; same number of indexes on `size_variants`, ≈ 16 bytes more per row.
- Cons: schema change on the largest table (in-place InnoDB DDL, seconds per 500,000 rows); index-only reads on PostgreSQL depend on the visibility map; planner choice on MySQL/PostgreSQL/SQLite not measured locally (CI and the feature tests only check results).

**Option B – add a separate covering index**
- Idea: new `(photo_id, type, filesize, width, height)`, the ratio index kept.
- Spec impact: as A.
- Pros: the 2023 index is untouched.
- Cons: a fourth index on `size_variants` (≈ 35–45 MB at the server's size), kept up on every write and competing for the same pool; two indexes with the same prefix.

**Option C – no schema change, queued computation**
- Idea: Q-108-02 C.
- Spec impact: new API state, job, polling; ADR-085-03 replaced.
- Pros: no migration; no request runs the pass.
- Cons: declined on 2026-11-04; the pass keeps its ≈ 22,000 random reads (30+ s on the server) in the worker; `sync` queue installs gain nothing.

### Q-108-06 · Does Insights hold at ×10 or ×50 the server's library?

**Status:** Open (2026-11-06), deferred by the owner ("first ship the index solution"). Raised by the owner after the Q-108-05 validation.
**Preferred option:** A – compute in the background, serve the last result

**Question**
After the widened index (Q-108-05 A, validated locally), a computation is one pass over every photo of the scope, in PHP, inside the request, whenever the cache entry is missing (every library change, or one hour). Measured on the server dump: ≈ 12 µs of PHP per photo (owner 1 Library 1.56 s, of which SQL 0.61 s, 78,287 photos) and the pass's working set fits the 128 MB pool. Linear estimates: ×10 (≈ 860,000 photos, 5.5 M size variants) ≈ 10 s PHP + ≥ 6 s SQL, working set ≈ 1 GB, read from disk on every pass; ×50 (≈ 4.3 M photos) ≈ 50 s PHP + ≥ 30 s SQL, working set ≈ 5 GB. The dump was freshly imported, so its pages lie on disk in key order; tables grown by inserts at random photo IDs are scattered, so on the server those reads behave closer to random (≈ 1 ms each inferred from Q-108-04). Memory stays flat. Under the 30 s limit ×10 is borderline locally and likely over on the server; ×50 is not workable. `photos.taken_key` (Feature 092) already holds the local capture time as `Y-m-d-H`, indexed. How should Insights scale beyond today's size?

**Option A (recommended) – compute in the background, serve the last result**
- Idea: the request returns the last stored result for (scope, period) with its revision and starts a recomputation job when it is stale; the page shows "updating" until the new result lands. Q-108-02 C revisited with the new estimates.
- Spec impact: API field for staleness/pending, job, front-end state; ADR-085-03 replaced.
- Pros: no request ever runs the pass, so no time limit at any size; the index keeps the job cheap.
- Cons: declined on 2026-11-04; `sync` queue installs gain nothing; first view of a new scope waits; the job still costs ≈ 15 s (×10) to ≈ 80 s+ (×50).

**Option B – aggregate in SQL over `taken_key`**
- Idea: replace the per-photo PHP pass by GROUP BY queries: calendar, rhythm, years and streaks over `taken_key`; devices, formats, exposure over their raw values; Year/Range filtered on `taken_key` by index (no UTC window, no edge strips). PHP handles groups, not photos.
- Spec impact: rewrite of the accumulators; `taken_key` must agree with Insights' local-time rules (FR-108-04 offsets beyond 14 h).
- Pros: PHP cost independent of the photo count; fewer bytes to PHP; stays synchronous.
- Cons: large rewrite; each GROUP BY still scans the scope in the database (several scans per computation), so ×50 on a 128 MB pool and a slow disk stays slow.

**Option C – incremental aggregate tables**
- Idea: summary rows per (owner, local day/hour, device, format …) kept up to date on photo writes; Insights reads the summaries.
- Spec impact: new tables, write-path hooks, backfill migration, album-scope strategy.
- Pros: read cost independent of library size.
- Cons: most complex; deletes and max-type figures (largest file, first/last capture) need recomputation; album-tree scope does not fit per-owner summaries.
