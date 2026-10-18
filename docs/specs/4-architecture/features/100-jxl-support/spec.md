# Feature 100 – JPEG XL Support

| Field | Value |
|-------|-------|
| Status | Ready for implementation |
| Last updated | 2026-10-18 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #100 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Users ask for JPEG XL (JXL) support ([discussion #2718](https://github.com/LycheeOrg/Lychee/discussions/2718)): JXL files are 50–60 % smaller than JPEG at the same quality. Browser support is still partial in October 2026 (Safari native, Chrome and Firefox 152 behind a flag or Labs toggle, see [caniuse](https://caniuse.com/jpegxl)), so generating JXL stays opt-in and comes with a warning.

This feature:

1. **Accepts `.jxl` uploads** as photos, with no setting. The original is stored as is (no conversion). Under `size_variant_format = original`, their small/medium are `.jxl` too (the uploader decides).
2. **Adds `jxl` to `size_variant_format`** (Feature 073) next to `jpeg` and `webp`. This is the only JXL setting: with `jxl`, every generated variant of every upload is JXL-encoded.
3. **Reports missing JXL support** on the diagnostics page.
4. **Ships a JXL-capable ImageMagick in the official Docker image** ([ADR-100-01](../../../6-decisions/ADR-100-01-docker-imagemagick-jxl.md)), because Debian 13's ImageMagick is built without JXL.

Affected modules: `App\Services\Image\FileExtensionService` (MIME type, extension, magic-byte detection), `App\Enum\SizeVariantFormat`, `App\Assets\BaseSizeVariantNamingStrategy`, the image handlers (`app/Image/Handlers`), a new diagnostics check, one config migration, `lang/*/all_settings.php`, the generated TypeScript type `App.Enum.SizeVariantFormat`, and `Dockerfile`. No REST route or frontend component changes.

## Goals

- G1: `.jxl` uploads (upload, server import, URL import, sync) are accepted as photos, and the original is kept byte-for-byte.
- G2: Admins can select `jxl` as `size_variant_format`; JXL variants contain JXL bytes matching the `.jxl` extension.
- G3: The default (`original`) is unchanged, so upgrading changes nothing for existing photo types.
- G4: The `size_variant_format` details text warns (orange triangle, `SA_override_visibility` convention) about browser support and the Imagick + libjxl requirement. This warning is the only mitigation for unsupported browsers.
- G5: Admins see on the diagnostics page whether this system can process JXL.
- G6: The official Docker image (`Dockerfile`) can decode and encode JXL out of the box.

## Non-Goals

