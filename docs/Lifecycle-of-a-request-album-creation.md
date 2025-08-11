# Lifecycle of a Request in Lychee: Album Creation

This document traces the complete lifecycle of an HTTP request in Lychee, using **Creating an Album** as a concrete example. Understanding this flow will help developers navigate the codebase and understand how requests are processed from start to finish.

## Request Flow Overview

```
1. Frontend (Vue.js) → 2. Route → 3. Middleware → 4. Request Validation → 5. Controller → 6. Action → 7. Model → 8. Database → 9. Response Resource → 10. Frontend
```

## Example: Creating an Album

Let's trace a `POST /Album` request to create a new album.

### 1. Frontend Request (Vue.js)

The lifecycle begins when a user clicks "Create Album" in the Vue.js frontend:

```typescript
// Frontend makes API call using AlbumService
import AlbumService, { type CreateAlbumData } from '@/services/album-service';

const albumData: CreateAlbumData = {
  title: 'My New Album',
  parent_id: 'parent-album-id-or-null' // or null for root level
};

AlbumService.createAlbum(albumData)
  .then((response) => {
    // Handle successful album creation
    const albumId = response.data; // Returns the new album ID
  })
  .catch((error) => {
    // Handle error
  });
```

**Under the hood, AlbumService.createAlbum() uses Axios:**
```typescript
// File: resources/js/services/album-service.ts
createAlbum(data: CreateAlbumData): Promise<AxiosResponse<string>> {
  return axios.post(`${Constants.getApiUrl()}Album`, data);
}
```

### 2. Route Resolution

Laravel's router resolves the request using the route definition:

```php
// File: routes/api_v2.php
Route::post('/Album', [Gallery\AlbumController::class, 'createAlbum']);
```

**What happens:**
- Router matches the HTTP method (`POST`) and path (`/Album`)
- Router identifies the target controller and method: `AlbumController@createAlbum`
- Route parameters and middleware are collected

### 3. Middleware Pipeline

Before reaching the controller, the request passes through Laravel's middleware pipeline:

#### Global Middleware Stack
```php
// File: app/Http/Kernel.php
protected $middleware = [
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class,
    \App\Http\Middleware\CheckForMaintenanceMode::class,
    // ... more middleware
];
```

#### Route-Specific Middleware
```php
// Applied via route groups in routes/api_v2.php
->middleware(['api', 'content_type:json', 'accept_content_type:json'])
```

**Key middleware actions:**
- **CSRF Protection**: Validates CSRF token
- **Authentication**: Checks if user is logged in
- **Content Type**: Ensures JSON content-type headers
- **Rate Limiting**: Prevents abuse (if configured)

### 4. Request Validation & Authorization

Laravel creates a Request object and validates it:

```php
// File: app/Http/Requests/Album/AddAlbumRequest.php
class AddAlbumRequest extends BaseApiRequest implements HasTitle, HasParentAlbum
{
    public function authorize(): bool
    {
        return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->parent_album]);
    }

    public function rules(): array
    {
        return [
            RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
            RequestAttribute::PARENT_ID_ATTRIBUTE => ['sometimes', new AlbumIDRule(true)],
        ];
    }

    protected function processValidatedValues(array $values, array $files): void
    {
        $this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
        // Process parent album if provided...
    }
}
```

**What happens:**
1. **Input Validation**: Validates title is required and follows TitleRule
2. **Data Processing**: Extracts and processes validated values
3. **Authorization**: Checks if user can edit the parent album (or root if null)
4. **Request Object Population**: Creates strongly-typed request object

**Validation Failure**: If validation fails, Laravel returns a `422 Unprocessable Entity` response with error details.

### 5. Controller Method Execution

The validated request reaches the controller:

```php
// File: app/Http/Controllers/Gallery/AlbumController.php
public function createAlbum(AddAlbumRequest $request): string
{
    // Dispatch cache update event
    AlbumRouteCacheUpdated::dispatch($request->parent_album?->id ?? '');
    
    // Get authenticated user
    $owner_id = Auth::id() ?? throw new UnauthenticatedException();
    
    // Create action instance
    $create = new Create($owner_id);
    
    // Execute business logic and return album ID
    return $create->create($request->title(), $request->parent_album())->id;
}
```

