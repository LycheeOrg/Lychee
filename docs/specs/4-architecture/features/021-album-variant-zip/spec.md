# Feature 021 – Album Variant ZIP Download

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-02-28 |
| Owners | — |
| Linked plan | `docs/specs/4-architecture/features/021-album-variant-zip/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/021-album-variant-zip/tasks.md` |
| Roadmap entry | #021 |
| GitHub issue | https://github.com/LycheeOrg/Lychee/issues/405 |

## Overview

Album-level ZIP downloads are currently hardcoded to include only the ORIGINAL size variant of each photo. This feature extends the album ZIP archive pipeline to accept an optional size variant parameter, allowing users to download ZIP archives containing medium, small, or thumbnail resolution images instead of the full-resolution originals. This reduces download size and time for use cases where full resolution is not needed. Affected modules: backend (Actions, Controllers, Requests), frontend (services, components, composables).

## Goals

1. Allow users to choose which size variant (ORIGINAL, MEDIUM2X, MEDIUM, SMALL2X, SMALL, THUMB2X, THUMB, RAW) is included in album ZIP downloads.
2. Gracefully fall back to ORIGINAL when the requested variant does not exist for a given photo.
3. Provide a frontend UI (modal dialog) for selecting the download variant before triggering an album ZIP download.
4. Respect existing download-gating config flags (`disable_medium_download`, `disable_small_download`, etc.) for album ZIP variant selection.
5. Maintain backward compatibility: omitting the variant parameter defaults to ORIGINAL (existing behaviour).

## Non-Goals

- Mixing multiple size variants in a single ZIP (e.g., ORIGINAL + MEDIUM for different photos).
- Per-photo variant selection within an album ZIP.
- Download progress indicators or client-side streaming.
- Changes to the photo-level ZIP download (already supports variant selection).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-021-01 | Album ZIP endpoint accepts an optional `variant` query parameter of type `DownloadVariantType`. | ZIP contains the chosen size variant for each photo. | Invalid enum value → 422 validation error. | — | Existing `AlbumDownload` event. | GitHub #405 |
| FR-021-02 | When `variant` is omitted for album downloads, the archive defaults to ORIGINAL (backward compat). | Produces the same ZIP as before this feature. | — | — | — | Backward compatibility |
| FR-021-03 | When a photo lacks the requested size variant, the archive falls back to ORIGINAL. | ZIP includes the original file for that photo. | — | If ORIGINAL also missing, photo is skipped (existing behaviour). | — | Graceful degradation |
| FR-021-04 | RAW variant gated by `raw_download_enabled` config flag (existing behaviour in `ZipRequest::authorize()`). | RAW included when enabled. | RAW requested when disabled → 403. | — | — | Existing config |
| FR-021-05 | Frontend shows a download-variant selection modal when the user clicks an album download button. | Modal lists available variant types; clicking one triggers the download. | Variants disabled by config flags are hidden. | — | — | GitHub #405 |
| FR-021-06 | Multi-album batch download also supports variant selection via the same modal. | ZIP of multiple albums with the chosen variant. | — | — | — | GitHub #405 |
| FR-021-07 | Context menu "Download" for albums opens the variant selection modal instead of immediately downloading. | Modal appears; user picks variant. | — | — | — | UX consistency |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-021-01 | No performance regression for default (ORIGINAL) album ZIP downloads. | Backward compat | Same streaming latency as before. | — | — |
| NFR-021-02 | Fallback to ORIGINAL per photo must not cause N+1 query overhead. | Performance | Eager loading of requested size variant type. | `ZipRequest::processAlbums()` eager loading | — |

## UI / Interaction Mock-ups

### Album Download Modal (DownloadAlbum.vue)

```
┌──────────────────────────────────────┐
│                                      │
│  ☁ Original                          │
│  ☁ Medium 2x                         │
│  ☁ Medium                            │
│  ☁ Small 2x                          │
│  ☁ Small                             │
│  ☁ Thumb 2x                          │
│  ☁ Thumb                             │
│                                      │
│  [ Close ]                           │
└──────────────────────────────────────┘
```

- Each row is a PrimeVue `Button` with `pi-cloud-download` icon.
- Rows for disabled variants (e.g., `disable_medium_download = true`) are hidden.
- RAW row shown only when `raw_download_enabled = true`.
- Clicking a row triggers `AlbumService.download(album_ids, variant)` and closes the modal.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-021-01 | Album ZIP with default (no variant param) → ORIGINAL files in archive |
| S-021-02 | Album ZIP with `variant=MEDIUM` → MEDIUM files; fallback to ORIGINAL when MEDIUM missing |
| S-021-03 | Album ZIP with `variant=RAW` and `raw_download_enabled=false` → 403 |
| S-021-04 | Album ZIP with `variant=RAW` and `raw_download_enabled=true` → RAW files in archive |
| S-021-05 | Multi-album ZIP with variant → all albums use chosen variant with per-photo fallback |
| S-021-06 | Frontend modal shows only enabled variants based on config flags |
| S-021-07 | Context menu download for album opens modal |

## Test Strategy

- **Unit:** Test `ZipRequest` rules accept optional `variant` for album downloads. Test `BaseArchive` variant threading (mocked dependencies).
- **Feature:** End-to-end album ZIP download with various variant values; verify response headers and status codes.
- **UI:** Manual verification of download modal appearance and variant filtering.

## Interface & Contract Catalogue

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-021-01 | GET /api/v2/Zip?album_ids=...&variant=MEDIUM | Album ZIP with variant selection | `variant` is optional; defaults to ORIGINAL |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-021-01 | Download modal visible | User clicks album download button → modal opens with variant list |
| UI-021-02 | Download modal hidden | User clicks variant or Close → modal closes; download starts if variant clicked |

## Documentation Deliverables

- Update knowledge map with download variant threading in album archive pipeline.
- Update roadmap with Feature 021 status.
