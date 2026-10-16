# Feature 094 – Scrubber Adaptive Ticks

| Field | Value |
|-------|-------|
| Status | Implemented 2026-10-16 (drift gate in plan) |
| Last updated | 2026-10-16 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #094 |
| Decisions | — (ADR-071-01 and ADR-092-01 are context; Q-094-01 … 08 resolved in [open-questions.md](open-questions.md)) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The v8 date scrubber rail (`TimelineDatesV3.vue`, Feature 066 T-067, reused on albums by Feature 071) labels its resting state by **year** only: one year label per year, plus one dot per month on `/timeline` or one dot per section header on albums (FR-071-12). A library or album that covers one year shows a single label and its month dots; one that covers a few days of one month shows a single label (plus one dot per header on an album with day headers).

This feature makes the label unit follow the **precision of the rail's entries** and the **span of the data**: the rail labels the coarsest calendar unit in which the data varies. On `/timeline` the entries are the bucket ids, so their precision is the chosen granularity. On albums the entries are the client-derived day runs of Feature 071 (ADR-071-01), so the precision is always day, whatever the section-header granularity (Q-094-01, Q-094-02).

| Data covers… | Label unit | Example labels |
|--------------|-----------|----------------|
| several years | year | `2026`, `2025`, `2024` |
| several months of one year | month | `Oct`, `Nov`, `Dec` (short month name, FR-094-03) |
| several days of one month | day | `14/10`, `15/10` (`DD/MM`, configurable in a later feature) |
| one day (`/timeline` at hour granularity only) | hour | `09:00`, `15:00` |

On `/timeline` at hour granularity, the lens and the readout pill show the date **and** the hour (`14 Oct 2026 15:00`), instead of the server's hour label; when every photo is on the same day, they show the hour only (`15:00`) (owner requests 2026-10-16).

Frontend only, v8 only. No API, config, storage or bucket change.

## Goals

- G1 – Rail labels follow the coarsest unit in which the data varies, never finer than the rail's entries.
- G2 – One rule for `/timeline` and album rails.
- G3 – Labels never overlap.
- G4 – Playhead, drag-scrub and tap behaviour unchanged.
- G5 – On `/timeline` at hour granularity the lens and readout pill tell the time (with the date, unless every photo is on the same day).

## Non-Goals

- N-094-01 – Configurable label formats. `d/m` and `H:00` are fixed constants here; a later feature moves them into `InitConfig` next to `date_scrubber_label_format`.
- N-094-02 – Lens and readout pill labels other than `/timeline` at hour granularity. They keep the bucket labels (`/timeline` at year, month, day) and `date_scrubber_label_format` (albums).
- N-094-03 – Hour entries on album rails. Album rails keep day runs (FR-071-08, ADR-071-01), so they never show hour labels (Q-094-01).
- N-094-04 – v7 (`TimelineDates.vue`) and the v2 Timeline path.
- N-094-05 – Backend: bucket ids, labels, granularity resolution, `AlbumConfig`, `InitConfig`.
- N-094-06 – Rail eligibility (FR-071-06) and settings layers (FR-071-01 … 07). An album whose grid has a single section header keeps showing no rail (Q-094-08).
- N-094-07 – A frontend unit-test runner (no new dependency, same as Feature 071 NG8).

## Definitions

