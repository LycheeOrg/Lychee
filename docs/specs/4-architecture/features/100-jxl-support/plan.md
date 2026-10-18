# Feature Plan 100 – JPEG XL Support

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Draft  
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
- `Dockerfile`, `.github/workflows/docker_build.yml`
- **Approved dependency change (Q-100-06, 2026-10-18, owner):** source-built ImageMagick 7.1.x + Debian `libjxl-dev` in the Docker image (ADR-100-01). No Composer/npm change.

## Assumptions & Risks

- **Assumptions:**
  - libmagic reports `image/jxl` (verified on Debian 13 with synthetic codestream and container headers), so `mime_content_type()` needs no special case.
  - ImageMagick's JXL coder maps quality 100 to lossless (distance 0).
  - The Imagick metadata reader returns width/height for JXL.
- **Risks / Mitigations:**
  - *Local dev box and CI have no JXL delegate* → JXL encode/decode tests skip (NFR-100-04); the encoder path is verified inside the new Docker image (I6) and recorded below.
  - *Source build of ImageMagick misses a delegate the Debian package had* (HEIC, TIFF, RAW via libraw, Ghostscript) → I5 compares `magick -list format` before/after and the drift gate records the diff; HEIC/PDF sample uploads are part of the manual check.
  - *Multi-arch build time* → builder stage cached; measure in I5 (NFR-100-05). If the limit is exceeded, raise a new open question before merging.
  - *`imagick` PECL build against a custom prefix* → set `PKG_CONFIG_PATH`; smoke test catches a mismatch.
  - *Fixture creation needs a JXL encoder* → generated with the new image (I6), so I6 depends on I5.

## Implementation Drift Gate

After all tasks are `[x]` and CI is green: map FR-100-01..11 to classes/tests in a table here, confirm `git diff --stat resources/js` only touches `lychee.d.ts` (NFR-100-02), `composer.json`/`package.json` unchanged (NFR-100-01), Docker image size delta (NFR-100-05), and the `magick -list format` before/after diff.

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
   - _Steps:_ spike: check for a trixie(-backports) ImageMagick package with JXL (ADR-100-01); otherwise builder stage compiling the pinned tarball against `libjxl-dev`; runtime stage copies prefix + libs; `imagick` built against it; policy path updated in `Dockerfile` and `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS`; smoke step in `docker_build.yml`; record `magick -list format` diff and image size.
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
