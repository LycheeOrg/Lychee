# Open Questions – Feature 092

Open questions for [Feature 092](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-092-01~~ | 092 – Bucketing refactor | High | What does a stored bucket id hold? (A finest-grained key per sort column, granularity applied at read time as a SQL prefix, SQL for every source · B today's model with the missing recompute triggers · C no stored id, computed in SQL at read time) | Resolved (Option A; owner, 2026-10-12; spec FR-092-02 … FR-092-09, ADR-092-01) | 2026-10-12 | 2026-10-12 |
| ~~Q-092-02~~ | 092 – Bucketing refactor | High | Time basis of date buckets (A capture-local time, stored local key on `photos` · B UTC everywhere) | Resolved (Option A; owner, 2026-10-12; spec FR-092-01, FR-092-18, ADR-092-02) | 2026-10-12 | 2026-10-12 |
| ~~Q-092-03~~ | 092 – Bucketing refactor | High | Persistence changes and file replacement (A rename `bucket_id` → `bucket_key`, two new `photos` columns, backfill migrations, `RecomputeRootAlbumBucketsJob` replaced by `RecomputeAllAlbumBucketsJob` · B same, keeping the column name `bucket_id`) | Resolved (Option A; owner, 2026-10-12; spec FR-092-13, FR-092-16, plan Approvals) | 2026-10-12 | 2026-10-12 |
| ~~Q-092-04~~ | 092 – Bucketing refactor | Medium | The bulk album renamer (`RenameAlbums`) rewrites album titles with `batch()->update()`, so `albums.bucket_key` (title-sorted parents) is never recomputed and album listings are not flushed (A recompute the renamed albums' keys and flush · B follow-up feature) | Resolved (Option A; owner, 2026-10-14; spec FR-092-12, S-092-19) | 2026-10-14 | 2026-10-14 |
| ~~Q-092-05~~ | 092 – Bucketing refactor | Medium | NFR-092-01 states absolute times (≤ 45 / ≤ 20 ms); the drift gate measured 81 / 33 ms and passed it as a speed-up ratio (A restate as a ratio against the old query on the same machine · B keep absolute, re-measure on the reference machine) | Resolved (Option A; owner, 2026-10-14; spec NFR-092-01) | 2026-10-14 | 2026-10-14 |
| ~~Q-092-06~~ | 092 – Bucketing refactor | Medium | Tag, person and search album listings now return `unknown` as every per-row album `bucket_id`, which no FR describes (A record it in FR-092-05 · B keep a computed id) | Resolved (Option A; owner, 2026-10-14; spec FR-092-05) | 2026-10-14 | 2026-10-14 |
| ~~Q-092-07~~ | 092 – Bucketing refactor | Medium | FR-092-13 is silent on `sorting_photos_order`; no settings list flushes cached photo listings when it changes (A add it to the photo-listing flush · B leave out of scope) | Resolved (Option A; owner, 2026-10-14; spec FR-092-13, S-092-20) | 2026-10-14 | 2026-10-14 |
| ~~Q-092-08~~ | 092 – Bucketing refactor | Low | How does the title date read `YYYYMMDD`? (A optional dashes `-?` · B a separate eight-digit branch) | Resolved (Option B; owner, 2026-10-14; spec FR-092-02, S-092-21) | 2026-10-14 | 2026-10-14 |

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

### ~~Q-092-04~~ – Bulk album renamer and album bucket keys

**Context:** FR-092-04 makes `albums.bucket_key` depend on the album title when the parent sorts by `TITLE` (`date_prefix` → title date, `alphabetical` → `title_base`). `PATCH /Album` recomputes it through `AlbumSaved` → `RecomputeAlbumStatsJob`. The bulk renamer (`RenameAlbums`, called by `RenamerController`) writes `title`/`title_base` with `batch()->update()` and dispatches nothing, so the key and the parent's cached children listing go stale. This is the album side of D-092-06, which FR-092-10 fixes for photos only.

- **Option A (chosen):** FR-092-12 gains "a bulk album rename recomputes the renamed albums' `bucket_key` and flushes their parents' children listings"; `RenameAlbums` dispatches the existing per-album recompute (or `AlbumSaved`) for albums whose title changed.
  - ✅ Closes the same defect class as D-092-06 for albums.
  - ✅ Reuses an existing job and event, no new code path.
  - ❌ One job per renamed album (bounded by the renamer's selection).
- **Option B:** Leave it out of scope and record it as a follow-up.
  - ✅ No scope growth.
  - ❌ Stale album buckets after a bulk rename until the next stats recompute.

**Resolution:** Option A. FR-092-12 now requires a title change through `PATCH /Album` or `RenameAlbums` to recompute the album's own `bucket_key` and flush its parent's children listing; scenario S-092-19.

### ~~Q-092-05~~ – NFR-092-01 measured as absolute or relative

**Context:** NFR-092-01 requires ≤ 45 ms (non-admin) and ≤ 20 ms (admin) for the bucket list of a 60,000-photo album. On the implementation machine the old query takes 339 / 199 ms (1.9× slower than the owner's 173 / 110 ms) and the new one 81 / 33 ms. The drift gate passed the NFR by its ratio (4.2× / 6.0× ≥ the required 3.8× / 5.5×), which the NFR does not say.

- **Option A (chosen):** Restate NFR-092-01 as a speed-up against the old query on the same machine and data (≥ 3.8× non-admin, ≥ 5.5× admin), keeping 45 / 20 ms as the reference-machine figures.
  - ✅ Measurable on any machine; matches what was measured.
  - ❌ The NFR no longer states a hard latency.
- **Option B:** Keep the absolute figures and have the owner re-run the scratch benchmark on the reference machine.
  - ✅ The NFR stays a hard latency.
  - ❌ Only verifiable on one machine.

**Resolution:** Option A. NFR-092-01 is now a speed-up against the pre-feature query on the same machine (≥ 3.8× non-admin, ≥ 5.5× admin), with 45 / 20 ms kept as the reference-machine figures.

### ~~Q-092-06~~ – Per-row album bucket ids outside the `own` scope

**Context:** FR-092-05 defines album ids for the children and root (`own`) listings only. During implementation, tag, person and search album listings (`BuildAlbumDataResource` without a scheme) were changed to return `unknown` for every per-row `bucket_id`. Before, they echoed the stored id computed for the album's own parent. The response shape is unchanged (NFR-092-06), but the values change, and no FR says so. The plan notes that no client reads per-row album ids (`albumBucketBoundaries.ts` reads only the `buckets` tier).

- **Option A (chosen):** Add to FR-092-05: "listings without a governing parent (tag, person, search) return `unknown` per-row album ids".
  - ✅ The spec matches the code; such listings have no parent sort to bucket by.
  - ❌ Any future client reading per-row ids there gets no grouping.
- **Option B:** Compute ids from the instance default album sort for those listings.
  - ✅ Non-trivial ids everywhere.
  - ❌ New code path, and the ids would not match any `buckets` tier.

**Resolution:** Option A. FR-092-05 now states that album listings with no governing parent (tag, person, search) return `unknown` per-row album ids.

### ~~Q-092-07~~ – `sorting_photos_order` and cached photo listings

**Context:** FR-092-13 lists the settings that recompute keys or flush photo listings and covers `sorting_albums_order` for albums, but not `sorting_photos_order`. `SettingsController` flushes photo listings for `timeline_photos_granularity` and `photo_title_bucket_prefix_length` only, so after changing the instance photo order, cached listings of albums inheriting it keep the old row and bucket order until they expire. FR-092-11 already flushes on an album's own `sorting_order` change.

- **Option A (chosen):** Add `sorting_photos_order` to FR-092-13's "flush every cached photo listing, no key rewritten" list (and to `PHOTO_LISTING_COARSE_FLUSH_CONFIGS`).
  - ✅ Consistent with FR-092-11 and the album side.
  - ❌ Slight scope growth (one config key, one test).
- **Option B:** Leave it out of scope (it predates this feature) and record a follow-up.
  - ✅ No scope growth.
  - ❌ Stale order after an instance-wide change.

**Resolution:** Option A. FR-092-13 now lists `sorting_photos_order` among the settings that flush every cached photo listing without rewriting keys; scenario S-092-20.

### ~~Q-092-08~~ – Title date grammar for `YYYYMMDD`

**Context:** The owner asked for `YYYYMMDD` titles (e.g. `20240315_123456`) on top of `YYYY`, `YYYY-MM`, `YYYY-MM-DD` (FR-092-02). The same grammar lives in `BucketKeys`, `BucketKeyBackfill`, `TimelineData::parseDateFromTitle()` and `dateScrubber.ts`.

- **Option A (recommended):** Make each dash optional: `/^(\d{4})(?:-?(\d{2}))?(?:-?(\d{2}))?/`.
  - ✅ Smallest change, same three groups, same shape in PHP and JS.
  - ✅ Also reads `YYYYMM` (`202403 x` → `2024-03-01`).
  - ❌ Also accepts mixed forms (`2024-0315`, `202403-15`) and reads seven digits as a month (`2024123 x` → `2024-12-01`).
- **Option B (chosen):** A separate branch that only takes exactly `YYYYMMDD`: `/^(\d{4})(?|(\d{2})(\d{2})|(?:-(\d{2}))?(?:-(\d{2}))?)/` (JS: alternation, groups 2/3 or 4/5).
  - ✅ Accepts exactly the four listed forms.
  - ❌ Harder to read, branch reset is PCRE-only so JS reads different groups.

**Resolution:** Option B (the owner first chose A, then switched to B). The owner also asked that every parser reject dates that are not on the calendar: `TimelineData::parseDateFromTitle()` no longer rolls `2023-13-01` over to January 2024 and `dateScrubber.ts` now checks the date, matching the `checkdate()` already in `BucketKeys` and `BucketKeyBackfill`. Recorded in FR-092-02 and S-092-21.
