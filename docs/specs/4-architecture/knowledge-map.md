# Knowledge Map

This document tracks modules, dependencies, and architectural relationships across the Lychee codebase. Update this when new modules, dependencies, or contracts appear.

## Core Modules

### Backend (Laravel/PHP)

#### Application Layer
- **Controllers** (`app/Http/Controllers/`) - Handle HTTP requests and route to services
- **Requests** (`app/Http/Requests/`) - Validate and sanitize incoming requests
- **Resources** (`app/Http/Resources/`) - Transform models to API responses (use Spatie Data)
- **Middleware** (`app/Http/Middleware/`) - Request/response filtering and authentication

#### Domain Layer
- **Models** (`app/Models/`) - Eloquent ORM models for database entities
  - **Album Model** - Nested set tree structure with pre-computed statistical fields:
    - `num_children` - Count of direct child albums
    - `num_photos` - Count of photos directly in this album (not descendants)
    - `min_taken_at`, `max_taken_at` - Date range of photos in album + descendants
    - `auto_cover_id_max_privilege` - Cover photo for admin/owner view (ignores access control)
    - `auto_cover_id_least_privilege` - Cover photo for public view (respects PhotoQueryPolicy + AlbumQueryPolicy)
- **Services** (`app/Services/`) - Business logic and orchestration
  - **LDAP Service** (`app/Services/Auth/LdapService.php`) - Enterprise directory integration (wrapper over LdapRecord)
    - Search-first authentication pattern: searches for user by username → gets DN → binds with DN + password
    - Attribute retrieval: syncs email and display_name from LDAP
    - Group membership queries for role assignment
    - TLS/SSL support with configurable certificate validation
    - Graceful error handling with connection timeout (default: 5 seconds)
    - Dependencies: LdapRecord Laravel package, php-ldap extension
- **Actions** (`app/Actions/`) - Single-responsibility command objects
  - **ProvisionLdapUser** (`app/Actions/User/ProvisionLdapUser.php`) - Auto-provision users from LDAP
    - Creates or updates local user from LdapUser DTO
    - Syncs attributes (email, display_name) on each login
    - Queries LDAP groups to assign admin role (`may_administrate`)
    - Sets random password for LDAP users (local password not used)
- **DTOs** (`app/DTO/`) - Data transfer objects (Spatie Data)
  - **LdapConfiguration** (`app/DTO/LdapConfiguration.php`) - Validates LDAP environment variables
  - **LdapUser** (`app/DTO/LdapUser.php`) - LDAP authentication result (username, userDn, email, display_name)
- **Enums** (`app/Enum/`) - Type-safe enumeration classes

#### Infrastructure Layer
- **Repositories** - Data access abstraction (if used)
- **Events** (`app/Events/`) - Domain event definitions
  - `PhotoSaved`, `PhotoDeleted` - Trigger album stats recomputation when photos change
  - `AlbumSaved`, `AlbumDeleted` - Trigger parent album stats recomputation when album structure changes
- **Listeners** (`app/Listeners/`) - Event handlers
  - `RecomputeAlbumStatsOnPhotoChange` - Dispatches recomputation job for photo's album
  - `RecomputeAlbumStatsOnAlbumChange` - Dispatches recomputation job for parent album
- **Jobs** (`app/Jobs/`) - Asynchronous task definitions
  - `RecomputeAlbumStatsJob` - Recomputes album statistics and propagates changes to ancestors
    - Uses `WithoutOverlapping` middleware (keyed by album_id) to prevent concurrent updates
    - Atomic transaction with 3 retries + exponential backoff
    - Propagates to parent album after successful update (cascades to root)
    - Stops propagation on failure (logs error, does not dispatch parent job)
- **Notifications** (`app/Notifications/`) - User notification logic
- **Worker Mode** (`docker/scripts/entrypoint.sh`) - Container mode selection for horizontal scaling
  - **Web Mode** (default): Runs FrankenPHP/Octane web server for handling HTTP requests
  - **Worker Mode**: Runs Laravel `queue:work` for background job processing
  - **Mode Selection**: Controlled by `LYCHEE_MODE` environment variable (`web` | `worker`)
  - **Auto-Restart**: Worker mode includes automatic restart loop for memory leak mitigation
  - **Configuration**: `QUEUE_NAMES` (queue priority), `WORKER_MAX_TIME` (restart interval)
  - **Deployment**: See [deploy-worker-mode.md](../2-how-to/deploy-worker-mode.md) for docker compose examples

### Frontend (Vue3/TypeScript)

#### Components
- **UI Components** (`resources/js/components/`) - PrimeVue-based interface elements
  - Gallery components (album, photo, flow, search modules)
    - **PhotoThumbPanelControl** - Layout selector with star rating filter (Feature 006)
      - 5 clickable stars for minimum rating threshold filter
      - Conditional rendering (hidden when no rated photos)
      - Toggle behavior (click same star to clear)
      - Keyboard accessible (Arrow keys, Enter/Space)
  - Forms, modals, drawers, settings components
