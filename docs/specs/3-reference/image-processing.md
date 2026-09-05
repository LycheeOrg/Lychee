# Image Processing

This document describes Lychee's image processing architecture, including size variant generation, processing pipeline, and storage strategy.

---

## Overview

Lychee handles multiple image operations to provide optimal viewing experiences across different devices and use cases. The system automatically generates size variants, extracts metadata, and organizes files efficiently.

## Size Variants

Photos are stored in multiple sizes to optimize performance and bandwidth:

### Variant Types

| Value | Name | Description |
|-------|------|-------------|
| 0 | **RAW** | Original camera RAW / HEIC / PSD file (preserved unmodified) |
| 1 | **Original** | Full-resolution uploaded image (JPEG after conversion, or native for non-RAW uploads) |
| 2 | **Medium2x** | High-DPI web-optimised version (2× resolution) |
| 3 | **Medium** | Standard web-optimised version |
| 4 | **Small2x** | High-DPI thumbnail version (2× resolution) |
| 5 | **Small** | Standard thumbnail version |
| 6 | **Thumb2x** | High-DPI small thumbnail for galleries (2× resolution) |
| 7 | **Thumb** | Standard small thumbnail for galleries |
| 8 | **Placeholder** | Low-quality image placeholder (LQIP) |

### Variant Configuration

Each variant type has configurable dimensions and quality settings:

```php
// Example configuration
'medium' => [
    'max_width' => 1920,
    'max_height' => 1080,
    'quality' => 90,
],
'small' => [
    'max_width' => 720,
    'max_height' => 480,
    'quality' => 85,
],
'thumb' => [
    'max_width' => 200,
    'max_height' => 200,
    'quality' => 80,
],
```

## Processing Pipeline

### Upload Flow

1. **Upload**: Original file received and validated
2. **Metadata Extraction**: EXIF data parsed (GPS, camera info, timestamps)
3. **Size Generation**: Multiple variants created based on configuration
4. **Color Analysis**: Dominant color palette extracted
5. **Storage**: Files organized by naming strategy
6. **Database**: Photo and size variant records created

### Size Variant Factory

The `SizeVariantDefaultFactory` handles variant generation:

```php
// app/Image/SizeVariantDefaultFactory.php
class SizeVariantDefaultFactory implements SizeVariantFactory
{
    public function createSizeVariants(Photo $photo): Collection
    {
        // Generate different sizes based on configuration
        // Returns collection of SizeVariant models
    }
}
```

### Processing Stages

The photo creation process uses a pipeline with multiple stages:

1. **Validation Stage**: Validate file type, size, and integrity
2. **Upload Stage**: Store original file
3. **Metadata Stage**: Extract EXIF, GPS, and camera data
4. **Variant Stage**: Generate size variants
5. **Palette Stage**: Extract color palette
6. **Finalization Stage**: Create database records

For detailed information about the photo processing pipeline, see [app/Actions/Photo/README.md](../../../app/Actions/Photo/README.md).

## RAW Upload Pipeline

Camera RAW files (NEF, CR2, CR3, ARW, DNG, ORF, RW2, RAF, PEF, SRW, NRW, PSD, HEIC, HEIF) are handled by a **dual-variant** pipeline that preserves the unmodified source file alongside a displayable JPEG original.

### Detection & Conversion

The `DetectAndStoreRaw` Init pipe (replacing the former `ConvertUnsupportedMedia`) performs:

1. Extension check against `CONVERTIBLE_RAW_EXTENSIONS` constant.
2. On match: original file is stashed in `InitDTO::$raw_source_file`; `RawToJpeg` converts it to JPEG via Imagick (quality 92) and the JPEG replaces the `source_file` in the DTO.
3. On Imagick failure: graceful fallback — file is kept as-is, no RAW variant is stored, a warning is logged.
4. **PDF exception**: `.pdf` files are **not** treated as convertible RAW formats; they remain as ORIGINAL.

### RAW Size Variant Storage

The `CreateRawSizeVariant` Standalone pipe (runs after `CreateOriginalSizeVariant`) copies the raw source file to permanent storage and creates a `size_variants` DB row with:
- `type = 0` (RAW)
- `width = 0`, `height = 0` (dimensions not decoded for RAW files)
- Native file extension preserved.

### Download Gating

RAW downloads are controlled by the `raw_download_enabled` configuration key (boolean, default `false`, category *Image Processing*). When disabled, `ZipRequest::authorize()` returns `false` for `DownloadVariantType::RAW` requests.

### Removed Classes

The following classes were removed as part of this refactoring:

| Removed | Replacement |
|---------|-------------|
| `HeifToJpeg` | `RawToJpeg` (handles all convertible formats) |
| `ConvertUnsupportedMedia` | `DetectAndStoreRaw` |
| `PhotoConverterFactory` | Direct `RawToJpeg` instantiation |
| `ConvertableImageType` enum | `FileExtensionService::CONVERTIBLE_RAW_EXTENSIONS` constant |
| `PhotoConverter` interface | Removed (no multiple converters needed) |

## Metadata Extraction

### EXIF Data

Extracted metadata includes:

- **Camera Information**: Make, model, lens
- **Capture Settings**: ISO, aperture, shutter speed, focal length
- **Timestamps**: Original capture time, digitization time
- **GPS Coordinates**: Latitude, longitude, altitude
- **Image Properties**: Width, height, orientation

