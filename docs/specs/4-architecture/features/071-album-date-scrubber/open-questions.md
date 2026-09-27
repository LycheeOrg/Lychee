# Open Questions – Feature 071

Open questions for [Feature 071](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-071-01~~ | 071 – Album Date Scrubber | High | "Only available if the album has full photos or full albums" — does that mean the album contains *only* photos or *only* sub-albums, or something else? It decides which grid the rail maps its scroll position onto. | Resolved (Option A — exactly one visible kind; decided client-side from bucket-tier counts so hidden sub-albums do not block; owner, 2026-09-24) → spec FR-071-06, NG5 | 2026-09-24 | 2026-09-24 |
| ~~Q-071-02~~ | 071 – Album Date Scrubber | High | What does "togglable" mean next to "configurable per album": a viewer show/hide button, the per-album override itself, or both? And how do the global, per-album and viewer layers combine? | Resolved (Option A — global → per-album nullable override → viewer toggle in localStorage; owner, 2026-09-24) → spec FR-071-01/02/03/07 | 2026-09-24 | 2026-09-24 |
| ~~Q-071-03~~ | 071 – Album Date Scrubber | Medium | Global default of the new gallery setting: off or on? | Resolved (Option B — default on; owner, 2026-09-24) → spec FR-071-01 | 2026-09-24 | 2026-09-24 |
| ~~Q-071-04~~ | 071 – Album Date Scrubber | High | Albums sorted by a non-date column (title, rating, type, highlighted) have non-date bucket IDs. The rail's tick logic parses `YYYY-MM-DD`. Hide the rail there, or generalise it to label-based buckets (e.g. an A–Z rail)? | Resolved (Option A, extended by owner: date sort columns **plus** title sort in `DATE_PREFIX` bucket mode; owner, 2026-09-24) → spec FR-071-04/05/08, NG6 | 2026-09-24 | 2026-09-24 |
| ~~Q-071-05~~ | 071 – Album Date Scrubber | High | Side note from the owner: should bucketing be refactored to always persist the smallest granularity (day) and roll it up at read time? Is it in scope here, a prerequisite, or a separate feature? | Resolved (Option A — rail derives day entries client-side, bucket storage untouched, refactor deferred to its own feature; owner, 2026-09-24) → spec FR-071-08, NG4, ADR-071-01 | 2026-09-24 | 2026-09-24 |

## Question Details

### ~~Q-071-01~~ · What counts as "full photos or full albums"? ✅ RESOLVED

**Status:** Resolved (Option A — exactly one visible kind; decided client-side from bucket-tier counts so hidden sub-albums do not block; owner, 2026-09-24) → spec FR-071-06, NG5

**Feature:** F-071 – Album Date Scrubber · **Priority:** High · **Opened:** 2026-09-24

**Context:** On the v8 SoA path, `AlbumPanel.vue` stacks two independently virtualised, independently bucketed grids in one page scroll: `AlbumThumbPanelVirtual` (sub-albums, tier `/Albums/{id}/buckets`) above `PhotoThumbPanelVirtual` (photos, `/Albums/{id}/Photos/buckets`). The rail maps one scroll range onto one bucket list, so it needs exactly one grid to describe.

**Option A (recommended) — "exclusively one kind":** the rail is available when the album has photos and no sub-albums, or sub-albums and no photos. Each case drives the rail from that single grid's buckets.
- ✅ Matches the wording; one scroll range and one bucket list, so the Timeline rail's math carries over unchanged.
- ✅ Covers the common "leaf album of photos" case.
- ❌ Mixed albums (a few sub-albums above many photos) never get a rail.

**Option B — "photos present":** the rail is available whenever the album has photos. It spans only the photo grid, and sub-albums above it are treated as a header offset.
- ✅ Mixed albums get a rail too.
- ❌ The rail's top does not match the page top; scrubbing to the top of the rail lands on the first photo, not on the sub-albums.

**Option C — "spans both":** concatenate the album buckets and photo buckets into one rail.
- ✅ Covers everything.
- ❌ Two different date axes (album `max_taken_at` vs photo `taken_at`) interleave into a non-monotonic rail, which is confusing and the most work.

---

### ~~Q-071-02~~ · "Togglable" + "configurable per album" — which layers? ✅ RESOLVED

**Status:** Resolved (Option A — global → per-album nullable override → viewer toggle in localStorage; owner, 2026-09-24) → spec FR-071-01/02/03/07

**Feature:** F-071 – Album Date Scrubber · **Priority:** High · **Opened:** 2026-09-24

**Option A (recommended) — three layers:** (1) global `configs` default; (2) a per-album nullable override on `base_albums` (`null` = follow global, `true`/`false` forces it), set by album editors in the album properties drawer, following the `photo_timeline` DEFAULT-override precedent; (3) a viewer show/hide button in the album header, remembered per browser in `localStorage`. The button is only shown when layers 1+2 resolve to "on" and the album is eligible.
- ✅ Satisfies all three words: global, per album, togglable.
- ✅ The viewer toggle needs no backend work.
- ❌ One more header button.

**Option B — two layers (global + per-album override):** "togglable" means the per-album switch itself; viewers cannot hide it.
- ✅ Smallest surface.
- ❌ A viewer who finds the rail distracting has no recourse.

**Option C — two layers (global + viewer toggle):** no per-album column.
- ✅ No migration.
- ❌ Contradicts "configurable per album".

---

### ~~Q-071-03~~ · Global default: off or on? ✅ RESOLVED

**Status:** Resolved (Option B — default on; owner, 2026-09-24) → spec FR-071-01

**Feature:** F-071 – Album Date Scrubber · **Priority:** Medium · **Opened:** 2026-09-24

**Option A (recommended) — off:** existing installs keep today's layout until an admin opts in.
- ✅ No surprise layout change (the rail takes a 64 px column) on upgrade.
- ❌ Fewer people discover it.

**Option B — on:**
- ✅ Consistent with `/timeline`, where the rail is always on.
- ❌ Every eligible album's layout changes on upgrade.

---

### ~~Q-071-04~~ · Non-date sort columns ✅ RESOLVED

**Status:** Resolved (Option A, extended by owner: date sort columns **plus** title sort in `DATE_PREFIX` bucket mode; owner, 2026-09-24) → spec FR-071-04/05/08, NG6

**Feature:** F-071 – Album Date Scrubber · **Priority:** High · **Opened:** 2026-09-24

**Context:** `PhotoBucketComputer::compute()` / `AlbumBucketComputer::compute()` produce date-shaped `bucket_id`s only for `created_at`/`taken_at` (photos) and `created_at`/`min_taken_at`/`max_taken_at` (albums). Title sorts produce `TitleBucketMode` keys, `rating_avg` gives `1`…`5`, `type` gives MIME strings, and `is_highlighted` gives `0`/`1`. `TimelineDatesV3.vue`'s `ticks` split every id on `-` and treat segment 0 as a year.

**Option A (recommended) — date sorts only:** the album is eligible only when its effective sort column is a date column. The server folds that into `is_date_scrubber_available`.
- ✅ It is a *date* scrubber; the Timeline rail is reused as-is.
- ❌ Title-sorted albums get nothing.

**Option B — generalise the rail:** date sorts keep year/month ticks; other sorts show one tick per bucket label (an A–Z style rail).
- ✅ Every bucketable album gets a scrubber.
- ❌ Needs a second tick strategy and lens labelling; highlighted/type buckets have 2–5 entries, so the rail adds little.

---

### ~~Q-071-05~~ · Always persist day granularity? ✅ RESOLVED

**Status:** Resolved (Option A — rail derives day entries client-side, bucket storage untouched, refactor deferred to its own feature; owner, 2026-09-24) → spec FR-071-08, NG4, ADR-071-01

**Feature:** F-071 – Album Date Scrubber · **Priority:** High · **Opened:** 2026-09-24

**Context:** The rail's quality depends on bucket granularity. `timeline_photos_granularity` defaults to `YEAR`, so a typical album's rail would have only year ticks, and the lens would magnify years only. The owner asked whether bucketing should always store day granularity. Findings:
- The photo `ratios` tier **already returns `taken_ats`/`created_ats` per photo**, and album tier 2 returns `min_taken_at`/`max_taken_at`/`created_at`. The rail can derive day/month ticks client-side from each tile's real pixel position, independent of the header buckets, with no storage change.
- The persisted refactor would make granularity changes free: no `RecomputeAlbumPhotoBucketsJob` or `RecomputeChildAlbumBucketsJob` on a granularity edit, and display grouping becomes `LEFT(bucket_id, n)` at read time. But it touches `photo_album.bucket_id`, `base_albums.bucket_id`, all four recompute jobs, the backfill command, `QueryPhotoBuckets`/`ratios` grouping, the Timeline's bucket-windowed fetch (`bucketDateRange`), and managed-cache invalidation keys. "Smallest" is also `HOUR` for photos, not `DAY`. Title buckets are unaffected.

**Option A (recommended) — out of scope; the rail derives fine ticks client-side:** 071 ships using per-tile dates for ticks and lens labels, and keeps the stored buckets for headers. The storage refactor becomes a separate feature, judged on its own merits (recompute cost, cache simplification).
- ✅ 071 needs no migration and no backend bucketing change; the rail gets day precision regardless of header granularity.
- ✅ The refactor's large blast radius (Timeline, cache, jobs) is not coupled to a UI feature.
- ❌ Two sources of "date grouping" coexist (stored buckets for headers, client-side dates for rail ticks).

**Option B — refactor first as a prerequisite feature (072), then 071:**
- ✅ One source of truth; the rail reads fine buckets directly.
- ❌ Blocks 071 behind a high-risk backend migration; day buckets multiply the `buckets` tier size (years → thousands of rows on large libraries) unless every consumer rolls up.

**Option C — rail uses stored buckets only, no refactor:**
- ✅ Least work.
- ❌ With the default `YEAR` granularity the rail is sparse and the lens is nearly useless.
