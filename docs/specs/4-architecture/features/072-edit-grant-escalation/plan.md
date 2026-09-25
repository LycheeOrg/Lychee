# Feature Plan 072 – Move Grant & Edit-Grant Escalation Fix

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Draft  
_Last updated:_ 2026-09-25

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and ADR-0011 have been updated.

> **Embargo:** GHSA-pw32-v9r5-85hc and GHSA-jp9x-63pp-pv4v are unpublished. Do not push this branch publicly before the release that fixes them.

## Vision & Success Criteria

Move, Copy and Merge get their own share grant, and no grant combination can be escalated into full-photo access, download, ownership or deletion. Success:

- The three advisory reproductions (S-072-01, S-072-08, S-072-11) fail before the change and pass after.
- Every S-072 scenario has a passing test (backend) or a recorded manual check (v8 UI).
- Existing move/merge/copy/sharing tests stay green with fixture-only changes (NFR-072-05).
- `make phpstan` 0 errors, `php-cs-fixer` clean, `npm run check` clean.

## Scope Alignment

- **In scope:** FR-072-01..34 — the `grants_move` column and backfill, `CAN_MOVE` abilities, authorization of the four endpoints with cross-owner guards, sharing API, v2/v3 rights, editability filter for the destination picker, v8 sharing UI, menus and picker.
- **Out of scope:** spec Non-Goals NG1–NG9. In particular: no v7 UI change (NG4), no source-aware picker (NG8), no clean-up of existing links (NG3).

## Dependencies & Interfaces

| Dependency | Use |
|------------|-----|
| `AlbumPolicy`, `PhotoPolicy` | New `CAN_MOVE`; reuse `CAN_DELETE`, `CAN_TRANSFER`, `CAN_EDIT`, `CAN_ACCESS_FULL_PHOTO`, `CAN_DOWNLOAD`. |
| `AlbumQueryPolicy::getComputedAccessPermissionSubQuery()` / `joinSubComputedAccessPermissions()` | Must carry `grants_move`; basis of the editability SQL helper. |
| `EffectiveAccessPermission` (ADR-0004 multi-group merge) | OR-merge of `grants_move` across user/group rows. |
| `GrantsAlbumRights` trait, `QueryRightsForAlbum`, `AlbumRootController::rights`, search rights | v3 per-child `grants_move[]`. |
| `ListAlbums`, `AlbumListController` | Editability filter (v2) and `can_edits` column (v3). |
| Sharing: `AddSharingRequest`, `EditSharingRequest`, `Share`, `Propagate`, `SharingController`, `AccessPermissionResource`, v3 `AlbumAccessPermissionResource`, `AlbumAccessPermissionListController`, `DiagnosticsController` | Carry the new grant (every current `grants_delete` touchpoint). |
| Test base `BaseApiWithDataTest` | Fixture `perm1`/`perm11` grant everything; add `grants_move()` there. |

## Assumptions & Risks

**Assumptions**
- A1 — Subtree ownership is uniform (verified: `Transfer` re-roots, `Move`/`Merge` call `fixOwnershipOfChildren()`), so FR-072-14's guard on the source covers descendants.
- A2 — The admin bypass reaches these requests. `AlbumPolicy`/`PhotoPolicy` inherit a `before()` hook; confirm in T-072-00.
- A3 — The `/api/v3/Albums` cache key already includes the user id (verified: `albumListingV3Key($user?->id, …)`), so `can_edits` needs no key change.

**Risks / Mitigations**
- R1 — **Missed `grants_*` touchpoint** (a resource or action that copies grants field by field). *Mitigation:* T-072-11 greps every `grants_delete` occurrence (18 files today) and handles each; S-072-20 covers the sharing round-trip.
- R2 — **Fixture fallout.** Tests that move/merge/copy in shared albums relied on edit. *Mitigation:* add `grants_move()` to the full-grant fixtures only; any assertion change beyond fixtures is logged as a finding (NFR-072-05).
- R3 — **SQL editability helper drifting from `AlbumPolicy::canEdit()`.** *Mitigation:* parity test S-072-19 over owner (with/without `may_upload`), user grant, group grant, public grant.
- R4 — **Behavioural change in UI gating** (`can_move` moves from delete to move grant; see the Behavioural Change Register). *Mitigation:* documented in the spec; release notes must mention it.
- R5 — **No browser available** in the authoring environment. *Mitigation:* frontend verified with `npm run check`; manual browser checks listed explicitly in T-072-19 and not marked done without a real run.