**Controller responsibilities:**
- Minimal business logic (delegation pattern)
- Event dispatching for cache invalidation
- User authentication verification
- Action instantiation and execution
- Response formatting

### 6. Action Pattern Execution

Business logic is encapsulated in Action classes:

```php
// File: app/Actions/Album/Create.php
class Create
{
    public function __construct(public readonly int $intended_owner_id) {}

    public function create(string $title, ?Album $parent_album): Album
    {
        // 1. Create new Album model
        $album = new Album();
        $album->title = $title;
        
        // 2. Set parent-child relationship
        $this->set_parent($album, $parent_album);
        
        // 3. Save to database
        $album->save();
        
        // 4. Set up permissions
        $this->set_permissions($album, $parent_album);
        
        // 5. Initialize statistics
        $this->setStatistics($album);
        
        return $album;
    }

    private function set_parent(Album $album, ?Album $parent_album): void
    {
        if ($parent_album !== null) {
            $album->owner_id = $parent_album->owner_id;
            $album->appendToNode($parent_album);  // Nested set pattern
        } else {
            $album->owner_id = $this->intended_owner_id;
            $album->makeRoot();
        }
    }

    private function set_permissions(Album $album, ?Album $parent_album): void
    {
        // Create access permissions for the album owner
        $access_perm = AccessPermission::withGrantFullPermissionsToUser($this->intended_owner_id);
        $album->access_permissions()->save($access_perm);
    }

    private function setStatistics(Album $album): void
    {
        $album->statistics()->create([
            'album_id' => $album->id,
            'visit_count' => 0,
            'download_count' => 0,
            'favourite_count' => 0,
            'shared_count' => 0,
        ]);
    }
}
```

**Action Pattern Benefits:**
- **Single Responsibility**: Each action handles one business operation
- **Testability**: Actions can be unit tested independently
- **Reusability**: Actions can be used from multiple controllers
- **Clarity**: Business logic is explicit and well-organized

### 7. Model Interactions

The Action interacts with Eloquent models:

```php
// File: app/Models/Album.php
class Album extends BaseAlbum
{
    use HasFactory, ThrowsConsistentExceptions, UTCBasedTimes;
    use NodeTrait;  // For nested set tree structure

    protected $fillable = [
        'title', 'description', 'owner_id', 'parent_id'
        // ... more attributes
    ];

    // Relationships
    public function owner(): BelongsTo { /* ... */ }
    public function photos(): HasMany { /* ... */ }
    public function access_permissions(): HasMany { /* ... */ }
    public function statistics(): HasOne { /* ... */ }
}
```

**Model Operations:**
1. **Attribute Assignment**: Title, owner_id, etc.
2. **Tree Structure**: Using Laravel Nested Set for hierarchical albums
3. **Relationship Creation**: Access permissions and statistics
4. **Database Persistence**: Multiple SQL queries executed

### 8. Database Operations

Several database operations occur during album creation:

```sql
-- 1. Insert new album record
INSERT INTO albums (id, title, owner_id, parent_id, _lft, _rgt, created_at, updated_at) 
VALUES ('uuid-generated', 'My New Album', 1, NULL, 1, 2, NOW(), NOW());

-- 2. Update nested set tree structure (if has parent)
UPDATE albums SET _rgt = _rgt + 2 WHERE _rgt >= ? AND id != ?;
UPDATE albums SET _lft = _lft + 2 WHERE _lft > ? AND id != ?;

-- 3. Create access permissions
INSERT INTO access_permissions (album_id, user_id, grants_full_photo_access, ...)
VALUES ('album-uuid', 1, 1, ...);

-- 4. Create statistics record
INSERT INTO album_statistics (album_id, visit_count, download_count, ...)
VALUES ('album-uuid', 0, 0, ...);
```

