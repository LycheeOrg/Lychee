# Feature 032 Tasks – Security Advisories Check

_Status: Draft_  
_Last updated: 2026-04-06_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-032-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Configuration & DTO

- [ ] T-032-01 – Add `advisories` config block to `config/urls.php` and `vulnerability-check` flag to `config/features.php` (FR-032-01, FR-032-08, NFR-032-03, NFR-032-04).  
  _Intent:_ `config/urls.php` gets a new `advisories` array with `api_url` (default: `https://api.github.com/repos/LycheeOrg/Lychee/security-advisories`) and `cache_ttl` (default: `1`, in days, from `ADVISORIES_CACHE_TTL_DAYS`). `config/features.php` gets `vulnerability-check` key (default: `true`, from `VULNERABILITY_CHECK_ENABLED`).  
  _Verification commands:_
  - `php artisan config:clear && php artisan config:show urls`
  - `php artisan config:show features`
  - `make phpstan`

- [ ] T-032-02 – Create `app/DTO/SecurityAdvisory.php` (DO-032-01).  
  _Intent:_ Immutable DTO with public constructor properties: `cve_id: ?string`, `ghsa_id: string`, `summary: string`, `cvss_score: ?float`, `cvss_vector: ?string`, `affected_version_range: string`. No Spatie Data extension needed (pure PHP DTO for internal use).  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-032-03 – Write `tests/Unit/Services/VersionRangeCheckerTest.php` (RED) then create `app/Services/VersionRangeChecker.php` (DO-032-02, NFR-032-06, S-032-13).  
  _Intent:_ Test file covers: `>=` (boundary: equal version matches, lesser does not), `<=`, `>`, `<`, `=`, `!=`, comma-separated multi-constraint (S-032-13: `>= 5.0.0, < 5.1.2` → 5.0.5 matches, 5.1.2 does not), malformed token skipped (S-032-09). `VersionRangeChecker::matches(Version $version, string $range): bool` — splits range on `,`, trims, parses operator + version, evaluates all; returns `true` only if all constraints pass.  
  _Verification commands:_
  - `php artisan test --filter=VersionRangeCheckerTest`
  - `make phpstan`

### I2 – Advisory Fetch Service