## Implementation Drift Gate

Once all tasks are `[x]`: map each FR to its test/class in a table appended here, re-run every scoped test filter listed in tasks.md sequentially (never concurrently — shared SQLite), and log any divergence in open-questions.md.

## Increment Map

1. **I1 – Persistence** (FR-072-01, 02, 06)
   - Migration: add `grants_move` (bool, default false) and backfill `grants_move = grants_edit`. No migration test.
   - `APC::GRANTS_MOVE`, `RequestAttribute::GRANTS_MOVE_ATTRIBUTE`, `AccessPermission` cast/fillable, factory `grants_move()`, `withGrantFullPermissionsToUser()` true, `ofPublic()`/`ofPublicHidden()` false.
   - `computed_access_permissions` sub-query and `EffectiveAccessPermission` carry it.
   - Fixture: `BaseApiWithDataTest` full-grant perms gain `grants_move()`.
   - _Commands:_ `php artisan test --filter=AlbumQueryPolicyTest`, `make phpstan`.
2. **I2 – Policies** (FR-072-03, 04)
   - Tests first: `CAN_MOVE` for owner ± `may_upload`, user grant, group grant, public (always false), edit-only (false); photo reduction over albums.
   - Implement `AlbumPolicy::canMove()`, `PhotoPolicy::canMove()`.
   - _Commands:_ `php artisan test --filter=MovePolicyTest`.
3. **I3 – `Album::move`** (FR-072-13, 14, 17, 18; S-072-08, 09, 10, 16)
   - Failing tests first (advisory reproduction included).
   - Dedicated authorization: `CAN_EDIT` on target, `CAN_MOVE` on each source, cross-owner guard helper (`sourceNeedsTransfer(Album $source, ?Album $target): bool`).
   - _Commands:_ `--filter=AlbumMoveGrantTest`, `--filter=AlbumMoveTest`.
4. **I4 – `Album::merge`** (FR-072-15, 16, 17, 18; S-072-11..14)
   - Failing tests first; reuse I3's guard helper; add `CAN_DELETE`.
   - _Commands:_ `--filter=AlbumMergeGrantTest`, `--filter=AlbumMergeTest`, `--filter=MergeAlbumRequestTest`, `--filter=MultiGroupPermissionMergeTest`.
5. **I5 – `Photo::copy` / `Photo::move`** (FR-072-10, 11, 12, 17, 18; S-072-01..07, 15, 22..24)
   - Failing tests first (advisory reproduction with `secure_image_link_enabled`).
   - Guard helper: `photoNeedsFullAccess(Photo $photo, ?Album $target, User $user): bool` (false for owner, root target, or same owner).
   - _Commands:_ `--filter=PhotoMoveGrantTest`, `--filter=PhotoCopyTest`, `--filter=PhotoMoveTest`, `--filter=MoveOrDuplicateTest`.
6. **I6 – Sharing API** (FR-072-05; S-072-20)
   - Tests first: create with/without `grants_move`, edit without it keeps value, propagate copies it, 422 on non-boolean.
   - Update every `grants_delete` touchpoint (R1).
   - _Commands:_ `--filter=SharingTest`, `--filter=AlbumSharingTest`, `--filter=GrantsMoveSharingTest`.
7. **I7 – Rights resources** (FR-072-20; S-072-21)
   - Tests first on v2 `AlbumRightsResource` (`can_move`, `can_merge`) and v3 `/rights` (`grants_move[]`, no `can_move_children`).
   - Update `GrantsAlbumRights`, `QueryRightsForAlbum`, `AlbumRootController::rights`, matching/search rights.
   - _Commands:_ `--filter=MoveGrantRightsTest`, plus existing v3 rights test classes.
8. **I8 – Editability SQL helper + v2 target list** (FR-072-30, 31, 32; S-072-17, 19)
   - Parity test first (S-072-19), then `getTargetListAlbums` filter test.
   - `AlbumQueryPolicy::appendEditableCondition()`; apply in `ListAlbums`.
   - _Commands:_ `--filter=EditableConditionParityTest`, `--filter=TargetListAlbumsTest`.
9. **I9 – v3 `can_edits`** (FR-072-33; S-072-17, NFR-072-04)
   - Test first: column present, values match, query count unchanged.
   - _Commands:_ `--filter=AlbumListV3Test`.
