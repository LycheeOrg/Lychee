# Feature Plan 080 – Versioning Code Simplification

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Testing  
_Last updated:_ 2026-10-03

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
`GitHubVersion` becomes "read `.git` locally, ask the compare API when on master". The versioning layer loses the `GitRemote` hierarchy, the commits/tags requests and three unused interfaces, with no REST payload change.

**Success signals:**
- Every path in the spec's removed-code table is gone; `grep` finds no reference to them.
- `GitHubVersionTest` covers S-080-01 … S-080-08; `VersionInfoTest` covers S-080-06 and S-080-09.
- `php artisan typescript:transform` only drops the unused `App.Enum.UpdateStatus` (NFR-080-02).
- PHPStan 0 errors, php-cs-fixer clean.

## Scope Alignment
- **In scope:** review items 1, 2, 3, 5 (see Q-080-01).
- **Out of scope:** lazy hydration, `InstalledVersion::isRelease()` rename, update gates, frontend.

## Dependencies & Interfaces
- `CompareRequest` and `CheckUpdateAvailability` from Feature 037 (FR-037-07/08), unchanged.
- Consumers of `GitHubVersion`: `CheckUpdateAvailability`, `VersionInfo`, `UpdateController`, `UpdatableCheck`, `BranchCheck`.

## Assumptions & Risks
- **Assumptions:** no install depends on online updates from a detached HEAD (`git pull` cannot work there).
- **Risks / Mitigations:** `GitHubVersionTest` mocks `File` calls in a fixed order; it is rewritten with a helper rather than patched test by test.

## Implementation Drift Gate
After I3, re-read FR-080-01 … 06 against `GitHubVersion`, `VersionInfo`, `UpdateController`, `BranchCheck` and record the result here.

2026-10-03: FR-080-01 … 06 match the code (`GitHubVersion::hydrateLocal/hydrateRemote/isDetached/isMasterBranch/hasPermissions`, `VersionInfo::getChannelName/getGitInfo`, `UpdateController::get`). `BranchCheck` unchanged: it relies on `isMasterBranch()`, now false on a detached HEAD (FR-080-04). No drift.

## Increment Map
1. **I1 – Delete dead code (review item 1)**
   - _Steps:_ delete `CheckUpdate`, `UpdateStatus`, `CheckUpdateTest`, the three interfaces and their `implements` clauses, the `CheckUpdate` singleton binding.
   - _Commands:_ `make phpstan`, `php artisan test --filter='CheckUpdateAvailabilityTest|BranchCheckTest'`
   - _Exit:_ PHPStan clean.
2. **I2 – `GitHubVersion` local + compare only (review items 2, 3)**
   - _Steps:_ rewrite `GitHubVersionTest` (red), then rewrite `GitHubVersion`; delete `GitCommits`, `GitTags`, `AbstractGitRemote`, `GitRemote`, `CommitsRequest`, `TagsRequest`, `GitRemoteTest`, `commits.json`, `tags.json`, config URLs, `CommitsRequest` binding.
   - _Commands:_ `php artisan test --filter='GitHubVersionTest|BranchCheckTest|CheckUpdateAvailabilityTest'`, `make phpstan`
   - _Exit:_ S-080-01 … S-080-08 green.
3. **I3 – One git-info formatter (review item 5)**
   - _Steps:_ `VersionInfoTest` (red), then `LycheeGitInfo` (`info`, `extra`), `VersionInfo::getGitInfo()` / `getChannelName()`, `UpdateController::get()`.
   - _Commands:_ `php artisan test --filter='VersionInfoTest|Feature_v2\\Maintenance\\UpdateTest|Feature_v2\\Diagnostics\\InfoTest'`
   - _Exit:_ S-080-06, S-080-09 green.
4. **I4 – Gate + docs**
   - _Steps:_ php-cs-fixer, PHPStan, `typescript:transform` no-diff check, all related tests, knowledge map, Feature 037 cross-reference, roadmap.

5. **I5 – Remove the online updater (FR-080-07 … FR-080-10, Q-080-02)**
   - _Steps:_ tests first: `ApplyMigrationTest` (red), `Feature_v2/Maintenance/UpdateTest` rewritten to assert `GET`/`POST Maintenance::update` → 404 (red). Then: `ApplyMigration` + `MigrateController::migrate()`, delete the updater files listed in the spec, drop `UpdatableCheck` from `Errors`, remove `GitHubVersion::hasPermissions()` and its tests. Config-removal migration (no test, project convention) + `all_settings` labels. Frontend: remove `MaintenanceUpdate` (v7/v8), service methods, `maintenance.update.*` keys, regenerate TS types.
   - _Commands:_ `php artisan test --filter='ApplyMigrationTest|Feature_v2\\Maintenance\\UpdateTest|GitHubVersionTest|Feature_v2\\Diagnostics'`, `make phpstan`, `npm run format`, `npm run check`.
   - _Exit:_ S-080-10 … S-080-14 satisfied (S-080-13 manually only).

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-080-01 | I2 / T-080-03 | `GitHubVersionTest` |
| S-080-02 | I2 / T-080-03 | `GitHubVersionTest` |
| S-080-03 | I2 / T-080-03 | `GitHubVersionTest` |
| S-080-04 | I2 / T-080-03 | `GitHubVersionTest` |
| S-080-05 | I2 / T-080-03 | `GitHubVersionTest` |
| S-080-06 | I2 / T-080-03, I3 / T-080-05 | `GitHubVersionTest`, `VersionInfoTest` |
| S-080-07 | I2 / T-080-04 | `BranchCheck` uses `isMasterBranch()`; covered by `GitHubVersionTest` detached case + `BranchCheckTest` |
| S-080-08 | I2 / T-080-03 | `GitHubVersionTest` |
| S-080-09 | I3 / T-080-05 | `VersionInfoTest` |
| S-080-10 | I5 / T-080-09 | `Feature_v2\Maintenance\UpdateTest` (route 404) |
| S-080-11 | I5 / T-080-09 | `Feature_v2\Maintenance\UpdateTest` |
| S-080-12 | I5 / T-080-09 | `ApplyMigrationTest` |
| S-080-13 | I5 / T-080-11 | Not tested (no migration tests, project convention) |
| S-080-14 | I5 / T-080-12 | `npm run check` + manual browser check |

## Analysis Gate
2026-10-03 — spec, plan and tasks agree; the only decision (Q-080-01) is resolved. No ADR: no cross-module boundary change.

## Exit Criteria
- Quality gate green (php-cs-fixer, PHPStan, related test classes).
- `typescript:transform` only drops `App.Enum.UpdateStatus`.
- Knowledge map + roadmap updated.

## Follow-ups / Backlog
- Review item 6 (lazy hydration) and item 4 (`isRelease()` rename) if wanted later.
- Negative caching for failed compare requests (from Feature 037 I8).
- ~~`UpdateController::check()` never hydrated remote data~~ — resolved by removing the online updater (I5).

## Intent Log
- 2026-10-03: operator asked whether versioning could be simplified; review listed six items; operator answered "Do 1235".
- 2026-10-03: `UpdateController::check()` found to never hydrate remote data; operator chose to remove online updating entirely (Q-080-02 Option C), keep `/migrate`, and reminded that migrations are not tested.
