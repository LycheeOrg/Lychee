# Feature 092 – Bucketing Refactor

| Field | Value |
|-------|-------|
| Status | Testing |
| Last updated | 2026-10-23 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #092 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Photo and album listings are split into buckets (Features 061, 062, 064, 066). A regular album stores a bucket value per photo link (`photo_album`) and per child album (`albums`); tag, person and smart albums and the Timeline compute buckets per request. This feature stores one granularity-free bucket key per row and derives the bucket id from it at read time (ADR-092-01), buckets photo capture dates in the zone the photo was taken in (ADR-092-02), and computes buckets in SQL for every source. Affected layers: bucket computers, recompute jobs, `lychee:recompute-buckets`, the v3 photo `buckets`/`ratios`/`details` actions, the v3 album children and root listings, settings and album/photo edit triggers, migrations, the photo-listing cache, and the v8 date scrubber. v3 response shapes are unchanged.

The feature removes these defects:

| ID | Defect | Effect |
|----|--------|--------|
| D-092-01 | Stored ids carry the granularity they were written with; labels use the current one. `timeline_photos_granularity`, `sorting_photos_col`, `photo_title_bucket_mode` and `photo_title_bucket_prefix_length` never trigger a photo bucket recompute; `timeline_albums_granularity`, `sorting_albums_col` and `title_bucket_*` recompute root albums only. | A `Y-m-d` id formatted with the hour format (`g:i`) reads `12:00`. |
| D-092-02 | A title date has no hour: at hour granularity the stored path writes the hour of the write time, the live path `Y-m-d`. | Labels of title-sorted albums at hour granularity show an arbitrary or midnight hour; stored and live ids differ. |
| D-092-03 | The upload path buckets the cast `taken_at` (original zone); recompute jobs, the live path and the Timeline bucket raw UTC. | Hour buckets, and day/month/year buckets near midnight, depend on which path wrote the row last. |
| D-092-04 | Tag, person and smart albums load every photo row into PHP for each `buckets`, `ratios` window and `details` window request. | Every window request pays a full pass over the album. |
| D-092-05 | Recompute jobs write every row of an album in one `upsert()`. | Above 21,845 photos the statement exceeds PostgreSQL's 65,535 bind parameters and the job fails. |
| D-092-06 | `lychee:takedate` and the bulk renamer change `taken_at`/`title` without recomputing buckets. | Stale buckets until the next recompute. |
| D-092-07 | The regular-album bucket list joins `photos` for every link of the album. | 110–173 ms for a 60,000-photo album on SQLite (Q-092-01 measurements). |

## Goals

- G1 – Bucket ids and labels agree with the settings in effect, with no recompute for granularity or prefix-length changes.
- G2 – Photo capture dates are bucketed in the zone the photo was taken in, on every path.
- G3 – No request loads every photo row of a source into PHP to compute buckets.
- G4 – The regular-album bucket list does not join `photos` per link.
- G5 – One key computation shared by every write path; one read-time scheme shared by every source.

## Non-Goals