- [ ] T-032-04 – Extend `app/Metadata/Json/ExternalRequestFunctions.php` to support custom HTTP headers (NFR-032-05).  
  _Intent:_ Add `array $extra_headers = []` as a third constructor parameter. Merge `$extra_headers` into the `http.header` array inside `fetchFromServer()` stream context options. Existing callers (`CommitsRequest`, `TagsRequest`, etc.) are unaffected (default empty array). Preserve backward compatibility.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`
  - `php artisan test` (full suite; ensure no regressions)

- [ ] T-032-05 – Create `app/Metadata/Json/AdvisoriesRequest.php` (FR-032-01, NFR-032-05).  
  _Intent:_ Extends `JsonRequestFunctions`. Constructor reads `Config::get('urls.advisories.api_url')` and `Config::get('urls.advisories.cache_ttl')`. Passes `['Accept: application/vnd.github+json']` as `$extra_headers` to the parent constructor.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-032-06 – Create test fixture `tests/Fixtures/github-security-advisories.json` (FX-032-01).  
  _Intent:_ JSON array with two entries:
  1. Advisory with `cve_id = "CVE-2024-00001"`, `cvss.score = 9.8`, `vulnerabilities[0].vulnerable_version_range` set to a range that matches the test version (e.g. `">= 4.0.0, < 99.0.0"`).
  2. Advisory with `cve_id = "CVE-2024-00002"`, `cvss.score = 3.1`, `vulnerabilities[0].vulnerable_version_range` set to a non-matching range (e.g. `"< 1.0.0"`).
  Both entries include `ghsa_id`, `summary`, `cvss.vector_string`, and `vulnerabilities[0].package`.  
  _Verification commands:_
  - `php -r "json_decode(file_get_contents('tests/Fixtures/github-security-advisories.json'), true, 512, JSON_THROW_ON_ERROR); echo 'OK';"` (validate JSON)

- [ ] T-032-07 – Write `tests/Unit/Services/SecurityAdvisoriesServiceTest.php` (RED) then create `app/Services/SecurityAdvisoriesService.php` (FR-032-01, FR-032-02, FR-032-03, FR-032-08, S-032-01, S-032-02, S-032-03, S-032-05, S-032-06, S-032-09).  
  _Intent:_ Service constructor takes `AdvisoriesRequest` (or mock), `VersionRangeChecker`, and `InstalledVersion`. `getMatchingAdvisories(): SecurityAdvisory[]` — returns `[]` if feature disabled (S-032-01); fetches via `AdvisoriesRequest::get_json(use_cache: true)`; returns `[]` on null/empty response (S-032-05); maps each advisory's `vulnerabilities` to check range match; skips and logs malformed tokens (S-032-09); returns matching `SecurityAdvisory[]`. Tests use fixture JSON injected directly (not via HTTP); cache behaviour verified with `Cache::fake()` (S-032-06).  
  _Verification commands:_
  - `php artisan test --filter=SecurityAdvisoriesServiceTest`
  - `make phpstan`

### I3 – Diagnostic Pipe

- [ ] T-032-08 – Write `tests/Unit/Actions/Diagnostics/SecurityAdvisoriesCheckTest.php` (RED) then create `app/Actions/Diagnostics/Pipes/Checks/SecurityAdvisoriesCheck.php` and register it in `Errors.php` (FR-032-04, S-032-01, S-032-02, S-032-03, S-032-04, NFR-032-01).  
  _Intent:_ Implements `DiagnosticPipe`. `handle()` guards: return `$next($data)` if `config('features.vulnerability-check')` is falsy or `Auth::user()` is not admin (`may_administrate = false`). For each advisory returned by `SecurityAdvisoriesService::getMatchingAdvisories()`, appends `DiagnosticData::error("Security vulnerability: {cve_id} (CVSS {score})", self::class, [$advisory->summary])`. When `cve_id` is null, use `ghsa_id` instead. Register `SecurityAdvisoriesCheck::class` at the end of `$pipes` in `Errors.php`. Tests: feature disabled → no entries (S-032-01); non-admin → no entries (NFR-032-01); no advisories → no entries (S-032-02); one advisory → one error entry with correct format (S-032-03); two advisories → two error entries (S-032-04).  
  _Verification commands:_
  - `php artisan test --filter=SecurityAdvisoriesCheckTest`
  - `make phpstan`

### I4 – REST Endpoint

- [ ] T-032-09 – Create `app/Http/Resources/Models/SecurityAdvisoryResource.php` (API-032-01).  
  _Intent:_ Extends Spatie `Data`. Public constructor: `cve_id: ?string`, `ghsa_id: string`, `summary: string`, `cvss_score: ?float`, `cvss_vector: ?string`. Add static factory `fromAdvisory(SecurityAdvisory $a): self`.  
  _Verification commands:_
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-032-10 – Write feature tests `tests/Feature_v2/SecurityAdvisories/IndexTest.php` (RED) then create `app/Http/Controllers/Admin/SecurityAdvisoriesController.php` and register the route (FR-032-05, FR-032-08, S-032-01, S-032-07, S-032-08, NFR-032-01).  
  _Intent:_ Test scenarios: admin receives `[]` when feature disabled (S-032-01); admin receives matched advisory list (S-032-03); non-admin receives 403 (S-032-07); unauthenticated receives 401 (S-032-08). Controller method: `index(Request $request): SecurityAdvisoryResource[]` — admin check via `$request->user()?->may_administrate`; returns resource collection. Route: `GET /api/v2/Security/Advisories` registered in admin API routes file with `auth` middleware.  
  _Verification commands:_
  - `php artisan test --filter=SecurityAdvisories`
  - `make phpstan`

### I5 – Frontend Modal

- [ ] T-032-11 – Update `resources/js/lychee.d.ts` to add `SecurityAdvisoryResource` type (API-032-01).  
  _Intent:_ Add `namespace App.Http.Resources.Models { interface SecurityAdvisoryResource { cve_id: string | null; ghsa_id: string; summary: string; cvss_score: number | null; cvss_vector: string | null; } }` (or equivalent namespace path matching the backend resource).  
  _Verification commands:_
  - `npm run type-check`

- [ ] T-032-12 – Create `resources/js/services/security-advisories-service.ts`.  
  _Intent:_ Exports `SecurityAdvisoriesService` object with `getAdvisories(): Promise<AxiosResponse<App.Http.Resources.Models.SecurityAdvisoryResource[]>>` calling `GET ${Constants.getApiUrl()}Security/Advisories`.  
  _Verification commands:_
  - `npm run type-check`
  - `npm run lint`

- [ ] T-032-13 – Write Vitest unit test then create `resources/js/components/modals/SecurityAdvisoriesModal.vue` (FR-032-06, FR-032-07, UI-032-01, UI-032-02, UI-032-03, S-032-10, S-032-11).  
  _Intent:_ PrimeVue `Dialog` component. Props: `advisories: SecurityAdvisoryResource[]`, `visible: boolean`. Emits `update:visible`. Lists CVE ID and CVSS score per advisory. "Close" button: emits `update:visible` with `false`, sets `sessionStorage.setItem('advisory_dismissed', '1')`. "Go to Diagnostics" button: navigates to diagnostics route and emits close. Vitest test: renders CVE list from props; clicking Close emits `update:visible(false)` and sets `sessionStorage`; does not render when `visible = false`.  
  _Verification commands:_
  - `npm run test` (Vitest)
  - `npm run type-check`

- [ ] T-032-14 – Integrate advisory check into post-login flow (FR-032-06, FR-032-07, FR-032-08, S-032-01, S-032-10, S-032-11, S-032-12).  
  _Intent:_ After a successful admin login response, check `sessionStorage.getItem('advisory_dismissed')`; if not set and `is_admin` is true, call `SecurityAdvisoriesService.getAdvisories().then(response => { if (response.data.length > 0) { /* show modal */ } })`. Pass the advisory list to `SecurityAdvisoriesModal`. Non-admin users: skip advisory call entirely (S-032-12). Feature-disabled path: endpoint returns `[]`; modal not shown (S-032-01). Dismissal: modal emits close → `advisory_dismissed` set in `sessionStorage` (S-032-10).  
  _Verification commands:_
  - `npm run type-check`
  - `npm run lint`

### I6 – Quality Gates & Documentation

- [ ] T-032-15 – Run full PHP test suite and all quality gates (exit criterion).  
  _Intent:_ Ensure no regressions from I1–I4 changes.  
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix --dry-run`

