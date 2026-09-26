# Knowledge Map

This document tracks modules, dependencies, and architectural relationships across the Lychee codebase. Update this when new modules, dependencies, or contracts appear.

## Core Modules

### Backend (Laravel/PHP)

#### Application Layer
- **API v3** (`routes/api_v3.php`, Feature 056) - Greenfield `/api/v3/...` surface, registered alongside `routes/api_v2.php` in `RouteServiceProvider` (`Route::middleware('api')->prefix('api/v3')->group(...)`); additive only, v2 untouched. Establishes the convention that per-route binary-passthrough endpoints opt out of the `api` group's `accept_content_type:json`/`content_type:json` middleware via `->withoutMiddleware(...)` and instead apply the new `json_errors` middleware (`App\Http\Middleware\EnsureJsonErrorResponses`) so error responses still render as JSON. `GET /Albums` (Feature 057) is the first endpoint to instead rely on the group's default JSON middleware unchanged, being a JSON (not binary) response.
- **Controllers** (`app/Http/Controllers/`) - Handle HTTP requests and route to services
  - **AdminDashboardController** (`app/Http/Controllers/Admin/AdminDashboardController.php`) - `GET /api/v2/Admin/Stats`; delegates to `AdminStatsService`, wraps result in `AdminStatsResource`.
  - **PhotoAssetController** (`app/Http/Controllers/Gallery/PhotoAssetController.php`, Feature 056) - `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}`; resolves the watermark-aware path via `Watermarker::get_path()`, then either streams the local file (`FlysystemFile`/`response()->file()`) or redirects (302) to a native S3 temporary URL for S3-backed size variants (mirrors `UrlGenerator::getAwsUrl()`'s `AwsS3V3Adapter` detection).
  - **AlbumListController** (`app/Http/Controllers/Gallery/AlbumListController.php`, Feature 057) - `GET /api/v3/Albums`; first v3 endpoint to realize the SoA collection convention (ADR-0009). Builds `Album::query()->toBase()` (no Eloquent hydration), curated via `AlbumQueryPolicy::applyVisibilityFilter()`, ordered by `_lft`, never paginated. Resolves `cover_ids[i]` via a `resolveCoverId()` helper (public static, reused by `AlbumListing\AlbumChildrenController`/`AlbumListing\AlbumRootController`/`AlbumListing\AlbumCategoryController::pinned()`, Feature 061/062) mirroring `HasAlbumThumb::getCoverTypeForAlbum()`'s priority rule (no relation load). Wraps the query+map step in `ManagedCacheService::rememberIf()`, gated on `managed_cache_albums_enabled`, keyed via `CacheKeyProvider::albumListingV3Key()`, tagged `CacheKeyProvider::albumListingV3Tag()`.
  - **AlbumListing\AlbumChildrenController** (`app/Http/Controllers/Gallery/AlbumListing/AlbumChildrenController.php`, Feature 061; consolidated + renamed by Feature 062, FR-062-12) - `buckets()`/`index()`/`rights()`, serving the sub-album tier at its renamed paths `GET /api/v3/Albums/{album_id}[/buckets|/rights]` (`/children` segment dropped, Q-062-12) — a straight port of Feature 061's three former single-action controllers (`AlbumBucketController`/`AlbumChildrenDataController`/`AlbumChildrenRightsController`, now deleted) into one class, one method each; query logic/response shape unchanged.
    - `buckets()`: `OWNER_ID`-sorted parents short-circuit to `bucketable: false` without a `GROUP BY`; otherwise a plain `Album::query()->where('parent_id', ...)`, `applyVisibilityFilter()`-scoped, `GROUP BY albums.bucket_id`, `toBase()`, ordered by a raw `(bucket_id IS NULL) ASC, bucket_id <dir>` (never `SortingDecorator`). `labels[i]` computed post-aggregation (bounded by bucket count) via `AlbumBucketComputer::resolveGranularity()` plus the same `timeline_album_date_format_*` config keys `TimelineData::fromAlbum()` reads — an independent implementation, not a shared code path.
    - `index()`: for a real `Album`: single flat `applyVisibilityFilter()`-scoped `toBase()` query, zero joins beyond that policy's own baseline (`base_albums`, `computed_access_permissions`) — `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s `base_albums` subselect was widened by one column (`is_nsfw`) rather than adding a second join. For a `TagAlbum`/`PersonAlbum`: reuses `AlbumRepository::queryMatchingAlbumsForTag()`/`queryMatchingAlbumsForPerson()` (`applyBrowsabilityFilter()`-scoped, config-gated by `TA_albums_listing_enabled`/`PA_albums_listing_enabled`), with `computed_access_permissions` joined explicitly (that query path has no built-in join for it, unlike `applyVisibilityFilter()`'s own). `description` SQL-truncated via `SUBSTR(...)`; `cover_id` resolved via `AlbumListController::resolveCoverId()`. Feature 062 widening: also selects/emits `owner_ids[i]` (free — `base_albums.owner_id` was already selected for cover resolution).
    - `rights()`: background-fetched right-click permission signals. Admin callers short-circuit to every right `true` without the grants join or `can_delete_children`'s `exists()` query ever running. Otherwise: one `AlbumQueryPolicy::joinSubComputedAccessPermissions(..., prefix: 'grants_', full: true, user: $currentUser)` join (a *second*, distinctly-prefixed join alongside `applyVisibilityFilter()`'s own non-full one — same subquery mechanism, different alias, avoiding a SQL alias collision), `GROUP BY albums.id` with `MAX()` per `grants_*` column (required — that subquery has no internal `GROUP BY` in `full: true` mode, so a caller in multiple groups with separate matching grants on one child would otherwise yield duplicate rows). `can_delete_children`/`can_move_children` mirror `AlbumPolicy::canDelete()`'s parent-scoped query verbatim, addressed at `album_id` directly — for a `TagAlbum`/`PersonAlbum`, both are always `false` instead (no single shared parent whose grants could uniformly apply to a dynamically-matched, disparately-parented result set). `owner_id` stays a real, always-present value for this tier (the `Spatie\LaravelData\Optional` widening only ever triggers for root, below).
  - **AlbumListing\AlbumRootController** (`app/Http/Controllers/Gallery/AlbumListing/AlbumRootController.php`, Feature 062) - `index()`/`buckets()`/`rights()` serving `GET /api/v3/Albums/root[/buckets|/rights]`. A shared `baseQuery(scope, user)` builds `Album::query()->whereIsRoot()` + `applyVisibilityFilter()` + the owner-equals/not-equals predicate for `own`/`shared` + the same `deduplicate_pinned_albums`-conditional join `Top::queryRootAlbums()` carries (NFR-062-05 row-set parity). `own` scope reuses the exact `bucket_id`/`AlbumSortingCriterion::createDefault()`-driven mechanism the sub-album tier uses (root has no parent, so the *instance-wide* default plays that role). `shared` scope's buckets are a live `GROUP BY base_albums.owner_id` (`querySharedBuckets()`) — a `LEFT JOIN users` + `COALESCE(display_name, username)` label only for an authenticated caller; a guest never runs that join, every label hardcoded `"unknown"`. `rights()`'s `owner_id` field is always `Spatie\LaravelData\Optional::create()` (never a real value) — root has no single owner to report there, unconditional across both scopes (Q-062-16).
  - **AlbumListing\AlbumCategoryController** (`app/Http/Controllers/Gallery/AlbumListing/AlbumCategoryController.php`, Feature 062) - `smart()`/`tags()`/`tagsRights()`/`persons()`/`pinned()`. `smart()` reuses `AlbumFactory::getAllBuiltInSmartAlbums(false)` + `Gate::check()` verbatim (mirrors `Top::get()`'s own precedent), zero cover/photo resolution. `tags()`/`persons()`/`pinned()` mirror `Top::queryTagAlbums()`/`queryPersonAlbums()`/`queryPinnedAlbums()`'s filter/sort exactly (via `SortingDecorator`, same as `Top.php`) minus their `->with([...])` eager loads, `toBase()`-queried. `persons()`/`pinned()` additionally take `own`\|`shared` scope (shared `applyScopePredicate()` helper) but — unlike root — `shared` is always one flat, ungrouped list (NG9), no `GROUP BY owner_id`, no `/buckets` route for either. `tagsRights()` mirrors the sub-album tier's grants-join pattern against `tag_albums.id` instead of a parent-scoped query.
  - **AlbumListing\PhotoChildrenController** (`app/Http/Controllers/Gallery/AlbumListing/PhotoChildrenController.php`, Feature 064) - `buckets()`/`index()`/`details()`, serving `GET /api/v3/Albums/{album_id}/Photos[/buckets|/details]` — ports the sub-album virtual-scroll trio above from albums to the photos directly inside one album. `buckets()`/`index()` delegate to `App\Actions\Photo\StructOfArrays\QueryPhotoBuckets`/`QueryPhotoRatios` (both `toBase()`-only, NFR-064-01); `details()` delegates to `QueryPhotoDetails`, the one tier that deliberately hydrates real `Photo` Eloquent models (`with(['size_variants','palette','statistics','tags','albums'])`) since FR-064-19/20/21 require reusing `SizeVariantsResouce`/`ColourPaletteResource`/`PhotoStatisticsResource` exactly as-is — reconciled with NFR-064-01 by this tier's own bounded scope (300-cap or one already-known bucket). All 3 wrapped in `ManagedCacheService::rememberIf()`, gated `managed_cache_albums_enabled` (no new toggle), tagged `CacheKeyProvider::photoListingTag($album_id)` + `userTag()`.
- **Requests** (`app/Http/Requests/`) - Validate and sanitize incoming requests
  - **GetPhotoAssetRequest** (`app/Http/Requests/Photo/GetPhotoAssetRequest.php`, Feature 056) - Resolves `Photo`/`SizeVariant` from route params; authorizes via `PhotoPolicy::CAN_SEE`/`CAN_ACCESS_FULL_PHOTO` depending on the size-variant class. Validates an optional paired `X-Timestamp`/`X-Mac` temporary-link signature (via `TemporaryLinkSigner`) ahead of the policy check; `signatureRequired()` (ADR-0008) decides per-caller whether a session alone suffices. Overrides `failedAuthorization()` to key 401-vs-403 off *which* check failed rather than session state (`Auth::check()`), since a signature-valid guest denied by policy needs 403 while a signature-required session missing its signature needs 401.
  - **AlbumListV3Request** (`app/Http/Requests/Gallery/AlbumListV3Request.php`, Feature 057) - Validates `with_parent_id`/`for_bulk_edit` (`sometimes|boolean`, default `false`); `authorize()` is open to any visitor/user unless either resolved flag is `true`, in which case it requires `may_administrate === true`.
  - **GetAlbumBucketsRequest** / **GetAlbumChildrenDataRequest** / **GetAlbumChildrenRightsRequest** (`app/Http/Requests/Album/`, Feature 061) - `album_id` bound from the route segment via `prepareForValidation()` (mirrors `GetPhotoAssetRequest`'s pattern, not `GetAlbumChildrenRequest`'s query-string one); `authorize()` requires both `config('features.struct-of-array') === true` and `AlbumPolicy::CAN_ACCESS`. `GetAlbumBucketsRequest` resolves a regular `Album` only (`Album::query()->where('id', ...)->firstOrFail()` — a `TagAlbum`/`PersonAlbum` id 404s by construction). `GetAlbumChildrenDataRequest`/`GetAlbumChildrenRightsRequest` additionally resolve `TagAlbum`/`PersonAlbum` (mirrors `GetAlbumChildrenRequest`'s (v2) try-Album-then-TagAlbum-then-PersonAlbum resolution exactly, via `HasAbstractAlbumTrait`) — bucketing does not extend to those two types (see `api-design.md`), but the render-data/rights tiers do. Kept as-is by Feature 062 (their album-resolution logic genuinely differs per method) — only their consuming controller moved.
  - **GetScopedAlbumsRequest** (`app/Http/Requests/Album/GetScopedAlbumsRequest.php`, Feature 062) - No album to resolve, just `config('features.struct-of-array')` + `scope` validation (`AlbumListingScope`): `required|in:own,shared` when `Auth::check()`, `nullable|in:shared` for a guest (defaults to `shared`). Shared verbatim across 5 methods, 2 controllers: `AlbumRootController::index()`/`buckets()`/`rights()` and `AlbumCategoryController::persons()`/`pinned()`.
  - **GetAlbumCategoryRequest** (`app/Http/Requests/Album/GetAlbumCategoryRequest.php`, Feature 062) - Flag-gate-only request (no rules) for the 3 un-scoped `AlbumCategoryController` methods: `smart()`/`tags()`/`tagsRights()`.
  - **GetPhotoBucketsRequest** / **GetPhotoRatiosRequest** / **GetPhotoDetailsRequest** (`app/Http/Requests/Photo/`, Feature 064) - `album_id` bound from the route segment, mirroring `GetAlbumBucketsRequest`'s pattern exactly; resolve a regular `Album` only (`TagAlbum`/`PersonAlbum`/`BaseSmartAlbum` 404, NG3). `GetPhotoDetailsRequest` additionally validates exactly-one-of `bucket_id`/`photo_ids[]` (`required_without`+`prohibits` rule pair — both or neither present is a 422) with `photo_ids[]` capped at `max:300` (NFR-064-04; `bucket_id` mode deliberately uncapped).
- **Resources** (`app/Http/Resources/`) - Transform models to API responses (use Spatie Data)
  - **AlbumListResource** / **AlbumListBulkEditFieldsResource** (`app/Http/Resources/V3/`, Feature 057) - SoA response for `GET /api/v3/Albums`: `ids`/`titles`/`lft`/`rgt`/`cover_ids` always present, `parent_ids` (whole-array `null` unless `with_parent_id=true`) and `bulk_edit` (whole-object `null` unless `for_bulk_edit=true`) additive. `AlbumListBulkEditFieldsResource` mirrors `BulkAlbumResource`'s field set as a second, index-aligned SoA block.
  - **AlbumBucketResource** / **AlbumChildrenDataResource** / **AlbumChildrenRightsResource** (`app/Http/Resources/V3/`, Feature 061; widened by Feature 062) - SoA responses for the three album virtual-scroll endpoints above; see `api-design.md`'s Feature 061 section for the full field list and semantics of each. `AlbumChildrenDataResource` gained `owner_ids[]` (additive). `AlbumChildrenRightsResource`'s `owner_id` widened to `string|Spatie\LaravelData\Optional` — `Optional::create()` (key omitted from the JSON entirely) for root's response, unconditionally across both scopes; a real string, unchanged, for the sub-album/`TagAlbum`/`PersonAlbum`-matching tiers.
  - **AlbumCategoryListResource** / **AlbumCategoryRightsResource** (`app/Http/Resources/V3/`, Feature 062) - `AlbumCategoryListResource`: `ids`/`titles`/`cover_ids`/`owner_ids`, the shared minimum-viable shape for `smart`/`tags`/`persons`/`pinned` (mirrors `AlbumListResource`). `AlbumCategoryRightsResource`: `ids`/`grants_edit`/`grants_download`/`grants_delete` for `tags/rights` — no `can_delete_children`/`can_move_children` concept (a flat catalogue has no parent-child relationship).
  - **PhotoBucketResource** / **PhotoRatioResource** / **PhotoDetailResource** (`app/Http/Resources/V3/`, Feature 064) - SoA responses for the three photo virtual-scroll endpoints; see `api-design.md`'s "API v3: Photo Listing Virtual-Scroll Backend" section for the full field list. `PhotoBucketResource` is a verbatim shape mirror of `AlbumBucketResource`. `PhotoRatioResource`'s conditional fields (`rating_avgs`/`rating_users`/`thumb_infos`/`tags`) and `PhotoDetailResource`'s (EXIF-lite/GPS/`locations`) are typed `array|Spatie\LaravelData\Optional`, mirroring `AlbumChildrenRightsResource`'s `Optional`-omission convention. `PhotoDetailResource` additionally nests `size_variants`/`palette`/`statistics` as existing resource classes reused as-is (`SizeVariantsResouce`/`ColourPaletteResource`/`PhotoStatisticsResource`) rather than flattened SoA — the one deliberate exception to the convention in this feature.
- **Middleware** (`app/Http/Middleware/`) - Request/response filtering and authentication

#### Domain Layer
- **Models** (`app/Models/`) - Eloquent ORM models for database entities
  - **Person Model** (`app/Models/Person.php`) - Represents an identified individual across multiple photos
    - Optional 1-to-1 link to a `User` account (claimed person)
    - `is_searchable` flag — when false, face overlays are hidden from non-owners (privacy mode)
    - Has many `Face` records
  - **Face Model** (`app/Models/Face.php`) - A detected face bounding box on a specific photo
    - Bounding box stored as relative floats (0.0–1.0) in `x`, `y`, `width`, `height`
    - `crop_token` — opaque token used to serve a cropped face thumbnail via dedicated endpoint
    - `is_dismissed` — operator/owner has chosen to ignore this face detection
    - Belongs to `Photo` (cascade delete), belongs to `Person` (null on delete)
    - Has many `FaceSuggestion` (similar faces ranked by confidence)
  - **Album Model** - Nested set tree structure with pre-computed statistical fields:
    - `num_children` - Count of direct child albums
    - `num_photos` - Count of photos directly in this album (not descendants)
    - `min_taken_at`, `max_taken_at` - Date range of photos in album + descendants
    - `auto_cover_id_max_privilege` - Cover photo for admin/owner view (ignores access control)
    - `auto_cover_id_least_privilege` - Cover photo for public view (respects PhotoQueryPolicy + AlbumQueryPolicy)
    - `bucket_id` (Feature 061; scope widened by Feature 062) - Materialized, pre-truncated date-bucket label (or alphabetical `title_base` prefix), governed by the album's own **parent's** effective sort column/granularity (or the *instance-wide* default for a root album, Feature 062); `NULL` for an `OWNER_ID`-sorted effective column (never bucketable) or when no data is available. Stays exclusively date/title-derived for every album, root included — never owner-derived (G4); root's `shared`-scope owner grouping is a separate, live `GROUP BY owner_id` that never touches this column. See `database-schema.md` for the full write-path/config story.
  - **TitleBucketMode** (`app/Enum/TitleBucketMode.php`, Feature 061) - `date_prefix` (default) \| `alphabetical`; instance-wide config (`title_bucket_mode`) governing how a `TITLE`-sorted parent's children compute `bucket_id` — no per-album override.
  - **AlbumListingScope** (`app/Enum/AlbumListingScope.php`, Feature 062) - `OWN` \| `SHARED`; the `scope` request dimension shared by root/persons/pinned (`GetScopedAlbumsRequest`).
  - **`ColumnSortingAlbumType::OWNER_ID` removal** (Feature 062, FR-062-08, G5) - Deleted outright (a Feature 060 dropdown narrowing had already hidden it from `configs.type_range` without removing the enum case). Migration `2026_09_02_120000_remove_owner_id_album_sorting.php` rewrites surviving `owner_id` values (`configs.sorting_albums_col`, `albums.album_sorting_col`) to `created_at`. `ColumnSortingType::OWNER_ID` (broader, internal enum — used for `Top::queryRootAlbums()`'s hardcoded sort and root's live `shared`-scope `ORDER BY owner_id`) is untouched (NG7).
  - **`photo_album.bucket_id`** (Feature 064) - Nullable string + composite `(album_id, bucket_id)` index on the **pivot table**, not on `photos` — a photo linked into two albums with different effective `sorting_col`/`photo_timeline` settings correctly carries two different `bucket_id` values, one per pivot row (NFR-064-06). Governed by the **containing album's own** `getEffectivePhotoSorting()`/`photo_timeline` (no parent indirection, unlike sub-albums). `NULL` for an `OWNER_ID`-sorted effective column (excluded from photo bucketing entirely, by policy — NG11) or when no data is available. See `database-schema.md` for the full write-path story.
  - **`photo_title_bucket_mode`/`photo_title_bucket_prefix_length`** (Feature 064, G6) - Photo-specific instance-wide configs governing `TITLE`-sorted photo bucketing; deliberately independent from albums' own `title_bucket_mode`/`title_bucket_prefix_length` (Feature 061) — never shared, per explicit user direction.
- **Services** (`app/Services/`) - Business logic and orchestration
  - **TemporaryLinkSigner** (`app/Services/TemporaryLinkSigner.php`, Feature 056) - `sign(int $timestamp): string`/`verify(int $timestamp, string $mac): bool`; stateless HMAC-SHA256 of the timestamp only (not `photo_id`/`size_variant`-scoped), keyed by `config('app.key')`, `hash_equals()` comparison. TTL/future-timestamp checks live in the caller (`GetPhotoAssetRequest`), not here.
  - **AlbumBucketComputer** (`app/Services/AlbumBucketComputer.php`, Feature 061) - Shared `bucket_id` truncation logic (`resolveGranularity()`, `compute()`), reused by `RecomputeAlbumStatsJob`, `RecomputeChildAlbumBucketsJob`, and the `lychee:recompute-buckets` backfill command — each caller resolves its own effective sort column/granularity (an Eloquent relation for one album, a raw self-join for a full-table pass) and hands this class only the already-decided values. Never queries `photos`.
  - **PhotoBucketComputer** (`app/Services/PhotoBucketComputer.php`, Feature 064) - Photo analogue of `AlbumBucketComputer`, same `resolveGranularity()`/`compute()` shape, operating on already-resolved scalar/Carbon inputs (never queries `photos`/`albums`/`size_variants`/`tags` itself). Adds a 4th, photos-only `HOUR` granularity tier (`Y-m-d-H`) `AlbumBucketComputer` has no equivalent for. `TITLE` branch reads the photo-specific `photo_title_bucket_mode`/`photo_title_bucket_prefix_length` configs, never albums' own.
  - **AdminStatsService** (`app/Services/AdminStatsService.php`) - Aggregates system-wide metrics (photos, albums, users, storage, jobs) with 5-minute cache under key `admin.stats`. Supports forced refresh via `$force = true`. Returns `AdminStatsOverview` DTO; partial failures captured in `errors[]` and suppress caching.
  - **LDAP Service** (`app/Services/Auth/LdapService.php`) - Enterprise directory integration (wrapper over LdapRecord)
    - Search-first authentication pattern: searches for user by username → gets DN → binds with DN + password
    - Attribute retrieval: syncs email and display_name from LDAP
    - Group membership queries for role assignment
    - TLS/SSL support with configurable certificate validation
    - Graceful error handling with connection timeout (default: 5 seconds)
    - Dependencies: LdapRecord Laravel package, php-ldap extension
- **Actions** (`app/Actions/`) - Single-responsibility command objects
  - **CreateInitialAdmin** (`app/Actions/User/CreateInitialAdmin.php`) - Creates the first admin user; wraps `User\Create` plus the `configs.owner_id` update; throws `AdminUserAlreadySetException` if an admin already exists. Shared by `SetUpAdminController` (v7/Blade) and `AdminSetupController` (v8/API, Feature 051).
  - **ProvisionLdapUser** (`app/Actions/User/ProvisionLdapUser.php`) - Auto-provision users from LDAP
    - Creates or updates local user from LdapUser DTO
    - Syncs attributes (email, display_name) on each login
    - Queries LDAP groups to assign admin role (`may_administrate`)
    - Sets random password for LDAP users (local password not used)
  - **Import Actions** (`app/Actions/Import/`) - CLI sync and import pipeline
    - `Exec::do(string $path, ?Album $parent_album)` — tree-based directory import (BuildTree pipe chain)
    - `Exec::doFiles(array $file_paths, ?Album $parent_album)` — direct file import, bypasses tree-based album creation (Feature 024)
  - **PurgeAlbumUserThumbs** (`app/Actions/Sharing/PurgeAlbumUserThumbs.php`, ADR-0010) - Drops cached tag/person/smart-album covers (`album_user_thumbs`) whenever access is revoked. `GetPhotoAssetRequest::isComputedAlbumThumb()` accepts such a row as proof that the photo represents the album and performs no per-request permission re-check (the endpoint is hit once per rendered thumbnail), so the invariant lives entirely on the write side: `forBaseAlbums()` (every viewer's row for photos of those albums - never narrowed by the revoked permission's user/group, since rows are keyed by the viewer who materialised them) from `SharingController::delete()` and `Propagate::overwrite()`; `forUsers()` from `PurgeAlbumUserThumbsOnMembershipChange` and `UserGroupsController::delete()`. Its docblock is the register of both the call sites and the paths already covered elsewhere (FK cascade, `User::delete()`, `Actions\Album\Delete`, `RecomputeAlbumUserThumbsOnPhotoChange`) - a revocation path added without a purge call re-opens a private-thumbnail leak.
  - **Photo Upload Pipes** (`app/Actions/Photo/Pipes/Standalone/`) - Per-photo processing stages
    - `ApplyUserProvidedMetadata` — assigns caller-supplied `title`/`description` to the `Photo` model before `HydrateMetadata` runs; no-op when fields are null (Feature 041)
    - `AutoRenamer` — applies user-configured renaming rules; skips when `StandaloneDTO::$title` is non-null (Feature 041)
- **DTOs** (`app/DTO/`) - Data transfer objects (Spatie Data)
  - **LdapConfiguration** (`app/DTO/LdapConfiguration.php`) - Validates LDAP environment variables
  - **LdapUser** (`app/DTO/LdapUser.php`) - LDAP authentication result (username, userDn, email, display_name)
  - **ImportParam** (`app/DTO/ImportParam.php`) - Upload strategy parameters; includes `title`, `description`, `preallocated_id` (Feature 041)
  - **InitDTO / StandaloneDTO** (`app/DTO/PhotoCreate/`) - DTO chain for photo creation; carry `title`, `description`, `preallocated_id` through the pipeline (Feature 041)
- **HTTP Resources** (`app/Http/Resources/Editable/UploadMetaResource.php`) - Upload response DTO; includes `expected_id`, `title`, `description` (Feature 041)
- **Enums** (`app/Enum/`) - Type-safe enumeration classes

#### Infrastructure Layer
- **Repositories** - Data access abstraction (if used)
- **Events** (`app/Events/`) - Domain event definitions
  - `PhotoSaved`, `PhotoDeleted` - Trigger album stats recomputation when photos change
  - `AlbumSaved`, `AlbumDeleted` - Trigger parent album stats recomputation when album structure changes
  - `AccessPermissionChanged(string $base_album_id)` (Feature 053) - Dispatched by `SharingController::create/edit/delete`; carries the affected album's id
  - `UserGroupMembershipChanged(int $user_id)` (Feature 053) - Dispatched by `UserGroupsManagementController::addUser/removeUser/updateUserRole`. Two listeners: `ManagedCacheUserListingInvalidator` (album-listing cache) and `PurgeAlbumUserThumbsOnMembershipChange` (cached album covers, ADR-0010) - leaving a group revokes album access without touching any `access_permissions` row, so the permission-keyed purges never see it.
  - `AlbumChildrenChanged(?string $parent_id)`, `TagAlbumSaved`, `PersonAlbumSaved`, `BaseAlbumRemoved(string $base_album_id)`, `AlbumComputedDataUpdated(string $album_id)`, `AlbumListingCacheFlushRequested` (no payload), `AlbumTagsChanged(array $tag_ids)` (Feature 053) - Remaining album-listing-cache domain events; see `app/Events/` and spec 053 FR-053-04..31 for the full dispatch-site list (20 sites across `AlbumController`, `Move`/`Merge`/`Transfer`/`Delete`/`SetProtectionPolicy`, `BulkEditAlbumsAction`, `SharingController`/`Propagate`, `UserGroupsManagementController`, `RecomputeAlbumStatsJob`/`RecomputeAlbumSizeJob`, `SettingsController`, `PhotosToBeDeletedDTO`, `FixTree`, `ApplyNsfwAlbumSensitivityJob`, `MergeTag`/`DeleteTag`).
- **Listeners** (`app/Listeners/`) - Event handlers
  - `RecomputeAlbumStatsOnPhotoChange` - Dispatches recomputation job for photo's album
  - `RecomputeAlbumStatsOnAlbumChange` - Dispatches recomputation job for parent album
  - `ManagedCacheAlbumListingInvalidator` (Feature 053, extended Feature 057/061) - Reacts to `AlbumSaved`/`AlbumDeleted`/`AlbumChildrenChanged`/`TagAlbumSaved`/`PersonAlbumSaved`/`BaseAlbumRemoved`/`AccessPermissionChanged`/`AlbumComputedDataUpdated`/`AlbumListingCacheFlushRequested`/`AlbumTagsChanged`/`PhotoPersonsChanged`/`PhotoMoved`/`PhotoSaved`/`PhotoWillBeDeleted` (14 mappings); evicts the precise `ManagedCacheService` tag(s) each event implies (own `album:{id}` tag, parent's `album-children:{parent|'root'}` tag, and/or one of `pinned-albums-listing`/`tag-albums-listing`/`person-albums-listing`/`person:{id}`), falling back to the coarse `album-listing-global` tag only for `AlbumListingCacheFlushRequested` (genuinely rare/subtree-wide operations). Resolves an id's tree parent/type via a lightweight `DB::table()` lookup when the event payload doesn't already carry it (`AccessPermissionChanged`). Feature 057: every one of the 14 handlers *also* unconditionally evicts the coarse `album-listing-v3` tag (FR-057-06) — a deliberately coarse, over-eviction-tolerant choice (whole v3 listing cache, all users/flag-combinations, flushed together) favoring implementation simplicity over precision. Feature 061 (FR-061-22): `handleAccessPermissionChanged()` additionally evicts the *changed album's own* `album-children:{id}` tag (not just its parent's) — the rights endpoint's cache is the first consumer whose contents (`can_delete_children`/`can_move_children`) depend on grants against the queried album itself, not just its children.
  - `ManagedCacheUserListingInvalidator` (Feature 053) - Reacts to `UserGroupMembershipChanged`; evicts the affected user's `user:{id}` `ManagedCacheService` tag
  - `ManagedCachePhotoListingInvalidator` (Feature 064, FR-064-15) - Reacts to `PhotoSaved` (resolves every album a saved photo is currently linked into via `photo_album`, evicts each), `PhotoMoved` (evicts both source and destination album), `PhotoDeleted` (evicts that one album), and a new, dedicated `AlbumPhotoSortingChanged` event (mirrors `AlbumChildrenChanged`'s role for the album-bucket case — deliberately not the generic `AlbumSaved`, which fires for many unrelated reasons and would over-invalidate) dispatched from `AlbumController::updateAlbum()` alongside `RecomputeAlbumPhotoBucketsJob::dispatchIf(...)`, same guard boolean. Evicts `CacheKeyProvider::photoListingTag($album_id)` — a tag distinct from (not a member of) the album-listing tags `ManagedCacheAlbumListingInvalidator` manages.
- **Services** (`app/Services/Cache/`)
  - `ManagedCacheService` (Feature 052, extended Feature 053) - Generic `remember(key, tags, ttl, callback)`/`rememberIf(condition, key, tags, ttl, callback)`/`forgetTag(tag)`/`addTags(key, tags)` memoize-with-tag-eviction service, independent of `RouteCacher`/HTTP. Tags are hand-rolled key-list bookkeeping (no native cache-tagging store required — works on the default `file` driver). Gated by config `managed_cache_enabled` (default `true`) + `managed_cache_ttl` (both `Mod Cache` category, visible regardless of the unrelated `features.enable-caching` flag) and, for album-listing consumers specifically, the additional `managed_cache_albums_enabled` toggle (ANDed with the master switch). Adopted by six independently-cached queries: `AlbumRepository::getChildrenPaginated()`; each of `Actions\Albums\Top::get()`'s four constituent queries (tag albums, person albums, pinned albums, root/shared albums — `Top::get()` itself is never wrapped, each sub-query carries its own type-discriminating key prefix to avoid collisions); and `GetTagWithPhotosAndAlbums::getAccessibleAlbums()` (key additionally hashes the session-scoped `AlbumPolicy::getUnlockedAlbumIDs()` set). v2's own photo-listing (`PhotoRepository::getPhotosForAlbumPaginated()`) remains uncached, a deferred Non-Goal — the v3 photo-listing tiers (Feature 064, `PhotoChildrenController`) reuse the same `managed_cache_albums_enabled` toggle instead, via a dedicated `photoListingTag($album_id)`.
- **Jobs** (`app/Jobs/`) - Asynchronous task definitions
  - `RecomputeAlbumStatsJob` - Recomputes album statistics and propagates changes to ancestors; extended (Feature 061) to also compute `bucket_id` via `AlbumBucketComputer`, resolving the album's own **parent's** effective sort column/granularity (or the instance-wide default for a root album)
  - `RecomputeChildAlbumBucketsJob` (Feature 061) - `(string $parent_album_id)`; recomputes `bucket_id` for every direct child of one parent in a single bulk `upsert()` (one `SELECT` + one write query, never one save per child). Dispatched from `AlbumController::updateAlbum()`'s write site when the saved album's own `album_sorting_col`/`album_sorting_order`/`album_timeline` changed (`wasChanged()`, post-`SetHeader::do()` save) — those settings govern the album's *children's* buckets, not its own.
  - `RecomputeRootAlbumBucketsJob` (Feature 062, FR-062-07) - No constructor argument; recomputes `bucket_id` for every root album (`whereIsRoot()`) in one bulk `upsert()`, using the *instance-wide* `AlbumSortingCriterion::createDefault()` sort column + `AlbumBucketComputer::resolveGranularity(null)` granularity (root has no parent to defer to). Dispatched from `SettingsController::setConfigs()` (`ROOT_ALBUM_BUCKET_RECOMPUTE_CONFIGS` key-intersection: `sorting_albums_col`/`sorting_albums_order`/`timeline_albums_granularity`/`title_bucket_mode`/`title_bucket_prefix_length`) — closes the propagation gap `RecomputeChildAlbumBucketsJob` deliberately leaves (parent-scoped only). Only ever affects `own`-scope buckets; `shared` scope is always computed live and needs no recompute path.
  - `RecomputeAlbumPhotoBucketsJob` (Feature 064, FR-064-03c) - `(string $album_id)`; recomputes `photo_album.bucket_id` for every direct photo of one album in a single bulk `upsert()`, mirroring `RecomputeChildAlbumBucketsJob`'s one-`SELECT`-plus-bulk-write shape. Dispatched from `AlbumController::updateAlbum()`'s write site when the saved album's own `sorting_col`/`sorting_order`/`photo_timeline` changed (`$album->base_class->wasChanged([...])` — these 3 columns live on `base_albums`, not `albums`, so the dirty-tracking that matters is on `base_class`, not the outer `Album`/`BaseAlbum` instance itself, unlike `album_sorting_col`/`album_timeline` above).
  - `RecomputePhotoBucketsJob` (Feature 064, FR-064-03b) - `(string $photo_id)`; recomputes `photo_album.bucket_id` for every album one photo is linked into, in a single bulk `upsert()` — unlike the job above, it cannot resolve one shared sort setting up front (each linked album may have different settings), so it raw-joins `base_albums` per row instead of N lazy Eloquent loads. Dispatched explicitly (no Eloquent observer, per repo convention) from `PhotoController::update()`/`rename()`/`highlight()` and `Rating::do()`, each guarded by its own `wasChanged([...])` check on the specific bucket-relevant column(s) that write site touches.
  - `ScanFacesJob` - Dispatches face detection requests to the Python AI Vision service for a batch of photo IDs; sets `face_scan_status = pending` on dispatch, `scanned` on completion
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
- **UI Components** (`resources/js/v7/components/`) - PrimeVue-based interface elements (migrating to Nuxt UI, Feature 049 — see Frontend Dependencies below; parallel Nuxt UI tree lives at `resources/js/v8/components/`)
  - Gallery components (album, photo, flow, search modules)
    - **PhotoThumbPanelControl** - Layout selector with star rating filter (Feature 006)
      - 5 clickable stars for minimum rating threshold filter
      - Conditional rendering (hidden when no rated photos)
      - Toggle behavior (click same star to clear)
      - Keyboard accessible (Arrow keys, Enter/Space)
    - **FaceOverlay** (`photoModule/FaceOverlay.vue`) - Absolutely-positioned bounding boxes over the photo detail view; opens `FaceAssignmentModal` on click of unknown faces
  - Forms, modals, drawers, settings components
    - **FaceAssignmentModal** - Assign an unknown face to an existing or new Person; shows suggestions ranked by confidence
    - **SelfieClaimModal** - Upload a selfie to match and claim a Person profile (links Person ↔ User)
  - Maintenance components
    - **MaintenanceBulkScanFaces** - Card to trigger a bulk scan of all unscanned photos
- **Views** (`resources/js/v7/views/`) - Page-level Vue components (Nuxt UI twins at `resources/js/v8/views/`, Feature 049)
  - Gallery views: Albums, Album, Favourites, Flow, Frame, Map, Search
  - Admin views (`resources/js/v7/views/admin/`): AdminDashboard, Settings, Users, UserGroups, Purchasables, ContactMessages, Webhooks, Moderation, Maintenance, Jobs
  - Diagnostics remains at top-level (`views/Diagnostics.vue`)
  - People views: **People** (`/people`) — paginated PersonCard grid; **PersonDetail** (`/people/:personId`) — photos grid with edit/delete/merge actions
  - **AdminSetupPage** (`/setup-admin`, v8-only, Feature 051) — first-admin creation form shown instead of the legacy Blade `install/admin` page when `nuxt_ui` is active and no admin user exists yet; posts to `POST /Admin::Setup`, then navigates to `gallery` on success. Registered in the shared `router/paths.ts` manifest; v7's `componentByName` lookup falls back to a new `Placeholder.vue` for this and any other unmapped route name.
- **Composables** (`resources/js/composables/`) - Reusable composition functions
  - Album, photo, search, selection, context menu composables
  - **useAdminTiles** (`resources/js/composables/useAdminTiles.ts`) - Returns `AdminTile[]` with per-tile visibility driven by capability flags; used by `AdminDashboard.vue`.
- **Services** (`resources/js/services/`) - API communication layer using axios
  - `admin-stats-service.ts` - `getStats(force)` → `GET /api/v2/Admin/Stats`
  - `people-service.ts` - CRUD for Person; claim/unclaim; merge; selfie upload
  - `face-detection-service.ts` - Scan photos/albums; assign/dismiss faces; bulk scan
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

### External Services
- **AI Vision Service** (`ai-vision-service/`) - Sidecar Python microservice for facial recognition
  - Framework: FastAPI + uv package manager
  - Single shared symmetric API key (`AI_VISION_FACE_API_KEY` in Lychee / `VISION_FACE_API_KEY` in Python) used in both directions via `X-API-Key` header
  - File access: reads photos from a **shared Docker volume** (no file transfer over HTTP)
  - Embeddings persisted to a separate named volume (`ai_vision_embeddings`)
  - Supporter Edition (SE) feature: endpoints return 403 on non-SE instances

### Frontend Dependencies
- **Vue3** - Progressive JavaScript framework (Composition API)
- **TypeScript** - Type-safe JavaScript
- **PrimeVue** - UI component library (235 of 286 frontend files, as of 2026-07-02). Being replaced by **Nuxt UI** (`@nuxt/ui`, standalone Vue mode) — see Feature 049 (`docs/specs/4-architecture/features/049-nuxt-ui-migration/`), spec/plan/tasks drafted, implementation not yet started. Update this entry to reference Nuxt UI once Feature 049's dependency-removal task (T-049-41) lands.
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
   - `lychee:recompute-buckets` (Features 061, 064) - Joins what used to be two separate commands into one. Album pass bulk-recomputes `bucket_id` for every album, `albums`/`base_albums`-only (never queries `photos`), via one chunked self-join query (`chunkById`) + one `upsert()` per chunk. Photo pass bulk-recomputes `bucket_id` for every `photo_album` row, `photos`/`photo_album`/`base_albums`-only (never queries `size_variants`/`tags`, NFR-064-02), via one self-join query + offset-based `chunk()` (not `chunkById()` — `photo_album`'s composite `(photo_id, album_id)` PK has no single-column unique id to cursor-paginate on without risking a photo's multi-album row-group splitting across a page boundary) + one `upsert()` per chunk. Run at initial deploy (backfill) and after any instance-wide `sorting_albums_col`/`timeline_albums_granularity`/`title_bucket_mode`/`title_bucket_prefix_length`/`sorting_photos_col`/`timeline_photos_granularity`/`photo_title_bucket_mode`/`photo_title_bucket_prefix_length` default change — the title-bucket configs carry no per-album/per-photo change trigger, so this command is their *only* propagation path.
   - `lychee:sync {paths*}` - Sync files and directories to Lychee (Feature 024): accepts both directory paths (tree-based import) and individual file paths (direct import via `Exec::doFiles()`); mixed invocations supported; `--album_id` optional

**Benefits**: 50%+ query time reduction for album listings, removes expensive nested set JOINs from read path

### Multi-Track Albums (Feature 055)
Replaces the single nullable `albums.track_short_path` column with a `tracks` child table (`app/Models/Track.php`), the same one-parent-many-children shape as `size_variants`/`album_size_statistics`: own auto-increment `id`, `album_id` FK (`onDelete('cascade')` as a DB-level safety net only), `disk` cast to `StorageDiskType`.

**Primary-track compatibility mechanism:** v7's single-track UI (`AlbumHeader.vue`, `contextMenuAlbumAdd.ts`, `Map.vue`, `album-service.ts`'s `uploadTrack`/`deleteTrack`) is untouched. It transparently operates on the album's "primary" track — an explicit `tracks.is_primary` boolean (no `ofMany`/`oldestOfMany` relation; that construct has zero prior usage anywhere in this codebase), maintained transactionally by application code:
- Creation (backfill migration, legacy upload, v8 batch upload) marks a row primary only if it is the album's first track.
- Deletion of the primary promotes the next-oldest remaining track (`ORDER BY id ASC LIMIT 1`) in the same transaction.

`Album::tracks(): HasMany<Track>` / `Album::primaryTrack(): HasOne<Track>` (`where('is_primary', true)`). v8 gets a forked `resources/js/v8/services/track-service.ts` and a new `AlbumTracks.vue` section inside the existing Album Settings modal — **never edit the shared `album-service.ts` or v7 tree for track changes; fork instead**, per this repo's general v8-migration convention.

**Delete cleanup:** `Actions/Album/Delete` collects all tracks recursively (not just one per album), grouped by `disk`, dispatching one `FileDeleterJob` per distinct disk (previously hardcoded to `StorageDiskType::LOCAL`, silently leaking S3-stored files). The `tracks` row cleanup lives in `AlbumsToBeDeletedDTO::executeDelete()`'s chunked dependents block (where `Schema::disableForeignKeyConstraints()` is active, so the FK cascade never fires during bulk deletes) — mirroring the existing `album_size_statistics` cleanup line there, not in `Delete.php` itself.

### Database-Driven Title Sorting (Feature 060)
Replaces PHP-level (`SORT_NATURAL | SORT_FLAG_CASE`) title/description sorting with a single SQL `ORDER BY` for both `photos` and `base_albums`. `title` itself is untouched; two new derived, indexed columns — `title_base` (case-folded non-digit prefix) and `title_index` (trailing numeric suffix, nullable) — are computed by the stateless `App\Services\TitleSplitter::split()` and stored alongside it.

`TitleSplitter` is called **explicitly at each of 12 write sites** (photo upload/import pipeline, `PhotoController::update()`/`rename()`, `Actions/Renamer/RenamePhotos.php`; album `Create`/`CreateTagAlbum`/`CreatePersonAlbum`/`SetHeader`, `AlbumController::updateTagAlbum()`/`updatePersonAlbum()`/`rename()`, `Actions/Renamer/RenameAlbums.php`) — deliberately **not** an Eloquent model event/hook, per explicit user direction. `tests/Feature_v2/TitleSplitIntegrityTest.php` is the permanent regression guard for a future write site that forgets the explicit call.

`ColumnSortingType::TITLE` is the sole title-ordering entry point (`TITLE_STRICT`/`DESCRIPTION`/`DESCRIPTION_STRICT` removed); `getRawOrderExpression()` returns `{prefix}title_base {dir}, COALESCE({prefix}title_index, -1) {dir}`. `SortingDecorator::POSTPONE_COLUMNS`/`applyPhpSorting()` are deleted — sorting is 100% SQL now, fixing the previous pagination-reshuffle bug. The 5 relation `match()` methods that re-implemented natural sort for eager-loading were simplified to preserve DB-provided order instead.

### Timeline Struct-of-Arrays (Feature 066)
Moves the v8 global Timeline (`/timeline`, cross-album "all photos by date") off its v2 paginated API onto the same v3 SoA tiered API (`buckets`/`ratios`/`details`) per-album photo listing already uses (Feature 064/065), reached with **zero new routes** via `album_id='timeline'`.

**`App\SmartAlbums\TimelineAlbum`** models the global scope as a new `AbstractAlbum` — structurally a `BaseSmartAlbum` subclass (so `App\Actions\Photo\StructOfArrays\ResolvesPhotoSource`'s existing dedup-safe `BaseSmartAlbum` branch, and the `Asset` endpoint, pick it up with zero changes), but `photos()` is **fully overridden** rather than reusing the inherited implementation — `BaseSmartAlbum::photos()` hardcodes `enable_smart_album_per_owner`/`hide_nsfw_in_smart_albums` gates Timeline has never used (its own, longstanding `hide_nsfw_in_timeline` key predates this feature), and `AlbumPolicy::canSee()` (every other smart album's access check) requires `may_upload` rights, unrelated to Timeline's real `timeline_photos_public`/`timeline_page_enabled` rule — `AlbumPolicy::canAccess()` gained a small, additive `TimelineAlbum` branch instead. `SmartAlbumType::TIMELINE` has `is_enabled(): false` so it never appears in the generic smart-album sidebar/listing, reachable only by an explicit `album_id`.

**Bucket-windowed fetch** is the one genuinely new v3 capability this feature introduces: a user's whole library can vastly exceed any single album's size, so unlike every other v3 tier-2 caller (whole-scope-at-once), Timeline's `ratios` tier is fetched incrementally, windowed by `bucket_ids[]` (new, optional, backward-compatible param — omitted, existing album callers are byte-for-byte unaffected) or resolved for a single `photo_ids[]` (deep-link bucket resolution). Live `bucket_id` computation for this global, non-materialized scope is SQL-pushdown bounded (`App\Services\PhotoBucketComputer::bucketDateRange()`, plain `mktime()`/`date()` overflow-normalization arithmetic, no Carbon) — not the full-library PHP row scan `TagAlbum`/`PersonAlbum` still use (safe only at album scale, deliberately left untouched).

**Cache invalidation** uses fine-grained per-bucket tags (`CacheKeyProvider::photoListingBucketTag()`) alongside the existing coarse per-album tag, so a photo save/move only evicts its own bucket's cached window — `ManagedCachePhotoListingInvalidator` resolves the touched photo's current bucket to do this; a delete (no `photo_ids` on the event) falls back to coarse-only eviction.

**Frontend**: `resources/js/stores/TimelineState.ts` hosts the v2 paginated logic and the new incremental v3 state side by side (mirrors `AlbumState.ts`'s own v2/v3 coexistence), dispatched via an `isTimelineSoaActive` getter on the same `is_struct_of_array_enabled` flag Feature 065 introduced. `resources/js/v8/components/gallery/albumModule/Virtualized/PhotoGridVirtual.vue` gained a `source: "album"|"timeline"` prop rather than a forked component — the album path is guarded behind `source==="album"` wherever the two diverge. Not-yet-loaded buckets render at a placeholder height computed by the *same* WASM layout primitive fed uniform `1.0`-ratio input (layout-mode-correct, not a guessed constant), cached per-bucket and only recomputed for the one bucket whose data actually changed. See `docs/specs/4-architecture/features/066-timeline-struct-of-arrays/` for full detail.

### Map Geo-Bucketing (Feature 067)
Moves the v8 Map (`/map`, `/map/{albumId}`) off its v2 unbounded whole-scope fetch (`App\Actions\Albums\PositionData`/`App\Actions\Album\PositionData`, every geotagged photo eager-loading `size_variants`/`statistics`/`palette`/`tags`/`rating` in one `->get()`) onto a new, purpose-built 2D grid-bucketing tier — the first bucket mechanism in this codebase that isn't 1D date/title truncation, since a map viewport's "what looks clustered" is a function of two continuous coordinates plus the current zoom level, not a static admin-configured granularity.

**`App\DTO\MapViewport`** (`north`/`south`/`east`/`west`/`zoom`) is the shared value object every Map query/request class passes around. `cellSizeForZoom(int $zoom): float` returns `360.0 / (2 ** $zoom)` — one Web-Mercator tile-width in degrees, deliberately tied to the same grid Leaflet's own tiles already use rather than an independent formula. `snapToGrid()` snaps the bounding box outward to whole grid cells at that cell size, idempotently — this happens **before** the value is used for either the SQL query or the cache key, since a cache keyed on raw, continuously-varying pan coordinates would almost never hit.

**`App\Actions\Map\ResolvesMapPhotoSource`** (trait, shared by `QueryMapBuckets`/`QueryMapPhotos`) reproduces `Albums\PositionData::do()`/`Album\PositionData::get()`'s exact candidate-row filters, minus their eager loads and `->get()` — root scope via `PhotoQueryPolicy::applySearchabilityFilter()` (`origin: null`, `hide_nsfw_in_map`-driven), album scope via `$album->photos()`/`all_photos()` unchanged. A shared `applyBoundingBoxFilter()` handles an antimeridian-crossing viewport (`west > east`) via `(longitude >= west OR longitude <= east)` instead of `whereBetween()`.

**`App\Actions\Map\QueryMapBuckets`** — one `GROUP BY FLOOR(latitude/$cell), FLOOR(longitude/$cell)` + `COUNT(*)`/`AVG(latitude)`/`AVG(longitude)`, `toBase()`-only; `FLOOR()` chosen over a per-driver date-truncation-style `match()` (like the 1D bucket mechanism needs) precisely because it needs none — identical syntax across sqlite/mysql/mariadb/pgsql.

**`App\Actions\Map\QueryMapPhotos`** — no per-cell grouping (Q-067-16, amended): a `SELECT DISTINCT` query over the viewport's bounding box (collapsing the row-per-membership fan-out an `include_sub_albums` join can produce) is bounded by `->limit(MAX_VIEWPORT_PHOTOS + 1)` (`500`), and its rows are only returned if the fetched count is at/under that cap - otherwise the response is empty and the frontend falls back to `/Map/buckets`' aggregate badges. (A separate `count()` pre-check before an unbounded fetch was tried first and dropped: it raced against concurrent writes between the two queries and forced an unbounded `COUNT(*)` at low zoom.) Below the cap, every distinct photo in the viewport is returned and handed to the same `leaflet.markercluster`-based client-side clustering the v2 (non-SoA) map already uses, instead of being pre-bucketed into a grid server-side. A photo's `album_ids[i]` — load-bearing, since it feeds the Asset endpoint's `{album_id}` path segment — is resolved by a *separate* join+collapse pass, not the row-fetch itself: album scope picks the in-scope album with the lowest `_lft` per photo (no per-descendant access re-check, mirroring `all_photos()`'s own semantics); root scope (no "natural" album) joins `photo_album` → `base_albums` → `computed_access_permissions`, applies `AlbumQueryPolicy::appendAccessibilityConditions()`, and picks the lowest accessible `album_id` per photo. No `size_variants` join anywhere in this tier — leaf-tier marker imagery is fetched by the frontend directly from the existing v3 Asset endpoint (Feature 056) via `ThumbAssetService`, since that endpoint's own authorization already covers exactly the thumbnail-class variants Map ever requests, with no thumb-vs-full-photo split to reproduce.

**Cache invalidation** mirrors Feature 066's own precedent structurally, but scoped differently: `CacheKeyProvider::mapListingTag($scope)` (`'root'` or one `album_id`) is the per-scope tag; a new `mapListingGlobalTag()` — carried by every Map cache entry regardless of scope — lets a single `App\Events\MapListingCacheFlushRequested` (dispatched when `hide_nsfw_in_map`/`map_include_subalbums`/`map_display`/`map_display_public` changes, mirroring `AlbumListingCacheFlushRequested`'s Q-053-05 precedent) flush every scope at once with one `forgetTag()` call, no scope enumeration needed. `App\Listeners\ManagedCacheMapListingInvalidator` (a new, dedicated listener, not an extension of `ManagedCachePhotoListingInvalidator`) reacts to `PhotoSaved`/`PhotoMoved`/`PhotoDeleted`.

**Frontend**: `resources/js/stores/MapState.ts` hosts `bucketsV3`/`photosV3`/`tracksV3`, gated by the same `is_struct_of_array_enabled` flag every other v3 store reads, with a debounced `requestViewport(bounds, zoom)` that dedupes an identical/in-flight snapped-viewport request via a client-side mirror of `MapViewport::snapToGrid()` (purely for dedup-key stability — the server independently re-snaps, authoritatively). `Map.vue` wires Leaflet's `moveend`/`zoomend` to that action; a bucket whose `counts[i]` exceeds the leaf threshold renders as a plain count-badge marker (click → zoom in, never a photo fetch); a leaf-tier photo reuses the existing `clusterFunc()`/`.leaflet-marker-photo` marker+popup template byte-for-byte, except its image `src` — absent from the backend response by design (Q-067-12) — is assigned asynchronously once `ThumbAssetService.acquire()`'s object URL resolves, the same pattern `Thumb.vue` already uses. GPX track loading is decoupled from viewport changes entirely — fetched once per album context via `MapState.ts.loadTracks()`. See `docs/specs/4-architecture/features/067-map-geo-bucketing/` for full detail.

### Flow Struct-of-Arrays & Publish-Date Scheduling (Feature 068)
Ports Flow (`/flow`, the reverse-chronological public/private album feed) onto the same v3 SoA pattern (ADR-0009), while independently giving `flow_strategy=opt-in` an actual settable publish date it never had a UI for.

**`GET /api/v3/Flow`** is a new, standalone **unpaginated** listing (not a bucket-tiered route like the photo/timeline/map families above) — each album is loaded whole-scope in one request, closer to `GET /api/v3/Albums`'s (Feature 057/062) own unpaginated `toBase()` precedent than to a `buckets`-tier aggregate, since here every row already represents one whole album, not a reduced count. `App\Actions\Albums\Flow::do()` gained an optional `$with_relations = true` param (default preserves v2 byte-for-byte) so the v3 controller can reuse its exact query/policy/ordering logic while skipping the nested photo/cover/statistics eager-loads entirely. Each card's own photo preview is fetched separately and lazily — the existing `ratios` tier (Feature 064) gained an additive `limit` param, mutually exclusive with `bucket_ids[]`/`photo_ids[]`, so a card requests only its first 12 photos (a fixed frontend constant, no config key) once it actually scrolls into view.

**Publish-date scheduling** reuses `base_albums.published_at` in place — that column already existed and already drove Flow's opt-in ordering, but was also independently load-bearing for `LandingPageResource`'s automatic-featured-items ordering (Feature 054), so a new Flow-specific column was rejected in favor of extending the existing one (a new `published_at_orig_tz` companion column lets it be cast via the existing `DateTimeWithTimezoneCast`, mirroring `Photo::taken_at`'s pattern). A genuinely latent bug was found and fixed here: `published_at` had never been listed in `BaseAlbumImpl::$attributes`'s explicit default array, so `ForwardsToParentImplementation::setAttribute()`'s array-key-exists check silently routed a fresh-model write to the wrong (child `Album`) model — nothing had ever written to it via a fresh in-memory assignment before this feature's write path existed.

**Frontend**: `resources/js/stores/FlowState.ts` hosts the v2 store fields alongside new v3 additions (`flowV3`, `cardPhotosV3`/`cardLoadStateV3` per-card state, `requestCardPhotos()`), dispatched via an `isFlowSoaActive` getter on the same `is_struct_of_array_enabled` flag. `Flow.vue` branches its *entire* template (not just the inner list) between the two paths — the v3 branch deliberately omits the v2 branch's `h-svh overflow-y-auto` wrapper, since `useWindowVirtualizer` (this feature's virtualizer, matching `PhotoGridVirtual.vue`/`AlbumListViewVirtual.vue`/`Timeline.vue`'s own established choice) tracks the real `window` scroll position and a nested scrolling div desyncs its visibility calc (the same pitfall `Timeline.vue` documents). Unlike those three, this feature's card list is **dynamically measured** (`measureElement`, `@tanstack/virtual-core`'s ResizeObserver-driven remeasurement) rather than analytically pre-computed — a first for this codebase's virtualization stack — since card height depends on variable text/photo content, not a WASM-packed layout. A new, forked `AlbumCardV3.vue` (not a shared-component edit, since v2's `AlbumCard.vue`/`CarouselImages.vue`/`TopImages.vue`/`HeaderImage.vue` all expect pre-resolved `size_variants.*.url` strings the v3 per-card preview doesn't have) renders images via the existing `<Thumb>` component, with a loading skeleton shown while that card's own photo fetch is in flight. See `docs/specs/4-architecture/features/068-flow-soa-and-publish-scheduling/` for full detail, including the Decision Cards for why the tier ended up unpaginated (Q-068-04) and why `published_at` was reused rather than forked (Q-068-01).

### Search Struct-of-Arrays (Feature 069)
Completes the v3 SoA line (062/064/066/067/068) by porting the last un-migrated v8 gallery view. Four new routes under a dedicated **`GET /api/v3/Search/*`** family (`App\Http\Controllers\Gallery\SearchListingController`), chosen over a parameterised pseudo-album (Q-069-01): a "search album" would make album *identity* depend on request state and push `terms` onto request classes Features 064/066 already depend on — landing the blast radius on shipped code — and would still need a separate album route anyway.

**No bucket tier** (Q-069-02, owner chose against the recommendation). Search is the one photo consumer without one: its result is a single flat, whole-scope list. That removes the only ceiling search ever had, so the tier is bounded instead by `search_result_limit` — `search_pagination_limit` renamed in place by migration and repurposed from "photos per page" to "max hits per search" — fetched as `limit + 1` and reporting `is_truncated` when the extra row exists. **ADR-0010** was written off the back of this: ADR-0009 fixed v3 collections' *shape* but never their *size*, and three bounding strategies had accumulated across six features with no recorded rule; it names all four (whole-scope / bucket-windowed / capped-with-truncation / capped-with-refusal) and the rule for choosing.

**`App\Actions\Search\StructOfArrays\SearchPhotoSource`** (a class, not a trait like its `ResolvesPhotoSource`/`ResolvesMapPhotoSource` counterparts — it hangs off no `AbstractAlbum`) reuses `PhotoSearch`'s existing strategy registry verbatim for the *predicate* and changes only the projection. Two v2 defects are fixed here as documented divergences: `applySearchabilityFilter()` left-joins `photo_album`+`albums` with **no `distinct()`**, so a photo in N albums fans out to N rows and inflates v2's `total` — demoted to a `whereIn` id-subquery (the technique `ResolvesPhotoSource`'s `BaseSmartAlbum` branch already documents); and each row now carries a per-photo `album_ids[i]`, resolved by Feature 067's own join-and-collapse (Q-067-11) because a cross-album result has no single album to feed `<Thumb>`/the Asset endpoint. A photo the owner has in **no** album reports the `unsorted` smart album rather than being dropped (Q-069-11) — it is searchable in v2 via `owner_id`, so dropping it would be a silent membership change.

**Album half** reuses `AlbumDataResource` + `AlbumRightsResource` and `BuildAlbumDataResource` **unmodified**. The drift gate caught why that is not free: `applyBrowsabilityFilter()`, unlike `applyVisibilityFilter()`, never calls `prepareModelQueryOrFail()` and so joins neither `base_albums` (aliased) nor `computed_access_permissions`, which the builder selects `password` from. Rather than prepend `applyVisibilityFilter()` (which would add a predicate v2's search never applied), a new public `AlbumSearch::sqlQueryAlbums()` composes `joinBaseAlbumOwnerId()` + `joinSubComputedAccessPermissions()` directly — both LEFT joins, so membership is provably unchanged and only the column set grows. `AlbumRightsResource::$owner_id` was widened to `string|Optional`: a search spans arbitrarily many parents, so the key is omitted entirely, exactly as `/Albums/root/rights` already resolved for root (Q-062-16).

**Shared with Feature 064**: `QueryPhotoDetails` gained a strictly additive `fromQuery()` sibling so both `details` tiers converge on one projection and cannot drift; the three ratio `size_variants` joins moved verbatim into a `JoinsRatioSizeVariants` trait (their aliases are load-bearing for the `COALESCE` that reads them, so a second hand-written copy would be a silent-breakage risk). One inherited divergence is documented rather than papered over (Q-069-12): v2 computes `should_downgrade` once per request from `grants_full_photo_access`, while the shared projection evaluates `PhotoPolicy::CAN_ACCESS_FULL_PHOTO` per photo — so `size_variants.original.url` can be present in v2 and null in v3 for the same photo.

**Cache**: deliberately the bluntest invalidator in the codebase. Every entry carries one `searchListingTag()` and nothing finer, because a photo edited in one album can enter or leave a result scoped to a different album purely by matching — there is no album-shaped partition of the search cache that would be safe to keep warm. Keys embed a **digest of the parsed tokens**, never the raw term, so no user-entered text reaches a cache-event log.

**Frontend**: `resources/js/stores/SearchState.ts` holds the v3 tiles store-locally (`photoTilesV3`/`albumTilesV3`), compacting into `photosStore` only for lightbox compatibility — mirroring `TimelineState.ts`'s split, and fixing the browsing-data bleed `Search.vue` already carried defensive comments about. `PhotoGridVirtual.vue` gained `source="search"` as its third value (the seam Feature 065 designed): headers off, cover/header concepts off, and `album-id` resolved **per tile** rather than per grid, since the result spans albums. `adaptPhotoTile()`'s second parameter was widened to `Omit<PhotoRatioResource, "bucket_ids">` — it never read that field. `Search.vue` drops its nested `overflow-y-auto` wrapper on the v3 branch only (the `useWindowVirtualizer` desync pitfall `Timeline.vue`/`Flow.vue` both document) and `UPagination` disappears entirely. The album half renders through a new, non-virtualized `SearchAlbumGridV3.vue` reusing the prop-driven `AlbumThumbVirtual.vue` tile: `AlbumThumbGridVirtual` reads its data from the album-browsing stores, which a search must not touch. `SpotlightSearch.vue` stays on v2 (Q-069-04). See `docs/specs/4-architecture/features/069-search-struct-of-arrays/` for full detail.

### RAW Upload Pipeline (Feature 020)
Preserves original camera RAW / HEIC / PSD files as a dedicated size variant while converting to a displayable JPEG for the gallery.

**Size Variant numbering:** `RAW=0, ORIGINAL=1, MEDIUM2X=2, MEDIUM=3, SMALL2X=4, SMALL=5, THUMB2X=6, THUMB=7, PLACEHOLDER=8`

**Upload flow (Init pipes):**
1. `DetectAndStoreRaw` — if extension is in `CONVERTIBLE_RAW_EXTENSIONS` (`.nef`, `.cr2`, `.cr3`, `.arw`, `.dng`, `.orf`, `.rw2`, `.raf`, `.pef`, `.srw`, `.nrw`, `.psd`, `.heic`, `.heif`): stores original in `InitDTO::$raw_source_file`, converts to JPEG via `RawToJpeg`. On failure: graceful fallback (keeps original file as-is, no RAW variant). PDF files are explicitly excluded.
2. `AssertSupportedMedia` → `FetchLastModifiedTime` → `MayLoadFileMetadata` → `FindDuplicate` (unchanged)

**Upload flow (Standalone pipes, added after `CreateOriginalSizeVariant`):**
- `CreateRawSizeVariant` — if `$raw_source_file` is set in DTO: copies raw file to storage, creates DB row with `type=RAW(0)` and `0×0` dimensions.

**Key classes:**
- `app/Actions/Photo/Convert/RawToJpeg.php` — Imagick converter, quality 92, does NOT delete source
- `app/Actions/Photo/Pipes/Init/DetectAndStoreRaw.php` — replaces deleted `ConvertUnsupportedMedia`
- `app/Actions/Photo/Pipes/Standalone/CreateRawSizeVariant.php`

**Removed classes:** `HeifToJpeg`, `ConvertUnsupportedMedia`, `PhotoConverterFactory`, `ConvertableImageType`, `PhotoConverter` interface

**API:** `DownloadVariantType::RAW` maps to the RAW download endpoint gated by `raw_download_enabled` config. The frontend checks whether a RAW size variant exists in the `size_variants` response to conditionally show the download button.

**Migrations:** `2026_02_28_000001` (shift type values), `2026_02_28_000002` (add `raw_download_enabled` config), `2026_02_28_000003` (reclassify existing raw-format ORIGINAL rows), `2026_02_28_000004` (add `size_raw` to `album_size_statistics`)

### Size-Variant Output Format (Feature 071)
`size_variant_format` (`original|jpeg|webp`) is read by `App\Assets\BaseSizeVariantNamingStrategy::generateExtension()` through `App\Enum\SizeVariantFormat::extension()`. It forces the extension of generated variants only; `ORIGINAL`, `RAW` and `PLACEHOLDER` are exempt, and `WatermarkGroupedWithRandomSuffixNamingStrategy` keeps its own final JPEG rule. The handlers encode by target extension: `ImagickHandler` always does, while `GdHandler` does so for `.webp` only and otherwise still dispatches on the source type (Q-071-04). `compression_quality` is `int:0:100`, where `0` means lossless (`IMG_WEBP_LOSSLESS` / `webp:lossless`) and maps to quality `100` for other formats, via `BaseImageHandler::resolveQuality()`/`isLossless()`. See `docs/specs/4-architecture/features/071-size-variant-format/`.

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

### AI Vision Inter-Service Communication
Lychee uses a **REST + webhook** pattern for facial recognition:

1. **Scan trigger** — Lychee sets `face_scan_status = pending` on photo(s), dispatches `ScanFacesJob`
2. **Batch HTTP request** — Job sends `POST /detect` to Python service with an array of `{photo_id, file_path}` pairs (file paths resolve to the shared Docker volume mount)
3. **Async callback** — Python service POSTs results back to Lychee's internal callback endpoint with detected bounding boxes and embedding vectors
4. **DB write** — Lychee creates `Face` rows and `FaceSuggestion` rows; sets `face_scan_status = scanned`
5. **Cluster/compare** — On claim or merge, Lychee calls `POST /embeddings/compare` for similarity matching

Shared volume architecture:
```
./lychee/uploads  ──►  lychee_api:/app/public/uploads  (read/write)
                  ──►  ai_vision:/data/photos           (read-only)
```

### NSFW Detection Inter-Service Communication (Feature 045)
Lychee uses the same **REST + webhook** pattern for NSFW content detection:

1. **Scan trigger** — `AutoScanNsfwOnUpload` pipe snapshots `upload_trust_level` on the photo, dispatches `DispatchNsfwScanJob`
2. **HTTP request** — Job sends `POST /api/nsfw/detect` to the NSFW classification service with `{photo_id, photo_path, preset?}`
3. **Async callback** — Service POSTs results to `POST /api/v2/NsfwDetection/results` with detection arrays (`block_detected`, `review_detected`, `sensitive_detected`)
4. **Action matrix** — `NsfwActionService` applies trust-tier × finding-tier matrix: block findings can hard-delete or moderate; review findings moderate or approve; sensitive findings mark albums as NSFW
5. **Detection logging** — `NsfwDetection` rows stored with tier booleans (`is_block`, `is_review`, `is_sensitive`), deduplicated by photo+label+bbox

Key modules:
- **Enums**: `NsfwStatus`, `NsfwPreset`, `NsfwBlockFindingAction`, `NsfwSensitiveAlbumAction`, `NsfwSensitiveNoAlbumAction`, `NsfwDetectionLabel`
- **Services**: `NsfwDetectionService` (HTTP client), `NsfwActionService` (action matrix)
- **Jobs**: `DispatchNsfwScanJob`, `ApplyNsfwAlbumSensitivityJob`
- **Controller**: `NsfwDetectionController` (callback + bulk scan), `NsfwConfigController` (config proxy)
- **Pipe**: `AutoScanNsfwOnUpload` (upload pipeline integration)
- **Model**: `NsfwDetection` (detection audit log)

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
- [Configure Facial Recognition](../2-how-to/configure-facial-recognition.md) - Docker setup, shared volume, environment variables, and permission modes
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

*Last updated: July 28, 2026*
