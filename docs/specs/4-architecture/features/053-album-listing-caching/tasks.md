# Feature 053 Tasks – Album Listing Caching

_Status: Complete_
_Last updated: 2026-08-09_

> Keep this checklist aligned with `plan.md`'s increments and `spec.md`'s normative sections. Tests are staged before implementation in every task. **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
>
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes.

## Checklist

- [x] T-053-01 – Migration: `managed_cache_enabled`/`managed_cache_ttl`/`managed_cache_albums_enabled` config rows + `SettingsController` `'Mod Cache'` exemption (FR-053-03, FR-053-25).
  _Intent:_ Resumes Feature 052's deferred T-052-05/06, plus the new dedicated `managed_cache_albums_enabled` (`BOOL`, default `'1'`) toggle for album-listing caching specifically. Migration mirrors `2024_12_28_190150_caching_config.php` (three rows); patch `app/Http/Controllers/Admin/SettingsController.php:74`'s visibility filter to exempt all three keys.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan test --filter=GetAllSettings`
  _Notes:_ Plan I1. Must land first — `ManagedCacheService` is otherwise unusable against the real `ConfigManager`.

- [x] T-053-02 – Scaffold 9 new event classes (EV-053-01..08, EV-053-11).
  _Intent:_ `AlbumChildrenChanged`, `TagAlbumSaved`, `PersonAlbumSaved`, `BaseAlbumRemoved`, `AccessPermissionChanged`, `UserGroupMembershipChanged`, `AlbumComputedDataUpdated`, `AlbumListingCacheFlushRequested`, `AlbumTagsChanged` under `app/Events/`, mirroring `AlbumSaved`'s `Dispatchable`/`SerializesModels` shape (`AlbumTagsChanged` specifically mirrors the existing `PhotoTagsChanged(array $photo_ids)`).
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Plan I2. Pure scaffolding, no dispatch sites wired yet.

- [x] T-053-03 – Fix `SetProtectionPolicy` `TypeError` bug + dispatch `TagAlbumSaved`/`PersonAlbumSaved` for protection changes (FR-053-11, S-053-14).
  _Intent:_ Failing regression test first (calling `SetProtectionPolicy::do()` with a `TagAlbum` currently throws `TypeError` since `AlbumSaved`'s constructor is typed `Album`). Branch the two `AlbumSaved::dispatch($album)` sites in `app/Actions/Album/SetProtectionPolicy.php` on `$album`'s actual type.
  _Verification commands:_
  - `php artisan test --filter=SetProtectionPolicy`
  _Notes:_ Plan I3. Depends on T-053-02.

- [x] T-053-04 – Dispatch `TagAlbumSaved`/`PersonAlbumSaved` on create/update (FR-053-09, S-053-11, S-053-12).
  _Intent:_ `CreateTagAlbum::create()`, `CreatePersonAlbum::create()`, `AlbumController::updateTagAlbum()`, `updatePersonAlbum()`.
  _Verification commands:_
  - `php artisan test --filter=TagAlbum`
  - `php artisan test --filter=PersonAlbum`
  _Notes:_ Plan I4. Depends on T-053-02.

- [x] T-053-05 – Dispatch `BaseAlbumRemoved` on tag/person album delete (FR-053-10, S-053-13).
  _Intent:_ `Delete.php`'s `deleteTagAlbums()`/`deletePersonAlbums()`, including the pure-tag/person batch case that currently returns before any event fires.
  _Verification commands:_
  - `php artisan test --filter=Delete`
  _Notes:_ Plan I5. Depends on T-053-02.

- [x] T-053-06 – Dispatch `AlbumSaved` on the 6 previously-silent `AlbumController` endpoints + `AlbumTagsChanged` on tag sync (FR-053-04, FR-053-28, S-053-04, S-053-05, S-053-33, S-053-34).
  _Intent:_ `updateAlbum()`, `cover()`, `header()`, `updateAlbumHeader()`, `rename()`, `setPinned()`. In `updateAlbum()`, when `$request->tagsProvided()`, also capture `$album->tags()->sync($tag_ids)`'s return value and dispatch `AlbumTagsChanged::dispatch(array_values(array_unique([...$changes['attached'], ...$changes['detached']])))` when that union is non-empty.
  _Verification commands:_
  - `php artisan test --filter=AlbumController`
  _Notes:_ Plan I6. Depends on T-053-02 (for `AlbumTagsChanged`).

- [x] T-053-07 – `Move::do()` event wiring (FR-053-05, FR-053-18 partial, S-053-06).
  _Intent:_ `AlbumSaved($album)` per moved album + `AlbumChildrenChanged($old_parent_id)` per distinct previous parent (captured before `appendNode()`/`saveAsRoot()`) + `AlbumListingCacheFlushRequested` when the moved album has descendants.
  _Verification commands:_
  - `php artisan test --filter=AlbumMove`
  _Notes:_ Plan I7. Depends on T-053-02.

- [x] T-053-08 – `Merge::do()` event wiring (FR-053-06, FR-053-18 partial, S-053-07).
  _Intent:_ `AlbumChildrenChanged($target_album->id)` + coarse flush if a merged sub-album brought descendants.
  _Verification commands:_
  - `php artisan test --filter=AlbumMerge`
  _Notes:_ Plan I8. Depends on T-053-02, T-053-07.

- [x] T-053-09 – `Transfer::do()` event wiring (FR-053-07, FR-053-18 partial, S-053-08).
  _Intent:_ `AlbumSaved`/`TagAlbumSaved`/`PersonAlbumSaved` per type + `AlbumChildrenChanged($old_parent_id)` if non-null + coarse flush if it had descendants.
  _Verification commands:_
  - `php artisan test --filter=Transfer`
  _Notes:_ Plan I9. Depends on T-053-02, T-053-03, T-053-04.

- [x] T-053-10 – `BulkEditAlbumsAction` event wiring (FR-053-08, S-053-09, S-053-10).
  _Intent:_ One `AlbumSaved` dispatch loop (lightweight `id`,`parent_id` projection) after all three groups applied — closes the Group-1/2 raw-`->update()` gap, including the `is_nsfw`-only case Group 3 currently excludes.
  _Verification commands:_
  - `php artisan test --filter=BulkEditAlbums`
  _Notes:_ Plan I10.

- [x] T-053-11 – `SharingController`/`Propagate` event wiring (FR-053-12, FR-053-13, S-053-15, S-053-16).
  _Intent:_ `AccessPermissionChanged` from `create()`/`edit()`/`delete()`; `AlbumListingCacheFlushRequested` from `propagate()` (both `Propagate::update()` and `Propagate::overwrite()` paths).
  _Verification commands:_
  - `php artisan test --filter=Sharing`
  - `php artisan test --filter=Propagate`
  _Notes:_ Plan I11. Depends on T-053-02.

- [x] T-053-12 – `UserGroupsManagementController` event wiring (FR-053-14, S-053-17).
  _Intent:_ `UserGroupMembershipChanged($user_id)` from `addUser()`/`removeUser()`/`updateUserRole()`.
  _Verification commands:_
  - `php artisan test --filter=UserGroupsManagement`
  _Notes:_ Plan I12. Depends on T-053-02.

- [x] T-053-13 – Async recompute-job completion event + loop-safety proof (FR-053-15, NFR-053-01, S-053-18).
  _Intent:_ `AlbumComputedDataUpdated($album_id)` dispatched at the end of `RecomputeAlbumStatsJob::handle()`/`RecomputeAlbumSizeJob::handle()`. **Must include** a test proving `RecomputeAlbumSizeOnAlbumChange`/`RecomputeAlbumStatsOnAlbumChange` do not react to this event (no re-dispatch loop).
  _Verification commands:_
  - `php artisan test --filter=RecomputeAlbum`
  _Notes:_ Plan I13. Depends on T-053-02.

- [x] T-053-14 – Settings config coarse-flush dispatch (FR-053-16, S-053-19).
  _Intent:_ `SettingsController::setConfigs()` dispatches `AlbumListingCacheFlushRequested` when the payload intersects `{sorting_albums_col, sorting_albums_order, sorting_pinned_albums_col, sorting_pinned_albums_order, deduplicate_pinned_albums, ai_vision_face_enabled, albums_per_page}`.
  _Verification commands:_
  - `php artisan test --filter=SettingsController`
  _Notes:_ Plan I14a. Depends on T-053-02.

- [x] T-053-15 – Regression test: `SetSmartProtectionPolicy::do()` dispatches nothing (~~FR-053-17~~ removed, S-053-20).
  _Intent:_ `~~FR-053-17~~` (originally: dispatch `AlbumListingCacheFlushRequested` on smart-album policy change) was removed during spec review — `Top()`'s smart-album section is computed fresh via `Gate::check()` on every call, never cached, so there's nothing for any event to invalidate. This task is now a regression/documentation test only: assert `SetSmartProtectionPolicy::do()` dispatches no event at all.
  _Verification commands:_
  - `php artisan test --filter=SmartProtection`
  _Notes:_ Plan I14f. No implementation change needed in `SetSmartProtectionPolicy` itself — do not add a dispatch call here.

- [x] T-053-16 – Photo-delete cross-album cover/header cleanup fix (FR-053-19, S-053-22).
  _Intent:_ `PhotosToBeDeletedDTO`'s force-delete path captures affected `albums`/`tag_albums` ids before the raw `header_id`/`cover_id` nulling `UPDATE`s, dispatches `AlbumSaved`/`TagAlbumSaved` per affected id after.
  _Verification commands:_
  - `php artisan test --filter=PhotoDelete`
  _Notes:_ Plan I14c. Depends on T-053-02, T-053-04. Single most surprising audit finding — cross-album, not scoped to the deleting album.

- [x] T-053-17 – `FixTree` coarse-flush dispatch (FR-053-20, S-053-23).
  _Intent:_ Dispatch `AlbumListingCacheFlushRequested` only when the repair actually changed rows (no-op run triggers nothing).
  _Verification commands:_
  - `php artisan test --filter=FixTree`
  _Notes:_ Plan I14d. Depends on T-053-02.

- [x] T-053-18 – `ApplyNsfwAlbumSensitivityJob` dispatch (FR-053-21, S-053-24).
  _Intent:_ `AlbumSaved::dispatch($album)` per album after `$album->is_nsfw = true; $album->save();`.
  _Verification commands:_
  - `php artisan test --filter=NsfwSensitivity`
  _Notes:_ Plan I14e.

- [x] T-053-19 – `ManagedCacheAlbumListingInvalidator` + `ManagedCacheUserListingInvalidator` (FR-053-22, FR-053-23, FR-053-31).
  _Intent:_ 10 event→tag mappings on the album listener + 1 on the user listener (`UserGroupMembershipChanged`). Album listener mapping: `AlbumSaved` → `album:{id}` + `album-children:{parent}` + unconditional `album-children:root` + unconditional `pinned-albums-listing`; `AlbumDeleted` → `album-children:{parent}` + unconditional `album-children:root` + unconditional `pinned-albums-listing`; `AlbumChildrenChanged` → `album-children:{parent}` only (no unconditional extras); `TagAlbumSaved` → `album:{id}` + unconditional `tag-albums-listing`; `PersonAlbumSaved` → `album:{id}` + unconditional `person-albums-listing`; `BaseAlbumRemoved` → `album:{id}` + unconditional `tag-albums-listing` AND `person-albums-listing` (both, cheap); `AccessPermissionChanged` → `album:{id}` + type-resolved (Album: parent/root/pinned as `AlbumSaved`; TagAlbum: `tag-albums-listing`; PersonAlbum: `person-albums-listing`); `AlbumComputedDataUpdated` → `album:{id}` only; `AlbumListingCacheFlushRequested` → `album-listing-global` alone (sufficient — every cached entry across all six query types carries this tag); `AlbumTagsChanged` → `forgetTag("tag:{id}")` per id. Register all 11 bindings in `EventServiceProvider`.
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheAlbumListingInvalidator`
  - `php artisan test --filter=ManagedCacheUserListingInvalidator`
  _Notes:_ Plan I15. Only hard-depends on T-053-02 (event classes); full end-to-end coverage benefits from T-053-03..18 also being done, but unit tests can dispatch events directly. Reworked from a single `album-children:root` catch-all into three separate named tags (`pinned-albums-listing`/`tag-albums-listing`/`person-albums-listing`) once `Top()` was split into four independently-cached queries (T-053-22) — otherwise pinning an album would spuriously evict the unrelated tag-albums/person-albums caches.

