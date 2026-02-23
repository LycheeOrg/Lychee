# Albums

This document explains the album architecture in Lychee, including regular albums, tag albums, and smart albums.

## Table of Contents

- [Album Architecture](#album-architecture)
- [Base Albums (BaseAlbumImpl)](#base-albums-basealbu mimpl)
- [Regular Albums](#regular-albums)
- [Tag Albums](#tag-albums)
- [Smart Albums](#smart-albums)
- [Album Type Comparison](#album-type-comparison)
- [Common Album Features](#common-album-features)

---

## Album Architecture

Lychee uses a sophisticated album architecture with **two distinct album hierarchies**:

**Persistent Albums (BaseAlbum hierarchy):**
- Both **Album** and **TagAlbum** extend `BaseAlbum`
- Both share the underlying logic in `BaseAlbumImpl` via composition
- Stored in database tables with shared foundation in `base_albums`
- Inherit common features: ownership, permissions, photo sorting, cover photos

**Virtual Albums (BaseSmartAlbum hierarchy):**
- **SmartAlbum** extends `BaseSmartAlbum` (separate inheritance tree)
- System-generated, non-persistent, query-driven
- No database storage, generated dynamically
- Different architecture from persistent albums

---

## Base Albums (BaseAlbumImpl)

**What is a Base Album?**
- The **shared foundation** for **Album** and **TagAlbum** (persistent albums)
- Stored in the `base_albums` database table
- Contains properties and relationships common to both album types
- Acts as the "parent class" implementation via composition pattern
- **Note:** SmartAlbum uses a different hierarchy (`BaseSmartAlbum`), not this one

**Shared Properties (both Album and TagAlbum inherit these):**
- **Core**: id, title, description, created_at, updated_at, published_at
- **Ownership**: owner_id, copyright
- **Display**: photo_layout, photo_timeline, is_nsfw, is_pinned
- **Sorting**: Photo sorting column and order
- **Relationships**: owner, access_permissions, shared_with, statistics

**Composition Pattern:**
- Laravel/Eloquent doesn't support class-table inheritance
- Both `Album` and `TagAlbum` extend `BaseAlbum`
- `BaseAlbum` forwards method calls to `BaseAlbumImpl` (composition over inheritance)
- This avoids code duplication while sharing behavior between Album and TagAlbum

---

## Regular Albums

### Album extends BaseAlbum

**What is a Regular Album?**
- **User-created** collections with full control
- **Extends BaseAlbum**, shares logic via BaseAlbumImpl composition
- Support **hierarchical organization** via nested-tree structure
- Can contain both photos and child albums
- Most common album type for user content

**Regular Album-Specific Properties:**
- **Hierarchy**: parent_id, _lft, _rgt (nested set model)
- **Visual**: cover_id, header_id, license
- **Layout**: album_thumb_aspect_ratio, album_timeline
- **Album sorting**: Order child albums by various criteria
- **Track**: Optional audio track for slideshow

### Nested Tree Structure

```
Root (no parent)
├─ Vacation 2024
│  ├─ Paris (parent: Vacation 2024)
│  │  ├─ Day 1 (parent: Paris)
│  │  └─ Day 2 (parent: Paris)
│  └─ Rome (parent: Vacation 2024)
└─ Work Portfolio
   ├─ Client A
   └─ Client B
```

**Tree Implementation:**
- Uses **nested set model** (`_lft` and `_rgt` boundaries)
- Implements `Node` interface via `NodeTrait`
- Enables efficient queries: ancestors, descendants, siblings
- Supports moving entire subtrees atomically
- Maintains referential integrity automatically

**Regular Album Relationships:**
- `photos()` - Direct child photos (not recursive)
- `all_photos()` - All photos including from sub-albums (recursive)
- `children()` - Direct child albums
- `descendants()` - All nested child albums (recursive)
- `parent()` - Parent album (null for root albums)
- `cover()` - Featured cover photo
- `header()` - Banner header photo

---

## Tag Albums

### TagAlbum extends BaseAlbum

**What is a Tag Album?**
- **Dynamic collections** based on photo tags
- **Extends BaseAlbum**, shares logic via BaseAlbumImpl composition
- Query-driven, not manually curated
- Automatically update when photo tags change
- No hierarchical nesting (flat structure)

**Tag Album-Specific Behavior:**
- Show photos matching specific tag criteria
- Tags stored as separate entities (not in album)
- Many-to-many relationship: photos ↔ tags
- Read-only collection (cannot directly add/remove photos)

**Differences from Regular Albums:**
- **No nesting**: Cannot have parent or child albums
- **No tree structure**: No _lft, _rgt, parent_id
- **Dynamic membership**: Photos included via tag queries
- **Tag-based**: Defined by which tags to show

**Tag Album Example:**
```
TagAlbum "Landscapes"
  → Shows: All photos tagged with "landscape"
  → Updates automatically when photos are tagged/untagged
  → No manual photo management needed
```

### How Tag Albums Work

**Tag Matching:**
- Display photos matching one or more tags
- Tags are stored as proper database entities with many-to-many relationships
- Photos link to tags via the `photos_tags` pivot table

**Dynamic Updates:**
- Adding/removing tag associations automatically updates tag albums
- No manual curation needed

**Example:**
```
Tag Album: "landscape, sunset"
  → Shows all photos linked to both "landscape" AND "sunset" tags

Tag Album: "portrait"
  → Shows all photos linked to the "portrait" tag
```

**Tag Storage:**
- Tags are **not** comma-separated strings on the Photo model
- Each tag is a separate entity in the `tags` table
- The `tags()` relationship returns a `Collection<int,Tag>` via many-to-many
- This allows proper querying, counting, and indexing

### Tag Album Features

- **Permission-aware**: Only show photos user can access
- **Shareable**: Can grant access like regular albums (via BaseAlbumImpl)
- **No nesting**: Cannot contain child albums (flat structure)
- **User-created**: Unlike Smart Albums, users define which tags to show
- **Dynamic content**: Photos managed via tag associations, not album membership
- **Cover photo**: Can set featured cover image (inherited from BaseAlbumImpl)

### Differences from Regular Albums

| Feature | Regular Album | Tag Album |
|---------|---------------|-----------|
| **Structure** | Hierarchical (nested tree) | Flat (no nesting) |
| **Photo membership** | Many-to-many via `photo_album` | Query-driven via tags |
| **Manual curation** | ✅ Add/remove photos directly | ❌ Via tag management |
| **Contains albums** | ✅ Can have child albums | ❌ No children |
| **User-created** | ✅ Yes | ✅ Yes |
| **Access control** | ✅ Via AccessPermission | ✅ Via AccessPermission |

---

## Smart Albums

### SmartAlbum extends BaseSmartAlbum

**Different Hierarchy:**
- **SmartAlbum extends BaseSmartAlbum** (not BaseAlbum)
- Separate inheritance tree from Album/TagAlbum
- Does **not** share BaseAlbumImpl logic
- System-generated, non-persistent, query-driven

**What are Smart Albums?**
Virtual collections that don't persist to database tables but are generated dynamically by the system:

- **Recent**: Recently uploaded/modified photos
- **Highlighted**: User's favorited photos  
- **Public**: All publicly accessible photos
- **Unsorted**: Photos not in any user album
- **On This Day**: Photos from this date in past years

**Key Differences from Album/TagAlbum:**
- Different class hierarchy (`BaseSmartAlbum` vs `BaseAlbum`)
- No database storage (generated on-the-fly)
- System-defined (users cannot create custom Smart Albums)
- No shared logic with persistent albums

### Built-in Smart Albums

**Recent:**
- Photos uploaded or modified recently
- Configurable time window
- Dynamic query-based

**Highlighted:**
- Photos marked as favorites (`is_highlighted = true`)
- User-specific (only your highlighted photos)
- Quick access to best shots

**Public:**
- All publicly accessible photos
- Shows what guests/public can see
- Useful for reviewing public content

**Unsorted:**
- Photos not assigned to any Regular Album
- Helps identify photos needing organization
- Excludes photos in user-created albums

**On This Day:**
- Photos taken on this date in previous years
- Nostalgic daily memories
- Based on `taken_at` date matching

### Smart Album Characteristics

- **Non-persistent**: Not stored in database, generated dynamically
- **Read-only**: Cannot directly add/remove photos
- **Query-driven**: Content determined by system queries
- **User-specific**: Show content based on user permissions
- **No nesting**: Cannot contain child albums
- **No access permissions**: Use built-in visibility logic

---

## Album Type Comparison

| Feature | BaseAlbumImpl (foundation) | Regular Album | Tag Album | Smart Album |
|---------|-----------|---------------|-----------|-------------|
| **Extends** | N/A (composition pattern) | BaseAlbum | BaseAlbum | BaseSmartAlbum |
| **Database Table** | base_albums | albums (+ base_albums) | tag_albums (+ base_albums) | None (virtual) |
| **User Created** | N/A (internal) | ✅ Yes | ✅ Yes | ❌ System-only |
| **Hierarchical** | N/A | ✅ Yes (nested tree) | ❌ No (flat) | ❌ No |
| **Contains Photos** | N/A | ✅ Many-to-many | ✅ Query-driven | ✅ Query-driven |
| **Contains Albums** | N/A | ✅ Yes (children) | ❌ No | ❌ No |
| **Cover Photo** | N/A | ✅ Yes | ✅ Yes | ❌ No |
| **Access Control** | ✅ Provides | ✅ Inherited from BaseAlbumImpl | ✅ Inherited from BaseAlbumImpl | ❌ System logic |
| **Photo Sorting** | ✅ Provides | ✅ Inherited from BaseAlbumImpl | ✅ Inherited from BaseAlbumImpl | ✅ System-defined |
| **Dynamic Content** | N/A | ❌ Static | ✅ Tag-based | ✅ System queries |
| **Shares BaseAlbumImpl** | N/A (is BaseAlbumImpl) | ✅ Yes | ✅ Yes | ❌ No (uses BaseSmartAlbum) |

---

## Common Album Features

All album types (Regular and Tag) share these inherited features from BaseAlbumImpl:

**Core Properties:**
- **ID**: Unique string identifier
- **Title**: Display name (required)
- **Description**: Optional detailed text
- **Owner**: User who created the album
- **Created/Updated**: Timestamps for creation and modification
- **Published at**: Optional publication date

**Visual Customization:**
- **Photo layout**: Timeline, grid, or masonry view
- **Photo timeline**: Timeline granularity (year, month, day)
- **Cover photo**: Featured image representing the album
- **Thumb**: Thumbnail variant for grid display

**Sorting & Display:**
- **Photo sorting**: Order photos by date, title, description, star, etc.
- Inherited by all album types via BaseAlbumImpl

**Protection:**
- **NSFW flag**: Mark sensitive content
- **is_pinned**: Pin album to top of listings
- **Copyright**: Copyright information
- **Access permissions**: Via AccessPermission relationship (see [Permissions](permissions.md))

**Regular Album-Only Features:**
- **Parent/children**: Hierarchical nesting
- **License**: Photo license type (defaults to system setting)
- **Header image**: Banner image for album view
- **Album sorting**: Order child albums
- **Aspect ratio**: Display ratio for album thumbnails
- **Track**: Audio track for slideshow
- **Purchasable**: E-commerce integration (webshop)

---

**Related:** [Photos](photos.md) | [Permissions](permissions.md) | [Users](users.md)

---

*Last updated: December 22, 2025*
