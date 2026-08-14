# Feature 052 Tasks – Managed Cache Service

_Status: In Progress — only `ManagedCacheService` itself (T-052-01..03) has been implemented so far. Approach was reworked: further work will add configuration of which parts are cached rather than proceeding straight through the original increments below._
_Last updated: 2026-08-08_

> Keep this checklist aligned with `plan.md`'s increments and `spec.md`'s normative sections. Tests are staged before implementation in every increment. Mark tasks `[x]` immediately after each one passes verification.
>
> **Rework note (2026-08-08):** T-052-04 through T-052-22 below were written for the original design (events, invalidation listeners, `AlbumRepository`/`PhotoRepository` adoption). That design is no longer being pursued as written — see `spec.md`'s "Superseded Design" appendix — so they've been moved to the Superseded section and unchecked, regardless of whether they were previously marked done. Only T-052-01..03 reflect code that actually exists in the repo today.

## Checklist

- [x] T-052-01 – Write failing unit tests for `ManagedCacheService` (FR-052-01, FR-052-02).
  _Intent:_ Cache miss executes callback + stores value; cache hit does not re-invoke callback; multiple tags on one key; `forgetTag()` evicts all member keys + itself; evicting an unknown tag is a no-op; `Cache::put()` exception falls back to the callback's return value.
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  _Notes:_ `tests/Unit/Services/Cache/ManagedCacheServiceTest.php`. Spec: FR-052-01/02.

- [x] T-052-02 – Implement `App\Services\Cache\ManagedCacheService` (FR-052-01, FR-052-02).
  _Intent:_ `remember(string $key, array $tags, $ttl, \Closure $callback): mixed`, `forgetTag(string $tag): void`, and `addTags(string $key, array $tags): void`, reimplementing `RouteCacher`'s key-list-as-tag pattern independently (no shared class, no `$route`/HTTP coupling).
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  - `make phpstan`
  _Notes:_ `app/Services/Cache/ManagedCacheService.php`. Spec: FR-052-01, FR-052-02, NFR-052-01, SVC-052-01/02/05.

- [x] T-052-03 – Gate `remember()`/`addTags()` on `managed_cache_enabled` config (FR-052-11, S-052-09).
  _Intent:_ `ManagedCacheService` constructor-injects `App\Repositories\ConfigManager` (DB-backed `configs` table, matching `MoneyService`'s DI pattern — **not** the `config()` helper). When `$this->config_manager->getValueAsBool('managed_cache_enabled') === false`, `remember()` always calls `$callback()` directly and performs no cache I/O; `addTags()` becomes a no-op.
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  _Notes:_ Landed alongside T-052-02. The `managed_cache_enabled`/`managed_cache_ttl` config rows themselves were never migrated (T-052-05 below is unchecked), so this gate is only exercised against a mocked `ConfigManager` in tests today — constructing the service with the real, DB-backed `ConfigManager` throws `ConfigurationKeyMissingException` until a migration exists. Spec: FR-052-11, S-052-09.

## Next Increment (TBD)

Per-part configuration of what gets cached is the intended next direction, but the design hasn't been written up yet — no tasks exist for it. Before resuming any of the superseded work below, the `managed_cache_enabled`/`managed_cache_ttl` config migration (previously T-052-05) will need to land regardless of the final design, since `ManagedCacheService` already depends on it at the code level (see T-052-03's note).

**2026-08-08 update:** T-052-05 (config migration) and T-052-06 (`SettingsController` `'Mod Cache'` exemption) are now tracked and completed as `T-053-01` under [Feature 053 – Album Listing Caching](../053-album-listing-caching/tasks.md), which resumes the album-listing half of this feature's superseded design (with a materially larger event/invalidation surface, found via a full repository audit). Photo-listing caching (the other superseded pilot consumer, `PhotoRepository::getPhotosForAlbumPaginated()`) remains unclaimed — see Feature 053's Non-Goals/Follow-ups.

## Superseded Tasks (not implemented)

The tasks below were written for the original design — event-driven invalidation wired into `AlbumRepository`/`PhotoRepository` — which was reworked and not carried out. None of the referenced files (events, listeners, migration, controller patches, repository wrapping) exist in the repo. Kept unchecked and verbatim for historical reference; do not resume from this list without first reconciling it against whatever the per-part cache-configuration design turns out to be.