- [x] T-053-20 – Tag CRUD event wiring: `MergeTag`/`DeleteTag` (FR-053-29, FR-053-30, S-053-35).
  _Intent:_ `MergeTag::handleAlbums()` dispatches `AlbumTagsChanged::dispatch([$source->id, $into->id])` after its raw `albums_tags` insert/delete, reusing the `$source_album_ids` it already computes. `DeleteTag::do()` dispatches `AlbumTagsChanged::dispatch($tag_ids)` after its raw `albums_tags` delete. `EditTag::do()` needs no direct change (delegates to `MergeTag::do()`, inherits the fix).
  _Verification commands:_
  - `php artisan test --filter=MergeTag`
  - `php artisan test --filter=DeleteTag`
  - `php artisan test --filter=EditTag`
  _Notes:_ Plan I16. Depends on T-053-02.

- [x] T-053-21 – `ManagedCacheService::rememberIf()` + adopt in `AlbumRepository::getChildrenPaginated()` (FR-053-25 method half, FR-053-01, FR-053-24 children half, NFR-053-03).
  _Intent:_ New `rememberIf(bool $condition, string $key, array $tags, $ttl, \Closure $callback): mixed` on `ManagedCacheService` — `$condition=false` calls `$callback()` with zero cache I/O attempted (not even a `Cache::get()` probe), `$condition=true` delegates unchanged to `remember()`. `AlbumRepository` constructor-injects `ConfigManager`; wraps its query in `rememberIf($config_manager->getValueAsBool('managed_cache_albums_enabled'), "album-children:{parent|'root'}:user:{id|'guest'}:page:{n}:sort:{col}:{order}", $tags, $config_manager->getValueAsInt('managed_cache_ttl'), $callback)` + `addTags()` per returned child. Cache-hit zero-relevant-query proof using Feature 052's documented table-filtered query-count pattern (query-log filtered to `albums`/`base_albums`, not literal zero — `CacheListener`'s own `configs` read is expected noise). Both-switches-off and either-switch-off-alone passthrough cases covered (S-053-25/29/30).
  _Verification commands:_
  - `php artisan test --filter=ManagedCacheService`
  - `php artisan test --filter=AlbumRepository`
  _Notes:_ Plan I17. Depends on T-053-01 (config row), T-053-19.

