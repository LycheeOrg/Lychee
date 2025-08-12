# Lychee Policies Documentation

This document explains the authorization and access control system in Lychee, focusing on the distinction between regular Policies and Query Policies, and their roles in securing the application.

## Overview

Lychee implements a comprehensive authorization system using Laravel's Policy classes with custom extensions. The system is designed to handle both individual model authorization and complex query-level filtering for security and performance.

## Policy Types

### Regular Policies (Authorization Policies)

Regular policies handle **individual model authorization** and define what actions a user can perform on specific resources. These policies work at the **model instance level**.

**Files:**
- `AlbumPolicy.php` - Album-specific permissions
- `PhotoPolicy.php` - Photo-specific permissions  
- `UserPolicy.php` - User management permissions
- `UserGroupPolicy.php` - User group permissions
- `SettingsPolicy.php` - Application settings permissions
- `MetricsPolicy.php` - Metrics and analytics permissions
- `BasePolicy.php` - Base class with admin override logic

**Key Characteristics:**
- **Instance-based**: Work with specific model instances (e.g., "Can user X edit album Y?")
- **Action-oriented**: Define specific actions (view, edit, delete, upload, share)
- **Permission checking**: Used by controllers to verify if a user can perform an action
- **Gate integration**: Work with Laravel's Gate system for authorization

**Example Authorization Methods:**
```php
// AlbumPolicy.php
public function canEdit(User $user, AbstractAlbum|null $album): bool
{
    return $this->isOwner($user, $album) || 
           $this->hasEditPermission($user, $album);
}

public function canUpload(?User $user, AbstractAlbum|null $album): bool
{
    return $this->isOwner($user, $album) ||
        $album->current_user_permissions()?->grants_upload === true ||
        $album->public_permissions()?->grants_upload === true;
}
```

⚠️ **Important: User Type Implications**

Pay careful attention to the difference between `User?` (nullable) and `User` (non-nullable) in policy method signatures:

- **`?User $user`**: Allows both authenticated and guest users. The method must handle `null` values for unauthenticated requests.
- **`User $user`**: Requires an authenticated user. Laravel will automatically deny access if no user is authenticated.

```php
// Allows guests - must handle null user
public function canSee(?User $user, Album $album): bool
{
    if ($user === null) {
        // Handle guest user logic
        return $album->is_public && !$album->requires_link;
    }
    // Handle authenticated user logic
    return $this->isOwner($user, $album) || $album->is_public;
}

// Requires authentication - Laravel denies if user is null
public function canEdit(User $user, Album $album): bool
{
    // $user is guaranteed to be non-null here
    return $this->isOwner($user, $album);
}
```

**Admin Override via `before()` Method:**

All policies inherit from `BasePolicy` which implements a `before()` method that runs **before any specific policy check**:

```php
// BasePolicy.php
public function before(?User $user, $ability)
{
    if ($user?->may_administrate === true) {
        return true; // Admin bypass - skips all other checks
    }
    // Returns null/void - continues to specific policy method
}
```

This means admin users automatically pass all policy checks without executing the specific permission logic, providing a global override for administrative access.

**Admin-Only Actions:**

As a consequence of the admin override, a policy method can return `false` for all regular users while still being valid due to the admin bypass. This pattern effectively creates admin-only actions:

```php
// app/Policies/SettingsPolicy.php
public function canEdit(User $user): bool
{
    // Another admin-only action
    // Regular users: false, Admins: true (via before() override)
	return false;
}
```

This design pattern allows for clean separation between actions available to regular users and those restricted to administrators only.


### Query Policies (Query Filtering Policies)

Query policies handle **database query filtering** and define what data a user can see when querying collections. These policies work at the **query builder level**.

**Files:**
- `AlbumQueryPolicy.php` - Album query filtering and visibility
- `PhotoQueryPolicy.php` - Photo query filtering and visibility

**Key Characteristics:**
- **Query-based**: Work with query builders to filter results before execution
- **Collection filtering**: Define what records a user can see in listings
- **Performance-oriented**: Apply filters at database level for efficiency
- **Visibility control**: Implement complex visibility rules (public, shared, private)
- **Hierarchical awareness**: Handle nested album structures and inheritance

**Key Concepts:**
- **Visibility**: What albums/photos appear in listings
- **Accessibility**: What albums/photos a user can access directly
- **Reachability**: What albums can be reached through navigation
- **Browsability**: What albums can be found by "clicking around"
- **Searchability**: What photos appear in search results

## Regular Policies Deep Dive

### AlbumPolicy

The `AlbumPolicy` class manages permissions for album operations:

**Core Permission Methods:**
```php
const IS_OWNER = 'isOwner';
const CAN_SEE = 'canSee';
const CAN_ACCESS = 'canAccess';
const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
const CAN_DOWNLOAD = 'canDownload';
const CAN_DELETE = 'canDelete';
const CAN_UPLOAD = 'canUpload';
const CAN_EDIT = 'canEdit';
const CAN_SHARE = 'canShare';
```

