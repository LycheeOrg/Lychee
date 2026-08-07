# Feature 052 Tasks – Managed Cache Service

_Status: Implemented_
_Last updated: 2026-07-28_

> Keep this checklist aligned with `plan.md`'s increments. Tests are staged before implementation in every increment. Mark tasks `[x]` immediately after each one passes verification. Q-052-06 (Option A) and Q-052-07 (Option B) are resolved; see Notes below for how each shapes T-052-05/06 and T-052-13/14.

## Checklist

- [x] T-052-01 – Write failing unit tests for `ManagedCacheService` (F-052-01, F-052-02).
  _Intent:_ Cache miss executes callback + stores value; cache hit does not re-invoke callback; multiple tags on one key; `forgetTag()` evicts all member keys + itself; evicting an unknown tag is a no-op; `Cache::put()` exception falls back to the callback's return value.
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  _Notes:_ New file `tests/Unit/Services/Cache/ManagedCacheServiceTest.php`. Spec: FR-052-01/02.

- [x] T-052-02 – Implement `App\Services\Cache\ManagedCacheService` (F-052-01, F-052-02).
  _Intent:_ `remember(string $key, array $tags, $ttl, \Closure $callback): mixed` and `forgetTag(string $tag): void`, reimplementing `RouteCacher`'s key-list-as-tag pattern independently (no shared class, no `$route`/HTTP coupling).
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  - `make phpstan`
  _Notes:_ `app/Services/Cache/ManagedCacheService.php`. Spec: FR-052-01, FR-052-02, NFR-052-01, SVC-052-01/02.

