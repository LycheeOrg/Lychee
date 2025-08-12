# Lychee Architecture Guide

This document provides a comprehensive overview of Lychee's architecture, to help developers understand the system's structure, design patterns, and key components.

## Overview

Lychee is a modern photo management system built on the **Laravel framework** (PHP) with a **Vue.js** frontend. It follows a clean architecture pattern with clear separation of concerns between the backend API and frontend presentation layer.

### Technology Stack

- **Backend**: PHP 8.3+, Laravel Framework
- **Frontend**: Vue.js 3 (Composition API), TypeScript, PrimeVue
- **Database**: MySQL, MariaDB, PostgreSQL, or SQLite
- **Build Tools**: Vite, Composer, npm
- **Image Processing**: GD, ImageMagick
- **Additional**: WebAuthn, OAuth, Redis (optional)

## Application Structure

### Backend Architecture (Laravel)

Lychee follows Laravel's MVC pattern with additional architectural layers for better organization. We highlight below the key directories and their purposes:

```
app/
├── Actions/           # Business logic operations
├── Console/           # Artisan commands
├── Factories/         # Object creation factories (mostly for albums)
├── Http/              # HTTP layer (Controllers, Requests, Resources)
├── Image/             # Image processing engine
├── Jobs/              # Jobs for background/synchronous processing
├── Listeners/         # Event listeners
├── Models/            # Eloquent models
├── ModelFunctions/    # Logic related to Eloquent models
├── Policies/          # Authorization policies
├── Providers/         # Service providers
├── Relations/         # Relations used in Lychee (e.g., album-photo relationships)
├── Rules/             # Custom validation rules
├── Services/          # Service classes
└── SmartAlbums/       # Smart album logic
```

Of those you can ignore the following folders as they don't contain the core logic:
- `Providers` - Service providers
- `Services` - Service classes

The following directies are mostly used for architectural purposes and contain utility classes, constants, and other non-core logic:
```
app/
├── Assets/            # Utility classes and helpers
├── Constants/         # Constants used in Lychee
├── Contracts/         # Interfaces and contracts
├── DTO/               # Data Transfer Objects
├── Eloquent/          # Wrapper classes for Eloquent models to provide better exception handling
├── Enum/              # Enum classes for type safety
├── Events/            # Event classes
├── Exceptions/        # Custom exception classes
├── Facades/           # Facades to make the helper functions accessible
├── Mail/              # Mail helper classes
├── Notifications/     # Notification helper classes
└── View/              # View logic, barely used because our frontend is a Vue.js SPA
```

#### Key Architectural Components

##### 1. Actions Pattern
Lychee uses the **Action Pattern** to encapsulate business logic:

```php
// app/Actions/Album/Create.php
class Create
{
    public function do(string $title, ?string $parent_id): Album
    {
        // Business logic for creating an album
    }
}
```

**Benefits:**
- Single responsibility principle
- Reusable business logic
- Easier testing and maintenance
- Clear separation from controllers

For a detailed example of the Action Pattern with Pipeline implementation, see the **[Photo Actions Documentation](../app/Actions/Photo/README.md)** which covers the complex photo creation pipeline with multiple processing stages.

##### 2. Request Classes
All API endpoints use dedicated Request classes for validation and authorization:

```php
// app/Http/Requests/Album/CreateAlbumRequest.php
class CreateAlbumRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'parent_id' => 'sometimes|string',
        ];
    }
}
```

**Conventions:**
- `$this->user` contains the authenticated user making the request. Do note modify this value. This value is directly populated by Laravel's authentication system.
- `$this->user2` contains a user provided by query parameters (for admin operations)

In order to access the authenticated user, we recommend using the `Auth::user()` instead of `$this->user`.

##### 3. Resource Classes (Spatie Data)
Instead of Laravel's JsonResource, Lychee uses **Spatie Data** for response serialization:

```php
// app/Http/Resources/Models/AlbumResource.php
class AlbumResource extends Data
{
    public function __construct(
        public string $id,
        public string $title,
        public ?string $parent_id,
        // ...
    ) {}
}
```

**Benefits:**
- Type-safe response serialization
- Automatic TypeScript type generation
- Better IDE support and autocompletion

##### 4. Factory Pattern
Lychee uses factories for complex object creation and fetching.
However this is mostly limited to albums as they come in different types (e.g., regular albums, smart albums):

