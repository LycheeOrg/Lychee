# Feature 111 – Login Rate Limit Feedback

| Field | Value |
|-------|-------|
| Status | Verified |
| Last updated | 2026-11-08 |
| Owners | Daniel |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #111 |

## Overview
Password login in v7 and v8 handles 401 locally but silently consumes 429.
Provide local translated feedback without a duplicate global error.

## Goals
Explain temporary login throttling in both interfaces.

## Non-Goals
Backend throttling changes, countdowns, submission lockout, dependencies or schema changes.

## Functional Requirements
| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-111-01 | Both password forms show translated inline feedback on 429. | Existing login success behaviour. | 401 keeps invalid credentials feedback. | 429 keeps form open and displays try-again-later feedback. | No credentials logged. | Q-111-01 A |
| FR-111-02 | Suppress duplicate global error only for password-login 429. | Other responses unchanged. | Match method and endpoint. | Other endpoints retain global handling. | Existing global error event unchanged elsewhere. | Q-111-01 A |
| FR-111-03 | Clear obsolete login feedback on a new attempt, success or switching to WebAuthn. | Success resets viewer state as before. | Only the current attempt's error is visible. | No automatic retry. | None. | Q-111-01 A |

## Non-Functional Requirements
| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-111-01 | Existing translated message and native error components are reused. | Small upstream contribution. | v7 and v8 component tests, lint, typecheck. | Existing frontend tooling. | Owner choice A |

## UI / Interaction Mock-ups
```text
Username [                  ]
Password [                  ]
[Too many attempts, try again later.]
[Cancel]             [Sign in]
```

## Branch & Scenario Matrix
| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-111-01 | 429 displays inline feedback in v7 and v8, no global overlay. |
| S-111-02 | 401 displays existing invalid-credentials feedback. |
| S-111-03 | Success emits existing login flow and clears feedback. |
| S-111-04 | Next attempt and switching to WebAuthn clear stale feedback. |
| S-111-05 | Other errors and other endpoints keep global handling. |

## Test Strategy
Frontend tests first: component interaction tests for both forms and interceptor tests for endpoint scoping. Confirm failing cases before implementation.

## Interface & Contract Catalogue
### API Routes / Services
POST /api/v2/Auth::login through AuthService.login; server contract unchanged.
### UI States
Idle, invalid credentials, temporarily throttled. No countdown.

## Telemetry & Observability
No new events. Suppress existing global error event only for locally handled login 429. Never log submitted credentials.

## Documentation Deliverables
Specification, plan, tasks, question outcome, roadmap and knowledge-map contract entry.

## Fixtures & Sample Data
Mock Axios responses only, no real credentials.

## Spec DSL
```yaml
feature: 111
route: POST /api/v2/Auth::login
ui_states: [idle, invalid_credentials, throttled]
backend_changes: false
```