**Permission Hierarchy:**
1. **Admin Override**: Admins have full access to everything
2. **Ownership**: Album owners have full control
3. **Shared Permissions**: Based on `AccessPermission` records
4. **Password Protection**: Albums can require passwords
5. **Public Access**: Based on `AccessPermission` records with a null `user_id`
   - **Direct Link Only**: When `is_link_required` is true, the album is public but not visible in listings - only accessible via direct link
   - **Full Public**: When `is_link_required` is false, the album is fully public, visible in listings, and browsable

**Session Management:**
- Unlocked albums are tracked in user sessions
- Password-protected albums remain unlocked during session
- Session key: `AlbumPolicy::UNLOCKED_ALBUMS_SESSION_KEY`

### PhotoPolicy

The `PhotoPolicy` class manages permissions for individual photos:

**Core Permission Methods:**
```php
const CAN_SEE = 'canSee';
const CAN_DOWNLOAD = 'canDownload';
const CAN_EDIT = 'canEdit';
const CAN_ACCESS_FULL_PHOTO = 'canAccessFullPhoto';
const CAN_DELETE_BY_ID = 'canDeleteById';
```

**Permission Logic:**
1. **Album-based Inheritance**: Photo permissions often inherit from parent album
2. **Direct Ownership**: Photo owners have control regardless of album
3. **Shared Album Access**: Photos in shared albums follow album permissions
4. **Public Photos**: Photos in public albums are publicly accessible

### BasePolicy

The `BasePolicy` provides common functionality:

**Admin Override Logic:**
```php
public function before(?User $user, $ability)
{
    if ($user?->may_administrate === true) {
        return true;
    }
}
```

All policies extend `BasePolicy` to inherit admin privilege override.

## Query Policies Deep Dive

### AlbumQueryPolicy

The `AlbumQueryPolicy` manages album query filtering with sophisticated visibility rules:

#### Core Filtering Methods

**1. Visibility Filtering (`applyVisibilityFilter`)**
- Determines which albums appear in listings
- Considers: ownership, sharing, public access, link requirements
- Used for: album trees, navigation menus

```php
public function applyVisibilityFilter(AlbumBuilder $query): AlbumBuilder
{
    // Admin users see everything
    if (Auth::user()?->may_administrate === true) {
        return $query;
    }
    
    // Apply visibility conditions based on:
    // - User ownership
    // - Shared permissions  
    // - Public access settings
    // - Link requirements
}
```

**2. Reachability Filtering (`applyReachabilityFilter`)**
- Determines which albums can be directly accessed
- Combines visibility and accessibility rules
- Used for: direct album access validation

**3. Browsability Filtering (`applyBrowsabilityFilter`)**
- Determines which albums can be reached through navigation
- Ensures entire path from root to album is accessible
- Used for: preventing access to orphaned albums

#### Key Concepts Explained

**The Three Levels of Album Access:**

1. **Visible**: Album appears in listings and trees (basic visibility), but might be protected by a password.
2. **Reachable**: Album can be accessed via direct link, but requires parent albums to be accessible (not private or blocked by is_link_required=true).
3. **Browsable**: Album can be navigated to through click-through from parent albums (requires entire path to be accessible and visible).

**Important Distinction:**
- **Reachability** depends on the album's own permissions AND its parent chain accessibility
- **Browsability** depends on the entire path from origin being both accessible AND visible (not blocked by private albums or is_link_required=true)

```
┌─ Album A (Private) ────────────────────────────────────┐
│ ┌─ Album B (Public, is_link_required = false) ────────┐│
│ │ ┌─ Album C (Public, is_link_required = false ) ────┐││
│ │ │ ┌─ Album D (Public, is_link_required = true ) ──┐│││
│ │ │ └───────────────────────────────────────────────┘│││
│ │ └──────────────────────────────────────────────────┘││
│ └─────────────────────────────────────────────────────┘│
└────────────────────────────────────────────────────────┘
```

**Access Analysis:**

**Via Direct Link (Anonymous User with specific URLs):**
- **Album A**: ❌ Not accessible (private album, user is not owner)
- **Album B**: ✅ Accessible (public album, direct link bypasses private parent A)
- **Album C**: ✅ Accessible (public album, direct link bypasses private parent A)
- **Album D**: ✅ Accessible (public album with is_link_required=true, accessible only via direct link)

**From Origin (Anonymous User Perspective):**
- **Album A**: ❌ Not visible, ❌ not reachable, ❌ not browsable (private)
- **Album B**: ❌ Not visible, ❌ not reachable, ❌ not browsable (parent A is private, blocks all access)
- **Album C**: ❌ Not visible, ❌ not reachable, ❌ not browsable (path blocked by private A)
- **Album D**: ❌ Not visible, ❌ not reachable, ❌ not browsable (path blocked by private A)

