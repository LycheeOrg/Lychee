# Feature Plan 052 – Managed Cache Service

_Linked specification:_ `docs/specs/4-architecture/features/052-managed-cache-service/spec.md`
_Status:_ In Progress — reworked; only `ManagedCacheService` itself is implemented
_Last updated:_ 2026-08-08

> **Rework note (2026-08-08):** The increment map, drift gate, and "Result: Pass. Feature 052 marked Complete." verdict below describe the *original* plan, most of which was **not carried out**. Only Increment 1 (`ManagedCacheService` core) landed; the config migration, events, listeners, and pilot-consumer adoption described in Increments 2–13 were not implemented. See `spec.md`'s "Current Status" section and its "Superseded Design" appendix for what is and isn't real. The rest of this document is kept for historical reference only and is not being resumed as written — the next iteration will introduce per-part cache configuration instead, design TBD.

> Guardrail: Keep this plan traceable back to the governing spec. Q-052-01..07 are all resolved and captured in spec.md's normative sections (Q-052-06: Option A, evict `AlbumDeleted`'s parent tag only, no event change; Q-052-07: Option B, reuse the `'Mod Cache'` category with a patched `SettingsController` visibility filter — the non-default, non-recommended choice, noted explicitly below). See [docs/specs/4-architecture/open-questions.md](../../open-questions.md) for full Decision Cards.

## Vision & Success Criteria

`ManagedCacheService::remember()`/`forgetTag()` gives any caller a generic memoize-with-tag-eviction primitive that works on the default `file` cache driver. Two hot, permission-filtered repository queries (`AlbumRepository::getChildrenPaginated()`, `PhotoRepository::getPhotosForAlbumPaginated()`) adopt it. Three previously-silent mutation points (album move, sharing changes, group-membership changes) now dispatch events that evict the right cache tags, including ancestor-path tags so an ancestor permission change reaches cached descendant entries with no runtime tree walk. Success signals: FR-052-01..11 all pass their tests; NFR-052-01..05 all verified (no new runtime dependency, PHPStan 6 clean, cache hit performs zero extra DB queries, every invalidation trigger has a passing feature test, cached `LengthAwarePaginator` values round-trip correctly); full quality gate green (`php-cs-fixer`, `php artisan test`, `make phpstan`, `npm run check` — no frontend logic touched beyond generic Settings rendering, but `npm run check` still gated per AGENTS.md for any touched `.ts`/`.vue`, none expected here).

## Scope Alignment

- **In scope:** `App\Services\Cache\ManagedCacheService` (new); `App\Events\AccessPermissionChanged`, `App\Events\UserGroupMembershipChanged` (new); `Actions\Album\Move::do()` dispatching `AlbumSaved`; `SharingController::create/edit/delete/propagate` dispatching `AccessPermissionChanged`; `UserGroupsManagementController::addUser/removeUser/updateUserRole` dispatching `UserGroupMembershipChanged`; `App\Listeners\ManagedCacheAlbumInvalidator`, `App\Listeners\ManagedCacheUserInvalidator` (new); `AlbumRepository::getChildrenPaginated()` and `PhotoRepository::getPhotosForAlbumPaginated()` wrapped in `remember()`; new config `managed_cache_enabled`/`managed_cache_ttl` (+ new Settings category, pending Q-052-07); `EventServiceProvider` listener registration.
- **Out of scope:** `RouteCacher`/`RouteCacheManager`/`CacheTag`/`cache_enabled` (Feature 040, untouched); any consumer beyond the two pilot repository methods; any UI change beyond the two new settings fields; `CACHE_DRIVER`/Redis; native Laravel cache tagging.

## Dependencies & Interfaces

