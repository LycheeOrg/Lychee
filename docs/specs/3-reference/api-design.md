# API Design

This document describes Lychee's RESTful API architecture, including endpoint design, authentication mechanisms, authorization patterns, and response structure.

---

## Overview

Lychee exposes a RESTful API for all operations, providing a clean and consistent interface for managing photos, albums, users, and system configuration. The API follows REST principles with resource-based endpoints and standard HTTP methods.

## RESTful API Design

### Endpoint Structure

API endpoints follow RESTful conventions:

```php
// routes/api_v2.php
Route::get('/Albums', [AlbumController::class, 'index']);      // List albums
Route::post('/Albums', [AlbumController::class, 'create']);    // Create album
Route::patch('/Albums', [AlbumController::class, 'update']);   // Update album
Route::delete('/Albums', [AlbumController::class, 'delete']);  // Delete album
```

### HTTP Methods

- **GET**: Retrieve resources
- **POST**: Create new resources
- **PATCH**: Update existing resources
- **DELETE**: Remove resources

## Authentication & Authorization

### Multi-layered Security

Lychee implements multiple authentication mechanisms:

1. **Session-based Authentication**: Traditional web sessions for browser access
2. **Token-based Authentication**: API tokens for external access
3. **OAuth**: Third-party authentication providers
4. **WebAuthn**: Passwordless authentication support

### Authorization Policies

Granular permissions using Laravel Policies:

```php
// app/Policies/AlbumPolicy.php
class AlbumPolicy
{
    public function view(User $user, Album $album): bool
    {
        return $user->can_edit || $album->is_public || $album->owner_id === $user->id;
    }
}
```

**Key Authorization Concepts:**

- **Policies**: Define authorization logic for model operations (view, create, update, delete)
- **Query Policies**: Filter database queries based on user permissions
- **Access Permissions**: Granular control for album and photo sharing

For comprehensive documentation about authorization patterns, including the distinction between regular Policies and Query Policies, see [app/Policies/README.md](../../../app/Policies/README.md).

## Response Patterns

### Consistent Response Structure

Lychee maintains consistent API responses:

- **Success responses**: Return appropriate HTTP status codes (200, 201, 204)
- **Resource classes**: Ensure consistent data structure across endpoints
- **Error responses**: Use standardized exception handling

### Resource Serialization

Instead of Laravel's JsonResource, Lychee uses **Spatie Data** for type-safe response serialization:

```php
// app/Http/Resources/Models/AlbumResource.php
class AlbumResource extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $parent_id,
        public ?string $description,
        public ?string $thumb_id,
        public int $photo_count,
        public bool $is_public,
        public bool $is_shared,
        // ...
    ) {}
}
```

**Benefits:**
- Type-safe response serialization
- Automatic TypeScript type generation
- Better IDE support and autocompletion
- Compile-time validation

### Type Safety

TypeScript types are automatically generated from PHP Resource classes:

```bash
php artisan typescript:transform
```

This ensures frontend and backend stay in sync with strongly-typed interfaces.

### Standard Response Codes

| Code | Meaning | Usage |
|------|---------|-------|
| 200 | OK | Successful GET, PATCH, DELETE with response body |
| 201 | Created | Successful POST with new resource |
| 204 | No Content | Successful operation with no response body |
| 400 | Bad Request | Validation failed or malformed request |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Authenticated but not authorized |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation errors with details |
| 500 | Internal Server Error | Server-side error |

## Request Validation

All API requests use dedicated Request classes for validation:

```php
// app/Http/Requests/Album/CreateAlbumRequest.php
class CreateAlbumRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'parent_id' => 'sometimes|string',
            'description' => 'sometimes|string|max:1000',
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', Album::class);
    }
}
```

**Request Lifecycle:**

1. **Validation**: `rules()` validates input data
2. **Processing**: `processValidatedValues()` transforms validated data
3. **Authorization**: `authorize()` checks permissions (properties from step 2 are available)
4. **Controller**: Validated and authorized request reaches controller

For comprehensive documentation about custom validation rules, see [app/Rules/README.md](../../../app/Rules/README.md).

## API Versioning

Lychee uses route-based versioning:

- **v2 API**: Current version (`/api/v2/...`), Array-of-Structs (AoS) response convention — collection endpoints return arrays of self-contained objects (see `PaginatedPhotosResource`/`PaginatedAlbumsResource` below).
- **v3 API**: Greenfield surface (`/api/v3/...`), additive and coexisting with v2 — nothing in v2 is deprecated or changed by v3's introduction. v3 establishes a Struct-of-Arrays (SoA) response convention for future *collection* endpoints (ADR-0009), though the first v3 endpoint below is single-item and doesn't need it.
- Future versions can be added without breaking existing integrations.

### API v3: Photo Asset Retrieval

**GET** `/api/v3/Photo/{photo_id}/Asset/{size_variant}`

