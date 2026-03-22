# Database Schema

This document describes Lychee's database architecture, including core models, relationships, and the distinction between regular albums and smart albums.

---

## Overview

Lychee's data layer is built around Eloquent ORM models that represent the main entities in the photo management system. The database supports MySQL, MariaDB, PostgreSQL, and SQLite.

## Core Models

### User Models

#### User
System users with authentication and ownership relationships.

**Key Fields:**
- `id`: Primary key
- `username`: Unique username
- `email`: Email address
- `password`: Hashed password
- `may_upload`: Upload permission flag
- `may_edit_own_settings`: Settings permission flag

**Relationships:**
- Has many `Album` (owned albums)
- Has many `Photo` (owned photos)
- Has many `PhotoRating`
- Belongs to `UserGroup` (SE edition)
- Has many `OauthCredential`

#### UserGroup
User groups for permission management (SE edition).

#### OauthCredential
OAuth authentication credentials linking users to external providers.

### Album Models

#### Album
Regular photo albums with hierarchical tree structure using nested set model.

**Key Fields:**
- `id`: Primary key
- `title`: Album title
- `description`: Optional description
- `owner_id`: Foreign key to User
- `parent_id`: Foreign key to parent Album (nullable)
- `_lft`, `_rgt`: Nested set boundaries for tree structure
- `is_public`: Public visibility flag
- `is_nsfw`: NSFW content flag
- `is_link_required`: Requires direct link flag

**Relationships:**
- Belongs to `User` (owner)
- Has many `Photo`
- Belongs to parent `Album` (self-referential)
- Has many child `Album` (children)
- Has many `AccessPermission`

For detailed information about the tree structure implementation, see [Album Tree Structure](../4-architecture/album-tree-structure.md) which explains the nested set model with `_lft` and `_rgt` boundaries.

#### TagAlbum
Special albums that automatically contain photos with specific tags.

**Key Fields:**
- `id`: Primary key
- `title`: Tag name
- Similar visibility fields as Album

**Relationships:**
- Has many `Photo` through `photo_tag` pivot table

For detailed information about the tag system, see [Tag System](../4-architecture/tag-system.md).

### Photo and Media Models

#### Photo
Individual photos with metadata, EXIF data, and file information.

**Key Fields:**
- `id`: Primary key
- `title`: Photo title
- `description`: Optional description
- `owner_id`: Foreign key to User
- `type`: MIME type
- `original_checksum`: SHA-256 checksum
- `is_public`: Public visibility flag
- `is_highlighted`: Favorite flag
- `taken_at`: Photo capture timestamp
- `latitude`, `longitude`: GPS coordinates

**Relationships:**
- Belongs to many `Album` through `photo_album` pivot table (many-to-many)
- Belongs to `User` (owner)
- Has many `SizeVariant`
- Has one `Palette`
- Has many `Tag` through `photos_tags` pivot table
- Has many `PhotoRating`
- Has one `Statistics` (visit/download tracking)

#### SizeVariant
Different size versions of photos (original, medium, small, thumb).

**Key Fields:**
- `id`: Primary key
- `photo_id`: Foreign key to Photo
- `type`: Size variant type (original, medium2x, medium, small2x, small, thumb2x, thumb)
- `width`, `height`: Dimensions
- `filesize`: File size in bytes
- `storage_disk`: Storage location
- `short_path`: Relative file path

**Relationships:**
- Belongs to `Photo`

#### Palette
Color palette information extracted from photos.

**Key Fields:**
- `photo_id`: Foreign key to Photo (primary key)
- `colors`: Array of dominant colors (hex values)

**Relationships:**
- Belongs to `Photo`

#### Face
A detected face bounding box within a specific photo.

**Key Fields:**
- `id`: Primary key (24-char random string)
- `photo_id`: Foreign key to Photo (cascade delete)
- `person_id`: Foreign key to Person (nullable; null on delete)
- `x`, `y`, `width`, `height`: Bounding box as relative floats (0.0–1.0)
- `confidence`: Detection confidence score (0.0–1.0)
- `crop_token`: Opaque token for serving cropped face thumbnails
- `is_dismissed`: Whether the face has been dismissed/ignored by an operator

**Relationships:**
- Belongs to `Photo`
- Belongs to `Person` (nullable)
- Has many `FaceSuggestion` (as the source face)

#### FaceSuggestion
Pairs of faces ranked by embedding similarity (used to suggest identities for unknown faces).

**Key Fields:**
- `face_id`: Foreign key to Face (cascade delete) — the unidentified face
- `suggested_face_id`: Foreign key to Face (cascade delete) — an already-assigned face of a known Person
- `confidence`: Similarity score (0.0–1.0)

**Unique Constraint:** Composite unique index on (`face_id`, `suggested_face_id`)

**Relationships:**
- Both keys belong to `Face`

#### Person
An identified individual who appears across one or more photos.

**Key Fields:**
- `id`: Primary key (24-char random string)
- `name`: Display name (max 255 chars, required)
- `user_id`: Foreign key to User (nullable, unique — one Person per User claim; null on delete)
- `is_searchable`: When false, face overlays for this Person are hidden from non-owners (privacy)

**Unique Constraint:** `user_id` is unique (one User → at most one claimed Person)

**Relationships:**
- Optionally belongs to `User` (the claimed user)
- Has many `Face`

#### PhotoRating
User ratings for photos on a 1-5 star scale.

**Key Fields:**
- `photo_id`: Foreign key to Photo
- `user_id`: Foreign key to User
- `rating`: Integer rating value (1-5)