```php
// app/Factories/AlbumFactory.php
class AlbumFactory
{
    public function findAbstractAlbumOrFail(string $album_id, bool $with_relations = true): AbstractAlbum
    {
        // Logic to find different album types
    }
}
```

##### 5. Models and Data Architecture

Lychee's data layer is built around several core Eloquent models that represent the main entities in the photo management system:

###### Core Models

**User Models:**
- **`User`** - System users with authentication and ownership relationships
- **`UserGroup`** - User groups for permission management (SE edition)
- **`OauthCredential`** - OAuth authentication credentials

**Album Models:**
- **`Album`** - Regular photo albums with hierarchical tree structure using nested set model. For detailed information about the tree structure implementation, see the **[Album Tree Structure Documentation](Album-tree-structure.md)** which explains the nested set model with `_lft` and `_rgt` boundaries.
- **`TagAlbum`** - Special albums that automatically contain photos with specific tags

**Photo and Media Models:**
- **`Photo`** - Individual photos with metadata, EXIF data, and file information
- **`SizeVariant`** - Different size versions of photos (original, medium, small, thumb)
- **`Palette`** - Color palette information extracted from photos

**Configuration and System Models:**
- **`Configs`** - Runtime configuration settings stored in database
- **`ConfigCategory`** - Categories for organizing configuration options
- **`AccessPermission`** - Granular access control for albums and photos
- **`Statistics`** - Photo and album statistics (count, sizes, etc.)
- **`JobHistory`** - Background job execution history
- **`LiveMetrics`** - System performance and usage metrics

###### Smart Albums vs Regular Albums

**Regular Albums** (`Album` model):
- Stored in database with hierarchical tree structure
- Photos are explicitly assigned through relationships
- Can be created, deleted, and modified by users
- Support nested organization with parent-child relationships
- Use the nested set model for efficient tree operations

**Smart Albums** (extending `BaseSmartAlbum`):
- Virtual albums that exist only in memory
- Photos are included based on dynamic criteria (starred, recent, etc.)
- Cannot be created or deleted by users - they always exist when enabled
- For detailed information about Smart Albums (virtual albums that dynamically contain photos based on criteria), see the **[Smart Albums Documentation](../app/SmartAlbums/README.md)** which covers Recent, Starred, On This Day, and Unsorted albums.

This dual approach allows Lychee to provide both traditional album organization and automatic categorization of photos.


### Frontend Architecture (Vue.js)

The frontend is a **Single Page Application (SPA)** built with Vue.js 3:

```
resources/
├── js/
│   ├── components/     # Reusable Vue components
│   ├── composables/    # Vue composables for shared logic
│   ├── config/         # Configuration of Axios and other global settings
│   ├── layouts/        # This encapsulate the logic for displaying the photos on the page (grid, list, etc.)
│   ├── menus/          # Mainly the left menu component.
│   ├── router/         # Vue Router configuration
│   ├── services/       # Classes to interface with the backend API, one service per domain (e.g., AlbumService, PhotoService)
│   ├── stores/         # State management (Pinia)
│   ├── style/          # This contains the customization of the look for the PrimeVue compoents.
│   ├── utils/          # Few helpers
│   ├── vendor/         # External libraries where we directly imported the code instead of using npm packages.
│   └── views/          # The pages of the application.
└── sass/               # SCSS stylesheets & tailwindcss configuration.
```

#### Key Frontend Conventions

1. **Composition API**: All components use Vue 3's Composition API
2. **TypeScript**: Full TypeScript support with generated types from backend
3. **PrimeVue**: UI component library for consistent design
4. **No `await`**: Use `.then()` instead of `await` for async operations
5. **Function declarations**: Use `function name() {}` instead of arrow functions

### Database Architecture

#### Models and Relationships

