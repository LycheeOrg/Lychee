# Architecture (Draft)

## Templates

Use the shared templates under `docs/specs/templates/` whenever you add or modify documentation artifacts:

- `feature-spec-template.md` – canonical schema for feature specifications (metadata table, clarifications, requirements, mock-ups, test strategy).
- `feature-plan-template.md` – increment planner with drift-gate notes, scope alignment, and ≤90-minute slices.
- `feature-tasks-template.md` – per-feature checklist capturing task intent plus verification commands.
- `adr-template.md` – architectural decision records.
- `how-to-template.md` – operational playbooks/how-to guides.
- `runbook-template.md` – incident/runbook documentation.

## Feature Artifact Layout

Every feature owns a dedicated directory under `docs/specs/4-architecture/features/<NNN>-<feature-name>/`. Each folder contains:

- `spec.md` – the authoritative specification.
- `plan.md` – the implementation plan aligned with the roadmap slice.
- `tasks.md` – the execution checklist, including scenario IDs and verification commands.
- Optional supporting notes (for example, protocol data models or UI sketches specific to that feature).