- [ ] T-052-04 – New domain events `AccessPermissionChanged`, `UserGroupMembershipChanged`.
  _Intent:_ `AccessPermissionChanged(public string $base_album_id)`, `UserGroupMembershipChanged(public int $user_id)`, mirroring `AlbumSaved`/`PhotoDeleted`'s `Dispatchable`/`SerializesModels` shape.
  _Notes:_ `app/Events/AccessPermissionChanged.php`, `app/Events/UserGroupMembershipChanged.php`. Original spec refs: EV-052-01, EV-052-02 (see spec.md's Superseded Design appendix).

- [ ] T-052-05 – Migration: `managed_cache_enabled`/`managed_cache_ttl` config rows under `'Mod Cache'`.
  _Intent:_ `managed_cache_enabled` (`BOOL`, default `'1'`), `managed_cache_ttl` (`POSITIVE`, default `'3600'`), both `cat => 'Mod Cache'`, mirroring `2024_12_28_190150_caching_config.php`.
  _Notes:_ Not superseded in substance — this migration is still needed regardless of design direction (see "Next Increment" above) — but it wasn't written, so it stays unchecked here.

- [ ] T-052-06 – Patch `SettingsController::getAll()`'s `'Mod Cache'` visibility filter to exempt the two new keys.
  _Intent:_ Change `app/Http/Controllers/Admin/SettingsController.php:74`'s `->when(config('features.enable-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))` to also `orWhereIn('key', ['managed_cache_enabled', 'managed_cache_ttl'])`, so those two stay visible with the flag off while every other `'Mod Cache'` row keeps its existing gating.

- [ ] T-052-07 – Write failing feature test: `Move::do()` dispatches `AlbumSaved` per moved album.

- [ ] T-052-08 – Implement `AlbumSaved::dispatch($album)` in `Actions\Album\Move::do()`.
  _Intent:_ Inside both the `appendNode()` and `saveAsRoot()` `foreach` branches, `app/Actions/Album/Move.php:16-47`.

- [ ] T-052-09 – Write failing feature tests: `SharingController` dispatches `AccessPermissionChanged`.
  _Intent:_ `create()` once per `albumIds()` entry; `edit()`/`delete()` once for `base_album_id`; `propagate()` once per affected album (source + descendants).

- [ ] T-052-10 – Implement the four `AccessPermissionChanged::dispatch()` sites in `SharingController`.
  _Intent:_ `app/Http/Controllers/Gallery/SharingController.php:45-198`. `propagate()` recomputes affected ids via `$album->descendants()->pluck('id')->push($album->id)`.

- [ ] T-052-11 – Write failing feature tests: `UserGroupsManagementController` dispatches `UserGroupMembershipChanged`.

- [ ] T-052-12 – Implement the three `UserGroupMembershipChanged::dispatch()` sites.
  _Intent:_ `app/Http/Controllers/Admin/UserGroupsManagementController.php:26-46`, carrying `$request->user2()->id`.

- [ ] T-052-13 – Write failing unit tests for `ManagedCacheAlbumInvalidator`'s 7 event mappings.
  _Intent:_ `AlbumSaved`, `AccessPermissionChanged`, `PhotoSaved`, `PhotoAdded`, `PhotoDeleted`, `PhotoMoved` → `forgetTag("album:{id}")` + parent tag; `PhotoSaved`/`PhotoAdded` resolved via `photo_album` pivot; `AlbumDeleted` evicts only the parent's tag.

- [ ] T-052-14 – Implement `App\Listeners\ManagedCacheAlbumInvalidator`.
  _Notes:_ `app/Listeners/ManagedCacheAlbumInvalidator.php`, constructor-injects `ManagedCacheService`.

- [ ] T-052-15 – Write failing unit test + implement `App\Listeners\ManagedCacheUserInvalidator`.
  _Notes:_ `app/Listeners/ManagedCacheUserInvalidator.php`.

- [ ] T-052-16 – Register all 8 listener bindings in `EventServiceProvider`.
  _Intent:_ 7 `Event::listen()` calls for `ManagedCacheAlbumInvalidator`'s events + 1 for `ManagedCacheUserInvalidator`, near `app/Providers/EventServiceProvider.php:104-105`.

- [ ] T-052-17 – Write failing feature test: `getChildrenPaginated()` cache-hit performs zero extra queries.
  _Notes:_ Query-count pattern from `tests/Feature_v2/Album/MultiGroupPermissionMergeTest.php:107-129`.

- [ ] T-052-18 – Wrap `AlbumRepository::getChildrenPaginated()` in `remember()`.
  _Intent:_ `app/Repositories/AlbumRepository.php:43-66`. Key template `request()->query('page', 1)`, `Auth::id() ?? 'guest'`; tags `"album:{album_id ?? 'root'}"` + `"album:{child.id}"` per returned child; TTL from `managed_cache_ttl`.

- [ ] T-052-19 – Write failing feature tests: `getPhotosForAlbumPaginated()` cache-hit query count + paginator round-trip.

- [ ] T-052-20 – Wrap `PhotoRepository::getPhotosForAlbumPaginated()` in `remember()`.
  _Intent:_ `app/Repositories/PhotoRepository.php:53-93`. Tag `"album:{album_id}"`.

- [ ] T-052-21 – Cover remaining scenarios not incidentally covered above (old design's S-052-06, S-052-07, S-052-08b, S-052-10, S-052-11, S-052-12).
  _Intent:_ Move invalidates both parents; ancestor cascade; negative-cache parent eviction on newly-visible child; TTL expiry; guest cache-key segment; photo add/move/delete invalidates photo list.

- [ ] T-052-22 – Update `knowledge-map.md` and `roadmap.md`; run full quality gate; run Implementation Drift Gate.
  _Intent:_ Document the new service/events/listeners; move Feature 052 to Completed once green; record drift-gate findings in `plan.md`.

### Historical implementation notes (from the reworked-away attempt)

These notes describe experience from an earlier implementation pass covering the superseded tasks above; that code no longer exists in the repo, but the pitfalls are worth keeping in mind if this direction is ever resumed.

- Q-052-06 was resolved **Option A**: `AlbumDeleted` handling (T-052-13/14) would evict only the parent's tag, no event-payload change.
- Q-052-07 was resolved **Option B** (the non-default choice — user overrode the recommended new-category option): `managed_cache_enabled`/`managed_cache_ttl` would share the existing `'Mod Cache'` category (T-052-05); `SettingsController::getAll()`'s visibility filter would gain a two-key exemption (T-052-06).
- Config values (`managed_cache_enabled`, `managed_cache_ttl`) are DB-backed `configs` table rows read via constructor-injected `App\Repositories\ConfigManager::getValueAsBool()`/`getValueAsInt()` (precedent: `app/Services/MoneyService.php:24-27`) — **not** the Laravel `config()` helper.
- `Actions\Album\Move::do()` (T-052-08) would also need to dispatch `AlbumSaved` for the album's *previous* parent when it changed (not just the moved album itself), mirroring `Photo\MoveOrDuplicate`'s existing from/to dispatch pattern — otherwise the "both parents invalidated" scenario is unsatisfiable, since nothing else carries the old parent's id after the move completes.
- A cache-hit query-count test ("zero additional queries") must filter the query log to queries against the `albums`/`photos` tables specifically, not assert a literal zero count: every `Cache::get()`/`Cache::put()`/`forgetTag()` call fires `Illuminate\Cache\Events\*`, handled by the pre-existing `App\Listeners\CacheListener`, which itself does one `configs` table read per event (to check `cache_event_logging`) via a non-injected, non-cached `ConfigManager` instance. This is pre-existing framework wiring unrelated to this feature.
- `ManagedCacheService::remember()`'s tags-up-front signature can't express "tag with the id of every item in the computed result" (needed for per-child tagging on a children listing). `ManagedCacheService::addTags(string $key, array $tags): void` (no-op if the key isn't currently cached) was added for this and **is** implemented today (T-052-02) even though its original consumer (`AlbumRepository::getChildrenPaginated()`) is not.
- Two pre-existing-infrastructure pitfalls hit while writing feature tests for the superseded work, worth flagging for whoever resumes it: (1) `RequiresEmptyUsers`/`RequiresEmptyAlbums`-style tests (manual pre/post-condition assertions instead of `DatabaseTransactions`) are fragile against leftover rows in the persistent, gitignored `database/database.sqlite` when earlier filtered test runs didn't roll back — prefer `DatabaseTransactions`. (2) `$this->actingAs($user)` leaves the auth guard authenticated for all subsequent calls in a test method — a test simulating "then a guest makes a request" after an authenticated call must explicitly call `$this->app['auth']->forgetGuards()` first.
- A third pitfall, found only by running the **full** `php artisan test` suite (not `--filter`): creating a photo via `Photo::factory()->in($album)->create()` uses `PhotoFactory::definition()`'s hardcoded `'owner_id' => 1`, which is only a valid FK early in a fresh DB — deep into a full-suite run, SQLite's auto-increment counter has advanced well past 1 and the insert fails with a FK constraint violation, wrapped several layers deep (`ModelDBException` → `TimeBasedIdException` → the app's own exception layer) so the root cause doesn't show in default test-runner output. Set `owner_id` explicitly to the album's real owner instead of relying on the factory default. **Lesson: run the full suite, not just `--filter`, before declaring a feature done.**
- Unrelated pre-existing full-suite fragility, not caused by this feature and not fixed: several Artisan commands (`app/Console/Commands/ImageProcessing/{EncodePlaceholders,Takedate,GenerateThumbs,MoveToS3,ExtractColourPalette,ExifLens,VideoData}.php`) call `set_time_limit($timeout)` with a default of 600s. `set_time_limit()` resets the timer for the *entire PHP process*, not just that command — since `php artisan test` runs the whole suite in one continuous process, once any test invokes one of these commands, a 600-second countdown starts silently and can fatal the entire remaining test run depending on machine load. Timing flake, not a regression; worth a future fix (e.g. `register_shutdown_function` to restore the previous limit) but out of scope here.
