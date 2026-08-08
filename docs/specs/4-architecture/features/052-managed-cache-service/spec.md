# Feature 052 – Managed Cache Service

| Field | Value |
|-------|-------|
| Status | In Progress |
| Last updated | 2026-08-08 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/052-managed-cache-service/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/052-managed-cache-service/tasks.md` |
| Roadmap entry | #052 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Current Status (2026-08-08)

Only the generic caching primitive itself has been implemented: `App\Services\Cache\ManagedCacheService::remember()` / `forgetTag()` / `addTags()`, gated by a `managed_cache_enabled` check via constructor-injected `App\Repositories\ConfigManager`, covered by `tests/Unit/Services/Cache/ManagedCacheServiceTest.php`. The `managed_cache_enabled`/`managed_cache_ttl` config rows themselves have **not** been migrated into the `configs` table — outside of tests (which inject a mock `ConfigManager`), constructing the service against the real, DB-backed `ConfigManager` and calling `remember()`/`addTags()` will throw `ConfigurationKeyMissingException` until that migration exists.

The approach originally planned beyond the service — dispatching `AlbumSaved`/`AccessPermissionChanged`/`UserGroupMembershipChanged` events, wiring them into two dedicated invalidation listeners, and adopting the service in `AlbumRepository::getChildrenPaginated()`/`PhotoRepository::getPhotosForAlbumPaginated()` — was **reworked and not carried out**. The requirements, scenarios, and interfaces below describe only what is implemented today. The original, superseded design is preserved verbatim in the [Superseded Design](#superseded-design-not-implemented) appendix for historical reference — none of it is normative anymore.

The next iteration will instead introduce **per-part configuration of what gets cached** (i.e. admin-configurable toggles for which computations/queries use `ManagedCacheService`, rather than a fixed pair of pilot consumers wired via blanket event invalidation). That design has not been written up yet and is not reflected in this document.

## Overview

Some values Lychee computes are expensive to recompute but depend on more than their literal inputs — most notably a user's effective access permissions on an album, which depend on the requesting user, the album, every ancestor album on its tree path (permissions inherit downward), and the user's group memberships. There is no general mechanism to memoize such a value and safely invalidate it later; the closest existing infrastructure, `RouteCacher`/`RouteCacheManager`/`CacheTag` (`app/Metadata/Cache/`), memoizes whole HTTP responses keyed by route + user and is wired only to a handful of album/photo mutation call sites.

`ManagedCacheService` is a small, general-purpose, key/value caching service (not scoped to SQL queries specifically, and not scoped to any particular domain concept) that lets a caller memoize the result of an arbitrary callable under an arbitrary key, while declaring a set of dependency **tags** the result depends on. Later, any code path can evict every cached entry tagged with a given value (e.g. `album:{id}` or `user:{id}`) in one call, without knowing which specific keys were affected. Because the underlying cache store (`CACHE_DRIVER=file` by default) has no native tagging primitive, tags are implemented as an application-level bookkeeping layer — a tag is itself a cache entry whose value is the set of member keys currently associated with it — mirroring the pattern `RouteCacher::rememberTags()`/`forgetTag()` (`app/Metadata/Cache/RouteCacher.php:142-149`) already proves out for the HTTP response cache, reimplemented independently so this service has no dependency on routes, requests, or `RouteCacheManager`'s per-URI config.

No consumer has adopted `ManagedCacheService` yet, and no invalidation event wiring exists yet — see Current Status above.

## Goals

1. Provide `App\Services\Cache\ManagedCacheService` with a generic `remember(string $key, array $tags, ttl, \Closure $callback): mixed` API and a `forgetTag(string $tag): void` API — the service itself has no knowledge of albums, users, or SQL; any caller (query result, computed value, external-call result, etc.) can use it. **Done.**
2. Gate the service behind a config check, `managed_cache_enabled`, read via `ConfigManager`. **Done at the code level** (see Current Status for the config-row/Settings-UI caveat).
3. Cover the service with tests (cache-hit path proves the callback isn't re-invoked; multi-tag and `forgetTag()`/`addTags()` behavior covered). **Done** for the service itself.
4. *(Future, unspecified)* Let admins configure, per part of the application, whether that part's data is served through `ManagedCacheService`. Design and requirements TBD.

## Non-Goals

- Replacing, deprecating, or re-enabling the existing HTTP response cache (`cache_enabled`, `RouteCacher`, Feature 040) — untouched by this feature.
- Event-driven invalidation wiring (album move / sharing changes / group-membership changes) and adoption of the service in any repository or query (e.g. `AlbumRepository`, `PhotoRepository`) — part of the original design (see Superseded Design appendix), not implemented, and not currently planned as originally written. Superseded by the not-yet-specified per-part cache-configuration approach (Goal 4).
- Any UI-facing change — no config rows, Settings fields, or end-user-visible behavior exist yet.
- Changing the default cache driver/store (`CACHE_DRIVER`, currently `file`) or adding a new runtime dependency (no Redis/Memcached requirement introduced; consistent with the offline-only constraint).
- Building a native tagged-cache-store integration (e.g. requiring Redis) — tags are hand-rolled key-list bookkeeping on top of the plain key/value store, by design.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-052-01 | `ManagedCacheService::remember(string $key, array $tags, \DateTimeInterface\|\DateInterval\|int\|null $ttl, \Closure $callback): mixed` returns the cached value for `$key` if present; otherwise calls `$callback()`, stores the result at `$key` with `$ttl`, and records `$key` against every tag in `$tags`. | Second call with the same `$key` returns the stored value without invoking `$callback()` again. | N/A — no user input to validate; `$tags` may be empty (value is cached but not tag-evictable). | If the cache store write throws, log and return `$callback()`'s value directly (mirrors `RouteCacher::remember()`'s existing failure handling, `app/Metadata/Cache/RouteCacher.php:63-68`). | None. | Problem statement: "cache some SQL queries instead of executing them." Implemented, `app/Services/Cache/ManagedCacheService.php`. |
| FR-052-02 | `ManagedCacheService::forgetTag(string $tag): void` evicts every cache key currently recorded under `$tag`, then removes the tag's own bookkeeping entry. | Calling `remember()` again with a previously-cached `$key` that was tagged with an evicted tag re-invokes `$callback()`. | N/A. | N/A — evicting an unknown/empty tag is a no-op. | None. | Problem statement: "clear the data when [dependencies] change." Implemented. |
| FR-052-11 | `ManagedCacheService::remember()`/`addTags()` consult config key `managed_cache_enabled` (bool) via constructor-injected `ConfigManager::getValueAsBool()`; when it is `false`, `remember()` always calls `$callback()` directly and performs no cache I/O, and `addTags()` is a no-op. **Not yet implemented:** the `managed_cache_enabled`/`managed_cache_ttl` config rows (DB migration), the `'Mod Cache'` category exemption in `SettingsController::getAll()`'s visibility filter, and any Settings UI. Until the migration lands, constructing `ManagedCacheService` with the real, DB-backed `ConfigManager` and calling `remember()`/`addTags()` throws `ConfigurationKeyMissingException`; the shipped unit tests only exercise this gate against a mocked `ConfigManager`. | With a mocked `managed_cache_enabled=false`, both `remember()` and `addTags()` skip all cache I/O. | N/A. | N/A. | None. | Partially implemented; config rows/UI deferred. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-052-01 | No new runtime dependency; the service uses the existing `Cache` facade against whichever `CACHE_DRIVER` is configured (default `file`), with no requirement on a tag-capable store (Redis/Memcached). | Offline-only constraint — Lychee must work with zero network connection and no mandatory external service. | Code review: `ManagedCacheService` imports only `Illuminate\Support\Facades\Cache`; `make phpstan` / `php artisan test` pass with the default SQLite/file test configuration, no Redis required. | `config/cache.php` default driver. | Offline-only project constraint. Implemented. |
| NFR-052-02 | PHPStan level 6 reports 0 errors; `php-cs-fixer` reports 0 violations; `php artisan test` passes with no regressions. | Standard quality gate for this repo. | `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run`, `php artisan test` all exit 0. | Existing tooling config. | AGENTS.md quality gate. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-052-01 | **Cache miss then hit (generic).** First call to `remember(key, tags, ttl, callback)` executes `callback()` and caches the result. A second call with the same `key` returns the cached value without re-invoking `callback()`. Implemented, no real consumer yet. |
| S-052-09 | **Config disabled — no cache I/O.** With `managed_cache_enabled=false` (mocked in tests), `remember()`/`addTags()` always call/skip through directly; no keys or tags are ever written to the cache store. |

Additional service-level branches covered by the current unit tests but not part of the original scenario matrix: multiple tags recorded for one key and independently evicting it (`forgetTag`), evicting an unknown tag being a no-op, `addTags()` associating tags with an already-cached key vs. being a no-op on an uncached or disabled-cache key, and `forgetTag()` throwing `LycheeLogicException` if a recorded member key isn't a string.

All other scenarios from the original design (S-052-02 through S-052-08, S-052-08b, S-052-10 through S-052-12) depend on the invalidation wiring or pilot consumers described in the Superseded Design appendix and are not implemented.

## Test Strategy

- **Core (`ManagedCacheService`):** Unit tests for `remember()` (cache miss executes callback + stores value; cache hit does not re-invoke callback; multiple tags on one key; config-disabled skips all cache I/O), `addTags()` (associates tags with an already-cached key; no-op when uncached or disabled), and `forgetTag()` (evicts all keys under a tag and the tag itself; evicting an unknown tag is a no-op; throws on a non-string member key). All implemented in `tests/Unit/Services/Cache/ManagedCacheServiceTest.php`.
- **Application (invalidation wiring):** Not implemented — see Superseded Design appendix for the original plan.
- **REST:** No new routes.
- **CLI:** None — no CLI surface for this feature.
- **UI (JS/Selenium):** None — no config rows or Settings fields exist yet.
- **Docs/Contracts:** `docs/specs/4-architecture/knowledge-map.md` update still pending; deferred until the per-part cache-configuration design (Goal 4) is written and implemented.

## Interface & Contract Catalogue

### Domain Objects

_None introduced — `ManagedCacheService` operates on plain scalars/arrays and whatever value type the caller's callback returns; no new persisted model or DTO is required._

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| SVC-052-01 | PHP service | `App\Services\Cache\ManagedCacheService::remember(string $key, array $tags, $ttl, \Closure $callback): mixed` | FR-052-01. Mirrors `RouteCacher::remember()`'s shape but with no `$route` parameter and no HTTP coupling. Implemented. |
| SVC-052-02 | PHP service | `App\Services\Cache\ManagedCacheService::forgetTag(string $tag): void` | FR-052-02. Implemented. |
| SVC-052-05 | PHP service | `App\Services\Cache\ManagedCacheService::addTags(string $key, array $tags): void` | Associates additional tags with an already-cached key without recomputing/re-storing its value; no-op if the key isn't currently cached or the managed cache is disabled. Not in the original design (added because `remember()`'s tags-up-front signature can't express "tag with the id of every item in a computed result"); documenting it here now that it's implemented. |

### Domain Events

_None introduced yet — `AccessPermissionChanged`/`UserGroupMembershipChanged` and the new `AlbumSaved` dispatch site from the original design are not implemented. See Superseded Design appendix._

### Listeners

_None introduced yet — `ManagedCacheAlbumInvalidator`/`ManagedCacheUserInvalidator` from the original design are not implemented. See Superseded Design appendix._

### CLI Commands / Flags

_None introduced._

### Telemetry Events

_None introduced — this is an internal performance mechanism with no user-facing or audit telemetry._

### Fixtures & Sample Data

_None introduced._

### UI States

_None introduced yet — `managed_cache_enabled`/`managed_cache_ttl` are not yet migrated as config rows, so no Settings fields exist. See Superseded Design appendix for the original plan._

## Telemetry & Observability

None. This is an internal performance mechanism; no new telemetry events, redaction rules, or verbose-trace additions.

## Documentation Deliverables

- Update `docs/specs/4-architecture/knowledge-map.md` with `ManagedCacheService` once the per-part cache-configuration follow-up design is implemented.
- Update `docs/specs/4-architecture/roadmap.md` Active Features entry to reflect the reworked, in-progress status.
- Update `docs/specs/_current-session.md`.

## Fixtures & Sample Data

None.

## Spec DSL

```yaml
services:
  - id: SVC-052-01
    method: ManagedCacheService::remember
  - id: SVC-052-02
    method: ManagedCacheService::forgetTag
  - id: SVC-052-05
    method: ManagedCacheService::addTags