### Metadata Write-Back

By default, Lychee only ever *reads* EXIF/IPTC/XMP metadata from uploaded files — editing a photo's title, description, or tags, or rating a photo, only updates the database. The opt-in `embed_metadata_in_files_enabled` config (category **Image Processing**, default off) changes this: when enabled, editing title/description/tags (any user with edit rights) or rating a photo as its **owner** additionally embeds the new value into the photo's **Original** file and, when present, its preserved **RAW** camera file (see "RAW Upload Pipeline" above), via a queued `App\Jobs\EmbedMetadataJob`.

**Tag mapping** (each field is written to a cross-application-compatible triad, not a single tag, for maximum compatibility with external tools):

| Lychee field | EXIF | IPTC | XMP |
|---|---|---|---|
| Title | `XPTitle` | `ObjectName` | `dc:Title` |
| Description | `ImageDescription` | `Caption-Abstract` | `dc:Description` |
| Tags | `XPKeywords` | `Keywords` | `dc:Subject` |
| Rating (owner's only) | `Rating`, `RatingPercent` | — | `xmp:Rating` |

**Requires `exiftool`** (the existing `has_exiftool`/`exiftool_path` detection, reused unchanged) — `lychee-org/php-exif` is read-only and native PHP has no EXIF-write support, so there is no fallback engine. Writes are invoked via Laravel's `Process` facade in array form (never a shell string), since title/description/tag text is user-controlled.

**Checksum caveat:** writing to a file changes its bytes and therefore its checksum. A second, independent config, `embed_metadata_update_checksum_enabled` (default **on**), controls whether a successful Original-variant write also refreshes `Photo::checksum`/`original_checksum` to match — keeping Lychee's own duplicate-detection (`App\Actions\Photo\Pipes\Init\FindDuplicate`) internally consistent with the file. Turning it off leaves those columns pointing at the pre-edit (or original pristine upload) bytes forever, even though the file itself has changed — useful if `original_checksum` needs to stay a permanent fingerprint of what was first uploaded, at the cost of an internal DB/file mismatch. Either way, a separate, untouched copy of the same source file elsewhere (backup, external drive) keeps its old checksum and will **no longer be recognized as a duplicate** if re-imported — this is why the main embed config is off by default and carries an explicit warning. (`SizeVariant::filesize` is always refreshed regardless of this setting — it's a factual byte count, not a policy choice.)

Local storage disks only in v1 — a non-local (e.g. S3) Original/RAW variant is skipped with a logged warning, not attempted. See [Feature 059 spec](../4-architecture/features/059-embed-metadata-in-file/spec.md) for full detail.

### Timestamp Handling

Lychee carefully handles timestamps from multiple sources:

- Photo capture time (`taken_at`)
- File creation time
- EXIF timestamps
- Upload time

For detailed information about timestamp handling, see [Timestamps Handling](timestamps-handling.md).

## Storage Strategy

### File Organization

Files are organized using a configurable naming strategy:

- **Original files**: Stored with checksums for deduplication
- **Variants**: Named with size suffix (e.g., `photo_medium.jpg`)
- **Storage disks**: Configurable (local, S3, etc.)

### Storage Model

Each `SizeVariant` tracks:

```php
class SizeVariant extends Model
{
    public string $photo_id;        // Parent photo
    public string $type;            // Variant type (original, medium, small, thumb)
    public int $width;              // Pixel width
    public int $height;             // Pixel height
    public int $filesize;           // File size in bytes
    public string $storage_disk;    // Storage location
    public string $short_path;      // Relative file path
}
```

## Image Processing Engines

Lychee supports multiple image processing libraries:

- **GD**: Built-in PHP image processing
- **ImageMagick**: Advanced image processing with more features

The system automatically selects the best available engine.

## Performance Considerations

### Optimization Strategies

- **Lazy Generation**: Variants generated on-demand when requested
- **Caching**: Processed images cached for quick retrieval
- **Progressive Processing**: Large batches processed in background jobs
- **Quality vs Size**: Configurable quality settings balance file size and visual quality

### Background Processing

Large upload operations can be processed asynchronously:

```php
// app/Jobs/ProcessPhotoJob.php
class ProcessPhotoJob implements ShouldQueue
{
    public function handle()
    {
        // Process photo variants in background
    }
}
```

## Color Palette Extraction

The `Palette` model stores dominant colors extracted from each photo:

```php
class Palette extends Model
{
    public string $photo_id;        // Parent photo (primary key)
    public array $colors;           // Array of hex color values
}
```

**Usage:**
- Theme generation
- Color-based photo search
- UI theming based on photo content

## Security Considerations

### File Validation

- **Type checking**: Only allowed image formats accepted
- **Size limits**: Maximum file size enforced
- **Content validation**: Image integrity verified
- **Malware scanning**: Optional virus scanning integration

### Storage Security

- **Access control**: Files stored outside web root
- **Private access**: Served through application layer with authorization
- **Checksums**: SHA-1 checksums (`App\Image\StreamStat`) for integrity verification and import-time duplicate detection

## Related Documentation

- [Database Schema](database-schema.md) - Photo and SizeVariant models
- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure
- [Request Lifecycle: Photo Upload](../4-architecture/request-lifecycle-photo-upload.md) - Detailed upload flow

---

*Last updated: August 27, 2026*
