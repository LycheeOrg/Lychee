# Feature Plan <NNN> – <Descriptive Name>

_Linked specification:_ ``docs/specs/4-architecture/features`/<NNN>/spec.md`  
_Status:_ Draft  
_Last updated:_ YYYY-MM-DD

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under ``docs/specs/5-decisions`/` have been updated.

## Vision & Success Criteria
Reiterate the user value, measurable success signals, and quality bars (telemetry parity, deterministic fixtures, etc.).

## Scope Alignment
- **In scope:** Bullet the behaviours/increments covered by this plan.
- **Out of scope:** State exclusions so adjacent workstreams stay unaffected.

## Dependencies & Interfaces
List modules, external specs, telemetry contracts, fixtures, or tooling that this plan relies on.

## Assumptions & Risks
- **Assumptions:** Preconditions that must hold true (e.g., fixture availability).
- **Risks / Mitigations:** Potential blockers and how to address them early.

## Implementation Drift Gate
Describe how the drift gate will be executed (what evidence, where to record results, which commands to rerun). Include placeholders for traceability matrices or lessons learned.

## Increment Map
Break the feature into ≤90-minute increments. Each increment should identify prerequisites, deliverables, tests, and commands.

1. **I1 – <Title>**
   - _Goal:_ Brief description.
   - _Preconditions:_ Specs/tests that must already exist.
   - _Steps:_ Bullet the work items (tests first, then implementation).
   - _Commands:_ `php artisan …`, `node --test …`, etc.
   - _Exit:_ Definition of done for this increment.
2. **I2 – <Title>**
   - …

Add as many increments as required. For parallel work, split into sub-increments (I3a/I3b) instead of bloating a single entry.

## Scenario Tracking
Map each Branch & Scenario Matrix ID to the increments/tasks that implement it so changes remain traceable.

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-<NNN>-01 | I1 / T<taskId> | e.g., covered by HOTP console harness |

## Analysis Gate
Record when the analysis gate was completed, who reviewed it, and any findings that must be addressed before implementation resumes.

## Exit Criteria
- Enumerate the checklist that must pass before declaring the feature complete (full Gradle gate, OpenAPI snapshot, documentation updates, etc.).

## Follow-ups / Backlog
Capture post-feature investigations, deferred optimisations, or monitoring tasks so they can be prioritised later.