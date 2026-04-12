# Feature 035 – Chunked Archive Download

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-04-12 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/035-chunked-download/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/035-chunked-download/tasks.md` |
| Roadmap entry | #35 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Large album downloads can produce a single ZIP file that exceeds browser, server, or client memory limits, causing failed or incomplete downloads. This feature introduces optional **chunked archive download**: an admin-configurable mode that splits the photo set into multiple numbered ZIP parts, which the frontend downloads sequentially and the user extracts with standard split-archive tools (7-Zip, WinRAR). Two new admin settings control the feature: a boolean toggle (`download_archive_chunked`) and a per-chunk photo count (`download_archive_chunk_size`, default 300). The existing `GET /Zip` endpoint gains a `chunk` parameter, and a new `GET /Zip/chunks` endpoint exposes the total chunk count ahead of time.

## Goals

- Add two DB-backed admin settings: `download_archive_chunked` (bool, default `0`) and `download_archive_chunk_size` (int, default `300`).
- Expose chunk metadata via `GET /Zip/chunks` so the frontend knows how many archives to request.
- Extend `GET /Zip` to accept an optional `chunk` query parameter that slices the photo set accordingly and names the download `archive.part<n>.zip`.
- When chunked mode is enabled, the `DownloadAlbum` (and `DownloadPhoto`) Vue components download all parts sequentially, presenting the user with a split archive compatible with 7-Zip / WinRAR.
- Expose `is_download_archive_chunked` through `InitConfig` so the frontend can decide the download strategy without an extra request. `download_archive_chunk_size` remains server-side only and is never sent to the client.

## Non-Goals

- True multi-volume ZIP (`z01`/`z02`/`znn`/`zip` extension format as produced natively by 7-Zip split-archive writer) — the parts are individual independent ZIPs containing a contiguous slice of photos, not a single split stream.
- Server-side parallelism or background job queuing for chunk generation.
- Progress reporting or resumable downloads.
- Chunking of sub-album structures — chunking is applied to the flat photo list across all requested albums; directory structure is preserved per-chunk as if the chunk were the full album.
- Changing behaviour for single-photo downloads (`PhotoController::getArchive`).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-035-01 | Admin can enable chunked download via `download_archive_chunked` setting (default `false`). | Setting stored in `configs` table; value reflected in `InitConfig`. | `ZipRequest` reads the setting before deciding chunked path; setting admin UI shows the toggle. | Feature disabled by default — no change in behaviour unless explicitly enabled. | None. | Problem statement. |
| FR-035-02 | Admin can set `download_archive_chunk_size` (integer ≥ 1, default `300`) defining how many photos per chunk. | Setting stored in `configs` table; consumed server-side only — not exposed to the frontend. | Validation rejects values < 1. | Value < 1 results in a 422 response from the settings form. | None. | Problem statement. |
| FR-035-03 | `GET /Zip/chunks?album_ids=…&variant=…` returns `{ total_chunks: int, total_photos: int }` when chunked mode is enabled. | JSON body with `total_chunks` and `total_photos`. | Same auth checks as `GET /Zip`; album/photo resolution errors return 4xx. | Returns `{ total_chunks: 1, total_photos: n }` when chunked mode is disabled (single archive). | None. | Problem statement: "1 request to get the number of chunks". |
| FR-035-04 | `GET /Zip?album_ids=…&variant=…&chunk=<n>` (1-indexed) returns a ZIP archive containing only photos in positions `[(n-1)*chunk_size + 1 … n*chunk_size]` of the ordered photo list. **All filenames for the complete photo set are pre-computed before any slicing occurs**, so names within the slice are guaranteed to be unique relative to every other chunk (no cross-chunk collisions). | Streamed response; `Content-Disposition` filename is `<title>.part<n>.zip`. | Invalid `chunk` values (< 1, > total_chunks, non-integer) return 422. | Out-of-range chunk returns 422; missing photos in the slice are silently skipped (existing behaviour). | Existing `AlbumDownload` / `PhotoDownload` metrics events dispatched once per chunk request. | Problem statement. |
| FR-035-05 | When `chunk` is absent, `GET /Zip` behaves exactly as before — a single archive, original filename — regardless of whether chunked mode is enabled (Q-035-01 → Option A). | Same response as current implementation. | Existing tests continue to pass without modification. | No change from current behaviour. | Unchanged. | Backward-compatibility; Q-035-01 resolved as Option A. |
| FR-035-06 | Frontend downloads all chunks sequentially when chunked mode is enabled, triggering a browser-native save dialog for each part. | All `n` chunks downloaded; user has `archive.part1.zip … archive.partN.zip` on disk. | UI shows a progress indicator (e.g. "Downloading part 2 / 5"). | Network error on part `k` is surfaced to the user; remaining parts are not attempted. | None. | Problem statement. |
| FR-035-07 | `InitConfig` exposes `is_download_archive_chunked: bool` only. `download_archive_chunk_size` is **not** sent to the client — it is consumed server-side when computing `total_chunks` and slicing the photo set. | `is_download_archive_chunked` present in the `GET /Gallery/Init` response; `download_archive_chunk_size` absent. | TypeScript type `App.Http.Resources.GalleryConfigs.InitConfig` has only `is_download_archive_chunked`. | N/A. | None. | Owner clarification. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-035-01 | Chunk slicing must not load all photos into memory at once; it must use offset/limit queries against the existing photo source. | Memory safety for large albums. | Peak RSS does not increase proportionally with album size when chunked mode is active. | `BaseArchive` refactor to accept `offset` + `limit`. | Architecture. |
| NFR-035-02 | The `GET /Zip/chunks` response must be fast (< 200 ms p95) — it only counts photos, does not load files. | UX: frontend calls this before starting downloads. | COUNT query; no file I/O. | Existing photo query pipeline. | UX. |
| NFR-035-03 | The `chunk` parameter must be validated and rejected (422) before any archive generation begins. | Security/robustness. | PHPUnit test covering invalid `chunk` values. | `ZipRequest` validation. | Robustness. |
| NFR-035-04 | Filenames for every photo in the full set must be pre-computed (de-duplicated) before the chunk slice is applied, so that names inside chunk N are globally unique across all chunks. | Correctness — avoids cross-chunk name collisions when a user extracts all parts into the same directory. | PHPUnit test: two chunks of a 350-photo album have disjoint filenames; no name appears in both ZIPs. | `BaseArchive` filename generation refactor. | Owner clarification. |

## UI / Interaction Mock-ups (required for UI-facing work)

### Admin Settings Panel (Image Processing category)

```
┌─────────────────────────────────────────────────────────────────┐
│ Image Processing                                                │
├─────────────────────────────────────────────────────────────────┤
│ …existing rows…                                                 │
│ Chunked archive download      [ OFF / ON  ]                     │
│   Split large downloads into multiple ZIP parts.                │
│ Photos per archive chunk      [ 300      ]                      │
│   Number of photos in each part (requires chunked mode on).     │
└─────────────────────────────────────────────────────────────────┘
```

_Note: both settings are admin-side only. The admin settings panel auto-renders bool/int config keys in the `Image Processing` category; no additional frontend wiring is required._

### Download Album Dialog (chunked mode ON, 3 chunks)

```
┌─────────────────────────────────────────────────────────────────┐
│              Download Album (3 parts)                           │
│                                                                 │
│  [▼ RAW – 3 parts]                                              │
│  [▼ Original – 3 parts]                                         │
│  [▼ Medium HiDPI – 3 parts]                                     │
│  …                                                              │
│                                                                 │
│  ── Downloading part 2 / 3 ──────────────── [Cancel]           │
│                                                                 │
│                          [Close]                                │
└─────────────────────────────────────────────────────────────────┘
```

### Download Album Dialog (chunked mode OFF — unchanged)

```
┌─────────────────────────────────────────────────────────────────┐
│              Download Album                                     │
│  [▼ RAW]                                                        │
│  [▼ Original]                                                   │
│  …                                                              │
│                          [Close]                                │
└─────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-035-01 | Chunked mode OFF — `GET /Zip` with no `chunk` param: single archive with current filename, identical to existing behaviour. |
| S-035-02 | Chunked mode ON — `GET /Zip/chunks`: returns correct `total_chunks` and `total_photos`. |
| S-035-03 | Chunked mode ON — `GET /Zip?chunk=1` on an album with 350 photos (chunk_size=300): returns 300-photo archive named `<title>.part1.zip`. |
| S-035-04 | Chunked mode ON — `GET /Zip?chunk=2` on an album with 350 photos (chunk_size=300): returns 50-photo archive named `<title>.part2.zip`. |
| S-035-05 | Chunked mode ON — `GET /Zip?chunk=0`: returns 422. |
| S-035-06 | Chunked mode ON — `GET /Zip?chunk=99` (beyond total_chunks): returns 422. |
| S-035-07 | Chunked mode ON — `GET /Zip` with no `chunk` param: behaves as a single-archive download (Option A — Q-035-01 resolved). |
| S-035-08 | Frontend sequential download: all n parts requested and saved; progress label updates per part. |
| S-035-09 | Frontend: network error on part k surfaces an error toast; no further parts are attempted. |
| S-035-10 | `GET /Zip/chunks` with invalid album IDs: returns 4xx consistent with existing ZipRequest validation. |
| S-035-11 | `download_archive_chunk_size` set to 0 or negative via admin: 422 from settings form. |
| S-035-12 | Chunked mode ON — two chunks of a 350-photo album (chunk_size=300) have disjoint filenames; no filename appears in both parts. |

