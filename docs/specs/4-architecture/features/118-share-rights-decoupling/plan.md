# Feature Plan 118 – Share Rights Decoupling

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented  
_Last updated:_ 2026-11-25

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
An admin gives each user upload, share and edit rights independently, with upload implying the other two (ADR-118-01). The setup of discussion #4982 works: users whose NAS folders are imported for them share and, if allowed, organise their albums, import from their `server_path`, and cannot upload through Lychee nor grant upload to each other.

Success: every FR-118-xx has a passing test or a manual check (S-118-14, S-118-21); users with `may_upload` see no change (NFR-118-01); phpstan, php-cs-fixer, `vp check`, typecheck green.

## Scope Alignment
- **In scope:** FR-118-01 … 22, NFR-118-01 … 05.
- **Out of scope:** the spec's Non-Goals (folder mapping, forced symbolic links, NAS file browser, grant semantics for recipients, `lychee:update_user` options).

## Dependencies & Interfaces
- `AlbumPolicy`, `PhotoPolicy`, `UserPolicy`, `UserGroupPolicy`, `TagPolicy`, `AlbumQueryPolicy`, `AlbumListController`, `SyncUserEligibility`.
- Rights resources: `RootAlbumRightsResource`, `AlbumRightsResource`; `UserManagementResource`.
- Requests: sharing (`Add`, `Edit`), `SetAlbumProtectionPolicyRequest`, `ImportFromServerRequest`, user management (`AddUserRequest`, `SetUserSettingsRequest`), renamer rule requests.
- Actions: `User\Create`, `User\Save`, `ProvisionLdapUser`, `Oauth`, `ProfileController::register()`, `CreateUser` command.
- Test fixture `BaseApiWithDataTest` (`userMayUpload1/2`, `userNoUpload` owns `album3`, `userLocked`), `UserFactory`.
- Frontend: v7/v8 user forms and lists, left menu, `AlbumPanel`/`AlbumEdit` drawers, header settings buttons, share forms (`CreateSharing`, `ShareLine`, `BulkSharingModal`), `AlbumVisibility`; `lychee.d.ts` via `php artisan typescript:transform`.
- ADR-118-01, ADR-115-01, ADR-0011 (Feature 072 grants).

## Assumptions & Risks
- **Assumptions:** `AppServiceProvider` scoped bindings reset per request in FrankenPHP worker mode, so the FR-118-16 memo can live in a scoped service (no static state).
- **Risks / Mitigations:**
  - Existing tests where `userNoUpload` acts as an owner (protection policy, upload, single-photo edit, smart albums) change expectations. Mitigation: each change is limited to the NFR-118-01 list and named in the task.
  - `Create::do()` is called positionally (`ProfileController::store()`). Mitigation: new parameters appended last, with defaults.
  - Policy edits touch the Feature 072 batch helpers. Mitigation: swap the flag only, no new branch (NFR-118-02); `EditableConditionParityTest`, `AggregateAuthorizationTest`, `MovePolicyTest`, `DeleteGrantTest` rerun.

## Implementation Drift Gate
2026-11-25, agent. Result: **pass**, with the manual browser checks S-118-14 and S-118-21 outstanding.

| Requirement | Code | Tests |
|-------------|------|-------|
| FR-118-01, 08, 22 | migration `2026_11_25_000001`, `User`, `UserFactory`, `User\Create`, `User\Save`, `ProvisionLdapUser` | `UserRightsMigrationTest`, `UserRightsInvariantTest`, `LdapAuthTest` |
| FR-118-02 … 05 | `AlbumPolicy` (`canShareWithUsers`, `canShareById`, `canChangeProtectionPolicy`, `canTransfer`), `UserPolicy::canList`, `UserGroupPolicy::canList`, `RootAlbumRightsResource` | `ShareRightTest`, `SharingTest`, `AlbumRootConfigV3Test` |
| FR-118-06 | `AlbumState.hasAlbumSettings`, v7/v8 `AlbumPanel`, `AlbumHeader`, `AlbumEdit` | typecheck; manual S-118-14 |
| FR-118-07 | `HeldGrantRule`, `HasHeldGrantsTrait`, sharing and protection requests, `useHeldGrants` + v7/v8 share forms | `HeldGrantRuleTest`, `HeldGrantTest`; manual S-118-21 |
| FR-118-09 … 12 | `AlbumPolicy`, `PhotoPolicy`, `AlbumQueryPolicy`, `AlbumListController`, `TagPolicy`, renamer requests | `EditRightTest`, Feature 072 suites, tag/renamer/album suites |
| FR-118-13 … 15 | `AlbumPolicy::canUpload`, `canImportInto`, `canImportFromServer`, `ImportFromServerRequest`, `SyncUserEligibility` | `UploadRightTest`, `ImportFromServerTest`, `ImportFromServerOptionsTest`, `SyncTest` |
| FR-118-16 | `OwnedPhotoCheck`, `AlbumPolicy::canSee` | `SmartAlbumOwnerVisibilityTest`, `OverridePermissionsTest` |
| FR-118-17 … 21 | `RequestAttribute`, `AddUserRequest`, `SetUserSettingsRequest`, `UserManagementController`, `UserManagementResource`, `ProfileController`, `Oauth`, `CreateUser`, migration `2026_11_25_000002`, lang files | `UserRightsManagementTest`, user management suites, `CreateUserCommandTest`, `LangTest`, `ConfigTranslationKeysTest` |

