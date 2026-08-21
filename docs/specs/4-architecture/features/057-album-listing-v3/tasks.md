# Feature 057 Tasks – Album Listing v3

_Status: Draft_
_Last updated: 2026-08-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-057-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.

## Checklist

- [ ] T-057-01 – Unit test + implement `AlbumQueryPolicy::joinBaseAlbumBulkEditFields()` (FR-057-03).
  _Intent:_ New minimal join helper (mirrors `joinBaseAlbumSensitive()`) providing `copyright`/`photo_layout`/`photo_sorting_col`/`photo_sorting_order`/`photo_timeline` from `base_albums`, under its own alias so it doesn't collide with the policy's default `base_albums` join.
  _Verification commands:_
  - `php artisan test --filter=AlbumQueryPolicyTest`
  - `make phpstan`
  _Notes:_ Plan I1.

- [ ] T-057-02 – Unit test + implement `CacheKeyProvider::albumListingV3Tag()`/`albumListingV3Key()` (FR-057-05, NFR-057-04).
  _Intent:_ Key varies by user identity + `(with_parent_id, for_bulk_edit)`; test asserts uniqueness across the (guest, user A, user B) × (00,10,01,11) matrix.
  _Verification commands:_
  - `php artisan test --filter=CacheKeyProviderTest`
  - `make phpstan`
  _Notes:_ Plan I1.

- [ ] T-057-03 – Feature tests first: default-mode S-057-01/02/08/13/15/16/17/18 (S-057-01, S-057-02, S-057-08, S-057-13, S-057-15, S-057-16, S-057-17, S-057-18).
  _Intent:_ `tests/Feature_v3/Album/AlbumListV3Test.php` extending `Tests\Feature_v3\Base\BaseApiWithDataTest`; guest visitor, non-admin owned/shared/public curation, empty result, password-locked-but-visible regression guard, and the 4-branch `cover_ids` priority resolution (explicit/max-privilege/least-privilege/null). Written to fail (no controller yet).
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  _Notes:_ Plan I2.

- [ ] T-057-04 – Implement `AlbumListV3Request`, `AlbumListResource`, `AlbumListController::index()`, route registration (FR-057-01, FR-057-07, FR-057-08, FR-057-09, NFR-057-01, NFR-057-03, S-057-01, S-057-02, S-057-08, S-057-13, S-057-15, S-057-16, S-057-17, S-057-18).
  _Intent:_ Default mode only, `toBase()` query incl. the 3 cover columns, `cover_ids` resolved via a pure helper mirroring `HasAlbumThumb::getCoverTypeForAlbum()`, SoA response, `GET /api/v3/Albums` in `routes/api_v3.php`. Makes T-057-03 pass.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  - `make phpstan`
  _Notes:_ Plan I2.

- [ ] T-057-05 – Feature tests first: `with_parent_id` S-057-03/05/12 (S-057-03, S-057-05, S-057-12).
  _Intent:_ Non-admin 403, admin correct `parent_ids` incl. `null` for roots, full index-alignment. Written to fail.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  _Notes:_ Plan I3.

- [ ] T-057-06 – Implement `with_parent_id` flag end-to-end (FR-057-02, S-057-03, S-057-05, S-057-12).
  _Intent:_ Extend `authorize()`, add `albums.parent_id` to the select, populate `AlbumListResource::$parent_ids`. Makes T-057-05 pass.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  - `make phpstan`
  _Notes:_ Plan I3.

- [ ] T-057-07 – Feature tests first: `for_bulk_edit` S-057-04/06/07 (S-057-04, S-057-06, S-057-07).
  _Intent:_ Non-admin 403, admin full field-parity values, both flags combined. Written to fail.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  _Notes:_ Plan I4.

- [ ] T-057-08 – Add `AlbumListBulkEditFieldsResource`; implement `for_bulk_edit` flag end-to-end (FR-057-03, FR-057-04, S-057-04, S-057-06, S-057-07).
  _Intent:_ Uses T-057-01's join helper, adds `users` left-join for `owner_name`, second `joinSubComputedAccessPermissions(prefix: 'public_', user: null)` for public-permission fields. Makes T-057-07 pass.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  - `make phpstan`
  _Notes:_ Plan I4. Manually cross-check a few field values against a direct `GET /BulkAlbumEdit` response for the same fixture album (sanity check, not a lasting test dependency).

- [ ] T-057-09 – Feature tests first: cache hit/toggle-off S-057-09/11 (S-057-09, S-057-11).
  _Intent:_ Query-count assertion (filtered to `albums`/`base_albums`/`access_permissions`/`users`, per Feature 053's `Illuminate\Cache\Events\*` noise caveat) for cache-hit; correctness-without-caching when either toggle is `false`. Written to fail.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  _Notes:_ Plan I5.

- [ ] T-057-10 – Wrap controller query in `ManagedCacheService::rememberIf()`; feature test S-057-14 (FR-057-05, NFR-057-04, S-057-09, S-057-11, S-057-14).
  _Intent:_ Gated on `managed_cache_enabled` AND `managed_cache_albums_enabled`, TTL from `managed_cache_ttl`, tagged with `albumListingV3Tag()`. Makes T-057-09 pass; add S-057-14 cross-identity/cross-mode no-leakage test.
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  - `make phpstan`
  _Notes:_ Plan I5.

- [ ] T-057-11 – Feature test first + extend `ManagedCacheAlbumListingInvalidator` S-057-10 (FR-057-06, S-057-10).
  _Intent:_ Every existing handler in the invalidator also evicts `albumListingV3Tag()`. Re-confirm the invalidator's current event list against the live file before editing (Implementation Drift Gate).
  _Verification commands:_
  - `php artisan test --filter=AlbumListV3Test`
  - `php artisan test --filter=ManagedCacheAlbumListingInvalidator`
  - `make phpstan`
  _Notes:_ Plan I6.

- [ ] T-057-12 – Quality gate + docs sync (NFR-057-06).
  _Intent:_ Full targeted test sweep, phpstan, php-cs-fixer; update `docs/specs/3-reference/api-design.md`, `docs/specs/4-architecture/knowledge-map.md`; move roadmap.md's Feature 057 row to Completed.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `make phpstan`
  - `php artisan test --filter=AlbumListV3Test`
  - `php artisan test --filter=AlbumQueryPolicyTest`
  - `php artisan test --filter=CacheKeyProviderTest`
  - `php artisan test --filter=ManagedCacheAlbumListingInvalidator`
  _Notes:_ Plan I7. Prepare commit summary per AGENTS.md commit protocol; do not commit directly.

## Notes / TODOs

None yet — implementation not started.
