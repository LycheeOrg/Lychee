# Feature 083 – Album Tab Title

| Field | Value |
|-------|-------|
| Status | Complete |
| Last updated | 2026-10-07 |
| Owners | koonweee |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #083 |

## Overview
Album pages currently use the site name as their browser tab title, making several open albums difficult to distinguish. Extend the shared v7/v8 document-title composable to use the loaded album title.

## Goals
Make album tabs identifiable while preserving the media viewer's existing title behaviour.

## Non-Goals
New settings, dependencies, server-side title changes, and changes to permissions or non-album panels.

## Functional Requirements
| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-083-01 | Show `Album title · Site title` on album and flow-album routes. | Use the current loaded album title, including existing translation support. | Loaded album ID must match the route's album ID. | Keep the site title while metadata is unavailable. | None | User request |
| FR-083-02 | Preserve the media viewer's title priority. | Show the open photo/video title; restore the album title on close. | Empty media titles use the album/site title. | Existing behaviour applies. | None | Existing composable |
| FR-083-03 | Restore the site title outside album panels. | Gallery home and other panels use the site title when no media is open. | Ignore stale album metadata. | No extra metadata requests. | None | User request |

## Non-Functional Requirements
| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-083-01 | Use one shared composable without new dependencies or requests. | Keep the change small and apply it to v7/v8 and guest/owner views. | Focused reactive tests, type-check, formatting and build. | Vue Router, existing stores | User request |

## UI / Interaction Mock-ups
```text
Album:        [ Summer holiday · Example Gallery ] [+]
Media open:   [ Beach sunset                     ] [+]
Gallery home: [ Example Gallery                  ] [+]
```

## Branch & Scenario Matrix
| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-083-01 | Loaded album title appears and updates when its title changes. |
| S-083-02 | Opening/closing media switches between media and album titles. |
| S-083-03 | Navigating home or to another panel ignores retained album metadata. |
| S-083-04 | Navigating between albums uses the site title until matching metadata loads. |
| S-083-05 | Missing or empty titles retain the site/album title; smart album labels are translated. |
| S-083-06 | Flow-album routes use the same album title behaviour. |

## Test Strategy
Run the actual composable with Vue's reactive scheduler and isolated route/store inputs using Node's existing test runner. Write the regression tests before implementation; run them in the existing JavaScript CI job. No PHP contracts change.

## Interface & Contract Catalogue
The existing `useDocumentTitle()` governs `document.title`. Existing album/photo stores and route parameters supply all inputs; no API, CLI, or data schema changes.

## Telemetry & Observability
No telemetry changes.

## Documentation Deliverables
Spec, plan, tasks, open-question log, roadmap entry, and shared composable knowledge-map entry.

## Fixtures & Sample Data
Only dummy strings such as `Summer holiday`, `Beach sunset`, and `Example Gallery`.