- [x] T-052-03 – Gate `remember()` on `managed_cache_enabled` config (F-052-11, S-052-09).
  _Intent:_ `ManagedCacheService` constructor-injects `App\Repositories\ConfigManager` (DB-backed `configs` table, matching `MoneyService`'s DI pattern — **not** the `config()` helper). When `$this->config_manager->getValueAsBool('managed_cache_enabled') === false`, `remember()` always calls `$callback()` directly and performs no cache I/O.
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  _Notes:_ Land alongside T-052-02. `ConfigManager::getValue()` throws `ConfigurationKeyMissingException` if the key doesn't exist yet, so this task has a real (not just stylistic) dependency on T-052-05's migration existing in the test DB. Spec: FR-052-11, S-052-09.

- [x] T-052-04 – New domain events `AccessPermissionChanged`, `UserGroupMembershipChanged` (F-052-04, F-052-05).
  _Intent:_ `AccessPermissionChanged(public string $base_album_id)`, `UserGroupMembershipChanged(public int $user_id)`, mirroring `AlbumSaved`/`PhotoDeleted`'s `Dispatchable`/`SerializesModels` shape.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ `app/Events/AccessPermissionChanged.php`, `app/Events/UserGroupMembershipChanged.php`. Spec: EV-052-01, EV-052-02.

- [x] T-052-05 – Migration: `managed_cache_enabled`/`managed_cache_ttl` config rows under `'Mod Cache'` (F-052-11).
  _Intent:_ `managed_cache_enabled` (`BOOL`, default `'1'`), `managed_cache_ttl` (`POSITIVE`, default `'3600'`), both `cat => 'Mod Cache'` (Q-052-07, Option B — reused, no new category), mirroring `2024_12_28_190150_caching_config.php`.
  _Verification commands:_
  - `php artisan test --filter=Settings` (migrations auto-apply to the SQLite test DB per AGENTS.md; never run `php artisan migrate` directly)
  _Notes:_ `database/migrations/2026_07_28_000001_managed_cache_config.php`. Spec: FR-052-11, UI-052-01/02.

- [x] T-052-06 – Patch `SettingsController::getAll()`'s `'Mod Cache'` visibility filter to exempt the two new keys (F-052-11).
  _Intent:_ Change `app/Http/Controllers/Admin/SettingsController.php:74`'s `->when(config('features.enable-request-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))` to also `orWhereIn('key', ['managed_cache_enabled', 'managed_cache_ttl'])`, so those two stay visible with the flag off while every other `'Mod Cache'` row keeps its existing gating.
  _Verification commands:_
  - `php artisan test --filter=Settings`
  - `make phpstan`
  _Notes:_ Feature test: `features.enable-request-caching=false` → response still contains `managed_cache_enabled`/`managed_cache_ttl`, still excludes `cache_enabled`/`cache_ttl`. Spec: FR-052-11 (Q-052-07 resolution).

- [x] T-052-07 – Write failing feature test: `Move::do()` dispatches `AlbumSaved` per moved album (F-052-03).
  _Verification commands:_
  - `php artisan test --filter=Move`
  _Notes:_ Spec: FR-052-03.

- [x] T-052-08 – Implement `AlbumSaved::dispatch($album)` in `Actions\Album\Move::do()` (F-052-03).
  _Intent:_ Inside both the `appendNode()` and `saveAsRoot()` `foreach` branches, `app/Actions/Album/Move.php:16-47`.
  _Verification commands:_
  - `php artisan test --filter=Move`
  - `make phpstan`
  _Notes:_ Spec: FR-052-03, EV-052-03.

- [x] T-052-09 – Write failing feature tests: `SharingController` dispatches `AccessPermissionChanged` (F-052-04, S-052-02..05).
  _Intent:_ `create()` once per `albumIds()` entry; `edit()`/`delete()` once for `base_album_id`; `propagate()` once per affected album (source + descendants).
  _Verification commands:_
  - `php artisan test --filter=Sharing`
  _Notes:_ Spec: FR-052-04, S-052-02/03/04/05.

- [x] T-052-10 – Implement the four `AccessPermissionChanged::dispatch()` sites in `SharingController` (F-052-04).
  _Intent:_ `app/Http/Controllers/Gallery/SharingController.php:45-198`. `propagate()` recomputes affected ids via `$album->descendants()->pluck('id')->push($album->id)` — no change to `Propagate.php`.
  _Verification commands:_
  - `php artisan test --filter=Sharing`
  - `make phpstan`
  _Notes:_ Spec: FR-052-04, EV-052-01.

- [x] T-052-11 – Write failing feature tests: `UserGroupsManagementController` dispatches `UserGroupMembershipChanged` (F-052-05, S-052-08).
  _Verification commands:_
  - `php artisan test --filter=UserGroupMembership`
  _Notes:_ Spec: FR-052-05, S-052-08.

- [x] T-052-12 – Implement the three `UserGroupMembershipChanged::dispatch()` sites (F-052-05).
  _Intent:_ `app/Http/Controllers/Admin/UserGroupsManagementController.php:26-46`, carrying `$request->user2()->id`.
  _Verification commands:_
  - `php artisan test --filter=UserGroupMembership`
  - `make phpstan`
  _Notes:_ Spec: FR-052-05, EV-052-02.

- [x] T-052-13 – Write failing unit tests for `ManagedCacheAlbumInvalidator`'s 7 event mappings (F-052-06).
  _Intent:_ `AlbumSaved`, `AccessPermissionChanged`, `PhotoSaved`, `PhotoAdded`, `PhotoDeleted`, `PhotoMoved` → `forgetTag("album:{id}")` + parent tag; `PhotoSaved`/`PhotoAdded` resolved via `photo_album` pivot (mirrors `AlbumRouteCacheRefresher::handle()`); `AlbumDeleted` evicts only `forgetTag("album:" . ($event->parent_id ?? 'root'))` (Q-052-06, Option A — no own-id available on the event).
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheAlbumInvalidator`
  _Notes:_ `tests/Unit/Listeners/ManagedCacheAlbumInvalidatorTest.php`. Spec: FR-052-06.