- Default value of `timeline_photo_date_format_hour`.
- Date scrubber granularity (Feature 071).
- v8 Timeline layout differences with v7; the v2 Timeline endpoints (`App\Actions\Photo\Timeline`).
- Map geo buckets (Feature 067).
- Capture-local dates for albums (FR-092-04).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-092-01 | `photos.taken_key` holds `taken_at` in the zone `taken_at_orig_tz` as `Y-m-d-H`, `NULL` when `taken_at` is `NULL`. It is assigned explicitly at every write of `taken_at`: upload metadata (`HydrateMetadata`), `PATCH /Photo` (including the reset to `initial_taken_at`), `lychee:takedate`. Uploading a duplicate file goes through the same upload metadata step. | Photo taken 2026-07-01 14:05 UTC in `Europe/Paris` → `2026-07-01-16`. | — | — | none | Q-092-02, ADR-092-02 |
| FR-092-02 | `photos.title_date_key` holds the leading date of the trimmed title as `Y-m-d`: `YYYYMMDD`, `YYYY`, `YYYY-MM` or `YYYY-MM-DD`, missing month and day read as `01`, kept only when it is a valid calendar date; otherwise `NULL`. Eight leading digits are always read as `YYYYMMDD` (an invalid one gives `NULL`, not its year); fewer read as `YYYY` (`202403 x` → `2024-01-01`). The album timeline (`TimelineData::parseDateFromTitle()`), the migration backfill and the date scrubber read the same grammar and give no date for an invalid one. It is assigned explicitly at every write of `title`: upload (`Save` pipe), `PATCH /Photo`, `PATCH /Photo::rename`, the bulk renamer (`RenamePhotos`). | `2024-03 Trip` → `2024-03-01`; `20240315_123456` → `2024-03-15`; `Holiday` → `NULL`; `2024-13-45 x` → `NULL`; `20241345 x` → `NULL`. | — | — | none | Q-092-01, Q-092-08, ADR-092-01 |
| FR-092-03 | `photo_album.bucket_key` holds, for the containing album's effective photo sort column: `TAKEN_AT` → `taken_key`; `CREATED_AT` → `created_at` as stored, `Y-m-d-H`; `TITLE` with `photo_title_bucket_mode = date_prefix` → `title_date_key`; `TITLE` with `alphabetical` → the first 255 characters of `title_base`; `IS_HIGHLIGHTED` → `1`/`0`; `TYPE` → `type`; `RATING_AVG` → the rounded average (`NULL` when never rated); `OWNER_ID` → `NULL` (not bucketable). | Keys never depend on `photo_timeline`, `timeline_photos_granularity` or `photo_title_bucket_prefix_length`. | — | — | none | Q-092-01, ADR-092-01 |
| FR-092-04 | `albums.bucket_key` holds, for the parent's effective album sort column (`album_sorting_col`, root albums and `NULL` → `sorting_albums_col`): `CREATED_AT` → `base_albums.created_at`, `MIN_TAKEN_AT`/`MAX_TAKEN_AT` → `albums.min_taken_at`/`max_taken_at`, each as stored, `Y-m-d`; `TITLE` with `title_bucket_mode = date_prefix` → the title date of FR-092-02; `alphabetical` → the first 255 characters of `title_base`. Album dates are aggregate instants stored and displayed without a zone, so they stay as stored. | Keys never depend on `album_timeline`, `timeline_albums_granularity` or `title_bucket_prefix_length`. | — | — | none | Q-092-01, ADR-092-01 |
| FR-092-05 | The bucket id is a prefix of the key, by sort column: dates (`TAKEN_AT`, `CREATED_AT`, `MIN/MAX_TAKEN_AT`) → 4/7/10/13 characters for year/month/day/hour; title date → the same length capped at 10; alphabetical title → `photo_title_bucket_prefix_length` (photos) or `title_bucket_prefix_length` (albums) characters; `IS_HIGHLIGHTED`, `TYPE`, `RATING_AVG` → the whole key. Granularity resolves as today (album override, else the instance default). A `NULL` key is the `unknown` bucket. Regular-album photo listings and album children/root (`own` scope) listings apply it in SQL (`SUBSTR`) to the stored key. Album listings with no governing parent (tag, person, search) return `unknown` as every per-row album id. | Album at month granularity, key `2026-07-01-16` → id `2026-07`; switching the album to hour shows `2026-07-01-16` with no recompute. | — | — | none | Q-092-01, Q-092-06, G1 |
| FR-092-06 | Tag, person and smart albums, and the Timeline, compute the bucket id in SQL from photo columns with the values FR-092-05 yields for the same photo: `taken_key`, `created_at`, `title_date_key`, `title_base`, `is_highlighted`, `type`, rounded `rating_avg`. The `buckets` tier groups in SQL; `ratios` and `details` windows filter in SQL; per-row ids in `ratios` come from the same expression. The Timeline keeps excluding photos without a value in its sort column. | A 60,000-photo tag album answers a window by reading only that window's rows. | — | — | none | Q-092-01, G3, D-092-04 |
| FR-092-07 | Window filters for one or more bucket ids: date-shaped ids → half-open range on the key or column, `[id, next id of the same precision)`; alphabetical → prefix equality; `IS_HIGHLIGHTED`, `TYPE` → equality; `RATING_AVG` → `[r − 0.5, r + 0.5)` on `rating_avg` for non-stored sources; `unknown` → `NULL`. Rows of every source are ordered by `NULL` key last, then bucket id in the sort direction, then the effective sort criterion, so grouping `ratios` rows by id reproduces the `buckets` tier. | Window `2026-07` on a stored-key album is an index range on `(album_id, bucket_key)`. | — | — | none | Q-092-01 |
| FR-092-08 | Labels are formatted by the precision of each id, not by the configured granularity: 1/2/3/4 segments → `timeline_photo_date_format_year`/`_month`/`_day`/`_hour` (photos), 1/2/3 → `timeline_album_date_format_year`/`_month`/`_day` (albums). Alphabetical, `IS_HIGHLIGHTED`, `TYPE`, `RATING_AVG` ids, `unknown`, and ids of a date scheme that are not date-shaped (`YYYY`, `YYYY-MM`, `YYYY-MM-DD`, `YYYY-MM-DD-HH`; keys written for another scheme, FR-092-13) are returned as is. Formatting uses `date()`/`mktime()`. | Title-sorted album at hour granularity → day ids with day labels. | — | — | none | D-092-01, D-092-02, Q-092-09 |
| FR-092-09 | The regular-album photo `buckets` tier reads `photo_album` only. For a viewer without `may_administrate`, links to photos with `is_validated = false` owned by someone else (anyone, for a guest) are excluded through a subquery on `photos`. | Counts equal those of the joined query for admins, owners and guests. | — | — | none | G4, D-092-07 |
| FR-092-10 | A change of a photo's `taken_at`, `created_at`, `title`, `is_highlighted`, `type` or `rating_avg` recomputes every `photo_album` key of that photo (`RecomputePhotoBucketsJob`), including through `lychee:takedate` and the bulk renamer. | `lychee:takedate` moves a photo to its new hour bucket. | — | — | none | D-092-06 |
| FR-092-11 | A change of an album's `sorting_col` recomputes its `photo_album` keys (`RecomputeAlbumPhotoBucketsJob`). A change of only `sorting_order` or `photo_timeline` rewrites no key and flushes that album's cached photo listing. | Switching an album from day to hour granularity runs no job. | — | — | none | G1 |
| FR-092-12 | A change of an album's `album_sorting_col` recomputes its direct children's keys (`RecomputeChildAlbumBucketsJob`). A change of only `album_sorting_order` or `album_timeline` rewrites no key and flushes the album's children listing. A change of an album's `title`, through `PATCH /Album` or the bulk renamer (`RenameAlbums`), recomputes that album's own `bucket_key` and flushes its parent's children listing. | The bulk renamer turns `Trip` into `2024-03 Trip` under a title-sorted parent → the album moves to the `2024-03` bucket. | — | — | none | G1, Q-092-04 |
| FR-092-13 | Settings: `sorting_photos_col` or `photo_title_bucket_mode` → every `photo_album` key is recomputed (`RecomputeAllPhotoBucketsJob`), then every cached photo listing is flushed; `timeline_photos_granularity`, `photo_title_bucket_prefix_length`, `sorting_photos_order` or a label format of FR-092-08 (`timeline_photo_date_format_year`/`_month`/`_day`/`_hour`) → every cached photo listing is flushed, no key rewritten. `sorting_albums_col` or `title_bucket_mode` → every album key is recomputed (`RecomputeAllAlbumBucketsJob`); `timeline_albums_granularity`, `title_bucket_prefix_length`, `sorting_albums_order` or `timeline_album_date_format_year`/`_month`/`_day` → no key rewritten, every cached album listing is flushed, the child `buckets`/`data`/`rights` tiers included. Until a recompute job has run, listings read the keys written for the previous values with the new scheme: they keep the previous grouping, and ids that are not date-shaped are their own labels (FR-092-08). | Changing `timeline_photos_granularity` from day to hour shows hour buckets in every album inheriting the default; changing `timeline_photo_date_format_day` shows the new labels at once. | — | — | none | D-092-01, Q-092-07, Q-092-09 |
| FR-092-14 | Every bulk key write (jobs, `lychee:recompute-buckets`, migrations) writes at most 1,000 rows per statement. | A 60,000-photo album is recomputed in 60 statements. | — | — | none | D-092-05 |
| FR-092-15 | `lychee:recompute-buckets` rewrites every album key and every `photo_album` key in the FR-092-03/FR-092-04 format, then flushes every cached album listing and every cached photo listing, like `RecomputeAllAlbumBucketsJob` and `RecomputeAllPhotoBucketsJob`; `--chunk` sets the rows per statement (default 1,000). | — | `--chunk` below 1 reads as 1. | — | console progress | — |
| FR-092-16 | Migrations add `photos.taken_key` (indexed) and `photos.title_date_key`, rename `photo_album.bucket_id` and `albums.bucket_id` to `bucket_key` (their composite indexes follow), then backfill `taken_key`, `title_date_key` and every bucket key, chunked, with self-contained helpers (`database/migrations/TemporaryModels/`). | Upgrading an instance shows correct buckets without running a command. | — | — | migration progress bar | Q-092-03 |
| FR-092-17 | Timeline cache invalidation resolves a saved or deleted photo's Timeline bucket from its `taken_key` (`TAKEN_AT`) or `created_at` (`CREATED_AT`) at the current granularity, for the new and the previous value. | Editing a photo's date evicts only the old and new Timeline buckets. | — | — | none | FR-066-09 |
| FR-092-18 | v8 date scrubber (Feature 071): a photo tile's `taken_at` day is its day in `taken_at_orig_tz`, matching the header buckets; `created_at` and album dates stay as stored. | Photo taken 23:30 UTC with zone `+02:00` → rail day and header bucket are the next day. | — | — | none | ADR-071-01, ADR-092-02 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-092-01 | Regular-album photo `buckets` tier for a 60,000-photo album: at least 3.8× faster than the pre-feature query for a non-admin viewer and 5.5× for an admin, measured on the same machine and data (on the reference machine: 173 → ≤ 45 ms, 110 → ≤ 20 ms). | G4 | Scratch benchmark on SQLite with the generated query and the pre-feature query (300,000 photos, 0.2 % unvalidated). | FR-092-09 | Q-092-01, Q-092-05 |
| NFR-092-02 | A window on date-shaped ids over a stored key uses an index range on `(album_id, bucket_key)` / `(parent_id, bucket_key)`. | G1 | SQLite `EXPLAIN QUERY PLAN` in the scratch benchmark. | FR-092-07 | Q-092-01 |
| NFR-092-03 | No request path computes buckets from every photo row of a source in PHP. | G3 | Code review: bucket ids come from SQL on every read path. | FR-092-06 | D-092-04 |
| NFR-092-04 | New bucket code (keys, read scheme, jobs, command, migrations) uses `date()`, `mktime()` or `DateTimeImmutable`, not Carbon. | Resource cost | Code review. | — | Owner preference |
| NFR-092-05 | No new dependency; works offline. | Dependency policy | `composer.json`, `package.json` unchanged. | — | AGENTS.md |
| NFR-092-06 | v3 response shapes unchanged. | Compatibility of the v8 frontend | `php artisan typescript:transform` leaves `resources/js/lychee.d.ts` unchanged. | — | — |