## Test Strategy

- **Core / Application:** PHPUnit feature tests for new `ZipChunksRequest`, `ZipRequest` with `chunk` param, `BaseArchive` filename pre-generation and slice logic. Covers S-035-02 through S-035-07, S-035-10 through S-035-12.
- **REST:** `tests/Feature_v2/` tests for `GET /Zip/chunks` and `GET /Zip?chunk=n` endpoints across auth scenarios (guest, user, admin).
- **UI (JS):** Vitest unit tests for `DownloadAlbum.vue` sequential-chunk logic; mock `AlbumService.getChunkCount()` and `AlbumService.downloadChunk()`.
- **Docs/Contracts:** `InitConfig` TypeScript type updated with `is_download_archive_chunked` only; verified by TypeScript compilation.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-035-01 | `ZipChunksData` — DTO returned by `GET /Zip/chunks`: `{ total_chunks: int, total_photos: int }`. | REST, application |
| DO-035-02 | `ChunkSlice` — value object `{ offset: int, limit: int }` derived from `chunk` + `chunk_size`. | application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-035-01 | REST GET `/api/v2/Zip/chunks` | Returns `{ total_chunks, total_photos }` for the given album/photo set and variant. | Same auth as `GET /Zip`; query params identical to `ZipRequest`. |
| API-035-02 | REST GET `/api/v2/Zip?chunk=<n>` | Streams a ZIP containing photos at offset `(n-1)*chunk_size` for `chunk_size` items. Filenames are pre-computed across the full set before slicing. `Content-Disposition` filename: `<title>.part<n>.zip`. | `chunk` param is optional; absent ≡ single-archive download (FR-035-05, Q-035-01 → Option A). |