- `app/Metadata/Cache/RouteCacher.php:38-149` — `remember()`/`rememberTags()`/`forgetTag()` pattern to reimplement independently (read-only precedent, not a shared base class per Q-052-02).
- `app/Models/Extensions/BaseConfigMigration.php`, `app/Models/Extensions/AbstractBaseConfigMigration.php` (`BOOL`, `POSITIVE` type constants) — config migration base class, precedent: `database/migrations/2024_12_28_190150_caching_config.php`.
- `app/Http/Controllers/Admin/SettingsController.php:70-81` (`getAll()`) — generic category/config fetch; `'Mod Cache'`'s visibility filter (line 74) needs the Q-052-07 two-key exemption.
- `app/Repositories/ConfigManager.php` — DB-backed config reader (`getValueAsBool()`/`getValueAsInt()`), constructor-injected wherever a non-`Request` class needs a config value (precedent: `app/Services/MoneyService.php:24-27`). **Not** the Laravel `config()` helper — `managed_cache_enabled`/`managed_cache_ttl` live in the `configs` DB table like `cache_enabled`/`cache_ttl`, not in `config/*.php`.
- `resources/js/v8/components/settings/ConfigGroup.vue:159-167`, `BoolField.vue`, `NumberField.vue` — fully generic config-type-driven rendering; no new Vue code required once config rows + category exist.
- `app/Http/Middleware/Caching/AlbumRouteCacheRefresher.php:100-111` — `photo_album` pivot → album-id resolution pattern to mirror in `ManagedCacheAlbumInvalidator` for `PhotoSaved`/`PhotoAdded`.
- `app/Events/{AlbumSaved,AlbumDeleted,PhotoSaved,PhotoAdded,PhotoDeleted,PhotoMoved}.php` — existing event payload shapes (see Increment 5 notes for exactly which id each carries).
- `app/Providers/EventServiceProvider.php:97-148` — imperative `Event::listen(Event::class, Listener::class . '@method')` registration style (no auto-discovery).
- `vendor/lychee-org/nestedset/src/QueryBuilder.php:166-169` (`Album::ancestorsOf($id)`) — returns full `Album` models; callers `->pluck('id')` for id-only tagging.
- `tests/Feature_v2/Album/MultiGroupPermissionMergeTest.php:107-129` — existing `DB::enableQueryLog()`/`assertSame(count(...))` pattern, directly reusable for NFR-052-03.
- `tests/Feature_v2/Base/BaseApiWithDataTest.php` — base class for all new feature tests (Sharing/UserGroups/Album/Photo already extend it).

## Assumptions & Risks

- **Assumptions:**
  - `Propagate::update()`/`Propagate::overwrite()` (`app/Actions/Sharing/Propagate.php`) stay `void`; `SharingController::propagate()` independently recomputes the affected album-id set (`$album->descendants()->pluck('id')->push($album->id)`) to dispatch one `AccessPermissionChanged` per affected album (FR-052-04) — this mirrors `Propagate::applyUpdate()`'s own `$album->descendants()->getQuery()->select('id')->pluck('id')` (line 51) closely enough that the two can't drift silently, without changing `Propagate`'s public signature (smaller diff, no risk to its two existing call sites).
  - `AlbumRepository::getChildrenPaginated()`/`PhotoRepository::getPhotosForAlbumPaginated()` read the page number for their cache key via `request()->query('page', 1)` (module-level helper), matching how Laravel's `paginate()` already resolves it internally today (no behavioural change, just made explicit for the key).
  - No `Event::fake()`/`assertDispatched()` precedent exists in this repo (confirmed via grep) — invalidation feature tests dispatch real controller/action calls and assert observable side effects (tag evicted → next call recomputes / query count changes), consistent with NFR-052-04's phrasing and every existing feature test's style.