## UI / Interaction Mock-ups

No layout change. The date scrubber rail (Feature 071) changes which day a photo counts for (FR-092-18):

```
Header bucket (hour)      Rail day (before)   Rail day (after)
2026-07-02 01:00 (+02)    2026-07-01          2026-07-02
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-092-01 | Album sorted by `taken_at` at day granularity, switched to hour → no job dispatched; `buckets` returns hour ids with hour labels matching each id (FR-092-05, FR-092-08, FR-092-11). |
| S-092-02 | `timeline_photos_granularity` changed → albums inheriting it return the new granularity; their cached listings are flushed; no job dispatched (FR-092-13). |
| S-092-03 | Title-sorted album (`date_prefix`) at hour granularity → day ids, day labels (FR-092-05, FR-092-08). |
| S-092-04 | Photo taken 14:05 UTC in `Europe/Paris` (summer) → `taken_key` `…-16` after upload, after `PATCH /Photo` and after `RecomputePhotoBucketsJob`; hour bucket `…-16` in the album and the Timeline (FR-092-01, FR-092-06). |
| S-092-05 | Tag album, each sort column (`taken_at`, `created_at`, title date, title alphabetical, highlighted, type, rating) → `buckets` ids and counts as expected; `ratios` and `details` windows return exactly the bucket's photos; per-row `ratios` ids equal the window ids (FR-092-06, FR-092-07). |
| S-092-06 | Smart album with the instance default sort → as S-092-05 for that sort (FR-092-06). |
| S-092-07 | Timeline → buckets from `taken_key`; windows by range; photos without a date excluded (FR-092-06). |
| S-092-08 | Album `sorting_col` changed → `RecomputeAlbumPhotoBucketsJob` dispatched and keys rewritten; only `sorting_order` changed → no job, cached listing flushed (FR-092-11). |
| S-092-09 | `sorting_photos_col` or `photo_title_bucket_mode` changed → `RecomputeAllPhotoBucketsJob` dispatched, keys rewritten, listings flushed; `photo_title_bucket_prefix_length` changed → listings flushed only (FR-092-13). |
| S-092-10 | Album children and root (`own`): ids are key prefixes; parent `album_timeline` change → no job; `album_sorting_col` change → `RecomputeChildAlbumBucketsJob`; `sorting_albums_col` change → `RecomputeAllAlbumBucketsJob` rewrites every album key, children of parents without an override included (FR-092-04, FR-092-05, FR-092-12, FR-092-13). |
| S-092-11 | Album with an unvalidated photo of another owner → excluded from a guest's and a non-owner's counts, included for its owner and an admin (FR-092-09). |
| S-092-12 | Recompute of an album with 1,001 linked photos → two statements, every key correct (FR-092-14). |
| S-092-13 | `lychee:takedate` changes `taken_at` → `taken_key` updated and the photo's links recomputed; bulk renamer changes titles → `title_date_key` updated and links recomputed (FR-092-01, FR-092-02, FR-092-10). |
| S-092-14 | Title `2024-13-45 x` → `title_date_key` `NULL` → `unknown` bucket (FR-092-02). |
| S-092-15 | `NULL` keys → `unknown` bucket last in both sort directions, for every source (FR-092-05, FR-092-07). |
| S-092-16 | v8 album with the date scrubber, photo taken 23:30 UTC with zone `+02:00` → rail day equals the header bucket day (FR-092-18). |
| S-092-17 | `lychee:recompute-buckets` → every key in the FR-092-03/04 format; album and photo listing caches flushed (FR-092-15). |
| S-092-18 | Photo date edited → the Timeline cache entries of the old and new buckets are evicted, others stay (FR-092-17). |
| S-092-19 | Bulk renamer gives a child of a title-sorted (`date_prefix`) parent a dated title → the child's `bucket_key` becomes the title date and the parent's children listing is flushed (FR-092-12). |
| S-092-20 | `sorting_photos_order` changed → every cached photo listing is flushed, no job dispatched (FR-092-13). |
| S-092-21 | Title `20240315_123456` → `title_date_key` `2024-03-15`, album timeline and scrubber day `2024-03-15`; title `20241345 x` or `2023-12-32 x` → `NULL` in all three (FR-092-02). |
| S-092-22 | A photo or album label format (`timeline_photo_date_format_*`, `timeline_album_date_format_*`) or `timeline_albums_granularity` changed → every cached photo or album listing, child-album tiers included, is flushed, no job dispatched; the next `buckets` request returns the new labels and ids (FR-092-08, FR-092-13). |
| S-092-23 | Root sorted by title, `title_bucket_mode` changed from `alphabetical` to `date_prefix`, recompute job not run yet → `buckets` ids `2019` and `alph` (titles `2019-05 Paris`, `Alpha`) with the same labels; after the job → `2019`, `unknown` (FR-092-08, FR-092-13). |

## Test Strategy

- **Unit (`tests/Unit`, `AbstractTestCase`):** key helpers (capture key across zones and DST, title date key including invalid dates), bucket key computation per sort column, read scheme (prefix lengths, next id per precision, labels by precision).
- **Precomputing (`tests/Precomputing`):** recompute jobs and `lychee:recompute-buckets` (format, chunking with 1,001 rows, all-photo/all-album jobs), upload path keys.
- **Feature (`tests/Feature_v3`, `BaseApiWithDataTest`):** v3 `buckets`/`ratios`/`details` for a regular album, a tag album, a smart album and the Timeline (S-092-01 … S-092-07, S-092-11, S-092-15); album children and root listings (S-092-10); settings and edit triggers (S-092-02, S-092-08, S-092-09); Timeline cache eviction (S-092-18). Existing classes that read the stored column or assert the removed triggers are updated.
- **Frontend (v8):** no unit-test runner; `npm run check`, `npm run format`, and a browser check of S-092-16.
- **Performance:** scratch benchmark for NFR-092-01 and NFR-092-02 (SQLite; MySQL and PostgreSQL through CI for correctness only).

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-092-01 | `photos.taken_key` `varchar(13)` nullable, indexed: capture-local `Y-m-d-H`. | Photo model, write sites, migrations |
| DO-092-02 | `photos.title_date_key` `varchar(10)` nullable: title date `Y-m-d`. | Photo model, write sites, migrations |
| DO-092-03 | `photo_album.bucket_key` `varchar(255)` nullable, index `(album_id, bucket_key)`. | Pivot writes, v3 photo actions |
| DO-092-04 | `albums.bucket_key` `varchar(255)` nullable, index `(parent_id, bucket_key)`. | Album stats/recompute jobs, v3 album listings |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-092-01 | REST | `GET /api/v3/Albums/{album_id}/Photos` (ratios), `…/Photos/buckets`, `…/Photos/details` | Shapes unchanged; ids per FR-092-05/06. |
| API-092-02 | REST | `GET /api/v3/Albums/{album_id}` (children data), `…/{album_id}/buckets`, `GET /api/v3/Albums/root`, `…/root/buckets` | Shapes unchanged; ids per FR-092-05. |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-092-01 | `php artisan lychee:recompute-buckets [--chunk=1000]` | FR-092-15. |

### Telemetry Events

None.

### Fixtures & Sample Data

None; tests build their photos and albums.

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-092-01 | Scrubber rail days | Photo tiles count for their capture-local day (FR-092-18). |

## Telemetry & Observability

No new telemetry.

## Documentation Deliverables

- ADR-092-01, ADR-092-02.
- Knowledge map entries for the bucket key, the read scheme and the new jobs.
- Feature 064/066/071 specs reference this feature where they describe stored or live bucket ids.


## Spec DSL

```
domain_objects:
  - id: DO-092-01
    name: photos.taken_key
  - id: DO-092-02
    name: photos.title_date_key
  - id: DO-092-03
    name: photo_album.bucket_key
  - id: DO-092-04
    name: albums.bucket_key
cli_commands:
  - id: CLI-092-01
    command: lychee:recompute-buckets
routes:
  - id: API-092-01
    method: GET
    path: /api/v3/Albums/{album_id}/Photos[/buckets|/details]
  - id: API-092-02
    method: GET
    path: /api/v3/Albums/{album_id|root}[/buckets]
ui_states:
  - id: UI-092-01
    description: scrubber rail counts photos by capture-local day
```