Findings (all low impact, fixed in place and recorded in the spec):
- FR-118-05: the root right reads `may_share` alone, no album count (Feature 062 zero-query rule).
- FR-118-06: v8 drawer rendered nothing for a single non-visibility section; now laid out from one section. v7 "About" tab gated and first tab chosen.
- FR-118-12: `TestRenamerRequest` also checked `may_upload` and was missed by the first inventory; now `may_edit`.
- FR-118-18: translation keys `share_rights`/`content_rights` (the `edit_rights` key already names the own-settings right).
- Existing tests that encoded the old rules were updated where the spec says behaviour changes: `ImportFromServerOptionsTest::testNoUploadRightAllowed` (was `…Forbidden`, S-115-02), `SyncTest::testNoPathUserWithoutUploadRight` (S-115-37), `OverridePermissionsTest` (viewer without photos). User-management payloads gained the two required fields.
- Pre-existing failures unrelated to this feature (fail without its code): `AlbumRootV3Test` ×2, `TimelineAlbumTest` ×1, `PhotoBucketsV3Test` ×1, year-boundary buckets (likely local timezone).

Commands rerun: the 30 scoped test classes in the final run (357 tests green), `make phpstan`, `vendor/bin/php-cs-fixer fix`, `vp check --fix <changed paths>`, `vp run typecheck`.

## Increment Map

1. **I1 – Schema, settings, invariant** (FR-118-01, 08, 19 migration part, 20, 22; NFR-118-05)
   - _Steps:_ migration test and Create/Save invariant test (red); migration adding `may_share`, `may_edit` with public `backfill()`; settings migration copying `grant_new_user_upload_rights`; `User` casts and `@property`; `UserFactory` states; `Create`/`Save` trailing parameters and invariant; LDAP.
   - _Commands:_ `php artisan test --filter='UserRightsMigrationTest|UserRightsInvariantTest'`, `make phpstan`.
   - _Exit:_ columns present, backfill and invariant tested.
2. **I2 – Share right in the policies** (FR-118-02 … 05; S-118-02 … 05, 16)
   - _Steps:_ `ShareRightTest` (red); policies; `RootAlbumRightsResource::$can_share_with_users`; update `userNoUpload` protection-policy expectations if any.
   - _Commands:_ `php artisan test --filter='ShareRightTest|SharingTest|UsersTest|UserGroupTest'`.
3. **I3 – Held grants** (FR-118-07; S-118-17, 18)
   - _Steps:_ `HeldGrantTest` (red); `HeldGrantRule`; rules in `AddSharingRequest`, `EditSharingRequest`, `SetAlbumProtectionPolicyRequest`.
   - _Commands:_ `php artisan test --filter='HeldGrantTest|SharingTest'`.
4. **I4 – Edit right in the policies** (FR-118-09 … 12; S-118-06, 07, 19)
   - _Steps:_ `EditRightTest` (red); `AlbumPolicy`, `PhotoPolicy`, `AlbumQueryPolicy`, `AlbumListController`, `TagPolicy`, renamer requests.
   - _Commands:_ `php artisan test --filter='EditRightTest|EditableConditionParityTest|AggregateAuthorizationTest|MovePolicyTest|DeleteGrantTest'` plus the album, photo, tag and renamer classes in tasks.md.
5. **I5 – Upload and import** (FR-118-13 … 15; S-118-08 … 10)
   - _Steps:_ `UploadRightTest` and import cases (red); `canUpload()`, `canImportFromServer()`, `CAN_IMPORT_INTO`, `ImportFromServerRequest`, `SyncUserEligibility`.
   - _Commands:_ `php artisan test --filter='UploadRightTest|ImportFromServerTest|ImportFromServerBrowseTest|ImportFromServerOptionsTest|SyncTest|PhotoAddTest'`.
6. **I6 – Smart albums** (FR-118-16, NFR-118-03; S-118-11)
   - _Steps:_ `SmartAlbumOwnerVisibilityTest` (red); scoped `OwnedPhotoCheck` service; `canSee()`.
   - _Commands:_ `php artisan test --filter='SmartAlbumOwnerVisibilityTest|SmartAlbums'`.
