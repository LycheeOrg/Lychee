# Feature Plan 111 – Login Rate Limit Feedback

Linked specification: [spec.md](spec.md). Status: verified. Updated: 2026-11-08.

## Vision & Success Criteria
Inline translated 429 feedback in v7/v8, no duplicate overlay, existing successful login intact.

## Scope Alignment
Owner chose Q-111-01 A. Backend policy unchanged.

## Dependencies & Interfaces
LoginForm.vue in v7/v8, AuthService, AxiosConfig, existing gallery_unlock.too_many_attempts translation.

## Assumptions & Risks
Checkout has no node_modules/vendor; Node, npm, pnpm, PHP, Composer and vp are absent from PATH. Establish a runnable environment before test-first implementation. Owner authorized environment setup using existing locked versions on 2026-11-08. Use Node 26 Docker container and npm ci, without dependency upgrades.

## Increment Map
1. I1 (≤90 minutes planned): failing component and interceptor tests for S-111-01–05, then minimal implementation, scoped tests, vp check --fix and vp run typecheck. Prepare operator commit with scripts/codex-commit-review.sh.

## Scenario Tracking
S-111-01–05 map to T-111-02/03/04.

## Analysis Gate
2026-11-08: requirements, UI sketch, question resolution, scope and branch matrix agree. No feature-111 ADR exists or is required for this local bug fix. PASS: Node 26 Docker environment installed locked dependencies. Scoped tests ran: 4 expected failures, 10 pass. All analysis checks satisfied before implementation.

## Implementation Drift Gate
Passed 2026-11-08: FR-111-01–03 covered by component and interceptor tests, all tasks checked, no scope drift, no backend changes. Browser verification against a running Lychee instance remains an explicit practical limitation.

## Exit Criteria
Tests, formatting, typecheck green, docs updated, operator commit prepared.

## Intent & Tool Usage
Owner requests upstream contribution after production reproduction using temporary five requests/five minutes image in homeserver. Read forms, Axios interceptor, service, templates and analysis checklist. No production changes in this increment.

## Verification environment
Owner authorized setup using existing lockfile. `docker run --rm -v "$PWD":/app -w /app node:26-bookworm npm ci --no-audit --no-fund` installed 585 packages without changing lockfiles. The same container runs `node_modules/.bin/vp` commands. No host runtime installation or dependency upgrades.

## Quality checks
- Initial scoped test run: 4 expected failures, 10 passes.
- After fix: 14 passes, later expanded with network/server-error cases.
- `vp check --fix` on the five changed frontend files: no warnings/errors.
- `vp run typecheck`: both generation build and vue-tsc passed. Existing noise.png asset warning during generation build.
- Final regression run includes viewer-state and client-cache tests (results below).
- No PHP modified, so PHP test/static-analysis commands are not applicable.

## Reflection
The form catch handlers consumed 429 without local feedback. The interceptor emitted a global error, so the reproduction establishes missing local feedback rather than a server request hanging. Reusing the existing gallery-unlock translation avoids translation churn. No new architectural boundary or ADR.

Final regression: 26 tests passed in 4 files. RCI review and git diff --check passed. Operator commit: fix(auth): show inline feedback for throttled password logins. Spec impact: document Feature 111 login rate-limit feedback and verification.
