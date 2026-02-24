# Backend Architecture

This document provides a comprehensive overview of Lychee's backend architecture, explaining the Laravel-based structure, design patterns, and key components that power the photo management system.

---

## Overview

Lychee's backend is built on the **Laravel framework** (PHP 8.4+) with a clean architecture pattern that emphasizes separation of concerns, reusability, and maintainability. The architecture follows Laravel's MVC pattern enhanced with additional layers for business logic, authorization, and data transformation.

### Technology Stack

- **Backend**: PHP 8.4+, Laravel Framework
- **Database**: MySQL, MariaDB, PostgreSQL, or SQLite
- **Build Tools**: Composer
- **Image Processing**: GD, ImageMagick
- **Additional**: WebAuthn, OAuth, Redis (optional)

## Application Structure

### Directory Organization

Lychee follows Laravel's MVC pattern with additional architectural layers for better organization. Key directories and their purposes:

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
├── Relations/         # Custom relationship classes (e.g., album-photo relationships)
├── Rules/             # Custom validation rules
├── Services/          # Service classes
└── SmartAlbums/       # Smart album logic
```

The following directories contain utility classes, constants, and architectural components:

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
└── View/              # View logic (minimal, frontend is Vue.js SPA)
```

### Configuration Management

#### Environment-based Configuration

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

#### Runtime Configuration

Dynamic settings stored in database via `Configs` model:

```php
// app/Models/Configs.php
$config_manager = resolve(ConfigManager::class);
$config_manager->getValueAsBool('gallery_title');
```

`Configs` class provides methods for type-safe access to configuration values:
- `getValueAsString`
- `getValueAsInt`
- `getValueAsBool`
- `getValueAsEnum`

## Key Architectural Components

### 1. Actions Pattern

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

For a detailed example of the Action Pattern with Pipeline implementation, see [app/Actions/Photo/README.md](../../../app/Actions/Photo/README.md) which covers the complex photo creation pipeline with multiple processing stages.

### 2. Request Classes

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
- `$this->user` contains the authenticated user making the request. Do not modify this value. It is populated by Laravel's authentication system.
- `$this->user2` contains a user provided via query parameters (for admin operations).

To access the authenticated user, prefer `Auth::user()` instead of `$this->user`.

**Note:** Lychee customizes Laravel's Request lifecycle so that `processValidatedValues()` runs before `authorize()`. Properties initialized during validation (e.g., `$this->album`) are available for authorization checks.

For comprehensive documentation about custom validation rules, see [app/Rules/README.md](../../../app/Rules/README.md).

### 3. Resource Classes (Spatie Data)

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

#### Type Safety

TypeScript types are automatically generated from PHP Resource classes:

```bash
php artisan typescript:transform
```

### 4. Factory Pattern

Lychee uses factories for complex object creation and fetching. This is mostly limited to albums as they come in different types (e.g., regular albums, smart albums):

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

## Security Architecture

### Input Validation

- All inputs validated through Request classes
- CSRF protection enabled via middleware by default
- SQL injection prevention via Eloquent ORM

### File Upload Security

- File type validation
- Size limits enforcement
- Secure file storage outside web root

### Access Control

- Role-based permissions using Laravel Policies
- Album-level privacy settings
- User group management (SE edition)

For comprehensive documentation about authorization patterns, query filtering, and security considerations, see [app/Policies/README.md](../../../app/Policies/README.md).

## Performance Considerations

### Database Optimization

- Proper indexing on frequently queried columns
- Eager loading to prevent N+1 queries; enforced with `Model::shouldBeStrict()`, which throws an exception if a relationship is accessed before being loaded

### Testing Architecture

#### Test Structure

```
tests/
├── Feature_v2/        # End-to-end feature tests for the version 2 API
├── Unit/              # Unit tests for individual components
└── AbstractTestCase.php  # Base test class
```

#### Quality Assurance Tools

- **PHPStan**: Static analysis for PHP
- **php-cs-fixer**: Code formatting

## Request Flow Examples

For detailed examples of how requests flow through this architecture, see:

- [Request Lifecycle: Album Creation](request-lifecycle-album-creation.md) - Complete walkthrough from frontend to database
- [Request Lifecycle: Photo Upload](request-lifecycle-photo-upload.md) - Comprehensive upload, processing, and storage guide

## Module Documentation

- [Renamer System](../3-reference/renamer-system.md) - Filename transformation during import
- [Smart Albums](../../../app/SmartAlbums/README.md) - Dynamic album system (Recent, Highlighted, On This Day, Unsorted)
- [Photo Actions](../../../app/Actions/Photo/README.md) - Photo pipeline with processing stages

## Related Documentation

- [Database Schema](../3-reference/database-schema.md) - Models, relationships, smart albums vs regular albums
- [API Design](../3-reference/api-design.md) - RESTful API patterns, authentication, and response structure
- [Album Tree Structure](album-tree-structure.md) - Nested set model implementation
- [Tag System](tag-system.md) - Tag architecture and operations

---

*Last updated: February 24, 2026*
