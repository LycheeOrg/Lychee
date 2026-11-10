# ADR-101-01: JXL-capable ImageMagick in the Docker image

- **Status:** Accepted
- **Date:** 2026-10-18
- **Related features/specs:** Feature 101 (docs/specs/4-architecture/features/101-jxl-support/spec.md)
- **Related open questions:** Q-101-06

## Context

Feature 101 adds JPEG XL uploads and a `jxl` value for `size_variant_format`. Decoding and encoding JXL go through Imagick only (GD has no JXL). The production `Dockerfile` is based on `dunglas/frankenphp:…-php8-trixie` and installs Debian 13's `imagemagick` package; `install-php-extensions imagick` builds the PHP extension against Debian's MagickWand.

Debian 13 builds ImageMagick 7.1.1-43 without the JXL coder: `magick -list format` and `Imagick::queryFormats('JXL')` show nothing although `libjxl0.11` is installable. Without a change, every Docker user selecting `jxl` (or uploading `.jxl`) gets a diagnostics error and failing uploads.

Affected modules: `Dockerfile` (new `imagemagick` stage, production stage), `ImagickPdfCheck` (policy file location).

## Decision

The production stage of `Dockerfile` builds ImageMagick 7 from the official release tarball, pinned by version and SHA-256, against Debian 13's `libjxl-dev` (plus the delegates the Debian package provides today: JPEG, PNG, WebP, TIFF, HEIF, LCMS, Ghostscript/PDF, FreeType, XML, zlib), in a separate builder stage. Both are installed into `/opt/imagemagick` (PHP itself lives in `/usr/local`). The builder stage also compiles the `imagick` PECL extension (pinned version and SHA-256) against that MagickWand; the production stage copies the prefix, installs the runtime libraries only, and enables the extension. Debian's `policy.xml` (from `imagemagick-7-common`) replaces upstream's open policy so resource limits and disabled coders stay as before; the PDF rule is added to it, and `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS` lists the new path.

The production stage ends with a build-time check: Imagick writes a JXL image and reads it back, otherwise the build fails.

`Dockerfile-legacy` follows the same decision since 2026-10-22 (FR-101-12). It used Sury's `php8.5-imagick`, which links Debian's MagickCore; once it moved to the official `php:8.5-fpm-trixie` image, nothing tied it to Debian's ImageMagick any more. It keeps its own `imagemagick` stage, with the same pinned versions, because imagick must be compiled for the non-thread-safe PHP of PHP-FPM.

No Debian 13 package (including trixie-backports) ships ImageMagick with JXL enabled, so the source build is required.

## Consequences

### Positive
- JXL works out of the box for Docker users, which is most Lychee installs.
- The ImageMagick version becomes explicit and reproducible (pinned tarball + checksum).

### Negative
- Longer multi-arch image build (ImageMagick compile per architecture).
- LycheeOrg now tracks ImageMagick security releases itself instead of relying on Debian security updates; the pinned version must be bumped manually, in both `Dockerfile` and `Dockerfile-legacy`.
- Slightly larger image (libjxl, libhwy, libbrotli).

## Alternatives Considered

- **B — Code only, Docker unchanged:** no build cost, but JXL fails on the official image until a follow-up.
- **C — A dedicated CI job with a JXL-capable ImageMagick:** verifies the encoder in CI, but Docker users still have no JXL.
- **Mixing Debian suites (forky/sid ImageMagick on trixie):** fragile dependency resolution; rejected.

## Security / Privacy Impact

- Larger attack surface in image decoding (libjxl). ImageMagick's `policy.xml` remains the control point; the existing PDF rule is preserved.
- Security patches for ImageMagick no longer arrive through `apt upgrade`; Dependabot/Trivy findings require a manual version bump.

## Operational Impact

- Docker build time and image size increase (bounded by NFR-101-05); the `imagemagick` stage is cached by the existing GitHub Actions build cache.
- Maintainers bump the pinned ImageMagick version on security releases.
- The diagnostics page (`ImagickJxlCheck`) tells non-Docker admins whether their system supports JXL.

## Links

- Related spec sections: `docs/specs/4-architecture/features/101-jxl-support/spec.md` (FR-101-06, FR-101-07, FR-101-11, FR-101-12, NFR-101-01, NFR-101-05)
- Related ADRs: none
- Related issues / PRs: [discussion #2718](https://github.com/LycheeOrg/Lychee/discussions/2718)
