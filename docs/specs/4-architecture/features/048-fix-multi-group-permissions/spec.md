# Feature 048 – Fix Multi-Group Permissions

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-07-01 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/048-fix-multi-group-permissions/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/048-fix-multi-group-permissions/tasks.md` |
| Roadmap entry | #048 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
`BaseAlbumImpl::current_user_permissions()` (`app/Models/BaseAlbumImpl.php:261-271`) resolves the effective `AccessPermission` for the current user on an album by calling `Collection::first()` twice: once for a direct user-level row, and once — only if the first lookup fails — for a matching user-group row. Because `first()` returns the earliest-matching element of an already in-memory `Collection` (ordered by DB fetch order, effectively insertion/id order), a user who belongs to **two or more groups** with different grants on the same album only ever receives the grants of whichever group's `AccessPermission` row happens to sort first. The other group's grants (e.g. Download) are silently dropped, and simply re-creating the sharing rows in a different order changes the outcome — this is the bug reported against the "All" + "Support_VIP" group scenario.

This is a backend-only authorization fix (modules: `app/Models`, `app/DTO`, consumed by `app/Policies/AlbumPolicy.php`). It has no REST contract or UI changes: the existing `AlbumRightsResource`/`can_download` field (and the other Gate-derived rights) simply start reflecting the correct value once the underlying permission resolution is fixed.

The merged result is returned as a new dedicated DTO, `App\DTO\EffectiveAccessPermission`, rather than a synthetic `App\Models\AccessPermission` Eloquent model instance. An `AccessPermission` model is inherently persistable (mass-assignable, `->save()`-able); a computed, request-scoped "effective grants for this user on this album" snapshot must never be written back to the database, and a plain readonly DTO makes that a type-level guarantee instead of a convention every future caller has to remember. This mirrors the existing `App\DTO\CheckoutDTO`/`App\DTO\PixelSizeAssignment` pattern (`final readonly class` with promoted constructor properties) already used in this codebase for similar read-only value objects.

## Goals
- A user who belongs to multiple groups (and/or has a direct per-user share) on the same album receives the **union** (logical OR) of every applicable grant flag, regardless of the order in which the `AccessPermission` rows were created.
- The fix must not add any new database queries beyond what `current_user_permissions()` already triggers today (the `access_permissions` relation is already eager-loaded via `BaseAlbumImpl::$with`, and `$user->user_groups` is already read by the current implementation).
- The merged/effective permission is represented by a type that cannot be accidentally persisted (no mass assignment, no `save()`), separating "a persistable share record" from "a computed capability snapshot" at the type level.
- Existing single-group / single-share / guest / no-permission behaviour is unchanged.

## Non-Goals
- Changing the DB schema, unique constraints, or the `access_permissions` table (the July 2026 dedup migration already guarantees at most one row per `(base_album_id, user_id)` and per `(base_album_id, user_group_id)` pair — this feature only changes how multiple *distinct* rows for the same user are combined in memory).
- Changing `public_permissions()` — it looks up the single, schema-guaranteed-unique `(user_id IS NULL, user_group_id IS NULL)` row and is not affected by this bug.
- Changing the already-correct SQL-level group aggregation in `AlbumPolicy::canDelete()`, `canEditById()`, `canDeleteById()` (these already `orWhereIn(user_group_id, ...)` across every group and are not affected by this bug).
- Adding any admin UI to manage merge precedence — the merge rule (most-permissive-wins) is fixed and not configurable (see Q-048-01 resolution below).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-048-01 | `BaseAlbumImpl::current_user_permissions()` returns a nullable `App\DTO\EffectiveAccessPermission` (or `null`) whose boolean grant flags (`grants_full_photo_access`, `grants_download`, `grants_upload`, `grants_edit`, `grants_delete`) are the logical OR of every `AccessPermission` row matching the current user — the direct `user_id` row (if any) plus every row whose `user_group_id` is one of the user's group ids. | Any grant flag `true` on **any** matching row makes that flag `true` on the merged result, independent of row insertion order. | If no row matches (no direct share, member of no group with access), the method returns `null` exactly as today. | Guests (`Auth::guest()`) continue to receive `null` with no matching attempted. | None (no new telemetry). | Bug report: user in "All" (View only) + "Support_VIP" (View, Access, Download) only received "All"'s grants; reordering the shares flipped the outcome. |
| FR-048-02 | The merge in FR-048-01 introduces zero additional database queries compared to the current implementation. | `access_permissions` (already eager-loaded per-album via `BaseAlbumImpl::$with`) and `$user->user_groups` (already read by the current code) are the only data sources; the merge itself is an in-memory `Collection` operation. | N/A (implementation constraint, not user input). | N/A. | None. | User requirement: "Propose a solution which is the least heavy on database queries." |
| FR-048-03 | All existing single-source scenarios (single direct share only, single group share only, public-only, owner, guest, no access) resolve to the exact same effective permissions as before this fix. | Regression coverage via existing and new tests (see Test Strategy). | N/A. | N/A. | None. | Backward compatibility — no behaviour change intended outside the multi-row-merge case. |
| FR-048-04 | `current_user_permissions()`'s return type (and every consumer's type expectations) change from a nullable `App\Models\AccessPermission` to a nullable `App\DTO\EffectiveAccessPermission` across `BaseAlbumImpl`, the `BaseAlbum` extension trait, and the `@property` docblocks on `Album`, `TagAlbum`, `PersonAlbum`. | PHPStan level 6 passes with the new type end-to-end; every consumer (`AlbumPolicy`, `PhotoController::index`) continues to compile because they only ever read `!== null` or `?->grants_*` boolean properties, which `EffectiveAccessPermission` also exposes. | A consumer that reads any `AccessPermission`-only member (`id`, `user_id`, `password`, `is_link_required`, `save()`, …) off the return value fails static analysis, surfacing any hidden dependency the spec's caller sweep (T-048-02) might have missed. | N/A. | None. | Follow-up from user request: "create a proper DTO for this to make sure it is not persisted by error." |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-048-01 | Fixing the bug must not add any SQL query to `current_user_permissions()` (query count for a given album+user pair stays identical before/after the fix). | User's explicit constraint: "least heavy on database queries." | Feature/unit test asserts identical query count via `DB::listen()` or `assertQueryCount`-style assertion, or a code-review confirmation that only already-loaded relations (`access_permissions`, `user_groups`) are touched (no `::query()`/`whereIn` added). | `BaseAlbumImpl::$with` continuing to eager-load `access_permissions`; `User::user_groups()` relation. | User directive. |
| NFR-048-02 | The merge algorithm must be a small, pure, order-independent helper (no branching on collection order) so future readers cannot reintroduce order-dependence. | AGENTS.md "Straight-line increments" guidance; this is exactly the bug class being fixed. | Code review / PHPStan level 6 clean. | None. | AGENTS.md coding conventions. |
| NFR-048-03 | The value returned by `current_user_permissions()` must be structurally non-persistable: not an Eloquent model, no mass-assignment/`save()`/`fill()` capability. | Prevent a future accidental `->save()` or mass-assignment on a computed, request-scoped permission snapshot from writing a bogus row into `access_permissions`. | `App\DTO\EffectiveAccessPermission` is a `final readonly class` (plain PHP object, not extending `Illuminate\Database\Eloquent\Model`) — verified by code review / class hierarchy, not by a runtime test. | New `App\DTO\EffectiveAccessPermission` class. | User directive: "create a proper DTO for this to make sure it is not persisted by error." |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-048-01 | User is in two groups sharing the same album with different grants, group with fewer grants created first → user receives the union of both groups' grants (reproduces the bug report; must now pass). |
| S-048-02 | Same as S-048-01 but with insertion order reversed → identical merged result as S-048-01 (order-independence). |
| S-048-03 | User has a direct per-user `AccessPermission` on an album (e.g. `grants_download=false`) **and** belongs to a group with `grants_download=true` on the same album → merged result has `grants_download=true` (Q-048-01, Option A). |
| S-048-04 | User belongs to a single group only (no direct share) → behaviour unchanged from before the fix. |
| S-048-05 | User has a direct share only (no group share) → behaviour unchanged from before the fix. |
| S-048-06 | User belongs to no group and has no direct share, album is not public → `current_user_permissions()` returns `null`, `canAccess` falls through to `public_permissions()`/owner checks exactly as today. |
| S-048-07 | Guest (unauthenticated) → `current_user_permissions()` returns `null` without evaluating any permission row. |
| S-048-08 | Album owner → unaffected; `isOwner()` short-circuits before `current_user_permissions()` is consulted in `AlbumPolicy`. |

## Test Strategy
- **Core (Unit):** Two layers of unit coverage:
  1. Pure DTO tests for `App\DTO\EffectiveAccessPermission::merge()` — construct a plain `Collection<AccessPermission>` in memory (no DB, no Auth faking needed) and assert the OR-merge logic directly (covers S-048-01, S-048-02, S-048-03 at the algorithm level, independent of how `BaseAlbumImpl` builds the input collection).
  2. `BaseAlbumImpl::current_user_permissions()` tests covering the filter/delegate/null-short-circuit logic (S-048-04 through S-048-07), using `setRelation()` to inject an in-memory `access_permissions` collection without touching the DB.
- **Application/REST (Feature):** New Feature_v2 test extending the existing `tests/Feature_v2/Album/AlbumSharingTest.php` pattern (reuses `BaseApiWithDataTest` fixtures: `group1`, `group2`, users, `AccessPermission::factory()`) that reproduces the exact bug report — create the album, share with a low-grant group then a high-grant group (and the reverse order), log in as a user in both groups, and assert `GET Albums/{id}` (or the album head/list response) exposes `rights.can_download === true` in both orders.
- **No CLI, UI (JS/Selenium), or Docs/Contracts impact** — no new routes, REST-facing DTOs, or frontend fields are introduced; `App\DTO\EffectiveAccessPermission` is an internal backend type, not exposed via any resource; the existing `AlbumRightsResource.can_download` field simply reflects the corrected value.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-048-01 | `App\DTO\EffectiveAccessPermission` — new `final readonly class` in `app/DTO/`. Constructor-promoted public properties for the 5 boolean grant flags (`grants_full_photo_access`, `grants_download`, `grants_upload`, `grants_edit`, `grants_delete`), each defaulting to `false`. Static factory `merge(Collection<int,AccessPermission> $permissions): self` builds an instance via boolean OR across the given rows. Not an Eloquent model — no `id`, `password`, `is_link_required`, `save()`, or mass-assignment; deliberately cannot represent (or persist) anything beyond the 5 grant flags. | core (`app/DTO`) |
| DO-048-02 | `current_user_permissions()` signature change: `BaseAlbumImpl::current_user_permissions()` and the matching `BaseAlbum::current_user_permissions()` forwarding method (`app/Models/Extensions/BaseAlbum.php:118-121`) now both return a nullable `EffectiveAccessPermission`; `@property` docblocks on `Album`, `TagAlbum`, `PersonAlbum` updated to `EffectiveAccessPermission` (nullable) accordingly. | core (Models) |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-048-01 | `tests/Feature_v2/Album/AlbumSharingTest.php` fixtures (`group1`, `group2`, `userWithGroup1`, `BaseApiWithDataTest`) | Reused for the new multi-group-merge regression test instead of creating a parallel fixture set. |

## Telemetry & Observability
No new telemetry. No new verbose-trace fields.

## Documentation Deliverables
- Update `docs/specs/6-decisions/` with an ADR recording the "most-permissive-wins, order-independent merge" policy for `current_user_permissions()`, since this establishes the canonical multi-source permission-merge rule for the album authorization module (security-relevant, cross-cutting: `AlbumPolicy` + `PhotoController`).
- Close Q-048-01 in `docs/specs/4-architecture/open-questions.md` (done).

## Fixtures & Sample Data
See FX-048-01 above — no new fixture files are required.

## Spec DSL
```
domain_objects:
  - id: DO-048-01
    name: EffectiveAccessPermission
    path: app/DTO/EffectiveAccessPermission.php
    kind: "final readonly class (plain DTO, not an Eloquent model)"
    fields:
      - name: grants_full_photo_access
        type: boolean
        constraints: "OR of all matching rows; default false"
      - name: grants_download
        type: boolean
        constraints: "OR of all matching rows; default false"
      - name: grants_upload
        type: boolean
        constraints: "OR of all matching rows; default false"
      - name: grants_edit
        type: boolean
        constraints: "OR of all matching rows; default false"
      - name: grants_delete
        type: boolean
        constraints: "OR of all matching rows; default false"
    static_factory: "merge(Collection<int,AccessPermission> $permissions): self"
  - id: DO-048-02
    name: current_user_permissions signature change
    affected:
      - app/Models/BaseAlbumImpl.php
      - app/Models/Extensions/BaseAlbum.php
      - app/Models/Album.php (docblock)
      - app/Models/TagAlbum.php (docblock)
      - app/Models/PersonAlbum.php (docblock)
