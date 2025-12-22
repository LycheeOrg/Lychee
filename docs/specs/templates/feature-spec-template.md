# Feature <NNN> – <Descriptive Name>

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | YYYY-MM-DD |
| Owners | <Name(s)> |
| Linked plan | ``docs/specs/4-architecture/features`/<NNN>/plan.md` |
| Linked tasks | ``docs/specs/4-architecture/features`/<NNN>/tasks.md` |
| Roadmap entry | #<workstream number> |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under ``docs/specs/5-decisions`/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Summarise the problem, affected modules (core/application/CLI/REST/UI), and the user impact in 2–3 sentences. Call out any constitutional constraints (spec-first, telemetry, persistence) that drive this work.

## Goals
List the concrete outcomes this feature must deliver (behavioural, quality, telemetry, documentation).

## Non-Goals
Call out adjacent topics that remain out of scope so the implementation doesn’t drift.

## Functional Requirements
Capture consumer-facing behaviour—whether the consumer is a human operator, API/CLI client, or another external system—in a table. IDs follow the pattern `FR-<featureId>-<nn>` (for example, `FR-040-01`). Tie each entry to scenario IDs so plans/tasks/tests can reference them.

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-<NNN>-01 | Describe the behaviour/constraint. | Required behaviour when inputs are valid. | Input validation logic, errors, or warnings. | How the system responds to downstream faults. | Event names, redaction rules, verbose trace changes. | Standards, specs, or owner directives that justify this requirement. |
| FR-<NNN>-02 | … | … | … | … | … | … |

## Non-Functional Requirements
List quality, performance, security, accessibility, or governance expectations in a table. IDs follow the pattern `NFR-<featureId>-<nn>` (for example, `NFR-040-01`).

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-<NNN>-01 | Describe the quality/performance/security constraint. | Why this constraint exists (accessibility, telemetry parity, deterministic fixtures, etc.). | How success is verified (latency budget, lint rule, WCAG reference, etc.). | Modules, tooling, or specs needed to satisfy it. | Normative reference or owner directive. |
| NFR-<NNN>-02 | … | … | … | … | … |

## UI / Interaction Mock-ups (required for UI-facing work)
Embed ASCII sketches illustrating layouts or state changes. Reference the guideline in [docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md](docs/specs/4-architecture/spec-guidelines/ui-ascii-mockups.md) when completing this section. Remove it if the feature has no UI impact.

```
<ASCII mock-up>
```

## Branch & Scenario Matrix
Assign each scenario a stable identifier (format `S-<NNN>-<nn>`, e.g., `S-005-01`) so feature plans, tasks, and tests can reference it without editing the spec. Keep entries high-level; all implementation status tracking happens in the plan/tasks.

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-<NNN>-01 | Describe behaviour |

## Test Strategy
Describe how each layer gains coverage. Mention failing tests that must be staged before implementation.
- **Core:** …
- **Application:** …
- **REST:** …
- **CLI:** …
- **UI (JS/Selenium):** …
- **Docs/Contracts:** OpenAPI, telemetry snapshots, etc.

## Interface & Contract Catalogue
Document the artifacts this feature governs so other specs and automation can reference them.

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-<NNN>-01 | e.g., HotpEvaluationRequest fields, validation rules | core, application, REST |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-<NNN>-01 | REST POST /api/v1/... | … | Schema reference |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-<NNN>-01 | ./bin/... evaluate | Describe what the command does. |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-<NNN>-01 | example.event | `field`, `reasonCode`, `sanitized=true`. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-<NNN>-01 | docs/specs/test-vectors/...json | Describe fixture usage. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-<NNN>-01 | Example state | Describe trigger/expected outcome. |

## Telemetry & Observability
Detail event names, required fields, redaction rules, and verbose-trace additions so all facades stay in sync.

## Documentation Deliverables
Enumerate roadmap/knowledge-map/how-to/ADR updates triggered by this feature.

## Fixtures & Sample Data
List any fixture files that must be added or updated (e.g., `docs/specs/test-vectors`/<protocol>/…).

## Spec DSL
Provide a machine-readable summary that mirrors the catalogue above so tooling can parse it. Use YAML/JSON-style keys and reuse the IDs already defined.

```
domain_objects:
  - id: DO-<NNN>-01
    name: HotpEvaluationRequest
    fields:
      - name: counter
        type: integer
        constraints: ">= 0"
routes:
  - id: API-<NNN>-01
    method: POST
    path: /api/v1/.../evaluate
cli_commands:
  - id: CLI-<NNN>-01
    command: ./bin/... evaluate
telemetry_events:
  - id: TE-<NNN>-01
    event: hotp.evaluate
fixtures:
  - id: FX-<NNN>-01
    path: docs/specs/test-vectors/...json
ui_states:
  - id: UI-<NNN>-01
    description: Evaluate form success card
```

## Appendix (Optional)
Include supporting notes, payload examples, or references that help future agents understand the context.