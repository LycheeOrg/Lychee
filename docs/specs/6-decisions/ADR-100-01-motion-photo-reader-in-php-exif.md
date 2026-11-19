# ADR-100-01: Motion photo reader in php-exif

- **Status:** Accepted
- **Date:** 2026-10-18
- **Related features/specs:** Feature 100 (docs/specs/4-architecture/features/100-google-motion-photos/spec.md)
- **Related open questions:** Q-100-08, Q-100-02

## Context

Lychee reads photo metadata through `lychee-org/php-exif`, with the exiftool, Imagick, FFprobe or Native reader chosen in `App\Metadata\Extractor`. Google Motion Photos embed an MP4 after the JPEG and describe it in XMP:
- The legacy format (Android ≤ 10) declares `GCamera:MicroVideoOffset`. php-exif 1.4.0 maps it only for the exiftool reader, so Imagick and Native installs never extract the video.
- The Motion Photo 1.0 format (Android 11+) declares `GCamera:MotionPhoto` and a `Container:Directory` sequence of items with `Item:Semantic` and `Item:Length`. exiftool flattens the directory into parallel per-field lists that misalign when an item has no `Item:Length` (the Pixel's `Primary` item), and the Native and Imagick adapters do not expose it at all. Mapping the tags per adapter is therefore not enough: the video has to be located from the file itself.

On PR #4900 the maintainers asked for this support to live in `lychee-org/php-exif` rather than in Lychee.

## Decision

The reader lives in php-exif as `PHPExif\MotionPhoto\*` (`JpegXmpReader`, `JpegHeader`, `JpegImageEnd`, `FtypBox`, `MotionPhotoXmp`, `MotionPhotoXmpHandler`, `MotionPhotoLocator::locate(string $path): int`), released in php-exif 1.5.0 (LycheeOrg/php-exif#83):
- It walks the JPEG markers to the standard XMP APP1 packet and parses it with `ext-xml`.
- A declared length (the `Container:Item` with `Item:Semantic="MotionPhoto"`, else `GCamera:MicroVideoOffset`) is accepted only when an MP4 `ftyp` box sits at the computed start.
- Otherwise, when `MotionPhoto="1"`, it scans for the first plausible `ftyp` box after the primary image's end-of-image marker, so compressed image data cannot match.

`Reader::read()` calls it whenever the adapter reports no `MicroVideoOffset` and stores the video length there, so every adapter reports legacy and Motion Photo 1.0 files the same way. Lychee's `Extractor` reads `getMicroVideoOffset()` as before, with no motion photo special case, and `lychee:extract_motion_photos` calls `PHPExif\MotionPhoto\MotionPhotoLocator` directly.

## Consequences

### Positive
- Motion photos work on every reader, not only exiftool.
- Metadata reading stays in one library; other php-exif users get motion photos.
- One place handles the legacy, Motion Photo 1.0 and Ultra HDR (`GainMap`) layouts.

### Negative
- Two-repository change: php-exif release, then a php-exif bump in Lychee (`^1.5.0`, owner approval 2026-10-19).
- The `ftyp` scan is a heuristic, limited to files that declare `MotionPhoto="1"`.

## Alternatives Considered

- **Keep the reader in Lychee** (`App\Metadata\MotionPhoto`, the first answer to Q-100-02): no cross-repository coordination, but against the maintainers' request and splits metadata reading between the library and the app.
- **Map the tags per adapter:** exiftool's flattened lists misalign on real Pixel files, Native and Imagick do not expose the directory.
- **`ftyp` scan only:** simplest, but ignores the declared length and is more prone to false positives.

## Security / Privacy Impact

- Parses untrusted uploaded bytes: the reader bounds every segment length by the file size, stops at SOS, and uses `ext-xml`'s SAX parser, which does not resolve external entities (no XXE).
- No new endpoint or stored data; the extracted video follows the existing live photo storage and access rules.

## Operational Impact

- One header read per JPEG (stops at SOS, typically < 100 KB) and, only for files declaring `MotionPhoto="1"` without a valid length, a chunked scan in constant memory.
- Failures log a warning and the photo imports as a still.

## Links

- Related spec sections: `docs/specs/4-architecture/features/100-google-motion-photos/spec.md#functional-requirements` (FR-100-01), NFR-100-02, NFR-100-03
- Related issues / PRs: LycheeOrg/php-exif#83, LycheeOrg/Lychee#4900 (comment 6251741379), #753, #1283
