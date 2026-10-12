# Open Questions – Feature 091

Open questions for [Feature 091](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| Q-091-01 | 091 – Bucketing refactor | High | What does a stored bucket id hold? (A finest-grained key per sort column, granularity applied at read time as a SQL prefix, SQL for every source · B today's model with the missing recompute triggers · C no stored id, computed in SQL at read time) | Open | 2026-10-12 | 2026-10-12 |
| Q-091-02 | 091 – Bucketing refactor | High | Time basis of date buckets (A capture-local time, stored local key on `photos` · B UTC everywhere) | Open | 2026-10-12 | 2026-10-12 |

## Question Details

### Q-091-01 – What a stored bucket id holds

**Context:** See [spec.md](spec.md) Overview for the defects. Today `photo_album.bucket_id` and `albums.bucket_id` hold the id truncated at the granularity in effect when the row was written; labels are formatted at read time with the granularity in effect now. Tag, person and smart albums have no stored id: each `buckets`, `ratios` window and `details` window request loads every photo row of the album into PHP. The Timeline groups in SQL on the raw column.

Measured on SQLite 3.46 (scratchpad, synthetic data: 300,000 photos, one album with 60,000 photos, `(album_id, bucket_id)` index as in production):

| Query | Time |
|-------|------|
| Bucket list from the stored id (today) | 4.2 ms |
| Bucket list computed from `photos` through the pivot join | 83–95 ms |
| Bucket list as a prefix of a finest-grained stored key: year / month / day / hour | 14 / 15 / 19 / 39 ms |
| Window, stored id `IN` 3 months (today) | 0.5 ms |
| Window, prefix range on a finest-grained stored key | 0.6 ms |
| Window, range on `photos.taken_at` through the pivot join | 63 ms |
| Fetching every row for the PHP path (tag, person, smart albums), before the PHP loop | 145 ms |
| Rewriting 60,000 ids: set-based `UPDATE … SELECT` / fetch + PHP loop + update | 282 / 356 ms |

The hour figure is high because random synthetic dates put almost every photo in its own hour (51,346 buckets).

- **Option A (recommended):** Granularity-free stored key, one bucket expression for every source.
  - The stored column holds the finest-grained key for the parent's effective sort column: the date to the hour (photos) or day (albums), the title date prefix, a `title_base` prefix at the longest allowed length, or the categorical value (highlighted, type, rounded rating).
  - Granularity and title prefix length are applied at read time as a SQL prefix of the key; the query stays on the covering `(album_id, bucket_id)` / `(parent_id, bucket_id)` index. Window fetches become prefix ranges on the same index.
  - Tag, person, smart albums and the Timeline group and filter with the same expression in SQL, replacing the PHP row scans.
  - One key function for the write paths and one matching SQL expression, replacing the three implementations of today (Carbon-based computer, string-based live path, Timeline SQL).
  - The key is rewritten only when the sort column or title bucket mode changes, per album or through the global defaults (trigger added), in chunks.
  - ✅ A granularity or prefix-length change cannot leave stale ids: ids and labels come from the same settings in the same request.
  - ✅ Tag, person and smart albums no longer load every row into PHP per request.
  - ✅ Fewer recompute triggers and jobs.
  - ❌ Album bucket list 4 → 15–19 ms on a 60,000-photo album (the response is cached).
  - ❌ Migration rewrites every stored id; bucket queries and their tests change.
- **Option B:** Keep today's model and close the gaps.
  - Recompute on every global config change that feeds the stored id, for every album inheriting the default; chunked writes; one time basis; the title-date hour fix; SQL grouping instead of the PHP scans for tag, person and smart albums.
  - ✅ Album bucket list stays at 4 ms.
  - ✅ No schema change.
  - ❌ Every setting that feeds the id needs its own trigger; a missing one shows wrong labels until the next recompute.
  - ❌ A global granularity change rewrites every pivot row of the library through the queue; labels are wrong until it ends.
- **Option C:** No stored id; every bucket computed in SQL at read time.
  - ✅ No recompute jobs, no backfill command, nothing can go stale.
  - ✅ One code path for every source.
  - ❌ Measured 20× slower bucket list (4 → 83–95 ms) and 100× slower windows (0.5 → 63 ms) on a 60,000-photo album.

### Q-091-02 – Time basis of date buckets

**Context:** `photos.taken_at` is stored in UTC with its original zone in `taken_at_orig_tz`. The upload path (`SetParent`) buckets the cast `taken_at`, which is in the original zone; the recompute jobs, the live PHP path and the Timeline SQL bucket the raw UTC value. The photo panel, the v7 timeline grouping (`PhotoResource::timeline_date_carbon()`, the cast `taken_at`) and Insights (Q-085-08) use capture-local time. Portable SQL cannot convert zones: MySQL `CONVERT_TZ()` with zone names needs the time zone tables loaded, SQLite has no zone support.

- **Option A (recommended):** Capture-local time.
  - A key of the local capture time (to the hour) is stored on `photos`, written wherever `taken_at` is written; SQL-side sources group on it.
  - `created_at` has no original zone and stays as stored.
  - ✅ A photo the panel shows at 16:05 lands in the 16:00 bucket.
  - ✅ Same basis as v7, the upload path and Insights.
  - ❌ One more column on `photos`, its writes and a backfill.
- **Option B:** UTC everywhere.
  - ✅ Raw-column SQL on every driver, no new column.
  - ❌ Hour buckets shifted by the zone offset (16:05 CEST lands under 14:00); day, month and year buckets wrong near midnight.
  - ❌ Differs from v7 and the photo panel.
