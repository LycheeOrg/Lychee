# Feature Plan 053 – Album Listing Caching

_Linked specification:_ `docs/specs/4-architecture/features/053-album-listing-caching/spec.md`
_Status:_ Complete
_Last updated:_ 2026-08-09

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when spec.md's normative sections have been updated.

## Vision & Success Criteria

Sub-album listing (`AlbumRepository::getChildrenPaginated()`), each of the four constituent queries inside the root gallery view (`Actions\Albums\Top::get()`), and the Tag detail page's album query (`GetTagWithPhotosAndAlbums::getAccessibleAlbums()`) stop re-executing their query/policy/sort pipeline on every request, while remaining correct under every mutation path found by the spec's audit — including the ones that bypass Eloquent's event system entirely (raw `DB::table()`, bulk `Model::query()->update()`, pivot `attach()`/`detach()`, vendor nested-set internals). Success = every FR-053-04 through FR-053-21 dispatch site (minus the removed FR-053-17) plus FR-053-28/29/30 exists and is tested (NFR-053-04), cache-hit paths perform zero relevant DB queries per query (NFR-053-03), the full quality gate is green, and the pre-existing `SetProtectionPolicy` `TypeError` (FR-053-11) is fixed as a side effect of correctly wiring tag/person album events. Caching stays strictly at the SQL-query layer throughout — six independent queries across three consumer actions, each wrapped individually; no whole action's assembled output (`Top::get()`, `GetTagWithPhotosAndAlbums::do()`) and no controller response is ever cached (Goal 3).

## Scope Alignment