**Unique Constraint:**
- Composite unique index on (`photo_id`, `user_id`) - one rating per user per photo

**Relationships:**
- Belongs to `Photo`
- Belongs to `User`

**Statistics:**
- Average rating and count are computed via `PhotoStatistics` model
- Current user's rating is exposed through `PhotoResource`

### Configuration and System Models

#### Configs
Runtime configuration settings stored in database.

**Key Fields:**
- `key`: Configuration key (primary key)
- `value`: Configuration value (string)
- `cat`: Category name
- `type_range`: Validation type/range
- `confidentiality`: Visibility level (0-3)

**Type-safe Access Methods:**
- `getValueAsString()`
- `getValueAsInt()`
- `getValueAsBool()`
- `getValueAsEnum()`

#### ConfigCategory
Categories for organizing configuration options.

#### AccessPermission
Granular access control for albums and photos.

**Key Fields:**
- `id`: Primary key
- `user_id`: Foreign key to User (nullable)
- `album_id`: Foreign key to Album
- `grants_full_photo_access`: Full access flag
- `grants_download`: Download permission flag
- `grants_upload`: Upload permission flag
- `grants_edit`: Edit permission flag
- `grants_delete`: Delete permission flag

**Relationships:**
- Belongs to `User`
- Belongs to `Album`

#### Statistics
Photo and album statistics (count, sizes, etc.).

#### JobHistory
Background job execution history.

#### LiveMetrics
System performance and usage metrics.

## Smart Albums vs Regular Albums

### Regular Albums

**Regular Albums** (`Album` model):
- Stored in database with hierarchical tree structure
- Photos are explicitly assigned through relationships
- Can be created, deleted, and modified by users
- Support nested organization with parent-child relationships
- Use the nested set model for efficient tree operations

### Smart Albums

**Smart Albums** (extending `BaseSmartAlbum`):
- Virtual albums that exist only in memory
- Photos are included based on dynamic criteria (highlighted, recent, etc.)
- Cannot be created or deleted by users - they always exist when enabled
- Types: Recent, Highlighted, On This Day, Unsorted

For detailed information about Smart Albums, see [app/SmartAlbums/README.md](../../../app/SmartAlbums/README.md).

### Dual Approach Benefits

This dual approach allows Lychee to provide:
- Traditional album organization (user-created structure)
- Automatic categorization (system-generated views)
- Flexible photo access patterns
- Efficient querying for both stored and dynamic content

## Key Relationships

### Hierarchical Structure
- **Albums**: Parent-child relationships using nested set model
- **Users**: Own albums and photos, belong to user groups (SE edition)

### Many-to-Many
- **Photos-Albums**: Many-to-many through `photo_album` pivot table (photos can belong to multiple albums)
- **Tags**: Many-to-many with photos through `photos_tags` pivot table
- **Face Suggestions**: Many-to-many between Face records through `face_suggestions` pivot table (ranked by similarity confidence)

### One-to-Many
- **Photos**: Owned by one user (but can belong to multiple albums)
- **Size Variants**: Multiple variants per photo
- **Access Permissions**: Multiple permissions per album
- **Faces**: Multiple detected faces per photo; multiple faces per Person

## Database Optimization

### Indexing Strategy

Proper indexing on frequently queried columns:
- Foreign keys (`owner_id`, `album_id`, `user_id`)
- Nested set boundaries (`_lft`, `_rgt`)
- Timestamps (`taken_at`, `created_at`)
- Visibility flags (`is_public`, `is_highlighted`)

### N+1 Query Prevention

Eager loading enforced with `Model::shouldBeStrict()`, which throws an exception if a relationship is accessed before being loaded.

## Related Documentation

- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure
- [Album Tree Structure](../4-architecture/album-tree-structure.md) - Nested set model implementation
- [Tag System](../4-architecture/tag-system.md) - Tag architecture and operations
- [Image Processing](image-processing.md) - Size variant generation and processing pipeline

## AI Vision Schema Additions (Feature 030)

### New Tables

| Table | Migration | Purpose |
|---|---|---|
| `persons` | `2026_03_21_000001_create_persons_table` | Identified individuals (name, user link, searchability) |
| `faces` | `2026_03_21_000002_create_faces_table` | Bounding boxes detected by AI Vision service |
| `face_suggestions` | `2026_03_21_000002_create_faces_table` | Ranked similarity pairs for identity suggestions |

### Column Added to `photos`

| Column | Type | Default | Purpose |
|---|---|---|---|
| `face_scan_status` | string(16), nullable | `null` | Tracks face detection lifecycle: `null` (not queued), `pending`, `scanned`, `failed` |

### New Config Keys (AI Vision category, `level = 1` / SE only)

| Key | Default | Description |
|---|---|---|
| `ai_vision_enabled` | `0` | Master toggle for the AI Vision subsystem |
| `ai_vision_face_enabled` | `0` | Enable facial recognition (requires master toggle) |
| `ai_vision_face_permission_mode` | `restricted` | Access control mode: `public`, `private`, `privacy-preserving`, `restricted` |
| `ai_vision_face_selfie_confidence_threshold` | `0.8` | Minimum confidence for selfie-based person claim |
| `ai_vision_face_person_is_searchable_default` | `1` | Default `is_searchable` for new Person records |
| `ai_vision_face_allow_user_claim` | `1` | Allow non-admin users to claim a Person profile |
| `ai_vision_face_scan_batch_size` | `200` | Photo IDs per bulk-scan job chunk |

---

*Last updated: March 22, 2026*
