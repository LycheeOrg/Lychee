# ADR-100-01: In-house XMP reader for Google Motion Photos

- **Status:** Superseded by ADR-100-02
- **Date:** 2026-10-17
- **Related features/specs:** Feature 100 (docs/specs/4-architecture/features/100-google-motion-photos/spec.md)
- **Related open questions:** Q-100-02

## Context

Lychee reads photo metadata through `lychee-org/php-exif`, with the exiftool, Imagick, FFprobe or Native reader chosen in `App\Metadata\Extractor`. Google Motion Photos embed an MP4 after the JPEG and describe it in XMP:
- The legacy format (Android ≤ 10) declares `GCamera:MicroVideoOffset`. php-exif 1.4.0 maps it only for the exiftool reader (`XMP-GCamera:MicroVideoOffset`), so Imagick and Native installs never extract the video.
- The current Motion Photo 1.0 format (Android 11+) declares `GCamera:MotionPhoto` and a `Container:Directory` sequence of items with `Item:Semantic` and `Item:Length`. php-exif maps none of it, and exiftool's flattened `-j -G1` output returns the directory as parallel per-field lists that php-exif's scalar mapper does not model.
- Issue #1283's exiftool dump lists only the `Primary` item, so some files may lack the video's directory entry.

## Decision

Read the motion photo XMP in Lychee itself, under `App\Metadata\MotionPhoto`:
- `JpegXmpReader` walks the JPEG markers from SOI to SOS and returns the standard XMP APP1 packet.
- `MotionPhotoXmp` parses it with `ext-xml` (namespace-aware) into a readonly record: `is_motion_photo`, `item_length`, `legacy_offset`.
- `MotionPhotoLocator` composes them: declared length first (directory item, then legacy offset), checked against an MP4 `ftyp` box at the computed start; when `MotionPhoto="1"` and no valid declared length exists, a chunked scan for the first plausible `ftyp` box after the primary image's end-of-image marker (`JpegImageEnd`), so compressed image data cannot match.
- `Extractor::createFromFile` calls the locator for `image/jpeg` when php-exif returned no `MicroVideoOffset`, and stores the result in the existing `micro_video_offset` field. The upload pipes and `GoogleMotionPictureHandler` are unchanged.

## Consequences

### Positive
- Motion photos work on every reader, not only exiftool.
- No dependency bump and no second-repository release; no new PHP extension (`ext-xml` is already required).
- One place handles the legacy, current and Ultra HDR (`GainMap`) layouts.

### Negative
- Lychee maintains a small JPEG segment walker and XMP parser, outside php-exif.
- The `ftyp` scan is a heuristic, limited to files that declare `MotionPhoto="1"`.
- When exiftool is the reader, the legacy tag is read twice (php-exif and, only if php-exif returned none, the locator); cost is a few KB of header reads.

## Alternatives Considered

- **B – Extend php-exif** to map `MotionPhoto` and the `DirectoryItem*` lists: keeps metadata in the library, but works only with exiftool and needs a php-exif release plus a dependency bump.
- **C – `ftyp` scan only:** simplest, but ignores the declared length and is more prone to false positives.

## Security / Privacy Impact

- Parses untrusted uploaded bytes: the reader bounds every segment length by the file size, stops at SOS, and uses `ext-xml`'s SAX parser, which does not resolve external entities (no XXE).
- No new endpoint or stored data; the extracted video follows the existing live photo storage and access rules.

## Operational Impact

- Upload adds one header read per JPEG (stops at SOS, typically < 100 KB) and, only for files declaring `MotionPhoto="1"` without a valid length, a chunked scan in constant memory.
- Failures log a warning and the photo imports as a still, as today.

## Links

- Related spec sections: `docs/specs/4-architecture/features/100-google-motion-photos/spec.md#functional-requirements` (FR-100-01), NFR-100-02, NFR-100-03
- Related issues / PRs: LycheeOrg/Lychee#753, LycheeOrg/Lychee#1283