- **Precision.** A date key `Y`, `Y-m`, `Y-m-d`, `Y-m-d-H` has precision 1 (year), 2 (month), 3 (day), 4 (hour): its number of `-` segments. Unit *a* is **coarser** than *b* when its precision is lower.
- **Entry key.** The date key of a rail entry: `/timeline` → the bucket id (FR-092-05: a prefix of the granularity-free key, so its length is the effective granularity); album → the day key of FR-071-08, without the `#n` run suffix.
- **Entry precision E.** The precision of the rail's first dated entry key: `/timeline` → the granularity (1–4); album → 3.
- **Span unit S.** From the smallest and largest dated entry key: the coarsest unit whose prefix differs between them; when they are equal, S = E.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-094-01 | Entry keys are read from the entries' `bucketId` with a trailing `#n` stripped. `unknown`, keys that do not match `^\d{4}(-\d{2}){0,3}$`, and entries without a layout entry are ignored for span, labels and dots. | `2026-10-14#1` → key `2026-10-14`, precision 3. | `unknown` → ignored. | No dated entry → no label, no dot (rail otherwise unchanged). | none | Derived. Defensive: undated photos never reach a rail today — `/timeline` excludes them (FR-092-06) and album day runs skip them (FR-071-08). |
| FR-094-02 | Label unit L = S. S compares the minimum and maximum keys, so it does not depend on the sort direction. A unit differs when the calendar prefix differs: 2025-12-20 and 2026-01-10 differ by year (Q-094-03). The view's granularity bounds L only through E: on `/timeline` E is the granularity; on albums E = day, independently of the section-header granularity (Q-094-02). An album rail needs at least two section headers (FR-071-06, kept by Q-094-08), so the data always spans at least one header unit and album labels are never finer than the album's headers. | `/timeline` at hour, data 2026-10-03 … 2026-10-28 → L = day. | Album with day headers, photos across months of 2026 → L = month. | — | none | User request; Q-094-02, Q-094-03 |
| FR-094-03 | Label formats by L, applied to the key's own fields with no time-zone conversion (keys are already capture-local, FR-092-01/18): year → `Y`; month → `phpDateFormat("M")`'s short month name: the browser locale's (`Intl`) when the gallery language is English (`Oct` in en-US, `Sept` in en-GB, `Okt.` in de-DE), English (`Oct`) for every other gallery language (Q-094-06); day → `d/m`; hour → `H:00` (Q-094-05). `d/m` and `H:00` are named constants (N-094-01). | L = day, key `2026-10-14` → `14/10`; L = hour, key `2026-10-14-15` → `15:00`. | — | — | none | User request; Q-094-05, Q-094-06 |
| FR-094-04 | One label per L-unit, at the `top` of that unit's first entry in rail order, positioned by pixel fraction as today. Collision: in rail order, a label within 16 px of the previously kept label is dropped; the first label is always kept. | Twelve months with photos → twelve labels. | Years 2010 … 2019 compressed into 40 px → no two kept labels closer than 16 px. | — | none | G3 |
| FR-094-05 | Highlight: the label of the L-unit that contains the hovered entry (while hovering), otherwise the entry under the playhead, is drawn `text-primary`; the others `text-muted` (generalises today's year highlight). When that label was dropped by FR-094-04, no label is highlighted. | Scrolling from October into November moves the highlight from `Oct` to `Nov`. | — | — | none | Parity |
| FR-094-06 | Dots. `/timeline` (no `separatorTops`): one dot per unit of precision L + 1, at that unit's first entry, only when L + 1 ≤ E (L = E → no dots). Albums: one dot per section header (`separatorTops`, FR-071-12), except the header directly above an entry that starts an L-unit (the first entry at or below it, with no other header in between): that unit's label already marks it, and the header row's own height would otherwise leave its dot a few pixels above the label on short albums. Headers finer than the day runs (hour headers) fall inside a run and always draw a dot. In both views a dot within 16 px of a label is dropped (unchanged rule) and, in rail order, a dot within 4 px of the previously kept dot is dropped. | `/timeline` at hour, data over several years → L = year, month dots (today's look). | `/timeline` at month, data within 2026 → L = month = E → labels only. | — | none | Q-094-04 |
| FR-094-07 | Lens and pill positions and animations (except the wide lens of FR-094-09), the count phrase, playhead, drag-scrub, tap, the `load`/`scrub` emits and the `countLabelKey`/`separatorTops` props behave as before. `TimelineDatesV3.vue` gets no new prop. | — | — | — | none | G4, N-094-02 |
| FR-094-08 | Hourly lens and readout: on `/timeline`, when the bucket ids have precision 4, each lens item and the readout pill show `phpDateFormat(date_scrubber_label_format, day)` + a space + the hour in the `H:00` constant of FR-094-03, built from the bucket id with no time-zone conversion. When all dated bucket ids share the same day (S = hour, so the rail labels hours, FR-094-02), they show the hour only, `H:00`. `Timeline.vue` passes these labels in the `buckets` prop it gives `TimelineDatesV3.vue`; the bucket tier's own labels (section headers) are unchanged. Precisions 1–3 keep the server labels; album rails keep their day labels (N-094-03). | Bucket `2026-10-14-15`, day format `d M Y`, data over several days → `14 Oct 2026 15:00`; all photos on 2026-10-14 → `15:00`. | — | — | none | Owner requests 2026-10-16 |
| FR-094-09 | Wide lens: when the rail shows dated hour labels (bucket ids of precision 4 on more than one day, FR-094-08), the lens is `w-60` (240 px) instead of `w-50`, and the readout pill beside the open lens sits at `right-63` / `left-63` (RTL) instead of `right-53` / `left-53`. `TimelineDatesV3.vue` decides this from `buckets.bucket_ids`, with no new prop. The fade mask keeps its 26 % proportion. Hour-only labels (one day), other granularities and album rails keep `w-50`. | `/timeline` at hour, data over several days → 240 px lens; the focal `14 Oct 2026 15:00` (about 148 px) clears the faded left edge. | `/timeline` at hour, one day → 200 px lens. | — | none | Q-094-07 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-094-01 | Tick and lens-width computation is O(n + h) in the number of entries and section headers: one min/max pass and one merged pass over entries and headers in rail order, no sort. | 60,000-photo albums derive one entry per day run | Code review; browser check on the 60,000-photo scratch album stays smooth while scrolling. | — | Feature 071 |
| NFR-094-02 | No new dependency; no API or `lychee.d.ts` change. | AGENTS.md | `package.json`, `composer.json` unchanged; `php artisan typescript:transform` leaves `lychee.d.ts` unchanged. | — | AGENTS.md |
| NFR-094-03 | Unit, span and label decisions live in pure helpers returning plain values. | AGENTS.md (straight-line increments) | Code review: `TimelineDatesV3.vue`'s `ticks` only composes helpers. | — | AGENTS.md |

