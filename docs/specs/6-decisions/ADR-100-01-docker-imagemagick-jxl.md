# ADR-100-01: JXL-capable ImageMagick in the Docker image

- **Status:** Accepted
- **Date:** 2026-10-18
- **Related features/specs:** Feature 100 (docs/specs/4-architecture/features/100-jxl-support/spec.md)
- **Related open questions:** Q-100-06

## Context

Feature 100 adds JPEG XL uploads and a `jxl` value for `size_variant_format`. Decoding and encoding JXL go through Imagick only (GD has no JXL). The production `Dockerfile` is based on `dunglas/frankenphp:…-php8-trixie` and installs Debian 13's `imagemagick` package; `install-php-extensions imagick` builds the PHP extension against Debian's MagickWand.

Debian 13 builds ImageMagick 7.1.1-43 without the JXL coder: `magick -list format` and `Imagick::queryFormats('JXL')` show nothing although `libjxl0.11` is installable. Without a change, every Docker user selecting `jxl` (or uploading `.jxl`) gets a diagnostics error and failing uploads.

Affected modules: `Dockerfile` (production stage), `.github/workflows/docker_build.yml` (smoke test), `ImagickPdfCheck` (policy file location).

## Decision

The production stage of `Dockerfile` builds ImageMagick 7 from the official release tarball, pinned by version and SHA-256, against Debian 13's `libjxl-dev` (plus the delegates the Debian package provides today: JPEG, PNG, WebP, TIFF, HEIF, LCMS, Ghostscript/PDF, FreeType, XML, zlib), in a separate builder stage. The runtime stage copies the install prefix and the runtime libraries only, and `install-php-extensions imagick` is built against that MagickWand. The PDF policy edit targets the new `policy.xml` location, which `ImagickPdfCheck::IMAGICK_POLICY_LOCATIONS` also lists.

The Docker build workflow smoke-tests the image: `Imagick::queryFormats('JXL')` is non-empty and a JPEG→JXL→JPEG round trip succeeds.

`Dockerfile-legacy` is unchanged (it uses Sury's `php8.5-imagick`, which links Debian's MagickCore).

The implementation spike (T-100-01) first checks whether a Debian package with JXL enabled exists for trixie (backports); if so, that package replaces the source build, keeping the rest of this decision (smoke test, policy path) intact.

## Consequences

### Positive
- JXL works out of the box for Docker users, which is most Lychee installs.
- The ImageMagick version becomes explicit and reproducible (pinned tarball + checksum).

### Negative
- Longer multi-arch image build (ImageMagick compile per architecture).
- LycheeOrg now tracks ImageMagick security releases itself instead of relying on Debian security updates; the pinned version must be bumped manually.
- Slightly larger image (libjxl, libhwy, libbrotli).

## Alternatives Considered

- **B — Code only, Docker unchanged:** no build cost, but JXL fails on the official image until a follow-up.
- **C — A dedicated CI job with a JXL-capable ImageMagick:** verifies the encoder in CI, but Docker users still have no JXL.
- **Mixing Debian suites (forky/sid ImageMagick on trixie):** fragile dependency resolution; rejected.

## Security / Privacy Impact

- Larger attack surface in image decoding (libjxl). ImageMagick's `policy.xml` remains the control point; the existing PDF rule is preserved.
- Security patches for ImageMagick no longer arrive through `apt upgrade`; Dependabot/Trivy findings require a manual version bump.

## Operational Impact

- Docker build time and image size increase (bounded by NFR-100-05).
- Maintainers bump the pinned ImageMagick version on security releases.
- The diagnostics page (`ImagickJxlCheck`) tells non-Docker admins whether their system supports JXL.

## Links

- Related spec sections: `docs/specs/4-architecture/features/100-jxl-support/spec.md` (FR-100-06, FR-100-07, FR-100-11, NFR-100-01, NFR-100-05)
- Related ADRs: none
- Related issues / PRs: [discussion #2718](https://github.com/LycheeOrg/Lychee/discussions/2718)
