# Feature 071 – Configurable Size-Variant Format and Quality

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-09-26 |
| Owners | NikitaTH |
| Linked plan | `docs/specs/4-architecture/features/071-size-variant-format/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/071-size-variant-format/tasks.md` |
| Roadmap entry | #071 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below, and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

## Overview

Generated size variants currently use a fixed file format. `thumb`/`thumb2x` are always JPEG. `small`/`small2x`/`medium`/`medium2x` inherit the original's extension. Videos and other non-photo media always get JPEG. The only quality knob is `compression_quality`, which is typed `positive` (1–∞, with no upper bound) and cannot express lossless encoding. Operators who want smaller gallery payloads cannot choose WebP, even though both image handlers (GD and Imagick) can already write it and placeholders already use it.

This feature adds an admin setting that selects the output format of generated size variants (`original` | `jpeg` | `webp`). It also re-types `compression_quality` to a bounded `0–100` range, where `0` means lossless. Affected modules: the size-variant naming strategy (`app/Assets`), the image handlers (`app/Image/Handlers`), and two config migrations. There are no REST, CLI, or frontend code changes: the settings page renders both keys through its existing generic widgets.

## Goals

- G1: Admins can switch generated size variants to WebP (or force JPEG) from **Settings → Image Processing**, with no file or DB edits.
- G2: Admins can request lossless encoding by setting `compression_quality` to `0`.
- G3: The default configuration produces byte-identical file naming to today (`original`), so upgrading changes nothing until an admin opts in.

## Non-Goals

