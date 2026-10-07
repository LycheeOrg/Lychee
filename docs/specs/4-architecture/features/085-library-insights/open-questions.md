# Open Questions – Feature 085

Open questions for [Feature 085](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-085-01~~ | 085 – Library insights | High | How does Insights relate to the Statistics page? (A replace it, storage panels become a section · B separate page next to it · C extend Statistics in place) | Resolved (Option A — Insights replaces Statistics; storage endpoints kept for the Storage section and album drawer; owner, 2026-10-05; spec FR-085-01 … FR-085-03, FR-085-07; ADR-085-01) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-02~~ | 085 – Library insights | High | Which UI versions get Insights? (A v8 only · B v7 and v8) | Resolved (Option A — v8 only; v7 keeps its Statistics page unchanged; owner, 2026-10-05 and 2026-10-07; spec Non-Goals, FR-085-01, FR-085-02; ADR-085-01) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-03~~ | 085 – Library insights | High | Whose photos does a view cover? (A own photos, admin can pick a user or the whole instance · B every photo the user can access · C own photos only, admin included) | Resolved (Option A — own photos, admin picks a user or whole instance; owner, 2026-10-05; spec FR-085-04, NFR-085-02; ADR-085-02) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-04~~ | 085 – Library insights | High | Edition gating? (A whole page SE, preview in core · B core basics, advanced sections SE · C everything core) | Resolved (Option A — whole page SE, preview in core; owner, 2026-10-05; spec FR-085-01) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-05~~ | 085 – Library insights | High | Which sections ship in this feature? (A phased: phase 1 core sections, later phases tracked as follow-ups · B the full catalogue at once · C phase 1 only, rest to backlog) | Resolved (Option A — phased, phase 1 in this feature; owner, 2026-10-05; spec Goals, Non-Goals, FR-085-05 … FR-085-13) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-06~~ | 085 – Library insights | High | How are charts drawn? (A hand-built SVG Vue components · B add a chart library) | Resolved (Option B — a chart library, lazy-loaded; which library is Q-085-13; owner, 2026-10-05; spec NFR-085-07) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-07~~ | 085 – Library insights | High | How are the aggregates computed? (A one streamed PHP pass over a narrow projection, cached per scope and library revision · B SQL GROUP BY per widget · C precomputed summary tables kept current by jobs) | Resolved (Option A — one streamed PHP pass, cached per scope, period and revision; owner, 2026-10-05; spec NFR-085-06, API-085-01; ADR-085-03) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-08~~ | 085 – Library insights | Medium | Which clock buckets hours, weekdays and days? (A local capture time from `taken_at` + `taken_at_orig_tz`, undated counted apart · B stored UTC `taken_at` · C local time, falling back to `created_at` when undated) | Resolved (Option A — local capture time, undated counted apart; owner, 2026-10-05; spec FR-085-15) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-09~~ | 085 – Library insights | Medium | Can chart elements open the matching photos? (A yes, paginated grid from a new filtered photo endpoint · B not in this feature) | Resolved (Option B — no drill-down in this feature; owner, 2026-10-05; spec Non-Goals) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-10~~ | 085 – Library insights | Medium | How are devices grouped? (A built-in maker normalisation and camera/phone classification · B plus per-user override rules · C raw `make`/`model` only) | Resolved (Option A — built-in normalisation and classification, no overrides; owner, 2026-10-05; spec FR-085-12, Non-Goals) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-11~~ | 085 – Library insights | Medium | What does the Places section show? (A located count and share plus a link to the existing map · B parse a country from `location` · C no Places section) | Resolved (Option A — located count and share plus map link; owner, 2026-10-05; spec FR-085-14) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-12~~ | 085 – Library insights | Medium | Do insights apply to an album subtree? (A not in this feature · B album scope selector on the page · C replace the album statistics drawer) | Resolved (Option A — no album scope in this feature; owner, 2026-10-05; spec Non-Goals) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-13~~ | 085 – Library insights | High | Which chart library? (A ECharts + vue-echarts · B Chart.js + vue-chartjs + matrix and treemap plugins · C unovis) | Resolved (Option A — ECharts + vue-echarts, SVG renderer, lazy chunk; owner, 2026-10-05; spec NFR-085-07; ADR-085-04) | 2026-10-05 | 2026-10-05 |
| ~~Q-085-14~~ | 085 – Library insights | High | Who may scope Insights to an album, and which photos count? (A owned album + descendants, all their photos; admins any album · B any accessible album, only the sub-albums the viewer can access · C owned album only, no descendants) | Resolved (Option A — owned album and descendants, every photo in them, administrators any album; owner, 2026-10-07; spec FR-085-16, S-085-20 … S-085-23; ADR-085-02) | 2026-10-07 | 2026-10-07 |

## Question Details

### ~~Q-085-01~~ – Relationship to the Statistics page

**Context:** The Statistics page (`/statistics`, SE) shows a size-variant meter, a punch-card calendar of `taken_at`/`created_at`, totals, and a per-album space table (`StatisticsController`, `Actions/Statistics/Spaces.php`, `Counts.php`). The reference dashboard from another famous photo app covers the calendar and totals and adds devices, exposure, rhythm, timeline, evolution and profile sections, but no storage-per-variant or per-album space.

- **Option A (chosen):** Insights replaces the Statistics page. The size-variant meter and album space table move into a "Storage" section; the punch card becomes the Insights calendar heatmap. `/statistics` and its five endpoints are removed.
  - ✅ One page for "numbers about my library"; no duplicated calendar.
  - ❌ The space table's admin use (who uses disk) sits next to personal insights.
- **Option B:** Separate Insights page; Statistics stays as the storage/admin page.
  - ✅ Clean split: storage vs. photography habits.
  - ❌ Two pages with overlapping totals and calendar.
- **Option C:** Extend the Statistics page in place.
  - ✅ No new route or menu entry.
  - ❌ The page name undersells the content; same result as A with a weaker name.

**Resolution:** Option A. `Statistics::sizeVariantSpace`, `::albumSpace` and `::totalAlbumSpace` stay because the album statistics drawer uses them; `::getCountsOverTime` and `::userSpace` are removed. Recorded in [spec.md](spec.md) FR-085-01 … FR-085-03, FR-085-07 and [ADR-085-01](../../../6-decisions/ADR-085-01-insights-replaces-statistics-page.md).

### ~~Q-085-02~~ – UI versions

**Context:** v8 replaces v7; new UI defaults to v8 only, but the Statistics page exists in both (`v7/views/Statistics.vue`, `v8/views/Statistics.vue`).

- **Option A (chosen):** v8 only. If Q-085-01 is A, v7 keeps a link to the v8 page or loses the menu entry.
  - ✅ Half the frontend work; Nuxt UI only.
  - ❌ v7 users lose the Statistics page when it is replaced.
- **Option B:** v7 and v8.
  - ✅ Parity.
  - ❌ Every chart component written twice (PrimeVue and Nuxt UI).

**Resolution:** Option A; v7 keeps its Statistics page, punch card and menu entry unchanged and has no access to Insights. Recorded in [spec.md](spec.md) Non-Goals, FR-085-01 and [ADR-085-01](../../../6-decisions/ADR-085-01-insights-replaces-statistics-page.md).

### ~~Q-085-03~~ – Data scope

**Context:** Statistics requests limit queries to `owner_id = Auth::id()` unless the user may administrate. The reference tool analyses only the owner's assets and excludes partner/shared ones.

- **Option A (chosen):** Own photos. An admin gets a selector: "My library", any user, or "Whole instance".
  - ✅ Matches today's rule and the reference; insights describe one photographer.
  - ✅ Admin keeps the instance-wide view Statistics gives today.
- **Option B:** Every photo the user can access (owned + shared + public).
  - ✅ Useful for viewers of a shared family gallery.
  - ❌ Mixes other photographers' habits; access-rights query on every aggregate.
- **Option C:** Own photos only, admins included.
  - ✅ Simplest.
  - ❌ Admin loses the instance-wide numbers.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-085-04, NFR-085-02 and [ADR-085-02](../../../6-decisions/ADR-085-02-owner-scoped-insights.md).

### ~~Q-085-04~~ – Edition gating

**Context:** Statistics is SE-only with a preview in core (`is_se_enabled`, `enable_se_preview`).

- **Option A (chosen):** Whole Insights page SE, with the existing preview mechanism in core.
  - ✅ Same stance as Statistics today.
  - ❌ Core users see only the preview.
- **Option B:** Overview and calendar in core; devices, exposure, rhythm, timeline, evolution, profile in SE.
  - ✅ Gives core users something real.
  - ❌ Per-section gating in API and UI.
- **Option C:** Everything core.
  - ✅ Simplest gating.
  - ❌ Removes an SE feature.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-085-01.

### ~~Q-085-05~~ – Sections in this feature

**Context:** Reference catalogue: overview cards, storage, "for perspective" fun facts, technique cards, image-dimension frame chart, orientation, people, places, time span/streaks, milestone timeline, devices (donut/treemap), exposure histograms, focal-length cones, exposure triangle, week × hour heatmap, month/weekday/hour bars, EXIF by time, calendar heatmap with anniversary, tendencies radar, evolution by year (cumulative, MB per file, resolution, devices streamgraph), photo profile indices, CSV/PDF/JPG/SQLite export. Library / Year / Date-range scopes apply to all.

- **Option A (chosen):** Phased. Phase 1 (this feature): Library/Year/Range scopes, overview cards, storage (migrated), people, time span and streaks, calendar heatmap, week × hour heatmap, month/weekday/hour bars, devices, exposure histograms (ISO, focal, shutter, aperture, video length). Phase 2 (follow-up feature): milestone timeline, evolution by year, tendencies. Phase 3: photo profile indices, exposure triangle, dimension frame chart, export. Fun facts and focal cones dropped.
  - ✅ Each phase shippable and reviewable.
  - ❌ The full experience arrives over several features.
- **Option B:** Full catalogue in one feature.
  - ✅ One design pass.
  - ❌ Very large; long-lived branch.
- **Option C:** Phase 1 only; the rest goes to the roadmap backlog without commitment.
  - ✅ Smallest.
  - ❌ Leaves the most distinctive parts (timeline, tendencies, profile) unplanned.

**Resolution:** Option A, recorded in [spec.md](spec.md) Goals, Non-Goals and FR-085-05 … FR-085-13.

### ~~Q-085-06~~ – Chart rendering

**Context:** `package.json` has no chart library; the punch card is a hand-made div grid. The reference also draws every chart as hand-built SVG. Lychee must run offline (bundled dependencies satisfy that). New dependencies need owner approval.

- **Option A:** Hand-built SVG Vue components (bar, histogram, heatmap grid, donut, line/area) under `resources/js/v8/components/insights/`.
  - ✅ No dependency; full control of theming and dark mode.
  - ❌ Tooltips, axes and responsiveness written by us.
- **Option B (chosen):** Add a chart library (for example ECharts or unovis), loaded as a lazy chunk.
  - ✅ Treemaps, streamgraphs, radar out of the box.
  - ❌ New dependency, several hundred KB; theming to match Nuxt UI.

**Resolution:** Option B, recorded in [spec.md](spec.md) NFR-085-07. The library is chosen in Q-085-13.

### ~~Q-085-07~~ – Computation strategy

**Context:** Lychee reads its own database (the reference copies assets through the REST API and recomputes a JSON summary per scope on manual sync). Three drivers are supported (MySQL/MariaDB, PostgreSQL, SQLite). Local hour and weekday need `taken_at_orig_tz` per row, which portable SQL cannot apply. Streaks, medians and modes need ordered series.

- **Option A (chosen):** One streamed pass in PHP over a narrow projection (`taken_at`, `taken_at_orig_tz`, `type`, `make`, `model`, `lens`, `iso`, `aperture`, `shutter`, `focal`, `filesize`, `duration`, `face_count`, location flag, `is_highlighted`), using `lazyById`/cursor, building all phase-1 aggregates. The result is cached per (owner scope, period) and keyed on a library revision (photo count + max `updated_at`).
  - ✅ Driver-independent; local time handled correctly; one query.
  - ❌ First load on a very large library takes seconds; needs the cache.
- **Option B:** SQL `GROUP BY` per widget with driver-specific date expressions.
  - ✅ Each widget fast and independent.
  - ❌ UTC only (or per-driver timezone tables); many queries; three dialects.
- **Option C:** Precomputed summary tables updated by jobs on photo mutation (like `album_size_statistics`).
  - ✅ Instant reads.
  - ❌ New tables, listeners and integrity checks; streaks and modes are hard to maintain incrementally.

**Resolution:** Option A, recorded in [spec.md](spec.md) NFR-085-06, API-085-01 and [ADR-085-03](../../../6-decisions/ADR-085-03-insights-single-pass-aggregation.md).

### ~~Q-085-08~~ – Time basis

**Context:** `taken_at` is stored in UTC with the original timezone in `taken_at_orig_tz`; it is null for photos without capture date. The reference buckets by local capture time and counts undated assets separately.

- **Option A (chosen):** Local capture time (`taken_at` converted to `taken_at_orig_tz`); undated photos excluded from time charts and shown as an "undated" count.
  - ✅ "8 pm" means 8 pm where the photo was taken.
- **Option B:** Stored UTC `taken_at`.
  - ❌ Hour-of-day heatmap shifted for every non-UTC photo.
- **Option C:** Local time, falling back to `created_at` (upload time) when undated.
  - ✅ No undated bucket.
  - ❌ Upload dates distort streaks and the calendar.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-085-15.

### ~~Q-085-09~~ – Drill-down to photos

**Context:** In the reference, clicking a bar, cell or device opens a paginated thumbnail grid filtered by that element.

- **Option A:** Yes. A new endpoint returns photo IDs/thumbs for an insight filter (date, week, hour, weekday, device, value range) within the current scope, rendered with the existing photo grid.
  - ✅ Turns charts into navigation.
  - ❌ A filter grammar plus access checks to specify and test.
- **Option B (chosen):** Not in this feature; charts are read-only.
  - ✅ Smaller phase 1.
  - ❌ Less useful charts.

**Resolution:** Option B, recorded in [spec.md](spec.md) Non-Goals.

### ~~Q-085-10~~ – Device grouping

**Context:** EXIF `make` varies ("NIKON CORPORATION", "Nikon"). The reference normalises maker names, classifies camera vs. mobile device by brand/model keywords, and lets users override per make/model.

- **Option A (chosen):** Built-in normalisation map and camera/phone classification in PHP; no user overrides.
  - ✅ Covers the common cases with no storage.
  - ❌ Unknown brands land in "Other".
- **Option B:** Option A plus per-user override rules (new table and UI).
  - ✅ Users fix odd devices themselves.
  - ❌ New table, settings UI, cache invalidation.
- **Option C:** Raw `make`/`model` strings only.
  - ❌ Duplicated makers split the charts.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-085-12 and Non-Goals.

### ~~Q-085-11~~ – Places

**Context:** Lychee stores `latitude`/`longitude` and a free-text `location`, with no country column. The reference counts countries and continents from EXIF text. Lychee already has a map view (Leaflet with the configured tile provider).

- **Option A (chosen):** "With location" count and percentage, plus a link to the existing map; no country breakdown.
  - ✅ No new data.
- **Option B:** Parse a country from `location` (last comma-separated part).
  - ❌ Depends on geocoder output language and format; unreliable.
- **Option C:** No Places section.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-085-14.

### ~~Q-085-12~~ – Album scope

**Context:** The album statistics drawer computes ISO/lens/model/… tables client-side from loaded photos (and renders aperture twice, focal never).

- **Option A (chosen):** Not in this feature; the drawer stays. The aperture/focal bug is fixed separately.
  - ✅ Keeps phase 1 focused on libraries.
- **Option B:** Album selector on the Insights page (album + descendants via `_lft`/`_rgt`).
  - ✅ Per-trip or per-event insights.
  - ❌ Another scope dimension in the cache key and access checks.
- **Option C:** Replace the album drawer with an embedded Insights view.
  - ❌ Couples the album page to the Insights API.

**Resolution:** Option A, recorded in [spec.md](spec.md) Non-Goals.

### ~~Q-085-13~~ – Chart library

**Context:** Q-085-06 chose a chart library. Phase 1 needs bars, histograms, a 7 × 24 heatmap, a calendar heatmap and a donut or bar ranking. Phases 2 and 3 add a treemap, a streamgraph, a radar, stacked areas and a scatter. The library is loaded only by the Insights page (lazy chunk), so its size does not reach other pages. Sizes below are tree-shaken bundles of the chart types listed, minified and gzipped (esbuild, measured 2026-10-05).

- **Option A (chosen):** `echarts` 6.1.0 (Apache-2.0) with `vue-echarts` 8.3.1 (MIT), SVG renderer. Bar, line, pie, heatmap, treemap, themeRiver, radar, scatter + calendar, visualMap, grid, tooltip, legend: 729 KB min / 250 KB gz.
  - ✅ Every phase-1, 2 and 3 chart built in, including the calendar heatmap and the streamgraph (themeRiver).
  - ✅ SVG or canvas renderer; themes for light and dark.
  - ❌ Largest bundle.
  - ❌ Option-object API; theming to match Nuxt UI tokens done in a shared theme.
- **Option B:** `chart.js` 4.5.1 (MIT) with `vue-chartjs` 5.3.4, `chartjs-chart-matrix` 3.1.0 (heatmap, calendar) and `chartjs-chart-treemap` 4.2.2. Bar, line, doughnut, radar, scatter, matrix, treemap: 213 KB min / 74 KB gz.
  - ✅ Smallest; widely used.
  - ❌ Four packages; calendar heatmap assembled from the matrix plugin.
  - ❌ Canvas only; no streamgraph (stacked area instead).
- **Option C:** `@unovis/ts` + `@unovis/vue` 1.7.1 (Apache-2.0). Stacked bar, line, area, donut, scatter, treemap, heatmap: 253 KB min / 83 KB gz.
  - ✅ SVG, Vue components, CSS-variable theming.
  - ❌ No radar and no calendar layout (built by hand); no streamgraph.

**Resolution:** Option A, recorded in [spec.md](spec.md) NFR-085-07 and [ADR-085-04](../../../6-decisions/ADR-085-04-echarts-chart-library.md).

### ~~Q-085-14~~ – Album scope

**Context:** The owner asked to dive into one album on the Insights page (Q-085-12 kept album scope out). Albums are a nested set (`_lft`/`_rgt`); an album can hold photos of several owners, and each sub-album has its own access permissions. Owner scope (ADR-085-02) needs no access query; an album scope does unless it is limited to owned albums.

- **Option A (chosen):** Album selector on the Insights page listing the viewer's own albums (administrators: every album). The scope is the album and its descendants, counting every photo in them whoever uploaded it.
  - ✅ One rights check (album ownership), same principle as ADR-085-02.
  - ✅ Trips and events usually live in one owned album tree.
  - ❌ Viewers of a shared album cannot analyse it.
- **Option B:** Any album the viewer can access; descendants count only where the viewer has access too.
  - ✅ Works for shared family galleries.
  - ❌ Per-album access query on every aggregate and in the cache key.
- **Option C:** Owned album only, no descendants.
  - ✅ Simplest query.
  - ❌ Parent albums of a trip show nothing.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-085-16, S-085-20 … S-085-23 and [ADR-085-02](../../../6-decisions/ADR-085-02-owner-scoped-insights.md).