### CLI Commands / Flags

_None for this feature._

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-035-01 | `AlbumDownload` (existing) | Dispatched once per chunk request; `album_id`. No new fields. |
| TE-035-02 | `PhotoDownload` (existing) | Dispatched once per chunk request; `photo_id`, `from_id`. No new fields. |

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-035-01 | `tests/Feature_v2/Zip/` | Feature tests for chunk endpoint (new folder). |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-035-01 | Download dialog — chunked idle | `is_download_archive_chunked = true`; dialog shows "(N parts)" label on each button. |
| UI-035-02 | Download dialog — downloading part k/n | After clicking a variant; sequential fetch in progress; progress label visible. |
| UI-035-03 | Download dialog — error | Network failure during chunk fetch; error toast shown; dialog closeable. |
| UI-035-04 | Download dialog — non-chunked (unchanged) | `is_download_archive_chunked = false`; dialog identical to current implementation. |

## Telemetry & Observability

Existing `AlbumDownload` and `PhotoDownload` metric events are reused unchanged. Each chunk request dispatches the same event as a full download would. No new events are introduced.

## Documentation Deliverables

- Update `docs/specs/4-architecture/knowledge-map.md` to reference feature 035.
- Admin settings documentation (inline `description` + `details` fields in the migration).

## Fixtures & Sample Data

- `tests/Feature_v2/Zip/` — new directory containing `ZipChunksTest.php` and `ZipChunkedDownloadTest.php`.

## Spec DSL

```yaml
domain_objects:
  - id: DO-035-01
    name: ZipChunksData
    fields:
      - name: total_chunks
        type: integer
        constraints: ">= 1"
      - name: total_photos
        type: integer
        constraints: ">= 0"
  - id: DO-035-02
    name: ChunkSlice
    fields:
      - name: offset
        type: integer
        constraints: ">= 0"
      - name: limit
        type: integer
        constraints: ">= 1"

routes:
  - id: API-035-01
    method: GET
    path: /api/v2/Zip/chunks
  - id: API-035-02
    method: GET
    path: /api/v2/Zip
    notes: chunk param optional

telemetry_events:
  - id: TE-035-01
    event: AlbumDownload (existing)
  - id: TE-035-02
    event: PhotoDownload (existing)

fixtures:
  - id: FX-035-01
    path: tests/Feature_v2/Zip/

ui_states:
  - id: UI-035-01
    description: Download dialog — chunked idle
  - id: UI-035-02
    description: Download dialog — downloading part k/n
  - id: UI-035-03
    description: Download dialog — error
  - id: UI-035-04
    description: Download dialog — non-chunked unchanged
```

## Appendix

### Q-035-01 — ✅ RESOLVED: Option A

**Question:** What should `GET /Zip` (no `chunk` param) do when chunked mode is enabled?

**Resolution (2026-04-12):** **Option A** — Treat missing `chunk` as a regular single-archive download, regardless of the chunked-mode setting. This is backward-compatible: legacy frontends and direct URL downloads work without modification. Encoded in FR-035-05.

---

### Why independent ZIPs rather than a true split archive?

A true multi-volume ZIP (`z01`/`z02`/`zip`) requires the final volume to be produced before the previous volumes can be verified by most clients. Generating them in sequence on the server would require buffering the entire archive in memory or on disk. Independent per-chunk ZIPs stream directly from photo storage, use no additional disk space on the server, and are playable individually by any ZIP tool. Users who want a single logical archive can combine parts with 7-Zip ("Combine files") or WinRAR.