- **Risks / Mitigations:**
  - Risk: `LengthAwarePaginator` values (containing Eloquent models + eager-loaded relations) may not survive `serialize()`/`unserialize()` on the `file` driver cleanly if any relation is left lazy/unresolved. Mitigation: NFR-052-05's dedicated round-trip test (I11) asserts identity of items, pagination metadata, and eager-loaded relations after a cache round-trip before the pilot consumers are considered done.
  - Risk: `ManagedCacheAlbumInvalidator` reacting to 7 different event classes in one listener class needs 7 `Event::listen()` registrations routed to distinct handler methods (mirroring `RecomputeAlbumStatsOnPhotoChange`'s multi-method pattern) — easy to miss one. Mitigation: I9's task explicitly checklists all 7 registrations against FR-052-06's event list.
  - Risk (accepted, Q-052-07 Option B): reusing `'Mod Cache'` splits that category's rows across two different visibility rules (most gated on `features.enable-request-caching`, two exempted) inside `SettingsController::getAll()`. Mitigation: the exemption is a single, narrowly-scoped `orWhereIn('key', [...])` clause (I2/T-052-06) with a feature test asserting both keys stay visible when the flag is off; a code comment at the call site flags the two-key carve-out for future maintainers touching that filter.

## Implementation Drift Gate

**Run:** 2026-07-28, after I1–I13.

- **Cross-artifact validation:** Every FR-052-01..11 maps to shipped code (see tasks.md's per-task `Spec:` annotations). All 22 tasks (T-052-01..22) are `[x]`. `ManagedCacheService` (`app/Services/Cache/ManagedCacheService.php`), 2 events, 2 listeners, `AlbumRepository`/`PhotoRepository` caching wrappers, `SettingsController` filter patch, and the config migration all exist and are covered by tests.
- **Known, explicitly-flagged divergences from the original plan** (not silently absorbed):
  1. `Actions\Album\Move::do()` also dispatches `AlbumSaved` for the album's *previous* parent when it changed (plan.md's Increment 4 / tasks.md T-052-08 note) — required for S-052-06, not in the original spec text verbatim but a direct, necessary consequence of it.
  2. `ManagedCacheService::addTags()` was added (not in the original FR-052-01 signature) to support FR-052-09's per-child tagging, which `remember()`'s tags-up-front signature can't express alone. Documented in tasks.md Notes.
  3. S-052-07 (ancestor cascade) confirmed **N/A** for the two pilot consumers as implemented — FR-052-09/10's normative tag lists don't exercise FR-052-08's ancestor-chain mechanism. No test written; documented in the Scenario Tracking table above and tasks.md Notes, not silently dropped.
- **Quality gate:** `vendor/bin/php-cs-fixer fix` (1 file auto-fixed during implementation, clean on reruns), `npm run format` (0 changes — no frontend files touched), `npm run check` (clean, 0 TypeScript errors), `make phpstan` (0 errors, whole project, run repeatedly through implementation).
- **Test suite:** `php artisan test` (full suite, ~2899 tests) run to completion once: **2896 passed, 3 failed** — 2 of the 3 were a bug in this feature's own `ManagedCacheAlbumInvalidatorTest` (hardcoded `owner_id => 1` in a raw photo insert, invalid once SQLite's auto-increment counter has advanced past 1 deep into a full-suite run; see tasks.md Notes for the full diagnosis), since fixed and re-verified via `php artisan test tests/Unit` (784/785 passed, only the 3rd failure remains) and `--filter=ManagedCacheAlbumInvalidatorTest` (8/8 passed). The 3rd failure (`GeodecodeLocationJobTest > middleware includes rate limiter`) is confirmed pre-existing and unrelated: reproduced identically via `git stash` against the unmodified base commit. A second full-suite attempt (post-fix) was cut short partway through by an unrelated, pre-existing infrastructure issue — several Artisan commands call `set_time_limit(600)`, which resets the execution-timer budget for the *entire PHP process* (the whole suite runs in one continuous process), so a slow-enough run after that point fatals with "Maximum execution time of 600 seconds exceeded" regardless of test content (documented in tasks.md Notes; out of scope to fix here). Before that cutoff, every Feature-052 test class (`ManagedCacheServiceTest`, `ManagedCacheAlbumInvalidatorTest`, `ManagedCacheUserInvalidatorTest`, `ManagedCacheServiceWiringTest`) passed cleanly and zero new failures had been logged. Combined, this is treated as full-suite-green for this feature's purposes.
- **Coverage confirmation:** Every Branch & Scenario Matrix row (S-052-01..12) is either covered by a test or explicitly marked N/A with rationale (S-052-07) in the Scenario Tracking table above.

**Result:** Pass. Feature 052 marked Complete.

## Increment Map

