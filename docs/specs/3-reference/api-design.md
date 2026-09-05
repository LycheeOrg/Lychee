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

**GET** `/api/v3/Asset/{album_id}/{photo_id}/{size_variant}`

Retrieves a single photo size-variant's binary file (thumbnail through original), watermark-aware. Registered via `routes/api_v3.php` (`App\Http\Controllers\Gallery\PhotoAssetController::show()`, `App\Http\Requests\Photo\GetPhotoAssetRequest`), under the `api` middleware group but with `accept_content_type:json`/`content_type:json` opted out of on this route specifically (binary passthrough, not JSON) — a `json_errors` middleware (`App\Http\Middleware\EnsureJsonErrorResponses`) still forces every *error* response to render as Lychee's standard JSON error body regardless of the caller's actual `Accept` header.

**Path parameters:**

| Parameter | Type | Description |
|-----------|------|--------------|
| `album_id` | string | The (real/tag/person) album the photo is being viewed through — resolves the album-level access check alongside the photo-level one (`RandomIDRule`) |
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

### API v3: Album Virtual-Scroll Backend

Three `GET` endpoints, all scoped to one parent `album_id` (a route segment, e.g. `/api/v3/Albums/{album_id}`), together forming the backend contract for a future virtual-scrolled album grid — sticky date headers, per-tile render data, and background-fetched right-click permission signals. Backend-contract-only: no v8 frontend consumer exists yet. All three are gated by `modules.is_struct_of_array_enabled` (`STRUCT_OF_ARRAY_ENABLED`, the same flag as `GET /api/v3/Albums` above) checked in each request's own `authorize()`, enforced server-side (403 when off), not just at the frontend. All three cache through `ManagedCacheService`, tagged with the same `CacheKeyProvider::albumChildrenTag($album_id)` the v2 listing's cache already uses, so every existing invalidation call site also invalidates these; `ManagedCacheAlbumListingInvalidator::handleAccessPermissionChanged()` additionally evicts the *changed album's own* `albumChildrenTag()` (not just its parent's), since the rights endpoint below is a consumer whose cache depends on grants against the queried album itself.

The three routes below live at `/Albums/{album_id}[/buckets|/rights]`, mirroring root's own naming (see the next section). They are all registered on one `App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController` (methods `buckets()`/`index()`/`rights()`), alongside separate controllers for root/category listings — see below.

`album_id` resolves to a real `App\Models\Album`, a `TagAlbum`, or a `PersonAlbum` for tiers 2/3 (an unresolvable id, or a smart album, is a 404) — mirrored from v2's `App\Http\Requests\Album\GetAlbumChildrenRequest`. Tier 1 (buckets) stays regular-`Album`-only (a `TagAlbum`/`PersonAlbum` id is a 404 there): a materialized `bucket_id` groups cleanly for a real album's direct children (`parent_id`-scoped, one governing sort column/granularity), but a `TagAlbum`/`PersonAlbum`'s "children" are dynamically-matched real Albums scattered under different real parents, each already carrying a `bucket_id` computed against *its own* parent's settings — there is no single governing source for a bucket aggregate to group by, so bucketing does not extend to those two types.

For a `TagAlbum`/`PersonAlbum`, tiers 2/3 reuse the same "matching albums" query logic v2's `AlbumChildrenController`/`AlbumRepository::getMatchingAlbumsForTagPaginated()`/`getMatchingAlbumsForPersonPaginated()` already build (`AlbumRepository::queryMatchingAlbumsForTag()`/`queryMatchingAlbumsForPerson()`, extracted from those methods and reused unpaginated, `toBase()`-queried) — `AlbumQueryPolicy::applyBrowsabilityFilter()` scoped (not `applyVisibilityFilter()`, matching v2's own choice for this case), gated by the same `TA_albums_listing_enabled`/`PA_albums_listing_enabled` configs (disabled → empty result, not an error). Tier 3's `can_delete_children`/`can_move_children` are always `false` for these two types — a dynamically-matched, disparately-parented result set has no single shared parent whose `access_permissions` grants could uniformly apply the way a real album's direct children (`parent_id = album_id` for every one of them) do; `grants_edit`/`grants_download` stay per-child-meaningful (each matching album has its own real grants). `owner_id` is the `TagAlbum`/`PersonAlbum`'s own owner.

