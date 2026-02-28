# Feature 020 – Raw Upload Support

| Field | Value |
|-------|-------|
| Status | Active |
| Last updated | 2026-02-28 |
| Owners | — |
| Linked plan | `docs/specs/4-architecture/features/020-raw-upload-support/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/020-raw-upload-support/tasks.md` |
| Roadmap entry | #020 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Lychee currently stores only one "original" copy of uploaded photos. Camera RAW files (NEF, CR2, ARW, DNG, ORF, RW2, RAF, PEF, SRW, etc.), PSD files, and HEIC/HEIF files are either converted-and-discarded or stored without processing. This feature introduces a new **RAW** size variant type that preserves the original untouched file while converting it to a displayable JPEG/PNG as the ORIGINAL size variant.

**Affected modules:** Enum layer (`SizeVariantType`, `DownloadVariantType`), Image processing pipeline (`Actions/Photo/Pipes/Init`, `Actions/Photo/Convert`), Models (`SizeVariants`, `SizeVariant`), Resources/API (`SizeVariantsResouce`, `SizeVariantResource`), Download/Archive (`BaseArchive`), Watermarking (`Watermarker`, `ApplyWatermark`), Shop (exclusion), Config system, Frontend (download option, no display), Migration (renumber `type` column).

## Goals

1. **Preserve raw originals**: When a user uploads a camera RAW, PSD, or HEIC/HEIF file, the untouched file is stored as a RAW size variant alongside the converted ORIGINAL.
2. **Renumber SizeVariantType**: Add `RAW = 0` and shift all existing values by +1 via a data migration.
3. **Downloadable raw files**: Provide a download endpoint for the RAW size variant, gated by a config option (`raw_download_enabled`, default: `false`).
4. **No web display**: RAW files are never served to the frontend for rendering; the API omits the RAW variant from the standard `SizeVariantsResouce`.
5. **No shop integration**: RAW is not purchasable via the shop (`PurchasableSizeVariantType` unchanged).
6. **No watermarking**: RAW files are never watermarked.
7. **Refactor HEIC/HEIF**: HEIC/HEIF uploads currently discard the original after conversion. Refactor to preserve the HEIC/HEIF as RAW and use the converted JPEG as ORIGINAL.

## Non-Goals