**From Album B Perspective (if user has been given direct link to B):**
- **Album A**: ❌ Not visible, ❌ not reachable, ❌ not browsable (private, user is not owner)
- **Album B**: ✅ Visible, ✅ reachable, ❌ not browsable (current location, accessible via direct link)
- **Album C**: ✅ Visible, ✅ reachable, ❌ not browsable (public child, parent B is accessible)
- **Album D**: ❌ Not visible, ❌ not reachable, ❌ not browsable (is_link_required=true makes parent C not accessible, breaking the chain)

**Key Insight: Parent Chain Dependency**
Reachability requires the entire parent chain to be accessible. If any parent has `is_link_required=true` or is private (and user lacks access), it breaks reachability for all descendants, even if those descendants are public.

**Access Level Hierarchy:**
```
Browsable ⊆ Reachable ⊆ Visible
```

- If an album is **browsable**, it must also be **reachable** and **visible**
- If an album is **reachable**, it must also be **visible**
- An album can be **visible** but not **reachable** (e.g., link-required albums)
- An album can be **reachable** but not **browsable** (e.g., child of private parent)

**Computed Access Permissions:**
- Dynamic JOIN with `access_permissions` table
- Aggregates user, user group, and public permissions
- Optimizes permission checking at database level

```php
private function getComputedAccessPermissionSubQuery(): BaseBuilder
{
    // Creates subquery that computes effective permissions
    // by combining user permissions, group permissions, and public settings
}
```

### PhotoQueryPolicy

The `PhotoQueryPolicy` manages photo query filtering with album-aware logic:

#### Core Filtering Methods

**1. Visibility Filtering (`applyVisibilityFilter`)**
- Determines which photos appear in listings
- Considers: photo ownership, album accessibility, public access
- Used for: photo galleries, recent photos

```php
public function applyVisibilityFilter(FixedQueryBuilder $query): FixedQueryBuilder
{
    // Admin users see everything
    if (Auth::user()?->may_administrate === true) {
        return $query;
    }
    
    // Apply visibility based on:
    // - Direct photo ownership
    // - Album accessibility (via AlbumQueryPolicy)
    // - Public photo settings
}
```

**2. Searchability Filtering (`applySearchabilityFilter`)**
- Determines which photos appear in search results
- Restricts search scope to accessible albums
- Handles album hierarchy constraints

```php
public function applySearchabilityFilter(
    FixedQueryBuilder $query, 
    ?Album $origin = null, 
    bool $include_nsfw = true
): FixedQueryBuilder
```

**3. Sensitivity Filtering (`applySensitivityFilter`)**
- Filters out photos in sensitive albums
- Respects user preferences and album settings
- Handles recursive sensitivity (sensitive parent albums)

#### Advanced Features

**Album Hierarchy Integration:**
```php
// Photos must be in albums that form an unbroken accessible path
$query->whereNotExists(function (BaseBuilder $q) {
    $this->album_query_policy->appendUnreachableAlbumsCondition($q);
});
```

**Root Level Photos:**
- Special handling for photos not in albums (root level)
- Different permission rules apply
- Owner-only or public access based on configuration

**Performance Optimizations:**
```php
private function prepareModelQueryOrFail(
    FixedQueryBuilder $query, 
    bool $add_albums, 
    bool $add_base_albums
): void
{
    // Automatically joins necessary tables
    // - photo_albums (for album relationships)
    // - albums (for hierarchy data)
    // - base_albums (for album metadata)
    // - computed_access_permissions (for permission data)
}
```

## Usage Patterns

### In Controllers

**Regular Policies:**
```php
// Check if user can edit specific album
Gate::authorize(AlbumPolicy::CAN_EDIT, $album);

// Check if user can upload to album
if (Gate::denies(AlbumPolicy::CAN_UPLOAD, $album)) {
    throw new UnauthorizedException();
}
```

**Query Policies:**
```php
// Get albums visible to current user
$albums = Album::query()
    ->when(true, fn($q) => $album_query_policy->applyVisibilityFilter($q))
    ->get();

// Search photos with restrictions
$photos = Photo::query()
    ->when(true, fn($q) => $photo_query_policy->applySearchabilityFilter($q, $origin))
    ->where('title', 'like', "%{$search}%")
    ->get();
```

### In Relationships

Query policies are automatically applied in custom relationship classes:

```php
// HasManyChildAlbums.php
public function addConstraints()
{
    if (static::$constraints) {
        parent::addConstraints();
        $this->album_query_policy->applyVisibilityFilter($this->getRelationQuery());
    }
}
```

## Security Considerations

### Defense in Depth

1. **Controller Level**: Regular policies check specific actions
2. **Query Level**: Query policies filter data at source
3. **Relationship Level**: Automatic filtering in model relationships
4. **Frontend Level**: UI hides unauthorized options

### Performance Impact

**Query Policies Benefits:**
- Filter data at database level (efficient)
- Reduce memory usage by not loading unauthorized data
- Enable complex permission logic in single queries

**Potential Concerns:**
- Complex JOIN operations for permission checking
- Nested subqueries for hierarchy validation
- Can impact query performance with large datasets