- **In scope:** All FR-053-01 through FR-053-31 (minus the removed FR-053-17) and NFR-053-01 through NFR-053-08 as written in `spec.md`. Six independently-cached queries across three consumer actions (`getChildrenPaginated()`; `Top()`'s tag-albums/person-albums/pinned-albums/root-albums queries, each separately; the Tag detail page's album query), 9 new events, both listeners (one carrying a 10th mapping), the `managed_cache_enabled`/`managed_cache_ttl`/`managed_cache_albums_enabled` migration + Settings patch (resuming Feature 052's T-052-05/06, plus the new per-part toggle).
- **Out of scope:** Photo-listing caching (`PhotoRepository::getPhotosForAlbumPaginated()` and `GetTagWithPhotosAndAlbums::getAccessiblePhotos()`), precise per-descendant tagging for subtree ownership propagation, admin per-part cache configuration (Feature 052's Goal 4), any change to `RouteCacher`/Feature 040. See spec.md Non-Goals for full list and rationale.

## Dependencies & Interfaces

- `App\Services\Cache\ManagedCacheService` (Feature 052, already implemented) — `remember()`/`forgetTag()`/`addTags()`, gated on `ConfigManager::getValueAsBool('managed_cache_enabled')`.
- `App\Repositories\ConfigManager` — DB-backed config reads (not the `config()` helper), per Feature 052's established pattern.
- Existing `App\Events\AlbumSaved`/`AlbumDeleted`, reused with new dispatch sites.
- Existing `App\Events\PhotoTagsChanged(array $photo_ids)` — shape/batching precedent for the new `AlbumTagsChanged(array $tag_ids)` (FR-053-27).
- Existing `RecomputeAlbumSizeOnAlbumChange`/`RecomputeAlbumStatsOnAlbumChange`/`RecomputeAlbumUserThumbsOnPhotoChange` listeners — must keep working unmodified (NFR-053-01).
- `2024_12_28_190150_caching_config.php` — precedent migration shape for FR-053-03.
- Feature 052's `tests/Unit/Services/Cache/ManagedCacheServiceTest.php` and its documented pitfalls (query-count filtering around `CacheListener`; `actingAs()` guard leakage; `owner_id` factory default only valid early in a fresh DB) — reused directly, not rediscovered.

## Assumptions & Risks

- **Assumptions:** `ManagedCacheService`'s existing `remember()`/`addTags()`/`forgetTag()` API plus the new `rememberIf()` (FR-053-25) need no further changes — every requirement in this feature is expressible with the existing 8-tag scheme (`album:{id}`, `album-children:{parent_id|'root'}`, `pinned-albums-listing`, `tag-albums-listing`, `person-albums-listing`, `user:{id}`, `album-listing-global`, `tag:{id}`). `LengthAwarePaginator<Album>` (`getChildrenPaginated()`) and each of the four `Collection<Album\|TagAlbum\|PersonAlbum>` results from `Top::get()`'s constituent queries are cleanly `serialize()`-able (to be confirmed by I17/I18's round-trip tests, NFR-053-05) — if not, the affected sub-query's adoption step is blocked until a collection adjustment is made (would need a new Q-053 open question if discovered). `TopAlbumDTO` itself needs no such check since it's never cached (assembled fresh every call). `Collection<ThumbAlbumResource>` (FR-053-26's return type) is assumed equally serializable — smaller/simpler than the others, lower risk, but worth the same round-trip check at I19.
- **Risks / Mitigations:**
  - *20 separate dispatch sites is a lot of surface for regressions.* Mitigated by doing each as its own small increment with a dedicated test, per NFR-053-04, rather than one large sweep.
  - *Splitting `Top::get()` into four independently-cached queries (rather than one cached `TopAlbumDTO`) is a late design change — first drafted as one monolithic cache, corrected once it was noticed this violated the same query-layer-caching principle already applied to the Tag detail page.* Mitigated by writing I18 as four clearly-separated sub-steps with their own tests, and by NFR-053-03's explicit mixed-hit-and-miss requirement proving the four are genuinely independent, not just individually correct in isolation.
  - *`AlbumComputedDataUpdated` dispatched from within a job that was itself dispatched by a listener on `AlbumSaved`/`AlbumDeleted` risks an infinite loop if wired to the wrong listener.* Mitigated by NFR-053-01's explicit test that `RecomputeAlbumStatsJob`/`RecomputeAlbumSizeJob` are not re-dispatched when `AlbumComputedDataUpdated` fires, and by never registering `RecomputeAlbumSizeOnAlbumChange`/`RecomputeAlbumStatsOnAlbumChange` against the new event.
  - *Coarse `album-listing-global` flush could become the path of least resistance for future increments if not enforced.* Mitigated by NFR-053-06 and by this plan's per-increment Exit criteria explicitly naming which tag(s) each increment must evict.
  - *`GetTagWithPhotosAndAlbums::getAccessibleAlbums()`'s cache key must vary with `AlbumPolicy::getUnlockedAlbumIDs()` (session-scoped), not just user id — getting this wrong is a privacy leak (a password-protected album's presence shown to a session that never unlocked it), not just a staleness bug.* Mitigated by NFR-053-07/S-053-32's dedicated cross-session test, written before the key format is finalized in I19.
  - *`Top()`'s tag-albums, person-albums, and root/shared-albums queries all sort by the identical `AlbumSortingCriterion::createDefault()` source and are otherwise keyed only by `Auth::id()` — a cache key format that doesn't also encode which of the three a given entry belongs to would collide, silently serving one query's cached data as another's (found on review, confirmed against the live code, not hypothetical — Q-053-11).* Mitigated by NFR-053-08's explicit type-discriminating-prefix requirement and I18's key-uniqueness unit test, both written before I18's implementation.
  - *Feature 052's known full-suite pitfalls (`set_time_limit(600)` in several Artisan commands resetting the whole test-process timer; SQLite auto-increment drift breaking `owner_id: 1` factory defaults deep into a full run) are pre-existing and unrelated but will resurface.* Mitigated by following Feature 052's tasks.md guidance: run the full suite (not `--filter`) before declaring this feature done, and set `owner_id` explicitly in any new factory-based test.

## Implementation Drift Gate

Executed 2026-08-09. All 20 dispatch sites (FR-053-04 through FR-053-21, minus removed FR-053-17, plus FR-053-28/29/30) and all six cache adoptions (FR-053-01, FR-053-02 ×4, FR-053-26) implemented and each covered by a targeted, passing test. `make phpstan`: 0 errors. `vendor/bin/php-cs-fixer fix --dry-run`: 0 violations (4 minor style fixes applied during implementation, all in newly-added files). Per explicit user instruction for this implementation pass, the full (non-`--filter`) `php artisan test` run was **not** executed — targeted `--filter` runs were used throughout instead, and all passed. Running the full suite (and confirming no cross-test interference beyond the one pre-existing-pattern issue found and fixed below) remains outstanding before this feature can be considered fully verified end-to-end.

Notable findings during implementation, beyond straight FR-by-FR wiring:
- `BulkEditAlbumsAction`'s new lightweight `id`,`parent_id`-only `Album::query()->select(...)` batch dispatch collided with `Album`'s default `$with` eager-load (`cover`, `min_privilege_cover`, `max_privilege_cover`, `thumb` — all require FK columns like `cover_id` that a minimal select doesn't include), throwing `MissingAttributeException` in strict mode. Fixed via `->without([...])` to strip the default eager loads for that one query, matching the pattern `AlbumRepository::getChildrenPaginated()` already used (`->without(['thumb'])`).
- Three more `AlbumController` endpoints beyond the spec's explicit `cover()` case — `rename()` and `setPinned()` — turned out to also accept any `BaseAlbum` (`Album|TagAlbum|PersonAlbum`) via `HasBaseAlbumTrait`, not just `Album`. Unconditionally dispatching `AlbumSaved` there would have reproduced the exact `TypeError` class of bug that FR-053-11 fixes for `SetProtectionPolicy` — a latent bug not called out in the spec, found and fixed the same way (type-branching dispatch) while implementing T-053-06.
- `AlbumRepositoryTest` does not run inside a DB transaction (uses manual `RequiresEmptyAlbums`/`RequiresEmptyUsers` truncation instead), so its two new disabled-cache tests (`Configs::set('managed_cache_enabled'/'managed_cache_albums_enabled', '0')`) were leaking that config state into the shared SQLite test database for subsequent test runs. Fixed by resetting both keys in the class's `tearDown()`.

No FR/NFR required re-scoping; no new open questions logged.

## Increment Map

1. **I1 – Config migration + Settings patch (resumes Feature 052 T-052-05/06, plus the new per-part toggle)**
   - _Goal:_ FR-053-03, FR-053-25 (config row half).
   - _Preconditions:_ None — first increment, no code dependency on later ones.
   - _Steps:_ Write failing test asserting `managed_cache_enabled`/`managed_cache_ttl`/`managed_cache_albums_enabled` rows all exist post-migration and are visible via `SettingsController::getAll()` regardless of `features.enable-caching`. Add migration mirroring `2024_12_28_190150_caching_config.php` (three rows, not two). Patch `SettingsController::getAll()`'s `'Mod Cache'` filter (`app/Http/Controllers/Admin/SettingsController.php:74`) to exempt all three keys.
   - _Commands:_ `php artisan migrate`, `php artisan test --filter=GetAllSettings`.
   - _Exit:_ `ManagedCacheService` can be constructed against the real `ConfigManager` outside of tests without throwing `ConfigurationKeyMissingException`; `managed_cache_albums_enabled` is readable and editable in Settings.

2. **I2 – New event classes (mechanical, all 9 at once)**
   - _Goal:_ EV-053-01 through EV-053-08, EV-053-11.
   - _Preconditions:_ None.
   - _Steps:_ Create `AlbumChildrenChanged`, `TagAlbumSaved`, `PersonAlbumSaved`, `BaseAlbumRemoved`, `AccessPermissionChanged`, `UserGroupMembershipChanged`, `AlbumComputedDataUpdated`, `AlbumListingCacheFlushRequested`, `AlbumTagsChanged` under `app/Events/`, each `Dispatchable`+`SerializesModels` (or plain `Dispatchable` for the payload-less `AlbumListingCacheFlushRequested`), mirroring `AlbumSaved`'s shape (`AlbumTagsChanged` specifically mirrors the existing `PhotoTagsChanged(array $photo_ids)`). No dispatch sites wired yet — this increment is pure scaffolding so every later increment can `use` a real class.
   - _Commands:_ `make phpstan`.
   - _Exit:_ All 9 classes exist, typed correctly, 0 phpstan errors.

3. **I3 – Fix `SetProtectionPolicy` bug + wire `TagAlbumSaved`/`PersonAlbumSaved` for protection changes**
   - _Goal:_ FR-053-11 (bug fix), part of FR-053-09.
   - _Preconditions:_ I2.
   - _Steps:_ Write failing regression test: call `SetProtectionPolicy::do()` with a `TagAlbum`, assert no `TypeError` and `TagAlbumSaved` dispatched (currently throws). Change `SetProtectionPolicy::do()`'s two `AlbumSaved::dispatch($album)` call sites to branch on `$album instanceof Album` vs `TagAlbum` vs `PersonAlbum`.
   - _Commands:_ `php artisan test --filter=SetProtectionPolicy`.
   - _Exit:_ S-053-14 passes; existing `Album`-instance behavior unchanged (no new test failures in `SharingTest`/existing protection-policy tests).

4. **I4 – `TagAlbumSaved`/`PersonAlbumSaved` on create/update**
   - _Goal:_ Remainder of FR-053-09.
   - _Preconditions:_ I2.
   - _Steps:_ Failing tests first. Dispatch from `CreateTagAlbum::create()`, `CreatePersonAlbum::create()` (after `save()`), `AlbumController::updateTagAlbum()`, `updatePersonAlbum()` (after `save()`+`sync()`).
   - _Commands:_ `php artisan test --filter=TagAlbum`, `--filter=PersonAlbum`.
   - _Exit:_ S-053-11/S-053-12 pass.

5. **I5 – `BaseAlbumRemoved` on tag/person album delete**
   - _Goal:_ FR-053-10.
   - _Preconditions:_ I2.
   - _Steps:_ Failing test: delete a pure tag-album batch (no regular albums mixed in), assert `BaseAlbumRemoved` dispatched per id. Wire dispatch into `Delete.php`'s `deleteTagAlbums()`/`deletePersonAlbums()`.
   - _Commands:_ `php artisan test --filter=Delete`.
   - _Exit:_ S-053-13 passes, including the mixed-batch case (regular albums still get `AlbumDeleted` as before).

6. **I6 – `AlbumSaved` on the 6 previously-silent `AlbumController` mutating endpoints + `AlbumTagsChanged` on tag sync**
   - _Goal:_ FR-053-04, FR-053-28.
   - _Preconditions:_ I2 (for `AlbumTagsChanged`).
   - _Steps:_ Failing test per endpoint (6 sub-cases) asserting `AlbumSaved` dispatched. Add `AlbumSaved::dispatch($album)` at the end of `updateAlbum()`, `cover()`, `header()`, `updateAlbumHeader()`, `rename()`, `setPinned()`. Additionally in `updateAlbum()`, when `$request->tagsProvided()`, capture `$album->tags()->sync($tag_ids)`'s return value and dispatch `AlbumTagsChanged::dispatch(...)` with the union of `attached`+`detached` tag ids (skip the dispatch if that union is empty).
   - _Commands:_ `php artisan test --filter=AlbumController`.
   - _Exit:_ S-053-04/S-053-05 pass; S-053-33/S-053-34's `updateAlbum()` half passes (full negative-cache proof needs I19's listener/cache to exist).

7. **I7 – `Move::do()` event wiring**
   - _Goal:_ FR-053-05, part of FR-053-18.
   - _Preconditions:_ I2.
   - _Steps:_ Failing test: move an album from parent A to parent B, assert `AlbumSaved($album)` and `AlbumChildrenChanged($A->id)` both dispatched (not `AlbumChildrenChanged($B->id)` — that's covered by `AlbumSaved`'s listener rule, verified at I15). Capture `$old_parent_id` per album before `appendNode()`/`saveAsRoot()`; dispatch after. Also dispatch `AlbumListingCacheFlushRequested` once per moved album that has descendants (FR-053-18 partial).
   - _Commands:_ `php artisan test --filter=AlbumMove`.
   - _Exit:_ S-053-06 passes; S-053-21's move half passes (needs I15's listener to fully verify the flush, but the dispatch-site test can assert the event fires).

