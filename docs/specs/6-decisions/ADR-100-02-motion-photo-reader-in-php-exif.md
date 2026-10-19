# ADR-100-02: Motion photo reader in php-exif

- **Status:** Accepted
- **Date:** 2026-10-18
- **Related features/specs:** Feature 100 (docs/specs/4-architecture/features/100-google-motion-photos/spec.md)
- **Related open questions:** Q-100-08, Q-100-02

## Context

ADR-100-01 placed the Google Motion Photo XMP reader in Lychee (`App\Metadata\MotionPhoto`), because php-exif's exiftool mapping flattens `Container:Directory` into parallel lists that misalign when an item has no `Item:Length`, and the Native and ImageMagick adapters do not expose the directory at all. On PR #4900 the maintainers asked for the support to live in `lychee-org/php-exif` instead. The flattening argument rules out tag mapping, not the library: the reader works on the file itself and needs nothing from Lychee.

## Decision

Move the reader into php-exif as `PHPExif\MotionPhoto\*` (`JpegXmpReader`, `JpegHeader`, `JpegImageEnd`, `FtypBox`, `MotionPhotoXmp`, `MotionPhotoXmpHandler`, `MotionPhotoLocator::locate(string $path): int`). `Reader::read()` calls it whenever the adapter reports no `MicroVideoOffset`, and stores the video length there, so every adapter reports legacy and Motion Photo 1.0 files the same way. Behaviour is unchanged from ADR-100-01 (declared length checked against an `ftyp` box, scan after the primary image's EOI when `MotionPhoto="1"`).

Lychee then drops `App\Metadata\MotionPhoto` and the locator call in `Extractor` (it reads `getMicroVideoOffset()` as before), and `lychee:extract_motion_photos` calls `PHPExif\MotionPhoto\MotionPhotoLocator` directly.

## Consequences

### Positive
- Metadata reading stays in one library; other php-exif users get motion photos.
- Lychee's `Extractor` has no motion photo special case.

### Negative
- Two-repository change: php-exif PR and release, then a php-exif bump in Lychee (owner approval required by AGENTS.md).
- Until then PR #4900 still carries the in-house reader.

## Alternatives Considered

- **Keep ADR-100-01:** no coordination, but against the maintainers' request.
- **Map the tags per adapter (ADR-100-01 Option B):** still rejected: exiftool's flattened lists misalign on real Pixel files, Native and ImageMagick do not expose the directory.

## Security / Privacy Impact

Unchanged from ADR-100-01: bounded reads of untrusted bytes, `ext-xml` SAX parser without external entities.

## Operational Impact

Unchanged; the reads move from Lychee to php-exif.

## Links

- Supersedes: ADR-100-01
- php-exif branch: `mlapaglia/php-exif` `feat/google-motion-photos`
- Related issues / PRs: LycheeOrg/Lychee#4900 (comment 6251741379), #753, #1283
