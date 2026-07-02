# Feature 048 Tasks – Fix Multi-Group Permissions

_Status: Draft_
_Last updated: 2026-07-01_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-048-`) inside the same parentheses immediately after the task title.

## Checklist

- [ ] T-048-01 – Repo-wide caller sweep for `current_user_permissions()` (Implementation Drift Gate).
  _Intent:_ Re-run `grep -rn "current_user_permissions()" app/ resources/js/` and confirm every caller only checks `!== null` or reads a `grants_*` boolean, matching DO-048-01/DO-048-02's assumption. Record the (unchanged) result inline in this task's notes.
  _Verification commands:_
  - `grep -rn "current_user_permissions()" app resources/js`
  _Notes:_ Must complete before T-048-04/T-048-05 (plan I1/I3 Drift Gate).

- [ ] T-048-02 – Unit tests: `EffectiveAccessPermission::merge()` pure algorithm (F-048-01, S-048-01, S-048-02, S-048-03).
  _Intent:_ Add `tests/Unit/DTO/EffectiveAccessPermissionTest.php`. Build plain `Illuminate\Support\Collection` instances of `new AccessPermission([...])` rows (no DB) and assert `EffectiveAccessPermission::merge()`: (a) OR-combines a low-grant + high-grant row correctly (S-048-01); (b) produces the identical result with the two rows swapped (S-048-02, order-independence); (c) OR-combines a direct-user row with a group row (S-048-03); (d) returns all-`false` flags when given an empty collection (edge case feeding into T-048-03's null-short-circuit).
  _Verification commands:_
  - `php artisan test --filter=EffectiveAccessPermissionTest`
  _Notes:_ Expected to fail (class doesn't exist yet) until T-048-04. Spec: FR-048-01.

- [ ] T-048-03 – Unit tests: `BaseAlbumImpl::current_user_permissions()` filter/delegate/null logic (F-048-01, F-048-03, S-048-04, S-048-05, S-048-06, S-048-07).
  _Intent:_ Add `tests/Unit/Models/BaseAlbumImplCurrentUserPermissionsTest.php`. Build a `BaseAlbumImpl` instance and inject synthetic `AccessPermission` rows via `setRelation(APC::ACCESS_PERMISSIONS, collect([...]))` (no DB); build a `User` instance with `setRelation('user_groups', collect([...]))`; authenticate via `Auth::shouldReceive('guest')->andReturn(false)` + `Auth::shouldReceive('user')->andReturn($user)` (Mockery) or `$this->actingAs($user)` if that proves simpler against in-memory models. Cover: single group only, unaffected (S-048-04); direct share only, unaffected (S-048-05); no matching row → `null` (S-048-06); guest → `null` without evaluating any row (S-048-07).
  _Verification commands:_
  - `php artisan test --filter=BaseAlbumImplCurrentUserPermissionsTest`
  _Notes:_ Expected to fail until T-048-05 rewires the method. Spec: FR-048-01, FR-048-03.

- [ ] T-048-04 – Implement `App\DTO\EffectiveAccessPermission` (F-048-01, NFR-048-02, NFR-048-03).
  _Intent:_ Add `app/DTO/EffectiveAccessPermission.php`: `final readonly class` (SPDX/copyright header matching other `app/DTO/*.php` files) with 5 boolean constructor-promoted properties (`grants_full_photo_access`, `grants_download`, `grants_upload`, `grants_edit`, `grants_delete`, each default `false`) and a static `merge(Collection<int,AccessPermission> $permissions): self` factory that sets each flag via `$permissions->contains(fn (AccessPermission $p) => $p->{flag} === true)`. No mutation of `$permissions`; no branching beyond the 5 independent flag computations (straight-line).
  _Verification commands:_
  - `php artisan test --filter=EffectiveAccessPermissionTest`
  - `make phpstan`
  _Notes:_ T-048-02 must go green. Spec: FR-048-01, NFR-048-02, NFR-048-03.

- [ ] T-048-05 – Wire the DTO into `current_user_permissions()` and propagate the type change (F-048-01, F-048-04).
  _Intent:_ In `app/Models/BaseAlbumImpl.php:261-271`, replace the two `->first(...)` calls with a single `->filter(fn (AccessPermission $p) => $p->user_id === $user->id || in_array($p->user_group_id, $group_ids, true))`, return `null` if the filtered collection is empty, otherwise `return EffectiveAccessPermission::merge($matching);`. Update `App\Models\Extensions\BaseAlbum::current_user_permissions()` (`app/Models/Extensions/BaseAlbum.php:118-121`) return type to match. Update the `@property` docblock in `app/Models/Album.php`, `app/Models/TagAlbum.php`, `app/Models/PersonAlbum.php` from `AccessPermission|null` to the nullable `EffectiveAccessPermission` (and drop the now-unused `AccessPermission` import in those files if PHPStan/php-cs-fixer flags it). Do not touch `public_permissions()`.
  _Verification commands:_
  - `php artisan test --filter=BaseAlbumImplCurrentUserPermissionsTest`
  - `make phpstan`
  _Notes:_ T-048-01 (grep sweep) must be re-confirmed clean first. T-048-03 must go green; a `make phpstan` failure here on any consumer would indicate the caller-sweep assumption was wrong (see plan Risks). Spec: FR-048-01, FR-048-04.

- [ ] T-048-06 – Feature regression test reproducing the exact bug report, both share orders (F-048-01, S-048-01, S-048-02).
  _Intent:_ In `tests/Feature_v2/Album/AlbumSharingTest.php` (reusing its `BaseApiWithDataTest` fixtures) or a new sibling test class in the same namespace, add a test that: creates an album, shares it with `group1` (all `grants_*` false, i.e. "View only") then `group2` (`grants_download` + others true), puts a user in both groups via `user_groups()->attach(...)`, calls the album endpoint the user would use to see the Download button (`GET Albums/{album_id}` or equivalent already-covered endpoint), and asserts `rights.can_download === true`. Add a second test with the two `AccessPermission::factory()->create()` calls swapped in order, asserting the same result.
  _Verification commands:_
  - `php artisan test --filter=AlbumSharingTest`
  _Notes:_ Confirms FR-048-01 through the real HTTP → `AlbumPolicy::canDownload` → `AlbumRightsResource.can_download` path, matching how the bug was actually observed (missing Download button). Spec: FR-048-01, S-048-01, S-048-02.

- [ ] T-048-07 – Query-count guard for NFR-048-01 (F-048-02).
  _Intent:_ In the T-048-06 feature test (or a dedicated test), capture the SQL query count for the `GET Albums/{album_id}` call with a single-group baseline, then repeat with the two-group multi-permission scenario and assert the query count is identical (e.g. via `DB::enableQueryLog()`/`count(DB::getQueryLog())` before/after, or `assertQueryCount` if the project already has that helper — check `tests/Traits` first).
  _Verification commands:_
  - `php artisan test --filter=AlbumSharingTest`
  _Notes:_ This is the executable proof of "least heavy on database queries." Spec: NFR-048-01.

- [ ] T-048-08 – Regression pass on existing permission/sharing tests (F-048-03).
  _Intent:_ Run the full existing test suites touching `AccessPermission`/`AlbumPolicy`/sharing to confirm zero regressions from the merge-rule change and the type swap (especially `AlbumSharingTest`, `PasswordDownloadBypassTest`, `BulkAlbumEdit` tests, `UserGroupTest`, `AccessPermissionTest`).
  _Verification commands:_
  - `php artisan test --filter=AlbumSharingTest`
  - `php artisan test --filter=PasswordDownloadBypassTest`
  - `php artisan test --filter=UserGroupTest`
  - `php artisan test --filter=AccessPermissionTest`
  - `php artisan test`
  _Notes:_ Spec: FR-048-03.

- [ ] T-048-09 – ADR-0004: multi-group permission merge policy (Documentation Deliverables).
  _Intent:_ Add `docs/specs/6-decisions/ADR-0004-multi-group-permission-merge.md` using `docs/specs/templates/adr-template.md`, documenting: (1) the "collect all matching rows (direct-user + every group), OR each boolean grant flag, no precedence between sources" merge policy, citing the precedent in spec.md's Appendix (`AlbumPolicy` public+current OR pattern, `canDeleteById`/`canEditById` group-OR-in-SQL, `AlbumSharingTest.php` docblock); and (2) the decision to represent the merged result as the new `App\DTO\EffectiveAccessPermission` (a plain, non-persistable DTO) rather than a synthetic `App\Models\AccessPermission` instance, so the result cannot be mass-assigned or `save()`d by accident. Reference Q-048-01, FR-048-01, FR-048-04, NFR-048-01, NFR-048-03.
  _Verification commands:_ None (docs only).
  _Notes:_ Link the ADR ID back into spec.md's Documentation Deliverables section once created.

- [ ] T-048-10 – Update roadmap and session snapshot (Documentation Deliverables).
  _Intent:_ Move Feature 048 from "Active Features" to "Completed Features" in `docs/specs/4-architecture/roadmap.md` with a completion date and summary once all tasks are done, and refresh `docs/specs/_current-session.md` accordingly.
  _Verification commands:_ None (docs only).
  _Notes:_ Per AGENTS.md "Sync context to disk."

- [ ] T-048-11 – Full PHP quality gate (per AGENTS.md "After Completing Work").
  _Intent:_ Run the complete PHP quality gate since only `.php` files are touched by this feature.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test`
  - `make phpstan`
  _Notes:_ Must be clean before handing off the commit per AGENTS.md commit protocol (staged summary + commit message prepared for the operator, not executed automatically).

## Notes / TODOs
- If `tests/Traits` already has a query-count-assertion helper (check before writing a new one in T-048-07), reuse it instead of hand-rolling `DB::enableQueryLog()`.
- T-048-05's docblock updates may leave `use App\Models\AccessPermission;` unused in `Album.php`/`TagAlbum.php`/`PersonAlbum.php` if that import existed only for the old `@property` type — check `php-cs-fixer`/PHPStan output and remove if flagged, but keep it if the file still uses `AccessPermission` elsewhere (e.g. `access_permissions` collection typing).
- `EffectiveAccessPermission` lives in `app/DTO/` (flat, no subfolder) matching `CheckoutDTO.php`/`PixelSizeAssignment.php` — do not create a new `app/DTO/Sharing/` subfolder for a single class.
