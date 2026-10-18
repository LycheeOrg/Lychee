# Feature Plan 100 – JPEG XL Support

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Implemented  
_Last updated:_ 2026-10-18

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria

Lychee accepts JPEG XL originals and can generate JXL size variants, with an honest diagnostics line where the system cannot. Success means:

- S-100-05, 06, 08..12 are green everywhere (unit level, no JXL delegate needed).
- S-100-01..04 and S-100-07 are green on a JXL-capable Imagick (the new Docker image) and skip elsewhere (NFR-100-04).
- The Docker image passes the JXL smoke test and still renders PDF thumbnails (S-100-13).
- The default configuration behaves exactly as before for non-JXL uploads.

## Scope Alignment

- **In scope:** `FileExtensionService` (MIME, extension, magic bytes), `SizeVariantFormat::JXL`, `size_variant_format` migration, naming strategy, Imagick JXL save/quality, GD refusal, the `supportsJxl()` probe, `ImagickJxlCheck`, translations, `Dockerfile` + workflow smoke test, fixture, docs.
- **Out of scope:** N-100-01..09 (conversion of existing files, converting originals, placeholders/watermarks, optimizer, AVIF, browser fallback, upload switch, `Dockerfile-legacy`, CI JXL job).

## Dependencies & Interfaces

- `App\Services\Image\FileExtensionService` (`isSupportedImage()`, `hasSupportedExifImageType()`)
- `App\Assets\BaseSizeVariantNamingStrategy::generatedExtension()` / `getExtensionForSizeVariant()`
- `App\Image\Handlers\ImagickHandler::load()`/`save()`, `GdHandler::save()` (`OUTPUT_TYPES`), `BaseImageHandler::resolveQuality()`/`isLossless()`
- `App\Repositories\ConfigManager::hasImagick()` / `getValueAsEnum()`
- `App\Actions\Diagnostics\Errors` pipe list; `ImagickPdfCheck` (pattern + policy locations)
- `App\Models\Extensions\BaseConfigMigration`
- `Dockerfile`
- **Approved dependency change (Q-100-06, 2026-10-18, owner):** source-built ImageMagick 7.1.x + Debian `libjxl-dev` in the Docker image (ADR-100-01). No Composer/npm change.

## Assumptions & Risks

- **Assumptions:**
  - libmagic reports `image/jxl` (verified on Debian 13 with synthetic codestream and container headers), so `mime_content_type()` needs no special case.
  - ImageMagick's JXL coder reads `jxl:distance`/`jxl:quality` (distance 0 is lossless).
  - The Imagick metadata reader returns width/height for JXL.
- **Risks / Mitigations:**
  - *Local dev box and CI have no JXL delegate* → JXL encode/decode tests skip (NFR-100-04); the encoder path is verified inside the new Docker image (I6) and recorded below.
  - *Source build of ImageMagick misses a delegate the Debian package had* (HEIC, TIFF, RAW via libraw, Ghostscript) → I5 compares `magick -list format` before/after and the drift gate records the diff; HEIC/PDF sample uploads are part of the manual check.
  - *Multi-arch build time* → builder stage cached; measure in I5 (NFR-100-05). If the limit is exceeded, raise a new open question before merging.
  - *`imagick` PECL build against a custom prefix* → set `PKG_CONFIG_PATH`; smoke test catches a mismatch.
  - *Fixture creation needs a JXL encoder* → generated with the new image (I6), so I6 depends on I5.

## Implementation Drift Gate

| Requirement | Implementation | Test |
|-------------|----------------|------|
| FR-100-01/02 | `FileExtensionService` (`.jxl`, `image/jxl`, `JXL_SIGNATURES`, `isJxl()`) | `FileExtensionServiceJxlTest` |
| FR-100-03/04 | `SizeVariantFormat::JXL`, `2026_10_18_000001_add_jxl_size_variant_format.php`, `BaseSizeVariantNamingStrategy::generatedExtension()` | `JxlNamingTest`, `SizeVariantFormatConfigSanityTest`, `BaseImageHandler::testJxlUpload*`, `testSizeVariantFormatJxl` |
| FR-100-05 | `ImagickHandler::save()` (`jxl:distance` / `jxl:quality`) | `BaseImageHandler::testSizeVariantFormatJxlLossless`, `testSizeVariantFormatJxlQuality` |
| FR-100-06 | `ConfigManager::hasJxl()`, `BaseImageHandler::assertCanProcess()`/`supportsJxl()`, `ImagickHandler::supportsJxl()`, `GdHandler` load/save | `GdHandlerJxlTest`, `ImagickHandlerJxlTest` |
| FR-100-07 | `ImagickJxlCheck`, registered in `Errors` | `ImagickJxlCheckTest` |
| FR-100-08/09 | existing `Extractor` reader chain; naming applies to new variants only | `testJxlUploadWithOriginalFormat` (width/height) |
| FR-100-10 | documentation only (setting details, `3-reference/image-processing.md`) | — |
| FR-100-11 | `Dockerfile` `imagemagick` stage + production stage, `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS` | build-time round-trip `RUN`; Imagick suite inside the image |

