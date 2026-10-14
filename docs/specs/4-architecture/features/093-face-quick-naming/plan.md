# Feature Plan 093 – Face Quick Naming

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented (drift gate passed)  
_Last updated:_ 2026-10-14

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A keyboard-first view where naming a face costs a name and Enter. Success means:
- Every unassigned, non-dismissed face the user may act on is reachable from the view (FR-093-02).
- Name, skip, back, re-name and discard all work without leaving the keyboard (FR-093-03 … FR-093-06).
- The next face shows without waiting for the network (NFR-093-01).
- The two v3 endpoints follow ADR-0009 and ADR-069-01: one `toBase()` SELECT each, verified by query counting (NFR-093-05).

## Scope Alignment
- **In scope:**
  - Setting `ai_vision_face_queue_limit`.
  - Optional explicit `is_dismissed` on `PATCH /api/v2/Face/{id}` (API-093-02).
  - `GET /api/v3/People`, `GET /api/v3/Faces/queue`, `GET /api/v3/Faces/{id}/photo/{variant}` (ADR-093-01).
  - Global right `modules.can_quick_name_faces`.
  - v8 view `/people/quick-naming` with entry buttons on the People page, the clusters page and each cluster card.
  - Translations, keybindings help, roadmap and knowledge map.
- **Out of scope:** v7 frontend, detection or clustering changes, person editing or merging, touch gestures (spec Non-Goals).

## Dependencies & Interfaces
- Feature 030 model (`faces`, `persons`, `FaceController::assign`, `FaceController::toggleDismissed`).
- ADR-0009 (SoA response shape) and ADR-069-01 (bounding strategies). Spec 079 NFRs as the reference v3 implementation (`QueryLiveMetrics`, `LiveMetricsListResource`).
- Policies: `AlbumPolicy::CAN_BATCH_FACE_OPS`, `PhotoPolicy::CAN_ASSIGN_FACE_ON_PHOTO` / `CAN_DISMISS_FACE`, `Person::scopeSearchable`.
- Frontend: `useLtRorRtL()`, `useGlobalRights()`, `FaceOverlay.vue` geometry (fractions 0..1), `KeybindingsHelp.vue`, services pattern of `metrics-service.ts` and adapters such as `adaptFlowTile.ts`.
- Tests: `tests/AssistedVision` suite (face factories `for_photo`, `with_cluster`, `for_person`, `dismissed`). v3 helpers come from `Tests\Feature_v3\Base\BaseApiWithDataTest::getJsonV3()`.

## Assumptions & Risks
- **Assumptions:**
  - Person lists stay small enough for a whole-scope list (ADR-069-01 strategy 1).
  - `FaceResource` returned by `POST /Face/{id}/assign` carries `person_id`, which is how a newly created person reaches the local list.
- **Risks / Mitigations:**
  - *Ordering query portability* (largest cluster first, unclustered last) across SQLite/MySQL/PostgreSQL. Mitigation: join a grouped count subquery and order with `CASE`, in one SELECT; tests run on SQLite locally, CI covers the others.
  - *Extracting the serving logic* from `PhotoAssetController` touches a hot path. Mitigation: `PhotoAssetTest` regression run in I4.
  - *Temporary image links* (MAC) for images loaded through `<img>`: reuse whatever mechanism the existing v3 image tiles use. Verify during I6.
  - *Flag default off* hides the feature (Q-093-08 A: accepted; the flag will be removed later).
  - *Translations*: 1 setting and about 20 view strings into 22 locales. Reuse the batch translation pipeline from the i18n work.

## Implementation Drift Gate
Run after all tasks are checked. Evidence:
- A table mapping FR/NFR to test classes and Vue files.
- Results of `php artisan test --filter=FaceQueueTest`, `--filter=PeopleListV3Test` and `--filter=FaceQuickNamingRightsTest`, plus `make phpstan` and `npm run check`.
- A manual run in LTR and RTL.

The report goes in this file under "Drift Gate Report".

## Increment Map

1. **I1 – Setting and translations**
   - _Goal:_ add DO-093-01.
   - _Steps:_ `BaseConfigMigration` adding `ai_vision_face_queue_limit` (cat `AI Vision`, positive int, default 500, `required_keys` `ai_vision_enabled,ai_vision_face_enabled`); `lang/*/all_settings.php` title and details in every locale.
   - _Commands:_ `php artisan test --filter=ConfigIntegrityTest` (if present), `vendor/bin/php-cs-fixer fix`.
   - _Exit:_ migration runs; key parity across `lang/*`.