- Editing or re-processing RAW files server-side (e.g., applying profiles, adjustments).
- Displaying RAW files inline in the gallery (only the converted ORIGINAL is shown).
- Adding RAW to the shop/purchasable types.
- Supporting RAW for video files.
- Backward-converting existing HEIC/HEIF photos that were already converted and whose originals were deleted.
- Hierarchical RAW → ORIGINAL dependency tracking (the RAW variant is simply another row in `size_variants`).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-020-01 | Add `RAW = 0` to `SizeVariantType` enum and shift all existing cases by +1 (`ORIGINAL = 1`, `MEDIUM2X = 2`, …, `PLACEHOLDER = 8`). | Enum values updated, migration shifts `type` column in `size_variants` table. | PHPStan passes, all existing tests pass with new values. | Migration rollback restores original values. | — | Owner directive |
| FR-020-02 | Create a database migration that shifts `type` column values using a two-phase approach (required because `size_variants` has a composite unique constraint on `(photo_id, type)` and PostgreSQL does not support `ORDER BY` in `UPDATE`): Phase 1: `UPDATE size_variants SET type = type + 10 WHERE type >= 0`; Phase 2: `UPDATE size_variants SET type = type - 9 WHERE type >= 10`. Result: all existing values incremented by 1, no unique constraint violations. | All existing rows shifted by +1. No `type = 0` rows exist before migration. | Migration runs against SQLite test DB and production DB (MySQL/PostgreSQL). Cross-DB safe (no `ORDER BY` in `UPDATE`). | Rollback: reverse two-phase (`type + 9`, then `type - 10`). | — | FR-020-01 |
| FR-020-03 | When uploading a file whose extension matches the RAW format list (see FR-020-09), the pipeline: (1) stores the original file as the RAW size variant, (2) converts it to JPEG, (3) treats the JPEG as the ORIGINAL for downstream size variant generation. | RAW row created in `size_variants`, ORIGINAL created from converted JPEG, thumb/small/medium generated normally. | File extension checked against RAW format list. Conversion must succeed for ORIGINAL to be created. | **Graceful fallback** (Q-020-01 resolved → Option C): if Imagick fails to convert the RAW file, fall back to existing `raw_formats` behavior — the file is stored as-is as the RAW size variant with no ORIGINAL or downstream variants (identical to current "accepted raw" storage, except the `type` is RAW instead of ORIGINAL). Log a warning. | — | Owner directive |
| FR-020-04 | Refactor HEIC/HEIF upload flow: instead of converting and deleting the original HEIC/HEIF, store it as RAW size variant and use the converted JPEG as ORIGINAL. **`HeifToJpeg` is replaced entirely** by a unified RAW-to-JPEG converter that handles all convertible formats (camera RAW, PSD, HEIC/HEIF) via Imagick. The converter must NOT delete the source file. `ConvertUnsupportedMedia` pipe is replaced by the new `DetectAndStoreRaw` pipe. If HEIC conversion fails (Imagick unavailable), the HEIC file is stored as RAW-only with no JPEG — this is an acceptable behavior change from the current hard failure. | HEIC/HEIF file preserved as RAW, JPEG used as ORIGINAL. | HEIC/HEIF extension detected as convertible RAW format. | Same as FR-020-03 (graceful fallback — stored as RAW-only, no ORIGINAL). | — | Owner directive |
| FR-020-05 | Add a new config option `raw_download_enabled` (boolean, default: `false`) to the config system. | Config row created via migration, accessible via `ConfigManager`. | Boolean validation in settings UI. | Invalid value rejected. | — | Owner directive |
| FR-020-06 | Add `RAW` case to `DownloadVariantType` enum. The download endpoint serves the RAW file only when `raw_download_enabled === true`. | User can download RAW file when config enabled. | Download request with `RAW` variant type checked against config. | Returns 403/404 when config is disabled or RAW variant doesn't exist. | — | Owner directive |
| FR-020-07 | RAW size variant is **never** watermarked. The skip is enforced in `Watermarker::do()` directly (alongside the existing PLACEHOLDER skip), which also covers `AlbumController::shouldWatermark()` usage. `ApplyWatermark` pipe requires no changes since it delegates to `Watermarker::do()`. | Watermark logic skips RAW. | Watermark applied to all other variants; RAW remains untouched. | — | — | Owner directive |
| FR-020-08 | RAW size variant is **never** included in `SizeVariantsResouce` (no `$raw` field added — the resource stays as-is). A `has_raw: bool` property is added to `PhotoResource` (top-level, alongside `precomputed`), derived from `$photo->size_variants->getRaw() !== null` (DB row existence check). The existing `PreComputedPhotoData::is_raw` (which uses `Photo::isRaw()` based on MIME type) is a separate concept and remains unchanged — it indicates the photo's MIME type is neither a supported image nor video, not whether a RAW size variant exists. | API response for photo includes `has_raw: boolean` on `PhotoResource`. `SizeVariantsResouce` unchanged. | Frontend uses `has_raw` (not `precomputed.is_raw`) to show download button. | — | — | Owner directive |
| FR-020-09 | Define the list of file extensions treated as RAW for auto-conversion: `.nef`, `.cr2`, `.cr3`, `.arw`, `.dng`, `.orf`, `.rw2`, `.raf`, `.pef`, `.srw`, `.nrw`, `.psd`, `.heic`, `.heif`. Uses Imagick with libraw/dcraw delegates for conversion (Q-020-02 resolved → Option A). If a format is unsupported by installed delegates, the graceful fallback from FR-020-03 applies. | Files with these extensions trigger RAW+convert pipeline. | Extension matching is case-insensitive. | Unknown extensions that match the `raw_formats` config are stored as RAW size variants (not ORIGINAL) — **except PDF**, which remains stored as ORIGINAL since it can be rendered/displayed (Q-020-04 resolved). | — | Owner directive |
| FR-020-10 | RAW is **not** added to `PurchasableSizeVariantType`. Shop cannot sell RAW files. | `PurchasableSizeVariantType` enum unchanged. | — | — | — | Owner directive |
| FR-020-11 | `SizeVariants` model class updated to include `$raw` property and corresponding `getRaw()`, `add()`, `getSizeVariant()`, `toCollection()`, `deleteAll()` methods. | RAW variant properly stored, retrieved, and deleted like any other variant. | Unit tests verify CRUD operations. | — | — | FR-020-01 |
| FR-020-12 | Frontend shows a "Download RAW" button/option on the photo detail view, visible only when `has_raw === true` **and** `raw_download_enabled === true`. | Button triggers download of RAW file. | Button hidden when config disabled or no RAW exists. | Download failure shows error toast. | — | FR-020-06, FR-020-08 |
| FR-020-13 | Update `SizeVariantFactory` (`createSizeVariants()` method) to **not** create RAW variants automatically — RAW is only created during the upload preprocessing step. | `createSizeVariants()` still creates PLACEHOLDER through MEDIUM2X only. | `SizeVariantDimensionHelpers::isEnabledByConfiguration()` handles RAW gracefully. | — | — | Consistency |
| FR-020-14 | Update all diagnostics checks (`PlaceholderExistsCheck`, `SmallMediumExistsCheck`, `WatermarkerEnabledCheck`) and statistics queries (`Spaces.php`) to account for the new type values. | Diagnostics queries use correct integer values for ORIGINAL (now 1). | All diagnostics pass with new enum values. | — | — | FR-020-01 |
| FR-020-15 | Update the `SizeVariantFactory` (for test factories) to use the new integer values. | Test factories produce correct `type` values. | — | — | — | FR-020-01 |
| FR-020-16 | Create a data migration that reclassifies existing files currently stored as ORIGINAL whose file extension (extracted from `size_variants.short_path`) matches `raw_formats` config (excluding `.pdf`) to the new RAW size variant type. Uses `short_path` because it preserves the original file extension. No JOIN to `photos` table needed. | Existing raw-format files have `type = RAW` (0) after migration. PDF files retain `type = ORIGINAL` (1). | Migration extracts extension from `short_path`, compares against `raw_formats` config value read at migration time; `.pdf` excluded. | Rollback: set these rows back to `type = ORIGINAL`. | — | Q-020-01, Q-020-04 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-020-01 | Migration must be safe for existing data. The `type` shift must handle all three supported databases (SQLite, MySQL, PostgreSQL). | Data integrity | Migration tested on all three DB engines. Rollback tested. | Database migration system | FR-020-02 |
| NFR-020-02 | RAW file conversion runs within the existing upload job pipeline, which is already asynchronous from the user's perspective (Q-020-03 resolved → Option A). No additional async infrastructure is needed. Conversion is synchronous within the job. | UX responsiveness | Upload already returns immediately; conversion happens in queued job. | Existing job/queue system | Q-020-03 resolved |
| NFR-020-03 | Storage impact: RAW files can be large (20–80 MB for camera RAW). Quota checks must account for RAW + ORIGINAL combined size. | Storage management | Existing `checkQuota()` in `Create.php` already counts total file size; RAW variant adds to it. | Quota system | FR-020-03 |
| NFR-020-04 | Conversion requires Imagick PHP extension with libraw/dcraw delegates (Q-020-02 resolved → Option A). System requirement: `apt install libraw-dev` or equivalent. System must gracefully degrade if Imagick cannot convert a specific RAW format (fallback per FR-020-03). | System dependencies | Diagnostics check verifies Imagick availability and lists supported delegates. | `ext-imagick`, `libraw-dev` | Q-020-02 resolved |
| NFR-020-05 | The `type` column renumbering must not break the composite unique constraint on `(photo_id, type)`. The two-phase migration approach (FR-020-02) avoids constraint violations by shifting to a temporary range first. | Data integrity | Unique constraint on `size_variants(photo_id, type)` remains valid after migration. | DB schema | FR-020-02 |

