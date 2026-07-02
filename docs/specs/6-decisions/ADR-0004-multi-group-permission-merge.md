# ADR-0004: Multi-Group Permission Merge Policy

- **Status:** Accepted
- **Date:** 2026-07-01
- **Last updated:** 2026-07-01
- **Related features/specs:** Feature 048 (docs/specs/4-architecture/features/048-fix-multi-group-permissions/spec.md)
- **Related open questions:** Q-048-01 (resolved)

## Context

`BaseAlbumImpl::current_user_permissions()` resolves the effective `AccessPermission` for the current user on an album. Before this change, it used `Collection::first()` twice: once for a direct user-level row, and — only if that lookup failed — once for a matching user-group row. Because `first()` short-circuits on the first element satisfying the predicate, a user who belongs to **two or more groups** with different grants on the same album only ever received the grants of whichever group's row happened to sort first in the already-loaded `access_permissions` collection (effectively insertion/id order). Re-creating the same shares in a different order flipped which group's grants applied.

This is a security-relevant authorization bug: a user could silently lose grants (e.g. Download) that an administrator intended them to have via a more permissive group, purely because of unrelated row ordering. It also crosses module boundaries — the resolution method lives in `app/Models`, but is consumed by the authorization layer (`app/Policies/AlbumPolicy.php`) and the gallery controller (`app/Http/Controllers/Gallery/PhotoController.php`) — so the merge policy needed to be decided once, centrally, rather than patched ad hoc at each call site.

A related, narrower question (Q-048-01) was whether a **direct user-level** `AccessPermission` row should keep overriding group rows (the pre-existing `??` precedence), or be folded into the same union as the group rows once group-vs-group merging was fixed.

## Decision

We adopt a **most-permissive-wins, order-independent merge** across every `AccessPermission` row matching the current user — the direct `user_id` row (if any) plus every row whose `user_group_id` is one of the user's group ids. Each of the five boolean grant flags (`grants_full_photo_access`, `grants_download`, `grants_upload`, `grants_edit`, `grants_delete`) on the merged result is `true` if it is `true` on **any** matching row. There is no precedence between a direct-user row and a group row (Q-048-01, Option A) — the most permissive applicable grant always wins, regardless of row order or source.

The merged result is represented by a new dedicated DTO, `App\DTO\EffectiveAccessPermission` (`final readonly class`, `app/DTO/EffectiveAccessPermission.php`), rather than a synthetic `App\Models\AccessPermission` Eloquent model instance. An `AccessPermission` model is inherently persistable (mass-assignable, `->save()`-able); a computed, request-scoped "effective grants for this user on this album" snapshot must never be written back to the database. Using a plain DTO makes "this can never hit the database" a type-level guarantee instead of a convention every future caller has to remember. `current_user_permissions()`'s return type changes from a nullable `AccessPermission` to a nullable `EffectiveAccessPermission` across `BaseAlbumImpl`, the `BaseAlbum` extension trait, and the `@property` docblocks on `Album`, `TagAlbum`, `PersonAlbum`.

The merge is performed entirely **in memory**, with zero additional database queries: `access_permissions` is already eager-loaded per-album via `BaseAlbumImpl::$with`, and `$user->user_groups` was already read by the pre-fix code. The two `->first(...)` calls are replaced by a single `->filter(...)` over the same already-loaded collection, followed by a `EffectiveAccessPermission::merge($matching)` call when the filtered collection is non-empty.

## Consequences

### Positive
- Eliminates the entire bug class (order-dependent effective permissions), not just the specific group-vs-group scenario reported — the same fix also makes direct-user-vs-group merging order-independent.
- Zero new database queries: the fix is strictly a query-count-neutral change (verified by an explicit query-count-guard test).
- `EffectiveAccessPermission` cannot be mass-assigned, `save()`d, or otherwise persisted — a future accidental write is now a compile-time/type error rather than a silent runtime bug.
- Consistent with the "most permissive wins" pattern already used elsewhere in this codebase: `AlbumPolicy` already ORs `current_user_permissions()?->grants_*` with `public_permissions()?->grants_*`; `canDeleteById`/`canEditById` already OR across every matching group row directly in SQL via `orWhereIn(user_group_id, ...)`. This decision makes the in-memory merge match the SQL-level merge that already existed for a subset of cases.

### Negative
- Widens the diff beyond the single buggy method: the return-type change propagates to the `BaseAlbum` trait and three model docblocks (`Album`, `TagAlbum`, `PersonAlbum`), even though no consumer's logic changes (PHPStan confirms no consumer reads an `AccessPermission`-only member).
- An admin who wanted to use a direct-user row to deliberately *restrict* a specific user below their group's grants would find that restriction silently ignored. No existing UI/flow supports this today, so this is a theoretical trade-off, not an observed regression.

