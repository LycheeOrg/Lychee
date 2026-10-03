# Feature 080 – Versioning Code Simplification

| Field | Value |
|-------|-------|
| Status | Testing |
| Last updated | 2026-10-03 |
| Owners | LycheeOrg |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Feature 037 (FR-037-07/08) moved update detection onto one action (`CheckUpdateAvailability`) and the GitHub compare API. That left most of the versioning layer (`app/Metadata/Versions`, `app/Contracts/Versions`, `app/Metadata/Json`) serving a list-scan algorithm and a "tags" git mode that no longer carry information. This feature removes them, so `GitHubVersion` only reads the local `.git` state and asks the compare API for the distance to master. Affected layers: metadata/actions, diagnostics pipes, the update pipeline (`BranchCheck`), `UpdateController`. No REST payload shape and no frontend change.

## Goals
- Delete code with no caller: `CheckUpdate`, `UpdateStatus`, the `HasVersion` / `HasIsRelease` / `VersionControl` interfaces.
- Stop fetching the GitHub commits list. The compare API is the only remote git call.
- Remove tags mode (`GitTags`, `TagsRequest`, `AbstractGitRemote`, `GitRemote`). A detached HEAD is a plain local state.
- One formatter for the git description line (`LycheeGitInfo`), now used by Diagnostics only.
- Remove updating Lychee from the web interface (Q-080-02): no git pull, no composer from the web process.

