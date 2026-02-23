# Photos

This document explains photos, size variants, EXIF metadata, and color palettes in Lychee.

## Table of Contents

- [What is a Photo?](#what-is-a-photo)
- [Photo Attributes](#photo-attributes)
- [Photo-Album Relationship](#photo-album-relationship)
- [Photo Types](#photo-types)
- [Photo Ownership](#photo-ownership)
- [Size Variants](#size-variants)
- [EXIF Metadata](#exif-metadata)
- [Color Palette](#color-palette)

---

## What is a Photo?

A **Photo** is the fundamental content unit in Lychee. Each photo represents a single image file uploaded to the system.

## Photo Attributes

**Core Properties:**
- **ID**: Unique string identifier
- **Title**: Display name
- **Description**: Optional detailed text
- **Type**: MIME type (image/jpeg, video/mp4, image/x-canon-cr2, etc.)
- **Checksum**: SHA-256 hash of current file state
- **Original checksum**: SHA-256 hash of originally uploaded file
- **Filesize**: Size in bytes
- **Owner**: User who uploaded the photo
- **Is highlighted**: Favorite/bookmark flag

**Temporal Data:**
- **Created at**: When uploaded to Lychee
- **Updated at**: Last modification timestamp
- **Taken at**: When the photo was captured (from EXIF, with timezone)
- **Initial taken at**: Original capture time before any edits

**Technical Metadata:**
- **Camera settings**: ISO, aperture, shutter speed, focal length, make, model, lens
- **Location**: Latitude, longitude, altitude, compass direction (img_direction), textual location
- **License**: Copyright license type (defaults to system-wide setting if not explicitly set)
  - Supported: All Rights Reserved, CC BY, CC BY-SA, CC BY-ND, CC BY-NC, CC BY-NC-SA, CC BY-NC-ND, CC0, Public Domain

**Special Features:**
- **Live Photos**: Support for Apple Live Photos (photo + video pair)
  - Stores video component path, checksum, and content ID
  - Seamlessly integrates motion with still image
- **Color Palette**: Extracted dominant colors for visual search and theming
- **Tags**: Many-to-many relationship with tag entities (not comma-separated strings)
- **Purchasable**: E-commerce integration for selling prints/downloads (webshop feature)

## Photo-Album Relationship

Photos and albums have a **many-to-many relationship**:
- A single photo can belong to multiple albums simultaneously
- An album can contain many photos
- This allows flexible organization without duplicating files

```
Photo "sunset.jpg"
  ├─ belongs to Album "Vacation 2024"
  ├─ belongs to Album "Best Sunsets"
  └─ belongs to Album "Client Portfolio"
```

## Photo Types

Lychee distinguishes between three media types:

**Photos (Images):**
- Supported image MIME types (JPEG, PNG, GIF, WebP, etc.)
- Generate size variants for performance
- Display full EXIF metadata

**Videos:**
- Supported video MIME types (MP4, MOV, etc.)
- May generate thumbnail frames
- Focal length field repurposed to store framerate
- Aspect ratio defaults to 1:1 if no medium/small variant exists

**Raw Files:**
- Neither photo nor video (e.g., Canon CR2, Nikon NEF)
- Preserved but limited preview capabilities
- Often paired with JPEG sidecar for display

## Photo Ownership

- Each photo has exactly one **owner** (the user who uploaded it)
- Only the owner can:
  - Delete the photo permanently
  - Edit core metadata (title, description, license)
  - Manage tags via the many-to-many tags relationship
  - Move the photo to different albums
  - Star/unstar the photo
- Others may have restricted access through album permissions

---

## Size Variants

### What are Size Variants?

**Size Variants** are pre-generated copies of photos at different resolutions. They optimize performance and bandwidth by serving appropriately-sized images.

### Variant Types

**Original:**
- Full-resolution uploaded file
- Preserved exactly as uploaded
- Only accessible with `grants_full_photo_access` permission

**Medium:**
- Mid-resolution for desktop viewing
- Typical: 1920px or 2048px on longest edge

**Small:**
- Lower resolution for mobile or thumbnails
- Typical: 1080px on longest edge

**Thumb:**
- Small thumbnail for grid views
- Square or aspect-ratio-preserved crop

**Thumb2x:**
- High-DPI version of thumbnail
- For retina/high-resolution displays

**Video handling**: Videos may generate thumbnail frames but not full size variants

### Access Control

- **Original**: Requires `grants_full_photo_access` permission
- **Medium/Small**: Available with basic album access
- **Thumbnails**: Always accessible if album is viewable

### Checksums and Integrity

Lychee maintains two checksum values:

- **checksum**: SHA-256 of the current file state
- **original_checksum**: SHA-256 of the originally uploaded file

This dual-checksum approach:
- Detects file modifications or corruption
- Enables duplicate detection across rotations/edits
- Supports restoration to original state
- Helps identify when files have been altered outside Lychee

---

## EXIF Metadata

### What is EXIF?

**EXIF (Exchangeable Image File Format)** is metadata embedded in photo files by cameras and editing software.

### EXIF Data in Lychee

**Extracted Metadata:**
- **Camera**: Make, model, lens
- **Settings**: ISO, aperture, shutter speed, focal length
- **Date/Time**: When the photo was taken
- **Location**: GPS coordinates (latitude, longitude, altitude)
- **Orientation**: Image rotation information

**EXIF Control:**
- **Privacy control**: Choose which EXIF fields to display publicly
- **Bulk editing**: Update EXIF for multiple photos
- **Preservation**: Original EXIF retained in files
- **Override**: Manual metadata overrides EXIF

### EXIF and Permissions

Access to EXIF data can be controlled:
- **Full photo access**: See all EXIF including sensitive data (GPS)
- **Limited access**: See only basic metadata (camera, settings)
- **No EXIF**: Hide all technical metadata

This allows photographers to share portfolios without exposing location data or camera gear details.

---

## Color Palette

### What is a Palette?

The **Palette** model stores extracted dominant colors from photos, enabling visual search and theming features.

### Palette Attributes

**Color Storage:**
- **photo_id**: Link to Photo
- **colour_1** through **colour_5**: Five dominant colors (integer format 0xRRGGBB)
- No timestamps

**Color Format:**
- Stored as integers (e.g., `0xFF5733` for orange-red)
- Converted to hex strings for display (`#FF5733`)
- Extracted during photo processing

### Use Cases

- **Visual search**: Find photos by color palette
- **Theme generation**: Auto-generate UI themes from photo colors
- **Color coordination**: Match photos with similar color schemes
- **Album covers**: Select representative colors for album branding

---

**Related:** [Albums](albums.md) | [Permissions](permissions.md) | [E-commerce](e-commerce.md)

---

*Last updated: December 22, 2025*
