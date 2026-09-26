# Feature Plan 071 – Configurable Size-Variant Format and Quality

_Linked specification:_ `docs/specs/4-architecture/features/071-size-variant-format/spec.md`  
_Status:_ Complete  
_Last updated:_ 2026-09-26

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

Admins can switch generated thumbnails to WebP, lossy or lossless, from the settings page. Success means:

- S-071-01..08 are green under both GD and Imagick.
- The default configuration produces the same file naming as before.
- There are no new dependencies and no frontend diff.

## Scope Alignment

- **In scope:** the two config migrations, the `SizeVariantFormat` enum, the extension override in `BaseSizeVariantNamingStrategy`, quality/lossless resolution in `BaseImageHandler`, WebP routing and quality in `GdHandler::save()`, lossless/quality handling in `ImagickHandler::save()`, tests, and docs.
- **Out of scope:** N-071-01..06 (conversion of existing files, AVIF, placeholder/watermark format, full GD target-type dispatch, originals, per-variant settings).

## Dependencies & Interfaces

- `App\Repositories\ConfigManager::getValueAsEnum()` / `getValueAsInt()`
- `App\Models\Configs::sanity()` (existing enum and `int:min:max` branches)
- `resources/js/v8/components/settings/ConfigGroup.vue` (existing generic widgets; no change)
- GD `imagewebp()` with `IMG_WEBP_LOSSLESS` (PHP ≥ 8.1), Imagick `webp:lossless` option

## Assumptions & Risks

- **Assumptions:** The Docker image ships `gd` with WebP and `imagick` with a WebP delegate (Debian trixie `imagemagick`). `GDSupportCheck` already warns otherwise.
- **Risks / Mitigations:**
  - *Imagick PNG semantics:* for PNG, `setImageCompressionQuality()` is a zlib level/filter pair, and `0` would disable compression. Mitigated by FR-071-08: `0` resolves to `100` for every non-WebP target.
  - *Imagick coverage:* the Imagick handler cases skip when `ext-imagick` is missing, so CI (which installs it) is authoritative for them.

## Implementation Drift Gate

After CI is green: map FR-071-01..10 to classes/tests in the table below, and confirm `git diff --stat resources/js` is empty (NFR-071-02) and `composer.json` is unchanged (NFR-071-01).

| Requirement | Implementation | Test |
|-------------|----------------|------|
| FR-071-01 | `2026_09_26_000001_add_size_variant_format_config.php` | `SizeVariantFormatConfigSanityTest` |
| FR-071-02/03/04/05 | `SizeVariantFormat::extension()`, `BaseSizeVariantNamingStrategy::generateExtension()` | `BaseImageHandler::testSizeVariantFormat*`, `SizeVariantFormatTest` |
| FR-071-06/10 | `2026_09_26_000002_bound_compression_quality.php` | `SizeVariantFormatConfigSanityTest` |
| FR-071-07/08 | `BaseImageHandler::resolveQuality()`/`isLossless()`, `GdHandler::save()`, `ImagickHandler::save()` | `BaseImageHandler::testSizeVariantFormatWebpLossless`, `…JpegLosslessClamps` |
| FR-071-09 | `GdHandler::save()` | `PhotosAddHandlerGDTest` (inherited cases) |

**Drift gate report (2026-09-26):**
- Every FR maps to the implementation and tests in the table above. No undocumented behaviour was added. The one addition is the `size_variant_format` documentation/details keys in all `lang/*/all_settings.php` files, following the convention of the latest config additions and recorded in the spec's Documentation Deliverables.
- The tests fail without the implementation (4 of 5 image-processing cases). `testSizeVariantFormatOriginalKeepsExtensions` passes on both sides by design, as a regression guard for G3.
- `git diff --stat resources/js` is empty (NFR-071-02), and `composer.json`/`package.json` are unchanged (NFR-071-01).
- Owner feedback applied: `compression_quality` is labelled "Quality of generated size variants", and the meaning of `0` lives in the details text.

## Increment Map

1. **I1 – Config + enum (≤45 min)**
   - _Steps:_ tests first (`SizeVariantFormatTest`, `SizeVariantFormatConfigSanityTest`), then the enum and both migrations.
   - _Commands:_ `php artisan test --filter=SizeVariantFormat`, `make phpstan`
   - _Exit:_ the unit tests are green.
2. **I2 – Naming strategy (≤30 min)**
   - _Steps:_ image-processing tests for S-071-01/02/03/05 (extension assertions), then the `generateExtension()` override.
   - _Commands:_ `php artisan test --filter=PhotosAddHandler`
3. **I3 – Handlers: WebP + quality (≤60 min)**
   - _Steps:_ tests S-071-03/04/06 (magic bytes), then `resolveQuality()`/`isLossless()` in `BaseImageHandler`, and the GD and Imagick `save()` changes.
   - _Commands:_ `php artisan test --filter=PhotosAddHandler`, `make phpstan`
4. **I4 – Docs + quality gate (≤30 min)**
   - _Steps:_ roadmap, knowledge map, `3-reference/image-processing.md`; run `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan` (CI).

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-071-01 | I2 / T-071-03 | `testSizeVariantFormatOriginalKeepsExtensions` |
| S-071-02 | I2 / T-071-03 | same test, PNG upload |
| S-071-03 | I3 / T-071-05 | `testSizeVariantFormatWebpLossy` |
| S-071-04 | I3 / T-071-05 | `testSizeVariantFormatWebpLossless` |
| S-071-05 | I2 / T-071-03 | `testSizeVariantFormatJpegFromPng` |
| S-071-06 | I3 / T-071-05 | `testSizeVariantFormatJpegLosslessClamps` |
| S-071-07 | I1 / T-071-01 | sanity test |
| S-071-08 | I1 / T-071-01 | sanity test |

## Analysis Gate

Completed 2026-09-26 (agent self-review):

- Spec completeness: pass. FR/NFR are populated, the ASCII mock-up is included, and Q-071-01..04 are folded into FR/N sections.
- Open questions: pass. No `Open` entries for 071; all four were resolved directly with rationale, and the owner can override. No ADR needed: the change is local to image processing and crosses no module boundary.
- Plan/tasks alignment: pass. Every FR maps to a task, and tests are sequenced before code.
- Constitution: pass. No dependencies, the helpers are small and pure, and the migrations are reversible.
- Tooling: commands are listed per increment.

## Exit Criteria

- CI green: php-cs-fixer, PHPStan, PHPUnit (Unit + ImageProcessing + Feature_v2).
- All tasks `[x]`, roadmap row updated, drift gate table confirmed.

## Follow-ups / Backlog

- Q-071-04 Option B: make `GdHandler::save()` dispatch on target extension for every format (with alpha flattening for JPEG).
- Optional: a maintenance action to convert existing size variants to the configured format.
- Per-size quality (`compression_quality_thumb`/`_small`/`_medium`) to fully close LycheeOrg/Lychee#1888 (Q-071-05 Option A deferred it). This needs the variant type passed from `SizeVariantDefaultFactory` into `save()`.
