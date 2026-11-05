# Feature 108 – Insights Speedup

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-05 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #108 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
`GET /api/v3/Insights` (Feature 085) fails on a server holding about 70,000 photos. On the development copy (MariaDB 11.8, 67,790 photos, 70,657 album links, Xdebug off) the same requests take 1.7 s (Library), 9.0 s (Range 2005–2026) and 12.7 s (album scope over a 47,707-photo tree); the default image (FrankenPHP/Octane) stops a request after 30 s (`LYCHEE_MAX_EXECUTION_TIME`). The failure is treated as that time limit (Q-108-01 B). Two paths grow with the square of the photos involved, one repeats a full pass per period, and the per-photo PHP work is heavier than needed. This feature makes every query of the computation linear without a schema change (Q-108-02 A) and keeps the responses byte-for-byte identical. Backend only: `App\Actions\Insights`, `App\DTO\Insights\InsightsPeriod`; no API, resource or frontend change.

## Goals
- Every scope (owner, whole instance, album tree) and period (Library, Year, Range) is computed with queries whose cost grows linearly with the photos involved.
- The same aggregates as Feature 085 for every scope and period, whatever the page size.
- The years list is computed at most once per (scope, revision).
- Less PHP per photo: one timezone shift and one `format()` per capture time, EXIF strings parsed once per photo.