- N-071-01: Converting already-generated size variants. Existing rows keep their paths and remain served as-is. Operators who want existing thumbnails re-encoded use the existing delete-and-regenerate procedure (`lychee:generate_thumbs`, `lychee:video_data`).
- N-071-02: AVIF or other formats. They are not requested, and GD cannot write AVIF on every build.
- N-071-03: Changing the placeholder format (already WebP, stored inline in the DB) or the watermark file format (always JPEG, `WatermarkGroupedWithRandomSuffixNamingStrategy`).
- N-071-04: *(withdrawn — see FR-071-09 and Q-071-06).*
- N-071-05: Changing the original's format or the RAW→JPEG conversion (`RawToJpeg`, fixed quality 92).
- N-071-06: Per-variant formats or quality. One format and one quality apply to all generated variants. This only partially addresses [LycheeOrg/Lychee#1888](https://github.com/LycheeOrg/Lychee/issues/1888): it covers WebP quality, while per-size quality is a follow-up (Q-071-05, Option A).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-071-01 | New config `size_variant_format`, category `Image Processing`, `type_range` `original\|jpeg\|webp`, default `original`, non-expert. | Setting appears in Settings and persists. | Any value outside the range is rejected by the generic `Configs::sanity()` enum branch with the standard error message. | n/a | None (config change only). | Owner request 2026-09-26; Q-071-03 |
| FR-071-02 | When `size_variant_format` is `webp`, every **generated** size variant (`thumb`, `thumb2x`, `small`, `small2x`, `medium`, `medium2x`) is written with the `.webp` extension and WebP content, for photos and non-photo media (video frames, PDF renders) alike. | New uploads and regenerations produce `.webp` files. | n/a | If the handler cannot write WebP, the existing `MediaFileOperationException('Failed to save image')` path applies. `GDSupportCheck` already reports GD builds without WebP. | None. | Owner request |
| FR-071-03 | When `size_variant_format` is `jpeg`, every generated size variant is written with the `.jpeg` extension, including `small`/`medium` of non-JPEG originals. | `.jpeg` paths are produced. | n/a | As FR-071-02. | None. | Owner request |
| FR-071-04 | When `size_variant_format` is `original`, extension selection is exactly the pre-feature behaviour: `thumb`/`thumb2x` and all non-photo variants use `.jpeg`, while `small`/`medium` families inherit the original's extension. | No behaviour change. | n/a | n/a | None. | G3; Q-071-03 |
| FR-071-05 | `ORIGINAL`, `RAW`, and `PLACEHOLDER` extensions are never affected by `size_variant_format`. | Originals keep their extension; placeholders stay `.webp`. | n/a | n/a | None. | N-071-03, N-071-05 |
| FR-071-06 | `compression_quality` is re-typed from `positive` to `int:0:100`. `1–100` is the lossy quality. `0` means lossless. | Settings renders a bounded number field (existing `int:` branch in `ConfigGroup.vue`). | Values outside `0–100` or non-digits are rejected by the existing bounded-int branch of `Configs::sanity()`. | n/a | None. | Owner request ("0 or lossless as lossless"); Q-071-01 |
| FR-071-07 | With `compression_quality = 0`, WebP output is encoded losslessly: `IMG_WEBP_LOSSLESS` on GD, `webp:lossless=true` on Imagick. | WebP files carry a `VP8L` chunk. | n/a | As FR-071-02. | None. | Owner request |
| FR-071-08 | With `compression_quality = 0`, formats without a lossless mode (JPEG, and any other re-encode such as an auto-rotated original) are encoded at the maximum quality, `100`. | JPEG output is valid and maximum quality. | n/a | n/a | None. | Q-071-02 |
| FR-071-09 | GD's `save()` encodes by the **target** file extension for every supported format (`.jpg`/`.jpeg`/`.png`/`.gif`/`.webp`) and falls back to the source type only for other extensions, so a file's content always matches its extension. Imagick already writes by extension. | A `.jpeg` thumb of a PNG original contains JPEG bytes. Transparency is lost in JPEG targets, as with Imagick. | n/a | Encoder failures (`Safe\Exceptions\ImageException`) are wrapped in `MediaFileOperationException('Failed to save image')`. | None. | Q-071-06 (supersedes Q-071-04) |
| FR-071-10 | The existing up-migration value of `compression_quality` is preserved. The down-migration restores `positive` and rewrites a stored `0` to `100`, so the old validator accepts it. | Round-trip migration is safe. | n/a | n/a | None. | Constitution: reversible migrations |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-071-01 | No new dependencies. | AGENTS.md dependency policy | `composer.json`/`package.json` unchanged | GD `imagewebp`, Imagick WebP delegate (both already required) | AGENTS.md |
| NFR-071-02 | No frontend code changes. Both keys render through existing generic widgets (`SliderField` for `a\|b\|c`, `NumberField` for `int:min:max`). | Minimal diff | `git diff --stat resources/js` is empty | `ConfigGroup.vue` | Ponytail / AGENTS.md straight-line increments |
| NFR-071-03 | Extension and quality decisions live in small pure helpers (`SizeVariantFormat::extension()`, `BaseImageHandler::resolveQuality()`/`isLossless()`), keeping each handler change nearly straight-line. | AGENTS.md "Straight-line increments" | Code review | — | AGENTS.md |
| NFR-071-04 | Both image handlers are covered by the same tests (`BaseImageHandler` suite runs under GD and Imagick). | Handler parity | `PhotosAddHandlerGDTest`, `PhotosAddHandlerImagickTest` | `RequiresImageHandler` trait | Test strategy |

## UI / Interaction Mock-ups

Settings → Image Processing (existing generic widgets, no new components):

```
┌─ Image Processing ─────────────────────────────────────────────┐
│ ...                                                             │
│ Quality of generated size variants                              │
│   [  80  ]  (0–100)                                             │
│ File format of generated size variants                          │
│   ( original | jpeg | webp )                                    │
│   original: thumbs JPEG, small/medium keep the original format  │
│ ...                                                             │
└─────────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-071-01 | Default `original`, JPEG upload: all generated variants keep today's extensions (`thumb` `.jpeg`, `small` inherits `.jpg`). |
| S-071-02 | Default `original`, PNG upload: `thumb` `.jpeg` containing JPEG, `small`/`medium` `.png` containing PNG (unchanged naming; on GD the thumb content now matches its extension). |
| S-071-03 | `webp` + quality `80`, JPEG upload: every generated variant ends `.webp` and contains lossy WebP (`VP8 ` chunk); original keeps `.jpg`. |
| S-071-04 | `webp` + quality `0`: generated variants contain lossless WebP (`VP8L` chunk). |
| S-071-05 | `jpeg` + PNG upload: `small`/`medium` end `.jpeg` and contain JPEG. |
| S-071-06 | `jpeg` + quality `0`: upload succeeds and JPEG is written (quality clamps to 100). |
| S-071-07 | `size_variant_format` set to an unknown value: rejected by config validation. |
| S-071-08 | `compression_quality` set to `101` or `-1`: rejected by config validation. |

## Test Strategy

- **Image processing (GD + Imagick):** new cases in `tests/ImageProcessing/Image/Handlers/BaseImageHandler.php` for S-071-01..06, asserting the URL extension and the file's magic bytes (`RIFF….WEBPVP8 `/`VP8L`, JPEG `FF D8`).
- **Unit:** `tests/Unit/Enum/SizeVariantFormatTest.php` for the enum→extension mapping, and a `Configs::sanity()` case set for S-071-07/08 in `tests/Unit/Models/SizeVariantFormatConfigSanityTest.php`.
- **REST/CLI/UI:** unchanged surfaces, so no new tests. Settings rendering reuses existing generic widgets.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-071-01 | `App\Enum\SizeVariantFormat` (`original`, `jpeg`, `webp`) with `extension(): ?string` (`null` for `original`) | app/Enum, app/Assets |

### API Routes / Services
None.

### CLI Commands / Flags
None. `lychee:generate_thumbs` and `lychee:video_data` honour the new setting automatically because they go through the same factory and naming strategy.

### Telemetry Events
None.

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-071-01 | `tests/Samples/night.jpg` | JPEG source (existing) |
| FX-071-02 | `tests/Samples/png.png` | PNG source (existing) |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-071-01 | Format selector | Admin picks `webp`; later uploads produce `.webp` variants. |

## Telemetry & Observability

No new events. Save failures surface through the existing `MediaFileOperationException` path.

## Documentation Deliverables

- `lang/*/all_settings.php`: `size_variant_format` documentation/details in every locale (English text, Russian translated), following the convention of the latest config additions. The `compression_quality` text is updated in `en` only.
- Roadmap row #071.
- Knowledge map entry under image processing.
- `docs/specs/3-reference/image-processing.md` gets a short section on output format and quality.
- Open questions Q-071-01..04 are logged and resolved.

## Spec DSL

```
domain_objects:
  - id: DO-071-01
    name: SizeVariantFormat
    values: [original, jpeg, webp]
configs:
  - key: size_variant_format
    type_range: "original|jpeg|webp"
    default: original
    category: Image Processing
  - key: compression_quality
    type_range: "int:0:100"   # was: positive
    semantics: "0 = lossless (WebP) / max quality (others)"
ui_states:
  - id: UI-071-01
    description: Format selector in Settings → Image Processing
```
