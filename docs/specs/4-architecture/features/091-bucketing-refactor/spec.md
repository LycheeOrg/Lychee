# Feature 091 – Bucketing Refactor

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-10-12 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #091 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Photo and album listings are split into buckets (Features 061, 064, 066): `photo_album.bucket_id` and `albums.bucket_id` for regular albums, computed per request for tag, person and smart albums and the Timeline. This feature makes bucketing faster and removes the defects below. Affected layers: bucket computers, recompute jobs, `lychee:recompute-buckets`, the v3 `buckets`/`ratios`/`details` actions, album listing controllers, migrations. The v3 response shapes stay unchanged.

Defects in the current implementation:

| ID | Defect | Effect |
|----|--------|--------|
| D-091-01 | Stored ids carry the granularity they were written with; labels use the current one. `timeline_photos_granularity`, `sorting_photos_col`, `photo_title_bucket_mode` and `photo_title_bucket_prefix_length` never trigger a photo bucket recompute; `timeline_albums_granularity`, `sorting_albums_col` and `title_bucket_*` recompute root albums only. | A `Y-m-d` id formatted with the hour format (`g:i`) reads `12:00`. |
| D-091-02 | A title date prefix has no hour: at hour granularity the stored path writes `Y-m-d-00`, the live path `Y-m-d`. | Every bucket of a title-sorted album reads `12:00`; stored and live ids differ. |
| D-091-03 | The upload path buckets the cast `taken_at` (original zone); recompute jobs, the live path and the Timeline bucket raw UTC. | Hour buckets, and day/month/year buckets near midnight, depend on which path wrote the row last. |
| D-091-04 | Tag, person and smart albums load every photo row into PHP for each `buckets`, `ratios` window and `details` window request, then send the matching ids back in a `whereIn`. | Every window request pays a full pass over the album (60,000 rows: 145 ms of fetch on SQLite before the PHP loop). |
| D-091-05 | `RecomputeAlbumPhotoBucketsJob` writes every row of the album in one `upsert()` with three bindings per row. | Above 21,845 photos the statement exceeds PostgreSQL's bind parameter limit and the job fails. |

Measurements and design options: [open-questions.md](open-questions.md) Q-091-01, Q-091-02.

## Goals

- Bucket ids and labels always agree with the settings in effect.
- One time basis for date buckets (Q-091-02).
- Bucket and window queries for tag, person and smart albums no longer grow with a PHP pass over the album.
- One bucket computation shared by the write paths and the read paths (Q-091-01).

## Non-Goals

- Default value of `timeline_photo_date_format_hour`.
- Date scrubber granularity (Feature 071).
- v8 Timeline layout differences with v7.

## Functional Requirements

Pending Q-091-01 and Q-091-02.