```php
// Core Models
// Core Models
User         # Users of the system
Album        # Photo albums (hierarchical structure)
Photo        # Individual photos with metadata
Tag          # Photo tags
UserGroup    # User groups (SE edition)

#### Key Relationships
- **Albums**: Hierarchical structure (parent-child relationships)
- **Photos**: Belong to albums, can have multiple size variants
- **Users**: Own albums and photos, belong to user groups
- **Tags**: Many-to-many relationship with photos

### Image Processing Architecture

Lychee handles multiple image operations:

#### Size Variants
Photos are stored in multiple sizes:
- **Original**: Full-resolution uploaded image
- **Medium**: Web-optimized version
- **Small**: Thumbnail version
- **Thumb**: Small thumbnail for galleries

#### Processing Pipeline
1. **Upload**: Original file stored
2. **Metadata Extraction**: EXIF data parsed
3. **Size Generation**: Multiple variants created
4. **Storage**: Files organized by naming strategy

```php
// app/Image/SizeVariantDefaultFactory.php
class SizeVariantDefaultFactory implements SizeVariantFactory
{
    public function createSizeVariants(Photo $photo): Collection
    {
        // Generate different sizes
    }
}
```

## API Architecture

### RESTful API Design

Lychee exposes a RESTful API for all operations:

```php
// routes/api_v2.php
Route::get('/Albums', [AlbumController::class, 'index']);
Route::post('/Albums', [AlbumController::class, 'create']);
Route::patch('/Albums', [AlbumController::class, 'update']);
Route::delete('/Albums', [AlbumController::class, 'delete']);
```

### Authentication & Authorization

#### Multi-layered Security
1. **Session-based Authentication**: Traditional web sessions
2. **Token-based Authentication**: API tokens for external access
3. **OAuth**: Third-party authentication providers
4. **WebAuthn**: Passwordless authentication support

#### Authorization Policies
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

For comprehensive documentation about Lychee's authorization system, including the distinction between regular Policies and Query Policies, see the **[Policies Documentation](../app/Policies/README.md)** which covers authorization patterns, query filtering, and security considerations.

### Response Patterns

#### Consistent Response Structure
- Success responses return appropriate HTTP status codes
- Resource classes ensure consistent data structure
- Error responses use standardized exception handling

#### Type Safety
TypeScript types are automatically generated from PHP Resource classes:

```bash
php artisan typescript:transform
```

## Configuration Management

### Environment-based Configuration
```php
// config/features.php
return [
	// ...
	/*
	 |--------------------------------------------------------------------------
	 | Add latency on requests to simulate slower network. Time in ms.
	 | Disabled on production environment.
	 |--------------------------------------------------------------------------
	 */
	'latency' => env('APP_ENV', 'production') === 'production' ? 0 : (int) env('APP_DEBUG_LATENCY', 0),
];
```

### Runtime Configuration
Dynamic settings stored in database via `Configs` model:

```php
// app/Models/Configs.php
Configs::getValueAsBool('gallery_title');
```

`Configs` class also provides methods for type-safe access to configuration values:
- `getValueAsString`
- `getValueAsInt`
- `getValueAsBool`
- `getValueAsEnum`

## Testing Architecture

### Test Structure
```
tests/
├── Feature_v2/           # End-to-end feature tests for the version 2 API
├── Unit/             # Unit tests for individual components
└── AbstractTestCase.php  # Base test class
```

### Quality Assurance Tools
- **PHPStan**: Static analysis for PHP
- **ESLint**: JavaScript/TypeScript linting
- **Prettier**: Code formatting
- **Vue TSC**: TypeScript checking for Vue components

## Security Architecture

### Input Validation
- All inputs validated through Request classes
- CSRF protection enabled via middleware by default.
- SQL injection prevention via Eloquent ORM.

For comprehensive documentation about Lychee's custom validation rules, including patterns, and implementation examples, see the **[Rules Documentation](../app/Rules/README.md)**.

### File Upload Security
- File type validation
- Size limits enforcement
- Secure file storage outside web root

### Access Control
- Role-based permissions (using Laravel Policies)
- Album-level privacy settings
- User group management (SE edition)

## Performance Considerations

### Database Optimization
- Proper indexing on frequently queried columns
- Eager loading to prevent N+1 queries, it will be unforced with `Model::shouldBeStrict()` which will throw an exception if the relationship is accessed before being loaded.

### Frontend Optimization
- Vite for fast builds and hot reloading
- Component lazy loading
- Image lazy loading and optimization

This architecture provides a solid foundation for photo management while maintaining flexibility for future enhancements and modifications.

## Request Flow Examples

For detailed examples of how requests flow through this architecture, see:

- **[Album Creation Lifecycle](Lifecycle-of-a-request-album-creation.md)** - Complete walkthrough of creating an album, from frontend form submission to database storage and response
- **[Photo Upload Lifecycle](Lifecycle-of-a-request-photo-upload.md)** - Comprehensive guide covering photo upload, chunking, processing, size variant generation, and metadata extraction