fixtures:
  - id: FX-048-01
    path: tests/Feature_v2/Album/AlbumSharingTest.php
    purpose: Reused fixtures for multi-group merge regression test
```

## Appendix (Optional)

### Root cause (for implementers)
```php
// app/Models/BaseAlbumImpl.php:261-271 (current, buggy)
public function current_user_permissions(): AccessPermission|null
{
    if (Auth::guest()) {
        return null;
    }
    $user = Auth::user();
    return $this->access_permissions->first(fn (AccessPermission $p) => $p->user_id === $user->id)
        ?? $this->access_permissions->first(fn (AccessPermission $p) => in_array($p->user_group_id, $user->user_groups->map(fn ($g) => $g->id)->all(), true));
}
```
`Collection::first()` short-circuits on the first element satisfying the predicate. When two group rows both satisfy the second predicate, only the first (by collection/DB order) is ever considered — the second group's grants are discarded even if they are strictly more permissive.

### Target design (for implementers)
```php
// app/DTO/EffectiveAccessPermission.php (new)
final readonly class EffectiveAccessPermission
{
    public function __construct(
        public bool $grants_full_photo_access = false,
        public bool $grants_download = false,
        public bool $grants_upload = false,
        public bool $grants_edit = false,
        public bool $grants_delete = false,
    ) {
    }

    /** @param Collection<int,AccessPermission> $permissions */
    public static function merge(Collection $permissions): self
    {
        return new self(
            grants_full_photo_access: $permissions->contains(fn (AccessPermission $p) => $p->grants_full_photo_access),
            grants_download: $permissions->contains(fn (AccessPermission $p) => $p->grants_download),
            grants_upload: $permissions->contains(fn (AccessPermission $p) => $p->grants_upload),
            grants_edit: $permissions->contains(fn (AccessPermission $p) => $p->grants_edit),
            grants_delete: $permissions->contains(fn (AccessPermission $p) => $p->grants_delete),
        );
    }
}

