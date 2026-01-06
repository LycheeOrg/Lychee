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

- **v2 API**: Current version (`/api/v2/...`)
- Future versions can be added without breaking existing integrations

## Related Documentation

- [Backend Architecture](../4-architecture/backend-architecture.md) - Overall backend structure
- [Request Lifecycle: Album Creation](../4-architecture/request-lifecycle-album-creation.md) - End-to-end album creation
- [Request Lifecycle: Photo Upload](../4-architecture/request-lifecycle-photo-upload.md) - Photo upload process

---

*Last updated: December 22, 2025*
