# Feature 093 Tasks – Face Quick Naming

_Status: Ready_  
_Last updated: 2026-10-14_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-093-01 – Setting `ai_vision_face_queue_limit` migration and translations (F-093-02, NFR-093-06).  
  _Intent:_ Add the `BaseConfigMigration` and title/details in all `lang/*/all_settings.php`.  
  _Verification commands:_  
  - `php artisan test --filter=ConfigIntegrity`  
  - `vendor/bin/php-cs-fixer fix`

- [x] T-093-02 – Failing tests for `GET /api/v3/People` (F-093-03, NFR-093-05, NFR-093-07).  
  _Intent:_ `tests/AssistedVision/People/PeopleListV3Test.php` covering:  
  - flag off 403, face recognition off 403, `privacy-preserving` user 403, guest allowed in `public` mode (mirrors v2)  
  - admin sees all  
  - user sees searchable persons plus their own  
  - ordered by name  
  - null crop without a representative face  
  - one query (`DB::listen`)  
  _Verification commands:_ `php artisan test --filter=PeopleListV3Test` (red).

- [x] T-093-03 – Implement `GET /api/v3/People` (F-093-03).  
  _Intent:_ `GetPeopleListRequest`, `PeopleListResource`, `PeopleListController`, route, `php artisan typescript:transform`.  
  _Verification commands:_  
  - `php artisan test --filter=PeopleListV3Test`  
  - `make phpstan`

- [x] T-093-04 – Failing tests for `GET /api/v3/Faces/queue` (F-093-02, F-093-12, S-093-10, S-093-11, S-093-17).  
  _Intent:_ `tests/AssistedVision/Face/FaceQueueTest.php` covering:  
  - guest 401, flag off 403, `restricted` user 403  
  - assigned/dismissed excluded  
  - ordering (largest cluster first, confidence desc, unclustered last)  
  - `cluster_label` filter, invalid label 422  
  - own-photos-only for users, admin sees all  
  - cap + `is_truncated`  
  - suggestions: highest confidence, only assigned and visible persons, non-searchable hidden from a regular user, null when none  
  - no `album_ids` in the response  
  - one query  
  _Verification commands:_ `php artisan test --filter=FaceQueueTest` (red).

- [x] T-093-05 – Implement `GET /api/v3/Faces/queue` (F-093-02, F-093-12).  
  _Intent:_ `GetFaceQueueRequest`, `QueryFaceQueue` (ordering, visibility and suggestion-subquery helpers), `FaceQueueResource`, `FaceQueueController`, route, TS types.  
  _Verification commands:_  
  - `php artisan test --filter=FaceQueueTest`  
  - `make phpstan`

- [ ] T-093-06 – Face-scoped image route, tests first (F-093-01, S-093-16; ADR-093-01).  
  _Intent:_ `FacePhotoAssetTest` (owner/admin 200, other user 403, guest 401, flag off 403, unknown face 404, variant fallback, MAC when temporary links are on), then `GetFacePhotoAssetRequest` + `FacePhotoAssetController` reusing the `PhotoAssetController` serving logic through an extracted helper.  
  _Verification commands:_  
  - `php artisan test --filter=FacePhotoAssetTest`  
  - `php artisan test --filter=PhotoAssetTest`

- [ ] T-093-07 – Global right `modules.can_quick_name_faces`, tests first (F-093-07, S-093-08).  
  _Intent:_ `FaceQuickNamingRightsTest` across the four modes and the flags, then the field in `ModulesRightsResource`, TS types.  
  _Verification commands:_ `php artisan test --filter=FaceQuickNamingRightsTest`.

- [ ] T-093-08 – Frontend services, adapters and route (F-093-02, F-093-03).  
  _Intent:_ `face-queue-service.ts`, SoA adapters, `quick-naming` path and route.  
  _Verification commands:_ `npm run check`.

- [ ] T-093-09 – Pure transition helpers (F-093-03…06, F-093-09).  
  _Intent:_ `rankMatches` (exact, prefix, substring; max 8; homonyms ordered suggestion first then face count; create entry always last, "Create another" when the name exists), `resolveSuggestion` (same-cluster override), `nextIndex`, `applyResult`, handled-id dedupe.  
  _Verification commands:_ `npm run check`.

- [ ] T-093-10 – `useQuickNamingSession` composable (F-093-02…06, F-093-09, F-093-11…14, S-093-01, S-093-02, S-093-04…06, S-093-09, S-093-11, S-093-14, S-093-17, S-093-19).  
  _Intent:_ queue, cursor, history, optimistic actions with failed-face marking and a "Go back" toast, explicit `is_dismissed` values, per-face request serialisation and pending-person reuse (FR-093-13), back target (FR-093-14), refetch on truncation, un-dismiss before assign, people-list reload after dismissing a named face, same-cluster suggestion override.  
  _Verification commands:_ `npm run check`.

- [ ] T-093-11 – `QuickNamingInput.vue` and keyboard rules (F-093-03, F-093-04, F-093-06, F-093-08, F-093-12, S-093-03, S-093-12, S-093-13, S-093-17, S-093-18, S-093-20).  
  _Intent:_ ranked list with "Create «text»" entry, highlight, Enter on the highlighted entry, Shift+Enter always creates a new person, rows show crop + face count, greyed-out suggestion accepted by Enter on an empty box, arrows when the box is empty or holds the unchanged pre-filled name, Backspace clears an unchanged pre-filled box in one press, Backspace/Space discard on an already-empty box, ignore `repeat`/IME, RTL mirroring, focus restore.  
  _Verification commands:_ `npm run check`; manual S-093-01…06, 12 in LTR and RTL.

- [ ] T-093-12 – `QuickNaming.vue` view (F-093-01, F-093-10, NFR-093-01, S-093-07, S-093-15).  
  _Intent:_ photo from API-093-05 with the face highlighted, crop, progress, "?" help button at the top right, buttons, done/error/failed/forbidden states, preload of the next 3 images.  
  _Verification commands:_ `npm run check`; manual UI-093-01…07.

- [ ] T-093-13 – Entry buttons (F-093-07, S-093-08, S-093-10).  
  _Intent:_ People page, clusters page, cluster cards (`?cluster=`), gated by `can_quick_name_faces`.  
  _Verification commands:_ `npm run check`.

- [ ] T-093-14 – View strings in all locales and keybindings help (F-093-10, NFR-093-03, NFR-093-04).  
  _Intent:_ `lang/*/people.php` keys, a quick naming section in `KeybindingsHelp.vue`.  
  _Verification commands:_ `npm run format`, `npm run check`.

- [ ] T-093-15 – Docs and quality gate.  
  _Intent:_ roadmap row, knowledge map, drift gate report, full scoped gate, commit prepared.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `npm run format`  
  - `npm run check`  
  - scoped tests  
  - `make phpstan`

- [ ] T-093-16 – Explicit `is_dismissed` on `PATCH /api/v2/Face/{id}`, tests first (F-093-09, S-093-19).  
  _Intent:_ failing cases in `FaceDismissTest` (`true` keeps dismissed, `false` restores, absent toggles, non-boolean 422), then the optional rule and the set-or-toggle helper.  
  _Verification commands:_  
  - `php artisan test --filter=FaceDismissTest`  
  - `make phpstan`

## Notes / TODOs
- The frontend has no Vue unit-test harness in the repo; S-093-01…12 are verified manually (recorded in the plan's drift gate).