## Non-Goals
- Changing the installer migration (`Install\MigrationController`, `/install/migrate`).
- Lazy hydration of `GitHubVersion` / `FileVersion` (item 6 of the 2026-10-03 review, not selected).
- Renaming `InstalledVersion::isRelease()` (item 4, not selected).
- Changing the `update-check` feature flag or the `check_for_updates` config.
- Any change to `FileVersion`, `InstalledVersion`, `CompareRequest`, `CheckUpdateAvailability` behaviour or the REST payloads of `Version`, `Admin/UpdateStatus`, `Maintenance::update`.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-080-01 | `GitHubVersion::hydrate()` reads `.git/HEAD`. `ref: refs/heads/<branch>` sets `local_branch = <branch>` and `local_head` = first 7 hex chars of `.git/refs/heads/<branch>`. Any other content is a detached HEAD: `local_branch = null`, `local_head` = first 7 chars of the content. | Local state available without any remote call. | — | `.git/HEAD` missing or unreadable → warning logged, both fields `null`. Ref file missing → warning logged, `local_head = null`. | Existing `Log::warning` lines kept. | Review 2026-10-03 items 2, 3. |
| FR-080-02 | When `hydrate(with_remote: true)` and the checkout is on `master` with a known `local_head`, the commits-behind count comes only from `CompareRequest` (`ahead_by`), and the age text from that request's cache age. No commits or tags list is fetched. | `getCountBehind()` returns `ahead_by`; `isUpToDate()` is true when it is 0. | Payload without an integer `ahead_by` → unknown. | Request failure / 404 → unknown (`false`), `isUpToDate()` true. | — | FR-037-08; review item 2. |
| FR-080-03 | `getBehindTest()` returns `Could not compare.` (unknown), `Up to date (<age>).` (0) or `<N> commits behind master (<age>)`. | Text used by Diagnostics and `Maintenance::update`. | — | — | — | Review item 2. |
| FR-080-04 | `GitHubVersion::isMasterBranch()` is true only when `local_branch === 'master'`. A detached HEAD is never master. `isDetached()` is true when `.git/HEAD` was read and holds no branch ref. `hasPermissions()` requires a branch (no tags mode). | `BranchCheck` lets master through. | — | Detached HEAD → `BranchCheck` stops the online update with `Branch is not master` (previously allowed, then `git pull` failed on the detached HEAD). | — | Review item 3; Q-080-01. |
| FR-080-05 | Version channel: no `.git` → `release`; detached HEAD → `tag`; branch → `git`. | `VersionInfo::getChannelName()` follows this table. | — | — | — | Review item 3; Q-080-01. |
| FR-080-06 | `VersionInfo::getGitInfo()` returns a `LycheeGitInfo` (or `null` when no `local_head`) used by both `VersionInfo::handle()` and `UpdateController::get()`. Branch checkout: `info = "<branch> (<sha>)"`, `extra = getBehindTest()`. Detached HEAD: `info = "<version.md version> (<sha>)"`, `extra = ""`. Diagnostics line = `info` followed by ` -- extra` when `extra` is not empty. | Both endpoints show the same text. | — | No git data → `No git data found.` (unchanged). | — | Review item 5. |
| FR-080-07 | Lychee shall not update itself from the web interface. Removed: web route `GET /Update`, `GET`/`POST /api/v2/Maintenance::update`, the Maintenance "Updates" card (v7 + v8), and the `BranchCheck` → `GitPull` → `ComposerCall` pipeline. Updates are applied on the server (git, composer, Docker image, release archive). | Admin dashboard banner still reports available updates (FR-037-07). | — | `GET /Update` returns 404; the API paths are unregistered (catch-all answers 406 to JSON). | — | Q-080-02. |
| FR-080-08 | `/migrate` (route name `migrate`, `migration:incomplete` middleware, `MigrateRequest` auth, `update.results` view) is kept and runs only the database migration through `ApplyMigration` (`ArtisanMigrate`, ANSI codes stripped from the output). | Pending migrations are applied and listed. | `MigrateRequest` unchanged. | — | — | Q-080-02 ("the migrate route stays"). |
| FR-080-09 | The settings `allow_online_git_pull` and `apply_composer_update` are removed by a migration; its `down()` restores both rows with their previous values (`1` / `0`, cat `Admin`, `0\|1`, secret, not on Docker, expert, order 2 / 3). Their labels leave `lang/*/all_settings.php`. | Settings page no longer lists them. | — | — | — | Q-080-02. |
| FR-080-10 | Diagnostics no longer runs `UpdatableCheck` ("can Lychee update itself" errors/warnings). `GitHubVersion::hasPermissions()` is removed with it. | Diagnostics errors list has no online-update entries. | — | — | — | Q-080-02. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-080-01 | At most one GitHub request per update check on a branch checkout (the compare request), cached per local SHA for `update_check_every_days`. | GitHub anonymous rate limit (60/h). | Unit test asserts no `CommitsRequest` / `TagsRequest` class exists and only `CompareRequest` is resolved. | `CompareRequest`. | Review item 2. |
| NFR-080-02 | REST payloads unchanged: `Version`, `Admin/UpdateStatus` (`Maintenance::update` removed by FR-080-07). | No frontend churn. | `php artisan typescript:transform` changes `resources/js/lychee.d.ts` only by dropping types of deleted classes (`App.Enum.UpdateStatus`, `UpdateInfo`, `UpdateCheckInfo`); existing Feature_v2 tests stay green. | — | Owner directive 2026-10-03. |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-080-01 | No `.git/HEAD` → warning, `local_branch`/`local_head` null, no remote call, `Could not compare.` |
| S-080-02 | On master, compare `ahead_by = 0` → up to date, `Up to date (<age>).` |
| S-080-03 | On master, compare `ahead_by = 42` → `42 commits behind master (<age>)`, not up to date. |
| S-080-04 | On master, compare fails → unknown, up to date, `Could not compare.` |
| S-080-05 | On a feature branch → no remote call, `isMasterBranch()` false. |
| S-080-06 | Detached HEAD → `isDetached()` true, `isMasterBranch()` false, no remote call, channel `tag`, info `<version> (<sha>)`, empty extra. |
| S-080-07 | Detached HEAD → `BranchCheck` returns `Branch is not master`. |
| S-080-08 | `hydrate(with_remote: false)` → local fields set, no remote call. |
| S-080-09 | Diagnostics shows the `LycheeGitInfo` line (superseded for `Maintenance::update` by FR-080-07). |
| S-080-10 | `GET /Update` → 404. |
| S-080-11 | `api/v2/Maintenance::update` is no longer a registered route (requests fall through to the app's catch-all, which answers JSON with 406). |
| S-080-12 | `/migrate` runs only `ArtisanMigrate` and renders `update.results`. |
| S-080-13 | After migrating, `allow_online_git_pull` / `apply_composer_update` rows are absent; rolling back restores them. Not covered by automated tests: database migrations are not tested in this project (`@codeCoverageIgnore` on `up()` / `down()`). |
| S-080-14 | Maintenance page renders without the Updates card (v7 + v8). |

## Test Strategy
- **Metadata:** rewrite `tests/Unit/Metadata/GitHubVersionTest.php` around FR-080-01..04 with a small `File` arrangement helper and a bound `CompareRequest` mock. Delete `tests/Unit/GitRemoteTest.php` and the `commits.json` / `tags.json` samples together with the classes they test; the compare parsing cases move into `GitHubVersionTest`.
- **Actions:** delete `tests/Unit/Actions/InstallUpdate/CheckUpdateTest.php` with `CheckUpdate`. `BranchCheckTest`, `CheckUpdateAvailabilityTest` stay green unchanged.
- **Diagnostics / REST:** new unit test for `VersionInfo::getGitInfo()` (branch, detached, none). Existing `Feature_v2/Maintenance/UpdateTest`, `Feature_v2/Diagnostics/InfoTest`, `Feature_v2/VersionTest`, `Feature_v2/Admin/AdminUpdateStatusControllerTest` stay green.
- **Contracts:** `php artisan typescript:transform` only drops `App.Enum.UpdateStatus` (NFR-080-02).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-080-01 | `LycheeGitInfo`: `info:string`, `extra:string`, `toString()` = `info` + (` -- extra` when not empty). | diagnostics, update controller |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-080-01 | REST `GET /api/v2/Maintenance::update` | Removed (FR-080-07). | 404. |
| API-080-02 | Web `GET\|POST /migrate` | Database migration only (FR-080-08). | `MigrateRequest`, `update.results`. |

### Removed code (approved 2026-10-03, review items 1–3)
| Path | Reason |
|------|--------|
| `app/Actions/InstallUpdate/CheckUpdate.php`, `app/Enum/UpdateStatus.php`, `tests/Unit/Actions/InstallUpdate/CheckUpdateTest.php` | No caller since FR-037-07. |
| `app/Contracts/Versions/HasVersion.php`, `HasIsRelease.php`, `VersionControl.php` | Never used as a type. |
| `app/Metadata/Json/CommitsRequest.php`, `app/Metadata/Versions/Remote/GitCommits.php`, `tests/Samples/commits.json` | Commits list no longer needed (FR-080-02). |
| `app/Metadata/Json/TagsRequest.php`, `app/Metadata/Versions/Remote/GitTags.php`, `app/Metadata/Versions/Remote/AbstractGitRemote.php`, `app/Contracts/Versions/Remote/GitRemote.php`, `tests/Samples/tags.json`, `tests/Unit/GitRemoteTest.php` | Tags mode removed (FR-080-04, FR-080-05). |
| `config/urls.php` keys `update.git.commits`, `update.git.tags` | No reader left. |
| `routes/web-admin-v2.php` `GET /Update`, `routes/api_v2.php` `GET`/`POST /Maintenance::update` | Online updater removed (FR-080-07). |
| `app/Http/Controllers/Admin/UpdateController.php` → replaced by `app/Http/Controllers/Admin/MigrateController.php` (`migrate()` only) | FR-080-07, FR-080-08. |
| `app/Actions/InstallUpdate/ApplyUpdate.php`, `tests/Unit/Actions/InstallUpdate/ApplyUpdateTest.php` → replaced by `ApplyMigration` + `ApplyMigrationTest` | FR-080-08. |
| `app/Actions/InstallUpdate/Pipes/BranchCheck.php`, `GitPull.php`, `ComposerCall.php` and their tests under `tests/Unit/Actions/InstallUpdate/Pipes/` | FR-080-07. |
| `app/Assets/CommandExecutor.php` | Only used by `GitPull` / `ComposerCall`. |
| `app/Actions/Diagnostics/Pipes/Checks/UpdatableCheck.php`, `app/Exceptions/VersionControlException.php` | FR-080-10 (exception only thrown/caught there). |
| `app/Http/Requests/Maintenance/UpdateRequest.php`, `app/Http/Resources/Diagnostics/UpdateInfo.php`, `UpdateCheckInfo.php` | FR-080-07. |
| `resources/js/v7/components/maintenance/MaintenanceUpdate.vue`, `resources/js/v8/components/maintenance/MaintenanceUpdate.vue`, `MaintenanceService.updateGet/updateCheck`, `maintenance.update.*` keys in `lang/*/maintenance.php` | FR-080-07. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-080-01 | `tests/Samples/compare.json` | Compare API sample (kept from Feature 037). |

## Telemetry & Observability
No new events. Existing `Log::warning` on unreadable `.git/HEAD` / ref file and `Log::error` on failed remote requests are kept.

## Documentation Deliverables
- Knowledge map: versioning entry rewritten (no GitRemote hierarchy).
- Feature 037 FR-037-08 cross-reference to FR-080-02.
- Roadmap: Feature 080 row.

## Spec DSL

```yaml
feature_id: 080
name: versioning-simplification
status: testing
domain_objects:
  - id: DO-080-01
    name: LycheeGitInfo
    fields:
      - { name: info,  type: string }
      - { name: extra, type: string }
routes:
  - { id: API-080-01, method: GET, path: /api/v2/Maintenance::update }
fixtures:
  - { id: FX-080-01, path: tests/Samples/compare.json }
channels:
  no_git: release
  detached_head: tag
  branch: git
remote_calls:
  branch_master: [CompareRequest]
  branch_other: []
  detached: []
```