7. **I7 – User management API, accounts, CLI** (FR-118-17, 19, 21; S-118-12, 13, 20)
   - _Steps:_ tests (red); `RequestAttribute`, requests, controller, resource; registration and OAuth; `CreateUser` options; setting descriptions in 23 languages.
   - _Commands:_ `php artisan test --filter='UserManagement|CreateUserCommandTest|OauthTest|RegistrationTest|LangTest'`.
8. **I8 – Frontend v8 and v7** (FR-118-05, 06, 07 UI, 18; S-118-14, 21)
   - _Steps:_ `php artisan typescript:transform`; services and types; user forms and lists; left menu; drawers and header buttons; grant checkboxes; `users.php` labels in 23 languages.
   - _Commands:_ `vp check --fix`, `vp run typecheck`, `php artisan test --filter=LangTest`.
9. **I9 – Docs and drift gate**
   - _Steps:_ knowledge map, roadmap, drift gate report.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-118-01 | I1 / T-118-01, 02 | `UserRightsMigrationTest` |
| S-118-02 … 05 | I2 / T-118-04, 05 | `ShareRightTest` |
| S-118-06, 07 | I4 / T-118-09 … 12 | `EditRightTest` |
| S-118-08 … 10 | I5 / T-118-13, 14 | `UploadRightTest`, import tests, `SyncTest` |
| S-118-11 | I6 / T-118-15, 16 | `SmartAlbumOwnerVisibilityTest` |
| S-118-12, 13 | I7 / T-118-17 … 19 | user management, CLI, OAuth, registration tests |
| S-118-14 | I8 / T-118-21, 22 | manual |
| S-118-15 | all | existing suites |
| S-118-16 | I2 / T-118-04, 05 | `ShareRightTest` |
| S-118-17, 18 | I3 / T-118-06, 07 | `HeldGrantTest` |
| S-118-19 | I4 / T-118-09, 10 | `EditRightTest` |
| S-118-20 | I1 / T-118-01, 02 | `UserRightsInvariantTest` |
| S-118-21 | I8 / T-118-21, 22 | manual |

## Analysis Gate
2026-11-25, agent. Result: **pass**.

1. Specification completeness: pass. Goals, FR-118-01 … 22, NFR-118-01 … 05 populated; Q-118-01 … 08 folded into normative rows; ASCII mock-ups for the user form, users table and settings drawer.
2. Open questions: pass. No `Open` row in open-questions.md. ADR-118-01 written and linked from the spec, the questions and the ADR index; spec 115 FR-115-03/07 annotated.
3. Plan alignment: pass. Links to spec and tasks; success criteria use the spec's wording.
4. Tasks coverage: pass. FR-118-01, 08, 22 → T-118-02/03; 02 … 05 → T-118-05; 06 → T-118-21/22; 07 → T-118-07, 21, 22; 09, 11 → T-118-10; 10 → T-118-11; 12 → T-118-12; 13 … 15 → T-118-14; 16 → T-118-16; 17 → T-118-03, 18; 18 → T-118-21 … 23; 19 → T-118-03, 19, 20; 20 → T-118-03; 21 → T-118-19. Each increment starts with a red-test task covering success, validation and failure branches.
5. Working agreements: pass. No dependency change. Policy edits swap a flag or add one flat condition (NFR-118-02); the grant check is one rule class. ADR-118-01, ADR-115-01 and ADR-0011 reviewed.
6. Tooling: pass. Commands listed per task.

Note: the task list is long (24 tasks); I4 and I7 are the largest and stay within one session each.

## Exit Criteria
- All tasks `[x]`; every test class in tasks.md green; `make phpstan`, `vendor/bin/php-cs-fixer fix`, `vp check --fix`, `vp run typecheck` green.
- `php artisan typescript:transform` rerun; knowledge map and roadmap updated; drift gate recorded.

## Follow-ups / Backlog
- None yet.

## Intent Log
- 2026-11-24: owner asked to separate functionality tied to `may_upload` (discussion #4982). Inventory by grep over `app/` and `resources/js`; Q-118-01 … 05 logged and answered (B, D, A, A, B).
- 2026-11-25: Q-118-06 … 08 answered (A, A, A); owner directive: `may_upload` implies `may_share` and `may_edit`, enforced on write, no priority logic in checks. Local adjustments: flag named `may_edit`; renamer rules under `may_edit`; grant checkboxes disabled rather than hidden; `lychee:update_user` untouched.
- 2026-11-25 (T-118-05): `AlbumRootConfigV3Test::testRunsNoAlbumOrPhotoQuery` caught the album count in `canShareWithUsers(null)` once it fed the root rights. The no-album branch now reads `may_share` alone; FR-118-05 and S-118-02 updated (low impact: the Sharing page shows an empty list instead of 403 for a sharer without albums).
