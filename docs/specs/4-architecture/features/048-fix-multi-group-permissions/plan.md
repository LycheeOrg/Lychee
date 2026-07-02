# Feature Plan 048 – Fix Multi-Group Permissions

_Linked specification:_ `docs/specs/4-architecture/features/048-fix-multi-group-permissions/spec.md`
_Status:_ Draft
_Last updated:_ 2026-07-01

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A user who belongs to multiple groups (with or without an additional direct per-user share) on the same album always receives the union of every applicable grant, regardless of the order the shares were created in. Success signals:
- The exact bug-report scenario (group "All" View-only created before group "Support_VIP" View+Access+Download) now grants Download.
- Reversing the creation order produces the identical result (order-independence).
- No new SQL query is introduced by the fix (NFR-048-01).
- `php artisan test`, `make phpstan`, and `php-cs-fixer` stay clean.

## Scope Alignment
- **In scope:** new `App\DTO\EffectiveAccessPermission` DTO + its `merge()` factory; `app/Models/BaseAlbumImpl.php::current_user_permissions()`; the `BaseAlbum` extension trait's forwarding method; `@property` docblock updates on `Album`/`TagAlbum`/`PersonAlbum`; unit tests for both the DTO and the model method; a Feature_v2 regression test extending `AlbumSharingTest.php`'s fixtures; an ADR documenting the merge policy.
- **Out of scope:** `public_permissions()` (still returns `App\Models\AccessPermission` — it resolves a genuinely persisted, schema-unique row, not a computed merge), the `access_permissions` DB schema/migrations, `AlbumPolicy::canDelete/canEditById/canDeleteById` (already correct), any REST/UI contract changes, admin UI for configuring merge precedence.

## Dependencies & Interfaces
- **New:** `App\DTO\EffectiveAccessPermission` (`app/DTO/EffectiveAccessPermission.php`) — `final readonly class` with 5 boolean constructor-promoted properties (`grants_full_photo_access`, `grants_download`, `grants_upload`, `grants_edit`, `grants_delete`, all default `false`) and a static `merge(Collection<int,AccessPermission> $permissions): self` factory. Mirrors the existing `App\DTO\CheckoutDTO`/`App\DTO\PixelSizeAssignment` style already used in this codebase. Deliberately **not** a subclass of `App\Models\AccessPermission` and **not** an Eloquent model, so it cannot be mass-assigned, `save()`d, or otherwise persisted (NFR-048-03).
- `App\Models\AccessPermission` (`app/Models/AccessPermission.php`) — unchanged; still the source rows read (never constructed as a synthetic instance) by the merge.
- `App\Constants\AccessPermissionConstants` (`app/Constants/AccessPermissionConstants.php`) — reused only to name the flags consistently in tests/docs; the DTO itself uses plain typed properties, not the string constants (no array-based construction to mass-assign against).
- `App\Models\User::user_groups()` (`app/Models/User.php:277`) — already lazy-loaded relation, unchanged.
- `BaseAlbumImpl::$with` (`app/Models/BaseAlbumImpl.php:215`) — already eager-loads `access_permissions`; no change needed.
- `App\Models\Extensions\BaseAlbum::current_user_permissions()` (`app/Models/Extensions/BaseAlbum.php:118-121`) — pure forwarding method; return type updated to match `BaseAlbumImpl`.
- `@property` docblocks on `App\Models\Album`, `App\Models\TagAlbum`, `App\Models\PersonAlbum` — updated from `AccessPermission|null` to a nullable `EffectiveAccessPermission`.
- Consumers (unchanged code, only benefit from stricter typing): `App\Policies\AlbumPolicy` (`app/Policies/AlbumPolicy.php:134,205,227,264,351`), `App\Http\Controllers\Gallery\PhotoController::index` (`app/Http/Controllers/Gallery/PhotoController.php:349`).
- Test fixtures in `tests/Feature_v2/Album/AlbumSharingTest.php` and `tests/Feature_v2/Base/BaseApiWithDataTest.php` (`group1`, `group2`, `userWithGroup1`, `AccessPermission::factory()->for_user_group()/for_user()`).