## UI / Interaction Mock-ups

### Photo Detail View – Download RAW Button

```
┌─────────────────────────────────────────────┐
│  Photo Title                        [×]     │
│                                             │
│  ┌───────────────────────────────────────┐  │
│  │                                       │  │
│  │          [Photo Display Area]         │  │
│  │         (ORIGINAL/MEDIUM shown)       │  │
│  │                                       │  │
│  └───────────────────────────────────────┘  │
│                                             │
│  Info Panel:                                │
│  ├─ Size: 6000×4000                         │
│  ├─ Type: image/jpeg                        │
│  ├─ ...                                     │
│                                             │
│  Downloads:                                 │
│  ├─ Original (24.5 MB)                      │
│  ├─ Medium (2.1 MB)                         │
│  ├─ Small (450 KB)                          │
│  └─ ◆ RAW (48.2 MB)    [only if enabled]   │
│                                             │
└─────────────────────────────────────────────┘
```

The RAW download option appears only when:
- The photo has a RAW size variant (`has_raw === true`)
- The config `raw_download_enabled === true`

### Settings Page – Raw Download Toggle

```
┌─────────────────────────────────────────────┐
│  Image Processing                           │
│  ├─ ...                                     │
│  ├─ Raw Formats: .nef|.cr2|...              │
│  ├─ Raw Download Enabled: [Toggle OFF]      │
│  ├─ ...                                     │
└─────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-020-01 | Upload a NEF file → RAW variant stored, JPEG ORIGINAL created, thumbnails generated from ORIGINAL. |
| S-020-02 | Upload a HEIC file → HEIC stored as RAW, JPEG ORIGINAL created, thumbnails generated normally. |
| S-020-03 | Upload a PSD file → PSD stored as RAW, JPEG ORIGINAL created (flattened composite). |
| S-020-04 | Upload a standard JPEG → No RAW variant created, normal upload flow unchanged. |
| S-020-05 | Upload a supported video → No RAW variant, video flow unchanged. |
| S-020-06 | Download RAW when `raw_download_enabled = true` → File downloaded successfully. |
| S-020-07 | Download RAW when `raw_download_enabled = false` → 403 Forbidden. |
| S-020-08 | Download RAW when photo has no RAW variant → 404 Not Found. |
| S-020-09 | API response for photo with RAW → `has_raw: true` in response, no RAW URL in `size_variants`. |
| S-020-10 | API response for photo without RAW → `has_raw: false`, normal response. |
| S-020-11 | Watermark applied to album → RAW variants skipped, all other variants watermarked. |
| S-020-12 | Shop purchase for photo with RAW → Only MEDIUM/MEDIUM2X/ORIGINAL/FULL available (`PurchasableSizeVariantType`), no RAW option. Note: FULL ≠ ORIGINAL — FULL is the photographer's full-resolution export, ORIGINAL is the largest uploaded size. |
| S-020-13 | Existing photos (pre-migration) → `type` values shifted by +1, no RAW variants exist, everything works. |
| S-020-14 | Upload RAW file with Imagick unavailable or unsupported delegate → Graceful fallback: file stored as RAW size variant with no ORIGINAL/downstream variants (same as legacy "accepted raw" behavior). Warning logged. |
| S-020-15 | Upload RAW file → Raw file is NOT watermarked even when watermarking is enabled globally. |
| S-020-16 | Bulk download (ZIP) with RAW download enabled → ZIP includes RAW files alongside other variants when `RAW` variant requested. |
| S-020-17 | Delete photo with RAW → Both RAW file and all other size variants deleted from storage. |
| S-020-18 | Frontend photo detail → "Download RAW" button visible only when `has_raw && raw_download_enabled`. |
| S-020-19 | Diagnostics checks → All checks pass with new `type` integer values (ORIGINAL = 1, etc.). |
| S-020-20 | Statistics/Spaces → RAW file sizes included in storage statistics. |
| S-020-21 | Upload a file matching `raw_formats` config (e.g., `.tif`) that is NOT in the auto-convert list → stored as RAW size variant (not ORIGINAL), no conversion attempted. |
| S-020-22 | Upload a PDF file → stored as ORIGINAL (not RAW), since PDF can be displayed. No conversion. |
| S-020-23 | Existing DB has photos with raw-format extensions stored as ORIGINAL → after FR-020-16 migration, those rows become `type = RAW` (except PDF which stays ORIGINAL). |
| S-020-24 | Upload HEIC file with Imagick unavailable → HEIC stored as RAW-only (no JPEG ORIGINAL generated). Behavior change from current hard failure — now graceful. |

## Test Strategy

- **Core (Unit):** Test `SizeVariantType` enum values, `SizeVariants` model `getRaw()`/`add()`/`getSizeVariant()` with RAW, `DownloadVariantType::RAW` mapping.
- **Application (Feature):** Test upload pipeline with RAW files (NEF, HEIC, PSD test fixtures), verify RAW + ORIGINAL created. Test watermark exclusion. Test download gating by config.
- **REST (API):** Test API response includes `has_raw` flag, excludes RAW from `size_variants`. Test download endpoint for RAW with config enabled/disabled.
- **Migration:** Test that `type` column shift produces correct values on all three DB engines.
- **UI (Frontend):** Verify "Download RAW" button visibility logic, settings toggle for `raw_download_enabled`.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-020-01 | `SizeVariantType::RAW = 0` — new enum case for raw files | Enum, Models, Image pipeline |
| DO-020-02 | `DownloadVariantType::RAW` — new download variant case | Enum, Archive/Download |
| DO-020-03 | `SizeVariants::$raw` property and `getRaw()` method | Models/Extensions |
| DO-020-04 | Config key `raw_download_enabled` (boolean, default false) | Config system |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-020-01 | Existing `GET /Photo/{id}/download` | Extended to accept `RAW` variant type | Gated by `raw_download_enabled` config |
| API-020-02 | Existing Photo resource response | `has_raw: bool` property on `PhotoResource`, derived from `$photo->size_variants->getRaw() !== null` (DB check, not MIME-based `Photo::isRaw()`) | Does not expose RAW URL. `SizeVariantsResouce` unchanged. |

### Config Keys

| ID | Key | Type | Default | Description |
|----|-----|------|---------|-------------|
| CFG-020-01 | `raw_download_enabled` | boolean | `false` | When true, RAW files can be downloaded by users with download permissions |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-020-01 | RAW download button visible | Photo has RAW variant AND `raw_download_enabled === true` → show "Download RAW" in download section |
| UI-020-02 | RAW download button hidden | No RAW variant OR config disabled → button not rendered |
| UI-020-03 | Settings toggle | Admin toggles `raw_download_enabled` in Image Processing settings |

## Telemetry & Observability

No new telemetry events. Existing upload logging covers RAW processing. Consider adding a log line when RAW conversion is attempted (success/failure).

## Documentation Deliverables

- Update knowledge map with RAW size variant type and conversion pipeline.
- Update image-processing reference doc (`docs/specs/3-reference/image-processing.md`).
- Update roadmap with Feature 020 entry.

## Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-020-01 | `tests/Samples/` | Small sample NEF/CR2/DNG file for upload tests (need to source or generate a minimal RAW file) |
| FX-020-02 | `tests/Samples/` | Small sample PSD file for PSD upload test |
| FX-020-03 | `tests/Samples/` | Existing HEIC samples: `classic-car.heic`, `sewing-threads.heic` — use for refactored HEIC flow test |

## Spec DSL

```yaml
domain_objects:
  - id: DO-020-01
    name: SizeVariantType::RAW
    fields:
      - name: value
        type: integer
        constraints: "= 0"
  - id: DO-020-02
    name: DownloadVariantType::RAW
    fields:
      - name: value
        type: string
        constraints: "'RAW'"
  - id: DO-020-03
    name: SizeVariants::raw
    fields:
      - name: raw
        type: "?SizeVariant"
  - id: DO-020-04
    name: raw_download_enabled
    fields:
      - name: value
        type: boolean
        constraints: "default false"