- [x] T-052-14 – Implement `App\Listeners\ManagedCacheAlbumInvalidator` (F-052-06).
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheAlbumInvalidator`
  - `make phpstan`
  _Notes:_ `app/Listeners/ManagedCacheAlbumInvalidator.php`, constructor-injects `ManagedCacheService`. Spec: FR-052-06, LSN-052-01.

- [x] T-052-15 – Write failing unit test + implement `App\Listeners\ManagedCacheUserInvalidator` (F-052-07, S-052-08).
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheUserInvalidator`
  - `make phpstan`
  _Notes:_ `app/Listeners/ManagedCacheUserInvalidator.php`. Spec: FR-052-07, LSN-052-02.

- [x] T-052-16 – Register all 8 listener bindings in `EventServiceProvider` (F-052-06, F-052-07).
  _Intent:_ 7 `Event::listen()` calls for `ManagedCacheAlbumInvalidator`'s events + 1 for `ManagedCacheUserInvalidator`, near `app/Providers/EventServiceProvider.php:104-105`.
  _Verification commands:_
  - `php artisan test --filter=ManagedCache`
  _Notes:_ Checklist against FR-052-06's exact 7-event list to avoid missing one. Spec: FR-052-06, FR-052-07.

- [x] T-052-17 – Write failing feature test: `getChildrenPaginated()` cache-hit performs zero extra queries (F-052-09, S-052-01, NFR-052-03).
  _Verification commands:_
  - `php artisan test --filter=AlbumRepository`
  _Notes:_ Query-count pattern from `tests/Feature_v2/Album/MultiGroupPermissionMergeTest.php:107-129`. Spec: FR-052-09, NFR-052-03.

- [x] T-052-18 – Wrap `AlbumRepository::getChildrenPaginated()` in `remember()` (F-052-09).
  _Intent:_ `app/Repositories/AlbumRepository.php:43-66`. Key per FR-052-09's template (`request()->query('page', 1)`, `Auth::id() ?? 'guest'`); tags `"album:{album_id ?? 'root'}"` + `"album:{child.id}"` per returned child; TTL `$this->config_manager->getValueAsInt('managed_cache_ttl')` (constructor-injected `ConfigManager`, not `config()`).
  _Verification commands:_
  - `php artisan test --filter=AlbumRepository`
  - `make phpstan`
  _Notes:_ Spec: FR-052-09, SVC-052-03.

- [x] T-052-19 – Write failing feature tests: `getPhotosForAlbumPaginated()` cache-hit query count (NFR-052-03) + paginator round-trip (NFR-052-05).
  _Verification commands:_
  - `php artisan test --filter=PhotoRepository`
  _Notes:_ Spec: FR-052-10, NFR-052-03, NFR-052-05.

- [x] T-052-20 – Wrap `PhotoRepository::getPhotosForAlbumPaginated()` in `remember()` (F-052-10).
  _Intent:_ `app/Repositories/PhotoRepository.php:53-93`. Key per FR-052-10's template; tag `"album:{album_id}"`.
  _Verification commands:_
  - `php artisan test --filter=PhotoRepository`
  - `make phpstan`
  _Notes:_ Spec: FR-052-10, SVC-052-04.

- [x] T-052-21 – Cover remaining scenarios not incidentally covered above (S-052-06, S-052-07, S-052-08b, S-052-10, S-052-11, S-052-12).
  _Intent:_ Move invalidates both parents; ancestor cascade; negative-cache parent eviction on newly-visible child; TTL expiry; guest cache-key segment; photo add/move/delete invalidates photo list.
  _Verification commands:_
  - `php artisan test --filter=ManagedCache`
  _Notes:_ Fill gaps only — do not duplicate assertions already covered by T-052-07..20's tests. Spec: Branch & Scenario Matrix S-052-06/07/08b/10/11/12.