## Assumptions & Risks
- **Assumptions:**
  - The `access_permissions` dedup migration (`2026_07_01_120000_deduplicate_and_constrain_access_permissions.php`) is already applied, so at most one row exists per `(album, user)` and per `(album, group)` pair — the merge only ever combines a bounded, small set of rows (1 direct + N groups).
  - No caller of `current_user_permissions()` reads anything off the returned object besides existence (`!== null`) and the 5 boolean grant flags (verified via `grep` across `app/`) — this assumption is now enforced at compile time by PHPStan once the return type changes to `EffectiveAccessPermission`, rather than relying solely on the grep sweep.
- **Risks / Mitigations:**
  - _Risk:_ A hidden caller relies on `id`/`user_id`/`password`/`save()` from the returned value. _Mitigation:_ switching the return type to a DTO that structurally lacks those members turns this risk into a build-time PHPStan/type error instead of a silent runtime bug — strictly safer than the original synthetic-`AccessPermission` approach. T-048-01 still runs the repo-wide grep first as a sanity check before the type change.
  - _Risk:_ Extra boilerplate (constructing a new class, updating 5 call sites' docblocks) increases the diff size for what started as a one-method fix. _Mitigation:_ kept as a single small, focused DTO with no extra behaviour; the wiring change in `BaseAlbumImpl` is a signature/type swap, not a logic rewrite (the merge algorithm itself is unchanged, just relocated into the DTO's static factory).

## Implementation Drift Gate
Before starting I2, re-run the `grep -rn "current_user_permissions()"` sweep across `app/` and `resources/js/` to confirm the caller list in the spec's DO-048-01 note is still exhaustive. Record the command output in this plan's Analysis Gate section. No traceability matrix beyond the Scenario Tracking table below is needed for a fix this small.

## Increment Map