## UI / Interaction Mock-ups

Resting rail, `/timeline` at hour granularity, by span of the data (w-16 rail, labels right-aligned, `•` = dot, `▶` = highlighted label):

```
 several years        months of 2026       days of Oct 2026     one day
┌──────┐            ┌──────┐            ┌──────┐            ┌──────┐
│▶2026 │            │  Dec │            │ 28/10│            │ 18:00│
│   •  │            │   •  │            │   •  │            │      │
│   •  │            │   •  │            │ 21/10│            │ 16:00│
│   •  │            │▶ Nov │            │   •  │            │▶15:00│
│ 2025 │            │   •  │            │   •  │            │      │
│   •  │            │   •  │            │▶14/10│            │ 11:00│
│   •  │            │  Oct │            │   •  │            │      │
│ 2024 │            │   •  │            │ 03/10│            │ 09:00│
└──────┘            └──────┘            └──────┘            └──────┘
 dots = months       dots = days          dots = hours         L = E = hour: no dots
```

Album rail with day headers, photos across months of 2026: month labels, one dot per day header that is not the first of its month (FR-094-06). An album whose photos fall into a single header bucket (for example year headers, all photos in 2026) shows no rail at all (FR-071-06, Q-094-08):

```
┌──────────────────────────────────────────────┬──────┐
│ 30 Oct 2026                                   │▶ Oct │
│ ┌────┐┌────┐┌────┐                            │   •  │
│ └────┘└────┘└────┘                            │   •  │
│ 27 Oct 2026                                   │  Sep │
│ ┌────┐┌────┐                                  │   •  │
│ └────┘└────┘                                  │   •  │
└──────────────────────────────────────────────┴──────┘
```

Lens and readout pill, `/timeline` at year, month or day granularity and on albums (unchanged):

```
                    ┌──────────────────┐ ┌────────────────┐
                    │ 14 Oct 2026  42  │ │   13 Oct 2026  │
                    └──────────────────┘ │ ━ 14 Oct 2026 ━│
                                         │   15 Oct 2026  │
                                         └────────────────┘
```

Lens and readout pill, `/timeline` at hour granularity, data over several days: wide lens, 240 px (FR-094-08, FR-094-09):

```
                    ┌────────────────────────┐ ┌──────────────────────┐
                    │ 14 Oct 2026 15:00   7  │ │   14 Oct 2026 11:00  │
                    └────────────────────────┘ │ ━ 14 Oct 2026 15:00 ━│
                                               │   14 Oct 2026 18:00  │
                                               └──────────────────────┘
```