- **Views** (`resources/js/views/`) - Page-level Vue components
  - Gallery views: Albums, Album, Favourites, Flow, Frame, Map, Search
  - Admin views: Settings, Users, Permissions, Maintenance, Diagnostics
- **Composables** (`resources/js/composables/`) - Reusable composition functions
  - Album, photo, search, selection, context menu composables
- **Services** (`resources/js/services/`) - API communication layer using axios
- **Layouts** (`resources/js/layouts/`) - Photo layout algorithms (square, justified, masonry, grid)

#### State Management
- **Pinia Stores** (`resources/js/stores/`) - Centralized state management
  - Auth, LycheeState, LeftMenuState, ModalsState, FlowState, FavouriteState
  - **PhotosState** - Photos collection management with rating filter support:
    - `photoRatingFilter` - Current filter setting (null | 1-5)
    - `hasRatedPhotos` getter - Checks if any photo has user rating
    - `filteredPhotos` getter - Returns photos filtered by minimum rating threshold
    - `filteredPhotosTimeline` getter - Returns timeline-grouped photos filtered by rating
- Vue3 reactive state and composables

#### Routing
- **Vue Router** (`resources/js/router/`) - Client-side routing configuration

## Key Dependencies

### PHP Dependencies
- **Laravel Framework** - Web application framework
- **Spatie Data** - DTOs and data transformation
- **moneyphp/money** - Monetary value handling
- **LdapRecord Laravel** - LDAP/Active Directory integration (v3.4.2)
  - Dependency: php-ldap PHP extension required

### Frontend Dependencies
- **Vue3** - Progressive JavaScript framework (Composition API)
- **TypeScript** - Type-safe JavaScript
- **PrimeVue** - UI component library
- **Axios** - HTTP client

## Architectural Patterns

### Request Flow
1. HTTP Request → Route → Middleware
2. Controller → Request Validation
3. Service/Action → Business Logic
4. Model/Repository → Database
5. Resource/DTO → Response Transform
6. HTTP Response

### LDAP Authentication Flow
LDAP-first authentication with automatic fallback to local credentials:

1. **Login Request** → `AuthController::login()` receives username/password
2. **LDAP Attempt** (if enabled):
   - `LdapService::authenticate()` uses search-first pattern:
     - Connect to LDAP server with service account
     - Search for user by username (gets DN)
     - Bind with user DN + password
     - Retrieve attributes (email, display_name)
   - Returns `LdapUser` DTO or null
3. **User Provisioning** (on LDAP success):
   - `ProvisionLdapUser` action creates/updates local user
   - Queries LDAP groups via `LdapService::queryGroups()`
   - Assigns admin role if user in `LDAP_ADMIN_GROUP_DN`
   - Syncs attributes from LDAP
4. **Local Fallback** (if LDAP fails or disabled):
   - Standard Laravel `Auth::attempt()` with local credentials
5. **Graceful Degradation**:
   - LDAP connection errors trigger automatic fallback to local auth
   - All errors logged with contextual data (username, host, error message)
   - User-friendly error messages (no LDAP implementation details)

### Album Pagination (Feature 007)
Implements offset-based pagination for albums and photos to efficiently handle large collections:

**Backend Architecture:**
1. **Separate Endpoints** - Three new endpoints replace monolithic album loading:
   - `GET /Album::head` - Album metadata without children/photos (HeadAlbumResource)
   - `GET /Album::albums?page={n}` - Paginated child albums (PaginatedAlbumsResource)
   - `GET /Album::photos?page={n}` - Paginated photos (PaginatedPhotosResource)
2. **Repository Methods** - `AlbumRepository::getChildrenPaginated()` and `PhotoRepository::getPhotosForAlbumPaginated()` use SortingDecorator for efficient queries
3. **Album Type Support** - Works with regular albums, Smart albums (Recent, Highlighted), and Tag albums
4. **Backward Compatibility** - Legacy `/Album` endpoint unchanged, returns full album data

**Frontend Architecture:**
1. **Service Layer** - `album-service.ts` provides `getHead()`, `getAlbums()`, `getPhotos()` methods
2. **State Management** - `AlbumState` store manages pagination state (current_page, last_page, per_page, total) for both photos and albums
3. **UI Components**:
   - `PaginationLoadMore.vue` - "Load More (N remaining)" button
   - `PaginationInfiniteScroll.vue` - Intersection Observer auto-loading
   - `usePagination.ts` composable for shared logic

**Configuration (configs table):**
- `albums_per_page` (integer, default: 30) - Child albums per page
- `photos_per_page` (integer, default: 100) - Photos per page
- `albums_pagination_ui_mode` (enum: infinite_scroll, load_more_button, page_navigation)
- `photos_pagination_ui_mode` (enum: infinite_scroll, load_more_button, page_navigation)

**Data Flow:**
1. Album open → Frontend calls `getHead()`, `getAlbums(page=1)`, `getPhotos(page=1)` in parallel
2. User interaction (scroll/button/page) → Frontend calls `getPhotos(page=N)` or `getAlbums(page=N)`
3. Response includes pagination metadata: `{data: [...], current_page, last_page, per_page, total}`