2. **I2 – `GET /api/v3/People` (tests first)**
   - _Goal:_ API-093-04.
   - _Steps:_
     - Write failing tests in `tests/AssistedVision/People/PeopleListV3Test.php`: flag off 403, AI Vision off 403, `privacy-preserving` user 403, guest allowed in `public` mode (mirrors v2), admin sees all, user sees searchable + own person, ordered by name, crop url null without representative face, one query.
     - Then add `GetPeopleListRequest`, `PeopleListResource` (`#[TypeScript]`), `PeopleListController` (one `toBase()` SELECT with a left join on the representative face) and the route.
     - Run `php artisan typescript:transform`.
   - _Commands:_ `php artisan test --filter=PeopleListV3Test`, `make phpstan`.
   - _Exit:_ green.
3. **I3 – `GET /api/v3/Faces/queue` (tests first)**
   - _Goal:_ API-093-03, FR-093-02.
   - _Steps:_
     - Write failing tests in `tests/AssistedVision/Face/FaceQueueTest.php`:
       - guest 401, flag off 403, `restricted` mode user 403, admin ok
       - assigned/dismissed excluded
       - ordering: largest cluster first, confidence desc, unclustered last
       - `cluster_label` filter; invalid label 422
       - user sees only own photos, admin all
       - cap with `is_truncated`
       - suggestions: highest confidence, only assigned and visible persons, null when none (FR-093-12)
       - no `album_ids` in the response
       - one query
     - Then add `GetFaceQueueRequest`, `QueryFaceQueue` action (small helpers for ordering and visibility), `FaceQueueResource` and `FaceQueueController`, plus the route.
   - _Commands:_ `php artisan test --filter=FaceQueueTest`, `make phpstan`.
   - _Exit:_ green.
4. **I4 – Face-scoped image route (ADR-093-01)**
   - _Goal:_ serve the size variant for a queued face.
   - _Steps:_ failing tests in `tests/AssistedVision/Face/FacePhotoAssetTest.php` (owner ok, admin ok, other user 403, guest 401, flag off 403, unknown face 404, fallback variant, MAC required when temporary links are on); then a request class + controller reusing the `PhotoAssetController` serving logic (extract a shared helper, no behaviour change for `/Asset`).
   - _Commands:_ `php artisan test --filter=FacePhotoAssetTest`, `php artisan test --filter=PhotoAssetTest` (regression).
   - _Exit:_ green.
4b. **I4b – Explicit dismiss value (tests first)**
   - _Goal:_ API-093-02 idempotent set (FR-093-09).
   - _Steps:_ failing cases in `tests/AssistedVision/Face/FaceDismissTest.php` (`true` keeps dismissed, `false` restores, absent toggles, non-boolean 422); then the optional rule in `ToggleDismissedRequest` and the set-or-toggle branch in `FaceController::toggleDismissed`, extracted into a small helper.
   - _Commands:_ `php artisan test --filter=FaceDismissTest`, `make phpstan`.
   - _Exit:_ green; existing toggle callers unchanged.
5. **I5 – Global right**
   - _Goal:_ `modules.can_quick_name_faces` (FR-093-07).
   - _Steps:_ failing test `tests/AssistedVision/FacePermissions/FaceQuickNamingRightsTest.php` across the four modes and flag states; then the field in `ModulesRightsResource`; regenerate TS types.
   - _Commands:_ `php artisan test --filter=FaceQuickNamingRightsTest`.
   - _Exit:_ green.
6. **I6 – Frontend data layer**
   - _Goal:_ services and adapters.
   - _Steps:_ `face-queue-service.ts` (queue, people), `adaptFaceQueue.ts` / `adaptPeopleList.ts` (SoA → objects), route path `quick-naming` in `paths.ts` and `v8/router/routes.ts`.
   - _Commands:_ `npm run check`.
   - _Exit:_ types compile.
7. **I7 – Session state composable**
   - _Goal:_ `useQuickNamingSession` holds the queue, cursor, history (named / skipped / discarded / failed), refetch with handled-id dedupe, the optimistic actions with failed-face marking and a "Go back" toast (FR-093-09), explicit `is_dismissed` values, the same-cluster suggestion override (FR-093-12) and a people-list reload after dismissing a named face (FR-093-11).
   - _Steps:_ pure helper functions for the transitions (`nextIndex`, `applyResult`, `rankMatches`, `resolveSuggestion`), kept straight-line.
   - _Commands:_ `npm run check`.
   - _Exit:_ compiles; helpers covered by manual scenarios S-093-01…12.