Same, every photo on 2026-10-14: hour only, 200 px lens (FR-094-08, FR-094-09):

```
                    ┌──────────────┐ ┌────────────┐
                    │ 15:00     7  │ │   11:00    │
                    └──────────────┘ │ ━ 15:00 ━  │
                                     │   18:00    │
                                     └────────────┘
```

## Example Catalogue

Every combination of view, granularity and data span, with the resulting resting rail. Normative: an implementation must produce these labels and dots. Labels are listed top to bottom for the default newest-first order; an oldest-first order lists the same labels in reverse (S-094-13). "Dots" are those that survive the culls of FR-094-04/06.

**Reference datasets** (capture-local dates, FR-092-01):

| Span | Dataset | Photo dates |
|------|---------|-------------|
| years | D-Y | 2024-03-05, 2024-11-20, 2025-07-01, 2026-06-02, 2026-10-14 |
| months of one year | D-M | 2026-06-02, 2026-07-15, 2026-08-09, 2026-09-21, 2026-10-14 |
| days of one month | D-D | 2026-10-03, 2026-10-14, 2026-10-21, 2026-10-28 |
| hours of one day | D-H | 2026-10-14 09:10, 11:25, 15:05, 15:40, 18:40 |
| one hour | D-1H | 2026-10-14 15:05, 15:40 |

### `/timeline` (entries = bucket ids, E = granularity)