// app/Models/BaseAlbumImpl.php (rewritten)
public function current_user_permissions(): EffectiveAccessPermission|null
{
    if (Auth::guest()) {
        return null;
    }
    $user = Auth::user();
    $group_ids = $user->user_groups->map(fn ($g) => $g->id)->all();
    $matching = $this->access_permissions->filter(
        fn (AccessPermission $p) => $p->user_id === $user->id || in_array($p->user_group_id, $group_ids, true)
    );
    return $matching->isEmpty() ? null : EffectiveAccessPermission::merge($matching);
}
```
This keeps `current_user_permissions()` straight-line (filter → empty-check → delegate) and makes the merge algorithm itself independently unit-testable via `EffectiveAccessPermission::merge()` without any `Auth`/relation setup.

### Precedent for the "most permissive wins" merge rule (used to resolve Q-048-01 without a schema change)
- `app/Policies/AlbumPolicy.php:205,227,264,351` already OR `current_user_permissions()?->grants_*` with `public_permissions()?->grants_*`.
- `app/Policies/AlbumPolicy.php:296-305,392-404,447-459` (`canDelete`, `canEditById`, `canDeleteById`) already OR across every matching group row directly in SQL via `orWhereIn(APC::USER_GROUP_ID, $user->user_groups->pluck('id'))`.
- `tests/Feature_v2/Album/AlbumSharingTest.php:30` docblock: "Expected behavior is that the access for that album via group are taken as max."

### Related prior work
- `database/migrations/2026_07_01_120000_deduplicate_and_constrain_access_permissions.php` (merged 2026-07-01 in `adc58943`, "Fix propagation error") already guarantees at most one `AccessPermission` row per `(base_album_id, user_id)` pair and per `(base_album_id, user_group_id)` pair, so the merge in FR-048-01 operates over a bounded set: at most 1 direct-user row + at most 1 row per group the user belongs to.
