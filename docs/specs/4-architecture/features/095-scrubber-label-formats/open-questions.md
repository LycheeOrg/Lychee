# Open Questions – Feature 095

Open questions for [Feature 095](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-095-01~~ | 095 – Scrubber label formats | High | Which labels become configurable (A the four rail resting formats, the hourly lens reuses the hour one · B also separate lens/pill formats · C reuse the existing timeline formats for the rail, no new keys) | Resolved (Option A; owner, 2026-10-16; spec FR-095-01, FR-095-04) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-02~~ | 095 – Scrubber label formats | Medium | Supporter Edition or free (A SE, level 1, like the timeline formats and lens settings · B free, level 0, like the rail toggle) | Resolved (Option A, day format free; owner, 2026-10-16; spec FR-095-01, G4) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-03~~ | 095 – Scrubber label formats | Medium | A format longer than the 64 px rail (A clip the label to the rail · B no limit, labels overflow over the grid · C reject formats over a length) | Resolved (Option A; owner, 2026-10-16; spec FR-095-05) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-04~~ | 095 – Scrubber label formats | Medium | Sub-album rails format their lens with the photo day format (A use `timeline_album_date_format_day` · B leave it) | Resolved (Option A; owner, 2026-10-16; spec FR-095-02, FR-095-06) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-05~~ | 095 – Scrubber label formats | Medium | Fix the `timeline_photo_date_format_hour` default `g:i` here (A yes, `H:00` for new installs and untouched values · B separate follow-up) | Resolved (Option A; owner, 2026-10-16; spec FR-095-07) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-06~~ | 095 – Scrubber label formats | Medium | Where the four settings appear in the Timeline category (A right after the quick-access date formats, order 23–26 · B with the lens settings at the end of the category · C right after the photo hour format, quick-access formats renumbered) | Resolved (Option A; owner, 2026-10-16; spec FR-095-01, S-095-10) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-07~~ | 095 – Scrubber label formats | Medium | `timeline_album_date_format_day` defaults to `j M` (no year), so Q-095-04 turns the sub-album pill `14 Oct 2026` into `14 Oct` (A accept: it matches the headers · B change that default to `j M Y` for untouched instances · C keep the photo day format for the pill) | Resolved (Option B; owner, 2026-10-16; spec FR-095-07, S-095-07, S-095-08) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-08~~ | 095 – Scrubber label formats | Medium | Hour section headers carry no date (`H:00`), so on a multi-day album they are ambiguous, and `computeBucketBoundaries()` merges adjacent buckets with equal labels: four days with one photo at 10:00 become one `10:00` header and the album loses its rail (A both: hour default `j M Y H:00` and merge only `unknown` labels · B only the new default · C only the merge fix) | Resolved (Option A, amended; owner, 2026-10-16; spec FR-095-08, FR-095-09) | 2026-10-16 | 2026-10-16 |
| ~~Q-095-09~~ | 095 – Scrubber label formats | Medium | Where the same-day / multi-day hour header choice is made (A server, in `BucketScheme::labels()` as all section labels today · B browser, relabelling hour headers from the bucket ids) | Resolved (Option A; owner, 2026-10-16; spec FR-095-08) | 2026-10-16 | 2026-10-16 |
## Question Details

### ~~Q-095-01~~ – Which labels become configurable

**Context:** See the label inventory in [spec.md](spec.md). The rail resting labels (L-095-01 … 04) are constants. The lens and pill use the server labels or `date_scrubber_label_format` (the photo day format), plus the `H:00` constant at hour granularity (L-095-05 … 08).

- **Option A (recommended):** Four new keys for the rail resting labels (year, month, day, hour). The hourly lens and pill replace their `H:00` constant with the configured hour format; their day part keeps `date_scrubber_label_format`.
  - ✅ Covers every hard-coded format; the lens and the rail never disagree on the hour.
  - ✅ Four settings, one place.
  - ❌ The hour format must suit both the 64 px rail and the lens (`H:00`, `G\h`, `ga` all do).
- **Option B:** A plus separate lens/pill formats (year, month, day, hour) instead of the server labels and `date_scrubber_label_format`.
  - ✅ The lens can be long while the headers stay short, or the reverse.
  - ❌ Eight new settings for overlapping concepts; the lens and the section headers can disagree.
- **Option C:** No new keys; the rail uses `timeline_photo_date_format_*` (albums: `timeline_album_date_format_*`).
  - ✅ No new settings.
  - ❌ The defaults (`F Y`, `j M Y`) do not fit the 64 px rail; the rail and the headers can no longer differ.

**Resolution:** Option A. Four rail formats; the hourly lens and pill use the hour one (FR-095-04).

### ~~Q-095-02~~ – Supporter Edition or free

**Context:** The timeline date formats and the lens settings are `level 1` and in `ConfigIntegrity::SE_FIELDS`; `album_date_scrubber_enabled` is `level 0`.

- **Option A (recommended):** SE: `level 1`, added to `SE_FIELDS`. Non-SE instances keep the Feature 094 defaults.
  - ✅ Consistent with every other timeline format and the lens settings.
  - ❌ Free installs cannot change `DD/MM` to their locale's order.
- **Option B:** Free: `level 0`.
  - ✅ Every install can fix `DD/MM` for its locale.
  - ❌ Inconsistent with the other timeline formats.

**Resolution:** Option A, amended by the owner: year, month and hour formats are SE (`level 1`, `SE_FIELDS`); the day format (`DD/MM`) is free (`level 0`) so every instance can change the day order (FR-095-01, G4).

### ~~Q-095-03~~ – Formats longer than the rail

**Context:** About 7 characters fit the 64 px rail. Labels do not wrap; a longer one overflows to the left over the grid (`l j F Y` → `Wednesday 14 October 2026`).

- **Option A (recommended):** Clip each label to the rail's inner width (`max-w` + `overflow-hidden`, no ellipsis), and say so in the setting's details.
  - ✅ The rail never covers the grid; the admin sees the cut and shortens the format.
  - ❌ A long format is silently cut.
- **Option B:** No limit; labels overflow to the left.
  - ✅ Nothing hidden.
  - ❌ Labels cover photos and the lens edge.
- **Option C:** Reject formats whose sample output exceeds N characters.
  - ✅ Never cut.
  - ❌ Character count is a poor proxy for width (`W` vs `i`); validation must render the format server-side for a sample date.

**Resolution:** Option A. Labels clipped to the rail width; the setting details say so (FR-095-05).

### ~~Q-095-04~~ – Sub-album rail lens format

**Context:** On sub-album rails (Feature 071) the lens and pill format days with `date_scrubber_label_format`, which `InitConfig` takes from `timeline_photo_date_format_day`, while the sub-album section headers use `timeline_album_date_format_day`. With different values, the pill and the headers of the same view disagree.

- **Option A (recommended):** `InitConfig` also serves `timeline_album_date_format_day`; sub-album rails use it.
  - ✅ The pill matches the sub-album headers.
  - ❌ One more `InitConfig` field.
- **Option B:** Leave it.
  - ✅ No change.
  - ❌ Pill and headers can disagree on sub-album pages.

**Resolution:** Option A. `InitConfig` serves `date_scrubber_album_label_format` from `timeline_album_date_format_day` (FR-095-02, FR-095-06).

### ~~Q-095-05~~ – Default of `timeline_photo_date_format_hour`

**Context:** The hour header default `g:i` shows `6:00` for 18:40 (no am/pm, no date); it is in the Feature 092 backlog. It is visible on the same pages as the rail (section headers at hour granularity).

- **Option A (recommended):** Change the default to `H:00`; a migration updates rows still holding `g:i`, leaves customised values alone.
  - ✅ Headers and rail agree out of the box; closes the backlog item.
  - ❌ Changes visible headers for instances that kept the default.
- **Option B:** Separate follow-up.
  - ✅ Keeps this feature small.
  - ❌ The ambiguous header default stays next to the new rail labels.

**Resolution:** Option A. Migration `g:i` → `H:00`, custom values kept (FR-095-07).

### ~~Q-095-06~~ – Position of the settings

**Context:** Settings within a category are ordered by `configs.order`, then by key (`ConfigCategory::configs()`). In `Mod Timeline`: photo date formats year/month/day/hour have order 15–18, quick-access date formats 19–22 (expert), and the lens settings `timeline_lens_*` have no order (32767, last, sorted by key). New keys without an order would land among the lens settings by key; with order 18 they would sort before `timeline_photo_date_format_hour` (key order).

- **Option A (recommended):** Orders 23–26 (year, month, day, hour), right after the quick-access date formats: every date format of the category stays together.
  - ✅ No existing row moves.
  - ✅ Date formats grouped.
  - ❌ Separated from the lens settings, which configure the same rail.
- **Option B:** No order (32767): with the lens settings at the end, `date_scrubber_format_*` sorting before `timeline_lens_*`.
  - ✅ Everything about the scrubber rail together.
  - ❌ Relies on key sort; date formats split across the category.
- **Option C:** Orders 19–22, right after the photo hour format; quick-access formats renumbered 23–26.
  - ✅ Next to the formats they resemble most, as in the mock-up.
  - ❌ Rewrites the order of four existing rows.

**Resolution:** Option A. Orders 23–26 after the quick-access date formats (FR-095-01).

### ~~Q-095-07~~ – Sub-album pill without the year

**Context:** Q-095-04 → A switches the sub-album lens and pill to `timeline_album_date_format_day`, whose default is `j M` (database/migrations/2024_10_30_064336_timeline_options.php), while the photo day format is `j M Y`. On an instance with defaults, the sub-album pill goes from `14 Oct 2026` to `14 Oct`, and lens items lose the year too. The sub-album day headers already show `14 Oct`.

- **Option A (recommended):** Accept: the pill and lens match the sub-album headers; the rail's own labels give the year (or the month/day context) of the span.
  - ✅ What Q-095-04 asked for: one format per view.
  - ❌ On a sub-album rail spanning several years, the pill alone does not tell the year.
- **Option B:** Also change the `timeline_album_date_format_day` default to `j M Y` (migration on untouched `j M` rows, like FR-095-07).
  - ✅ The pill keeps the year; headers gain it too.
  - ❌ Changes every untouched instance's sub-album day headers.
- **Option C:** Keep the photo day format for the sub-album pill (revisit Q-095-04).
  - ✅ No visible change.
  - ❌ Pill and headers can disagree, the issue Q-095-04 fixed.

**Resolution:** Option B. The FR-095-07 migration also turns untouched `j M` into `j M Y`; rollback restores `j M`.

### ~~Q-095-08~~ – Hour headers without a date

**Context:** Found in the Q-094-09 browser check (2026-10-16). An album at hour granularity whose photos span several days shows headers `22:00`, `17:00`, … with no day (`timeline_photo_date_format_hour`, default `H:00` since FR-095-07; `g:i` before). Worse, `computeBucketBoundaries()` (`albumBucketBoundaries.ts`) merges adjacent buckets that share a label — a rule meant for the root "shared" scope, where a guest sees every owner as `unknown`. Album A11 (one photo at 10:00 on each of four days) gets four distinct buckets from the server but one merged `10:00` boundary: `photoBucketableV3` is false, no headers, no rail. The same merge hits any date format coarser than the bucket (an admin day format `M Y` merges days).

- **Option A (recommended):** Both: the FR-095-07 migration writes `j M Y H:00` instead of `H:00` for untouched `g:i` rows (the migration is not released yet, so no extra migration), and `computeBucketBoundaries()` merges adjacent buckets only when their label is `unknown`.
  - ✅ Hour headers say which day; distinct buckets never collapse.
  - ✅ The shared-scope `unknown` case keeps its single header.
  - ❌ Longer hour headers; an admin who relied on coarse formats merging headers loses that.
- **Option B:** Only the new hour default.
  - ✅ Fixes the default case.
  - ❌ A custom format coarser than the bucket still merges buckets and can hide the rail.
- **Option C:** Only the merge fix.
  - ✅ Headers and rail always match the server's buckets.
  - ❌ Repeated `10:00` headers on consecutive days stay ambiguous.

**Resolution:** Option A, amended by the owner: two configurable hour formats chosen by the listing's span — **same day** (`timeline_photo_date_format_hour`, `H:00`) when every photo is on one day, **multi day** (new `timeline_photo_date_format_hour_multi_day`, `j M Y H:00`) otherwise (FR-095-08); boundaries merge only `unknown` labels (FR-095-09). Interpreted as a per-listing decision (all hour headers in one mode), recorded in the spec.

### ~~Q-095-09~~ – Server or browser for the hour header mode

**Context:** Section labels are built on the server (`BucketScheme::labels()`, FR-092-08) for every v3 bucket tier (`/timeline`, album / tag / person / smart photo listings, album children and root) and cached with the listing; the v8 grid displays them as received. The rail formats of Feature 095 are applied in the browser. FR-095-08 needs the whole bucket list to pick the mode; both sides have it (the `buckets` tier is fetched whole).

- **Option A (recommended):** Server: `labels()` picks the hour format from the list it formats.
  - ✅ One place builds every section label, as today; any v3 client gets correct labels.
  - ✅ Smallest change; no new `InitConfig` field.
  - ❌ A change of the new format needs the listing flush (`PHOTO_LISTING_COARSE_FLUSH_CONFIGS`), like the other label formats.
- **Option B:** Browser: `InitConfig` serves both hour formats; the album and Timeline stores relabel hour ids (13 characters) after the `buckets` tier arrives.
  - ✅ Same mechanism as the rail formats; no cache flush when the format changes.
  - ❌ Two places build section labels (server for every other id, browser for hours); the API's `labels` would no longer be what v8 shows.
  - ❌ Every consumer of the bucket tier must apply the relabel (album photos, Timeline, tag/person/smart albums).

**Resolution:** Option A. `BucketScheme::labels()` picks the hour format (FR-095-08).