| ID | Granularity | Dataset | Bucket ids (E) | S = L | Labels | Dots (FR-094-06) |
|----|-------------|---------|----------------|-------|--------|------------------|
| EX-094-T01 | year | D-Y | `2026`, `2025`, `2024` (1) | year | `2026`, `2025`, `2024` | none (L = E) |
| EX-094-T02 | year | D-M, D-D, D-H | `2026` (1) | year | `2026` | none |
| EX-094-T03 | month | D-Y | `2026-10` … `2024-03` (2) | year | `2026`, `2025`, `2024` | months: `2026-06`, `2024-03` (each year's first month in rail order sits on its label) |
| EX-094-T04 | month | D-M | `2026-10` … `2026-06` (2) | month | `Oct`, `Sep`, `Aug`, `Jul`, `Jun` | none (L = E) |
| EX-094-T05 | month | D-D, D-H | `2026-10` (2) | month | `Oct` | none |
| EX-094-T06 | day | D-Y | `2026-10-14` … `2024-03-05` (3) | year | `2026`, `2025`, `2024` | months: `2026-06`, `2024-03` |
| EX-094-T07 | day | D-M | `2026-10-14` … `2026-06-02` (3) | month | `Oct`, `Sep`, `Aug`, `Jul`, `Jun` | none: one day per month, each on its label |
| EX-094-T08 | day | D-D | `2026-10-28` … `2026-10-03` (3) | day | `28/10`, `21/10`, `14/10`, `03/10` | none (L = E) |
| EX-094-T09 | day | D-H | `2026-10-14` (3) | day | `14/10` | none |
| EX-094-T10 | hour | D-Y | `2026-10-14-…` … `2024-03-05-…` (4) | year | `2026`, `2025`, `2024` | months: `2026-06`, `2024-03` |
| EX-094-T11 | hour | D-M | `2026-10-14-…` … `2026-06-02-…` (4) | month | `Oct`, `Sep`, `Aug`, `Jul`, `Jun` | none: day dots, each on its label |
| EX-094-T12 | hour | D-D | `2026-10-28-…` … `2026-10-03-…` (4) | day | `28/10`, `21/10`, `14/10`, `03/10` | none: hour dots, each on its label |
| EX-094-T13 | hour | D-H | `2026-10-14-18`, `-15`, `-11`, `-09` (4) | hour | `18:00`, `15:00`, `11:00`, `09:00` | none (L = E) |
| EX-094-T14 | hour | D-1H | `2026-10-14-15` (4) | hour | `15:00` | none |

Dots appear between labels only when a label unit holds more than one finer unit. With a denser variant of a dataset (photos on several days of each month, several hours of each day), T03/T06/T10 add one dot per further month, T07/T11 one per further day, T12 one per further hour, all subject to the 16 px and 4 px culls.

### Album rails (entries = day runs, E = day)

A rail is shown only when the grid has at least two section headers (FR-071-06, Q-094-08); rows with a single header bucket show no rail. Labels depend only on the span. Dots are the grid's section headers (FR-071-12); a header of the same or a coarser unit than L always sits directly above the start of an L-unit and draws no dot (FR-094-06), so only headers finer than L, and not first in their L-unit, show as dots.

| ID | Header granularity | Dataset | S = L | Labels | Dots (section headers) |
|----|--------------------|---------|-------|--------|------------------------|
| EX-094-A01 | year | D-Y | year | `2026`, `2025`, `2024` | none |
| EX-094-A02 | month | D-Y | year | `2026`, `2025`, `2024` | month headers `2026-06`, `2024-03` (each year's first header in rail order is on its label) |
| EX-094-A03 | day | D-Y | year | `2026`, `2025`, `2024` | day headers `2026-06-02`, `2024-03-05` |
| EX-094-A04 | hour (photos only) | D-Y | year | `2026`, `2025`, `2024` | hour headers of `2026-06-02`, `2024-03-05` |
| EX-094-A05 | year | D-M | — | no rail: one `2026` header (Q-094-08) | — |
| EX-094-A06 | month | D-M | month | `Oct`, `Sep`, `Aug`, `Jul`, `Jun` | none (each header on its label) |
| EX-094-A07 | day | D-M | month | `Oct`, `Sep`, `Aug`, `Jul`, `Jun` | none (one day per month, on its label) |
| EX-094-A08 | hour (photos only) | D-M | month | `Oct`, `Sep`, `Aug`, `Jul`, `Jun` | none (one hour per month, on its label) |
| EX-094-A09 | year | D-D | — | no rail: one header (Q-094-08) | — |
| EX-094-A10 | month | D-D | — | no rail: one header (Q-094-08) | — |
| EX-094-A11 | day | D-D | day | `28/10`, `21/10`, `14/10`, `03/10` | none |
| EX-094-A12 | hour (photos only) | D-D | day | `28/10`, `21/10`, `14/10`, `03/10` | none (one hour per day, on its label) |
| EX-094-A13 | year | D-H, D-1H | — | no rail: one header (Q-094-08) | — |
| EX-094-A14 | month | D-H, D-1H | — | no rail: one header (Q-094-08) | — |
| EX-094-A15 | day | D-H, D-1H | — | no rail: one header (Q-094-08) | — |
| EX-094-A16 | hour (photos only) | D-H | day | `14/10` (no hour labels, N-094-03) | hour headers `-15`, `-11`, `-09` |
| EX-094-A17 | hour (photos only) | D-1H | — | no rail: one header (Q-094-08) | — |
| EX-094-A18 | `disabled` | any | — | no rail (FR-071-06) | — |

These rows hold for photo rails sorted by `taken_at`, `created_at` or title (`DATE_PREFIX`, whose headers are capped at day by FR-092-05, so A04, A08, A12, A16 and A17 do not occur), and for sub-album rails sorted by `created_at`, `min_taken_at`, `max_taken_at` or title (album granularity has no `hour`, so A04/A08/A12/A16/A17 do not occur).

### Lens and readout labels

| ID | View, granularity | Hovered entry | Lens item / pill label | Lens width |
|----|-------------------|---------------|------------------------|------------|
| EX-094-L01 | `/timeline`, hour, D-Y / D-M / D-D | `2026-10-14-15` | `14 Oct 2026 15:00` (FR-094-08) | 240 px |
| EX-094-L02 | `/timeline`, hour, data across midnight (EX-094-B03) | `2026-10-14-00` | `14 Oct 2026 00:00` | 240 px |
| EX-094-L07 | `/timeline`, hour, D-H (one day) | `2026-10-14-15` | `15:00` (FR-094-08) | 200 px |
| EX-094-L08 | `/timeline`, hour, D-1H (one hour) | `2026-10-14-15` | `15:00` | 200 px |
| EX-094-L03 | `/timeline`, day | `2026-10-14` | server label (`timeline_photo_date_format_day`) | 200 px |
| EX-094-L04 | `/timeline`, month | `2026-10` | server label (`timeline_photo_date_format_month`) | 200 px |
| EX-094-L05 | `/timeline`, year | `2026` | server label (`timeline_photo_date_format_year`) | 200 px |
| EX-094-L06 | album, any header granularity (hour included) | `2026-10-14` | `14 Oct 2026` (`date_scrubber_label_format`, unchanged) | 200 px |

Examples assume `date_scrubber_label_format = d M Y`. Every example in this catalogue assumes an English gallery language and an en-US browser (FR-094-03).

### Calendar boundaries and edge cases

| ID | View, granularity | Data | Labels | Dots | Rule |
|----|-------------------|------|--------|------|------|
| EX-094-B01 | `/timeline`, day | 2025-12-20, 2025-12-28, 2026-01-04, 2026-01-10 | `2026`, `2025` | none: month dots `2026-01`, `2025-12` sit on their labels | Calendar boundary (Q-094-03) |
| EX-094-B02 | `/timeline`, day | 2026-09-28, 2026-09-30, 2026-10-01, 2026-10-03 | `Oct`, `Sep` | days `2026-10-01`, `2026-09-28` | Month boundary |
| EX-094-B03 | `/timeline`, hour | 2026-10-13 22:10, 23:50, 2026-10-14 00:20, 01:30 | `14/10`, `13/10` | hours `2026-10-14-00`, `2026-10-13-22` | Day boundary |
| EX-094-B04 | album, day | same as B01 | `2026`, `2025` | header `2026-01-04`, `2025-12-20` | Calendar boundary on albums |
| EX-094-B05 | `/timeline` at day | photo taken 2026-10-13 23:30 UTC in `+02:00`, others on 2026-10-14 | `14/10` only | none | Capture-local keys (FR-092-18): the photo counts for 2026-10-14, not 2026-10-13 |
| EX-094-B11 | `/timeline` at hour | same as B05, others at 15:05 | `15:00`, `01:00` | none | The photo counts for 2026-10-14 01:00; one day → hour labels |
| EX-094-B06 | `/timeline`, year; album sorted by `taken_at` | 2026 photos + photos without `taken_at` | `2026` | none | Undated photos never reach the rail (FR-094-01): excluded from `/timeline` (FR-092-06), skipped by album day runs (FR-071-08) |
| EX-094-B07 | album, month headers, sorted by `taken_at` ascending (rows are grouped by bucket first, FR-092-07, so a day run can recur only under headers coarser than day) | D 2026-09-30 12:00 UTC, A 2026-10-13 23:30 UTC in `+02:00`, B 2026-10-13 23:45 UTC in `+00:00`, C 2026-10-14 00:10 UTC in `+00:00` | `Sep`, `Oct` (rail order) | none | Runs `2026-09-30`, `2026-10-14`, `2026-10-13`, `2026-10-14#1`: the suffix is stripped, C's key reads as `2026-10-14` and adds no label (FR-094-01, FR-094-04) |
| EX-094-B08 | `/timeline`, year | 2010 … 2026, nearly all photos in 2026 | `2026`, then every year label ≥ 16 px below the previous kept one | none | Label cull (FR-094-04) |
| EX-094-B09 | `/timeline`, day | photos every day of 2026-01 … 2026-10 | `Oct` … `Jan` | day dots ≥ 4 px apart and ≥ 16 px from a label | Dot cull (FR-094-06) |
| EX-094-B10 | `/timeline` | one dated photo | one label at L = E | none | S = E (FR-094-02); an album with one photo has one header and no rail |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-094-01 | `/timeline`, granularity year, data 2019 … 2026 → year labels, no dots (FR-094-02, FR-094-06). |
| S-094-02 | `/timeline`, hour, data over several years → year labels, month dots (FR-094-06). |
| S-094-03 | `/timeline`, hour, data across months of 2026 → short month labels, day dots (FR-094-03, FR-094-06). |
| S-094-04 | `/timeline`, hour, data across days of October 2026 → `DD/MM` labels, hour dots (FR-094-03). |
| S-094-05 | `/timeline`, hour, data within one day → `H:00` labels, no dots (FR-094-03). |
| S-094-06 | `/timeline`, month, data within 2026 → month labels, no dots (FR-094-02, FR-094-06). |
| S-094-07 | `/timeline`, day, data 2025-12-20 … 2026-01-10 → labels `2026`, `2025`, no visible dots: each month dot sits on its year label (FR-094-02, EX-094-B01). |
| S-094-08 | Album whose photos fall into a single header bucket (year headers, all photos in 2026) → no rail, as before (FR-071-06, Q-094-08). |
| S-094-09 | Album, day headers, photos across months of one year → month labels, one dot per day header that is not the first of its month (FR-094-06, EX-094-A07). |
| S-094-10 | Album, hour granularity, photos within one day → a single `DD/MM` label; no hour labels (N-094-03). |
| S-094-11 | Album, month headers, photos across several years → year labels, one dot per month header that is not the first of its year (FR-094-02, FR-094-06, EX-094-A02). |
| S-094-12 | Sub-album rail sorted by `max_taken_at`, albums across days of one month → `DD/MM` labels (FR-094-02). |
| S-094-13 | Ascending and descending sort of the same data → same label unit and the same labels (FR-094-02). |
| S-094-14 | Ten year labels compressed into 40 px → no two labels closer than 16 px (FR-094-04). |
| S-094-15 | Scroll / hover across a unit boundary → the highlight follows (FR-094-05). |
| S-094-16 | `/timeline` with a single dated bucket → one label at precision E (FR-094-02). |
| S-094-17 | Photos without a date beside dated ones → they do not change the span or the labels (FR-094-01, EX-094-B06). |
| S-094-18 | Lens, pill, drag-scrub and tap unchanged on `/timeline` and on albums, LTR and RTL, light and dark (FR-094-07). |
| S-094-19 | `/timeline` at hour → lens items and pill show date and hour in a 240 px lens; with every photo on one day, the hour only in a 200 px lens; headers keep their labels (FR-094-08, FR-094-09). |

## Test Strategy

- **Frontend helpers:** pure functions in `resources/js/v8/utils/railTicks.ts`. With no JS test runner (N-094-07), each helper's branch table is written in the plan before the code, and the Example Catalogue rows are checked in the browser (S-094-01 … 19).
- **Static:** `npm run format`, `npm run check` (vue-tsc).
- **Manual browser check:** `run-lychee` scratch instance, seeded so each scenario is reachable; screenshots recorded in the plan.
- **Backend:** none (no PHP change). `php artisan typescript:transform` must leave `lychee.d.ts` unchanged.

## Interface & Contract Catalogue

| Surface | Change |
|---------|--------|
| `resources/js/v8/utils/railTicks.ts` (new) | `entryKeyOf()`, `precisionOf()`, `spanUnit()`, `formatTickLabel()`, `buildRailTicks()`, `hourlyLensLabels()`, `isWideLens()` |
| `Timeline.vue` | passes hourly lens labels (`hourlyLensLabels()`) in the rail's `buckets` prop when ids have precision 4 (FR-094-08) |
| `TimelineDatesV3.vue` | `ticks` built by `buildRailTicks()`; `Tick.kind` becomes `"label" \| "dot"`; highlight compares L-prefixes; wide lens and pill offset from `isWideLens()` (FR-094-09). No prop change. |
| `AlbumPanel.vue`, `dateScrubber.ts`, grids | none |
| API, config, DB, lang | none |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-094-01 | Label unit year / month / day / hour | L = S (FR-094-02); hour only on `/timeline`. |
| UI-094-02 | Highlighted label | The unit containing the playhead or hovered entry (FR-094-05). |

## Telemetry & Observability

None.

## Documentation Deliverables

- Feature 071 spec: NG2 and FR-071-12 point to this feature for resting-state labels.
- Feature 092 plan: backlog item "Date scrubber granularity follows the timeline granularity" closed by this feature.
- Knowledge map: `railTicks.ts` under the Album Date Scrubber entry.
- Roadmap row #094.

## Spec DSL

```
ui_states:
  - id: UI-094-01
    description: rail label unit follows entry precision and data span
  - id: UI-094-02
    description: highlighted label follows the playhead or hover
modules:
  - resources/js/v8/utils/railTicks.ts
  - resources/js/v8/components/gallery/timelineModule/TimelineDatesV3.vue
  - resources/js/v8/views/gallery-panels/Timeline.vue
```
