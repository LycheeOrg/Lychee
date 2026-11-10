# Feature Plan 083 – Fix Search Header Refresh

_Linked specification:_ [spec.md](spec.md)
_Status:_ Implemented
_Last updated:_ 2026-11-10

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
A user on a real mobile browser who hard-refreshes a Lychee search results URL sees the header (back button + title) the same as any other navigation path into search. Success = S-083-01 passes on a real device; S-083-02/03 show no regression; `npm run format`/`npm run check` stay green.

## Scope Alignment
- **In scope:** The two-line fix in `resources/js/v7/views/gallery-panels/Search.vue` (FR-083-01, FR-083-02); real-device verification; this spec/plan/tasks/open-questions set; a short knowledge-map addendum; preparing the upstream PR.
- **Out of scope:** `v8` tree, broader `scrollIntoView()` audit elsewhere in the codebase, adding a JS/TS test harness (see spec Non-Goals).

## Dependencies & Interfaces
- `resources/js/v7/views/gallery-panels/Search.vue` only.
- Dev fork's local Docker stack (`docker-compose-local.yaml`, `localhost:8000`) for rebuild/redeploy during verification — already used during the investigation that produced this fix.
- No REST/DB/DTO dependencies.

## Assumptions & Risks
- **Assumptions:** The reported bug and fix are specific to the `v7` tree (confirmed — `v8` has a structurally different `SearchHeader`/`UHeader` implementation and was not the code path serving `akina.oldboys.nz` at investigation time).
- **Risks / Mitigations:** No automated regression coverage exists for this bug class (real-mobile-only). Mitigation: the real-device isolation matrix (spec Appendix) is the strongest verification available given the repo's current tooling; documented explicitly rather than silently skipped, per spec Test Strategy.

## Implementation Drift Gate
Executed as part of this same session, immediately after implementation (increment I2) — see completed checklist appended to this plan's **Analysis Gate** section below, since both the specification and the implementation were already finalised (and independently real-device-verified) before this formal spec/plan/tasks pass was written. No drift found: the two-line diff matches FR-083-01/FR-083-02 exactly, and no undocumented behaviour was introduced.

## Increment Map

1. **I1 – Spec/plan/tasks/open-questions authored** (this increment)
   - _Goal:_ Capture the already-diagnosed bug, fix, and real-device verification evidence in the formal SDD artefacts before preparing the upstream PR.
   - _Preconditions:_ Root cause diagnosed and fix isolation-tested on a real device (done, prior to this spec existing — see spec Appendix).
   - _Steps:_ Write spec.md, open-questions.md (resolved Q-083-01), plan.md, tasks.md; prime/update the knowledge map.
   - _Commands:_ None (docs only).
   - _Exit:_ All four feature files exist and cross-reference each other and the spec's FR/NFR/Scenario IDs.
2. **I2 – Implementation verification & quality gate**
   - _Goal:_ Confirm the already-applied two-line diff matches the spec exactly and passes the frontend quality gate.
   - _Preconditions:_ I1 complete.
   - _Steps:_ Diff `resources/js/v7/views/gallery-panels/Search.vue` against FR-083-01/FR-083-02; run `npm run format`; run `npm run check`.
   - _Commands:_ `npm run format`, `npm run check`.
   - _Exit:_ Both commands pass with zero errors; diff unchanged by `format` (already matches Prettier style) or re-verified if it reformatted anything.
3. **I3 – Commit preparation**
   - _Goal:_ Hand the operator a ready-to-run commit command per the Commit protocol.
   - _Preconditions:_ I2 green.
   - _Steps:_ Stage `resources/js/v7/views/gallery-panels/Search.vue` and the new `docs/specs/4-architecture/features/083-fix-search-header-refresh/` files; run `scripts/codex-commit-review.sh`; present the suggested Conventional Commit message and ready `git commit`/`git push` commands.
   - _Commands:_ `scripts/codex-commit-review.sh` (or equivalent), `git commit …`, `git push -u origin spec/083-fix-search-header-refresh`.
   - _Exit:_ Operator has copy/paste-ready commands; nothing committed or pushed by the agent without explicit operator action.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-083-01 | I2 / T-083-01 | Verified real-device, prior session (spec Appendix); re-confirmed here against the final diff. |
| S-083-02 | I2 / T-083-01 | Unaffected path — no `q` present at mount, so `onMounted()`'s auto-search branch never runs. |
| S-083-03 | I2 / T-083-01, T-083-06 | Desktop Chrome — bug never reproduced; fix confirmed a true no-op via byte-identical layout geometry before/after (T-083-06), not just "never reproduced." |
| S-083-04 | I2 / T-083-01 | DevTools emulation — documented as non-discriminating for this bug class (see spec). |

## Analysis Gate

**Recorded 2026-11-10, self-reviewed (solo session, no separate human reviewer at this stage — operator will review via the PR itself).**

1. Specification completeness — ✅ Objectives/FR/NFR populated; Q-083-01 resolved and folded into FR-083-03; UI mock-up included (before/after ASCII, since this is a timing bug not a static layout change).
2. Open questions review — ✅ No blocking `Open` entries remain (Q-083-01 resolved). No architecturally-significant decision here — no ADR needed (small, reversible, two-line frontend fix, not a cross-module boundary/security/telemetry decision).
3. Plan alignment — ✅ This plan references spec.md/tasks.md correctly; dependencies/success criteria match spec wording.
4. Tasks coverage — ✅ Every FR maps to T-083-01 (see tasks.md); tests-before-implementation is inverted here only because implementation already happened (and was itself verified) before this formal pass was written — documented explicitly as a deviation, not silently skipped, since there is no automated test to sequence against in any order (see spec Test Strategy).
5. Working-agreement compliance — ✅ No new control-flow complexity (2-line diff, no new branching). No new dependencies.
6. Tooling readiness — ✅ `npm run format`/`npm run check` commands documented in plan I2 and tasks.md.

**Implementation Drift Gate** — ✅ run immediately after I2 (see note above): diff matches FR-083-01/FR-083-02 verbatim, no undocumented behaviour, no missing coverage beyond what's already explicitly documented as unavailable (spec Test Strategy).

## Exit Criteria
- [x] `resources/js/v7/views/gallery-panels/Search.vue` diff matches FR-083-01 + FR-083-02 exactly.
- [x] `npm run format` clean.
- [x] `npm run check` clean.
- [x] Knowledge-map addendum added.
- [x] Commit prepared (not executed) for the operator, on `spec/083-fix-search-header-refresh` (never `master` — see [[feedback_use_dedicated_branch_for_upstream_prs]]).
- [ ] PR opened against `LycheeOrg/Lychee` by the operator, referencing the discussion thread.

## Follow-ups / Backlog
- Consider auditing other `scrollIntoView()` call sites in `v7`/`v8` for the same ancestor-climbing risk (flagged in spec Non-Goals; not blocking this fix).
- Consider porting the equivalent fix to `v8`'s `Search.vue` if/when `NUXT_UI_ENABLED` ships as the default (flagged in spec Non-Goals; not currently the live code path so not urgent).