8. **I8 – `Merge::do()` event wiring**
   - _Goal:_ FR-053-06, part of FR-053-18.
   - _Preconditions:_ I2, I7 (reuses the same descendant-check helper if extracted).
   - _Steps:_ Failing test: merge two source albums (one with a sub-album) into a target, assert `AlbumChildrenChanged($target->id)` dispatched once. Assert `AlbumListingCacheFlushRequested` dispatched if any merged sub-album brought its own descendants.
   - _Commands:_ `php artisan test --filter=AlbumMerge`.
   - _Exit:_ S-053-07 passes.

9. **I9 – `Transfer::do()` event wiring**
   - _Goal:_ FR-053-07, part of FR-053-18.
   - _Preconditions:_ I2, I3/I4 (needs `TagAlbumSaved`/`PersonAlbumSaved` available for non-`Album` transfers).
   - _Steps:_ Failing tests: transfer a leaf `Album` (assert `AlbumSaved` + old-parent `AlbumChildrenChanged` if it had one, no coarse flush), transfer an `Album` with descendants (assert coarse flush too), transfer a `TagAlbum` (assert `TagAlbumSaved`, no `AlbumChildrenChanged`).
   - _Commands:_ `php artisan test --filter=Transfer`.
   - _Exit:_ S-053-08, S-053-21's transfer half pass.

