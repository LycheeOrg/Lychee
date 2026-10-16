# Open Questions – Feature 094

Open questions for [Feature 094](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-094-01~~ | 094 – Scrubber adaptive ticks | High | Album rails derive day entries only (ADR-071-01), so an album at hour granularity within one day cannot show hour labels (A hour entries when G = hour and photos are date-sorted · B keep day entries, hour labels on `/timeline` only) | Resolved (Option B; owner, 2026-10-16: album rails keep day entries; spec N-094-03, FR-094-02, S-094-10) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-02~~ | 094 – Scrubber adaptive ticks | High | Is the granularity a floor for the label unit? Album rails hold day data even at year granularity (A L = coarser of G and span · B albums use their own day/hour data, G ignored) | Resolved (Option B; owner, 2026-10-16; spec FR-094-02, S-094-08) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-03~~ | 094 – Scrubber adaptive ticks | Medium | "Spans multiple years" for 2025-12-20 … 2026-01-10: calendar boundary or amount of data? (A calendar boundary · B coarsest unit with at least 3 distinct values) | Resolved (Option A; owner, 2026-10-16; spec FR-094-02, S-094-07) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-04~~ | 094 – Scrubber adaptive ticks | Medium | What the dots between labels mark (A `/timeline` one unit finer than L, albums keep section-header dots · B one unit finer than L on both views · C unchanged dots) | Resolved (Option A; owner, 2026-10-16; spec FR-094-06) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-05~~ | 094 – Scrubber adaptive ticks | Medium | Hour label format when the data covers one day (A fixed `H:00` constant · B `timeline_photo_date_format_hour` · C browser locale hour) | Resolved (Option A; owner, 2026-10-16; spec FR-094-03) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-06~~ | 094 – Scrubber adaptive ticks | Low | Month label: the request shows `Oct.`; the locale short month gives `Oct` (en), `oct.` (fr) (A locale short month · B English-style abbreviation with a period) | Resolved (Option A; owner, 2026-10-16; spec FR-094-03) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-07~~ | 094 – Scrubber adaptive ticks | Medium | Hourly lens labels `14 Oct 2026 15:00` (17 characters) do not fit the 200 px lens: the focal item, about 148 px wide in monospace, reaches into the lens's faded left edge (A widen the lens when dated hour labels are shown · B shorter lens date `14/10 15:00`, full label in the pill · C keep the width, accept the fade) | Resolved (Option A; owner, 2026-10-16; spec FR-094-09, S-094-19, EX-094-L01 … L08) | 2026-10-16 | 2026-10-16 |
| ~~Q-094-08~~ | 094 – Scrubber adaptive ticks | Medium | Feature 071 shows no album rail when the grid has a single section header (`photoBucketableV3` needs more than one bucket), so EX-094-A05, A09, A10, A13 … A15 and A17 never render (A show the rail on single-header albums · B keep 071 eligibility, mark those rows "no rail") | Resolved (Option B; owner, 2026-10-16; spec FR-094-02, N-094-06, S-094-08, Example Catalogue) | 2026-10-16 | 2026-10-16 |

## Question Details

### ~~Q-094-01~~ – Hour labels on album rails

**Context:** `/timeline` rail entries are its bucket ids, so at hour granularity they carry the hour. Album rail entries are client-derived day runs (FR-071-08, ADR-071-01), whatever the header granularity. With the rule "one day → hour labels", an album at hour granularity whose photos are all on one day would show a single `DD/MM` label, while the same photos on `/timeline` show hour labels.

- **Option A (recommended):** When G = hour and the rail follows photos sorted by `taken_at` or `created_at`, derive hour runs (`Y-m-d-H`, `taken_at` in `taken_at_orig_tz` like `taken_key`, `created_at` as stored). Otherwise day runs as today. Lens and pill keep their day label.
  - ✅ Same rail on `/timeline` and albums (G2).
  - ✅ Still client-side; ADR-071-01 holds (no storage change).
  - ❌ More entries per album (one per hour run), and `PhotoGridVirtual.vue`'s derivation grows a precision parameter.
  - ❌ Lens items for hour runs repeat the same day label (several per day); acceptable, or a follow-up.
- **Option B:** Keep day entries on albums. Label unit on albums never goes below day.
  - ✅ No change to the derivation.
  - ❌ Albums and `/timeline` differ for single-day data at hour granularity.

**Resolution:** Option B. Owner: "albums do not have hourly entries/timeline". Album rails keep the day runs of FR-071-08; hour labels appear on `/timeline` only (N-094-03). Note: album photo headers can still be set to `hour` (`timelinePhotoGranularityOptions`, FR-092-05); the rail ignores it.

### ~~Q-094-02~~ – Granularity as a floor for the label unit

**Context:** The request says to use the chosen granularity as the basis and to look at the data too. On `/timeline` the two coincide: bucket ids carry nothing finer than G. Album rails hold day data whatever G is (ADR-071-01), so an album at year granularity whose photos are all in 2026 could show month labels — or a single `2026`, matching its single section header.

- **Option A (recommended):** L = the coarser of G and the span unit S. An album at year granularity shows year labels only.
  - ✅ The rail never labels finer than the section headers the grid draws; one rule for both views.
  - ✅ The owner controls rail detail through the existing granularity setting.
  - ❌ A one-year album at the default `YEAR` granularity gets a single label (as today).
- **Option B:** Albums use their entry precision E instead of G; G only matters on `/timeline`.
  - ✅ Richer rail on albums with the default `YEAR` granularity.
  - ❌ Rail labels and section headers disagree (`Oct`, `Nov` labels beside one `2026` header).
  - ❌ Two rules for one component.

**Resolution:** Option B. L is the span of the rail entries; the granularity matters only through the entry precision (FR-094-02). Rail labels may be finer than the album's section headers (S-094-08).

### ~~Q-094-03~~ – How the span is decided

**Context:** "Spans multiple years" is ambiguous for data 2025-12-20 … 2026-01-10: two calendar years but three weeks of data. The calendar rule gives labels `2026`, `2025`.

- **Option A (recommended):** Calendar boundary: S is the coarsest unit whose prefix differs between the smallest and largest key.
  - ✅ Exactly the rule as stated; trivial to explain and compute.
  - ✅ Labels are always unambiguous (month labels never mix two years, day labels never mix two months).
  - ❌ Short spans across a boundary get two coarse labels; the dots carry the detail.
- **Option B:** Coarsest unit with at least 3 distinct values, falling back to finer units (with day labels, `DD/MM` stays unambiguous across a year boundary only within ~11 months).
  - ✅ More labels for short spans across a boundary.
  - ❌ Month labels can then mix years (`Dec`, `Jan` without a year); harder to predict.

**Resolution:** Option A. Calendar boundary (FR-094-02, S-094-07).

### ~~Q-094-04~~ – What the dots mark

**Context:** Today `/timeline` draws a dot per month; albums draw a dot per section header (owner request, FR-071-12). With month, day or hour labels, a month dot no longer fits between labels.

- **Option A (recommended):** `/timeline`: one dot per unit one level finer than L, if not finer than G (L = G → no dots). Albums keep one dot per section header (FR-071-12). Dots within 4 px of the previous dot are dropped.
  - ✅ `/timeline` at hour over several years looks as today (month dots).
  - ✅ Keeps the owner's FR-071-12 decision for albums.
  - ❌ Two dot rules in one component (already the case today).
- **Option B:** One dot per unit one level finer than L on both views (supersedes FR-071-12).
  - ✅ One rule.
  - ❌ Album dots stop matching the grid's section headers.
- **Option C:** Dots unchanged (month dots on `/timeline`, header dots on albums).
  - ✅ Least change.
  - ❌ Month dots under month labels coincide with the labels and are all dropped; day/hour labels get month dots that mean nothing.

**Resolution:** Option A (FR-094-06).

### ~~Q-094-05~~ – Hour label format

**Context:** The request covers year, month and day labels; one-day data needs hour labels. `timeline_photo_date_format_hour` defaults to `g:i`, which shows neither am/pm (backlog of Feature 092).

- **Option A (recommended):** Fixed constant `H:00` next to the `d/m` constant; both move to configuration in the planned follow-up.
  - ✅ Unambiguous, short enough for the w-16 rail.
  - ✅ Same treatment as `DD/MM`.
  - ❌ 24-hour clock regardless of locale until the follow-up.
- **Option B:** Reuse `timeline_photo_date_format_hour`.
  - ✅ Already configurable.
  - ❌ The default `g:i` is ambiguous, and that format is also used for header labels where more room exists.
- **Option C:** Browser locale hour (`Intl.DateTimeFormat(locale, { hour: "numeric" })`).
  - ✅ Locale-aware (`3 PM`, `15 h`).
  - ❌ Inconsistent with the fixed `DD/MM`; width varies.

**Resolution:** Option A. `H:00` constant (FR-094-03, N-094-01).

### ~~Q-094-06~~ – Month label spelling

**Context:** The request writes `Oct.`, `Nov.`. `phpDateFormat("M")` uses `Intl` short month names in the browser locale when the gallery language is English (`Oct` en-US, `Sept` en-GB, `Okt.` de-DE), and English names for every other gallery language. (Corrected 2026-10-16: the first version said the gallery locale.)

- **Option A (recommended):** Locale short month name as given by `Intl` (via `phpDateFormat("M")`).
  - ✅ Localised; no lang keys.
  - ❌ English shows `Oct` without a period.
- **Option B:** Append a period when the locale's short name has none and differs from the full name (`May` stays `May`).
  - ✅ Matches the request literally in English.
  - ❌ Extra rule; not idiomatic in every locale.

**Resolution:** Option A. `phpDateFormat("M")` (FR-094-03).

### ~~Q-094-07~~ – Lens width for hourly labels

**Context:** FR-094-08 puts `14 Oct 2026 15:00` (17 characters, with `date_scrubber_label_format = d M Y`) in the lens and the pill. The lens is `w-50` (200 px), its text is monospace and right-aligned 12 px from the edge, and the focal item is 14.5 px: about 8.7 px per character, so about 148 px. The lens fades its left 26 % (52 px) to transparent, so the first one or two characters of the focal item sit in the fade. Day labels today (`14 Oct 2026`, about 96 px) clear it. The pill has no width limit.

- **Option A (recommended):** Widen the lens to `w-60` (240 px) when the labels are dated hour labels (bucket ids of precision 4 on several days, decided inside `TimelineDatesV3.vue` from `buckets.bucket_ids`, no prop); the pill offset next to the open lens moves from `right-53`/`left-53` to `right-63`/`left-63`.
  - ✅ The requested label is shown whole, in the lens and the pill alike.
  - ✅ No new prop; other views and granularities are unchanged.
  - ❌ The lens covers 40 px more of the grid while hovering in that mode; FR-094-07 ("lens positions as before") gets an exception.
- **Option B:** Lens items use a shorter date, `d/m H:00` (`14/10 15:00`), matching the rail's day labels; the pill keeps `14 Oct 2026 15:00`.
  - ✅ No layout change.
  - ❌ The lens drops the year; lens and pill labels differ, so `TimelineDatesV3.vue` needs a separate lens label (new prop or field).
- **Option C:** Keep `w-50` and accept the partial fade of the focal item's first characters.
  - ✅ No change.
  - ❌ The first characters of the date are hard to read at the focal point; longer day formats make it worse.

**Resolution:** Option A. 240 px lens and pill offset `63` for dated hour labels, decided inside `TimelineDatesV3.vue` (FR-094-09); FR-094-07 notes the exception.

### ~~Q-094-08~~ – Album rail on single-header albums

**Context:** Found in the browser check (T-094-03). The rail's eligibility (FR-071-06, `albumDateScrubberState.ts`) requires `photoTimelineBucketed = is_photo_timeline_enabled && photoBucketableV3`, and `photoBucketableV3` is false when the grid has a single bucket: one header carries no information, so the grid drops it and renders flat (`AlbumState.ts`). An album at year granularity whose photos are all in 2026 (EX-094-A05, the case behind Q-094-02 → B) therefore shows no rail and no toggle. The same holds for A09, A10, A13, A14, A15 and A17. The Example Catalogue lists labels for them.

- **Option A (recommended):** The album rail no longer needs more than one section header: it needs the photo timeline enabled, a bucketable sort (`bucketable: true`) and at least two dated day runs. A single-header album then gets its month or day labels (A05 → `Oct` … `Jun`, A13 … A15 → no rail, as one day is one run). The grid keeps rendering flat, so the rail has no header dots.
  - ✅ Delivers Q-094-02 → B where it matters most: the default `YEAR` granularity with photos from one year.
  - ✅ The rail stays off when it would show a single label (one day run).
  - ❌ Changes Feature 071 eligibility (FR-071-06); its spec and S-071 scenarios need an amendment.
- **Option B:** Keep Feature 071 eligibility. Rows A05, A09, A10, A13 … A15 and A17 become "no rail".
  - ✅ No change outside this feature.
  - ❌ Albums at year granularity with one year of photos (the default setup) never get the adaptive labels.

**Resolution:** Option B. Feature 071 eligibility unchanged; single-header rows of the Example Catalogue show no rail. Consequence for Q-094-02: an album rail always has at least two headers, so the data spans at least one header unit and album labels are never finer than the headers; Q-094-02 → B no longer changes what an album shows (FR-094-02).