8. **I8 – Name box and keyboard handling**
   - _Goal:_ FR-093-03/04/06/08.
   - _Steps:_
     - `QuickNamingInput.vue`: ranking (exact, prefix, substring; max 8), rows with crop + face count (homonyms), "Create «text»" / "Create another «text»" entry, highlight, Enter on the highlighted entry, Shift+Enter always creates, greyed-out suggestion accepted by Enter on an empty box.
     - Keydown rules: arrows when the box is empty or holds the unchanged pre-filled name; Backspace on an unchanged pre-filled box clears it in one press; Backspace/Space discard on an already empty box; `repeat` and IME ignored; RTL mirroring.
     - Focus restore.
   - _Commands:_ `npm run check`.
   - _Exit:_ manual run of S-093-01…06 and S-093-12.
9. **I9 – View and photo highlight**
   - _Goal:_ `QuickNaming.vue`: photo from API-093-05 with dimmed surroundings and a highlighted box (geometry as in `FaceOverlay.vue`), crop, progress, "?" help button at the top right (FR-093-10), Previous/Discard/Skip buttons, the done/error/failed/forbidden states and image preloading (NFR-093-01).
   - _Commands:_ `npm run check`.
   - _Exit:_ UI-093-01…07 reachable.
10. **I10 – Entry points**
    - _Goal:_ FR-093-07. Buttons on `People.vue`, `FaceClusters.vue` and each cluster card (`?cluster=<label>`), gated by `can_quick_name_faces`.
    - _Commands:_ `npm run check`.
    - _Exit:_ S-093-08, S-093-10.
11. **I11 – Strings and help**
    - _Goal:_ NFR-093-03/04, FR-093-10. Add view strings to `lang/en/people.php` and translate them into every locale; add a quick naming section to `KeybindingsHelp.vue`.
    - _Commands:_ `npm run format`, `npm run check`.
    - _Exit:_ key parity.
12. **I12 – Docs and quality gate**
    - _Goal:_ roadmap row, knowledge map, drift gate.
    - _Commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, the scoped tests above, `make phpstan`.
    - _Exit:_ all green; commit prepared for the operator.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-093-01 | I7, I8 / T-093-10, T-093-11 | Manual |
| S-093-02 | I7, I8 / T-093-10, T-093-11 | Manual; person list update |
| S-093-03 | I8 / T-093-11 | Manual, LTR + RTL |
| S-093-04 | I7, I8 / T-093-10, T-093-11 | Manual |
| S-093-05 | I7, I8 / T-093-10, T-093-11 | Manual |
| S-093-06 | I7 / T-093-10 | Manual |
| S-093-07 | I9 / T-093-12 | Manual |
| S-093-08 | I5, I10 / T-093-07, T-093-13 | `FaceQuickNamingRightsTest` |
| S-093-09 | I7 / T-093-10 | Manual |
| S-093-10 | I3, I10 / T-093-04, T-093-13 | `FaceQueueTest` |
| S-093-11 | I3, I7 / T-093-04, T-093-10 | `FaceQueueTest` (cap) + manual refetch |
| S-093-12 | I8 / T-093-11 | Manual |
| S-093-13 | I8 / T-093-11 | Manual |
| S-093-14 | I7 / T-093-10 | Manual (block the request in devtools) |
| S-093-15 | I9, I11 / T-093-12, T-093-14 | Manual |
| S-093-16 | I4 / T-093-06 | `FacePhotoAssetTest` |
| S-093-17 | I3, I7, I8 / T-093-04, T-093-10, T-093-11 | `FaceQueueTest` (suggestion fields) + manual |
| S-093-18 | I8 / T-093-11 | Manual |
| S-093-19 | I4b, I7 / T-093-16, T-093-10 | `FaceDismissTest` + manual |
| S-093-20 | I7, I8 / T-093-09, T-093-11 | Manual |

## Analysis Gate
Third pass, run on 2026-10-14 by the implementing agent against [analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md). It follows the answers to Q-093-10 … Q-093-12.

1. **Specification completeness:** Pass.
   - FR-093-01 … FR-093-14 and NFR-093-01 … NFR-093-07 are populated.
   - All twelve answers are folded into the normative sections.
   - The mock-ups cover the suggestion, the create entry and the help button.
2. **Open questions review:** Pass.
   - No open entries remain (Q-093-13 on homonyms resolved after this pass; re-checked, no other impact).
   - ADR-093-01 covers the image route. The explicit dismiss value (Q-093-12) is a backward-compatible request addition with no ADR needed.
3. **Plan alignment:** Pass.
4. **Tasks coverage:** Pass.
   - FR-093-01 → T-093-06/12
   - FR-093-02 → T-093-04/05
   - FR-093-03 → T-093-02/03/09/11
   - FR-093-04 → T-093-11
   - FR-093-05/06 → T-093-10/11
   - FR-093-07 → T-093-07/13
   - FR-093-08 → T-093-11
   - FR-093-09 → T-093-10/16
   - FR-093-10 → T-093-12/14
   - FR-093-11 → T-093-10
   - FR-093-12 → T-093-04/05/10/11
   - FR-093-13/14 → T-093-10
   - Backend tests come first. Accepted gap: the frontend scenarios are checked manually (no Vue test runner).