- [x] T-052-22 – Update `knowledge-map.md` and `roadmap.md`; run full quality gate; run Implementation Drift Gate.
  _Intent:_ Document the new service/events/listeners; move Feature 052 to Completed once green; record drift-gate findings in `plan.md`.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ Spec: Documentation Deliverables.

## Notes / TODOs

- Q-052-06 resolved **Option A**: `AlbumDeleted` handling (T-052-13/14) evicts only the parent's tag, no event-payload change.
- Q-052-07 resolved **Option B** (the non-default choice — user overrode the recommended new-category option): `managed_cache_enabled`/`managed_cache_ttl` share the existing `'Mod Cache'` category (T-052-05); `SettingsController::getAll()`'s visibility filter gains a two-key exemption (T-052-06) so they stay visible when `features.enable-request-caching` is `false`.
- Config values (`managed_cache_enabled`, `managed_cache_ttl`) are DB-backed `configs` table rows read via constructor-injected `App\Repositories\ConfigManager::getValueAsBool()`/`getValueAsInt()` (precedent: `app/Services/MoneyService.php:24-27`) — **not** the Laravel `config()` helper. `ConfigManager::getValue()` throws if the key is missing, so T-052-01/03's tests implicitly need T-052-05's migration applied in the test DB; sequence T-052-05 before or alongside T-052-01/02/03 during implementation even though they're numbered later.
- Exact handler-method-per-event vs. single shared `handle()` for `ManagedCacheAlbumInvalidator` (T-052-14) is an implementation detail with no spec impact — decide during implementation based on which keeps the union type/dispatch table cleanest.
- Whether T-052-08's `AlbumSaved::dispatch()` insertion needs a dedicated `Move`-specific feature test or can reuse an existing `AlbumSaved` listener's observable side effect as the assertion surface is an implementation detail for T-052-07.
- `Actions\Album\Move::do()` (T-052-08) also dispatches `AlbumSaved` for the album's *previous* parent when it changed (not just the moved album itself), mirroring `Photo\MoveOrDuplicate`'s existing from/to dispatch pattern — otherwise S-052-06 ("both parents" invalidated) is unsatisfiable, since nothing else carries the old parent's id after the move completes.
- NFR-052-03 ("cache hit performs zero additional queries") tests must filter the query log to queries against the `albums`/`photos` tables specifically, not assert a literal zero count: every `Cache::get()`/`Cache::put()`/`forgetTag()` call fires `Illuminate\Cache\Events\*`, handled by the pre-existing `App\Listeners\CacheListener`, which itself does one `configs` table read per event (to check `cache_event_logging`) via a non-injected, non-cached `ConfigManager` instance. This is pre-existing framework wiring unrelated to this feature — confirmed while implementing T-052-17 — and out of scope to change here.
- `ManagedCacheService::remember()`'s tags-up-front signature (FR-052-01) can't express "tag with the id of every item in the computed result" (FR-052-09's per-child tagging). Added a small `ManagedCacheService::addTags(string $key, array $tags): void` (no-op if the key isn't currently cached) to associate extra tags with an already-cached key after the callback has run — used by T-052-18 for the per-child tags. No spec/contract change to `remember()` itself.
- S-052-07 (ancestor cascade) is **N/A** for the two pilot consumers as implemented — FR-052-09/10's normative tag lists (parent + per-item tags) don't exercise FR-052-08's ancestor-chain-tagging mechanism, and the scenario's own wording hedges this ("if the pilot's own visibility depends on inherited permissions"). See plan.md's Scenario Tracking table for the full rationale. No test written for it; not a gap in FR-052-09/10 compliance.
- T-052-21's scenario coverage landed across four files: `tests/Unit/Services/Cache/ManagedCacheServiceTest.php` (S-052-09/10 — config-disabled, TTL expiry), `tests/Feature_v2/Album/AlbumMoveTest.php` (S-052-06 — old+new parent dispatch), and `tests/Feature_v2/Caching/ManagedCacheServiceWiringTest.php` (S-052-08b negative-cache, S-052-11 guest key, plus real end-to-end wiring proofs for Move/Sharing/UserGroups/Photo beyond what T-052-16 alone required).
- Two pre-existing-infrastructure pitfalls hit while writing feature tests, worth flagging for future sessions: (1) `tests/Unit/Repositories/AlbumRepositoryTest.php`/`PhotoRepositoryTest.php`-style tests using `RequiresEmptyUsers`/`RequiresEmptyAlbums` (manual pre/post-condition assertions instead of `DatabaseTransactions`) are fragile against leftover rows in the persistent, gitignored `database/database.sqlite` when earlier filtered test runs didn't roll back — `PhotoRepositoryTest` was written using `DatabaseTransactions` instead specifically to avoid this. (2) `$this->actingAs($user)` leaves the auth guard authenticated for all subsequent calls in a test method — a test simulating "then a guest makes a request" after an authenticated call must explicitly call `$this->app['auth']->forgetGuards()` first (see `ManagedCacheServiceWiringTest::testGuestCachedListingHitsCacheAndSurvivesAnUnrelatedUserGroupChange`).
- A third pitfall, found only by running the **full** `php artisan test` suite (not `--filter`): `ManagedCacheAlbumInvalidatorTest`'s `handlePhotoSaved`/`handlePhotoAdded` tests originally created their photo via `Photo::factory()->in($album)->create()`, whose `PhotoFactory::definition()` hardcodes `'owner_id' => 1`. That happens to be a valid FK in an isolated run (the test's own first-created user gets id 1 in a fresh DB), but not deep into the full suite, where thousands of prior tests have advanced SQLite's auto-increment counter well past 1 — the insert then fails with a FK constraint violation (`ModelDBException: Updating photo failed`, `TimeBasedIdException` retry-then-fail wrapping the real `SQLSTATE[23000]` cause, both further wrapped by the app's own exception layer so the root cause doesn't show in the default test-runner output — had to reproduce via `php artisan test tests/Unit` and temporarily catch+dump `getPrevious()` to find it). Fixed by a `createPhotoInAlbum()` test helper that explicitly sets `owner_id` to the album's real owner and bypasses `PhotoFactory`'s heavy `configure()` hook (7 `SizeVariant`s + a `Statistics` row, not needed to test pivot resolution) by using `Photo::create()`/`forceFill()`+`save()` directly instead of `Factory::create()`. **Lesson: run the full suite, not just `--filter`, before declaring a feature done — some failures only reproduce under full-suite DB/auto-increment state.**
- Unrelated pre-existing full-suite fragility discovered while verifying (not caused by this feature, not fixed here — flagging for whoever next runs the full suite and sees an unexplained mid-run fatal): several Artisan commands (`app/Console/Commands/ImageProcessing/{EncodePlaceholders,Takedate,GenerateThumbs,MoveToS3,ExtractColourPalette,ExifLens,VideoData}.php`) call `set_time_limit($timeout)` with a default `$timeout` of 600s. `set_time_limit()` resets the timer for the *entire PHP process*, not just that command — since `php artisan test` runs the whole suite in one continuous process, once any test invokes one of these commands (e.g. `tests/ImageProcessing/Commands/EncodePlaceholdersTest.php`), a 600-second countdown starts silently and fatals the *entire remaining test run* (`PHP Fatal error: Maximum execution time of 600 seconds exceeded`) if the rest of the suite happens to take longer than that from that point on — which depends entirely on machine load at run time, not on which tests exist. Reproduced this exact fatal on one full-suite run in this session and a clean pass on another, both against the same code, confirming it's a timing flake, not a regression. Out of scope to fix here (pre-existing, unrelated to caching); worth a future fix (e.g. `register_shutdown_function` to restore the previous limit, or moving these commands' timeout handling to a subprocess).
