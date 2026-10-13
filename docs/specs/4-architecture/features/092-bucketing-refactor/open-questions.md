# Open Questions – Feature 092

Open questions for [Feature 092](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-092-01~~ | 092 – Bucketing refactor | High | What does a stored bucket id hold? (A finest-grained key per sort column, granularity applied at read time as a SQL prefix, SQL for every source · B today's model with the missing recompute triggers · C no stored id, computed in SQL at read time) | Resolved (Option A; owner, 2026-10-12; spec FR-092-02 … FR-092-09, ADR-092-01) | 2026-10-12 | 2026-10-12 |
| ~~Q-092-02~~ | 092 – Bucketing refactor | High | Time basis of date buckets (A capture-local time, stored local key on `photos` · B UTC everywhere) | Resolved (Option A; owner, 2026-10-12; spec FR-092-01, FR-092-18, ADR-092-02) | 2026-10-12 | 2026-10-12 |
| ~~Q-092-03~~ | 092 – Bucketing refactor | High | Persistence changes and file replacement (A rename `bucket_id` → `bucket_key`, two new `photos` columns, backfill migrations, `RecomputeRootAlbumBucketsJob` replaced by `RecomputeAllAlbumBucketsJob` · B same, keeping the column name `bucket_id`) | Resolved (Option A; owner, 2026-10-12; spec FR-092-13, FR-092-16, plan Approvals) | 2026-10-12 | 2026-10-12 |

## Question Details

### ~~Q-092-01~~ – What a stored bucket id holds

**Context:** See [spec.md](spec.md) Overview for the defects. Today `photo_album.bucket_id` and `albums.bucket_id` hold the id truncated at the granularity in effect when the row was written; labels are formatted at read time with the granularity in effect now. Tag, person and smart albums have no stored id: each `buckets`, `ratios` window and `details` window request loads every photo row of the album into PHP. The Timeline groups in SQL on the raw column.

Measured on SQLite 3.46 (scratchpad, synthetic data: 300,000 photos, one album with 60,000 photos, 0.2 % unvalidated, production indexes), with the query shape the application generates (`photos` joined to `photo_album`; non-admin viewers add the upload-validation filter), month granularity:

| Query | Non-admin | Admin |
|-------|-----------|-------|
| Bucket list from the stored id (today) | 173 ms | 110 ms |
| Bucket list as a prefix of a finest-grained stored key, joined | 89 ms | 78 ms |
| Bucket list as a prefix of a finest-grained stored key, `photo_album` only, unvalidated photos excluded by subquery | 42 ms | 15 ms |
| Bucket list computed from `photos` columns, joined | 84 ms | — |
| Window of 3 months, stored id `IN` (today) | 2.0 ms | — |
| Window of 3 months, prefix range on the stored key | 2.1 ms | — |
| Window of 3 months, range on `photos.taken_at` | 86 ms | — |
| Fetching every row for the PHP path (tag, person, smart albums), before the PHP loop | 145 ms | — |
| Rewriting 60,000 keys: set-based `UPDATE … SELECT` / fetch + PHP loop + update | 282 / 356 ms | — |

- **Option A (chosen):** Granularity-free stored key, one bucket scheme for every source.
  - The stored column holds the finest-grained key for the parent's effective sort column: the date to the hour (photos) or day (albums), the title date, a `title_base` prefix of 255 characters, or the categorical value (highlighted, type, rounded rating).
  - Granularity and title prefix length are applied at read time as a SQL prefix of the key. Window fetches become prefix ranges on the `(album_id, bucket_key)` / `(parent_id, bucket_key)` index.
  - Tag, person, smart albums and the Timeline group and filter with the same scheme in SQL, replacing the PHP row scans.
  - The key is rewritten only when the sort column or title bucket mode changes, per album or through the global defaults, in chunks.
  - ✅ A granularity or prefix-length change cannot leave stale ids: ids and labels come from the same settings in the same request.
  - ✅ Tag, person and smart albums no longer load every row into PHP per request.
  - ✅ Bucket list 173 → 42 ms (non-admin) and 110 → 15 ms (admin) with the `photo_album`-only read; windows unchanged.
  - ❌ Migration rewrites every stored key; bucket queries and their tests change.
- **Option B:** Keep today's model and close the gaps (triggers for every global setting, chunked writes, one time basis, SQL grouping for tag, person and smart albums).
  - ✅ No schema change.
  - ❌ Every setting that feeds the id needs its own trigger; a global granularity change rewrites every pivot row of the library through the queue, labels wrong until it ends.
- **Option C:** No stored id; every bucket computed in SQL at read time.
  - ✅ No recompute jobs, no backfill command, nothing can go stale.
  - ❌ Windows 2 → 86 ms on a 60,000-photo album; bucket list stays joined (84 ms).

**Resolution:** Option A. Recorded in [spec.md](spec.md) FR-092-02 … FR-092-09, FR-092-11 … FR-092-13 and [ADR-092-01](../../../6-decisions/ADR-092-01-granularity-free-bucket-keys.md). Album dates (`created_at`, `min_taken_at`, `max_taken_at`) are keyed as stored (FR-092-04).

### ~~Q-092-02~~ – Time basis of date buckets

**Context:** `photos.taken_at` is stored in UTC with its original zone in `taken_at_orig_tz`. The upload path (`SetParent`) buckets the cast `taken_at`, which is in the original zone; the recompute jobs, the live PHP path and the Timeline SQL bucket the raw UTC value. The photo panel, the v7 timeline grouping (`PhotoResource::timeline_date_carbon()`, the cast `taken_at`) and Insights (Q-085-08) use capture-local time. Portable SQL cannot convert zones: MySQL `CONVERT_TZ()` with zone names needs the time zone tables loaded, SQLite has no zone support.

- **Option A (chosen):** Capture-local time.
  - A key of the local capture time (to the hour) is stored on `photos`, written wherever `taken_at` is written; SQL-side sources group on it.
  - `created_at` has no original zone and stays as stored.
  - ✅ A photo the panel shows at 16:05 lands in the 16:00 bucket.
  - ✅ Same basis as v7, the upload path and Insights.
  - ❌ One more column on `photos`, its writes and a backfill.
- **Option B:** UTC everywhere.
  - ✅ Raw-column SQL on every driver, no new column.
  - ❌ Hour buckets shifted by the zone offset (16:05 CEST lands under 14:00); day, month and year buckets wrong near midnight.
  - ❌ Differs from v7 and the photo panel.

**Resolution:** Option A. Recorded in [spec.md](spec.md) FR-092-01, FR-092-18 and [ADR-092-02](../../../6-decisions/ADR-092-02-capture-local-photo-date-buckets.md).

### ~~Q-092-03~~ – Persistence changes and file replacement

**Context:** Q-092-01 A and Q-092-02 A change persistence (AGENTS.md escalation policy) and replace one job:

- New `photos.taken_key` (`varchar(13)`, indexed) and `photos.title_date_key` (`varchar(10)`).
- `photo_album.bucket_id` and `albums.bucket_id` hold keys instead of ids.
- Backfill migrations rewrite `taken_key`, `title_date_key` and every bucket key on upgrade, chunked by 1,000 rows (Feature 060 precedent), so no command has to be run after upgrading. A rollback restores the column names; `lychee:recompute-buckets` of the earlier version then rewrites the old ids.
- `RecomputeRootAlbumBucketsJob` recomputes root albums only; FR-092-13 needs every album (children of parents without an override inherit the default too). It is replaced by `RecomputeAllAlbumBucketsJob`, which removes `app/Jobs/RecomputeRootAlbumBucketsJob.php` and `tests/Precomputing/CoverSelection/RecomputeRootAlbumBucketsJobTest.php` (their content moves to the new job and its test).

- **Option A (chosen):** As listed, with `photo_album.bucket_id` and `albums.bucket_id` renamed to `bucket_key` (composite indexes recreated under the new name).
  - ✅ The name states what the column holds: a key whose prefix is the bucket id.
  - ✅ A reader missed during the refactor fails in tests instead of silently returning full keys as ids.
  - ❌ Two more schema steps (drop index, rename, create index) per table.
- **Option B:** As listed, keeping the column name `bucket_id`.
  - ✅ Fewer schema steps.
  - ❌ `bucket_id` holds something other than the bucket id.
  - ❌ A missed reader keeps working and returns full keys.

**Resolution:** Option A. The owner approved the schema changes and the removal of `app/Jobs/RecomputeRootAlbumBucketsJob.php` and `tests/Precomputing/CoverSelection/RecomputeRootAlbumBucketsJobTest.php`. Recorded in [spec.md](spec.md) FR-092-13, FR-092-16 and [plan.md](plan.md) Approvals.