```

## Appendix

### Existing infrastructure this feature builds alongside (not replaces)

- `App\Metadata\Cache\RouteCacher` / `RouteCacheManager` / `App\Enum\CacheTag` — whole-HTTP-response cache, keyed by route + user, tagged by `CacheTag` + album id. Governed by config `cache_enabled` (forced off by default, Feature 040). Untouched by this feature.
- `App\Listeners\AlbumCacheCleaner` / `TaggedRouteCacheCleaner` — existing route-cache invalidation listeners, unrelated to `ManagedCacheService`.

### Why tags are hand-rolled key-lists, not a native cache-tagging feature

Laravel's `Cache::tags([...])->remember(...)` requires a tag-capable store (Redis or Memcached). This project's default `CACHE_DRIVER` is `file` (`config/cache.php:20`, `.env.example:113`), which does not support native tags, and introducing a hard Redis dependency would conflict with the offline-only / minimal-runtime-dependency posture. `ManagedCacheService` therefore reimplements the same "a tag is a cache key whose value is a set of member keys" bookkeeping `RouteCacher` already uses (`app/Metadata/Cache/RouteCacher.php:142-149`), independently, so it works unmodified on any Laravel cache driver.

### Superseded Design (not implemented)

The remainder of this appendix is the original 2026-07-28 design for invalidation wiring and pilot-consumer adoption. It was **not implemented** and the approach has been reworked (see Current Status). Kept verbatim for historical reference only — nothing below is normative.

**Original gaps this design set out to close, none wired to any cache invalidation (or, in two cases, any event at all):**
1. `App\Actions\Album\Move::do()` (`app/Actions/Album/Move.php`) dispatches no event when an album is moved (re-parented or moved to root).
2. `App\Http\Controllers\Gallery\SharingController` (`create()`, `edit()`, `delete()`, `propagate()`) dispatches no event when `AccessPermission` rows are created, edited, deleted, or propagated.
3. `App\Http\Controllers\Admin\UserGroupsManagementController` (`addUser()`, `removeUser()`, `updateUserRole()`) dispatches no event when a user's group membership changes.

**Original goals 2–5 (not pursued):**
- Fix the three gaps above by dispatching a new or existing domain event at each mutation point.
- Wire those events (plus the existing `PhotoSaved`/`PhotoAdded`/`PhotoDeleted`/`PhotoMoved`/`AlbumDeleted` events) into listeners calling `ManagedCacheService::forgetTag()` for the affected album/user tag(s).
- Support ancestor-inclusive tagging so a cached value depending on an album's effective (inherited) permissions is tagged with the album's own id and every ancestor id on its tree path at write time.
- Prove the mechanism end-to-end by adopting it in exactly two real consumers: `AlbumRepository::getChildrenPaginated()` and `PhotoRepository::getPhotosForAlbumPaginated()`.

**Original Functional Requirements (not implemented):**

| ID | Requirement |
|----|-------------|
| FR-052-03 | `Actions\Album\Move::do()` dispatches `AlbumSaved` for every moved album after `appendNode()`/`saveAsRoot()` completes. |
| FR-052-04 | `SharingController::create()`, `edit()`, `delete()`, and `propagate()` each dispatch a new `App\Events\AccessPermissionChanged` event (carrying the affected `base_album_id`) once per affected album after the mutation completes. |
| FR-052-05 | `UserGroupsManagementController::addUser()`, `removeUser()`, and `updateUserRole()` each dispatch a new `App\Events\UserGroupMembershipChanged` event (carrying the affected `user_id`) after the mutation completes. |
| FR-052-06 | A listener reacts to `AlbumSaved`, `AlbumDeleted`, `AccessPermissionChanged`, `PhotoSaved`, `PhotoAdded`, `PhotoDeleted`, and `PhotoMoved` and calls `ManagedCacheService::forgetTag("album:{id}")` for each affected album id and its immediate parent (or `"album:root"`). `AlbumDeleted` only carries `parent_id`, so only the parent's tag is evicted for that event. |
| FR-052-07 | A listener reacts to `UserGroupMembershipChanged` and calls `ManagedCacheService::forgetTag("user:{id}")` for the affected user id. |
| FR-052-08 | Values depending on an album's effective (inherited) permissions are tagged with the album's own id and every ancestor id on its tree path at write time. |
| FR-052-09 | `AlbumRepository::getChildrenPaginated()` wraps its query in `remember()`, tagged with the album's tag plus each returned child's tag, TTL from `managed_cache_ttl`. |
| FR-052-10 | `PhotoRepository::getPhotosForAlbumPaginated()` wraps its query in `remember()`, tagged with the album's tag, TTL from `managed_cache_ttl`. |

**Original Non-Functional Requirements (not implemented):**

| ID | Requirement |
|----|-------------|
| NFR-052-03 | A cache hit in `getChildrenPaginated()` or `getPhotosForAlbumPaginated()` performs zero additional DB queries beyond the cache store read. |
| NFR-052-04 | Every invalidation trigger (Move, Sharing create/edit/delete/propagate, UserGroup membership add/remove/role-change, photo add/move/delete) has a feature test proving the relevant tag is evicted and a subsequent read recomputes. |
| NFR-052-05 | `LengthAwarePaginator` values returned by both pilot consumers survive a cache round-trip (serialize/unserialize) without error, including contained Eloquent collections and eager-loaded relations. |

**Original Branch & Scenario Matrix (not implemented):**

| Scenario ID | Description |
|-------------|-------------|
| S-052-01b | Cache miss then hit (photos), same as generic S-052-01 but for `getPhotosForAlbumPaginated()`. |
| S-052-02 | Sharing create invalidates the album's tag (and its parent's). |
| S-052-03 | Sharing edit invalidates the album's tag (and its parent's). |
| S-052-04 | Sharing delete invalidates the album's tag (and its parent's). |
| S-052-05 | Sharing propagate invalidates every affected album (source + descendants). |
| S-052-06 | Album move invalidates the moved album and both old/new parents. |
| S-052-07 | Ancestor permission change cascades to a descendant's cached entry via ancestor-path tagging, with no runtime tree walk. |
| S-052-08 | User-group membership change invalidates the user's tag. |
| S-052-08b | A newly-visible child invalidates the parent's cached children list (negative cache), via the parent-tag eviction in FR-052-06. |
| S-052-10 | TTL expiry — an entry older than `managed_cache_ttl` is recomputed on the next `remember()` call. |
| S-052-11 | Guest/unauthenticated caller uses a fixed `'guest'` cache-key segment, never subject to `UserGroupMembershipChanged` eviction. |
| S-052-12 | Photo upload/move/delete invalidates the containing album's photo list. |

**Original Interface entries (not implemented):**

| ID | Description |
|----|-------------|
| SVC-052-03 (original) | `AlbumRepository::getChildrenPaginated()` wrapped in `remember()`. |
| SVC-052-04 (original) | `PhotoRepository::getPhotosForAlbumPaginated()` wrapped in `remember()`. |
| EV-052-01 | `App\Events\AccessPermissionChanged` (new), carries `base_album_id`. |
| EV-052-02 | `App\Events\UserGroupMembershipChanged` (new), carries `user_id`. |
| EV-052-03 | `App\Events\AlbumSaved` (existing event, new dispatch site in `Actions\Album\Move::do()`). |
| LSN-052-01 | `App\Listeners\ManagedCacheAlbumInvalidator` (new). |
| LSN-052-02 | `App\Listeners\ManagedCacheUserInvalidator` (new). |
| UI-052-01 | `managed_cache_enabled` toggle in Settings, under `'Mod Cache'`. |
| UI-052-02 | `managed_cache_ttl` numeric field in Settings, under `'Mod Cache'`. |

**Note on config category (Q-052-07):** the original design resolved to reuse the existing `'Mod Cache'` category for `managed_cache_enabled`/`managed_cache_ttl`, with a two-key exemption patched into `SettingsController::getAll()`'s visibility filter (`app/Http/Controllers/Admin/SettingsController.php:74`, which currently gates on `config('features.enable-caching')`). Neither the config rows, the category assignment, nor the filter patch exist yet.