### Album Statistics Pre-computation (Event-Driven)
Replaces on-the-fly virtual column computation with physical database fields updated asynchronously:

1. **Mutation Events** - Photo/album changes trigger domain events
   - Photo: created, deleted, updated (taken_at, is_highlighted, NSFW status changes)
   - Album: created, deleted, moved, NSFW status changes
2. **Event Listeners** - Dispatch `RecomputeAlbumStatsJob` for affected album
3. **Job Execution** - Recomputes 6 fields in database transaction:
   - Count fields: `num_children`, `num_photos`
   - Date range: `min_taken_at`, `max_taken_at` (recursive descendants)
   - Dual covers: `auto_cover_id_max_privilege` (admin view), `auto_cover_id_least_privilege` (public view)
4. **Propagation** - After successful update, job dispatches itself for parent album → cascades to root
5. **Failure Handling** - On failure (after 3 retries), logs error and stops propagation
6. **CLI Commands**:
   - `lychee:recompute-album-stats` - Unified command: with album_id for single-album recompute, without album_id for bulk backfill of all albums
   - `lychee:recompute-album-stats {album_id}` - Manual recovery after propagation failures

**Benefits**: 50%+ query time reduction for album listings, removes expensive nested set JOINs from read path

### Naming Conventions
- PHP: snake_case for variables, PSR-4 for classes
- Vue3: Composition API with TypeScript
- No async/await in Vue3, use `.then()` instead
- Function declarations: `function functionName() {}` not arrow functions

### Code Organization
- User making request: `$this->user`
- User from query: `$this->user2`
- Resource classes extend Spatie Data (not JsonResource)
- No Blade views - Vue3 only

## Cross-Module Contracts

### API Communication
- Base URL: `${Constants.getApiUrl()}`
- Services in `services/` directory
- Axios for HTTP requests

### Money Handling
- Use `moneyphp/money` library
- Store as integers (smallest currency unit)
- Example: $10.99 = 1099 cents

## Related Documentation

### Domain Model
- [Photos](../1-concepts/photos.md) - Content model, size variants, EXIF, palettes
- [Albums](../1-concepts/albums.md) - Album architecture and types
- [Permissions](../1-concepts/permissions.md) - Access control system
- [Users](../1-concepts/users.md) - User accounts and authentication
- [E-commerce](../1-concepts/e-commerce.md) - Webshop system
- [System Features](../1-concepts/system.md) - Statistics, jobs, OAuth, config

### How-To Guides
- [Add OAuth Provider](../2-how-to/add-oauth-provider.md) - Step-by-step OAuth integration
- [Configure Pagination](../2-how-to/configure-pagination.md) - Album and photo pagination settings
- [Translating Lychee](../2-how-to/translating-lychee.md) - Translation guide for developers and translators
- [Using Renamer](../2-how-to/using-renamer.md) - Filename transformation during import

### Reference Documentation

#### Frontend Reference
- [Frontend Architecture](../3-reference/frontend-architecture.md) - Vue3, TypeScript, Pinia, composables
- [Frontend Gallery Views](../3-reference/frontend-gallery.md) - Gallery viewing modes and component architecture
- [Frontend Layout System](../3-reference/frontend-layouts.md) - Photo layout algorithms

#### Backend Reference
- [API Design](../3-reference/api-design.md) - RESTful API patterns, authentication, and response structure
- [Database Schema](../3-reference/database-schema.md) - Models, relationships, smart albums vs regular albums
- [Image Processing](../3-reference/image-processing.md) - Size variant generation and processing pipeline
- [Renamer System](../3-reference/renamer-system.md) - Filename transformation system architecture
- [Shop Implementation](../3-reference/shop-implementation.md) - E-commerce models, services, and API endpoints
- [Timestamps Handling](../3-reference/timestamps-handling.md) - Timestamp handling conventions and best practices
- [Localization](../3-reference/localization.md) - Translation system and file structure

#### Coding Standards
- [Coding Conventions](../3-reference/coding-conventions.md) - PHP and Vue3 conventions

### Architecture Documentation
- [Backend Architecture](backend-architecture.md) - Laravel structure, design patterns, and key components
- [Album Tree Structure](album-tree-structure.md) - Nested set model implementation
- [Request Lifecycle: Album Creation](request-lifecycle-album-creation.md) - Complete album creation flow
- [Request Lifecycle: Photo Upload](request-lifecycle-photo-upload.md) - Photo upload and processing flow
- [Shop Architecture](shop-architecture.md) - E-commerce architecture and integration
- [Tag System](tag-system.md) - Tag architecture and operations
- [Architecture Graph](../architecture-graph.json) - Up-to-date module snapshot

### Operations Documentation
- [Verifying Releases](../5-operations/verifying-releases.md) - Code signing and release verification

### Feature Documentation
- See feature specs in `features/` for detailed component interactions

---

*Last updated: January 14, 2026*
