# Feature Plan 083 – Album Tab Title

_Linked specification:_ [spec.md](spec.md)

_Status:_ Complete

_Last updated:_ 2026-10-07

## Vision & Success Criteria
Album tabs identify the current album, media tabs retain their existing title, and navigating away restores the site title.

## Scope Alignment
Extend the existing shared title watcher and add focused regression tests. No settings, dependencies, or backend changes.

## Dependencies & Interfaces
Existing Vue reactivity, Vue Router, album/photo/site stores, Node test runner, and TypeScript transpiler already in the lockfile.

## Assumptions & Risks
Album metadata may remain in its store after leaving an album. Check the route and matching album ID before using its title. Missing metadata requires no new request.

## Increment Map
1. **I1 – Album title and navigation regression coverage (≤90 minutes)**
   - Write failing reactive regression tests for S-083-01 through S-083-06.
   - Extend `useDocumentTitle()` and run the tests to green.
   - Run `node --test tests/Frontend/useDocumentTitle.test.mjs`, `npm run format`, `npm run check`, lint and build.
   - Add the test command to the existing JavaScript CI job; update documentation and review the diff.

## Scenario Tracking
All scenarios map to I1 and T-083-01 through T-083-03 in [tasks.md](tasks.md).

## Analysis Gate
2026-10-07: specification, UI mock-up, branches, tasks and existing architecture reviewed. No high/medium-impact open questions or related ADRs. User authorized the stated title precedence, site-title behaviour and a small upstream PR. Frontend-only quality gates apply.

## Implementation Drift Gate
2026-10-07: all seven reactive tests pass after formatting. `npm run format`, `npm run check-formatting`, `npm run check`, `npx eslint resources/js/`, and `npm run build` pass. The main and embed production builds succeed. Self-review confirms the route/ID guard prevents retained album metadata from leaking into other panels, while media-title precedence is unchanged. No spec drift or follow-up work.

## Exit Criteria
Regression tests, formatting, type-check, lint and build pass; PR uses dummy examples and targets master.

## Intent & Tool Usage
User requested an upstream PR following contribution guidelines and code styles, with a high-level description, dummy examples and no overengineering. Reviewed AGENTS.md, contribution guide, templates, knowledge map, architecture graph, route/store lifecycle and previous title fix #4758. Used local Git/npm and GitHub CLI; no production application changes.

## Follow-ups / Backlog
None.