10. **I10 – v8 sharing UI** (FR-072-22)
    - Regenerate TS types; Move checkbox in `ShareLine.vue`, `BulkSharingModal.vue`, `AlbumCreateShareDialog.vue`, `Sharing.vue` header; translation keys in `lang/<locale>/*.php` for every locale, then `php artisan lang:json`.
    - _Commands:_ `npm run format`, `npm run check`, `--filter=LangTest`.
11. **I11 – v8 rights, menus, picker** (FR-072-21, 30, 33, 34)
    - `adaptAlbumChildTile` (`can_move`, `can_merge` from `grants_move[i]`), `adaptCategoryTile` (`can_merge=false`), context menus (album Move/Merge, photo Move/Copy), `AlbumListState` stores `can_edits`, `SearchTargetAlbum.vue` v3 path filters on it.
    - _Commands:_ `npm run format`, `npm run check`.
12. **I12 – Docs & gates**
    - knowledge-map, `api-design.md`, roadmap, `_current-session.md`; full scoped quality gate; drift gate report.

## Scenario Tracking

| Scenario ID | Increment / Task | Notes |
|-------------|------------------|-------|
| S-072-01, 02, 03, 04, 05, 06, 07, 15, 22, 23, 24 | I5 / T-072-08, T-072-09 | 01/02 are the GHSA-pw32 reproduction |
| S-072-08, 09, 10 | I3 / T-072-04, T-072-05 | 08 is the GHSA-jp9x move reproduction |
| S-072-11, 12, 13, 14 | I4 / T-072-06, T-072-07 | 11 is the GHSA-jp9x merge reproduction |
| S-072-16 | I3–I5 | admin case in each test class |
| S-072-17 | I8 / T-072-14, I9 / T-072-16 | v2 and v3 |
| S-072-18 | I5 / T-072-08, I11 / T-072-20 | action 403 (backend); picker listing AA (manual) |
| S-072-19 | I8 / T-072-14 | parity |
| S-072-20 | I6 / T-072-10 | |
| S-072-21 | I7 / T-072-12 | |

## Analysis Gate

Run 2026-09-25 (agent self-review).

1. Specification completeness — ✅ FR/NFR populated; all Q-072 answers encoded (FR-072-02, 12, 17, 30..34, NG7..NG9); ASCII mock-ups present.
2. Open questions — ✅ no `Open` Q-072 rows; ADR-0011 written and linked.
3. Plan alignment — ✅ links correct; success criteria match spec.
4. Tasks coverage — ✅ every FR maps to a task (see tasks.md); tests precede implementation in every increment; each increment ≤90 min.
5. Constitution — ✅ spec-first, tests-first, no new dependencies; guards extracted into two pure helpers (NFR-072-03). ADR-0004 (multi-group merge) reviewed: `grants_move` must follow its OR-merge.
6. Tooling — ✅ commands listed per increment and task.

Findings addressed before implementation:
- FR-072-34 originally said the dialog stays open after a 403. The four dialogs close on confirm and rely on the global error toast; the spec was adjusted to keep that behaviour (low impact, no UI rework).
- FR-072-20 extended to the v3 rights tier: `can_move_children` (a copy of `can_delete_children`) is replaced by per-child `grants_move[]`.

## Exit Criteria

- All tasks `[x]` except manual browser checks, which stay open until actually performed.
- Scoped test filters listed in tasks.md green (run sequentially); `make phpstan` 0 errors; `php-cs-fixer` clean; `npm run check` clean.
- Roadmap, knowledge-map, `api-design.md` updated; drift gate report appended.

## Follow-ups / Backlog

- Advisory housekeeping on GitHub: affected range `<= 7.9.0`, GHSA-jp9x package name, add `Photo::move`/`Album::merge` to GHSA-pw32, set patched version.
- Release notes: `can_move` now follows the move grant, not delete (R4); existing shares get `grants_move = grants_edit`.
- v7 sharing UI does not show the grant (NG4); reconsider if v7 is kept longer than planned.

## Intent Log

- 2026-09-25: triage of both advisories → `vuln.md` (repo root, untracked, embargoed) → spec drafted with Option 3 (guards only) → owner asked for a separate move grant and a filtered picker → spec reworked; Q-072-01..08 answered (02/03 superseded) → ADR-0011 → plan and tasks.