#### Tier 1 — `GET /api/v3/Albums/{album_id}/buckets`

Registered via `App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController::buckets()` / `App\Http\Requests\Album\GetAlbumBucketsRequest`. Returns bucket counts (never per-child data) for the parent's direct children, grouped by a **materialized** `albums.bucket_id` column — populated at write time (see `database-schema.md`), never a live `DATE_FORMAT`/`GROUP BY`-per-row computation — so the query is always a plain, index-served `GROUP BY bucket_id` (composite index `(parent_id, bucket_id)`), confirmed via `EXPLAIN QUERY PLAN` against a 7,000-child fixture. Lets a client size a scrollbar and render sticky headers before fetching a single child row.

**Response:** `AlbumBucketResource` (Struct-of-Arrays)
```json
{
  "bucket_ids": ["2024", "2023", "unknown"],
  "counts": [12, 8, 3],
  "labels": ["2024", "2023", "unknown"],
  "bucketable": true
}
```
- `bucket_ids`/`counts`/`labels` are parallel, index-aligned; ordered by a plain `ORDER BY bucket_id <dir>` (`<dir>` from the parent's own effective sort direction) — never routed through `SortingDecorator`/PHP natural sort, even when the parent sorts by `title`. The `"unknown"` entry (children whose `bucket_id` is `NULL` — no dated photos, or an unparseable title) always sorts last, regardless of `<dir>`.
- `labels[i]` is a ready-to-render display string for `bucket_ids[i]`, computed at read time (not materialized): `Carbon::parse(bucket_ids[i])->format($timeline_album_date_format_*)` for date-based sources, `bucket_ids[i]` verbatim for `alphabetical`-mode `title` sorting, and the literal string `"unknown"` for that entry (never `Carbon`-parsed).
- `bucketable: false` (all three arrays empty) when the parent's effective sort column is `OWNER_ID` — every direct child of one album always shares that album's `owner_id`, so grouping by it can never produce more than one bucket; the endpoint short-circuits without ever running a `GROUP BY`.

#### Tier 2 — `GET /api/v3/Albums/{album_id}`

Registered via `App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController::index()` / `App\Http\Requests\Album\GetAlbumChildrenDataRequest`. Returns the actual per-direct-child tile data, whole-album-at-once (no windowed pagination), as a single flat `toBase()` query with zero joins beyond `applyVisibilityFilter()`'s own baseline (`base_albums`, `computed_access_permissions`) plus one small additional left join for the album's own public access grant (`public_access_permissions` — see below) — confirmed via query-log capture (exactly one query against `albums`).

**Response:** `AlbumChildrenDataResource` (Struct-of-Arrays, one entry per visible direct child)
```json
{
  "ids": ["abc123"],
  "titles": ["Sub-album"],
  "descriptions": ["First 100 characters…"],
  "cover_ids": ["photo123"],
  "bucket_ids": ["2024"],
  "owner_ids": ["42"],
  "is_password_requireds": [false],
  "is_nsfws": [false],
  "is_pinneds": [false],
  "is_publics": [false],
  "is_link_requireds": [false],
  "has_subalbums": [false],
  "num_photos": [12],
  "num_subalbums": [0],
  "created_ats": ["2024-01-01T00:00:00Z"],
  "min_taken_ats": [null],
  "max_taken_ats": [null]
}
```
- `bucket_ids[i]` is that child's own `bucket_id` (`"unknown"` substituted for `NULL`) — the join key back to Tier 1: grouping this endpoint's children by `bucket_ids` and counting reproduces Tier 1's `{bucket_ids, counts}` exactly, for the same `(album_id, caller)`.
- `owner_ids[i]` is that child's own owner id — additive, present for both this sub-album tier and the root tier below.
- Rows are ordered by `bucket_id` first — exactly mirroring Tier 1's own `ORDER BY (bucket_id IS NULL) ASC, bucket_id <dir>`, `"unknown"` always last — then by the parent's effective sort criterion within each bucket; for a `TagAlbum`/`PersonAlbum` (no `bucket_id` concept), by the instance-wide default sort criterion instead. A client can therefore slice this endpoint's flat array into sections using Tier 1's own per-bucket `counts`, with zero client-side grouping or sorting.
- `descriptions[i]` is SQL-truncated to 100 characters (`SUBSTR(...)`, not PHP-side).
- `cover_ids[i]` resolves via the same priority rule as the Album Listing endpoint above (`App\Http\Controllers\Gallery\AlbumListController::resolveCoverId()`, reused unchanged). No thumbnail media `type`/blur `placeholder` field — resolving a cover to pixels is the caller's job via the Asset endpoint (`GET /api/v3/Asset/{album_id}/{photo_id}/{size_variant}` — see above).
- `is_pinneds[i]` is a plain `base_albums.is_pinned` column, added to the same narrow-column subquery `applyVisibilityFilter()`'s `base_albums` join already selects (`AlbumQueryPolicy::joinBaseAlbumOwnerId()`) — zero extra join, same pattern as `is_nsfws`.
- `is_publics[i]`/`is_link_requireds[i]` reflect the child album's own public/anonymous access grant, **independent of the requesting viewer** — not to be confused with `is_password_requireds[i]`, which reflects the *viewer's own effective* access via `computed_access_permissions`. Resolved via one additional left join, `public_access_permissions` (a narrow-column subquery over `access_permissions` pre-filtered to `user_id IS NULL AND user_group_id IS NULL` — a unique index on `(base_album_id, user_id_unique_key, user_group_id_unique_key)` guarantees at most one such row per album, so the join can never fan out the result set). `is_publics[i]` is `true` iff that row exists; `is_link_requireds[i]` is that row's own `is_link_required` column (`false` when no public grant exists at all). Matches `ThumbAlbumResource`/`AlbumProtectionPolicy::ofBaseAlbum()`'s existing `is_public`/`is_link_required` resolution exactly, just computed at the query layer instead of per-model. The subquery's column list is deliberately narrow (`base_album_id`, `is_link_required` only) rather than a raw table join — `access_permissions` carries its own `created_at`/`updated_at` timestamps that would otherwise collide with `SortingDecorator`'s unqualified `ORDER BY created_at`.

#### Tier 3 — pixels

Already served by the Asset endpoint documented above — no separate endpoint. A client fetches pixels per mounted tile using Tier 2's `cover_ids[i]`.

#### `GET /api/v3/Albums/{album_id}/rights`

Registered via `App\Http\Controllers\Gallery\AlbumListing\AlbumChildrenController::rights()` / `App\Http\Requests\Album\GetAlbumChildrenRightsRequest`. Meant to be fetched in the background immediately after Tier 2 renders (not on the right-click/selection event itself) — the permission signals a right-click/multi-select context menu on a selection of albums needs, with zero interaction-time latency.

**Response:** `AlbumChildrenRightsResource`
```json
{
  "owner_id": "42",
  "can_delete_children": false,
  "can_move_children": false,
  "ids": ["abc123"],
  "grants_edit": [false],
  "grants_download": [true]
}
```
- `owner_id`/`can_delete_children`/`can_move_children` are whole-response (one value, not an array) — uniform across every direct child, since both rights checks key off `parent_id`, which is `album_id` itself for every direct child. `can_delete_children` mirrors `AlbumPolicy::canDelete()`'s parent-scoped `AccessPermission` query verbatim; `can_move_children` reuses the same value (mirrors `AlbumRightsResource::can_move`'s existing reuse of the delete gate).
- `grants_edit`/`grants_download` are per-child, index-aligned with `ids` — the only rights components that genuinely vary per child (a subalbum can be individually shared independent of its siblings). Computed via one `LEFT JOIN` against `AlbumQueryPolicy::getComputedAccessPermissionSubQuery(full: true, user: $currentUser)` (reused from `AlbumListController.php:88`'s existing call site, with the real caller instead of the hardcoded public-only case), `GROUP BY` child id with `MAX()` per `grants_*` column — required, not optional: that subquery applies no internal `GROUP BY` in `full: true` mode, so a caller belonging to multiple groups with separate matching grants on the same child would otherwise produce duplicate joined rows; `MAX()` OR-merges them correctly.
- `grants_upload`/`grants_full_photo_access` and any combined `can_edit`/`can_download`/`can_upload`/`can_access_original`/`can_share`/`can_share_with_users`/`can_transfer` field are deliberately not transmitted — the client already knows its own identity (for the owner-based component of `can_edit`/`can_download`) and combines it with the transmitted `grants_*` flag itself; neither `can_upload` nor `can_access_original` is offered by the right-click menu this endpoint serves at all.
- Admin callers (`may_administrate`) short-circuit to every right `true` for every child, without the grants join or the `can_delete_children` `exists()` query ever running.

### API v3: Root & Category Album Listings

Eight `GET` endpoints, all under `App\Http\Controllers\Gallery\AlbumListing\*`, gated by the same `modules.is_struct_of_array_enabled` flag as the endpoints above. Root albums (`parent_id IS NULL`) get the same buckets/index/rights trio sub-albums have, plus a `scope` (`own`\|`shared`) request parameter reproducing today's `GET /api/v2/Albums` owned/shared partition; smart/person/tag/pinned albums get flat, un-bucketed listings. `GET /api/v2/Albums` (`AlbumsController`/`Top`/`RootAlbumResource`) is untouched — this is an additive v3 surface, not a replacement.

**`scope` request rule** (`GetScopedAlbumsRequest`, shared by root's three endpoints plus `/Albums/persons`/`/Albums/pinned`): an **authenticated** caller must pass exactly one of `own`\|`shared` (422 otherwise, no implicit default); an **unauthenticated** caller may omit it (defaults to `shared`) or pass `shared` explicitly — passing `own` as a guest is 422, never a silently-empty result. `/Albums/smart` and `/Albums/tags` take no `scope` at all (un-scoped, `GetAlbumCategoryRequest`).

#### `GET /api/v3/Albums/root[/buckets|/rights]`

Registered via `App\Http\Controllers\Gallery\AlbumListing\AlbumRootController` (`index()`/`buckets()`/`rights()`).

- **`scope=own`** behaves exactly like the sub-album tier above: `Album::query()->whereIsRoot()->where('base_albums.owner_id', $user->id)`, buckets grouped by the already-persisted, date/title-derived `bucket_id` column. Kept live against instance-wide sort/timeline/title-bucket config changes by `App\Jobs\RecomputeRootAlbumBucketsJob` (mirrors `RecomputeChildAlbumBucketsJob`'s one-`SELECT`-plus-bulk-`upsert()` shape; dispatched from `SettingsController::setConfigs()` whenever the saved keys intersect `sorting_albums_col`/`sorting_albums_order`/`timeline_albums_granularity`/`title_bucket_mode`/`title_bucket_prefix_length`).
- **`scope=shared`** groups by owner as the primary dimension via a **live, read-time `GROUP BY base_albums.owner_id`** — never the persisted `bucket_id` column, and `AlbumBucketComputer` is never invoked for this path (`albums.bucket_id` stays exclusively date/title-derived, system-wide, for every album, root included). The children-data endpoint's `bucket_ids[i]` field is, for this scope only, the row's own `owner_id` as a string — grouping response rows by `bucket_ids` still reproduces the buckets endpoint's own grouping, without ever writing owner data into the real `bucket_id` column. Rows are ordered by `owner_id` first, then the instance's normal sort criterion within each owner group. `bucketable` is **unconditionally `true`** for this scope, even for a zero-result query (empty `bucket_ids`/`counts`/`labels` arrays) — `bucketable` describes whether the grouping *mechanism* is available, not whether data happens to exist.
  - **Label resolution is authentication-gated:** an authenticated caller sees `COALESCE(users.display_name, users.username)` per owner (one `LEFT JOIN users`); a **guest** never executes that join at all — every label is hardcoded to the literal string `"unknown"`, though the grouping and counts stay real (a guest still learns *how many* distinct contributors there are, never their names). This is deliberate and easy to get backwards in a future edit — do not "fix" it to show real names to guests.
- **`GET /api/v3/Albums/root/rights`**: root has no single parent album's `access_permissions` to check `can_delete_children`/`can_move_children` against (heterogeneous ownership either way) — both flags are always `false` for a non-admin caller, `true` for an admin, regardless of scope. `owner_id` (present and real on the sub-album tier's own rights response) is **omitted from the JSON payload entirely** here (a `Spatie\LaravelData\Optional` field, not a serialized `null`) — unconditional across both `own` and `shared` scope, since root never has one single owner to report.

#### `GET /api/v3/Albums/smart` · `GET /api/v3/Albums/tags` · `GET /api/v3/Albums/tags/rights`

Registered via `App\Http\Controllers\Gallery\AlbumListing\AlbumCategoryController` (`smart()`/`tags()`/`tagsRights()`). Both listings return the shared, minimum-viable `AlbumCategoryListResource` (Struct-of-Arrays: `ids`/`titles`/`cover_ids`/`owner_ids`) — no `scope`, no bucket tier (these categories are curated/bounded by an admin's taxonomy or built-in definition, not by photo volume). `smart` reuses `AlbumFactory::getAllBuiltInSmartAlbums(false)` verbatim (the same in-memory, `Gate`-filtered list `Top::get()` already builds) — `owner_ids[i]` is always `"0"` (no real owner). `tags` mirrors `Top::queryTagAlbums()`'s filter/sort exactly, minus its `access_permissions`/`owner`/`userThumbRow.photo.size_variants` eager loads (`toBase()`-queried instead). `tags/rights` returns a flat `AlbumCategoryRightsResource` (`ids`/`grants_edit`/`grants_download`/`grants_delete` — no `can_delete_children`/`can_move_children` concept for a flat catalogue with no parent-child relationship).

#### `GET /api/v3/Albums/persons` · `GET /api/v3/Albums/pinned`

Registered via the same `AlbumCategoryController` (`persons()`/`pinned()`), each consuming `GetScopedAlbumsRequest` like root. Both mirror their respective `Top::queryPersonAlbums()`/`queryPinnedAlbums()` filter/join/sort exactly (`persons` keeps the `ai_vision_face_enabled`-off empty-block gate regardless of scope; `pinned` keeps its own separate `sorting_pinned_albums_col`/`sorting_pinned_albums_order` config, untouched by the `OWNER_ID` removal below, and is not restricted to root albums). Unlike root, **`shared` scope for these two is one flat, ungrouped list** — never a per-owner `GROUP BY`, and neither has a `/buckets` route: a pinned album's real tree position is arbitrary (its `bucket_id` is governed by whatever its *actual* parent's sort settings are), so a "pinned buckets" aggregate would mix incomparable dimensions from unrelated parts of the tree; `owner_ids[i]` in the flat listing still lets a client render a "shared by X" label per row without server-side grouping. No rights endpoint for either (a pinned root album already gets rights via `/Albums/root/rights`; a pinned sub-album via the sub-album tier's own `/rights`; person albums have no dedicated rights endpoint in this feature).

#### `ColumnSortingAlbumType::OWNER_ID`

`OWNER_ID` is not present in `ColumnSortingAlbumType` (the *configurable* `sorting_albums_col`/`album_sorting_col` enum) — it is not offered as a sortable column choice, and any surviving `owner_id` value (`configs.sorting_albums_col` or a per-album `albums.album_sorting_col` override) is rewritten to `created_at`. `ColumnSortingType::OWNER_ID` (the broader, internal enum used for the live `shared`-scope `ORDER BY owner_id` above, and `Top::queryRootAlbums()`'s existing hardcoded sort) is a separate enum and is unaffected. Deployers should run `lychee:recompute-album-buckets` after upgrading, so any row left `bucket_id=null` under a formerly-`OWNER_ID` effective column gets a real date/title value.

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

*Last updated: September 3, 2026*
