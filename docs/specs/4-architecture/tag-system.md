# Tag System Architecture

This document outlines the implementation of the tag system in Lychee, focusing on the data models, relationships, custom queries, and operations like renaming and merging.

---

## 1. Data Model and Relationships

### 1.1 The Tag Model

The `Tag` model (`app/Models/Tag.php`) represents a simple tagging entity with minimal properties:

```php
class Tag extends Model
{
    // No timestamps
    public $timestamps = false;
    
    // Properties
    protected $fillable = [
        'name',          // The tag text
        'description',   // Optional description
    ];
    
    // Relationships
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(
            Photo::class,
            'photos_tags',  // pivot table
            'tag_id',       // foreign key in pivot
            'photo_id'      // related key in pivot
        );
    }
}
```

The model includes a helper method `Tag::from(array $tags)` that:
1. Sanitizes tag names (trims whitespace, removes empty values)
2. Finds existing tags in the database
3. Creates any missing tags in bulk
4. Returns a collection of all tag objects

This method is critical for maintaining tag consistency across the application.

### 1.2 Photo-Tag Relationship

Photos maintain a many-to-many relationship with tags through the `photos_tags` pivot table:

```php
// In Photo model
public function tags(): BelongsToMany
{
    return $this->belongsToMany(
        Tag::class,
        'photos_tags',   // pivot table
        'photo_id',      // this model key
        'tag_id',        // related model key
    );
}
```

### 1.3 Tag Albums

Tag Albums (`app/Models/TagAlbum.php`) are virtual collections of photos sharing specific tags. A TagAlbum:
- Extends `BaseAlbum`
- Contains a `BelongsToMany` relationship to `Tag` via `tag_albums_tags` pivot table
- Implements a custom `photos()` relation that fetches photos containing all assigned tags

## 2. Custom Tag Album Photo Query

The `HasManyPhotosByTag` relation implements a sophisticated filtering mechanism to return only photos that contain *all* tags specified in a tag album:

```php
$tag_count = count($tags_ids);
$query->whereExists(fn (BaseBuilder $q) => $q->select(['photo_id', DB::raw('COUNT(tag_id) AS num')])
    ->from('photos_tags')
    ->whereIn('photos_tags.tag_id', $tags_ids)
    ->whereColumn('photos_tags.photo_id', 'photos.id')
    ->groupBy('photos_tags.photo_id')
    ->havingRaw('COUNT(DISTINCT photos_tags.tag_id) = ?', [$tag_count])
);
```

This query:
1. Counts how many tags are assigned to the album (`$tag_count`)
2. Creates a subquery that selects photos from the pivot table
3. Groups these by photo ID and counts matched tags
4. Uses `HAVING` to ensure the photo has exactly the number of required tags
5. The outer `whereExists` connects this to the main photos query

This approach implements a logical AND between tags rather than OR, meaning photos must have all specified tags to appear in the album.


## 3. Tag Operations and User Isolation

Tag operations are handled by action classes:
- **ListTags**: Returns all tags accessible to the current user
- **GetTagWithPhotos**: Retrieves a tag with its associated photos
- **EditTag**: Handles tag renaming using a merge strategy
- **DeleteTag**: Removes tag associations and cleans up orphaned tags
- **MergeTag**: Combines two tags while preserving user contexts

#### Renaming strategy

Renaming a tag might seem as simple as updating the `name` column on the existing record, but since tags are shared across all users and contexts, a direct rename would unintentionally change the tag for everyone. Instead, Lychee performs a merge-based rename scoped to the current user:

1. **Create or find** a new tag with the target name (so as not to overwrite the original).
2. **Transfer only your own** photo and tag-album associations from the old tag to the new one.
3. **Remove** the old associations and, if the old tag has no more links, delete it via `TagCleanupTrait`.

This approach ensures:
- **Multi-user safety**: Each user's tag context is preserved
- **Collision avoidance**: avoids merging meanings when different users use the same tag name for different contexts.
- **Database efficiency**: Unused tags are automatically pruned.

## 4. Tag Cleanup

The `TagCleanupTrait` provides automatic cleanup of unused tags:

```php
// Simplified from TagCleanupTrait
public function cleanupUnusedTags(): int
{
    return Tag::whereNotExists(fn ($q) => $q->select(DB::raw(1))
        ->from('photos_tags')
        ->whereColumn('photos_tags.tag_id', 'tags.id'))
    ->whereNotExists(fn ($q) => $q->select(DB::raw(1))
        ->from('tag_albums_tags')
        ->whereColumn('tag_albums_tags.tag_id', 'tags.id'))
    ->delete();
}
```

This trait is used by operations like merge and delete to maintain database efficiency.

---

*Last updated: December 22, 2025*