10. **I10 – `BulkEditAlbumsAction` event wiring**
    - _Goal:_ FR-053-08.
    - _Preconditions:_ None (uses existing `AlbumSaved`).
    - _Steps:_ Failing tests: bulk-edit `is_nsfw` only (Group 1 only) → assert `AlbumSaved` per album; bulk-edit `album_sorting_col` only (Group 2 only) → same; bulk-edit visibility fields (Group 3) → confirm no double-count regression (harmless double dispatch acceptable per spec, but test should not assert exactly-once if that's not guaranteed). Add one lightweight `id`,`parent_id` projection query + dispatch loop at the end of `BulkEditAlbumsAction::do()`.
    - _Commands:_ `php artisan test --filter=BulkEditAlbums`.
    - _Exit:_ S-053-09/S-053-10 pass.

11. **I11 – `SharingController`/`Propagate` event wiring**
    - _Goal:_ FR-053-12, FR-053-13.
    - _Preconditions:_ I2.
    - _Steps:_ Failing tests per method: `create()`/`edit()`/`delete()` → `AccessPermissionChanged`; `propagate()` (both `Propagate::update()`'s per-descendant loop path and `Propagate::overwrite()`'s raw-bulk path) → `AlbumListingCacheFlushRequested`.
    - _Commands:_ `php artisan test --filter=Sharing`, `--filter=Propagate`.
    - _Exit:_ S-053-15/S-053-16 pass.

12. **I12 – `UserGroupsManagementController` event wiring**
    - _Goal:_ FR-053-14.
    - _Preconditions:_ I2.
    - _Steps:_ Failing tests: `addUser()`/`removeUser()`/`updateUserRole()` each dispatch `UserGroupMembershipChanged($user_id)`.
    - _Commands:_ `php artisan test --filter=UserGroupsManagement`.
    - _Exit:_ S-053-17 passes.

13. **I13 – Async recompute-job completion event**
    - _Goal:_ FR-053-15.
    - _Preconditions:_ I2.
    - _Steps:_ Failing tests: `RecomputeAlbumStatsJob::handle()`/`RecomputeAlbumSizeJob::handle()` dispatch `AlbumComputedDataUpdated($album_id)` after `save()`. **Critical companion test (NFR-053-01):** assert neither `RecomputeAlbumSizeOnAlbumChange` nor `RecomputeAlbumStatsOnAlbumChange` is listening to `AlbumComputedDataUpdated` (no re-dispatch loop) — do this before wiring the listener in I15, or add a `Event::fake()` assertion that only the new listener class handles it.
    - _Commands:_ `php artisan test --filter=RecomputeAlbum`.
    - _Exit:_ S-053-18 passes; loop-safety proven.

14. **I14 – Remaining coarse-flush dispatch sites**
    - _Goal:_ FR-053-16, FR-053-19, FR-053-20, FR-053-21. (~~FR-053-17~~ removed during spec review — `Top()`'s smart-album section is never cached, so `SetSmartProtectionPolicy::do()` needs no dispatch at all; a regression test proving that stays part of this increment.)
    - _Preconditions:_ I2.
    - _Steps:_ Four independent, small sub-changes (can be done in any order within this increment):
      a. `SettingsController::setConfigs()` → `AlbumListingCacheFlushRequested` when payload intersects the 7-key set (FR-053-16).
      b. *(removed — see Goal note above; keep the letter gap rather than reflowing c/d/e, for stable cross-references)*
      c. `PhotosToBeDeletedDTO`'s force-delete path → capture affected album/tag-album ids before the raw cover/header-nulling `UPDATE`s, dispatch `AlbumSaved`/`TagAlbumSaved` per id after (FR-053-19).
      d. `Console\Commands\FixTree` → dispatch after a repair that actually changed rows (FR-053-20).
      e. `ApplyNsfwAlbumSensitivityJob::handle()` → `AlbumSaved::dispatch($album)` per album after `save()` (FR-053-21).
      f. Regression test: `SetSmartProtectionPolicy::do()` dispatches nothing (S-053-20) — cheap to add here alongside (a)-(e) even though it's a "confirm absence" test, not a new dispatch site.
    - _Commands:_ `php artisan test --filter=SettingsController`, `--filter=PhotoDelete`, `--filter=FixTree`, `--filter=NsfwSensitivity`, `--filter=SmartProtection`.
    - _Exit:_ S-053-19, S-053-20, S-053-22, S-053-23, S-053-24 pass.

15. **I15 – `ManagedCacheAlbumListingInvalidator` + `ManagedCacheUserListingInvalidator`**
    - _Goal:_ FR-053-22, FR-053-23, FR-053-31.
    - _Preconditions:_ I2 (event classes must exist to construct in unit tests directly via `Event::dispatch()`; full end-to-end coverage additionally benefits from I3–I14 and I16 being done first, but isn't blocked on them).
    - _Steps:_ Failing unit tests per event→tag mapping (10 mappings on the album listener including `AlbumTagsChanged` → `forgetTag("tag:{id}")` per id, 1 on the user listener) — including the FR-053-22 rule that regular-`Album` `AlbumSaved`/`AlbumDeleted`/`AlbumChildrenChanged` always additionally evict `album-children:root` (not just the album's actual parent), covering both the pin and unpin direction for nested albums (S-053-28) without a dedicated coarse-flush trigger. Implement both listener classes, constructor-injecting `ManagedCacheService`. Register all 11 `Event::listen()` bindings in `EventServiceProvider`.
    - _Commands:_ `php artisan test --filter=ManagedCacheAlbumListingInvalidator`, `--filter=ManagedCacheUserListingInvalidator`.
    - _Exit:_ Every mapping in FR-053-22/31 individually verified; NFR-053-06 spot-checked (only the 4 designated flush triggers reach `album-listing-global`; the three new named tags — `pinned-albums-listing`/`tag-albums-listing`/`person-albums-listing` — each evicted only by their own relevant events, not by unrelated ones).

16. **I16 – Tag CRUD event wiring (`MergeTag`/`DeleteTag`)**
    - _Goal:_ FR-053-29, FR-053-30.
    - _Preconditions:_ I2.
    - _Steps:_ Failing tests: `MergeTag::handleAlbums()` dispatches `AlbumTagsChanged::dispatch([$source->id, $into->id])` after its raw insert/delete (reusing the `$source_album_ids` it already computes, no extra query needed); `DeleteTag::do()` dispatches `AlbumTagsChanged::dispatch($tag_ids)` after its raw `albums_tags` delete. `EditTag::do()` needs no direct change — it delegates to `MergeTag::do()` and inherits the fix.
    - _Commands:_ `php artisan test --filter=MergeTag`, `--filter=DeleteTag`, `--filter=EditTag`.
    - _Exit:_ S-053-35 passes.

17. **I17 – `ManagedCacheService::rememberIf()` + adopt in `AlbumRepository::getChildrenPaginated()`**
    - _Goal:_ FR-053-25 (method half), FR-053-01, FR-053-24 (children half).
    - _Preconditions:_ I15 (invalidation must exist before adoption, so cache correctness is provable immediately, not left dangling), I1 (config row must exist).
    - _Steps:_ Failing unit test for `rememberIf()` itself (condition `false` → `$callback()`, zero `Cache::get()`/`put()` calls; condition `true` → delegates to `remember()` unchanged). Add the method to `ManagedCacheService`. Failing feature test: cache-hit zero-extra-query proof (NFR-053-03, using Feature 052's documented table-filtered query-count pattern); both-switches-off and only-one-switch-off passthrough (S-053-25/29/30). `AlbumRepository` constructor-injects `ConfigManager`; wrap the query in `rememberIf(...)`; `addTags()` per returned child.
    - _Commands:_ `php artisan test --filter=ManagedCacheService`, `php artisan test --filter=AlbumRepository`.
    - _Exit:_ S-053-01, S-053-03, S-053-25, S-053-27, S-053-29, S-053-30 pass.

18. **I18 – Adopt `ManagedCacheService` in each of `Actions\Albums\Top::get()`'s four constituent queries, independently**
    - _Goal:_ FR-053-02, FR-053-24 (root half), NFR-053-03/05 (the `Top()`-specific proofs), FR-053-25 (queries 2–5 of the total six).
    - _Preconditions:_ I15, I17 (reuses its `rememberIf()`/query-count test pattern).
    - _Steps:_ `Top::get()` itself is **not** wrapped — it stays a plain 4-piece assembler, unchanged in structure. Wrap each of its four queries in its own `rememberIf($this->config_manager->getValueAsBool('managed_cache_albums_enabled'), $key, $tags, $this->config_manager->getValueAsInt('managed_cache_ttl'), $callback)` call (reuses `Top`'s already-injected `ConfigManager`), in this order, each as its own failing-test-first sub-step. **Critical: give each query's key its own type-discriminating prefix** (NFR-053-08) — `tag_albums`/`person_albums`/root-shared-albums all sort by the identical `$this->sorting` criteria and are otherwise keyed only by `Auth::id()`, so a key format that omits the prefix will silently collide (verified against `app/Actions/Albums/Top.php`'s actual sort calls during spec review, Q-053-11):
      a. **Tag albums** — key `"tag-albums-listing:user:{id\|'guest'}:sort:{col}:{order}"`, tags `tag-albums-listing`, `user:{id\|'guest'}`, `album-listing-global`; `addTags()` per returned `TagAlbum`.
      b. **Person albums** — key `"person-albums-listing:user:{id\|'guest'}:sort:{col}:{order}"`, tags `person-albums-listing`, `user:{id\|'guest'}`, `album-listing-global`; `addTags()` per returned `PersonAlbum`. (Conditional on `ai_vision_face_enabled`; when disabled, the method returns `collect()` without querying at all today — no caching needed for that branch.)
      c. **Pinned albums** — key `"pinned-albums-listing:user:{id\|'guest'}:sort:{col}:{order}"` (own `sorting_pinned_albums_col/order`), tags `pinned-albums-listing`, `user:{id\|'guest'}`, `album-listing-global`; `addTags()` per returned `Album`.
      d. **Root/shared albums** — key `"album-children:root:user:{id\|'guest'}:sort:{col}:{order}"` (no `page:` segment, unlike FR-053-01's key, so it can never collide with `getChildrenPaginated()`'s own root case despite sharing the same tag), tags `album-children:root` (same tag `getChildrenPaginated()` uses for its own root case), `user:{id\|'guest'}`, `album-listing-global`; `addTags()` per returned `Album`. Ownership partitioning (`$a`/`$b` split for authenticated users) stays in-memory, applied *after* retrieving the (possibly cached) result — not part of the cached value itself.
      Explicitly do **not** wrap `$smart_albums` — it involves no SQL query (see spec.md Overview/§FR-053-17 removal).
      Then: a key-uniqueness unit test (NFR-053-08) asserting all four keys (plus `getChildrenPaginated()`'s and `getAccessibleAlbums()`'s, for the full six) are distinct given identical user/sort fixtures. Cache-hit zero-extra-query proof per sub-query (NFR-053-03), plus a mixed-hit-and-miss test proving the four are independent (one cached, three not → only three tables show query activity) *and* that a primed tag-albums cache never leaks into a root-albums read for the same user. Round-trip fidelity per returned collection type (NFR-053-05) — if any fails, treat as a blocking discovery and log a new `Q-053-xx` open question rather than silently reshaping it. `TopAlbumDTO` itself needs no round-trip test (never cached).
    - _Commands:_ `php artisan test --filter=Top`.
    - _Exit:_ S-053-02, S-053-20 (confirms smart albums untouched), S-053-25, S-053-29, S-053-30 pass; NFR-053-03/05/08 proven per sub-query or a new open question logged.

19. **I19 – Adopt `ManagedCacheService` in `GetTagWithPhotosAndAlbums::getAccessibleAlbums()`**
    - _Goal:_ FR-053-26, FR-053-25 (third consumer), NFR-053-07.
    - _Preconditions:_ I15, I16 (tag-mutation invalidation must exist first), I17 (reuses its `rememberIf()`).
    - _Steps:_ Failing tests, in this order: (1) NFR-053-07/S-053-32's cross-session key-collision guard — two simulated `getUnlockedAlbumIDs()` sets for the same tag+user must never share a cache entry — write and confirm this **before** finalizing the key format, since it drives the key design, not the other way round. (2) Cache-hit zero-extra-query proof for `getAccessibleAlbums()` specifically (`getAccessiblePhotos()` unaffected, still runs every call — S-053-31). (3) Both-switches-off passthrough. Key: `tag_id`, `Auth::id() ?? 'guest'`, hash of `AlbumPolicy::getUnlockedAlbumIDs()`. Tags: `tag:{tag_id}`, `user:{id\|'guest'}` up front, `addTags()` with `album:{id}` per returned album.
    - _Commands:_ `php artisan test --filter=GetTagWithPhotosAndAlbums`.
    - _Exit:_ S-053-31, S-053-32, S-053-33, S-053-34 pass (the negative-cache halves of S-053-33/34 complete here, building on I6's dispatch-site half).

20. **I20 – Full regression pass, drift gate, documentation**
    - _Goal:_ NFR-053-02, NFR-053-04 (final sweep across all 20 triggers), Implementation Drift Gate, Documentation Deliverables.
    - _Preconditions:_ I1–I19 all complete.
    - _Steps:_ Run full `php artisan test` (not `--filter`, per Feature 052's documented full-suite-only pitfalls). Run `make phpstan` and `php-cs-fixer --dry-run`. Execute the Implementation Drift Gate (re-check every FR against the diff). Update `knowledge-map.md`, `roadmap.md`, Feature 052's `tasks.md` cross-reference note, `_current-session.md`.
    - _Commands:_ `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run`.
    - _Exit:_ All Exit Criteria below satisfied.

## Scenario Tracking

| Scenario ID | Increment | Notes |
|-------------|-----------|-------|
| S-053-01 | I17 | |
| S-053-02 | I18 | |
| S-053-03 | I17 | |
| S-053-04 | I6 | |
| S-053-05 | I6 | |
| S-053-06 | I7 | |
| S-053-07 | I8 | |
| S-053-08 | I9 | |
| S-053-09 | I10 | |
| S-053-10 | I10 | |
| S-053-11 | I4 | |
| S-053-12 | I4 | |
| S-053-13 | I5 | |
| S-053-14 | I3 | |
| S-053-15 | I11 | |
| S-053-16 | I11 | |
| S-053-17 | I12 | |
| S-053-18 | I13 | |
| S-053-19 | I14a | |
| S-053-20 | I14f + I18 | Regression test (no dispatch) written at I14f; the "smart albums are always live regardless" half is inherent to I18's design (nothing to cache there in the first place). |
| S-053-21 | I7 + I9 + I15 | Dispatch-site half proven at I7/I9; flush-actually-happens half proven once I15's listener exists. |
| S-053-22 | I14c | |
| S-053-23 | I14d | |
| S-053-24 | I14e | |
| S-053-25 | I17 + I18 | |
| S-053-26 | I17 | TTL expiry covered by `ManagedCacheService`'s existing tests (Feature 052) plus one integration check here. |
| S-053-27 | I17 | |
| S-053-28 | I6 + I15 | Dispatch-site half proven at I6 (`setPinned()`/`updateAlbum()` already dispatch `AlbumSaved`); the root+parent-both-evicted behavior is proven once I15's listener rule exists. |
| S-053-29 | I17 + I18 | |
| S-053-30 | I17 + I18 | |
| S-053-31 | I19 | |
| S-053-32 | I19 | |
| S-053-33 | I6 + I19 | Dispatch-site half at I6; cache-adoption half (the negative-cache proof) at I19. |
| S-053-34 | I6 + I19 | |
| S-053-35 | I16 | |

## Analysis Gate

Signed off 2026-08-09, retroactively at implementation completion (I1–I20 were executed directly against this already-reviewed spec/plan without a separate pre-I1 gate step). Every FR-053-xx/NFR-053-xx enumerated in spec.md has a corresponding implementation and passing test, traced increment-by-increment in tasks.md; no mutation site from the audit was found missing during implementation. See Implementation Drift Gate above for the handful of implementation-time findings (none required a spec change).

## Exit Criteria

- All 20 increments (I1–I20) complete, each increment's Exit condition met.
- Every FR-053-01 through FR-053-31 and NFR-053-01 through NFR-053-08 satisfied and traced to a passing test (NFR-053-04's 20-trigger enumeration fully checked off in tasks.md).
- Full `php artisan test` green (not `--filter`), `make phpstan` 0 errors, `php-cs-fixer --dry-run` 0 violations.
- Implementation Drift Gate executed and recorded above.
- `knowledge-map.md`, `roadmap.md`, `_current-session.md` updated; Feature 052's `tasks.md` carries the cross-reference note.

## Follow-ups / Backlog

- Photo-listing caching (`PhotoRepository::getPhotosForAlbumPaginated()` and `GetTagWithPhotosAndAlbums::getAccessiblePhotos()`) — deferred per this feature's Non-Goals; would need its own mutation-surface audit (photo tag/person/rating/highlight changes, upload pipeline) before a spec could be written.
- Feature 052's Goal 4 (admin-configurable per-part cache toggles) — this feature's fixed `managed_cache_enabled` gate would become the default-on toggle if that design ever lands.
- Precise per-descendant tagging for subtree ownership propagation, if the coarse `album-listing-global` flush proves too disruptive in practice (would need real usage data first — not worth speculative optimization now, per NFR-053-06's tradeoff).