1. **I1 – Unit tests: reproduce the bug and pin the target DTO API**
   - _Goal:_ Add failing tests at two levels: (a) `EffectiveAccessPermission::merge()` pure algorithm tests, and (b) `BaseAlbumImpl::current_user_permissions()` filter/delegate tests — both proving the order-dependent bug (S-048-01/S-048-02) and the direct-user-vs-group merge (S-048-03).
   - _Preconditions:_ None — uses existing `AccessPermission`/`UserGroup`/`User` factories; the DTO class doesn't need to exist yet for its test file to be written (red).
   - _Steps:_
     - Re-run the caller grep from the Drift Gate to confirm scope.
     - Add `tests/Unit/DTO/EffectiveAccessPermissionTest.php`: build plain `Collection<AccessPermission>` instances (via `new AccessPermission([...])`, no DB) and assert `EffectiveAccessPermission::merge()` OR-combines each flag correctly, independent of collection order (S-048-01, S-048-02, S-048-03).
     - Add `tests/Unit/Models/BaseAlbumImplCurrentUserPermissionsTest.php`: inject an in-memory `access_permissions` collection via `setRelation()` and assert `current_user_permissions()` filters correctly and returns `null` when nothing matches (S-048-04 through S-048-07).
     - Confirm both new test files fail to run/compile against the current code (the DTO class and the new filter logic don't exist yet — this is the expected red state).
   - _Commands:_ `php artisan test --filter=EffectiveAccessPermissionTest`, `php artisan test --filter=BaseAlbumImplCurrentUserPermissionsTest`
   - _Exit:_ Both new test files exist and fail for the expected reason (missing class / wrong merge result, not an unrelated error).

2. **I2 – Implement `App\DTO\EffectiveAccessPermission`**
   - _Goal:_ Create the DTO (FR-048-01, NFR-048-03) so the I1 DTO-level tests go green.
   - _Preconditions:_ I1 tests exist and are red.
   - _Steps:_
     - Add `app/DTO/EffectiveAccessPermission.php`: `final readonly class` with 5 boolean constructor-promoted properties (all default `false`) and a static `merge(Collection<int,AccessPermission> $permissions): self` factory using `$permissions->contains(fn ($p) => $p->grants_x)` per flag — no branching, no mutation of the input collection (NFR-048-02).
     - Include the standard SPDX/copyright header used across `app/DTO/*.php`.
   - _Commands:_ `php artisan test --filter=EffectiveAccessPermissionTest`, `make phpstan`
   - _Exit:_ `EffectiveAccessPermissionTest` passes; `BaseAlbumImplCurrentUserPermissionsTest` still red (not wired up yet).

3. **I3 – Wire the DTO into `current_user_permissions()` and update consumer types**
   - _Goal:_ Rewrite `BaseAlbumImpl::current_user_permissions()` (FR-048-01, FR-048-04) to collect every matching row (direct-user + all matching groups) via a single `->filter(...)`, short-circuit to `null` when empty, and delegate to `EffectiveAccessPermission::merge()`; propagate the new nullable return type to the `BaseAlbum` trait and the `@property` docblocks on `Album`/`TagAlbum`/`PersonAlbum`.
   - _Preconditions:_ I2 complete (DTO exists).
   - _Steps:_
     - Replace the two `->first(...)` calls in `BaseAlbumImpl::current_user_permissions()` with one `->filter(...)` + empty-check + `EffectiveAccessPermission::merge($matching)`.
     - Update `App\Models\Extensions\BaseAlbum::current_user_permissions()` return type to match.
     - Update the `@property` docblock in `Album.php`, `TagAlbum.php`, `PersonAlbum.php` from `AccessPermission|null` to the nullable `EffectiveAccessPermission`.
     - Do not touch `public_permissions()`.
   - _Commands:_ `php artisan test --filter=BaseAlbumImplCurrentUserPermissionsTest`, `make phpstan`
   - _Exit:_ Both I1 test files pass (green); `make phpstan` clean (confirms no consumer relied on an `AccessPermission`-only member per FR-048-04's validation path); no other existing test regresses.

4. **I4 – Feature-level regression test (exact bug report reproduction)**
   - _Goal:_ Prove the fix end-to-end through the HTTP/API layer, matching how the bug was actually observed (Download button visibility).
   - _Preconditions:_ I3 merged.
   - _Steps:_
     - Extend `tests/Feature_v2/Album/AlbumSharingTest.php` (or a new `MultiGroupPermissionMergeTest.php` in the same namespace, reusing its `BaseApiWithDataTest` fixtures) with a test that: creates an album, shares it with `group1` (View-only, i.e. all `grants_*` false) then `group2` (`grants_download=true` + others), puts a user in both groups, calls `GET Albums/{album_id}` (or the equivalent album-head endpoint already covered by existing tests) as that user, and asserts `rights.can_download === true`.
     - Add a mirrored test with the two `AccessPermission::factory()` calls swapped in order, asserting the identical result (S-048-02).
   - _Commands:_ `php artisan test --filter=AlbumSharingTest`
   - _Exit:_ Both order variants pass; existing `AlbumSharingTest` tests (listing-once behaviour) still pass unmodified.

5. **I5 – Query-count guard (NFR-048-01)**
   - _Goal:_ Add an explicit assertion that the fix does not introduce a new SQL query.
   - _Preconditions:_ I3/I4 complete.
   - _Steps:_
     - In the Feature test from I4 (or a dedicated unit test), wrap the `current_user_permissions()` call (or the full `GET Albums/{id}` request) with `DB::enableQueryLog()`/`DB::getQueryLog()` before/after, and assert the query count is unchanged from a single-group baseline captured in the same test.
   - _Commands:_ `php artisan test --filter=AlbumSharingTest`
   - _Exit:_ Query-count assertion passes; documents NFR-048-01 compliance directly in the test suite (self-verifying, no manual query-log inspection needed for future changes).

6. **I6 – ADR + documentation**
   - _Goal:_ Record the "most-permissive-wins, order-independent merge, expressed as a non-persistable DTO" as the canonical policy for combining `AccessPermission` sources (Documentation Deliverables in spec.md).
   - _Preconditions:_ I2–I5 green.
   - _Steps:_
     - Add `docs/specs/6-decisions/ADR-0004-multi-group-permission-merge.md` using the ADR template, referencing FR-048-01/FR-048-04/NFR-048-01/NFR-048-03/Q-048-01 and the precedent citations already gathered in spec.md's Appendix, including the rationale for introducing `EffectiveAccessPermission` as a DTO instead of a synthetic `AccessPermission` instance.
     - Update `docs/specs/4-architecture/roadmap.md` Active Features table with Feature 048.
     - Update `docs/specs/_current-session.md`.
   - _Commands:_ None (docs only).
   - _Exit:_ ADR merged; roadmap and session snapshot reflect Feature 048.

7. **I7 – Quality gates**
   - _Goal:_ Full PHP quality gate per AGENTS.md (PHP-only change; no `.vue`/`.ts`/`.js`/`.css` touched).
   - _Preconditions:_ I1–I6 complete.
   - _Steps:_ Run the PHP quality gate.
   - _Commands:_
     - `vendor/bin/php-cs-fixer fix`
     - `php artisan test`
     - `make phpstan`
   - _Exit:_ All three commands clean; ready for commit per AGENTS.md commit protocol.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-048-01 | I1 / T-048-01, I2 / T-048-03, I3 / T-048-05, I4 / T-048-07 | Core bug reproduction, DTO + model unit level + feature level. |
| S-048-02 | I1 / T-048-01, I4 / T-048-08 | Order-reversal proves order-independence. |
| S-048-03 | I1 / T-048-01 | Direct-user + group merge (Q-048-01 Option A). |
| S-048-04 | I1 / T-048-02 | Single-group-only regression. |
| S-048-05 | I1 / T-048-02 | Direct-share-only regression. |
| S-048-06 | I1 / T-048-02 | No-access-returns-null regression. |
| S-048-07 | I1 / T-048-02 | Guest-returns-null regression. |
| S-048-08 | Covered by existing `AlbumPolicy` owner short-circuit; no new test needed. | Owner path bypasses `current_user_permissions()` entirely. |

## Analysis Gate
**Reviewed:** 2026-07-01, by the authoring agent (spec/plan/tasks drafted in the same session), per [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md). Re-reviewed 2026-07-01 after adopting the `EffectiveAccessPermission` DTO (user follow-up request) in place of a synthetic `AccessPermission` instance.

1. **Specification completeness** — ✅ FR/NFR tables populated (FR-048-01…04, NFR-048-01…03). ✅ Q-048-01 resolved directly in spec.md (Non-Goals + Appendix precedent). ✅ No UI mock-up needed — spec Overview states this is a backend-only fix with no REST/UI contract change (existing `AlbumRightsResource.can_download` field is unaffected in shape, only in value); the new DTO is an internal type, never serialized to a resource.
2. **Open questions review** — ✅ No `Open` entries remain for Feature 048 in open-questions.md (Q-048-01 resolved 2026-07-01). ✅ Architecturally significant (security-relevant merge policy + non-persistable-by-design DTO) → ADR-0004 planned in I6/T-048-10, referenced from spec.md Documentation Deliverables.
3. **Plan alignment** — ✅ This plan references `spec.md` and `tasks.md` in the same directory; dependencies/success criteria match spec wording (order-independence, zero new queries, non-persistable DTO).
4. **Tasks coverage** — ✅ FR-048-01 → T-048-01/02/03/04/05/07; FR-048-02 → T-048-05/08; FR-048-03 → T-048-02/09; FR-048-04 → T-048-05/06; NFR-048-01 → T-048-08; NFR-048-02 → T-048-04; NFR-048-03 → T-048-04. ✅ Tests (T-048-01/02) precede implementation (T-048-03/04/05). ✅ Success (merge correctness), validation (regression on existing single-source scenarios), and failure (null-return for no-access/guest) branches all enumerated in S-048-01…08 with tests queued in T-048-01/02/07 before T-048-03/04/05 implementation.
5. **Constitution compliance** — ✅ No new dependencies (DTO uses only existing `Illuminate\Support\Collection`). ✅ I2/T-048-03 and I3/T-048-05 keep both the DTO and `current_user_permissions()` straight-line (NFR-048-02). ✅ No existing ADRs reference Feature 048 module boundaries yet (first ADR for this area is the one this feature adds).
6. **Tooling readiness** — ✅ Commands documented per increment/task (`php artisan test --filter=...`, `make phpstan`, `vendor/bin/php-cs-fixer fix`).

**Outcome:** Pass — no blocking findings. Cleared to begin I1.

## Exit Criteria
- All Increment Map items (I1–I7) complete and checked off in `tasks.md`.
- `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix` all clean.
- ADR-0004 merged and linked from this spec/plan.
- Roadmap and `_current-session.md` updated.
- Q-048-01 remains resolved with no new high/medium open questions outstanding for Feature 048.

## Follow-ups / Backlog
- Consider whether `Propagate.php` (the sharing-propagation action touched by `adc58943`) should reuse the same merge helper if it ever needs to compute an "effective" permission rather than per-row propagation — out of scope for this fix, noted for future reference only.