## Non-Goals
- N-108-01: No index or column added (Option B of Q-108-02 declined: the single `taken_at` index was dropped in 2022 for MySQL planner reasons).
- N-108-02: No queued or background computation (Option C declined).
- N-108-03: No change to the time limit, the cache TTL or the revision rule.
- N-108-04: No change to `Statistics::albumSpace` or any other endpoint the page calls (measured at 11 ms).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-108-01 | `GET /api/v3/Insights` returns the same aggregates as Feature 085 for every scope and period. | Feature 085 tests pass unchanged. | Request validation unchanged (`GetInsightsRequest`). | Unchanged. | None. | Q-108-02 A |
| FR-108-02 | Paging never changes the result: aggregates computed with a page size of 1 equal those computed with the default 1,000, for owner, whole-instance and album scopes over Library, Year and Range. A photo linked to two albums of a scope tree is counted once, also when its links straddle a page boundary. | Equal `toArray()` for page sizes 1 and 1,000 (S-108-01, S-108-02). | – | – | None. | Q-108-02 A |
| FR-108-03 | The local years list of a scope (`years`) is kept in the cache under (scope, revision) for one hour. A Library computation stores it; a Year or Range computation reads it and streams the scope's capture times only when the entry is missing. | Year after Library at the same revision: no years pass (S-108-06). | – | Entry missing or expired: the years pass runs and stores the list. | None. | Q-108-02 A |
| FR-108-04 | A capture time whose timezone is more than 14 h from UTC at that instant (an offset such as `+15:00`, or a local mean time before standard time, e.g. `Asia/Manila` before 1845) is read in UTC, like an unknown timezone, so the widened window and the core window of NFR-108-02 hold for every stored value and the photo counts in the same period for the totals and for the album and people counts. ±14:00 itself (`Pacific/Kiritimati`) is a valid offset. | `+15:00` at local 2026-01-01 00:30 (stored 2025-12-31 09:30 UTC) counts for 2025 in the total and in the album count (S-108-10). | – | – | None. | PR #4959 review (CodeRabbit), agent |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-108-01 | Album scope streams: photo IDs are paged by keyset on `photo_album.photo_id` (`ORDER BY photo_id LIMIT n`, no sort on MariaDB); each link's album is tested by a correlated scalar subquery on `albums` by primary key (`_lft` between the tree's bounds), which the planner runs per link instead of a semi-join; IDs are deduplicated per page; each page loads its photos by ID in ascending ID order. Every query binds a fixed number of values whatever the size of the tree: no list of album IDs is bound. No paged query carries the `EXISTS` scope of `InsightsScopeQuery::photos()`. Owner and whole-instance scopes keep `lazyById` on `photos.id`. | The `EXISTS` scope becomes a semi-join that MariaDB materialises and sorts (`Start temporary … Using filesort`) on every page. | `EXPLAIN` on the dev copy; album scope of the largest tree 12.7 s → ≤ 3 s (Xdebug off). | `InsightsScopeQuery`, `ComputeInsights` | Q-108-02 A |
| NFR-108-02 | Albums with a photo in a Year or Range period and distinct people of the period: one `DISTINCT` over the core window — UTC from `from` 00:00:00 + 14 h to `to` 23:59:59 − 14 h, inside the period in every timezone — then the photos of the rest of the widened window (the edge strips, `NOT BETWEEN` the core) by pages of IDs. `PeriodEdges::photoIdPages()` pages the IDs of every photo in the edge strips (keyset on `photos.id`, no scope, page size 1,000, five bindings); each page runs one `DISTINCT` over the scope's rows of those photos (`photos.id IN` the page, so the planner looks them up by primary key), whose rows are kept when their local date is in the period. The number of queries grows with the photos in the edge strips (two per page, plus one), never with links or faces. For a period shorter than 28 h the core is inverted: `BETWEEN` matches nothing and `NOT BETWEEN` everything, so no branch is needed. The Library period keeps its plain `COUNT` queries. | Keyset pages over `photo_album ⋈ photos` with an `OR` cursor and an unindexed `taken_at` filter rebuild and sort the whole join on every page; one unpaged edge query is buffered whole by MySQL (Q-108-03). | Query count independent of link and face count (S-108-03); one edge read per page of edge photos (S-108-11); Range 2005–2026 9.0 s → ≤ 3 s on the dev copy. | `InsightsPeriod::utcCore()`, `PeriodEdges` | Q-108-02 A, Q-108-03 B |
| NFR-108-03 | Per-photo PHP: a capture time is parsed once with `Safe\DateTimeImmutable`, shifted on its native inner object and read with one `format()`; the Monday of its week comes from integer day arithmetic. `PhotoRow` holds the parsed ISO, aperture, shutter, focal length and duration, so each EXIF string is parsed once per photo. | Local time costs 7 µs per photo (four date parses per row inside `Safe\DateTimeImmutable`); three accumulators re-parse the same strings. | PHP share of the Library pass on the dev copy ≤ 0.8 s and Library ≤ 1.5 s (Xdebug off); the remaining cost is fetching the rows (≈ 0.6 s), which the single-pass design keeps. `LocalCaptureTimeTest`, `ExposureParserTest` green. | `LocalCaptureTime`, `PhotoRow`, accumulators | Q-108-02 A |
| NFR-108-04 | No migration and no new dependency. Memory stays flat in the library size: the core values of NFR-108-02 grow with albums and people, not with photos, and each edge read holds the rows (links or faces) of at most one page of edge photos. No query binds more values than the page size (1,000) plus a fixed number, so no driver's parameter limit (SQLite 32,766, PostgreSQL and MySQL 65,535) can be reached. | Q-108-02 A; owner review 2026-11-05 (unbounded `whereIn`); Q-108-03 B (buffered edge rows). | Peak memory on the dev copy ≤ 30 MB per computation; S-108-09. | – | Q-108-02 A |
| NFR-108-05 | MySQL/MariaDB, PostgreSQL and SQLite return identical aggregates (NFR-085-05). | Supported drivers. | Feature tests on SQLite locally, CI on the others. | – | Coding conventions |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-108-01 | Album scope over a tree where a photo sits in the album and in its sub-album: page sizes 1 and 1,000 give the same aggregates, the photo counted once, over Library, Year and Range. |
| S-108-02 | Owner and whole-instance scopes: page sizes 1 and 1,000 give the same aggregates over Library, Year and Range. |
| S-108-03 | Year: linking photos of the period to more albums and adding faces of new people to them changes the album and people counts but not the number of queries. |
| S-108-04 | A photo stored on the last UTC day before the period but taken inside it counts for the period's albums and people; one stored inside the widened window but taken outside does not (Feature 085 tests). |
| S-108-05 | A one-day Range (inverted core) counts its albums and people from the edge rows alone. |
| S-108-06 | A Year request after a Library request at the same revision reuses the years list; after a photo is added the list is computed again. |
| S-108-07 | Local time conversion is unchanged, including a Monday before 1970 and a week starting in the previous year. |
| S-108-08 | `InsightsPeriod::utcCore()`: Year 2025 → `2025-01-01 14:00:00` … `2025-12-31 09:59:59`; one-day Range → start after end; Library → null. |
| S-108-09 | Album scope: adding albums to the tree does not change the largest number of values bound by any query of the computation. |
| S-108-10 | A photo whose timezone is more than 14 h from UTC at its capture time is read in UTC: it counts for its UTC year in the total and in the album count; a ±14:00 offset is kept. |
| S-108-11 | Edge strips read by pages of photo IDs: with page size n every page holds between 1 and n photos of the edge strips, the pages together hold each of them once, the Library period has none, and with page size 1 the album and people counts of a one-day Range run one edge read per edge photo each. |