**Drift gate report (2026-10-18):**
- Every FR maps to code and tests above; no undocumented behaviour. Tests were red before each implementation (T-100-01, 03, 05, 07); `testSizeVariantFormatJxlQuality` fails without the `jxl:quality` option.
- Local run (no JXL delegate): 134 passed, 15 skipped (JXL cases skip, NFR-100-04). Inside the new image: `PhotosAddHandlerImagickTest` + JXL unit tests 44 passed; `testGoogleMotionPhotoUpload` and `testTrickyVideoUpload` fail identically in the image built from `master`, so they are environmental (video tooling), not regressions. Locally, the three PDF media-box cases fail with or without this change; they pass inside the image.
- NFR-100-01: `composer.json`/`package.json` unchanged; ImageMagick 7.1.2-32 and imagick 3.8.1 pinned by SHA-256.
- NFR-100-02: `resources/js` diff is the one-line `SizeVariantFormat` union in `lychee.d.ts` (`php artisan typescript:transform` reformats the whole file, so the line was applied by hand).
- NFR-100-05: image 1.989 GB (`master`) → 2.024 GB (+34 MB).
- Imagick formats: 271 → 280. Gained: JXL, ASE/ASEPRITE, C2PA, ICN, SCREENSHOT, SF3, WBINFO and single-channel aliases. Lost: DJVU, DOT/GV (Graphviz), EXR (OpenEXR), WMF/WMZ (libwmf), X/XWD (X11). JPEG, PNG, GIF, WebP, HEIC/HEIF, TIFF, AVIF (read), RAW (libraw) and PDF are kept.
- `vp check --fix` rewrites unrelated files on `master` (two `EmbedCodeDialog.vue` become unparseable); those changes were discarded and `vp check` (no fix) reports 0 errors.

## Increment Map

1. **I1 – Format detection (≤60 min)** — FR-100-01, FR-100-02; S-100-05, S-100-06
   - _Steps:_ tests first (`FileExtensionServiceJxlTest`: synthetic codestream + container headers accepted, `.jxl` with JPEG bytes rejected, MIME↔extension map), then the constants and a small `isJxl(string $path): bool` magic-byte helper used by `isSupportedImage()` for `.jxl`.
   - _Commands:_ `php artisan test --filter=FileExtensionServiceJxlTest`, `make phpstan`
   - _Exit:_ green; existing `FileExtensionService` tests unchanged.
2. **I2 – Config + enum + naming (≤45 min)** — FR-100-03, FR-100-04; S-100-12
   - _Steps:_ tests first (migration `type_range`, enum accepts `jxl`; naming strategy returns `.jxl` for all generated variants with `jxl`, and `.jxl` small/medium for a JXL original under `original`), then `SizeVariantFormat::JXL`, migration `…_add_jxl_size_variant_format.php` (reversible: down rewrites `jxl` to `original`), `generatedExtension()` arm, `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter="SizeVariantFormat|JxlNaming"`, `make phpstan`
3. **I3 – Support probe + handlers (≤75 min)** — FR-100-05, FR-100-06; S-100-04, S-100-07, S-100-08
   - _Steps:_ tests first (GD `.jxl` target throws; Imagick load/save of `.jxl` throws the clear exception when the probe says unsupported), then `supportsJxl()` (single helper, NFR-100-03), the guard in `ImagickHandler::load()`/`save()`, `jxl:lossless`/quality handling via `resolveQuality()`, and `GdHandler::save()` refusal of `.jxl`.
   - _Commands:_ `php artisan test --filter="GdHandlerJxlTest|ImagickHandlerJxlTest"`, `make phpstan`
4. **I4 – Diagnostics (≤45 min)** — FR-100-07; S-100-09..11
   - _Steps:_ tests first (`ImagickJxlCheckTest`, probe mocked: info / error with `jxl` / nothing), then `ImagickJxlCheck` registered after `ImagickPdfCheck` in `Errors`.
   - _Commands:_ `php artisan test --filter=ImagickJxlCheckTest`, `make phpstan`