routes:
  - id: API-020-01
    method: GET
    path: /Photo/{id}/download?variant=RAW

config_keys:
  - id: CFG-020-01
    key: raw_download_enabled
    type: boolean
    default: false

ui_states:
  - id: UI-020-01
    description: RAW download button visible
  - id: UI-020-02
    description: RAW download button hidden
  - id: UI-020-03
    description: Settings raw download toggle
```

## Appendix

### Raw Format Extensions (FR-020-09)

| Extension | Camera / Software | Notes |
|-----------|-------------------|-------|
| `.nef` | Nikon | Nikon Electronic Format |
| `.cr2` | Canon | Canon Raw v2 |
| `.cr3` | Canon | Canon Raw v3 |
| `.arw` | Sony | Alpha Raw |
| `.dng` | Adobe | Digital Negative (universal RAW) |
| `.orf` | Olympus | Olympus Raw |
| `.rw2` | Panasonic | Raw format |
| `.raf` | Fujifilm | Raw format |
| `.pef` | Pentax | Pentax Electronic File |
| `.srw` | Samsung | Samsung Raw |
| `.nrw` | Nikon | Nikon Raw (coolpix) |
| `.psd` | Adobe Photoshop | Photoshop Document |
| `.heic` | Apple / MPEG | High Efficiency Image Coding |
| `.heif` | MPEG | High Efficiency Image File Format |

### Current Upload Pipeline (Before)

```
Source File → ConvertUnsupportedMedia (HEIC→JPEG via HeifToJpeg, deletes HEIC)
           → AssertSupportedMedia
           → MayLoadFileMetadata → FindDuplicate
           → PlacePhoto → CreateOriginalSizeVariant
           → CreateSizeVariants → ApplyWatermark → EncodePlaceholder
