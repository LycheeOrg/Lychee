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
Album metadata may remain in its store after leaving an album. Check only that the route is album or flow-album before using its title. The user accepts the previous title until another album loads and the raw site-title placeholder during initialization. Album and site titles are used directly. Missing metadata requires no new request.

## Increment Map
1. **I1 – Album title and navigation regression coverage (≤90 minutes)**
   - Write failing reactive regression tests for S-083-01 through S-083-06.
   - Extend `useDocumentTitle()` and run the tests to green.
   - Run `node --test tests/Frontend/useDocumentTitle.test.mjs`, `npm run format`, `npm run check`, lint and build.
   - Add the test command to the existing JavaScript CI job; update documentation and review the diff.

2. **I2 – Simplify title selection after upstream review (≤90 minutes)**
   - Update the spec and regression expectations for literal titles and the accepted album-loading gap.
   - Remove frontend translations, the album-ID check, and redundant intermediate values. Keep the album-route check and album + site format.
   - Run the focused tests, formatting, TypeScript, ESLint and both builds, then commit, push and reply to the three review comments as authorized by the user.

## Scenario Tracking
All scenarios map to I1 and T-083-01 through T-083-03 in [tasks.md](tasks.md). I2 and T-083-04 revise S-083-04 and S-083-05 after review while retaining S-083-03 coverage.

## Analysis Gate
2026-10-07: specification, UI mock-up, branches, tasks and existing architecture reviewed. No high/medium-impact open questions or related ADRs. User authorized the stated title precedence, site-title behaviour and a small upstream PR. Frontend-only quality gates apply.

## Implementation Drift Gate
2026-10-07: all seven reactive tests pass after formatting. `npm run format`, `npm run check-formatting`, `npm run check`, `npx eslint resources/js/`, and `npm run build` pass. The main and embed production builds succeed. Original implementation self-review confirmed the route/ID guard prevents retained album metadata from leaking into other panels, while media-title precedence is unchanged. No spec drift or follow-up work.

2026-10-07 review simplification: the revised navigation and literal-title tests fail on the previous implementation (two failures), then all seven pass with the simplified watcher. `npm run format`, test-file Prettier, `npm run check-formatting`, `npm run check`, `npx eslint resources/js/`, `npm run build` and `git diff --check` pass. Only the album-route check remains. No unrelated tracked files changed.

## Exit Criteria
Regression tests, formatting, type-check, lint and build pass; PR uses dummy examples and targets master.

## Intent & Tool Usage
User requested an upstream PR following contribution guidelines and code styles, with a high-level description, dummy examples and no overengineering. Reviewed AGENTS.md, contribution guide, templates, knowledge map, architecture graph, route/store lifecycle and previous title fix #4758. Used local Git/npm and GitHub CLI; no production application changes.

2026-10-07 review follow-up: user approved literal titles, a route-only guard, previous album titles during loading, and the brief initial site placeholder. Authorized committing/pushing the changes and posting the agreed concise replies.

## Follow-ups / Backlog
None.