Retrieves a single photo size-variant's binary file (thumbnail through original), watermark-aware. Registered via `routes/api_v3.php` (`App\Http\Controllers\Gallery\PhotoAssetController::show()`, `App\Http\Requests\Photo\GetPhotoAssetRequest`), under the `api` middleware group but with `accept_content_type:json`/`content_type:json` opted out of on this route specifically (binary passthrough, not JSON) — a `json_errors` middleware (`App\Http\Middleware\EnsureJsonErrorResponses`) still forces every *error* response to render as Lychee's standard JSON error body regardless of the caller's actual `Accept` header.

**Path parameters:**

| Parameter | Type | Description |
|-----------|------|--------------|
| `photo_id` | string | The photo's ID (`RandomIDRule`) |
| `size_variant` | string | A `SizeVariantType` case name, case-insensitive (`raw`, `original`, `medium2x`, `medium`, `small2x`, `small`, `thumb2x`, `thumb`, `placeholder`) |

**Headers (temporary-link mode, optional but paired):**

| Header | Type | Description |
|--------|------|-------------|
| `X-Timestamp` | integer | Unix seconds the link was signed at |
| `X-Mac` | string | Hex HMAC-SHA256 of `X-Timestamp`, keyed by `config('app.key')` (`App\Services\TemporaryLinkSigner`) |

Two access modes, both always gated by `PhotoPolicy` (`CAN_SEE` for thumbnail-class variants, `CAN_ACCESS_FULL_PHOTO` for full-resolution variants) — a deliberate strengthening over v2's `SecurePathController`, which enforces no application-level policy at all:

1. **Authenticated (session)**: no headers needed, unless `signatureRequired()` (config-driven, see below) says otherwise for this caller.
2. **Unauthenticated (temporary link)**: guests are only ever authorized via a valid, unexpired `X-Timestamp`/`X-Mac` pair; reuses `temporary_image_link_enabled`/`_when_logged_in`/`_when_admin`/`_life_in_seconds` (no new config keys).

**Response codes:**

| Code | Meaning |
|------|---------|
| 200 | Binary file streamed directly (local-disk size variant) |
| 302 | Redirect to a native S3 temporary URL (S3-backed size variant, no proxying through Lychee) |
| 401 | No/invalid/expired/future-dated temporary-link signature, or session insufficient per config |
| 403 | `PhotoPolicy` denies the resolved caller |
| 404 | Unknown `photo_id`, or the photo has no `SizeVariant` row of the requested type |
| 422 | Unrecognized `size_variant` token, or only one of `X-Timestamp`/`X-Mac` present |

### API v3: Album Listing

**GET** `/api/v3/Albums`

The first v3 endpoint to realize the Struct-of-Arrays (SoA) collection convention (ADR-0009): a single, cacheable, rights-curated, flat, unpaginated album listing, registered via `routes/api_v3.php` (`App\Http\Controllers\Gallery\AlbumListController::index()`, `App\Http\Requests\Gallery\AlbumListV3Request`). Built for three future (separately-scoped) frontend consumers — the Move-target dropdown, the Fix Tree admin page, and the Bulk Album Edit admin page — none of which are wired up by this endpoint itself (backend contract only). Result set is curated via `AlbumQueryPolicy::applyVisibilityFilter()` (visibility, not reachability — a password-protected-but-not-unlocked album still appears if otherwise visible), ordered by `albums._lft` ascending, queried via `Illuminate\Database\Eloquent\Builder::toBase()` for zero Eloquent hydration overhead, and cached through `App\Services\Cache\ManagedCacheService` keyed by user identity and the exact flag combination requested (`managed_cache_enabled`/`managed_cache_albums_enabled`/`managed_cache_ttl`, no new config).

**Query parameters:**

| Parameter | Type | Default | Description |
|-----------|------|---------|--------------|
| `with_parent_id` | boolean | `false` | Adds `parent_ids` to the response. Requires `may_administrate === true`. |
| `for_bulk_edit` | boolean | `false` | Adds the `bulk_edit` block (full `BulkAlbumResource` field parity). Requires `may_administrate === true`. |

**Response:** `AlbumListResource` (Struct-of-Arrays, all arrays index-aligned, never paginated)
```json
{
  "ids": ["abc123", "def456"],
  "titles": ["Vacation 2025", "Sub-album"],
  "lft": [1, 2],
  "rgt": [4, 3],
  "cover_ids": ["photo123", null],
  "parent_ids": null,
  "bulk_edit": null
}
```

`cover_ids[i]` resolves per the same priority rule as `App\Relations\HasAlbumThumb::getCoverTypeForAlbum()`: explicit `cover_id`, else `auto_cover_id_max_privilege` for an admin/owner viewer, else `auto_cover_id_least_privilege`, else `null` (no live legacy-fallback query). `parent_ids` is `null` (the whole array, not just entries) when `with_parent_id=false`; when `true`, it is index-aligned with a per-entry `null` for a root album (never omitted). `bulk_edit` is `null` when `for_bulk_edit=false`; when `true`, it is an `AlbumListBulkEditFieldsResource` — its own SoA block, index-aligned to the outer arrays, with full `BulkAlbumResource` field parity (owner, license, sorting, timelines, NSFW/public/link-required/grants flags, `created_at`).

