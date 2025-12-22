# Image Processing

This document describes Lychee's image processing architecture, including size variant generation, processing pipeline, and storage strategy.

---

## Overview

Lychee handles multiple image operations to provide optimal viewing experiences across different devices and use cases. The system automatically generates size variants, extracts metadata, and organizes files efficiently.

## Size Variants

Photos are stored in multiple sizes to optimize performance and bandwidth:

### Variant Types

- **Original**: Full-resolution uploaded image (unmodified)
- **Medium2x**: High-DPI web-optimized version (2x resolution)
- **Medium**: Standard web-optimized version
- **Small2x**: High-DPI thumbnail version (2x resolution)
- **Small**: Standard thumbnail version
- **Thumb2x**: High-DPI small thumbnail for galleries (2x resolution)
- **Thumb**: Standard small thumbnail for galleries

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

## Metadata Extraction

### EXIF Data

Extracted metadata includes:

- **Camera Information**: Make, model, lens
- **Capture Settings**: ISO, aperture, shutter speed, focal length
- **Timestamps**: Original capture time, digitization time
- **GPS Coordinates**: Latitude, longitude, altitude
- **Image Properties**: Width, height, orientation

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
- **Checksums**: SHA-256 checksums for integrity verification

## Related Documentation

- [Database Schema](database-schema.md) - Photo and SizeVariant models
- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure
- [Request Lifecycle: Photo Upload](../4-architecture/request-lifecycle-photo-upload.md) - Detailed upload flow

---

*Last updated: December 22, 2025*