- [x] T-053-22 – Adopt `ManagedCacheService` in each of `Actions\Albums\Top::get()`'s four constituent queries, independently (FR-053-02, FR-053-24 root half, NFR-053-03/05/08, FR-053-25 queries 2-5).
  _Intent:_ `Top::get()` itself is **not** wrapped — it stays a plain 4-piece assembler. Each of its four queries gets its own `rememberIf($this->config_manager->getValueAsBool('managed_cache_albums_enabled'), $key, $tags, $this->config_manager->getValueAsInt('managed_cache_ttl'), $callback)` call (reuses `Top`'s already-injected `ConfigManager`). **Each key needs its own type-discriminating prefix (NFR-053-08)** — `tag_albums`/`person_albums`/root-shared-albums all sort by the identical `$this->sorting` criteria and are otherwise keyed only by `Auth::id()`, so omitting the prefix silently collides (confirmed against the live code during spec review, Q-053-11): tag albums (key `tag-albums-listing:user:{id|'guest'}:sort:{col}:{order}`, tags `tag-albums-listing`+`user:{id|'guest'}`+`album-listing-global`, `addTags()` per `TagAlbum`); person albums (key `person-albums-listing:user:{id|'guest'}:sort:{col}:{order}`, tags `person-albums-listing`+`user:{id|'guest'}`+`album-listing-global`, `addTags()` per `PersonAlbum`, still returns `collect()` uncached when `ai_vision_face_enabled` is off); pinned albums (key `pinned-albums-listing:user:{id|'guest'}:sort:{col}:{order}`, tags `pinned-albums-listing`+`user:{id|'guest'}`+`album-listing-global`, `addTags()` per `Album`); root/shared albums (key `album-children:root:user:{id|'guest'}:sort:{col}:{order}` — no `page:` segment, so it can't collide with `getChildrenPaginated()`'s own root case despite sharing the same tag — tags `album-children:root`+`user:{id|'guest'}`+`album-listing-global`, `addTags()` per `Album`, ownership partition stays in-memory after retrieval). `$smart_albums` is explicitly **not** wrapped — no SQL query involved. Key-uniqueness unit test asserting all six of this feature's cache keys are distinct given identical user/sort fixtures (NFR-053-08). Per-query serialize round-trip test for each returned collection type. If any fails, log a new `Q-053-xx` open question rather than silently reshaping it — `TopAlbumDTO` itself needs no round-trip test since it's never cached.
  _Verification commands:_
  - `php artisan test --filter=Top`
  _Notes:_ Plan I18. Depends on T-053-19, T-053-21. Cache-hit query-count proof must be per-query (NFR-053-03), plus one mixed-hit-and-miss test proving the four are independent and non-leaking.

- [x] T-053-23 – Adopt `ManagedCacheService` in `GetTagWithPhotosAndAlbums::getAccessibleAlbums()` (FR-053-26, FR-053-25 third consumer, NFR-053-07).
  _Intent:_ Write the cross-session key-collision guard test (NFR-053-07/S-053-32 — two different `AlbumPolicy::getUnlockedAlbumIDs()` sets for the same tag+user must never share a cache entry) **before** finalizing the key format. Key: `"tag-albums:{tag_id}:user:{id|'guest'}:unlocked:{hash}"` — `tag_id`, `Auth::id() ?? 'guest'`, hash of `getUnlockedAlbumIDs()`. `$ttl` from `$config_manager->getValueAsInt('managed_cache_ttl')`, same source as the other five queries. Tags up front: `tag:{tag_id}`, `user:{id|'guest'}`, `album-listing-global`; `addTags()` with `album:{id}` per returned album. `getAccessiblePhotos()` (the sibling query in the same action) is untouched — always live, per the query-layer-not-request-layer caching principle.
  _Verification commands:_
  - `php artisan test --filter=GetTagWithPhotosAndAlbums`
  _Notes:_ Plan I19. Depends on T-053-19, T-053-20 (tag-mutation invalidation), T-053-21 (`rememberIf()`).

- [x] T-053-24 – Full regression pass, Implementation Drift Gate, documentation (NFR-053-02, NFR-053-04 final sweep).
  _Intent:_ Full `php artisan test` (not `--filter` — Feature 052's `set_time_limit(600)`/SQLite-auto-increment full-suite pitfalls apply here too), `make phpstan`, `php-cs-fixer --dry-run`. Execute Implementation Drift Gate in `plan.md`. Update `knowledge-map.md`, `roadmap.md`, Feature 052's `tasks.md` cross-reference note, `_current-session.md`.
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`
  _Notes:_ Plan I20. Depends on all prior tasks.

## Notes / TODOs

- If any of T-053-22's four per-query collection round-trips fails serialization fidelity, do not silently patch around it — log a new `Q-053-xx` open question, since it may indicate a shape change with its own ripple effects (e.g. Vue/TypeScript consumers via `#[TypeScript()]`). Note `TopAlbumDTO` itself has no round-trip test at all — it is never cached (see T-053-22's intent).
- The coarse `album-listing-global` flush is implemented across 6 tasks (T-053-07/08/09/11/14/17) covering the 4 FR-level flush triggers (`FR-053-18` spans three tasks — T-053-07/08/09, one each for Move/Merge/Transfer; `FR-053-13`→T-053-11, `FR-053-16`→T-053-14, `FR-053-20`→T-053-17) — not T-053-15, which no longer dispatches anything. This is a deliberate accuracy/performance tradeoff per spec.md's Non-Goals and NFR-053-06 — do not "improve" it into precise per-descendant tagging without a new spec discussion; that was explicitly deferred, not forgotten.
- `FR-053-17`/T-053-15 was removed/repurposed during spec review: `Top()`'s smart-album section was never a SQL-query caching concern (always computed live), so no dispatch site was ever needed there. Don't resurrect it without first checking whether something in `Top()` actually changed to make smart albums cacheable.
- T-053-01 formally closes out Feature 052's `T-052-05`/`T-052-06` — when done, add a one-line cross-reference note to `docs/specs/4-architecture/features/052-managed-cache-service/tasks.md` pointing here (do not un-strike or renumber 052's own superseded-task list).
- T-053-23's cross-session guard (NFR-053-07) is a correctness/privacy property, not a performance nicety — a wrong key format there can leak a password-protected album's presence across sessions. Do not relax it for cache-hit-rate reasons without a new spec discussion.