5. **I5 – Docker image (≤90 min, may run longer for build time)** — FR-100-11, NFR-100-01, NFR-100-05; S-100-13
   - _Steps:_ `imagemagick` stage compiling the pinned ImageMagick tarball and imagick against `libjxl-dev` into `/opt/imagemagick` with Debian's `policy.xml`; production stage copies the prefix, installs runtime libraries, enables imagick; policy path in `Dockerfile` and `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS`; build-time JXL round-trip check; record the format list and image size.
   - _Commands:_ `docker build -t lychee-jxl .`, `docker run --rm lychee-jxl php -r "var_dump(Imagick::queryFormats('JXL'));"`, `php artisan test --filter=ImagickPdfCheck`
6. **I6 – Fixture + image-processing cases (≤60 min)** — FR-100-01, 03, 04, 05, 08; S-100-01..04
   - _Steps:_ generate FX-100-01 inside the I5 image; image-processing cases in `BaseImageHandler` (skipped without delegate) asserting extensions and magic bytes; run them inside the image.
   - _Commands:_ `php artisan test --filter=PhotosAddHandlerImagickTest` (inside the image), `php artisan test --filter=PhotosAddHandler` (locally: skips)
7. **I7 – Translations + docs + quality gate (≤45 min)** — G4, Documentation Deliverables
   - _Steps:_ `size_variant_format` details in all `lang/*/all_settings.php`; `3-reference/image-processing.md`; knowledge map; roadmap; `vendor/bin/php-cs-fixer fix`, `vp check --fix`, `vp run typecheck`, scoped tests, `make phpstan`; drift gate.
   - _Commands:_ `php artisan test --filter="LangTest|ConfigsTest"`, `make phpstan`

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-100-01 | I6 / T-100-11 | Imagick + delegate only |
| S-100-02 | I2 / T-100-03, I6 / T-100-11 | naming unit-tested everywhere |
| S-100-03 | I2 / T-100-03, I6 / T-100-11 | |
| S-100-04 | I3 / T-100-06, I6 / T-100-11 | |
| S-100-05 | I1 / T-100-01 | |
| S-100-06 | I1 / T-100-01 | synthetic headers |
| S-100-07 | I3 / T-100-05 | probe mocked |
| S-100-08 | I3 / T-100-05 | |
| S-100-09..11 | I4 / T-100-07 | |
| S-100-12 | I2 / T-100-03 | |
| S-100-13 | I5 / T-100-09 | workflow smoke + manual PDF check |

## Analysis Gate

_2026-10-18, agent._

1. Specification completeness — **pass**: goals, FR-100-01..11, NFR-100-01..05, mock-ups (settings, diagnostics); resolved questions folded into normative sections.
2. Open questions — **pass**: Q-100-01..07 resolved; ADR-100-01 records the Docker decision.
3. Plan alignment — **pass**: links to spec/tasks; dependencies match FR/NFR wording.
4. Tasks coverage — **pass**: every FR maps to a task (FR-100-01/02 → T-100-01/02, FR-100-03/04 → T-100-03/04, FR-100-05/06 → T-100-05/06, FR-100-07 → T-100-07/08, FR-100-08 → T-100-11, FR-100-09 → T-100-03 (no-op by construction, regression in T-100-11), FR-100-10 → T-100-12 (docs only), FR-100-11 → T-100-09); tests precede code; increments ≤90 min except I5's build wall time.
5. Working agreements — **pass**: dependency change approved and recorded; helpers keep control flow flat; ADRs for Feature 073 area reviewed (none exist; spec 073 read).
6. Tooling readiness — **pass**: commands listed per increment.

## Exit Criteria

- All tasks `[x]`; quality gate green (`php-cs-fixer`, scoped tests, `make phpstan`, `vp check --fix`, `vp run typecheck` for `lychee.d.ts`).
- `php artisan typescript:transform` rerun after the enum change.
- Docker workflow smoke test green on all platforms.
- Roadmap, knowledge map, `3-reference/image-processing.md` updated; drift gate report recorded here.

## Follow-ups / Backlog

- JXL in `Dockerfile-legacy` (N-100-08).
- Process for bumping the pinned ImageMagick version on security releases (ADR-100-01).

## Intent Log

- 2026-10-18: feature created from discussion #2718 at the owner's request; spec drafted after reading Feature 073, `FileExtensionService`, handlers, `Extractor`, `Dockerfile`. Local probe: ImageMagick 7.1.1-43 (Debian 13) lists no JXL coder; `mime_content_type()` returns `image/jxl` for synthetic JXL headers; no `cjxl` available. Q-100-01..07 resolved.