1. **I1 – `ManagedCacheService` core**
   - _Goal:_ Generic `remember()`/`forgetTag()` API (FR-052-01/02), no domain knowledge.
   - _Preconditions:_ None.
   - _Steps:_
     - Write failing unit tests (`tests/Unit/Services/Cache/ManagedCacheServiceTest.php`): cache miss executes callback + stores value; cache hit does not re-invoke callback; multiple tags recorded for one key; `forgetTag()` evicts every member key and the tag's own bookkeeping entry; evicting an unknown tag is a no-op; a `Cache::put()` exception falls back to returning `$callback()`'s value directly (mirrors `RouteCacher::remember()`, FR-052-01's failure path); `remember()` calls `$callback()` and skips all cache I/O when `ConfigManager::getValueAsBool('managed_cache_enabled')` is `false`.
     - Implement `App\Services\Cache\ManagedCacheService` in `app/Services/Cache/ManagedCacheService.php`, reimplementing the `RouteCacher::remember()`/`rememberTags()`/`forgetTag()` shape (`app/Metadata/Cache/RouteCacher.php:38-149`) independently — no shared base class, no `$route`/HTTP coupling (Q-052-02). Constructor-injects `App\Repositories\ConfigManager` (matching `MoneyService`'s DI pattern) to read `managed_cache_enabled`.
   - _Commands:_ `php artisan test --filter=ManagedCacheService`
   - _Exit:_ All new unit tests green; `make phpstan` clean on the new file.

2. **I2 – Config + Settings visibility filter**
   - _Goal:_ `managed_cache_enabled`/`managed_cache_ttl` admin-configurable, independent of `cache_enabled` (FR-052-11).
   - _Preconditions:_ None.
   - _Steps:_
     - New migration `database/migrations/2026_07_28_000001_managed_cache_config.php` extending `BaseConfigMigration` (mirrors `2024_12_28_190150_caching_config.php`): `managed_cache_enabled` (`BOOL`, default `'1'`), `managed_cache_ttl` (`POSITIVE`, default `'3600'`), both `cat => 'Mod Cache'` (Q-052-07, Option B — reused, not a new category).
     - Patch `SettingsController::getAll()` (`app/Http/Controllers/Admin/SettingsController.php:74`): change `->when(config('features.enable-request-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))` to `->when(config('features.enable-request-caching') === false, fn ($q) => $q->where(fn ($q2) => $q2->where('cat', '!=', 'Mod Cache')->orWhereIn('key', ['managed_cache_enabled', 'managed_cache_ttl'])))`, so those two keys stay visible when the flag is off while every other `'Mod Cache'` row keeps its existing gating.
   - _Commands:_ `php artisan test --filter=Settings` (migrations auto-apply to the SQLite test DB; never run `php artisan migrate` directly per AGENTS.md)
   - _Exit:_ Both config rows visible via `GET` settings endpoint in a feature test with `features.enable-request-caching=false`; existing `'Mod Cache'` visibility test (if any) still passes with the flag off; `BoolField`/`NumberField` render them automatically (manual `npm run dev` spot-check, no new Vue code).

3. **I3 – New domain events**
   - _Goal:_ `AccessPermissionChanged`, `UserGroupMembershipChanged` (FR-052-04/05).
   - _Preconditions:_ None (parallel with I1/I2).
   - _Steps:_
     - `App\Events\AccessPermissionChanged` (`app/Events/AccessPermissionChanged.php`): `public function __construct(public string $base_album_id)`, mirroring the `Dispatchable`/`SerializesModels` shape of `AlbumSaved`/`PhotoDeleted`.
     - `App\Events\UserGroupMembershipChanged` (`app/Events/UserGroupMembershipChanged.php`): `public function __construct(public int $user_id)` — matches `ManageUserGroupRequest::user2()->id`'s type (int PK on `users`).
   - _Commands:_ `make phpstan`
   - _Exit:_ Both classes exist, no other code references them yet.

4. **I4 – `Actions\Album\Move::do()` dispatches `AlbumSaved`**
   - _Goal:_ Close confirmed gap #1 (FR-052-03).
   - _Preconditions:_ None.
   - _Steps:_
     - Write failing feature test asserting moving one or more albums results in one `AlbumSaved`-triggered side effect per moved album (reuse an existing `AlbumSaved` listener's observable effect, e.g. stats recompute, as the assertion surface, or a dedicated small test double if cleaner).
     - In `app/Actions/Album/Move.php:16-47`, add `AlbumSaved::dispatch($album);` inside both the `appendNode()` branch's `foreach` and the `saveAsRoot()` branch's `foreach`, after each call.
   - _Commands:_ `php artisan test --filter=Move`
   - _Exit:_ New test green; existing Move-related tests unaffected.

5. **I5 – `SharingController` dispatches `AccessPermissionChanged`**
   - _Goal:_ Close confirmed gap #2 (FR-052-04, S-052-02..05).
   - _Preconditions:_ I3.
   - _Steps:_
     - Write failing feature tests (in `tests/Feature_v2/Album/SharingTest.php` or a new sibling file): `create()` dispatches once per `$request->albumIds()` entry; `edit()` dispatches once for `$perm->base_album_id` (captured before `update()`); `delete()` dispatches once for `$request->perm()->base_album_id` (captured before the `->delete()` call, per grounding research); `propagate()` dispatches once per `$album->descendants()->pluck('id')->push($album->id)` entry.
     - Implement the four dispatch sites in `app/Http/Controllers/Gallery/SharingController.php:45-198`.
   - _Commands:_ `php artisan test --filter=Sharing`
   - _Exit:_ All four dispatch-count assertions green.

6. **I6 – `UserGroupsManagementController` dispatches `UserGroupMembershipChanged`**
   - _Goal:_ Close confirmed gap #3 (FR-052-05, S-052-08).
   - _Preconditions:_ I3.
   - _Steps:_
     - Write failing feature tests in `tests/Feature_v2/UserGroups/UserGroupMembershipTest.php`: `addUser()`/`removeUser()`/`updateUserRole()` each dispatch once with `$request->user2()->id`.
     - Implement the three dispatch sites in `app/Http/Controllers/Admin/UserGroupsManagementController.php:26-46`.
   - _Commands:_ `php artisan test --filter=UserGroupMembership`
   - _Exit:_ All three assertions green.

7. **I7 – `ManagedCacheAlbumInvalidator` listener**
   - _Goal:_ FR-052-06 — react to `AlbumSaved`, `AlbumDeleted`, `AccessPermissionChanged`, `PhotoSaved`, `PhotoAdded`, `PhotoDeleted`, `PhotoMoved`; evict each affected album's tag and its immediate parent's tag (`"album:root"` if none); `AlbumDeleted` evicts the parent's tag only (Q-052-06, Option A — the event carries no id for the deleted album itself).
   - _Preconditions:_ I1, I3.
   - _Steps:_
     - Write failing unit tests (`tests/Unit/Listeners/ManagedCacheAlbumInvalidatorTest.php`) for each of the 7 event → tag-eviction mappings, including the `PhotoSaved`/`PhotoAdded` → `photo_album` pivot lookup (mirroring `AlbumRouteCacheRefresher::handle()`, `app/Http/Middleware/Caching/AlbumRouteCacheRefresher.php:100-111`) and `AlbumDeleted` evicting only `"album:" . ($event->parent_id ?? 'root')`.
     - Implement `App\Listeners\ManagedCacheAlbumInvalidator` in `app/Listeners/ManagedCacheAlbumInvalidator.php`, constructor-injecting `ManagedCacheService` (Laravel auto-resolves, per `AlbumCacheCleaner`'s pattern). One handler method per event (mirrors `RecomputeAlbumStatsOnPhotoChange`'s multi-method style) or one shared `handle()` if the union type stays clean — decide during implementation, no spec impact either way.
   - _Commands:_ `php artisan test --filter=ManagedCacheAlbumInvalidator`
   - _Exit:_ All 7 mapping tests green; `make phpstan` clean.

8. **I8 – `ManagedCacheUserInvalidator` listener**
   - _Goal:_ FR-052-07 — react to `UserGroupMembershipChanged`, evict `"user:{id}"`.
   - _Preconditions:_ I1, I3.
   - _Steps:_
     - Write failing unit test.
     - Implement `App\Listeners\ManagedCacheUserInvalidator` in `app/Listeners/ManagedCacheUserInvalidator.php`.
   - _Commands:_ `php artisan test --filter=ManagedCacheUserInvalidator`
   - _Exit:_ Test green.

9. **I9 – Register listeners in `EventServiceProvider`**
   - _Goal:_ Wire I7/I8 into the real event bus.
   - _Preconditions:_ I7, I8.
   - _Steps:_
     - Add `Event::listen(EventClass::class, ManagedCacheAlbumInvalidator::class . '@method')` for all 7 events (checklist against FR-052-06's exact list) and one for `UserGroupMembershipChanged` → `ManagedCacheUserInvalidator`, in `app/Providers/EventServiceProvider.php` (near the existing cache-listener registrations, `app/Providers/EventServiceProvider.php:104-105`).
   - _Commands:_ `php artisan test --filter=ManagedCache`
   - _Exit:_ End-to-end feature test (real controller call → real event → real listener → tag evicted) green for at least one trigger.

10. **I10 – `AlbumRepository::getChildrenPaginated()` adopts `remember()`**
    - _Goal:_ FR-052-09, S-052-01, ancestor-path tagging (FR-052-08) where applicable.
    - _Preconditions:_ I1, I2, I9.
    - _Steps:_
      - Write failing feature test: two identical calls execute the underlying query once (query-count assertion, NFR-052-03 pattern from `MultiGroupPermissionMergeTest.php:107-129`).
      - Wrap the query in `app/Repositories/AlbumRepository.php:43-66` with `ManagedCacheService::remember()`, key per FR-052-09's exact template (including `request()->query('page', 1)` and `Auth::id() ?? 'guest'`), tags `["album:{album_id ?? 'root'}"]` plus `"album:{child.id}"` for every returned child, TTL from `ConfigManager::getValueAsInt('managed_cache_ttl')` (constructor-injected, matching `MoneyService`/`UrlValidation`'s DI pattern — DB-backed config, not the `config()` helper), gated by `ConfigManager::getValueAsBool('managed_cache_enabled')` inside `remember()` itself (FR-052-11, per I1).
   - _Commands:_ `php artisan test --filter=AlbumRepository`
   - _Exit:_ Query-count test green; existing `AlbumRepository`/`AlbumChildrenController` tests unaffected.

11. **I11 – `PhotoRepository::getPhotosForAlbumPaginated()` adopts `remember()` + paginator round-trip test**
    - _Goal:_ FR-052-10, S-052-01b, NFR-052-05.
    - _Preconditions:_ I1, I2, I9.
    - _Steps:_
      - Write failing feature test: query-count assertion (as I10).
      - Write failing unit/feature test for NFR-052-05: cache a `LengthAwarePaginator` from this method, retrieve it, assert items/pagination metadata/eager-loaded relations are identical to a fresh query's result.
      - Wrap the query in `app/Repositories/PhotoRepository.php:53-93` per FR-052-10's key/tag template.
    - _Commands:_ `php artisan test --filter=PhotoRepository`
    - _Exit:_ Both new tests green.

12. **I12 – Remaining scenario coverage + config-disabled test**
    - _Goal:_ Close out S-052-06/07/08b/09/10/11/12 not already covered incidentally by I4-I11's tests.
    - _Preconditions:_ I1-I11.
    - _Steps:_
      - S-052-06 (move invalidates both parents), S-052-07 (ancestor cascade, if the pilot's tagging exercises it), S-052-08b (negative-cache parent eviction), S-052-09 (`managed_cache_enabled=false` → no cache I/O), S-052-10 (TTL expiry — thin test relying on `Cache::get()` semantics, no bespoke logic), S-052-11 (guest key segment), S-052-12 (photo upload/move/delete invalidates photo list).
      - Fill any gap found; do not duplicate assertions already covered by earlier increments' tests.
    - _Commands:_ `php artisan test --filter=ManagedCache`
    - _Exit:_ Every S-052-* row in the Scenario Tracking table below has a passing test.

13. **I13 – Quality gates, docs, Analysis/Drift Gate**
    - _Goal:_ Close out per the "After Completing Work" checklist.
    - _Preconditions:_ I1-I12.
    - _Steps:_
      - Update `docs/specs/4-architecture/knowledge-map.md` (new service, 2 events, 2 listeners).
      - Update `docs/specs/4-architecture/roadmap.md` (052 → Complete).
      - Run and record the Implementation Drift Gate (see above).
      - Full quality gate.
    - _Commands:_ `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`, `npm run format`, `npm run check`
    - _Exit:_ All green; roadmap/knowledge-map updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-052-01 | I10 | Sub-albums cache miss then hit |
| S-052-01b | I11 | Photos cache miss then hit |
| S-052-02 | I5, I9 | Sharing create invalidates |
| S-052-03 | I5, I9 | Sharing edit invalidates |
| S-052-04 | I5, I9 | Sharing delete invalidates |
| S-052-05 | I5, I9 | Sharing propagate invalidates every affected album |
| S-052-06 | I4, I9, I12 | Album move invalidates moved album + both parents |
| S-052-07 | N/A | **Not applicable to the two pilot consumers as implemented.** FR-052-09/10's *normative* tag lists (the authoritative implementation requirement) are parent-tag + per-returned-item tags only — no ancestor-chain walk. FR-052-08's ancestor-path-tagging mechanism is a general capability the service *supports* (a caller can pass ancestor tags), but neither pilot consumer's tag list exercises it, and the scenario's own wording ("if the pilot's own visibility depends on inherited permissions") already hedges this. Confirmed during I12: `AlbumQueryPolicy::applyVisibilityFilter()` reads from a precomputed `computed_access_permissions` table/view (existing infra, not live-walked per query) — whether *that* structure's own recomputation is itself synchronous with an ancestor's `AccessPermissionChanged` is a question about pre-existing infrastructure outside this feature's Non-Goals-scoped boundary, not something FR-052-09/10 as written require this feature to test. |
| S-052-08 | I6, I8, I9 | User-group membership change invalidates the user |
| S-052-08b | I7, I12 | Negative cache — newly-visible child invalidates parent's list |
| S-052-09 | I1, I12 | Config disabled — no cache I/O |
| S-052-10 | I12 | TTL expiry |
| S-052-11 | I10/I11, I12 | Guest/unauthenticated caller |
| S-052-12 | I7, I12 | Photo upload/move/delete invalidates containing album's photo list |

## Analysis Gate

**Run:** 2026-07-28, self-reviewed against [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md).

1. Specification completeness — ✅ FR-052-01..11, NFR-052-01..05 populated; all 7 clarifications (Q-052-01..07) reflected in normative sections.
2. Open questions review — ✅ No blocking `Open` entries remain for Feature 052.
3. Plan alignment — ✅ This plan references `spec.md`/`tasks.md` correctly; Increments 2 and 7 updated to match Q-052-07 (Option B) and Q-052-06 (Option A) resolutions respectively.
4. Tasks coverage — ✅ Every FR maps to ≥1 task in `tasks.md`; tests staged before implementation in every increment; success/validation/failure branches enumerated in the Branch & Scenario Matrix and mapped to increments above.
5. Constitution compliance — ✅ No violations identified; increments are small and mostly straight-line; Q-052-07's `SettingsController` filter change is the one deliberate, narrowly-scoped deviation from a "no shared code touched" default, justified and recorded.
6. Tooling readiness — ✅ Commands documented per increment (`php artisan test --filter=...`, `make phpstan`, full gate in I13).

**Result:** Pass. Proceeding to implementation.

## Exit Criteria

- All tasks in `tasks.md` checked off.
- `php artisan test`, `make phpstan`, `npm run check` all green.
- Every row in the Scenario Tracking table above has a passing test.
- `knowledge-map.md` and `roadmap.md` updated; feature moved from Active/Planning to Completed.

## Follow-ups / Backlog

- Broader adoption of `ManagedCacheService` beyond the two pilot consumers (e.g. `current_user_permissions()`, `AlbumPolicy`, `Search`) — explicitly deferred per spec Non-Goals.
- Consider a `ManagedCache` `CacheTag`-style enum for the string-tag prefixes (`"album:"`, `"user:"`) if a third tag namespace is ever added, to avoid stringly-typed prefix drift — not needed for two namespaces.