**Response codes:**

| Code | Meaning |
|------|---------|
| 200 | Curated listing returned (empty arrays, not an error, when nothing is visible) |
| 403 | `with_parent_id=true` or `for_bulk_edit=true` requested by a non-admin caller |
| 422 | `with_parent_id`/`for_bulk_edit` present but not boolean-parseable |

## Pagination Endpoints

Lychee implements offset-based pagination for albums and photos to efficiently handle large collections. Three dedicated endpoints allow incremental data loading:

### Album Head Endpoint

**GET** `/api/v2/Album::head`

Returns album metadata without loading children or photos. Lightweight endpoint for initial album information.

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| album_id | string | Yes | Album ID (supports regular, Smart, and Tag albums) |

**Response:** `HeadAlbumResource`
```json
{
  "id": "abc123",
  "title": "Vacation 2025",
  "description": "Summer vacation photos",
  "num_photos": 450,
  "num_children": 12,
  "thumb": {
    "id": "photo123",
    "type": "photo",
    "thumb": "https://...",
    "thumb2x": "https://..."
  },
  "rights": {
    "can_edit": true,
    "can_share": true,
    "can_download": true
  }
}
```

**Response Codes:**
| Code | Description |
|------|-------------|
| 200 | Success |
| 403 | Forbidden - User lacks access to album |
| 404 | Not Found - Album does not exist |

### Paginated Albums Endpoint

**GET** `/api/v2/Album::albums`

Returns paginated child albums for a parent album.

**Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| album_id | string | Yes | - | Parent album ID |
| page | integer | No | 1 | Page number (1-indexed) |

**Response:** `PaginatedAlbumsResource`
```json
{
  "data": [
    {
      "id": "album1",
      "title": "Beach",
      "num_photos": 45,
      "thumb": {...}
    }
  ],
  "current_page": 1,
  "last_page": 2,
  "per_page": 30,
  "total": 42
}
```

**Response Codes:**
| Code | Description |
|------|-------------|
| 200 | Success (empty data array if page exceeds available pages) |
| 403 | Forbidden - User lacks access to album |
| 404 | Not Found - Album does not exist |
| 422 | Unprocessable Entity - Invalid page parameter |

### Paginated Photos Endpoint

**GET** `/api/v2/Album::photos`

Returns paginated photos for an album. Supports regular albums, Smart albums (Recent, Highlighted, etc.), and Tag albums.

**Parameters:**
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| album_id | string | Yes | - | Album ID (regular, Smart, or Tag album) |
| page | integer | No | 1 | Page number (1-indexed) |

**Response:** `PaginatedPhotosResource`
```json
{
  "data": [
    {
      "id": "photo1",
      "title": "Beach sunset",
      "taken_at": "2025-07-15T18:30:00Z",
      "size_variants": {...},
      "tags": ["vacation", "sunset"]
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 100,
  "total": 450,
  "timeline": {...}
}
```

**Response Codes:**
| Code | Description |
|------|-------------|
| 200 | Success (empty data array if page exceeds available pages) |
| 403 | Forbidden - User lacks access to album |
| 404 | Not Found - Album does not exist |
| 422 | Unprocessable Entity - Invalid page parameter |

### Pagination Configuration

Page sizes and UI modes are configurable via the admin settings panel or directly in the `configs` table:

| Config Key | Type | Default | Description |
|------------|------|---------|-------------|
| albums_per_page | integer (1-1000) | 30 | Number of child albums per page |
| photos_per_page | integer (1-1000) | 100 | Number of photos per page |
| albums_pagination_ui_mode | enum | infinite_scroll | UI mode for album pagination |
| photos_pagination_ui_mode | enum | infinite_scroll | UI mode for photo pagination |
| albums_infinite_scroll_threshold | integer | 2 | Viewport heights from bottom to trigger album loading |
| photos_infinite_scroll_threshold | integer | 2 | Viewport heights from bottom to trigger photo loading |

**UI Mode Options:**
- `infinite_scroll` - Auto-load next page on scroll (default)
- `load_more_button` - Manual "Load More" button
- `page_navigation` - Traditional page number navigation

### Pagination Best Practices

1. **Initial Load:** Call all three endpoints in parallel when opening an album:
   - `/Album::head` for metadata
   - `/Album::albums?page=1` for first page of children
   - `/Album::photos?page=1` for first page of photos

2. **Incremental Loading:** Use the `last_page` field to determine if more pages exist before requesting.

3. **Empty Results:** Requesting a page beyond available data returns an empty `data` array with correct `total` count.

4. **Backward Compatibility:** The legacy `/Album` endpoint remains unchanged and returns full album data without pagination.

## Related Documentation

- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure
- [Request Lifecycle: Album Creation](../4-architecture/request-lifecycle-album-creation.md) - End-to-end album creation
- [Request Lifecycle: Photo Upload](../4-architecture/request-lifecycle-photo-upload.md) - Photo upload process

---

*Last updated: February 24, 2026*
