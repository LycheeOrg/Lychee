# Feature 052 – Managed Cache Service

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-07-28 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/052-managed-cache-service/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/052-managed-cache-service/tasks.md` |
| Roadmap entry | #052 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Some values Lychee computes are expensive to recompute but depend on more than their literal inputs — most notably a user's effective access permissions on an album, which depend on the requesting user, the album, every ancestor album on its tree path (permissions inherit downward), and the user's group memberships. Today there is no general mechanism to memoize such a value and safely invalidate it later; the closest existing infrastructure, `RouteCacher`/`RouteCacheManager`/`CacheTag` (`app/Metadata/Cache/`), memoizes whole HTTP responses keyed by route + user and is wired only to a handful of album/photo mutation call sites.

This feature introduces `ManagedCacheService`: a small, general-purpose, key/value caching service (not scoped to SQL queries specifically, and not scoped to any particular domain concept) that lets a caller memoize the result of an arbitrary callable under an arbitrary key, while declaring a set of dependency **tags** the result depends on. Later, any code path can evict every cached entry tagged with a given value (e.g. `album:{id}` or `user:{id}`) in one call, without knowing which specific keys were affected. Because the underlying cache store (`CACHE_DRIVER=file` by default) has no native tagging primitive, tags are implemented as an application-level bookkeeping layer — a tag is itself a cache entry whose value is the set of member keys currently associated with it — mirroring the pattern `RouteCacher::rememberTags()`/`forgetTag()` (`app/Metadata/Cache/RouteCacher.php:142-149`) already proves out for the HTTP response cache, reimplemented independently so this service has no dependency on routes, requests, or `RouteCacheManager`'s per-URI config.

While investigating existing invalidation coverage, three real gaps were confirmed — none of them wired to any cache-invalidation (or, in two cases, any event at all) today:
1. `App\Actions\Album\Move::do()` (`app/Actions/Album/Move.php`) dispatches no event when an album is moved (re-parented or moved to root).
2. `App\Http\Controllers\Gallery\SharingController` (`create()`, `edit()`, `delete()`, `propagate()`) dispatches no event when `AccessPermission` rows are created, edited, deleted, or propagated.
3. `App\Http\Controllers\Admin\UserGroupsManagementController` (`addUser()`, `removeUser()`, `updateUserRole()`) dispatches no event when a user's group membership changes.

This feature closes all three gaps and wires their new events into `ManagedCacheService` invalidation, then proves the whole mechanism against two real, generic-shaped consumers: `AlbumRepository::getChildrenPaginated()` (`app/Repositories/AlbumRepository.php:43`, listing an album's sub-albums) and `PhotoRepository::getPhotosForAlbumPaginated()` (`app/Repositories/PhotoRepository.php:53`, listing an album's photos). Both are permission-filtered (`AlbumQueryPolicy::applyVisibilityFilter()` / `FiltersUploadValidation::applyUploadValidationFilter()`), user-dependent, and executed on essentially every album-view page load — and, notably, both are the exact same routes (`api/v2/Album::albums`, `api/v2/Album::photos`) the existing HTTP response cache already lists in `RouteCacheManager::cache_list` (`app/Metadata/Cache/RouteCacheManager.php:39-40`) but which run uncached today by default, since that cache is forced off (`cache_enabled = 0`, Feature 040). This new, independently-toggled service gives these two hot, permission-filtered queries a cache lifeline regardless of the HTTP response cache's default-off posture.

## Goals

1. Provide `App\Services\Cache\ManagedCacheService` with a generic `remember(string $key, array $tags, ttl, \Closure $callback): mixed` API and a `forgetTag(string $tag): void` API — the service itself has no knowledge of albums, users, or SQL; any caller (query result, computed value, external-call result, etc.) can use it.
2. Fix the three confirmed invalidation gaps by dispatching a new or existing domain event at each of the three mutation points above.
3. Wire those events (plus the existing `PhotoSaved`/`PhotoAdded`/`PhotoDeleted`/`PhotoMoved`/`AlbumDeleted` events, already dispatched today) into listeners that call `ManagedCacheService::forgetTag()` for the affected album/user tag(s).
4. Support ancestor-inclusive tagging so that a cached value depending on an album's *effective* (inherited) permissions is tagged with the album's own id and every ancestor id on its tree path at write time — making an ancestor's tag eviction reach all its descendants' cached entries without a runtime tree walk.
5. Prove the mechanism end-to-end by adopting it in exactly two real consumers, `AlbumRepository::getChildrenPaginated()` (sub-albums of an album) and `PhotoRepository::getPhotosForAlbumPaginated()` (photos of an album), without adding any other call sites in this feature.
6. Gate the service behind a new, independent admin-configurable toggle, `managed_cache_enabled` (default `true`), decoupled from Feature 040's `cache_enabled`.
7. Cover the service and every new invalidation trigger with tests (cache-hit path proves the callback isn't re-invoked; each trigger proves the relevant tag is actually evicted).

## Non-Goals

- Replacing, deprecating, or re-enabling the existing HTTP response cache (`cache_enabled`, `RouteCacher`, Feature 040) — untouched by this feature.
- Migrating any query besides the two pilot consumers (`AlbumRepository::getChildrenPaginated()`, `PhotoRepository::getPhotosForAlbumPaginated()`) onto the new service — broader adoption (e.g. `current_user_permissions()`, `AlbumPolicy`, `Search`) is left to future features/backlog.
- Any UI-facing change beyond the admin settings toggle for `managed_cache_enabled` — no end-user-visible behavior change.
- Changing the default cache driver/store (`CACHE_DRIVER`, currently `file`) or adding a new runtime dependency (no Redis/Memcached requirement introduced; consistent with the offline-only constraint).
- Building a native tagged-cache-store integration (e.g. requiring Redis) — tags are hand-rolled key-list bookkeeping on top of the plain key/value store, by design (Q-052-04 resolution).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-052-01 | `ManagedCacheService::remember(string $key, array $tags, \DateTimeInterface\|\DateInterval\|int\|null $ttl, \Closure $callback): mixed` returns the cached value for `$key` if present; otherwise calls `$callback()`, stores the result at `$key` with `$ttl`, and records `$key` against every tag in `$tags`. | Second call with the same `$key` returns the stored value without invoking `$callback()` again. | N/A — no user input to validate; `$tags` may be empty (value is cached but not tag-evictable). | If the cache store write throws, log and return `$callback()`'s value directly (mirrors `RouteCacher::remember()`'s existing failure handling, `app/Metadata/Cache/RouteCacher.php:63-68`). | None. | Problem statement: "cache some SQL queries instead of executing them"; Q-052-01/02 resolutions (generic service). |
| FR-052-02 | `ManagedCacheService::forgetTag(string $tag): void` evicts every cache key currently recorded under `$tag`, then removes the tag's own bookkeeping entry. | Calling `remember()` again with a previously-cached `$key` that was tagged with an evicted tag re-invokes `$callback()`. | N/A. | N/A — evicting an unknown/empty tag is a no-op. | None. | Problem statement: "clear the data when [dependencies] change." |
| FR-052-03 | `Actions\Album\Move::do()` dispatches `AlbumSaved` for every moved album (mirrors the existing dispatch sites in `Actions\Album\Create`/`Actions\Album\SetProtectionPolicy`) after `appendNode()`/`saveAsRoot()` completes. | Moving one or more albums dispatches one `AlbumSaved` event per moved album. | N/A. | N/A. | None. | Confirmed gap #1 (Overview); Q-052-04 resolution notes Move must dispatch an event regardless of cascade design. |
| FR-052-04 | `SharingController::create()`, `edit()`, `delete()`, and `propagate()` each dispatch a new `App\Events\AccessPermissionChanged` event (carrying the affected `base_album_id`) once per affected album after the mutation completes. | Creating/editing/deleting/propagating a share dispatches one `AccessPermissionChanged` event per affected `base_album_id`. | N/A. | N/A. | None. | Confirmed gap #2 (Overview); problem statement: "clear the data when access rights change." |
| FR-052-05 | `UserGroupsManagementController::addUser()`, `removeUser()`, and `updateUserRole()` each dispatch a new `App\Events\UserGroupMembershipChanged` event (carrying the affected `user_id`) after the mutation completes. | Adding/removing a user from a group, or changing their role, dispatches one `UserGroupMembershipChanged` event for that user. | N/A. | N/A. | None. | Confirmed gap #3; Q-052-05 resolution (in scope). |
| FR-052-06 | A listener reacts to `AlbumSaved`, `AlbumDeleted`, `AccessPermissionChanged`, `PhotoSaved`, `PhotoAdded`, `PhotoDeleted`, and `PhotoMoved` (photo events resolved to their album id(s) via the `photo_album` pivot, mirroring the existing lookup in `AlbumRouteCacheRefresher::handle()`, `app/Http/Middleware/Caching/AlbumRouteCacheRefresher.php:100-111`) and calls `ManagedCacheService::forgetTag("album:{id}")` for each affected album id **and** `ManagedCacheService::forgetTag("album:{parent_id}")` for that album's immediate parent (or `"album:root"` if it has none). **Exception (Q-052-06):** `AlbumDeleted` (`app/Events/AlbumDeleted.php`) carries only `?string $parent_id`, not the deleted album's own id, so for this event only the parent's tag is evicted; the deleted album's own tag, if any, is left to expire via TTL rather than being evicted explicitly. | Uploading, moving, or deleting a photo; moving, deleting, or changing protection on an album; or changing its sharing — each evicts that album's tag and its immediate parent's tag. Deleting an album evicts its parent's tag (its own tag, if any, expires via TTL). | N/A. | N/A. | None. | Problem statement's explicit trigger list: "access rights change... a photo is uploaded, an album is moved." Parent-tag eviction closes the "negative cache" gap in FR-052-09: a child that becomes newly visible (or newly hidden) must invalidate the parent's cached children list even though that list never contained the child's own tag. Q-052-06 resolution (Option A). |
| FR-052-07 | A listener reacts to `UserGroupMembershipChanged` and calls `ManagedCacheService::forgetTag("user:{id}")` for the affected user id. | Adding/removing a user from a group, or changing their role, evicts that user's tag. | N/A. | N/A. | None. | Q-052-05 resolution. |
| FR-052-08 | When caching a value that depends on album X's effective (inherited) permissions, the caller tags the entry with `"album:{X.id}"` **and** `"album:{A.id}"` for every ancestor `A` on X's root path (via `Album::ancestorsOf($id)`, the existing nested-set query scope, `app/Models/Album.php:98`), at write time. | Changing permissions on ancestor A (dispatch of `AccessPermissionChanged` for A) evicts A's tag, which also evicts a descendant D's cached entry that was tagged with A when written — with no explicit reference to D anywhere in the invalidation call. | N/A. | N/A. | None. | Q-052-04 resolution (ancestor-path tagging). |
| FR-052-09 | `AlbumRepository::getChildrenPaginated(?string $album_id, AlbumSortingCriterion $sorting, int $per_page)` wraps its query in `ManagedCacheService::remember()`, keyed by `"children:{album_id ?? 'root'}:{sorting->column}:{sorting->order}:{per_page}:{page}:{user_id ?? 'guest'}"` (page number read from the current request), tagged with `"album:{album_id ?? 'root'}"` (FR-052-06 parent-tag semantics) plus `"album:{child.id}"` for every child album actually present on the returned page, with TTL from `managed_cache_ttl`. | Listing an album's sub-albums twice with identical parameters for the same user executes the underlying query only once; the second call returns the cached `LengthAwarePaginator`. | N/A — root-level listing uses `album_id ?? 'root'` consistently in both key and tag. | If the cache store is unavailable, `remember()`'s existing failure handling (FR-052-01) falls back to always recomputing. | None. | Consumer scope revised per user instruction: sub-album listing (2026-07-21). |
| FR-052-10 | `PhotoRepository::getPhotosForAlbumPaginated(string $album_id, PhotoSortingCriterion $sorting, int $per_page, ?array $tag_ids, string $tag_logic, ?string $person_id)` wraps its query in `ManagedCacheService::remember()`, keyed by `"photos:{album_id}:{sorting->column}:{sorting->order}:{per_page}:{page}:{tag_ids joined}:{tag_logic}:{person_id}:{user_id ?? 'guest'}"`, tagged with `"album:{album_id}"`, with TTL from `managed_cache_ttl`. | Listing an album's photos twice with identical parameters for the same user executes the underlying query only once. | N/A. | If the cache store is unavailable, falls back to always recomputing (same as FR-052-01). | None. | Consumer scope revised per user instruction: photo listing (2026-07-21). |
| FR-052-11 | New config `managed_cache_enabled` (bool, default `true`) gates whether `remember()` reads/writes the cache store at all; when `false`, `remember()` always calls `$callback()` directly and performs no cache I/O. New config `managed_cache_ttl` (int seconds, default `3600`) is the default TTL used by FR-052-09/10. Both are admin-configurable in Settings, added under the existing `'Mod Cache'` config category (`config_categories` table). `SettingsController::getAll()`'s existing category-visibility filter (`app/Http/Controllers/Admin/SettingsController.php:74`, `->when(config('features.enable-request-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))`) is changed to exempt these two keys specifically, so they remain visible even when `features.enable-request-caching` is `false` (its default) — keeping `managed_cache_enabled` genuinely independent of Feature 040's `cache_enabled` while still sharing the category's UI grouping. | With `managed_cache_enabled=false`, both pilot consumers recompute on every call and no keys/tags are ever written. `managed_cache_enabled`/`managed_cache_ttl` remain visible in Settings regardless of `features.enable-request-caching`; every other `'Mod Cache'` row keeps its existing gating. | Feature test toggling the config and asserting cache I/O does/doesn't occur; feature test asserting the two keys are present in `SettingsController::getAll()`'s response with `features.enable-request-caching=false`. | N/A. | None. | Q-052-03 resolution (independent flag, decoupled from `cache_enabled`); Q-052-07 resolution (Option B — shared category, patched visibility filter). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-052-01 | No new runtime dependency; the service uses the existing `Cache` facade against whichever `CACHE_DRIVER` is configured (default `file`), with no requirement on a tag-capable store (Redis/Memcached). | Offline-only constraint — Lychee must work with zero network connection and no mandatory external service. | Code review: `ManagedCacheService` imports only `Illuminate\Support\Facades\Cache`; `make phpstan` / `php artisan test` pass with the default SQLite/file test configuration, no Redis required. | `config/cache.php` default driver. | Offline-only project constraint. |
| NFR-052-02 | PHPStan level 6 reports 0 errors; `php-cs-fixer` reports 0 violations; `php artisan test` passes with no regressions. | Standard quality gate for this repo. | `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run`, `php artisan test` all exit 0. | Existing tooling config. | AGENTS.md quality gate. |
| NFR-052-03 | A cache hit in `getChildrenPaginated()` or `getPhotosForAlbumPaginated()` performs strictly fewer DB queries than a cache miss (i.e., zero additional queries beyond the cache store read). | Performance — the entire point of the feature is to avoid re-executing these two queries, which already run on every album-view page load. | Feature test asserting query count via `DB::listen()`/`assertQueryCount`-style assertion on a repeated call, for both pilot consumers. | Eager-loaded relations in both repository methods (unchanged). | Problem statement: "cache some SQL queries instead of executing them." |
| NFR-052-04 | Every new invalidation trigger (Move, Sharing create/edit/delete/propagate, UserGroup membership add/remove/role-change, photo add/move/delete) has a feature test proving the relevant tag is evicted and a subsequent read recomputes, for both pilot consumers where applicable. | Correctness — an untested invalidation path is indistinguishable from a missing one until a stale-permission or stale-listing bug is reported in production. | `php artisan test --filter=ManagedCache` (or equivalent) covering S-052-02 through S-052-09 below. | `DatabaseTransactions` test base classes per AGENTS.md. | AGENTS.md test-first cadence; Q-052-04/05 resolutions. |
| NFR-052-05 | `LengthAwarePaginator` values returned by both pilot consumers must survive a cache round-trip (serialize/unserialize via the configured cache store) without error, including their contained `Album`/`Photo` Eloquent collections and eager-loaded relations. | The cached value type differs from a plain scalar/DTO — this is a new risk not present in a simple permission-merge result. | Unit/feature test asserting a cached-then-retrieved `LengthAwarePaginator`'s items, pagination metadata (`currentPage`, `perPage`, `total`), and eager-loaded relations are identical to a freshly-queried one. | Default `CACHE_DRIVER=file` (PHP `serialize()`/`unserialize()`); no lazy-loaded/Closure-bearing relations left unresolved on the cached models. | Identified during spec revision — caching whole paginators is a new class of value for this service vs. the original scalar-only pilot. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-052-01 | **Cache miss then hit (sub-albums).** First call to `getChildrenPaginated(P, ...)` for a given user executes the underlying query and caches the result. A second call with identical parameters for the same user returns the cached `LengthAwarePaginator` without re-executing the query. |
| S-052-01b | **Cache miss then hit (photos).** Same as S-052-01 but for `getPhotosForAlbumPaginated()`. |
| S-052-02 | **Sharing create invalidates.** `SharingController::create()` grants a new permission on album X to user U. `AccessPermissionChanged` fires for X; X's tag (and X's parent's tag, FR-052-06) is evicted; the next call to either pilot consumer touching X or X's parent recomputes and reflects the new grant. |
| S-052-03 | **Sharing edit invalidates.** Editing an existing `AccessPermission`'s grant flags evicts the album's tag (and its parent's tag) the same way. |
| S-052-04 | **Sharing delete invalidates.** Deleting a permission evicts the album's tag (and its parent's tag). |
| S-052-05 | **Sharing propagate invalidates every affected album.** Propagating permissions down a subtree dispatches `AccessPermissionChanged` once per album actually touched by the propagation (source + descendants), evicting each one's tag (and each one's parent's tag). |
| S-052-06 | **Album move invalidates the moved album and both parents.** Moving album X from parent P_old to parent P_new dispatches `AlbumSaved` for X; X's tag, P_old's tag, and P_new's tag are all evicted — P_old's children list no longer includes X, P_new's children list now does. |
| S-052-07 | **Ancestor change cascades to descendant without a tree walk.** A cached photos-list entry for descendant D was tagged with ancestor A's tag at write time (FR-052-08, if the pilot's own visibility depends on inherited permissions). Changing A's permissions evicts A's tag; D's cached entry is gone even though the invalidation call never mentioned D. |
| S-052-08 | **User-group membership change invalidates the user.** Adding, removing, or changing the role of user U in a group dispatches `UserGroupMembershipChanged`; U's tag is evicted; the next call to either pilot consumer for U recomputes. |
| S-052-08b | **Newly-visible child invalidates the parent's list (negative cache).** Album C is a child of P but currently hidden from user U (e.g. private). `getChildrenPaginated(P, ...)` is cached for U without C. C's permissions change to grant U access; `AccessPermissionChanged` fires for C; because the listener also evicts C's parent's tag (FR-052-06), P's cached children-list for U is evicted even though it never contained C's tag; the next call recomputes and now includes C. |
| S-052-09 | **Config disabled — no cache I/O.** With `managed_cache_enabled=false`, `remember()` always calls the callback directly; no keys or tags are ever written to the cache store. |
| S-052-10 | **TTL expiry.** An entry older than `managed_cache_ttl` is treated as absent on the next `remember()` call and is recomputed (standard `Cache::get()` TTL semantics — no bespoke expiry logic needed). |
| S-052-11 | **Guest/unauthenticated caller.** Either pilot consumer called with no authenticated user uses a fixed cache key segment (`'guest'`, not tied to a real `user_id`) and is never subject to `UserGroupMembershipChanged` eviction. |
| S-052-12 | **Photo upload/move/delete invalidates the containing album's photo list.** Uploading a photo into album X (`PhotoAdded`/`PhotoSaved`), moving a photo into/out of X (`PhotoMoved`), or deleting a photo from X (`PhotoDeleted`) evicts X's tag; the next `getPhotosForAlbumPaginated(X, ...)` call recomputes. |

## Test Strategy

- **Core (`ManagedCacheService`):** Unit tests for `remember()` (cache miss executes callback + stores value; cache hit does not re-invoke callback; multiple tags on one key; `forgetTag()` evicts all keys under a tag and the tag itself; evicting an unknown tag is a no-op).
- **Application (invalidation wiring):** Feature tests for each of S-052-02 through S-052-12 — dispatch the real controller/action call, assert the relevant tag's member keys are gone, and assert a subsequent call to `getChildrenPaginated()`/`getPhotosForAlbumPaginated()` re-executes the underlying query (query-count assertion, NFR-052-03).
- **REST:** No new routes; existing `Sharing`/`Album::move`/`UserGroups`/`Album::albums`/`Album::photos` endpoints are covered indirectly by the feature tests above exercising them end-to-end.
- **CLI:** None — no CLI surface for this feature.
- **UI (JS/Selenium):** None — `managed_cache_enabled`/`managed_cache_ttl` are plain settings rows using existing `BoolField`/numeric-field components; no new UI logic to test beyond existing settings-page coverage.
- **Docs/Contracts:** Update `docs/specs/4-architecture/knowledge-map.md` with `ManagedCacheService` and its listeners once implemented.

## Interface & Contract Catalogue

### Domain Objects

_None introduced — `ManagedCacheService` operates on plain scalars/arrays and whatever value type the caller's callback returns; no new persisted model or DTO is required._

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| SVC-052-01 | PHP service | `App\Services\Cache\ManagedCacheService::remember(string $key, array $tags, $ttl, \Closure $callback): mixed` | FR-052-01. Mirrors `RouteCacher::remember()`'s shape but with no `$route` parameter and no HTTP coupling. |
| SVC-052-02 | PHP service | `App\Services\Cache\ManagedCacheService::forgetTag(string $tag): void` | FR-052-02. |
| SVC-052-03 | PHP repository | `App\Repositories\AlbumRepository::getChildrenPaginated()` (unchanged signature, new caching wrapper) | FR-052-09. |
| SVC-052-04 | PHP repository | `App\Repositories\PhotoRepository::getPhotosForAlbumPaginated()` (unchanged signature, new caching wrapper) | FR-052-10. |

### Domain Events

| ID | Event | Description | Notes |
|----|-------|-------------|-------|
| EV-052-01 | `App\Events\AccessPermissionChanged` (new) | Dispatched by `SharingController::create()/edit()/delete()/propagate()`; carries `base_album_id`. | FR-052-04. |
| EV-052-02 | `App\Events\UserGroupMembershipChanged` (new) | Dispatched by `UserGroupsManagementController::addUser()/removeUser()/updateUserRole()`; carries `user_id`. | FR-052-05. |
| EV-052-03 | `App\Events\AlbumSaved` (existing, new dispatch site) | Now also dispatched by `Actions\Album\Move::do()` for each moved album. | FR-052-03. |

### Listeners

| ID | Listener | Reacts to | Action |
|----|----------|-----------|--------|
| LSN-052-01 | `App\Listeners\ManagedCacheAlbumInvalidator` (new) | `AlbumSaved`, `AlbumDeleted`, `AccessPermissionChanged`, `PhotoSaved`, `PhotoAdded`, `PhotoDeleted`, `PhotoMoved` | `ManagedCacheService::forgetTag("album:{id}")` for each affected album id (photo events resolved via `photo_album` pivot). |
| LSN-052-02 | `App\Listeners\ManagedCacheUserInvalidator` (new) | `UserGroupMembershipChanged` | `ManagedCacheService::forgetTag("user:{id}")`. |

### CLI Commands / Flags

_None introduced._

### Telemetry Events

_None introduced — this is an internal performance mechanism with no user-facing or audit telemetry._

### Fixtures & Sample Data

_None introduced._

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-052-01 | `managed_cache_enabled` toggle in Settings | Admin views/edits the toggle in the existing Settings page, under the `'Mod Cache'` category (same `BoolField` pattern as `cache_enabled`, and same category, per Q-052-07); default `true`; visible regardless of `features.enable-request-caching`. |
| UI-052-02 | `managed_cache_ttl` numeric field in Settings | Admin views/edits the default TTL (seconds), under the `'Mod Cache'` category; default `3600`; visible regardless of `features.enable-request-caching`. |

## Telemetry & Observability

None. This is an internal performance mechanism; no new telemetry events, redaction rules, or verbose-trace additions.

## Documentation Deliverables

- Update `docs/specs/4-architecture/knowledge-map.md` with `ManagedCacheService`, its two new events, and its two new listeners once implemented.
- Update `docs/specs/4-architecture/roadmap.md` Active Features entry as progress is made; move to Completed once done.
- Update `docs/specs/_current-session.md`.

## Fixtures & Sample Data

None.

## Spec DSL

```yaml
domain_events:
  - id: EV-052-01
    name: AccessPermissionChanged
    fields:
      - name: base_album_id
        type: string
  - id: EV-052-02
    name: UserGroupMembershipChanged
    fields:
      - name: user_id
        type: int
services:
  - id: SVC-052-01
    method: ManagedCacheService::remember
  - id: SVC-052-02
    method: ManagedCacheService::forgetTag
ui_states:
  - id: UI-052-01
    description: managed_cache_enabled toggle in Settings
  - id: UI-052-02
    description: managed_cache_ttl field in Settings
```

## Appendix

### Existing infrastructure this feature builds alongside (not replaces)

- `App\Metadata\Cache\RouteCacher` / `RouteCacheManager` / `App\Enum\CacheTag` — whole-HTTP-response cache, keyed by route + user, tagged by `CacheTag` + album id. Governed by config `cache_enabled` (forced off by default, Feature 040). Untouched by this feature.
- `App\Listeners\AlbumCacheCleaner` / `TaggedRouteCacheCleaner` — existing route-cache invalidation listeners. Untouched by this feature; `ManagedCacheAlbumInvalidator`/`ManagedCacheUserInvalidator` are new, separate listeners for the new service.
- Confirmed `AlbumSaved` dispatch sites (pre-existing): `Actions\Album\Create`, `Actions\Album\SetProtectionPolicy`, `Actions\Photo\MoveOrDuplicate`. **Not** dispatched by `Actions\Album\Move` until FR-052-03.
- `AccessPermission` mutations (`SharingController::create/edit/delete/propagate`) dispatch no event until FR-052-04.
- `UserGroup` membership mutations (`UserGroupsManagementController::addUser/removeUser/updateUserRole`) dispatch no event until FR-052-05.

### Why tags are hand-rolled key-lists, not a native cache-tagging feature

Laravel's `Cache::tags([...])->remember(...)` requires a tag-capable store (Redis or Memcached). This project's default `CACHE_DRIVER` is `file` (`config/cache.php:20`, `.env.example:113`), which does not support native tags, and introducing a hard Redis dependency would conflict with the offline-only / minimal-runtime-dependency posture. `ManagedCacheService` therefore reimplements the same "a tag is a cache key whose value is a set of member keys" bookkeeping `RouteCacher` already uses (`app/Metadata/Cache/RouteCacher.php:142-149`), independently, so it works unmodified on any Laravel cache driver.
