# Lychee Smart Albums Documentation

Smart Albums are virtual, dynamically-generated albums in Lychee that automatically contain photos based on specific criteria. Unlike regular albums where photos are explicitly assigned, Smart Albums collect photos that match certain conditions like being highlighted, recently uploaded, or unassigned to any album.

## Overview

Smart Albums implement the `AbstractAlbum` interface but extend `BaseSmartAlbum` instead of being traditional Eloquent models. They are singleton classes that cannot be created or deleted by users - they always exist when enabled in the configuration.

### Key Characteristics

- **Virtual Albums**: No database storage for the album itself, only for photos
- **Dynamic Content**: Photos appear/disappear based on their properties
- **Singleton Pattern**: One instance per smart album type
- **Read-Only**: Cannot be created, deleted, or have photos manually added/removed
- **Configurable**: Can be enabled/disabled via configuration settings

## Architecture

### BaseSmartAlbum Logic

The `BaseSmartAlbum` class provides the common functionality for all smart albums:

#### Core Components

1. **Smart Photo Condition**: A closure that defines the filtering criteria
2. **Photo Query Policy**: Applies security and visibility filters
3. **Caching**: Results are cached for performance
4. **Thumbnail Generation**: Automatic thumbnail selection based on configuration

#### Key Methods

```php
// Constructor accepts the smart album type and filtering condition
protected function __construct(SmartAlbumType $id, \Closure $smart_condition)

// Returns photos matching the smart condition with security filters applied
public function photos(): Builder

// Cached photo retrieval with default sorting
protected function getPhotosAttribute(): LengthAwarePaginator

// Thumbnail generation with random or sorted selection
protected function getThumbAttribute(): ?Thumb
```

#### Security and Visibility

Smart Albums respect Lychee's security model through:

- **PhotoQueryPolicy**: Filters photos based on user permissions
- **Searchability Filter**: Ensures users only see photos they have access to
- **Sensitivity Filtering**: Respects the `hide_nsfw_in_smart_albums` configuration
- **Override Setting**: `SA_override_visibility` can bypass some security filters

## Smart Album Types

### 1. Recent Album (`RecentAlbum`)

**Purpose**: Contains recently uploaded photos based on their creation date.

**Filtering Logic**:
```php
$query->where('photos.created_at', '>=', $recent_cutoff_date);
```

**Configuration**:
- `enable_recent`: Enable/disable the Recent album
- `recent_age`: Number of days to consider "recent" (default varies)

**Behavior**: Shows photos uploaded within the configured number of days from the current date.

### 2. Highlighted Album (`HighlightedAlbum`)

**Purpose**: Contains all photos marked as highlighted by users.

**Filtering Logic**:
```php
$query->where('photos.is_highlighted', '=', true);
```

**Configuration**:
- `enable_highlighted`: Enable/disable the Highlighted album

**Behavior**: Shows all photos with the `is_highlighted` flag set to true, regardless of their album assignment.

### 3. On This Day Album (`OnThisDayAlbum`)

**Purpose**: Contains photos taken on the same month and day in previous years.

**Filtering Logic**:
```php
$query->where(fn (Builder $q) => $q
    ->whereMonth('photos.taken_at', '=', $today->month)
    ->whereDay('photos.taken_at', '=', $today->day))
->orWhere(fn (Builder $q) => $q
    ->whereNull('photos.taken_at')
    ->whereYear('photos.created_at', '<', $today->year)
    ->whereMonth('photos.created_at', '=', $today->month)
    ->whereDay('photos.created_at', '=', $today->day));
```

**Configuration**:
- `enable_on_this_day`: Enable/disable the On This Day album

**Behavior**: 
- Primary: Shows photos taken on this day in previous years (uses `taken_at` EXIF data)
- Fallback: For photos without EXIF date, uses upload date (`created_at`) from previous years

### 4. Unsorted Album (`UnsortedAlbum`) - Edge Case

**Purpose**: Contains photos that are not assigned to any regular album.

**Filtering Logic**:
```php
$query->whereNull(PA::ALBUM_ID); // Where album_id is null in the photo_album relationship
```

**Configuration**:
- `enable_unsorted`: Enable/disable the Unsorted album

**Special Behavior - The Edge Case**:

The `UnsortedAlbum` has unique authorization logic that differs from other smart albums:

```php
public function photos(): Builder
{
    if ($this->public_permissions !== null) {
        // If Unsorted album is made public, ALL unsorted photos become visible
        // This bypasses normal user ownership checks
        return Photo::query()
            ->leftJoin(PA::PHOTO_ALBUM, 'photos.id', '=', PA::PHOTO_ID)
            ->with(['size_variants', 'statistics', 'palette'])
            ->where($this->smart_photo_condition);
    }

    // Normal security filtering applies
    return parent::photos();
}
```

**Why It's an Edge Case**:

1. **Ownership Bypass**: When the Unsorted album is made public, it shows ALL unsorted photos from ALL users, not just the current user's photos
2. **Security Implications**: This can expose photos that users might not intend to be public
3. **Different Authorization**: Unlike other smart albums that always respect photo ownership, Unsorted can override this when public
4. **Administrative Feature**: Primarily intended for admin users to see all unorganized content in the system

### 5. Untagged Album (`UntaggedAlbum`)

**Purpose**: Contains photos that are not assigned to any tag.

**Filtering Logic**:
```php
$query->whereDoesntHave('tags');
```

**Configuration**:
- `enable_untagged`: Enable/disable the Untagged album

**Behavior**: Shows all photos that do not have any tags associated with them.

## Configuration Settings

Smart Albums can be individually enabled/disabled:

```php
// In Configs table
'enable_unsorted'     => true/false
'enable_highlighted'  => true/false  
'enable_recent'       => true/false
'enable_on_this_day'  => true/false
'enable_untagged'     => true/false

// Additional settings
'recent_age'                    => 30 (days)
'SA_override_visibility'        => false
'hide_nsfw_in_smart_albums'     => true
'SA_random_thumbs'              => false
```

## Implementation Details

### Singleton Pattern

Each smart album uses the singleton pattern to ensure only one instance exists:

```php
private ?self $instance = null;

public function getInstance(): self
{
    return self::$instance ??= new self();
}
```

### MimicModel Trait

Smart Albums use the `MimicModel` trait to behave like Eloquent models while not being stored in the database:

- Provides `__get()` method for dynamic property access
- Enables consistent API with regular albums
- Allows smart albums to be treated uniformly in the frontend

### Performance Considerations

- **Query Caching**: Photo results are cached after first retrieval
- **Lazy Loading**: Photos are only queried when actually accessed
- **Optimized Queries**: Use joins and indexes for efficient filtering
- **Thumbnail Caching**: Thumbnails are cached and can be randomized

### Internationalization

Smart album titles are automatically translated using Laravel's localization system:

```php
$this->title = __('gallery.smart_album.' . strtolower($id->name)) ?? $id->name;
```

Translation keys in `lang/{locale}/gallery.php`:
- `gallery.smart_album.unsorted`
- `gallery.smart_album.highlighted`
- `gallery.smart_album.recent`
- `gallery.smart_album.on_this_day`
- `gallery.smart_album.untagged`

## Usage in Application

Smart Albums appear alongside regular albums in the main gallery view when enabled. They:

1. Are fetched by the `Albums/Top.php` Action
2. Appear in the frontend with special smart album styling
3. Support the same photo operations (mostly) as regular albums
4. Respect user permissions and privacy settings
5. Can have their own access permissions via the `AccessPermission` model

The smart album system provides users with convenient ways to access their photos based on common organizational patterns without manual curation.

---

*Last updated: August 14, 2025*