5. **Working-agreement compliance:** Pass.
   - No new dependencies.
   - The v2 change is additive: the parameter is optional and the existing behaviour stays the default. This is not a compatibility shim, since nothing old is emulated.
   - Helpers are pure.
6. **Tooling readiness:** Pass.

**Outcome:** ready for implementation.

## Drift Gate Report
Run on 2026-10-15 by the implementing agent after all 16 tasks were checked.

**Traceability**
| Requirement | Code | Verification |
|-------------|------|--------------|
| FR-093-01 | `FaceQuickNaming.vue` (photo, rect-fitted highlight), `FacePhotoAssetController` | `FacePhotoAssetTest`; browser run (screenshots 02, 07) |
| FR-093-02 | `QueryFaceQueue`, `GetFaceQueueRequest` | `FaceQueueTest` (ordering, filter, visibility, cap, single query) |
| FR-093-03 | `rankMatches`, `QuickNamingInput.vue`, `PeopleListController` | `PeopleListV3Test`; browser run ("Ali" → Alice) |
| FR-093-04 | `decideKey`, `useQuickNamingSession.skip/back` | Browser run, LTR and RTL |
| FR-093-05 | `QuickNamingInput.submit`, `useQuickNamingSession.unassign` | Browser run (revisit named, prefill) |
| FR-093-06 | `decideKey` (repeat/IME guards), `discard()` | Browser run; DB check (dismissed face) |
| FR-093-07 | `ModulesRightsResource::canQuickNameFaces`, entry buttons | `FaceQuickNamingRightsTest` |
| FR-093-08 | Input remount + autofocus, window focus listener | Browser run (focus asserted) |
| FR-093-09 | `enqueue()` failure marking, toast "Go back", explicit `is_dismissed` | `FaceDismissTest`; browser run with the assign route forced to 500 |
| FR-093-10 | "?" button, `KeybindingsHelp` `only` prop | Browser run (screenshot 13) |
| FR-093-11 | In-memory session, `reloadPeople()` after dismissing a named face | Code review |
| FR-093-12 | Suggestion subqueries, `resolveSuggestion` | `FaceQueueTest` (suggestions); browser run (same-cluster suggestion accepted) |
| FR-093-13 | Per-face chains, pending creations | Browser run (Shift+Enter creates a third "John Smith", DB shows one new person) |
| FR-093-14 | `?from=` back target, cluster-card button | Code review |
| NFR-093-03/06 | `lang/*/people.php`, `dialogs.php`, `all_settings.php` | Key-parity check across 23 locales |
| NFR-093-05 | Single `toBase()` SELECT | `DB::listen` assertions |

**Findings fixed during the gate** (commit `c4e80e5b`):
- The highlight drifted when the photo area shrank. It is now fitted to the rendered image rectangle.
- RTL direction was read at render time. It is now read at key time.
- A failed creation left its pending person in the list (raw-object vs reactive-proxy comparison). Removal now filters by id.

**Deviations from the plan:**
- The v3 asset route only allows thumbnail-class variants, so the face route got its own `FacePhotoAssetType` (medium allowed). This is recorded in ADR-093-01 and API-093-05.
- The quick naming help section is hidden from the global help modal and only shown from the view's "?" button.

**Environment notes:**
- The local test SQLite file carried the schema of another branch (`albums.min_takestamp` already dropped). It was moved aside to the scratchpad and rebuilt, which fixed every face test at once.
- The browser checks ran on a scratch instance (env overrides for the DB, uploads, cache and SoA flag; `php artisan serve` on port 8123; Playwright with the system Chromium) and never touched the development database.

**Gate:** `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check` and `make phpstan` all pass. The scoped run of 12 test classes passes (155 tests).

**Lessons:**
- Overlays on `object-contain` images need the rendered rectangle, not percentages of a shrinking wrapper.
- Never compare objects pushed into a Vue reactive array by identity.

## Exit Criteria
- All tasks checked, and scoped tests, `make phpstan`, `npm run check` and the formatters are green.
- `php artisan typescript:transform` has been rerun after the resource changes.
- Roadmap and knowledge map are updated, and the drift gate report is written.

## Follow-ups / Backlog
- Server-side person search if person lists grow large (Q-093-03 option C).
- Storing a dismissal reason (`blurry`) if detector tuning needs it (Q-093-02 option C).
- Touch swipe gestures.
- v7 parity, if ever wanted.