## Alternatives Considered

### Merge algorithm
- **Option B (Q-048-01): direct-user row keeps override precedence; only group rows merge among themselves.** Smaller behavioural change (only fixes the reported group-vs-group scenario), but keeps an inconsistent, order-sensitive special case at the user-vs-group boundary, and a low-privilege direct-user row could still silently suppress a high-privilege group row — arguably the same class of surprise as the original bug, just one level up the precedence chain. Rejected in favor of Option A (uniform merge, no precedence) for consistency with the rest of the codebase.

### Representation of the merged result
- **Synthetic `App\Models\AccessPermission` instance** (`new AccessPermission([...])`, mirroring the existing `AccessPermission::ofPublic()`/`ofPublicHidden()` static-factory pattern). Simpler — no new class, no return-type propagation. Rejected because an `AccessPermission` model is structurally persistable; a computed capability snapshot that happens to share the same class as a real, savable database row is a latent risk (accidental `->save()` or mass-assignment on the synthetic instance would silently attempt to write a bogus row). A plain DTO removes this risk entirely.
- **DB-level aggregate merge** (e.g. a `MAX()`/boolean-OR SQL query per grant flag). Would also fix the bug, but adds a new query per `current_user_permissions()` call, directly violating the explicit "least heavy on database queries" requirement that drove this fix, given `access_permissions` and `user_groups` are already loaded in memory.

## Security / Privacy Impact

- This is a security-relevant authorization fix: before this change, a user could receive **fewer** grants than an administrator intended (a share downgrade caused by unrelated row ordering), which is a fail-safe direction (under-granting), but still incorrect and reported by the affected operator as a Download button silently missing.
- The fix does not widen access beyond what the sum of a user's direct share and group memberships was always intended to grant — it only ensures that sum is computed correctly and deterministically. No new grant flag or capability is introduced.
- `EffectiveAccessPermission` carries no secrets (only 5 booleans) and exposes no `password`/`is_link_required` fields — those remain exclusively on `public_permissions()`, which is unaffected by this change.

## Operational Impact

- No migration, no schema change, no new configuration. This is a pure application-layer fix.
- No new monitoring/alerting requirements. The change is covered by unit tests (`EffectiveAccessPermission::merge()`, `BaseAlbumImpl::current_user_permissions()`) and a feature-level regression test (`MultiGroupPermissionMergeTest`) that reproduces the exact bug report end-to-end, plus a query-count-guard test that fails if a future change reintroduces an extra query.
- No performance impact: query count is unchanged (verified); the in-memory merge operates over a small, bounded collection (the July 2026 `deduplicate_and_constrain_access_permissions` migration guarantees at most one row per `(album, user)` pair and per `(album, group)` pair, so the merge combines at most 1 direct-user row + N group rows, where N is the number of groups the user belongs to).

## Links

- Related spec sections: [docs/specs/4-architecture/features/048-fix-multi-group-permissions/spec.md](../4-architecture/features/048-fix-multi-group-permissions/spec.md) (FR-048-01, FR-048-02, FR-048-04, NFR-048-01, NFR-048-02, NFR-048-03)
- Related open question (resolved): Q-048-01 in [docs/specs/4-architecture/open-questions.md](../4-architecture/open-questions.md)
- Implementation: [app/DTO/EffectiveAccessPermission.php](../../../app/DTO/EffectiveAccessPermission.php), [app/Models/BaseAlbumImpl.php](../../../app/Models/BaseAlbumImpl.php)
- Precedent for the "most permissive wins" pattern: [app/Policies/AlbumPolicy.php](../../../app/Policies/AlbumPolicy.php) (public+current OR pattern; `canDeleteById`/`canEditById` group-OR-in-SQL)
- Regression tests: [tests/Unit/DTO/EffectiveAccessPermissionTest.php](../../../tests/Unit/DTO/EffectiveAccessPermissionTest.php), [tests/Unit/Models/BaseAlbumImplCurrentUserPermissionsTest.php](../../../tests/Unit/Models/BaseAlbumImplCurrentUserPermissionsTest.php), [tests/Feature_v2/Album/MultiGroupPermissionMergeTest.php](../../../tests/Feature_v2/Album/MultiGroupPermissionMergeTest.php)
- Related prior work: `database/migrations/2026_07_01_120000_deduplicate_and_constrain_access_permissions.php` (merged in `adc58943`, "Fix propagation error"), which guarantees at most one `AccessPermission` row per `(base_album_id, user_id)` pair and per `(base_album_id, user_group_id)` pair.