### 9. Response Formation

The controller returns the album ID, which Laravel automatically converts to JSON:

```php
// Controller returns: string (album ID)
return $create->create($request->title(), $request->parent_album())->id;

// Laravel converts to HTTP response:
HTTP/1.1 200 OK
Content-Type: application/json

"album-uuid-here"
```

**Response Handling:**
- **Automatic Serialization**: Laravel converts return value to JSON
- **HTTP Status Code**: 200 OK for successful creation
- **Headers**: Content-Type, CSRF tokens, etc.

### 10. Frontend Processing

The Vue.js frontend receives and processes the response through the AlbumService:

```typescript
// Using the AlbumService
AlbumService.createAlbum(albumData)
  .then(response => {
    const albumId = response.data; // New album ID returned
    
    // Clear album cache to refresh listings
    AlbumService.clearAlbums();
    
    // Update local state (using Pinia store)
    albumStore.addNewAlbum({
      id: albumId,
      title: albumData.title,
      parent_id: albumData.parent_id,
      // ... other properties
    });
    
    // Navigate to new album or update UI
    router.push(`/gallery/${albumId}`);
    
    // Show success notification
    notify.success('Album created successfully!');
  })
  .catch(error => {
    // Handle different error types
    if (error.response?.status === 422) {
      // Validation errors
      const errors = error.response.data.errors;
      handleValidationErrors(errors);
    } else {
      // Other errors
      notify.error('Failed to create album');
    }
  });
```

**Key Frontend Features:**
- **Type Safety**: TypeScript interfaces ensure correct data structure
- **Cache Management**: Automatic cache clearing for updated album listings
- **Error Handling**: Specific handling for validation vs server errors
- **State Management**: Integration with Pinia stores for reactive UI updates

## Error Handling Throughout the Lifecycle

### Validation Errors (422 Unprocessable Entity)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

### Authorization Errors (403 Forbidden)
```json
{
  "message": "Unauthorized action."
}
```

### Server Errors (500 Internal Server Error)
```json
{
  "message": "Internal server error occurred."
}
```

## Event System Integration

Throughout the lifecycle, events are dispatched for cross-cutting concerns:

```php
// Cache invalidation
AlbumRouteCacheUpdated::dispatch($album->id);

// Metrics tracking (if enabled)
AlbumCreated::dispatch($album);
```

## Security Measures

### Input Sanitization
- **XSS Prevention**: HTML escaping in templates
- **SQL Injection Prevention**: Eloquent ORM parameterized queries

### Access Control
- **Authentication**: User identity verification
- **Authorization**: Permission-based access control
- **CSRF Protection**: Token validation

## Testing the Lifecycle

### Feature Tests
```php
// File: tests/Feature/CreateAlbumTest.php
public function test_user_can_create_album()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/v2/Album', [
            'title' => 'Test Album',
            'parent_id' => null
        ]);
    
    $response->assertStatus(200);
    $this->assertDatabaseHas('albums', [
        'title' => 'Test Album',
        'owner_id' => $user->id
    ]);
}
```

## Summary

The request lifecycle in Lychee follows a clean, layered architecture:

1. **Presentation Layer**: Vue.js frontend handles user interaction
2. **HTTP Layer**: Laravel routing and middleware handle request processing
3. **Validation Layer**: Request classes ensure data integrity and authorization
4. **Controller Layer**: Minimal logic, delegates to actions
5. **Business Logic Layer**: Actions encapsulate domain operations
6. **Data Layer**: Eloquent models interact with database
7. **Response Layer**: Resources format and return data

This architecture provides:
- **Separation of Concerns**: Each layer has specific responsibilities
- **Testability**: Individual components can be tested in isolation
- **Maintainability**: Clear structure makes code changes predictable
- **Security**: Multiple layers of validation and authorization
- **Performance**: Optimized database operations and caching strategies

Understanding this flow helps developers know where to make changes for different types of features and ensures consistency with Lychee's architectural patterns.
