# Analysis Gate Checklist

Use this checklist after a feature's specification, plan, and tasks exist but before implementation begins. After implementation, complete the Implementation Drift Gate section before the feature can be marked complete. Together these guardrails enforce the project constitution and keep specifications, plans, tasks, and code aligned.

## Inputs
- Feature specification (e.g., docs/specs/4-architecture/features/XXX/spec.md)
- Feature plan (e.g., docs/specs/4-architecture/features/XXX/plan.md)
- Feature tasks (e.g., docs/specs/4-architecture/features/XXX/tasks.md)
- Open questions log ([docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md))
- Constitution ([docs/specs/6-decisions/project-constitution.md](docs/specs/6-decisions/project-constitution.md))
- Feature plan subsection reserved for the Implementation Drift Gate report (create if missing)

## Checklist
1. **Specification completeness** 
   - [ ] Objectives, functional, and non-functional requirements are populated.
   - [ ] Resolved high- and medium-impact questions for this feature are reflected directly in the spec’s normative sections (requirements, NFR, behaviour/UI, telemetry/policy).
   - [ ] UI-impacting work includes an ASCII mock-up in the spec ([docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md)).
2. **Open questions review**
   - [ ] No blocking `Open` entries remain for this feature in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md). If any exist, pause and obtain clarification.
   - [ ] For architecturally significant decisions (cross-feature/module boundaries, security/telemetry strategies, major NFR trade-offs), ADRs exist or are planned, and the spec/open-questions entries link to the corresponding ADR IDs.
3. **Plan alignment**
   - [ ] Feature plan references the correct specification and tasks files.
   - [ ] Dependencies and success criteria match the specification wording.
4. **Tasks coverage**
   - [ ] Every functional requirement maps to at least one task.
   - [ ] Tasks sequence tests before implementation and keep planned increments ≤90 minutes by outlining logical, self-contained slices (execution may run longer if needed).
   - [ ] Planned tests enumerate the success, validation, and failure branches with failing cases queued before implementation begins.
5. **Constitution compliance**
   - [ ] No planned work violates principles (spec-first, clarification gate, test-first, documentation sync, dependency control).
   - [ ] Planned increments minimise new control-flow complexity by extracting validation/normalisation into small helpers, keeping each change nearly straight-line.
   - [ ] For the active feature, relevant ADRs (per their Related features/specs metadata) have been reviewed as part of this analysis.
6. **Tooling readiness**
   - [ ] Commands documented for the feature plan or runbook.
   - [ ] Analysis results recorded in the feature plan (copy this checklist with pass/fail notes).

## Implementation Drift Gate (Pre-Completion)
Run this section once all planned tasks are complete and the latest build is green.

1. **Preconditions**
   - [ ] Feature tasks are all marked complete (☐ → ☑) and associated specs/plans reflect the final implementation.
   - [ ] Latest quality gate check (or narrower documented suite) has passed within this increment.
2. **Cross-artifact validation**
   - [ ] Every high- and medium-impact specification requirement maps to executable code/tests; cite spec sections against classes/tests in the drift report, and note any low-level coverage adjustments.
   - [ ] No implementation or tests lack an originating spec/plan task; undocumented work is captured as a follow-up task or spec addition.
   - [ ] Feature plan and tasks remain consistent with the shipped implementation (dependencies, acceptance criteria, sequencing).
3. **Divergence handling**
   - [ ] High- and medium-impact gaps or over-deliveries are logged as new entries in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) for user direction.
   - [ ] Low-impact or low-level drift (typos, minor wording, formatting) is corrected directly before finalising the report; document the fix without escalating.
   - [ ] Follow-up tasks or spec updates are drafted for any outstanding divergences awaiting approval.
4. **Coverage confirmation**
   - [ ] Tests exist for each success, validation, and failure branch enumerated in the specification, and their latest run is green.
   - [ ] Any missing coverage is documented with explicit tasks and blockers.
5. **Report & retrospective**
   - [ ] Implementation Drift Gate report added to the feature plan, detailing findings, artefact links, and reviewer(s).
   - [ ] Lessons learned and reusable guidance captured for future features (e.g., updates to specs/runbooks/templates).
   - [ ] Stakeholders (product, technical, AI agent as applicable) have acknowledged the report outcome before completion.

## Output
Document the outcome in the relevant feature plan under a "Analysis Gate" subsection, including:
- Date/time of the review
- Checklist pass/fail notes
- Follow-up actions or remediation tasks

Only proceed to implementation when every checkbox is satisfied or deferred with explicit owner approval.

For the Implementation Drift Gate, append the completed checklist and report summary to the feature plan. Do not mark the feature complete until all high/medium-impact divergences are resolved through updated specs, approved tasks, or user sign-off.