- N-100-01: Converting existing size variants. As in Feature 073, operators regenerate with `lychee:generate_thumbs` after deleting the variants.
- N-100-02: Converting JXL originals to another format on import ("the original stays as is").
- N-100-03: JXL placeholders (stay WebP, stored in the DB) and watermark output (stays JPEG).
- N-100-04: A JXL optimizer in `config/image-optimizer.php` (Spatie has no JXL optimizer; files of unknown type are skipped).
- N-100-05: AVIF output or other new formats.
- N-100-06: Per-viewer content negotiation or a WebP fallback set.
- N-100-07: An opt-in setting for JXL uploads.
- N-100-08: JXL in `Dockerfile-legacy` (Debian packages + `php8.5-imagick` from Sury; a source-built ImageMagick would conflict). Follow-up.
- N-100-09: A CI job with a JXL-capable ImageMagick; the Docker smoke test (FR-100-11) covers the encoder.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-100-01 | `image/jxl` ↔ `.jxl` is added to `FileExtensionService` (`SUPPORTED_IMAGE_FILE_EXTENSIONS`, `SUPPORTED_IMAGE_MIME_TYPES`, `MIME_TYPES_TO_FILE_EXTENSIONS`). Always on, no setting. libmagic (Debian 13, `file` 5.46) reports `image/jxl` for both JXL encodings. | A `.jxl` upload becomes a photo whose original keeps `.jxl` and its bytes. | n/a | Without JXL support, see FR-100-06. | None. | Q-100-07 |
| FR-100-02 | JXL content is detected by magic bytes, because `exif_imagetype()` has no JXL type: bare codestream `FF 0A`, or ISO-BMFF container `00 00 00 0C 4A 58 4C 20 0D 0A 87 0A`. The check replaces `hasSupportedExifImageType()` for `.jxl` files only. | A real JXL passes. | A `.jxl` file with other content is rejected (`MediaFileUnsupportedException`). | n/a | None. | `exif_imagetype` limitation |
| FR-100-03 | `SizeVariantFormat` gains `JXL = 'jxl'`; `size_variant_format` `type_range` becomes `original\|jpeg\|webp\|jxl` (default unchanged: `original`). With `jxl`, every generated variant (`thumb`, `thumb2x`, `small`, `small2x`, `medium`, `medium2x`) of photos and non-photo media is written as `.jxl` with JXL content, exactly like `webp`. The `details` text gets the G4 warning. | New uploads and regenerations produce `.jxl` variants. | Values outside the range are rejected by the generic enum validation. | See FR-100-06. | None. | Q-100-05, Q-100-07 |
| FR-100-04 | Under `size_variant_format = original`, the FR-073-04 rule applies literally to JXL originals: `small`/`small2x`/`medium`/`medium2x` keep `.jxl` and are JXL-encoded; thumbs stay JPEG. `jpeg`/`webp`/`jxl` force their format as usual. | A JXL upload gets `.jxl` small/medium. | n/a | See FR-100-06. | None. | Q-100-01 |
| FR-100-05 | `compression_quality` (`int:0:100`, Feature 073) applies to JXL through the ImageMagick JXL coder options (the coder ignores the image compression quality): `1–100` sets `jxl:quality`; `0` sets `jxl:distance` to `0`, which is lossless. | `0` produces lossless JXL (decoded pixels equal the source); a lower quality produces a smaller file. | n/a | n/a | None. | FR-073-06 parity |
| FR-100-06 | Decoding JXL originals and encoding JXL variants require Imagick with the JXL delegate (`\Imagick::queryFormats('JXL')` non-empty); GD has no JXL support. Without it, loading a `.jxl` original or saving a `.jxl` target throws `MediaFileOperationException('JPEG XL requires Imagick with JXL support')`. `GdHandler::save()` throws for `.jxl` targets instead of falling back to the source type (never non-JXL bytes in a `.jxl` file). No silent fallback. | A JXL is decoded and `.jxl` variants are written. | n/a | Clear exception as described. | Diagnostics (FR-100-07). | Q-100-02 |
| FR-100-07 | New diagnostics check `ImagickJxlCheck` (registered next to `ImagickPdfCheck`). When the system cannot process JXL (Imagick disabled/missing, or no JXL delegate) it adds one line: an **info** line "JPEG XL is not supported by this system: `.jxl` uploads will fail" in general, an **error** when `size_variant_format = jxl` (every upload would fail). Nothing is reported when JXL is supported. | Diagnostics page shows the line. | Skipped when the `configs` table does not exist (same guard as `ImagickPdfCheck`). | n/a | Diagnostics info/error. | Q-100-07 |
| FR-100-08 | Metadata of JXL originals is read by the existing reader chain (`Extractor`): Exiftool when available, otherwise the Imagick reader. Imagick is guaranteed by FR-100-06, so width/height are always populated. | Width, height and EXIF (when present) are stored. | n/a | Existing `MediaFileOperationException` path. | None. | `app/Metadata/Extractor.php` |
| FR-100-09 | Switching `size_variant_format` away from `jxl` only affects newly generated variants; existing JXL originals and `.jxl` variants stay listed, served and downloadable. | Existing data unaffected. | n/a | n/a | None. | N-100-01 |
| FR-100-10 | No runtime fallback for browsers that cannot decode JXL: no second variant set, no client banner. | Supporting browsers display JXL. | n/a | Unsupporting browsers show broken images (documented in G4). | None. | Q-100-03 |
| FR-100-11 | The `Dockerfile` production image ships ImageMagick 7 built against libjxl, and the `imagick` PHP extension is linked against it, so `Imagick::queryFormats('JXL')` returns `['JXL']`. ImageMagick and imagick live in `/opt/imagemagick` and keep Debian's `policy.xml` (plus the PDF rule); `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS` includes `/opt/imagemagick/etc/ImageMagick-7/policy.xml`. The production stage ends with a build-time check that Imagick round-trips a JXL image. | The image writes and reads back JXL. | n/a | The Docker build fails if the check fails. | None. | Q-100-06, ADR-100-01 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-100-01 | No new Composer or npm dependency. The Docker image gains libjxl and a source-built ImageMagick (pinned version and checksum). | AGENTS.md dependency policy | `composer.json`/`package.json` unchanged; `Dockerfile` pins the ImageMagick tarball by version and SHA-256 | ImageMagick 7.1.x, libjxl (Debian 13 `libjxl-dev`) | ADR-100-01 |
| NFR-100-02 | No frontend component changes; `size_variant_format` renders through the existing `ConfigGroup.vue` widget. The only TS change is the regenerated `App.Enum.SizeVariantFormat` union. | Minimal diff | `git diff --stat resources/js` limited to `lychee.d.ts` | typescript-transformer | NFR-073-02 |
| NFR-100-03 | JXL detection, JXL support probing and the format/extension mapping live in small pure helpers (magic-byte helper in `FileExtensionService`, a single `supportsJxl()` probe reused by handlers and diagnostics, `generatedExtension()` match arm). | Straight-line increments | Code review | — | AGENTS.md |
| NFR-100-04 | Tests needing a JXL delegate are skipped (not failed) when the local/CI ImageMagick lacks it; all other branches run everywhere. | CI portability (Ubuntu runners have no JXL) | Test output | — | Q-100-06 |
| NFR-100-05 | The Docker image size grows by at most ~40 MB and the multi-arch build stays within the existing workflow timeout. | Operability | `docker image ls` before/after; workflow duration | — | ADR-100-01 |