## Test Strategy
- **Models / Actions:** unit tests for `InsightsPeriod::utcCore()` (S-108-08) and new `LocalCaptureTime` cases (S-108-07, S-108-10); `ExposureParserTest` unchanged.
- **REST API (v3):** new `InsightsScalingTest` (S-108-01 … 03, S-108-05, S-108-09 … 11) on the `InsightsDataset` plus the album-scope additions; `InsightsCacheTest` gains S-108-06; all Feature 085 Insights classes stay green (S-108-04).
- **Artisan commands:** none.
- **Frontend (v7/v8):** none.
- **Docs/Contracts:** no resource change, no `typescript:transform`.
- **Measurements:** scratchpad scripts on the dev copy (read-only), before and after, recorded in the plan.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-108-01 | `InsightsPeriod::utcCore()`: UTC bounds inside the period in every timezone, `null` for the Library period. | DTO |
| DO-108-02 | `PhotoRow` exposure fields as parsed numbers (`?float`). | Actions\Insights |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-108-01 | REST GET /api/v3/Insights | Unchanged contract (API-085-01). | Faster computation only. |

### CLI Commands / Flags
None.

### Telemetry Events
None.

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-108-01 | tests/Feature_v3/Insights/InsightsDataset.php | Reused; album-scope additions built in `InsightsScalingTest`. |

### UI States
None.

## Telemetry & Observability
None added.

## Documentation Deliverables
- Feature 085: NFR-085-06 rewritten to point at NFR-108-01 … 03; ADR-085-03 amended.
- Knowledge map: Library Insights paragraph (album-scope paging, side counts, years cache).
- Roadmap row 108.

## Fixtures & Sample Data
No new fixture files.

## Spec DSL

```
domain_objects:
  - id: DO-108-01
    name: InsightsPeriod::utcCore
    returns: "array{0:string,1:string}|null"
  - id: DO-108-02
    name: PhotoRow
    fields:
      - { name: iso, type: "?float" }
      - { name: aperture, type: "?float" }
      - { name: shutter, type: "?float" }
      - { name: focal, type: "?float" }
      - { name: duration, type: "?float" }
routes:
  - id: API-108-01
    method: GET
    path: /api/v3/Insights
cli_commands: []
telemetry_events: []
fixtures:
  - id: FX-108-01
    path: tests/Feature_v3/Insights/InsightsDataset.php
ui_states: []
```

## Appendix
Baseline on the dev copy (MariaDB 11.8, 67,790 photos, Xdebug off, read-only scripts in the session scratchpad):

| Request | Time | SQL | Peak memory |
|---------|------|-----|-------------|
| Whole instance, Library | 1.73 s | 71 queries, 0.53 s | 23 MB |
| Whole instance, Year 2024 | 1.67 s | 89 queries, 0.99 s | 21 MB |
| Owner 1, Library | 1.77 s | 64 queries, 0.63 s | 23 MB |
| Whole instance, Range 2005–2026 | 8.98 s | – | – |
| Album scope, largest tree (47,707 photos) | 12.67 s | – | – |

PHP per Library pass (67,790 rows): `PhotoRow` 0.07 s, local time 0.47 s, devices 0.14 s, exposure 0.16 s, timeline 0.15 s, formats 0.06 s, others < 0.03 s. `InsightsRevision` 0.08–0.09 s per request.