```

### Proposed Upload Pipeline (After)

```
Source File → DetectAndStoreRaw (REPLACES ConvertUnsupportedMedia):
               ├─ If extension in RAW/convertible list:
               │    1. Copy file to RAW storage, record path in DTO
               │    2. Convert to JPEG via unified RawToJpeg (REPLACES HeifToJpeg)
               │    3. On success: replace source_file with JPEG
               │    4. On failure: keep file as-is (graceful fallback)
               └─ If not RAW: pass through unchanged
           → AssertSupportedMedia
           → MayLoadFileMetadata → FindDuplicate
           → PlacePhoto → CreateOriginalSizeVariant
           → CreateRawSizeVariant (NEW: creates RAW DB row if raw path set in DTO)
           → CreateSizeVariants → ApplyWatermark → EncodePlaceholder
```

**Removed classes:** `HeifToJpeg`, `ConvertUnsupportedMedia`, `PhotoConverterFactory`, `ConvertableImageType` (HEIC/HEIF detection folded into RAW format list).  
**New classes:** `DetectAndStoreRaw` (Init pipe), `CreateRawSizeVariant` (Standalone pipe), `RawToJpeg` (converter — unified Imagick-based conversion for all RAW+HEIC+PSD formats).

---

*Last updated: 2025-07-17*