## UI / Interaction Mock-ups

Settings → Image Processing, expert view (existing generic widget, no new component):

```
┌─ Image Processing ──────────────────────────────────────────────────────┐
│ ...                                                                      │
│ Quality of generated size variants                                       │
│   [  80  ]  (0–100)                                                      │
│ Format of generated size variants                                        │
│   ( original | jpeg | webp | jxl )                                       │
│   Original: thumbs are JPEG, small and medium keep the uploaded format.  │
│   ⚠ JXL: browsers without JPEG XL support show broken images.            │
│     Requires Imagick with JXL support (see Diagnostics).                 │
│ ...                                                                      │
└──────────────────────────────────────────────────────────────────────────┘
```

Diagnostics page, system without JXL support:

```
┌─ Diagnostics ───────────────────────────────────────────────────────────┐
│ ...                                                                      │
│ Info:  JPEG XL is not supported by this system: .jxl uploads will fail.  │
│ Error: size_variant_format is jxl but JPEG XL is not supported by this   │
│        system: every upload will fail.            (only when jxl is set) │
│ ...                                                                      │
└──────────────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-100-01 | JXL supported, `.jxl` upload, `size_variant_format = webp`: original stored as `.jxl` (bytes unchanged), variants `.webp`. |
| S-100-02 | JXL supported, `.jxl` upload, `size_variant_format = original`: thumbs `.jpeg`, small/medium (and 2x) `.jxl` with JXL bytes. |
| S-100-03 | `size_variant_format = jxl`, JPEG upload: original `.jpg`, every generated variant `.jxl` with JXL signature. |
| S-100-04 | `size_variant_format = jxl`, `compression_quality = 0`: lossless JXL. |
| S-100-05 | `.jxl` extension with JPEG content: rejected (magic-byte mismatch). |
| S-100-06 | Bare-codestream and container JXL headers both pass detection. |
| S-100-07 | JXL unsupported, `.jxl` upload: clear `MediaFileOperationException`. |
| S-100-08 | GD asked to save a `.jxl` target: throws; never writes non-JXL bytes into a `.jxl` file. |
| S-100-09 | Diagnostics, JXL unsupported, format ≠ `jxl`: one info line. |
| S-100-10 | Diagnostics, JXL unsupported, format = `jxl`: one error line. |
| S-100-11 | Diagnostics, JXL supported: no line. |
| S-100-12 | `size_variant_format` set to an unknown value: rejected; `jxl` accepted. |
| S-100-13 | Docker image: `Imagick::queryFormats('JXL')` non-empty, PDF thumbnails still generated. |

## Test Strategy

- **Unit:** `FileExtensionService` JXL extension/MIME and magic bytes (S-100-05, S-100-06) using synthetic headers; migration `type_range` (S-100-12); `ImagickJxlCheckTest` with the support probe mocked (S-100-09..11); GD refusal (S-100-08); naming strategy `jxl` arm.
- **Image processing (Imagick, skipped without JXL delegate):** cases in `tests/ImageProcessing/Image/Handlers/BaseImageHandler.php` for S-100-01..04 and FR-100-05, asserting extension, magic bytes, pixel equality (lossless) and file size versus quality; S-100-07 with the probe reporting no support.
- **Docker:** build-time JXL round-trip check in `Dockerfile` (S-100-13), plus the JXL image-processing cases and a PDF upload run inside the built image, recorded in the plan.
- **Fixture:** FX-100-01, generated inside the new Docker image (`magick tests/Samples/night.jpg -resize 800x -quality 80 jxl.jxl`), since no local encoder exists.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-100-01 | `App\Enum\SizeVariantFormat::JXL` | app/Enum, app/Assets |

### API Routes / Services
| ID | Description |
|----|-------------|
| SV-100-01 | JXL support probe (`supportsJxl(): bool`, Imagick enabled and `queryFormats('JXL')` non-empty), shared by `ImagickHandler`, `GdHandler` and `ImagickJxlCheck`. |

### CLI Commands / Flags
None. `lychee:generate_thumbs` and `lychee:sync` honour the setting through the shared factory/naming strategy and `FileExtensionService`.

### Telemetry Events
None.

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-100-01 | `tests/Samples/jxl.jxl` | Real JXL source (new, small) |
| FX-100-02 | `tests/Samples/night.jpg` | JPEG source (existing) |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-100-01 | `jxl` format choice | Admin picks `jxl`; later variants are `.jxl`. |
| UI-100-02 | Diagnostics JXL line | Shown when the system cannot process JXL (info, or error with `jxl`). |

## Telemetry & Observability

No new events. Diagnostics report missing JXL support (FR-100-07); encode/decode failures surface through `MediaFileOperationException`.

## Documentation Deliverables

- `lang/*/all_settings.php`: updated `size_variant_format` details, translated in every locale.
- `docs/specs/3-reference/image-processing.md`: JXL section (requirements, setting, browser caveat, diagnostics).
- Knowledge map entry next to Feature 073; roadmap row #100; ADR-100-01.

## Spec DSL

```
domain_objects:
  - id: DO-100-01
    name: SizeVariantFormat
    values: [original, jpeg, webp, jxl]
configs:
  - key: size_variant_format
    type_range: "original|jpeg|webp|jxl"
    default: original
mime_types:
  - mime: image/jxl
    extension: .jxl
    magic: ["FF 0A", "00 00 00 0C 4A 58 4C 20 0D 0A 87 0A"]
diagnostics:
  - class: ImagickJxlCheck
    unsupported_and_format_jxl: error
    unsupported: info
ui_states:
  - id: UI-100-01
  - id: UI-100-02
```