- [ ] T-032-16 – Run full frontend quality gates (exit criterion).  
  _Intent:_ Ensure no regressions from I5 changes.  
  _Verification commands:_
  - `npm run type-check`
  - `npm run lint`
  - `npm run test`

- [ ] T-032-17 – Update `docs/specs/4-architecture/knowledge-map.md` with `SecurityAdvisoriesService` and `SecurityAdvisoriesCheck` entries.  
  _Intent:_ Document the new service and diagnostic pipe in the architectural knowledge map.  
  _Verification commands:_ (manual review)

- [ ] T-032-18 – Move Feature 032 in `docs/specs/4-architecture/roadmap.md` from Active → Completed.  
  _Intent:_ Roadmap reflects completed state.  
  _Verification commands:_ (manual review)

## Notes / TODOs

- T-032-04 touches `ExternalRequestFunctions` which is used by multiple existing classes (`CommitsRequest`, `TagsRequest`, `UpdateRequest`, `ChangeLogsRequest`). Regression tests for those classes must pass after the change.
- If `InstalledVersion::getVersion()` throws before migrations are run, `SecurityAdvisoriesCheck` should catch the exception and skip gracefully (log a debug message).
- The `cve_id` field on GitHub advisory responses can be `null` for unpublished CVEs; `SecurityAdvisoriesCheck` falls back to `ghsa_id` in that case.
- `sessionStorage` is cleared when the browser tab is closed (S-032-11 expected behaviour); if a persistent-across-sessions dismissal is needed, a follow-up task should use `localStorage` or a database flag.
