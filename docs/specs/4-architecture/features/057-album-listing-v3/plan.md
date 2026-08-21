# Feature Plan 057 – Album Listing v3

_Linked specification:_ `docs/specs/4-architecture/features/057-album-listing-v3/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

A single admin-and-visitor-facing `GET /api/v3/Albums` endpoint that three future (separately-scoped) frontend consumers can point at: the Move-target dropdown, the Fix Tree admin page, and the Bulk Album Edit admin page. Success = all FR-057-01..08 implemented, all S-057-01..14 scenarios green, `make phpstan` clean, `php-cs-fixer` clean, and the endpoint measurably lighter than its closest existing analog (`ListAlbums`/`getTargetListAlbums`) by construction (`toBase()`, no tree-building, no breadcrumb computation, no thumbnail resolution).

## Scope Alignment

- **In scope:** New route/controller/FormRequest/resources under the v3 surface; `AlbumQueryPolicy` join-helper addition; `CacheKeyProvider` key/tag additions; `ManagedCacheAlbumListingInvalidator` extension; Feature_v3 tests; docs updates (`api-design.md`, `knowledge-map.md`, `roadmap.md`).
- **Out of scope:** Any `.vue`/`.ts` change; any change to `routes/api_v2.php` or its controllers; new database migrations; new config keys (reuses `managed_cache_enabled`/`managed_cache_albums_enabled`/`managed_cache_ttl`).

## Dependencies & Interfaces

- `App\Policies\AlbumQueryPolicy` (`app/Policies/AlbumQueryPolicy.php`) — `applyVisibilityFilter()`, `joinBaseAlbumOwnerId()`, `joinSubComputedAccessPermissions()`.
- `App\Services\Cache\ManagedCacheService` (`app/Services/Cache/ManagedCacheService.php`) — `rememberIf()`.
- `App\Services\Cache\CacheKeyProvider` (`app/Services/Cache/CacheKeyProvider.php`) — extended with new methods.
- `App\Listeners\ManagedCacheAlbumListingInvalidator` (Feature 053) — extended, not replaced.
- `routes/api_v3.php`, `tests/Feature_v3/Base/BaseApiWithDataTest.php` (Feature 056 precedent).
- Reference precedent: `app/Http/Controllers/Admin/Maintenance/FullTree.php` (query shape), `app/Http/Controllers/Admin/BulkAlbumController.php` + `app/Http/Resources/Admin/BulkAlbumResource.php` (DO-057-02's field list source of truth).

## Assumptions & Risks

- **Assumptions:** `AlbumQueryPolicy::applyVisibilityFilter()`'s internal `prepareModelQueryOrFail()` join (`joinBaseAlbumOwnerId()`, default `$full=true`) already provides `base_albums.title`/`owner_id`/`description`/`created_at` for free once a caller pre-selects columns before calling the filter — confirmed by reading `AlbumQueryPolicy.php:468-500`. No re-verification needed unless that method's column list changes.
- **Risks / Mitigations:**
  - *Risk:* `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s fixed 6-column join doesn't carry `copyright`/`photo_layout`/`sorting_col`/`sorting_order`/`photo_timeline` (all on `base_albums`, confirmed via migration audit) needed for DO-057-02. *Mitigation:* add a second, purpose-specific join helper `joinBaseAlbumBulkEditFields()` on `AlbumQueryPolicy`, mirroring the existing `joinBaseAlbumSensitive()` pattern (a second minimal join under its own column set) rather than widening the shared `joinBaseAlbumOwnerId()` (which many other consumers depend on) — see I1.
  - *Risk:* `owner_name` requires a `users` join that no existing `AlbumQueryPolicy` helper provides (today's `BulkAlbumResource` gets it via lazy Eloquent relation `$row->owner->name`, unavailable on `toBase()` rows). *Mitigation:* explicit `->leftJoin('users', 'users.id', '=', 'base_albums.owner_id')` in the controller/repository query, selecting `users.name as owner_name`.
  - *Risk:* `is_public`/`is_link_required`/`grants_*` in `BulkAlbumResource` come from the *public* `access_permissions` row (`user_id IS NULL AND user_group_id IS NULL`), which is a different concept from the *viewer-scoped* `computed_access_permissions` join `applyVisibilityFilter()` already adds — reusing the same alias would collide. *Mitigation:* a second `joinSubComputedAccessPermissions()` call with a distinct alias prefix (e.g. `public_`) and `$user = null` (its existing null-user branch already selects exactly the public row).
  - *Risk (added, Q-057-05):* `cover_ids` resolution needs `owner_id` (already selected via the policy's default `base_albums` join) plus the current user's `may_administrate`/`id`, mirroring `HasAlbumThumb::getCoverTypeForAlbum()`'s priority rule exactly. *Mitigation:* implement as a small pure static helper (e.g. `AlbumListController::resolveCoverId(stdClass $row, ?User $user): ?string`) operating on already-selected columns — no relation load, no extra query, no dependency on the `HasAlbumThumb` Eloquent Relation class itself (which is model-bound and not usable against `toBase()` rows).
  - *Risk:* Coarse cache invalidation (FR-057-06) could over-evict (flush all users'/all modes' cached listings on any single album mutation). *Mitigation:* accepted deliberately — correctness and implementation simplicity over precision, consistent with the "lightest possible way" instruction; documented in spec Non-Goals/FR-057-06.

## Implementation Drift Gate

Record here at implementation time: confirm `AlbumQueryPolicy::joinBaseAlbumOwnerId()`'s column list is unchanged from the audit above before I2; confirm `ManagedCacheAlbumListingInvalidator`'s current event list (Feature 053) before I6, since new events may have been added by other feature work since 2026-08-10. Record `make phpstan`/`php-cs-fixer`/targeted `php artisan test` results per increment below once run.

## Increment Map

1. **I1 – `AlbumQueryPolicy` join helper + `CacheKeyProvider` additions**
   - _Goal:_ Add the two small, isolated, unit-testable building blocks the rest of the feature depends on, with zero behavior change to any existing consumer.
   - _Preconditions:_ spec.md DO-057-02 field list finalized (done).
   - _Steps:_
     - Unit test first: `AlbumQueryPolicy::joinBaseAlbumBulkEditFields()` returns the expected `copyright`/`photo_layout`/`photo_sorting_col`/`photo_sorting_order`/`photo_timeline` columns (aliased from `base_albums`, prefix pattern mirroring `joinBaseAlbumSensitive()`).
     - Implement `joinBaseAlbumBulkEditFields()` in `app/Policies/AlbumQueryPolicy.php`.
     - Unit test first: new `CacheKeyProvider` methods — `albumListingV3Tag()` (returns `'album-listing-v3'`), `albumListingV3Key(int|string|null $user_id, bool $with_parent_id, bool $for_bulk_edit)` — assert key uniqueness across the (guest, user A, user B) × (00,10,01,11) matrix (NFR-057-04).
     - Implement both methods in `app/Services/Cache/CacheKeyProvider.php`.
   - _Commands:_ `php artisan test --filter=AlbumQueryPolicyTest`, `php artisan test --filter=CacheKeyProviderTest`.
   - _Exit:_ Both new methods exist, unit-tested, `make phpstan` clean on the two changed files.

2. **I2 – Route, FormRequest, controller, default-mode resource (no cache yet)**
   - _Goal:_ `GET /api/v3/Albums` returns the minimal SoA shape correctly for guests and non-admins; no flags, no cache yet.
   - _Preconditions:_ I1 done.
   - _Steps:_
     - Feature test first (`tests/Feature_v3/Album/AlbumListV3Test.php` extending `Tests\Feature_v3\Base\BaseApiWithDataTest`): S-057-01 (guest), S-057-02 (non-admin, owned+shared+public), S-057-08 (empty), S-057-12 (index alignment), S-057-15..18 (cover-id priority resolution).
     - Add `App\Http\Requests\Gallery\AlbumListV3Request` (DO-057-03) — `with_parent_id`/`for_bulk_edit` both default `false` in this increment's `authorize()`/`rules()` (full logic lands in I3/I4; wire the shape now to avoid rework).
     - Add `App\Http\Resources\V3\AlbumListResource` (DO-057-01, Spatie `Data` + `#[TypeScript]`) including `cover_ids`.
     - Add `App\Http\Controllers\Gallery\AlbumListController::index()` — builds `Album::query()`, pre-selects `['albums.id','base_albums.title','albums._lft','albums._rgt','albums.cover_id','albums.auto_cover_id_max_privilege','albums.auto_cover_id_least_privilege']`, applies `AlbumQueryPolicy::applyVisibilityFilter($query, Auth::user())`, orders by `albums._lft`, `->toBase()->get()`, maps to SoA arrays; resolves `cover_ids[i]` per FR-057-09's priority rule (mirrors `HasAlbumThumb::getCoverTypeForAlbum()`, `app/Relations/HasAlbumThumb.php:71-109`) using each row's `owner_id` (already selected via the policy's join) and the current `Auth::user()`.
     - Register `Route::get('/Albums', [Gallery\AlbumListController::class, 'index']);` in `routes/api_v3.php`.
   - _Commands:_ `php artisan test --filter=AlbumListV3Test`.
   - _Exit:_ S-057-01/02/08/15/16/17/18 green; `make phpstan` clean.

3. **I3 – `with_parent_id` flag**
   - _Goal:_ Admin-gated `parent_ids` addition.
   - _Preconditions:_ I2 done.
   - _Steps:_
     - Feature tests first: S-057-03 (non-admin → 403), S-057-05 (admin → correct `parent_ids`, `null` for roots), S-057-12 (full index-alignment including `parent_ids`).
     - Extend `AlbumListV3Request::authorize()`: `true` unless `with_parent_id` resolves `true` and `Auth::user()?->may_administrate !== true`.
     - Controller: when `with_parent_id`, add `albums.parent_id` to the select and populate `AlbumListResource::$parent_ids`; otherwise leave it `null`.
   - _Commands:_ `php artisan test --filter=AlbumListV3Test`.
   - _Exit:_ S-057-03/05/12 green.

4. **I4 – `for_bulk_edit` flag**
   - _Goal:_ Admin-gated full-parity bulk-edit block.
   - _Preconditions:_ I1 (join helper), I3 (authorize() pattern) done.
   - _Steps:_
     - Feature tests first: S-057-04 (non-admin → 403), S-057-06 (admin → values match `BulkAlbumResource`-equivalent computation for the same fixture albums), S-057-07 (both flags combined).
     - Add `App\Http\Resources\V3\AlbumListBulkEditFieldsResource` (DO-057-02).
     - Controller: when `for_bulk_edit`, additionally call `joinBaseAlbumBulkEditFields()` (I1), `->leftJoin('users', 'users.id', '=', 'base_albums.owner_id')`, and a second `joinSubComputedAccessPermissions(..., prefix: 'public_', full: true, user: null)` for the public permission row; select/derive all DO-057-02 fields (`is_public` = `public_computed_access_permissions.base_album_id IS NOT NULL`).
     - Extend `authorize()` to also gate on `for_bulk_edit`.
   - _Commands:_ `php artisan test --filter=AlbumListV3Test`.
   - _Exit:_ S-057-04/06/07 green; cross-check a handful of values against a direct `BulkAlbumController::index()` response for the same fixture album as a manual sanity check (not a permanent test dependency).

5. **I5 – `ManagedCacheService` integration**
   - _Goal:_ Wrap the query in `rememberIf()` using I1's key/tag.
   - _Preconditions:_ I2/I3/I4 done (full response shape stable).
   - _Steps:_
     - Feature tests first: S-057-09 (second identical request served from cache — assert via query-count, filtered to `albums`/`base_albums`/`access_permissions`/`users` tables per Feature 053's documented `Illuminate\Cache\Events\*` noise caveat), S-057-11 (toggle off → uncached, still correct), S-057-14 (no cross-identity/cross-mode leakage).
     - Controller: wrap the query-and-map logic in `$managed_cache_service->rememberIf($enabled, $key, $ttl, fn () => ..., tags: [$cache_key_provider->albumListingV3Tag()])`, `$enabled = $config_manager->getValueAsBool('managed_cache_enabled') && $config_manager->getValueAsBool('managed_cache_albums_enabled')`, `$ttl = $config_manager->getValueAsInt('managed_cache_ttl')`.
   - _Commands:_ `php artisan test --filter=AlbumListV3Test`.
   - _Exit:_ S-057-09/11/14 green.

6. **I6 – Extend `ManagedCacheAlbumListingInvalidator`**
   - _Goal:_ Cache correctness on mutation (FR-057-06).
   - _Preconditions:_ I5 done; re-confirm (Implementation Drift Gate) the invalidator's current event list before editing.
   - _Steps:_
     - Feature test first: S-057-10 (edit/move/delete/permission-change between two requests → second reflects the change).
     - Extend `app/Listeners/ManagedCacheAlbumListingInvalidator.php`: every handler method that currently calls `forgetTag(...)` also calls `forgetTag($this->cache_key_provider->albumListingV3Tag())`.
   - _Commands:_ `php artisan test --filter=AlbumListV3Test`, `php artisan test --filter=ManagedCacheAlbumListingInvalidator`.
   - _Exit:_ S-057-10 green; existing Feature 053 invalidator tests still green (no regression).

7. **I7 – Quality gate, docs, wrap-up**
   - _Goal:_ Full feature-level verification and documentation sync.
   - _Preconditions:_ I1–I6 done, all S-057-01..14 green.
   - _Steps:_
     - `vendor/bin/php-cs-fixer fix`; `make phpstan`; `php artisan test --filter=AlbumListV3Test`, `--filter=AlbumQueryPolicyTest`, `--filter=CacheKeyProviderTest`, `--filter=ManagedCacheAlbumListingInvalidator`.
     - Update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`.
     - Move `docs/specs/4-architecture/roadmap.md`'s Feature 057 row from Active to Completed once all tasks are `[x]`.
     - Prepare commit summary per AGENTS.md commit protocol (stage files, run `./scripts/codex-commit-review.sh`, present to operator — do not commit directly).
   - _Commands:_ as above.
   - _Exit:_ All checklist items in tasks.md `[x]`; quality gate green.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-057-01 | I2 / T-057-02 | Guest visitor, default mode. |
| S-057-02 | I2 / T-057-02 | Non-admin, owned+shared+public. |
| S-057-03 | I3 / T-057-04 | Non-admin + `with_parent_id` → 403. |
| S-057-04 | I4 / T-057-06 | Non-admin + `for_bulk_edit` → 403. |
| S-057-05 | I3 / T-057-04 | Admin + `with_parent_id`. |
| S-057-06 | I4 / T-057-06 | Admin + `for_bulk_edit`, field-value parity. |
| S-057-07 | I4 / T-057-07 | Both flags combined. |
| S-057-08 | I2 / T-057-02 | Empty result set. |
| S-057-09 | I5 / T-057-09 | Cache hit, no repeat query. |
| S-057-10 | I6 / T-057-11 | Cache invalidation on mutation. |
| S-057-11 | I5 / T-057-10 | Cache toggles off → uncached but correct. |
| S-057-12 | I3 / T-057-05 | Index alignment incl. root `parent_ids`. |
| S-057-13 | I2 / T-057-03 | Password-locked-but-visible album still listed. |
| S-057-14 | I5 / T-057-10 | No cross-identity/cross-mode cache leakage. |
| S-057-15 | I2 / T-057-04 | Explicit `cover_id` set. |
| S-057-16 | I2 / T-057-04 | Owner/admin sees `auto_cover_id_max_privilege`. |
| S-057-17 | I2 / T-057-04 | Other viewer sees `auto_cover_id_least_privilege`. |
| S-057-18 | I2 / T-057-04 | No cover columns set → `null`, no fallback query. |

## Analysis Gate

Not yet run. Per AGENTS.md, run the analysis gate checklist ([docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md)) once spec, plan, and tasks agree, before starting I1.

## Exit Criteria

- All FR-057-01..08 and NFR-057-01..06 implemented and covered by S-057-01..14.
- `make phpstan` (level 6+) clean on all new/changed files.
- `vendor/bin/php-cs-fixer fix` clean.
- `php artisan test` targeted runs (`AlbumListV3Test`, `AlbumQueryPolicyTest`, `CacheKeyProviderTest`, `ManagedCacheAlbumListingInvalidator`) all green.
- `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`, `docs/specs/4-architecture/roadmap.md` updated.
- Open questions Q-057-01..04 remain resolved (already recorded in spec.md Appendix).

## Follow-ups / Backlog

- Frontend integration of the Move-target dropdown, Fix Tree page, and Bulk Album Edit page against this endpoint (explicitly out of scope here) — each is its own future feature.
- If the coarse `album-listing-v3` invalidation tag (FR-057-06) proves too eager in practice (e.g. very large installs with frequent unrelated album edits), consider a follow-up to make it precision-tagged like Feature 053's per-parent tags — deferred since no evidence of a problem exists yet.
